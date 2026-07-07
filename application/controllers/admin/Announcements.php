<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Announcements extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('announcements_model');
        $this->load->model('departments_model');
        
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

    }

    /* List all announcements */
    public function index()
    {
        // if ($this->input->is_ajax_request()) {
        //     $this->app->get_table_data('announcements');
        // }
        // $data['title'] = _l('announcements');
        // $this->load->view('admin/announcements/manage', $data);show_404
        $this->announcement();
    }

    /* Edit announcement or add new if passed id */
    public function announcement($id = '')
    {
        // if (!is_admin()) {
        //     access_denied('Announcement');
        // }
        
      $department_id=$this->staff_model->get(get_staff_user_id(), $where = [], $all = 0)->department??'';
 
if(is_admin())
{
    $department_id='';
}
        $data['departments'] = $this->staff_model->staff_department($department_id);
        $data['priority']    = $this->announcements_model->announcements_priority();
        $this->load->view('admin/announcements/announcement', $data);
    }

    public function get_announcements()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $filters = array(
            'id'  => $this->input->post('id'),
            'department'  => $this->input->post('department'),
            'priority'    => $this->input->post('priority'),
            'read_status' => $this->input->post('read_status'),
            'search'      => $this->input->post('search'),
            'sub_department'=> $this->input->post('sub_department'),
            'sort_by'     => $this->input->post('sort_by', 'date'),
            'user_id'     => get_staff_user_id(),
            'user_name'   => get_staff_full_name(get_staff_user_id())
        );

        $result = $this->announcements_model->get_announcements($filters);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function create_announcement()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $data = array(
            'title'        => $this->input->post('title'),
            'content'      => $this->input->post('content'),
            'department'   => $this->input->post('department'),
            'priority'     => $this->input->post('priority'),
            'sub_department'=> $this->input->post('sub_department'),
            'stakeholder'  => get_staff_full_name(get_staff_user_id()),
            'created_by'   => get_staff_user_id(),
            'created_date' => date('Y-m-d H:i:s')
        );

        $result = $this->announcements_model->create_announcement($data);

        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Announcement created successfully', 'id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create announcement']);
        }
    }

    public function update_announcement($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Check if user is authorized (only stakeholder can edit)
        $announcement = $this->announcements_model->get_announcement_by_id($id);

        if (!$announcement) {
            echo json_encode(['success' => false, 'message' => 'Announcement not found']);
            return;
        }

        if ($announcement['can_edit'] != 1) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to edit this announcement']);
            return;
        }



        $data = array(
            'title'        => $this->input->post('title'),
            'content'      => $this->input->post('content'),
            'department'   => $this->input->post('department'),
            'priority'     => $this->input->post('priority'),
            'sub_department'=> $this->input->post('sub_department'),
            'updated_date' => date('Y-m-d H:i:s')
        );

        $result = $this->announcements_model->update_announcement($id, $data, get_staff_user_id());


        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update announcement']);
        }
    }

    public function mark_as_read($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->announcements_model->mark_as_read($id, get_staff_user_id());

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }

    // API: Delete announcement
    public function delete_announcement($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Check authorization
        $announcement = $this->announcements_model->get_announcement_by_id($id);

        if (!$announcement) {
            echo json_encode(['success' => false, 'message' => 'Announcement not found']);
            return;
        }

        if ($announcement['stakeholder'] != get_staff_full_name(get_staff_user_id())) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this announcement']);
            return;
        }

        $result = $this->announcements_model->delete_announcement($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }

    // API: Get dashboard stats
    public function get_stats()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $stats = $this->announcements_model->get_stats(get_staff_user_id());

        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    public function view($id)
    {
        if (is_staff_member()) {
            $announcement = $this->announcements_model->get($id);
            if (!$announcement) {
                blank_page(_l('announcement_not_found'));
            }
            $data['announcement']         = $announcement;
            $data['recent_announcements'] = $this->announcements_model->get('', [
                'announcementid !=' => $id,
            ], 4);
            $data['title'] = $announcement->name;
            $this->load->view('admin/announcements/view', $data);
        }
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('announcements'));
        }
        if (!is_admin()) {
            access_denied('Announcement');
        }
        $response = $this->announcements_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('announcement')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('announcement_lowercase')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    
    // Bump the view counter (this is what was missing entirely)
public function increment_view($id)
{
    if (!$this->input->is_ajax_request()) show_404();
    $this->announcements_model->increment_view($id, get_staff_user_id());
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}

// Stream an attachment inline (images preview, others open/download)
public function download_attachment($id)
{
    $att = $this->announcements_model->get_attachment($id);
    if (!$att) show_404();
    $path = FCPATH . $att['file_path'];
    if (!is_file($path)) show_404();
    $mime = function_exists('mime_content_type') ? mime_content_type($path) : ($att['file_type'] ?: 'application/octet-stream');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . basename($att['file_name']) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

public function delete_attachment($id)
{
    if (!$this->input->is_ajax_request()) show_404();
    $att = $this->announcements_model->get_attachment($id);
    if ($att) {
        $p = FCPATH . $att['file_path'];
        if (is_file($p)) @unlink($p);
        $this->announcements_model->delete_attachment($id);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}

// Shared upload handler — call after insert/update
private function _handle_uploads($announcement_id)
{
    if (empty($_FILES['attachments']['name'][0])) return;
    $allowed = ['pdf','doc','docx','xls','xlsx','csv','png','jpg','jpeg','gif','webp'];
    $relDir  = 'uploads/announcements/' . $announcement_id . '/';
    $absDir  = FCPATH . $relDir;
    if (!is_dir($absDir)) mkdir($absDir, 0755, true);

    $files = $_FILES['attachments'];
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        $orig = $files['name'][$i];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        $safe = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
        if (move_uploaded_file($files['tmp_name'][$i], $absDir . $safe)) {
            $this->announcements_model->add_attachment([
                'announcement_id' => $announcement_id,
                'file_name'       => $orig,
                'file_path'       => $relDir . $safe,
                'file_type'       => $files['type'][$i],
                'file_size'       => $files['size'][$i],
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }
}

public function unread_count()
{
    if (!$this->input->is_ajax_request()) show_404();
    header('Content-Type: application/json');
    echo json_encode(['count' => $this->announcements_model->get_unread_count()]);
}
}