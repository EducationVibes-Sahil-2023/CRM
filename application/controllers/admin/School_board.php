<?php

defined('BASEPATH') or exit('No direct script access allowed');

class School_board extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('board_model');
    }
    /* List all staff roles */
    public function index()
    {
        if (!has_permission('school_board', '', 'view')) {
            access_denied('School Board view');
            die;
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('school_board');
        }
        $data['title']                = "School Board";
        $this->load->view('admin/school_board', $data);
    }

    public function board_name()
    {
        // Permission check for creating
        if (!has_permission('school_board', '', 'create')) {
            access_denied('School Board create');
            die;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $message = '';
            $success = false;

            // Set your actual table name here
            $table = 'board'; // Example: Replace with your real table name

            $data['name'] = trim($data['name']);
            unset($data['type']);

            // Create new
            if (empty($data['board_id'])) {
                unset($data['board_id']);

                // Check for duplicate name
                $check_partner = $this->db->select("id")->where("name", $data["name"])->get($table)->row_array();
                if (!empty($check_partner)) {
                    echo json_encode([
                        'success' => false,
                        'message' => "School Board name already exists."
                    ]);
                    die;
                }

                // Insert new record
                $data["created_at"] = date('Y-m-d H:i:s');
                $data["created_by"] = get_staff_user_id();

                $id = $this->board_model->add($table, $data);

                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('School Board'));
                } else {
                    $message = "Failed to create School Board. Please try again.";
                }
            } else {
                // Permission check for editing
                if (!has_permission('school_board', '', 'edit')) {
                    access_denied('School Board edit');
                    die;
                }

                $id = $data['board_id'];
                unset($data['board_id']);

                // Check for duplicate name during update
                $check_partner = $this->db->select("id")
                    ->where("name", $data["name"])
                    ->where("id !=", $id)
                    ->get($table)
                    ->row_array();

                if (!empty($check_partner)) {
                    echo json_encode([
                        'success' => false,
                        'message' => "School Board name already exists."
                    ]);
                    die;
                }

                // Update record
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["updated_by"] = get_staff_user_id();

                $success = $this->board_model->update($table, $data, $id);

                $message = $success
                    ? _l('updated_successfully', "School Board updated successfully.")
                    : "Failed to update School Board. Please try again.";
            }

            echo json_encode([
                'success' => $success,
                'message' => $message
            ]);
            die;
        }
    }
}
