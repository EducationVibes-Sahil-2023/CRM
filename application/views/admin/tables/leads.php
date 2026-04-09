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
$statuses              = $this->ci->leads_model->get_status();
$statuses = array_column($statuses, null, "id");

$type              = $this->ci->leads_model->get_type();
$type = array_column($type, null, "id");


if ($is_admin) {
    $custom_fields         = get_table_custom_fields('leads');
}


$holidays = "'" . implode("','", holiday_list()) . "'"; 

$startLength = $_POST['start'];
$endLength = $_POST['length'];

function calculateOfficeTimeDiff(
    $assignedDate,
    $callDate,
    $officeStart = '10:00:00',
    $officeEnd = '20:00:00'
) {
    
    $holidays =[];
    if (empty($assignedDate) || empty($callDate)) {
        return '';
    }

    $start = new DateTime($assignedDate);
    $end   = new DateTime($callDate);

    if ($end <= $start) {
        return '';
    }

    $totalSeconds = 0;

    while ($start < $end) {

        $currentDate = $start->format('Y-m-d');
        $isSunday  = ($start->format('w') == 0);
        $isHoliday = in_array($currentDate, $holidays);

        // ❌ Skip Sundays & Holidays completely
        if ($isSunday || $isHoliday) {
            $start->modify('+1 day')->setTime(0, 0, 0);
            continue;
        }

        // Office hours for current day
        list($h1, $m1, $s1) = explode(':', $officeStart);
        list($h2, $m2, $s2) = explode(':', $officeEnd);

        $dayStart = (clone $start)->setTime($h1, $m1, $s1);
        $dayEnd   = (clone $start)->setTime($h2, $m2, $s2);

        // Determine actual working window
        $actualStart = max($start, $dayStart);
        $actualEnd   = min($end, $dayEnd);

        if ($actualEnd > $actualStart) {
            $totalSeconds += ($actualEnd->getTimestamp() - $actualStart->getTimestamp());
        }

        // Move to next day
        $start->modify('+1 day')->setTime(0, 0, 0);
    }

    // Format result
    $days = floor($totalSeconds / 86400);
    $hours = floor(($totalSeconds % 86400) / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;

    return sprintf('%dD:%02d:%02d:%02d', $days, $hours, $minutes,$seconds);
}
 

$sTable  =  db_prefix() . "leads l";

$call_table     = db_prefix() . 'calls_activity_logs';

$select = [];

$select = [
   "l.id as id",
    "CASE
    WHEN (
        GREATEST(
            IFNULL(DATE(l.lastupdate_date), '1970-01-01'),
            IFNULL((
                SELECT MAX(DATE(n.dateadded))
                FROM tblnotes n
                WHERE n.rel_id = l.id 
                  AND n.rel_type = 'lead'
            ), '1970-01-01')
        ) >= IFNULL((
            SELECT MAX(DATE(r.date))
            FROM tblreminders r
            WHERE r.rel_id = l.id 
              AND r.rel_type = 'lead'
        ), '1970-01-01')
    ) THEN 3

    WHEN (
        CURDATE() <= IFNULL((
            SELECT MAX(DATE(r.date))
            FROM tblreminders r
            WHERE r.rel_id = l.id 
              AND r.rel_type = 'lead'
        ), '1970-01-01')
    ) THEN 2

    ELSE 1
END AS followup_status",
    "count(DISTINCT calls.id) AS update_count",
    "IFNULL(SUM( DISTINCT calls.duration), 0) AS call_duration",
    "l.lastconnect_date as lastconnect_date",
    " MIN(
    CASE 
        WHEN (calls.call_start + 19800) > UNIX_TIMESTAMP(l.dateassigned)
        THEN calls.call_start
    END
) AS first_call_start,

CASE 
    WHEN l.dateassigned IS NULL 
    THEN NULL
    ELSE TIMESTAMPDIFF(
        SECOND,
        l.dateassigned,
        FROM_UNIXTIME(
            MIN(
                CASE 
                    WHEN (calls.call_start + 19800) > UNIX_TIMESTAMP(l.dateassigned)
                    THEN calls.call_start
                END
            ) + 19800
        )
    )
END AS time_diff_seconds",
    "l.dateadded as dateadded",
   
];

// if(is_admin())
// {
    $select[]= "l.lastupdate_date as lastupdate_date";
// }

$select =array_merge($select,["(SELECT GROUP_CONCAT(name SEPARATOR ',') FROM tbltaggables 
        JOIN tbltags ON tbltaggables.tag_id = tbltags.id 
        WHERE rel_id = l.id AND rel_type='lead' ORDER BY tag_order ASC LIMIT 1) as tags","l.name as name","l.phonenumber as phonenumber","l.status as status"]);

if ($is_admin) {
    foreach ($custom_fields as $field) {
        $select[] = 'CONCAT("' . $field['fieldto'] . '-", l.id, "-' . $field['id'] . '") as ' . strtolower(str_replace(' ', '_', $field["name"]));
    }
}

$select = array_merge($select, [
    'l.type as type',
    'l.website as website',
    'l.reference_name as reference_name',
    'l.source as source',
    'l.email as email',
    'l.assigned as assigned',
    'l.dateassigned as dateassigned',
    'l.city as city',
    'l.state as state',
    'MAX(r.dateadded) AS followup',
    "l.upcomming_count as upcomming_count",
    "st.name as status_name",
    "lt.name as type_name",
    "ls.name as source_name",
    "st.color as color",
    "st.bg_color as bg_color",
    "CONCAT(s.firstname,' ',s.lastname) as assigned_name",
    "office_start_time",
    "office_end_time",
    // "MIN(calls.call_start) phonenumber_duration",
    
    "MIN(
    CASE 
        WHEN (calls.call_start + 19800) > UNIX_TIMESTAMP(l.dateassigned)
        THEN calls.call_start 
    END
) AS phonenumber_duration",
    
    "IFNULL(clients.userid,0) is_converted"

]);



$finalSelect = [
   "Final.id as id",
    "Final.followup_status as followup_status",
    "SUM(Final.update_count) as update_count",
    "IFNULL(SUM(Final.call_duration), 0) as call_duration",
    "Final.lastconnect_date as lastconnect_date",
    "MAX(Final.first_call_start) as first_call_start,
MIN(Final.time_diff_seconds) as time_diff_seconds",
    "Final.dateadded as dateadded",
   
];

// if(is_admin())
// {
    $finalSelect[]= "MAX(Final.lastupdate_date) as lastupdate_date";
// }

$finalSelect =array_merge($finalSelect,["Final.tags as tags","Final.name as name","Final.phonenumber as phonenumber","Final.status as status"]);

if ($is_admin) {
    foreach ($custom_fields as $field) {
        $finalSelect[] = 'CONCAT("' . $field['fieldto'] . '-", Final.id, "-' . $field['id'] . '") as ' . strtolower(str_replace(' ', '_', $field["name"]));
    }
}

$finalSelect = array_merge($finalSelect, [
    'Final.type as type',
    'Final.website as website',
    'Final.reference_name as reference_name',
    'Final.source as source',
    'Final.email as email',
    'Final.assigned as assigned',
    'Final.dateassigned as dateassigned',
    'Final.city as city',
    'Final.state as state',
    'MAX(Final.followup) as followup',
    "Final.upcomming_count as upcomming_count",
    "Final.status_name as status_name",
    "Final.type_name as type_name",
    "Final.source_name as source_name",
    "Final.color as color",
    "Final.bg_color as bg_color",
    "Final.assigned_name as assigned_name",
    "Final.office_start_time as office_start_time",
    "Final.office_end_time as office_end_time",
    "MIN(Final.phonenumber_duration) as phonenumber_duration",
    "Final.is_converted as is_converted"

]);


$where=[];

$externalLimit= "";



if (!empty($this->ci->input->post('up_to_date'))) {
    $up_to_date = $this->ci->input->post('up_to_date');
    $up_from_date   = $this->ci->input->post('up_from_date');

    $up_from_date = $this->ci->db->escape_str($up_from_date); // Start date
    $up_to_date = $this->ci->db->escape_str($up_to_date);     // End date


    $where[] = " AND calls.adjusted_call_start >= '{$up_from_date} 00:00:00'
        AND calls.adjusted_call_start <= '{$up_to_date} 23:59:59' ";


    if (!empty($this->ci->input->post('assigned'))) {
        $where[] = " AND  calls.staffid IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ") ";
    }
} 

$role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
if ($role == 3) {
    $sid = $get_staff_user_id;
    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();
    $idsarr = array_column($teamids, 'staffid');
    $sids = implode(",", $idsarr);
    $where[] = !empty($sids) ? " AND l.assigned IN ({$sid}, {$sids})" : "AND l.assigned = {$sid} ";
}

// Apply filters based on input parameters
if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {
    $where[] = " AND l.assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ") ";
}

if ($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0) {
    $where[] = " AND l.status IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('status'))) . ") ";
}

if ($this->ci->input->post('source')) {
    $where[] = " AND l.source IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('source'))) . ") ";
}

if ($this->ci->input->post('sub_status')) {
    $where[] = " AND l.sub_status IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('sub_status'))) . ") ";
}
if ($this->ci->input->post('view_form')) {
    $websites = $this->ci->input->post('view_form');
    $escaped_websites = array_map(function ($w) {
        return " '" . $this->ci->db->escape_str(trim($w)) . "' ";
    }, $websites);

    $where[] = " AND l.website IN (" . implode(',', $escaped_websites) . ") ";
}

if ($this->ci->input->post('reference_name')) {
    $reference_name = $this->ci->input->post('reference_name');
    $escaped_reference_name = array_map(function ($w) {
        return "'" . $this->ci->db->escape_str(trim($w)) . "'";
    }, $reference_name);

    $where[] = " AND l.reference_name IN (" . implode(',', $escaped_reference_name) . ") ";
}




if ($this->ci->input->post('lead_type')) {
    $where[] = " AND l.type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ") ";
}

if ($this->ci->input->post('assign_to_date')) {
    $assign_from_date = $this->ci->input->post('assign_from_date');
    $assign_to_date = $this->ci->input->post('assign_to_date');
    array_push($where, ' AND DATE(l.dateassigned) BETWEEN "' . $this->ci->db->escape_str($assign_from_date) . '" AND "' . $this->ci->db->escape_str($assign_to_date) . '" ');
}

// Date filters for lead creation, assignment, follow-up, and NEET score
if ($this->ci->input->post('to_date')) {
    $from_date = $this->ci->input->post('from_date');
    $to_date   = $this->ci->input->post('to_date');
    $where[]   = " AND DATE(l.dateadded) BETWEEN '{$this->ci->db->escape_str($from_date)}' AND '{$this->ci->db->escape_str($to_date)}' ";
}


if ($this->ci->input->post('followup_to_date')) {
    $followup_from_date = $this->ci->input->post('followup_from_date');
    $followup_to_date   = $this->ci->input->post('followup_to_date');
    $where[] = " AND DATE(r.date) BETWEEN '{$this->ci->db->escape_str($followup_from_date)}' AND '{$this->ci->db->escape_str($followup_to_date)}' ";
}

if ($this->ci->input->post('last_update_date') || $this->ci->input->post('last_contact_date')) {

    // Add condition for last_contact_date
    if (!empty($this->ci->input->post('last_contact_date'))) {
        $last_contact_date = $this->ci->db->escape_str($this->ci->input->post('last_contact_date'));
        array_push($where, ' AND l.lastconnect_date <= "' . $this->ci->db->escape_str($last_contact_date) . '" ');
    }

    // Add condition for last_update_dateCOUNT(DISTINCT calls.id)
    if (!empty($this->ci->input->post('last_update_date'))) {
        $last_update_date = $this->ci->db->escape_str($this->ci->input->post('last_update_date'));
        array_push($where, ' AND l.lastupdate_date <= "' . $this->ci->db->escape_str($last_update_date) . '" ');
    }
}

// if ($this->ci->input->post('show_update_counts') && $this->ci->input->post('show_update_counts') == 1) {
//     $min = isset($_POST['update_count_min']) ? $_POST['update_count_min'] : 0;
//     $max = isset($_POST['update_count_max']) ? $_POST['update_count_max'] : 0;

//     $where[] = "AND count(calls.id) BETWEEN '{$this->ci->db->escape_str($min)}' AND '{$this->ci->db->escape_str($max)}'";
// }

$having ="";
$having_ ="";
if ($this->ci->input->post('show_update_counts') && $this->ci->input->post('show_update_counts') == 1) {
    $min = isset($_POST['update_count_min']) ? $_POST['update_count_min'] : 0;
    $max = isset($_POST['update_count_max']) ? $_POST['update_count_max'] : 0;

    $having = " HAVING update_count   BETWEEN '{$this->ci->db->escape_str($min)}' AND '{$this->ci->db->escape_str($max)}' ";
    
    
}
// Check user permissions and access scope
if (!has_permission('leads', '', 'view')) {
    $where[] = " AND (l.assigned = {$get_staff_user_id} OR l.is_public = 1) ";
}

$order_by ="";
$order_by_admin ="";

if(isset($_POST["order"][0]["column"]) && $_POST["order"][0]["column"] >= 0)
{
     if(empty($this->ci->input->post('assigned')) && (is_admin() || $get_staff_user_id == 306 || $role == 3) && empty($where))
     {
         $_POST["order"][0]["column"] = 0;
     }
     else if( $_POST["order"][0]["column"] == 5)
     {
          $_POST["order"][0]["column"] = 0;
     }
   
     
     
        $order_by_ = $select[$_POST["order"][0]["column"]];
        $order_by = " order by ".trim(explode(' AS ', strtoupper($order_by_))[1]) ." ".$_POST["order"][0]["dir"]." ";
        if(is_admin() || $role == 3){
        $order_by_admin = " order by ".trim(explode(' AS ', strtoupper($order_by_))[1]) ." ".$_POST["order"][0]["dir"]." ";
        }
        
// echo $order_by;
// die;

}

$where_condition ="";
if(!empty($where)){
$where_condition  = implode("  ",$where);
}

$select_query = implode(",",$select);

$final_select_query = implode(",",$finalSelect);



if (!empty(trim($_POST["search"]["value"]))) {

    $search = trim($_POST["search"]["value"]);

    // Start the search condition
    $where_condition .= " AND ( ";

    if (is_numeric($search)) {
        // Numeric search → only phone numbers
        $where_condition .= " l.phonenumber LIKE '{$search}%' 
                              OR l.alternative_phonenumber LIKE '{$search}%' ";
    } else {
        // Text search → name, city, state, website
        $search_escaped = addslashes($search); // prevent issues
        $where_condition .= " l.name LIKE '{$search_escaped}%'
        OR l.city LIKE '{$search_escaped}%'
        OR l.state LIKE '{$search_escaped}%'
        OR l.website LIKE '{$search_escaped}%' 
        OR (SELECT GROUP_CONCAT(name SEPARATOR ',') FROM tbltaggables 
        JOIN tbltags ON tbltaggables.tag_id = tbltags.id 
        WHERE rel_id = l.id AND rel_type='lead' ORDER BY tag_order ASC LIMIT 1) LIKE '%{$search_escaped}%' ";
                              
    }

    $where_condition .= " ) ";
}

 if (empty($this->ci->input->post('assigned')) && (is_admin() || $get_staff_user_id == 306 || $role == 3)) {
     if(empty($having)){
    $externalLimit = " LIMIT $startLength,$endLength ";
      $startLength=0;
     }
    
 }
 


   $sql = "
SELECT  $final_select_query FROM ( 
   ( SELECT ".$select_query." FROM tblleads l LEFT JOIN tblcalls_activity_logs calls
    ON l.alternative_phonenumber = calls.contact 
    AND l.assigned = calls.staffid 
LEFT JOIN tblstaff s ON s.staffid = l.assigned 
LEFT JOIN tblstaff_department d ON d.id = s.department
LEFT JOIN tblclients clients ON clients.userid = l.id 
LEFT JOIN tblleads_status st ON l.status = st.id 
LEFT JOIN tblleads_type lt ON l.type = lt.id 
LEFT JOIN tblleads_sources ls ON l.source = ls.id 
LEFT JOIN tblreminders r ON l.id = r.rel_id AND r.rel_type = 'lead'
WHERE l.lost = 0 AND l.junk = 0  and l.status = 33 $where_condition GROUP BY l.id  $having_ $externalLimit )
   
   UNION ALL
   
   ( SELECT ".$select_query." FROM tblleads l LEFT JOIN tblcalls_activity_logs calls
    ON l.phonenumber = calls.contact 
    AND l.assigned = calls.staffid
LEFT JOIN tblstaff s ON s.staffid = l.assigned 
LEFT JOIN tblstaff_department d ON d.id = s.department
LEFT JOIN tblclients clients ON clients.userid = l.id 
LEFT JOIN tblleads_status st ON l.status = st.id 
LEFT JOIN tblleads_type lt ON l.type = lt.id 
LEFT JOIN tblleads_sources ls ON l.source = ls.id
LEFT JOIN tblreminders r ON l.id = r.rel_id AND r.rel_type = 'lead'
WHERE l.lost = 0 AND l.junk = 0 and l.status = 33 $where_condition GROUP BY l.id  $having_ $externalLimit ) )  as Final GROUP BY Final.id  $having $order_by LIMIT $startLength,$endLength ";

// if(is_admin())
// {
//      echo $sql;
//      die;
// }
  
    $Result = $this->ci->db->query($sql)->result_array();
    
$otherLength = !empty($Result) ? count($Result) - 1 : 0;

$otherLength = ($otherLength == 0) 
    ? $endLength 
    : ($endLength - $otherLength);
    
    $otherLength = intval($otherLength);
   $sql ="";
   
if (empty($this->ci->input->post('assigned'))  && (is_admin() || $get_staff_user_id == 306 || $role == 3)) {
   
   $startLength =  $_POST['start'];
    if(empty($having)){
        $externalLimit = " LIMIT $startLength,$otherLength ";
        $startLength =0;
    }
   
 }
 
 
    $sql = "
SELECT $final_select_query FROM ( 
   ( SELECT ".$select_query." FROM tblleads l LEFT JOIN tblcalls_activity_logs calls
    ON l.alternative_phonenumber = calls.contact 
    AND l.assigned = calls.staffid 
LEFT JOIN tblstaff s ON s.staffid = l.assigned 
LEFT JOIN tblstaff_department d ON d.id = s.department
LEFT JOIN tblclients clients ON clients.userid = l.id 
LEFT JOIN tblleads_status st ON l.status = st.id 
LEFT JOIN tblleads_type lt ON l.type = lt.id 
LEFT JOIN tblleads_sources ls ON l.source = ls.id 
LEFT JOIN tblreminders r ON l.id = r.rel_id AND r.rel_type = 'lead'
WHERE l.lost = 0 AND l.junk = 0 and l.status!=33 $where_condition GROUP BY l.id $order_by_admin $having_ $externalLimit )
   
   UNION ALL
   
   ( SELECT ".$select_query." FROM tblleads l LEFT JOIN tblcalls_activity_logs calls
    ON l.phonenumber = calls.contact 
    AND l.assigned = calls.staffid 
LEFT JOIN tblstaff s ON s.staffid = l.assigned 
LEFT JOIN tblstaff_department d ON d.id = s.department
LEFT JOIN tblclients clients ON clients.userid = l.id 
LEFT JOIN tblleads_status st ON l.status = st.id 
LEFT JOIN tblleads_type lt ON l.type = lt.id 
LEFT JOIN tblleads_sources ls ON l.source = ls.id
LEFT JOIN tblreminders r ON l.id = r.rel_id AND r.rel_type = 'lead'
WHERE l.lost = 0 AND l.junk = 0 and l.status!=33 $where_condition GROUP BY l.id $order_by_admin $having_ $externalLimit ) )  as Final GROUP BY Final.id  $having $order_by LIMIT $startLength,$otherLength";
   

    // $Result = $this->ci->db->query($sql)->result_array();

$Result_ = $this->ci->db->query($sql)->result_array();


// if(is_admin())
// {
//      echo $sql;
//      die;
// }
  

// if(is_admin())
// {
//     print_r($sql);
//     die;
// }
$rResult = array_merge((array)$Result, (array)$Result_);
;

 $start_ = (intval($_POST['start']) == 0) ? 0 : intval($_POST['start']);
 $last_ = (count($rResult) == intval($_POST['length'])) ? (1 + intval($_POST['length'])) : count($rResult);

$output =[];
$output['draw'] = $_POST['draw'] ? intval($_POST['draw']) : 0;
$output['iTotalDisplayRecords'] = $start_ + $last_;
$output['iTotalRecords'] = intval($_POST['start']) + $last_;
 $output['aaData'] =[];


if ($is_admin) {
    $lead_ids_array = array_column($rResult, "id");
    if (!empty($lead_ids_array)) {
        $customFieldValues              = $this->ci->leads_model->get_custum_values($lead_ids_array);

        $customFieldValues = array_column($customFieldValues, "value", "column_name");
    }
}

foreach ($rResult as $aRow) {

    // $dates = [];

    // if (!empty($aRow['notesdate']) && $aRow['notesdate'] !== "0000-00-00") {
    //     $dates[] = date("Y-m-d", strtotime($aRow['notesdate']));
    // }

    // if (!empty($aRow['lastupdate_date']) && $aRow['lastupdate_date'] !== "0000-00-00") {
    //     $dates[] = date("Y-m-d", strtotime($aRow['lastupdate_date']));
    // }

    // $latest_update_date = !empty($dates) ? max($dates) : null; // Get the latest valid date

    // $aRow['status_name'] = isset($statuses[$aRow['status']]["name"]) ? $statuses[$aRow['status']]["name"] : '';
    // $aRow['bg_color'] = isset($statuses[$aRow['status']]["bg_color"]) ? $statuses[$aRow['status']]["bg_color"] : '';
    // $aRow['color'] = isset($statuses[$aRow['status']]["color"]) ? $statuses[$aRow['status']]["color"] : '';
    // $aRow['type_name'] = isset($type[$aRow['type']]["name"]) ? $type[$aRow['type']]["name"] : '';
    // $aRow['source_name'] = isset($source[$aRow['source']]["name"]) ? $source[$aRow['source']]["name"] : '';
    // $aRow['assigned_name'] =
    //     (isset($staff_list[$aRow['assigned']]["firstname"]) ? $staff_list[$aRow['assigned']]["firstname"] : '') .
    //     " " .
    //     (isset($staff_list[$aRow['assigned']]["lastname"]) ? $staff_list[$aRow['assigned']]["lastname"] : '');

    $row = [];
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    // echo $aRow['followup']  ;
    // $curdate = date("Y-m-d");
    // $date1 = !empty($latest_update_date) ? date("Y-m-d", strtotime($latest_update_date)) : '';
    // $date2 = !empty($aRow['followup']) ? date("Y-m-d", strtotime($aRow['followup'])) : '';
    // if ($date1 >= $date2) {
    //     $col = '<span style="color:#0f970f;font-size: 16px;"><i class="fa fa-check-circle"></i></span>';
    // } else {
    //     $col = ($curdate <= $date2) ? '<span style="color:#f4f407;font-size: 16px;"><i class="fa fa-check-circle"></i></span>' : '<span style="color:#fb3121;font-size: 16px;"><i class="fa fa-times-circle"></i></span>';
    // }
    // $row[]    = $col;
    
   
     $col ='<span style="color:#fb3121;font-size: 16px;"><i class="fa fa-times-circle"></i></span>';
    if($aRow['followup_status'] == 3)
    {
        $col ='<span style="color:#0f970f;font-size: 16px;"><i class="fa fa-check-circle"></i></span>';
    }
    else if($aRow['followup_status'] == 2)
    {
        $col ='<span style="color:#f4f407;font-size: 16px;"><i class="fa fa-check-circle"></i></span>';
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
    $row[] =  ($aRow['lastconnect_date'] == '0000-00-00') ? '' : $aRow['lastconnect_date'];
   
    
  $d1 = $aRow['phonenumber_duration'];
$d2 = $aRow['alternative_duration'];

// Convert empty values to null
$d1 = !empty($d1) ? ($d1) : null;
$d2 = !empty($d2) ? ($d2) : null;

// Get minimum valid timestamp
if ($d1 && $d2) {
    $minDate = min($d1, $d2);
} elseif ($d1) {
    $minDate = $d1;
} elseif ($d2) {
    $minDate = $d2;
} else {
    $minDate = null;
}


// Convert back to datetime if needed
$minDateFormatted = $minDate ? date('Y-m-d H:i:s', $minDate) : null;

// Final call
// $row[] = calculateOfficeTimeDiff(
//     $aRow['dateassigned'],
//     $minDateFormatted,
//     $aRow['office_start_time'],
//     $aRow['office_end_time']
// );

    
 
 $row[] = calculateOfficeTimeDiff(
     $aRow['dateassigned'],
    $minDateFormatted,
    $aRow['office_start_time'],
     $aRow['office_end_time']
 );

//   $row[] = $aRow['first_connect_difference'];
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

    $nameRow = '<a ' . $hrefAttr . '>' .mb_substr($aRow['name'], 0, 30) . '</a>';
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
        $row[] = mb_substr(!empty($customFieldValues[$aRow[str_replace(" ", "_", strtolower($field['name']))]]) ? $customFieldValues[$aRow[str_replace(" ", "_", strtolower($field['name']))]] : '', 0, 30);
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

    if ($is_admin || $role == 3) {
        $row[] = $aRow['reference_name'];
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
    
     $row[] = $aRow['upcomming_count'];

    $row['DT_RowId'] = 'lead_' . $aRow['id'];

    if ($aRow['assigned'] == $get_staff_user_id) {
        $row['DT_RowClass'] = 'alert-info';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }
  if (!empty($aRow['bg_color'])) { 
    $row['DT_RowAttr'] = [
        'style' => "background-color: {$aRow['bg_color']} !important;"
    ];
}

    // $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);
    $output['aaData'][] = $row;
}
