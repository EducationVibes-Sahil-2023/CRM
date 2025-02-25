<?php

use PayPalHttp\Serializer\Json;

defined('BASEPATH') or exit('No direct script access allowed');


$this->ci->load->model('gdpr_model');
$lockAfterConvert      = get_option('lead_lock_after_convert_to_customer');
$consentLeads          = get_option('gdpr_enable_consent_for_leads');
$get_staff_user_id = get_staff_user_id();
$has_permission_delete = has_permission('leads', '', 'delete');

$custom_fields = [];
$customFieldValues = [];
$is_admin = is_admin();
if ($is_admin) {
    $custom_fields         = get_table_custom_fields('leads');
}

$statuses              = $this->ci->leads_model->get_status();
$type              = $this->ci->leads_model->get_type();
$source              = $this->ci->leads_model->get_source();
$staff_list              = $this->ci->leads_model->get_staff_list();

$statuses = array_column($statuses, null, "id");
$type = array_column($type, null, "id");
$source = array_column($source, null, "id");
$staff_list = array_column($staff_list, null, "staffid");

$tbllead_performance_column = $this->ci->leads_model->tbllead_performance_column($this->ci->input->post('columnNames'));
$tbllead_performance_column = array_column($tbllead_performance_column, null, "tbl_column_name");


$sTable  =  db_prefix() . "leads";

$call_table     = db_prefix() . 'calls_activity_logs';
$sIndexColumn   = 'id';
$up_from_date   = $this->ci->input->post('up_from_date');
$up_to_date     = $this->ci->input->post('up_to_date');
$where          = [];
$join           = [];
$filter = false;
$sIndexColumn = 'id';


if (!empty($this->ci->input->post('up_to_date'))) {
    $up_to_date = $this->ci->input->post('up_to_date');
    $up_from_date   = $this->ci->input->post('up_from_date');
    // Inputs for the query
    // Escape input dates
    $up_from_date = $this->ci->db->escape_str($up_from_date); // Start date
    $up_to_date = $this->ci->db->escape_str($up_to_date);     // End date
    $start = intval($start);                                  // Offset
    $length = intval($length);
    $where_c = "";
    $join_type = "";
    $sql_p1 = "";
    if ($this->ci->input->post('show_update_counts') && $this->ci->input->post('show_update_counts') == 1) {

        $min = isset($_POST['update_count_min']) ? $_POST['update_count_min'] : 0;
        $max = isset($_POST['update_count_max']) ? $_POST['update_count_max'] : 0;
        // $where_c = " AND ifnull(tblcalls_activity_logs.id,0) between {$min} AND {$max} ";


        if ($min == 0) {
            $join_type = "RIGHT";
        }
    }

    if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {
        $where_c  .= " AND {$sTable}.assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
    }


    // $sTable = $call_table;

    $join[] = " JOIN " . db_prefix() . "leads ON (
   {$call_table}.contact IN (phonenumber,alternative_phonenumber
    )
    AND DATE({$call_table}.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}' $where_c
)";

    $where[] = " AND DATE({$call_table}.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}' ";

    if (is_admin()) {
        $_POST["order"][0]["column"] = "";
    } else {
        $_POST["order"][0]["column"] = "";
    }
}
// echo $_POST["order"][0]["column"];

// print_r($_POST);


$join[] = " LEFT JOIN " . db_prefix() . "leads_status  ON ({$sTable}.status = " . db_prefix() . "leads_status.id)";
$join[] = " LEFT JOIN " . db_prefix() . "leads_sources  ON ({$sTable}.source = " . db_prefix() . "leads_sources.id)";
$join[] = " LEFT JOIN " . db_prefix() . "leads_type ON ({$sTable}.type = " . db_prefix() . "leads_type.id)";
$join[] = " LEFT JOIN " . db_prefix() . "staff ON ({$sTable}.assigned = " . db_prefix() . "staff.staffid)";
$join[] = " LEFT JOIN " . db_prefix() . "countries ON ({$sTable}.country = " . db_prefix() . "countries.country_id)";




if (!empty($_POST["search"]["value"])) {
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'taggables ON ' . db_prefix() . 'taggables.rel_id = ' . $sTable . '.id  AND ' . db_prefix() . 'taggables.rel_type = "lead" ');
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id ');
}





if (!$filter || ($filter && !in_array($filter, ['lost', 'junk']))) {
    $where[] = "AND " . $sTable . ".lost = 0 AND " . $sTable . ".junk = 0 AND " . $sTable . ".utm_campaign_name != '' ";
}

// Role-based filters for assigned leads
$role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
if ($role == 3) {
    $sid = $get_staff_user_id;
    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();
    $idsarr = array_column($teamids, 'staffid');
    $sids = implode(",", $idsarr);
    $where[] = !empty($sids) ? "AND " . $sTable . ".assigned IN ({$sid}, {$sids})" : "AND " . $sTable . ".assigned = {$sid}";
}

// Apply filters based on input parameters
if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {
    $where[] = "AND " . $sTable . ".assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
}

if ($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0) {
    $where[] = "AND " . $sTable . ".status IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('status'))) . ")";
}

if ($this->ci->input->post('source')) {
    $where[] = "AND " . $sTable . ".source IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('source'))) . ")";
}
if ($this->ci->input->post('department')) {
    $where[] = "AND " . db_prefix() . "staff.department IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('department'))) . ")";
}

if ($this->ci->input->post('location')) {
    $where[] = "AND " . db_prefix() . "staff.office_location IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('location'))) . ")";
}

if ($this->ci->input->post('lead_type')) {
    $where[] = "AND " . $sTable . ".type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ")";
}

if ($this->ci->input->post('utm_campaign_name')) {
    $where[] = "AND " . $sTable . ".utm_campaign_name IN ('" . implode("','", $this->ci->db->escape_str($this->ci->input->post('utm_campaign_name'))) . "')";
}

if ($this->ci->input->post('utm_ads_set_name')) {
    $where[] = "AND " . $sTable . ".utm_ads_set_name IN ('" . implode("','", $this->ci->db->escape_str($this->ci->input->post('utm_ads_set_name'))) . "')";
}

if ($this->ci->input->post('utm_ads_name')) {
    $where[] = "AND " . $sTable . ".utm_ads_name IN ('" . implode("','", $this->ci->db->escape_str($this->ci->input->post('utm_ads_name'))) . "')";
}


if ($this->ci->input->post('utm_form_name')) {
    $where[] = "AND " . $sTable . ".utm_form_name IN ('" . implode("','", $this->ci->db->escape_str($this->ci->input->post('utm_form_name'))) . "')";
}

if ($this->ci->input->post('utm_term')) {
    $where[] = "AND " . $sTable . ".utm_term IN ('" . implode("','", $this->ci->db->escape_str($this->ci->input->post('utm_term'))) . "')";
}


if ($this->ci->input->post('assign_to_date')) {
    $assign_from_date = $this->ci->input->post('assign_from_date');
    $assign_to_date = $this->ci->input->post('assign_to_date');
    array_push($where, ' AND DATE(dateassigned) BETWEEN "' . $this->ci->db->escape_str($assign_from_date) . '" AND "' . $this->ci->db->escape_str($assign_to_date) . '"');
}

// Date filters for lead creation, assignment, follow-up, and NEET score
if ($this->ci->input->post('to_date')) {
    $from_date = $this->ci->input->post('from_date');
    $to_date   = $this->ci->input->post('to_date');
    $where[]   = "AND DATE(" . $sTable . ".dateadded) BETWEEN '{$this->ci->db->escape_str($from_date)}' AND '{$this->ci->db->escape_str($to_date)}'";
}


if ($this->ci->input->post('followup_to_date')) {
    $followup_from_date = $this->ci->input->post('followup_from_date');
    $followup_to_date   = $this->ci->input->post('followup_to_date');
    $join[] = " JOIN " . db_prefix() . "reminders ON " . db_prefix() . "reminders.rel_id = " . $sTable . ".id";
    $where[] = "AND DATE(" . db_prefix() . "reminders.date) BETWEEN '{$this->ci->db->escape_str($followup_from_date)}' AND '{$this->ci->db->escape_str($followup_to_date)}'";
}

if ($this->ci->input->post('last_update_date') || $this->ci->input->post('last_contact_date')) {

    // Add condition for last_contact_date
    if (!empty($this->ci->input->post('last_contact_date'))) {
        $last_contact_date = $this->ci->db->escape_str($this->ci->input->post('last_contact_date'));
        array_push($where, ' AND lastconnect_date <= "' . $this->ci->db->escape_str($last_contact_date) . '"');
    }

    // Add condition for last_update_date
    if (!empty($this->ci->input->post('last_update_date'))) {
        $last_update_date = $this->ci->db->escape_str($this->ci->input->post('last_update_date'));
        array_push($where, ' AND lastupdate_date <= "' . $this->ci->db->escape_str($last_update_date) . '"');
    }
}

if ($this->ci->input->post('show_update_counts') && $this->ci->input->post('show_update_counts') == 1) {
    $min = isset($_POST['update_count_min']) ? $_POST['update_count_min'] : 0;
    $max = isset($_POST['update_count_max']) ? $_POST['update_count_max'] : 0;

    $where[] = "AND update_count BETWEEN '{$this->ci->db->escape_str($min)}' AND '{$this->ci->db->escape_str($max)}'";
}

// Check user permissions and access scope
if (!has_permission('leads', '', 'view')) {
    $where[] = "AND (" . $sTable . ".assigned = {$get_staff_user_id} OR " . $sTable . ".is_public = 1)";
}

if (!empty($tbllead_performance_column)) {
    foreach ($tbllead_performance_column as $key => $value) {

        if (!empty($value["sql_condition"])) {
            $key = $value["sql_condition"];
        }
        $aColumns[] = $key . " as " . str_replace(" ", "_", strtolower($value["label_name"]));
    }
}


// // Define columns to be selected
// $aColumns = [

//     $sTable . '.name as name',
//     $sTable . '.id as leadsid',
// ];

// if (is_gdpr() && $consentLeads == '1') {
//     $aColumns[] = '1';
// }
// if ($is_admin) {
// $aColumns = array_merge($aColumns, [
//      "IFNULL({$sTable}.update_count,0) as update_count",
//     "IFNULL({$sTable}.call_duration,0) as call_duration",
//      $sTable .'.lastconnect_date as lastcontact_date',
//     $sTable . '.dateadded as dateadded',
//     $sTable .'.lastupdate_date as lastupdate_date',
//     $sTable . '.name as name',
//     $sTable . '.phonenumber as phonenumber',
//     $sTable . '.status as status',

// ]);
// }
// else
// {
//    $aColumns = array_merge($aColumns, [
//      "IFNULL({$sTable}.update_count,0) as update_count",
//     "IFNULL({$sTable}.call_duration,0) as call_duration",
//      $sTable .'.lastconnect_date as lastcontact_date',
//     $sTable . '.dateadded as dateadded',
//     $sTable . '.name as name',
//     $sTable . '.phonenumber as phonenumber',
//     $sTable . '.status as status',

// ]); 
// }

// if ($is_admin) {
//     foreach ($custom_fields as $field) {
//         $aColumns[] = 'CONCAT("' . $field['fieldto'] . '-", ' . $sTable . '.id, "-' . $field['id'] . '") as ' . strtolower(str_replace(' ', '_', $field["name"]));
//     }
// }

// $aColumns = array_merge($aColumns, [
//     $sTable . '.type as type',
//     $sTable . '.website as website',
//     $sTable . '.source as source',
//     $sTable . '.email as email',
//     $sTable . '.assigned as assigned',
//     $sTable . '.dateassigned as dateassigned',
//     $sTable . '.city as city',
//     $sTable . '.state as state',

// ]);

// if (!empty($_POST["search"]["value"])) {
//     $aColumns[] =  db_prefix() . 'tags.name as tags';
// } else {
//     $aColumns[] = '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . $sTable . '.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags';
// }

// if ($this->ci->input->post('followup_to_date')) {
//     $aColumns[] = db_prefix() . "reminders.date as followup";
// } else {
//     $aColumns[] = '(SELECT date FROM ' . db_prefix() . 'reminders  WHERE rel_id = ' . $sTable . '.id and rel_type="lead" ORDER by id DESC LIMIT 1) as followup';
// }

// $aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);

$additionalColumns = [];
// $additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [
//     'lead_value',
//     'company',
//     'junk',
//     'lost',
//     'assigned',
//     $sTable . '.addedfrom as addedfrom',
//     '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . $sTable . '.id) as is_converted',
//     'alternative_phonenumber',
//     'zip',
//     '(SELECT ' . db_prefix() . 'notes.dateadded FROM ' . db_prefix() . 'notes  WHERE rel_id = ' . $sTable . '.id and rel_type="lead" ORDER by id DESC LIMIT 1) as notesdate'

// ]);






$search_column = [];
// Define search and group-by clauses
// if (!empty($_POST["search"]["value"])) {
//     $search_column = [$sTable . ".city", $sTable . ".phonenumber", $sTable . ".state", db_prefix() . 'tags.name', "alternative_phonenumber"];
// }

$having_ = "";
$having = "";
$group_by = ' Group By ' . $sTable . '.id ' . $having . " ";



// Execute final query with applied filters and joins

if (is_admin()) {
    if (!empty($_POST["order"][0]["column"]) && ($_POST["order"][0]["column"] == 5)) {
        // $_POST["order"][0]["column"] = 0;
    }
} else {
    if (!empty($_POST["order"][0]["column"])) {
        //   $_POST["order"][0]["column"] =0;
    }
}

if (!empty($this->ci->input->post('up_to_date'))) {
    $sTable = $call_table;
}


$result = data_tables_init_($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns, $group_by, '', '', $search_column);

$output  = $result['output'];
$rResult = $result['rResult'];


// $lead_ids_array = array_column($rResult, "id");
// $phone_numbers = array_column($rResult, "phonenumber_");

// $call_data = [];
// if (!empty($phone_numbers)) {
//     $phone_numbers = implode(",", $phone_numbers);
//     $phone_numbers = explode(",", $phone_numbers);
//     $phone_numbers = array_map(function ($value) {
//         return preg_replace('/\s+/', '', trim($value));  // Remove all spaces
//     }, $phone_numbers);


//     $get_call_data = get_call_information_new($phone_numbers);
//     $call_data = array_column($get_call_data, null, 'contact');
// }



// if ($is_admin) {
//     if (!empty($lead_ids_array)) {
//         $customFieldValues              = $this->ci->leads_model->get_custum_values($lead_ids_array);

//         $customFieldValues = array_column($customFieldValues, "value", "column_name");
//     }
// }




// $response = [
//     "draw" => intval($_POST['draw']),
//     "recordsTotal" => count($rResult), // Total records in the database
//     "recordsFiltered" => count($rResult), // After filtering (if applicable)
//     "data" => $rResult
// ];
// echo json_encode($response);
// die;


foreach ($rResult as $aRow) {
    $row = [];
    $row =  array_values($aRow);
    $output['aaData'][] = $row;
}
