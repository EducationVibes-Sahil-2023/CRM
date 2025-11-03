<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('hostel_management', '', 'backend');

// Columns for DataTables
$aColumns = [
    db_prefix() . 'hostel.name as name',
    db_prefix() . 'hostel_rental.hostel_id as hostel_id',
    db_prefix() . 'hostel_rental.room_capacity as room_capacity',
    db_prefix() . 'hostel_rental.currency as currency',
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'hostel_rental.rent as rent',
    db_prefix() . 'hostel_rental.id as id',
    db_prefix() . 'hostel_rental.rental_details as rental_details',
    db_prefix() . 'hostel_rental.acadmic_year as acadmic_year',
    db_prefix() . 'hostel_rental.year as year',


];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'hostel_rental';
$join = [];
// Joins with aliases for payment modes
$join = [
    'LEFT JOIN ' . db_prefix() . 'currencies 
        ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'hostel_rental.currency',
    'LEFT JOIN ' . db_prefix() . 'hostel 
        ON ' . db_prefix() . 'hostel.id = ' . db_prefix() . 'hostel_rental.hostel_id',
];

// Optional WHERE conditions
$where = [];

$where[] = " AND " . db_prefix() . 'hostel_rental.status = 1 ';

$where[] = " AND " . db_prefix() . 'hostel_rental.acadmic_year != "" ';
// Group by ID to prevent duplicates
$group_by = 'GROUP BY ' . db_prefix() . 'hostel_rental.id';

// Execute DataTables query
$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [db_prefix() . 'hostel_rental.id'], // Select ID explicitly
    $group_by
);

$output  = $result['output'];
$rResult = $result['rResult'];

// Build DataTable rows
foreach ($rResult as $aRow) {
    $rental_details = json_decode($aRow['rental_details'], true);
    $rental_info = '';
    if (!empty($rental_details)) {
        foreach ($rental_details as $detail) {
            $rental_info .= 'Name: ' . $detail['name'] . ', Amount: ' . $detail['amount'] . ' ' . $detail['currency_name'] . '<br>';
        }
    }
    $row = [];
    $row[] = $aRow['name'];
    $row[] = $rental_info;

    $action = '';
    if (has_permission('hostel_management', '', 'backend')) {
        // Convert PHP array to JSON, then encode in base64
        $encodedData = base64_encode(json_encode($aRow));

        // Use it in your onclick function
        $action .= '<a href="javascript:void(0);" onclick="Edit('
            . $aRow['id'] . ', \'' . $encodedData . '\')" class="btn btn-default btn-icon">
                <i class="fa fa-pencil-square-o"></i>
                </a>';
    }
    if (has_permission('hostel_management', '', 'backend')) {
        $action .= ' <a href="javascript:void(0);"  onclick="Delete(' . $aRow['id'] . ')" c class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>';
    }
    $row[] = $action;
    $output['aaData'][] = $row;
}

// Output JSON
// echo json_encode($output);
