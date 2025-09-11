<?php

defined('BASEPATH') or exit('No direct script access allowed');

// $university_applicant_fees = university_applicant_fees(1);

// Select columns
$aColumns = [
    "university_name as university_name",
    "acadmic_year as acadmic_year",
    "year as year",
    "pdf as pdf",
    "CONCAT('Q', ROW_NUMBER() OVER (PARTITION BY university_name ORDER BY id ASC)) AS quotation_label",
    "CONCAT(university_name, '-', acadmic_year, '-', year,' Year', ' - ',
               'Q', ROW_NUMBER() OVER (PARTITION BY university_name ORDER BY id ASC)
        ) AS unique_id"
];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'applicant_quotation_payment';
$where = [];
$where[] = "AND release_to_counsellor = 1";
$where[] = "AND client_id = " . $client_id;
$groupBy = "";
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [], $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];

// Format rows
foreach ($rResult as $aRow) {
    $row = [];

    // Base columns
    $row[] = $aRow['university_name'];
    $row[] = $aRow['acadmic_year'];
    $row[] = $aRow['year'];
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
