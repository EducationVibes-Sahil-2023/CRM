<?php
defined('BASEPATH') or exit('No direct script access allowed');

$lead_data             = array_column(get_type(), null, 'id');
$lead_source           = array_column(get_source(), null, 'id');
$staff_data            = array_column(get_all_staff(), null, 'staffid');
$get_staff_user_id     = get_staff_user_id();
$has_permission_delete = has_permission('visit_leads', '', 'delete');
$statuses              = visitor_status();

$aColumns = [
    db_prefix() . 'visitor_status.name as status',
    "date(".db_prefix() . 'visitor_request.date_of_visit) as date_of_visit',
    "count(" . db_prefix() . "leads.id) as no_of_visitors",
    db_prefix() . 'cities_.name as location',
    db_prefix() . 'visitor_type.name as visitor_type',
    db_prefix() . 'leads.type as lead_type',
    db_prefix() . 'visitor_request.id as id',
    db_prefix() . 'leads.id as lead_id',
    db_prefix() . 'visitor_request.image as visit_image',
    db_prefix() . 'visitor_request.seminar_address as seminar_address',
    db_prefix() . 'visitor_request.whatsapp_notify as whatsapp_notify',
     db_prefix() . 'visitor_request.location as location_id',
     db_prefix() . 'visitor_request.visitor_type as type_id'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'visitor_request';
$where        = [];
$filter       = [];
$join         = [];

array_push($join, 'JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'visitor_request.lead_id');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_status ON ' . db_prefix() . 'visitor_status.id = ' . db_prefix() . 'visitor_request.status');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'cities_ ON ' . db_prefix() . 'cities_.id = ' . db_prefix() . 'visitor_request.location');
array_push($join, 'LEFT JOIN ' . db_prefix() . 'visitor_type ON ' . db_prefix() . 'visitor_type.id = ' . db_prefix() . 'visitor_request.visitor_type');

if (!empty($params["request_type"]) && $params["request_type"] == 1) {
    $role = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        $sid     = $get_staff_user_id;
        $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $this->ci->db->close();
        $this->ci->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids   = implode(",", $idsarr);
        $where[] = !empty($sids)
            ? "AND " . $sTable . ".assigned IN ({$sid}, {$sids})"
            : "AND " . $sTable . ".assigned = {$sid}";
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        }
    } else {
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        } else {
            if (is_admin()) {
                // admin: no extra restriction
            } else {
                if (has_permission('visit_leads', '', 'view_department')) {
                    $lead_type = $this->ci->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->lead_type;
                    $where[]   = "AND " . db_prefix() . "leads.type = " . $lead_type;
                } else if (has_permission('visit_leads', '', 'view')) {
                    // view all: no extra restriction
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
        $sid     = $get_staff_user_id;
        $teamids = $this->ci->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $this->ci->db->close();
        $this->ci->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids   = implode(",", $idsarr);
        $where[] = !empty($sids)
            ? "AND " . $sTable . ".created_by IN ({$sid}, {$sids})"
            : "AND " . $sTable . ".created_by = {$sid}";
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        }
    } else {
        // Apply filters based on input parameters
        if ($this->ci->input->post('assigned')) {
            $where[] = "AND " . $sTable . ".created_by IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('assigned'))) . ")";
        } else {
            if (is_admin()) {
                // admin: no extra restriction
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

if (!empty($this->ci->input->post('lead_type'))) {
    $where[] = "AND " . db_prefix() . "leads.type IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('lead_type'))) . ")";
}
if (!empty($this->ci->input->post('source_type'))) {
    $where[] = "AND " . db_prefix() . "leads.source IN (" . implode(',', $this->ci->db->escape_str($this->ci->input->post('source_type'))) . ")";
}

$where[] = "AND " . $sTable . ".whatsapp_notify >= '".date('Y-m-d H:i:s')."' or " . $sTable . ".whatsapp_notify IS NULL ";

if (!empty($this->ci->input->post('last_update_date'))) {
    $last_update_date = $this->ci->db->escape_str($this->ci->input->post('last_update_date'));
    array_push($where, ' AND lastupdate_date <= "' . $this->ci->db->escape_str($last_update_date) . '"');
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

 $where[] = " AND " . $sTable . ".visitor_type = 2 ";
 $where[] = " AND " . $sTable . ".status IN (1,3) ";
 $where[] = " AND DATE(" . $sTable . ".date_of_visit) >= '" . date('Y-m-d') . "'";
if (!empty($this->ci->input->post('to_date'))) {
    $from_date = $this->ci->input->post('from_date');
    $to_date   = $this->ci->input->post('to_date');
    $where[]   = "AND DATE(" . $sTable . ".date_of_visit) BETWEEN '{$this->ci->db->escape_str($from_date)}' AND '{$this->ci->db->escape_str($to_date)}'";
}
if ($this->ci->input->post('category') != "") {
    $category        = (int) $this->ci->input->post('category');
    $currentDate     = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');
    if ($category < 1) {
        // Past records only (before today)
        $where[] = "AND $sTable.date_of_visit < '$currentDateTime'";
    } elseif ($category > 1) {
        // Future records including today
        $where[] = "AND $sTable.date_of_visit >= '$currentDateTime'";
    } else {
        // Only today's records
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

// FIXED: include the GROUP BY keyword — this version of data_tables_init inserts
// $sGroupBy raw (right after the JOINs), so it must contain "GROUP BY".
$groupBy = "GROUP BY " . db_prefix() . "leads.type," . db_prefix() . "visitor_request.location,date(" . db_prefix() . "visitor_request.date_of_visit),seminar_address,whatsapp_notify,image";

// Matches signature: data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where,
//   $additionalSelect, $sGroupBy, $searchAs, $order_by_status, $search_column)
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $groupBy, [], 0, $search_column);

if (!empty($this->ci->input->post('excelStatus')) && $this->ci->input->post('excelStatus') == 1) {
    echo json_encode($result['rResult'], true);
    die;
}

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // FIXED: ternary wrapped in parentheses (concatenation binds tighter than ?:)
    $row['DT_RowClass'] = 'has-row-options ' . (!empty($aRow['color']) ? $aRow['color'] : 'pending');

    $id      = (int) $aRow['id'];
    $lead_id = (int) $aRow['lead_id'];

    // 1. Status
    // $row[] = $aRow['status'];

    // 2. Date Of Visit
    $row[] = !empty($aRow['date_of_visit']) ? ($aRow['date_of_visit']) : '';

    // 3. No of Visitors
    $row[] = $aRow['no_of_visitors'];

    // 4. Place of Visit
    $row[] = $aRow['location'];

    // 5. Visit Type
    $row[] = $aRow['visitor_type'];

    // 6. Lead type
    $row[] = isset($lead_data[$aRow['lead_type']]) ? $lead_data[$aRow['lead_type']]['name'] : '';

    // 7. Image — file upload (with preview link if already uploaded)
    $image_cell = '<input type="file" class="form-control visit-image-upload" '
        . 'name="visit_image_' . $id . '" id="visit_image_' . $id . '" '
        . 'accept="image/*" data-id="' . $id . '" data-lead="' . $lead_id . '">';
    if (!empty($aRow['visit_image'])) {
        $image_cell .= '<div class="mtop5"><a href="' . site_url($aRow['visit_image'])
            . '" target="_blank"><i class="fa fa-eye"></i> View</a></div>';
    }
    $row[] = $image_cell;

    // 8. Address — editable text
    $row[] = '<textarea  style="width:300px" class="form-control visit-address-text" rows="2" '
        . 'name="visit_address_' . $id . '" id="visit_address_' . $id . '" '
        . 'data-id="' . $id . '" data-lead="' . $lead_id . '">'
        . e($aRow['seminar_address'] ?? '') . '</textarea>';

    // 9. Whatsapp Notification date & time
    $wa_value = !empty($aRow['whatsapp_notify'])
        ? date('Y-m-d\TH:i', strtotime($aRow['whatsapp_notify'])) : '';
    $row[] = '<input type="datetime-local" class="form-control whatsapp-notify-datetime" '
        . 'name="whatsapp_notify_' . $id . '" id="whatsapp_notify_' . $id . '" '
        . 'value="' . $wa_value . '" data-id="' . $id . '">';

    // 10. Action
    $action ='';
    if($aRow['whatsapp_notify'] >= date('Y-m-d H:i:s') || $aRow["date_of_visit"] >= date('Y-m-d H:i:s') ){
    $action  = '<div class="row-options">';
$action .= '<a href="javascript:void(0)"
    onclick=\'saveSeminar_Data(
        '.$id.',
        '.json_encode($aRow["location_id"]).',
        '.json_encode($aRow["type_id"]).',
        '.json_encode($aRow["lead_type"]).',
        '.json_encode($aRow["date_of_visit"]).'
    )\'
    class="btn btn-default btn-icon btn-xs save-visit-row">
    <i class="fa fa-save"></i>
</a>';
}
    // if ($has_permission_delete) {
    //     $action .= '<a href="' . admin_url('leads/delete_visit/' . $id) . '" '
    //         . 'class="btn btn-danger btn-icon btn-xs _delete"><i class="fa fa-trash"></i></a>';
    // }
    $action .= '</div>';
    $row[] = $action;

    $output['aaData'][] = $row;
}