<?php

defined('BASEPATH') or exit('No direct script access allowed');
$this->ci->load->model('leads_model');

// error_reporting(E_ALL);
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


$hasPermissionDelete = has_permission('customers', '', 'delete');
$customFieldsColumns = [];
$custom_fields = get_table_custom_fields('customers');

$tblma_applicant_tracker = $this->ci->leads_model->tblma_applicant_tracker($this->ci->input->post('columnNames'));

$tblma_applicant_tracker = array_column($tblma_applicant_tracker, null, "tbl_column_name");
$fees_data = get_clients_fees(2);
// $fees_details = get_clients_fees_details_ids(2);

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
        if (in_array($value["column_name"], ["fees"])) {
            foreach ($fees_data as $fees) {
                $aColumns[] = "MAX(CASE WHEN " . db_prefix() . "applicant_fees_details.fees_id = {$fees['id']} AND " . db_prefix() . "applicant_fees_details.client_id = {$sTable}.userid THEN CONCAT(" . db_prefix() . "currencies.symbol,'',ifnull(" . db_prefix() . "applicant_fees_details.amount,0)) END) as " . str_replace(" ", "_", strtolower($fees["name"]));
                $aColumns_count++;
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
    'LEFT JOIN ' . db_prefix() . 'contacts ON ' . db_prefix() . 'contacts.userid=' . db_prefix() . 'clients.userid AND ' . db_prefix() . 'contacts.is_primary=1',
    'LEFT JOIN ' . db_prefix() . 'basic_details ON ' . db_prefix() . 'basic_details.userid=' . db_prefix() . 'clients.userid ',
    'LEFT JOIN ' . db_prefix() . 'client_passport_details ON ' . db_prefix() . 'client_passport_details.client_id=' . db_prefix() . 'clients.userid',
    'LEFT JOIN ' . db_prefix() . 'passport_stages ON ' . db_prefix() . 'passport_stages.id=' . db_prefix() . 'client_passport_details.passport_status',
    'LEFT JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id=' . db_prefix() . 'clients.leadid ',
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

];




$join = hooks()->apply_filters('customers_table_sql_join', $join);

// Filter by custom groups
$groups   = $this->ci->clients_model->get_groups();
$groupIds = [];
foreach ($groups as $group) {
    if ($this->ci->input->post('customer_group_' . $group['id'])) {
        array_push($groupIds, $group['id']);
    }
}
if (count($groupIds) > 0) {
    // array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_groups WHERE groupid IN (' . implode(', ', $groupIds) . '))');
}

$countries  = $this->ci->clients_model->get_clients_distinct_countries();
$countryIds = [];
foreach ($countries as $country) {
    if ($this->ci->input->post('country_' . $country['country_id'])) {
        array_push($countryIds, $country['country_id']);
    }
}
if (count($countryIds) > 0) {
    array_push($filter, 'AND country IN (' . implode(',', $countryIds) . ')');
}


$this->ci->load->model('invoices_model');
// Filter by invoices
$invoiceStatusIds = [];
foreach ($this->ci->invoices_model->get_statuses() as $status) {
    if ($this->ci->input->post('invoices_' . $status)) {
        array_push($invoiceStatusIds, $status);
    }
}
if (count($invoiceStatusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT clientid FROM ' . db_prefix() . 'invoices WHERE status IN (' . implode(', ', $invoiceStatusIds) . '))');
}

// Filter by estimates
$estimateStatusIds = [];
$this->ci->load->model('estimates_model');
foreach ($this->ci->estimates_model->get_statuses() as $status) {
    if ($this->ci->input->post('estimates_' . $status)) {
        array_push($estimateStatusIds, $status);
    }
}
if (count($estimateStatusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT clientid FROM ' . db_prefix() . 'estimates WHERE status IN (' . implode(', ', $estimateStatusIds) . '))');
}

// Filter by projects
$projectStatusIds = [];
$this->ci->load->model('projects_model');
foreach ($this->ci->projects_model->get_project_statuses() as $status) {
    if ($this->ci->input->post('projects_' . $status['id'])) {
        array_push($projectStatusIds, $status['id']);
    }
}
if (count($projectStatusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT clientid FROM ' . db_prefix() . 'projects WHERE status IN (' . implode(', ', $projectStatusIds) . '))');
}

// Filter by proposals
$proposalStatusIds = [];
$this->ci->load->model('proposals_model');
foreach ($this->ci->proposals_model->get_statuses() as $status) {
    if ($this->ci->input->post('proposals_' . $status)) {
        array_push($proposalStatusIds, $status);
    }
}
if (count($proposalStatusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT rel_id FROM ' . db_prefix() . 'proposals WHERE status IN (' . implode(', ', $proposalStatusIds) . ') AND rel_type="customer")');
}

// Filter by having contracts by type
$this->ci->load->model('contracts_model');
$contractTypesIds = [];
$contract_types   = $this->ci->contracts_model->get_contract_types();

foreach ($contract_types as $type) {
    if ($this->ci->input->post('contract_type_' . $type['id'])) {
        array_push($contractTypesIds, $type['id']);
    }
}
if (count($contractTypesIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT client FROM ' . db_prefix() . 'contracts WHERE contract_type IN (' . implode(', ', $contractTypesIds) . '))');
}

// Filter by proposals
$customAdminIds = [];
foreach ($this->ci->clients_model->get_customers_admin_unique_ids() as $cadmin) {
    if ($this->ci->input->post('responsible_admin_' . $cadmin['staff_id'])) {
        array_push($customAdminIds, $cadmin['staff_id']);
    }
}

if (count($customAdminIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id IN (' . implode(', ', $customAdminIds) . '))');
}

$role = $this->ci->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
if ($role == 3) {
    // $this->load->database();
    $sid = get_staff_user_id(); //48;//get_staff_user_id();

    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();

    $idsarr = array_column($teamids, 'staffid');
    $sids = implode(",", $idsarr);
}

if ($this->ci->input->post('requires_registration_confirmation')) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.registration_confirmed=0');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('customers', '', 'view')) {
    array_push($where, 'AND (' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')  or ' . db_prefix() . 'leads.assigned = ' . get_staff_user_id() . ')');
}

if ($this->ci->input->post('exclude_inactive')) {
    array_push($where, 'AND (' . db_prefix() . 'clients.active = 1 OR ' . db_prefix() . 'clients.active=0 AND registration_confirmed = 0)');
}

if ($this->ci->input->post('my_customers')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
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
    array_push($where, 'AND ' . db_prefix() . 'clients.applicant_status = ' . ($this->ci->db->escape_str($this->ci->input->post('application_stage')) - 1));
}

if ($this->ci->input->post('application_sub_stage')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.application_text = ' . $this->ci->db->escape($this->ci->input->post('application_sub_stage')));
}


if ($this->ci->input->post('vendor_type')) {

    array_push($where, 'AND ' . db_prefix() . 'client_university_shortlisting.vendor_id IN ("' . implode(',', $this->ci->db->escape_str($this->ci->input->post('vendor_type'))) . '")');
}

if ($this->ci->input->post('to_date')) {
    $from_date = $this->ci->input->post('from_date');
    $to_date = $this->ci->input->post('to_date');
    array_push($where, 'AND DATE(' . db_prefix() . 'clients.datecreated) BETWEEN "' . $this->ci->db->escape_str($from_date) . '" AND "' . $this->ci->db->escape_str($to_date) . '"');
}


$aColumns = hooks()->apply_filters('customers_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}


$result = data_tables_init(array_merge($aColumns, [
    db_prefix() . 'contacts.id as contact_id',
    db_prefix() . 'clients.zip as zip',
    'registration_confirmed',
    db_prefix() . 'applicant_tracker.name as applicant_stage_name',
    db_prefix() . 'applicant_tracker.id as applicant_stage_id',
    db_prefix() . 'clients.userid as userid',
]), $sIndexColumn, $sTable, $join, $where, [], 'GROUP BY ' . db_prefix() . 'clients.userid');

// die;
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    if (!empty($aRow["name"])) {
        $company = ($aRow['contact_id'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . $aRow['name'] . '</a>' : '');
        $url = admin_url('clients/client/' . $aRow['userid']);

        if ($isPerson && $aRow['contact_id']) {
            $url .= '?contactid=' . $aRow['contact_id'];
        }

        $company = '<a href="' . $url . '">' . $company . '</a>';

        $company .= '<div class="row-options">';
        $company .= '<a href="' . admin_url('clients/client/' . $aRow['userid'] . ($isPerson && $aRow['contact_id'] ? '?group=contacts' : '')) . '">' . _l('view') . '</a>';

        if ($aRow['registration_confirmed'] == 0 && is_admin()) {
            $company .= ' | <a href="' . admin_url('clients/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
        }
        if (!$isPerson) {
            $company .= ' | <a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
        }
        if ($hasPermissionDelete) {
            $company .= ' | <a href="' . admin_url('clients/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
        }

        $company .= '</div>';

        $aRow["name"] = $company;
    }


    $row = array_values(array_slice($aRow, 0, $aColumns_count));

    $output['aaData'][] = $row;
}
