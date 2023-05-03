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
            "condition" => "required|email|{exist:{tblstaff:email:active:1}}"
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
            "condition" => "required|staffid|{exist:{tblstaff:staffid:active:1}}"
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
}
