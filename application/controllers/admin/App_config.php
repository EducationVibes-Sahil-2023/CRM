<?php

defined('BASEPATH') or exit('No direct script access allowed');

class App_config extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /* List all email templates */
    public function index()
    {

        if ($this->input->is_ajax_request()) {

            $get_client_config_data =  get_client_config_data();
            $table_response = [];
            if (!empty($get_client_config_data)) {
                $this->load->model('leads_model');

                $staff_list              = $this->leads_model->get_staff_list();
                $staff_list = array_column($staff_list, null, "staffid");

                foreach ($get_client_config_data as $data) {
                    $row = [];
                    $row[] = htmlspecialchars($data["company_name"]);
                    $row[] = "<img src='" . htmlspecialchars($data["logo"]) . "' alt='Logo' width='50' height='50'>";
                    $row[] = htmlspecialchars($data["version"]);
                    $row[] = htmlspecialchars($data["created_date"]);
                    $row[] = htmlspecialchars($staff_list[$data["created_by"]]["firstname"]) . " " . htmlspecialchars($staff_list[$data["created_by"]]["lastname"]);
                    $row[] = "<i class='fa fa-edit' onclick=\"edit_client_app_config('" . htmlspecialchars($data['id']) . "', '" . htmlspecialchars($data['company_name']) . "', '" . htmlspecialchars($data['base_url']) . "')\"></i>";
                    $table_response["aaData"][] = $row;
                }
            } else {
                $table_response["aaData"] = [];
            }
            $table_response["draw"] = 1;
            $table_response["iTotalDisplayRecords"] = count($get_client_config_data);
            $table_response["iTotalRecords"] = count($get_client_config_data);
            echo json_encode($table_response);
            die;
        }

        $data['title'] = _l('app_config');
        $this->load->view('admin/app/index', $data);
    }

    public function client_update()
    {
        $response = array(
            "status" => 0,
            "message" => "Wrong Request Parameter not found"
        );

        try {
            if (empty($_POST)) {
                throw new Exception("No data provided in the request.");
            }

            $data = [];
            // Handle file upload
            if (!empty($_FILES["logo"]['name'])) {
                $upload_data = $_FILES["logo"];

                if ($upload_data["error"] !== UPLOAD_ERR_OK) {
                    throw new Exception("Upload failed with error code: " . $upload_data["error"]);
                }

                $filename = uniqid() . '_' . basename($upload_data['name']);
                $destination = 'uploads' . '/' . $filename;

                if (!move_uploaded_file($upload_data['tmp_name'], $destination)) {
                    throw new Exception("Failed to upload media file.");
                    $response = array(
                        "status" => 0,
                        "message" => "Failed to upload media file.",
                    );
                    echo json_encode($response);
                    die;
                }

                $media_url = base_url() . $destination;
                $data['logo'] = $media_url;
            }


            // Handle other POST data
            if (empty($_POST["base_url"])) {
                throw new Exception("Base URL is required.");
            }

            $data["company_name"] = $_POST["company_name"];
            $data["base_url"] = $_POST["base_url"];

            // Check if ID is set for update
            if (!empty($_POST["id"])) {
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["updated_by"] = get_staff_user_id();
                $result_data = $this->db
                    ->select("*")
                    ->where("id", $_POST["id"])
                    ->get(db_prefix() . 'client_app_config')
                    ->row();

     
                $current_version = $result_data->version;
                if (strpos($current_version, '.') !== false) {
                    list($major, $minor) = explode('.', $current_version);
                    $minor = ($minor + 1) % 10;
                    $major = ($minor == 0) ? $major + 1 : $major;
                    $data["version"] = "$major.$minor";
                } else {
                    $data["version"] = $current_version + 0.1;
                }
            } else {
                $data["created_date"] = date('Y-m-d H:i:s');
                $data["created_by"] = get_staff_user_id();
                $data["version"] = 1;
            }

            // Process the data saving logic here
            // Example: $this->db->update('your_table', $data, ['id' => $_POST["id"]]);
            if (!empty($_POST["id"])) {
                $this->db->update(db_prefix() . 'client_app_config', $data, ['id' => $_POST["id"]]);
            } else {
                $this->db->insert(db_prefix() . 'client_app_config', $data);
            }

            $response = array(
                "status" => 1,
                "message" => "Client updated successfully.",
                "data" => $data
            );
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            set_alert('danger', $response['message']);
        }

        echo json_encode($response);
        die;
    }
}
