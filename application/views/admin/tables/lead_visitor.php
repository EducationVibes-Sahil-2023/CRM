<?php

defined('BASEPATH') or exit('No direct script access allowed');
$lead_data = array_column(get_type(), null, 'id');
$lead_source = array_column(get_source(), null, 'id');
$staff_data = array_column(get_all_staff(), null, 'staffid');
$get_staff_user_id = get_staff_user_id();
$has_permission_delete = has_permission('visit_leads', '', 'delete');

$aColumns = [
    db_prefix() . 'visitor_status.name as status',
    db_prefix() . 'visitor_request.date_of_visit as date_of_visit',
    db_prefix() . 'leads.name as student_name',
    db_prefix() . 'leads.phonenumber as phonenumber',
    db_prefix() . 'cities_.name as location',
    db_prefix() . 'visitor_type.name as visitor_type',
    db_prefix() . 'visitor_request.assigned as assigned',
    db_prefix() . 'leads.type as lead_type',
    db_prefix() . 'visitor_request.created_at as created_at',
    db_prefix() . 'visitor_request.created_by as created_by',
    db_prefix() . 'visitor_request.updated_at as updated_at',
    db_prefix() . 'visitor_request.updated_by as updated_by',
    db_prefix() . 'visitor_request.id as id',
    db_prefix() . 'visitor_request.lead_id as lead_id',
    db_prefix() . 'visitor_request.status as status_id',
    db_prefix() . 'visitor_status.color as color',
    db_prefix() . 'leads_status .name as status_name',


];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'visitor_request';

$where  = [];
$filter = [];

$join          = [];
array_push($join, 'JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'visitor_request.lead_id');
array_push($join, 'JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_status ON ' . db_prefix() . 'visitor_status.id = ' . db_prefix() . 'visitor_request.status');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'cities_ ON ' . db_prefix() . 'cities_.id = ' . db_prefix() . 'visitor_request.location');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_type ON ' . db_prefix() . 'visitor_type.id = ' . db_prefix() . 'visitor_request.visitor_type');



if (!empty($params["request_type"]) && $params["request_type"] == 1) {

    $role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {


        $sid = $get_staff_user_id;
        $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $this->ci->db->close();
        $this->ci->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $where[] = !empty($sids) ? "AND " . $sTable . ".assigned IN ({$sid}, {$sids})" : "AND " . $sTable . ".assigned = {$sid}";

        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        }
    } else {
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        } else {
            if (is_admin()) {
            } else {
                $_POST['assigned'][] = $get_staff_user_id;
                $where[] = "AND " . $sTable . ".assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
            }
        }
    }

    if (!empty($this->ci->input->post('attendee'))) {
        $where[] = "AND " . $sTable . ".assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('attendee'))) . ")";
    }
} else {
    $role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {



        $sid = $get_staff_user_id;
        $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $this->ci->db->close();
        $this->ci->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $where[] = !empty($sids) ? "AND " . $sTable . ".created_by IN ({$sid}, {$sids})" : "AND " . $sTable . ".created_by = {$sid}";

        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        }
    } else {

        // Apply filters based on input parameters
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        } else {
            if (is_admin()) {
            } else {
                $_POST['assigned'][] = $get_staff_user_id;
                $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
            }
        }
    }


    if (!empty($this->ci->input->post('attendee'))) {
        $where[] = "AND " . $sTable . ".assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('attendee'))) . ")";
    }
}

// print_r($where);

if (!empty($this->ci->input->post('lead_type'))) {
    $where[] = "AND " . db_prefix() . "leads.type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ")";
}


if (!empty($this->ci->input->post('type'))) {
    $where[] = "AND " . $sTable . ".visitor_type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('type'))) . ")";
}

if (!empty($this->ci->input->post('location'))) {
    $where[] = "AND " . $sTable . ".location IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('location'))) . ")";
}

if (!empty($this->ci->input->post('status'))) {
    $where[] = "AND " . $sTable . ".status IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('status'))) . ")";
}

if (!empty($this->ci->input->post('lead_status'))) {
    $where[] = "AND " . db_prefix() . "leads.status IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_status'))) . ")";
}
if (!empty($this->ci->input->post('to_date'))) {
    $from_date = $this->ci->input->post('from_date');
    $to_date   = $this->ci->input->post('to_date');
    $where[]   = "AND DATE(" . $sTable . ".date_of_visit) BETWEEN '{$this->ci->db->escape_str($from_date)}' AND '{$this->ci->db->escape_str($to_date)}'";
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $edit_btn = '';
    if (in_array($aRow["status_id"], [1, 3]) || $has_permission_delete) {
        $edit_btn = "<div class='row-options'><a onclick='init_lead(" . $aRow['lead_id'] . ",``,`#show_visitor_lead_div`,1)'>" . _l('view') . "</a>";

        if ($has_permission_delete) {
            $edit_btn .= ' | <a href="javascript:void(0)" onclick="delete_visit(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
        }

        $edit_btn .= "</div>";
    }
    $row[] = $aRow["status"];
    $row[] = date('j F Y, h:i A <\b\r> l', strtotime($aRow["date_of_visit"]));
    $row[] = $aRow["student_name"] . "<br>" . $edit_btn;
    $row[] = $aRow["phonenumber"];
    $row[] = $aRow["location"];
    $row[] = $aRow["visitor_type"];
    $row[] = !empty($staff_data[$aRow["assigned"]]["full_name"]) ? $staff_data[$aRow["assigned"]]["full_name"] : "";
    $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : "";
    $row[] = !empty($lead_data[$aRow["lead_type"]]["name"]) ? $lead_data[$aRow["lead_type"]]["name"] : '';
    $row[] = !empty($aRow["status_name"]) ? $aRow["status_name"] : '';
    $row['DT_RowClass'] = 'has-row-options ' . " " . !empty($aRow["color"]) ? $aRow["color"] : 'pending';
    $output['aaData'][] = $row;
}
