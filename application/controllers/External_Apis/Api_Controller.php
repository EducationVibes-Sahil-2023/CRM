<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api_Controller extends CI_Controller
{
    public $secretKey = '1234567890';
    public $staffId = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('External_Apis/validate');
        $this->load->model('External_Apis/Api_Model');

        $this->load->helper('jwt');
        $token = $this->input->get_request_header('Authorization');
        $current_url = current_url();

        if (str_contains($current_url, 'login') || str_contains($current_url, 'process_data') || str_contains($current_url, 'call_activity_cron') ||  str_contains($current_url, "update_all_contacts") ||  str_contains($current_url, "excel_sync") || str_contains($current_url, "applicant_sync_excel") || str_contains($current_url, "quotations") || str_contains($current_url, "payment_dues")) {
        } else {
            if (!empty($token)) {
                $token = explode(" ", $token);
                if (!empty($token[1])) {
                    $token = $token[1];
                }
            }

            $token_decode_data = $this->decode_token($token);

            if (gettype($token_decode_data) == "array")
                if (empty($token_decode_data["status"])) {
                    echo json_encode(array("status" => 0, "message" => $token_decode_data["message"]));
                    die;
                }
            // if (!empty($token_decode_data->expire_status) && $token_decode_data->expire_status == 1) {
            // } else {
            //     if (empty($token_decode_data->iat)) {
            //         echo json_encode(array("status" => 0, "message" => "Jwt token iat is missing"));
            //         die;
            //     }
            //     if (($token_decode_data->iat > (time() + 60)) || $token_decode_data->iat + 60 < time()) {
            //         echo json_encode(array("status" => 0, "message" => "Jwt token is expired"));
            //         die;
            //     }
            // }

            $login_token = !empty($token_decode_data->login_token) ? $token_decode_data->login_token : '';
            $getData = $this->Api_Model->getData(db_prefix() . 'login_analytics', array("token" => $login_token));

            if (!empty($getData["data"][0]["expire_status"]) && $getData["data"][0]["expire_status"] == 1) {
            } else {
                //                 if (empty($token_decode_data->iat)) {
                //                     echo json_encode(array("status" => 0, "message" => "Jwt token iat is missing"));
                //                     die;
                //                 }
                //                 if (($token_decode_data->iat > (time() + 60)) || $token_decode_data->iat + 60 < time()) {
                //                     echo json_encode(array("status" => 0, "message" => "Jwt token is expired"));
                //                     die;
                //                 }
            }
            if (!empty($getData["status"])) {
                $this->staffId = $getData["data"][0]["staffid"];
            } else {
                echo json_encode($getData);
                die;
            }
        }
    }

    public function decode_token($token)
    {
        $response = [];
        try {
            $jwt = new JWT();
            $response = $jwt->decode($token, $this->secretKey, "HS256");
        } catch (Exception $e) {
            $response = array("status" => 0, "message" => "Invalid/Expired Token");
        }
        return $response;
    }

    public function generate_token($data)
    {
        $response = [];
        try {
            $jwt = new JWT();
            $response = $jwt->encode($data, $this->secretKey, "HS256");
        } catch (Exception $e) {
            $response = array("status" => 0, "message" => "Not generate token.");
        }
        return $response;
    }

    function json_output($response)
    {
        return json_encode($response, true);
    }
}
