<?php

defined('BASEPATH') or exit('No direct script access allowed');
$has_permission_delete = has_permission('hostel_quotation_delete', '', 'delete');
// $university_applicant_fees = university_applicant_fees(1);
$aColumns = [

    "CONCAT('Q', ROW_NUMBER() OVER (PARTITION BY university_name ORDER BY " . db_prefix() . "hostel_quotation.id ASC)) AS quotation_label",
    "university_name AS university_name",
    "room_capacity AS room_capacity",
    "start_date AS start_date",
    "end_date AS end_date",
    "pdf AS pdf",


    // ✅ Unique ID for each quotation
    "CONCAT(
        university_name, '-', start_date, '-', end_date, '-', room_capacity, ' - ',
        'Q', ROW_NUMBER() OVER (PARTITION BY university_name ORDER BY id ASC)
    ) AS unique_id",

    // ✅ Month difference
    // "TIMESTAMPDIFF(MONTH, " . db_prefix() . "hostel_quotation.start_date," . db_prefix() . "hostel_quotation.end_date)
    //   + (DAY(" . db_prefix() . "hostel_quotation.end_date) >= DAY(" . db_prefix() . "hostel_quotation.start_date)) AS month_difference",
       "GREATEST(
    1,
    TIMESTAMPDIFF(
        MONTH, 
        " . db_prefix() . "hostel_quotation.start_date,
        " . db_prefix() . "hostel_quotation.end_date
    ) 
    + (
        DAY(" . db_prefix() . "hostel_quotation.end_date) 
        >= 
        DAY(" . db_prefix() . "hostel_quotation.start_date)
    )
) AS month_difference",

    // ✅ Extract hostel amount directly from JSON (id = 5)
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
                    '].inr_value'
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
$_POST['length'] = 100;
$sIndexColumn =  "id";
$sTable       = db_prefix() . 'hostel_quotation';

// ✅ Where condition
$where = [];
$where[] = "AND hostel_info_id = " . (int) $hostel_info_id;
$where[] = "AND status = 1";

// ✅ Join currency table safely
$join = [
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = CAST(
        JSON_UNQUOTE(
            JSON_EXTRACT(
                ' . db_prefix() . 'hostel_quotation.hostel_due,
                CONCAT(
                    "$.main.fees_info[",
                    REGEXP_SUBSTR(
                        JSON_UNQUOTE(
                            JSON_SEARCH(' . db_prefix() . 'hostel_quotation.hostel_due, "one", "5", NULL, "$.main.fees_info[*].id")
                        ),
                        "[0-9]+"
                    ),
                    "].document_currency"
                )
            )
        ) AS UNSIGNED
    )'
];

$groupBy = "";

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'hostel_quotation.id'], $groupBy, [], 1);
$output  = $result['output'];
$rResult = $result['rResult'];

// print_r($rResult);
// Format rows
foreach ($rResult as $aRow) {
    $row = [];

    $nameRow  = '<a href="'
        . admin_url('hostel_management/hostel/georgia/' . $hostel_info_id)
        . '?tab=quotation&quotation_id=' . $aRow['id']
        . '">'
        . htmlspecialchars($aRow['quotation_label'])
        . '</a>';

    // Add action options
    $nameRow .= '<div class="row-options">';

    if ($has_permission_delete) {
        $nameRow .= ' <a href="javascript:void(0)" onclick="DeleteQuotation(' . $hostel_info_id . ','
            . (int)$aRow['id']
            . ')" class="text-danger">'
            . _l('delete')
            . '</a>';
    }

    $nameRow .= '</div>';

    // Push into row
    $row[] = $nameRow;

    $row[] = $aRow['start_date'];
    $row[] = $aRow['end_date'];
    $row[] = $aRow['month_difference'];
    $row[] = $aRow['room_capacity'];
    $row[] = $aRow['hostel_amount'] . " " . $aRow['currency_name'] ?? 0 . " " . $aRow['currency_name'];
    $row[] = (!empty((int)$aRow['hostel_amount']) ? $aRow['hostel_amount'] : 0) * $aRow['month_difference'] . " " . $aRow['currency_name'];

    if (!empty($aRow['pdf'])) {
        $row[] = '<button class="btn btn-primary"
                    onclick="window.open(\'' . $aRow['pdf'] . '\', \'_blank\')">
                    <i class="fa fa-eye"></i>
                </button>';
    } else {
        $row[] = "";
    }



    // Push into DataTables output
    $output['aaData'][] = $row;
}
