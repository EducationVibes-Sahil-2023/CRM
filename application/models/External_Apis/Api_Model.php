<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->db->reconnect();

        $timezone = get_option('default_timezone');

        if ($timezone != '') {
            date_default_timezone_set($timezone);
        }
    }

    public function login($email, $password)
    {
        try {
            $response = [];
            if ((!empty($email)) and (!empty($password))) {
                $table = db_prefix() . 'staff';
                $this->db->where('email', $email);
                $user = $this->db->get($table)->row();
                if ($user) {
                    // Email is okey lets check the password now
                    if (!app_hasher()->CheckPassword($password, $user->password)) {
                        hooks()->do_action('failed_login_attempt', [
                            'user'            => $user,
                            'is_staff_member' => $staff,
                        ]);

                        log_activity('Failed Login Attempt [Email: ' . $email . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ', IP: ' . $this->input->ip_address() . ']');


                        return  $response = array(
                            "status" => 0,
                            "message" => "Incorrect password."
                        );
                    }
                } else {

                    hooks()->do_action('non_existent_user_login_attempt', [
                        'email'           => $email,
                        'is_staff_member' => $staff,
                    ]);

                    log_activity('Non Existing User Tried to Login [Email: ' . $email . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ', IP: ' . $this->input->ip_address() . ']');

                    return  $response = array(
                        "status" => 0,
                        "message" => "Non Existing User."
                    );
                }

                if ($user->active == 0) {
                    hooks()->do_action('inactive_user_login_attempt', [
                        'user'            => $user,
                        'is_staff_member' => $staff,
                    ]);
                    log_activity('Inactive User Tried to Login [Email: ' . $email . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ', IP: ' . $this->input->ip_address() . ']');

                    return  $response = array(
                        "status" => 0,
                        "message" => "User is Inactive."
                    );
                }

                $access_token = bin2hex(random_bytes(16));
                $response["user_data"] = $user;
                $response["status"] = 1;
                $response["message"] = "User login successfully.";
                $response["login_token"] = $access_token;
                $response["followup_contact"] = [];

                if (!empty($user->staffid)) {
                    $follow_up_contact = $this->follow_up_contact($user->staffid);
                    if (!empty($follow_up_contact["data"])) {
                        $response["followup_contact"] = $follow_up_contact["data"];
                    }
                }


                // insert login details
                $insert_data = array(
                    "staffid" => $user->staffid,
                    "token" => $access_token,
                    "status" => 1,
                    "login_datetime" => date('Y-m-d H:i:s')
                );
                $this->update_data(db_prefix() . 'login_analytics', ["status" => 0], array("staffid" => $user->staffid));

                $login_details = $this->insert_data(db_prefix() . 'login_analytics', $insert_data);
                if (!empty($login_details["status"]) && $login_details["status"] == 0) {
                    $response["message"] = $response["message"] . " .But Login analytics not save.";
                }
            } else {
                $response = array(
                    "status" => 0,
                    "message" => "Invalid user."
                );
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }

        return $response;
    }

    public function insert_data($table, $data)
    {
        try {
            $response = [];
            $this->db->insert($table, $data);
            if ($this->db->affected_rows() > 0) {
                $response = array(
                    "status" => 1,
                    "message" => "Data insert successfully.",
                    "id" => $this->db->insert_id()
                );
            } else {
                $response = array(
                    "status" => 0,
                    "message" => "Failed to insert data"
                );
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    public function update_data($table, $data, $where)
    {
        try {
            $response = [];
            $this->db->where($where);
            $this->db->update($table, $data);
            if ($this->db->affected_rows() > 0) {
                $response = array(
                    "status" => 1,
                    "message" => "Data update successfully.",
                    "id" => $this->db->insert_id()
                );
            } else {
                $response = array(
                    "status" => 0,
                    "message" => "Failed to update data"
                );
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    public function getdata($table, $where)
    {
        $response = [];
        try {
            $this->db->where($where);
            $data = $this->db->get($table);
            if ($data->num_rows() > 0) {

                $data = $data->row_array();
                if (!empty($data["status"])) {
                    $response = array(
                        "status" => 1,
                        "message" => "Fetch Data successfully.",
                        "data" => $data
                    );
                } else {
                    $response = array(
                        "status" => 0,
                        "message" => "Login token expire.",
                    );
                }
            } else {
                $response = array(
                    "status" => 0,
                    "message" => "Failed to fetch data"
                );
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    public function follow_up_contact($staffId)
    {
        $response = [];
        try {
            $current_date = date('Y-m-d');
            $sql = "SELECT l.name,l.phonenumber,l.email,r.date FROM " . db_prefix() . "leads l inner join " . db_prefix() . "reminders r on (l.id = r.rel_id and DATE_FORMAT(r.date,'%Y-%m-%d')='{$current_date}') where l.assigned = {$staffId}  group by phonenumber order by r.date asc";
            $result = $this->db->query($sql);
            if ($result->num_rows() > 0) {
                $response = array(
                    "status" => 1,
                    "message" => "Today follow up contacts.",
                    "data" => $result->result_array()
                );
            } else {
                $response = array(
                    "status" => 1,
                    "message" => "No contact",
                    "data" => $result->result_array()
                );
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }
}
