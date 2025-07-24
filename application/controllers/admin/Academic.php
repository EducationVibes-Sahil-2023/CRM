<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Academic extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('academic_model');
    }
    public function university()
    {

        if (!has_permission('academic', '', 'view')) {
            access_denied('Academic view');
            die;
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('university');
        }
        $data['title']                = "Universities";
        $data['type']                = "university";
        $this->load->view('admin/academic/university', $data);
    }

    public function courses()
    {
        if (!has_permission('academic', '', 'view')) {
            access_denied('Academic view');
            die;
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('courses');
        }
        $data['title']                = "University Courses";
        $this->load->view('admin/academic/courses', $data);
    }

    public function university_name()
    {
        if (!has_permission('academic', '', 'create')) {
            access_denied('Academic create');
            die;
        }

        if ($this->input->post()) {
            try {
                $data = $this->input->post();
                $table = db_prefix() . 'universities_name';
                $name = trim($data['name']);
                $success = false;
                $message = '';

                // ADD NEW UNIVERSITY
                if (empty($data['university_id'])) {
                    unset($data['university_id']);

                    $existing = $this->db->select("id")
                        ->where("name", $name)
                        ->get($table)
                        ->row_array();

                    if (!empty($existing)) {
                        echo json_encode([
                            'success' => false,
                            'message' => "University name already exists"
                        ]);
                        die;
                    }

                    $data['name'] = $name;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['created_by'] = get_staff_user_id();

                    $id = $this->academic_model->add($table, $data);

                    if ($id) {
                        $success = true;
                        $message = _l('added_successfully', 'University name');
                    }
                }
                // UPDATE EXISTING UNIVERSITY
                else {
                    $id = $data['university_id'];
                    unset($data['university_id']);

                    $existing = $this->db->select("id")
                        ->where("name", $name)
                        ->where("id !=", $id)
                        ->get($table)
                        ->row_array();

                    if (!empty($existing)) {
                        echo json_encode([
                            'success' => false,
                            'message' => "University name already exists"
                        ]);
                        die;
                    }

                    $data['name'] = $name;
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = get_staff_user_id();

                    $success = $this->academic_model->update($table, $data, $id);
                    if ($success) {
                        $message = _l('updated_successfully', 'University');
                    }
                }

                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
                die;
            } catch (Exception $e) {
                // Log the error (optional)
                log_message('error', 'University Save Error: ' . $e->getMessage());

                echo json_encode([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again later.'
                ]);
                die;
            }
        }
    }


    public function courses_name()
    {
        if (!has_permission('academic', '', 'create')) {
            access_denied('Academic create');
            die;
        }

        if ($this->input->post()) {
            try {
                $data = $this->input->post();
                $table = db_prefix() . '_courses';
                $name = trim($data['name']);
                unset($data['name']);
                $success = false;
                $message = '';

                // ADD NEW UNIVERSITY
                if (empty($data['courses_id'])) {
                    unset($data['courses_id']);

                    $existing = $this->db->select("id")
                        ->where("course_name", $name)
                        ->get($table)
                        ->row_array();

                    if (!empty($existing)) {
                        echo json_encode([
                            'success' => false,
                            'message' => "Course name already exists"
                        ]);
                        die;
                    }

                    $data['course_name'] = $name;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['created_by'] = get_staff_user_id();

                    $id = $this->academic_model->add($table, $data);

                    if ($id) {
                        $success = true;
                        $message = _l('added_successfully', 'Course name');
                    }
                }
                // UPDATE EXISTING UNIVERSITY
                else {
                    $id = $data['courses_id'];
                    unset($data['courses_id']);

                    $existing = $this->db->select("id")
                        ->where("course_name", $name)
                        ->where("id !=", $id)
                        ->get($table)
                        ->row_array();

                    if (!empty($existing)) {
                        echo json_encode([
                            'success' => false,
                            'message' => "Course name already exists"
                        ]);
                        die;
                    }

                    $data['course_name'] = $name;
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = get_staff_user_id();

                    $success = $this->academic_model->update($table, $data, $id);
                    if ($success) {
                        $message = _l('updated_successfully', 'Course');
                    }
                }

                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
                die;
            } catch (Exception $e) {
                // Log the error (optional)
                log_message('error', 'Course Save Error: ' . $e->getMessage());

                echo json_encode([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again later.'
                ]);
                die;
            }
        }
    }
}
