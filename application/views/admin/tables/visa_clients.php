<?php
defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('client_visa_delete', '', 'delete');

// Columns for DataTables
$aColumns = [
    db_prefix() . 'external_visa_data.name as name',
     db_prefix() . 'external_visa_data.id as id',
    db_prefix() . 'external_visa_vendor.name as visa_vendor',
    db_prefix() . 'external_visa_type.name as visa_type',
    db_prefix() . 'external_visa_data.visa_app_date as visa_app_date',
    db_prefix() . 'external_visa_status.name as visa_status',
    db_prefix() . 'external_visa_data.visa_rec_date as visa_rec_date',
    'pm.name as payment_mode',
    db_prefix() . 'external_visa_data.payment_date as payment_date',
    db_prefix() . 'external_visa_data.visa_cost as visa_cost',
    db_prefix() . 'external_visa_data.insurance_cost as insurance_cost',
    db_prefix() . 'external_visa_data.country_name as country',
    'dm.name as deposite_mode',
    db_prefix() . 'external_visa_data.deposite_amount as deposite_amount',
    db_prefix() . 'external_visa_data.deposite_date as deposite_date',
    db_prefix() . 'external_visa_data.remark as remark',
];


$sTable       = db_prefix() . 'external_visa_data';
$sIndexColumn = 'id';

// Joins with aliases for payment modes
$join = [
    'LEFT JOIN ' . db_prefix() . 'external_payment_mode AS pm 
        ON pm.id = ' . db_prefix() . 'external_visa_data.payment_mode',
    'LEFT JOIN ' . db_prefix() . 'external_payment_mode AS dm 
        ON dm.id = ' . db_prefix() . 'external_visa_data.deposite_mode',
    'LEFT JOIN ' . db_prefix() . 'external_visa_type 
        ON ' . db_prefix() . 'external_visa_type.id = ' . db_prefix() . 'external_visa_data.visa_type',
    'LEFT JOIN ' . db_prefix() . 'external_visa_vendor 
        ON ' . db_prefix() . 'external_visa_vendor.id = ' . db_prefix() . 'external_visa_data.visa_vendor',
    'LEFT JOIN ' . db_prefix() . 'external_visa_status 
        ON ' . db_prefix() . 'external_visa_status.id = ' . db_prefix() . 'external_visa_data.visa_status',
];

// Optional WHERE conditions
$where = [];

if (!empty($this->ci->input->post('visa_type'))) {
            $where[]  = " AND {$sTable}.visa_type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('visa_type'))) . ")";

}

if (!empty($this->ci->input->post('visa_vendor'))) {
            $where[]  = " AND {$sTable}.visa_vendor IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('visa_vendor'))) . ")";

}

if (!empty($this->ci->input->post('visa_status'))) {
            $where[]  = " AND {$sTable}.visa_status IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('visa_status'))) . ")";

}

$where[] = " AND " . db_prefix() . 'external_visa_data.status = 1 ';
// Group by ID to prevent duplicates
$group_by = 'GROUP BY ' . db_prefix() . 'external_visa_data.id';

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
    // [db_prefix() . 'external_visa_data.id'], // Select ID explicitly
    $group_by,
    "","",$search_column
);

$output  = $result['output'];
$rResult = $result['rResult'];

// Build DataTable rows
foreach ($rResult as $aRow) {
    $row = [];
    $nameRow = $aRow['name'] . "<br>";
    if (has_permission('external_ticket', '', 'create')) {
        $nameRow .= '<a href="' . admin_url('clients/external_visa/' . $aRow['id'] . '?edit=true') . '" >' . _l('edit') . '</a>';
    }

    if ($has_permission_delete) {
        $nameRow .= ' | <a href="javascript:void(0)" onclick="deleteVisa(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    }
    $row[] = $nameRow;

    $row[] = $aRow['visa_vendor'];
    $row[] = $aRow['visa_type'];
    $row[] = _d($aRow['visa_app_date']); // Format date
    $row[] = $aRow['visa_status'];       // Yes/No
    $row[] = _d($aRow['visa_rec_date']);
    $row[] = $aRow['payment_mode'];
    $row[] = _d($aRow['payment_date']);
    $row[] = $aRow['visa_cost'];
    $row[] = $aRow['insurance_cost'];
    $row[] = $aRow['country'];
    $row[] = $aRow['deposite_mode'];
    $row[] = $aRow['deposite_amount'];
    $row[] = _d($aRow['deposite_date']);
    $row[] = $aRow['remark'];

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
