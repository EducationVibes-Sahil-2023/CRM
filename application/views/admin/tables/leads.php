
<?php



defined('BASEPATH') or exit('No direct script access allowed');

// $this->load->model('leads_model');



$this->ci->load->model('gdpr_model');

$lockAfterConvert      = get_option('lead_lock_after_convert_to_customer');

$has_permission_delete = has_permission('leads', '', 'delete');

$custom_fields         = get_table_custom_fields('leads');

$consentLeads          = get_option('gdpr_enable_consent_for_leads');

$statuses              = $this->ci->leads_model->get_status();

$type              = $this->ci->leads_model->get_type();
$up_from_date = "";
$up_to_date = "";

// echo "<pre>";
// print_r($_POST);die;
//echo "<pre>";print_r($type);die;


$aColumns = [

    '1',

    db_prefix() . 'leads.id as id',

    db_prefix() . 'leads.name as name',

];

if (is_gdpr() && $consentLeads == '1') {

    $aColumns[] = '1';
}

$aColumns = array_merge($aColumns, [
    'company',
    db_prefix() . 'leads.assigned as staffid',

    db_prefix() . 'leads.email as email',

    db_prefix() . 'leads.phonenumber as phonenumber',

    'lead_value', 'city', 'state', 'website', 'type', 'dateassigned',

    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags',
    'firstname as assigned_firstname',
    // 'firstname as assigned_firstname',
    // '(select value from ' . db_prefix() . 'customfieldsvalues where relid=tblleads.id and fieldid = 24) as intake',
    // '(select value from ' . db_prefix() . 'customfieldsvalues where relid=tblleads.id and fieldid = 32) as destination',
    // '(select value from ' . db_prefix() . 'customfieldsvalues where relid=tblleads.id and fieldid = 8 order by id desc limit 1) as neet_score',
    // '(select value from ' . db_prefix() . 'customfieldsvalues where relid=tblleads.id and fieldid = 16 order by id desc limit 1) as course_name',

    db_prefix() . 'leads_status.name as status_name',

    db_prefix() . 'leads_type.name as type_name',

    // db_prefix() . 'leads_type.tpcolor as type_color',

    db_prefix() . 'leads_sources.name as source_name',

    'lastcontact',

    db_prefix() . 'leads.dateadded',

    '(SELECT ' . db_prefix() . 'notes.dateadded FROM ' . db_prefix() . 'notes  WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by id DESC LIMIT 1) as notesdate',

    '(SELECT date FROM ' . db_prefix() . 'reminders  WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by id DESC LIMIT 1) as followup',

]);



$sIndexColumn = 'id';

$sTable       = db_prefix() . 'leads';



$join = [

    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'leads.assigned',

    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',

    'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . db_prefix() . 'leads.type',

    'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',

    //'LEFT JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id',

];



foreach ($custom_fields as $key => $field) {

    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);

    array_push($aColumns, 'ctable_' . $key . '.value as ' . trim(str_replace(' ', '_', strtolower($field["name"]))));

    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'leads.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
    // print_r($join);
    // die;
}

// $fields_ids = array_column($custom_fields, 'id');

// if (!empty($fields_ids)) {
//     $keyy = 0;
//     $join_query_prefix = '';
//     $join_query_surfix = '';
//     $join_query = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $keyy . ' ON ' . db_prefix() . 'leads.id = ctable_' . $keyy . '.relid AND ctable_' . $keyy . '.fieldto="' . $custom_fields[$keyy]['fieldto'] . '"';
//     $join_query_joins = '';
//     foreach ($fields_ids as $key => $field_id) {
//         if (!empty($field_id)) {
//             $join_query_prefix = ' AND (';
//             $join_query_surfix = ')';
//             if ($key == 0) {
//                 $join_query_joins .= '  ctable_' . $keyy . '.fieldid = "' . $field_id . '" ';
//             } else {
//                 $join_query_joins .= ' AND  ctable_' . $keyy . '.fieldid = "' . $field_id . '"';
//             }
//         }
//         array_push($aColumns, 'ctable_' . $field_id . '.value as ' . $custom_fields[$key]["name"]);
//     }

//     array_push($join, $join_query . $join_query_prefix . $join_query_joins . $join_query_surfix);
// }

$lead_date_query = '';

if (!empty($this->ci->input->post('up_to_date'))) {
    $from_date = $this->ci->input->post('up_from_date');
    $to_date = $this->ci->input->post('up_to_date');
    $lead_date_query = ' AND DATE(n.dateadded) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"';
}

$lead_count_join = 'LEFT JOIN ' . db_prefix() . 'notes as n on  (' . db_prefix() . 'leads.id = n.rel_id and n.rel_type="lead" ' . $lead_date_query . ') ';

array_push($join, $lead_count_join);
array_push($aColumns, ' count(n.id) as update_count ');

$where  = [];

$filter = false;



if ($this->ci->input->post('custom_view')) {

    $filter = $this->ci->input->post('custom_view');

    if ($filter == 'lost') {

        array_push($where, 'AND lost = 1');
    } elseif ($filter == 'junk') {

        array_push($where, 'AND junk = 1');
    } elseif ($filter == 'not_assigned') {

        array_push($where, 'AND assigned = 0');
    } elseif ($filter == 'contacted_today') {

        array_push($where, 'AND lastcontact LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'created_today') {

        array_push($where, 'AND ' . db_prefix() . 'leads.dateadded  LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'public') {

        array_push($where, 'AND is_public = 1');
    } elseif (startsWith($filter, 'consent_')) {

        array_push($where, 'AND ' . db_prefix() . 'leads.id IN (SELECT lead_id FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $this->ci->db->escape_str(strafter($filter, 'consent_')) . ' and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $this->ci->db->escape_str(strafter($filter, 'consent_')) . ' AND lead_id=' . db_prefix() . 'leads.id))');
    }
}



if (!$filter || ($filter && $filter != 'lost' && $filter != 'junk')) {

    array_push($where, 'AND lost = 0 AND junk = 0');
}
// $this->load->database();    
$role = $this->ci->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
if ($role == 3) {
    // $this->load->database();
    $sid = get_staff_user_id(); //48;//get_staff_user_id();

    $teamids = $this->ci->db->query("select staffid
			from    (select * from " . db_prefix() . "staff
			where active = '1' order by reporting_person, staffid) products_sorted,
					(select @pv := $sid) initialisation
			where   find_in_set(reporting_person, @pv)
			and     length(@pv := concat(@pv, ',', staffid))")->result_array();
    // return $query;
    // array_push($teamids,get_staff_user_id());
    // foreach ($teamids as $t) {
    # code...
    // }
    $idsarr = array_column($teamids, 'staffid');

    // echo "<pre>";print_r($idsarr);
    $sids = implode(",", $idsarr);
    // echo "<pre>";print_r($sids);
    if (!empty($sids)) {
        array_push($where, 'AND assigned in (' . $sid . ',' . $sids . ')');
    } else {
        array_push($where, 'AND assigned in (' . $sid . ')');
    }
    //     array_push($where, 'AND assigned in (' . $sid . ',' . $sids . ')');
    // print_r($where);die;
}

// if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {

//     array_push($where, 'AND assigned =' . $this->ci->db->escape_str($this->ci->input->post('assigned')));
// }


if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {

    // array_push($where, 'AND assigned =' . $this->ci->db->escape_str($this->ci->input->post('assigned')));
    array_push($where, 'AND assigned IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ')');
}






if (
    $this->ci->input->post('status')

    && count($this->ci->input->post('status')) > 0

    && ($filter != 'lost' && $filter != 'junk')
) {

    array_push($where, 'AND ' . db_prefix() . 'leads.status IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('status'))) . ')');
}

if ($this->ci->input->post('degree') && count($this->ci->input->post('degree')) > 0) {
    $x = $this->ci->db->escape_str($this->ci->input->post('degree'));
    $y = '"' . implode('","', $x) . '"';
    array_push($where, 'AND ctable_3.value IN (' . $y . ')');
}

if ($this->ci->input->post('course') && count($this->ci->input->post('course')) > 0) {
    $x = $this->ci->db->escape_str($this->ci->input->post('course'));
    $y = '"' . implode('","', $x) . '"';
    array_push($where, 'AND ctable_2.value IN (' . $y . ')');
}

if ($this->ci->input->post('source')) {

    // array_push($where, 'AND source =' . $this->ci->db->escape_str($this->ci->input->post('source')));
    array_push($where, 'AND ' . db_prefix() . 'leads.source IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('source'))) . ')');
}


if ($this->ci->input->post('lead_type')) {

    array_push($where, 'AND type IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ')');

    // array_push($where, 'AND type =' . $this->ci->db->escape_str($this->ci->input->post('lead_type')));
    // print_r($where);
}

if (!empty($this->ci->input->post('neet_score'))) {
    $neet_range = explode("-", $this->ci->input->post('neet_score'));
    array_push($where, ' AND (select value from ' . db_prefix() . 'customfieldsvalues where relid=' . db_prefix() . 'leads.id and fieldid = 8 AND  (' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . trim($neet_range[0]) . ' AND ' . trim($neet_range[1]) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" ) order by id desc limit 1) ');
}


if ($this->ci->input->post('to_date')) {
    $from_date = $this->ci->input->post('from_date');
    $to_date = $this->ci->input->post('to_date');
    // echo "<pre>";print_r($from_date.'to'.$to_date);
    array_push($where, 'AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"');
    // echo "<pre>";print_r($from_date.'to'.$to_date.'--');
    // print_r($where);

}

if ($this->ci->input->post('up_to_date')) {
    $up_from_date = $this->ci->input->post('up_from_date');
    $up_to_date = $this->ci->input->post('up_to_date');
    //     array_push($where, 'AND DATE(lastcontact) BETWEEN "' . $this->ci->db->escape_str($up_from_date) . '" AND "' . $this->ci->db->escape_str($up_to_date) . '"');
    array_push($where, 'AND DATE(' . db_prefix() . 'leads.lastcontact) BETWEEN "' . $this->ci->db->escape_str($up_from_date) . '" AND "' . $this->ci->db->escape_str($up_to_date) . '"');
}

if ($this->ci->input->post('followup_to_date')) {
    $followup_from_date = $this->ci->input->post('followup_from_date');
    $followup_to_date = $this->ci->input->post('followup_to_date');
    //$date2 = date("Y-m-d",strtotime('followup'));
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id');
    array_push($where, 'AND DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $this->ci->db->escape_str($followup_from_date) . '" AND "' . $this->ci->db->escape_str($followup_to_date) . '"');
}
// WHERE DATE(dateadded) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "'dateassigned
if ($this->ci->input->post('assign_to_date')) {
    $assign_from_date = $this->ci->input->post('assign_from_date');
    $assign_to_date = $this->ci->input->post('assign_to_date');
    array_push($where, 'AND DATE(dateassigned) BETWEEN "' . $this->ci->db->escape_str($assign_from_date) . '" AND "' . $this->ci->db->escape_str($assign_to_date) . '"');
}


if (!has_permission('leads', '', 'view')) {

    // array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
    array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR is_public = 1)');
}



$aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);



// Fix for big queries. Some hosting have max_join_limit

if (count($custom_fields) > 4) {

    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

// $call_query = "";
$call_query = " (SELECT IFNULL(SUM(duration), 0) AS call_duration FROM " . db_prefix() . "calls_activity_logs WHERE SUBSTRING(TRIM(contact), LENGTH(TRIM(contact)) - 9) = SUBSTRING(TRIM(" . db_prefix() . "leads.phonenumber), LENGTH(TRIM(" . db_prefix() . "leads.phonenumber)) - 9) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') AND staffid = " . db_prefix() . "leads.assigned  LIMIT 1) call_duration ";
$call_query_having = "";
if ($this->ci->input->post('up_from_date_call')) {
    $up_from_date = $this->ci->input->post('up_from_date_call');
    $up_to_date = $this->ci->input->post('up_to_date_call');
    $call_query = " (SELECT IFNULL(SUM(duration), 0) AS duration FROM " . db_prefix() . "calls_activity_logs WHERE SUBSTRING(TRIM(contact), LENGTH(TRIM(contact)) - 9) = SUBSTRING(TRIM(" . db_prefix() . "leads.phonenumber), LENGTH(TRIM(" . db_prefix() . "leads.phonenumber)) - 9) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') AND staffid = " . db_prefix() . "leads.assigned  AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' LIMIT 1) call_duration ";
    $call_query_having = "(SELECT sum(id)  FROM " . db_prefix() . "calls_activity_logs WHERE SUBSTRING(TRIM(contact), LENGTH(TRIM(contact)) - 9) = SUBSTRING(TRIM(" . db_prefix() . "leads.phonenumber), LENGTH(TRIM(" . db_prefix() . "leads.phonenumber)) - 9) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') AND staffid = " . db_prefix() . "leads.assigned  AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' LIMIT 1)";


    // //     array_push($where, 'AND DATE(lastcontact) BETWEEN "' . $this->ci->db->escape_str($up_from_date) . '" AND "' . $this->ci->db->escape_str($up_to_date) . '"');
    // array_push($where, 'AND DATE(' . db_prefix() . 'leads.lastcontact) BETWEEN "' . $this->ci->db->escape_str($up_from_date) . '" AND "' . $this->ci->db->escape_str($up_to_date) . '"');
}


$additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [

    'junk',

    'lost',

    'color',

    db_prefix() . 'leads.status',

    'assigned',

    'lastname as assigned_lastname',

    db_prefix() . 'leads.addedfrom as addedfrom',

    '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . db_prefix() . 'leads.id) as is_converted',

    'zip'



    // '(SELECT sum(calls.duration)
    // FROM tblcalls_activity_logs AS calls
    // WHERE RIGHT(TRIM(calls.contact), 10) = RIGHT(TRIM(' . db_prefix() . 'leads.phonenumber), 10)
    // LIMIT 1) AS  call_duration'

]);
if (!empty($call_query)) {
    array_push($additionalColumns, $call_query);
}

// echo"<pre>";
// print_r($aColumns);
// print_r($sIndexColumn);
// print_r($sTable);
// print_r($join);
// print_r($additionalColumns);
// die;
//print_r(data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns));die;
$having = "";
if ($this->ci->input->post('show_update_counts') && $this->ci->input->post('show_update_counts') == 1) {
    $min = isset($_POST['update_count_min']) ? $_POST['update_count_min'] : 0;
    $max = isset($_POST['update_count_max']) ? $_POST['update_count_max'] : 0;
    $having .= " Having count(n.id) between {$min} AND {$max} ";
}

if ($call_query_having) {

    if (!empty($having)) {
        $having .= " AND ";
    }
    $having .= " Having " . $call_query_having . " > 0 ";
}

$group_by = ' Group By ' . db_prefix() . 'leads.id ' . $having . " ";

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns, $group_by, '', '');

$output  = $result['output'];

$rResult = $result['rResult'];


foreach ($rResult as $aRow) {

    $row = [];



    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';
    $curdate = date("Y-m-d");
    $date1 = date("Y-m-d", strtotime($aRow['notesdate']));
    $date2 = date("Y-m-d", strtotime($aRow['followup']));
    if ($date1 >= $date2) {
        $col = '<span style="color:#0f970f;font-size: 16px;"><i class="fa fa-check-circle"></i></span>';
    } else {
        $col = ($curdate <= $date2) ? '<span style="color:#f4f407;font-size: 16px;"><i class="fa fa-check-circle"></i></span>' : '<span style="color:#fb3121;font-size: 16px;"><i class="fa fa-times-circle"></i></span>';
    }
    //$col=($date1 >= $date2)?'<span style="color:#0f970f;font-size: 16px;"><i class="fa fa-check-circle"></i></span>':'<span style="color:#fb3121;font-size: 16px;"><i class="fa fa-times-circle"></i></span>';

    $row[]    = $col;
    // $updatecount = leads_update_count_id($aRow['id'], $this->ci->input->post());
    $updatecount = !empty($aRow["update_count"]) ? $aRow["update_count"] : 0;
    $row[]    = $updatecount;
    // $row[]    = !empty($aRow['phonenumber']) ? call_duration($aRow['phonenumber'], $aRow['staffid'], $up_from_date, $up_to_date) : convertToHMS(0, 1);
    // $row[]    = !empty($aRow['phonenumber']) ? call_duration($aRow['phonenumber'], $aRow['staffid']) : convertToHMS(0, 1);
    if (empty($aRow["call_duration"])) {
        $aRow["call_duration"] = 0;
    }
    $row[]    = convertToHMS($aRow["call_duration"], 1);


    // $row[]    = 0;



    $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';

    //$row[]    = '<a ' . $hrefAttr . '>' . $aRow['id'] . '</a>';

    $nameRow = '<a ' . $hrefAttr . '>' . $aRow['name'] . '</a>';



    $nameRow .= '<div class="row-options">';

    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';



    $locked = false;



    if ($aRow['is_converted'] > 0) {

        $locked = ((!is_admin() && $lockAfterConvert == 1) ? true : false);
    }



    if (!$locked) {

        $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
    }



    // if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {

    //     $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
    // }

    if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {

        $nameRow .= ' | <a href="javascript:void(0)" onclick="delete_leads(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    }

    $nameRow .= '</div>';





    $row[] = $nameRow;



    if (is_gdpr() && $consentLeads == '1') {

        $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';

        $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');



        foreach ($consents as $consent) {

            $consentHTML .= '<p style="margin-bottom:0px;">' . $consent['name'] . (!empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
        }

        $row[] = $consentHTML;
    }

    $row[] = ($aRow['phonenumber'] != '' ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');


    if ($aRow['status_name'] == null) {

        if ($aRow['lost'] == 1) {

            $outputStatus = '<span class="label label-danger inline-block">' . _l('lead_lost') . '</span>';
        } elseif ($aRow['junk'] == 1) {

            $outputStatus = '<span class="label label-warning inline-block">' . _l('lead_junk') . '</span>';
        }
    } else {

        $outputStatus = '<span class="inline-block lead-status-' . $aRow['status'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . $aRow['color'] . '">' . $aRow['status_name'];

        if (!$locked) {

            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';

            $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';

            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';

            $outputStatus .= '</a>';



            $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';

            foreach ($statuses as $leadChangeStatus) {

                if ($aRow['status'] != $leadChangeStatus['id']) {

                    $outputStatus .= '<li>

                  <a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">

                     ' . $leadChangeStatus['name'] . '

                  </a>

               </li>';
                }
            }

            $outputStatus .= '</ul>';

            $outputStatus .= '</div>';
        }

        $outputStatus .= '</span>';
    }

    $outputStatus = '<span class="inline-block lead-status-' . $aRow['status'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . $aRow['color'] . '">' . $aRow['status_name'];

    $row[] = $outputStatus;
    // foreach ($custom_fields as $key => $field) {
    //     if ($field['name'] == 'NEET Score') {
    //         $row[] = !empty(is_numeric($aRow['neet_score'])) ? $aRow['neet_score'] : '';
    //     }
    // }
    // $row[] = $aRow['intake'];
    // $row[] = $aRow['intake'];
    // foreach ($custom_fields as $key => $field) {
    //     if ($field['name'] == 'Course') {
    //         $row[] = !empty($aRow['course_name']) ? $aRow['course_name'] : '';
    //     }
    // }


    foreach ($custom_fields as $key => $field) {
        $row[] = (!empty($aRow[str_replace(" ", "_", strtolower($field['name']))]) && $aRow[str_replace(" ", "_", strtolower($field['name']))] != "null" &&  $aRow[str_replace(" ", "_", strtolower($field['name']))] != "undefined") ? $aRow[str_replace(" ", "_", strtolower($field['name']))] : '';
    }
    ///////////////////////////////////////
    $i = 0;
    $row1 = [];
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row11 = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
        $row1[$i] = $row11;
        $i++;
    }
    // $row[] = $row1[3];
    // $row[] = $row1[2];


    $outputLeadType = '<span class="inline-block lead-type-' . $aRow['type'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . $aRow['color'] . '">' . $aRow['type_name'];

    if (!$locked) {

        $outputLeadType .= '<div class="dropdown inline-block mleft5 table-export-exclude">';

        $outputLeadType .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsType-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';

        $outputLeadType .= '<span data-toggle="tooltip" title="' . _l('Change Lead Type') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';

        $outputLeadType .= '</a>';



        $outputLeadType .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsType-' . $aRow['id'] . '">';

        foreach ($type as $leadChangeType) {

            if ($aRow['type'] != $leadChangeType['id']) {

                $outputLeadType .= '<li>

                  <a href="#" onclick="lead_mark_as_type(' . $leadChangeType['id'] . ',' . $aRow['id'] . '); return false;">

                     ' . $leadChangeType['name'] . '

                  </a>

               </li>';
            }
        }

        $outputLeadType .= '</ul>';

        $outputLeadType .= '</div>';
    }

    $outputLeadType .= '</span>';





    $row[] = $outputLeadType;

    $row[] = $aRow['website'];

    $row[] = $aRow['source_name'];

    $row[] = date("Y-m-d", strtotime($aRow[db_prefix() . 'leads.dateadded']));

    $row[] = ($aRow['lastcontact'] == '0000-00-00 00:00:00' || !is_date($aRow['lastcontact']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['lastcontact']) . '" class="text-has-action is-date">' . $aRow['lastcontact'] . '</span>');

    $row[] = ($aRow['email'] != '' ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // $row[] = $row1[1];

    $assignedOutput = '';

    if ($aRow['assigned'] != 0) {

        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];



        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [

            'staff-profile-image-small',

        ]) . '</a>';



        // For exporting

        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';

        $assignedOutput = $full_name;
    }



    $row[] = $assignedOutput;

    //$row[] = $aRow['dateassigned'];
    $row[] = ($aRow['dateassigned'] == '0000-00-00 00:00:00' || !is_date($aRow['dateassigned']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateassigned']) . '" class="text-has-action is-date">' . $aRow['dateassigned'] . '</span>');

    // $row[] = $row1[0];
    // $row[] = $aRow["destination"];

    $row[] = $aRow['city'];

    $row[] = $aRow['state'];



    $base_currency = get_base_currency();

    // $row[] = ($aRow['lead_value'] != 0 ? app_format_money($aRow['lead_value'],$base_currency->symbol) : '');



    $row[] .= render_tags($aRow['tags']);





















    // $row[] = '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateadded']) . '" class="text-has-action is-date">' . $aRow['dateadded'] . '</span>';




    // $row[] = date("Y-m",strtotime($aRow['dateadded']));
    $row[] = ($aRow['followup'] == '0000-00-00 00:00:00' || !is_date($aRow['followup']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['followup']) . '" class="text-has-action is-date">' . $aRow['followup'] . '</span>');


    //time_ago($aRow['lastcontact'])

    // Custom fields add values




    $row['DT_RowId'] = 'lead_' . $aRow['id'];



    if ($aRow['assigned'] == get_staff_user_id()) {

        $row['DT_RowClass'] = 'alert-info';
    }



    if (isset($row['DT_RowClass'])) {

        $row['DT_RowClass'] .= ' has-row-options';
    } else {

        $row['DT_RowClass'] = 'has-row-options';
    }



    $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);


    //print_r($row);die;
    $output['aaData'][] = $row;
}
