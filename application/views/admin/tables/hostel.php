<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('client_visa_delete', '', 'delete');

// Columns for DataTables
$aColumns = [
    db_prefix() . 'hostel_infomation.name as name',
    db_prefix() . 'hostel_infomation.passport as passport',
    db_prefix() . 'hostel_infomation.university_name as university_name',
    db_prefix() . 'hostel_infomation.room_no as room_no',
    db_prefix() . 'hostel_infomation.floor_no as floor_no',
    db_prefix() . 'hostel_infomation.company as company',
    db_prefix() . 'hostel_infomation.hostel as hostel',
    db_prefix() . 'hostel_infomation.room_capacity as room_capacity',
    db_prefix() . 'hostel_infomation.rent_amount as rent_amount',
    db_prefix() . 'hostel_infomation.currency as currency',
    db_prefix() . 'hostel_infomation.start_date as start_date',
    db_prefix() . 'hostel_infomation.end_date as end_date',
    db_prefix() . 'hostel_infomation.id as id',
    db_prefix() . 'hostel.name as hostel_name',
    db_prefix() . 'hostel_company.name as company_name',
    db_prefix() . 'currencies.name as currency_name',
    //find month difference between two dates as month_difference
    "TIMESTAMPDIFF(MONTH, " . db_prefix() . "hostel_infomation.start_date, " . db_prefix() . "hostel_infomation.end_date) as month_difference"


];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'hostel_infomation';
$join = [];
// Joins with aliases for payment modes
$join = [
    'LEFT JOIN ' . db_prefix() . 'hostel
        ON ' . db_prefix() . 'hostel.id = ' . db_prefix() . 'hostel_infomation.hostel',
    'LEFT JOIN ' . db_prefix() . 'hostel_company
        ON ' . db_prefix() . 'hostel_company.id = ' . db_prefix() . 'hostel_infomation.company',
    'LEFT JOIN ' . db_prefix() . 'currencies 
        ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'hostel_infomation.currency'

];

// Optional WHERE conditions
$where = [];

$where[] = " AND " . db_prefix() . 'hostel_infomation.status = 1 ';
// Group by ID to prevent duplicates
$group_by = 'GROUP BY ' . db_prefix() . 'hostel_infomation.id';

// Execute DataTables query
$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [db_prefix() . 'hostel_infomation.id'], // Select ID explicitly
    $group_by
);

$output  = $result['output'];
$rResult = $result['rResult'];

// Build DataTable rows
foreach ($rResult as $aRow) {
    $row = [];
    $nameRow = $aRow['name'] . "<br>";
    if (has_permission('hostel_management', '', 'backend_edit')) {
        $nameRow .= '<a href="' . admin_url('hostel_management/groups/' . $aRow['id'] . '?groups=profile') . '" >' . _l('edit') . '</a>';
    }

    if ($has_permission_delete) {
        $nameRow .= ' | <a href="javascript:void(0)" onclick="Delete(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    }
    $row[] = $nameRow;
    $row[] = $aRow['passport'];
    $row[] = $aRow['university_name'];
    $row[] = $aRow['room_no']; // Format date
    $row[] = $aRow['floor_no'];       // Yes/No
    $row[] = $aRow['company_name'];
    $row[] = $aRow['hostel_name'];
    $row[] = $aRow['room_capacity'];
    $row[] = $aRow['rent_amount'];
    $row[] = $aRow['currency_name'];
    $row[] = $aRow['start_date'];
    $row[] = $aRow['end_date'];
    $row[] = $aRow['month_difference'];

    // Add delete button if user has permission
    // if ($has_permission_delete) {
    //     $row[] = '<a href="' . admin_url('visa/delete/' . $aRow['id']) . '" class="btn btn-danger btn-sm _delete">
    //                 <i class="fa fa-trash"></i>
    //               </a>';
    // }

    $output['aaData'][] = $row;
}

// Output JSON
// echo json_encode($output);
