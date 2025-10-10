<?php

defined('BASEPATH') or exit('No direct script access allowed');

// $university_applicant_fees = university_applicant_fees(1);

// Select columns
$aColumns = [
    "university_name as university_name",
    "room_capacity as room_capacity",
    "start_date as start_date",
    "end_date as end_date",
    "room_capacity as room_capacity",
    "pdf as pdf",
    "CONCAT('Q', ROW_NUMBER() OVER (PARTITION BY university_name ORDER BY id ASC)) AS quotation_label",
    "CONCAT(university_name, '-', start_date,'-',end_date,'-', '-',room_capacity, ' - ',
               'Q', ROW_NUMBER() OVER (PARTITION BY university_name ORDER BY id ASC)
        ) AS unique_id"
];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'hostel_quotation';
$where = [];
$where[] = "AND release_to_counsellor = 1";
$where[] = "AND hostel_info_id = " . $hostel_info_id;
$groupBy = "";
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [], $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];

// Format rows
foreach ($rResult as $aRow) {
    $row = [];

    // Base columns
    $row[] = $aRow['university_name'];
    $row[] = $aRow['start_date'];
    $row[] = $aRow['end_date'];
    $row[] = $aRow['room_capacity'];
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
