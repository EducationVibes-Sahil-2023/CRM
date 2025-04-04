<?php

defined('BASEPATH') or exit('No direct script access allowed');
$this->ci->load->model('leads_model');

$user_lead_type = get_user_lead_type(get_staff_user_id());
if (!empty($user_lead_type->lead_type)) {
    $user_lead_type = $user_lead_type->lead_type;
} else {
    $user_lead_type = 0;
}
$get_applicant_stages = get_applicant_stage_mbbs();
$get_applicant_stages = array_column($get_applicant_stages, null, 'id');


$get_applicant_sub_stages = get_applicant_sub_stage_mbbs();
$get_applicant_sub_stages = array_column($get_applicant_sub_stages, null, 'id');

$orignal_documents = get_orignal_document_list();


$hasPermissionDelete = has_permission('customers', '', 'delete');
$customFieldsColumns = [];
$tblma_applicant_tracker = $this->ci->leads_model->tblma_applicant_tracker($this->ci->input->post('columnNames'));

$tblma_applicant_tracker = array_column($tblma_applicant_tracker, null, "tbl_column_name");
$fees_data = get_clients_fees(2);


$statuses = get_applicant_statuses();
$statuses = array_column($statuses, null, 'id');

$this->ci->db->query("SET sql_mode = ''");
$sIndexColumn = 'userid';
$sTable       = db_prefix() . 'clients';
$where        = [];
// Add blank where all filter can be stored
$filter = [];



$aColumns = [];
$aColumns_count = 0;
if (!empty($tblma_applicant_tracker)) {
    foreach ($tblma_applicant_tracker as $key => $value) {

        if (in_array($value["column_name"], ["fees", "original_documents"])) {
            if ($value["column_name"] == "fees") {
                if (!empty($fees_data)) {
                    foreach ($fees_data as $fees) {
                        $aColumns[] = "MAX(CASE WHEN " . db_prefix() . "applicant_fees_details.fees_id = {$fees['id']} AND " . db_prefix() . "applicant_fees_details.client_id = {$sTable}.userid THEN CONCAT(" . db_prefix() . "currencies.symbol,'',ifnull(" . db_prefix() . "applicant_fees_details.amount,0)) END) as " . str_replace(" ", "_", strtolower($fees["name"]));
                        $aColumns_count++;
                    }
                }
            }
            if ($value["column_name"] == "original_documents") {

                if (!empty($orignal_documents)) {
                    foreach ($orignal_documents as $documents) {
                        $short_name = $documents['short_name']; // Store short_name
                        $safe_column_name = str_replace(" ", "_", $short_name); // Replace spaces with underscores

                        $aColumns[] = "MAX(CASE WHEN " . db_prefix() . "orignal_documents.short_name = '" . $short_name . "' 
                                        THEN 'YES' ELSE 'NO' END) AS `" . $safe_column_name . "`";
                        $aColumns_count++;
                    }
                }
            }
            continue;
        }

        // Use sql_condition if available
        $column_key = !empty($value["sql_condition"]) ? $value["sql_condition"] : $key;

        // Add column name to array
        $aColumns[] = "$column_key as " . str_replace(" ", "_", strtolower($value["label_name"]));
        $aColumns_count++;
    }
}




$join = [
    'LEFT JOIN ' . db_prefix() . 'basic_details ON ' . db_prefix() . 'basic_details.userid=' . db_prefix() . 'clients.userid ',
    'LEFT JOIN ' . db_prefix() . 'applicant_status ON ' . db_prefix() . 'applicant_status.id=' . db_prefix() . 'clients.active ',
    ' JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'clients.leadid 
AND ' . db_prefix() . 'leads.type IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ')',
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'leads.assigned=' . db_prefix() . 'staff.staffid ',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
    'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . db_prefix() . 'leads.type',
    'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
    'LEFT JOIN ' . db_prefix() . 'applicant_tracker ON ' . db_prefix() . 'applicant_tracker.id = (' . db_prefix() . 'clients.applicant_status+1)',
    'LEFT JOIN ' . db_prefix() . 'client_university_shortlisting ON ' . db_prefix() . 'client_university_shortlisting.client_id = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'admission_preferences ON ' . db_prefix() . 'admission_preferences.userid = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'applicant_fees_details ON ' . db_prefix() . 'applicant_fees_details.client_id = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'applicant_fees ON ' . db_prefix() . 'applicant_fees.id = ' . db_prefix() . 'applicant_fees_details.fees_id',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'applicant_fees_details.currency_id',
    'LEFT JOIN ' . db_prefix() . 'orignal_document_status ON ' . db_prefix() . 'orignal_document_status.id = ' . db_prefix() . 'clients.orignal_document_status',
    'LEFT JOIN ' . db_prefix() . 'orignal_documents_received ON ' . db_prefix() . 'orignal_documents_received.userid = ' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'orignal_documents ON ' . db_prefix() . 'orignal_documents.id = ' . db_prefix() . 'orignal_documents_received.doc_id',
    'LEFT JOIN ' . db_prefix() . 'applicant_stages stage_category ON stage_category.id = ' . db_prefix() . 'clients.applicant_stage',
    'LEFT JOIN ' . db_prefix() . 'application_sub_category_mbbs  stage_sub_category ON stage_sub_category.id = ' . db_prefix() . 'clients.applicant_sub_status',
    'LEFT JOIN ' . db_prefix() . 'client_passport_details ON ' . db_prefix() . 'client_passport_details.client_id=' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'passport_stages ON ' . db_prefix() . 'passport_stages.id=' . db_prefix() . 'client_passport_details.passport_status',
    'LEFT JOIN ' . db_prefix() . 'academic_details ON ' . db_prefix() . 'academic_details.userid=' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'neet_status ON ' . db_prefix() . 'neet_status.id=' . db_prefix() . 'academic_details.neet_status'

];

$role = $this->ci->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
$post_sales = $this->ci->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row();

if ($role == 3) {
    $sid = get_staff_user_id();
    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();

    $idsarr = array_column($teamids, 'staffid');
    $sids = implode(",", $idsarr);
}

if (!has_permission('customers', '', 'view') && $post_sales->post_sales != 1) {
    array_push($where, 'AND (' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')  or ' . db_prefix() . 'leads.assigned = ' . get_staff_user_id() . ')');
}

if (!is_admin()) {
    if (has_permission('customers', '', 'view') && $post_sales->post_sales != 1) {
        array_push($where, 'AND (' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')  or ' . db_prefix() . 'leads.assigned IN ( ' . $sids . '))');
    }
}

if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {
    array_push($where, 'AND  ' . db_prefix() . 'leads.assigned IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ')');
}

if ($this->ci->input->post('source')) {
    array_push($where, 'AND ' . db_prefix() . 'leads.source IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('source'))) . ')');
}


if ($this->ci->input->post('lead_type')) {
    array_push($where, 'AND ' . db_prefix() . 'leads.type IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ')');
}

if ($this->ci->input->post('application_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_stage = ' . ($this->ci->db->escape_str($this->ci->input->post('application_stage'))));
}

if ($this->ci->input->post('application_sub_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_sub_status = ' . $this->ci->db->escape($this->ci->input->post('application_sub_stage')));
}

if ($this->ci->input->post('university')) {
    $universities = $this->ci->input->post('university');
    if (is_array($universities)) {
        $escaped_universities = array_map([$this->ci->db, 'escape'], $universities);
        array_push($where, 'AND ' . db_prefix() . 'admission_preferences.primary_university IN (' . implode(',', $escaped_universities) . ')');
    }
}

if ($this->ci->input->post('country')) {
    $countries = $this->ci->input->post('country');
    if (is_array($countries)) {
        $escaped_countries = array_map([$this->ci->db, 'escape'], $countries);
        array_push($where, 'AND ' . db_prefix() . 'admission_preferences.primary_country IN (' . implode(',', $escaped_countries) . ')');
    }
}

if ($this->ci->input->post('status_')) {
    $status = $this->ci->input->post('status_');
    if (is_array($status)) {
        $escaped_status = array_map([$this->ci->db, 'escape'], $status);
        array_push($where, 'AND ' . db_prefix() . 'clients.active IN (' . implode(',', $escaped_status) . ')');
    }
}

if ($this->ci->input->post('minor_status')) {
    $minor = $this->ci->input->post('minor_status');
    array_push($where, "AND ((TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 AND 'Yes' = '{$minor}')
    OR (TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 18 AND 'No' = '{$minor}'))");
}

if ($this->ci->input->post('passport_status')) {
    $passport_status = $this->ci->input->post('passport_status');
    if (is_array($passport_status)) {
        $escaped_passport_status = array_map([$this->ci->db, 'escape'], $passport_status);
        array_push($where, 'AND ' . db_prefix() . 'passport_stages.id IN (' . implode(',', $escaped_passport_status) . ')');
    }
}

if ($this->ci->input->post('doc_status')) {
    $doc_status = $this->ci->input->post('doc_status');
    if (is_array($doc_status)) {
        $escaped_doc_status = array_map([$this->ci->db, 'escape'], $doc_status);
        array_push($where, 'AND ' . db_prefix() . 'clients.orignal_document_status IN (' . implode(',', $escaped_doc_status) . ')');
    }
}

if ($this->ci->input->post('application_sub_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_sub_status = ' . $this->ci->db->escape($this->ci->input->post('application_sub_stage')));
}





if ($this->ci->input->post('to_date')) {
    $from_date = $this->ci->input->post('from_date');
    $to_date = $this->ci->input->post('to_date');
    array_push($where, 'AND DATE(' . db_prefix() . 'clients.datecreated) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"');
}

if ($this->ci->input->post('last_to_date')) {
    $from_date = $this->ci->input->post('last_from_date');
    $to_date = $this->ci->input->post('last_to_date');
    array_push($where, 'AND DATE(' . db_prefix() . 'clients.last_update) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"');
}

$additional_array = [
    db_prefix() . 'clients.zip as zip',
    'registration_confirmed',
    db_prefix() . 'applicant_tracker.name as applicant_stage_name',
    db_prefix() . 'applicant_tracker.id as applicant_stage_id',
    db_prefix() . 'clients.userid as userid',
    db_prefix() . 'clients.active as status_id',
    db_prefix() . 'applicant_status.color as color',
];
$result = data_tables_init(array_merge($aColumns, $additional_array), $sIndexColumn, $sTable, $join, $where, [], 'GROUP BY ' . db_prefix() . 'clients.userid');

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    if (!empty($aRow["name"])) {
        $company = ($aRow['userid'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . $aRow['name'] . '</a>' : '');
        $url = admin_url('clients/client/' . $aRow['userid']);

        // if ($isPerson && $aRow['contact_id']) {
        //     $url .= '?contactid=' . $aRow['contact_id'];
        // }

        $company = '<a href="' . $url . '">' . $company . '</a>';

        $company .= '<div class="row-options">';
        $company .= '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '">' . _l('view') . '</a>';

        if ($aRow['registration_confirmed'] == 0 && is_admin()) {
            // $company .= ' | <a href="' . admin_url('clients/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
        }
        if (!$isPerson) {
            // $company .= ' | <a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
        }
        if ($hasPermissionDelete) {
            $company .= ' | <a href="' . admin_url('clients/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
        }

        $company .= '</div>';

        $aRow["name"] = $company;
    }

    $selection = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';

    if (is_admin() || is_postSale()) {
        array_unshift($aRow, $selection);
    }

    if (!empty($aRow["status"])) {
        $color = !empty($aRow['color']) ? $aRow['color'] : 'default';

        $outputStatus = '<span class="inline-block text-' . $color . ' lead-status-' . $aRow['status'] . ' label label-' . $color . '" style="color:' . $color . '; border:1px solid ' . $color . ';">' . $aRow['status'];

        if (!$locked) {
            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $outputStatus .= '<a href="#" style="font-size:14px; vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            $outputStatus .= '</a>';

            if ((is_admin() || is_postSale()) && $statuses[$aRow["status_id"]]['refund'] != 1) {
                $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';

                $canceled_status = !empty($statuses[$aRow["status_id"]]['canceled']);
                $refunded_status = !empty($statuses[$aRow["status_id"]]['refund']);

                foreach ($statuses as $leadChangeStatus) {
                    if ($aRow['status_id'] != $leadChangeStatus['id']) {
                        $is_refunded = !empty($leadChangeStatus["refund"]);

                        // If the current status is canceled, allow only refund transitions.
                        if ($canceled_status && $is_refunded) {
                            $outputStatus .= '<li><a href="#" onclick="applicant_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['userid'] . ',' . $leadChangeStatus['canceled'] . ',' . $leadChangeStatus['refund'] . '); return false;">' . $leadChangeStatus['name'] . '</a></li>';
                        }
                        // If the current status is refunded, do nothing.
                        else if (!$refunded_status && !$is_refunded && !$canceled_status) {
                            $outputStatus .= '<li><a href="#" onclick="applicant_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['userid'] . ',' . $leadChangeStatus['canceled'] . ',' . $leadChangeStatus['refund'] . '); return false;">' . $leadChangeStatus['name'] . '</a></li>';
                        }
                    }
                }

                $outputStatus .= '</ul>';
            }

            $outputStatus .= '</div>';
        }

        $outputStatus .= '</span>';
        $aRow["status"] = $outputStatus;
    }


    if (!empty($aRow["secondary_university"])) {
        $primary_university = trim($aRow["primary_university"]);
        $secondary_university = json_decode($aRow["secondary_university"], true); // Decode JSON as an associative array

        $filtered_universities = [];

        foreach ($secondary_university as $universities) {
            // Remove primary university and merge the remaining universities into the final array
            $filtered_universities = array_merge($filtered_universities, array_diff(array_map('trim', explode(",", $universities)), [$primary_university]));
        }

        $aRow["secondary_university"] = implode(",", $filtered_universities);
    }


    $row = array_values(array_slice($aRow, 0, $aColumns_count + ((is_admin() || is_postSale()) ? 1 : 0)));

    $output['aaData'][] = $row;
}
