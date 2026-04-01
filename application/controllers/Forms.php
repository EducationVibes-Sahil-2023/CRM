<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

defined('BASEPATH') or exit('No direct script access allowed');

class Forms extends ClientsController
{
    public function index()
    {
        show_404();
    }

public function checkWhatsappMessage()
{
   
$this->db->where('status', 2);
$this->db->where('cron_time <', date('Y-m-d H:i:s'));
$query = $this->db->get(db_prefix().'whatsapp_messages_channel');

$result = $query->result();

foreach ($result as $row) {

    try {

        // Start transaction for safety
        $this->db->trans_begin();

        // Call WhatsApp function
        $response = welcome_whatsapp_channel_study_abroad(
            $row->phonenumber,
            $row->responsible,
            $row->lead_id,
            $row->channel_type
        );

        // If function returns false or error
        if ($response === false) {
            throw new Exception('WhatsApp function failed for ID: ' . $row->id);
        }

        // Update status after successful send
        $this->db->where('id', $row->id)
                 ->update(db_prefix().'whatsapp_messages_channel', [
                     'status'   => 1, // sent
                     'updated_at'  => date('Y-m-d H:i:s')
                 ]);

        // Commit transaction
        if ($this->db->trans_status() === FALSE) {
            throw new Exception('DB transaction failed for ID: ' . $row->id);
        }

        $this->db->trans_commit();

    } catch (Exception $e) {

        // Rollback on error
        $this->db->trans_rollback();

        // Optional: mark as failed
        $this->db->where('id', $row->id)
                 ->update(db_prefix().'whatsapp_messages_channel', [
                     'status' => 0 // failed
                 ]);

        // Log error (recommended)
        log_message('error', $e->getMessage());
    }
}


}


 public function call_whatsaap_function()
    {
        
    }

    public function wtl($key)
    {
        $generate_lead_transfer_request = "";
        $generate_lead_transfer_request_array = [];
        $this->load->model('leads_model');
        $form = $this->leads_model->get_form([
            'form_key' => $key,
        ]);
        $tags = "";

        if (!$form) {
            show_404();
        }

        if (!empty($_POST["facebook_status"])) {
            $form->facebook_status = 1;
        }
        $tags = "";
        $GLOBALS['locale'] = get_locale_key($form->language);

        $data['form_fields'] = json_decode($form->form_data);
        if (!$data['form_fields']) {
            $data['form_fields'] = [];
        }
        
        
    if($key == "834681a14c5d64a07d1fabcd11a5f9a8"){
        /* Get JSON body */
$json = file_get_contents('php://input');
$json_data = json_decode($json, true);

/* Convert JSON to POST */
if (is_array($json_data)) {
    $_POST = array_merge($_POST, $json_data);
}

}

        
          //  if($key == "c04d2a1fda6448b12c7fe55c5f2184f2"){
        //       $this->db->insert(db_prefix() . 'facebook_webhook_data', ['data' => json_encode($post_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),"form_id"=>"whatsapp"]);
        //         }
        if ($this->input->post('key')) {
            if ($this->input->post('key') == $key) {
                $post_data = $this->input->post();

                $google_source =  !empty($form->lead_source) ? $form->lead_source : '';
                
                if(!empty($post_data["form-cf-37"]) && $post_data["form-cf-37"] == "Reddit Ads")
                {
                   $form->lead_source =69; 
                   
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
                
 
                $call_data = array();
                $required  = [];
                $lead_type = !empty($form->lead_type) ? trim($form->lead_type) : '';
                if ($form->responsible == 0) {
                    if ($post_data['callassignee'] != null) {
                        $phoneNumber = $post_data['callassignee'];
                        $this->db->where('phonenumber', $phoneNumber);
                        $user =  $this->db->get(db_prefix() . 'staff')->row();
                        $form->responsible = $user->staffid;
                        $call_data = array("type" => 1, "formData" => $post_data);
                    }
                }
                $post_data["call_duration"] = 0;
                foreach ($data['form_fields'] as $field) {
                    if (isset($field->required)) {
                        $required[] = $field->name;
                    }
                }


                if (empty($post_data['callassignee']) && !empty($post_data['auto_assign'])  && $post_data['auto_assign'] == 1) {
               
                    
                    $form->responsible = 1;
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $ipdetails = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
                    $state_name = !empty($ipdetails->region) ? trim($ipdetails->region) : '';
                    $city_name = !empty($ipdetails->city) ? trim($ipdetails->city) : '';
                    $lead_type = !empty($post_data["type"]) ? trim($post_data["type"]) : '';
                    $fb_status_check = false;
                    if (!empty($city_name) && !empty($lead_type)) {
                        $assign_staff_id = $this->leads_model->automatic_assign_staff_city($city_name, $lead_type);
                        if (!empty($assign_staff_id[0]["staffid"])) {
                            $form->responsible = $assign_staff_id[0]["staffid"];
                            $fb_status_check = true;
                        }
                    } else if (!empty($state_name) && !empty($lead_type) &&  $fb_status_check == false) {
                        $assign_staff_id = $this->leads_model->automatic_assign_staff($state_name, $lead_type);
                        if (!empty($assign_staff_id[0]["staffid"])) {
                            $form->responsible = $assign_staff_id[0]["staffid"];
                        } else if (!empty($lead_type)) {
                            $assign_staff_id = $this->leads_model->automatic_assign_staff('', $lead_type, 1);
                            if (!empty($assign_staff_id[0]["staffid"])) {
                                $form->responsible = $assign_staff_id[0]["staffid"];
                            }
                        }
                    }
                }

                if (!empty($form->facebook_status) && $form->facebook_status == 1) {
                    $form->responsible = 1;
                    $state_name = !empty($post_data['state']) ? $post_data['state'] : '';
                    $lead_type = !empty($post_data['type']) ? $post_data['type'] : '';

                    if (!empty($lead_type)) {
                        $facebook_lead_name = !empty($post_data['website']) ? $post_data['website'] : '';
                        $assign_staff_id = $this->leads_model->automatic_assign_staff('', $lead_type, '', $facebook_lead_name);
                        $status_fb_lead_assign = false;
                        if (!empty($assign_staff_id)) {
                            foreach ($assign_staff_id as $fl) {
                                if (!empty($fl["facebook_lead_name"])) {
                                    $fb_form_name = explode(",", $fl["facebook_lead_name"]);
                                    if (!empty($fb_form_name)) {
                                        foreach ($fb_form_name as $fb_name) {
                                            if (!empty($fb_name) && $status_fb_lead_assign == false) {
                                                if (strpos(strtolower(trim($facebook_lead_name)), strtolower(trim($fb_name))) !== false) {
                                                    $form->responsible = $fl["staffid"];
                                                    $status_fb_lead_assign = true;
                                                }
                                            }
                                            if ($status_fb_lead_assign == true) {
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($status_fb_lead_assign == false) {
                            if (!empty($lead_type)) {
                                $assign_staff_id = $this->leads_model->automatic_assign_staff('', $lead_type, 1);
                                if (!empty($assign_staff_id[0]["staffid"])) {
                                    $form->responsible = $assign_staff_id[0]["staffid"];
                                }
                            }
                        }
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


                if (!empty($post_data['tag_assign'])  && $post_data['tag_assign'] > 0) {
                    $form->responsible = 1; // Default responsible staff ID

                    // Check if the staff is active and exists
                    $check_staff = $this->db->select("staffid")->where('active', 1)
                        ->where('staffid', $post_data['tag_assign'])
                        ->get(db_prefix() . 'staff')->row();

                    // If staff exists and is active, update the responsible staff ID
                    if (!empty($check_staff)) {
                        $form->responsible = $check_staff->staffid;
                    }
                }
                
                
                if($key == "834681a14c5d64a07d1fabcd11a5f9a8"){
                    
                    $auto_assign = array_filter(explode(",", $form->auto_assign));
                    
                   
                    $lead_type = !empty($post_data["type"]) ? trim($post_data["type"]) : '';
                    $state_name = !empty($post_data['state']) ? $post_data['state'] : '';
                    $lead_type = !empty($post_data['type']) ? $post_data['type'] : '';
                    
                     if (!empty($form->state_wise)  && $form->state_wise == 1) {
                    $assign_staff_id = $this->leads_model->automatic_assign_staff($state_name, $lead_type);
                     }
                     else
                     {
                         $assign_staff_id = $this->leads_model->automatic_assign_staff('', $lead_type, '', '', $auto_assign); 
                     }
                    
                     if (!empty($assign_staff_id[0]["staffid"])) {
                        $form->responsible = $assign_staff_id[0]["staffid"];
                    }
                    
                       $this->db->insert(db_prefix() . 'facebook_webhook_data', ['data' => json_encode($post_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),"form_id"=>"sulekha"]);
                }

                if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_lead_form') == 1) {
                    $required[] = 'accept_terms_and_conditions';
                }

                foreach ($required as $field) {
                    if ($field == 'file-input') {
                        continue;
                    }
                    if (!isset($post_data[$field]) || isset($post_data[$field]) && empty($post_data[$field])) {
                        $this->output->set_status_header(422);
                        die;
                    }
                }

                if (show_recaptcha() && $form->recaptcha == 1) {
                    if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => _l('recaptcha_error'),
                        ]);
                        die;
                    }
                }

                if (isset($post_data['g-recaptcha-response'])) {
                    unset($post_data['g-recaptcha-response']);
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


                // if (!empty($call_data)) {
                //     $this->curl_function($call_data);
                // }



                // if ($form->allow_duplicate == 0) {
                //     $where = [];
                //     if (!empty($form->track_duplicate_field) && isset($regular_fields[$form->track_duplicate_field])) {
                //         $where[$form->track_duplicate_field] = $regular_fields[$form->track_duplicate_field];
                //     }
                //     if (!empty($form->track_duplicate_field_and) && isset($regular_fields[$form->track_duplicate_field_and])) {
                //         $where[$form->track_duplicate_field_and] = $regular_fields[$form->track_duplicate_field_and];
                //     }

                //     if (count($where) > 0) {
                //         $total = total_rows(db_prefix() . 'leads', $where);

                //         $duplicateLead = false;
                //         /**
                //          * Check if the lead is only 1 time duplicate
                //          * Because we wont be able to know how user is tracking duplicate and to send the email template for
                //          * the request
                //          */
                //         if ($total == 1) {
                //             $this->db->where($where);
                //             $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();
                //         }

                //         if ($total > 0) {
                //             // Success set to true for the response.
                //             $success      = true;
                //             $insert_to_db = false;

                //             // convert to fresh lead
                //             $this->db->where($where);
                //             $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();
                //             $updateStatus = [

                //                 'status' => $form->lead_status,
                //                 // 'description' => 'Re Query',
                //                 // 'assigned' => $form->responsible,
                //                 'last_status_change' => date("Y-m-d"),
                //                 'lastcontact' => date("Y-m-d h:i:s"),
                //                 'dateassigned' => date("Y-m-d")
                //             ];

                //             if (!empty($post_data["website"])) {
                //                 $updateStatus['website'] = $post_data["website"];
                //             }


                //             $regular_fields = [];
                //             $custom_fields  = [];
                //             foreach ($post_data as $name => $val) {
                //                 if (strpos($name, 'form-cf-') !== false) {
                //                     array_push($custom_fields, [
                //                         'name'  => $name,
                //                         'value' => $val,
                //                     ]);
                //                 }

                //                 $custom_fields_build['leads'] = [];
                //                 foreach ($post_data as $name => $val) {
                //                     // if (!empty($_POST['form-cf-' . MARKETING_SOURCE_ID])) {
                //                     //     $custom_fields_build['leads'][MARKETING_SOURCE_ID] = !empty($_POST['form-cf-' . MARKETING_SOURCE_ID]) ? $_POST['form-cf-' . MARKETING_SOURCE_ID] : "";
                //                     // }

                //                     // if (!empty($_POST['form-cf-' . CALL_TYPE_ID])) {
                //                     //     $custom_fields_build['leads'][CALL_TYPE_ID] = !empty($_POST['form-cf-' . CALL_TYPE_ID]) ? $_POST['form-cf-' . CALL_TYPE_ID] : "";
                //                     // }
                //                     // update web history json 
                //                     if (!empty($_POST['form-cf-' . WEB_HISTORY_ID])) {
                //                         $web_activity_log_data = $this->db->select("value")->where(array("fieldid" => WEB_HISTORY_ID, "fieldto" => "leads", "relid" => $duplicateLead->id))->get(db_prefix() . "customfieldsvalues")->row_array();



                //                         if (empty($web_activity_log_data)) {
                //                             $custom_fields_build['leads'][WEB_HISTORY_ID] = !empty($_POST['form-cf-' . WEB_HISTORY_ID]) ? $_POST['form-cf-' . WEB_HISTORY_ID] : "";
                //                         } else {
                //                             $custom_fields_build['leads'][WEB_HISTORY_ID] = $web_activity_log_data["value"] . "," . (!empty($_POST['form-cf-' . WEB_HISTORY_ID]) ? $_POST['form-cf-' . WEB_HISTORY_ID] : "");
                //                         }
                //                     }
                //                 }
                //             }




                //             if (!empty($custom_fields_build['leads'])) {
                //                 handle_custom_fields_post($duplicateLead->id, $custom_fields_build);
                //             }

                //             if (!empty($form->lead_source)) {
                //                 $source_data_get = $this->leads_model->get_source($duplicateLead->source);
                //                 if (!empty($source_data_get->fixed_source) && $source_data_get->fixed_source == 1) {
                //                 } else {
                //                     $updateStatus['source'] = $form->lead_source;
                //                 }
                //             }

                //             if (!empty($updateStatus['source'])) {
                //                 $this->leads_model->update_lead_source($updateStatus['source'], $duplicateLead->id);
                //             }



                //             if ($post_data['callassignee'] != null) {
                //                 $updateStatus["assigned"] = $form->responsible;
                //             }

                //             // $updateStatus["assigned"] = 1;


                //             if (!empty($form->assign_previous_lead_alert) && $form->assign_previous_lead_alert == 1) {
                //                 if (!empty($updateStatus["assigned"]) && !empty($duplicateLead->assigned) && $duplicateLead->assigned != $updateStatus["assigned"]) {
                //                     $notifiedUsers = [];
                //                     $notified = add_notification([
                //                         'description'     => 'lead_assign_previous_lead',
                //                         'touserid'        => $duplicateLead->assigned,
                //                         'fromcompany'     => 1,
                //                         'fromuserid'      => null,
                //                         'additional_data' => serialize([
                //                             $duplicateLead->name,
                //                             // !empty($this->leads_model->get_source($duplicateLead->source)->name) ? $this->leads_model->get_source($duplicateLead->source)->name : '',
                //                             get_staff_full_name($updateStatus["assigned"])
                //                         ])
                //                     ]);
                //                     if ($notified) {
                //                         array_push($notifiedUsers, $duplicateLead->assigned);
                //                     }
                //                     pusher_trigger_notification($notifiedUsers);
                //                     $this->leads_model->log_lead_activity($duplicateLead->id, 'lead_assign_previous_lead', true, serialize([
                //                         $duplicateLead->name,
                //                         // !empty($this->leads_model->get_source($duplicateLead->source)->name) ? $this->leads_model->get_source($duplicateLead->source)->name : '',
                //                         get_staff_full_name($updateStatus["assigned"])
                //                     ]));
                //                 }
                //             }
                //             $this->db->where('id', $duplicateLead->id);
                //             $this->db->update(db_prefix() . 'leads', $updateStatus);


                //             $notifiedUsers = [];
                //             $notified = add_notification([
                //                 'description'     => 'not_lead_imported_from_form',
                //                 'touserid'        => $form->responsible,
                //                 'fromcompany'     => 1,
                //                 'fromuserid'      => null,
                //                 'additional_data' => serialize([
                //                     $form->name,
                //                 ]),
                //                 'link' => '#leadid=' . $duplicateLead->id,
                //             ]);
                //             if ($notified) {
                //                 array_push($notifiedUsers, $form->responsible);
                //             }

                //             pusher_trigger_notification($notifiedUsers);
                //             $this->leads_model->log_lead_activity($duplicateLead->id, 'not_lead_imported_from_form', true, serialize([
                //                 $form->name,
                //             ]));
                //             hooks()->do_action('web_to_lead_form_submitted', [
                //                 'lead_id' => $duplicateLead->id,
                //                 'form_id' => $form->id,
                //                 'task_id' => 0,
                //             ]);

                //             //end convert to specified status

                //             if ($form->create_task_on_duplicate == 1) {
                //                 $task_name_from_form_name = false;
                //                 $task_name                = '';
                //                 if (isset($regular_fields['name'])) {
                //                     $task_name = $regular_fields['name'];
                //                 } elseif (isset($regular_fields['email'])) {
                //                     $task_name = $regular_fields['email'];
                //                 } elseif (isset($regular_fields['company'])) {
                //                     $task_name = $regular_fields['company'];
                //                 } else {
                //                     $task_name_from_form_name = true;
                //                     $task_name                = $form->name;
                //                 }
                //                 if ($task_name_from_form_name == false) {
                //                     $task_name .= ' - ' . $form->name;
                //                 }

                //                 $description          = '';
                //                 $custom_fields_parsed = [];
                //                 foreach ($custom_fields as $key => $field) {
                //                     $custom_fields_parsed[$field['name']] = $field['value'];
                //                 }

                //                 $all_fields    = array_merge($regular_fields, $custom_fields_parsed);
                //                 $fields_labels = [];
                //                 foreach ($data['form_fields'] as $f) {
                //                     if ($f->type != 'header' && $f->type != 'paragraph' && $f->type != 'file') {
                //                         $fields_labels[$f->name] = $f->label;
                //                     }
                //                 }

                //                 $description .= $form->name . '<br /><br />';
                //                 foreach ($all_fields as $name => $val) {
                //                     if (isset($fields_labels[$name])) {
                //                         if ($name == 'country' && is_numeric($val)) {
                //                             $c = get_country($val);
                //                             if ($c) {
                //                                 $val = $c->short_name;
                //                             } else {
                //                                 $val = 'Unknown';
                //                             }
                //                         }

                //                         $description .= $fields_labels[$name] . ': ' . $val . '<br />';
                //                     }
                //                 }

                //                 $task_data = [
                //                     'name'        => $task_name,
                //                     'priority'    => get_option('default_task_priority'),
                //                     'dateadded'   => date('Y-m-d H:i:s'),
                //                     'startdate'   => date('Y-m-d'),
                //                     'addedfrom'   => $form->responsible,
                //                     'status'      => 1,
                //                     'description' => $description,
                //                 ];

                //                 $task_data = hooks()->apply_filters('before_add_task', $task_data);
                //                 $this->db->insert(db_prefix() . 'tasks', $task_data);
                //                 $task_id = $this->db->insert_id();
                //                 if ($task_id) {
                //                     $attachment = handle_task_attachments_array($task_id, 'file-input');

                //                     if ($attachment && count($attachment) > 0) {
                //                         $this->tasks_model->add_attachment_to_database($task_id, $attachment, false, false);
                //                     }

                //                     $assignee_data = [
                //                         'taskid'   => $task_id,
                //                         'assignee' => $form->responsible,
                //                     ];
                //                     $this->tasks_model->add_task_assignees($assignee_data, true);

                //                     hooks()->do_action('after_add_task', $task_id);
                //                     if ($duplicateLead && $duplicateLead->email != '') {
                //                         send_mail_template('lead_web_form_submitted', $duplicateLead);
                //                     }
                //                 }
                //             }
                //         }
                //     }
                // }

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
                        /**
                         * Check if the lead is only 1 time duplicate
                         * Because we wont be able to know how user is tracking duplicate and to send the email template for
                         * the request
                         */
                        // if ($total == 1) {
                        //     if(!empty($where)){
                        //     $this->db->where($where);
                        //     }

                        //     $this->db->group_start();
                        //     $this->db->or_where($where_or);
                        //     $this->db->group_end();
                        //     $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();
                        // }

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



                            // if ($duplicateLead->status == 1) {
                            // echo json_encode(['success' => true, 'message' => "Leads Status is Custumer so data is not update"]);
                            // die;
                            // }
                                
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



                            if ($generate_lead_transfer_request && $generate_lead_transfer_request == 1) {

                                if (!empty($call_data)) {
                                    $response_call = $this->curl_function($call_data);
                                    $response_call = json_decode($response_call);
                                    if (isset($response_call[0]->status) && $response_call[0]->status == 0) {
                                        echo json_encode([
                                            'success' => 0,
                                            'message' => $response_call[0]->message
                                        ]);
                                    }
                                }
                                
                                $updateStatus_dup = [];
                                
                                              $updateStatus_dup['upcomming_date'] = date('Y-m-d H:i:s');
$updateStatus_dup['upcomming_count'] = ($duplicateLead->upcomming_count ?? 1) + 1;
                            $this->db->where('id', $duplicateLead->id);
                            $this->db->update(db_prefix() . 'leads', $updateStatus_dup);
                            
                            
                                if ($form->responsible == $duplicateLead->assigned) {
                                    echo json_encode(['success' => true, 'message' => "Lead Transfer Request Generate successfully"]);
                                    die;
                                }
                                $generate_lead_transfer_request_array = [];
                                $generate_lead_transfer_request_array["lead_id"] = $duplicateLead->id;
                                $generate_lead_transfer_request_array["transfer_lead_type"] = $lead_type;
                                $generate_lead_transfer_request_array["transfer_source_type"] = !empty($updateStatus['source']) ? $updateStatus['source'] : $duplicateLead->source;
                                $generate_lead_transfer_request_array["transfer_lead_assign"] = !empty($form->responsible) ? $form->responsible : 1;
                                $generate_lead_transfer_request_array["reason"] = "Automatic Lead transfer request.";
                                $generate_lead_transfer_request_array["auto_genrate_lead_transfer"] = 1;




                                if (!empty($generate_lead_transfer_request_array)) {

                                    $response_trnasferRequest = $this->add_lead_transfer_request($generate_lead_transfer_request_array, $duplicateLead->assigned);


                                    if (isset($response_trnasferRequest["success"]) && $response_trnasferRequest["success"] == 0) {
                                        echo json_encode([
                                            'success' => 0,
                                            'message' => $response_trnasferRequest["message"]
                                        ]);
                                        die;
                                    } else {
                                        echo json_encode([
                                            'success' => $success,
                                            'message' => $form->success_submit_msg,
                                            'redirect_url' => false,
                                        ]);
                                        die;
                                    }
                                }
                            }
                            
                            
                                $updateStatus = [
                                'status' => 33??$form->lead_status,
                                // 'description' => 'Re Query',
                                // 'assigned' => $form->responsible,
                                'last_status_change' => date("Y-m-d"),
                                // 'lastcontact' => date("Y-m-d H:i:s"),
                                // 'dateassigned' => date("Y-m-d H:i:s"),
                                ];
                                                        

    
                            $statusChecker = $this->leads_model->update_lead_status(array("status"=>33,"leadid"=>$duplicateLead->id));
 

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
                                    // if (!empty($_POST['form-cf-' . MARKETING_SOURCE_ID])) {
                                    //     $custom_fields_build['leads'][MARKETING_SOURCE_ID] = !empty($_POST['form-cf-' . MARKETING_SOURCE_ID]) ? $_POST['form-cf-' . MARKETING_SOURCE_ID] : "";
                                    // }

                                    // if (!empty($_POST['form-cf-' . CALL_TYPE_ID])) {
                                    //     $custom_fields_build['leads'][CALL_TYPE_ID] = !empty($_POST['form-cf-' . CALL_TYPE_ID]) ? $_POST['form-cf-' . CALL_TYPE_ID] : "";
                                    // }
                                    // update web history json 
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



                            if ($post_data['callassignee'] != null) {
                                $updateStatus["assigned"] = $form->responsible;
                            }

                            // $updateStatus["assigned"] = 1;


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
                            
                            
                           $statusActivity =  $this->leads_model->update_lead_status(array("status"=>33,"leadid"=>$duplicateLead->id));
                           
             
                            
                            $updateStatus['upcomming_date'] = date('Y-m-d H:i:s');
                            $updateStatus['upcomming_count'] = ($duplicateLead->upcomming_count ?? 1) + 1;
                            
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

                    //  if ($key == "de34ba611f3853dc13f2596a4ba992ac") {
                    // $regular_fields['type']       = 1;

                    // }

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
                           
if(!empty($post_data["phonenumber"]) && !empty($lead_id)) {
    $data = [
        'phonenumber'   => $post_data["phonenumber"],
        'responsible'   => $form->responsible,
        'lead_id'       => $lead_id,
        'channel_type'  => 8,
        'status'        => 2,
        'cron_time'     => date('Y-m-d H:i:s', strtotime('+10 minute')),
        'created_at'    => date('Y-m-d H:i:s')
    ];

    $this->db->insert(db_prefix().'whatsapp_messages_channel', $data);

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

                        $this->leads_model->lead_assigned_member_notification($lead_id, $form->responsible, true);

                        handle_lead_attachments($lead_id, 'file-input', $form->name);
                        if (!empty($post_data['tags'])) {
                            $tags = $post_data['tags'];
                            handle_tags_save($tags, $lead_id, 'lead');
                        }

                        if ($form->notify_lead_imported != 0) {
                            if ($form->notify_type == 'assigned') {
                                $to_responsible = true;
                            } else {
                                $ids            = @unserialize($form->notify_ids);
                                $to_responsible = false;
                                if ($form->notify_type == 'specific_staff') {
                                    $field = 'staffid';
                                } elseif ($form->notify_type == 'roles') {
                                    $field = 'role';
                                }
                            }

                            if ($to_responsible == false && is_array($ids) && count($ids) > 0) {
                                $this->db->where('active', 1);
                                $this->db->where_in($field, $ids);
                                $staff = $this->db->get(db_prefix() . 'staff')->result_array();
                            } else {
                                $staff = [
                                    [
                                        'staffid' => $form->responsible,
                                    ],
                                ];
                            }
                            $notifiedUsers = [];
                            foreach ($staff as $member) {
                                if ($member['staffid'] != 0) {
                                    $notified = add_notification([
                                        'description'     => 'not_lead_imported_from_form',
                                        'touserid'        => $member['staffid'],
                                        'fromcompany'     => 1,
                                        'fromuserid'      => null,
                                        'additional_data' => serialize([
                                            $form->name,
                                        ]),
                                        'link' => '#leadid=' . $lead_id,
                                    ]);
                                    if ($notified) {
                                        array_push($notifiedUsers, $member['staffid']);
                                    }
                                }
                            }
                            pusher_trigger_notification($notifiedUsers);
                        }
                        if (isset($regular_fields['email']) && $regular_fields['email'] != '') {
                            $lead = $this->leads_model->get($lead_id);
                            send_mail_template('lead_web_form_submitted', $lead);
                        }
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


                if (!empty($call_data)) {
                    $response_call = $this->curl_function($call_data);
                    $response_call = json_decode($response_call);
                    if (isset($response_call[0]->status) && $response_call[0]->status == 0) {
                        echo json_encode([
                            'success' => 0,
                            'message' => $response_call[0]->message
                        ]);
                        die;
                    }
                }
                $redirect_url = false;
                echo json_encode([
                    'success' => $success,
                    'message' => $form->success_submit_msg,
                    'redirect_url' => $redirect_url,
                ]);

                return true;
            }
        }

        $data['form'] = $form;
        $this->load->view('forms/web_to_lead', $data);
    }


    private function add_lead_transfer_request($postData, $raised_by)
    {
        $this->load->model('leads_model');

        try {
            // Validate required fields
            if (empty($postData['lead_id'])) {
                throw new Exception('Lead ID is required.');
            }

            // Extract values from $postData
            $lead_id = $postData['lead_id'];
            $type = $postData['transfer_lead_type'] ?? null;
            $assigned = $postData['transfer_lead_assign'] ?? null;
            $reason = $postData['reason'] ?? null;
            $source = $postData['transfer_source_type'] ?? null;
            $auto_genrate_lead_transfer = !empty($postData['auto_genrate_lead_transfer']) ? 1 : 0;

            // Check if a lead transfer request already exists
            $check_lead_transfer_request = $this->leads_model->get_lead_transfer_request_exist($lead_id);

            // Prepare lead transfer request data
            $data = [];
            if (!empty($type)) {
                $data["lead_type"] = $type;
            }
            if (!empty($assigned)) {
                $data["assign"] = $assigned;
            }
            if (!empty($reason)) {
                $data["reason"] = $reason;
            }
            if (!empty($source)) {
                $data["lead_source"] = $source;
            }


            // If a request already exists OR auto-generate is enabled
            if (!empty($check_lead_transfer_request->id) || $auto_genrate_lead_transfer) {
                // Delete existing lead transfer request (if any)
                if (!empty($check_lead_transfer_request->id)) {
                    $this->db->where('id', $check_lead_transfer_request->id);
                    $this->db->delete(db_prefix() . 'lead_transfer_request');
                }


                // handle automatic lead transfer to admin 
                $update_array = [
                    'assigned' => IVR_AUTO_ASIGNATION,
                    // "status" => 2
                ];
                $success = $this->leads_model->update_leads($update_array, $lead_id);
                $raised_by = IVR_AUTO_ASIGNATION;

                // Add new lead transfer request
                $data = array_merge($data, [
                    "leadid" => $lead_id,
                    "status" => 3,
                    "automatic" => 1,
                    "created_by" => !empty($raised_by) ? $raised_by : 1,
                    "created_at" => date('Y-m-d H:i:s')
                ]);

                $insert_ = $this->db->insert(db_prefix() . 'lead_transfer_request', $data);

                if (!$insert_) {
                    throw new Exception("Failed to submit lead transfer request.");
                }

                return [
                    'success' => true,
                    'message' => "Lead transfer request submitted successfully.",
                    'lead_id' => $lead_id
                ];
            }

            // If no existing request & auto-generate is not enabled, return failure
            return [
                'success' => false,
                'message' => "No existing lead transfer request found, and auto-generate is disabled.",
                'lead_id' => $lead_id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function l($hash)
    {
        if (get_option('gdpr_enable_lead_public_form') == '0') {
            show_404();
        }
        $this->load->model('leads_model');
        $this->load->model('gdpr_model');
        $lead = $this->leads_model->get('', ['hash' => $hash]);

        if (!$lead || count($lead) > 1) {
            show_404();
        }

        $lead = array_to_object($lead[0]);
        load_lead_language($lead->id);

        if ($this->input->post('update')) {
            $data = $this->input->post();
            unset($data['update']);
            $this->leads_model->update($data, $lead->id);
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($this->input->post('export') && get_option('gdpr_data_portability_leads') == '1') {
            $this->load->library('gdpr/gdpr_lead');
            $this->gdpr_lead->export($lead->id);
        } elseif ($this->input->post('removal_request')) {
            $success = $this->gdpr_model->add_removal_request([
                'description'  => nl2br($this->input->post('removal_description')),
                'request_from' => $lead->name,
                'lead_id'      => $lead->id,
            ]);
            if ($success) {
                send_gdpr_email_template('gdpr_removal_request_by_lead', $lead->id);
                set_alert('success', _l('data_removal_request_sent'));
            }
            redirect($_SERVER['HTTP_REFERER']);
        }

        $lead->attachments = $this->leads_model->get_lead_attachments($lead->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['title'] = $lead->name;
        $data['lead']  = $lead;
        $this->view('forms/lead');
        $this->data($data);
        $this->layout(true);
    }

    public function public_ticket($key)
    {
        $this->load->model('tickets_model');

        if (strlen($key) != 32) {
            show_error('Invalid ticket key.');
        }

        $ticket = $this->tickets_model->get_ticket_by_id($key);

        if (!$ticket) {
            show_404();
        }

        if (!is_client_logged_in() && $ticket->userid) {
            load_client_language($ticket->userid);
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('message', _l('ticket_reply'), 'required');

            if ($this->form_validation->run() !== false) {
                $replyData = ['message' => $this->input->post('message')];

                if ($ticket->userid && $ticket->contactid) {
                    $replyData['userid']    = $ticket->userid;
                    $replyData['contactid'] = $ticket->contactid;
                } else {
                    $replyData['name']  = $ticket->from_name;
                    $replyData['email'] = $ticket->ticket_email;
                }

                $replyid = $this->tickets_model->add_reply($replyData, $ticket->ticketid);

                if ($replyid) {
                    set_alert('success', _l('replied_to_ticket_successfully', $ticket->ticketid));
                }

                redirect(get_ticket_public_url($ticket));
            }
        }

        $data['title']          = $ticket->subject;
        $data['ticket_replies'] = $this->tickets_model->get_ticket_replies($ticket->ticketid);
        $data['ticket']         = $ticket;
        hooks()->add_action('app_customers_footer', 'ticket_public_form_customers_footer');
        $data['single_ticket_view'] = $this->load->view($this->createThemeViewPath('single_ticket'), $data, true);

        $navigationDisabled = hooks()->apply_filters('disable_navigation_on_public_ticket_view', true);
        if ($navigationDisabled) {
            $this->disableNavigation();
        }

        $this->disableSubMenu();

        $this->data($data);

        $this->view('forms/public_ticket');
        no_index_customers_area();
        $this->layout(true);
    }

    public function ticket()
    {
        $form            = new stdClass();
        $form->language  = get_option('active_language');
        $form->recaptcha = 1;

        $this->lang->load($form->language . '_lang', $form->language);
        if (file_exists(APPPATH . 'language/' . $form->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $form->language);
        }

        $form->success_submit_msg = _l('success_submit_msg');

        $form = hooks()->apply_filters('ticket_form_settings', $form);

        if ($this->input->post() && $this->input->is_ajax_request()) {
            $post_data = $this->input->post();

            $required = ['subject', 'department', 'email', 'name', 'message', 'priority'];

            if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_ticket_form') == 1) {
                $required[] = 'accept_terms_and_conditions';
            }

            foreach ($required as $field) {
                if (!isset($post_data[$field]) || isset($post_data[$field]) && empty($post_data[$field])) {
                    $this->output->set_status_header(422);
                    die;
                }
            }

            if (show_recaptcha() && $form->recaptcha == 1) {
                if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('recaptcha_error'),
                    ]);
                    die;
                }
            }

            $post_data = [
                'email'      => $post_data['email'],
                'name'       => $post_data['name'],
                'subject'    => $post_data['subject'],
                'department' => $post_data['department'],
                'priority'   => $post_data['priority'],
                'service'    => isset($post_data['service']) && is_numeric($post_data['service'])
                    ? $post_data['service']
                    : null,
                'custom_fields' => isset($post_data['custom_fields']) && is_array($post_data['custom_fields'])
                    ? $post_data['custom_fields']
                    : [],
                'message' => $post_data['message'],
            ];

            $success = false;

            $this->db->where('email', $post_data['email']);
            $result = $this->db->get(db_prefix() . 'contacts')->row();

            if ($result) {
                $post_data['userid']    = $result->userid;
                $post_data['contactid'] = $result->id;
                unset($post_data['email']);
                unset($post_data['name']);
            }

            $this->load->model('tickets_model');

            $post_data = hooks()->apply_filters('ticket_external_form_insert_data', $post_data);
            $ticket_id = $this->tickets_model->add($post_data);

            if ($ticket_id) {
                $success = true;
            }

            if ($success == true) {
                hooks()->do_action('ticket_form_submitted', [
                    'ticket_id' => $ticket_id,
                ]);
            }

            echo json_encode([
                'success' => $success,
                'message' => $form->success_submit_msg,
            ]);

            die;
        }

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $data['priorities']  = $this->tickets_model->get_priority();

        $data['priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['services']                         = $this->tickets_model->get_service();

        $data['form'] = $form;
        $this->load->view('forms/ticket', $data);
    }

    private function curl_function($post_data)
    {
        $data = array("call_data" => json_encode($post_data));
        try {
            $token = JWT_TOKEN;
            // header('Content-Type: application/json'); // Specify the type of data
            $ch = curl_init(base_url("external/call_update")); // Initialise cURL
            $authorization = "Authorization: Bearer " . $token; // Prepare the authorization token
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization)); // Inject the token into the header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Set the posted fields
            // Disable SSL certificate validation for a local server
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
            $result = curl_exec($ch); // Execute the cURL statement
            curl_close($ch); // Close the cURL connection

            $result = array(json_decode($result, true));
        } catch (Exception $e) {
            $result = array(array(
                "status" => 0,
                "message" => "something bad happen"
            ));
        }

        return json_encode($result);
    }

    public function test_db_connection()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $response = $this->db->select("*")->from(db_prefix() . "leads")->limit(500)->get()->result_array();
        echo $this->db->last_query();
        echo json_encode($response, true);
        error_reporting(E_ALL & ~E_NOTICE); // Or the appropriate level
        ini_set('display_errors', 0); // Set to 0 for production
    }
    public function test_code()
    {
        echo json_encode(array("success" => 1, "message" => "Run Successfully"));
    }
}
