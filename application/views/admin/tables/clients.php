<?php

defined('BASEPATH') or exit('No direct script access allowed');

$user_lead_type = get_user_lead_type(get_staff_user_id());
if (!empty($user_lead_type->lead_type)) {
    $user_lead_type = $user_lead_type->lead_type;
} else {
    $user_lead_type = 0;
}

$hasPermissionDelete = has_permission('customers', '', 'delete');
$customFieldsColumns = [];
$custom_fields = get_table_custom_fields('customers');
$this->ci->db->query("SET sql_mode = ''");

$aColumns = [
    '1',
    db_prefix() . 'clients.userid as userid',
    db_prefix() . 'clients.company',
    db_prefix() . 'contacts.firstname firstname',
    db_prefix() . 'contacts.email  as email',
    db_prefix() . 'clients.phonenumber as phonenumber',
    db_prefix() . 'clients.active',
    // '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'customer_groups JOIN ' . db_prefix() . 'customers_groups ON ' . db_prefix() . 'customer_groups.groupid = ' . db_prefix() . 'customers_groups.id WHERE customer_id = ' . db_prefix() . 'clients.userid ORDER by name ASC) as customerGroups',
    db_prefix() . 'clients.datecreated as datecreated',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'leads_type.name as type_name',
    db_prefix() . 'leads_sources.name as source_name',
    'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as assigned_name',

    // 'GROUP_CONCAT(' . db_prefix() . 'client_university_shortlisting.vendor_id) as vendor_id',

];


$sIndexColumn = 'userid';
$sTable       = db_prefix() . 'clients';
$where        = [];
// Add blank where all filter can be stored
$filter = [];

$join = [
    'LEFT JOIN ' . db_prefix() . 'contacts ON ' . db_prefix() . 'contacts.userid=' . db_prefix() . 'clients.userid AND ' . db_prefix() . 'contacts.is_primary=1',
    'LEFT JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id=' . db_prefix() . 'clients.leadid ',
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'leads.assigned=' . db_prefix() . 'staff.staffid ',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
    'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . db_prefix() . 'leads.type',
    'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
    'LEFT JOIN ' . db_prefix() . 'applicant_tracker ON ' . db_prefix() . 'applicant_tracker.id = (' . db_prefix() . 'clients.applicant_status+1)',
    'LEFT JOIN ' . db_prefix() . 'client_university_shortlisting ON ' . db_prefix() . 'client_university_shortlisting.client_id = ' . db_prefix() . 'clients.userid',

];


foreach ($custom_fields as $key => $field) {
    $showField = true;
    // Check if the user_lead_type is not empty and it is not an admin
    // if (!empty($user_lead_type) && !is_admin()) {
    //     if ($user_lead_type == 1 && !in_array(strtolower(trim($field['name'])), ['course', 'degree'])) {
    //         // Check if staff_department is not empty and it does not match the show_lead_type
    //         if (!empty($_SESSION["staff_department"]) && !empty($field['show_lead_type']) && $_SESSION["staff_department"] != $field['show_lead_type']) {
    //             continue;
    //         }
    //     } else if ($user_lead_type == 2 && !in_array(strtolower(trim($field['name'])), ['neet score'])) {
    //         // Check if staff_department is not empty and it does not match the show_lead_type
    //         if (!empty($_SESSION["staff_department"]) && !empty($field['show_lead_type']) && $_SESSION["staff_department"] != $field['show_lead_type']) {
    //             continue;
    //         }
    //     }
    // } elseif (!is_admin()) {
    //     // Check if staff_department is not empty and it does not match the show_lead_type
    //     if (!empty($_SESSION["staff_department"]) && !empty($field['show_lead_type']) && $_SESSION["staff_department"] != $field['show_lead_type']) {
    //         continue;
    //     }

    // if (!empty($_SESSION["staff_department"]) && !empty($field['show_lead_type']) && $_SESSION["staff_department"] != $field['show_lead_type']) {
    //     continue;
    // }

    // if (!is_admin() && !empty($user_lead_type) && !empty($field['show_lead_type']) && !in_array($user_lead_type, explode(",", $field['show_lead_type']))) {
    //     continue;
    // }


    if (!is_admin()) {
        $showField = false;
        if (!empty($user_lead_type) && !empty($field['show_lead_type'])) {
            if (in_array($user_lead_type, explode(",", $field['show_lead_type']))) {
                $showField = true;
            } else {
                $showField = false;
            }
        }
    }


    if ($showField) {
        $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
        array_push($customFieldsColumns, $selectAs);
        array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
        array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'clients.userid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
    }
}



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

    $teamids = $this->ci->db->query("select staffid
			from    (select * from tblstaff
			where active = '1' order by reporting_person, staffid) products_sorted,
					(select @pv := $sid) initialisation
			where   find_in_set(reporting_person, @pv)
			and     length(@pv := concat(@pv, ',', staffid))")->result_array();
    // return $query;
    // array_push($teamids,get_staff_user_id());
    // foreach ($teamids as $t) {
    # code...
    // }
    $idsarr = array_column($teamids, 'staffid');

    // echo "<pre>";print_r($idsarr);
    $sids = implode(",", $idsarr);
    // echo "<pre>";print_r($sids);

    // array_push($where, 'AND assigned in (' .$sid. ','. $sids. ')');
    // array_push($where, 'AND '.db_prefix().'clients.userid IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id IN (' . $sid. ','. $sids.')');
    // print_r($where);die;
}

if ($this->ci->input->post('requires_registration_confirmation')) {
    array_push($filter, 'AND ' . db_prefix() . 'clients.registration_confirmed=0');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('customers', '', 'view')) {
    array_push($where, 'AND ' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
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

    // array_push($where, 'AND type =' . $this->ci->db->escape_str($this->ci->input->post('lead_type')));
    // print_r($where);
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
// echo "<pre>";
// print_r($aColumns);

$aColumns = hooks()->apply_filters('customers_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}
// print_r($aColumns);
// die;

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'contacts.id as contact_id',
    db_prefix() . 'contacts.lastname lastname',
    db_prefix() . 'clients.zip as zip',
    'registration_confirmed',
    db_prefix() . 'applicant_tracker.name applicant_stage_name',
    db_prefix() . 'applicant_tracker.id applicant_stage_id',
], 'GROUP BY ' . db_prefix() . 'clients.userid');

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    $company = ($aRow['contact_id'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>' : '');
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

    $row[] = $company;

    // Primary contact
    // $row[] = ($aRow['contact_id'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>' : '');

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
    <input type="checkbox"' . ($aRow['registration_confirmed'] == 0 ? ' disabled' : '') . ' data-switch-url="' . admin_url() . 'clients/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['userid'] . '" data-id="' . $aRow['userid'] . '" ' . ($aRow[db_prefix() . 'clients.active'] == 1 ? 'checked' : '') . '>
    <label class="onoffswitch-label" for="' . $aRow['userid'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow[db_prefix() . 'clients.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    $row[] = $toggleActive;

    // Customer groups parsing
    // $groupsRow = '';
    // if ($aRow['customerGroups']) {
    //     $groups = explode(',', $aRow['customerGroups']);
    //     foreach ($groups as $group) {
    //         $groupsRow .= '<span class="label label-default mleft5 inline-block customer-group-list pointer">' . $group . '</span>';
    //     }
    // }

    // $row[] = $groupsRow;
    $row[] = $aRow['applicant_stage_name'];
    $check_applicant_status = get_applicant_status($aRow['applicant_stage_id'], $aRow['userid']);
    // $check_applicant_status = [];
    $row[] = !empty($check_applicant_status["applicant_stage_status"]) ? $check_applicant_status["applicant_stage_status"] : "";
    $row[] = !empty($check_applicant_status["updated_date"]) ? $check_applicant_status["updated_date"] : "";
    $row[] = _dt($aRow['datecreated']);
    $row[] = $aRow['assigned_name'];
    $row[] = $aRow['status_name'];
    $row[] = $aRow['type_name'];
    $row[] = $aRow['source_name'];

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    if ($aRow['registration_confirmed'] == 0) {
        $row['DT_RowClass'] .= ' alert-info requires-confirmation';
        $row['Data_Title']  = _l('customer_requires_registration_confirmation');
        $row['Data_Toggle'] = 'tooltip';
    }

    $row = hooks()->apply_filters('customers_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
