<?php

defined('BASEPATH') or exit('No direct script access allowed');

$university_applicant_fees = university_applicant_fees(1);

// Select columns
$aColumns = [
   "p.primary_university as university_name",
    db_prefix() . "university_quotation.acadmic_year as acadmic_year",
    db_prefix() . "university_quotation.year as year",
];

// Dynamic applicant fees columns
foreach ($university_applicant_fees as $fee) {
  $aColumns[] = "
    CONCAT(
        FORMAT(IFNULL(MAX(CASE WHEN aqfd.fees_id = " . $fee['id'] . " THEN aqfd.amount ELSE 0 END), 0), 2),
        ' ',
        COALESCE(MAX(CASE WHEN aqfd.fees_id = " . $fee['id'] . " THEN c.name END), '')
    ) AS `" . $fee['quotation_name'] . "`
";

}

$aColumns[] = db_prefix() . "university_quotation.id as id";
$aColumns[] = "c.name as currency_name";

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'university_quotation';

// Joins
$join[] = 'LEFT JOIN ' . db_prefix() . 'applicant_fees af 
            ON af.university_quotation = ' . db_prefix() . 'university_quotation.id';

$join[] = 'LEFT JOIN ' . db_prefix() . 'applicant_quotation_fees_details aqfd 
            ON aqfd.university_name = ' . db_prefix() . 'university_quotation.university_name 
            AND aqfd.acadmic_year = ' . db_prefix() . 'university_quotation.acadmic_year 
            AND aqfd.year = ' . db_prefix() . 'university_quotation.year';

$join[] = 'LEFT JOIN ' . db_prefix() . 'currencies c 
            ON c.id = aqfd.currency_id';

 $join[] = " LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = " . db_prefix() . "university_quotation.client_id ";
 
$groupBy = "GROUP BY " . db_prefix() . "university_quotation.university_name, " . db_prefix() . "university_quotation.acadmic_year, " . db_prefix() . "university_quotation.year," . db_prefix() . "university_quotation.id";
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [], $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];

// Format rows
foreach ($rResult as $aRow) {
    $row = [];

    // Base columns
    $row[] = $aRow['university_name'];
    $row[] = $aRow['acadmic_year'];
    $row[] = $aRow['year'];

    // Dynamic applicant fees
    foreach ($university_applicant_fees as $fee) {
        $row[] = isset($aRow[$fee['quotation_name']]) ? $aRow[$fee['quotation_name']] : '0';
    }


    // Action buttons
    $attributes = [];
    $options = icon_btn('quotations/create_mbbs_abroad/' . $aRow['id'], 'pencil-square-o', 'btn-default', $attributes);
    $row[]   = $options;


    // Push into DataTables output
    $output['aaData'][] = $row;
}
