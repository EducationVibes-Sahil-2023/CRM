<?php
defined('BASEPATH') or exit('No direct script access allowed');

$sTable  = db_prefix() . "leads_transfer_logs";
$sIndexColumn = 'id';
$this->ci->load->model('leads_model');
$statuses = array_column($this->ci->leads_model->get_status(), null, 'id');
$staff = array_column($this->ci->leads_model->get_staff_list(), null, 'staffid');

// ❗ NO "AS" here
$aColumns = [
    "{$sTable}.id as id",
    "{$sTable}.name as name",
    "{$sTable}.phonenumber as phonenumber",
    "{$sTable}.update_count as update_count",
    "{$sTable}.old_status as old_status",
    "{$sTable}.new_status as new_status",
    "{$sTable}.old_assignation as old_assignation",
    "{$sTable}.new_assignation as new_assignation",
    "{$sTable}.old_assignation_date as old_assignation_date",
    "{$sTable}.new_assignation_date as new_assignation_date",
    "{$sTable}.created_at as created_at",
];

$join = [];
$where = [];
$additionalColumns = [];

// $join[] = "LEFT JOIN " . db_prefix() . "leads_status os ON os.id = {$sTable}.old_status";
// $join[] = "LEFT JOIN " . db_prefix() . "leads_status ns ON ns.id = {$sTable}.new_status";
// $join[] = "LEFT JOIN " . db_prefix() . "staff so ON so.staffid = {$sTable}.old_assignation";
// $join[] = "LEFT JOIN " . db_prefix() . "staff sn ON sn.staffid = {$sTable}.new_assignation";

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
    $where[] = " AND ({$sTable}.old_status IN ({$statusIds})) ";
}

if (!empty($_POST['assigned'])) {
    $assignedIds = implode(',', array_map('intval', $_POST['assigned']));
    $where[] = " AND ({$sTable}.old_assignation IN ({$assignedIds})) ";
}

if (!empty($_POST['update_count'])) {
    $updateCounts = implode(',', array_map('intval', $_POST['update_count']));
    $where[] = " AND ({$sTable}.update_count IN ({$updateCounts})) ";
}



$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    $additionalColumns
);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {

    $row = $aRow;

    $row[] = $aRow['id'];
    $row[] = $aRow['name'];
    $row[] = $aRow['phonenumber'];
    $row[] = $aRow['update_count'];
    $row[] = !empty($statuses[$aRow['old_status']]) ? $statuses[$aRow['old_status']]['name'] : '';
    $row[] = !empty($statuses[$aRow['new_status']]) ? $statuses[$aRow['new_status']]['name'] : '';
    $row[] = !empty($staff[$aRow['old_assignation']]) ? $staff[$aRow['old_assignation']]['staff_name'] : '';
    $row[] = !empty($staff[$aRow['new_assignation']]) ? $staff[$aRow['new_assignation']]['staff_name'] : '';
    $row[] = _dt($aRow['old_assignation_date']);
    $row[] = _dt($aRow['new_assignation_date']);
    $row[] = _dt($aRow['created_at']);

    $output['aaData'][] = $row;
}
