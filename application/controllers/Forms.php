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

    /**
     * Web to lead form
     * User no need to see anything like LEAD in the url, this is the reason the method is named wtl
     * @param  string $key web to lead form key identifier
     * @return mixed
     */

    public function wtl($key)
    {
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
        // Change the locale so the validation loader function can load
        // the proper localization file
        $GLOBALS['locale'] = get_locale_key($form->language);

        $data['form_fields'] = json_decode($form->form_data);
        if (!$data['form_fields']) {
            $data['form_fields'] = [];
        }
        if ($this->input->post('key')) {
            if ($this->input->post('key') == $key) {
                $post_data = $this->input->post();
                $post_data["phonenumber"] = !empty($post_data["phonenumber"]) ? substr(trim($post_data["phonenumber"]), -10) : '';
                $post_data["phonenumber"] = str_replace("+91", "", $post_data["phonenumber"]);
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
                    $lead_type = !empty($post_data["type"]) ? trim($post_data["type"]) : '';

                    if (!empty($state_name) && !empty($lead_type)) {
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
                                // if (strpos(strtolower(trim($facebook_lead_name)), strtolower(trim($assign_staff_id["facebook_lead_name"]))) !== false) {
                                //     $form->responsible = $fl["staffid"];
                                //     $status_fb_lead_assign = true;
                                //     break;
                                // }
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

                    // if (!empty($state_name) && !empty($lead_type)) {
                    //     $assign_staff_id = $this->leads_model->automatic_assign_staff($state_name, $lead_type);
                    //     if (!empty($assign_staff_id[0]["staffid"])) {
                    //         $form->responsible = $assign_staff_id[0]["staffid"];
                    //     }
                    // }

                }

                // if ($key == "de34ba611f3853dc13f2596a4ba992ac") {
                //                                    $assign_staff_id = $this->leads_model->automatic_assign_staff('', '', '', '', [177, 176, 181, 179, 154]);

                //              if (!empty($assign_staff_id[0]["staffid"])) {
                //                $form->responsible = $assign_staff_id[0]["staffid"];
                //          }
                //    }


                if (!empty($form->auto_assign)) {
                    // $lead_type = !empty($form->lead_type) ? $form->lead_type : '';
                    $auto_assign = array_filter(explode(",", $form->auto_assign));
                    $assign_staff_id = $this->leads_model->automatic_assign_staff('', '', '', '', $auto_assign);
                    if (!empty($assign_staff_id[0]["staffid"])) {
                        $form->responsible = $assign_staff_id[0]["staffid"];
                    }
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

                if ($form->allow_duplicate == 0) {
                    $where = [];
                    if (!empty($form->track_duplicate_field) && isset($regular_fields[$form->track_duplicate_field])) {
                        $where[$form->track_duplicate_field] = $regular_fields[$form->track_duplicate_field];
                    }
                    if (!empty($form->track_duplicate_field_and) && isset($regular_fields[$form->track_duplicate_field_and])) {
                        $where[$form->track_duplicate_field_and] = $regular_fields[$form->track_duplicate_field_and];
                    }

                    if (count($where) > 0) {
                        $total = total_rows(db_prefix() . 'leads', $where);

                        $duplicateLead = false;
                        /**
                         * Check if the lead is only 1 time duplicate
                         * Because we wont be able to know how user is tracking duplicate and to send the email template for
                         * the request
                         */
                        if ($total == 1) {
                            $this->db->where($where);
                            $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();
                        }

                        if ($total > 0) {
                            // Success set to true for the response.
                            $success      = true;
                            $insert_to_db = false;

                            // convert to fresh lead
                            $this->db->where($where);
                            $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();
                            $updateStatus = [

                                'status' => $form->lead_status,
                                // 'description' => 'Re Query',
                                // 'assigned' => $form->responsible,
                                'last_status_change' => date("Y-m-d"),
                                'lastcontact' => date("Y-m-d h:i:s"),
                                'dateassigned' => date("Y-m-d")
                            ];

                            if (!empty($form->lead_source)) {
                                $updateStatus['source'] = $form->lead_source;
                            }


                            if ($post_data['callassignee'] != null) {
                                $updateStatus["assigned"] = $form->responsible;
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
                    if ($key == 'de34ba611f3853dc13f2596a4ba992ac') {

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
                        'redirect_url' => 'https://www.affinityeducation.in/RussiaMBBSFees.pdf',
                    ]);
                }
                if ($key == 'de5f4e08f2c0a817663204d26456673e') {
                    $redirect_url = 'https://www.affinityeducation.in/RussiaMBBSFees.pdf';
                }

                // if ($key=='4f4b15c43f022e3dc84abea5e294ecae') {
                //     $redirect_url = 'https://www.affinityeducation.in/tank-you/';
                // }
                else if ($key == 'da7820bb0e381bb3270ec82e2529f41c' or $key == 'bcf660b7e492ecee806e758aeed193ae') {
                    $redirect_url = 'https://www.getadmissioninfo.com/thank-you/';
                } else if ($key == '61fa7a52bd7bc82f5372c92f81b37619') {
                    $redirect_url = 'https://www.getadmissioninfo.com/btech/thankyou.html';
                } else if ($key == '18ff6be6a5a40b33fa37e1dfae9a602f') {
                    $redirect_url = 'https://www.crfu.in/thank-you/';
                } else if ($key == '88b27fc010871251f07cd6a6874a2d9b') {
                    $redirect_url = 'https://www.chuvsu.in/thank-you/';
                } else if ($key == 'e9ae7aa962ce4f41b5124034a06ff5c4') {
                    $redirect_url = 'https://www.knmu.in/thank-you/';
                } else if ($key == 'f03e0563eb497c3730bcade0a2112911') {
                    $redirect_url = 'https://www.perpetualdalta.in/thank-you/';
                } else if ($key == 'cab5e3e36baae573612c6713fcd1c14f') {
                    $redirect_url = 'https://www.skmakazakhstan.in/thank-you/';
                } else if ($key == 'c3836d043422092406ae79dd06d6ffca') {
                    $redirect_url = 'https://www.tversmu.in/thank-you/';
                } else if ($key == 'c665263e4c5115eea24c75b2fe6a3933') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/MBBSAbroadBrochure.pdf';
                } else if ($key == '5b260174df04229b5c4ecf2524aa8399') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/RussiaMBBSFees.pdf';
                } else if ($key == '967e3629d1cc69115f302ee770b5ec95') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/UkraineMBBSFees.pdf';
                } else if ($key == '9c49df00bcbe750cfb82591e7d1c06a2') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/PhilippinesMBBSFees.pdf';
                } else if ($key == '64452629d91a41573059d7412abcd083') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/NepalMBBSFees.pdf';
                } else if ($key == '3d9ec1f140fb9e1895445c9ab2f3cb6e') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/KyrgyzstanMBBSFees.pdf';
                } else if ($key == 'e23a55ecf17ce313df3ca177156a7bcc') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/KazakhstanMBBSFees.pdf';
                } else if ($key == '68a4f8db553b9c5806b5f93b60ad8616') {
                    $redirect_url = 'https://www.mbbsadmissionabroad.in/GeorgiaMBBSFees.pdf';
                } else if ($key == '87a2c974fae4454e54d369ee88064f7d') {
                    $redirect_url = false;
                } else {
                    $redirect_url = false;
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $form->success_submit_msg,
                    'redirect_url' => $redirect_url,
                ]);
                //redirect('https://educationvibes.in');
                //die;
                return true;
            }
        }

        $data['form'] = $form;
        $this->load->view('forms/web_to_lead', $data);
    }
    /**
     * Web to lead form
     * User no need to see anything like LEAD in the url, this is the reason the method is named eq lead
     * @param  string $hash lead unique identifier
     * @return mixed
     */
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




    // private function curl_function($post_data)
    // {
    //     $data = array("call_data" => json_encode($post_data));
    //     try {
    //         $token = JWT_TOKEN;
    //         header('Content-Type: application/json'); // Specify the type of data
    //         $ch = curl_init(base_url("external/call_update")); // Initialise cURL
    //         $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization)); // Inject the token into the header
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Set the posted fields
    //         // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    //         $result = curl_exec($ch); // Execute the cURL statement
    //         curl_close($ch); // Close the cURL connection
    //     } catch (Exception $e) {
    //         return true;
    //     }
    // }

//     private function curl_function($post_data)
// {
//     $data = array("call_data" => json_encode($post_data));
    
//     try {
//         $token = JWT_TOKEN;
//         $url = base_url("external/call_update");
        
//         $ch = curl_init($url);
        
//         if ($ch === false) {
//             throw new Exception('Failed to initialize cURL');
//         }

//         $authorization = "Authorization: Bearer " . $token;
//         $headers = array('Content-Type: application/json', $authorization);
        
//         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

//         $result = curl_exec($ch);

//         if ($result === false) {
//             throw new Exception('cURL error: ' . curl_error($ch));
//         }

//         $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
//         if ($http_code !== 200) {
//             throw new Exception('HTTP error: ' . $http_code);
//         }

//         curl_close($ch);
        
//         // Return the result or handle it as needed.
//         return $result;
//     } catch (Exception $e) {
//         // Handle the error here, e.g., log the error message or take appropriate action.
//         // You can also echo or return the error message for debugging purposes.
//         echo "Error: " . $e->getMessage();
//         return false;
//     }
// }


    private function curl_function($post_data)
    {
        $data = array("call_data" => json_encode($post_data));
        try {
            $token = JWT_TOKEN;
            header('Content-Type: application/json'); // Specify the type of data
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
        $response = $this->db->select("*")->from(db_prefix() . "leads")->get()->result_array();
        echo $this->db->last_query();
        echo json_encode($response,true);
    }
    public function test_code()
    {
        echo json_encode(array("success" => 1, "message" => "Run Successfully"));
    }

}
