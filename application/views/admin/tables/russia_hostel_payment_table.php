<?php

defined('BASEPATH') or exit('No direct script access allowed');
$university_applicant_fees   = university_applicant_fees_payments();
$university_applicant_fees_  = array_column($university_applicant_fees, null, 'id');

$get_currencies   = get_currencies();
$currency_lookup  = array_column($get_currencies, null, 'id');

$sTable       = db_prefix() . 'hostel_payments';

$_POST['length'] = 100;

// Select columns
$aColumns = [
    $sTable . ".pay_date as pay_date",
    $sTable . ".payment_type as type",
    $sTable . ".mode as mode",
    db_prefix() . "quotation_mode.name as mode_name",
    $sTable . ".vendor_name as vendor_name",
    // db_prefix() . "quotation_vendor.name as v_name",
         "IF(".$sTable.".mode = 3, " . db_prefix() . "_hostel_vendors.name, " . db_prefix() . "quotation_vendor.name) AS v_name",

    $sTable . ".inr_value as inr_value",
    $sTable . ".payment_type as payment_type",
    $sTable . ".university_name as university_name",
    $sTable . ".acadmic_year as acadmic_year",
    $sTable . ".year as year",
    // $sTable . ".start_date as start_date",
    // $sTable . ".end_date as end_date",
    // $sTable . ".room_capacity as room_capacity",
    "IF(" . $sTable . ".status=1,'Approved',IF(" . $sTable . ".status=2,'Rejected','Pending')) as status",
    "IF(" . $sTable . ".status=1,'success',IF(" . $sTable . ".status=2,'danger','warning')) as status_color",
    $sTable . ".fess_infomation as fess_infomation",
    $sTable . ".pdf as pdf",
    $sTable . ".status as status_id",
    $sTable . ".id as id",
    // $sTable . ".amount as amount",
    // "TIMESTAMPDIFF(MONTH, " . db_prefix() . "hostel_payments.start_date," . db_prefix() . "hostel_payments.end_date)
    //    + (DAY(" . db_prefix() . "hostel_payments.end_date) >= DAY(" . db_prefix() . "hostel_payments.start_date)) AS month_difference",
    // db_prefix() . 'currencies.name as currency_name',

];

$sIndexColumn = 'id';

$join = [
    ' LEFT JOIN ' . db_prefix() . 'quotation_mode ON ' . db_prefix() . 'quotation_mode.id = ' . $sTable . '.mode',
    ' LEFT JOIN ' . db_prefix() . 'quotation_vendor ON ' . db_prefix() . 'quotation_vendor.id = ' . $sTable . '.vendor_id',
    ' LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . $sTable . '.ex_currency',
        'LEFT JOIN ' . db_prefix() . '_hostel_vendors ON ' . db_prefix() . '_hostel_vendors.id = ' . $sTable . '.vendor_id',

    // " LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = " . db_prefix() . "payment_quotations.client_id ",

];

$where   = [];
$where[] = "AND " . $sTable . ".hostel_info_id = " . (int) $hostel_info_id;
$where[] = "AND " . $sTable . ".status > 0 ";

if (!empty($_POST['payment_id'])) {
    $where[] = "AND " . $sTable . ".id = " . (int) $_POST['payment_id'];
}

$groupBy = "";

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $groupBy);
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
            $row[] =  $fInfo->fee_amount . " " . $currency_lookup[$fInfo->credit_currency]['name'] ?? '0';
            $row[] = $fInfo->fee_inr_value . " " . $currency_lookup[$fInfo->document_currency]['name'] ?? '0';
            if (!empty($aRow['pdf']) && !empty($fInfo->fee_id) && $fInfo->fee_id ==6) {
            $row[] = '
                &nbsp; 
                <button class="btn btn-xs btn-primary" onclick="window.open(\'' . $aRow['pdf'] . '\', \'_blank\')">
                    <i class="fa fa-eye"></i>
                </button>';
        } else {
            $row[] = '';
        }
        
                if (!empty($fInfo->fee_id) && $fInfo->fee_id ==6) {
            $row[] = '&nbsp; <button class="btn btn-primary" onclick="GeneratePDF(' . $hostel_info_id . ',' . $aRow['id'] . ')">Generate PDF</button>';
            } else {
            $row[] = '';
            }

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
            <button class="btn btn-xs btn-primary" onclick="showSplit(this, ' . $feesInfo . ', ' . (int)$aRow['id'] . ')">
                <i class="fa fa-eye"></i>
            </button>
            &nbsp;' . $aRow['pay_date'];
        $row[] = $aRow['university_name'];
        $row[] = $aRow['acadmic_year'];
        $row[] = $aRow['year'];
        $row[] = $university_applicant_fees_[$aRow['type']]['name'] ?? '';
        $row[] = $aRow['mode_name'];
        $row[] = !empty($aRow['v_name']) ? $aRow['v_name'] : $aRow['vendor_name'];
        // $row[] = $aRow['inr_value'];
        // $row[] = $aRow['start_date'];
        // $row[] = $aRow['end_date'];
        // $row[] = $aRow['room_capacity'];
        // $row[] = $aRow['month_difference'];
        // $row[] = $aRow['amount'];
        // $row[] = $aRow['currency_name'];


        // Status + approval buttons
        $status = '';
        if (
            !empty($aRow['status_id'])
            && $aRow['status_id'] == 3
            && (is_admin() || has_permission('payment_quotation', '', 'payment_approval'))
        ) {
            $status .= '
                <button class="btn-xs btn btn-xs btn-success" onclick="document_approved(this, 1,' . (int)$aRow['id'] . ')">
                    <i class="fa fa-check"></i>
                </button>
                &nbsp;
                <button class="btn-xs btn btn-xs btn-danger" onclick="document_approved(this, 2,' . (int)$aRow['id'] . ')">
                    <i class="fa fa-times"></i>
                </button>
                &nbsp;
               ';
        }

        $status .= " &nbsp; <span class='text-" . $aRow['status_color'] . "'>" . $aRow['status'] . "</span>";
        $row[] = $status;

        // Action buttons

        if (!empty($aRow['pdf'])) {
            $row[] = '
                &nbsp; 
                <button class="btn btn-xs btn-primary" onclick="window.open(\'' . base_url($aRow['pdf']) . '\', \'_blank\')">
                    <i class="fa fa-eye"></i>
                </button>';
        } else {
            $row[] = '';
        }
        $action = '';
        if (is_admin() ||  has_permission('hostel_management', '', 'hostel_payment_delete')) {
            $action .= '
                <a class="btn btn-xs btn-sm btn-primary" href="?tab=payment&payment_id=' . (int)$aRow['id'] . '">
                    <i class="fa fa-pencil"></i>
                </a>';
        }

        if (is_admin() || has_permission('hostel_payment_delete', '', 'delete')) {
            $action .= '
     <button class="btn-xs btn btn-xs btn-danger" onclick="document_approved(this, 0,' . (int)$aRow['id'] . ')">
                    <i class="fa fa-trash"></i>
                </button>';
        }
        $row[] = $action;

        // Push row
        $output['aaData'][] = $row;
    }
}
