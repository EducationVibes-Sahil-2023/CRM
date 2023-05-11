<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'controllers/External_Apis/Api_Controller.php';

class Login_Controller extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Your constructor code goes here
        // For example, you could load any required models or libraries
    }

    public function login()
    {
        $email = !empty($_POST["email"]) ? trim($_POST["email"]) : '';
        $password = !empty($_POST["password"]) ? trim($_POST["password"]) : '';
        $remember = !empty($_POST["remember"]) ? trim($_POST["remember"]) : '';
        $response = [];
        $rules[] = array(
            "field" => "Email",
            "value" => $email,
            "condition" => "required|email|{exist:{" . db_prefix() . "staff:email:active:1}}"
        );
        $rules[] = array(
            "field" => "Password",
            "value" => $password,
            "condition" => "required"
        );

        $validate = $this->validate->validation($rules);
        if ($validate === true) {
            $this->load->model("Authentication_model");
            $response = $this->Api_Model->login($email, $password);
        } else {
            $response[] = array(
                "status" => 0,
                "message" => $validate[0],
            );
        }
        echo  $this->json_output($response);
    }
    public function follow_up_data()
    {
        $staffId = $this->staffId;
        $response = [];
        $rules[] = array(
            "field" => "Staff Id",
            "value" => $staffId,
            "condition" => "required|{exist:{" . db_prefix() . "staff:staffid:active:1}}"
        );
        $validate = $this->validate->validation($rules);
        if ($validate === true) {
            $response = $this->Api_Model->follow_up_contact($this->staffId);
        } else {
            $response[] = array(
                "status" => 0,
                "message" => $validate[0],
            );
        }
        echo  $this->json_output($response);
    }

    public function call_update()
    {
        $form_data = !empty($_POST["call_data"]) ? json_decode($_POST["call_data"], true) : '';
        $form_data_array = [];
        $form_data_array_temp = [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response[] = array(
                "status" => 0,
                "message" => 'Error decoding JSON: ' . json_last_error_msg()
            );
            echo  $this->json_output($response);
            die;
        }
        $response = [];
        $rules[] = array(
            "field" => "Staff Id",
            "value" => !empty($this->staffId) ? $this->staffId : '',
            "condition" => "required|{exist:{" . db_prefix() . "staff:staffid:active:1}}"
        );

        $rules[] = array(
            "field" => "Call Data",
            "value" => json_encode($form_data, true),
            "condition" => "required"
        );
        if (!empty($form_data["type"]) && $form_data["type"] == 2) {
            foreach ($form_data["formData"] as $form_d) {
                $type =  2;
                $callassignee =  !empty($form_d["callassignee"]) ? $form_d["callassignee"] : '';
                $phonenumber =  !empty($form_d["phonenumber"]) ? $form_d["phonenumber"] : '';
                $call_status =  !empty($form_d["form-cf-13"]) ? $form_d["form-cf-13"] : 'Not Found';
                $calls_type =  !empty($form_d["calls_type"]) ? $form_d["calls_type"] : '';
                $call_duration =  !empty($form_d["call_duration"]) ? $form_d["call_duration"] : '';
                $call_start =  !empty($form_d["startdate_time"]) ? strtotime($form_d["startdate_time"]) : '';
                $call_end =  !empty($form_d["enddate_time"]) ? strtotime($form_d["enddate_time"]) : '';

                array_push($form_data_array_temp, array(
                    "staff_contact" => $callassignee,
                    "contact" => $phonenumber,
                    "call_status" => $call_status,
                    "calls_source" => $type,
                    "calls_type" => $calls_type,
                    "duration" => $call_duration,
                    "call_start" => $call_start,
                    "call_end" => $call_end,
                    "datetime" => date('Y-m-d H:i:s')
                ));
            }
        } else {

            $staffid = "";
            $callassignee =  !empty($form_data["formData"]["callassignee"]) ? $form_data["formData"]["callassignee"] : '';
            $phonenumber =  !empty($form_data["formData"]["phonenumber"]) ? $form_data["formData"]["phonenumber"] : '';
            $call_start =  !empty($form_data["formData"]["startdate_time"]) ? strtotime($form_data["formData"]["startdate_time"]) : '';
            $call_end =  !empty($form_data["formData"]["enddate_time"]) ? strtotime($form_data["formData"]["enddate_time"]) : '';
            $call_status =  !empty($form_data["formData"]["form-cf-13"]) ? $form_data["formData"]["form-cf-13"] : 'Not Found';
            $type =  1;
            $calls_type =  1;
            $call_duration =  !empty($form_data["formData"]["call_duration"]) ? $form_data["formData"]["call_duration"] : '';
            if (!empty($callassignee)) {
                // die;
                $staff_data =  $this->Api_Model->getdata(db_prefix() . "staff", array("phonenumber" => $callassignee, "active" => 1), "staffid");
                if (!empty($staff_data["status"]) && $staff_data["status"] == 1) {
                    $staffid = !empty($staff_data["data"][0]["staffid"]) ? $staff_data["data"][0]["staffid"] : '';
                }
            }


            $form_data_array = array(
                "staffid" => $staffid,
                "staff_contact" => $callassignee,
                "contact" => $phonenumber,
                "call_status" => $call_status,
                "calls_source" => $type,
                "calls_type" => $calls_type,
                "duration" => $call_duration,
                "call_start" => $call_start,
                "call_end" => $call_end,
                "datetime" => date('Y-m-d H:i:s')
            );

            $rules[] = array(
                "field" => "Assignee Contact Number",
                "value" => $callassignee,
                "condition" => "required|{exist:{" . db_prefix() . "staff:phonenumber:active:1}}"
            );
            $rules[] = array(
                "field" => "Staff Id",
                "value" => $staffid,
                "condition" => "required|{exist:{" . db_prefix() . "staff:staffid:active:1}}"
            );
            $rules[] = array(
                "field" => "Call Source",
                "value" => $type,
                "condition" => "required|{exist:{" . db_prefix() . "calls_source:id}}"
            );
            $rules[] = array(
                "field" => "Call Type",
                "value" => $calls_type,
                "condition" => "required|{exist:{" . db_prefix() . "calls_type:id}}"
            );
            $rules[] = array(
                "field" => "Call Duration",
                "value" => $call_duration,
                "condition" => ""
            );
            $rules[] = array(
                "field" => "Call Status",
                "value" => $call_status,
                "condition" => "required"
            );
        }



        $validate = $this->validate->validation($rules);
        if ($validate === true) {
            if (!empty($form_data["type"]) && $form_data["type"] == 1) {
                $response = $this->Api_Model->update_call_data($form_data_array);
            }
            if (!empty($form_data["type"]) && $form_data["type"] == 2) {
                $response = $this->Api_Model->update_call_data_bulk_temp($form_data_array_temp);
            }
        } else {
            $response[] = array(
                "status" => 0,
                "message" => $validate[0],
            );
        }
        echo  $this->json_output($response);
    }

    public function call_activity_cron()
    {
        $response = $this->Api_Model->update_call_activity();
        echo  $this->json_output($response);
    }
}
