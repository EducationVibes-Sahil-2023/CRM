<?php

defined('BASEPATH') or exit('No direct script access allowed');
$lead_data = array_column(get_type(), null, 'id');
$staff_data = array_column(get_all_staff(), null, 'staffid');
$aColumns = [
    db_prefix() . 'lead_transfer_request.id as lead_transfer_id',
    db_prefix() . 'lead_transfer_request.created_at as created_date',
    db_prefix() . 'lead_transfer_request.approved_date as approved_date',
    db_prefix() . 'lead_transfer_request.reason as reason',
    db_prefix() . 'lead_transfer_request.approval_text as approval_text',
    db_prefix() . 'lead_transfer_request.assign as assign',
    db_prefix() . 'lead_transfer_request.lead_type as lead_type',
    db_prefix() . 'lead_transfer_request.leadid as leadid',
    db_prefix() . 'lead_transfer_request.status as status',
    db_prefix() . 'lead_transfer_request.created_by as created_by',
    db_prefix() . 'lead_transfer_request.approved_by as approved_by',
    db_prefix() . 'lead_transfer_request.assign as assign',
    db_prefix() . 'leads.phonenumber as phonenumber',
    db_prefix() . 'leads.type as old_lead_type',
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
    // db_prefix() . 'leads_type.name as lead_name',
    // "concat(" . db_prefix() . "staff.firstname, ' ', " . db_prefix() . "staff.lastname) as staff_name",
    // "(
    //     SELECT CONCAT(approved_staff.firstname, ' ', approved_staff.lastname) 
    //     FROM " . db_prefix() . "staff AS approved_staff 
    //     WHERE approved_staff.staffid = " . db_prefix() . "lead_transfer_request.approved_by
    // ) AS approval_name",
    // "(
    //     SELECT CONCAT(approved_staff.firstname, ' ', approved_staff.lastname) 
    //     FROM " . db_prefix() . "staff AS approved_staff 
    //     WHERE approved_staff.staffid = " . db_prefix() . "lead_transfer_request.created_by
    // ) AS created_name",





];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lead_transfer_request';

$where  = [];
$filter = [];

$join          = [];
array_push($join, 'JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'lead_transfer_request.leadid');
// array_push($join, 'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . db_prefix() . 'lead_transfer_request.lead_type');
// array_push($join, 'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'lead_transfer_request.assign');

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



// $aColumns = hooks()->apply_filters('proposals_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
// if (count($custom_fields) > 4) {
//     @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
// }

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];
if (!empty($params["type"]) && $params["type"] == "counsellor") {
    foreach ($rResult as $aRow) {
        $row = [];
        // Ensure the reason is safely encoded for JavaScript
        $reason = !empty($aRow['reason']) ? addslashes($aRow['reason']) : '';
        // Prepare variables with fallbacks
        $lead_transfer_id = !empty($aRow['lead_transfer_id']) ? $aRow['lead_transfer_id'] : '';
        $assign = !empty($aRow['assign']) ? $aRow['assign'] : '';
        $lead_type = !empty($aRow['lead_type']) ? $aRow['lead_type'] : '';
        $leadid = !empty($aRow['leadid']) ? $aRow['leadid'] : '';

        $edit_btn = "<i class='fa fa-edit btn btn-default' onclick=' edit_lead_request(" . $leadid . ")'></i>";
        $outputLeadType = '<span class="inline-block lead-type-' . $aRow['lead_type'] . ' label label-' . (empty($lead_data[$aRow["lead_type"]]['color']) ? 'default' : '') . '" style="color:' . $lead_data[$aRow["lead_type"]]["color"] . ';border:1px solid ' . $aRow['color'] . '">' . $lead_data[$aRow["lead_type"]]["name"];

        if ($aRow["status"] == 3) {

            $outputLeadType .= '<div class="dropdown inline-block mleft5 table-export-exclude">';

            $outputLeadType .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsType-' . $aRow['lead_transfer_id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';

            $outputLeadType .= '<span data-toggle="tooltip" title="' . _l('Change Lead Type') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';

            $outputLeadType .= '</a>';



            $outputLeadType .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsType-' . $aRow['lead_transfer_id'] . '">';

            foreach ($lead_data as $leadChangeType) {

                if ($aRow['type'] != $leadChangeType['id']) {

                    $outputLeadType .= '<li>
    
                      <a href="#" onclick="change_transfer_lead_staff(' . $aRow['leadid'] . ',' . $aRow['lead_transfer_id'] . ',' . $leadChangeType['id'] . '); return false;">
    
                         ' . $leadChangeType['name'] . '
    
                      </a>
    
                   </li>';
                }
            }

            $outputLeadType .= '</ul>';

            $outputLeadType .= '</div>';
        }

        $outputLeadType .= '</span>';





        $row[] = $outputLeadType;
        // $row[] = !empty($lead_data[$aRow["lead_type"]]["name"]) ? $lead_data[$aRow["lead_type"]]["name"] : '';
        // $row[] = !empty($staff_data[$aRow["assign"]]["full_name"]) ? $staff_data[$aRow["assign"]]["full_name"] : '';

        $outputStaffType = '<span class="inline-block lead-type-' . $aRow['lead_type'] . ' label label-' . ((1 == 0) ? 'default' : '') . '" style=" border:1px solid black; color:black;">' . $staff_data[$aRow["assign"]]["full_name"];

        if ($aRow["status"] == 3) {

            $outputStaffType .= '<div class="dropdown inline-block mleft5 table-export-exclude">';

            $outputStaffType .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableStaffType-' . $aRow['lead_transfer_id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';

            $outputStaffType .= '<span data-toggle="tooltip" title="' . _l('Change Assign') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';

            $outputStaffType .= '</a>';



            $outputStaffType .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tabStaffType-' . $aRow['lead_transfer_id'] . '">';

            foreach ($staff_data as $staff_d) {

                if ($aRow['assign'] != $staff_d['staffid']) {

                    $outputStaffType .= '<li>
    
                      <a href="#" onclick="change_transfer_lead_staff(' . $aRow['leadid'] . ',' . $aRow['lead_transfer_id'] . ',``,' . $staff_d['staffid'] . '); return false;">
    
                         ' . $staff_d['full_name'] . '
    
                      </a>
    
                   </li>';
                }
            }

            $outputStaffType .= '</ul>';

            $outputStaffType .= '</div>';
        }

        $outputStaffType .= '</span>';





        $row[] = $outputStaffType;
        $row[] = !empty($aRow['phonenumber']) ? ($aRow['phonenumber']) : '';
        $row[] = !empty($aRow['reason']) ? ($aRow['reason']) : '';
        $row[] = !empty($aRow['status_name']) ? '<span class="text-' . $aRow['status_color'] . '">' . $aRow['status_name'] . '</span>' : 'Not defined';
        $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : '';
        $row[] = !empty($aRow['created_date']) ? _d($aRow['created_date']) : '';
        if (is_admin() || has_permission('leads', '', 'approval')) {
            if ($aRow['status'] == '3') {

                $edit_btn = "<i class='fa fa-edit btn btn-default' onclick='init_lead(" . $aRow['leadid'] . ", true)'></i>";

                $row[] = $edit_btn . '&nbsp;<button class="btn btn-success" onclick="update_lead_transfer(' . $aRow['leadid'] . ', ' . $aRow['lead_type'] . ', ' . $aRow['lead_transfer_id'] . ', ' . $aRow['assign'] . ', 1)">Approved</button>';
            } else {
                $row[] = "";
            }
        } else {
            if ($aRow['status'] == '3' && has_permission('leads', '', 'view')) {

                // Construct the HTML with proper concatenation and encoding
                $row[] = $edit_btn;
            } else {
                $row[] = "";
            }
        }


        // Continue with further processing of $row



        $row['DT_RowClass'] = 'has-row-options';

        // $row = hooks()->apply_filters('proposals_table_row_data', $row, $aRow);

        $output['aaData'][] = $row;
    }
} else if (!empty($params["type"]) && $params["type"] == "admin") {

    foreach ($rResult as $aRow) {
        $row = [];
        // Ensure the reason is safely encoded for JavaScript
        $reason = !empty($aRow['reason']) ? addslashes($aRow['reason']) : '';
        // Prepare variables with fallbacks
        $lead_transfer_id = !empty($aRow['lead_transfer_id']) ? $aRow['lead_transfer_id'] : '';
        $assign = !empty($aRow['assign']) ? $aRow['assign'] : '';
        $lead_type = !empty($aRow['lead_type']) ? $aRow['lead_type'] : '';
        $leadid = !empty($aRow['leadid']) ? $aRow['leadid'] : '';

        $edit_btn = "<i class='fa fa-edit btn btn-default' onclick=' edit_lead_request(" . $leadid . ")'></i>";
        $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : '';


        // $row[] = !empty($lead_data[$aRow["lead_type"]]["name"]) ? $lead_data[$aRow["lead_type"]]["name"] : '';
        // $row[] = !empty($staff_data[$aRow["assign"]]["full_name"]) ? $staff_data[$aRow["assign"]]["full_name"] : '';
        $row[] = !empty($lead_data[$aRow["old_lead_type"]]["name"]) ? $lead_data[$aRow["old_lead_type"]]["name"] : '';

        $outputStaffType = '<span class="inline-block lead-type-' . $aRow['lead_type'] . ' label label-' . ((1 == 0) ? 'default' : 'black') . '" style=" border:1px solid black; color:black;">' . $staff_data[$aRow["assign"]]["full_name"];

        if ($aRow["status"] == 3) {

            $outputStaffType .= '<div class="dropdown inline-block mleft5 table-export-exclude">';

            $outputStaffType .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableStaffType-' . $aRow['lead_transfer_id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';

            $outputStaffType .= '<span data-toggle="tooltip" title="' . _l('Change Assign') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';

            $outputStaffType .= '</a>';



            $outputStaffType .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tabStaffType-' . $aRow['lead_transfer_id'] . '">';

            foreach ($staff_data as $staff_d) {

                if ($aRow['assign'] != $staff_d['staffid']) {

                    $outputStaffType .= '<li>
    
                      <a href="#" onclick="change_transfer_lead_staff(' . $aRow['leadid'] . ',' . $aRow['lead_transfer_id'] . ',``,' . $staff_d['staffid'] . '); return false;">
    
                         ' . $staff_d['full_name'] . '
    
                      </a>
    
                   </li>';
                }
            }

            $outputStaffType .= '</ul>';

            $outputStaffType .= '</div>';
        }

        $outputStaffType .= '</span>';





        $row[] = $outputStaffType;
        $row[] = !empty($aRow['phonenumber']) ? ($aRow['phonenumber']) : '';
        $outputLeadType = '<span class="inline-block lead-type-' . $aRow['lead_type'] . ' label label-' . (empty($lead_data[$aRow["lead_type"]]['color']) ? 'default' : '') . '" style="color:' . $lead_data[$aRow["lead_type"]]["color"] . ';border:1px solid ' . $aRow['color'] . '">' . $lead_data[$aRow["lead_type"]]["name"];

        if ($aRow["status"] == 3) {

            $outputLeadType .= '<div class="dropdown inline-block mleft5 table-export-exclude">';

            $outputLeadType .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsType-' . $aRow['lead_transfer_id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';

            $outputLeadType .= '<span data-toggle="tooltip" title="' . _l('Change Lead Type') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';

            $outputLeadType .= '</a>';



            $outputLeadType .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsType-' . $aRow['lead_transfer_id'] . '">';

            foreach ($lead_data as $leadChangeType) {

                if ($aRow['type'] != $leadChangeType['id']) {

                    $outputLeadType .= '<li>
    
                      <a href="#" onclick="change_transfer_lead_staff(' . $aRow['leadid'] . ',' . $aRow['lead_transfer_id'] . ',' . $leadChangeType['id'] . '); return false;">
    
                         ' . $leadChangeType['name'] . '
    
                      </a>
    
                   </li>';
                }
            }

            $outputLeadType .= '</ul>';

            $outputLeadType .= '</div>';
        }

        $outputLeadType .= '</span>';





        $row[] = $outputLeadType;
        $row[] = !empty($aRow['reason']) ? ($aRow['reason']) : '';
        $row[] = !empty($aRow['status_name']) ? '<span class="text-' . $aRow['status_color'] . '">' . $aRow['status_name'] . '</span>' : 'Not defined';
        $row[] = !empty($aRow['created_date']) ? _d($aRow['created_date']) : '';
        if (is_admin() || has_permission('leads', '', 'approval')) {
            if ($aRow['status'] == '3') {

                $edit_btn = "<i class='fa fa-edit btn btn-default' onclick='init_lead(" . $aRow['leadid'] . ", true)'></i>";

                $row[] = $edit_btn . '&nbsp;<button class="btn btn-success" onclick="update_lead_transfer(' . $aRow['leadid'] . ', ' . $aRow['lead_type'] . ', ' . $aRow['lead_transfer_id'] . ', ' . $aRow['assign'] . ', 1)">Approved</button>';
            } else {
                $row[] = "";
            }
        } else {
            if ($aRow['status'] == '3' && has_permission('leads', '', 'view')) {

                // Construct the HTML with proper concatenation and encoding
                $row[] = $edit_btn;
            } else {
                $row[] = "";
            }
        }


        // Continue with further processing of $row



        $row['DT_RowClass'] = 'has-row-options';

        // $row = hooks()->apply_filters('proposals_table_row_data', $row, $aRow);

        $output['aaData'][] = $row;
    }
}
