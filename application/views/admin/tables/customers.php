<?php

defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);

		ini_set('display_errors', 1);

$this->ci->load->model('leads_model');

$this->ci->db->query("SET sql_mode = ''");

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'leads';
$where        = [];
$filter       = [];

$aColumns = [
    $sTable . ".id as id",
    $sTable . ".name as name",
    $sTable . ".phonenumber as phonenumber",
    $sTable . ".email as email",
    db_prefix() . "leads_status.name as status_name",
    db_prefix() . "leads_status.color as status_color",
    db_prefix() . "leads_type.name as type_name",
    db_prefix() . "leads_sources.name as source_name",
    "CONCAT(" . db_prefix() . "staff.firstname,' '," . db_prefix() . "staff.lastname )as assigne_name"
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . $sTable . '.status',
    'LEFT JOIN ' . db_prefix() . 'leads_type ON ' . db_prefix() . 'leads_type.id = ' . $sTable . '.type',
    'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . $sTable . '.source',
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $sTable . '.assigned'
];

// Ensure only status = 1 leads are shown
$where[] = "AND {$sTable}.status = 1 and source!=1 ";

// Leads NOT in clients (i.e. not converted)
$where[] = "AND {$sTable}.id NOT IN (
    SELECT leadid FROM " . db_prefix() . "clients WHERE leadid IS NOT NULL
)";

$where[] = "AND {$sTable}.phonenumber NOT IN (
    SELECT phonenumber FROM " . db_prefix() . "clients WHERE phonenumber IS NOT NULL
)";

// Permission-based filtering (assigned staff logic)
$get_staff_user_id = get_staff_user_id();
$role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role ?? 0;

if ($role == 3) {
    $sid = $get_staff_user_id;
    $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
    $this->ci->db->close();
    $this->ci->db->initialize();

    $idsarr = array_column($teamids, 'staffid');
    $sids = implode(',', $idsarr);
    $where[] = !empty($sids) ? "AND {$sTable}.assigned IN ({$sid}, {$sids})" : "AND {$sTable}.assigned = {$sid}";
}
else
{
    if(!is_admin() && !is_postSale()){
   $where[] = "AND {$sTable}.assigned IN ($get_staff_user_id)";  
    }
}

// Filter: Assigned staff
if ($this->ci->input->post('assigned')) {
    $assigned = implode(',', array_map('intval', $this->ci->input->post('assigned')));
    $where[] = "AND {$sTable}.assigned IN ($assigned)";
}

// Filter: Source
if ($this->ci->input->post('source')) {
    $source = implode(',', array_map('intval', $this->ci->input->post('source')));
    $where[] = "AND {$sTable}.source IN ($source)";
}

// Filter: Lead Type
if ($this->ci->input->post('lead_type')) {
    $lead_type = implode(',', array_map('intval', $this->ci->input->post('lead_type')));
    $where[] = "AND {$sTable}.type IN ($lead_type)";
}

if ($this->ci->input->post('session_year')) {
    $session_year = (int) $this->ci->input->post('session_year');
    $where[] = "AND YEAR({$sTable}.last_status_change) = {$session_year}";
}



// Handle ordering logic for non-admin
if (!is_admin() && !is_postSale() && $_POST["order"][0]["column"] == 0) {
    $_POST["order"][0]["column"] = "id";
}



// Run the datatable query
$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [],
    'GROUP BY ' . $sTable . '.id'
);

$output  = $result['output'];
$rResult = $result['rResult'];

// Build the table rows
foreach ($rResult as $aRow) {
    $row = [];
    $href = admin_url('leads/index/' . $aRow['id']);
    $onclick = "init_lead(" . $aRow['id'] . ");return false;";
    $hrefAttr = 'href="' . $href . '" onclick="' . $onclick . '"';

    // Name + Options
    $nameRow = '<a ' . $hrefAttr . '>' . $aRow['name'] . '</a>';
    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';

    $locked = false; // You can include is_converted logic if needed

    if (!$locked) {
        $nameRow .= ' | <a href="' . $href . '?edit=true" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
    }

    $nameRow .= '</div>';

    $row[] = $nameRow;
    $row[] = $aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '';
    $row[] = $aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '';

    // Status label
    $statusLabel = '<span class="inline-block lead-status-' . $aRow['status_name'] . ' label label-default"';
    $statusLabel .= ' style="color:' . $aRow['status_color'] . ';border:1px solid ' . $aRow['status_color'] . '">';
    $statusLabel .= $aRow['status_name'] . "</span>";
    $row[] = $statusLabel;

    $row[] = $aRow['type_name'] ?? '';
    $row[] = $aRow['source_name'] ?? '';
    $row[] = $aRow['assigne_name'] ?? '';

    $output['aaData'][] = $row;
}
