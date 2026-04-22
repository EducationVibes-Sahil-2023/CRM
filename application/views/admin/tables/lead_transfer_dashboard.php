<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
defined('BASEPATH') or exit('No direct script access allowed');
$lead_data = array_column(get_type(), null, 'id');
$lead_source = array_column(get_source(), null, 'id');
$lead_status = array_column(get_status(), null, 'id');
$staff_data = array_column(get_all_staff(), null, 'staffid');
$get_staff_user_id = get_staff_user_id();

$aColumns = [

    // Assign & basic info
    db_prefix() . 'lead_transfer_request.assign as assign',
    db_prefix() . 'lead_transfer_request.created_by as created_by',
    db_prefix() . 'leads.phonenumber as phonenumber',

    // Lead info (old + new)
    db_prefix() . 'leads.status as lead_status',
    db_prefix() . 'leads.type as old_lead_type',
    db_prefix() . 'leads.source as old_lead_source',
    db_prefix() . 'lead_transfer_request.lead_type as lead_type',

    // ✅ Correct lead_source logic (NULL + 0 handled)
    'COALESCE(
        NULLIF(' . db_prefix() . 'lead_transfer_request.lead_source, 0),
        ' . db_prefix() . 'leads.source
    ) as lead_source',

    // Reason
    db_prefix() . 'lead_transfer_request.reason as reason',

    // Status labels
    "CASE 
        WHEN " . db_prefix() . "lead_transfer_request.status = 1 THEN 'Approved'
        WHEN " . db_prefix() . "lead_transfer_request.status = 2 THEN 'Rejected'
        WHEN " . db_prefix() . "lead_transfer_request.status = 3 THEN 'Pending'
        ELSE 'Not defined'
    END as status_name",

    // Dates
    db_prefix() . 'lead_transfer_request.created_at as created_date',
    db_prefix() . 'lead_transfer_request.approved_date as approved_date',

    // IDs & status
    db_prefix() . 'lead_transfer_request.leadid as leadid',
    db_prefix() . 'lead_transfer_request.status as transfer_status',

    // Approval info
    db_prefix() . 'lead_transfer_request.approved_by as approved_by',
    db_prefix() . 'lead_transfer_request.approval_text as approval_text',

    // Other flags
    db_prefix() . 'lead_transfer_request.automatic as automatic',
    db_prefix() . 'lead_transfer_request.id as lead_transfer_id',

    // Status color
    "CASE 
        WHEN " . db_prefix() . "lead_transfer_request.status = 1 THEN 'success'
        WHEN " . db_prefix() . "lead_transfer_request.status = 2 THEN 'danger'
        WHEN " . db_prefix() . "lead_transfer_request.status = 3 THEN 'warning'
        ELSE ''
    END as status_color"
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lead_transfer_request';

$where  = [];
$filter = [];

$join          = [];
array_push($join, 'JOIN ' . db_prefix() . 'leads  ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'lead_transfer_request.leadid');
$join[] = "LEFT JOIN " . db_prefix() . "staff so ON so.staffid = {$sTable}.created_by";



if (!empty($_POST['transfer_date'])) {
    $dateRange = explode(' to ', $_POST['transfer_date']);
    if (count($dateRange) == 2) {
        $startDate = date('Y-m-d', strtotime($dateRange[0]));
        $endDate = date('Y-m-d', strtotime($dateRange[1]));
        $where[] = " AND DATE({$sTable}.created_at) BETWEEN '{$startDate}' AND '{$endDate}' ";
    }
}

if (!empty($_POST['status'])) {
    $statusIds = implode(',', array_map('intval', $_POST['status']));
    $where[] = " AND (".db_prefix()."leads.status IN ({$statusIds})) ";
}


if (!empty($_POST['source'])) {
    $sourceIds = implode(',', array_map('intval', $_POST['source']));
    $where[] = " AND (".db_prefix()."leads.source IN ({$sourceIds})) ";
}



if (!empty($_POST['assigned'])) {
    $assignedIds = implode(',', array_map('intval', $_POST['assigned']));
    $where[] = " AND ({$sTable}.created_by IN ({$assignedIds})) ";
}else
{
    
      if(!empty($_POST['department']))
        {
        $departments = implode(',', array_map('intval', $_POST['department']));
    $where[] = " AND (so.department IN ({$departments})) ";
        }
        
        
    $role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
    $sid = $get_staff_user_id;
if ($role == 3) {
    
    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();
    $idsarr = array_column($teamids, 'staffid');
    $sids = implode(",", $idsarr);
    
}
if(!is_admin()){
$where[] = !empty($sids) ? " AND {$sTable}.created_by IN ({$sid}, {$sids})" : "AND {$sTable}.created_by = {$sid} ";
}
}


// if (!empty($_POST['update_count'])) {
//     $updateCounts = implode(',', array_map('intval', $_POST['update_count']));
//     $where[] = " AND ({$sTable}.update_count IN ({$updateCounts})) ";
// }

 array_push($where, " AND date(" . db_prefix() . "lead_transfer_request.created_at) >= '2026-04-06' ");




$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);



$output  = $result['output'];
$rResult = $result['rResult'];

    foreach ($rResult as $aRow) {
         $row = [];
        $reason = !empty($aRow['reason']) ? addslashes($aRow['reason']) : '';
        $lead_transfer_id = !empty($aRow['lead_transfer_id']) ? $aRow['lead_transfer_id'] : '';
        $assign = !empty($aRow['assign']) ? $aRow['assign'] : '';
        $lead_type = !empty($aRow['lead_type']) ? $aRow['lead_type'] : '';
        $leadid = !empty($aRow['leadid']) ? $aRow['leadid'] : '';
        $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : '';
        $row[] = !empty($lead_status[$aRow["status"]]["name"]) ? $lead_status[$aRow["status"]]["name"] : '';
        $row[] = !empty($lead_data[$aRow["old_lead_type"]]["name"]) ? $lead_data[$aRow["old_lead_type"]]["name"] : '';
        $row[] = !empty($lead_source[$aRow["old_lead_source"]]["name"]) ? $lead_source[$aRow["old_lead_source"]]["name"] : '';
        $row[] = !empty($staff_data[$aRow["assign"]]["full_name"]) ? $staff_data[$aRow["assign"]]["full_name"] : "";
        $row[] = !empty($aRow['phonenumber']) ? ($aRow['phonenumber']) : '';
        $row[] = $lead_data[$aRow["lead_type"]]["name"];
        $row[] = !empty($lead_source[$aRow["lead_source"]]["name"])?$lead_source[$aRow["lead_source"]]["name"]:$lead_source[$aRow["old_lead_source"]]["name"];
        $row[] = !empty($aRow['reason']) ? ($aRow['reason']) : '';
        $row[] = !empty($aRow['status_name']) ? '<span class="text-' . $aRow['status_color'] . '">' . $aRow['status_name'] . '</span>' : 'Not defined';
        $row[] = !empty($aRow['created_date']) ? _d($aRow['created_date']) : '';
      

        $row['DT_RowClass'] = 'has-row-options';
        $output['aaData'][] = $row;
}
