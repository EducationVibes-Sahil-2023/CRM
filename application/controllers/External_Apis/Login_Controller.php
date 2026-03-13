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
                    if($validate[0]=="Not Exist Email")
                    {
                    $validate[0] ="No account found with the provided email address.";
                    }
                    else if($validate[0]=="Password cannot be empty")
                    {
                    $validate[0] ="Please enter your password.";
                    }
                   
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

        die;

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

    public function applicant_sync_excel_old()
    {

        $this->load->helper('google');

        // Initialize response
        $response = [
            'status' => 0,
            'message' => 'An unknown error occurred.',
        ];

        try {
            // Check if 'id' is provided
            if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
                throw new Exception('Missing required parameter: id');
            }

            // Decode the ID (from JavaScript encodeURIComponent)
            $id = rawurldecode($_REQUEST['id']);

            // Validate the format of the ID (optional, example: only allow alphanumeric and comma)
            if (!preg_match('/^[a-zA-Z0-9,_\-]+$/', $id)) {
                throw new Exception('Invalid sheet ID format.');
            }

            // Attempt to sync
            $auto_sync = syncExcel($id);

            if ($auto_sync === true) {
                $response = [
                    'status' => 1,
                    'message' => 'Google sheet synced successfully.',
                ];
            } else {
                // Assume syncExcel() returns false or an error string/array
                $errorMessage = is_array($auto_sync) ? $auto_sync[0] : 'Sync failed due to unknown reason.';
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            $response = [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }

        // Output JSON response
        echo $this->json_output([$response]);
    }

    public function applicant_sync_excel()
    {

        $this->load->helper('google');

        // Initialize response
        $response = [
            'status' => 0,
            'message' => 'An unknown error occurred.',
        ];

        try {
            // Check if 'id' is provided
            if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
                throw new Exception('Missing required parameter: id');
            }

            // Decode the ID (from JavaScript encodeURIComponent)
            $id = rawurldecode($_REQUEST['id']);

            // Validate the format of the ID (optional, example: only allow alphanumeric and comma)
            if (!preg_match('/^[a-zA-Z0-9,_\-]+$/', $id)) {
                throw new Exception('Invalid sheet ID format.');
            }

            // Attempt to sync
            $auto_sync = syncExcel_neww($id);

            if ($auto_sync === true) {
                $response = [
                    'status' => 1,
                    'message' => 'Google sheet synced successfully.',
                ];
            } else {
                // Assume syncExcel() returns false or an error string/array
                $errorMessage = is_array($auto_sync) ? $auto_sync[0] : 'Sync failed due to unknown reason.';
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            $response = [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }

        // Output JSON response
        echo $this->json_output([$response]);
    }


    public function lead_sync_excel()
    {
        $this->load->helper('google');

        // Initialize response
        $response = [
            'status' => 0,
            'message' => 'An unknown error occurred.',
        ];

        $auto_sync = syncExcel_leads();

        if ($auto_sync === true) {
            $response = [
                'status' => 1,
                'message' => 'Google sheet synced successfully.',
            ];
        } else {
            // Assume syncExcel() returns false or an error string/array
            $errorMessage = is_array($auto_sync) ? $auto_sync[0] : 'Sync failed due to unknown reason.';
            throw new Exception($errorMessage);
        }
    }
    
public function quotations()
{
    $this->load->helper('google');

    try {
        $auto_sync = ma_quotations();

        if (!empty($auto_sync)) {
            // Pass data to sync function
            $syncResult = syncExcel($auto_sync); // <-- assuming you have this helper to push data

            if ($syncResult === true) {
                $response = [
                    'status'  => 1,
                    'message' => 'Google sheet synced successfully.',
                ];
            } else {
                $errorMessage = is_array($syncResult) ? reset($syncResult) : 'Sync failed due to unknown reason.';
                throw new Exception($errorMessage);
            }
        } else {
            throw new Exception('No data available to sync.');
        }
    } catch (Exception $e) {
        $response = [
            'status'  => 0,
            'message' => $e->getMessage(),
        ];
    }

    echo json_encode($response);
}

public function payment_dues()
{
    
       $this->load->helper('google');
    paymentDues();
}

public function payment_dues_hostel()
{
    
       $this->load->helper('google');
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    paymentDuesHostel();
}


   public function update_quotations()
{
   $basePath = './uploads/clients_documents/';

    // 1️⃣ Get all users with userid > 1783
    $query = $this->db
                  ->select('userid')
                  ->from('tblclients_new')
                  ->where('userid >', 1783)
                  ->get();

    $users = $query->result_array();

    foreach ($users as $user) {
        $userid = $user['userid'];
        $userFolder = rtrim($basePath, '/') . '/' . intval($userid) . '/';

        if (!is_dir($userFolder)) {
            echo "User {$userid}: Folder not found.<br>";
            continue;
        }

        // Initialize latest file tracking
        $latestFiles = [
            'quotation' => null,
            'registration_slip_invoice' => null,
            'registration_slip' => null,
            'fees_structure' => null,
            'refund_payment_proof' => null
        ];
        $latestTimes = array_fill_keys(array_keys($latestFiles), 0);

        $files = scandir($userFolder);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $filePath = $userFolder . $file; // full path with ./ at start
            $mtime = filemtime($filePath);

            // Convert path to start with 'uploads/' (remove leading ./)
            $dbPath = preg_replace('#^\./#', '', $filePath);

            // 1️⃣ quotation: ends with quotation.<ext>
            if (preg_match('/quotation\.[a-z0-9]+$/i', $file)) {
                if ($mtime > $latestTimes['quotation']) {
                    $latestTimes['quotation'] = $mtime;
                    $latestFiles['quotation'] = $dbPath; // store path starting with uploads/
                }
            }

// 2️⃣ registration_slip_invoice: starts with "Registration_Slip_" (case-sensitive)
if (strpos($file, 'Registration_Slip_') === 0) {
    if ($mtime > $latestTimes['registration_slip_invoice']) {
        $latestTimes['registration_slip_invoice'] = $mtime;
        $latestFiles['registration_slip_invoice'] = $dbPath;
    }
}

// 3️⃣ registration_slip: ends with "registration_slip" (case-sensitive)
$searchSuffix = 'registration_slip'; // without extension

// Check if filename (without extension) ends with 'registration_slip'
$filenameWithoutExt = pathinfo($file, PATHINFO_FILENAME);

if (substr($filenameWithoutExt, -strlen($searchSuffix)) === $searchSuffix) {
    if ($mtime > $latestTimes['registration_slip']) {
        $latestTimes['registration_slip'] = $mtime;
        $latestFiles['registration_slip'] = $dbPath; // full path including extension
    }
}



            // 3️⃣ fees_structure: ends with fees_structure.<ext>
            if (preg_match('/fees_structure\.[a-z0-9]+$/i', $file)) {
                if ($mtime > $latestTimes['fees_structure']) {
                    $latestTimes['fees_structure'] = $mtime;
                    $latestFiles['fees_structure'] = $dbPath;
                }
            }

            // 4️⃣ refund_payment_proof: ends with refund_payment_proof.<ext>
            if (preg_match('/refund_payment_proof\.[a-z0-9]+$/i', $file)) {
                if ($mtime > $latestTimes['refund_payment_proof']) {
                    $latestTimes['refund_payment_proof'] = $mtime;
                    $latestFiles['refund_payment_proof'] = $dbPath;
                }
            }
        }

        // Only prepare columns that have files
        $updateData = array_filter($latestFiles, fn($v) => !empty($v));

        if (!empty($updateData)) {
            echo "<b>User {$userid} - Preview Update:</b><br>";
            foreach ($updateData as $column => $path) {
                echo "&nbsp;&nbsp;Column <b>{$column}</b> would be updated with path: <b>{$path}</b><br>";
            }

            // Optional: show SQL query preview
            $setParts = [];
            foreach ($updateData as $col => $val) {
                $setParts[] = "`{$col}` = '" . addslashes($val) . "'";
            }
            $this->db->where('userid', $userid);
$this->db->update('tblclients_new', $updateData);
            $sqlPreview = "UPDATE `tblclients_new` SET " . implode(', ', $setParts) . " WHERE userid = {$userid};";
            echo "&nbsp;&nbsp;<i>SQL Preview:</i> {$sqlPreview}<br><br>";
        } else {
            echo "User {$userid}: No files found to update.<br><br>";
        }
    }

    echo "✅ Preview completed for all users.<br>";
}

public function check_lead_auto_transfer_lead()
{
     $this->load->model('Leads_model');
    $this->Leads_model->check_lead_auto_transfer_lead();
    
}


public function update_excelData()
{
    

  
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

  $this->load->library('app_object_cache');


    
      $this->load->model('Leads_model','leads_model');
    $key =  $_REQUEST["key"];
     $form = $this->leads_model->get_form([
            'form_key' => $key,
        ]);
        
        if (!$form) {
            
            return false;
        }
        
    

    
        
         if (!empty($key)) {
                // $post_data = $this->input->post();
                
                $json = file_get_contents('php://input');
    $jsonData = json_decode($json, true);



                
            if(!empty($jsonData))
            {
                foreach($jsonData as $j_Data){
                    
                        $data['form_fields'] = json_decode($form->form_data);
        if (!$data['form_fields']) {
            $data['form_fields'] = [];
        }
                
                  $generate_lead_transfer_request = "";
        $generate_lead_transfer_request_array = [];
        
                $post_data = $j_Data;

                $google_source =  !empty($form->lead_source) ? $form->lead_source : '';

                if (!empty($post_data["form-cf-37"]) && $post_data["form-cf-37"] == "Reddit Ads") {
                    $form->lead_source = 69;
                }
                $post_data["phonenumber"] =  substr(preg_replace('/\D/', '', $post_data["phonenumber"]), -10);
                $post_data["phonenumber"] = !empty($post_data["phonenumber"]) ? substr(trim($post_data["phonenumber"]), -10) : '';
                $post_data["phonenumber"] = str_replace("+91", "", $post_data["phonenumber"]);
                if (!isset($post_data["phonenumber"]) || strlen(trim($post_data["phonenumber"])) != 10) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid phone number. It must be exactly 10 digits.',
                        'redirect_url' => false,
                    ]);
                    return true;
                }


                $required  = [];
                $lead_type = !empty($form->lead_type) ? trim($form->lead_type) : '';
              
                foreach ($data['form_fields'] as $field) {
                    if (isset($field->required)) {
                        $required[] = $field->name;
                    }
                }

                if (!empty($form->auto_assign)) {
                    $auto_assign = array_filter(explode(",", $form->auto_assign));
                    $assign_staff_id = $this->leads_model->automatic_assign_staff('', '', '', '', $auto_assign);
                    if (!empty($assign_staff_id[0]["staffid"])) {
                        $form->responsible = $assign_staff_id[0]["staffid"];
                    }
                }


                if (!empty($form->state_wise)  && $form->state_wise == 1) {
                    $form->responsible = 1;

                    if (!empty($form->allow_state_location) && $form->allow_state_location == 1) {
                        $state_name = !empty($post_data['state']) ? trim($post_data['state']) : '';
                        $city_name = !empty($post_data['city']) ? trim($post_data['city']) : '';
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $ipdetails = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
                        $state_name = !empty($ipdetails->region) ? trim($ipdetails->region) : '';
                        $city_name = !empty($ipdetails->city) ? trim($ipdetails->city) : '';
                    }
                    $lead_type = !empty($post_data["type"]) ? trim($post_data["type"]) : '';
                    if (empty($lead_type)) {
                        $lead_type = !empty($form->lead_type) ? trim($form->lead_type) : '';
                    }
                    $status_assign = false;

                    if (!empty($city_name) && $status_assign == false) {
                        $assign_staff_id = $this->leads_model->automatic_assign_staff_city($city_name, $lead_type, '', '', '', $google_source);
                        if (!empty($assign_staff_id[0]["staffid"])) {
                            $form->responsible = $assign_staff_id[0]["staffid"];
                            $status_assign = true;
                        }
                    }

                    if (!empty($state_name)  && $status_assign == false) {
                        $assign_staff_id = $this->leads_model->automatic_assign_staff($state_name, $lead_type, '', '', '', $google_source);
                        if (!empty($assign_staff_id[0]["staffid"])) {
                            $form->responsible = $assign_staff_id[0]["staffid"];
                            $status_assign = true;
                        }
                    } else if (!empty($lead_type)  && $status_assign == false) {
                        $assign_staff_id = $this->leads_model->automatic_assign_staff('', $lead_type, 1);
                        if (!empty($assign_staff_id[0]["staffid"])) {
                            $form->responsible = $assign_staff_id[0]["staffid"];
                        }
                    }
                }


    

                unset($post_data['key']);

                $regular_fields = [];
                $custom_fields  = [];
                foreach ($post_data as $name => $val) {
                    if (strpos($name, 'form-cf-') !== false) {
                        array_push($custom_fields, [
                            'name'  => $name,
                            'value' => $val,
                        ]);
                    } else {
                        if ($this->db->field_exists($name, db_prefix() . 'leads')) {
                            if ($name == 'country') {
                                if (!is_numeric($val)) {
                                    if ($val == '') {
                                        $val = 0;
                                    } else {
                                        $this->db->where('iso2', $val);
                                        $this->db->or_where('short_name', $val);
                                        $this->db->or_where('long_name', $val);
                                        $country = $this->db->get(db_prefix() . 'countries')->row();
                                        if ($country) {
                                            $val = $country->country_id;
                                        } else {
                                            $val = 0;
                                        }
                                    }
                                }
                            } elseif ($name == 'address') {
                                $val = trim($val);
                                $val = nl2br($val);
                            }

                            $regular_fields[$name] = $val;
                        }
                    }
                }
                $success      = false;
                $insert_to_db = true;


           

                $where_or = [];
                if ($form->allow_duplicate == 0) {
                    $where = [];
                    if (!empty($form->track_duplicate_field) && isset($regular_fields[$form->track_duplicate_field])) {
                        $where[$form->track_duplicate_field] = $regular_fields[$form->track_duplicate_field];
                    }
                    if (!empty($form->track_duplicate_field_and) && isset($regular_fields[$form->track_duplicate_field_and])) {
                        $where[$form->track_duplicate_field_and] = $regular_fields[$form->track_duplicate_field_and];
                    }

                    if (count($where) > 0) {
                        unset($where['phonenumber']);
                        unset($where['email']);
                        $where_or = [
                            'alternative_phonenumber' => $post_data["phonenumber"],
                            'phonenumber' => $post_data["phonenumber"]
                        ];
                        // First query to count total duplicates
                        if (!empty($where)) {
                            $this->db->where($where);
                        }
                        $this->db->group_start(); // Start OR condition grouping
                        $this->db->or_where($where_or);
                        $this->db->group_end(); // End OR condition grouping

                        $total = $this->db->count_all_results(db_prefix() . 'leads');
                        // $total = total_rows(db_prefix() . 'leads', $where);

                        $duplicateLead = false;
                       

                        if ($total > 0) {
                            // Success set to true for the response.
                            $success      = true;
                            $insert_to_db = false;


                            // convert to fresh lead
                            if (!empty($where)) {
                                $this->db->where($where);
                            }

                            $this->db->group_start();
                            $this->db->or_where($where_or);
                            $this->db->group_end();

                            $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();



                            if (!empty($form->lead_source)) {

                                $source_data_get = $this->leads_model->get_source($duplicateLead->source);
                                $source_data_get_ = $this->leads_model->get_source($form->lead_source);

                                if (!empty($source_data_get->fixed_source) && $source_data_get->fixed_source == 1) {
                                } else {
                                    $updateStatus['source'] = $form->lead_source;
                                }

                                if (!empty($source_data_get_->lead_transfer_status) && $source_data_get_->lead_transfer_status == 1) {
                                    $generate_lead_transfer_request = 1;
                                }
                            }



                            // if ($generate_lead_transfer_request && $generate_lead_transfer_request == 1) {

                            //     if (!empty($call_data)) {
                            //         $response_call = $this->curl_function($call_data);
                            //         $response_call = json_decode($response_call);
                            //         if (isset($response_call[0]->status) && $response_call[0]->status == 0) {
                            //             // echo json_encode([
                            //             //     'success' => 0,
                            //             //     'message' => $response_call[0]->message
                            //             // ]);
                            //         }
                            //     }
                            //     if ($form->responsible == $duplicateLead->assigned) {
                            //         // echo json_encode(['success' => true, 'message' => "Lead Transfer Request Generate successfully"]);
                            //         // die;
                            //     }
                            //     $generate_lead_transfer_request_array = [];
                            //     $generate_lead_transfer_request_array["lead_id"] = $duplicateLead->id;
                            //     $generate_lead_transfer_request_array["transfer_lead_type"] = $lead_type;
                            //     $generate_lead_transfer_request_array["transfer_source_type"] = !empty($updateStatus['source']) ? $updateStatus['source'] : $duplicateLead->source;
                            //     $generate_lead_transfer_request_array["transfer_lead_assign"] = !empty($form->responsible) ? $form->responsible : 1;
                            //     $generate_lead_transfer_request_array["reason"] = "Automatic Lead transfer request.";
                            //     $generate_lead_transfer_request_array["auto_genrate_lead_transfer"] = 1;




                            //     if (!empty($generate_lead_transfer_request_array)) {

                            //         $response_trnasferRequest = $this->add_lead_transfer_request($generate_lead_transfer_request_array, $duplicateLead->assigned);


                            //         if (isset($response_trnasferRequest["success"]) && $response_trnasferRequest["success"] == 0) {
                            //             // echo json_encode([
                            //             //     'success' => 0,
                            //             //     'message' => $response_trnasferRequest["message"]
                            //             // ]);
                            //             // die;
                            //         } else {
                            //             // echo json_encode([
                            //             //     'success' => $success,
                            //             //     'message' => $form->success_submit_msg,
                            //             //     'redirect_url' => false,
                            //             // ]);
                            //             // die;
                            //         }
                            //     }
                            // }


                            $updateStatus = [

                                'status' => $form->lead_status,
                                'last_status_change' => date("Y-m-d"),
                                'lastcontact' => date("Y-m-d h:i:s"),
                                'dateassigned' => date("Y-m-d")
                            ];

                            if (!empty($post_data["website"])) {
                                $updateStatus['website'] = $post_data["website"];
                            }


                            $regular_fields = [];
                            $custom_fields  = [];
                            foreach ($post_data as $name => $val) {
                                if (strpos($name, 'form-cf-') !== false) {
                                    array_push($custom_fields, [
                                        'name'  => $name,
                                        'value' => $val,
                                    ]);
                                }

                                $custom_fields_build['leads'] = [];
                                foreach ($post_data as $name => $val) {
          
                                    if (!empty($_POST['form-cf-' . WEB_HISTORY_ID])) {
                                        $web_activity_log_data = $this->db->select("value")->where(array("fieldid" => WEB_HISTORY_ID, "fieldto" => "leads", "relid" => $duplicateLead->id))->get(db_prefix() . "customfieldsvalues")->row_array();



                                        if (empty($web_activity_log_data)) {
                                            $custom_fields_build['leads'][WEB_HISTORY_ID] = !empty($_POST['form-cf-' . WEB_HISTORY_ID]) ? $_POST['form-cf-' . WEB_HISTORY_ID] : "";
                                        } else {
                                            $custom_fields_build['leads'][WEB_HISTORY_ID] = $web_activity_log_data["value"] . "," . (!empty($_POST['form-cf-' . WEB_HISTORY_ID]) ? $_POST['form-cf-' . WEB_HISTORY_ID] : "");
                                        }
                                    }
                                }
                            }




                            if (!empty($custom_fields_build['leads'])) {
                                handle_custom_fields_post($duplicateLead->id, $custom_fields_build);
                            }

                            if (!empty($form->lead_source)) {
                                $source_data_get = $this->leads_model->get_source($duplicateLead->source);
                                if (!empty($source_data_get->fixed_source) && $source_data_get->fixed_source == 1) {
                                } else {
                                    $updateStatus['source'] = $form->lead_source;
                                }
                            }



                            if (!empty($updateStatus['source'])) {
                                $this->leads_model->update_lead_source($updateStatus['source'], $duplicateLead->id);
                            }
                            
                 


                         


                            if (!empty($form->assign_previous_lead_alert) && $form->assign_previous_lead_alert == 1) {
                                if (!empty($updateStatus["assigned"]) && !empty($duplicateLead->assigned) && $duplicateLead->assigned != $updateStatus["assigned"]) {
                                    $notifiedUsers = [];
                                    $notified = add_notification([
                                        'description'     => 'lead_assign_previous_lead',
                                        'touserid'        => $duplicateLead->assigned,
                                        'fromcompany'     => 1,
                                        'fromuserid'      => null,
                                        'additional_data' => serialize([
                                            $duplicateLead->name,
                                            // !empty($this->leads_model->get_source($duplicateLead->source)->name) ? $this->leads_model->get_source($duplicateLead->source)->name : '',
                                            get_staff_full_name($updateStatus["assigned"])
                                        ])
                                    ]);
                                    if ($notified) {
                                        array_push($notifiedUsers, $duplicateLead->assigned);
                                    }
                                    pusher_trigger_notification($notifiedUsers);
                                    $this->leads_model->log_lead_activity($duplicateLead->id, 'lead_assign_previous_lead', true, serialize([
                                        $duplicateLead->name,
                                        // !empty($this->leads_model->get_source($duplicateLead->source)->name) ? $this->leads_model->get_source($duplicateLead->source)->name : '',
                                        get_staff_full_name($updateStatus["assigned"])
                                    ]));
                                }
                            }
                            $this->db->where('id', $duplicateLead->id);
                            $this->db->update(db_prefix() . 'leads', $updateStatus);


                            $notifiedUsers = [];
                            $notified = add_notification([
                                'description'     => 'not_lead_imported_from_form',
                                'touserid'        => $form->responsible,
                                'fromcompany'     => 1,
                                'fromuserid'      => null,
                                'additional_data' => serialize([
                                    $form->name,
                                ]),
                                'link' => '#leadid=' . $duplicateLead->id,
                            ]);
                            
                            
                            if ($notified) {
                                array_push($notifiedUsers, $form->responsible);
                            }

                            pusher_trigger_notification($notifiedUsers);
                            $this->leads_model->log_lead_activity($duplicateLead->id, 'not_lead_imported_from_form', true, serialize([
                                $form->name,
                            ]));
                            hooks()->do_action('web_to_lead_form_submitted', [
                                'lead_id' => $duplicateLead->id,
                                'form_id' => $form->id,
                                'task_id' => 0,
                            ]);

                            //end convert to specified status

                            if ($form->create_task_on_duplicate == 1) {
                                $task_name_from_form_name = false;
                                $task_name                = '';
                                if (isset($regular_fields['name'])) {
                                    $task_name = $regular_fields['name'];
                                } elseif (isset($regular_fields['email'])) {
                                    $task_name = $regular_fields['email'];
                                } elseif (isset($regular_fields['company'])) {
                                    $task_name = $regular_fields['company'];
                                } else {
                                    $task_name_from_form_name = true;
                                    $task_name                = $form->name;
                                }
                                if ($task_name_from_form_name == false) {
                                    $task_name .= ' - ' . $form->name;
                                }

                                $description          = '';
                                $custom_fields_parsed = [];
                                foreach ($custom_fields as $key => $field) {
                                    $custom_fields_parsed[$field['name']] = $field['value'];
                                }

                                $all_fields    = array_merge($regular_fields, $custom_fields_parsed);
                                $fields_labels = [];
                                foreach ($data['form_fields'] as $f) {
                                    if ($f->type != 'header' && $f->type != 'paragraph' && $f->type != 'file') {
                                        $fields_labels[$f->name] = $f->label;
                                    }
                                }

                                $description .= $form->name . '<br /><br />';
                                foreach ($all_fields as $name => $val) {
                                    if (isset($fields_labels[$name])) {
                                        if ($name == 'country' && is_numeric($val)) {
                                            $c = get_country($val);
                                            if ($c) {
                                                $val = $c->short_name;
                                            } else {
                                                $val = 'Unknown';
                                            }
                                        }

                                        $description .= $fields_labels[$name] . ': ' . $val . '<br />';
                                    }
                                }

                                $task_data = [
                                    'name'        => $task_name,
                                    'priority'    => get_option('default_task_priority'),
                                    'dateadded'   => date('Y-m-d H:i:s'),
                                    'startdate'   => date('Y-m-d'),
                                    'addedfrom'   => $form->responsible,
                                    'status'      => 1,
                                    'description' => $description,
                                ];

                                $task_data = hooks()->apply_filters('before_add_task', $task_data);
                                $this->db->insert(db_prefix() . 'tasks', $task_data);
                                $task_id = $this->db->insert_id();
                                if ($task_id) {
                                    $attachment = handle_task_attachments_array($task_id, 'file-input');

                                    if ($attachment && count($attachment) > 0) {
                                        $this->tasks_model->add_attachment_to_database($task_id, $attachment, false, false);
                                    }

                                    $assignee_data = [
                                        'taskid'   => $task_id,
                                        'assignee' => $form->responsible,
                                    ];
                                    $this->tasks_model->add_task_assignees($assignee_data, true);

                                    hooks()->do_action('after_add_task', $task_id);
                                    if ($duplicateLead && $duplicateLead->email != '') {
                                        send_mail_template('lead_web_form_submitted', $duplicateLead);
                                    }
                                }
                            }
                        }
                    }
                }
                if ($insert_to_db == true) {
                    $regular_fields['status'] = $form->lead_status;
                    if ((isset($regular_fields['name']) && empty($regular_fields['name'])) || !isset($regular_fields['name'])) {
                        $regular_fields['name'] = 'Unknown';
                    }
                    $ip = $_SERVER['REMOTE_ADDR'];
                    if (!empty($form->facebook_status) && $form->facebook_status == 1) {
                        $regular_fields['city']       = $post_data['city'];
                        $regular_fields['state']       = $post_data['state'];
                        $regular_fields['website']    = !empty($post_data['website']) ? $post_data['website'] : '';
                    } else {


                        $ipdetails = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

                        $regular_fields['city']       = $ipdetails->city;
                        $regular_fields['state']       = $ipdetails->region;


                        $regular_fields['country']       = ($ipdetails->country == 'IN') ? '102' : 0;
                        $regular_fields['zip']       = $ipdetails->postal;
                    }
                    if ($key == 'de34ba611f3853dc13f2596a4ba992ac' || $key == 'b3ac9c60479c54b9ab83dc3a85b71bde' ||  (!empty($form->allow_state_location) && $form->allow_state_location == 1)) {

                        $regular_fields['city']       = $post_data['city'];
                        $regular_fields['state']       = $post_data['state'];
                    }


                    if (!empty($lead_type)) {
                        $regular_fields['type']  = $lead_type;
                    }
                    $regular_fields['source']       = $form->lead_source;
                    $regular_fields['addedfrom']    = 0;
                    $regular_fields['lastcontact']  = null;
                    $regular_fields['assigned']     = $form->responsible;
                    $regular_fields['dateadded']    = date('Y-m-d H:i:s');
                    $regular_fields['from_form_id'] = $form->id;
                    $regular_fields['is_public']    = $form->mark_public;

                    $this->db->insert(db_prefix() . 'leads', $regular_fields);
                    $lead_id = $this->db->insert_id();

                    hooks()->do_action('lead_created', [
                        'lead_id'          => $lead_id,
                        'web_to_lead_form' => true,
                    ]);

                    if (!empty($post_data['tags'])) {
                        $tags = $post_data['tags'];
                        handle_tags_save($tags, $lead_id, 'lead');
                    }

                    $success = false;
                    if ($lead_id) {
                        $success = true;

                        if (ENABLE_WHATSAPP_MESSAGE) {

                            if (!empty($post_data["phonenumber"]) && !empty($lead_id)) {
                                $data = [
                                    'phonenumber'   => $post_data["phonenumber"],
                                    'responsible'   => $form->responsible,
                                    'lead_id'       => $lead_id,
                                    'channel_type'  => 8,
                                    'status'        => 2,
                                    'cron_time'     => date('Y-m-d H:i:s', strtotime('+10 minute')),
                                    'created_at'    => date('Y-m-d H:i:s')
                                ];

                                $this->db->insert(db_prefix() . 'whatsapp_messages_channel', $data);
                            }


                            // welcome_whatsapp_channel_study_abroad($post_data["phonenumber"], $form->responsible, $lead_id, 8);
                            welcome_whatsapp_message_send($post_data["phonenumber"], $form->responsible, $lead_id, WELCOME_WHATSAPP_MESSAGE);
                        }

                        $this->leads_model->log_lead_activity($lead_id, 'not_lead_imported_from_form', true, serialize([
                            $form->name,
                        ]));
                        // /handle_custom_fields_post
                        $custom_fields_build['leads'] = [];
                        foreach ($custom_fields as $cf) {
                            $cf_id                                = strafter($cf['name'], 'form-cf-');
                            $custom_fields_build['leads'][$cf_id] = $cf['value'];
                        }

                        handle_custom_fields_post($lead_id, $custom_fields_build);
//  $this->load->library('mails/Lead_assigned');
//       $this->load->library('mails/App_mail_template');
   
                        // $this->leads_model->lead_assigned_member_notification($lead_id, $form->responsible, true);

                        handle_lead_attachments($lead_id, 'file-input', $form->name);
                        if (!empty($post_data['tags'])) {
                            $tags = $post_data['tags'];
                            handle_tags_save($tags, $lead_id, 'lead');
                        }

                        // if (isset($regular_fields['email']) && $regular_fields['email'] != '') {
                        //     $lead = $this->leads_model->get($lead_id);
                        //     send_mail_template('lead_web_form_submitted', $lead);
                        // }
                    }
                } // end insert_to_db
                if ($success == true) {
                    if (!isset($lead_id)) {
                        $lead_id = 0;
                    }
                    if (!isset($task_id)) {
                        $task_id = 0;
                    }
                    hooks()->do_action('web_to_lead_form_submitted', [
                        'lead_id' => $lead_id,
                        'form_id' => $form->id,
                        'task_id' => $task_id,
                        'redirect_url' => '',
                    ]);
                }


                $redirect_url = false;
                // echo json_encode([
                //     'success' => $success,
                //     'message' => $form->success_submit_msg,
                //     'redirect_url' => $redirect_url,
                // ]);

                // return true;
            }
            }
         }
}

public function transfer_whatsapp_notification()
{
    $this->load->model('Leads_model');
    $this->Leads_model->transfer_whatsapp_notification();

}

public function tbl_call_sync()
{
    try {

        // Allow only POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Invalid request method. Only POST allowed.'
                ]));
        }

        // Get POST data
        $postData = $this->input->post();

        // if (empty($postData)) {
        //     return $this->output
        //         ->set_status_header(400)
        //         ->set_content_type('application/json')
        //         ->set_output(json_encode([
        //             'status'  => false,
        //             'message' => 'No data received.'
        //         ]));
        // }

        // Convert to JSON
        $data = json_encode($postData);

        // Insert into DB
        $insert = $this->Api_Model->insert_data(
            db_prefix() . '_call_sync',
            [
                'created_at' => date('Y-m-d H:i:s'),
                'data'       => $data
            ]
        );

        if (!$insert) {
            throw new Exception('Database insert failed');
        }

        // Success response
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'Data synced successfully',
                'data'=>$postData
            ]));

    } catch (Exception $e) {

        log_message('error', 'Call Sync API Error: ' . $e->getMessage());

        return $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => false,
                'message' => 'Server error',
                'error'   => $e->getMessage()
            ]));
    }
}

public function google_qualified_leads()
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Allow only POST
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid request method'
        ]);
        return;
    }

    $this->load->model('Leads_model');

    // Get POST data
    $type  = trim($this->input->post('type'));
    $limit = $this->input->post('limit');

    // Validation
    if (empty($type)) {
        echo json_encode([
            'status' => false,
            'message' => 'Type is required'
        ]);
        return;
    }



    // Get data
    $data = $this->Leads_model->google_qualified_leads($type, $limit);

    if (!empty($data)) {
        echo json_encode([
            'status' => true,
            'count'  => count($data),
            'data'   => $data
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'No records found'
        ]);
    }
}

}
