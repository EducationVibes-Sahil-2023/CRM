<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    db_prefix() . 'lead_transfer_request.id as lead_transfer_id',
    db_prefix() . 'leads_type.name as lead_name',
    "concat(" . db_prefix() . "staff.firstname, ' ', " . db_prefix() . "staff.lastname) as staff_name",
    "(
        SELECT CONCAT(approved_staff.firstname, ' ', approved_staff.lastname) 
        FROM " . db_prefix() . "staff AS approved_staff 
        WHERE approved_staff.staffid = " . db_prefix() . "lead_transfer_request.approved_by
    ) AS approval_name",
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
    END AS status_color",
    db_prefix() . 'lead_transfer_request.created_at as created_date',
    db_prefix() . 'lead_transfer_request.approved_date as approved_date',
    db_prefix() . 'lead_transfer_request.reason as reason',
    db_prefix() . 'lead_transfer_request.approval_text as approval_text',
    db_prefix() . 'lead_transfer_request.assign as assign',
    db_prefix() . 'lead_transfer_request.lead_type as lead_type',
    db_prefix() . 'lead_transfer_request.leadid as leadid'



];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lead_transfer_request';

$where  = [];
$filter = [];

$join          = [];
array_push($join, 'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . db_prefix() . 'lead_transfer_request.lead_type');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'lead_transfer_request.assign');

if (is_admin()) {
} else {
    array_push($where, db_prefix() . "lead_transfer_request.created_by = " . get_staff_user_id());
}



// $aColumns = hooks()->apply_filters('proposals_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
// if (count($custom_fields) > 4) {
//     @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
// }

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];


    $row[] = !empty($aRow['lead_name']) ? $aRow['lead_name'] : '';
    $row[] = !empty($aRow['staff_name']) ? $aRow['staff_name'] : '';
    $row[] = !empty($aRow['created_date']) ? _d($aRow['created_date']) : '';
    $row[] = !empty($aRow['reason']) ? $aRow['reason'] : '';
    $row[] = !empty($aRow['status_name']) ? '<span class="text-' . $aRow['status_color'] . '">' . $aRow['status_name'] . '</span>' : 'Not defined';
    $row[] = !empty($aRow['approved_date']) ? _d($aRow['approved_date']) : '';
    $row[] = !empty($aRow['approval_name']) ? $aRow['approval_name'] : '';
    if (is_admin()) {
        if ($aRow['lead_transfer_id'] == 3) {
            $row[] = '<button class="btn btn-success" onclick="update_lead_transfer(' . $aRow['leadid'] . ',' . $aRow['lead_type'] . ',' . $aRow['lead_transfer_id'] . ',' . $aRow['assign'] . ',1)" >Approved</button></br></br><button class="btn btn-danger" onclick="update_lead_transfer(' . $aRow['leadid'] . ',' . $aRow['lead_type'] . ',' . $aRow['lead_transfer_id'] . ',"",2)" >Reject</button>';
        } else {
            $row[] = "";
        }
    }
    // Continue with further processing of $row



    $row['DT_RowClass'] = 'has-row-options';

    // $row = hooks()->apply_filters('proposals_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
