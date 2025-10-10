<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('client_visa_delete', '', 'delete');

// Columns for DataTables
$aColumns = [
    db_prefix() . 'hostel_rental.university_name as university_name',
    db_prefix() . 'hostel_rental.university_id as university_id',
    db_prefix() . 'hostel_rental.room_capacity as room_capacity',
    db_prefix() . 'hostel_rental.currency as currency',
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'hostel_rental.rent as rent',
    db_prefix() . 'hostel_rental.id as id',


];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'hostel_rental';
$join = [];
// Joins with aliases for payment modes
$join = [
    'LEFT JOIN ' . db_prefix() . 'currencies 
        ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'hostel_rental.currency',
];

// Optional WHERE conditions
$where = [];

$where[] = " AND " . db_prefix() . 'hostel_rental.status = 1 ';
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
    $row = [];
    // $nameRow = $aRow['name'] . "<br>";
    // if (has_permission('external_ticket', '', 'create')) {
    //     $nameRow .= '<a href="' . admin_url('clients/external_visa/' . $aRow['id'] . '?edit=true') . '" >' . _l('edit') . '</a>';
    // }

    // if ($has_permission_delete) {
    //     $nameRow .= ' | <a href="javascript:void(0)" onclick="deleteVisa(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    // }
    // $row[] = $nameRow;

    // $row[] = $aRow['visa_vendor'];
    // $row[] = $aRow['visa_type'];
    // $row[] = _d($aRow['visa_app_date']); // Format date
    // $row[] = $aRow['visa_status'];       // Yes/No
    // $row[] = _d($aRow['visa_rec_date']);
    // $row[] = $aRow['payment_mode'];
    // $row[] = _d($aRow['payment_date']);
    // $row[] = $aRow['visa_cost'];
    // $row[] = $aRow['insurance_cost'];
    // $row[] = $aRow['country'];
    // $row[] = $aRow['deposite_mode'];
    // $row[] = $aRow['deposite_amount'];
    // $row[] = _d($aRow['deposite_date']);
    // $row[] = $aRow['remark'];

    // Add delete button if user has permission
    // if ($has_permission_delete) {
    //     $row[] = '<a href="' . admin_url('visa/delete/' . $aRow['id']) . '" class="btn btn-danger btn-sm _delete">
    //                 <i class="fa fa-trash"></i>
    //               </a>';
    // }

    $row[] = $aRow['university_name'];
    $row[] = $aRow['room_capacity'];
    $row[] = $aRow['currency_name'];
    $row[] = $aRow['rent'];
    $action = '';
    if (has_permission('hostel', '', 'edit')) {
        // Convert PHP array to JSON, then encode in base64
        $encodedData = base64_encode(json_encode($aRow));

        // Use it in your onclick function
        $action .= '<a href="javascript:void(0);" onclick="Edit('
            . $aRow['id'] . ', \'' . $encodedData . '\')" class="btn btn-default btn-icon">
                <i class="fa fa-pencil-square-o"></i>
                </a>';
    }
    if (has_permission('hostel', '', 'delete')) {
        $action .= ' <a href="javascript:void(0);"  onclick="Delete(' . $aRow['id'] . ')" c class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>';
    }
    $row[] = $action;
    $output['aaData'][] = $row;
}

// Output JSON
// echo json_encode($output);
