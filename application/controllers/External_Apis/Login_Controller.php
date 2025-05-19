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
        $start_time = microtime(true); // Start timer

        $response = [];
        if (!empty($_POST["call_data"])) {
            $staff_data_ =   $this->Api_Model->getdata(db_prefix() . "staff", array("staffid" => $this->staffId));

            $form_data = !empty($_POST["call_data"]) ? json_decode($_POST["call_data"], true) : '';
            $form_data_array = [];
            $form_data_array_temp = [];
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response = array(
                    "status" => 0,
                    "message" => 'Error decoding JSON: ' . json_last_error_msg()
                );
                echo  $this->json_output($response);
                die;
            }

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
                    // $callassignee =  !empty($form_d["callassignee"]) ? $form_d["callassignee"] : '';

                    $callassignee = !empty($staff_data_["data"][0]["phonenumber"]) ?
                        $staff_data_["data"][0]["phonenumber"] : (!empty($form_d["callassignee"]) ?
                            $form_d["callassignee"] :
                            ''
                        );


                    $phonenumber =  !empty($form_d["phonenumber"]) ? $form_d["phonenumber"] : '';
                    $call_status =  !empty($form_d["form-cf-13"]) ? $form_d["form-cf-13"] : 'Not Found';
                    $calls_type =  !empty($form_d["calls_type"]) ? $form_d["calls_type"] : '';
                    $call_duration =  !empty($form_d["call_duration"]) ? $form_d["call_duration"] : '';
                    $call_start =  !empty($form_d["startdate_time"]) ? strtotime($form_d["startdate_time"]) : '';
                    $call_end =  !empty($form_d["enddate_time"]) ? strtotime($form_d["enddate_time"]) : '';

                    // Validate $call_start
                    if (!is_numeric($call_start)) {
                        // throw new InvalidArgumentException('Invalid UNIX timestamp for call_start');
                    }

                    // Adjust the timestamp by 5 hours and 30 minutes
                    $adjusted_time = intval($call_start) + (5 * 3600) + (30 * 60);

                    // Format the adjusted time to 'Y-m-d'
                    $adjusted_date = date('Y-m-d', $adjusted_time);

                    // Compare with the current date
                    // if ($adjusted_date == date('Y-m-d')) {

                    $phonenumber = !empty($phonenumber) ? substr(trim($phonenumber), -10) : '';
                    $phonenumber = str_replace("+91", "", $phonenumber);
                    array_push($form_data_array_temp, array(
                        "staffid" => !empty($this->staffId) ? intval($this->staffId) : '',
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
                    // }
                }
            } else {

                $staffid = "";
                $callassignee =  !empty($form_data["formData"]["callassignee"]) ? $form_data["formData"]["callassignee"] : '';
                $staff_data_ =   $this->Api_Model->getdata(db_prefix() . "staff", array("phonenumber" => $form_data["formData"]["callassignee"]));

                if (!empty($staff_data_["data"][0]["phonenumber"])) {
                    $callassignee = !empty($staff_data_["data"][0]["phonenumber"]) ? $staff_data_["data"][0]["phonenumber"] : $form_data["formData"]["callassignee"];
                } else {
                    $staff_data_ =   $this->Api_Model->getdata(db_prefix() . "staff", array("alternate_number" => $form_data["formData"]["callassignee"]));

                    if (!empty($staff_data_["data"][0]["phonenumber"])) {
                        $callassignee = !empty($staff_data_["data"][0]["phonenumber"]) ? $staff_data_["data"][0]["phonenumber"] : $form_data["formData"]["callassignee"];
                    }
                }

                // $callassignee =  !empty($form_data["formData"]["callassignee"]) ? $form_data["formData"]["callassignee"] : '';
                // $callassignee = !empty($staff_data["data"][0]["phonenumber"]) ?
                //     $staff_data["data"][0]["phonenumber"] : (!empty($form_data["formData"]["callassignee"]) ?
                //         $form_data["formData"]["callassignee"] :
                //         ''
                //     );
                $phonenumber =  !empty($form_data["formData"]["phonenumber"]) ? $form_data["formData"]["phonenumber"] : '';
                $call_start =  !empty($form_data["formData"]["startdate_time"]) ? strtotime($form_data["formData"]["startdate_time"]) : '';
                $call_end =  !empty($form_data["formData"]["enddate_time"]) ? strtotime($form_data["formData"]["enddate_time"]) : '';
                $call_status =  !empty($form_data["formData"]["form-cf-13"]) ? $form_data["formData"]["form-cf-13"] : 'Not Found';
                $type =  1;
                $calls_type =  1;
                $call_duration =  !empty($form_data["formData"]["call_duration"]) ? $form_data["formData"]["call_duration"] : '';

                if (!empty($callassignee)) {
                    // die;
                    // $staff_data =  $this->Api_Model->getdata(db_prefix() . "staff", array("phonenumber" => $callassignee, "active" => 1), "staffid");


                    if (!empty($staff_data_["data"][0]["staffid"])) {
                        $staffid = !empty($staff_data_["data"][0]["staffid"]) ? $staff_data_["data"][0]["staffid"] : '';
                    }
                }
                $phonenumber = !empty($phonenumber) ? substr(trim($phonenumber), -10) : '';
                $phonenumber = str_replace("+91", "", $phonenumber);

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
                    "condition" => ""
                );
            }

            $validate = $this->validate->validation($rules);

            if ($validate === true) {
                if (!empty($form_data["type"]) && $form_data["type"] == 1) {
                    $response = $this->Api_Model->update_call_data($form_data_array);
                } else if (!empty($form_data["type"]) && $form_data["type"] == 2) {
                    if (!empty($form_data_array_temp)) {
                        $this->load->model('Leads_model');
                        $response = $this->Api_Model->update_call_data_bulk_temp($form_data_array_temp);
                        $this->Leads_model->hitCronUrlAsync(base_url("external/call_activity_cron"));
                    } else {
                        $response = array(
                            "status" => 1,
                            "message" => "Call data update successfully.",
                        );
                    }
                } else {
                    $response = array(
                        "status" => 1,
                        "message" => "Call data update successfully.",
                    );
                }

                // End time measurement
                $end_time = microtime(true);
                $execution_time = round($end_time - $start_time, 4); // seconds with 4 decimal places

                // Prepare log content
                $log_data = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'execution_time' => $execution_time . ' sec',
                    'updated_rows' => !empty($form_data_array_temp) ? count($form_data_array_temp) : 1,
                    'message' => "Call Sync",
                ];

                // Log file path
                $log_file_path = APPPATH . 'logs/calls_update_log_' . date('Y-m-d') . '.log';

                // Format the log entry
                $log_entry = '[' . $log_data['timestamp'] . '] '
                    . 'Execution Time: ' . $log_data['execution_time'] . ', '
                    . 'Rows Updated: ' . $log_data['updated_rows'] . ', '
                    . 'Message: ' . $log_data['message'] . PHP_EOL;

                // Save log entry
                file_put_contents($log_file_path, $log_entry, FILE_APPEND);
            } else {
                $response = array(
                    "status" => 0,
                    "message" => $validate[0],
                );
            }
        } else {
            $response = array(
                "status" => 1,
                "message" => "Call data update successfully.",
            );
        }
        echo  $this->json_output($response);
    }



    // public function call_update()
    //     {
    //         // ini_set('display_errors', '1');
    //         // ini_set('display_startup_errors', '1');
    //         // error_reporting(E_ALL);
    //         $response = [];

    //         // Check if call_data is provided
    //         if (empty($_POST['call_data'])) {
    //             $response = [
    //                 'status' => 1,
    //                 'message' => 'No call data to update.',
    //             ];
    //             echo $this->json_output($response);
    //             return;
    //         }

    //         $staff_data = $this->Api_Model->getdata(
    //             db_prefix() . 'staff',
    //             ['staffid' => $this->staffId]
    //         );

    // // print_r($staff_data);
    //         // Decode and validate JSON input
    //         $form_data = json_decode($_POST['call_data'], true);
    //         if (json_last_error() !== JSON_ERROR_NONE) {
    //             $response = [
    //                 'status' => 0,
    //                 'message' => 'Error decoding JSON: ' . json_last_error_msg(),
    //             ];
    //             echo $this->json_output($response);
    //             return;
    //         }

    //         // Build and validate input data
    //         $rules = $this->build_validation_rules($form_data);
    //         $validate = $this->validate->validation($rules);

    //         if ($validate !== true) {
    //             $response = [
    //                 'status' => 0,
    //                 'message' => $validate[0],
    //             ];
    //             echo $this->json_output($response);
    //             return;
    //         }

    //         // Get the last sync date from the form data
    //         $firstValue = reset($form_data["formData"]);
    //         $last_sync_date = $firstValue["startdate_time"] ?? null;


    //         // Process call data while avoiding duplicates
    //         $call_data = $this->process_call_data($form_data, $staff_data);

    //         // Update based on type
    //         if ($form_data['type'] == 1) {
    //             if (!empty($call_data)) {
    //                 $response = $this->Api_Model->update_call_data($call_data);
    //             } else {
    //                 $response = [
    //                     'status' => 1,
    //                     'message' => 'Call data already updated.',
    //                 ];
    //             }
    //         } elseif ($form_data['type'] == 2 && !empty($call_data)) {
    //             $response = $this->Api_Model->update_call_data_bulk_temp($call_data);

    //             if (!empty($last_sync_date) && $response["status"] == 1) {
    //                 $this->update_last_sync($this->staffId, $last_sync_date);
    //             }
    //         } else {
    //             $response = [
    //                 'status' => 1,
    //                 'message' => 'Call data already updated.',
    //             ];
    //         }

    //         echo $this->json_output($response);
    //     }

    private function build_validation_rules($form_data)
    {
        return [
            [
                'field' => 'Staff Id',
                'value' => $this->staffId,
                'condition' => 'required|{exist:{' . db_prefix() . 'staff:staffid:active:1}}',
            ],
            [
                'field' => 'Call Data',
                'value' => json_encode($form_data, true),
                'condition' => 'required',
            ]
            // [
            //     'field' => 'Assignee Contact Number',
            //     'value' => $form_data['formData']['callassignee'] ?? '',
            //     'condition' => 'required|{exist:{' . db_prefix() . 'staff:phonenumber:active:1}}',
            // ],
        ];
    }
    private function process_call_data($form_data, $staff_data)
    {
        $call_data = [];
        $type = $form_data['type'] ?? 1;

        if ($type == 2) {

            $where = array("staffid" => $this->staffId);
            $existing_sync = $this->Api_Model->getdata(
                db_prefix() . "call_sync",
                $where
            );
            $last_sync_time = "";
            if ($existing_sync["data"][0]["last_sync"]) {
                $last_sync_time = $existing_sync["data"][0]["last_sync"];
            }

            foreach ($form_data['formData'] as $form_d) {

                // Add only if no duplicate exists
                if (($last_sync_time <= $form_d["startdate_time"]) || $last_sync_time  = "") {
                    $call_data[] = $this->map_call_data($form_d, $staff_data, $type);
                }
            }
        } else {

            $call_data = $this->map_call_data($form_data['formData'], $staff_data, $type);
        }

        return $call_data;
    }

    private function map_call_data($form_d, $staff_data, $type)
    {
        if ($type == 2) {
            $callassignee = $staff_data['data'][0]['phonenumber'] ?? ($form_d['callassignee'] ?? '');
            $phonenumber = !empty($form_d['phonenumber']) ? substr(trim(str_replace('+91', '', $form_d['phonenumber'])), -10) : '';
            $call_start = !empty($form_d['startdate_time']) ? strtotime($form_d['startdate_time']) : '';
            $call_end = !empty($form_d['enddate_time']) ? strtotime($form_d['enddate_time']) : '';

            return [
                'staffid' => $this->staffId,
                'staff_contact' => $callassignee,
                'contact' => $phonenumber,
                'call_status' => $form_d['form-cf-13'] ?? 'Not Found',
                'calls_source' => $type,
                'calls_type' => $form_d['calls_type'] ?? ($form_d['call_type'] ?? ''),
                'duration' => $form_d['call_duration'] ?? '',
                'call_start' => $call_start,
                'call_end' => $call_end,
                'datetime' => date('Y-m-d H:i:s'),
            ];
        } else if ($type == 1) {
            $staffid = "";
            $callassignee =  !empty($form_d["callassignee"]) ? $form_d["callassignee"] : '';
            $staff_data_ =   $this->Api_Model->getdata(db_prefix() . "staff", array("phonenumber" => $form_d["callassignee"]));

            if (!empty($staff_data_["data"][0]["phonenumber"])) {
                $callassignee = !empty($staff_data_["data"][0]["phonenumber"]) ? $staff_data_["data"][0]["phonenumber"] : $form_d["callassignee"];
            } else {
                $staff_data_ =   $this->Api_Model->getdata(db_prefix() . "staff", array("alternate_number" => $form_d["callassignee"]));

                if (!empty($staff_data_["data"][0]["phonenumber"])) {
                    $callassignee = !empty($staff_data_["data"][0]["phonenumber"]) ? $staff_data_["data"][0]["phonenumber"] : $form_d["callassignee"];
                }
            }

            $phonenumber = !empty($form_d['phonenumber']) ? substr(trim(str_replace('+91', '', $form_d['phonenumber'])), -10) : '';
            $call_start = !empty($form_d['startdate_time']) ? strtotime($form_d['startdate_time']) : '';
            $call_end = !empty($form_d['enddate_time']) ? strtotime($form_d['enddate_time']) : '';
            $type = 1;
            if (!empty($callassignee)) {
                if (!empty($staff_data_["data"][0]["staffid"])) {
                    $staffid = !empty($staff_data_["data"][0]["staffid"]) ? $staff_data_["data"][0]["staffid"] : '';
                }
            }

            return [
                'staffid' => $staffid,
                'staff_contact' => $callassignee,
                'contact' => $phonenumber,
                'call_status' => $form_d['form-cf-13'] ?? 'Not Found',
                'calls_source' => $type,
                'calls_type' => $form_d['calls_type'] ?? ($form_d['call_type'] ?? ''),
                'duration' => $form_d['call_duration'] ?? '',
                'call_start' => $call_start,
                'call_end' => $call_end,
                'datetime' => date('Y-m-d H:i:s'),
            ];
        }
    }

    private function update_last_sync($staffId, $start_date_time)
    {
        $last_sync_time = $start_date_time;
        $where = array("staffid" => $staffId);
        $existing_sync = $this->Api_Model->getdata(
            db_prefix() . "call_sync",
            $where
        );


        if (!empty($existing_sync['data'])) {
            $this->Api_Model->update_data(
                db_prefix() . 'call_sync',
                ['last_sync' => $last_sync_time],
                ['staffid' => $staffId]
            );
        } else {
            $this->Api_Model->insert_data(
                db_prefix() . 'call_sync',
                ['staffid' => $staffId, 'last_sync' => $last_sync_time]
            );
        }
    }


    public function call_activity_cron()
    {
        $response = $this->Api_Model->update_call_activity();
        echo  $this->json_output($response);
    }

    public function update_all_contacts()
    {
        $response = $this->Api_Model->update_all_contacts();
        echo  $this->json_output($response);
    }

    public function excel_sync($id = "")
    {

        if (empty($id)) {
            $id = $_REQUEST['id'];
        }
        
        $this->load->library('GoogleSheetApi');
        $this->load->helper('google');
        $auto_sync = get_data_excel($id);

        if ($auto_sync) {
            $response[] = array(
                "status" => 1,
                "message" => "Google sheet Sync successfully.",
            );
        } else {
            $response[] = array(
                "status" => 0,
                "message" => $validate[0],
            );
        }
        echo  $this->json_output($response);
    }
}
