<?php

defined('BASEPATH') or exit('No direct script access allowed');
$lead_data = array_column(get_type(), null, 'id');
$lead_source = array_column(get_source(), null, 'id');
$staff_data = array_column(get_all_staff(), null, 'staffid');

$aColumns = [
    db_prefix() . 'lead_transfer_request.id as lead_transfer_id',
    db_prefix() . 'lead_transfer_request.created_at as created_date',
    db_prefix() . 'lead_transfer_request.approved_date as approved_date',
    db_prefix() . 'lead_transfer_request.reason as reason',
    db_prefix() . 'lead_transfer_request.approval_text as approval_text',
    db_prefix() . 'lead_transfer_request.assign as assign',
    db_prefix() . 'lead_transfer_request.lead_type as lead_type',
    db_prefix() . 'lead_transfer_request.lead_source as lead_source',
    db_prefix() . 'lead_transfer_request.leadid as leadid',
    db_prefix() . 'lead_transfer_request.status as status',
    db_prefix() . 'lead_transfer_request.created_by as created_by',
    db_prefix() . 'lead_transfer_request.approved_by as approved_by',
    db_prefix() . 'lead_transfer_request.assign as assign',
    db_prefix() . 'leads.phonenumber as phonenumber',
    db_prefix() . 'leads.type as old_lead_type',
    db_prefix() . 'leads.source as old_lead_source',
    db_prefix() . 'lead_transfer_request.automatic as automatic',
    "CASE 
        WHEN " . db_prefix() . "lead_transfer_request.status = 1 THEN 'Approved'
        WHEN " . db_prefix() . "lead_transfer_request.status = 2 THEN 'Rejected'
        WHEN " . db_prefix() . "lead_transfer_request.status = 3 THEN 'Pending'
        ELSE 'Not defined'
    END AS status_name",
    "CASE 
        WHEN " . db_prefix() . "lead_transfer_request.status = 1 THEN 'success'
        WHEN " . db_prefix() . "lead_transfer_request.status = 2 THEN 'danger'
        WHEN " . db_prefix() . "lead_transfer_request.status = 3 THEN 'warning'
        ELSE ''
    END AS status_color"
];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lead_transfer_request';

$where  = [];
$filter = [];

$join          = [];
array_push($join, 'JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'lead_transfer_request.leadid');

if (!empty($params["rel_id"]) && $params["rel_id"] != "") {
    array_push($where, " AND " . db_prefix() . "lead_transfer_request.leadid = " . $params["rel_id"]);
}


if (!empty($params["action"]) && $params["action"] == 1) {
    array_push($where, " AND " . db_prefix() . "lead_transfer_request.status = 3 ");
}

if (is_admin()) {
} else {

    array_push($where, " AND " . db_prefix() . "lead_transfer_request.created_by = " . get_staff_user_id());
}


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];
if (!empty($params["type"]) && $params["type"] == "counsellor") {
    foreach ($rResult as $aRow) {
        $row = [];
        $reason = !empty($aRow['reason']) ? addslashes($aRow['reason']) : '';
        $lead_transfer_id = !empty($aRow['lead_transfer_id']) ? $aRow['lead_transfer_id'] : '';
        $assign = !empty($aRow['assign']) ? $aRow['assign'] : '';
        $lead_type = !empty($aRow['lead_type']) ? $aRow['lead_type'] : '';
        $leadid = !empty($aRow['leadid']) ? $aRow['leadid'] : '';
        $edit_btn = "<i class='fa fa-edit btn btn-default' onclick=' edit_lead_request(" . $leadid . ")'></i>";
        $row[] = $lead_data[$aRow["lead_type"]]["name"];
        $row[] = $lead_source[$aRow["lead_source"]]["name"];
        $row[] = !empty($staff_data[$aRow["assign"]]["full_name"]) ? $staff_data[$aRow["assign"]]["full_name"] : "";
        $row[] = !empty($aRow['phonenumber']) ? ($aRow['phonenumber']) : '';
        $row[] = !empty($aRow['reason']) ? ($aRow['reason']) : '';
        $row[] = !empty($aRow['status_name']) ? '<span class="text-' . $aRow['status_color'] . '">' . $aRow['status_name'] . '</span>' : 'Not defined';
        $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : '';
        $row[] = !empty($aRow['created_date']) ? _d($aRow['created_date']) : '';
        if (is_admin() || has_permission('leads', '', 'approval')) {
            if ($aRow['status'] == '3') {

                $edit_btn = "<i class='fa fa-edit btn btn-default' onclick='edit_lead_request(" . $aRow['leadid'] . ")'></i>";

                $row[] = $edit_btn . '&nbsp;<button class="btn btn-success" onclick="update_lead_transfer(' . $aRow['leadid'] . ', ' . $aRow['lead_type'] . ', ' . $aRow['lead_transfer_id'] . ', ' . $aRow['assign'] . ', 1)">Approved</button>';
            } else {
                $row[] = "";
            }
        } else {
            if ($aRow['status'] == '3' && $aRow['automatic'] == 0) {
                $row[] = "";
            } else {
                $row[] = "";
            }
        }


        $row['DT_RowClass'] = 'has-row-options';

        $output['aaData'][] = $row;
    }
} else if (!empty($params["type"]) && $params["type"] == "admin") {

    foreach ($rResult as $aRow) {
        $row = [];
        $reason = !empty($aRow['reason']) ? addslashes($aRow['reason']) : '';
        $lead_transfer_id = !empty($aRow['lead_transfer_id']) ? $aRow['lead_transfer_id'] : '';
        $assign = !empty($aRow['assign']) ? $aRow['assign'] : '';
        $lead_type = !empty($aRow['lead_type']) ? $aRow['lead_type'] : '';
        $leadid = !empty($aRow['leadid']) ? $aRow['leadid'] : '';
        $edit_btn = "<i class='fa fa-edit btn btn-default' onclick=' edit_lead_request(" . $leadid . ")'></i>";
        $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : '';
        $row[] = !empty($lead_data[$aRow["old_lead_type"]]["name"]) ? $lead_data[$aRow["old_lead_type"]]["name"] : '';
        $row[] = !empty($lead_source[$aRow["old_lead_source"]]["name"]) ? $lead_source[$aRow["old_lead_source"]]["name"] : '';
        $row[] = !empty($staff_data[$aRow["assign"]]["full_name"]) ? $staff_data[$aRow["assign"]]["full_name"] : "";
        $row[] = !empty($aRow['phonenumber']) ? ($aRow['phonenumber']) : '';
        $row[] = $lead_data[$aRow["lead_type"]]["name"];
        $row[] = $lead_source[$aRow["lead_source"]]["name"];
        $row[] = !empty($aRow['reason']) ? ($aRow['reason']) : '';
        $row[] = !empty($aRow['status_name']) ? '<span class="text-' . $aRow['status_color'] . '">' . $aRow['status_name'] . '</span>' : 'Not defined';
        $row[] = !empty($aRow['created_date']) ? _d($aRow['created_date']) : '';
        if (is_admin() || has_permission('leads', '', 'approval')) {
            if ($aRow['status'] == '3') {

                $edit_btn = "<i class='fa fa-edit btn btn-default' onclick='init_lead(" . $aRow['leadid'] . ", true,`#show_transfer_lead_div`)'></i>";

                $row[] = $edit_btn . '&nbsp;<button class="btn btn-success" onclick="update_lead_transfer(' . $aRow['leadid'] . ', ' . $aRow['lead_type'] . ', ' . $aRow['lead_transfer_id'] . ', ' . $aRow['assign'] . ', 1, ' . $aRow['lead_source'] . ')">Approved</button>';
            } else {
                $row[] = "";
            }
        } else {
            if ($aRow['status'] == '3' && $aRow['automatic'] != 1) {
                $row[] = $edit_btn;
            } else {
                $row[] = "";
            }
        }

        $row['DT_RowClass'] = 'has-row-options';
        $output['aaData'][] = $row;
    }
}
