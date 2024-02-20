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
                $staff = false;
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

                $issuedAt = time();
                $expirationTime = $issuedAt + 60 * 60 * 24 * 60;
                $data = array(
                    "login_token" => $access_token,
                    'iat' => $issuedAt,
                    'exp' => $expirationTime,
                );
                $jwt_token =  $this->generate_token($data);
                $response["jwt_token"] = !empty($jwt_token) ? $jwt_token : '';

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
                $this->update_data(db_prefix() . 'login_analytics', ["status" => 0, "expire" => 0], array("staffid" => $user->staffid));

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

    public function insert_data_batch($table, $data)
    {
        try {
            $response = [];
            $this->db->insert_batch($table, $data);
            if ($this->db->affected_rows() > 0) {
                $response = array(
                    "status" => 1,
                    "message" => "Data insert successfully."
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

    public function getdata($table, $where, $select = "*", $limit = "")
    {
        $response = [];
        try {
            $this->db->select($select);
            $this->db->where($where);
            if (!empty($limit) && is_integer($limit)) {
                $this->db->limit($limit);
            }
            $data = $this->db->get($table);
            if ($data->num_rows() > 0) {
                $response = array(
                    "status" => 1,
                    "message" => "Fetch Data successfully.",
                    "data" => $data->result_array()
                );
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
    public function update_call_data($call_data)
    {

        $response = [];
        try {

            $where = array(
                "staff_contact" => $call_data["staff_contact"],
                "staffid" => $call_data["staffid"],
                "calls_source" => $call_data["calls_source"],
                "calls_type" => $call_data["calls_type"],
                "call_start" => $call_data["call_start"],
                "call_end" => $call_data["call_end"],
                "status" => 1
            );


            $check_call_details =  $this->getdata(db_prefix() . "calls_activity_logs", $where, "id");
            if ($check_call_details["status"] == 1) {
                $response = array(
                    "status" => 1,
                    "message" => "Call activity already updated.",
                );
            } else {
                $call_activity = $this->insert_data(db_prefix() . 'calls_activity_logs', $call_data);
                if ($call_activity["status"] == 1) {
                    $response = array(
                        "status" => 1,
                        "message" => "Call activity update successfully.",
                    );
                } else {
                    $response = array(
                        "status" => 0,
                        "message" => "Call activity failed to update data.",
                    );
                }
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    public function update_call_data_bulk_temp($call_data)
    {
        $response = [];
        try {

            $call_activity_temp = $this->insert_data_batch(db_prefix() . 'calls_activity_temp_logs', $call_data);

            if ($call_activity_temp["status"] == 1) {
                $response = array(
                    "status" => 1,
                    "message" => "Call activity update successfully.",
                );
            } else {
                $response = array(
                    "status" => 0,
                    "message" => "Call activity failed to update data.",
                );
            }
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    public function update_call_activity()
    {
        $response = [];
        try {
            $get_all_activity_temp = $this->getdata(db_prefix() . 'calls_activity_temp_logs', array("id!=" => ""), "*", 10000);
            $delete_ids = [];
            if (!empty($get_all_activity_temp["data"])) {
                foreach ($get_all_activity_temp["data"] as $key => $call_data) {
                    $where = array(
                        "staff_contact" => $call_data["staff_contact"],
                        "contact" => $call_data["contact"],
                        "calls_source" => $call_data["calls_source"],
                        "calls_type" => $call_data["calls_type"],
                        "duration" => $call_data["duration"],
                        "call_start" => $call_data["call_start"],
                        "call_end" => $call_data["call_end"],
                        "status" => 1
                    );
                    $check_exisit = $this->getdata(db_prefix() . 'calls_activity_logs', $where, "*");
                    if ($check_exisit["status"] == 1) {
                        $delete_ids[] = $call_data["id"];
                    } else {
                        $staffid = "";
                        $staff_data =  $this->getdata(db_prefix() . "staff", array("phonenumber" => $call_data["staff_contact"], "active" => 1), "staffid");
                        if (!empty($staff_data["status"]) && $staff_data["status"] == 1) {
                            $staffid = !empty($staff_data["data"][0]["staffid"]) ? $staff_data["data"][0]["staffid"] : '';
                        }
                        if (!empty($staffid)) {
                            $delete_ids[] = $call_data["id"];
  if (!empty($call_data["staff_contact"]) && !empty($staffid)) {
                            $insert_data = array(
                                "staffid" => $staffid,
                                "staff_contact" => $call_data["staff_contact"],
                                "contact" => $call_data["contact"],
                                "call_status" => $call_data["call_status"],
                                "calls_source" => $call_data["calls_source"],
                                "calls_type" => $call_data["calls_type"],
                                "duration" => $call_data["duration"],
                                "call_start" => $call_data["call_start"],
                                "call_end" => $call_data["call_end"],
                                "datetime" => $call_data["datetime"],
                            );
                            $this->insert_data(db_prefix() . 'calls_activity_logs', $insert_data);
  }
                        }
                    }
                }

                if (!empty($delete_ids)) {

                    $sql = "DELETE FROM " . db_prefix() . "calls_activity_temp_logs WHERE id IN (" . implode(',', $delete_ids) . ")";
                    // Execute the query
                    $this->db->query($sql);
                }
            }

            $response = array(
                "status" => 1,
                "message" => "Call activity update successfully.",
            );
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
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
}
