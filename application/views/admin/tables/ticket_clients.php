<?php
defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('client_ticket_delete', '', 'delete');

// Columns for DataTables
$aColumns = [
    db_prefix() . 'external_ticket_data.id as id',
    db_prefix() . 'external_ticket_data.name as name',
    db_prefix() . 'external_ticket_vendor.name as ticket_vendor',
    db_prefix() . 'external_visa_type.name as ticket_type',
    db_prefix() . 'external_ticket_data.flight_date as flight_date',
    'pm.name as payment_mode',
    db_prefix() . 'external_ticket_data.payment_date as payment_date',
    db_prefix() . 'external_ticket_data.ticket_cost as ticket_cost',
    db_prefix() . 'external_ticket_data.country_name as country',
    db_prefix() . 'departure_location.name as departure_name',
    'des.name as destination_name',
    'dm.name as deposite_mode',
    db_prefix() . 'external_ticket_data.deposite_amount as deposite_amount',
    db_prefix() . 'external_ticket_data.deposite_date as deposite_date',
    db_prefix() . 'external_ticket_data.remark as remark',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'external_ticket_data';

// Joins with aliases for payment modes
$join = [
    'LEFT JOIN ' . db_prefix() . 'external_payment_mode AS pm 
        ON pm.id = ' . db_prefix() . 'external_ticket_data.payment_mode',
    'LEFT JOIN ' . db_prefix() . 'external_payment_mode AS dm 
        ON dm.id = ' . db_prefix() . 'external_ticket_data.deposite_mode',
    'LEFT JOIN ' . db_prefix() . 'external_visa_type 
        ON ' . db_prefix() . 'external_visa_type.id = ' . db_prefix() . 'external_ticket_data.ticket_type',
    'LEFT JOIN ' . db_prefix() . 'external_ticket_vendor 
        ON ' . db_prefix() . 'external_ticket_vendor.id = ' . db_prefix() . 'external_ticket_data.ticket_vendor',
    'LEFT JOIN ' . db_prefix() . 'departure_location 
        ON ' . db_prefix() . 'departure_location.id = ' . db_prefix() . 'external_ticket_data.departure_id',
    'LEFT JOIN ' . db_prefix() . 'departure_location  as des
        ON des.id = ' . db_prefix() . 'external_ticket_data.destination_id'
];

// Optional WHERE conditions
$where = [];

$where[] = " AND " . db_prefix() . 'external_ticket_data.status = 1 ';
// Group by ID to prevent duplicates
$group_by = 'GROUP BY ' . db_prefix() . 'external_ticket_data.id';

$search_column = [];
// Define search and group-by clauses
if (!empty($_POST["search"]["value"])) {
    $search_column = [$sTable . ".name"];
}

// Execute DataTables query
$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [],
    // [db_prefix() . 'external_ticket_data.id'], // Select ID explicitly
    $group_by,
    "",
    "",
    $search_column
);

$output  = $result['output'];
$rResult = $result['rResult'];

// Build DataTable rows
foreach ($rResult as $aRow) {
    $row = [];
    $nameRow = $aRow['name'] . "<br>";
    if (has_permission('external_ticket', '', 'create')) {
        $nameRow .= '<a href="' . admin_url('clients/external_ticket/' . $aRow['id'] . '?edit=true') . '" >' . _l('edit') . '</a>';
    }

    if ($has_permission_delete) {
        $nameRow .= ' | <a href="javascript:void(0)" onclick="deleteTicket(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    }
    $row[] = $nameRow;

    $row[] = $aRow['ticket_vendor'];
    $row[] = $aRow['ticket_type'];
    $row[] = _d($aRow['flight_date']); // Format date
    // $row[] = $aRow['ticket_status'];       // Yes/No
    $row[] = $aRow['payment_mode'];
    $row[] = _d($aRow['payment_date']);
    $row[] = $aRow['ticket_cost'];
    $row[] = $aRow['country'];
    $row[] = $aRow['departure_name'];
    $row[] = $aRow['destination_name'];
    $row[] = $aRow['deposite_mode'];
    $row[] = $aRow['deposite_amount'];
    $row[] = _d($aRow['deposite_date']);
    $row[] = $aRow['remark'];

    // Add delete button if user has permission
    // if ($has_permission_delete) {
    //     $row[] = '<a href="' . admin_url('ticket/delete/' . $aRow['id']) . '" class="btn btn-danger btn-sm _delete">
    //                 <i class="fa fa-trash"></i>
    //               </a>';
    // }

    $output['aaData'][] = $row;
}

// Output JSON
// echo json_encode($output);
