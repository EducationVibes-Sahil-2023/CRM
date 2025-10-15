<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('hostel', '', 'delete');

// Columns for DataTables
$aColumns = [
    "*"

];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'hostel';
$join = [];
// Joins with aliases for payment modes
// $join = [
//     'LEFT JOIN (
//         SELECT hq.*
//         FROM ' . db_prefix() . 'hostel_quotation hq
//         INNER JOIN (
//             SELECT hostel_info_id, MAX(id) AS max_id
//             FROM ' . db_prefix() . 'hostel_quotation
//             WHERE status = 1
//             GROUP BY hostel_info_id
//         ) latest_hq
//         ON hq.id = latest_hq.max_id
//     ) AS latest_quotation
//     ON latest_quotation.hostel_info_id = ' . db_prefix() . 'hostel_infomation.id',

//     'LEFT JOIN ' . db_prefix() . 'hostel
//         ON ' . db_prefix() . 'hostel.id = ' . db_prefix() . 'hostel_infomation.hostel',

//     'LEFT JOIN ' . db_prefix() . 'hostel_company
//         ON ' . db_prefix() . 'hostel_company.id = ' . db_prefix() . 'hostel_infomation.company',

//     'LEFT JOIN ' . db_prefix() . 'currencies 
//         ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'hostel_infomation.currency'
// ];


// Optional WHERE conditions
$where = [];

$where[] = " AND " . db_prefix() . "hostel.status = 1 ";
if (is_admin()) {
} else {
    $where[] = " AND " . db_prefix() . "hostel.created_by = " . get_staff_user_id();
}
// Group by ID to prevent duplicates
$group_by = 'GROUP BY ' . db_prefix() . 'hostel.id';

// Execute DataTables query
$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [db_prefix() . 'hostel.id'], // Select ID explicitly
    $group_by,
    [],
    1
);

$output  = $result['output'];
$rResult = $result['rResult'];

// Build DataTable rows
foreach ($rResult as $aRow) {
    $row = [];
    $nameRow = $aRow['hostel_name'] . "<br>";
    if (has_permission('hostel', '', 'edit')) {
        $encodedData = base64_encode(json_encode($aRow));
        $nameRow .= '<a  href="javascript:void(0);" onclick="Edit('
            . $aRow['id'] . ', \'' . $encodedData . '\')"   >' . _l('edit') . '</a>';
    }

    if ($has_permission_delete) {
        $nameRow .= ' | <a href="javascript:void(0)" onclick="Delete(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    }
    $row[] = $nameRow;
    $row[] = $aRow['name'];
    $row[] = $aRow['contact_number'];
    $row[] = $aRow['email']; // Format date

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
