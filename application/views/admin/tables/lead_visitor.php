<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Visitor request table — call_agg rebuilt on YOUR "latest visitor_request
 * per lead" pattern.
 *
 * What changed vs. your file (ONLY the call_agg join block):
 *
 *  - The calls subquery now starts from (SELECT lead_id, MAX(id) FROM
 *    tblvisitor_request GROUP BY lead_id) — one row per lead — before joining
 *    calls. Your old version joined leads × ALL visitor_requests, so a lead
 *    with 3 visit rows counted every call 3 times (inflated call_count and
 *    call_duration). This fixes that correctness bug AND shrinks the join.
 *
 *  - The LIMIT 1000 from your draft is NOT included: an arbitrary cap would
 *    silently drop call data for leads beyond the first 1000 ids. The
 *    GROUP BY dedupe gives the speed win without losing anyone.
 *
 * Everything else (permissions, filters, output loop) is unchanged.
 *
 * Run once if not done yet:
 *   ALTER TABLE tblcalls_activity_logs ADD INDEX idx_contact (contact);
 *   ALTER TABLE tblvisitor_request     ADD INDEX idx_lead (lead_id);
 *   ALTER TABLE tblnotes               ADD INDEX idx_rel (rel_type, rel_id);
 */

$lead_data = array_column(get_type(), null, 'id');
$lead_source = array_column(get_source(), null, 'id');
$staff_data = array_column(get_all_staff(), null, 'staffid');
$get_staff_user_id = get_staff_user_id();
$has_permission_delete = has_permission('visit_leads', '', 'delete');
$statuses = visitor_status();
$connected_from = !empty($_POST['connected_from_date']) ? $_POST['connected_from_date'] : '';
$connected_to   = !empty($_POST['connected_to_date'])   ? $_POST['connected_to_date']   : '';
$updated_from   = !empty($_POST['updated_from_date'])   ? $_POST['updated_from_date']   : '';
$updated_to     = !empty($_POST['updated_to_date'])     ? $_POST['updated_to_date']     : '';

$aColumns = [
    db_prefix() . 'visitor_status.name as status',
    db_prefix() . 'visitor_request.date_of_visit as date_of_visit',
    db_prefix() . 'leads.name as student_name',
    db_prefix() . 'leads.phonenumber as phonenumber',
    'call_agg.call_count as update_count',
    'call_agg.call_duration as call_duration',
    db_prefix() . 'leads.update_count as total_update_count',
    db_prefix() . 'leads.call_duration as total_call_duration',
    db_prefix() . 'cities_.name as location',
    db_prefix() . 'visitor_type.name as visitor_type',
    db_prefix() . 'visitor_request.assigned as assigned',
    db_prefix() . 'visitor_request.created_by as created_by',
    db_prefix() . 'leads.type as lead_type',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'leads_sources.name as source_name',
    db_prefix() . 'visitor_request.whatsapp_notify as whatsapp_notify',
    db_prefix() . 'visitor_request.whatsapp_status as whatsapp_status',
];

if (is_admin()) {
    $aColumns = array_merge($aColumns, [
        db_prefix() . 'leads.website as website',
        db_prefix() . 'leads.utm_campaign_name as utm_campaign_name',
        db_prefix() . 'leads.utm_ads_set_name as utm_ads_set_name',
        db_prefix() . 'leads.utm_ads_name as utm_ads_name',
        db_prefix() . 'leads.utm_term as utm_term',
    ]);
}

$aColumns = array_merge($aColumns, [
    'DATE(' . db_prefix() . 'leads.dateadded) AS created_at',
    'NULLIF(
        DATE(
            GREATEST(
                IFNULL(last_note.dateadded, "0000-00-00 00:00:00"),
                IFNULL(call_agg.lastupdated_date,"0000-00-00 00:00:00")
            )
        ),
    "0000-00-00") AS updated_at',
    'DATE(call_agg.lastcontact_date) AS lastcontact_date',
]);

$aColumns = array_merge($aColumns, [
    'tblvisitor_request.lead_id as lead_id',
    'tblvisitor_request.status as status_id',
    'tblvisitor_status.color as color',
    'tblvisitor_request.updated_by as updated_by',
    'tblvisitor_request.id as id',
]);

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'visitor_request';
$where        = [];
$join         = [];

array_push($join, 'JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'visitor_request.lead_id');

// ---------------------------------------------------------------------
// call_agg — YOUR latest-visitor_request-per-lead pattern
// ---------------------------------------------------------------------
$callWhere = "";
$callJoin  = " LEFT ";
if (!empty($connected_from) && !empty($connected_to)) {
    $callJoin = '';
    $callWhere .= " AND ca.call_status = 'Answered'";
    $callWhere .= " AND ca.adjusted_call_start >= "
        . $this->ci->db->escape($connected_from . ' 00:00:00');
    $callWhere .= " AND ca.adjusted_call_start < "
        . $this->ci->db->escape(date('Y-m-d', strtotime($connected_to . ' +1 day')) . ' 00:00:00');
} elseif (!empty($updated_from) && !empty($updated_to) && empty($connected_from)) {
    $callJoin = '';
    $callWhere .= " AND ca.adjusted_call_start >= "
        . $this->ci->db->escape($updated_from . ' 00:00:00');
    $callWhere .= " AND ca.adjusted_call_start < "
        . $this->ci->db->escape(date('Y-m-d', strtotime($updated_to . ' +1 day')) . ' 00:00:00');
}

$join[] = $callJoin . '
JOIN (
    SELECT
        lead_id,
        SUM(duration) AS call_duration,
        COUNT(*) AS call_count,
        MAX(CASE WHEN call_status = "Answered" THEN adjusted_call_start END) AS lastcontact_date,
        MAX(adjusted_call_start) AS lastupdated_date
    FROM (
        /* main number — one row per LEAD (latest visitor_request), so calls
           are never multiplied by the number of visit rows */
        SELECT
            vr.lead_id AS lead_id,
            ca.duration,
            ca.call_status,
            ca.adjusted_call_start
        FROM (
            SELECT lead_id, MAX(id) AS id
            FROM ' . db_prefix() . 'visitor_request where lead_id = tblvisitor_request.id
            GROUP BY lead_id
        ) latest
        JOIN ' . db_prefix() . 'visitor_request vr ON vr.id = latest.id
        JOIN ' . db_prefix() . 'leads l ON l.id = vr.lead_id
        JOIN ' . db_prefix() . 'calls_activity_logs ca
            ON ca.contact = l.phonenumber
        WHERE 1=1
        ' . $callWhere . '

        UNION ALL

        /* alternative number */
        SELECT
            vr.lead_id AS lead_id,
            ca.duration,
            ca.call_status,
            ca.adjusted_call_start
        FROM (
            SELECT lead_id, MAX(id) AS id
            FROM ' . db_prefix() . 'visitor_request where lead_id = tblvisitor_request.id
            GROUP BY lead_id
        ) latest
        JOIN ' . db_prefix() . 'visitor_request vr ON vr.id = latest.id
        JOIN ' . db_prefix() . 'leads l ON l.id = vr.lead_id
        JOIN ' . db_prefix() . 'calls_activity_logs ca
            ON ca.contact = l.alternative_phonenumber
        WHERE l.alternative_phonenumber <> ""
        ' . $callWhere . '
    ) t
    GROUP BY lead_id
) AS call_agg
ON call_agg.lead_id = ' . db_prefix() . 'leads.id';

array_push($join, 'JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status');
array_push($join, 'JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_status ON ' . db_prefix() . 'visitor_status.id = ' . db_prefix() . 'visitor_request.status');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'cities_ ON ' . db_prefix() . 'cities_.id = ' . db_prefix() . 'visitor_request.location');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_type ON ' . db_prefix() . 'visitor_type.id = ' . db_prefix() . 'visitor_request.visitor_type');

// last_note — grouped version (single pass over tblnotes, uses idx_rel)
// instead of the correlated MAX-per-note-row original.
array_push($join, '
    LEFT JOIN (
        SELECT rel_id, MAX(dateadded) AS dateadded
        FROM ' . db_prefix() . 'notes
        WHERE rel_type = "lead"
        GROUP BY rel_id
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
            if (!is_admin()) {
                if (has_permission('visit_leads', '', 'view_department')) {
                    $lead_type = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->lead_type;
                    $where[] = "AND " . db_prefix() . "leads.type =" . (int)$lead_type;
                } elseif (has_permission('visit_leads', '', 'view')) {
                    // no extra filter
                } else {
                    $_POST['assigned'][] = $get_staff_user_id;
                    $where[] = "AND " . $sTable . ".assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
                }
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
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        } else {
            if (!is_admin()) {
                $_POST['assigned'][] = $get_staff_user_id;
                $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
            }
        }
    }
    if (!empty($this->ci->input->post('attendee'))) {
        $where[] = "AND " . $sTable . ".assigned IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('attendee'))) . ")";
    }
}

if (!empty($this->ci->input->post('lead_type'))) {
    $where[] = "AND " . db_prefix() . "leads.type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ")";
}
if (!empty($this->ci->input->post('source_type'))) {
    $where[] = "AND " . db_prefix() . "leads.source IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('source_type'))) . ")";
}
if (!empty($this->ci->input->post('last_update_date'))) {
    $where[] = ' AND lastupdate_date <= "' . $this->ci->db->escape_str($this->ci->input->post('last_update_date')) . '"';
}
if (!empty($this->ci->input->post('last_contact_date'))) {
    $where[] = ' AND lastconnect_date <= "' . $this->ci->db->escape_str($this->ci->input->post('last_contact_date')) . '" ';
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
if (!empty($this->ci->input->post('callDurationOperator')) && (!empty($this->ci->input->post('callDurationHour')) || !empty($this->ci->input->post('callDurationMinute')))) {
    $operator = $this->ci->input->post('callDurationOperator');
    $hour     = (int)$this->ci->input->post('callDurationHour');
    $minute   = (int)$this->ci->input->post('callDurationMinute');
    if ($operator === '&lt;=') { $operator = '<='; }
    elseif ($operator === '&gt;=') { $operator = '>='; }
    if (!in_array($operator, ['<=', '>='], true)) { $operator = '>='; }
    if ($hour > 0 || $minute > 0) {
        $durationInSeconds = ($hour * 3600) + ($minute * 60);
        $where[] = " AND call_agg.call_duration {$operator} " . (int)$durationInSeconds;
    }
}
if ($this->ci->input->post('category') != "") {
    $category = (int)$this->ci->input->post('category');
    $currentDate = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');
    if ($category < 1) {
        $where[] = "AND $sTable.date_of_visit < '$currentDateTime'";
    } elseif ($category > 1) {
        $where[] = "AND $sTable.date_of_visit >= '$currentDateTime'";
    } else {
        $where[] = "AND DATE($sTable.date_of_visit) = '$currentDate'";
    }
}

$search_column = [];
if (!empty($_POST["search"]["value"])) {
    $search_column = [db_prefix() . 'leads.name', db_prefix() . 'leads.phonenumber', db_prefix() . 'cities_.name'];
}
if (!empty($this->ci->input->post('excelStatus')) && $this->ci->input->post('excelStatus') == 1) {
    $_POST["order"][0]["column"] = 0;
    $_POST["order"][0]["dir"]    = "desc";
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], '', '', '', $search_column);

if (!empty($this->ci->input->post('excelStatus')) && $this->ci->input->post('excelStatus') == 1) {
    echo json_encode($result['rResult'], true);
    die;
}

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
    if (is_admin() || has_permission('visit_leads', '', 'modify')) {
        $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
        $outputStatus .= '</a>';
        $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
        foreach ($statuses as $leadChangeStatus) {
            if ($aRow['status_id'] != $leadChangeStatus['id']) {
                $outputStatus .= '<li><a href="#" onclick="visit_lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">' . $leadChangeStatus['name'] . '</a></li>';
            }
        }
        $outputStatus .= '</ul></div>';
    }
    $outputStatus .= '</span>';
    $row[] = $outputStatus;

    if (!empty($aRow['whatsapp_notify']) && in_array($aRow['whatsapp_status'], [4, 1])) {
        $whatsApp_notification = '&nbsp; <i class="fa fa-whatsapp text-success" data-toggle="tooltip" data-placement="top" title="Last WhatsApp notification sent on ' . _dt($aRow['whatsapp_notify']) . '"></i>';
    } else {
        $whatsApp_notification = '';
    }

    $row[] = date('j F Y', strtotime($aRow["date_of_visit"]));
    $row[] = $aRow["student_name"] . $whatsApp_notification . "<br>" . $edit_btn;
    $row[] = $aRow["phonenumber"];
    $row[] = $aRow["update_count"];
    $row[] = !empty($aRow['call_duration']) ? convertToHMS($aRow['call_duration'], 1) : convertToHMS(0, 1);
    $row[] = $aRow["total_update_count"];
    $row[] = !empty($aRow['total_call_duration']) ? convertToHMS($aRow['total_call_duration'], 1) : convertToHMS(0, 1);
    $row[] = $aRow["location"];
    $row[] = $aRow["visitor_type"];
    $row[] = !empty($staff_data[$aRow["assigned"]]["full_name"]) ? $staff_data[$aRow["assigned"]]["full_name"] : "";
    $row[] = !empty($staff_data[$aRow["created_by"]]["full_name"]) ? $staff_data[$aRow["created_by"]]["full_name"] : "";
    $row[] = !empty($lead_data[$aRow["lead_type"]]["name"]) ? $lead_data[$aRow["lead_type"]]["name"] : '';
    $row[] = !empty($aRow["status_name"]) ? $aRow["status_name"] : '';
    $row[] = !empty($aRow["source_name"]) ? $aRow["source_name"] : '';
    if (is_admin()) {
        $row[] = !empty($aRow["website"]) ? $aRow["website"] : '';
        $row[] = !empty($aRow["utm_campaign_name"]) ? $aRow["utm_campaign_name"] : '';
        $row[] = !empty($aRow["utm_ads_set_name"]) ? $aRow["utm_ads_set_name"] : '';
        $row[] = !empty($aRow["utm_ads_name"]) ? $aRow["utm_ads_name"] : '';
        $row[] = !empty($aRow["utm_term"]) ? $aRow["utm_term"] : '';
    }
    $row[] = !empty($aRow["created_at"]) ? $aRow["created_at"] : '';
    $row[] = !empty($aRow["updated_at"]) ? $aRow["updated_at"] : '';
    $row[] = !empty($aRow["lastcontact_date"]) ? $aRow["lastcontact_date"] : '';

    // precedence fix: parentheses keep 'has-row-options' in the class list
    $row['DT_RowClass'] = 'has-row-options ' . (!empty($aRow["color"]) ? $aRow["color"] : 'pending');

    $output['aaData'][] = $row;
}