<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Leads_model extends App_Model

{

    public function __construct()

    {

        parent::__construct();
    }



    /**

     * Get lead

     * @param  string $id Optional - leadid

     * @return mixed

     */

    public function get($id = '', $where = [])

    {

        $this->db->select('*,' . db_prefix() . 'leads.name, ' . db_prefix() . 'leads.id,' . db_prefix() . 'leads_status.name as status_name,' . db_prefix() . 'leads_sources.name as source_name,' . db_prefix() . 'leads_type.name as type_name');

        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id=' . db_prefix() . 'leads.status', 'left');

        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id=' . db_prefix() . 'leads.source', 'left');

        $this->db->join(db_prefix() . 'leads_type', db_prefix() . 'leads_type.id=' . db_prefix() . 'leads.type', 'left');

        $this->db->where($where);

        if (is_numeric($id)) {

            $this->db->where(db_prefix() . 'leads.id', $id);

            $lead = $this->db->get(db_prefix() . 'leads')->row();

            if ($lead) {

                if ($lead->from_form_id != 0) {

                    $lead->form_data = $this->get_form([

                        'id' => $lead->from_form_id,

                    ]);
                }

                $lead->attachments = $this->get_lead_attachments($id);

                $lead->public_url  = leads_public_url($id);
            }



            return $lead;
        }



        return $this->db->get(db_prefix() . 'leads')->result_array();
    }

    public function get_customfieldsvalues($fid)
    {
        $this->db->select('DISTINCT(`value`)');
        $this->db->where('fieldid', $fid);
        $consents = $this->db->get(db_prefix() . 'customfieldsvalues')->result_array();

        return $consents;
    }


    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)

    {

        $limit                          = get_option('leads_kanban_limit');

        $default_leads_kanban_sort      = get_option('default_leads_kanban_sort');

        $default_leads_kanban_sort_type = get_option('default_leads_kanban_sort_type');

        $has_permission_view            = has_permission('leads', '', 'view');



        $this->db->select(db_prefix() . 'leads.title, ' . db_prefix() . 'leads.website, ' . db_prefix() . 'leads.lead_value, ' . db_prefix() . 'leads.address, ' . db_prefix() . 'leads.city, ' . db_prefix() . 'leads.state, ' . db_prefix() . 'leads.country, ' . db_prefix() . 'leads.zip, ' . db_prefix() . 'leads.name as lead_name,' . db_prefix() . 'leads_sources.name as source_name,' . db_prefix() . 'leads.id as id,' . db_prefix() . 'leads.assigned,' . db_prefix() . 'leads.email,' . db_prefix() . 'leads.phonenumber,' . db_prefix() . 'leads.company,' . db_prefix() . 'leads.dateadded,' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.lastcontact,(SELECT COUNT(*) FROM ' . db_prefix() . 'clients WHERE leadid=' . db_prefix() . 'leads.id) as is_lead_client, (SELECT COUNT(id) FROM ' . db_prefix() . 'files WHERE rel_id=' . db_prefix() . 'leads.id AND rel_type="lead") as total_files, (SELECT COUNT(id) FROM ' . db_prefix() . 'notes WHERE rel_id=' . db_prefix() . 'leads.id AND rel_type="lead") as total_notes,(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC) as tags');

        $this->db->from(db_prefix() . 'leads');

        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id=' . db_prefix() . 'leads.source');

        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'leads.assigned', 'left');

        $this->db->where('status', $status);

        if (!$has_permission_view) {

            $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');
        }

        if ($search != '') {

            if (!startsWith($search, '#')) {

                $this->db->where('(' . db_prefix() . 'leads.name LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\' OR ' . db_prefix() . 'leads_sources.name LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\' OR ' . db_prefix() . 'leads.email LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\' OR ' . db_prefix() . 'leads.phonenumber LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\' OR ' . db_prefix() . 'leads.company LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\' OR CONCAT(' . db_prefix() . 'staff.firstname, \' \', ' . db_prefix() . 'staff.lastname) LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\')');
            } else {

                $this->db->where(db_prefix() . 'leads.id IN

                (SELECT rel_id FROM ' . db_prefix() . 'taggables WHERE tag_id IN

                (SELECT id FROM ' . db_prefix() . 'tags WHERE name="' . $this->db->escape_str(strafter($search, '#')) . '")

                AND ' . db_prefix() . 'taggables.rel_type=\'lead\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)

                ');
            }
        }



        if (isset($sort['sort_by']) && $sort['sort_by'] && isset($sort['sort']) && $sort['sort']) {

            $this->db->order_by($sort['sort_by'], $sort['sort']);
        } else {

            $this->db->order_by($default_leads_kanban_sort, $default_leads_kanban_sort_type);
        }



        if ($count == false) {

            if ($page > 1) {

                $page--;

                $position = ($page * $limit);

                $this->db->limit($limit, $position);
            } else {

                $this->db->limit($limit);
            }
        }



        if ($count == false) {

            return $this->db->get()->result_array();
        }



        return $this->db->count_all_results();
    }



    /**

     * Add new lead to database

     * @param mixed $data lead data

     * @return mixed false || leadid

     */

    public function add($data, $status = 0, $delete_created = 0)

    {


        if (isset($data['custom_contact_date']) || isset($data['custom_contact_date'])) {

            if (isset($data['contacted_today'])) {

                // $data['lastcontact'] = date('Y-m-d H:i:s');
                $data['lastcontact'] = null;


                unset($data['contacted_today']);
            } else {

                // $data['lastcontact'] = to_sql_date($data['custom_contact_date'], true);
                $data['lastcontact'] = null;
            }
        }



        if (isset($data['is_public']) && ($data['is_public'] == 1 || $data['is_public'] === 'on')) {

            $data['is_public'] = 1;
        } else {

            $data['is_public'] = 0;
        }



        if (!isset($data['country']) || isset($data['country']) && $data['country'] == '') {

            $data['country'] = 0;
        }



        if (isset($data['custom_contact_date'])) {

            unset($data['custom_contact_date']);
        }



        $data['description'] = nl2br($data['description']);



        $data['dateadded']   = !empty($data['dateadded']) ? $data['dateadded'] : date('Y-m-d H:i:s');


        $data['addedfrom']   = get_staff_user_id();
        $data['exam_details']   = [];



        $data = hooks()->apply_filters('before_lead_added', $data);



        $tags = '';

        if (isset($data['tags'])) {

            $tags = $data['tags'];

            unset($data['tags']);
        }

        if (!empty($data['exam_name']) && count($data['exam_name']) > 0) {
            $data["exam_details"] = [];
            foreach ($data['exam_name'] as $key => $exam_d) {

                if (!empty($data["exam_name"][$key]) &&  !empty($data["exam_score"][$key])) {
                    array_push($data["exam_details"], array("exam_name" => $data["exam_name"][$key], "exam_score" => $data["exam_score"][$key]));
                }
            }

            unset($data['exam_name']);
            unset($data['exam_score']);
        }

        // print_r($data);
        if (!empty($data["exam_details"])) {
            $data["exam_details"]  = json_encode($data["exam_details"], true);
        } else {
            $data["exam_details"]  =  "";
        }

        if (isset($data['custom_fields'])) {

            $custom_fields = $data['custom_fields'];

            unset($data['custom_fields']);
        }

        $data['address'] = trim($data['address']);

        $data['address'] = nl2br($data['address']);



        $data['email'] = trim($data['email']);

        $this->db->insert(db_prefix() . 'leads', $data);


        $insert_id = $this->db->insert_id();


        if ($insert_id) {

            log_activity('New Lead Added [ID: ' . $insert_id . ']');

            $this->log_lead_activity($insert_id, 'not_lead_activity_created');



            handle_tags_save($tags, $insert_id, 'lead');



            if (isset($custom_fields)) {

                handle_custom_fields_post($insert_id, $custom_fields);
            }



            $this->lead_assigned_member_notification($insert_id, $data['assigned'], '', 1);

            hooks()->do_action('lead_created', $insert_id);



            return $insert_id;
        }



        return false;
    }



    public function lead_assigned_member_notification($lead_id, $assigned, $integration = false, $skip = false,$systemGenerated=false)

    {

        if ((!empty($assigned) && $assigned != 0)) {

            if ($integration == false) {

                if ($assigned == get_staff_user_id()) {

                    return false;
                }
            }



            $name = $this->db->select('name')->from(db_prefix() . 'leads')->where('id', $lead_id)->get()->row()->name;



            $notification_data = [

                'description'     => ($integration == false) ? 'not_assigned_lead_to_you' : 'not_lead_assigned_from_form',

                'touserid'        => $assigned,

                'link'            => '#leadid=' . $lead_id,

                'additional_data' => ($integration == false ? serialize([

                    $name,

                ]) : serialize([])),

            ];



            if ($integration != false) {

                $notification_data['fromcompany'] = 1;
            }



            if (add_notification($notification_data)) {

                pusher_trigger_notification([$assigned]);
            }



            $this->db->select('email');

            $this->db->where('staffid', $assigned);

            $email = $this->db->get(db_prefix() . 'staff')->row()->email;


            if ($skip != true || $skip != 1) {
                send_mail_template('lead_assigned', $lead_id, $email);
            }



            $this->db->where('id', $lead_id);

            $this->db->update(db_prefix() . 'leads', [

                'dateassigned' => date('Y-m-d H:i:s'),

            ]);



            $not_additional_data = [

                $systemGenerated==false?get_staff_full_name():"CRM Generated",

                '<a href="' . admin_url('profile/' . $assigned) . '" target="_blank">' . get_staff_full_name($assigned) . '</a>',

            ];



            if ($integration == true) {

                unset($not_additional_data[0]);

                array_values(($not_additional_data));
            }



            $not_additional_data = serialize($not_additional_data);



            $not_desc = ($integration == false ? 'not_lead_activity_assigned_to' : 'not_lead_activity_assigned_from_form');

            $this->log_lead_activity($lead_id, $not_desc, $integration, $not_additional_data);
        }
    }



    /**

     * Update lead

     * @param  array $data lead data

     * @param  mixed $id   leadid

     * @return boolean

     */

    public function update($data, $id)

    {

        $current_lead_data = $this->get($id);

        $current_status    = $this->get_status($current_lead_data->status);
        $data["exam_details"]  =  "";

        if ($current_status) {

            $current_status_id = $current_status->id;

            $current_status    = $current_status->name;
        } else {

            if ($current_lead_data->junk == 1) {

                $current_status = _l('lead_junk');
            } elseif ($current_lead_data->lost == 1) {

                $current_status = _l('lead_lost');
            } else {

                $current_status = '';
            }

            $current_status_id = 0;
        }



        $affectedRows = 0;

        if (isset($data['custom_fields'])) {

            $custom_fields = $data['custom_fields'];

            if (handle_custom_fields_post($id, $custom_fields)) {

                $affectedRows++;
            }

            unset($data['custom_fields']);
        }

        if (!defined('API')) {

            if (isset($data['is_public'])) {

                $data['is_public'] = 1;
            } else {

                $data['is_public'] = 0;
            }



            if (!isset($data['country']) || isset($data['country']) && $data['country'] == '') {

                $data['country'] = 0;
            }



            if (isset($data['description'])) {

                $data['description'] = nl2br($data['description']);
            }

            if (isset($data['reference_name'])) {

                $data['reference_name'] = nl2br($data['reference_name']);
            }
        }



        if (isset($data['lastcontact']) && $data['lastcontact'] == '' || isset($data['lastcontact']) && $data['lastcontact'] == null) {

            $data['lastcontact'] = null;
        } elseif (isset($data['lastcontact'])) {

            $data['lastcontact'] = to_sql_date($data['lastcontact'], true);
        }


        if (!empty($data['exam_name']) && count($data['exam_name']) > 0) {
            $data["exam_details"] = [];
            foreach ($data['exam_name'] as $key => $exam_d) {

                if (!empty($data["exam_name"][$key]) &&  !empty($data["exam_score"][$key])) {
                    array_push($data["exam_details"], array("exam_name" => $data["exam_name"][$key], "exam_score" => $data["exam_score"][$key]));
                }
            }

            unset($data['exam_name']);
            unset($data['exam_score']);
        }

        if (!empty($data["exam_details"])) {
            $data["exam_details"]  = json_encode($data["exam_details"], true);
        } else {
            $data["exam_details"]  =  "";
        }


        if (isset($data['tags'])) {

            if (handle_tags_save($data['tags'], $id, 'lead')) {

                $affectedRows++;
            }

            unset($data['tags']);
        }



        if (isset($data['remove_attachments'])) {

            foreach ($data['remove_attachments'] as $key => $val) {

                $attachment = $this->get_lead_attachments($id, $key);

                if ($attachment) {

                    $this->delete_lead_attachment($attachment->id);
                }
            }

            unset($data['remove_attachments']);
        }



        $data['address'] = trim($data['address']);

        $data['address'] = nl2br($data['address']);



        $data['email'] = trim($data['email']);
        if (!empty($data['source'])  && $current_lead_data->source != $data['source']) {
            $this->update_lead_source($data['source'], $id);
        }
        if (!empty($data['type']) && $current_lead_data->type != $data['type']) {
            $this->update_lead_type(array("type" => $data['type'], "leadid" => $id));
        }
        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads', $data);

        if ($this->db->affected_rows() > 0) {
            // if(is_admin()){
             update_lead_performace_feedback($id);
            // }


            $affectedRows++;



            if (isset($data['status']) && $current_status_id != $data['status']) {

                $this->db->where('id', $id);

                $this->db->update(db_prefix() . 'leads', [

                    'last_status_change' => date('Y-m-d H:i:s'),

                ]);


                $new_status_name = $this->get_status($data['status'])->name;

                $this->log_lead_activity($id, 'not_lead_activity_status_updated', false, serialize([

                    get_staff_full_name(),

                    $current_status,

                    $new_status_name,

                ]));



                hooks()->do_action('lead_status_changed', [

                    'lead_id'    => $id,

                    'old_status' => $current_status_id,

                    'new_status' => $data['status'],

                ]);
            }



            if (($current_lead_data->junk == 1 || $current_lead_data->lost == 1) && $data['status'] != 0) {

                $this->db->where('id', $id);

                $this->db->update(db_prefix() . 'leads', [

                    'junk' => 0,

                    'lost' => 0,

                ]);
            }



            if (isset($data['assigned'])) {

                if ($current_lead_data->assigned != $data['assigned'] && (!empty($data['assigned']) && $data['assigned'] != 0)) {

                    $this->lead_assigned_member_notification($id, $data['assigned']);
                }
            }

            log_activity('Lead Updated [ID: ' . $id . ']');



            return true;
        }

        if ($affectedRows > 0) {

            return true;
        }



        return false;
    }

    public function update_leads($data, $id)

    {
        $current_lead_data = $this->get($id);

        $current_status    = $this->get_status($current_lead_data->status);
        if ($current_status) {

            $current_status_id = $current_status->id;

            $current_status    = $current_status->name;
        } else {

            if ($current_lead_data->junk == 1) {

                $current_status = _l('lead_junk');
            } elseif ($current_lead_data->lost == 1) {

                $current_status = _l('lead_lost');
            } else {

                $current_status = '';
            }

            $current_status_id = 0;
        }


        if (!empty($data['type']) && $current_lead_data->type != $data['type']) {
            $this->update_lead_type(array("type" => $data['type'], "leadid" => $id));
        }
        if (isset($data['assigned'])) {
            if ($current_lead_data->assigned != $data['assigned'] && (!empty($data['assigned']) && $data['assigned'] != 0)) {
                $this->lead_assigned_member_notification($id, $data['assigned']);
            }
        }

        if (isset($data['status']) && $current_status_id != $data['status']) {

            $this->db->where('id', $id);

            $this->db->update(db_prefix() . 'leads', [

                'last_status_change' => date('Y-m-d H:i:s'),

            ]);


            $new_status_name = $this->get_status($data['status'])->name;

            $this->log_lead_activity($id, 'not_lead_activity_status_updated', false, serialize([

                get_staff_full_name(),

                $current_status,

                $new_status_name,

            ]));



            hooks()->do_action('lead_status_changed', [

                'lead_id'    => $id,

                'old_status' => $current_status_id,

                'new_status' => $data['status'],

            ]);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'leads', $data);
        if ($this->db->affected_rows() > 0) {
        // if(is_admin()){
        update_lead_performace_feedback($id);
        // }

            log_activity('Lead Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**

     * Delete lead from database and all connections

     * @param  mixed $id leadid

     * @return boolean

     */


    public function update_lead_source($sourceid, $leadid)
    {
        $current_lead_data = $this->get($leadid);

        if (!$current_lead_data) {
            // Handle the case where lead data is not found
            return false;
        }

        $old_source_data = $this->get_source($current_lead_data->source);
        $new_source_data = $this->get_source($sourceid);

        if (!$old_source_data || !$new_source_data) {
            // Handle the case where source data is not found
            return false;
        }

        if (!empty($current_lead_data->source) && !empty($sourceid) && ($current_lead_data->source != $sourceid)) {
            log_activity('Leads Source Updated [SourceID: ' . $old_source_data->id . ', Name: ' . $new_source_data->id . ']');
            $this->log_lead_activity($leadid, 'not_lead_activity_source_updated', false, serialize([
                get_staff_full_name(),
                $old_source_data->name,
                $new_source_data->name,
            ]));
        } else {
            log_activity('Leads Source Updated [SourceID: ' . $old_source_data->id . ', Name: ' . $new_source_data->id . ']');
            $this->log_lead_activity($leadid, 'not_lead_activity_source_updated', false, serialize([
                get_staff_full_name(),
                $old_source_data->name,
                $new_source_data->name,
            ]));
        }

        return true;
    }



    public function delete($id)

    {

        $affectedRows = 0;



        hooks()->do_action('before_lead_deleted', $id);



        $lead = $this->get($id);



        $this->db->where('id', $id);

        $this->db->delete(db_prefix() . 'leads');

        if ($this->db->affected_rows() > 0) {

            log_activity('Lead Deleted [Deleted by: ' . get_staff_full_name() . ', ID: ' . $id . ']');

            $data_array["phonenumber"] = substr(preg_replace('/\D/', '', $lead->phonenumber), -10);
            $data_array["alternative_phonenumber"] = substr(preg_replace('/\D/', '', $lead->alternative_phonenumber), -10);

            $data_array["email"] = $lead->email;
            $data_array["name"] = $lead->name;
            $data_array["lead_id"] = $lead->id;

            if (!$this->db->insert(db_prefix() . "leads_delete", $data_array)) {
                log_message('error', 'Failed to insert into tblleads_delete: ' . $this->db->last_query());
            }

            $affectedRows++;
        }

        if ($affectedRows > 0) {

            return true;
        }



        return false;
    }

    function hitCronUrlAsync($cronUrl)
    {
        try {
            // Initialize cURL session
            $ch = curl_init();

            // Set cURL options for asynchronous request
            curl_setopt($ch, CURLOPT_URL, $cronUrl); // Set the URL to hit
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Don't return the response
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Timeout for quick execution
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Allow redirects if necessary
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Bypass SSL verification

            // Execute cURL request asynchronously
            curl_exec($ch);

            // Close cURL session
            curl_close($ch);

            // No need to wait for response
            return array("status" => 1, "message" => "Cron URL hit asynchronously.");
        } catch (Exception $e) {
            // Handle exceptions and return an error response
            return array("status" => 0, "message" => $e->getMessage());
        }
    }

    public function delete_leads_information($lead)
    {
        $id = $lead->lead_id;
        $attachments = $this->get_lead_attachments($id);

        foreach ($attachments as $attachment) {

            $this->delete_lead_attachment($attachment['id']);
        }



        // Delete the custom field values

        $this->db->where('relid', $id);

        $this->db->where('fieldto', 'leads');

        $this->db->delete(db_prefix() . 'customfieldsvalues');



        $this->db->where('leadid', $id);

        $this->db->delete(db_prefix() . 'lead_activity_log');



        $this->db->where('leadid', $id);

        $this->db->delete(db_prefix() . 'lead_integration_emails');



        $this->db->where('rel_id', $id);

        $this->db->where('rel_type', 'lead');

        $this->db->delete(db_prefix() . 'notes');



        $this->db->where('rel_type', 'lead');

        $this->db->where('rel_id', $id);

        $this->db->delete(db_prefix() . 'reminders');



        $this->db->where('rel_type', 'lead');

        $this->db->where('rel_id', $id);

        $this->db->delete(db_prefix() . 'taggables');



        $this->load->model('proposals_model');

        $this->db->where('rel_id', $id);

        $this->db->where('rel_type', 'lead');

        $proposals = $this->db->get(db_prefix() . 'proposals')->result_array();



        foreach ($proposals as $proposal) {

            $this->proposals_model->delete($proposal['id']);
        }



        // Get related tasks

        $this->db->where('rel_type', 'lead');

        $this->db->where('rel_id', $id);

        $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();

        foreach ($tasks as $task) {

            $this->tasks_model->delete_task($task['id']);
        }



        $phonenumber = str_replace("+91", "", $lead->phonenumber);
        $phonenumber = substr(preg_replace('/\D/', '', $phonenumber), -10);
        if (!empty($phonenumber)) {
            $this->delete_call_list($phonenumber);
        }

        $alternative_phonenumber = str_replace("+91", "", $lead->alternative_phonenumber);
        $alternative_phonenumber = $phonenumber = substr(preg_replace('/\D/', '', $alternative_phonenumber), -10);
        if (!empty($alternative_phonenumber)) {
            $this->delete_call_list($alternative_phonenumber);
        }

        $this->delete_notes(array($id));

        if (is_gdpr()) {

            $this->db->where('(description LIKE "%' . $lead->email . '%" OR description LIKE "%' . $lead->name . '%" OR description LIKE "%' . $lead->phonenumber . '%")');

            $this->db->delete(db_prefix() . 'activity_log');
        }
    }



    /**

     * Mark lead as lost

     * @param  mixed $id lead id

     * @return boolean

     */

    public function mark_as_lost($id)

    {

        $this->db->select('status');

        $this->db->from(db_prefix() . 'leads');

        $this->db->where('id', $id);

        $last_lead_status = $this->db->get()->row()->status;



        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads', [

            'lost'               => 1,

            'status'             => 0,

            'last_status_change' => date('Y-m-d H:i:s'),

            'last_lead_status'   => $last_lead_status,

        ]);



        if ($this->db->affected_rows() > 0) {

            $this->log_lead_activity($id, 'not_lead_activity_marked_lost');



            log_activity('Lead Marked as Lost [ID: ' . $id . ']');



            hooks()->do_action('lead_marked_as_lost', $id);



            return true;
        }



        return false;
    }



    /**

     * Unmark lead as lost

     * @param  mixed $id leadid

     * @return boolean

     */

    public function unmark_as_lost($id)

    {

        $this->db->select('last_lead_status');

        $this->db->from(db_prefix() . 'leads');

        $this->db->where('id', $id);

        $last_lead_status = $this->db->get()->row()->last_lead_status;



        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads', [

            'lost'   => 0,

            'status' => $last_lead_status,

        ]);

        if ($this->db->affected_rows() > 0) {

            $this->log_lead_activity($id, 'not_lead_activity_unmarked_lost');



            log_activity('Lead Unmarked as Lost [ID: ' . $id . ']');



            return true;
        }



        return false;
    }



    /**

     * Mark lead as junk

     * @param  mixed $id lead id

     * @return boolean

     */

    public function mark_as_junk($id)

    {

        $this->db->select('status');

        $this->db->from(db_prefix() . 'leads');

        $this->db->where('id', $id);

        $last_lead_status = $this->db->get()->row()->status;



        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads', [

            'junk'               => 1,

            'status'             => 0,

            'last_status_change' => date('Y-m-d H:i:s'),

            'last_lead_status'   => $last_lead_status,

        ]);



        if ($this->db->affected_rows() > 0) {

            $this->log_lead_activity($id, 'not_lead_activity_marked_junk');



            log_activity('Lead Marked as Junk [ID: ' . $id . ']');



            hooks()->do_action('lead_marked_as_junk', $id);



            return true;
        }



        return false;
    }



    /**

     * Unmark lead as junk

     * @param  mixed $id leadid

     * @return boolean

     */

    public function unmark_as_junk($id)

    {

        $this->db->select('last_lead_status');

        $this->db->from(db_prefix() . 'leads');

        $this->db->where('id', $id);

        $last_lead_status = $this->db->get()->row()->last_lead_status;



        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads', [

            'junk'   => 0,

            'status' => $last_lead_status,

        ]);

        if ($this->db->affected_rows() > 0) {

            $this->log_lead_activity($id, 'not_lead_activity_unmarked_junk');

            log_activity('Lead Unmarked as Junk [ID: ' . $id . ']');



            return true;
        }



        return false;
    }



    /**

     * Get lead attachments

     * @since Version 1.0.4

     * @param  mixed $id lead id

     * @return array

     */

    public function get_lead_attachments($id = '', $attachment_id = '', $where = [])

    {

        $this->db->where($where);

        $idIsHash = !is_numeric($attachment_id) && strlen($attachment_id) == 32;

        if (is_numeric($attachment_id) || $idIsHash) {

            $this->db->where($idIsHash ? 'attachment_key' : 'id', $attachment_id);



            return $this->db->get(db_prefix() . 'files')->row();
        }

        $this->db->where('rel_id', $id);

        $this->db->where('rel_type', 'lead');

        $this->db->order_by('dateadded', 'DESC');



        return $this->db->get(db_prefix() . 'files')->result_array();
    }



    public function add_attachment_to_database($lead_id, $attachment, $external = false, $form_activity = false)

    {

        $this->misc_model->add_attachment_to_database($lead_id, 'lead', $attachment, $external);



        if ($form_activity == false) {

            $this->leads_model->log_lead_activity($lead_id, 'not_lead_activity_added_attachment');
        } else {

            $this->leads_model->log_lead_activity($lead_id, 'not_lead_activity_log_attachment', true, serialize([

                $form_activity,

            ]));
        }



        // No notification when attachment is imported from web to lead form

        if ($form_activity == false) {

            $lead         = $this->get($lead_id);

            $not_user_ids = [];

            if ($lead->addedfrom != get_staff_user_id()) {

                array_push($not_user_ids, $lead->addedfrom);
            }

            if ($lead->assigned != get_staff_user_id() && $lead->assigned != 0) {

                array_push($not_user_ids, $lead->assigned);
            }

            $notifiedUsers = [];

            foreach ($not_user_ids as $uid) {

                $notified = add_notification([

                    'description'     => 'not_lead_added_attachment',

                    'touserid'        => $uid,

                    'link'            => '#leadid=' . $lead_id,

                    'additional_data' => serialize([

                        $lead->name,

                    ]),

                ]);

                if ($notified) {

                    array_push($notifiedUsers, $uid);
                }
            }

            pusher_trigger_notification($notifiedUsers);
        }
    }



    /**

     * Delete lead attachment

     * @param  mixed $id attachment id

     * @return boolean

     */

    public function delete_lead_attachment($id)

    {

        $attachment = $this->get_lead_attachments('', $id);

        $deleted    = false;



        if ($attachment) {

            if (empty($attachment->external)) {

                unlink(get_upload_path_by_type('lead') . $attachment->rel_id . '/' . $attachment->file_name);
            }

            $this->db->where('id', $attachment->id);

            $this->db->delete(db_prefix() . 'files');

            if ($this->db->affected_rows() > 0) {

                $deleted = true;

                log_activity('Lead Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }



            if (is_dir(get_upload_path_by_type('lead') . $attachment->rel_id)) {

                // Check if no attachments left, so we can delete the folder also

                $other_attachments = list_files(get_upload_path_by_type('lead') . $attachment->rel_id);

                if (count($other_attachments) == 0) {

                    // okey only index.html so we can delete the folder also

                    delete_dir(get_upload_path_by_type('lead') . $attachment->rel_id);
                }
            }
        }



        return $deleted;
    }



    // Sources



    /**

     * Get leads sources

     * @param  mixed $id Optional - Source ID

     * @return mixed object if id passed else array

     */

    public function get_source($id = false, $performance_status = 0,$where=[])

    {

        if (is_numeric($id)) {

            $this->db->where('id', $id);



            return $this->db->get(db_prefix() . 'leads_sources')->row();
        }
        if (!empty($performance_status) && is_numeric($performance_status) && $performance_status == 1) {
            $this->db->select('l.*,m.name as marketing_name');
            $this->db->from(db_prefix() . 'leads_sources As l');
            $this->db->join(db_prefix() . 'lead_marketing m', "l.marketing_type = m.id", "left");
            $this->db->where('performance_status', 1);
            $this->db->order_by('l.name', 'asc');
            return $this->db->get()->result_array();
        }

if(!empty($where))
{
    $this->db->where($where);  
}

        $this->db->select('l.*,m.name as marketing_name');
        $this->db->from(db_prefix() . 'leads_sources As l');
        $this->db->join(db_prefix() . 'lead_marketing m', "l.marketing_type = m.id", "left");
        $this->db->order_by('l.name', 'asc');

        return $this->db->get()->result_array();
    }



    /**

     * Add new lead source

     * @param mixed $data source data

     */

    public function add_source($data)

    {

        $this->db->insert(db_prefix() . 'leads_sources', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {

            log_activity('New Leads Source Added [SourceID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }



        return $insert_id;
    }



    /**

     * Update lead source

     * @param  mixed $data source data

     * @param  mixed $id   source id

     * @return boolean

     */

    public function update_source($data, $id)

    {

        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads_sources', $data);

        if ($this->db->affected_rows() > 0) {

            log_activity('Leads Source Updated [SourceID: ' . $id . ', Name: ' . $data['name'] . ']');



            return true;
        }



        return false;
    }



    /**

     * Delete lead source from database

     * @param  mixed $id source id

     * @return mixed

     */

    public function delete_source($id)

    {

        $current = $this->get_source($id);

        // Check if is already using in table

        if (is_reference_in_table('source', db_prefix() . 'leads', $id) || is_reference_in_table('lead_source', db_prefix() . 'leads_email_integration', $id)) {

            return [

                'referenced' => true,

            ];
        }

        $this->db->where('id', $id);

        $this->db->delete(db_prefix() . 'leads_sources');

        if ($this->db->affected_rows() > 0) {

            if (get_option('leads_default_source') == $id) {

                update_option('leads_default_source', '');
            }

            log_activity('Leads Source Deleted [SourceID: ' . $id . ']');



            return true;
        }



        return false;
    }



    // Statuses



    /**

     * Get lead statuses

     * @param  mixed $id status id

     * @return mixed      object if id passed else array

     */

    public function get_status($id = '', $where = [])

    {

        $this->db->where($where);

        if (is_numeric($id)) {

            $this->db->where('id', $id);



            return $this->db->get(db_prefix() . 'leads_status')->row();
        }



        $statuses = $this->app_object_cache->get('leads-all-statuses');



        if (!$statuses) {


            $this->db->select('ls.*,c.name conversion_type_name');
            $this->db->from(db_prefix() . 'leads_status ls', 'asc');
            $this->db->join(db_prefix() . 'lead_conversion_type c', 'ls.conversion_type = c.id', "left");
            $this->db->order_by('ls.statusorder', 'asc');

            $statuses = $this->db->get()->result_array();

            $this->app_object_cache->add('leads-all-statuses', $statuses);
        }



        return $statuses;
    }





    /**

     * Add new lead status

     * @param array $data lead status data

     */

    public function add_status($data)

    {

        if (isset($data['color']) && $data['color'] == '') {

            $data['color'] = hooks()->apply_filters('default_lead_status_color', '#757575');
        }



        if (!isset($data['statusorder'])) {

            $data['statusorder'] = total_rows(db_prefix() . 'leads_status') + 1;
        }



        $this->db->insert(db_prefix() . 'leads_status', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {

            log_activity('New Leads Status Added [StatusID: ' . $insert_id . ', Name: ' . $data['name'] . ']');



            return $insert_id;
        }



        return false;
    }



    // TYPES



    /**

     * Get lead types

     * @param  mixed $id status id

     * @return mixed      object if id passed else array

     */

    public function get_type($id = '', $where = [])

    {

        $this->db->where($where);

        if (is_numeric($id)) {

            $this->db->where('id', $id);



            return $this->db->get(db_prefix() . 'leads_type')->row();
        }



        $type = $this->app_object_cache->get('leads-all-type');



        if (!$type) {

            $this->db->order_by('statusorder', 'asc');

            $type = $this->db->get(db_prefix() . 'leads_type')->result_array();

            $this->app_object_cache->add('leads-all-type', $type);
        }



        return $type;
    }



    public function update_status($data, $id)

    {

        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'leads_status', $data);

        if ($this->db->affected_rows() > 0) {

            log_activity('Leads Status Updated [StatusID: ' . $id . ', Name: ' . $data['name'] . ']');



            return true;
        }



        return false;
    }



    /**

     * Delete lead status from database

     * @param  mixed $id status id

     * @return boolean

     */

    public function delete_status($id)

    {

        $current = $this->get_status($id);

        // Check if is already using in table

        if (is_reference_in_table('status', db_prefix() . 'leads', $id) || is_reference_in_table('lead_status', db_prefix() . 'leads_email_integration', $id)) {

            return [

                'referenced' => true,

            ];
        }



        $this->db->where('id', $id);

        $this->db->delete(db_prefix() . 'leads_status');

        if ($this->db->affected_rows() > 0) {

            if (get_option('leads_default_status') == $id) {

                update_option('leads_default_status', '');
            }

            log_activity('Leads Status Deleted [StatusID: ' . $id . ']');



            return true;
        }



        return false;
    }



    /**

     * Update canban lead status when drag and drop

     * @param  array $data lead data

     * @return boolean

     */

    public function update_lead_visitor_status($data)

    {
        $this->db->where('id', $data['id']);

        $this->db->update(db_prefix() . 'visitor_request', [

            'status' => $data['status'],

        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
    public function update_lead_status($data)

    {

        $this->db->select('status');

        $this->db->where('id', $data['leadid']);

        $_old = $this->db->get(db_prefix() . 'leads')->row();



        $old_status = '';



        if ($_old) {

            $old_status = $this->get_status($_old->status);

            if ($old_status) {

                $old_status = $old_status->name;
            }
        }



        $affectedRows   = 0;

        $current_status = $this->get_status($data['status'])->name;



        $this->db->where('id', $data['leadid']);

        $this->db->update(db_prefix() . 'leads', [

            'status' => $data['status'],

        ]);



        $_log_message = '';



        if ($this->db->affected_rows() > 0) {

            $affectedRows++;

            if ($current_status != $old_status && $old_status != '') {

                $_log_message    = 'not_lead_activity_status_updated';

                $additional_data = serialize([

                    get_staff_full_name(),

                    $old_status,

                    $current_status,

                ]);



                hooks()->do_action('lead_status_changed', [

                    'lead_id'    => $data['leadid'],

                    'old_status' => $old_status,

                    'new_status' => $current_status,

                ]);
            }

            $this->db->where('id', $data['leadid']);

            $this->db->update(db_prefix() . 'leads', [

                'last_status_change' => date('Y-m-d H:i:s'),

            ]);
        }

        if (isset($data['order'])) {

            foreach ($data['order'] as $order_data) {

                $this->db->where('id', $order_data[0]);

                $this->db->update(db_prefix() . 'leads', [

                    'leadorder' => $order_data[1],

                ]);
            }
        }

        if ($affectedRows > 0) {

            if ($_log_message == '') {

                return true;
            }

            $this->log_lead_activity($data['leadid'], $_log_message, false, $additional_data);



            return true;
        }



        return false;
    }



    /**

     * Update canban lead status when drag and drop

     * @param  array $data lead data

     * @return boolean

     */

    public function update_lead_type($data)

    {

        $this->db->select('type');

        $this->db->where('id', $data['leadid']);

        $_old = $this->db->get(db_prefix() . 'leads')->row();



        $old_type = '';



        if ($_old) {

            $old_type = $this->get_type($_old->type);

            if ($old_type) {

                $old_type = $old_type->name;
            }
        }



        $affectedRows   = 0;

        $current_type = $this->get_type($data['type'])->name;



        $this->db->where('id', $data['leadid']);

        $this->db->update(db_prefix() . 'leads', [

            'type' => $data['type'],

        ]);



        $_log_message = '';



        if ($this->db->affected_rows() > 0) {

            $affectedRows++;

            if ($current_type != $old_type && $old_type != '') {

                $_log_message    = 'not_lead_activity_status_updated';

                $additional_data = serialize([

                    get_staff_full_name(),

                    $old_type,

                    $current_type,

                ]);



                hooks()->do_action('lead_type_changed', [

                    'lead_id'    => $data['leadid'],

                    'old_type' => $old_type,

                    'new_type' => $current_type,

                ]);
            }

            $this->db->where('id', $data['leadid']);

            $this->db->update(db_prefix() . 'leads', [

                'last_type_change' => date('Y-m-d H:i:s'),

            ]);
        }

        if (isset($data['order'])) {

            foreach ($data['order'] as $order_data) {

                $this->db->where('id', $order_data[0]);

                $this->db->update(db_prefix() . 'leads', [

                    'leadorder' => $order_data[1],

                ]);
            }
        }

        if ($affectedRows > 0) {

            if ($_log_message == '') {

                return true;
            }

            $this->log_lead_activity($data['leadid'], $_log_message, false, $additional_data);



            return true;
        }



        return false;
    }



    /* Ajax */



    /**

     * All lead activity by staff

     * @param  mixed $id lead id

     * @return array

     */

    public function get_lead_activity_log($id)

    {

        $sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'ASC');



        $this->db->where('leadid', $id);

        $this->db->order_by('date', $sorting);



        return $this->db->get(db_prefix() . 'lead_activity_log')->result_array();
    }

    public function get_lead_call_activity_log($id)
    {
        $sql = "SELECT c.*,IF(c.contact = l.phonenumber, 'Primary', 'Secondary') AS contact_type,IF(c.contact = l.phonenumber, 'assets/images/call-primary-icon.png', 'assets/images/call-secondary-icon.png') AS image_icon,concat(s.firstname,' ',s.lastname) staff_name,s.profile_image,t.name call_type_name,so.name source_name,t.icon type_icon,so.icon source_icon FROM " . db_prefix() . "calls_activity_logs c LEFT JOIN " . db_prefix() . "leads l ON (l.phonenumber = c.contact or l.alternative_phonenumber = c.contact) LEFT join " . db_prefix() . "staff s on s.staffid = c.staffid LEFT join " . db_prefix() . "calls_type t on t.id=c.calls_type LEFT join " . db_prefix() . "calls_source so ON so.id = c.calls_source WHERE c.contact!=''  AND l.id = '{$id}' AND c.status = 1 GROUP by c.id order by FROM_UNIXTIME(c.call_start) DESC";
        return $this->db->query($sql)->result_array();
    }




    public function staff_can_access_lead($id, $staff_id = '')

    {

        $staff_id = $staff_id == '' ? get_staff_user_id() : $staff_id;



        if (has_permission('leads', $staff_id, 'view') || has_permission('visit_leads', $staff_id, 'view_department') || has_permission('visit_leads', $staff_id, 'view')) {

            return true;
        }



        $CI = &get_instance();



        if (total_rows(db_prefix() . 'leads', 'id="' . $CI->db->escape_str($id) . '" AND (assigned=' . $CI->db->escape_str($staff_id) . ' OR is_public=1 OR addedfrom=' . $CI->db->escape_str($staff_id) . ')') > 0) {

            return true;
        }


        if (total_rows(db_prefix() . 'visitor_request', 'lead_id="' . $CI->db->escape_str($id) . '" AND (assigned=' . $CI->db->escape_str($staff_id) . ' OR created_by=' . $CI->db->escape_str($staff_id) . ')') > 0) {

            return true;
        }



        return false;
    }



    /**

     * Add lead activity from staff

     * @param  mixed  $id          lead id

     * @param  string  $description activity description

     */

    public function log_lead_activity($id, $description, $integration = false, $additional_data = '')

    {

        $log = [

            'date'            => date('Y-m-d H:i:s'),

            'description'     => $description,

            'leadid'          => $id,

            'staffid'         => get_staff_user_id(),

            'additional_data' => $additional_data,

            'full_name'       => get_staff_full_name(get_staff_user_id()),

        ];

        if ($integration == true) {

            $log['staffid']   = 0;

            $log['full_name'] = '[CRON]';
        }



        $this->db->insert(db_prefix() . 'lead_activity_log', $log);



        return $this->db->insert_id();
    }



    /**

     * Get email integration config

     * @return object

     */

    public function get_email_integration()

    {

        $this->db->where('id', 1);



        return $this->db->get(db_prefix() . 'leads_email_integration')->row();
    }



    /**

     * Get lead imported email activity

     * @param  mixed $id leadid

     * @return array

     */

    public function get_mail_activity($id)

    {

        $this->db->where('leadid', $id);

        $this->db->order_by('dateadded', 'asc');



        return $this->db->get(db_prefix() . 'lead_integration_emails')->result_array();
    }



    /**

     * Update email integration config

     * @param  mixed $data All $_POST data

     * @return boolean

     */

    public function update_email_integration($data)

    {

        $this->db->where('id', 1);

        $original_settings = $this->db->get(db_prefix() . 'leads_email_integration')->row();



        $data['create_task_if_customer']        = isset($data['create_task_if_customer']) ? 1 : 0;

        $data['active']                         = isset($data['active']) ? 1 : 0;

        $data['delete_after_import']            = isset($data['delete_after_import']) ? 1 : 0;

        $data['notify_lead_imported']           = isset($data['notify_lead_imported']) ? 1 : 0;

        $data['only_loop_on_unseen_emails']     = isset($data['only_loop_on_unseen_emails']) ? 1 : 0;

        $data['notify_lead_contact_more_times'] = isset($data['notify_lead_contact_more_times']) ? 1 : 0;

        $data['mark_public']                    = isset($data['mark_public']) ? 1 : 0;

        $data['responsible']                    = !isset($data['responsible']) ? 0 : $data['responsible'];



        if ($data['notify_lead_contact_more_times'] != 0 || $data['notify_lead_imported'] != 0) {

            if (isset($data['notify_type']) && $data['notify_type'] == 'specific_staff') {

                if (isset($data['notify_ids_staff'])) {

                    $data['notify_ids'] = serialize($data['notify_ids_staff']);

                    unset($data['notify_ids_staff']);
                } else {

                    $data['notify_ids'] = serialize([]);

                    unset($data['notify_ids_staff']);
                }

                if (isset($data['notify_ids_roles'])) {

                    unset($data['notify_ids_roles']);
                }
            } else {

                if (isset($data['notify_ids_roles'])) {

                    $data['notify_ids'] = serialize($data['notify_ids_roles']);

                    unset($data['notify_ids_roles']);
                } else {

                    $data['notify_ids'] = serialize([]);

                    unset($data['notify_ids_roles']);
                }

                if (isset($data['notify_ids_staff'])) {

                    unset($data['notify_ids_staff']);
                }
            }
        } else {

            $data['notify_ids']  = serialize([]);

            $data['notify_type'] = null;

            if (isset($data['notify_ids_staff'])) {

                unset($data['notify_ids_staff']);
            }

            if (isset($data['notify_ids_roles'])) {

                unset($data['notify_ids_roles']);
            }
        }



        // Check if not empty $data['password']

        // Get original

        // Decrypt original

        // Compare with $data['password']

        // If equal unset

        // If not encrypt and save

        if (!empty($data['password'])) {

            $or_decrypted = $this->encryption->decrypt($original_settings->password);

            if ($or_decrypted == $data['password']) {

                unset($data['password']);
            } else {

                $data['password'] = $this->encryption->encrypt($data['password']);
            }
        }



        $this->db->where('id', 1);

        $this->db->update(db_prefix() . 'leads_email_integration', $data);

        if ($this->db->affected_rows() > 0) {

            return true;
        }



        return false;
    }



    public function change_status_color($data)

    {

        $this->db->where('id', $data['status_id']);

        $this->db->update(db_prefix() . 'leads_status', [

            'color' => $data['color'],

        ]);
    }



    public function update_status_order($data)

    {

        foreach ($data['order'] as $status) {

            $this->db->where('id', $status[0]);

            $this->db->update(db_prefix() . 'leads_status', [

                'statusorder' => $status[1],

            ]);
        }
    }



    public function get_form($where)

    {

        $this->db->where($where);



        return $this->db->get(db_prefix() . 'web_to_lead')->row();
    }



    public function add_form($data)

    {

        $data                       = $this->_do_lead_web_to_form_responsibles($data);

        $data['success_submit_msg'] = nl2br($data['success_submit_msg']);

        $data['form_key']           = app_generate_hash();



        $data['create_task_on_duplicate'] = (int) isset($data['create_task_on_duplicate']);

        $data['mark_public']              = (int) isset($data['mark_public']);
        $data['allow_state_location']              = (int) isset($data['allow_state_location']);
        $data['state_wise']              = (int) isset($data['state_wise']);



        if (isset($data['allow_duplicate'])) {

            $data['allow_duplicate']           = 1;

            $data['track_duplicate_field']     = '';

            $data['track_duplicate_field_and'] = '';

            $data['create_task_on_duplicate']  = 0;
        } else {

            $data['allow_duplicate'] = 0;
        }
        if (!empty($data['auto_assign'])) {
            $data['auto_assign'] = implode(",", $data['auto_assign']);
        }


        $data['dateadded'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'web_to_lead', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {

            log_activity('New Web to Lead Form Added [' . $data['name'] . ']');



            return $insert_id;
        }



        return false;
    }


    public function update_form($id, $data)

    {

        $data                       = $this->_do_lead_web_to_form_responsibles($data);

        $data['success_submit_msg'] = nl2br($data['success_submit_msg']);



        $data['create_task_on_duplicate'] = (int) isset($data['create_task_on_duplicate']);

        $data['mark_public']              = (int) isset($data['mark_public']);

        $data['allow_state_location']              = (int) isset($data['allow_state_location']);
        $data['state_wise']              = (int) isset($data['state_wise']);



        if (isset($data['allow_duplicate'])) {

            $data['allow_duplicate']           = 1;

            $data['track_duplicate_field']     = '';

            $data['track_duplicate_field_and'] = '';

            $data['create_task_on_duplicate']  = 0;
        } else {

            $data['allow_duplicate'] = 0;
        }

        if (isset($data['assign_previous_lead_alert'])) {
            $data['assign_previous_lead_alert']  = 1;
        } else {
            $data['assign_previous_lead_alert']  = 0;
        }
        if (!empty($data['auto_assign'])) {
            $data['auto_assign'] = implode(",", $data['auto_assign']);
        } else {
            $data['auto_assign'] = '';
        }


        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'web_to_lead', $data);



        return ($this->db->affected_rows() > 0 ? true : false);
    }


    public function delete_form($id)

    {

        $this->db->where('id', $id);

        $this->db->delete(db_prefix() . 'web_to_lead');



        $this->db->where('from_form_id', $id);

        $this->db->update(db_prefix() . 'leads', [

            'from_form_id' => 0,

        ]);



        if ($this->db->affected_rows() > 0) {

            log_activity('Lead Form Deleted [' . $id . ']');



            return true;
        }



        return false;
    }



    private function _do_lead_web_to_form_responsibles($data)

    {

        if (isset($data['notify_lead_imported'])) {

            $data['notify_lead_imported'] = 1;
        } else {

            $data['notify_lead_imported'] = 0;
        }



        if ($data['responsible'] == '') {

            $data['responsible'] = 0;
        }

        if ($data['notify_lead_imported'] != 0) {

            if ($data['notify_type'] == 'specific_staff') {

                if (isset($data['notify_ids_staff'])) {

                    $data['notify_ids'] = serialize($data['notify_ids_staff']);

                    unset($data['notify_ids_staff']);
                } else {

                    $data['notify_ids'] = serialize([]);

                    unset($data['notify_ids_staff']);
                }

                if (isset($data['notify_ids_roles'])) {

                    unset($data['notify_ids_roles']);
                }
            } else {

                if (isset($data['notify_ids_roles'])) {

                    $data['notify_ids'] = serialize($data['notify_ids_roles']);

                    unset($data['notify_ids_roles']);
                } else {

                    $data['notify_ids'] = serialize([]);

                    unset($data['notify_ids_roles']);
                }

                if (isset($data['notify_ids_staff'])) {

                    unset($data['notify_ids_staff']);
                }
            }
        } else {

            $data['notify_ids']  = serialize([]);

            $data['notify_type'] = null;

            if (isset($data['notify_ids_staff'])) {

                unset($data['notify_ids_staff']);
            }

            if (isset($data['notify_ids_roles'])) {

                unset($data['notify_ids_roles']);
            }
        }



        return $data;
    }

    // function automatic_assign_staff($state_name = "", $lead_type = "", $deprtment_head_status = "", $facebook_lead = "", $staff_ids = array(), $google_source = '')
    // {

    //     //   $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned,st.facebook_lead_name from  " . db_prefix() . "staff st LEFT JOIN " . db_prefix() . "states s ON (FIND_IN_SET(s.id,st.assign_state) ";
    //     // if (!empty($lead_type)) {
    //     //     $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
    //     // }
    //     // $sql .= " ) where 1=1 ";

    //     // if (!empty($state_name)) {
    //     //     $sql .= " AND LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' ";
    //     // }
    //     // if (!empty($deprtment_head_status)) {
    //     //     $sql .= " and st.department_head = '1' ";
    //     // }
    //     // if (!empty($facebook_lead)) {
    //     //     $sql .= " and st.facebook_lead_name != '' ";
    //     // }
    //     // if (!empty($lead_type)) {
    //     //     $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
    //     // }
    //     // if (!empty($staff_ids)) {
    //     //     $sql .= " and st.staffid in (" . implode(",", $staff_ids) . ") ";
    //     // }

    //     // $sql .= "  group by st.staffid order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc  ";
    //     // if (!empty($facebook_lead)) {
    //     // } else {
    //     //     $sql .= " limit 1 ";
    //     // }

    //     $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned,group_concat(DISTINCT(f.name)) facebook_lead_name from  " . db_prefix() . "staff st LEFT JOIN " . db_prefix() . "states s ON (FIND_IN_SET(s.id,st.assign_state)";
    //     if (!empty($lead_type)) {
    //         $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
    //     }
    //     $sql .= " ) ";
    //     $sql .= " LEFT JOIN " . db_prefix() . "facebook_name f ON (FIND_IN_SET(f.id,st.facebook_lead_name) ) ";
    //     $sql .= " where 1=1 ";
    //     if (!empty($state_name)) {
    //         $sql .= " AND LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' ";
    //     }

    //     if (!empty($deprtment_head_status)) {
    //         $sql .= " and st.department_head = '1' ";
    //     }
    //     if (!empty($facebook_lead)) {
    //         $sql .= " and st.facebook_lead_name != '' ";
    //     }
    //     if (!empty($google_source)) {
    //         $sql .= " and (st.google_source != '' AND FIND_IN_SET({$google_source},st.google_source)) ";
    //     }
    //     if (!empty($lead_type)) {
    //         $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
    //     }
    //     if (!empty($staff_ids)) {
    //         $sql .= " and st.staffid in (" . implode(",", $staff_ids) . ") ";
    //     }
    //     $sql .= " group by st.staffid order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc ";
    //     if (!empty($facebook_lead)) {
    //     } else {
    //         $sql .= " limit 1 ";
    //     }
    //     // echo $sql;
    //     // die;
    //     // $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned from " . db_prefix() . "states s join " . db_prefix() . "staff st ON (FIND_IN_SET(s.id,st.assign_state) and st.lead_type = '" . trim($lead_type) . "'  and st.active = '1') where LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc limit 1";
    //     return $this->db->query($sql)->result_array();
    // }


 function getLastWorkingDay($date, $holidays) {
        do {
            $date = date('Y-m-d', strtotime($date . ' -1 day'));
            $day  = date('w', strtotime($date));
        } while ($day == 0 || in_array($date, $holidays)); // skip Sunday & holidays

        return $date;
    }
    
    function automatic_assign_staff($state_name = "", $lead_type = "", $deprtment_head_status = "", $facebook_lead = "", $staff_ids = array(), $google_source = '',$staff_not='')
    {

        //   $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned,st.facebook_lead_name from  " . db_prefix() . "staff st LEFT JOIN " . db_prefix() . "states s ON (FIND_IN_SET(s.id,st.assign_state) ";
        // if (!empty($lead_type)) {
        //     $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        // }
        // $sql .= " ) where 1=1 ";

        // if (!empty($state_name)) {
        //     $sql .= " AND LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' ";
        // }
        // if (!empty($deprtment_head_status)) {
        //     $sql .= " and st.department_head = '1' ";
        // }
        // if (!empty($facebook_lead)) {
        //     $sql .= " and st.facebook_lead_name != '' ";
        // }
        // if (!empty($lead_type)) {
        //     $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        // }
        // if (!empty($staff_ids)) {
        //     $sql .= " and st.staffid in (" . implode(",", $staff_ids) . ") ";
        // }

        // $sql .= "  group by st.staffid order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc  ";
        // if (!empty($facebook_lead)) {
        // } else {
        //     $sql .= " limit 1 ";
        // }

        $sql = "Select  s.name,
    st.staffid,
    CONCAT(st.firstname, ' ', st.lastname) AS staff_name,
    last_lead.dateassigned,
    GROUP_CONCAT(DISTINCT f.name) AS facebook_lead_name from  " . db_prefix() . "staff st LEFT JOIN " . db_prefix() . "states s ON (FIND_IN_SET(s.id,st.assign_state)";
        if (!empty($lead_type)) {
            $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        }
        $sql .= " ) ";
        $sql .= " LEFT JOIN " . db_prefix() . "facebook_name f ON (FIND_IN_SET(f.id,st.facebook_lead_name) ) ";
        $sql .= " LEFT JOIN 
    (
        SELECT 
            assigned, 
            MAX(dateassigned) AS dateassigned
        FROM 
            " . db_prefix() . "leads
        GROUP BY 
            assigned
    ) AS last_lead 
    ON st.staffid = last_lead.assigned ";

        $sql .= " where 1=1 ";
        if (!empty($state_name)) {
            $sql .= " AND LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' ";
        }

        if (!empty($deprtment_head_status)) {
            $sql .= " and st.department_head = '1' ";
        }
        if (!empty($facebook_lead)) {
            $sql .= " and st.facebook_lead_name != '' AND f.name = '{$facebook_lead}' ";
        }
        if (!empty($google_source)) {
            $sql .= " and (st.google_source != '' AND FIND_IN_SET({$google_source},st.google_source)) ";
        }
        if (!empty($lead_type)) {
            $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        }
        if (!empty($staff_ids)) {
            $sql .= " and st.staffid in (" . implode(",", $staff_ids) . ") ";
        }
        
         if (!empty($staff_not)) {
            $sql .= " and st.staffid !='".$staff_not."' ";
        }
     
     
     
   if (ACTIVE_STAFF_ONLY == 1) {

            $currentTime = date('H:i');
            $today       = date('Y-m-d');
            $dayOfWeek   = date('w'); // 0 = Sunday


            // Example holiday array (you can fetch from DB)
            $holidays = holiday_list();
            $isHoliday = in_array($today, $holidays);

            if ($dayOfWeek == 0 || $isHoliday) {
                // Sunday or Holiday → check last login date
                $sql .= " AND DATE(st.last_login) = (
                    SELECT MAX(DATE(last_login))
                    FROM tblstaff
                    WHERE DATE(last_login) < '$today'
                 ) ";
            } else {

                if ($currentTime >= '12:00') {
                    // Between 10 AM and 11 AM → check today only
                    $sql .= " AND (DATE(st.last_login) = '$today' 
                       OR DATE(st.last_activity) = '$today') ";
                } else {
                    // Before 10 AM → check today OR yesterday
                    // $yesterday = date('Y-m-d', strtotime('-1 day'));
                     $yesterday = $this->getLastWorkingDay($today, $holidays);

                    $sql .= " AND (
                        DATE(st.last_login) IN ('$today','$yesterday')
                        OR DATE(st.last_activity) IN ('$today','$yesterday')
                     ) ";
                }
            }
        }

        $sql .= " group by st.staffid,last_lead.dateassigned order by last_lead.dateassigned asc ";
        if (!empty($facebook_lead)) {
        } else {
            $sql .= " limit 1 ";
        }

        // echo $sql;
        // die;
        // $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned from " . db_prefix() . "states s join " . db_prefix() . "staff st ON (FIND_IN_SET(s.id,st.assign_state) and st.lead_type = '" . trim($lead_type) . "'  and st.active = '1') where LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc limit 1";
        return $this->db->query($sql)->result_array();
    }

public function holiday_list()
{
    return holiday_list();
}

    function automatic_assign_staff_city($city_name = "", $lead_type = "", $deprtment_head_status = "", $facebook_lead = "", $staff_ids = array(), $google_source = '',$staff_not='')
    {

        $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned,group_concat(DISTINCT(f.name)) facebook_lead_name from  " . db_prefix() . "staff st LEFT JOIN " . db_prefix() . "cities s ON (FIND_IN_SET(s.id,st.assign_city)";
        if (!empty($lead_type)) {
            $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        }
        $sql .= " ) ";
        $sql .= " LEFT JOIN " . db_prefix() . "facebook_name f ON (FIND_IN_SET(f.id,st.facebook_lead_name) ) ";
        $sql .= " where 1=1 ";
        if (!empty($city_name)) {
            $sql .= " AND LOWER(TRIM(s.name)) = '" . strtolower(trim($city_name)) . "' ";
        }

        if (!empty($deprtment_head_status)) {
            $sql .= " and st.department_head = '1' ";
        }
        if (!empty($facebook_lead)) {
            $sql .= " and st.facebook_lead_name != '' ";
        }
        if (!empty($google_source)) {
            $sql .= " and (st.google_source != '' AND FIND_IN_SET({$google_source},st.google_source)) ";
        }
        if (!empty($lead_type)) {
            $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        }
        if (!empty($staff_ids)) {
            $sql .= " and st.staffid in (" . implode(",", $staff_ids) . ") ";
        }
          if (!empty($staff_not)) {
            $sql .= " and st.staffid !='".$staff_not."' ";
        }
        
      if (ACTIVE_STAFF_ONLY == 1) {

            $currentTime = date('H:i');
            $today       = date('Y-m-d');
            $dayOfWeek   = date('w'); // 0 = Sunday


            // Example holiday array (you can fetch from DB)
            $holidays = holiday_list();
            $isHoliday = in_array($today, $holidays);

            if ($dayOfWeek == 0 || $isHoliday) {
                // Sunday or Holiday → check last login date
                $sql .= " AND DATE(st.last_login) = (
                    SELECT MAX(DATE(st.last_login))
                    FROM tblstaff
                    WHERE DATE(st.last_login) < '$today'
                 ) ";
            } else {

                if ($currentTime >= '11:00') {
                    // Between 10 AM and 11 AM → check today only
                    $sql .= " AND (DATE(st.last_login) = '$today' 
                       OR DATE(st.last_activity) = '$today') ";
                } else {
                    // Before 10 AM → check today OR yesterday
                    // $yesterday = date('Y-m-d', strtotime('-1 day'));
                     $yesterday = $this->getLastWorkingDay($today, $holidays);

                    $sql .= " AND (
                        DATE(st.last_login) IN ('$today','$yesterday')
                        OR DATE(st.last_activity) IN ('$today','$yesterday')
                     ) ";
                }
            }
        }

        $sql .= " group by st.staffid order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc ";
        if (!empty($facebook_lead)) {
        } else {
            $sql .= " limit 1 ";
        }
        // echo $sql;
        // die;
        // $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned from " . db_prefix() . "states s join " . db_prefix() . "staff st ON (FIND_IN_SET(s.id,st.assign_state) and st.lead_type = '" . trim($lead_type) . "'  and st.active = '1') where LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc limit 1";
        return $this->db->query($sql)->result_array();
    }
    public function get_marketing_type()
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'lead_marketing')->row();
        }
        $this->db->order_by('id', 'asc');
        return $this->db->get(db_prefix() . 'lead_marketing')->result_array();
    }

    public function get_conversion_type()
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'lead_conversion_type')->row();
        }
        $this->db->order_by('id', 'asc');
        return $this->db->get(db_prefix() . 'lead_conversion_type')->result_array();
    }

    public function add_fb_form($data)

    {

        $this->db->insert(db_prefix() . 'facebook_name', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {

            log_activity('New Facebook Form Added [FormID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }



        return $insert_id;
    }



    /**

     * Update lead source

     * @param  mixed $data source data

     * @param  mixed $id   source id

     * @return boolean

     */

    public function update_fb_form($data, $id)

    {

        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'facebook_name', $data);

        if ($this->db->affected_rows() > 0) {

            log_activity('Facebook Form Updated [FORMID: ' . $id . ', Name: ' . $data['name'] . ']');



            return true;
        }



        return false;
    }
    public function re_assign($id = "", $data)
    {
        $this->db->where('id', $id);
        $temp_lead =  $this->db->get(db_prefix() . 'leads')->row();

        $this->delete($id);
        unset($temp_lead->id);
        unset($temp_lead->dateadded);
        unset($temp_lead->lastcontact);
        unset($temp_lead->dateassigned);
        unset($temp_lead->last_status_change);
        unset($temp_lead->last_type_change);
        $temp_lead->assigned = $data["assigned"];
        if (!empty($data["status"])) {
            $temp_lead->status = $data["status"];
        }
        if (!empty($data["source"])) {
            $temp_lead->source = $data["source"];
        }
        if (!empty($data["leadtype"])) {
            $temp_lead->type = $data["leadtype"];
        }
        $temp_lead = (array)$temp_lead;
        return $this->add($temp_lead);
    }

    public function lead_data($ids)
    {
        $this->db->where_in('id', $ids);
        return  $this->db->get(db_prefix() . 'leads')->result_array();
    }

    public function get_vendor($id = '', $where = [])

    {

        $this->db->where($where);

        if (is_numeric($id)) {

            $this->db->where('id', $id);



            return $this->db->get(db_prefix() . 'profile_creater_vendor')->row();
        }



        $type = $this->app_object_cache->get('leads-all-vendor');



        if (!$type) {

            $this->db->order_by('sequence', 'asc');

            $type = $this->db->get(db_prefix() . 'profile_creater_vendor')->result_array();

            $this->app_object_cache->add('leads-all-vendor', $type);
        }



        return $type;
    }

    public function delete_call_list($phonenumber)
    {

        $phonenumber = substr(preg_replace('/\D/', '', $phonenumber), -10);
        $this->db->where('contact', $phonenumber);
        $this->db->delete(db_prefix() . 'calls_activity_logs');
    }

    public function delete_notes($ids)
    {
        $this->db->where_in('rel_id', $ids);
        $this->db->where('rel_type', "lead");
        $this->db->delete(db_prefix() . 'notes');
    }

    public function get_staff_list()
    {
        $this->db->select('firstname,lastname,staffid,concat(firstname," ",lastname) staff_name,post_sales');
        return $staff = $this->db->get(db_prefix() . 'staff')->result_array();
    }


    public function get_lead_transfer_request()
    {
        $sql = "SELECT lt.name as lead_name, concat(ts.firstname,' ',ts.lastname) as staff_name,if(status=1,'Approved',if(status=2,'reject',if(status=3,'pending','Not defined'))) as status_name,if(status=1,'success',if(status=2,'danger',if(status=3,'warning',''))) as status_color FROM  " . db_prefix() . "lead_transfer_request tr join  " . db_prefix() . "leads_type lt on lt.id=tr.lead_type join  " . db_prefix() . "staff  ts on ts.staffid = tr.assign where tr.created_by=" . get_staff_user_id() . " ";
        return $this->db->query($sql)->result_array();
    }

    public function get_lead_transfer_request_exist($lead_id)
    {
        $this->db->select('id');
        $this->db->select('if(status=1,"Approved",if(status=3,"Pending","Not Found")) as status_text');
        $this->db->where_in("status", [3]);
        $this->db->where(array("leadid" => $lead_id));
        $staff = $this->db->get(db_prefix() . 'lead_transfer_request')->row();
        return $staff;
    }

    public function get_custum_values()
    {
        $this->db->select('CONCAT(fieldto, "-", relid, "-", fieldid) AS column_name, value');
        $this->db->where('fieldto', 'leads');
        $staff = $this->db->get(db_prefix() . 'customfieldsvalues')->result_array();
        return $staff;
    }

    public function tbllead_performance_column($ids = [])
    {
        // $this->db->select('*');
        // $this->db->where('show_column', '1');
        // $column = $this->db->get(db_prefix() . 'lead_performance_column')->result_array();

        $this->db->select('*, columnid as tbl_column_name,if(sequence=0,999999,sequence) sequence'); // Select all columns (*) and alias 'columnid' as 'tbl_column_name'.
        $this->db->where('show_column', '1'); // Add a condition where 'show_column' equals '1'.
        if (!empty($ids)) { // Check if the $ids variable is not empty.
            $this->db->where_in('id', $ids); // Add a condition to match multiple 'id' values in the $ids array.
        }
        $this->db->order_by('sequence', 'ASC'); // Order the results by 'sequence' in ascending order.
        $column = $this->db->get(db_prefix() . 'performance_columns')->result_array();
        // Execute the query on the table prefixed with 'performance_columns' and get the results as an array.
        return $column; // Return the resulting array.

    }

    public function performance_related_dropdown()
    {

        $this->db->query("SET SESSION group_concat_max_len = 1000000000");

        return $this->db->query("
    SELECT 
        source,
        " . db_prefix() . "leads_sources.name as source_name,
        GROUP_CONCAT(DISTINCT utm_campaign_name) AS campaign_name,
        GROUP_CONCAT(DISTINCT CONCAT(utm_campaign_name, '##', utm_ads_set_name)) AS ads_set_name,
        GROUP_CONCAT(DISTINCT CONCAT(utm_ads_set_name, '##', utm_ads_name)) AS ads_name,
        GROUP_CONCAT(DISTINCT utm_form_name) AS form_name,
        GROUP_CONCAT(DISTINCT utm_term) AS term
    FROM " . db_prefix() . "leads 
    JOIN " . db_prefix() . "leads_sources 
        ON (" . db_prefix() . "leads.source = " . db_prefix() . "leads_sources.id 
        AND " . db_prefix() . "leads_sources.performance_status = 1)
    WHERE utm_campaign_name != ''
    GROUP BY " . db_prefix() . "leads.source
")->result_array();
    }

    // public function get_lead_visitor_request_exist($lead_id)
    // {
    //     $this->db->select('id,assigned,created_by');
    //     $this->db->where_in("status", [1, 3]);
    //     $this->db->where(array("lead_id" => $lead_id));
    //     $staff = $this->db->get(db_prefix() . 'visitor_request')->row();
    //     return $staff;
    // }


    public function get_lead_visitor_request_exist($lead_id)
    {
        $sid = get_staff_user_id();
        $visit_leads_view = has_permission('visit_leads', '', 'view');
        $visit_leads_global = has_permission('visit_leads', '', 'view_department');
        // Get reporting persons
        $query = $this->db->query('CALL GetReportingPersons(?)', array($sid));
        $teamids = $query->result_array();



        // Close and reinitialize DB after calling a stored procedure
        $this->db->close();
        $this->db->initialize();

        $role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;

        // Extract staff IDs and include the current staff ID
        $idsarr = array_column($teamids, 'staffid');
        array_push($idsarr, $sid);  // Fix push_array() issue

        // Select required fields
        $this->db->select('id,assigned,created_by');

        // Filter conditions

        //     if ($role == 3) {
        // //   $this->db->where_in("status", [1, 3]);
        // }
        // else{
        //       $this->db->where_in("status", [1, 3]);
        // }
        $this->db->where_in("status", [1, 3]);

        $this->db->where(array("lead_id" => $lead_id));

        // Use where_in and or_where_in properly

        if ($visit_leads_view || $visit_leads_global) {
        } else {
            if (!is_admin()) {

                $this->db->group_start();
                $this->db->where_in("created_by", $idsarr);
                $this->db->or_where_in("assigned", $idsarr);
                $this->db->group_end();
            }
        }

        // Fetch the result
        $staff = $this->db->get(db_prefix() . 'visitor_request')->row();

        return $staff;
    }

    public function get_lead_visitor_request($lead_id)
    {
        $sql = "SELECT * FROM  " . db_prefix() . "visitor_request where status in (1,3) and lead_id=" . $lead_id . " ";
        return $this->db->query($sql)->row();
    }


    public function get_lead_visitor_activity_log($id)

    {

        $sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'DESC');



        $this->db->where('lead_id', $id);

        $this->db->order_by('date', $sorting);



        return $this->db->get(db_prefix() . 'visitor_activity_log')->result_array();
    }


    public function tblma_applicant_tracker($ids = [])
    {
        // $this->db->select('*, columnid as tbl_column_name,if(sequence=0,999999,sequence) sequence'); // Select all columns (*) and alias 'columnid' as 'tbl_column_name'.
        // $this->db->where('show_column', '1'); // Add a condition where 'show_column' equals '1'.
        // if (!empty($ids)) { // Check if the $ids variable is not empty.
        //     $this->db->where_in('id', $ids); // Add a condition to match multiple 'id' values in the $ids array.
        // }
        // // $this->db->order_by('sequence', 'ASC'); // Order the results by 'sequence' in ascending order.
        // if (!empty($ids)) {
        //     $this->db->order_by('FIELD(id, ' . implode(',', $ids) . ')');
        // } else {
        //     $this->db->order_by('sequence', 'ASC');
        // }
        // $column = $this->db->get(db_prefix() . 'ma_applicant_tracker')->result_array();
        // // Execute the query on the table prefixed with 'performance_columns' and get the results as an array.
        // return $column; // Return the resulting array.

        // 1. Separate normal column ids and additional column ids first
        $normal_ids = [];
        $additional_ids = [];

        $normal_ids = [];
        $additional_ids = [];
        $check_con = (is_admin() || is_postSale()) ? 1 : 0;

        // $this->db->query("SET sql_mode = ''");
        if (!empty($ids)) {
            // First fetch columns field
            $this->db->select('id, columns,is_postsale');
            $this->db->where_in('id', $ids);
            if (empty($check_con)) {
                $this->db->where('is_postsale', 0);
            }

            if (!empty($ids)) {
                $this->db->order_by('FIELD(id, ' . implode(',', $ids) . ')');
            } else {
                $this->db->order_by('sequence', 'ASC');
            }
            $column_data = $this->db->get(db_prefix() . 'ma_applicant_tracker')->result_array();


            foreach ($column_data as $col) {
                if ($col['columns'] == '') {
                    $normal_ids[] = $col['id']; // Normal column
                } else {
                    if (!empty($col['columns'])) {
                        $normal_ids = array_merge($normal_ids, explode(",", $col['columns'])); // Merge directly
                    }
                }
            }
        }

        // Now merge normal + additional ids
        $columns_ids = $normal_ids; // Unique to avoid duplicates




        $this->db->select('*, columnid as tbl_column_name,if(sequence=0,999999,sequence) sequence,is_postsale'); // Select all columns (*) and alias 'columnid' as 'tbl_column_name'.
        $this->db->where('show_column', '1'); // Add a condition where 'show_column' equals '1'.

        if (empty($check_con)) {
            $this->db->where('is_postsale', 0);
        }

        if (!empty($ids)) { // Check if the $ids variable is not empty.
            $this->db->where_in('id', $columns_ids); // Add a condition to match multiple 'id' values in the $columns_ids array.
        }



        // $this->db->order_by('sequence', 'ASC'); // Order the results by 'sequence' in ascending order.
        if (!empty($columns_ids)) {
            $this->db->order_by('FIELD(id, ' . implode(',', $columns_ids) . ')');
        } else {
            $this->db->order_by('sequence', 'ASC');
        }
        $column = $this->db->get(db_prefix() . 'ma_applicant_tracker')->result_array();
        if (get_staff_user_id() == 243) {
            // echo $this->db->last_query();
            // die;

            // print_r($column);
        }

        // Execute the query on the table prefixed with 'performance_columns' and get the results as an array.
        return $column; // Return the resulting array.
    }


    public function tblsa_applicant_tracker($ids = [])
    {
        // 1. Separate normal column ids and additional column ids first
        $normal_ids = [];
        $additional_ids = [];

        $normal_ids = [];
        $additional_ids = [];
        $check_con = (is_admin() || is_postSale()) ? 1 : 0;

        // $this->db->query("SET sql_mode = ''");
        if (!empty($ids)) {
            // First fetch columns field
            $this->db->select('id, columns,is_postsale');
            $this->db->where_in('id', $ids);
            if (empty($check_con)) {
                $this->db->where('is_postsale', 0);
            }

            if (!empty($ids)) {
                $this->db->order_by('FIELD(id, ' . implode(',', $ids) . ')');
            } else {
                $this->db->order_by('sequence', 'ASC');
            }
            $column_data = $this->db->get(db_prefix() . 'sa_applicant_tracker')->result_array();


            foreach ($column_data as $col) {
                if ($col['columns'] == '') {
                    $normal_ids[] = $col['id']; // Normal column
                } else {
                    if (!empty($col['columns'])) {
                        $normal_ids = array_merge($normal_ids, explode(",", $col['columns'])); // Merge directly
                    }
                }
            }
        }

        // Now merge normal + additional ids
        $columns_ids = $normal_ids; // Unique to avoid duplicates




        $this->db->select('*, columnid as tbl_column_name,if(sequence=0,999999,sequence) sequence,is_postsale'); // Select all columns (*) and alias 'columnid' as 'tbl_column_name'.
        $this->db->where('show_column', '1'); // Add a condition where 'show_column' equals '1'.

        if (empty($check_con)) {
            $this->db->where('is_postsale', 0);
        }

        if (!empty($ids)) { // Check if the $ids variable is not empty.
            $this->db->where_in('id', $columns_ids); // Add a condition to match multiple 'id' values in the $columns_ids array.
        }


        if (!empty($columns_ids)) {
            $this->db->order_by('FIELD(id, ' . implode(',', $columns_ids) . ')');
        } else {
            $this->db->order_by('sequence', 'ASC');
        }
        $column = $this->db->get(db_prefix() . 'sa_applicant_tracker')->result_array();

        return $column; // Return the resulting array.
    }
    public function delete_visit($id)
    {
        $this->db->where('visit_id', $id);
        $this->db->delete(db_prefix() . 'visitor_activity_log');

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'visitor_request');


        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function view_form()
    {

        $this->db->select(' l.website id,l.website name ');
        $this->db->from(db_prefix() . 'leads AS l');
        $this->db->where("website!=", '');
        $this->db->group_by('l.website');
        $this->db->order_by('l.website', 'asc');

        return $this->db->get()->result_array();
    }
    
    
 public function check_lead_auto_transfer_lead()
{
 
    $this->load->library('merge_fields/App_merge_fields');
    $this->load->library('app_object_cache');
    $this->load->library('mails/App_mail_template');
  
    // phpinfo();
    $this->load->library('Fcm_lib'); // file: application/libraries/Fcm.php
    //  $this->load->library('mails/Lead_assigned');
        // $this->load->helper('google');
    

    try {

        $cache_key = 'auto_transfer_leads_cache';

        // Load cache driver
        $this->load->driver('cache', ['adapter' => 'file']);

        // Check cache (60 seconds)
        if ($cached = $this->cache->get($cache_key)) {
            // return $this->output
            //     ->set_content_type('application/json')
            //     ->set_output(json_encode([
            //         "status" => true,
            //         "message" => "Loaded from cache",
            //         "data" => $cached
            //     ]));
        }

        $now = date('Y-m-d H:i:s');

        $this->db->select('
            l.id,
            l.name,
            l.phonenumber,
            l.alternative_phonenumber,
            l.email,
            l.dateadded,
            l.lastcontact,
            l.dateassigned,
            l.lastupdate_date,
            l.update_count,
            l.call_duration,
            l.auto_transfer_status,
            l.assigned,
            l.website,
            l.status,
            l.from_form_id,
            l.type,
               l.state,
               l.city,
               st.office_location_region,
               st.office_state_region,
            s.name as status_name,
            src.name as source_name,
            t.name as type_name,
            st.phonenumber as staff_contact,
            st.fcm_token as fcm_token,
            stt.phonenumber as team_leader_contact,
            stt.fcm_token as leader_fcm_token,
            CONCAT(st.firstname," ",st.lastname) as assigned_name,
            CONCAT(stt.firstname," ",stt.lastname) as team_leader,
           
            TIMESTAMPDIFF(MINUTE, l.dateassigned, "'.$now.'") AS diff_minutes
        ', false);

//  FROM_UNIXTIME(c.call_start + 19800) AS call_time,
        $this->db->from('tblleads l');

        $this->db->join('tblstaff st', 'st.staffid = l.assigned', 'left');

        $this->db->join(
            'tblstaff stt',
            'stt.staffid = if(st.reporting_person>0,st.reporting_person,1)',
            'left',
            false
        );

        // $this->db->join(
        //     '(SELECT contact, staffid, MAX(call_start) AS call_start 
        //       FROM tblcalls_activity_logs 
        //       GROUP BY contact, staffid) c',
        //     'c.staffid = l.assigned 
        //      AND c.contact IN (
        //         COALESCE(NULLIF(l.phonenumber,""), NULL),
        //         COALESCE(NULLIF(l.alternative_phonenumber,""), NULL)
        //      )',
        //     'left',
        //     false
        // );

        $this->db->join('tblleads_status s', 's.id = l.status');
        $this->db->join('tblleads_sources src', 'src.id = l.source');
        $this->db->join('tblleads_type t', 't.id = l.type');

        $this->db->where('l.update_count', 0);
        $this->db->where('l.call_duration', 0);

        // $this->db->where('(l.lastupdate_date = "0000-00-00" OR l.lastupdate_date >= l.dateassigned)', NULL, FALSE);
        // $this->db->where('c.call_start IS NULL', NULL, FALSE);

        $this->db->where("TIMESTAMPDIFF(MINUTE, l.dateassigned, '$now') >= 90", NULL, FALSE);

        $this->db->where('l.status', 2);
        // $this->db->where('l.auto_transfer_status!=', 2);
         $this->db->where('l.from_form_id!=', 0);
          $this->db->where('l.mass_assigned_status', 0);
           $this->db->where('l.assigned', 170);
           $this->db->where('l.id', 431037);
      
      

        // $this->db->where_in('l.id', [447809 ,447874]);

        $this->db->where('DATE(l.dateassigned) >=', START_AUTO_LEAD_TRANSFER_DATE);

        $this->db->order_by('l.dateassigned', 'DESC');

        $query = $this->db->get();
        $result = $query->result_array();
        // echo $this->db->last_query();

        $filtered = [];
        $bulkNotifications = [];
        $leader_bulkNotifications=[];
 

        foreach ($result as $res) {

            $seconds = calculate_business_seconds(
                $res['dateassigned'],
                date('Y-m-d H:i:s')
            );

            $res['diff_minutes'] = $seconds;

          if ($seconds >= 7200 && $res['auto_transfer_status']== 2) {

        $transferData = [];
        $staffId = 1; // default staff

        // if (!empty($res['from_form_id'])) {

        //     $form = $this->get_form([
        //         'id' => $res['from_form_id']
        //     ]);

        //     if (!empty($form) && !empty($form->assigned)) {
        //         $staffId = $form->assigned;
        //     }
            
        //     // if (!empty($form->facebook_status) && $form->facebook_status == 1) {
                
        //     //      $state_name = !empty($res['state']) ? $res['state'] : '';
        //     //         $lead_type = !empty($res['type']) ? $res['type'] : '';
                    
                    
        //     //         if (!empty($lead_type)) {
        //     //             $facebook_lead_name = !empty($res['website']) ? $res['website'] : '';
        //     //             $assign_staff_id = $this->automatic_assign_staff('', $lead_type, '', $facebook_lead_name,'','',$res['assigned']);
        //     //             $status_fb_lead_assign = false;
        //     //             if (!empty($assign_staff_id)) {
        //     //                 foreach ($assign_staff_id as $fl) {
        //     //                     if (!empty($fl["facebook_lead_name"])) {
        //     //                         $fb_form_name = explode(",", $fl["facebook_lead_name"]);
        //     //                         if (!empty($fb_form_name)) {
        //     //                             foreach ($fb_form_name as $fb_name) {
        //     //                                 if (!empty($fb_name) && $status_fb_lead_assign == false) {
        //     //                                     if (strpos(strtolower(trim($facebook_lead_name)), strtolower(trim($fb_name))) !== false) {
        //     //                                         $staffId = $fl["staffid"];
        //     //                                         $status_fb_lead_assign = true;
        //     //                                     }
        //     //                                 }
        //     //                                 if ($status_fb_lead_assign == true) {
        //     //                                     break;
        //     //                                 }
        //     //                             }
        //     //                         }
        //     //                     }
        //     //                 }
        //     //             }
        //     //             if ($status_fb_lead_assign == false) {
        //     //                 if (!empty($lead_type)) {
        //     //                     $assign_staff_id = $this->automatic_assign_staff('', $lead_type, 1,'','','',$res['assigned']);
        //     //                     if (!empty($assign_staff_id[0]["staffid"])) {
        //     //                         $staffId = $assign_staff_id[0]["staffid"];
        //     //                     }
        //     //                 }
        //     //             }
        //     //         }
                
        //     // }
            
        //     //   if (!empty($form->auto_assign)) {
        //     //         $auto_assign = array_filter(explode(",", $form->auto_assign));
        //     //         $assign_staff_id = $this->automatic_assign_staff('', '', '', '', $auto_assign,'',$res['assigned']);
        //     //         if (!empty($assign_staff_id[0]["staffid"])) {
        //     //             $staffId = $assign_staff_id[0]["staffid"];
        //     //         }
        //     //     }
                
                
        //     //       if (!empty($form->state_wise)  && $form->state_wise == 1) {
        //     //         $staffId = 1;

        //     //         if (!empty($form->allow_state_location) && $form->allow_state_location == 1) {
        //     //             $state_name = !empty($res['state']) ? trim($res['state']) : '';
        //     //             $city_name = !empty($res['city']) ? trim($res['city']) : '';
        //     //         } else {
        //     //             $ip = $_SERVER['REMOTE_ADDR'];
        //     //             $ipdetails = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
        //     //             $state_name = !empty($ipdetails->region) ? trim($ipdetails->region) : '';
        //     //             $city_name = !empty($ipdetails->city) ? trim($ipdetails->city) : '';
        //     //         }
        //     //         $lead_type = !empty($res["type"]) ? trim($res["type"]) : '';
        //     //         if (empty($lead_type)) {
        //     //             $lead_type = !empty($form->lead_type) ? trim($form->lead_type) : '';
        //     //         }
        //     //         $status_assign = false;

        //     //         if (!empty($city_name) && $status_assign == false) {
        //     //             $assign_staff_id = $this->leads_model->automatic_assign_staff_city($city_name, $lead_type, '', '', '', $google_source,$res['assigned']);
        //     //             if (!empty($assign_staff_id[0]["staffid"])) {
        //     //                 $form->responsible = $assign_staff_id[0]["staffid"];
        //     //                 $status_assign = true;
        //     //             }
        //     //         }

        //     //         if (!empty($state_name)  && $status_assign == false) {
        //     //             $assign_staff_id = $this->leads_model->automatic_assign_staff($state_name, $lead_type, '', '', '', $google_source,$res['assigned']);
        //     //             if (!empty($assign_staff_id[0]["staffid"])) {
        //     //                 $form->responsible = $assign_staff_id[0]["staffid"];
        //     //                 $status_assign = true;
        //     //             }
        //     //         } else if (!empty($lead_type)  && $status_assign == false) {
        //     //             $assign_staff_id = $this->leads_model->automatic_assign_staff('', $lead_type, 1,'','','',$res['assigned']);
        //     //             if (!empty($assign_staff_id[0]["staffid"])) {
        //     //                 $staffId = $assign_staff_id[0]["staffid"];
        //     //             }
        //     //         }
        //     //     }
            

        // } else {

        //     $assign_staff_id = $this->automatic_assign_staff('', $res['type'],'','','','',$res['assigned']);

        //     if (is_array($assign_staff_id) && !empty($assign_staff_id[0]['staffid'])) {
        //         $staffId = $assign_staff_id[0]['staffid'];
        //     }
        // }
        
        
  
        $assign_staff_id = $this->transferLeadAssignation_distribution($res['type']??'',$res['assigned']??'',$res['office_location_region']??'',$res['office_state_region']??'');
        
        if(empty($assign_staff_id[0]["staffid"]))
        {
        $assign_staff_id =   $this->transferLeadAssignation_distribution($res['type'],$res['assigned']??'',$res['office_location_region']??'');
        }
        
        if(!empty($assign_staff_id[0]["staffid"]))
        {
        $staffId = $assign_staff_id[0]["staffid"];
        }
        
   

        $transferData = [
            'assigned' => $staffId,
            'last_status_change' => date('Y-m-d H:i:s'),
            'dateassigned' => date('Y-m-d H:i:s'),
            'auto_transfer_status'=>0,
            'status' => 2
        ];

        // Send notification only if staff changed
        if (!empty($res['assigned']) && $res['assigned'] != $staffId && $staffId != 0) {
            $this->lead_assigned_member_notification($res['id'], $staffId,'','',1);
        }

        if (!empty($res['id'])) {

            $this->db->where('id', $res['id']);
            $update = $this->db->update(db_prefix() . 'leads', $transferData);

            if (!$update) {
                log_message('error', 'Lead update failed for ID: ' . $res['id']);
            }
        }



    continue;
}
            else if ($seconds >= 6300 ) {

                $res['warning'] = 2;

                if ($res['auto_transfer_status'] != 2) {

                    $res['channel_type'] = 11;
                        $bulkNotifications[] = [
                'token' => $res['fcm_token'],
                'notification' => array("body"=>"{$res['name']} ({$res['phonenumber']}) from {$res['source_name']} has not been contacted for 01 Hour 45 minutes. Please call within the next 15 minutes to avoid reassignment.","title"=>"⚠️ Lead Not Contacted",
                "data"=>array("type"=>"CALL","url" => "tel:{$res['phonenumber']}")
                )
                ];
                    $filtered[] = $res;
                    

                    $res['staff_contact'] = $res['team_leader_contact'];
                    $res['channel_type'] = 10;
                    $filtered[] = $res;
                    
                    
                    // $leader_bulkNotifications[]=$res['fcm_token'];
                    
                    //leader_fcm_token update
                         $bulkNotifications[] = [
                'token' => $res['fcm_token'],
                'notification' => [
    "title" => "⚠️ Lead Not Contacted",
    "body"  => "Dear {$res['team_leader']}, the lead {$res['assigned_name']} - {$res['name']} ({$res['phonenumber']}) from {$res['source_name']} has not been contacted for 1 hour 45 minutes. Please review and take the necessary action."
    // "data" => ["type" => "CALL", "url" => "tel:{$res['phonenumber']}"]
]
                ];
                }

            } elseif ($seconds >= 5400   ) {

                $res['warning'] = 1;
                // $res['channel_type'] = 9;
                

                if ($res['auto_transfer_status'] != 1) {
                $filtered[] = $res;
                $bulkNotifications[] = [
                'token' => $res['fcm_token'],
                'notification' => array("body"=>"{$res['name']} ({$res['phonenumber']}) from {$res['source_name']} has not been contacted for 1 Hour 30 minutes. Please call within the next 30 minutes to avoid reassignment.","title"=>"⚠️ Lead Not Contacted",
                "data"=>array("type"=>"CALL","url" => "tel:{$res['phonenumber']}")
                )
                ];
                
                }
            }
        }
       
       if(!empty($bulkNotifications)){
      $this->fcm_lib->sendBulk_message($bulkNotifications);
       }



       if(!empty($filtered)){
        $this->auto_transfer_lead_fcm_notification($filtered);
       }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                "status" => true,
                // "total_records" => count($result),
                // "filtered_records" => count($filtered),
                // "data" => $filtered
            ]));

    } catch (Exception $e) {

        log_message('error', 'Lead Auto Transfer Error: '.$e->getMessage());

        return $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                "status" => false,
                "message" => $e->getMessage()
            ]));
    }
}




    function transferLeadAssignation($lead_type,$staff_not,$locationRegion='',$leadRegion='')
    {

        $sql = "Select 
    st.staffid,
    CONCAT(st.firstname, ' ', st.lastname) AS staff_name,
    last_lead.dateassigned
    from  " . db_prefix() . "staff st ";
       
        $sql .= " LEFT JOIN 
    (
        SELECT 
            assigned, 
            MAX(dateassigned) AS dateassigned
        FROM 
            " . db_prefix() . "leads
        GROUP BY 
            assigned
    ) AS last_lead 
    ON st.staffid = last_lead.assigned ";

        $sql .= " where 1=1 ";
       
        if (!empty($lead_type)) {
            $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        }
        
        if (!empty($leadRegion)) {
        $sql .= " and st.office_state_region = '" . trim($leadRegion) . "' ";
        }else if (!empty($lead_type)) {
        $sql .= " and st.office_location_region = '" . trim($lead_type) . "' ";
        }
    
         if (!empty($staff_not)) {
            $sql .= " and st.staffid !='".$staff_not."' ";
        }
     
     
     
   if (ACTIVE_STAFF_ONLY == 1) {

            $currentTime = date('H:i');
            $today       = date('Y-m-d');
            $dayOfWeek   = date('w'); // 0 = Sunday


            // Example holiday array (you can fetch from DB)
            $holidays = holiday_list();
            $isHoliday = in_array($today, $holidays);

            if ($dayOfWeek == 0 || $isHoliday) {
                // Sunday or Holiday → check last login date
                $sql .= " AND DATE(st.last_login) = (
                    SELECT MAX(DATE(last_login))
                    FROM tblstaff
                    WHERE DATE(last_login) < '$today'
                 ) ";
            } else {

                if ($currentTime >= '12:00') {
                    // Between 10 AM and 11 AM → check today only
                    $sql .= " AND (DATE(st.last_login) = '$today' 
                       OR DATE(st.last_activity) = '$today') ";
                } else {
                    // Before 10 AM → check today OR yesterday
                    // $yesterday = date('Y-m-d', strtotime('-1 day'));
                     $yesterday = $this->getLastWorkingDay($today, $holidays);

                    $sql .= " AND (
                        DATE(st.last_login) IN ('$today','$yesterday')
                        OR DATE(st.last_activity) IN ('$today','$yesterday')
                     ) ";
                }
            }
        }

        $sql .= " group by st.staffid,last_lead.dateassigned order by last_lead.dateassigned asc ";
        if (!empty($facebook_lead)) {
        } else {
            $sql .= " limit 1 ";
        }
        
    
        // $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned from " . db_prefix() . "states s join " . db_prefix() . "staff st ON (FIND_IN_SET(s.id,st.assign_state) and st.lead_type = '" . trim($lead_type) . "'  and st.active = '1') where LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc limit 1";
        return $this->db->query($sql)->result_array();
    }
    
    function transferLeadAssignation_distribution($lead_type,$staff_not,$locationRegion='',$leadRegion='')
    {
        
       
$sql = "SELECT 
    st.staffid,
    CONCAT(st.firstname, ' ', st.lastname) AS staff_name,
    last_lead.dateassigned
FROM " . db_prefix() . "staff st
LEFT JOIN (
    SELECT 
        assigned, 
        MAX(dateassigned) AS dateassigned
    FROM " . db_prefix() . "leads
    GROUP BY assigned
) AS last_lead 
ON st.staffid = last_lead.assigned";

if (!empty($leadRegion)) {
    $sql .= " JOIN (
        SELECT distribution_regions, non_staff_ids, lead_type
        FROM " . db_prefix() . "leads_distribution
        WHERE lead_region = '" . $leadRegion . "' and lead_type = '".$lead_type."'
    ) dis 
    ON FIND_IN_SET(st.office_state_region, dis.distribution_regions)
       AND (dis.non_staff_ids IS NULL OR FIND_IN_SET(st.staffid, dis.non_staff_ids) = 0)
       AND (dis.lead_type IS NULL OR dis.lead_type = st.lead_type)";
}

$sql .= " WHERE st.active = 1 and st.admin != 1 ";

if (!empty($leadRegion)) {
}
else
{
        if (!empty($lead_type)) {
            $sql .= " and st.lead_type = '" . trim($lead_type) . "' ";
        }
        
        if (!empty($leadRegion)) {
        $sql .= " and st.office_state_region = '" . trim($leadRegion) . "' ";
        }else if (!empty($locationRegion)) {
        $sql .= " and st.office_location_region = '" . trim($locationRegion) . "' ";
        }
    
}

// Optional filters
if (!empty($staff_not)) {
    $sql .= " AND st.staffid !='" . $staff_not . "' ";
}

// Handle ACTIVE_STAFF_ONLY logic
if (ACTIVE_STAFF_ONLY == 1) {

    $currentTime = date('H:i');
    $today       = date('Y-m-d');
    $dayOfWeek   = date('w'); // 0 = Sunday

    $holidays = holiday_list();
    $isHoliday = in_array($today, $holidays);

    if ($dayOfWeek == 0 || $isHoliday) {
        $sql .= " AND DATE(st.last_login) = (
            SELECT MAX(DATE(last_login))
            FROM " . db_prefix() . "staff
            WHERE DATE(last_login) < '$today'
        ) ";
    } else {
        if ($currentTime >= '12:00') {
            $sql .= " AND (DATE(st.last_login) = '$today' OR DATE(st.last_activity) = '$today') ";
        } else {
            $yesterday = $this->getLastWorkingDay($today, $holidays);
            $sql .= " AND (
                DATE(st.last_login) IN ('$today','$yesterday')
                OR DATE(st.last_activity) IN ('$today','$yesterday')
            ) ";
        }
    }
}

// Group & order
$sql .= " GROUP BY st.staffid, last_lead.dateassigned
          ORDER BY last_lead.dateassigned ASC ";

// Limit
if (empty($facebook_lead)) {
    $sql .= " LIMIT 1";
}
        

        // $sql = "Select s.name,st.staffid ,CONCAT(st.firstname,' ',st.lastname) staff_name,(select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned  desc limit 1) dateassigned from " . db_prefix() . "states s join " . db_prefix() . "staff st ON (FIND_IN_SET(s.id,st.assign_state) and st.lead_type = '" . trim($lead_type) . "'  and st.active = '1') where LOWER(TRIM(s.name)) = '" . strtolower(trim($state_name)) . "' order by (select dateassigned from " . db_prefix() . "leads where assigned = st.staffid order by dateassigned desc limit 1) asc limit 1";
        return $this->db->query($sql)->result_array();
    
    }
function autoTransferLeads($leadData, $leadconvertStatus = 2)
{
    
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

    // Get form details
    $check_form = $this->get_form([
        'id' => $leadData['from_form_id'],
    ]);

    if (empty($check_form)) {
        return true;
    }

    // Determine Facebook status
    $check_form->facebook_status = !empty($leadData['website']) ? 1 : 0;

    $google_source = !empty($check_form->lead_source) ? $check_form->lead_source : '';

  $transferType = $leadData["trnasfer_type"]??0;
 


    /*
    |--------------------------------------------------------------------------
    | Facebook Lead Assignment
    |--------------------------------------------------------------------------
    */
    // if (!empty($check_form->facebook_status) && $check_form->facebook_status == 1) {

    //     $check_form->responsible = 1;
    //     $lead_type = $leadData['type'] ?? '';

    //     if (!empty($lead_type)) {

    //         $facebook_lead_name = $leadData['website'] ?? '';
    //         $assign_staff_id = $this->automatic_assign_staff(
    //             '',
    //             $lead_type,
    //             '',
    //             $facebook_lead_name,
    //             '',
    //             '',
    //             $leadData['assigned'] ?? ''
    //         );

    //         $status_fb_lead_assign = false;

    //         if (!empty($assign_staff_id)) {
    //             foreach ($assign_staff_id as $fl) {

    //                 if (!empty($fl["facebook_lead_name"])) {
    //                     $fb_form_names = explode(",", $fl["facebook_lead_name"]);

    //                     foreach ($fb_form_names as $fb_name) {
    //                         if (
    //                             !empty($fb_name) &&
    //                             !$status_fb_lead_assign &&
    //                             strpos(strtolower(trim($facebook_lead_name)), strtolower(trim($fb_name))) !== false
    //                         ) {
    //                             $check_form->responsible = $fl["staffid"];
    //                             $status_fb_lead_assign = true;
    //                             break;
    //                         }
    //                     }
    //                 }

    //                 if ($status_fb_lead_assign) {
    //                     break;
    //                 }
    //             }
    //         }

    //         // Fallback assignment
    //         if (!$status_fb_lead_assign) {
    //             $assign_staff_id = $this->automatic_assign_staff(
    //                 '',
    //                 $lead_type,
    //                 1,
    //                 '',
    //                 '',
    //                 '',
    //                 $leadData['assigned'] ?? ''
    //             );

    //             if (!empty($assign_staff_id[0]["staffid"])) {
    //                 $check_form->responsible = $assign_staff_id[0]["staffid"];
    //             }
    //         }
    //     }

    // /*
    // |--------------------------------------------------------------------------
    // | Auto Assign
    // |--------------------------------------------------------------------------
    // */
    // } elseif (!empty($check_form->auto_assign)) {

    //     $auto_assign = array_filter(explode(",", $check_form->auto_assign));

    //     $assign_staff_id = $this->automatic_assign_staff(
    //         '',
    //         '',
    //         '',
    //         '',
    //         $auto_assign,
    //         '',
    //         $leadData['assigned'] ?? ''
    //     );

    //     if (!empty($assign_staff_id[0]["staffid"])) {
    //         $check_form->responsible = $assign_staff_id[0]["staffid"];
    //     }

    // /*
    // |--------------------------------------------------------------------------
    // | State-wise Assignment
    // |--------------------------------------------------------------------------
    // */
    // } elseif (!empty($check_form->state_wise) && $check_form->state_wise == 1) {

    //     $check_form->responsible = 1;

    //     if (!empty($check_form->allow_state_location) && $check_form->allow_state_location == 1) {
    //         $state_name = trim($leadData['state'] ?? '');
    //         $city_name  = trim($leadData['city'] ?? '');
    //     } else {
    //         $ip = $_SERVER['REMOTE_ADDR'];
    //         $ipdetails = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

    //         $state_name = trim($ipdetails->region ?? '');
    //         $city_name  = trim($ipdetails->city ?? '');
    //     }

    //     $lead_type = trim($leadData["type"] ?? $check_form->lead_type ?? '');
    //     $status_assign = false;

    //     // City-based assignment
    //     if (!empty($city_name) && !$status_assign) {
    //         $assign_staff_id = $this->automatic_assign_staff_city(
    //             $city_name,
    //             $lead_type,
    //             '',
    //             '',
    //             '',
    //             $google_source,
    //             $leadData['assigned'] ?? ''
    //         );

    //         if (!empty($assign_staff_id[0]["staffid"])) {
    //             $check_form->responsible = $assign_staff_id[0]["staffid"];
    //             $status_assign = true;
    //         }
    //     }

    //     // State-based assignment
    //     if (!empty($state_name) && !$status_assign) {
    //         $assign_staff_id = $this->automatic_assign_staff(
    //             $state_name,
    //             $lead_type,
    //             '',
    //             '',
    //             '',
    //             $google_source,
    //             $leadData['assigned'] ?? ''
    //         );

    //         if (!empty($assign_staff_id[0]["staffid"])) {
    //             $check_form->responsible = $assign_staff_id[0]["staffid"];
    //             $status_assign = true;
    //         }
    //     }

    //     // Fallback
    //     if (!$status_assign && !empty($lead_type)) {
    //         $assign_staff_id = $this->automatic_assign_staff(
    //             '',
    //             $lead_type,
    //             1,
    //             '',
    //             '',
    //             '',
    //             $leadData['assigned'] ?? ''
    //         );

    //         if (!empty($assign_staff_id[0]["staffid"])) {
    //             $check_form->responsible = $assign_staff_id[0]["staffid"];
    //         }
    //     }
    // }
    
     $check_form->responsible = 1;
    //  echo "okkkk";
  $assign_staff_id =   $this->transferLeadAssignation_distribution($leadData['type'],$leadData['assigned']??'',$leadData['office_location_region']??'',$leadData['office_state_region']??'');
  
     if(empty($assign_staff_id[0]["staffid"]))
  {
      $assign_staff_id =   $this->transferLeadAssignation_distribution($leadData['type'],$leadData['assigned']??'',$leadData['office_location_region']??'');
  }

//   if(empty($assign_staff_id[0]["staffid"]))
//   {
//       $assign_staff_id =   $this->transferLeadAssignation($leadData['type'],$leadData['assigned']??'',$leadData['office_location_region']??'');
//   }
//   echo $this->db->last_query();
    //  echo  "okkkkkkkkk";
    //  print_r($assign_staff_id);
     
    //  return true;
    //  die;
    //  print_r($assign_staff_id);
        if (!empty($assign_staff_id[0]["staffid"])) {
         $check_form->responsible = $assign_staff_id[0]["staffid"];
        }

    /*
    |--------------------------------------------------------------------------
    | Update Lead Status
    |--------------------------------------------------------------------------
    */
    $this->update_lead_status([
        "leadid" => $leadData['id'],
        "status" => $leadconvertStatus ?? 2
    ]);

    /*
    |--------------------------------------------------------------------------
    | Update Assignment
    |--------------------------------------------------------------------------
    */
    
   
    if (!empty($leadData['id']) && (int)$leadData['id'] > 0) {

        $responsible = $check_form->responsible ?? 1;
        
        $leadDataUpdate = [
        'assigned' => $responsible
        ];
        
        if (!empty($transferType) && $transferType == 1) {
        $leadDataUpdate['transfer_count'] = !empty($leadData['update_count']) 
            ? $leadData['update_count'] + 1 
            : 1;
        }
        
        $this->db->where('id', (int)$leadData['id']);
        $update = $this->db->update(db_prefix() . 'leads', $leadDataUpdate);

        if ($update && $this->db->affected_rows() > 0) {
    
    $insert_Data_Logs = [
        "leadid"=>$leadData["id"],
        "phonenumber"=>$leadData["phonenumber"],
        "name"=>$leadData["name"],
        "old_status"=>$leadData['status'],
        "new_status"=> $leadconvertStatus,
        "old_assignation"=>$leadData['assigned'],
        "new_assignation"=>$check_form->responsible??1,
        "old_assignation_date"=>$leadData['dateassigned'],
        "new_assignation_date"=>date('Y-m-d H:i:s'),
        "update_count"=>$leadData["update_count"],
        "created_at"=>date('Y-m-d H:i:s')
        ];

  $this->db->insert(db_prefix().'leads_transfer_logs', $insert_Data_Logs);
  
            if (
                !empty($leadData['assigned']) &&
                $leadData['assigned'] != $responsible &&
                $responsible != 0
            ) {
                $this->lead_assigned_member_notification(
                    $leadData['id'],
                    $responsible,
                    '',
                    '',
                    1
                );
            }

        } else {
            log_message('error', 'Lead update failed or no changes. ID: ' . $leadData['id']);
        }
    }
}

// Not Reachable Leads
public function check_lead_auto_assignation_lead()
{


     $this->load->library('merge_fields/App_merge_fields');
    $this->load->library('app_object_cache');
    $this->load->library('mails/App_mail_template');
    
 $date = date('Y-m-d');


$holidays = holiday_list();
$ignoreDates = [];

// ✅ Add last Sunday
$ignoreDates[] = date('Y-m-d', strtotime('last sunday'));

if (!empty($holidays)) {
    foreach ($holidays as $h) {

        // normalize date
        if (is_array($h) && isset($h['date'])) {
            $hDate = date('Y-m-d', strtotime($h['date']));
        } elseif (is_object($h) && isset($h->date)) {
            $hDate = date('Y-m-d', strtotime($h->date));
        } else {
            $hDate = date('Y-m-d', strtotime($h));
        }

        // ✅ ONLY include if within last 7 days
        if (
            strtotime($hDate) >= strtotime('-7 days') &&
            strtotime($hDate) <= strtotime('today')
        ) {
            $ignoreDates[] = $hDate;
        }
    }
}

// remove duplicates
$ignoreDates = array_unique($ignoreDates);

$sqlAdditional='';
if(!empty($ignoreDates))
{
    $ignoreDates = "'" . implode("','", $ignoreDates) . "'";
    $sqlAdditional = " AND date(l.dateassigned) Not In ($ignoreDates) ";
}

      $workingDays = 0;
$offset = 0;

while ($workingDays < 4) {

    $day  = date('w', strtotime($date)); // 0 = Sunday

    if ($day != 0 && !in_array($date, $holidays)) {
        // ✅ valid working day
        $workingDays++;
    }

    $offset++;
}






   $sql = "SELECT 
  l.id, 
  l.name, 
  l.phonenumber, 
  l.alternative_phonenumber, 
  l.email, 
  l.dateadded, 
  l.lastcontact, 
  l.dateassigned, 
  l.lastupdate_date, 
  IFNULL(c.call_update_count, 0) AS update_count,
  l.call_duration, 
  l.auto_transfer_status, 
  l.assigned, 
  l.website, 
  l.status, 
  l.from_form_id, 
  l.type, 
  l.state, 
  l.city, 
  1 as trnasfer_type,
  s.name AS status_name, 
  src.name AS source_name, 
  t.name AS type_name, 
  l.transfer_count,
  st.phonenumber AS staff_contact, 
  st.office_state_region,
  st.office_location_region,
  CONCAT(st.firstname, ' ', st.lastname) AS assigned_name, 
  FROM_UNIXTIME(c.call_start + 19800) AS call_time, 
  TIMESTAMPDIFF(MINUTE, l.dateassigned, NOW()) AS diff_minutes,
  IFNULL(c.call_update_count, 0) AS call_update_count,
  date(DATE_SUB(NOW(), INTERVAL $offset DAY) )

FROM tblleads l

LEFT JOIN tblstaff st 
  ON st.staffid = l.assigned 


LEFT JOIN (
    SELECT 
        contact, 
        staffid,
        MAX(call_start) AS call_start,
        COUNT(*) AS call_update_count
    FROM tblcalls_activity_logs
    GROUP BY contact, staffid
) c 
  ON c.staffid = l.assigned 
  AND (
        c.contact = l.phonenumber 
        OR c.contact = l.alternative_phonenumber
      )

JOIN tblleads_status s 
  ON s.id = l.status 

JOIN tblleads_sources src 
  ON src.id = l.source 

JOIN tblleads_type t 
  ON t.id = l.type 

WHERE 
1=1
   AND l.status IN (20)
   AND IFNULL(c.call_update_count, 0) < 5
   AND l.from_form_id != 0
   AND l.type IN (1,2)
   AND l.mass_assigned_status!=1
   AND l.dateadded >= '".START_AUTO_LEAD_TRANSFER_DATE."'
   AND st.not_transfer_lead_status !=1
   AND date(l.dateassigned) < date(DATE_SUB(NOW(), INTERVAL $offset DAY)) ".$sqlAdditional."
   AND l.transfer_count < 3

  ORDER BY l.id limit 50 "; 



// ✅ Execute query
$query = $this->db->query($sql);
$result = $query->result_array();


//   
// 
//

//   

// print_r($result);
// die;
// echo "<pre>";

//     foreach ($result as $leadData)
// {
    // print_r($result);
//     print_r($this->autoTransferLeads($leadData));
// }


if (empty($result)) {
    echo json_encode(['status' => true]);
    die;
}

    foreach ($result as $leadData)
{
    $this->autoTransferLeads($leadData);
}



// ✅ Debug output

}

// Monday cron
public function weekend_lead_assignation()
{


    $today       = date('Y-m-d');
    $createdDate = date('Y-m-d H:i:s');
    $holidays = holiday_list();

  // Get last working day
     $last_working_day = $this->getLastWorkingDay($today, $holidays);


    // Format holidays for SQL
    $holidays_str = !empty($holidays) ? "'" . implode("','", $holidays) . "'" : "''";


    // Get staff who logged in on last working day
  $staff_result = $this->db->query("
    SELECT staffid
    FROM " . db_prefix() . "staff
    WHERE active = 1
      AND admin != 1
      AND DATE(last_login) != CURDATE()
      AND DATE(last_login) != (
          SELECT MAX(DATE(last_login))
          FROM " . db_prefix() . "staff
          WHERE active = 1
            AND admin != 1
            AND DAYOFWEEK(last_login) != 1
            AND DATE(last_login) NOT IN ({$holidays_str})
      )
")->result_array();
         
    
    $staff_ids = array_column($staff_result, 'staffid');




if (!empty($staff_ids)) {
    $staff_ids_str = implode(',', $staff_ids);
    
    // Get leads assigned to these staff
     $sql = "
   Select * From ( (SELECT l.id as leadid, l.assigned, '$createdDate' as created_date,count(calls.id) update_count,l.phonenumber,l.name,l.status,l.dateassigned
    FROM " . db_prefix() . "leads l
    LEFT JOIN " . db_prefix() . "calls_activity_logs calls on (calls.contact = l.phonenumber and calls.staffid = l.assigned  AND (calls.call_start + 19800) >= UNIX_TIMESTAMP(l.dateassigned))
    JOIN " . db_prefix() . "staff s ON s.staffid = l.assigned
    
    WHERE
            l.lost = 0 
      AND l.junk = 0
      AND l.assigned IN ({$staff_ids_str})
      AND ( l.status = 2 or l.status=33 )
      AND l.mass_assigned_status != 1
      AND (
          DATE(l.dateadded) >= '$last_working_day' 
          OR DATE(l.dateassigned) >= '$last_working_day'
      )
      AND update_count = 0  GROUP by l.id
    ORDER BY l.id ) UNION ALL  (SELECT l.id as leadid, l.assigned, '$createdDate' as created_date,count(calls.id) update_count,l.phonenumber,l.name,l.status,l.dateassigned
    FROM " . db_prefix() . "leads l
    LEFT JOIN " . db_prefix() . "calls_activity_logs calls on (calls.contact = l.alternative_phonenumber and calls.staffid = l.assigned  AND (calls.call_start + 19800) >= UNIX_TIMESTAMP(l.dateassigned))
    JOIN " . db_prefix() . "staff s ON s.staffid = l.assigned
    
    WHERE
            l.lost = 0 
      AND l.junk = 0
      AND l.assigned IN ({$staff_ids_str})
      AND ( l.status = 2 or l.status=33 )
      AND l.mass_assigned_status != 1
      AND (
          DATE(l.dateadded) >= '$last_working_day' 
          OR DATE(l.dateassigned) >= '$last_working_day'
      )
      AND update_count = 0  GROUP by l.id
    ORDER BY l.id ) ) leadsData  GROUP by leadsData.leadid
    
";




$result = $this->db->query($sql)->result_array();


} else {
    $result = [];
}


// Get counts summary
$counts_by_staff = [];

foreach ($result as $lead) {
    $staff_id = $lead['assigned'];

    if (!isset($counts_by_staff[$staff_id])) {
        $counts_by_staff[$staff_id] = ['lead_count' => 0];
    }

    $counts_by_staff[$staff_id]['lead_count']++;
}

// Display counts
foreach ($counts_by_staff as $key => $staff) {
    echo "Staff ID: {$key} - Total Leads: {$staff['lead_count']}<br>";
}

die;
  $this->db->insert_batch(db_prefix().'weekend_lead_transfer', $result);
  return true;

}

public function weekend_lead_assignation_auto()
{
    
      $this->load->library('merge_fields/App_merge_fields');
    $this->load->library('app_object_cache');
    $this->load->library('mails/App_mail_template');
    
    
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    // Validate date
    $today = date('Y-m-d');
    if (empty($today)) {
        log_message('error', 'Invalid date in weekend_lead_assignation_auto');
        return false;
    }

    // Build query
    $this->db->select('tl.id as id,tl.phonenumber,tl.name,tl.dateassigned,tl.update_count,tl.from_form_id,tl.website,tl.state,tl.type,tl.website,tl.type,tl.city,tl.assigned,tl.status,2 trnasfer_type');
    $this->db->from(db_prefix().'weekend_lead_transfer wlt');
    $this->db->join(db_prefix().'leads tl', 'tl.id = wlt.leadid', 'left');
    $this->db->where('DATE(wlt.created_date)', $today);
    $this->db->where('wlt.leadid', 456237);

    $query = $this->db->get();
    
    
    echo "<pre>";
    print_r($query->result_array());
die;        

    // Check query execution
    if (!$query) {
        log_message('error', 'DB error: '.$this->db->last_query());
        return false;
    }

    $consents = $query->result_array();

    // Validate result
    if (empty($consents)) {
        log_message('info', 'No weekend leads found for date: '.$today);
        return true; // nothing to process
    }
    
    

    foreach ($consents as $leadData) {

        // Validate required fields
        if (empty($leadData['id'])) {
            log_message('error', 'Missing id: '.json_encode($leadData));
            continue;
        }

        try {
            $this->autoTransferLeads($leadData,$leadData['status']??'');
        } catch (Exception $e) {
            log_message('error', 'Error in autoTransferLeads: '.$e->getMessage());
            continue;
        }
    }

    return true;
}
    
    
public function auto_transfer_lead_notification($filtered)
{
    $insert_Data = [];
    $leadId_Data = [];

    foreach ($filtered as $key => $data)
    {
        // Insert data
        $insert_Data[$key]["phonenumber"]  = $data["phonenumber"];
        $insert_Data[$key]["contact"]  = $data["staff_contact"];
        $insert_Data[$key]["status"]       = 2;
        $insert_Data[$key]["staff_id"]     = $data["assigned"];
        $insert_Data[$key]["channel_type"] = $data["channel_type"];
        $insert_Data[$key]["data"]         = json_encode($data, true);
        $insert_Data[$key]["created_at"]   = date('Y-m-d H:i:s');

        // Update data
        $leadId_Data[$key]["id"] = $data["id"];
        $leadId_Data[$key]["auto_transfer_status"] = $data["warning"];
    }

    // Batch insert
    if (!empty($insert_Data)) {
        $this->db->insert_batch(db_prefix().'lead_auto_transfer_notification_whatsapp_log', $insert_Data);
    }

    // Batch update
    if (!empty($leadId_Data)) {
        $this->db->update_batch(db_prefix().'leads', $leadId_Data, 'id');
    }
}

public function auto_transfer_lead_fcm_notification($filtered)
{
    $insert_Data = [];
    $leadId_Data = [];

    foreach ($filtered as $key => $data)
    {
        // Insert data
        $insert_Data[$key]["leadid"]  = $data["id"];
        $insert_Data[$key]["name"]  = $data["name"];
        $insert_Data[$key]["status"]       = 1;
        $insert_Data[$key]["phonenumber"]     = $data["phonenumber"];
        $insert_Data[$key]["email"] = $data["email"];
        $insert_Data[$key]["dateassigned"]  = $data["dateassigned"];
        $insert_Data[$key]["update_count"]  = $data["update_count"];
        $insert_Data[$key]["call_duration"]     = $data["call_duration"];
        $insert_Data[$key]["assigned"] = $data["assigned"];
        $insert_Data[$key]["created_date"]   = date('Y-m-d H:i:s'); 
        $insert_Data[$key]["status_name"]  = $data["status_name"];
        $insert_Data[$key]["source_name"]  = $data["source_name"];
        $insert_Data[$key]["type_name"]     = $data["type_name"];
        $insert_Data[$key]["fcm_token"] = $data["fcm_token"];
        $insert_Data[$key]["leader_fcm_token"] = $data["leader_fcm_token"];
        $insert_Data[$key]["assigned_name"] = $data["assigned_name"];
        $insert_Data[$key]["team_leader"] = $data["team_leader"];
        $insert_Data[$key]["diff_minutes"] = $data["diff_minutes"];
        $insert_Data[$key]["warning"] = $data["warning"];
        $insert_Data[$key]["status"] = $data["status"];
        $insert_Data[$key]["data"]         = json_encode($data, true);

        // Update data
        $leadId_Data[$key]["id"] = $data["id"];
        $leadId_Data[$key]["auto_transfer_status"] = $data["warning"];
    }

    // Batch insert
    if (!empty($insert_Data)) {
        $this->db->insert_batch(db_prefix().'lead_auto_transfer_fcm_notification_logs', $insert_Data);
    }

    // Batch update
    if (!empty($leadId_Data)) {
   
        $this->db->update_batch(db_prefix().'leads', $leadId_Data, 'id');
    }
}
public function transfer_whatsapp_notification()
{
    $success = 0;
    $failed  = 0;
    $total   = 0;

    try {

        $this->db->where('status', 2);
        $this->db->where('cron_time <', date('Y-m-d H:i:s'));

        $query  = $this->db->get(db_prefix().'lead_auto_transfer_notification_whatsapp_log');
        $result = $query->result();

        if (empty($result)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => true,
                    'message' => 'No pending notifications',
                    'data'    => []
                ]));
        }

        $total = count($result);
        $leadId_Data =[];
        foreach ($result as $key => $row) {

            try {

                $this->db->trans_begin();

                $response = send_whatsaap_notification_lead_transfer(
                    $row->contact,
                    $row->channel_type,
                    $row->data
                );

                if (empty($response)) {
                    throw new Exception('WhatsApp sending failed for ID: '.$row->id);
                }

                // mark success
                $this->db->where('id', $row->id)
                    ->update(db_prefix().'lead_auto_transfer_notification_whatsapp_log', [
                        'status'     => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('DB update failed for ID: '.$row->id);
                }

                $this->db->trans_commit();
                $success++;

            } catch (Exception $e) {

                $this->db->trans_rollback();

                $this->db->where('id', $row->id)
                    ->update(db_prefix().'lead_auto_transfer_notification_whatsapp_log', [
                        'status'     => 0,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'cron_time' => date('Y-m-d H:i:s')
                    ]);

                log_message('error','WhatsApp Notification Error : '.$e->getMessage());
                $failed++;
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'WhatsApp notification process completed',
                'summary' => [
                    'total'   => $total,
                    'success' => $success,
                    'failed'  => $failed
                ]
            ]));

    } catch (Exception $e) {

        log_message('error','Cron WhatsApp Transfer Error : '.$e->getMessage());

        return $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => false,
                'message' => 'Server Error',
                'error'   => $e->getMessage()
            ]));
    }
}

function google_qualified_leads($type, $start = 0)
{
    $this->db->select("DATE_FORMAT(created_at, '%m/%d/%Y %l:%i:%s %p') AS formatted_date, email, phonenumber");
    $this->db->from(db_prefix().'leads_google_performnce_logs');
    $this->db->where('source', '39');
    $this->db->where('type', $type);
    $this->db->limit(100, $start); // limit, offset

    $query = $this->db->get();

    // Debug query (optional)
    // echo $this->db->last_query();

    return $query->result_array();
}


// function leads_transfers_summary($data)
// {
 
//  $get_staff_user_id = get_staff_user_id();
//     $idsarr =[];
//   if (!empty($_POST['view_assigned'])) {
// }else
// {
    
    
   
        
//      $role = $this->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
//      $sid = $get_staff_user_id;
// if ($role == 3) {
// $teamids = $this->db->query('CALL GetReportingPersons(?)', array($get_staff_user_id))->result_array();

// $this->db->close();
// $this->db->initialize();

// $idsarr = array_column($teamids, 'staffid');


      

// }
// }



//   // Build query
//   if($_POST['leadType'] == "Lead Assignation")
// {
//      $this->db->select("CONCAT(s.firstname, ' ', s.lastname) AS staff_name, COUNT(l.id) AS counts");
//         $this->db->from('tblleads_transfer_logs l');
//         $this->db->join('tblstaff s', 's.staffid = l.new_assignation', 'inner');
//          $this->db->join('tblleads lead', 'lead.id = l.leadid', 'inner');
// }
// else{
//         $this->db->select("CONCAT(s.firstname, ' ', s.lastname) AS staff_name, COUNT(l.id) AS counts");

//         $this->db->from('tblleads_transfer_logs l');
//         $this->db->join('tblstaff s', 's.staffid = l.old_assignation', 'inner');
//          $this->db->join('tblleads lead', 'lead.id = l.leadid', 'inner');
        
// }
        
//         if(!empty($_POST['view_status']))
//         {
//         $this->db->where_in('l.old_status', $_POST['view_status']);
//         }
//           if(!empty($_POST['view_sources']))
//         {
//         $this->db->where_in('lead.source', $_POST['view_sources']);
//         }
        
      
        
//         if(!empty($_POST['view_update_count']))
//         {
//         $this->db->where_in('l.update_count', $_POST['view_update_count']);
//         }
        
        
       
        
       
        
//       if (!empty($this->input->post('date_range'))) {
//     // Get date range from POST
//     $dateRange = $this->input->post('date_range'); // e.g. "2026-04-01 to 2026-04-02"
    
//     // Split by 'to'
//     $dates = explode(' to ', $dateRange);

//     if (count($dates) === 2) {
//         $startDate = trim($dates[0]);
//         $endDate = trim($dates[1]);

//         // Apply CI3 where condition
//         $this->db->where('DATE(l.created_at) >=', $startDate);
//         $this->db->where('DATE(l.created_at) <=', $endDate);
//     }
// }
     
     
         
       

        
               
// if (!empty($_POST['view_assigned'])) {
//     $this->db->where_in('l.old_assignation', $_POST['view_assigned']);
// }else
// {
 

//  if(is_admin())
//  {
     
//  }
//  else{

//  !empty($idsarr) ? $this->db->where_in('l.old_assignation', array_merge($idsarr, [$get_staff_user_id])): $this->db->where('l.old_assignation', $get_staff_user_id);
// }


//   if(!empty($_POST['department']))
//         {
//         $this->db->where_in('s.department', $_POST['department']);
//         }
        
// } 


// if($_POST['leadType'] == "Lead Assignation")
// {
//     $this->db->group_by('l.new_assignation');
//         $this->db->order_by('counts', 'DESC');
// }
// else
// {
//     $this->db->group_by('l.old_assignation');
//         $this->db->order_by('counts', 'DESC');
// }

        
        

//         $query = $this->db->get();
    
  
//       return  $data['leads_summary'] = $query->result_array();


    
// }

function leads_transfers_summary($data)
{
    $get_staff_user_id = get_staff_user_id();
    $idsarr = [];

    // =========================
    // 👤 ROLE FILTER
    // =========================
    if (empty($_POST['view_assigned'])) {

        $role = $this->db->where('staffid', $get_staff_user_id)
                         ->get(db_prefix() . 'staff')
                         ->row()->role;

        if ($role == 3) {
            $teamids = $this->db->query('CALL GetReportingPersons(?)', [$get_staff_user_id])->result_array();

            $this->db->close();
            $this->db->initialize();

            $idsarr = array_column($teamids, 'staffid');
        }
    }

    // =========================
    // 🔁 COMMON FILTER FUNCTION
    // =========================
    $applyFilters = function($db) use ($idsarr, $get_staff_user_id) {

        if (!empty($_POST['view_status'])) {
            $db->where_in('l.old_status', $_POST['view_status']);
        }

        if (!empty($_POST['view_sources'])) {
            $db->where_in('lead.source', $_POST['view_sources']);
        }

        if (!empty($_POST['view_update_count'])) {
            $db->where_in('l.update_count', $_POST['view_update_count']);
        }

        if (!empty($_POST['date_range'])) {
            $dates = explode(' to ', $_POST['date_range']);
            if (count($dates) === 2) {
                $db->where('DATE(l.created_at) >=', trim($dates[0]));
                $db->where('DATE(l.created_at) <=', trim($dates[1]));
            }
        }

        if (!empty($_POST['view_assigned'])) {
            $db->where_in('l.old_assignation', $_POST['view_assigned']);
        } else {
            if (!is_admin()) {
                if (!empty($idsarr)) {
                    $db->where_in('l.old_assignation', array_merge($idsarr, [$get_staff_user_id]));
                } else {
                    $db->where('l.old_assignation', $get_staff_user_id);
                }
            }
        }

        // ✅ Department filter (safe now because join exists)
        if (!empty($_POST['department'])) {
            $db->where_in('s.department', $_POST['department']);
        }
    };
    
 

    // =========================
    // 📊 STAFF SUMMARY
    // =========================
    if ($_POST['leadType'] == "Lead Assignation") {

        $this->db->select("CONCAT(s.firstname,' ',s.lastname) AS staff_name, COUNT(l.id) AS counts");
        $this->db->from('tblleads_transfer_logs l');
        $this->db->join('tblstaff s', 's.staffid = l.new_assignation', 'left');

    } else {

        $this->db->select("CONCAT(s.firstname,' ',s.lastname) AS staff_name, COUNT(l.id) AS counts");
        $this->db->from('tblleads_transfer_logs l');
        $this->db->join('tblstaff s', 's.staffid = l.old_assignation', 'left');
    }

    $this->db->join('tblleads lead', 'lead.id = l.leadid');

    $applyFilters($this->db);

    if ($_POST['leadType'] == "Lead Assignation") {
        $this->db->group_by('l.new_assignation');
    } else {
        $this->db->group_by('l.old_assignation');
    }

    $this->db->order_by('counts', 'DESC');

    $staffSummary = $this->db->get()->result_array();

    // =========================
    // 🥇 TOP LEADS
    // =========================
    $topLeads = array_slice($staffSummary, 0, 5);

    // =========================
    // 📊 TOP SOURCES
    // =========================
    $this->db->select("so.name AS source_name, COUNT(l.id) as total");
    $this->db->from('tblleads_transfer_logs l');
    $this->db->join('tblleads lead', 'lead.id = l.leadid');
    $this->db->join('tblleads_sources so', 'so.id = lead.source', 'left');

    // ✅ IMPORTANT: join staff here also (FIX)
    if ($_POST['leadType'] == "Lead Assignation") {
        $this->db->join('tblstaff s', 's.staffid = l.new_assignation', 'left');
    } else {
        $this->db->join('tblstaff s', 's.staffid = l.old_assignation', 'left');
    }

    $applyFilters($this->db);

    $this->db->group_by('lead.source');
    $this->db->order_by('total', 'DESC');
    $this->db->limit(5);

    $topSources = $this->db->get()->result_array();

    // =========================
    // ✅ FINAL RESPONSE
    // =========================
    return [
        'leads_summary' => $staffSummary,
        'top_lead'      => $topLeads,
        'top_sources'   => $topSources
    ];
}

function leads_transfers_summary_self($data)
{
    $get_staff_user_id = get_staff_user_id();
    $idsarr = [];

    // =========================
    // 👤 ROLE FILTER
    // =========================
    if (empty($_POST['view_assigned_self'])) {

        $role = $this->db->where('staffid', $get_staff_user_id)
                         ->get(db_prefix() . 'staff')
                         ->row()->role;

        if ($role == 3) {
            $teamids = $this->db->query('CALL GetReportingPersons(?)', [$get_staff_user_id])->result_array();

            $this->db->close();
            $this->db->initialize();

            $idsarr = array_column($teamids, 'staffid');
        }
    }

    // =========================
    // 🔁 COMMON FILTER FUNCTION
    // =========================
    $applyFilters = function($db) use ($idsarr, $get_staff_user_id) {

        if (!empty($_POST['view_status_self'])) {
            $db->where_in('lead.status', $_POST['view_status_self']);
        }

        if (!empty($_POST['view_sources_self'])) {
            $db->where_in('lead.source', $_POST['view_sources_self']);
        }

        if (!empty($_POST['view_update_count_self'])) {
            $db->where_in('l.update_count', $_POST['view_update_count_self']);
        }

        // 📅 DATE RANGE
        if (!empty($_POST['date_range_self'])) {
            $dates = explode(' to ', $_POST['date_range_self']);
            if (count($dates) === 2) {
                $db->where('DATE(l.created_at) >=', trim($dates[0]));
                $db->where('DATE(l.created_at) <=', trim($dates[1]));
            }
        }

        // 👤 ASSIGNED FILTER
        if (!empty($_POST['view_assigned_self'])) {
            $db->where_in('l.created_by', $_POST['view_assigned_self']);
        } else {
            if (!is_admin()) {
                !empty($idsarr)
                    ? $db->where_in('l.created_by', array_merge($idsarr, [$get_staff_user_id]))
                    : $db->where('l.created_by', $get_staff_user_id);
            }
        }

        // 🏢 DEPARTMENT
        if (!empty($_POST['department_self'])) {
            $db->where_in('s.department', $_POST['department_self']);
        }
        
           $db->where('l.created_by!=', IVR_AUTO_ASIGNATION);
    };

    // =========================
    // 📊 STAFF SUMMARY
    // =========================
    if ($_POST['leadType'] == "Lead Assignation") {
        $this->db->select("CONCAT(s.firstname,' ',s.lastname) AS staff_name, COUNT(l.id) AS counts");
        $this->db->from('tbllead_transfer_request l');
        $this->db->join('tblstaff s', 's.staffid = l.assign');
    } else {
        $this->db->select("CONCAT(s.firstname,' ',s.lastname) AS staff_name, COUNT(l.id) AS counts");
        $this->db->from('tbllead_transfer_request l');
        $this->db->join('tblstaff s', 's.staffid = l.created_by');
    }

    $this->db->join('tblleads lead', 'lead.id = l.leadid');

    $applyFilters($this->db);

    if ($_POST['leadType'] == "Lead Assignation") {
        $this->db->group_by('l.assign');
    } else {
        $this->db->group_by('l.created_by');
    }

    $this->db->order_by('counts', 'DESC');


    $staffSummary = $this->db->get()->result_array();
    
   

    // =========================
    // 🥇 TOP 5 STAFF
    // =========================
    $topLeads = !empty($staffSummary) ? array_slice($staffSummary, 0, 5) : [];

    // =========================
    // 📊 TOP SOURCES
    // =========================
    $this->db->select("so.name AS source_name, COUNT(l.id) as total");
    $this->db->from('tbllead_transfer_request l');
    $this->db->join('tblleads lead', 'lead.id = l.leadid');
    $this->db->join('tblleads_sources so', 'so.id = lead.source', 'left');
     $this->db->join('tblstaff s', 's.staffid = l.created_by');

    // ⚠️ IMPORTANT: SAME FILTERS AGAIN
    $applyFilters($this->db);

    $this->db->group_by('lead.source');
    $this->db->order_by('total', 'DESC');
    $this->db->limit(5);

    $topSources = $this->db->get()->result_array();

    // =========================
    // ✅ FINAL RESPONSE
    // =========================
    return [
        'leads_summary' => $staffSummary,
        'top_lead'      => $topLeads,
        'top_sources'   => $topSources
    ];
}

// function leads_transfers_summary_self($data)
// {
 
// //   ini_set('display_errors', 1);
// // ini_set('display_startup_errors', 1);
// // error_reporting(E_ALL);
 
//  $get_staff_user_id = get_staff_user_id();
//     $idsarr =[];
//   if (!empty($_POST['view_assigned'])) {
// }else
// {
    
    
   
        
//      $role = $this->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
//      $sid = $get_staff_user_id;
// if ($role == 3) {
// $teamids = $this->db->query('CALL GetReportingPersons(?)', array($get_staff_user_id))->result_array();

// $this->db->close();
// $this->db->initialize();

// $idsarr = array_column($teamids, 'staffid');


      

// }
// }



//   // Build query
//   if($_POST['leadType'] == "Lead Assignation")
// {
//      $this->db->select("CONCAT(s.firstname, ' ', s.lastname) AS staff_name, COUNT(l.id) AS counts");
//         $this->db->from('tbllead_transfer_request l');
//         $this->db->join('tblstaff s', 's.staffid = l.assign', 'inner');
//          $this->db->join('tblleads lead', 'lead.id = l.leadid', 'inner');
// }
// else{
//         $this->db->select("CONCAT(s.firstname, ' ', s.lastname) AS staff_name, COUNT(l.id) AS counts");

//         $this->db->from('tbllead_transfer_request l');
//         $this->db->join('tblstaff s', 's.staffid = l.created_by', 'inner');
//          $this->db->join('tblleads lead', 'lead.id = l.leadid', 'inner');
        
// }
        
//         if(!empty($_POST['view_status_self']))
//         {
//         $this->db->where_in('lead.status', $_POST['view_status_self']);
//         }
        
      
//           if(!empty($_POST['view_sources_self']))
//         {
//         $this->db->where_in('lead.source', $_POST['view_sources_self']);
//         }
        
//         if(!empty($_POST['view_update_count']))
//         {
//         $this->db->where_in('l.update_count', $_POST['view_update_count']);
//         }
        
        
//         $this->db->where('DATE(l.created_at) >=', "2026-04-06");
        
       
        
//       if (!empty($this->input->post('date_range_self'))) {
//     // Get date range from POST
//     $dateRange = $this->input->post('date_range_self'); // e.g. "2026-04-01 to 2026-04-02"
    
//     // Split by 'to'
//     $dates = explode(' to ', $dateRange);

//     if (count($dates) === 2) {
//         $startDate = trim($dates[0]);
//         $endDate = trim($dates[1]);

//         // Apply CI3 where condition
//         $this->db->where('DATE(l.created_at) >=', $startDate);
//         $this->db->where('DATE(l.created_at) <=', $endDate);
//     }
// }
     
     
         
       

        
               
// if (!empty($_POST['view_assigned_self'])) {
//     $this->db->where_in('l.created_by', $_POST['view_assigned_self']);
// }else
// {
 

//  if(is_admin())
//  {
     
//  }
//  else{

//  !empty($idsarr) ? $this->db->where_in('l.created_by', array_merge($idsarr, [$get_staff_user_id])): $this->db->where('l.created_by', $get_staff_user_id);
// }


//   if(!empty($_POST['department_self']))
//         {
//         $this->db->where_in('s.department', $_POST['department_self']);
//         }
        
// } 


// if($_POST['leadType'] == "Lead Assignation")
// {
//     $this->db->group_by('l.assign');
//         $this->db->order_by('counts', 'DESC');
// }
// else
// {
//     $this->db->group_by('l.created_by');
//         $this->db->order_by('counts', 'DESC');
// }

        
       

//         $query = $this->db->get();
    
//       return  $data['leads_summary'] = $query->result_array();


    
// }

function lead_sub_status()
{
    $sql = "SELECT CONCAT(lead_type,'-',status_id) AS lead_type_status,s.status_id,s.lead_type,sub.id sub_status_id, name  
            FROM tblstatus_mapping s 
            JOIN tblsub_lead_status sub ON sub.id = s.status_sub_id";

    $query = $this->db->query($sql);

    return $query->result_array();
}


}
