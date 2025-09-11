<?php

defined('BASEPATH') or exit('No direct script access allowed');
$university_applicant_fees = university_applicant_fees("", 1, [
    "university_name" => $university_name,
    "acadmic_year"    => $acadmic_year
]);
$university_applicant_fees_ = array_column($university_applicant_fees, NULL, 'id');

$get_currencies = get_currencies();
$currency_lookup = array_column($get_currencies, NULL, 'id');
// $university_applicant_fees = university_applicant_fees(1);

// Select columns
$aColumns = [
    "university_name as university_name",
    "academic_year as academic_year",
    "year as year",
    "if(status=1,'Aproved',if(status=2,'rejected','Pending')) as status",
    "if(status=1,'success',if(status=2,'danger','warning')) as status_color",
    "inr_value as inr_value",
    "fess_infomation as fess_infomation",
    "pdf as pdf",
    "status as status_id",
    "id as id",
];

// print_r($payment_id);
// die;

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'payment_quotations';
$where = [];
$where[] = "AND client_id = " . $client_id;
if ($_POST['payment_id']) {
    $where[] = "AND id = " . $_POST['payment_id'];
}
$groupBy = "";
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [], $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];

if (!empty($_POST['payment_id'])) {
    // Child table rendering (fees breakdown)
    foreach ($rResult as $aRow) {
        $fess_infomation = !empty($aRow['fess_infomation'])
            ? json_decode($aRow['fess_infomation'])
            : [];

        if (empty($fess_infomation)) {
            continue; // skip empty fee rows
        }

        foreach ($fess_infomation as $fInfo) {
            $row   = [];
            $row[] = $university_applicant_fees_[$fInfo->fee_id]['name'] ?? '-';
            $row[] = $fInfo->fee_amount ?? '0';
            $row[] = $currency_lookup[$fInfo->fee_currency]['name'] ?? '-';
            $row[] = $fInfo->fee_inr_value ?? '0';
            $output['aaData'][] = $row;
        }
    }
} else {
    // Main table rendering
    foreach ($rResult as $aRow) {
        $row = [];

        // Safely encode JSON for onclick
        $feesInfo = htmlspecialchars(
            json_encode($aRow['fess_infomation'], JSON_UNESCAPED_UNICODE),
            ENT_QUOTES,
            'UTF-8'
        );

        $row[] = '
            <button class="btn btn-primary" onclick="showSplit(this, ' . $feesInfo . ', ' . (int)$aRow['id'] . ')">
                <i class="fa fa-eye"></i>
            </button>
            &nbsp;' . $aRow['university_name'];

        $row[] = $aRow['academic_year'];
        $row[] = $aRow['year'];

        // Status + approval buttons
        $status = '';
        if (
            !empty($aRow['status_id'])
            && $aRow['status_id'] == 3
            && (is_admin() || has_permission('payment_quotation', '', 'payment_approval'))
        ) {
            $status .= '
                <button class="btn-xs btn btn-success" onclick="document_approved(this, 1,' . (int)$aRow['id'] . ')">
                    <i class="fa fa-check"></i>
                </button>
                &nbsp;
                <button class="btn-xs btn btn-danger" onclick="document_approved(this, 2,' . (int)$aRow['id'] . ')">
                    <i class="fa fa-times"></i>
                </button>
                &nbsp;
                <button class="btn-xs btn btn-danger" onclick="document_approved(this, 0,' . (int)$aRow['id'] . ')">
                    <i class="fa fa-trash"></i>
                </button>';
        }

        $status .= " &nbsp; <span class='text-" . $aRow['status_color'] . "'>" . $aRow['status'] . "</span>";
        $row[] = $status;

        $row[] = $aRow['inr_value'];

        // Action buttons
        $action = '';
        if (is_admin() || has_permission('payment_quotation', '', 'edit')) {
            $action .= '
                <a class="btn btn-sm btn-primary" href="?group=payment&payment_id=' . (int)$aRow['id'] . '">
                    <i class="fa fa-pencil"></i>
                </a>';
        }
        if (!empty($aRow['pdf'])) {
            $action .= '
                &nbsp; 
                <button class="btn btn-primary" onclick="window.open(\'' . base_url($aRow['pdf']) . '\', \'_blank\')">
                    <i class="fa fa-eye"></i>
                </button>';
        }

        $row[] = $action;

        // Push row
        $output['aaData'][] = $row;
    }
}
