<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Knowledge_base extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('knowledge_base_model');
        $this->load->model('knowledge_base_group_model');
    }

    /* List all knowledgebase articles */
    // public function index()
    // {
    //     if (!has_permission('knowledge_base', '', 'view')) {
    //         access_denied('knowledge_base');
    //     }
    //     if ($this->input->is_ajax_request()) {
    //         $this->app->get_table_data('kb_articles');
    //     }
    //     $data['groups']    = $this->knowledge_base_model->get_kbg();
    //     $data['bodyclass'] = 'top-tabs kan-ban-body';
    //     $data['title']     = _l('kb_string');
    //     $this->load->view('admin/knowledge_base/articles', $data);
    // }

    public function index()
    {
        if (!has_permission('knowledge_base', '', 'view_own') && !has_permission('knowledge_base', '', 'view') && !staff_has_assigned_knowledge_base()) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('kb_articles');
            $this->app->get_table_data('knowledge_base');
        }
        // $data['groups']    = $this->knowledge_base_model->get_kbg();
        $data['knowledge_group']     = $this->knowledge_base_group_model->get_knowledge_groups('', ['status' => 1]);
        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $this->load->view('admin/knowledge_base/knowledge_base', $data);
    }

    public function edit_knowledge_base($id)
    {
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
        }
        $data['knowledge']     = $this->db->select('*')->from(db_prefix() . 'knowledge')->where(array('id' => $id))->get()->result_array();
        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $data['knowledge_group']     = $this->knowledge_base_group_model->get_knowledge_groups('', ['status' => 1]);
        $this->load->view('admin/knowledge_base/create_knowledge_base', $data);
    }

    public function create_knowledge_base()
    {
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $id = !empty($data["id"]) ? $data["id"] : '';
            $data['media'] = '';
            $data['group_id'] = !empty($data['group_id']) ? implode(",", $data['group_id']) : '';
            if (!empty($data["id"])) {

                if (!empty($_FILES["media"]['name'])) {
                    $upload_data["name"] = $_FILES["media"]['name'];
                    $upload_data["type"] = $_FILES["media"]['type'];
                    $upload_data["tmp_name"] = $_FILES["media"]['tmp_name'];
                    $upload_data["error"] = $_FILES["media"]['error'];
                    $upload_data["size"] = $_FILES["media"]['size'];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {

                        $filename = uniqid() . '_' . basename($upload_data['name']);
                        // Move the uploaded file to the desired directory
                        $destination = KNOWLEDGE_MEDIA_PATH . '/' . $filename;
                        if (move_uploaded_file($upload_data['tmp_name'], $destination)) {
                        } else {
                            set_alert('danger', 'Failed to uploaded media file.');
                        }
                        $media_url = base_url() . $destination;
                        $data['media'] = $media_url;
                    } else {
                        $data['resp_code'] = 'ERR';
                        $data['resp_desc'] = "Upload failed.";
                        set_alert('danger', "Upload failed.");
                        echo json_encode($data);
                        die;
                    }
                } else {
                    unset($data['media']);
                }
                unset($data['id']);

                $success =   $this->db->update(db_prefix() . 'knowledge', $data, array("id" => $id));
                if ($success == true) {
                    $message = _l('updated_successfully', _l('kb_string'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {


                if (!empty($_FILES["media"]['name'])) {
                    $upload_data["name"] = $_FILES["media"]['name'];
                    $upload_data["type"] = $_FILES["media"]['type'];
                    $upload_data["tmp_name"] = $_FILES["media"]['tmp_name'];
                    $upload_data["error"] = $_FILES["media"]['error'];
                    $upload_data["size"] = $_FILES["media"]['size'];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $filename = uniqid() . '_' . basename($upload_data['name']);
                        // Move the uploaded file to the desired directory
                        $destination = KNOWLEDGE_MEDIA_PATH . '/' . $filename;
                        if (move_uploaded_file($upload_data['tmp_name'], $destination)) {
                        } else {
                            set_alert('danger', 'Failed to uploaded media file.');
                        }
                        $media_url = base_url() . $destination;
                        $data['media'] = $media_url;
                    } else {
                        $data['resp_code'] = 'ERR';
                        $data['resp_desc'] = "Upload failed.";
                        set_alert('danger', "Upload failed.");
                        echo json_encode($data);
                        die;
                    }
                } else {
                    unset($data['media']);
                }
                unset($data['id']);

                $insert = $this->db->insert(db_prefix() . 'knowledge', $data);
                $insert_id = $this->db->insert_id();
                if ($insert == true) {
                    $message = _l('added_successfully', _l('kb_string'));
                }
                echo json_encode([
                    'success' => $insert_id ? true : false,
                    'message' => $message,
                    'id'      => $insert_id,
                    'name'    => $data['title'],
                ]);
            }

            die;
        }
        // $data['groups']    = $this->knowledge_base_model->get_kbg();

        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $data['knowledge_group']     = $this->knowledge_base_group_model->get_knowledge_groups('', ['status' => 1]);
        $this->load->view('admin/knowledge_base/create_knowledge_base', $data);
    }

    public function manage_knowledge_groups()
    {
        if (!has_permission('knowledge_base', '', 'view') && !has_permission('knowledge_base', '', 'view_own')  && !staff_has_assigned_knowledge_base()) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('knowledge_groups');
        }
        $data['title'] = _l('customer_groups');
        $data['department']  = $this->db->where("status", 1)->get(db_prefix() . "staff_department")->result_array();

        $data['members']     = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $this->load->view('admin/knowledge_base/manage_knowledge_groups', $data);
    }


    /* Delete knowledge group */
    public function delete_kmowledge_group($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        $delete      = $this->knowledge_base_group_model->delete($id);
        if ($delete) {
            set_alert('success', _l('deleted', _l('kb_dt_group_name')));
            redirect(admin_url('knowledge_base/manage_knowledge_groups'));
        }
    }


    public function create_folder()
    {
        // Check permission


        if (!empty($_POST["show_folder"]) && $_POST["show_folder"] == 1) {
            if (!has_permission('knowledge_base', '', 'view' && !has_permission('knowledge_base', '', 'view_own')) && !staff_has_assigned_knowledge_base()) {
                access_denied('knowledge_base');
                $response = array(
                    "success" => 0,
                    "message" => "Access denied"
                );
                echo json_encode($response);
                die;
            }

            $parent_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
            // $folders = $this->db->select("*")->where(array("status" => 1, "parent_id" => $parent_id))->get(db_prefix() . "knowledge_base_folder")->result_array();
            $folders = $this->knowledge_base_group_model->get_folders(array("f.status" => 1, "f.parent_id" => $parent_id));
            // $files = $this->db->select("*")->where(array("status" => 1, "folder_id" => $parent_id))->get(db_prefix() . "knowledge_base_files")->result_array();
            $files = $this->knowledge_base_group_model->get_files(array("fs.status" => 1, "fs.folder_id" => $parent_id));

            // Prepare response
            $response = array(
                "success" => 1,
                "folder" => $folders,
                "files" => $files,
                "message" => "Folders list"
            );
            echo json_encode($response);
            die;
        }


        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
            $response = array(
                "success" => 0,
                "message" => "Access denied"
            );
            echo json_encode($response);
            die;
        }

        // Validate folder name and group IDs
        if (empty($_POST["folder_names"]) || empty($_POST["group_id"])) {
            $response = array(
                "success" => 0,
                "message" => "Error: No valid folder names provided"
            );
            echo json_encode($response);
            die;
        }


        // Get folder name and group IDs from POST data
        $folder_names =  str_replace("//", "/", explode("/", trim($_POST["folder_names"])));
        $group_id = $_POST["group_id"];
        $parent_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
        $folder_id = !empty($_POST["folder_id"]) ? $_POST["folder_id"] : 0;

        // Initialize data array for batch insertion
        $data = [];

        // Iterate over folder names
        foreach ($folder_names as $folder_name) {
            // Validate each folder name
            if (!empty($folder_name)) {
                // Check if the folder already exists
                $existingFolder = $this->db->get_where(db_prefix() . "knowledge_base_folder", ['folder_name' => $folder_name])->row_array();
                if (!$existingFolder) {
                    // Prepare data for insertion
                    if (!empty($folder_id)) {
                        $data[] = array(
                            'id' => $folder_id,
                            'folder_name' => $folder_name,
                            'status' => 1,
                            'parent_id' => $parent_id,
                            'group_ids' => implode(",", $group_id) // Assuming group_ids is an array
                        );
                    } else {
                        $data[] = array(
                            'folder_name' => $folder_name,
                            'status' => 1,
                            'parent_id' => $parent_id,
                            'group_ids' => implode(",", $group_id) // Assuming group_ids is an array
                        );
                    }
                    // Create directory if it doesn't exist
                    $dirPath = str_replace("//", "/", KNOWLEDGE_BASE_MEDIA_PATH . $folder_name);
                    if (!is_dir($dirPath)) {
                        mkdir($dirPath, 0777, true);
                    }
                }
            }
        }

        // Check if there's any data to insert
        if (!empty($data)) {
            // Perform batch insertion
            if (!empty($folder_id)) {
                $this->db->update_batch(db_prefix() . "knowledge_base_folder", $data, 'id');
            } else {
                $this->db->insert_batch(db_prefix() . "knowledge_base_folder", $data);
            }

            // Fetch inserted folders
            $folders =  $this->knowledge_base_group_model->get_folders();
            // $files = $this->db->select("*")->where(array("status" => 1, "folder_id" => $parent_id))->get(db_prefix() . "knowledge_base_files")->result_array();
            $files = $this->knowledge_base_group_model->get_files(array("fs.status" => 1, "fs.folder_id" => $parent_id));

            // Prepare response
            $response = array(
                "success" => 1,
                "folder" => $folders,
                "files" => $files,
                "message" => "Folders created successfully"
            );
        } else {
            // No valid data to insert
            // Handle this case
            $response = array(
                "success" => 0,
                "message" => "Error: No valid folder names provided"
            );
        }

        // Send JSON response
        echo json_encode($response);
    }

    public function upload_dir_data()
    {

        // Check permission
        if (!has_permission('knowledge_base', '', 'create')) {
            access_denied('knowledge_base');
            $response = array(
                "success" => 0,
                "message" => "Access denied"
            );
            echo json_encode($response);
            die;
        }

        $current_dir = !empty($_POST["current_dir"]) ? $_POST["current_dir"] : '';

        $folder_id = !empty($_POST["index"]) ? $_POST["index"] : 0;
        $data = [];


        if (!empty($_FILES["files"])) {
            foreach ($_FILES["files"]["name"] as $key => $filename) {
                $file_type = pathinfo($filename, PATHINFO_EXTENSION); // Get file extension
                $basename = pathinfo($filename, PATHINFO_FILENAME); // Get file name without extension
                $tmp_name = $_FILES["files"]["tmp_name"][$key];
                $error = $_FILES["files"]["error"][$key];

                $upload_data["name"] = $filename;
                $upload_data["type"] = $_FILES["files"]["type"][$key];
                $upload_data["tmp_name"] = $tmp_name;
                $upload_data["error"] = $error;
                $upload_data["size"] = $_FILES["files"]["size"][$key];

                if ($error === UPLOAD_ERR_OK) {
                    // Move the uploaded file to the desired directory
                    $destination = KNOWLEDGE_BASE_MEDIA_PATH . '/' . $filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $media_url = base_url() . $destination;
                        $data[] = array("path" => $media_url, "type" => $file_type, "file_name" => $basename, "folder_id" => $folder_id, "status" => 1);
                    }
                }
            }
        }


        if (!empty($data)) {
            $this->db->insert_batch(db_prefix() . "knowledge_base_files", $data);
            $response = array(
                "success" => 1,
                "message" => "Files uploaded successfully"
            );
        } else {
            // No valid data to insert
            // Handle this case
            $response = array(
                "success" => 0,
                "message" => "Error"
            );
        }

        // Send JSON response
        echo json_encode($response);
    }

    public function knowledge_group()
    {


        if ($this->input->is_ajax_request()) {

            $data = $this->input->post();
            $data['staff_ids'] = !empty($data['staff_ids']) ? implode(",", array_unique($data['staff_ids'])) : '';

            $data['department'] = !empty($data['department']) ? implode(",", array_unique($data['department'])) : '';


            if ($data['id'] == '') {

                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }

                $id      = $this->knowledge_base_group_model->add($data);
                $message = $id ? _l('added_successfully', _l('knowledge_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['name'],
                ]);
            } else {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }


                $success = $this->knowledge_base_group_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('knowledge_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    /* Add new article or edit existing*/
    public function article($id = '')
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = html_purify($this->input->post('description', false));

            if ($id == '') {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }
                $id = $this->knowledge_base_model->add_article($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('kb_article')));
                    redirect(admin_url('knowledge_base/article/' . $id));
                }
            } else {
                if (!has_permission('knowledge_base', '', 'edit')) {
                    access_denied('knowledge_base');
                }
                $success = $this->knowledge_base_model->update_article($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('kb_article')));
                }
                redirect(admin_url('knowledge_base/article/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('kb_article_lowercase'));
        } else {
            $article         = $this->knowledge_base_model->get($id);
            $data['article'] = $article;
            $title           = _l('edit', _l('kb_article')) . ' ' . $article->subject;
        }

        $this->app_scripts->add('tinymce-stickytoolbar', site_url('assets/plugins/tinymce-stickytoolbar/stickytoolbar.js'));

        $data['bodyclass'] = 'kb-article';
        $data['title']     = $title;
        $this->load->view('admin/knowledge_base/article', $data);
    }

    public function view($slug)
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('View Knowledge Base Article');
        }

        $data['article'] = $this->knowledge_base_model->get(false, $slug);

        if (!$data['article']) {
            show_404();
        }

        $data['related_articles'] = $this->knowledge_base_model->get_related_articles($data['article']->articleid, false);

        add_views_tracking('kb_article', $data['article']->articleid);
        $data['title'] = $data['article']->subject;
        $this->load->view('admin/knowledge_base/view', $data);
    }

    public function add_kb_answer()
    {
        // This is for did you find this answer useful
        if (($this->input->post() && $this->input->is_ajax_request())) {
            echo json_encode($this->knowledge_base_model->add_article_answer($this->input->post('articleid'), $this->input->post('answer')));
            die();
        }
    }

    /* Change article active or inactive */
    public function change_article_status($id, $status)
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->knowledge_base_model->change_article_status($id, $status);
            }
        }
    }

    public function update_kan_ban()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $success = $this->knowledge_base_model->update_kan_ban($this->input->post());
                $message = '';
                if ($success) {
                    $message = _l('updated_successfully', _l('kb_article'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
                die();
            }
        }
    }

    public function change_group_color()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $this->knowledge_base_model->change_group_color($this->input->post());
            }
        }
    }

    /* Delete article from database */
    public function delete_article($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        if (!$id) {
            redirect(admin_url('knowledge_base'));
        }
        $response = $this->knowledge_base_model->delete_article($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('kb_article')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('kb_article_lowercase')));
        }
        redirect(admin_url('knowledge_base'));
    }

    /* View all article groups */
    public function manage_groups()
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        $data['groups'] = $this->knowledge_base_model->get_kbg();
        $data['title']  = _l('als_kb_groups');
        $this->load->view('admin/knowledge_base/manage_groups', $data);
    }

    /* Add or edit existing article group */
    public function group($id = '')
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->post()) {
            $post_data        = $this->input->post();
            $article_add_edit = isset($post_data['article_add_edit']);
            if (isset($post_data['article_add_edit'])) {
                unset($post_data['article_add_edit']);
            }
            if (!$this->input->post('id')) {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }
                $id = $this->knowledge_base_model->add_group($post_data);
                if (!$article_add_edit && $id) {
                    set_alert('success', _l('added_successfully', _l('kb_dt_group_name')));
                } else {
                    echo json_encode([
                        'id'      => $id,
                        'success' => $id ? true : false,
                        'name'    => $post_data['name'],
                    ]);
                }
            } else {
                if (!has_permission('knowledge_base', '', 'edit')) {
                    access_denied('knowledge_base');
                }

                $id = $post_data['id'];
                unset($post_data['id']);
                $success = $this->knowledge_base_model->update_group($post_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('kb_dt_group_name')));
                }
            }
            die;
        }
    }

    /* Change group active or inactive */
    public function change_group_status($id, $status)
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->knowledge_base_model->change_group_status($id, $status);
            }
        }
    }

    public function update_groups_order()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $this->knowledge_base_model->update_groups_order();
            }
        }
    }

    /* Delete article group */
    public function delete_group($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        if (!$id) {
            redirect(admin_url('knowledge_base/manage_groups'));
        }
        $response = $this->knowledge_base_model->delete_group($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('danger', _l('is_referenced', _l('kb_dt_group_name')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('kb_dt_group_name')));
        } else {
            set_alert('warning', _l('problem_deleting', mb_strtolower(_l('kb_dt_group_name'))));
        }
        redirect(admin_url('knowledge_base/manage_groups'));
    }

    public function get_article_by_id_ajax($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->knowledge_base_model->get($id));
        }
    }
}
