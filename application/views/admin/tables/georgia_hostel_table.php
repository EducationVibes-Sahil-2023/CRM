<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('hostel_management', '', 'delete');

// Columns for DataTables
$aColumns = [
    db_prefix() . 'hostel_infomation.name as name',
    db_prefix() . 'hostel_infomation.passport as passport',
    db_prefix() . 'hostel_infomation.university_name as university_name',
    'latest_quotation.room_no as room_no',
    'latest_quotation.floor_no as floor_no',
    'latest_quotation.company as company',
    'latest_quotation.hostel as hostel',
    'latest_quotation.room_capacity as room_capacity',
    'latest_quotation.rent as rent_amount',
    'latest_quotation.currency as currency',
    'latest_quotation.start_date as start_date',
    'latest_quotation.end_date as end_date',
    db_prefix() . 'hostel_infomation.id as id',
    db_prefix() . 'hostel.name as hostel_name',
    db_prefix() . 'hostel_company.name as company_name',
    db_prefix() . 'currencies.name as currency_name',
    //find month difference between two dates as month_difference
    "TIMESTAMPDIFF(MONTH, latest_quotation.start_date,latest_quotation.end_date)
       + (DAY(latest_quotation.end_date) >= DAY(latest_quotation.start_date)) AS month_difference",
    "CAST(
        JSON_UNQUOTE(
            JSON_EXTRACT(
                hostel_due,
                CONCAT(
                    '$.main.fees_info[',
                    REGEXP_SUBSTR(
                        JSON_UNQUOTE(
                            JSON_SEARCH(hostel_due, 'one', '5', NULL, '$.main.fees_info[*].id')
                        ),
                        '[0-9]+'
                    ),
                    '].amount'
                )
            )
        ) AS DECIMAL(10,2)
    ) AS hostel_amount",

    // ✅ Extract hostel currency_id directly from JSON
    "CAST(
        JSON_UNQUOTE(
            JSON_EXTRACT(
                hostel_due,
                CONCAT(
                    '$.main.fees_info[',
                    REGEXP_SUBSTR(
                        JSON_UNQUOTE(
                            JSON_SEARCH(hostel_due, 'one', '5', NULL, '$.main.fees_info[*].id')
                        ),
                        '[0-9]+'
                    ),
                    '].currency_id'
                )
            )
        ) AS UNSIGNED
    ) AS hostel_currency_id",
    db_prefix() . 'currencies.name as currency_name',



];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'hostel_infomation';
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
//         ON ' . db_prefix() . 'currencies.id = latest_quotation.currency'
// ];

$join = [
    'LEFT JOIN ' . db_prefix() . 'hostel_quotation AS latest_quotation 
        ON latest_quotation.hostel_info_id = ' . db_prefix() . 'hostel_infomation.id ',

    'LEFT JOIN ' . db_prefix() . 'hostel 
        ON ' . db_prefix() . 'hostel.id = ' . db_prefix() . 'hostel_infomation.hostel',

    'LEFT JOIN ' . db_prefix() . 'hostel_company 
        ON ' . db_prefix() . 'hostel_company.id = ' . db_prefix() . 'hostel_infomation.company',

    'LEFT JOIN ' . db_prefix() . 'currencies 
        ON ' . db_prefix() . 'currencies.id = latest_quotation.currency'
];

// Optional WHERE conditions
// $where = [];

$where[] = " AND " . db_prefix() . "hostel_infomation.status = 1 ";
$where[] = " AND latest_quotation.status = 1 ";
$where[] = " AND " . db_prefix() . 'hostel_infomation.acadmic_year = "" ';

// $where[] = " AND latest_quotation.status > 0 ";
if (is_admin() || has_permission('hostel_management', '', 'view')) {
} else {
    $where[] = " AND " . db_prefix() . "hostel_infomation.created_by = " . get_staff_user_id();
}
// Group by ID to prevent duplicates
$group_by = 'GROUP BY latest_quotation.id';

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
    $nameRow = "";
    $nameRow .= '<a target="_blank" href="' . admin_url('hostel_management/hostel/georgia/' . $aRow['id'] . '?tab=profile') . '" >' . $aRow['name'] . '</a>';
    // if (has_permission('hostel_management', '', 'edit')) {
    // }
    $nameRow .=   '<div class="row-options">';

    if ($has_permission_delete) {
        $nameRow .= ' <a href="javascript:void(0)" onclick="Delete(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
    }

    $nameRow .=  '</div>';
    $row[] = $nameRow;
    $row[] = $aRow['passport'];
    $row[] = $aRow['university_name'];
    $row[] = $aRow['room_no']; // Format date
    $row[] = $aRow['floor_no'];       // Yes/No
    $row[] = $aRow['company_name'];
    $row[] = $aRow['hostel_name'];
    $row[] = $aRow['room_capacity'];
    $row[] = $aRow['hostel_amount'];
    $row[] = $aRow['currency_name'];
    $row[] = $aRow['start_date'];
    $row[] = $aRow['end_date'];
    $row[] = $aRow['month_difference'];
    $monthDiff = isset($aRow['month_difference']) && is_numeric($aRow['month_difference'])
        ? (float)$aRow['month_difference']
        : 0;

    $rentAmount = isset($aRow['hostel_amount']) && is_numeric($aRow['hostel_amount'])
        ? (float)$aRow['hostel_amount']
        : 0;

    $row[] = $monthDiff * $rentAmount;


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
