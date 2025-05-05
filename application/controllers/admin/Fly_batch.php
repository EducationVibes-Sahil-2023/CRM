<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fly_batch extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fly_model');
    }

    /* List all taxes */
    public function index($id = "")
    {
        if ($this->input->is_ajax_request()) {

            if (!empty($id)) {
                $_POST["client_id"] = $id;
                $_POST["manually"] = 1;
            }
            $this->app->get_table_data('fly_batch');
            die;
        }
        $data['title'] = "Fly Ticket Batch";
        $this->load->view('admin/fly_ticket/manage', $data);
    }


    public function create($id = "")
    {
        if (!has_permission('fly_batch', '', 'create')) {
            access_denied('Fly Ticket Batch Create');
            die;
        }
        $data['title'] = "Fly Ticket Create";
        $data['batch_data'] = [];
        if (!empty($id)) {
            $result = $this->fly_model->get_ticket_batch($id);
            if (!empty($result)) {
                $data['batch_data'] = $result;
            }
        }
        $data['country_list'] = get_country_list(7);
        $data['vendor_list'] = get_vendor_list(3);
        $data['payment_mode'] = get_payment_mode();
        $data['departure_location'] = get_departure_list();
        $data['university_list'] = get_university_list("MBBS Abroad");



        $this->load->view('admin/fly_ticket/create', $data);
    }


    public function client_list()
    {
        // Fetch university name from POST request securely
        $university_names = $this->input->post("university_name", true); // array or string
 $batch_id = $this->input->post("$batch_id", true); // array or string
        
        // Ensure it's treated as an array
        if (!is_array($university_names)) {
            $university_names = [$university_names];
        }

        // Validate input
        if (empty($university_names)) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => "University name is required.",
            ]);
            return;
        }

        // Get client list for all universities
        $get_client_list = get_client_list_fly_batch($university_names,$batch_id); // Adjust this function if needed

        // Initialize response data
        $data = [];

        if (!empty($get_client_list)) {
            $data['resp_code'] = 'RCS';
            $data['resp_desc'] = "Client list retrieved successfully.";
            $data['client_list'] = $get_client_list;
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = "No client list found for selected universities.";
        }

        // Return JSON response
        echo json_encode($data);
    }

    public function create_batch()
    {
        try {
            $client_list = !empty($_POST["client_list"]) ? json_decode($_POST["client_list"], true) : [];
            $batch_id = $this->input->post("batch_id", true);
            $id = $this->input->post("id", true);
            $batch_name = $this->input->post("batch_name", true);
            $country_ids = $this->input->post("country_ids", true);
            $country_name = $this->input->post("country_name", true);
            $university_ids = $this->input->post("university_ids", true);
            $university_name = $this->input->post("university_name", true);
            $vendor_id = $this->input->post("vendor_name", true);
            $ticket_cost = $this->input->post("ticket_cost", true);
            $payment_date = $this->input->post("payment_date", true);
            $payment_mode = $this->input->post("payment_mode", true);
            $fly_date = $this->input->post("fly_date", true);
            $departure_location = $this->input->post("departure_location", true);
            $manually = $this->input->post("manually", true);

            check_invitation_letter($client_list);
            // If manually is 1, process only ticket creation
            if ((int)$manually === 1) {
                $postData_Ticket = [
                    "id"         => $id,
                    "client_ids"         => $client_list,
                    "vendor_id"          => $vendor_id,
                    "ticket_cost"        => $ticket_cost,
                    "payment_date"       => $payment_date,
                    "payment_mode"       => $payment_mode,
                    "fly_date"           => $fly_date,
                    "departure_location" => $departure_location,
                    "ticket_status" => 1,
                ];

                $ticket_result = $this->fly_model->insert_client_ticket($postData_Ticket, 0);

                if ($ticket_result["status"] === true) {
                    echo json_encode([
                        'resp_code' => 'RCS',
                        'resp_desc' => "Manual Fly Ticket created successfully.",
                    ]);
                } else {
                    echo json_encode([
                        'resp_code' => 'ERR',
                        'resp_desc' => !empty($ticket_result['message']) ? $ticket_result['message'] : "Manual ticket creation failed.",
                    ]);
                }
                return;
            }

            // Validate required inputs
            if (empty($country_name) || empty($batch_name) || empty($client_list)) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => "Missing required fields",
                ]);
                return;
            }

            // Check for duplicate batch name (excluding current if editing)
            $where = ['name' => trim($batch_name)];
            if (!empty($batch_id)) {
                $where["id !="] = $batch_id;
            }

            $check_exist = $this->fly_model->check_batch($where);

            if ($check_exist) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => "Fly Batch already exists",
                ]);
                return;
            }

            // Prepare batch data
            $postData = [
                'id'              => !empty($batch_id) ? (int)$batch_id : null,
                'name'            => trim($batch_name),
                'country_ids'     => trim($country_ids),
                'country_name'    => trim($country_name),
                'university_ids'  => $university_ids,
                'university_name' => $university_name,
            ];

            if (empty($postData['id'])) {
                $postData['status']     = 1;
                $postData['created_by'] = get_staff_user_id();
                $postData['created_at'] = date('Y-m-d H:i:s');
            } else {
                $postData['updated_by'] = get_staff_user_id();
                $postData['updated_at'] = date('Y-m-d H:i:s');
            }

            // Insert/update batch
            $insert_result = $this->fly_model->insert_update($postData);


            $final_batch_id = !empty($insert_result["id"]) ? $insert_result["id"] : 0;

            // Proceed with ticket assignment
            $postData_Ticket = [
                "batch_id"         => $final_batch_id,
                "client_ids"       => $client_list,
                "country_name"     => $country_name,
                "vendor_id"        => $vendor_id,
                "ticket_cost"      => $ticket_cost,
                "payment_date"     => $payment_date,
                "payment_mode"     => $payment_mode,
                "fly_date"         => $fly_date,
                "departure_location" => $departure_location,
            ];

            $ticket_result = $this->fly_model->insert_client_ticket($postData_Ticket, 1);
            if ($ticket_result["status"] === true) {
                $message = empty($batch_id) ? "Fly Ticket Batch created successfully." : "Fly Ticket Batch updated successfully.";
                // set_alert('success', $message);
                echo json_encode([
                    'resp_code' => 'RCS',
                    'resp_desc' => $ticket_result,
                ]);
            } else {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => $ticket_result["message"]?$ticket_result["message"]:"Client-ticket mapping failed.",
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Server error: ' . $e->getMessage(),
            ]);
        }
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
