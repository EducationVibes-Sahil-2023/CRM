<?php
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

    $where_c  .= " AND {$call_table}.staffid = {$sTable}.assigned ";

    // $sTable = $call_table;

    $join[] = " JOIN " . db_prefix() . "leads ON (
   {$call_table}.contact IN (
        REPLACE(TRIM(REPLACE(phonenumber, '+91', '')), ' ', ''),
        REPLACE(TRIM(REPLACE(alternative_phonenumber, '+91', '')), ' ', '')
    )
    AND DATE({$call_table}.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}' $where_c
)";

    $where[] = " AND DATE({$call_table}.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}' ";

    if (!empty($this->ci->input->post('assigned'))) {
        $where[] = "AND " . $call_table . ".staffid IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
    }
}


if (!empty($_POST["search"]["value"])) {
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'taggables ON ' . db_prefix() . 'taggables.rel_id = ' . $sTable . '.id  AND ' . db_prefix() . 'taggables.rel_type = "lead" ');
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id ');
}





if (!$filter || ($filter && !in_array($filter, ['lost', 'junk']))) {
    $where[] = "AND " . $sTable . ".lost = 0 AND " . $sTable . ".junk = 0";
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

if ($this->ci->input->post('view_form')) {
    $websites = $this->ci->input->post('view_form');
    $escaped_websites = array_map(function ($w) {
        return "'" . $this->ci->db->escape_str(trim($w)) . "'";
    }, $websites);

    $where[] = "AND " . $sTable . ".website IN (" . implode(',', $escaped_websites) . ")";
}



if ($this->ci->input->post('lead_type')) {
    $where[] = "AND " . $sTable . ".type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ")";
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

$aColumns = [
    $sTable . '.id as id',
    '(
        CASE
            WHEN (
                GREATEST(
                    IFNULL(DATE(' . $sTable . '.lastupdate_date), "0000-00-00"),
                    IFNULL((
                        SELECT MAX(DATE(dateadded))
                        FROM ' . db_prefix() . 'notes
                        WHERE rel_id = ' . $sTable . '.id AND rel_type = "lead"
                    ), "0000-00-00")
                ) >= IFNULL((
                    SELECT MAX(DATE(date))
                    FROM ' . db_prefix() . 'reminders
                    WHERE rel_id = ' . $sTable . '.id AND rel_type = "lead"
                ), "0000-00-00")
            ) THEN 3
            WHEN (
                CURDATE() <= IFNULL((
                    SELECT MAX(DATE(date))
                    FROM ' . db_prefix() . 'reminders
                    WHERE rel_id = ' . $sTable . '.id AND rel_type = "lead"
                ), "0000-00-00")
            ) THEN 2
            ELSE 1
        END
    ) as followup_status'
];




if (is_gdpr() && $consentLeads == '1') {
    $aColumns[] = '1';
}
if ($is_admin) {
    $aColumnsExtra = [
        "IFNULL({$sTable}.update_count,0) as update_count",
        "IFNULL({$sTable}.call_duration,0) as call_duration",
        "{$sTable}.lastconnect_date as lastcontact_date",
        "{$sTable}.dateadded as dateadded",
        "{$sTable}.lastupdate_date as lastupdate_date",
    ];
} else {
    $aColumnsExtra = [
        "IFNULL({$sTable}.update_count,0) as update_count",
        "IFNULL({$sTable}.call_duration,0) as call_duration",
        "{$sTable}.lastconnect_date as lastcontact_date",
        "{$sTable}.dateadded as dateadded",
    ];
}

// Add conditional tags column based on search
if (!empty($_POST["search"]["value"])) {
    $aColumnsExtra[] = db_prefix() . 'tags.name as tags';
} else {
    $aColumnsExtra[] = '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables 
        JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id 
        WHERE rel_id = ' . $sTable . '.id AND rel_type="lead" ORDER BY tag_order ASC LIMIT 1) as tags';
}

// Common fields for both admin and non-admin
$aColumnsExtra[] = "{$sTable}.name as name";
$aColumnsExtra[] = "{$sTable}.phonenumber as phonenumber";
$aColumnsExtra[] = "{$sTable}.status as status";

// Merge with existing columns
$aColumns = array_merge($aColumns, $aColumnsExtra);

if ($is_admin) {
    foreach ($custom_fields as $field) {
        $aColumns[] = 'CONCAT("' . $field['fieldto'] . '-", ' . $sTable . '.id, "-' . $field['id'] . '") as ' . strtolower(str_replace(' ', '_', $field["name"]));
    }
}

$aColumns = array_merge($aColumns, [
    $sTable . '.type as type',
    $sTable . '.website as website',
    $sTable . '.source as source',
    $sTable . '.email as email',
    $sTable . '.assigned as assigned',
    $sTable . '.dateassigned as dateassigned',
    $sTable . '.city as city',
    $sTable . '.state as state',

]);



if ($this->ci->input->post('followup_to_date')) {
    $aColumns[] = db_prefix() . "reminders.date as followup";
} else {
    $aColumns[] = '(SELECT date FROM ' . db_prefix() . 'reminders  WHERE rel_id = ' . $sTable . '.id and rel_type="lead" ORDER by id DESC LIMIT 1) as followup';
}

$aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);

$additionalColumns = [];
$additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [
    'lead_value',
    'company',
    'junk',
    'lost',
    'assigned',
    $sTable . '.addedfrom as addedfrom',
    '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . $sTable . '.id) as is_converted',
    'alternative_phonenumber',
    'zip',
    '(SELECT ' . db_prefix() . 'notes.dateadded FROM ' . db_prefix() . 'notes  WHERE rel_id = ' . $sTable . '.id and rel_type="lead" ORDER by id DESC LIMIT 1) as notesdate',
    "{$sTable}.lastupdate_date as lastupdate_date",

]);





$search_column = [];
// Define search and group-by clauses
if (!empty($_POST["search"]["value"])) {
    $search_column = [$sTable . ".city", $sTable . ".phonenumber", $sTable . ".state", db_prefix() . 'tags.name', "alternative_phonenumber", $sTable . ".website", $sTable. ".name"];
}

$having_ = "";
$having = "";
$group_by = ' Group By ' . $sTable . '.id ' . $having . " ";



// Execute final query with applied filters and joins

if (is_admin()) {
    if (!empty($_POST["order"][0]["column"]) && ($_POST["order"][0]["column"] == 5)) {
        $_POST["order"][0]["column"] = 0;
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

// print_r($rResult);
// die;
$lead_ids_array = array_column($rResult, "id");
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



if ($is_admin) {
    if (!empty($lead_ids_array)) {
        $customFieldValues              = $this->ci->leads_model->get_custum_values($lead_ids_array);

        $customFieldValues = array_column($customFieldValues, "value", "column_name");
    }
}

foreach ($rResult as $aRow) {

    $dates = [];

    if (!empty($aRow['notesdate']) && $aRow['notesdate'] !== "0000-00-00") {
        $dates[] = date("Y-m-d", strtotime($aRow['notesdate']));
    }

    if (!empty($aRow['lastupdate_date']) && $aRow['lastupdate_date'] !== "0000-00-00") {
        $dates[] = date("Y-m-d", strtotime($aRow['lastupdate_date']));
    }

    $latest_update_date = !empty($dates) ? max($dates) : null; // Get the latest valid date

    $aRow['status_name'] = isset($statuses[$aRow['status']]["name"]) ? $statuses[$aRow['status']]["name"] : '';
    $aRow['color'] = isset($statuses[$aRow['status']]["color"]) ? $statuses[$aRow['status']]["color"] : '';
    $aRow['type_name'] = isset($type[$aRow['type']]["name"]) ? $type[$aRow['type']]["name"] : '';
    $aRow['source_name'] = isset($source[$aRow['source']]["name"]) ? $source[$aRow['source']]["name"] : '';
    $aRow['assigned_name'] =
        (isset($staff_list[$aRow['assigned']]["firstname"]) ? $staff_list[$aRow['assigned']]["firstname"] : '') .
        " " .
        (isset($staff_list[$aRow['assigned']]["lastname"]) ? $staff_list[$aRow['assigned']]["lastname"] : '');

    $row = [];
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    // echo $aRow['followup']  ;
    $curdate = date("Y-m-d");
    $date1 = !empty($latest_update_date) ? date("Y-m-d", strtotime($latest_update_date)) : '';
    $date2 = !empty($aRow['followup']) ? date("Y-m-d", strtotime($aRow['followup'])) : '';
    if ($date1 >= $date2) {
        $col = '<span style="color:#0f970f;font-size: 16px;"><i class="fa fa-check-circle"></i></span>';
    } else {
        $col = ($curdate <= $date2) ? '<span style="color:#f4f407;font-size: 16px;"><i class="fa fa-check-circle"></i></span>' : '<span style="color:#fb3121;font-size: 16px;"><i class="fa fa-times-circle"></i></span>';
    }
    $row[]    = $col;


    $updatecount = !empty($aRow['update_count']) ? $aRow['update_count'] : 0;


    $row[]    = $updatecount;
    $call_duration = 0;
    $last_call_update = "";
    $row[] = !empty($aRow['call_duration'])
        ? convertToHMS(
            $aRow['call_duration'],
            1
        )
        : convertToHMS($call_duration, 1);

    // if ($role != 1) {
    //     if (empty($latest_update_date)) {
    //         $row[] = "";
    //     } else {
    //         $row[] = (($latest_update_date == '0000-00-00') ? '' : '<span data-toggle="tooltip" data-title="' . ($latest_update_date) . '" class="text-has-action is-date">' . $latest_update_date . '</span>');
    //     }
    // }
    $row[] =  ($aRow['lastcontact_date'] == '0000-00-00') ? '' : $aRow['lastcontact_date'];
    $row[] = date("Y-m-d", strtotime($aRow['dateadded']));


    // $row[] = date("Y-m-d", strtotime($aRow['dateadded']));
    // $row[] = date("Y-m-d", strtotime($aRow['dateadded']));
    // if ($role != 1) {
    //     if (empty($latest_update_date)) {
    //         $row[] = "";
    //     } else {
    //         $row[] = (($latest_update_date == '0000-00-00') ? '' : '<span data-toggle="tooltip" data-title="' . ($latest_update_date) . '" class="text-has-action is-date">' . $latest_update_date . '</span>');
    //     }
    // }

    // if ($role != 1) {
    if (empty($aRow['lastupdate_date'])) {
        $row[] = "";
    } else {
        $row[] = (($aRow['lastupdate_date'] == '0000-00-00') ? '' : '<span data-toggle="tooltip" data-title="' . ($aRow['lastupdate_date']) . '" class="text-has-action is-date">' .  $aRow['lastupdate_date'] . '</span>');
    }
    // }

    $row[] .= render_tags($aRow['tags']);
    $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';

    $nameRow = '<a ' . $hrefAttr . '>' . $aRow['name'] . '</a>';
    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';
    $locked = false;
    if ($aRow['is_converted'] > 0) {
        $locked = ((!$is_admin && $lockAfterConvert == 1) ? true : false);
    }
    if (!$locked) {
        $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
    }
    if ($aRow['addedfrom'] == $get_staff_user_id || $has_permission_delete) {
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
    foreach ($custom_fields as $key => $field) {
        $row[] = !empty($customFieldValues[$aRow[str_replace(" ", "_", strtolower($field['name']))]]) ? $customFieldValues[$aRow[str_replace(" ", "_", strtolower($field['name']))]] : '';
    }

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
    if ($is_admin) {
        $row[] = $aRow['website'];
    }

    $row[] = $aRow['source_name'];
    if ($role != 1) {
        $row[] = ($aRow['email'] != '' ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');
    }

    if ($role != 1) {
        $assignedOutput = '';
        if ($aRow['assigned'] != 0) {
            $full_name = $aRow['assigned_name'];
            $profileImagePath = !empty($aRow['profile_image']) ? base_url('uploads/staff_profile_images/' . $aRow['assigned'] . '/small_' . $aRow['profile_image']) : base_url('assets/images/user-placeholder.jpg');
            $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '"><img src="' . $profileImagePath . '"  class="staff-profile-image-small" /></a>';
            $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
            $assignedOutput = $full_name;
        }
        $row[] = $assignedOutput;
    }

    $row[] = ($aRow['dateassigned'] == '0000-00-00 00:00:00' || !is_date($aRow['dateassigned']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateassigned']) . '" class="text-has-action is-date">' .  date("Y-m-d", strtotime($aRow['dateassigned'])) . "<br>" . date("H:i:s", strtotime($aRow['dateassigned'])) . '</span>');
    $row[] = $aRow['city'];
    $row[] = $aRow['state'];
    // $row[] .= render_tags($aRow['tags']);

    if ($role != 1) {
        $row[] = ($aRow['followup'] == '0000-00-00 00:00:00' || !is_date($aRow['followup']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['followup']) . '" class="text-has-action is-date">' .  date("Y-m-d", strtotime($aRow['followup'])) . "<br>" . date("H:i:s", strtotime($aRow['followup'])) . '</span>');
    }

    $row['DT_RowId'] = 'lead_' . $aRow['id'];

    if ($aRow['assigned'] == $get_staff_user_id) {
        $row['DT_RowClass'] = 'alert-info';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    // $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);
    $output['aaData'][] = $row;
}
