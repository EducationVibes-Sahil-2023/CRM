<?php

defined('BASEPATH') or exit('No direct script access allowed');


$lead_data = array_column(get_type(), null, 'id');
$lead_source = array_column(get_source(), null, 'id');
$staff_data = array_column(get_all_staff(), null, 'staffid');
$get_staff_user_id = get_staff_user_id();
$has_permission_delete = has_permission('visit_leads', '', 'delete');
$statuses = visitor_status();


$aColumns = [
    db_prefix() . 'visitor_status.name as status',
    db_prefix() . 'visitor_request.date_of_visit as date_of_visit',
    db_prefix() . 'leads.name as student_name',
    db_prefix() . 'leads.phonenumber as phonenumber',
    db_prefix() . 'leads.update_count as update_count',
    db_prefix() . 'leads.call_duration as call_duration',
    db_prefix() . 'cities_.name as location',
    db_prefix() . 'visitor_type.name as visitor_type',
    db_prefix() . 'visitor_request.assigned as assigned',
    db_prefix() . 'visitor_request.created_by as created_by',
    db_prefix() . 'leads.type as lead_type',
    db_prefix() . 'leads_status .name as status_name',
    db_prefix() . 'leads_sources .name as source_name',
    db_prefix() . 'leads.website as website',
    "Date(" . db_prefix() . 'leads.dateadded) as created_at',
    // "Date(last_note.date_contacted) as updated_at",
    "GREATEST(
    IFNULL(DATE(last_note.dateadded), ''),
    IFNULL(DATE(" . db_prefix() . "leads.lastupdate_date), '')
) AS updated_at",
    "Date(" . db_prefix() . 'leads.lastconnect_date) as lastcontact_date',
    db_prefix() . 'visitor_request.lead_id as lead_id',
    db_prefix() . 'visitor_request.status as status_id',
    db_prefix() . 'visitor_status.color as color',
    db_prefix() . 'visitor_request.updated_by as updated_by',
    db_prefix() . 'visitor_request.id as id',




];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'visitor_request';

$where  = [];
$filter = [];

$join          = [];
array_push($join, 'JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'visitor_request.lead_id');
array_push($join, 'JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status');
array_push($join, 'JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_status ON ' . db_prefix() . 'visitor_status.id = ' . db_prefix() . 'visitor_request.status');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'cities_ ON ' . db_prefix() . 'cities_.id = ' . db_prefix() . 'visitor_request.location');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_type ON ' . db_prefix() . 'visitor_type.id = ' . db_prefix() . 'visitor_request.visitor_type');
array_push($join, '
    LEFT JOIN (
        SELECT *
        FROM ' . db_prefix() . 'notes n1
        WHERE n1.id = (
            SELECT MAX(n2.id)
            FROM ' . db_prefix() . 'notes n2
            WHERE n2.rel_id = n1.rel_id AND n2.rel_type = "lead"
        )
    ) AS last_note ON last_note.rel_id = ' . db_prefix() . 'leads.id
');



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

if (!empty($this->ci->input->post('source_type'))) {
    $where[] = "AND " . db_prefix() . "leads.source IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('source_type'))) . ")";
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

if ($this->ci->input->post('category') != "") {
    $category = (int) $this->ci->input->post('category');
    $currentDate = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');

    if ($category < 1) {
        // Past records only (before today)
        $where[] = "AND $sTable.date_of_visit < '$currentDateTime'";
    } elseif ($category > 1) {
        // Future records including today (considering current date and time)
        $where[] = "AND $sTable.date_of_visit >= '$currentDateTime'";
    } else {
        // Only today's records (date match, ignore time)
        $where[] = "AND DATE($sTable.date_of_visit) = '$currentDate'";
    }
}

$search_column = [];
// Define search and group-by clauses
if (!empty($_POST["search"]["value"])) {
    $search_column = [db_prefix() . 'leads.name', db_prefix() . 'leads.phonenumber', db_prefix() . 'cities_.name'];
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], '', '', '', $search_column);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $edit_btn = '';
    if (in_array($aRow["status_id"], [1, 3]) || $role == 3) {
        $edit_btn = "<div class='row-options'><a onclick='init_lead(" . $aRow['lead_id'] . ",``,`#show_visitor_lead_div`,1)'>" . _l('view') . "</a>";

        if ($has_permission_delete) {
            $edit_btn .= ' | <a href="javascript:void(0)" onclick="delete_visit(' . $aRow['id'] . ')" class=" text-danger">' . _l('delete') . '</a>';
        }

        $edit_btn .= "</div>";
    }


    $outputStatus = '<span class="inline-block text-' . $aRow['color'] . ' lead-status-' . $aRow['status_id'] . ' label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid black; background:white;">' . $aRow['status'];
    if (is_admin()) {
        $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
        $outputStatus .= '</a>';
        $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
        foreach ($statuses as $leadChangeStatus) {
            if ($aRow['status_id'] != $leadChangeStatus['id']) {
                $outputStatus .= '<li>
              <a href="#" onclick="visit_lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                 ' . $leadChangeStatus['name'] . '
              </a>
          </li>';
            }
        }
        $outputStatus .= '</ul>';
        $outputStatus .= '</div>';
    }
    $outputStatus .= '</span>';
    $row[] = $outputStatus;
    // $row[] = date('j F Y, h:i A <\b\r> l', strtotime($aRow["date_of_visit"]));
    $row[] = date('j F Y', strtotime($aRow["date_of_visit"]));
    $row[] = $aRow["student_name"] . "<br>" . $edit_btn;
    $row[] = $aRow["phonenumber"];
    $row[] = $aRow["update_count"];
    $call_duration = 0;
    $row[] = !empty($aRow['call_duration']) ? convertToHMS($aRow['call_duration'], 1) : convertToHMS($call_duration, 1);
    $row[] = $aRow["location"];
    $row[] = $aRow["visitor_type"];
    $row[] = !empty($staff_data[$aRow["assigned"]]["full_name"]) ? $staff_data[$aRow["assigned"]]["full_name"] : "";
    $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : "";
    $row[] = !empty($lead_data[$aRow["lead_type"]]["name"]) ? $lead_data[$aRow["lead_type"]]["name"] : '';
    $row[] = !empty($aRow["status_name"]) ? $aRow["status_name"] : '';
    $row[] = !empty($aRow["source_name"]) ? $aRow["source_name"] : '';
    $row[] = !empty($aRow["website"]) ? $aRow["website"] : '';
    $row[] = !empty($aRow["created_at"]) ? $aRow["created_at"] : '';
    $row[] = !empty($aRow["updated_at"]) ? $aRow["updated_at"] : '';
    $row[] = !empty($aRow["lastcontact_date"]) ? $aRow["lastcontact_date"] : '';
    $row['DT_RowClass'] = 'has-row-options ' . " " . !empty($aRow["color"]) ? $aRow["color"] : 'pending';
    $output['aaData'][] = $row;
}
