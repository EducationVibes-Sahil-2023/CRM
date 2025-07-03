<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Exam_batch extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('exam_model');
    }

    /* List all taxes */
    public function index()
    {
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('entrance_exam');
        }
        $data['title'] = "Exam Batch";
        $this->load->view('admin/exam/manage', $data);
    }


    public function create($id = "")
    {
        if (!has_permission('exam_batch', '', 'create')) {
            access_denied('Exam Batch Create');
            die;
        }
        $data['title'] = "Batch Create";
        $data['batch_data'] = [];
        if (!empty($id)) {
            $result = $this->exam_model->get_exam_batch($id);
            if(!empty($result))
            {
                $data['batch_data'] = $result;
            }
        }
        $data['university_list'] = get_university_list("MBBS Abroad");
        $data['exams'] = get_university_exam();

        
        $this->load->view('admin/exam/create', $data);
    }


    public function client_list()
    {
        // Fetch university name from POST request securely
        $university_name = $this->input->post("university_name", true);

        // Validate input
        if (empty($university_name)) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => "University name is required.",
            ]);
            return;
        }

        // Get client list
        $get_client_list = get_client_list($university_name);

        // Initialize response data
        $data = [];

        // Check if data is retrieved
        if (!empty($get_client_list)) {
            $data['resp_code'] = 'RCS';
            $data['resp_desc'] = "Client list retrieved successfully.";
            $data['client_list'] = $get_client_list;
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = "No client list found for university: $university_name";
        }

        // Return JSON response
        echo json_encode($data);
    }

    public function create_batch()
    {


        try {
            // Decode client list JSON safely
            $client_list = !empty($_POST["client_list"]) ? json_decode($_POST["client_list"], true) : [];

            // Validate required fields
            if (empty($_POST["university_name"]) || empty($_POST["exam_name"]) || empty($_POST["batch_name"]) || empty($client_list)) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = "Missing required fields";
                echo json_encode($data);
                die;
            }
          
   
     $where = array(
    "university_name" => trim($_POST["university_name"] ?? ''),
    "exam_id" => trim($_POST["exam_name"] ?? ''),
    "name" => trim($_POST["batch_name"] ?? ''),
    "exam_date" => !empty($_POST["batch_date"]) ? trim($_POST["batch_date"]) : '0000-00-00'
);

// Add "id!=" condition if "id" is provided
if (!empty($_POST["batch_id"])) {
    $where["id !="] = trim($_POST["batch_id"]);
}





$check_exist = $this->exam_model->check_batch($where);


if ($check_exist) {
    $data['resp_code'] = 'ERR';
            $data['resp_desc'] = "Batch Already Exist";
                        echo json_encode($data);
                        die;
}
   
    

// Continue with further execution if the record does not exist

            // Prepare data
            $postData = [
                'id'              => !empty($_POST["batch_id"]) ? intval($_POST["batch_id"]) : 0,
                'university_name' => trim($_POST["university_name"]),
                'exam_id'         => trim($_POST["exam_name"]),
                'name'            => trim($_POST["batch_name"]),
                'exam_date'       => trim($_POST["batch_date"]),
                'student_count'   => !empty($client_list) ? count($client_list) : 0
            ];
            $postData_Exam["client_ids"] = $client_list;
            $postData_Exam["exam_id"] = trim($_POST["exam_name"]);
            $postData_Exam["batch_id"] = trim($_POST["batch_id"]);
            $postData_Exam["exam_date"] = trim($_POST["batch_date"]);
            $postData_Exam["m_university_name"] = trim($_POST["university_name"]);
            // Set timestamps and user info
            if (empty($postData['id'])) {
                $postData['created_by'] = get_staff_user_id();
                $postData['created_at'] = date('Y-m-d H:i:s');
            } else {
                $postData['updated_by'] = get_staff_user_id();
                $postData['updated_at'] = date('Y-m-d H:i:s');
            }

            // Insert or update batch using model
            $rows_affected = $this->exam_model->insert_update($postData);
            $postData_Exam["batch_id"] = !empty($rows_affected["id"]) ? $rows_affected["id"] : "";

            if ($rows_affected["status"] == true) {
                $rows_affected = $this->exam_model->insert_client_exams($postData_Exam);
                if ($rows_affected["status"] == true) {
                    if (!empty($_POST["batch_id"])) {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = "Batch Exam Update successfully.";
                        set_alert('success', "Batch Exam Update successfully.");
                    } else {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = "Batch Exam Create successfully.";
                        set_alert('success', "Batch Exam Create successfully.");
                    }
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Batch Exam Not Create successfully.";
                    set_alert('success', "Batch Exam Not Create successfully.");
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = $rows_affected["message"];
                set_alert('danger', "Batch Exam Update failed");
            }
        } catch (Exception $e) {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }



    /* Add or edit tax / ajax */
    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['taxid'] == '') {
                $success = $this->taxes_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('tax'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->taxes_model->edit($data);
                $message = '';
                if (is_array($success) && isset($success['tax_is_using_expenses'])) {
                    $success = false;
                    $message = _l('tax_is_used_in_expenses_warning');
                } elseif ($success == true) {
                    $message = _l('updated_successfully', _l('tax'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }



    /* Delete tax from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('taxes'));
        }
        $response = $this->taxes_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('tax_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('tax')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('tax_lowercase')));
        }
        redirect(admin_url('taxes'));
    }
}
