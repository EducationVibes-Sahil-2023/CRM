<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('app_admin_head', 'leads_app_admin_head_data');

function leads_app_admin_head_data()
{
?>
    <script>
        var leadUniqueValidationFields = <?php echo json_decode(json_encode(get_option('lead_unique_validation'))); ?>;
        var leadAttachmentsDropzone;
    </script>
<?php
}

/**
 * Check if the user is lead creator
 * @since  Version 1.0.4
 * @param  mixed  $leadid leadid
 * @param  mixed  $staff_id staff id (Optional)
 * @return boolean
 */

function is_lead_creator($lead_id, $staff_id = '')
{
    if (!is_numeric($staff_id)) {
        $staff_id = get_staff_user_id();
    }

    return total_rows(db_prefix() . 'leads', [
        'addedfrom' => $staff_id,
        'id'        => $lead_id,
    ]) > 0;
}

/**
 * Lead consent URL
 * @param  mixed $id lead id
 * @return string
 */
function lead_consent_url($id)
{
    return site_url('consent/l/' . get_lead_hash($id));
}

/**
 * Lead public form URL
 * @param  mixed $id lead id
 * @return string
 */
function leads_public_url($id)
{
    return site_url('forms/l/' . get_lead_hash($id));
}

/**
 * Get and generate lead hash if don't exists.
 * @param  mixed $id  lead id
 * @return string
 */
function get_lead_hash($id)
{
    $CI   = &get_instance();
    $hash = '';

    $CI->db->select('hash');
    $CI->db->where('id', $id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();
    if ($lead) {
        $hash = $lead->hash;
        if (empty($hash)) {
            $hash = app_generate_hash() . '-' . app_generate_hash();
            $CI->db->where('id', $id);
            $CI->db->update(db_prefix() . 'leads', ['hash' => $hash]);
        }
    }

    return $hash;
}

/**
 * Get leads summary
 * @return array
 */
function get_leads_summary()
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(addedfrom = ' . get_staff_user_id() . ' OR assigned=' . get_staff_user_id() . ' OR is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    /*    $statuses[] = [
        'junk'  => true,
        'name'  => _l('junk_leads'),
        'color' => '',
    ];*/

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        // return $query;

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
        // print_r($where);die;
    }

    foreach ($statuses as $status) {
        $sql .= ' SELECT COUNT(*) as total';
        $sql .= ' FROM ' . db_prefix() . 'leads';

        if (isset($status['lost'])) {
            $sql .= ' WHERE lost=1';
        } elseif (isset($status['junk'])) {
            $sql .= ' WHERE junk=1';
        } else {
            $sql .= ' WHERE status=' . $status['id'];
        }
        if (!$has_permission_view) {
            $sql .= ' AND ' . $whereNoViewPermission;
        }
        if ($role == 3) {
            $sql .= $tids;
        }
        $sql .= ' UNION ALL ';
        $sql = trim($sql);
    }
    //print_r($sql);die;
    $result = [];

    // Remove the last UNION ALL
    $sql    = substr($sql, 0, -10);
    $result = $CI->db->query($sql)->result();

    // if (!$has_permission_view) {
    //     $CI->db->where($whereNoViewPermission);
    // }

    $total_leads = $CI->db->count_all_results(db_prefix() . 'leads');

    $totalLeads = 0;
    foreach ($statuses as $key => $status) {
        if (isset($status['lost']) || isset($status['junk'])) {
            $statuses[$key]['percent'] = ($total_leads > 0 ? number_format(($result[$key]->total * 100) / $total_leads, 2) : 0);
        }
        $statuses[$key]['total'] = $result[$key]->total;
        if ($status["isdefault"] == 0) {

            $totalLeads += $result[$key]->total;
        } else {
            $statuses[$key]['total'] = 0;
        }
    }
    $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

    return $statuses;
}

// function get_leads_summary_filter($params)
// {
//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $statuses = $CI->leads_model->get_status();


//     $totalStatuses         = count($statuses);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         // $this->load->database();
//         $sid = get_staff_user_id(); //48;//get_staff_user_id();

//         $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
//         $CI->db->close();
//         $CI->db->initialize();
//         $idsarr = array_column($teamids, 'staffid');
//         $sids = implode(",", $idsarr);
//         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }
//     }



//     $sql .= '  SELECT COUNT(DISTINCT(' . db_prefix() . 'leads.id)) as total ';

//     $sql .= ',(
//             SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//             FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//             WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//             AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
//             ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//             LIMIT 1
//         ) as lastcontact,
//         (
//             SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//             FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//             WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//             ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//             LIMIT 1
//         ) as lastupdatecontact ';

//     $sql .= ' FROM ' . db_prefix() . 'leads';

//     $sql .= ' LEFT join ' . db_prefix() . 'leads_status ON  ' . db_prefix() . 'leads.status=' . db_prefix() . 'leads_status.id ';

//     if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
//         $sql .= ' join ' . db_prefix() . 'customfieldsvalues ON  ' . db_prefix() . 'leads.id=' . db_prefix() . 'customfieldsvalues.relid ';
//     }
//     if (!empty($params['up_to_date'])) {

//         $up_from_date = $params['up_from_date'];
//         $up_to_date = $params['up_to_date'];
//         $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( " . db_prefix() . "leads.phonenumber = calls.contact )";
//     } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) | !empty($params['last_update_date'])) {
//         if (!empty($params['last_contact_date'])) {
//             $sql .= " left join " . db_prefix() . "calls_activity_logs as calls on ( " . db_prefix() . "leads.phonenumber = calls.contact  and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') )";
//         } else {
//             $sql .= " left join " . db_prefix() . "calls_activity_logs as calls on ( " . db_prefix() . "leads.phonenumber = calls.contact )";
//         }
//     }

//     if (!empty($params['followup_to_date'])) {
//         $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id ';
//     }

//     if (isset($status['lost'])) {
//         $sql .= ' WHERE lost=1';
//     } elseif (isset($status['junk'])) {
//         $sql .= ' WHERE junk=1';
//     } else {
//         $sql .= ' WHERE ' . db_prefix() . 'leads.status=' . $status['id'];
//     }
//     if (!$has_permission_view) {
//         $sql .= ' AND ' . $whereNoViewPermission;
//     }
//     if (!empty($params['assigned'])) {
//         $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
//         $sql .= $tids;
//     } else {
//         if ($role == 3) {
//             $sql .= $tids;
//         }
//     }


//     if (!empty($params['source'])) {
//         $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//     }

//     if (!empty($params['neet_score'])) {
//         $neet_range = explode("-", $params['neet_score']);

//         $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
//     }
//     if (!empty($params['lead_type'])) {
//         $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//     }

//     if (!empty($params['to_date'])) {
//         $from_date = $params['from_date'];
//         $to_date = $params['to_date'];
//         $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     }

//     if (!empty($params['up_to_date'])) {
//         $up_from_date = $params['up_from_date'];
//         $up_to_date = $params['up_to_date'];
//         $sql .= " AND  (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//             . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
//     }
//     if (!empty($params['followup_to_date'])) {
//         $followup_from_date = $params['followup_from_date'];
//         $followup_to_date = $params['followup_to_date'];
//         $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     }

//     if (!empty($params['assign_to_date'])) {
//         $assign_from_date = $params['assign_from_date'];
//         $assign_to_date = $params['assign_to_date'];
//         $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     }

//     if (!empty($params['fb_source'])) {
//         $facebook_source_name = $params['fb_source'];
//         $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//     }

//     if (!empty($params['google_source'])) {
//         $google_source_name = $params['google_source'];
//         $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//     }

//     $sql .= " GROUP BY " . db_prefix() . "leads.id ";

//     if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
//         $sql .= ' HAVING ';

//         if (!empty($params['last_contact_date'])) {
//             $last_contact_date = $params["last_contact_date"];
//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $sql .= "(lastcontact <= '" . $last_contact_date . "')";
//             } else {
//                 $sql .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
//             }

//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $sql .= ' AND ';
//             }
//         } else if (!empty($params['last_update_date'])) {
//             $last_contact_date = $params["last_update_date"];
//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $sql .= "(lastupdatecontact <= '" . $last_contact_date . "')";
//             } else {
//                 $sql .= "(lastupdatecontact <= '" . $last_contact_date . "' OR lastupdatecontact IS NULL)";
//             }

//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $sql .= ' AND ';
//             }
//         }


//         if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//             $min = $params['update_count_min'];
//             $max = $params['update_count_max'];
//             // $sql .= 'COUNT(' . db_prefix() . 'leads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//             $sql .= 'COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//         }
//     }

//     $sql .= " GROUP BY " . db_prefix() . "leads.status ORDER BY " . db_prefix() . "leads_status.statusorder";

//     $sql = trim($sql);


//     // $sql = 'SELECT COUNT(DISTINCT ' . db_prefix() . 'leads.id) AS total,
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) AS lastcontact,
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) AS lastupdatecontact

//     // $sql = 'SELECT ' . db_prefix() . 'leads_status.id as status_id,COUNT(DISTINCT ' . db_prefix() . 'leads.id) AS total';

//     // if (!empty($params['last_contact_date'])) {
//     //     $sql .= ',
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) AS lastcontact';
//     // }

//     // if (!empty($params['last_update_date'])) {
//     //     $sql .= ',
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) AS lastupdatecontact';
//     // }
//     // $sql .= ' FROM ' . db_prefix() . 'leads
//     // Right JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads.status = ' . db_prefix() . 'leads_status.id';
//     // if (!empty($_POST["status"])) {
//     //     $sql .= ' AND ' . db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ') ';
//     // }
//     // if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
//     //     $sql .= ' JOIN ' . db_prefix() . 'customfieldsvalues ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'customfieldsvalues.relid';
//     // }
//     // if (!empty($params['up_to_date'])) {
//     //     $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
//     // } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) || !empty($params['last_update_date'])) {
//     //     $sql .= ' LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
//     //     if (!empty($params['last_contact_date'])) {
//     //         $sql .= ' AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\')';
//     //     }
//     // }

//     // if (!empty($params['followup_to_date'])) {
//     //     $sql .= ' JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id';
//     // }

//     // if (isset($status['lost'])) {
//     //     $sql .= ' WHERE lost = 1';
//     // } elseif (isset($status['junk'])) {
//     //     $sql .= ' WHERE junk = 1';
//     // } else {
//     //     $sql .= ' WHERE ' . db_prefix() . 'leads_status.id IS NOT NULL';
//     // }

//     // if (!$has_permission_view) {
//     //     $sql .= ' AND ' . $whereNoViewPermission;
//     // }

//     // if (!empty($params['assigned'])) {
//     //     $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
//     // } else if ($role == 3) {
//     //     $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
//     // }

//     // if (!empty($params['source'])) {
//     //     $sql .= ' AND source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
//     // }

//     // if (!empty($params['neet_score'])) {
//     //     $neet_range = explode('-', $params['neet_score']);
//     //     $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value != ""';
//     // }

//     // if (!empty($params['lead_type'])) {
//     //     $sql .= ' AND type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
//     // }

//     // if (!empty($params['to_date'])) {
//     //     $from_date = $params['from_date'];
//     //     $to_date = $params['to_date'];
//     //     $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     // }

//     // if (!empty($params['up_to_date'])) {
//     //     $up_from_date = $params['up_from_date'];
//     //     $up_to_date = $params['up_to_date'];
//     //     $sql .= ' AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), \'%Y-%m-%d\') BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//     // }

//     // if (!empty($params['followup_to_date'])) {
//     //     $followup_from_date = $params['followup_from_date'];
//     //     $followup_to_date = $params['followup_to_date'];
//     //     $sql .= ' AND DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     // }

//     // if (!empty($params['assign_to_date'])) {
//     //     $assign_from_date = $params['assign_from_date'];
//     //     $assign_to_date = $params['assign_to_date'];
//     //     $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     // }

//     // if (!empty($params['fb_source'])) {
//     //     $facebook_source_name = $params['fb_source'];
//     //     $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//     // }

//     // if (!empty($params['google_source'])) {
//     //     $google_source_name = $params['google_source'];
//     //     $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//     // }

//     // $sql .= ' GROUP BY ' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.id ';

//     // // $sql .= ' GROUP BY ' . db_prefix() . 'leads.id';

//     // if (!empty($params['last_contact_date']) || (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
//     //     $sql .= ' HAVING';

//     //     if (!empty($params['last_contact_date'])) {
//     //         $last_contact_date = $params['last_contact_date'];
//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//     //             $sql .= ' (lastcontact <= "' . $last_contact_date . '")';
//     //         } else {
//     //             $sql .= ' (lastcontact <= "' . $last_contact_date . '" OR lastcontact IS NULL)';
//     //         }
//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//     //             $sql .= ' AND ';
//     //         }
//     //     } else if (!empty($params['last_update_date'])) {
//     //         $last_update_date = $params['last_update_date'];
//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//     //             $sql .= ' (lastupdatecontact <= "' . $last_update_date . '")';
//     //         } else {
//     //             $sql .= ' (lastupdatecontact <= "' . $last_update_date . '" OR lastupdatecontact IS NULL)';
//     //         }
//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//     //             $sql .= ' AND ';
//     //         }
//     //     }

//     //     if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//     //         $min = $params['update_count_min'];
//     //         $max = $params['update_count_max'];
//     //         $sql .= ' COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//     //     }
//     // }

//     // $sql .=  ' ORDER BY ' . db_prefix() . 'leads_status.statusorder ';


//     // $result = [];

//     // $result = $CI->db->query($sql)->result();

//     // // Extract the status_id values from the result array
//     // $status_ids = array_map(function ($item) {
//     //     return $item->status_id;
//     // }, $result);

//     // // Count the occurrences of each status_id
//     // $result = array_count_values($status_ids);

//     // // if (!empty($result)) {
//     // //     $result = array_column($result, "total", "status_id");
//     // // }
//     $totalLeads = 0;


//     foreach ($statuses as $key => $status) {
//         $statuses[$key]['total'] = 0;
//         // echo $status["id"];
//         if (!empty($_POST["status"])) {
//             if (in_array($status["id"], $_POST["status"])) {
//                 $statuses[$key]['total'] = !empty($result[$key]->total) ? $result[$key]->total : 0;
//                 // $statuses[$key]['total'] = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
//             } else {
//                 $statuses[$key]['total'] = 0;
//             }
//         } else {
//             $statuses[$key]['total']  = !empty($result[$key]->total) ? $result[$key]->total : 0;
//             // $statuses[$key]['total']  = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
//         }

//         $totalLeads += !empty($statuses[$key]['total']) ? $statuses[$key]['total'] : 0;
//     }


//     $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

//     return $statuses;
// }


// function get_leads_summary_filter_report($params)
// {
//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $statuses = $CI->leads_model->get_status();


//     $totalStatuses         = count($statuses);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         // $this->load->database();
//         $sid = get_staff_user_id(); //48;//get_staff_user_id();

//         $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
//         $CI->db->close();
//         $CI->db->initialize();
//         $idsarr = array_column($teamids, 'staffid');
//         $sids = implode(",", $idsarr);
//         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }
//     }


//     // $sql .= '  SELECT COUNT(DISTINCT(' . db_prefix() . 'leads.id)) as total ';

//     // $sql .= ',(
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) as lastcontact,
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) as lastupdatecontact ';

//     // $sql .= ' FROM ' . db_prefix() . 'leads';

//     // $sql .= ' LEFT join ' . db_prefix() . 'leads_status ON  ' . db_prefix() . 'leads.status=' . db_prefix() . 'leads_status.id ';

//     // if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
//     //     $sql .= ' join ' . db_prefix() . 'customfieldsvalues ON  ' . db_prefix() . 'leads.id=' . db_prefix() . 'customfieldsvalues.relid ';
//     // }
//     // if (!empty($params['up_to_date'])) {

//     //     $up_from_date = $params['up_from_date'];
//     //     $up_to_date = $params['up_to_date'];
//     //     $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( " . db_prefix() . "leads.phonenumber = calls.contact )";
//     // } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) | !empty($params['last_update_date'])) {
//     //     if (!empty($params['last_contact_date'])) {
//     //         $sql .= " left join " . db_prefix() . "calls_activity_logs as calls on ( " . db_prefix() . "leads.phonenumber = calls.contact  and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') )";
//     //     } else {
//     //         $sql .= " left join " . db_prefix() . "calls_activity_logs as calls on ( " . db_prefix() . "leads.phonenumber = calls.contact )";
//     //     }
//     // }

//     // if (!empty($params['followup_to_date'])) {
//     //     $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id ';
//     // }

//     // if (isset($status['lost'])) {
//     //     $sql .= ' WHERE lost=1';
//     // } elseif (isset($status['junk'])) {
//     //     $sql .= ' WHERE junk=1';
//     // } else {
//     //     $sql .= ' WHERE ' . db_prefix() . 'leads.status=' . $status['id'];
//     // }
//     // if (!$has_permission_view) {
//     //     $sql .= ' AND ' . $whereNoViewPermission;
//     // }
//     // if (!empty($params['assigned'])) {
//     //     $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
//     //     $sql .= $tids;
//     // } else {
//     //     if ($role == 3) {
//     //         $sql .= $tids;
//     //     }
//     // }


//     // if (!empty($params['source'])) {
//     //     $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//     // }

//     // if (!empty($params['neet_score'])) {
//     //     $neet_range = explode("-", $params['neet_score']);

//     //     $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
//     // }
//     // if (!empty($params['lead_type'])) {
//     //     $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//     // }

//     // if (!empty($params['to_date'])) {
//     //     $from_date = $params['from_date'];
//     //     $to_date = $params['to_date'];
//     //     $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     // }

//     // if (!empty($params['up_to_date'])) {
//     //     $up_from_date = $params['up_from_date'];
//     //     $up_to_date = $params['up_to_date'];
//     //     $sql .= " AND  (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//     //         . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
//     // }
//     // if (!empty($params['followup_to_date'])) {
//     //     $followup_from_date = $params['followup_from_date'];
//     //     $followup_to_date = $params['followup_to_date'];
//     //     $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     // }

//     // if (!empty($params['assign_to_date'])) {
//     //     $assign_from_date = $params['assign_from_date'];
//     //     $assign_to_date = $params['assign_to_date'];
//     //     $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     // }

//     // if (!empty($params['fb_source'])) {
//     //     $facebook_source_name = $params['fb_source'];
//     //     $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//     // }

//     // if (!empty($params['google_source'])) {
//     //     $google_source_name = $params['google_source'];
//     //     $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//     // }

//     // $sql .= " GROUP BY " . db_prefix() . "leads.id ";

//     // if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
//     //     $sql .= ' HAVING ';

//     //     if (!empty($params['last_contact_date'])) {
//     //         $last_contact_date = $params["last_contact_date"];
//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//     //             $sql .= "(lastcontact <= '" . $last_contact_date . "')";
//     //         } else {
//     //             $sql .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
//     //         }

//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//     //             $sql .= ' AND ';
//     //         }
//     //     } else if (!empty($params['last_update_date'])) {
//     //         $last_contact_date = $params["last_update_date"];
//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//     //             $sql .= "(lastupdatecontact <= '" . $last_contact_date . "')";
//     //         } else {
//     //             $sql .= "(lastupdatecontact <= '" . $last_contact_date . "' OR lastupdatecontact IS NULL)";
//     //         }

//     //         if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//     //             $sql .= ' AND ';
//     //         }
//     //     }


//     //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//     //         $min = $params['update_count_min'];
//     //         $max = $params['update_count_max'];
//     //         // $sql .= 'COUNT(' . db_prefix() . 'leads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//     //         $sql .= 'COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//     //     }
//     // }

//     // $sql .= " GROUP BY " . db_prefix() . "leads.status ORDER BY " . db_prefix() . "leads_status.statusorder";

//     // $sql = trim($sql);


//     // $sql = 'SELECT COUNT(DISTINCT ' . db_prefix() . 'leads.id) AS total,
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) AS lastcontact,
//     //     (
//     //         SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//     //         FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//     //         WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//     //         ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//     //         LIMIT 1
//     //     ) AS lastupdatecontact

//     $sql = 'SELECT ' . db_prefix() . 'leads.assigned,' . db_prefix() . 'leads_status.id as status_id,COUNT(DISTINCT ' . db_prefix() . 'leads.id) AS total';

//     if (!empty($params['last_contact_date'])) {
//         $sql .= ',
//         (
//             SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//             FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//             WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//             AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
//             ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//             LIMIT 1
//         ) AS lastcontact';
//     }

//     if (!empty($params['last_update_date'])) {
//         $sql .= ',
//         (
//             SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
//             FROM ' . db_prefix() . 'calls_activity_logs AS calls 
//             WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
//             ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
//             LIMIT 1
//         ) AS lastupdatecontact';
//     }
//     $sql .= ' FROM ' . db_prefix() . 'leads
//     Right JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads.status = ' . db_prefix() . 'leads_status.id';
//     if (!empty($_POST["status"])) {
//         $sql .= ' AND ' . db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ') ';
//     }
//     if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
//         $sql .= ' JOIN ' . db_prefix() . 'customfieldsvalues ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'customfieldsvalues.relid';
//     }
//     if (!empty($params['up_to_date'])) {
//         $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
//     } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) || !empty($params['last_update_date'])) {
//         $sql .= ' LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
//         if (!empty($params['last_contact_date'])) {
//             $sql .= ' AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\')';
//         }
//     }

//     if (!empty($params['followup_to_date'])) {
//         $sql .= ' JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id';
//     }

//     if (isset($status['lost'])) {
//         $sql .= ' WHERE lost = 1';
//     } elseif (isset($status['junk'])) {
//         $sql .= ' WHERE junk = 1';
//     } else {
//         $sql .= ' WHERE ' . db_prefix() . 'leads_status.id IS NOT NULL';
//     }

//     if (!$has_permission_view) {
//         $sql .= ' AND ' . $whereNoViewPermission;
//     }

//     if (!empty($params['assigned'])) {
//         $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
//     } else if ($role == 3) {
//         $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
//     }

//     if (!empty($params['source'])) {
//         $sql .= ' AND source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
//     }

//     if (!empty($params['neet_score'])) {
//         $neet_range = explode('-', $params['neet_score']);
//         $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value != ""';
//     }

//     if (!empty($params['lead_type'])) {
//         $sql .= ' AND type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
//     }

//     if (!empty($params['to_date'])) {
//         $from_date = $params['from_date'];
//         $to_date = $params['to_date'];
//         $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     }

//     if (!empty($params['up_to_date'])) {
//         $up_from_date = $params['up_from_date'];
//         $up_to_date = $params['up_to_date'];
//         $sql .= ' AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), \'%Y-%m-%d\') BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//     }

//     if (!empty($params['followup_to_date'])) {
//         $followup_from_date = $params['followup_from_date'];
//         $followup_to_date = $params['followup_to_date'];
//         $sql .= ' AND DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     }

//     if (!empty($params['assign_to_date'])) {
//         $assign_from_date = $params['assign_from_date'];
//         $assign_to_date = $params['assign_to_date'];
//         $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     }

//     if (!empty($params['fb_source'])) {
//         $facebook_source_name = $params['fb_source'];
//         $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//     }

//     if (!empty($params['google_source'])) {
//         $google_source_name = $params['google_source'];
//         $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//     }

//     $sql .= ' GROUP BY ' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.assigned ';

//     // $sql .= ' GROUP BY ' . db_prefix() . 'leads.id';

//     if (!empty($params['last_contact_date']) || (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
//         $sql .= ' HAVING';

//         if (!empty($params['last_contact_date'])) {
//             $last_contact_date = $params['last_contact_date'];
//             if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//                 $sql .= ' (lastcontact <= "' . $last_contact_date . '")';
//             } else {
//                 $sql .= ' (lastcontact <= "' . $last_contact_date . '" OR lastcontact IS NULL)';
//             }
//             if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//                 $sql .= ' AND ';
//             }
//         } else if (!empty($params['last_update_date'])) {
//             $last_update_date = $params['last_update_date'];
//             if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//                 $sql .= ' (lastupdatecontact <= "' . $last_update_date . '")';
//             } else {
//                 $sql .= ' (lastupdatecontact <= "' . $last_update_date . '" OR lastupdatecontact IS NULL)';
//             }
//             if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//                 $sql .= ' AND ';
//             }
//         }

//         if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
//             $min = $params['update_count_min'];
//             $max = $params['update_count_max'];
//             $sql .= ' COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//         }
//     }

//     $sql .=  ' ORDER BY ' . db_prefix() . 'leads_status.statusorder ';


//     $result = [];

//     $result = $CI->db->query($sql)->result();

//     // echo "<pre>";
//     // print_r($result);
//     $groupedData = array();
//     foreach ($result as $item) {
//         $assigned = $item->assigned;
//         if (!isset($groupedData[$assigned])) {
//             $groupedData[$assigned] = array();
//         }
//         $groupedData[$assigned][] = $item;
//     }
//     $groupedData_array = array();
//     foreach ($groupedData as $ass_key => $groupedData_) {
//         // Extract the status_id values from the result array
//         $status_ids = array_map(function ($item) {
//             return $item->status_id;
//         }, $groupedData_);

//         // Count the occurrences of each status_id
//         $result = array_count_values($status_ids);

//         // if (!empty($result)) {
//         //     $result = array_column($result, "total", "status_id");
//         // }
//         $totalLeads = 0;


//         foreach ($statuses as $key => $status) {
//             $statuses[$key]['total'] = 0;
//             // echo $status["id"];
//             if (!empty($_POST["status"])) {
//                 if (in_array($status["id"], $_POST["status"])) {
//                     $statuses[$key]['total'] = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
//                 } else {
//                     $statuses[$key]['total'] = 0;
//                 }
//             } else {
//                 $statuses[$key]['total']  = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
//             }

//             $totalLeads += !empty($statuses[$key]['total']) ? $statuses[$key]['total'] : 0;
//         }


//         $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);
//         $groupedData_array[$ass_key] = $statuses;
//     }

//     return $groupedData_array;
// }


// function get_leads_report_($params, $export = 0)
// {
//     $params['date_type'] = !empty($params['date_type']) ? trim(strtolower($params['date_type'])) : '';

//     $CI = &get_instance();
//     $sql = "SELECT ";
//     if (!empty($params['date_type'])) {
//         if (!empty($params['date_type'])) {
//             if ($params['date_type'] == "daily") {
//                 $sql .= "DATE(l.dateadded) as dateadded, ";
//             } elseif ($params['date_type'] == "week") {
//                 $sql .= "YEARWEEK(l.dateadded, 1) as dateadded, ";
//             } elseif ($params['date_type'] == "month") {
//                 $sql .= "DATE_FORMAT(l.dateadded, '%Y - %M') as dateadded, ";
//             } elseif ($params['date_type'] == "year") {
//                 $sql .= "YEAR(l.dateadded) as dateadded, ";
//             }
//         }

//         if (!empty($export) && $export == 1) {
//             $sql .= " CONCAT(staff.firstname,' ',staff.lastname) full_name,l.assigned,";
//         }

//         $sql .= "COUNT(DISTINCT l.id) as count FROM " . db_prefix() . "leads l ";

//         if (!empty($params['up_to_date'])) {
//             $sql .= "JOIN " . db_prefix() . "calls_activity_logs as calls ON (l.phonenumber = calls.contact) ";
//         }
//         if (!empty($params['department']) || !empty($params['location'])) {
//             $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
//         } else  if (!empty($export) && $export == 1) {
//             $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
//         }

//         $sql .= "WHERE 1=1 ";

//         if (!empty($params['source'])) {
//             $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//         }
//         if (!empty($params['department'])) {
//             $sql .= ' AND staff.department in (' . implode(",", $CI->db->escape_str($params['department'])) . ')';
//         }
//         if (!empty($params['location'])) {
//             $sql .= ' AND staff.office_location in (' . implode(",", $CI->db->escape_str($params['location'])) . ')';
//         }

//         if (!empty($params['to_date'])) {
//             $from_date = $params['from_date'];
//             $to_date = $params['to_date'];
//             $sql .= 'AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
//         }

//         if (!empty($params['lead_type'])) {
//             $sql .= 'AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//         }

//         if (!empty($params['up_to_date'])) {
//             $up_from_date = $params['up_from_date'];
//             $up_to_date = $params['up_to_date'];
//             $sql .= "AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//                 . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "' ";
//         }

//         if (!empty($params['assign_to_date'])) {
//             $assign_from_date = $params['assign_from_date'];
//             $assign_to_date = $params['assign_to_date'];
//             $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//         }
//         if (!empty($params['fb_source'])) {
//             $facebook_source_name = $params['fb_source'];
//             $sql .= 'AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\') ';
//         }

//         if (!empty($params['google_source'])) {
//             $google_source_name = $params['google_source'];
//             $sql .= 'AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND '
//                 . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\') ';
//         }

//         if (!empty($params['status'])) {
//             $sql .= 'AND l.status IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['status'])) . ') ';
//         }

//         if (!empty($params['assigned'])) {
//             $sql .= 'AND l.assigned IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['assigned'])) . ') ';
//         }

//         if (!empty($params['date_type'])) {
//             if ($params['date_type'] == "daily") {
//                 $sql .= "GROUP BY DATE(l.dateadded) ";
//             } elseif ($params['date_type'] == "week") {
//                 $sql .= "GROUP BY YEARWEEK(l.dateadded, 1) ";
//             } elseif ($params['date_type'] == "month") {
//                 $sql .= "GROUP BY DATE_FORMAT(l.dateadded, '%Y - %m') ";
//             } elseif ($params['date_type'] == "year") {
//                 $sql .= "GROUP BY YEAR(l.dateadded) ";
//             }

//             if (!empty($export) && $export == 1) {
//                 $sql .= ",l.assigned";
//             }
//         }

//         if (!empty($export) && $export == 1) {
//         } else {
//             $sql .= " LIMIT 15 ";
//         }

//         return $result = $CI->db->query($sql)->result();
//     } else {
//         return [];
//     }
// }

// function get_leads_report_conversion($params)
// {
//     $params['date_type'] = !empty($params['date_type']) ? trim(strtolower($params['date_type'])) : '';

//     $CI = &get_instance();
//     $sql = "SELECT 
//         dateadded,
//         GROUP_CONCAT(CONCAT(conversion_type_name, ': ', conversion_count) SEPARATOR ', ') as conversion_counts
//     FROM (
//         SELECT ";

//     if (!empty($params['date_type'])) {
//         if ($params['date_type'] == "daily") {
//             $sql .= "DATE(l.dateadded) as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
//         } elseif ($params['date_type'] == "week") {
//             $sql .= "YEARWEEK(l.dateadded, 1) as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
//         } elseif ($params['date_type'] == "month") {
//             $sql .= "DATE_FORMAT(l.dateadded, '%Y - %M') as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
//         } elseif ($params['date_type'] == "year") {
//             $sql .= "YEAR(l.dateadded) as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
//         }

//         $sql .= "FROM " . db_prefix() . "leads l ";

//         if (!empty($params['up_to_date'])) {
//             $sql .= "JOIN " . db_prefix() . "calls_activity_logs as calls ON (l.phonenumber = calls.contact) ";
//         }

//         if (!empty($params['department']) || !empty($params['location'])) {
//             $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
//         }

//         $sql .= "JOIN " . db_prefix() . "leads_status as status ON (status.id = l.status) ";
//         $sql .= "JOIN " . db_prefix() . "lead_conversion_type as c ON (c.id = status.conversion_type AND c.status = 1) ";
//         $sql .= "WHERE 1=1 ";

//         if (!empty($params['source'])) {
//             $sql .= ' AND source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//         }

//         if (!empty($params['department'])) {
//             $sql .= ' AND staff.department IN (' . implode(",", $CI->db->escape_str($params['department'])) . ')';
//         }

//         if (!empty($params['location'])) {
//             $sql .= ' AND staff.office_location IN (' . implode(",", $CI->db->escape_str($params['location'])) . ')';
//         }

//         if (!empty($params['to_date'])) {
//             $from_date = $params['from_date'];
//             $to_date = $params['to_date'];
//             $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
//         }

//         if (!empty($params['lead_type'])) {
//             $sql .= 'AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//         }

//         if (!empty($params['up_to_date'])) {
//             $up_from_date = $params['up_from_date'];
//             $up_to_date = $params['up_to_date'];
//             $sql .= " AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//                 . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "' ";
//         }
//         if (!empty($params['assign_to_date'])) {
//             $assign_from_date = $params['assign_from_date'];
//             $assign_to_date = $params['assign_to_date'];
//             $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//         }
//         if (!empty($params['fb_source'])) {
//             $facebook_source_name = $params['fb_source'];
//             $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\') ';
//         }

//         if (!empty($params['google_source'])) {
//             $google_source_name = $params['google_source'];
//             $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND '
//                 . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\') ';
//         }

//         if (!empty($params['status'])) {
//             $sql .= ' AND l.status IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['status'])) . ') ';
//         }

//         if (!empty($params['assigned'])) {
//             $sql .= ' AND l.assigned IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['assigned'])) . ') ';
//         }

//         if ($params['date_type'] == "daily") {
//             $sql .= "GROUP BY DATE(l.dateadded), c.name ORDER BY DATE(l.dateadded) ASC";
//         } elseif ($params['date_type'] == "week") {
//             $sql .= "GROUP BY YEARWEEK(l.dateadded, 1), c.name ORDER BY YEARWEEK(l.dateadded, 1) ASC";
//         } elseif ($params['date_type'] == "month") {
//             $sql .= "GROUP BY DATE_FORMAT(l.dateadded, '%Y - %M'), c.name ORDER BY DATE_FORMAT(l.dateadded, '%Y - %M') ASC";
//         } elseif ($params['date_type'] == "year") {
//             $sql .= "GROUP BY YEAR(l.dateadded), c.name ORDER BY YEAR(l.dateadded) ASC";
//         }

//         // Close the subquery and group by the final dateadded column
//         $sql .= ") as conversion_counts_subquery GROUP BY dateadded ORDER BY dateadded ASC";
//         $sql .= " LIMIT 15 ";
//         return $result = $CI->db->query($sql)->result();
//     } else {
//         return [];
//     }
// }

// function get_leads_report_marketing($params)
// {
//     $params['date_type'] = !empty($params['date_type']) ? trim(strtolower($params['date_type'])) : '';

//     $CI = &get_instance();
//     $sql = "SELECT 
//         dateadded,
//         GROUP_CONCAT(CONCAT(marketing_type_name, ': ', marketing_count) SEPARATOR ', ') as marketing_count
//     FROM (
//         SELECT ";

//     if (!empty($params['date_type'])) {
//         if ($params['date_type'] == "daily") {
//             $sql .= "DATE(l.dateadded) as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
//         } elseif ($params['date_type'] == "week") {
//             $sql .= "YEARWEEK(l.dateadded, 1) as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
//         } elseif ($params['date_type'] == "month") {
//             $sql .= "DATE_FORMAT(l.dateadded, '%Y - %M') as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
//         } elseif ($params['date_type'] == "year") {
//             $sql .= "YEAR(l.dateadded) as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
//         }

//         $sql .= "FROM " . db_prefix() . "leads l ";

//         if (!empty($params['up_to_date'])) {
//             $sql .= "JOIN " . db_prefix() . "calls_activity_logs as calls ON (l.phonenumber = calls.contact) ";
//         }

//         if (!empty($params['department']) || !empty($params['location'])) {
//             $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
//         }

//         $sql .= "JOIN " . db_prefix() . "leads_sources as source ON (source.id = l.source) ";
//         $sql .= "JOIN " . db_prefix() . "lead_marketing as m ON (m.id = source.marketing_type AND m.status = 1) ";
//         $sql .= "WHERE 1=1 ";

//         if (!empty($params['source'])) {
//             $sql .= ' AND source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//         }

//         if (!empty($params['department'])) {
//             $sql .= ' AND staff.department IN (' . implode(",", $CI->db->escape_str($params['department'])) . ')';
//         }

//         if (!empty($params['location'])) {
//             $sql .= ' AND staff.office_location IN (' . implode(",", $CI->db->escape_str($params['location'])) . ')';
//         }

//         if (!empty($params['to_date'])) {
//             $from_date = $params['from_date'];
//             $to_date = $params['to_date'];
//             $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
//         }

//         if (!empty($params['lead_type'])) {
//             $sql .= 'AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//         }

//         if (!empty($params['up_to_date'])) {
//             $up_from_date = $params['up_from_date'];
//             $up_to_date = $params['up_to_date'];
//             $sql .= " AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//                 . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "' ";
//         }
//         if (!empty($params['assign_to_date'])) {
//             $assign_from_date = $params['assign_from_date'];
//             $assign_to_date = $params['assign_to_date'];
//             $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//         }

//         if (!empty($params['fb_source'])) {
//             $facebook_source_name = $params['fb_source'];
//             $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\') ';
//         }

//         if (!empty($params['google_source'])) {
//             $google_source_name = $params['google_source'];
//             $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND '
//                 . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\') ';
//         }

//         if (!empty($params['status'])) {
//             $sql .= ' AND l.status IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['status'])) . ') ';
//         }

//         if (!empty($params['assigned'])) {
//             $sql .= ' AND l.assigned IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['assigned'])) . ') ';
//         }

//         if ($params['date_type'] == "daily") {
//             $sql .= "GROUP BY DATE(l.dateadded), m.name ";
//         } elseif ($params['date_type'] == "week") {
//             $sql .= "GROUP BY YEARWEEK(l.dateadded, 1), m.name ";
//         } elseif ($params['date_type'] == "month") {
//             $sql .= "GROUP BY DATE_FORMAT(l.dateadded, '%Y - %M'), m.name ";
//         } elseif ($params['date_type'] == "year") {
//             $sql .= "GROUP BY YEAR(l.dateadded), m.name ";
//         }

//         $sql .= ") as conversion_counts_subquery GROUP BY dateadded ORDER BY dateadded ASC";
//         $sql .= " LIMIT 15 ";
//         return $result = $CI->db->query($sql)->result();
//     } else {
//         return [];
//     }
// }

// function get_status_summary_filter($params)
// {
//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $sources = $CI->leads_model->get_source();
//     $totalSource         = count($sources);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '( l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         $sid = get_staff_user_id();
//         $query = [];
//         $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
//         $staff_ids = implode(",", array_column($query_sql, 'staffid'));

//         if (!empty($staff_ids)) {
//             $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
//         }
//         $idsarr = array_column($query, 'staffid');
//         $sids = implode(",", $idsarr);

//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }

//         //         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//     }

//     foreach ($sources as $source) {
//         $sql .= ' SELECT COUNT(DISTINCT(l.id)) as total,c.id conversion_id ';
//         $sql .= ' FROM ' . db_prefix() . 'leads l  inner join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status  inner join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

//         if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
//             $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
//         }
//         if (!empty($params['up_to_date'])) {
//             // $up_from_date_join = $params['up_from_date'];
//             // $up_to_date_join = $params['up_to_date'];
//             // $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';

//             $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact )";
//         }
//         if (!empty($params['followup_to_date'])) {
//             $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
//         }

//         // if (isset($status['lost'])) {
//         //     $sql .= ' WHERE lost=1';
//         // } elseif (isset($status['junk'])) {
//         //     $sql .= ' WHERE junk=1';
//         // } else {
//         //     $sql .= ' WHERE status=' . $status['id'];
//         // }

//         $sql .= ' WHERE source=' . $source['id'];

//         if (!$has_permission_view) {
//             $sql .= ' AND ' . $whereNoViewPermission;
//         }
//         if (!empty($params['assigned'])) {
//             // $tids = " AND assigned = " . $params['assigned'];
//             $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
//             $sql .= $tids;
//         } else {
//             if ($role == 3) {
//                 $sql .= $tids;
//             }
//         }

//         if (!empty($params['status'])) {
//             $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
//         }




//         // if (!empty($params['source'])) {
//         //     $sql .= ' AND source =' . $CI->db->escape_str($params['source']);
//         // }

//         /*if (isset($params['course'])) {
//             $sql .= 'AND tblcustomfieldsvalues.value ='.$params['course'];
//         }


// 		if (isset($params['degree'])) {
//             $sql .= 'AND tblcustomfieldsvalues.value ='.$params['degree'];
//         }*/

//         // if (!empty($params['lead_type'])) {
//         //     $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//         // }
//         if (!empty($params['lead_type'])) {
//             $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//             // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//         }
//         if (!empty($params['to_date'])) {
//             $from_date = $params['from_date'];
//             $to_date = $params['to_date'];
//             $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//         }
//         if (!empty($params['up_to_date'])) {
//             $up_from_date = $params['up_from_date'];
//             $up_to_date = $params['up_to_date'];
//             //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//             // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

//             $sql .= " AND  (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//                 . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
//         }
//         if (!empty($params['followup_to_date'])) {
//             $followup_from_date = $params['followup_from_date'];
//             $followup_to_date = $params['followup_to_date'];
//             $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//         }

//         if (!empty($params['assign_to_date'])) {
//             $assign_from_date = $params['assign_from_date'];
//             $assign_to_date = $params['assign_to_date'];
//             $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//         }
//         if (!empty($params['fb_source'])) {
//             $facebook_source_name = $params['fb_source'];
//             $sql .= ' AND  l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//         }

//         if (!empty($params['google_source'])) {
//             $google_source_name = $params['google_source'];
//             $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//         }

//         $sql .= ' UNION ALL ';
//         $sql = trim($sql);
//     }
//     $result = [];

//     // Remove the last UNION ALL
//     $sql    = substr($sql, 0, -10);
//     $result = $CI->db->query($sql)->result();
//     $totalLeads = 0;

//     foreach ($sources as $key => $source) {
//         $sources[$key]['total'] = 0;

//         if (!empty($_POST["source"])) {
//             if (in_array($source["id"], $_POST["source"])) {
//                 $sources[$key]['total'] = !empty($result[$key]->total) ? $result[$key]->total : 0;
//                 $sources[$key]['conversion_id'] = !empty($result[$key]->conversion_id) ? $result[$key]->conversion_id : 0;
//             } else {
//                 $sources[$key]['total'] = 0;
//                 $sources[$key]['conversion_id'] = '';
//             }
//         } else {
//             $sources[$key]['total']  = !empty($result[$key]->total) ? $result[$key]->total : 0;
//         }

//         $totalLeads += !empty($sources[$key]['total']) ? $sources[$key]['total'] : 0;
//     }

//     $sources[] = array("name" => "Total Status Leads", "color_name" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

//     return $sources;
// }

// function get_status_summary_filter_report($params)
// {
//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $sources = $CI->leads_model->get_source();
//     $totalSource         = count($sources);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '( l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         $sid = get_staff_user_id();
//         $query = [];
//         $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
//         $staff_ids = implode(",", array_column($query_sql, 'staffid'));

//         if (!empty($staff_ids)) {
//             $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
//         }
//         $idsarr = array_column($query, 'staffid');
//         $sids = implode(",", $idsarr);

//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }

//         //         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//     }

//     // foreach ($sources as $source) {
//     $sql .= ' SELECT l.assigned,COUNT(DISTINCT(l.id)) as total,c.id conversion_id,l.source ';
//     $sql .= ' FROM ' . db_prefix() . 'leads l  inner join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status  inner join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

//     if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
//         $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
//     }
//     if (!empty($params['up_to_date'])) {
//         // $up_from_date_join = $params['up_from_date'];
//         // $up_to_date_join = $params['up_to_date'];
//         // $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';

//         $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact )";
//     }
//     if (!empty($params['followup_to_date'])) {
//         $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
//     }

//     // if (isset($status['lost'])) {
//     //     $sql .= ' WHERE lost=1';
//     // } elseif (isset($status['junk'])) {
//     //     $sql .= ' WHERE junk=1';
//     // } else {
//     //     $sql .= ' WHERE status=' . $status['id'];
//     // }

//     $sql .= ' WHERE 1=1 ';

//     if (!$has_permission_view) {
//         $sql .= ' AND ' . $whereNoViewPermission;
//     }
//     if (!empty($params['assigned'])) {
//         // $tids = " AND assigned = " . $params['assigned'];
//         $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
//         $sql .= $tids;
//     } else {
//         if ($role == 3) {
//             $sql .= $tids;
//         }
//     }

//     if (!empty($params['status'])) {
//         $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
//     }




//     // if (!empty($params['source'])) {
//     //     $sql .= ' AND source =' . $CI->db->escape_str($params['source']);
//     // }

//     /*if (isset($params['course'])) {
//             $sql .= 'AND tblcustomfieldsvalues.value ='.$params['course'];
//         }


// 		if (isset($params['degree'])) {
//             $sql .= 'AND tblcustomfieldsvalues.value ='.$params['degree'];
//         }*/

//     // if (!empty($params['lead_type'])) {
//     //     $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//     // }
//     if (!empty($params['lead_type'])) {
//         $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//         // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//     }
//     if (!empty($params['to_date'])) {
//         $from_date = $params['from_date'];
//         $to_date = $params['to_date'];
//         $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     }
//     if (!empty($params['up_to_date'])) {
//         $up_from_date = $params['up_from_date'];
//         $up_to_date = $params['up_to_date'];
//         //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//         // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

//         $sql .= " AND  (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//             . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
//     }
//     if (!empty($params['followup_to_date'])) {
//         $followup_from_date = $params['followup_from_date'];
//         $followup_to_date = $params['followup_to_date'];
//         $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     }

//     if (!empty($params['assign_to_date'])) {
//         $assign_from_date = $params['assign_from_date'];
//         $assign_to_date = $params['assign_to_date'];
//         $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     }
//     if (!empty($params['fb_source'])) {
//         $facebook_source_name = $params['fb_source'];
//         $sql .= ' AND  l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//     }

//     if (!empty($params['google_source'])) {
//         $google_source_name = $params['google_source'];
//         $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//     }

//     // $sql .= ' UNION ALL ';
//     $sql = trim($sql);
//     // }
//     $result = [];
//     $sql .= " group by l.source,l.assigned ORDER BY 
//     total DESC ";

//     // Remove the last UNION ALL

//     $result = $CI->db->query($sql)->result();


//     // $totalLeads = 0;

//     // foreach ($sources as $key => $source) {
//     //     $sources[$key]['total'] = 0;

//     //     if (!empty($_POST["source"])) {
//     //         if (in_array($source["id"], $_POST["source"])) {
//     //             $sources[$key]['total'] = !empty($result[$key]->total) ? $result[$key]->total : 0;
//     //             $sources[$key]['conversion_id'] = !empty($result[$key]->conversion_id) ? $result[$key]->conversion_id : 0;
//     //         } else {
//     //             $sources[$key]['total'] = 0;
//     //             $sources[$key]['conversion_id'] = '';
//     //         }
//     //     } else {
//     //         $sources[$key]['total']  = !empty($result[$key]->total) ? $result[$key]->total : 0;
//     //     }

//     //     $totalLeads += !empty($sources[$key]['total']) ? $sources[$key]['total'] : 0;
//     // }

//     // $sources[] = array("name" => "Total Status Leads", "color_name" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

//     // return $sources;


//     $groupedData = array();
//     foreach ($result as $item) {
//            $assigned = $item->assigned;
//         if (!isset($groupedData[$assigned])) {
//             $groupedData[$assigned] = array();
//         }
//         $groupedData[$assigned][] = $item;
//     }



//     $groupedData_array = array();
//     foreach ($groupedData as $ass_key => $groupedData_) {
//         // Extract the status_id values from the result array
//         $status_ids = array_map(function ($item) {
//             return $item->status_id;
//         }, $groupedData_);

//         // Count the occurrences of each status_id
//         $result = array_count_values($status_ids);

//         // if (!empty($result)) {
//         //     $result = array_column($result, "total", "status_id");
//         // }
//         $totalLeads = 0;


//         foreach ($sources as $key => $source) {
//             $sources[$key]['total'] = 0;

//             if (!empty($_POST["source"])) {
//                 if (in_array($source["id"], $_POST["source"])) {
//                     $sources[$key]['total'] = !empty($result[$key]->total) ? $result[$key]->total : 0;
//                     $sources[$key]['conversion_id'] = !empty($result[$key]->conversion_id) ? $result[$key]->conversion_id : 0;
//                 } else {
//                     $sources[$key]['total'] = 0;
//                     $sources[$key]['conversion_id'] = '';
//                 }
//             } else {
//                 $sources[$key]['total']  = !empty($result[$key]->total) ? $result[$key]->total : 0;
//             }

//             $totalLeads += !empty($sources[$key]['total']) ? $sources[$key]['total'] : 0;
//         }


//         $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);
//         $groupedData_array[$ass_key] = $statuses;
//     }

//     return $groupedData_array;
// }

function get_leads_summary_filter_report($params, $all_status = 0)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();


    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';
    
 if(is_seoTeam())
     {
      $whereNoViewPermission =" 1=1 ";   
     }

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $sql = 'SELECT ' . db_prefix() . 'leads.assigned,' . db_prefix() . 'leads_status.id as status_id,' . db_prefix() . 'leads_status.name as status_name,COUNT(DISTINCT ' . db_prefix() . 'leads.id) AS status_count,' . db_prefix() . 'leads_status.statusorder ';

    if (!empty($params['last_contact_date'])) {
        $sql .= ',
        ( SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
            FROM ' . db_prefix() . 'calls_activity_logs AS calls 
            WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
            AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
            ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
            LIMIT 1 ) AS lastcontact';
    }

    if (!empty($params['last_update_date'])) {
        $sql .= ', (
            SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
            FROM ' . db_prefix() . 'calls_activity_logs AS calls 
            WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
            ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
            LIMIT 1  ) AS lastupdatecontact';
    }
    $sql .= ' FROM ' . db_prefix() . 'leads ';
    if (!empty($params['up_to_date'])) {
        $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON calls.contact IN (' . db_prefix() . 'leads.phonenumber,' . db_prefix() . 'leads.alternative_phonenumber)';
    } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) || !empty($params['last_update_date'])) {
        $sql .= ' LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON calls.contact IN (' . db_prefix() . 'leads.phonenumber,' . db_prefix() . 'leads.alternative_phonenumber)';
        if (!empty($params['last_contact_date'])) {
            $sql .= ' AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\', \'unknow\')';
        }
    }
    $sql .= ' LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads.status = ' . db_prefix() . 'leads_status.id';
    if (!empty($_POST["status"])) {
        $sql .= ' AND ' . db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ') ';
    }
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
        $sql .= ' JOIN ' . db_prefix() . 'customfieldsvalues ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'customfieldsvalues.relid';
    }

    if (!empty($params['followup_to_date'])) {
        $sql .= ' JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id';
    }
    if (isset($status['lost'])) {
        $sql .= ' WHERE lost = 1';
    } elseif (isset($status['junk'])) {
        $sql .= ' WHERE junk = 1';
    } else {
        $sql .= ' WHERE ' . db_prefix() . 'leads_status.id IS NOT NULL';
    }
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';

        if (!empty($params['up_to_date'])) {
            $sql .= ' AND calls.staffid IN (' . implode(',', $params['assigned']) . ')';
        }
    } else if ($role == 3) {
        // $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
    }
    if (!empty($params['source'])) {
        $sql .= ' AND source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['neet_score'])) {
        $neet_range = explode('-', $params['neet_score']);
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value != ""';
    }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= ' AND Date(adjusted_call_start) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }
    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }
    if (!empty($params['fb_source'])) {
        $facebook_source_name = $params['fb_source'];
        $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
    }
    if (!empty($params['google_source'])) {
        $google_source_name = $params['google_source'];
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
    }


    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql .= ' GROUP BY ' . db_prefix() . 'leads.status';
    } else {
        $sql .= ' GROUP BY ' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.assigned ';
    }

    //   $sql_alternative = str_replace(db_prefix() . 'leads.phonenumber', db_prefix() . 'leads.alternative_phonenumber', $sql);



    //   $sql = $sql." UNION ALL ".$sql_alternative;

    //  $sql =  'SELECT tt.assigned,tt.status_id,tt.status_name,sum(tt.status_count) status_count,tt.statusorder  from ('.$sql.') tt ';

    //     if (!empty($params["total_status"]) && $params["total_status"] == 1) {
    //         $sql .= ' GROUP BY tt.status_id';
    //     } else {
    //         $sql .= ' GROUP BY tt.status_id,tt.assigned ';
    //     }
    //  $sql .=' ORDER BY tt.statusorder ';

    if (!empty($params['last_contact_date']) || (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        $sql .= ' HAVING';
        if (!empty($params['last_contact_date'])) {
            $last_contact_date = $params['last_contact_date'];
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' (lastcontact <= "' . $last_contact_date . '")';
            } else {
                $sql .= ' (lastcontact <= "' . $last_contact_date . '" OR lastcontact IS NULL)';
            }
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' AND ';
            }
        } else if (!empty($params['last_update_date'])) {
            $last_update_date = $params['last_update_date'];
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' (lastupdatecontact <= "' . $last_update_date . '")';
            } else {
                $sql .= ' (lastupdatecontact <= "' . $last_update_date . '" OR lastupdatecontact IS NULL)';
            }
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' AND ';
            }
        }
        if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $sql .= ' COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }



    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql = " SELECT t.assigned, CONCAT('{', GROUP_CONCAT(CONCAT('\"', t.status_name, '\"', ':', COALESCE(t.status_count, 0)) SEPARATOR ', '), '}') AS status_counts, COALESCE(SUM(t.status_count), 0) AS total  FROM ( " . $sql . ") t  ";
    } else {
        $sql = " SELECT t.assigned, CONCAT('{', GROUP_CONCAT(CONCAT('\"', t.status_name, '\"', ':', COALESCE(t.status_count, 0)) SEPARATOR ', '), '}') AS status_counts, COALESCE(SUM(t.status_count), 0) AS total  FROM ( " . $sql . ") t GROUP BY t.assigned ";
    }



    $result = [];
    $result = $CI->db->query($sql)->result();


    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        return $result;
    } else {
        return array_column($result, null, "assigned");
    }
    // echo "<pre>";
    // print_r($result);
    // die;
    // $groupedData = [];

    // foreach ($result as $item) {
    //     if (!isset($groupedData[$item->assigned])) {
    //         $groupedData[$item->assigned] = [
    //             'assigned' => $item->assigned,
    //             'statuses' => []
    //         ];
    //     }
    //     $groupedData[$item->assigned]['statuses'][] = [
    //         'status_id' => $item->status_id,
    //         'total' => $item->total
    //     ];
    // }




    // $totalLeads = 0;
    // foreach ($groupedData as $key => $ass) {
    //     foreach ($statuses as $key => $status) {
    //         $statuses[$key]['total'] = 0;
    //         // echo $status["id"];
    //         if (!empty($_POST["status"])) {
    //             if (in_array($status["id"], $_POST["status"])) {
    //                 $statuses[$key]['total'] = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
    //             } else {
    //                 $statuses[$key]['total'] = 0;
    //             }
    //         } else {
    //             $statuses[$key]['total']  = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
    //         }

    //         $totalLeads += !empty($statuses[$key]['total']) ? $statuses[$key]['total'] : 0;
    //     }
    // }
    // $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

    // return $statuses;
}
function get_leads_summary_filter_report_($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();


    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql = 'SELECT ' . db_prefix() . 'leads_sources.name source_name,' . db_prefix() . 'leads_status.name status_name,' . db_prefix() . 'leads.source source_id,"Total Details" as assigned,' . db_prefix() . 'leads_status.id as status_id,' . db_prefix() . 'leads_status.name as status_name,count(DISTINCT ' . db_prefix() . 'leads.id) AS total,CONCAT(' . db_prefix() . 'leads_status.name,"-",' . db_prefix() . 'leads_sources.name) as index_name,' . db_prefix() . 'leads_status.conversion_type conversion_id,' . db_prefix() . 'leads_sources.marketing_type marketing_id';
    } else {
        $sql = 'SELECT ' . db_prefix() . 'leads_sources.name source_name,' . db_prefix() . 'leads_status.name status_name,' . db_prefix() . 'leads.source source_id,' . db_prefix() . 'leads.assigned,' . db_prefix() . 'leads_status.id as status_id,' . db_prefix() . 'leads_status.name as status_name,count(DISTINCT ' . db_prefix() . 'leads.id) AS total,CONCAT(' . db_prefix() . 'leads_status.name,"-",' . db_prefix() . 'leads_sources.name) as index_name,' . db_prefix() . 'leads_status.conversion_type conversion_id,' . db_prefix() . 'leads_sources.marketing_type marketing_id';
    }


    if (!empty($params['last_contact_date'])) {
        $sql .= ',
        ( SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
            FROM ' . db_prefix() . 'calls_activity_logs AS calls 
            WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
            AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
            ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
            LIMIT 1 ) AS lastcontact';
    }

    if (!empty($params['last_update_date'])) {
        $sql .= ', (
            SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
            FROM ' . db_prefix() . 'calls_activity_logs AS calls 
            WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
            ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
            LIMIT 1  ) AS lastupdatecontact';
    }
    $sql .= ' FROM ' . db_prefix() . 'leads
    LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads.status = ' . db_prefix() . 'leads_status.id';
    $sql .=  ' LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads.source = ' . db_prefix() . 'leads_sources.id';
    if (!empty($_POST["status"])) {
        $sql .= ' AND ' . db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ') ';
    }
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
        $sql .= ' JOIN ' . db_prefix() . 'customfieldsvalues ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['up_to_date'])) {
        $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
    } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) || !empty($params['last_update_date'])) {
        $sql .= ' LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
        if (!empty($params['last_contact_date'])) {
            $sql .= ' AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\')';
        }
    }
    if (!empty($params['followup_to_date'])) {
        $sql .= ' JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id';
    }
    if (isset($status['lost'])) {
        $sql .= ' WHERE lost = 1';
    } elseif (isset($status['junk'])) {
        $sql .= ' WHERE junk = 1';
    } else {
        $sql .= ' WHERE ' . db_prefix() . 'leads_status.id IS NOT NULL';
    }
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';

        if (!empty($params['up_to_date'])) {
            $sql .= ' AND calls.staffid IN (' . implode(',', $params['assigned']) . ')';
        }
    } else if ($role == 3) {
        // $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
    }
    if (!empty($params['source'])) {
        $sql .= ' AND source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['neet_score'])) {
        $neet_range = explode('-', $params['neet_score']);
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value != ""';
    }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= ' AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), \'%Y-%m-%d\') BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }
    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }
    if (!empty($params['fb_source'])) {
        $facebook_source_name = $params['fb_source'];
        $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
    }
    if (!empty($params['google_source'])) {
        $google_source_name = $params['google_source'];
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
    }

    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql .= ' GROUP BY ' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.source';
    } else {
        $sql .= ' GROUP BY ' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.source,' . db_prefix() . 'leads.assigned ';
    }
    if (!empty($params['last_contact_date']) || (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        $sql .= ' HAVING';
        if (!empty($params['last_contact_date'])) {
            $last_contact_date = $params['last_contact_date'];
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' (lastcontact <= "' . $last_contact_date . '")';
            } else {
                $sql .= ' (lastcontact <= "' . $last_contact_date . '" OR lastcontact IS NULL)';
            }
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' AND ';
            }
        } else if (!empty($params['last_update_date'])) {
            $last_update_date = $params['last_update_date'];
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' (lastupdatecontact <= "' . $last_update_date . '")';
            } else {
                $sql .= ' (lastupdatecontact <= "' . $last_update_date . '" OR lastupdatecontact IS NULL)';
            }
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' AND ';
            }
        }
        if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $sql .= ' COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }
    $sql .=  ' ORDER BY ' . db_prefix() . 'leads_status.statusorder ';
    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        // $sql = " SELECT t.assigned, CONCAT('{', GROUP_CONCAT(CONCAT('\"', t.status_name, '\"', ':', COALESCE(t.status_count, 0)) SEPARATOR ', '), '}') AS status_counts, COALESCE(SUM(t.status_count), 0) AS total  FROM ( " . $sql . ") t  ";

        // $sql = "
        // SELECT 
        //     t.assigned, 
        //     CONCAT(
        //         '{', 
        //         GROUP_CONCAT(
        //             CONCAT(
        //                 '\"', 
        //                 t.status_name, 
        //                 '\": ', 
        //                 COALESCE(t.status_count, 0),
        //                 ', \"color\": \"', 
        //                 t.color, 
        //                 '\", ',
        //                 '\"conversion_type\": \"', 
        //                 t.conversion_type, 
        //                 '\", ',
        //                 '\"source_id\": \"', 
        //                 t.source_id, 
        //                 '\", ',
        //                 '\"status_name\": \"', 
        //                 t.name, 
        //                 '\"'
        //             ) 
        //             SEPARATOR ', '
        //         ), 
        //         ', \"status_1\": 1', -- Set this to the appropriate value or condition
        //         '}'
        //     ) AS status_counts, 
        //     COALESCE(SUM(t.status_count), 0) AS total  
        // FROM ( " . $sql . ") t 

        // ";
    } else {
        // $sql = " SELECT t.assigned, CONCAT('{', GROUP_CONCAT(CONCAT('\"', t.status_name, '\"', ':', COALESCE(t.status_count, 0)) SEPARATOR ', '), '}') AS status_counts, COALESCE(SUM(t.status_count), 0) AS total  FROM ( " . $sql . ") t GROUP BY t.assigned ";

        //     $sql = "
        //     SELECT 
        //         t.assigned, 
        //         CONCAT(
        //             '{',
        //             GROUP_CONCAT(
        //                 CONCAT(
        //                     '\"', 
        //                     t.status_name, 
        //                     '\": {',
        //                     '\"status_count\": ', COALESCE(t.status_count, 0), ', ',
        //                     '\"color\": \"', t.color, '\", ',
        //                     '\"conversion_type\": \"', t.conversion_type, '\", ',
        //                     '\"source_id\": \"', t.source_id, '\", ',
        //                     '\"status_name\": \"', t.name, '\"',
        //                     '}'
        //                 ) 
        //                 SEPARATOR ', '
        //             ),
        //             '}'
        //         ) AS status_counts, 
        //         COALESCE(SUM(t.status_count), 0) AS total  
        //     FROM ( " . $sql . ") t 
        //     GROUP BY t.assigned
        // ";
    }
    $result = [];
    $result = $CI->db->query($sql)->result_array();

    $result_array = [];
    foreach ($result as $r) {
        $result_array[$r["assigned"]][$r["index_name"]] = $r;
    }
    return $result_array;
    // if (!empty($params["total_status"]) && $params["total_status"] == 1) {
    //     return $result;
    // } else {
    //     return array_column($result, null, "assigned");
    // }
    // echo "<pre>";
    // print_r($result);
    // die;
    // $groupedData = [];

    // foreach ($result as $item) {
    //     if (!isset($groupedData[$item->assigned])) {
    //         $groupedData[$item->assigned] = [
    //             'assigned' => $item->assigned,
    //             'statuses' => []
    //         ];
    //     }
    //     $groupedData[$item->assigned]['statuses'][] = [
    //         'status_id' => $item->status_id,
    //         'total' => $item->total
    //     ];
    // }




    // $totalLeads = 0;
    // foreach ($groupedData as $key => $ass) {
    //     foreach ($statuses as $key => $status) {
    //         $statuses[$key]['total'] = 0;
    //         // echo $status["id"];
    //         if (!empty($_POST["status"])) {
    //             if (in_array($status["id"], $_POST["status"])) {
    //                 $statuses[$key]['total'] = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
    //             } else {
    //                 $statuses[$key]['total'] = 0;
    //             }
    //         } else {
    //             $statuses[$key]['total']  = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
    //         }

    //         $totalLeads += !empty($statuses[$key]['total']) ? $statuses[$key]['total'] : 0;
    //     }
    // }
    // $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

    // return $statuses;
}


function get_leads_summary_filter($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();


    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $sql = 'SELECT ' . db_prefix() . 'leads_status.id as status_id,COUNT(DISTINCT ' . db_prefix() . 'leads.id) AS total';

    if (!empty($params['last_contact_date'])) {
        $sql .= ',
        ( SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
            FROM ' . db_prefix() . 'calls_activity_logs AS calls 
            WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
            AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\') 
            ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
            LIMIT 1 ) AS lastcontact';
    }

    if (!empty($params['last_update_date'])) {
        $sql .= ', (
            SELECT DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') 
            FROM ' . db_prefix() . 'calls_activity_logs AS calls 
            WHERE calls.contact = ' . db_prefix() . 'leads.phonenumber 
            ORDER BY DATE_FORMAT(DATE_ADD(\'1970-01-01\', INTERVAL (call_start + (5 * 3600 + 30 * 60)) SECOND), \'%Y-%m-%d\') DESC 
            LIMIT 1  ) AS lastupdatecontact';
    }
    $sql .= ' FROM ' . db_prefix() . 'leads
    Right JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads.status = ' . db_prefix() . 'leads_status.id';
    if (!empty($_POST["status"])) {
        $sql .= ' AND ' . db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ') ';
    }
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
        $sql .= ' JOIN ' . db_prefix() . 'customfieldsvalues ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['up_to_date'])) {
        $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
    } else if ((isset($params['update_count_max']) && $params['update_count_max'] != "") || (!empty($params['last_contact_date'])) || !empty($params['last_update_date'])) {
        $sql .= ' LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . db_prefix() . 'leads.phonenumber = calls.contact';
        if (!empty($params['last_contact_date'])) {
            $sql .= ' AND LOWER(TRIM(call_status)) IN (\'answered\', \'status_unknow\')';
        }
    }
    if (!empty($params['followup_to_date'])) {
        $sql .= ' JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . db_prefix() . 'leads.id';
    }
    if (isset($status['lost'])) {
        $sql .= ' WHERE lost = 1';
    } elseif (isset($status['junk'])) {
        $sql .= ' WHERE junk = 1';
    } else {
        $sql .= ' WHERE ' . db_prefix() . 'leads_status.id IS NOT NULL';
    }
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
    } else if ($role == 3) {
        // $sql .= ' AND assigned IN (' . implode(',', $params['assigned']) . ')';
    }
    if (!empty($params['source'])) {
        $sql .= ' AND source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['neet_score'])) {
        $neet_range = explode('-', $params['neet_score']);
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value != ""';
    }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= ' AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), \'%Y-%m-%d\') BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }
    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }
    if (!empty($params['fb_source'])) {
        $facebook_source_name = $params['fb_source'];
        $sql .= ' AND ' . db_prefix() . 'leads.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
    }
    if (!empty($params['google_source'])) {
        $google_source_name = $params['google_source'];
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
    }
    $sql .= ' GROUP BY ' . db_prefix() . 'leads.status,' . db_prefix() . 'leads.id ';
    if (!empty($params['last_contact_date']) || (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        $sql .= ' HAVING';
        if (!empty($params['last_contact_date'])) {
            $last_contact_date = $params['last_contact_date'];
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' (lastcontact <= "' . $last_contact_date . '")';
            } else {
                $sql .= ' (lastcontact <= "' . $last_contact_date . '" OR lastcontact IS NULL)';
            }
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' AND ';
            }
        } else if (!empty($params['last_update_date'])) {
            $last_update_date = $params['last_update_date'];
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' (lastupdatecontact <= "' . $last_update_date . '")';
            } else {
                $sql .= ' (lastupdatecontact <= "' . $last_update_date . '" OR lastupdatecontact IS NULL)';
            }
            if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
                $sql .= ' AND ';
            }
        }
        if (isset($params['update_count_max']) && $params['update_count_max'] != '') {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $sql .= ' COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }
    $sql .=  ' ORDER BY ' . db_prefix() . 'leads_status.statusorder ';


    $result = [];
    $result = $CI->db->query($sql)->result();

    // Extract the status_id values from the result array
    $status_ids = array_map(function ($item) {
        return $item->status_id;
    }, $result);

    // Count the occurrences of each status_id
    $result = array_count_values($status_ids);

    // if (!empty($result)) {
    //     $result = array_column($result, "total", "status_id");
    // }

    $totalLeads = 0;
    foreach ($statuses as $key => $status) {
        $statuses[$key]['total'] = 0;
        // echo $status["id"];
        if (!empty($_POST["status"])) {
            if (in_array($status["id"], $_POST["status"])) {
                $statuses[$key]['total'] = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
            } else {
                $statuses[$key]['total'] = 0;
            }
        } else {
            $statuses[$key]['total']  = !empty($result[$status["id"]]) ? $result[$status["id"]] : 0;
        }

        $totalLeads += !empty($statuses[$key]['total']) ? $statuses[$key]['total'] : 0;
    }
    $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

    return $statuses;
}

function get_leads_report_($params, $export = 0)
{
    $params['date_type'] = !empty($params['date_type']) ? trim(strtolower($params['date_type'])) : '';
    if (!empty($params['date_type'])) {
        $CI = &get_instance();
        $sql = "SELECT ";
        if (!empty($params['date_type'])) {

            if (!empty($params['assign_to_date'])) {
                if ($params['date_type'] == "daily") {
                    $sql .= "DATE(l.dateassigned) as dateadded, ";
                } elseif ($params['date_type'] == "week") {
                    $sql .= "YEARWEEK(l.dateassigned, 1) as dateadded, ";
                } elseif ($params['date_type'] == "month") {
                    $sql .= "DATE_FORMAT(l.dateassigned, '%Y - %M') as dateadded, ";
                } elseif ($params['date_type'] == "year") {
                    $sql .= "YEAR(l.dateassigned) as dateadded, ";
                }
            } else  if (!empty($params['up_to_date'])) {
                if ($params['date_type'] == "daily") {
                    $sql .= "DATE(calls.adjusted_call_start) as dateadded, ";
                } elseif ($params['date_type'] == "week") {
                    $sql .= "YEARWEEK(calls.adjusted_call_start, 1) as dateadded, ";
                } elseif ($params['date_type'] == "month") {
                    $sql .= "DATE_FORMAT(calls.adjusted_call_start, '%Y - %M') as dateadded, ";
                } elseif ($params['date_type'] == "year") {
                    $sql .= "YEAR(calls.adjusted_call_start) as dateadded, ";
                }
            } else {
                if ($params['date_type'] == "daily") {
                    $sql .= "DATE(l.dateadded) as dateadded, ";
                } elseif ($params['date_type'] == "week") {
                    $sql .= "YEARWEEK(l.dateadded, 1) as dateadded, ";
                } elseif ($params['date_type'] == "month") {
                    $sql .= "DATE_FORMAT(l.dateadded, '%Y - %M') as dateadded, ";
                } elseif ($params['date_type'] == "year") {
                    $sql .= "YEAR(l.dateadded) as dateadded, ";
                }
            }
        }

        if (!empty($export) && $export == 1) {
            $sql .= " CONCAT(staff.firstname,' ',staff.lastname) full_name,l.assigned,";
        }

        $sql .= "COUNT(DISTINCT l.id) as count FROM " . db_prefix() . "leads l ";

        if (!empty($params['up_to_date'])) {
            $sql .= "JOIN " . db_prefix() . "calls_activity_logs as calls ON calls.contact IN (l.phonenumber) AND l.assigned=calls.staffid ";
        }
        if (!empty($params['department']) || !empty($params['location'])) {
            $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
        } else  if (!empty($export) && $export == 1) {
            $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
        }

        $sql .= "WHERE 1=1 ";

        if (!empty($params['source'])) {
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['department'])) {
            $sql .= ' AND staff.department in (' . implode(",", $CI->db->escape_str($params['department'])) . ')';
        }
        if (!empty($params['location'])) {
            $sql .= ' AND staff.office_location in (' . implode(",", $CI->db->escape_str($params['location'])) . ')';
        }

        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= 'AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
        }

        if (!empty($params['assign_to_date'])) {
            $from_date = $params['assign_from_date'];
            $to_date = $params['assign_to_date'];
            $sql .= 'AND DATE(l.dateassigned) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
        }

        if (!empty($params['lead_type'])) {
            $sql .= 'AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        }

        if (!empty($params['up_to_date'])) {
            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            $sql .= "AND Date(adjusted_call_start) BETWEEN '"
                . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "' ";
        }

        if (!empty($params['fb_source'])) {
            $facebook_source_name = $params['fb_source'];
            $sql .= 'AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\') ';
        }

        if (!empty($params['google_source'])) {
            $google_source_name = $params['google_source'];
            $sql .= 'AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND '
                . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\') ';
        }

        if (!empty($params['status'])) {
            $sql .= 'AND l.status IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['status'])) . ') ';
        }

        if (!empty($params['assigned'])) {
            $sql .= 'AND l.assigned IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['assigned'])) . ') ';
        }

        if (!empty($params['date_type'])) {

            if (!empty($params['assign_to_date'])) {

                if ($params['date_type'] == "daily") {
                    $sql .= "GROUP BY DATE(l.dateassigned) ";
                } elseif ($params['date_type'] == "week") {
                    $sql .= "GROUP BY YEARWEEK(l.dateassigned, 1) ";
                } elseif ($params['date_type'] == "month") {
                    $sql .= "GROUP BY DATE_FORMAT(l.dateassigned, '%Y - %m') ";
                } elseif ($params['date_type'] == "year") {
                    $sql .= "GROUP BY YEAR(l.dateassigned) ";
                }
            } else if (!empty($params['up_to_date'])) {
                if ($params['date_type'] == "daily") {
                    $sql .= "GROUP BY DATE(calls.adjusted_call_start) ";
                } elseif ($params['date_type'] == "week") {
                    $sql .= "GROUP BY YEARWEEK(calls.adjusted_call_start, 1) ";
                } elseif ($params['date_type'] == "month") {
                    $sql .= "GROUP BY DATE_FORMAT(calls.adjusted_call_start, '%Y - %m') ";
                } elseif ($params['date_type'] == "year") {
                    $sql .= "GROUP BY YEAR(calls.adjusted_call_start) ";
                }
            } else {
                if ($params['date_type'] == "daily") {
                    $sql .= "GROUP BY DATE(l.dateadded) ";
                } elseif ($params['date_type'] == "week") {
                    $sql .= "GROUP BY YEARWEEK(l.dateadded, 1) ";
                } elseif ($params['date_type'] == "month") {
                    $sql .= "GROUP BY DATE_FORMAT(l.dateadded, '%Y - %m') ";
                } elseif ($params['date_type'] == "year") {
                    $sql .= "GROUP BY YEAR(l.dateadded) ";
                }
            }
            if (!empty($export) && $export == 1) {
                if (!empty($params["total_status"]) && $params["total_status"] == 1) {
                } else {
                    $sql .= ",l.assigned";
                }
            }
        }
        $sql_alternative = str_replace('l.phonenumber', 'l.alternative_phonenumber', $sql);
        $sql = $sql . " UNION ALL " . $sql_alternative;
        $sql = "Select * from (" . $sql . ") tt ";

        if (!empty($params['date_type'])) {
            if ($params['date_type'] == "daily") {
                $sql .= "GROUP BY tt.dateadded ";
            } elseif ($params['date_type'] == "week") {
                $sql .= "GROUP BY tt.dateadded ";
            } elseif ($params['date_type'] == "month") {
                $sql .= "GROUP BY tt.dateadded ";
            } elseif ($params['date_type'] == "year") {
                $sql .= "GROUP BY tt.dateadded ";
            }

            if (!empty($export) && $export == 1) {
                if (!empty($params["total_status"]) && $params["total_status"] == 1) {
                } else {
                    $sql .= ",tt.assigned";
                }
            }
        }

        if (!empty($export) && $export == 1) {
        } else {
            $sql .= " LIMIT 15 ";
        }

        return $result = $CI->db->query($sql)->result();
    } else {
        return [];
    }
}

function get_leads_report_conversion($params)
{
    $params['date_type'] = !empty($params['date_type']) ? trim(strtolower($params['date_type'])) : '';

    $CI = &get_instance();
    $sql = "SELECT 
        dateadded,dateadded_modify,
        GROUP_CONCAT(CONCAT(conversion_type_name, ': ', conversion_count) SEPARATOR ', ') as conversion_counts
    FROM (
        SELECT ";

    if (!empty($params['date_type'])) {
        if ($params['date_type'] == "daily") {
            $sql .= "DATE(l.dateadded) as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
        } elseif ($params['date_type'] == "week") {
            $sql .= "YEARWEEK(l.dateadded, 1) as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
        } elseif ($params['date_type'] == "month") {
            $sql .= "DATE_FORMAT(l.dateadded, '%Y - %M') as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
        } elseif ($params['date_type'] == "year") {
            $sql .= "YEAR(l.dateadded) as dateadded, c.name as conversion_type_name, COUNT(DISTINCT l.id) as conversion_count ";
        }

        $sql .= ",DATE_FORMAT(l.dateadded, '%Y-%m') dateadded_modify FROM " . db_prefix() . "leads l ";

        if (!empty($params['up_to_date'])) {
            $sql .= "JOIN " . db_prefix() . "calls_activity_logs as calls ON calls.contact IN (l.phonenumber,l.alternative_phonenumber) ";
        }

        if (!empty($params['department']) || !empty($params['location'])) {
            $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
        }

        $sql .= "JOIN " . db_prefix() . "leads_status as status ON (status.id = l.status) ";
        $sql .= "JOIN " . db_prefix() . "lead_conversion_type as c ON (c.id = status.conversion_type AND c.status = 1) ";
        $sql .= "WHERE 1=1 ";

        if (!empty($params['source'])) {
            $sql .= ' AND source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }

        if (!empty($params['department'])) {
            $sql .= ' AND staff.department IN (' . implode(",", $CI->db->escape_str($params['department'])) . ')';
        }

        if (!empty($params['location'])) {
            $sql .= ' AND staff.office_location IN (' . implode(",", $CI->db->escape_str($params['location'])) . ')';
        }

        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
        }

        if (!empty($params['lead_type'])) {
            $sql .= 'AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        }

        if (!empty($params['up_to_date'])) {
            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            $sql .= " AND Date(adjusted_call_start) BETWEEN '"
                . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "' ";
        }

        if (!empty($params['fb_source'])) {
            $facebook_source_name = $params['fb_source'];
            $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\') ';
        }

        if (!empty($params['google_source'])) {
            $google_source_name = $params['google_source'];
            $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND '
                . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\') ';
        }

        if (!empty($params['status'])) {
            $sql .= ' AND l.status IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['status'])) . ') ';
        }

        if (!empty($params['assigned'])) {
            $sql .= ' AND l.assigned IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['assigned'])) . ') ';
        }

        if ($params['date_type'] == "daily") {
            $sql .= "GROUP BY DATE(l.dateadded), c.name ORDER BY DATE(l.dateadded) ASC";
        } elseif ($params['date_type'] == "week") {
            $sql .= "GROUP BY YEARWEEK(l.dateadded, 1), c.name ORDER BY YEARWEEK(l.dateadded, 1) ASC";
        } elseif ($params['date_type'] == "month") {
            $sql .= "GROUP BY DATE_FORMAT(l.dateadded, '%Y - %M'), c.name ORDER BY DATE_FORMAT(dateadded_modify, '%Y-%m') ASC";
        } elseif ($params['date_type'] == "year") {
            $sql .= "GROUP BY YEAR(l.dateadded), c.name ORDER BY YEAR(l.dateadded) ASC";
        }

        if ($params['date_type'] == "month") {
            $sql .= ") as conversion_counts_subquery GROUP BY dateadded order by dateadded_modify asc";
        } else {
            $sql .= ") as conversion_counts_subquery GROUP BY dateadded order by dateadded asc";
        }

        $sql .= " LIMIT 15 ";
        return $result = $CI->db->query($sql)->result();
    } else {
        return [];
    }
}

function get_leads_report_marketing($params)
{
    $params['date_type'] = !empty($params['date_type']) ? trim(strtolower($params['date_type'])) : '';

    $CI = &get_instance();
    $sql = "SELECT 
        dateadded,dateadded_modify,
        GROUP_CONCAT(CONCAT(marketing_type_name, ': ', marketing_count) SEPARATOR ', ') as marketing_count
    FROM (
        SELECT ";

    if (!empty($params['date_type'])) {
        if ($params['date_type'] == "daily") {
            $sql .= "DATE(l.dateadded) as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
        } elseif ($params['date_type'] == "week") {
            $sql .= "YEARWEEK(l.dateadded, 1) as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
        } elseif ($params['date_type'] == "month") {
            $sql .= "DATE_FORMAT(l.dateadded, '%Y - %M') as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
        } elseif ($params['date_type'] == "year") {
            $sql .= "YEAR(l.dateadded) as dateadded, m.name as marketing_type_name, COUNT(DISTINCT l.id) as marketing_count ";
        }

        $sql .= ",DATE_FORMAT(l.dateadded, '%Y-%m') dateadded_modify FROM " . db_prefix() . "leads l ";

        if (!empty($params['up_to_date'])) {
            $sql .= "JOIN " . db_prefix() . "calls_activity_logs as calls ON  calls.contact IN (l.phonenumber,l.alternative_phonenumber) ";
        }

        if (!empty($params['department']) || !empty($params['location'])) {
            $sql .= "JOIN " . db_prefix() . "staff as staff ON (staff.staffid = l.assigned) ";
        }

        $sql .= "JOIN " . db_prefix() . "leads_sources as source ON (source.id = l.source) ";
        $sql .= "JOIN " . db_prefix() . "lead_marketing as m ON (m.id = source.marketing_type AND m.status = 1) ";
        $sql .= "WHERE 1=1 ";

        if (!empty($params['source'])) {
            $sql .= ' AND source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }

        if (!empty($params['department'])) {
            $sql .= ' AND staff.department IN (' . implode(",", $CI->db->escape_str($params['department'])) . ')';
        }

        if (!empty($params['location'])) {
            $sql .= ' AND staff.office_location IN (' . implode(",", $CI->db->escape_str($params['location'])) . ')';
        }

        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '" ';
        }

        if (!empty($params['lead_type'])) {
            $sql .= 'AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        }

        if (!empty($params['up_to_date'])) {
            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            $sql .= " AND Date(adjusted_call_start) BETWEEN '"
                . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "' ";
        }

        if (!empty($params['fb_source'])) {
            $facebook_source_name = $params['fb_source'];
            $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\') ';
        }

        if (!empty($params['google_source'])) {
            $google_source_name = $params['google_source'];
            $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND '
                . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\') ';
        }

        if (!empty($params['status'])) {
            $sql .= ' AND l.status IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['status'])) . ') ';
        }

        if (!empty($params['assigned'])) {
            $sql .= ' AND l.assigned IN (' . implode(",", array_map(array($CI->db, 'escape_str'), $params['assigned'])) . ') ';
        }

        if ($params['date_type'] == "daily") {
            $sql .= "GROUP BY DATE(l.dateadded), m.name ORDER BY DATE(l.dateadded) ASC";
        } elseif ($params['date_type'] == "week") {
            $sql .= "GROUP BY YEARWEEK(l.dateadded, 1), m.name ORDER BY YEARWEEK(l.dateadded, 1) ASC";
        } elseif ($params['date_type'] == "month") {
            $sql .= "GROUP BY DATE_FORMAT(l.dateadded, '%Y - %M'), m.name ORDER BY DATE_FORMAT(dateadded_modify, '%Y-%m') ASC";
        } elseif ($params['date_type'] == "year") {
            $sql .= "GROUP BY YEAR(l.dateadded), m.name ORDER BY DATE_FORMAT(l.dateadded, '%Y') ASC";
        }
        if ($params['date_type'] == "month") {
            $sql .= ") as conversion_counts_subquery GROUP BY dateadded order by dateadded_modify asc";
        } else {
            $sql .= ") as conversion_counts_subquery GROUP BY dateadded order by dateadded asc";
        }
        $sql .= " LIMIT 15 ";
        return $result = $CI->db->query($sql)->result();
    } else {
        return [];
    }
}

function get_status_summary_filter($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $sources = $CI->leads_model->get_source();
    $totalSource         = count($sources);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '( l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        $sid = get_staff_user_id();
        $query = [];
        $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
        $staff_ids = implode(",", array_column($query_sql, 'staffid'));

        if (!empty($staff_ids)) {
            $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
        }
        $idsarr = array_column($query, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }

        //         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
    }

    foreach ($sources as $source) {
        $sql .= ' SELECT COUNT(DISTINCT(l.id)) as total,c.id conversion_id ';
        $sql .= ' FROM ' . db_prefix() . 'leads l  inner join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status  inner join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

        if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
            $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
        }
        if (!empty($params['up_to_date'])) {
            // $up_from_date_join = $params['up_from_date'];
            // $up_to_date_join = $params['up_to_date'];
            // $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';

            $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact )";
        }
        if (!empty($params['followup_to_date'])) {
            $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
        }

        // if (isset($status['lost'])) {
        //     $sql .= ' WHERE lost=1';
        // } elseif (isset($status['junk'])) {
        //     $sql .= ' WHERE junk=1';
        // } else {
        //     $sql .= ' WHERE status=' . $status['id'];
        // }

        $sql .= ' WHERE source=' . $source['id'];

        if (!$has_permission_view) {
            $sql .= ' AND ' . $whereNoViewPermission;
        }
        if (!empty($params['assigned'])) {
            // $tids = " AND assigned = " . $params['assigned'];
            $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
            $sql .= $tids;
        } else {
            if ($role == 3) {
                $sql .= $tids;
            }
        }

        if (!empty($params['status'])) {
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }




        // if (!empty($params['source'])) {
        //     $sql .= ' AND source =' . $CI->db->escape_str($params['source']);
        // }

        /*if (isset($params['course'])) {
            $sql .= 'AND tblcustomfieldsvalues.value ='.$params['course'];
        }
		 
		 
		if (isset($params['degree'])) {
            $sql .= 'AND tblcustomfieldsvalues.value ='.$params['degree'];
        }*/

        // if (!empty($params['lead_type'])) {
        //     $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        // }
        if (!empty($params['lead_type'])) {
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
            // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

            $sql .= " AND  (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
                . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
        }
        if (!empty($params['followup_to_date'])) {
            $followup_from_date = $params['followup_from_date'];
            $followup_to_date = $params['followup_to_date'];
            $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
        }

        if (!empty($params['assign_to_date'])) {
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['fb_source'])) {
            $facebook_source_name = $params['fb_source'];
            $sql .= ' AND  l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
        }

        if (!empty($params['google_source'])) {
            $google_source_name = $params['google_source'];
            $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
        }

        $sql .= ' UNION ALL ';
        $sql = trim($sql);
    }
    $result = [];

    // Remove the last UNION ALL
    $sql    = substr($sql, 0, -10);
    $result = $CI->db->query($sql)->result();
    $totalLeads = 0;

    foreach ($sources as $key => $source) {
        $sources[$key]['total'] = 0;

        if (!empty($_POST["source"])) {
            if (in_array($source["id"], $_POST["source"])) {
                $sources[$key]['total'] = !empty($result[$key]->total) ? $result[$key]->total : 0;
                $sources[$key]['conversion_id'] = !empty($result[$key]->conversion_id) ? $result[$key]->conversion_id : 0;
            } else {
                $sources[$key]['total'] = 0;
                $sources[$key]['conversion_id'] = '';
            }
        } else {
            $sources[$key]['total']  = !empty($result[$key]->total) ? $result[$key]->total : 0;
        }

        $totalLeads += !empty($sources[$key]['total']) ? $sources[$key]['total'] : 0;
    }

    $sources[] = array("name" => "Total Status Leads", "color_name" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

    return $sources;
}

function get_status_summary_filter_report($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    // $sources = $CI->leads_model->get_source();
    // $totalSource         = count($sources);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '( l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';
     if(is_seoTeam())
     {
      $whereNoViewPermission =" 1=1 ";   
     }

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        $sid = get_staff_user_id();
        $query = [];
        $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
        $staff_ids = implode(",", array_column($query_sql, 'staffid'));

        if (!empty($staff_ids)) {
            $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
        }
        $idsarr = array_column($query, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    if (!empty($params["total_status"]) && $params["total_status"] == 1) {

        $sql .= " SELECT COUNT(DISTINCT(l.id)) as total,c.id conversion_id,ls.id status_id,s.id,s.id as source_id,s.name source_name,s.color_name,s.marketing_type as marketing_id,CONCAT(l.assigned,'-',s.id) uni ";
    } else {
        $sql .= " SELECT  l.assigned,COUNT(DISTINCT(l.id)) as total,c.id conversion_id,ls.id status_id,s.id,s.id as source_id,s.name source_name,s.color_name,s.marketing_type as marketing_id,CONCAT(l.assigned,'-',s.id) uni ";
    }

    if (!empty($params['up_to_date'])) {
        $sql .= ' 
        FROM ' . db_prefix() . 'calls_activity_logs AS calls
        LEFT JOIN ' . db_prefix() . 'leads AS l 
            ON calls.contact IN (l.phonenumber, l.alternative_phonenumber)
        LEFT JOIN ' . db_prefix() . 'leads_status AS ls 
            ON ls.id = l.status
        LEFT JOIN ' . db_prefix() . 'leads_sources AS s 
            ON s.id = l.source
        LEFT JOIN ' . db_prefix() . 'lead_marketing AS m 
            ON m.id = s.marketing_type
        LEFT JOIN ' . db_prefix() . 'lead_conversion_type AS c 
            ON c.id = ls.conversion_type
    ';
    } else {
        $sql .= ' FROM ' . db_prefix() . 'leads l  left join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status  left join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';
    }


    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
        $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
    }
    // if (!empty($params['up_to_date'])) {

    //     $sql .= " join " . db_prefix() . "calls_activity_logs as calls on  calls.contact IN (l.phonenumber,l.alternative_phonenumber)";
    // }
    if (!empty($params['followup_to_date'])) {
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }

    // if (isset($status['lost'])) {
    //     $sql .= ' WHERE lost=1';
    // } elseif (isset($status['junk'])) {
    //     $sql .= ' WHERE junk=1';
    // } else {
    //     $sql .= ' WHERE status=' . $status['id'];
    // }

    $sql .= ' WHERE 1=1 ';

    if (!empty($params['source'])) {
        $sql .= " AND source IN ( " . implode(",", $params['source']) . ") ";
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        if (!empty($params['up_to_date'])) {
            $sql .= ' AND calls.staffid IN (' . implode(',', $params['assigned']) . ')';
        }
        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND  (Date(adjusted_call_start) BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }
    if (!empty($params['fb_source'])) {
        $facebook_source_name = $params['fb_source'];
        $sql .= ' AND  l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
    }

    if (!empty($params['google_source'])) {
        $google_source_name = $params['google_source'];
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
    }

    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql .= " GROUP BY s.id ";
    } else {
        $sql .= " GROUP BY assigned,s.id ";
    }
    $sql_alternative = str_replace('l.phonenumber', 'l.alternative_phonenumber', $sql);

    $sql = $sql . " UNION ALL " . $sql_alternative;
    $sql = "Select * from (" . $sql . ") tt ";
    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql .= " GROUP BY tt.source_id; ";
    } else {
        $sql .= " GROUP BY tt.assigned,tt.source_id ";
    }
    


    $result = [];
    $result = $CI->db->query($sql)->result_array();
    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        return $result;
    } else {
        return array_column($result, null, 'uni');
    }
}



function leads_update_count_($params = false, $max_status = 0, $leads_count = 0, $day_update_count = 0)
{

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    if (!empty($max_status) && $max_status == 1) {
        $sql .= " SELECT count(DISTINCT(calls.id)) as total ";
    } else if (!empty($leads_count) && $leads_count == 1) {
        $sql .= " SELECT count(DISTINCT(l.id)) as total ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " SELECT  count(DISTINCT(l.id)) as total ";
    } else {
        $sql .= " SELECT  count(DISTINCT(calls.id)) as total ";
    }

    if (!empty($leads_count) && $leads_count == 1) {
        $sql .= ",concat(Date(adjusted_call_start),'-',calls.contact) uni_dates FROM " . db_prefix() . "leads as l left join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        if (!empty($params["assign_to_date"])) {
            $sql .= ",date(l.dateassigned) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
        } else if (!empty($params["up_to_date"])) {
            $sql .= ",concat(Date(adjusted_call_start)) uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact";
        } else {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
        }
    } else {
        $sql .= ",(
            SELECT 
                Date(adjusted_call_start)
            FROM 
                " . db_prefix() . "calls_activity_logs AS calls_sub 
            WHERE 
                calls_sub.contact = l.phonenumber 
            ORDER BY 
                calls_sub.call_start DESC 
            LIMIT 1
        ) AS lastcontact";
        $sql .= ", CONCAT(
           Date(adjusted_call_start),
            '-', 
            calls.contact
        ) AS uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on  l.phonenumber = calls.contact  ";
    }


    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

        $sql .= " AND l.assigned = calls.staffid AND Date(adjusted_call_start)  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }



    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned ";
    // }

    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }



    if (!empty($params['assigned'])) {
        // $tids = " AND l.assigned = " . $params['assigned'];
        // $tids = " AND calls.staffid IN ( " . implode(",", $params['assigned']) . ") ";

        // $sql .= $tids;
    }

    $sql .= '  ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }


    $sql .= " Where 1= 1 ";

    if (!empty($params['assigned'])) {
        // $tids = " AND l.assigned = " . $params['assigned'];
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";

        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }
    if (!empty($params['source'])) {
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }




    if (!empty($params['neet_score'])) {
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        // $up_from_date = $params['up_from_date'];
        // $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/

    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND Date(adjusted_call_start) <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    } else if (!empty($params['last_update_date'])) {
        $last_update_date = $params['last_update_date'];
        $sql .= " AND  Date(adjusted_call_start) <= '{$last_update_date}' AND staffid = l.assigned  ";
    }


    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }



    if (!empty($leads_count) && $leads_count == 1) {

        if (!empty($params['status'])) {
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }
        if (!empty($params['source'])) {
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['lead_type'])) {
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['assign_to_date'])) {
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['assigned'])) {
            // $tids = " AND l.assigned = " . $params['assigned'];
            $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        }
    }

    $having = "";
    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params["last_contact_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    } else if (!empty($params['last_update_date'])) {
        $last_contact_date = $params["last_update_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    }

    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        if (!empty($having)) {
            $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        } else {
            $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }

    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($lead_count) && $lead_count == 1) {
        $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc ";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    } else {
        $sql .= " group by l.id " . $grup_by . $having . " " . $sql_add . "   ";
        // $sql .= " order by concat(l.id,'-',CAST(n.dateadded AS date)) asc ";
        $sql = trim($sql);
        // $sql = "SELECT count(total) as total_sum FROM ( {$sql} )  as subquery ";
    }


    $update_count = $CI->db->query($sql)->num_rows();

    // $result = $CI->db->query($sql)->result_array();

    // // $update_count = count(array_unique(array_column($result, "total")));
    // $update_count = count(array_count_values(array_column($result, "total")));

    return !empty($update_count) ? $update_count : 0;
}


// function leads_update_count($params = false, $max_status = 0, $leads_count = 0, $day_update_count = 0)
// {

//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $statuses = $CI->leads_model->get_status();

//     $totalStatuses         = count($statuses);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

//     $statuses[] = [
//         'lost'  => true,
//         'name'  => _l('lost_leads'),
//         'color' => '#f0f0f0',
//     ];

//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         // $this->load->database();
//         $sid = get_staff_user_id(); //48;//get_staff_user_id();
//         $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
//         $CI->db->close();
//         $CI->db->initialize();

//         // $teamids = $CI->db->query("select staffid
//         // 	from    (select * from tblstaff
//         // 	where active = '1' order by reporting_person, staffid) products_sorted,
//         // 			(select @pv := $sid) initialisation
//         // 	where   find_in_set(reporting_person, @pv)
//         // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

//         $idsarr = array_column($teamids, 'staffid');
//         $sids = implode(",", $idsarr);

//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }
//     }

//     // $sql .= ' SELECT COUNT(l.id) as total';
//     // $sql .= ' SELECT count(distinct(CAST(n.dateadded AS date))) as total';
//     if (!empty($max_status) && $max_status == 1) {
//         $sql .= " SELECT count(DISTINCT(calls.id)) as total ";
//     } else if (!empty($leads_count) && $leads_count == 1) {
//         $sql .= " SELECT count(DISTINCT(l.id)) as total ";
//     } else if (!empty($day_update_count) && $day_update_count == 1) {
//         $sql .= " SELECT  count(DISTINCT(l.id)) as total ";
//     } else {
//         $sql .= " SELECT  count(DISTINCT(calls.id)) as total ";
//     }

//     if (!empty($leads_count) && $leads_count == 1) {
//         $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'),'-',calls.contact) uni_dates FROM " . db_prefix() . "leads as l left join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
//     } else if (!empty($day_update_count) && $day_update_count == 1) {
//         if (!empty($params["up_to_date"])) {
//             $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact";
//         } else if (!empty($params["to_date"])) {
//             $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
//         } else {
//             $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
//         }
//     } else {
//         $sql .= ",(
//             SELECT 
//                 DATE_FORMAT(
//                     DATE_ADD('1970-01-01', INTERVAL (calls_sub.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
//                 ) 
//             FROM 
//                 " . db_prefix() . "calls_activity_logs AS calls_sub 
//             WHERE 
//                 calls_sub.contact = l.phonenumber 
//             ORDER BY 
//                 calls_sub.call_start DESC 
//             LIMIT 1
//         ) AS lastcontact";
//         $sql .= ", CONCAT(
//             DATE_FORMAT(
//                 DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
//             ),
//             '-', 
//             calls.contact
//         ) AS uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on  l.phonenumber = calls.contact  ";
//     }


//     if (!empty($params['up_to_date'])) {
//         $up_from_date = $params['up_from_date'];
//         $up_to_date = $params['up_to_date'];
//         // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

//         $sql .= " AND l.assigned = calls.staffid AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
//     }



//     // if (!empty($params['last_contact_date'])) {
//     //     $last_contact_date = $params['last_contact_date'];
//     //     $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned ";
//     // }

//     // if (!empty($params['last_contact_date'])) {
//     //     $last_contact_date = $params['last_contact_date'];
//     //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
//     // }



//     if (!empty($params['assigned'])) {
//         // $tids = " AND l.assigned = " . $params['assigned'];
//         // $tids = " AND calls.staffid IN ( " . implode(",", $params['assigned']) . ") ";

//         // $sql .= $tids;
//     }

//     if ((!empty($leads_count) && $leads_count == 1) || (!empty($day_update_count) && $day_update_count == 1)) {
//         $sql .= ' ) ';
//     }
//     $sql .= '  ';
//     if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
//         $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
//     }

//     if (!empty($params['followup_to_date'])) {
//         $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
//     }

//     if (!$has_permission_view) {
//         $sql .= ' AND ' . $whereNoViewPermission;
//     }


//     $sql .= " Where 1= 1 ";

//     if (!empty($params['assigned'])) {
//         // $tids = " AND l.assigned = " . $params['assigned'];
//         $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";

//         $sql .= $tids;
//     } else {
//         if ($role == 3) {
//             $sql .= $tids;
//         }
//     }

//     if (!empty($params['status'])) {
//         // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
//         $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
//     }
//     if (!empty($params['source'])) {
//         // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
//         $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//     }
//     if (!empty($params['lead_type'])) {
//         $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//         // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//     }




//     if (!empty($params['neet_score'])) {
//         $neet_range = explode("-", $params['neet_score']);
//         $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
//     }
//     if (!empty($params['to_date'])) {
//         $from_date = $params['from_date'];
//         $to_date = $params['to_date'];
//         $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     }
//     if (!empty($params['followup_to_date'])) {
//         $followup_from_date = $params['followup_from_date'];
//         $followup_to_date = $params['followup_to_date'];
//         $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     }

//     if (!empty($params['assign_to_date'])) {
//         $assign_from_date = $params['assign_from_date'];
//         $assign_to_date = $params['assign_to_date'];
//         $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     } elseif (!empty($params['up_to_date'])) {
//         // $up_from_date = $params['up_from_date'];
//         // $up_to_date = $params['up_to_date'];
//         // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//     }/*else{
//             $today = date("Y-m-d");
//             $sql .= " AND n.dateadded LIKE '%" .$today."%'";
//         }*/

//     if (!empty($params['last_contact_date'])) {
//         $last_contact_date = $params['last_contact_date'];
//         $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
//     } else if (!empty($params['last_update_date'])) {
//         $last_update_date = $params['last_update_date'];
//         $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_update_date}' AND staffid = l.assigned  ";
//     }


//     // if (!empty($params['last_contact_date'])) {
//     //     $last_contact_date = $params['last_contact_date'];
//     //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
//     // }

//     $grup_by = "";
//     if (!empty($params['neet_score'])) {
//         $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
//     }



//     if (!empty($leads_count) && $leads_count == 1) {

//         if (!empty($params['status'])) {
//             // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
//             $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
//         }
//         if (!empty($params['source'])) {
//             // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
//             $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//         }
//         if (!empty($params['lead_type'])) {
//             $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//             // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//         }
//         if (!empty($params['to_date'])) {
//             $from_date = $params['from_date'];
//             $to_date = $params['to_date'];
//             $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//         }
//         if (!empty($params['assign_to_date'])) {
//             $assign_from_date = $params['assign_from_date'];
//             $assign_to_date = $params['assign_to_date'];
//             $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//         }
//         if (!empty($params['assigned'])) {
//             // $tids = " AND l.assigned = " . $params['assigned'];
//             $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
//         }
//     }

//     $having = "";
//     if (!empty($params['last_contact_date'])) {
//         $last_contact_date = $params["last_contact_date"];
//         $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
//     } else if (!empty($params['last_update_date'])) {
//         $last_contact_date = $params["last_update_date"];
//         $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
//     }

//     $sql_add = "";
//     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//         $min = $params['update_count_min'];
//         $max = $params['update_count_max'];
//         if (!empty($having)) {
//             $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//         } else {
//             $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//         }
//     }

//     if (!empty($max_status) && $max_status == 1) {
//         $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
//         $sql = trim($sql);
//         $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
//     } else if (!empty($lead_count) && $lead_count == 1) {
//         $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
//         $sql = trim($sql);
//         $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
//     } else if (!empty($day_update_count) && $day_update_count == 1) {
//         $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc ";
//         return $update_count = $CI->db->query($sql)->result_array();
//         die;
//     } else {
//         $sql .= " group by l.id " . $grup_by . $having . " " . $sql_add . "   ";
//         // $sql .= " order by concat(l.id,'-',CAST(n.dateadded AS date)) asc ";
//         $sql = trim($sql);
//         $sql = "SELECT count(total) as total_sum FROM ( {$sql} )  as subquery ";
//     }



//     $update_count = $CI->db->query($sql)->row()->total_sum;

//     // $result = $CI->db->query($sql)->result_array();

//     // // $update_count = count(array_unique(array_column($result, "total")));
//     // $update_count = count(array_count_values(array_column($result, "total")));

//     return !empty($update_count) ? $update_count : 0;
// }



function leads_update_count($params = false, $max_status = 0, $leads_count = 0, $day_update_count = 0)
{


    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();
    $check_today = true;

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    // $sql .= ' SELECT COUNT(l.id) as total';
    // $sql .= ' SELECT count(distinct(CAST(n.dateadded AS date))) as total';
    if (!empty($max_status) && $max_status == 1) {
        $sql .= " SELECT count(DISTINCT(calls.id)) as total ";
    } else if (!empty($leads_count) && $leads_count == 1) {
        $sql .= " SELECT count(DISTINCT(l.id)) as total ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " SELECT  count(DISTINCT(l.id)) as total ";
    } else {
        $sql .= " SELECT  count(DISTINCT(calls.id)) as total ";
    }

    if (!empty($leads_count) && $leads_count == 1) {
        $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'),'-',calls.contact) uni_dates FROM " . db_prefix() . "leads as l left join " . db_prefix() . "calls_activity_logs as calls on ( calls.contact in (l.phonenumber,l.alternative_phonenumber ) and calls.staffid = l.assigned ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        if (!empty($params["assign_to_date"])) {
            $sql .= ",date(l.dateassigned) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( calls.contact in (l.phonenumber,l.alternative_phonenumber ) and calls.staffid = l.assigned ";
        } else  if (!empty($params["up_to_date"])) {
            $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( calls.contact in (l.phonenumber,l.alternative_phonenumber ) and calls.staffid = l.assigned";
        } else {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( calls.contact in (l.phonenumber,l.alternative_phonenumber)  and calls.staffid = l.assigned ";
        }
    } else {
        $sql .= ",(
            SELECT 
                DATE_FORMAT(
                    DATE_ADD('1970-01-01', INTERVAL (calls_sub.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
                ) 
            FROM 
                " . db_prefix() . "calls_activity_logs AS calls_sub 
            WHERE 
                calls_sub.contact = l.phonenumber 
            ORDER BY 
                calls_sub.call_start DESC 
            LIMIT 1
        ) AS lastcontact";
        $sql .= ", CONCAT(
            DATE_FORMAT(
                DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
            ),
            '-', 
            calls.contact
        ) AS uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on  l.phonenumber = calls.contact  ";
    }


    if (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

        $sql .= " AND l.assigned = calls.staffid AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }



    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned ";
    // }

    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }



    if (!empty($params['assigned'])) {
        $check_today = false;
        // $tids = " AND l.assigned = " . $params['assigned'];
        // $tids = " AND calls.staffid IN ( " . implode(",", $params['assigned']) . ") ";

        // $sql .= $tids;
    }

    if ((!empty($leads_count) && $leads_count == 1) || (!empty($day_update_count) && $day_update_count == 1)) {
        $sql .= ' ) ';
    }
    $sql .= '  ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }


    $sql .= " Where 1= 1 ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        // $tids = " AND l.assigned = " . $params['assigned'];
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";

        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        $check_today = false;
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }
    if (!empty($params['source'])) {
        $check_today = false;
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }




    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }
    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
        // $up_from_date = $params['up_from_date'];
        // $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_update_date}' AND staffid = l.assigned  ";
    }


    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $check_today = false;
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }



    if (!empty($leads_count) && $leads_count == 1) {

        if (!empty($params['status'])) {
            $check_today = false;
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }
        if (!empty($params['source'])) {
            $check_today = false;
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['lead_type'])) {
            $check_today = false;
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $check_today = false;
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['assign_to_date'])) {
            $check_today = false;
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['assigned'])) {
            $check_today = false;
            // $tids = " AND l.assigned = " . $params['assigned'];
            $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        }
    }


    if ($check_today === true) {

        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }

    $having = "";
    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params["last_contact_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    } else if (!empty($params['last_update_date'])) {
        $last_contact_date = $params["last_update_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    }

    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        if (!empty($having)) {
            $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        } else {
            $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }

    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($lead_count) && $lead_count == 1) {
        $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc  limit 50";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    } else {
        $sql .= " group by l.id " . $grup_by . $having . " " . $sql_add . "   ";
        // $sql .= " order by concat(l.id,'-',CAST(n.dateadded AS date)) asc ";
        $sql = trim($sql);
        $sql = "SELECT count(total) as total_sum FROM ( {$sql} )  as subquery ";
    }


    $update_count = $CI->db->query($sql)->row()->total_sum;

    // $result = $CI->db->query($sql)->result_array();

    // // $update_count = count(array_unique(array_column($result, "total")));
    // $update_count = count(array_count_values(array_column($result, "total")));

    return !empty($update_count) ? $update_count : 0;
}

function leads_update_count_pri($params = false, $max_status = 0, $leads_count = 0, $day_update_count = 0)
{

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();
    $check_today = true;

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    // $sql .= ' SELECT COUNT(l.id) as total';
    // $sql .= ' SELECT count(distinct(CAST(n.dateadded AS date))) as total';
    if (!empty($max_status) && $max_status == 1) {
        $sql .= " SELECT l.id,count(DISTINCT(calls.id)) as total ";
    } else if (!empty($leads_count) && $leads_count == 1) {
        $sql .= " SELECT l.id,count(DISTINCT(l.id)) as total ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " SELECT l.id, count(DISTINCT(l.id)) as total ";
    } else {
        $sql .= " SELECT l.id, count(DISTINCT(calls.id)) as total ";
    }

    if (!empty($leads_count) && $leads_count == 1) {
        $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'),'-',calls.contact) uni_dates FROM " . db_prefix() . "leads as l left join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        if (!empty($params["up_to_date"])) {
            $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact";
        } else if (!empty($params["to_date"])) {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
        } else {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";
        }
    } else {
        $sql .= ",(
            SELECT 
                DATE_FORMAT(
                    DATE_ADD('1970-01-01', INTERVAL (calls_sub.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
                ) 
            FROM 
                " . db_prefix() . "calls_activity_logs AS calls_sub 
            WHERE 
                calls_sub.contact = l.phonenumber 
            ORDER BY 
                calls_sub.call_start DESC 
            LIMIT 1
        ) AS lastcontact";
        $sql .= ", CONCAT(
            DATE_FORMAT(
                DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
            ),
            '-', 
            calls.contact
        ) AS uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on  l.phonenumber = calls.contact  ";
    }


    if (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

        $sql .= " AND l.assigned = calls.staffid AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }



    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned ";
    // }

    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }



    if (!empty($params['assigned'])) {
        $check_today = false;
        // $tids = " AND l.assigned = " . $params['assigned'];
        // $tids = " AND calls.staffid IN ( " . implode(",", $params['assigned']) . ") ";

        // $sql .= $tids;
    }

    if ((!empty($leads_count) && $leads_count == 1) || (!empty($day_update_count) && $day_update_count == 1)) {
        $sql .= ' ) ';
    }
    $sql .= '  ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }


    $sql .= " Where 1= 1 ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        // $tids = " AND l.assigned = " . $params['assigned'];
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";

        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        $check_today = false;
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }
    if (!empty($params['source'])) {
        $check_today = false;
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }




    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }
    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
        // $up_from_date = $params['up_from_date'];
        // $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_update_date}' AND staffid = l.assigned  ";
    }


    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $check_today = false;
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }



    if (!empty($leads_count) && $leads_count == 1) {

        if (!empty($params['status'])) {
            $check_today = false;
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }
        if (!empty($params['source'])) {
            $check_today = false;
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['lead_type'])) {
            $check_today = false;
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $check_today = false;
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['assign_to_date'])) {
            $check_today = false;
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['assigned'])) {
            $check_today = false;
            // $tids = " AND l.assigned = " . $params['assigned'];
            $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        }
    }


    if ($check_today === true) {

        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }

    $having = "";
    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params["last_contact_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    } else if (!empty($params['last_update_date'])) {
        $last_contact_date = $params["last_update_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    }

    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        if (!empty($having)) {
            $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        } else {
            $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }

    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($lead_count) && $lead_count == 1) {
        $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc ";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    } else {
        $sql .= " group by l.id " . $grup_by . $having . " " . $sql_add . "   ";
        // $sql .= " order by concat(l.id,'-',CAST(n.dateadded AS date)) asc ";
        $sql = trim($sql);
        // $sql = "SELECT count(total) as total_sum FROM ( {$sql} )  as subquery ";

        return $update_count = $CI->db->query($sql)->result_array();
        die;
    }


    $update_count = $CI->db->query($sql)->row()->total_sum;

    // $result = $CI->db->query($sql)->result_array();

    // // $update_count = count(array_unique(array_column($result, "total")));
    // $update_count = count(array_count_values(array_column($result, "total")));

    return !empty($update_count) ? $update_count : 0;
}
function leads_update_count_sec($params = false, $max_status = 0, $leads_count = 0, $day_update_count = 0)
{

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();
    $check_today = true;

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    // $sql .= ' SELECT COUNT(l.id) as total';
    // $sql .= ' SELECT count(distinct(CAST(n.dateadded AS date))) as total';
    if (!empty($max_status) && $max_status == 1) {
        $sql .= " SELECT l.id,count(DISTINCT(calls.id)) as total ";
    } else if (!empty($leads_count) && $leads_count == 1) {
        $sql .= " SELECT l.id,count(DISTINCT(l.id)) as total ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " SELECT  l.id,count(DISTINCT(l.id)) as total ";
    } else {
        $sql .= " SELECT  l.id,count(DISTINCT(calls.id)) as total ";
    }

    if (!empty($leads_count) && $leads_count == 1) {
        $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'),'-',calls.contact) uni_dates FROM " . db_prefix() . "leads as l left join " . db_prefix() . "calls_activity_logs as calls on ( l.alternative_phonenumber = calls.contact  ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        if (!empty($params["up_to_date"])) {
            $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.alternative_phonenumber = calls.contact";
        } else if (!empty($params["to_date"])) {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.alternative_phonenumber = calls.contact  ";
        } else {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "leads as l  join " . db_prefix() . "calls_activity_logs as calls on ( l.alternative_phonenumber = calls.contact  ";
        }
    } else {
        $sql .= ",(
            SELECT 
                DATE_FORMAT(
                    DATE_ADD('1970-01-01', INTERVAL (calls_sub.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
                ) 
            FROM 
                " . db_prefix() . "calls_activity_logs AS calls_sub 
            WHERE 
                calls_sub.contact = l.alternative_phonenumber 
            ORDER BY 
                calls_sub.call_start DESC 
            LIMIT 1
        ) AS lastcontact";
        $sql .= ", CONCAT(
            DATE_FORMAT(
                DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'
            ),
            '-', 
            calls.contact
        ) AS uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on  l.alternative_phonenumber = calls.contact  ";
    }


    if (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

        $sql .= " AND l.assigned = calls.staffid AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }



    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned ";
    // }

    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }



    if (!empty($params['assigned'])) {
        $check_today = false;
        // $tids = " AND l.assigned = " . $params['assigned'];
        // $tids = " AND calls.staffid IN ( " . implode(",", $params['assigned']) . ") ";

        // $sql .= $tids;
    }

    if ((!empty($leads_count) && $leads_count == 1) || (!empty($day_update_count) && $day_update_count == 1)) {
        $sql .= ' ) ';
    }
    $sql .= '  ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }


    $sql .= " Where 1= 1 ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        // $tids = " AND l.assigned = " . $params['assigned'];
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";

        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        $check_today = false;
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }
    if (!empty($params['source'])) {
        $check_today = false;
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }




    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }
    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
        // $up_from_date = $params['up_from_date'];
        // $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_update_date}' AND staffid = l.assigned  ";
    }


    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $check_today = false;
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }



    if (!empty($leads_count) && $leads_count == 1) {

        if (!empty($params['status'])) {
            $check_today = false;
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }
        if (!empty($params['source'])) {
            $check_today = false;
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['lead_type'])) {
            $check_today = false;
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $check_today = false;
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['assign_to_date'])) {
            $check_today = false;
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['assigned'])) {
            $check_today = false;
            // $tids = " AND l.assigned = " . $params['assigned'];
            $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        }
    }


    if ($check_today === true) {

        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }

    $having = "";
    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params["last_contact_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    } else if (!empty($params['last_update_date'])) {
        $last_contact_date = $params["last_update_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    }

    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        if (!empty($having)) {
            $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        } else {
            $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }

    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($lead_count) && $lead_count == 1) {
        $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc ";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    } else {
        $sql .= " group by l.id " . $grup_by . $having . " " . $sql_add . "   ";
        // $sql .= " order by concat(l.id,'-',CAST(n.dateadded AS date)) asc ";
        $sql = trim($sql);
        // $sql = "SELECT count(total) as total_sum FROM ( {$sql} )  as subquery ";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    }


    $update_count = $CI->db->query($sql)->row()->total_sum;

    // $result = $CI->db->query($sql)->result_array();

    // // $update_count = count(array_unique(array_column($result, "total")));
    // $update_count = count(array_count_values(array_column($result, "total")));

    return !empty($update_count) ? $update_count : 0;
}


function leads_update_count_duration($params = false, $alternative = 0)
{

    $CI = &get_instance();
    // if (!class_exists('leads_model')) {
    //     $CI->load->model('leads_model');
    // }
    // $statuses = $CI->leads_model->get_status();
    $check_today = true;

    // $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    // $statuses[] = [
    //     'lost'  => true,
    //     'name'  => _l('lost_leads'),
    //     'color' => '#f0f0f0',
    // ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }


    $sql .= " SELECT l.id, count(DISTINCT(calls.id)) as total,SUM(duration) as duration FROM " . db_prefix() . "leads as l ";

    if (!empty($alternative) && $alternative == 1) {
        $sql .= " join " . db_prefix() . "calls_activity_logs as calls on  calls.contact IN (l.alternative_phonenumber) ";
    } else {
        $sql .= " join " . db_prefix() . "calls_activity_logs as calls on  calls.contact IN (l.phonenumber) ";
    }

    if (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];

        $sql .= " AND l.assigned = calls.staffid AND  DATE(adjusted_call_start) between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }



    if (!empty($params['assigned'])) {
        $check_today = false;
    }

    if ((!empty($leads_count) && $leads_count == 1) || (!empty($day_update_count) && $day_update_count == 1)) {
        $sql .= ' ) ';
    }
    $sql .= '  ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }


    $sql .= " Where 1= 1 ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        $check_today = false;
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }
    if (!empty($params['source'])) {
        $check_today = false;
        $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }




    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }
    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
    }

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_update_date}' AND staffid = l.assigned  ";
    }


    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $check_today = false;
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }



    if (!empty($leads_count) && $leads_count == 1) {

        if (!empty($params['status'])) {
            $check_today = false;
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }
        if (!empty($params['source'])) {
            $check_today = false;
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['lead_type'])) {
            $check_today = false;
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        }
        if (!empty($params['to_date'])) {
            $check_today = false;
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['assign_to_date'])) {
            $check_today = false;
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['assigned'])) {
            $check_today = false;
            $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        }
    }


    if ($check_today === true) {

        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }


    $having = "";
    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params["last_contact_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    } else if (!empty($params['last_update_date'])) {
        $last_contact_date = $params["last_update_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    }

    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        if (!empty($having)) {
            $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        } else {
            $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }

    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($lead_count) && $lead_count == 1) {
        $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum  FROM ( {$sql} )  as subquery ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc ";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    } else {
        $sql .= " group by l.id " . $grup_by . $having . " " . $sql_add . "   ";
        $sql = trim($sql);

        return $update_count = $CI->db->query("SELECT count(total) as update_count,SUM(duration) as duration FROM (" . $sql . " )  as subquery")->result_array();
    }


    $update_count = $CI->db->query($sql)->row()->total_sum;

    return !empty($update_count) ? $update_count : 0;
}


function leads_update_count_report($params = false, $max_status = 0, $leads_count = 0, $day_update_count = 0)
{

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    // $sql .= ' SELECT COUNT(l.id) as total';
    // $sql .= ' SELECT count(distinct(CAST(n.dateadded AS date))) as total';
    if (!empty($max_status) && $max_status == 1) {
        $sql .= " SELECT count(DISTINCT(calls.id)) as total ";
    } else if (!empty($leads_count) && $leads_count == 1) {
        $sql .= " SELECT l.assigned,count(DISTINCT(l.id)) as total,count(calls.id) ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " SELECT  count(DISTINCT(l.id)) as total ";
    } else {
        $sql .= " SELECT  count(DISTINCT(calls.id)) as total ";
    }

    if (!empty($leads_count) && $leads_count == 1) {
        $sql .= ",concat(Date(adjusted_call_start),'-',calls.contact) uni_dates FROM " . db_prefix() . "calls_activity_logs as calls  join " . db_prefix() . "leads as l on ( calls.contact IN (l.phonenumber)  ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        if (!empty($params["up_to_date"])) {
            $sql .= ",concat(Date(adjusted_call_start)) uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration  FROM " . db_prefix() . "calls_activity_logs as calls  join " . db_prefix() . "leads as l on ( calls.contact IN (l.phonenumber) ";
        } else if (!empty($params["to_date"])) {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration   FROM " . db_prefix() . "calls_activity_logs as calls  join " . db_prefix() . "leads as l on ( calls.contact IN (l.phonenumber) ";
        } else {
            $sql .= ",date(l.dateadded) as uni_dates, SUM( CASE WHEN LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') THEN calls.duration ELSE NULL END) AS call_duration   FROM " . db_prefix() . "calls_activity_logs as calls  join " . db_prefix() . "leads as l on ( calls.contact IN (l.phonenumber)  ";
        }
    } else {
        $sql .= ",(
            SELECT 
                Date(adjusted_call_start)
            FROM 
                " . db_prefix() . "calls_activity_logs AS calls_sub 
            WHERE 
                calls_sub.contact = l.phonenumber 
            ORDER BY 
                calls_sub.call_start DESC 
            LIMIT 1
        ) AS lastcontact";
        $sql .= ", CONCAT(
            Date(adjusted_call_start),
            '-', 
            calls.contact
        ) AS uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on  l.phonenumber = calls.contact  ";
    }


    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

        $sql .= " AND l.assigned = calls.staffid AND  Date(adjusted_call_start)  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }



    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '{$last_contact_date}' AND staffid = l.assigned ";
    // }

    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }



    if (!empty($params['assigned'])) {
        // $tids = " AND l.assigned = " . $params['assigned'];
        // $tids = " AND calls.staffid IN ( " . implode(",", $params['assigned']) . ") ";

        // $sql .= $tids;
    }

    if ((!empty($leads_count) && $leads_count == 1) || (!empty($day_update_count) && $day_update_count == 1)) {
        $sql .= ' ) ';
    }
    $sql .= '  ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $sql .= ' join ' . db_prefix() . 'reminders  on  ' . db_prefix() . 'reminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }


    $sql .= " Where 1= 1 ";

    if (!empty($params['assigned'])) {
        // $tids = " AND l.assigned = " . $params['assigned'];
        $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";

        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['status'])) {
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }
    if (!empty($params['source'])) {
        // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
        $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }




    if (!empty($params['neet_score'])) {
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        // $up_from_date = $params['up_from_date'];
        // $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/

    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND Date(adjusted_call_start) <= '{$last_contact_date}' AND staffid = l.assigned and LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    } else if (!empty($params['last_update_date'])) {
        $last_update_date = $params['last_update_date'];
        $sql .= " AND  Date(adjusted_call_start) <= '{$last_update_date}' AND staffid = l.assigned  ";
    }


    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params['last_contact_date'];
    //     $sql .= " AND  lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "' ";
    // }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }



    if (!empty($leads_count) && $leads_count == 1) {

        if (!empty($params['status'])) {
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND l.status in (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
        }
        if (!empty($params['source'])) {
            // $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }
        if (!empty($params['lead_type'])) {
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['assign_to_date'])) {
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }
        if (!empty($params['assigned'])) {
            // $tids = " AND l.assigned = " . $params['assigned'];
            $sql .= " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
        }
    }

    $having = "";
    if (!empty($params['last_contact_date'])) {
        $last_contact_date = $params["last_contact_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    } else if (!empty($params['last_update_date'])) {
        $last_contact_date = $params["last_update_date"];
        $having .= " Having lastcontact <= '" . $last_contact_date . "'  or lastcontact is NULL  ";
    }

    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        if (!empty($having)) {
            $sql_add = ' AND COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        } else {
            $sql_add = ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND  "' . $CI->db->escape_str($max) . '" ';
        }
    }


    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . $having . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($lead_count) && $lead_count == 1) {
        $sql .= " group by l.id  " . $having . "order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else if (!empty($day_update_count) && $day_update_count == 1) {
        $sql .= " group by date(uni_dates) " . $having . "order by date(uni_dates) asc ";
        return $update_count = $CI->db->query($sql)->result_array();
        die;
    } else {
        if (!empty($params["total_status"]) && $params["total_status"] == 1) {
            $sql .= " group by 1" . $grup_by . $having . " " . $sql_add . "   ";
        } else {
            $sql .= " group by l.assigned,l.id " . $grup_by . $having . " " . $sql_add . "   ";
        }
        // $sql .= " order by concat(l.id,'-',CAST(n.dateadded AS date)) asc ";

        // $sql = "SELECT count(total) as total_sum FROM ( {$sql} )  as subquery ";
    }
    if (!empty($leads_count) && $leads_count == 1) {
        // $sql .= " limit 10 ";
    }


    $update_count = $CI->db->query($sql)->result_array();
    $sql = str_replace('l.phonenumber', 'l.alternative_phonenumber', $sql);
    $update_count_alternative = $CI->db->query($sql)->result_array();
    $callsByDate = [];


    foreach ($update_count as $entry) {
        if (!empty($entry['uni_dates'])) {
            if (!isset($callsByDate[$entry['assigned']])) {
                $callsByDate[$entry['assigned']] = [
                    "assigned" => $entry['assigned'],
                    "total" => 0
                ];
            }
            $callsByDate[$entry['assigned']]["total"] += $entry['total'];
        }
    }
    foreach ($update_count_alternative as $entry) {
        if (!empty($entry['uni_dates'])) {
            if (!isset($callsByDate[$entry['assigned']])) {
                $callsByDate[$entry['assigned']] = [
                    "assigned" => $entry['assigned'],
                    "total" => 0
                ];
            }
            $callsByDate[$entry['assigned']]["total"] += $entry['total'];
        }
    }

    // To limit the results to 10, you can use array_slice
    // $callsByDate = array_slice($callsByDate, 0, 10);




    return $callsByDate;
    // $result = $CI->db->query($sql)->result_array();

    // // $update_count = count(array_unique(array_column($result, "total")));
    // $update_count = count(array_count_values(array_column($result, "total")));

    // return !empty($update_count) ? array_column($update_count, null, "assigned") : [];
}

function leads_update_count_id($id, $params = false)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $sql .= ' SELECT COUNT(l.id) as total';
    $sql .= ' FROM ' . db_prefix() . 'leads as l inner join tblnotes as n on l.id = n.rel_id ';
    if (!empty($params['followup_to_date'])) {
        $sql .= ' Left join tblreminders  on  tblreminders.rel_id = l.id ';
    }

    $sql .= '  WHERE l.id=' . $id . ' AND l.id > 0 ';
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        $tids = " AND l.assigned = " . $params['assigned'];
        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }
    if (!empty($params['source'])) {
        $sql .= ' AND l.source =' . $CI->db->escape_str($params['source']);
    }
    // if (!empty($params['lead_type'])) {
    //     $sql .= ' AND l.type =' . $CI->db->escape_str($params['lead_type']);
    // }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } else if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/
    $sql = trim($sql); //print_r($sql);die;
    $result = $CI->db->query($sql)->row()->total;
    return $result;
}

/**
 * Render lead status select field with ability to create inline statuses with + sign
 * @param  array  $statuses         current statuses
 * @param  string  $selected        selected status
 * @param  string  $lang_key        the label of the select
 * @param  string  $name            the name of the select
 * @param  array   $select_attrs    additional select attributes
 * @param  boolean $exclude_default whether to exclude default Client status
 * @return string
 */
function render_leads_status_select($statuses, $selected = '', $lang_key = '', $name = 'status', $select_attrs = [], $exclude_default = false)
{
    foreach ($statuses as $key => $status) {
        if ($status['isdefault'] == 1) {
            if ($exclude_default == false) {
                $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
            } else {
                unset($statuses[$key]);
            }

            break;
        }
    }

    if (is_admin() || get_option('staff_members_create_inline_lead_status') == '1') {
        return render_select_with_input_group($name, $statuses, ['id', 'name'], $lang_key, $selected, '<a href="#" onclick="new_lead_status_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a>', $select_attrs);
    }

    return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

function render_leads_type_select($statuses, $selected = '', $lang_key = '', $name = 'type', $select_attrs = [], $exclude_default = false)
{
    // print_r($statuses);die;
    // foreach ($statuses as $key => $status) {
    //     if ($status['isdefault'] == 1) {
    //         if ($exclude_default == false) {
    //             $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
    //         } else {
    //             unset($statuses[$key]);
    //         }

    //         break;
    //     }
    // }

    return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

/**
 * Render lead source select field with ability to create inline source with + sign
 * @param  array   $sources         current sourcees
 * @param  string  $selected        selected source
 * @param  string  $lang_key        the label of the select
 * @param  string  $name            the name of the select
 * @param  array   $select_attrs    additional select attributes
 * @return string
 */
function render_leads_source_select($sources, $selected = '', $lang_key = '', $name = 'source', $select_attrs = [])
{
    if (is_admin() || get_option('staff_members_create_inline_lead_source') == '1') {
        echo render_select_with_input_group($name, $sources, ['id', 'name'], $lang_key, $selected, '<a href="#" onclick="new_lead_source_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a>', $select_attrs);
    } else {
        echo render_select($name, $sources, ['id', 'name'], $lang_key, $selected, $select_attrs);
    }
}

/**
 * Load lead language
 * Used in public GDPR form
 * @param  string $lead_id
 * @return string return loaded language
 */
function load_lead_language($lead_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $lead_id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();

    // Lead not found or default language already loaded
    if (!$lead || empty($lead->default_language)) {
        return false;
    }

    $language = $lead->default_language;

    if (!file_exists(APPPATH . 'language/' . $language)) {
        return false;
    }

    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];

    $CI->lang->load($language . '_lang', $language);
    if (file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')) {
        $CI->lang->load('custom_lang', $language);
    }

    return true;
}

function get_leads_summary_filter_excel($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();


    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    // $statuses[] = [
    //     'lost'  => true,
    //     'name'  => _l('lost_leads'),
    //     'color' => '#f0f0f0',
    // ];


    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }

        //         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
    }

    foreach ($statuses as $status) {
        $sql .= ' SELECT COUNT(DISTINCT(l.id)) as total,c.id conversion_id,m.id marketing_id, ls.name status_name ,s.name source_name,concat(ls.name,"-",s.name) index_name ';
        $sql .= ' FROM ' . db_prefix() . 'leads l inner join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status inner join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

        if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
            $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
        }
        if (!empty($params['up_to_date'])) {
            // $up_from_date_join = $params['up_from_date'];
            // $up_to_date_join = $params['up_to_date'];
            // $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';

            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact )";
        }
        if (!empty($params['followup_to_date'])) {
            $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
        }

        if (isset($status['lost'])) {
            $sql .= ' WHERE lost=1';
        } elseif (isset($status['junk'])) {
            $sql .= ' WHERE junk=1';
        } else {
            $sql .= ' WHERE l.status=' . $status['id'];
        }
        if (!$has_permission_view) {
            $sql .= ' AND ' . $whereNoViewPermission;
        }
        if (!empty($params['assigned'])) {
            // $tids = " AND assigned = " . $params['assigned'];
            $tids = " AND l.assigned IN ( " . implode(",", $params['assigned']) . ") ";
            $sql .= $tids;
        } else {
            if ($role == 3) {
                $sql .= $tids;
            }
        }

        if (!empty($params['source'])) {
            $sql .= ' AND l.source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }




        // if (!empty($params['source'])) {
        //     $sql .= ' AND source =' . $CI->db->escape_str($params['source']);
        // }

        /*if (isset($params['course'])) {
            $sql .= 'AND tblcustomfieldsvalues.value ='.$params['course'];
        }


        if (isset($params['degree'])) {
            $sql .= 'AND tblcustomfieldsvalues.value ='.$params['degree'];
        }*/

        // if (!empty($params['lead_type'])) {
        //     $sql .= ' AND l.type =' . $CI->db->escape_str($params['lead_type']);
        // }
        if (!empty($params['lead_type'])) {
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
        }
        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
            // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

            $sql .= " AND  ( DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
                . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
        }
        if (!empty($params['followup_to_date'])) {
            $followup_from_date = $params['followup_from_date'];
            $followup_to_date = $params['followup_to_date'];
            $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
        }

        if (!empty($params['assign_to_date'])) {
            $assign_from_date = $params['assign_from_date'];
            $assign_to_date = $params['assign_to_date'];
            $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
        }

        if (!empty($params['fb_source'])) {
            $facebook_source_name = $params['fb_source'];
            $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
        }
        if (!empty($params['google_source'])) {
            $google_source_name = $params['google_source'];
            $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
        }

        $sql .= '  GROUP BY l.source,l.status ';
        $sql .= ' UNION ALL ';
        $sql = trim($sql);
    }
    $result = [];

    // Remove the last UNION ALL
    $sql    = substr($sql, 0, -10);

    $result = $CI->db->query($sql)->result_array();

    if (!empty($result)) {
        $result = array_column($result, null, "index_name");
    }

    // if (!$has_permission_view) {
    //     $CI->db->where($whereNoViewPermission);
    // }

    // $total_leads = $CI->db->count_all_results(db_prefix() . 'leads');


    // foreach ($statuses as $key => $status) {
    //     if (isset($status['lost']) || isset($status['junk'])) {
    //         $statuses[$key]['percent'] = ($total_leads > 0 ? number_format(($result[$key]->total * 100) / $total_leads, 2) : 0);
    //     }

    //     $statuses[$key]['total'] = $result[$key]->total;
    // }


    return $result;
}


function get_leads_summary_filter_excel_report($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();
    $totalStatuses = count($statuses);


    $has_permission_view = has_permission('leads', '', 'view');
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned = ' . get_staff_user_id() . ' OR l.is_public = 1)';
    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    $tids = '';

    if ($role == 3) {
        $sid = get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned IN (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned IN (' . $sid . ')';
        }
    }
    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql = "SELECT 'Total Details' assigned, 
        COUNT(DISTINCT(l.id)) AS total,
        c.id AS conversion_id,
        m.id AS marketing_id,
        ls.name AS status_name,
        s.name AS source_name,
        CONCAT(ls.name, '-', s.name) AS index_name
    FROM 
        " . db_prefix() . "leads l
    INNER JOIN " . db_prefix() . "leads_status ls ON ls.id = l.status
    INNER JOIN " . db_prefix() . "leads_sources s ON s.id = l.source
    LEFT JOIN " . db_prefix() . "lead_marketing m ON m.id = s.marketing_type
    LEFT JOIN " . db_prefix() . "lead_conversion_type c ON c.id = ls.conversion_type";
    } else {
        $sql = "SELECT l.assigned, 
        COUNT(DISTINCT(l.id)) AS total,
        c.id AS conversion_id,
        m.id AS marketing_id,
        ls.name AS status_name,
        s.name AS source_name,
        CONCAT(ls.name, '-', s.name) AS index_name
    FROM 
        " . db_prefix() . "leads l
    INNER JOIN " . db_prefix() . "leads_status ls ON ls.id = l.status
    INNER JOIN " . db_prefix() . "leads_sources s ON s.id = l.source
    LEFT JOIN " . db_prefix() . "lead_marketing m ON m.id = s.marketing_type
    LEFT JOIN " . db_prefix() . "lead_conversion_type c ON c.id = ls.conversion_type";
    }

    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
        $sql .= ' JOIN tblcustomfieldsvalues ON l.id = tblcustomfieldsvalues.relid';
    }
    if (!empty($params['up_to_date'])) {
        $sql .= " JOIN " . db_prefix() . "calls_activity_logs AS calls ON (l.phonenumber = calls.contact)";
    }
    if (!empty($params['followup_to_date'])) {
        $sql .= ' JOIN tblreminders ON tblreminders.rel_id = l.id';
    }

    $sql .= ' WHERE 1=1';

    if (isset($status['lost'])) {
        $sql .= ' AND lost = 1';
    } elseif (isset($status['junk'])) {
        $sql .= ' AND junk = 1';
    } else {
        if (!empty($params['status'])) {
            $sql .= ' AND l.status IN (' . implode(",", $params['status']) . ')';
        }
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        $sql .= ' AND l.assigned IN (' . implode(",", $params['assigned']) . ')';
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['source'])) {
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $sql .= ' AND type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "'";
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }
    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }
    if (!empty($params['fb_source'])) {
        $facebook_source_name = $params['fb_source'];
        $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
    }
    if (!empty($params['google_source'])) {
        $google_source_name = $params['google_source'];
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
    }

    $sql .= ' GROUP BY assigned, l.source, c.id';

    $result = $CI->db->query($sql)->result_array();

    // if (!empty($result)) {
    //     $result = array_column($result, null, "index_name_staff");
    // }

    $data_array = []; // Initialize an empty array to hold grouped data

    // Iterate through each result from the query
    foreach ($result as $res) {
        // Use the 'assigned' field as the key to group data
        $assignedId = $res["assigned"];

        // Initialize the array for this 'assigned' key if it doesn't exist
        if (!isset($data_array[$assignedId])) {
            $data_array[$assignedId] = [];
        }

        // Append the current record to the group corresponding to the 'assigned' key
        $data_array[$assignedId][$res["index_name"]] = $res;
    }

    // $data_array now contains the data grouped by the 'assigned' field

    return $data_array;
}


// function get_status_summary_filter_performance($params, $conversion_status = 0)
// {
//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $statuses = $CI->leads_model->get_status();


//     $totalStatuses         = count($statuses);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '( l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

//     // $statuses[] = [
//     //     'lost'  => true,
//     //     'name'  => _l('lost_leads'),
//     //     'color' => '#f0f0f0',
//     // ];


//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         // $this->load->database();
//         $sid = get_staff_user_id(); //48;//get_staff_user_id();

//         $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
//         $CI->db->close();
//         $CI->db->initialize();

//         // $teamids = $CI->db->query("select staffid
//         // 	from    (select * from tblstaff
//         // 	where active = '1' order by reporting_person, staffid) products_sorted,
//         // 			(select @pv := $sid) initialisation
//         // 	where   find_in_set(reporting_person, @pv)
//         // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

//         $idsarr = array_column($teamids, 'staffid');
//         $sids = implode(",", $idsarr);
//         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';


//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }
//     }

//     foreach ($statuses as $status) {
//         $sql .= ' SELECT COUNT(DISTINCT(l.id)) as total,c.id conversion_id,m.id marketing_id,m.name marketing_name,c.name conversion_name, ls.name status_name ,s.name source_name,concat(ls.name,"-",s.name) index_name,s.id source_id,ls.id status_id,concat(s.name,"-",c.name) index_conversion_name,concat(m.name,"-",c.name) index_performance_name ';
//         $sql .= ' FROM ' . db_prefix() . 'leads l inner join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status inner join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

//         // $sql .=' FROM ' . db_prefix() . 'lead_marketing m left join ' . db_prefix() . 'leads_sources s ON s.marketing_type = m.id left join ' . db_prefix() . 'leads l ON s.id = l.source left join ' . db_prefix() . 'leads_status ls ON  ls.id = l.status left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

//         if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
//             $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
//         }
//         if (!empty($params['up_to_date'])) {
//             $up_from_date_join = $params['up_from_date'];
//             $up_to_date_join = $params['up_to_date'];
//             // $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';
//             $sql .= " join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact )";
//         }
//         if (!empty($params['followup_to_date'])) {
//             $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
//         }

//         if (isset($status['lost'])) {
//             $sql .= ' WHERE lost=1';
//         } elseif (isset($status['junk'])) {
//             $sql .= ' WHERE junk=1';
//         } else {
//             $sql .= ' WHERE l.status=' . $status['id'];
//         }
//         if (!$has_permission_view) {
//             $sql .= ' AND ' . $whereNoViewPermission;
//         }
//         if (!empty($params['assigned'])) {
//             // $tids = " AND assigned = " . $params['assigned'];
//             $tids = " AND l.assigned IN ( " . implode(",", $params['assigned']) . ") ";
//             $sql .= $tids;
//         } else {
//             if ($role == 3) {
//                 $sql .= $tids;
//             }
//         }

//         if (!empty($params['source'])) {
//             $sql .= ' AND l.source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//         }




//         // if (!empty($params['source'])) {
//         //     $sql .= ' AND source =' . $CI->db->escape_str($params['source']);
//         // }

//         /*if (isset($params['course'])) {
//             $sql .= 'AND tblcustomfieldsvalues.value ='.$params['course'];
//         }


// 		if (isset($params['degree'])) {
//             $sql .= 'AND tblcustomfieldsvalues.value ='.$params['degree'];
//         }*/

//         // if (!empty($params['lead_type'])) {
//         //     $sql .= ' AND l.type =' . $CI->db->escape_str($params['lead_type']);
//         // }
//         if (!empty($params['lead_type'])) {
//             $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//             // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
//         }
//         if (!empty($params['to_date'])) {
//             $from_date = $params['from_date'];
//             $to_date = $params['to_date'];
//             $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//         }
//         if (!empty($params['up_to_date'])) {
//             $up_from_date = $params['up_from_date'];
//             $up_to_date = $params['up_to_date'];
//             //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//             // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
//             $sql .= " AND  (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//                 . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
//         }
//         if (!empty($params['followup_to_date'])) {
//             $followup_from_date = $params['followup_from_date'];
//             $followup_to_date = $params['followup_to_date'];
//             $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//         }

//         if (!empty($params['assign_to_date'])) {
//             $assign_from_date = $params['assign_from_date'];
//             $assign_to_date = $params['assign_to_date'];
//             $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//         }

//         if (!empty($params['fb_source'])) {
//             $facebook_source_name = $params['fb_source'];
//             $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
//         }

//         if (!empty($params['google_source'])) {
//             $google_source_name = $params['google_source'];
//             $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
//         }

//         if ($conversion_status) {
//             $sql .= '  GROUP BY l.source,c.id ';
//         } else {
//             $sql .= '  GROUP BY m.id,c.id ';
//         }
//         $sql .= ' UNION ALL ';
//         $sql = trim($sql);
//     }
//     $result = [];

//     // Remove the last UNION ALL
//     $sql    = substr($sql, 0, -10);

//     $result = $CI->db->query($sql)->result_array();


//     return $result;
// }


function get_status_summary_filter_performance($params, $conversion_status = 0)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();


    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '( l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

 if(is_seoTeam())
     {
      $whereNoViewPermission =" 1=1 ";   
     }
    // $statuses[] = [
    //     'lost'  => true,
    //     'name'  => _l('lost_leads'),
    //     'color' => '#f0f0f0',
    // ];


    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';


        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    // foreach ($statuses as $status) {

    if (!empty($params["total_status"]) && $params["total_status"] == 1) {
        $sql .= " SELECT 'Total Details' AS assigned,
        COUNT(DISTINCT l.id) AS total,
        COALESCE(c.id, 0) AS conversion_id,
        COALESCE(m.id, 0) AS marketing_id,
        COALESCE(m.name, '') AS marketing_name,
        COALESCE(c.name, '') AS conversion_name,
        ls.name AS status_name,
        s.name AS source_name,
        CONCAT(ls.name, '-', s.name) AS index_name,
        s.id AS source_id,
        ls.id AS status_id,
        CONCAT(s.name, '-', c.name) AS index_conversion_name,
        CONCAT(m.name, '-', c.name) AS index_performance_name ";
    } else {
        $sql .= " SELECT l.assigned,
        COUNT(DISTINCT l.id) AS total,
        COALESCE(c.id, 0) AS conversion_id,
        COALESCE(m.id, 0) AS marketing_id,
        COALESCE(m.name, '') AS marketing_name,
        COALESCE(c.name, '') AS conversion_name,
        ls.name AS status_name,
        s.name AS source_name,
        CONCAT(ls.name, '-', s.name) AS index_name,
        s.id AS source_id,
        ls.id AS status_id,
        CONCAT(s.name, '-', c.name) AS index_conversion_name,
        CONCAT(m.name, '-', c.name) AS index_performance_name ";
    }

    if (!empty($params['up_to_date'])) {

        $sql .= ' FROM ' . db_prefix() . 'calls_activity_logs as calls';
        $sql .= ' LEFT JOIN ' . db_prefix() . 'leads l ON calls.contact IN (l.phonenumber)';
        $sql .= ' LEFT JOIN ' . db_prefix() . 'leads_status ls ON ls.id = l.status';
        $sql .= ' LEFT JOIN ' . db_prefix() . 'leads_sources s ON s.id = l.source';
        $sql .= ' LEFT JOIN ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type';
        $sql .= ' LEFT JOIN ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type';
    } else {
        $sql .= ' FROM ' . db_prefix() . 'leads l LEFT join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status LEFT join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';
    }

    // $sql .=' FROM ' . db_prefix() . 'lead_marketing m left join ' . db_prefix() . 'leads_sources s ON s.marketing_type = m.id left join ' . db_prefix() . 'leads l ON s.id = l.source left join ' . db_prefix() . 'leads_status ls ON  ls.id = l.status left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['google_source'])) {
        $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date_join = $params['up_from_date'];
        $up_to_date_join = $params['up_to_date'];
        // $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';
        // $sql .= " join " . db_prefix() . "calls_activity_logs as calls on calls.contact IN ( l.phonenumber,l.alternative_phonenumber  )";
    }
    if (!empty($params['followup_to_date'])) {
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }

    if (isset($status['lost'])) {
        $sql .= ' WHERE lost=1';
    } elseif (isset($status['junk'])) {
        $sql .= ' WHERE junk=1';
    } else {
        $sql .= ' WHERE 1=1 ';
        if (!empty($params['status'])) {
            $sql .= ' AND l.status IN (' . implode(",", $params['status']) . ')';
        }
    }
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    if (!empty($params['assigned'])) {
        // $tids = " AND assigned = " . $params['assigned'];
        $tids = " AND l.assigned IN ( " . implode(",", $params['assigned']) . ") ";
        $sql .= $tids;
    } else {
        if ($role == 3) {
            $sql .= $tids;
        }
    }

    if (!empty($params['source'])) {
        $sql .= ' AND l.source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }




    // if (!empty($params['source'])) {
    //     $sql .= ' AND source =' . $CI->db->escape_str($params['source']);
    // }

    /*if (isset($params['course'])) {
            $sql .= 'AND tblcustomfieldsvalues.value ='.$params['course'];
        }
		 
		 
		if (isset($params['degree'])) {
            $sql .= 'AND tblcustomfieldsvalues.value ='.$params['degree'];
        }*/

    // if (!empty($params['lead_type'])) {
    //     $sql .= ' AND l.type =' . $CI->db->escape_str($params['lead_type']);
    // }
    if (!empty($params['lead_type'])) {
        $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
        // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
    }
    if (!empty($params['to_date'])) {
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND  DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
        $sql .= " AND  (Date(adjusted_call_start) BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "') ";
    }
    if (!empty($params['followup_to_date'])) {
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }

    if (!empty($params['fb_source'])) {
        $facebook_source_name = $params['fb_source'];
        $sql .= ' AND l.website IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $facebook_source_name)) . '\')';
    }

    if (!empty($params['google_source'])) {
        $google_source_name = $params['google_source'];
        $sql .= ' AND ' . db_prefix() . 'customfieldsvalues.fieldid = ' . MARKETING_SOURCE_ID . ' AND  ' . db_prefix() . 'customfieldsvalues.value IN (\'' . implode('\', \'', array_map(array($CI->db, 'escape_str'), $google_source_name)) . '\')';
    }

    if ($conversion_status) {
        if (!empty($params["total_status"]) && $params["total_status"] == 1) {
            $sql .= '  GROUP BY l.source,c.id';
        } else {
            $sql .= '  GROUP BY assigned,l.source,c.id ';
        }
    } else {
        if (!empty($params["total_status"]) && $params["total_status"] == 1) {
            $sql .= '  GROUP BY ls.id, s.id, c.id, m.id';
        } else {
            $sql .= '  GROUP BY  assigned, ls.id, s.id, c.id, m.id ';
        }
    }

    $alt =  str_replace("l.phonenumber", "l.alternative_phonenumber", $sql);
    $sql .= " UNION ALL ";

    $sql = $sql . " " . $alt;

    // $sql .= ' UNION ALL ';
    $sql = trim($sql);
    // }
    $result = [];

    $sql = " SELECT 
        assigned,
        'total', total,
        'conversion_id', conversion_id,
        'marketing_id', marketing_id,
        'marketing_name', marketing_name,
        'conversion_name', conversion_name,
        'status_name', status_name,
        'source_name', source_name,
        'index_name', index_name,
        'source_id', source_id,
        'status_id', status_id,
        'index_conversion_name', index_conversion_name,
        'index_performance_name', index_performance_name
        FROM ( " . $sql . " )  AS tt
        ";

    if ($conversion_status) {
        if (!empty($params["total_status"]) && $params["total_status"] == 1) {
            $sql .= '  GROUP BY tt.source_id,tt.conversion_id';
        } else {
            $sql .= '  GROUP BY tt.assigned,tt.source_id,tt.conversion_id ';
        }
    } else {
        if (!empty($params["total_status"]) && $params["total_status"] == 1) {
            $sql .= '  GROUP BY  tt.status_id, tt.source_id, tt.conversion_id, tt.marketing_id';
        } else {
            $sql .= '  GROUP BY  tt.assigned, tt.status_id, tt.source_id, tt.conversion_id, tt.marketing_id ';
        }
    }

    $result = $CI->db->query($sql)->result_array();
    $groupedResult = array_reduce($result, function ($carry, $item) {
        $carry[$item['assigned']][] = $item;
        return $carry;
    }, []);
    return $groupedResult;
    // $result = array_column($result, null, "assigned");
    // echo count($result);
    // print_r($result);
    // die;
    // return $result;
}

// function calls_update_count($params = false, $max_status = 0)
// {

//     // return convertToHMS(0);
//     // die;
//     $CI = &get_instance();
//     if (!class_exists('leads_model')) {
//         $CI->load->model('leads_model');
//     }
//     $statuses = $CI->leads_model->get_status();

//     $totalStatuses         = count($statuses);
//     $has_permission_view   = has_permission('leads', '', 'view');
//     $sql                   = '';
//     $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

//     $statuses[] = [
//         'lost'  => true,
//         'name'  => _l('lost_leads'),
//         'color' => '#f0f0f0',
//     ];

//     $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
//     if ($role == 3) {
//         // $this->load->database();
//         $sid = get_staff_user_id(); //48;//get_staff_user_id();

//         $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
//         $CI->db->close();
//         $CI->db->initialize();

//         // $teamids = $CI->db->query("select staffid
//         // 	from    (select * from tblstaff
//         // 	where active = '1' order by reporting_person, staffid) products_sorted,
//         // 			(select @pv := $sid) initialisation
//         // 	where   find_in_set(reporting_person, @pv)
//         // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
//         $idsarr = array_column($teamids, 'staffid');
//         $sids = implode(",", $idsarr);

//         if (!empty($sids)) {
//             $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
//         } else {
//             $tids = ' AND assigned in (' . $sid . ')';
//         }
//     }

//     // $sql = "SELECT IFNULL(SUM(call_duration), 0) AS call_duration FROM (";
//     $sql .= "SELECT SUM(calls.duration) AS call_duration,MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastcontact,
//         MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastupdatecontact FROM " . db_prefix() . "leads l ";
//     // $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.assigned = calls.staffid AND RIGHT(TRIM(REPLACE(REPLACE(calls.contact, ' ', ''), ',', '')), 10) = RIGHT(TRIM(REPLACE(REPLACE(l.phonenumber, ' ', ''), ',', '')), 10) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknown')) ";
//     if (!empty($params['followup_to_date'])) {
//         $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
//     }
//     $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.phonenumber = calls.contact AND l.assigned = calls.staffid ";

//     if (!empty($params['assigned'])) {
//         $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
//     }

//     if (!empty($params['last_update_date'])) {
//         $sql .= ' AND calls.staffid = l.assigned ';
//     }

//     // if (!empty($params['assigned'])) {
//     //     $sql .= " AND calls.staffid IN (" . implode(",", $params['assigned']) . ") ";
//     // }
//     $sql .= " ) ";
//     $sql .= " WHERE LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') ";

//     if (!$has_permission_view) {
//         $sql .= ' AND ' . $whereNoViewPermission;
//     }

//     if (!empty($params['status'])) {
//         $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
//     }

//     if (!empty($params['source'])) {
//         $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
//     }

//     if (!empty($params['lead_type'])) {
//         $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
//     }

//     if (!empty($params['neet_score'])) {
//         $neet_range = explode("-", $params['neet_score']);
//         $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
//     }

//     if (!empty($params['last_update_date'])) {
//         $last_update_date = $params['last_update_date'];
//         $sql .= " AND DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '" . $CI->db->escape_str($last_update_date) . "' ";
//     }

//     if (!empty($params['to_date'])) {
//         $from_date = $params['from_date'];
//         $to_date = $params['to_date'];
//         $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
//     }

//     if (!empty($params['last_contact_date'])) {
//         $last_contact_date = $params['last_contact_date'];
//         $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "'  or lastcontact is NULL ) ";
//     } else if (!empty($params['last_update_date'])) {
//         $last_update_date = $params['last_update_date'];
//         $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_update_date) . "'  or lastcontact is NULL ) ";
//     }

//     if (!empty($params['followup_to_date'])) {
//         $followup_from_date = $params['followup_from_date'];
//         $followup_to_date = $params['followup_to_date'];
//         $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
//     }

//     if (!empty($params['assign_to_date'])) {
//         $assign_from_date = $params['assign_from_date'];
//         $assign_to_date = $params['assign_to_date'];
//         $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
//     } elseif (!empty($params['up_to_date'])) {
//         $up_from_date = $params['up_from_date'];
//         $up_to_date = $params['up_to_date'];
//         $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
//             . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "')";
//     }

//     $grup_by = "";
//     if (!empty($params['neet_score'])) {
//         $grup_by .= ',' . db_prefix() . 'customfieldsvalues.relid';
//     }
//     if (!empty($params['assigned'])) {
//         $grup_by .= ',calls.staffid,calls.call_start';
//     }

//     $sql_add = "";
//     // if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//     //     $min = $params['update_count_min'];
//     //     $max = $params['update_count_max'];
//     //     $sql_add = ' HAVING COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//     // }

//     // $having = "";
//     // if (!empty($params['last_contact_date'])) {
//     //     $last_contact_date = $params["last_contact_date"];
//     //     $having .= " Having lastcontact <= '" . $last_contact_date . "' or lastcontact is NULL ";
//     // } else if (!empty($params['last_update_date'])) {
//     //     $last_contact_date = $params["last_update_date"];
//     //     $having .= " Having lastcontact <= '" . $last_update_date . "'  or lastcontact is NULL  ";
//     // }

//     $having = "";
//     if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
//         $having .= ' HAVING ';

//         if (!empty($params['last_contact_date'])) {
//             $last_contact_date = $params["last_contact_date"];
//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $having .= "(lastcontact <= '" . $last_contact_date . "')";
//             } else {
//                 $having .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
//             }

//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $having .= ' AND ';
//             }
//         } else if (!empty($params['last_update_date'])) {
//             $last_contact_date = $params["last_update_date"];
//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $having .= "(lastupdatecontact <= '" . $last_contact_date . "')";
//             } else {
//                 $having .= "(lastupdatecontact <= '" . $last_contact_date . "' OR lastupdatecontact IS NULL)";
//             }

//             if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//                 $having .= ' AND ';
//             }
//         }


//         if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
//             $min = $params['update_count_min'];
//             $max = $params['update_count_max'];
//             // $having .= 'COUNT(' . db_prefix() . 'leads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//             $having .= 'COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
//         }
//     }


//     $sql .= " GROUP BY calls.contact" . $grup_by . " " . $having . $sql_add;
//     // . ") AS subquery";

//     $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

//     $update_count = $CI->db->query($sql)->row()->total_sum;

//     return !empty($update_count) ? convertToHMS($update_count) : convertToHMS(0);
// }

function calls_update_count($params = false, $max_status = 0)
{

    // return convertToHMS(0);
    // die;
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $check_today = true;
    // $sql = "SELECT IFNULL(SUM(call_duration), 0) AS call_duration FROM (";
    $sql .= "SELECT SUM(calls.duration) AS call_duration,MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastcontact,
        MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastupdatecontact FROM " . db_prefix() . "leads l ";
    // $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.assigned = calls.staffid AND RIGHT(TRIM(REPLACE(REPLACE(calls.contact, ' ', ''), ',', '')), 10) = RIGHT(TRIM(REPLACE(REPLACE(l.phonenumber, ' ', ''), ',', '')), 10) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknown')) ";
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }
    $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.phonenumber = calls.contact AND l.assigned = calls.staffid ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $sql .= ' AND calls.staffid = l.assigned ';
    }

    // if (!empty($params['assigned'])) {
    //     $sql .= " AND calls.staffid IN (" . implode(",", $params['assigned']) . ") ";
    // }
    $sql .= " ) ";
    $sql .= " WHERE LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') ";

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }

    if (!empty($params['status'])) {
        $check_today = false;
        $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['source'])) {
        $check_today = false;
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }

    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '" . $CI->db->escape_str($last_update_date) . "' ";
    }

    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "'  or lastcontact is NULL ) ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_update_date) . "'  or lastcontact is NULL ) ";
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "')";
    }

    if ($check_today === true) {
        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by .= ',' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['assigned'])) {
        $grup_by .= ',calls.staffid,calls.call_start';
    }

    $sql_add = "";
    // if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
    //     $min = $params['update_count_min'];
    //     $max = $params['update_count_max'];
    //     $sql_add = ' HAVING COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
    // }

    // $having = "";
    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params["last_contact_date"];
    //     $having .= " Having lastcontact <= '" . $last_contact_date . "' or lastcontact is NULL ";
    // } else if (!empty($params['last_update_date'])) {
    //     $last_contact_date = $params["last_update_date"];
    //     $having .= " Having lastcontact <= '" . $last_update_date . "'  or lastcontact is NULL  ";
    // }

    $having = "";
    if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        $having .= ' HAVING ';

        if (!empty($params['last_contact_date'])) {
            $last_contact_date = $params["last_contact_date"];
            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= "(lastcontact <= '" . $last_contact_date . "')";
            } else {
                $having .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
            }

            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= ' AND ';
            }
        } else if (!empty($params['last_update_date'])) {
            $last_contact_date = $params["last_update_date"];
            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= "(lastupdatecontact <= '" . $last_contact_date . "')";
            } else {
                $having .= "(lastupdatecontact <= '" . $last_contact_date . "' OR lastupdatecontact IS NULL)";
            }

            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= ' AND ';
            }
        }


        if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            // $having .= 'COUNT(' . db_prefix() . 'leads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
            $having .= 'COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }


    $sql .= " GROUP BY calls.contact" . $grup_by . " " . $having . $sql_add;
    // . ") AS subquery";

    $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

    $update_count = $CI->db->query($sql)->row()->total_sum;

    return !empty($update_count) ? convertToHMS($update_count) : convertToHMS(0);
}


function calls_update_count_pri($params = false, $max_status = 0)
{

    // return convertToHMS(0);
    // die;
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $check_today = true;
    // $sql = "SELECT IFNULL(SUM(call_duration), 0) AS call_duration FROM (";
    $sql .= "SELECT SUM(calls.duration) AS call_duration,MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastcontact,
        MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastupdatecontact FROM " . db_prefix() . "leads l ";
    // $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.assigned = calls.staffid AND RIGHT(TRIM(REPLACE(REPLACE(calls.contact, ' ', ''), ',', '')), 10) = RIGHT(TRIM(REPLACE(REPLACE(l.phonenumber, ' ', ''), ',', '')), 10) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknown')) ";
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }
    $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.phonenumber = calls.contact AND l.assigned = calls.staffid ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $sql .= ' AND calls.staffid = l.assigned ';
    }

    // if (!empty($params['assigned'])) {
    //     $sql .= " AND calls.staffid IN (" . implode(",", $params['assigned']) . ") ";
    // }
    $sql .= " ) ";
    $sql .= " WHERE LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') ";

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }

    if (!empty($params['status'])) {
        $check_today = false;
        $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['source'])) {
        $check_today = false;
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }

    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '" . $CI->db->escape_str($last_update_date) . "' ";
    }

    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "'  or lastcontact is NULL ) ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_update_date) . "'  or lastcontact is NULL ) ";
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "')";
    }

    if ($check_today === true) {
        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by .= ',' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['assigned'])) {
        $grup_by .= ',calls.staffid,calls.call_start';
    }

    $sql_add = "";
    // if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
    //     $min = $params['update_count_min'];
    //     $max = $params['update_count_max'];
    //     $sql_add = ' HAVING COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
    // }

    // $having = "";
    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params["last_contact_date"];
    //     $having .= " Having lastcontact <= '" . $last_contact_date . "' or lastcontact is NULL ";
    // } else if (!empty($params['last_update_date'])) {
    //     $last_contact_date = $params["last_update_date"];
    //     $having .= " Having lastcontact <= '" . $last_update_date . "'  or lastcontact is NULL  ";
    // }

    $having = "";
    if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        $having .= ' HAVING ';

        if (!empty($params['last_contact_date'])) {
            $last_contact_date = $params["last_contact_date"];
            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= "(lastcontact <= '" . $last_contact_date . "')";
            } else {
                $having .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
            }

            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= ' AND ';
            }
        } else if (!empty($params['last_update_date'])) {
            $last_contact_date = $params["last_update_date"];
            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= "(lastupdatecontact <= '" . $last_contact_date . "')";
            } else {
                $having .= "(lastupdatecontact <= '" . $last_contact_date . "' OR lastupdatecontact IS NULL)";
            }

            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= ' AND ';
            }
        }


        if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            // $having .= 'COUNT(' . db_prefix() . 'leads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
            $having .= 'COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }


    $sql .= " GROUP BY calls.contact" . $grup_by . " " . $having . $sql_add;
    // . ") AS subquery";

    $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

    $update_count = $CI->db->query($sql)->row()->total_sum;

    return !empty($update_count) ? $update_count : 0;
}

function calls_update_count_sec($params = false, $max_status = 0)
{

    // return convertToHMS(0);
    // die;
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses         = count($statuses);
    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '#f0f0f0',
    ];

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        // $teamids = $CI->db->query("select staffid
        // 	from    (select * from tblstaff
        // 	where active = '1' order by reporting_person, staffid) products_sorted,
        // 			(select @pv := $sid) initialisation
        // 	where   find_in_set(reporting_person, @pv)
        // 	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $check_today = true;
    // $sql = "SELECT IFNULL(SUM(call_duration), 0) AS call_duration FROM (";
    $sql .= "SELECT SUM(calls.duration) AS call_duration,MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastcontact,
        MAX(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start + (5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')) AS lastupdatecontact FROM " . db_prefix() . "leads l ";
    // $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.assigned = calls.staffid AND RIGHT(TRIM(REPLACE(REPLACE(calls.contact, ' ', ''), ',', '')), 10) = RIGHT(TRIM(REPLACE(REPLACE(l.phonenumber, ' ', ''), ',', '')), 10) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknown')) ";
    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }
    $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.alternative_phonenumber = calls.contact AND l.assigned = calls.staffid ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $sql .= ' AND calls.staffid = l.assigned ';
    }

    // if (!empty($params['assigned'])) {
    //     $sql .= " AND calls.staffid IN (" . implode(",", $params['assigned']) . ") ";
    // }
    $sql .= " ) ";
    $sql .= " WHERE LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') ";

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }

    if (!empty($params['status'])) {
        $check_today = false;
        $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['source'])) {
        $check_today = false;
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }

    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d') <= '" . $CI->db->escape_str($last_update_date) . "' ";
    }

    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_contact_date) . "'  or lastcontact is NULL ) ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND ( lastcontact <= '" . $CI->db->escape_str($last_update_date) . "'  or lastcontact is NULL ) ";
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    } elseif (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "')";
    }

    if ($check_today === true) {
        $current_date = date('Y-m-d');
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "')";
    }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by .= ',' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['assigned'])) {
        $grup_by .= ',calls.staffid,calls.call_start';
    }

    $sql_add = "";
    // if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
    //     $min = $params['update_count_min'];
    //     $max = $params['update_count_max'];
    //     $sql_add = ' HAVING COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
    // }

    // $having = "";
    // if (!empty($params['last_contact_date'])) {
    //     $last_contact_date = $params["last_contact_date"];
    //     $having .= " Having lastcontact <= '" . $last_contact_date . "' or lastcontact is NULL ";
    // } else if (!empty($params['last_update_date'])) {
    //     $last_contact_date = $params["last_update_date"];
    //     $having .= " Having lastcontact <= '" . $last_update_date . "'  or lastcontact is NULL  ";
    // }

    $having = "";
    if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        $having .= ' HAVING ';

        if (!empty($params['last_contact_date'])) {
            $last_contact_date = $params["last_contact_date"];
            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= "(lastcontact <= '" . $last_contact_date . "')";
            } else {
                $having .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
            }

            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= ' AND ';
            }
        } else if (!empty($params['last_update_date'])) {
            $last_contact_date = $params["last_update_date"];
            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= "(lastupdatecontact <= '" . $last_contact_date . "')";
            } else {
                $having .= "(lastupdatecontact <= '" . $last_contact_date . "' OR lastupdatecontact IS NULL)";
            }

            if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
                $having .= ' AND ';
            }
        }


        if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            // $having .= 'COUNT(' . db_prefix() . 'leads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
            $having .= 'COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }


    $sql .= " GROUP BY calls.contact" . $grup_by . " " . $having . $sql_add;
    // . ") AS subquery";

    $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

    $update_count = $CI->db->query($sql)->row()->total_sum;

    return !empty($update_count) ? $update_count : 0;
}

function convertToHMS($seconds, $status = 0)
{
    if ($seconds == "") {
        $seconds = 0;
    }
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    if ($status == 1) {
        return sprintf('%d:%d:%d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%d Hours : %d Mins : %d Sec', $hours, $minutes, $seconds);
    }
}

function call_duration($row_data, $post_data = "")
{


    $phone = trim($row_data["phonenumber"]);
    $staff_id = $row_data["staffid"];
    $lead_id = $row_data["id"];
    $CI = &get_instance();
    $sql = " SELECT  IFNULL(SUM(calls.duration), 0) AS duration,if(max(calls.call_start)!='',DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (ifnull(max(calls.call_start),'')+(5 * 3600 + 30 * 60)) SECOND),'%Y-%m-%d %H:%i:%s'),'') last_contact_date  FROM " . db_prefix() . "calls_activity_logs calls where SUBSTRING(TRIM(calls.contact), LENGTH(TRIM(calls.contact)) - 9) = SUBSTRING(TRIM('{$phone}'), LENGTH(TRIM('{$phone}')) - 9) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknow') ";
    if (!empty($calling_from_date) && !empty($calling_to_date)) {
        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$calling_from_date}' AND '{$calling_to_date}' ";
    }
    $sql .= " LIMIT 1 ";
    return $CI->db->query($sql)->result_array();
}

function leads_call_update_count()
{
    $CI = &get_instance();
    $query = $CI->db->query(" SELECT MAX(contact_count) max_count
    FROM (
        SELECT COUNT(1) AS contact_count
        FROM  " . db_prefix() . "calls_activity_logs
        GROUP BY contact
    ) AS subquery;
    ")->row();

    $maxCount = $query->max_count;
    return $maxCount;
}

function get_all_staff()
{
    $CI = &get_instance();
    return $CI->staff_model->get('', ['is_not_staff' => 0, 'active' => 1], 1);
}
function get_type()
{
    $CI = &get_instance();
    return $CI->leads_model->get_type();
}

function get_source()
{
    $CI = &get_instance();
    return $CI->leads_model->get_source();
}

function get_status()
{
    $CI = &get_instance();
    return $CI->leads_model->get_status();
}


function last_lead_request($lead_id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->select('IF(status = 1, "Approved", IF(status = 3, "Pending", "Not Found")) as status_text', false);
    $CI->db->where("status", 3);
    $CI->db->where("leadid", $lead_id);
    $CI->db->order_by("id", "desc");
    $CI->db->limit(1);
    $lead_request = $CI->db->get(db_prefix() . 'lead_transfer_request')->row();
    return $lead_request;
}

function get_whatsapp_template()
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where("status", 1);
    $CI->db->order_by("id", "desc");
    $whatsapp_template = $CI->db->get(db_prefix() . 'whatsapp_template')->result_array();
    return $whatsapp_template;
}


function all_leads()
{
    $CI = &get_instance();

    // Fetch active staff data
    $staff_data = $CI->db->select('staffid')->where('active', 1)->get(db_prefix() . 'staff')->result_array();

    foreach ($staff_data as $staff) {
        // Fetch role of the staff
        $role = $CI->db->select('role')->where('staffid', $staff['staffid'])->get(db_prefix() . 'staff')->row()->role;


        $staff_ids = "";


        if ($staff["staffid"] != 1) {
            if ($role == 3) {
                $sid = $staff['staffid'];
                // Get reporting persons
                $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
                $CI->db->close();
                $CI->db->initialize();
                $CI->db->select('RIGHT(TRIM(phonenumber), 10) AS phonenumber, name, assigned, id');

                $idsarr = array_column($teamids, 'staffid');
                $sids = implode(",", $idsarr);

                if (!empty($sids)) {
                    $CI->db->where_in('assigned', explode(',', $sids));
                    $staff_ids = $sids;
                } else {
                    $CI->db->where('assigned', $sid);
                    $staff_ids = $sid;
                }
            } else {

                $CI->db->select('RIGHT(TRIM(phonenumber), 10) AS phonenumber, name, assigned, id');

                $CI->db->where('assigned', $staff['staffid']);
                $staff_ids = $staff['staffid'];
            }
        } else {
            $CI->db->select('RIGHT(TRIM(phonenumber), 10) AS phonenumber, name, assigned, id');
        }


        // Fetch leads data
        $leads = $CI->db->get(db_prefix() . 'leads')->result_array();
        $leads = array_column($leads, null, 'phonenumber');

        // Prepare data for insertion
        $staff_id = $staff['staffid'];
        $contacts = json_encode($leads);
        $data = array(
            'staff_id' => $staff_id,
            'contacts' => $contacts,
            'count' => count($leads)
        );

        // Check if staff_id already exists
        $CI->db->where('staff_id', $staff_id);
        $query = $CI->db->get(db_prefix() . '_staff_contacts_assignation');

        if ($query->num_rows() > 0) {
            // Staff ID exists, update the record
            $CI->db->where('staff_id', $staff_id);
            $updated = $CI->db->update(db_prefix() . '_staff_contacts_assignation', $data);

            // Optional: Log error if update fails
            if (!$updated) {
                $error = $CI->db->error();
                log_message('error', 'Update failed for staff_id ' . $staff_id . ': ' . $error['message']);
            } else {
                log_message('info', 'Update successful for staff_id ' . $staff_id);
            }
        } else {
            // Staff ID does not exist, insert a new record
            if (!empty($leads)) {
                $inserted = $CI->db->insert(db_prefix() . '_staff_contacts_assignation', $data);

                // Optional: Log error if insertion fails
                if (!$inserted) {
                    $error = $CI->db->error();
                    log_message('error', 'Insert failed for staff_id ' . $staff_id . ': ' . $error['message']);
                    return false;
                } else {
                    log_message('info', 'Insert successful for staff_id ' . $staff_id);
                }
            }
        }
    }
    return true;
}


function get_all_leads()
{
    $CI = &get_instance();
    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    $CI->db->select('RIGHT(TRIM(phonenumber), 10) AS phonenumber, name, assigned,id');

    if (!is_admin()) {
        if ($role == 3) {
            // $this->load->database();
            $sid = get_staff_user_id(); //48;//get_staff_user_id();
            $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
            $CI->db->close();
            $CI->db->initialize();
            $idsarr = array_column($teamids, 'staffid');
            $sids = implode(",", $idsarr);
            $CI->db->where('assigned', $sids);
        } else {
            $CI->db->where('assigned', get_staff_user_id());
        }
    }

    $leads = $CI->db->get(db_prefix() . 'leads')->result_array();
    $leads = json_encode(array_column($leads, null, 'phonenumber'));
    return $leads;

    // $CI =& get_instance();
    // $staff_data = $CI->db->select('contacts')->where(array("staff_id"=>get_staff_user_id()))->get(db_prefix() . '_staff_contacts_assignation')->row(); 

    // if(empty($staff_data->contacts))
    // {
    //     return [];
    // }
    // else
    // {
    //     return $staff_data->contacts;
    // }
}

function search_tags($search)
{
    $CI = &get_instance();
    $search = preg_quote($search, '/'); // Escaping special characters for REGEXP

    $sql = "SELECT name,id
            FROM " . db_prefix() . "tags 
            WHERE name REGEXP '" . $search . "'
            LIMIT 20 ";

    $query = $CI->db->query($sql);
    return $query->result_array();
}


function calculate_call_duration($params = false, $max_status = 0)
{

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        $sid = get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $check_today = true;
    $sql .= "SELECT   SUM(IF(call_status IN ('answered', 'status_unknown'), IFNULL(duration, 0), 0)) AS total_call_duration,adjusted_call_start,
    COUNT(DISTINCT CONCAT(calls.contact, '-', adjusted_call_start)) AS update_count FROM " . db_prefix() . "leads l ";

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }
    $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON ( REPLACE(TRIM(REPLACE(l.phonenumber, '+91', '')),
        ' ',
        '') = calls.contact AND l.assigned = calls.staffid ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $sql .= ' AND calls.staffid = l.assigned ';
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND DATE(adjusted_call_start) <= '" . $CI->db->escape_str($last_update_date) . "' ";
    }


    if (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND DATE(adjusted_call_start) BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "'";
    }

    if ($check_today === true && empty($params['up_to_date']) && empty($params['last_update_date']) && empty($params['to_date']) && empty($params['last_contact_date']) &&  empty($params['followup_to_date']) && empty($params['assign_to_date'])) {
        $current_date = date('Y-m-d');
        $sql .= " AND DATE(adjusted_call_start) BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "'";
    }


    $sql .= " ) ";

    $sql .= " WHERE 1 ";

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }

    if (!empty($params['status'])) {
        $check_today = false;
        $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['source'])) {
        $check_today = false;
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }

    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }

    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND ( l.lastconnect_date <= '" . $CI->db->escape_str($last_contact_date) . "'  or l.lastconnect_date is NULL ) ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND ( date(adjusted_call_start) <= '" . $CI->db->escape_str($last_update_date) . "'  or adjusted_call_start is NULL ) ";
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }



    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by .= ',' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['assigned'])) {
        $grup_by .= ',calls.staffid';
    }

    $sql_add = "";

    $having = "";
    if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        // $having .= ' HAVING ';

        // if (!empty($params['last_contact_date'])) {
        //     $last_contact_date = $params["last_contact_date"];
        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= "(lastcontact <= '" . $last_contact_date . "')";
        //     } else {
        //         $having .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
        //     }

        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= ' AND ';
        //     }
        // } 
        // else if (!empty($params['last_update_date'])) {
        //     $last_contact_date = $params["last_update_date"];
        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= "(adjusted_call_start <= '" . $last_contact_date . "')";
        //     } else {
        //         $having .= "(adjusted_call_start <= '" . $last_contact_date . "' OR adjusted_call_start IS NULL)";
        //     }

        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= ' AND ';
        //     }
        // }


        if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $having .= ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }


    $sql .= " GROUP BY calls.contact" . $grup_by . " " . $having . $sql_add;
    // . ") AS subquery";

    $alt =  str_replace("l.phonenumber", "l.alternative_phonenumber", $sql);
    $sql .= " UNION ALL ";

    $sql = $sql . " " . $alt;


    // $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

    $duration = $CI->db->query($sql)->result_array();




    return !empty($duration) ? $duration : [];
}

function calculate_call_duration_new($params = false, $max_status = 0)
{

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    $has_permission_view   = has_permission('leads', '', 'view');
    $sql                   = '';
    $whereNoViewPermission = '(l.addedfrom = ' . get_staff_user_id() . ' OR l.assigned=' . get_staff_user_id() . ' OR l.is_public = 1)';

    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        $sid = get_staff_user_id();

        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();

        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);

        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $check_today = true;
    $sql .= "SELECT   SUM(IF(call_status IN ('answered', 'status_unknown'), IFNULL(duration, 0), 0)) AS total_call_duration,adjusted_call_start,
    COUNT(DISTINCT CONCAT(calls.contact, '-', adjusted_call_start)) AS update_count FROM " . db_prefix() . "leads l ";

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }
    $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (calls.contact IN (REPLACE(TRIM(REPLACE(l.phonenumber, '+91', '')),
        ' ',
        '') ) AND l.assigned = calls.staffid ";

    if (!empty($params['assigned'])) {
        $check_today = false;
        $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $sql .= ' AND calls.staffid = l.assigned ';
    }

    if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND DATE(adjusted_call_start) <= '" . $CI->db->escape_str($last_update_date) . "' ";
    }


    if (!empty($params['up_to_date'])) {
        $check_today = false;
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND DATE(adjusted_call_start) BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "'";
    }

    if ($check_today === true && empty($params['up_to_date']) && empty($params['last_update_date']) && empty($params['to_date']) && empty($params['last_contact_date']) &&  empty($params['followup_to_date']) && empty($params['assign_to_date'])) {
        $current_date = date('Y-m-d');
        $sql .= " AND DATE(adjusted_call_start) BETWEEN '"
            . $CI->db->escape_str($current_date) . "' AND '" . $CI->db->escape_str($current_date) . "'";
    }


    $sql .= " ) ";

    $sql .= " WHERE 1 ";

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }

    if (!empty($params['status'])) {
        $check_today = false;
        $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['source'])) {
        $check_today = false;
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['view_form'])) {
        $websites = $params['view_form'];
        $escaped_websites = array_map(function ($w) {
            return "'" . trim($w) . "'";
        }, $websites);
        $sql .= " AND l.website IN (" . implode(',', $escaped_websites) . ")";
    }


    if (!empty($params['reference_name'])) {
        $reference_name = $params['reference_name'];
        $escaped_reference_name = array_map(function ($w) {
            return "'" . trim($w) . "'";
        }, $reference_name);

        $sql .= " AND  l.reference_name IN (" . implode(',', $escaped_reference_name) . ")";
    }

    if (!empty($params['lead_type'])) {
        $check_today = false;
        $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
    }

    if (!empty($params['neet_score'])) {
        $check_today = false;
        $neet_range = explode("-", $params['neet_score']);
        $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
    }

    if (!empty($params['to_date'])) {
        $check_today = false;
        $from_date = $params['from_date'];
        $to_date = $params['to_date'];
        $sql .= ' AND DATE(l.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $check_today = false;
        $last_contact_date = $params['last_contact_date'];
        $sql .= " AND ( l.lastconnect_date <= '" . $CI->db->escape_str($last_contact_date) . "'  or l.lastconnect_date is NULL ) ";
    } else if (!empty($params['last_update_date'])) {
        $check_today = false;
        $last_update_date = $params['last_update_date'];
        $sql .= " AND ( date(adjusted_call_start) <= '" . $CI->db->escape_str($last_update_date) . "'  or adjusted_call_start is NULL ) ";
    }

    if (!empty($params['followup_to_date'])) {
        $check_today = false;
        $followup_from_date = $params['followup_from_date'];
        $followup_to_date = $params['followup_to_date'];
        $sql .= ' AND DATE(tblreminders.date) BETWEEN "' . $CI->db->escape_str($followup_from_date) . '" AND "' . $CI->db->escape_str($followup_to_date) . '"';
    }

    if (!empty($params['assign_to_date'])) {
        $check_today = false;
        $assign_from_date = $params['assign_from_date'];
        $assign_to_date = $params['assign_to_date'];
        $sql .= ' AND DATE(dateassigned) BETWEEN "' . $CI->db->escape_str($assign_from_date) . '" AND "' . $CI->db->escape_str($assign_to_date) . '"';
    }



    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by .= ',' . db_prefix() . 'customfieldsvalues.relid';
    }
    if (!empty($params['assigned'])) {
        $grup_by .= ',calls.staffid';
    }

    $sql_add = "";

    $having = "";
    if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {
        // $having .= ' HAVING ';

        // if (!empty($params['last_contact_date'])) {
        //     $last_contact_date = $params["last_contact_date"];
        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= "(lastcontact <= '" . $last_contact_date . "')";
        //     } else {
        //         $having .= "(lastcontact <= '" . $last_contact_date . "' OR lastcontact IS NULL)";
        //     }

        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= ' AND ';
        //     }
        // } 
        // else if (!empty($params['last_update_date'])) {
        //     $last_contact_date = $params["last_update_date"];
        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= "(adjusted_call_start <= '" . $last_contact_date . "')";
        //     } else {
        //         $having .= "(adjusted_call_start <= '" . $last_contact_date . "' OR adjusted_call_start IS NULL)";
        //     }

        //     if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //         $having .= ' AND ';
        //     }
        // }


        if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $having .= ' HAVING COUNT(calls.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }
    }


    $sql .= " GROUP BY calls.contact" . $grup_by . " " . $having . $sql_add;

    // . ") AS subquery";

    $alt =  str_replace("l.phonenumber", "l.alternative_phonenumber", $sql);
    $sql .= " UNION ALL ";

    $sql = $sql . " " . $alt;


    // $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

    $duration = $CI->db->query($sql)->result_array();




    return !empty($duration) ? $duration : [];
}
function get_leads_summary_filter_new($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    $statuses = $CI->leads_model->get_status();
    $tblleads = db_prefix() . 'leads';

    $has_permission_view   = has_permission('leads', '', 'view');
    $whereNoViewPermission = '(' . $tblleads . '.addedfrom = ' . get_staff_user_id() . ' OR ' . $tblleads . '.assigned=' . get_staff_user_id() . ' OR ' . $tblleads . '.is_public = 1)';

    // Fetch role and handle reporting persons for role ID 3
    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    $tids = '';
    if ($role == 3) {
        $sid = get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = !empty($sids) ? " AND assigned IN ($sid, $sids)" : " AND assigned IN ($sid)";
    }

    // Base query
    $sql = 'SELECT IFNULL(' . db_prefix() . 'leads_status.id, "unknown") AS status_id, COUNT(DISTINCT ' . $tblleads . '.id) AS total ';
    $sql .= 'FROM ' . $tblleads . ' ';
    $sql .= 'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . $tblleads . '.status = ' . db_prefix() . 'leads_status.id ';

    // Conditional joins based on parameters
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
        $sql .= 'JOIN ' . db_prefix() . 'customfieldsvalues ON ' . $tblleads . '.id = ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['up_to_date']) || !empty($params['last_contact_date']) || !empty($params['last_update_date'])) {
        $sql .= 'LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . $tblleads . '.phonenumber = calls.contact ';
    }

    if (!empty($params['followup_to_date'])) {
        $sql .= 'JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . $tblleads . '.id ';
    }



    // WHERE clause
    $conditions = ['junk = 0', 'lost = 0'];
    if (!$has_permission_view) {
        $conditions[] = $whereNoViewPermission;
    }
    if (!empty($params['status'])) {
        $conditions[] = db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ')';
    }
    if (!empty($params['assigned'])) {
        $conditions[] = 'assigned IN (' . implode(',', $params['assigned']) . ')';
    } elseif ($role == 3) {
        $conditions[] = substr($tids, 4); // Remove the leading " AND"
    }
    if (!empty($params['source'])) {
        $conditions[] = 'source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['neet_score'])) {
        $neet_range = explode('-', $params['neet_score']);
        $conditions[] = db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' .
            $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' .
            db_prefix() . 'customfieldsvalues.value != ""';
    }
    if (!empty($params['lead_type'])) {
        $conditions[] = 'type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    }
    if (!empty($params['from_date']) && !empty($params['to_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.dateadded) BETWEEN "' . $CI->db->escape_str($params['from_date']) . '" AND "' . $CI->db->escape_str($params['to_date']) . '"';
    }
    if (!empty($params['up_to_date'])) {
        $conditions[] = 'DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), \'%Y-%m-%d\') BETWEEN "' . $CI->db->escape_str($params['up_from_date']) . '" AND "' . $CI->db->escape_str($params['up_to_date']) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $conditions[] = 'DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($params['followup_from_date']) . '" AND "' . $CI->db->escape_str($params['followup_to_date']) . '"';
    }

    if (!empty($params['assign_from_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.dateassigned) BETWEEN "' . $CI->db->escape_str($params['assign_from_date']) . '" AND "' . $CI->db->escape_str($params['assign_to_date']) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.lastconnect_date) <= "' . $CI->db->escape_str($params['last_contact_date']) . '"';
    }

    if (!empty($params['last_update_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.lastupdate_date) <= "' . $CI->db->escape_str($params['last_update_date']) . '"';
    }

    if (!empty($params['update_count_min'])) {
        $conditions[] =  $tblleads . '.update_count Between "' . $CI->db->escape_str($params['update_count_min']) . '" AND "' . $CI->db->escape_str($params['update_count_max']) . '"';
    }

    if (!empty($params['up_to_date'])) {
        $up_to_date = $params['up_to_date'];
        $up_from_date   = $params['up_from_date'];

        $up_from_date = $CI->db->escape_str($up_from_date); // Start date
        $up_to_date = $CI->db->escape_str($up_to_date);     // End date


        $where_c = "";
        $join_type = "";
        if ($params['update_count_min']) {

            $min = isset($params['update_count_min']) ? $params['update_count_min'] : 0;
            $max = isset($params['update_count_max']) ? $params['update_count_max'] : 0;
            $where_c = " AND ifnull(calls.update_count,0) between {$min} AND {$max} ";


            if ($min == 0) {
                $join_type = "RIGHT";
            }
        }

        // SQL Queries
        $sql_p1 = " SELECT id 
    FROM  ( SELECT leads.id FROM {$tblleads} leads  {$join_type} JOIN " . db_prefix() . "calls_activity_logs calls ON (calls.contact IN (leads.phonenumber)  {$where_c}) WHERE 1=1
    AND DATE(calls.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}' {$where_c} ";

        $sql_p1 .= " UNION ALL ";

        $sql_p1 .= "SELECT leads.id FROM {$tblleads} leads  {$join_type} JOIN " . db_prefix() . "calls_activity_logs calls ON ( calls.contact IN (leads.alternative_phonenumber)  {$where_c}) WHERE 1=1
    AND DAte(calls.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}' {$where_c} ";

        $sql_p1 .= " ) AS combined_result GROUP BY id ORDER BY id DESC ";

        $conditions[] = "  {$tblleads}.id IN ($sql_p1) ";
    }

    // Apply conditions to WHERE clause
    if (!empty($conditions)) {
        $sql .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
    }

    // GROUP BY and ORDER BY
    $sql .= 'GROUP BY ' . $tblleads . '.status ';
    $sql .= 'ORDER BY ' . db_prefix() . 'leads_status.statusorder';

    // Execute query
    $result = $CI->db->query($sql)->result();

    // Prepare results
    if (!empty($result)) {
        $result = array_column($result, "total", "status_id");
    }

    $statuses[] = ["id" => "unknown", "name" => "Unknown Status"];

    $totalLeads = 0;
    foreach ($statuses as $key => $status) {
        $statuses[$key]['total'] = $result[$status["id"]] ?? 0;
        $totalLeads += $statuses[$key]['total'];
    }

    $statuses[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads];

    return $statuses;
}


function get_leads_summary_filter_neww($params)
{

    $params['update_count_min'] = (isset($params['update_count_min']) && is_numeric($params['update_count_min']) && $params['update_count_min'] !== 'NaN')
        ? $params['update_count_min']
        : '';

    $params['update_count_max'] = (isset($params['update_count_max']) && is_numeric($params['update_count_max']) && $params['update_count_max'] !== 'NaN')
        ? $params['update_count_max']
        : '';


    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    $statuses = $CI->leads_model->get_status();
    $tblleads = db_prefix() . 'leads';

    $has_permission_view   = has_permission('leads', '', 'view');
    $whereNoViewPermission = '(' . $tblleads . '.addedfrom = ' . get_staff_user_id() . ' OR ' . $tblleads . '.assigned=' . get_staff_user_id() . ' OR ' . $tblleads . '.is_public = 1)';

    // Fetch role and handle reporting persons for role ID 3
    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    $tids = '';
    if ($role == 3) {
        $sid = get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = !empty($sids) ? " AND assigned IN ($sid, $sids)" : " AND assigned IN ($sid)";
    }

    // Base query
    $sql = 'SELECT IFNULL(' . db_prefix() . 'leads_status.id, "unknown") AS status_id, COUNT(DISTINCT ' . $tblleads . '.id) AS total ';
    $sql .= 'FROM ' . $tblleads . ' ';
    $sql .= 'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . $tblleads . '.status = ' . db_prefix() . 'leads_status.id ';


    // Conditional joins based on parameters
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
        $sql .= 'JOIN ' . db_prefix() . 'customfieldsvalues ON ' . $tblleads . '.id = ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['up_to_date']) || !empty($params['last_contact_date']) || !empty($params['last_update_date'])) {
        // $sql .= 'LEFT JOIN ' . db_prefix() . 'calls_activity_logs AS calls ON ' . $tblleads . '.phonenumber = calls.contact ';


    }

    if (!empty($params['followup_to_date'])) {
        $sql .= 'JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . $tblleads . '.id ';
    }


    if (!empty($params['location']) || !empty($params['department'])) {
        $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $tblleads . '.assigned ';
    }

    // WHERE clause
    $conditions = ['junk = 0', 'lost = 0'];
    if (!$has_permission_view) {
        $conditions[] = $whereNoViewPermission;
    }
    if (!empty($params['status'])) {
        $conditions[] = db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ')';
    }
    if (!empty($params['assigned'])) {
        $conditions[] = 'assigned IN (' . implode(',', $params['assigned']) . ')';
    } elseif ($role == 3) {
        $conditions[] = substr($tids, 4); // Remove the leading " AND"
    }
    if (!empty($params['source'])) {
        $conditions[] = 'source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';
    }
    if (!empty($params['neet_score'])) {
        $neet_range = explode('-', $params['neet_score']);
        $conditions[] = db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' .
            $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' .
            db_prefix() . 'customfieldsvalues.value != ""';
    }
    if (!empty($params['lead_type'])) {
        $conditions[] = 'type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    }
    if (!empty($params['from_date']) && !empty($params['to_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.dateadded) BETWEEN "' . $CI->db->escape_str($params['from_date']) . '" AND "' . $CI->db->escape_str($params['to_date']) . '"';
    }
    if (!empty($params['up_to_date'])) {
        // $conditions[] = 'DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), \'%Y-%m-%d\') BETWEEN "' . $CI->db->escape_str($params['up_from_date']) . '" AND "' . $CI->db->escape_str($params['up_to_date']) . '"';
    }
    if (!empty($params['followup_to_date'])) {
        $conditions[] = 'DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($params['followup_from_date']) . '" AND "' . $CI->db->escape_str($params['followup_to_date']) . '"';
    }

    if (!empty($params['assign_from_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.dateassigned) BETWEEN "' . $CI->db->escape_str($params['assign_from_date']) . '" AND "' . $CI->db->escape_str($params['assign_to_date']) . '"';
    }

    if (!empty($params['last_contact_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.lastconnect_date) <= "' . $CI->db->escape_str($params['last_contact_date']) . '"';
    }

    if (!empty($params['last_update_date'])) {
        $conditions[] = 'DATE(' . $tblleads . '.lastupdate_date) <= "' . $CI->db->escape_str($params['last_update_date']) . '"';
    }

    if (isset($params['update_count_min']) && $params['update_count_min'] != '') {
        $conditions[] =  $tblleads . '.update_count Between "' . $CI->db->escape_str($params['update_count_min']) . '" AND "' . $CI->db->escape_str($params['update_count_max']) . '"';
    }


    if (!empty($params["utm_status"]) && $params["utm_status"] == 1) {
        $conditions[] = ' ' . $tblleads . '.utm_campaign_name != " " ';
    }

    if (!empty($params['utm_campaign_name'])) {
        $conditions[] = " " . $tblleads . ".utm_campaign_name IN ('" . implode("','", $CI->db->escape_str($params['utm_campaign_name'])) . "')";
    }

    if (!empty($params['utm_ads_set_name'])) {
        $conditions[] = " " . $tblleads . ".utm_ads_set_name IN ('" . implode("','", $CI->db->escape_str($params['utm_ads_set_name'])) . "')";
    }

    if (!empty($params['utm_ads_name'])) {
        $conditions[] = " " . $tblleads . ".utm_ads_name IN ('" . implode("','", $CI->db->escape_str($params['utm_ads_name'])) . "')";
    }

    if (!empty($params['utm_term'])) {
        $conditions[] = " " . $tblleads . ".utm_term IN ('" . implode("','", $CI->db->escape_str($params['utm_term'])) . "')";
    }

    if (!empty($params['utm_form_name'])) {
        $conditions[] = " " . $tblleads . ".utm_form_name IN ('" . implode("','", $CI->db->escape_str($params['utm_form_name'])) . "')";
    }


    if (!empty($params['department'])) {
        $conditions[] = " " . db_prefix() . "staff.department IN ('" . implode("','", $CI->db->escape_str($params['department'])) . "')";
    }

    if (!empty($params['location'])) {
        $conditions[] = " " . db_prefix() . "staff.office_location IN ('" . implode("','", $CI->db->escape_str($params['location'])) . "')";
    }


    if (!empty($params['view_form'])) {
        $websites = $params['view_form'];
        $escaped_websites = array_map(function ($w) {
            return "'" . trim($w) . "'";
        }, $websites);

        $conditions[] = " " . $tblleads . ".website IN (" . implode(',', $escaped_websites) . ")";
    }

    if (!empty($params['reference_name'])) {
        $reference_name = $params['reference_name'];
        $escaped_reference_name = array_map(function ($w) {
            return "'" . trim($w) . "'";
        }, $reference_name);

        $conditions[] = " " . $tblleads . ".reference_name IN (" . implode(',', $escaped_reference_name) . ")";
    }


    if (!empty($params['up_to_date'])) {
        $up_to_date = $params['up_to_date'];
        $up_from_date   = $params['up_from_date'];

        $up_from_date = $CI->db->escape_str($up_from_date); // Start date
        $up_to_date = $CI->db->escape_str($up_to_date);     // End date


        $where_c = "";
        $join_type = "";
        if ($params['update_count_min']  && $params['update_count_min'] != '') {

            $min = isset($params['update_count_min']) ? $params['update_count_min'] : 0;
            $max = isset($params['update_count_max']) ? $params['update_count_max'] : 0;
            $where_c = " AND ifnull(calls.update_count,0) between {$min} AND {$max} ";


            if ($min == 0) {
                $join_type = "RIGHT";
            }
        }

        if (!empty($params['assigned'])) {
            $where_c .= " AND calls.staffid IN (" . implode(',', $params['assigned']) . ") ";
        }

        $where_c .= " AND calls.staffid = leads.assigned";

        // SQL Queries
        $sql_p1 = " SELECT id 
    FROM  ( SELECT leads.id FROM {$tblleads} leads  {$join_type} JOIN " . db_prefix() . "calls_activity_logs calls ON (calls.contact IN (leads.phonenumber)  AND DATE(calls.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}'  {$where_c}) WHERE 1=1
    {$where_c} ";

        $sql_p1 .= " UNION ALL ";

        $sql_p1 .= "SELECT leads.id FROM {$tblleads} leads  {$join_type} JOIN " . db_prefix() . "calls_activity_logs calls ON ( calls.contact IN (leads.alternative_phonenumber) AND DATE(calls.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}'  {$where_c}) WHERE 1=1
     AND '{$up_to_date}' {$where_c} ";

        $sql_p1 .= " ) AS combined_result ";

        $conditions[] = "  {$tblleads}.id IN ($sql_p1) ";
    }

    // Apply conditions to WHERE clause
    if (!empty($conditions)) {
        $sql .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
    }

    // GROUP BY and ORDER BY
    $sql .= 'GROUP BY ' . $tblleads . '.status ';
    $sql .= 'ORDER BY ' . db_prefix() . 'leads_status.statusorder';

    // Execute query
    $result = $CI->db->query($sql)->result();

    if (is_admin()) {
        // echo $sql;
        // die;
    }
    // Prepare results
    if (!empty($result)) {
        $result = array_column($result, "total", "status_id");
    }

    $statuses[] = ["id" => "unknown", "name" => "Unknown Status"];

    $totalLeads = 0;
    foreach ($statuses as $key => $status) {
        $statuses[$key]['total'] = $result[$status["id"]] ?? 0;
        $totalLeads += $statuses[$key]['total'];
    }

    $statuses[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads];

    return $statuses;
}

function get_university_list($lead_type)
{
    $CI = &get_instance();
    return $CI->s_db->query("SELECT c.id country_id,co.name,c.country_name,u.university_name,u.university_name university_name_id,u.id university_id,u.fees_mandatory,u.exam FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id) where name='$lead_type' and status = 0 ")->result_array();
}

function get_country_list($segment_id)
{
    $CI = &get_instance();

    return $CI->s_db->query("SELECT c.id,c.country_name FROM  countries c where c.segment_id='{$segment_id}' ")->result_array();
}

function get_university_exam()
{
    $CI = &get_instance();

    return $CI->db->query("SELECT * from " . db_prefix() . "university_exams ")->result_array();
}
function get_client_list($university_name)
{
    $CI = &get_instance();

    $sql = "SELECT c.userid, CONCAT(b.first_name,' ',b.last_name) as full_name 
            FROM " . db_prefix() . "clients c 
            LEFT JOIN " . db_prefix() . "admission_preferences a ON c.userid = a.userid 
            LEFT JOIN " . db_prefix() . "basic_details b ON b.userid = c.userid 
            WHERE a.course = 'MBBS' 
            AND a.university LIKE '%$university_name%' 
            AND c.active = 1";

    return $CI->db->query($sql, ["%$university_name%"])->result_array();
}

function get_client_list_fly_batch($university_names = [], $batch_id = "")
{
    $CI = &get_instance();

    if (empty($university_names)) {
        return [];
    }

    // Clean and prepare university names
    $university_names = array_unique(array_filter($university_names));
    $escaped_universities = array_map(function ($name) use ($CI) {
        return $CI->db->escape($name);
    }, $university_names);

    $university_list = implode(',', $escaped_universities);

    $sql = "
    SELECT c.userid, CONCAT(b.first_name, ' ', b.last_name) AS full_name
    FROM " . db_prefix() . "clients c
    
    LEFT JOIN " . db_prefix() . "admission_preferences a ON c.userid = a.userid
    LEFT JOIN (
        SELECT t1.*
        FROM " . db_prefix() . "ticket_data t1
        INNER JOIN (
            SELECT client_id, MAX(id) AS max_id
            FROM " . db_prefix() . "ticket_data
            GROUP BY client_id
        ) t2 ON t1.client_id = t2.client_id AND t1.id = t2.max_id
    ) t ON t.client_id = c.userid
    LEFT JOIN " . db_prefix() . "basic_details b ON b.userid = c.userid
    WHERE a.course LIKE '%MBBS%'
    AND c.active = 1
    AND a.primary_university IN ($university_list)
   
";

    if (empty($batch_id)) {
        $sql .= "AND (
        t.ticket_status > 2 OR t.id IS NULL
    )";
    }


    return $CI->db->query($sql)->result_array();
}





function getLast10Digits($phoneNumber)
{
    // Remove non-numeric characters
    $digits = preg_replace('/\D/', '', $phoneNumber);

    // Get last 10 digits
    return substr($digits, -10);
}


function whatsapp_message_send($client_id, $whatsapp_template_id, $document_data = [])
{
    $CI = &get_instance();

    // Fetch client details
    $client = $CI->db->where('userid', $client_id)->get(db_prefix() . 'basic_details')->row();

    $CI->db->where('userid', $client_id);
    $basic_details = $CI->db->get(db_prefix() . 'basic_details')->row();


    $documents_list = get_orignal_document_data_list(array($client_id));
    $documents_name_list = $documents_list[$client_id]["document_names"];

    if (!empty($client->addedfrom)) {
        $CI->db->select("email,firstname,lastname,phonenumber");
        $CI->db->where('staffid', $client->addedfrom);
        $assigned_counselor = $CI->db->get(db_prefix() . 'staff')->row();
    }

    if (!empty($staff_id)) {
        $CI->db->select("email,firstname,lastname,phonenumber");
        $CI->db->where('staffid', $staff_id);
        $assigned_post_sale_counselor =  $CI->db->get(db_prefix() . 'staff')->row();
    }

    $CI->db->where('userid', $client_id);
    $admission_preferences = $CI->db->get(db_prefix() . 'admission_preferences')->row();
    if (!$client) {
        log_message('error', "Client not found: ID $client_id");
        return json_encode(["error" => "Client not found."]);
    }

    // Define authentication token and sender number
    $productToken = WHATSAAP_PRODUCT_KEY;
    $fromNumber = WHATSAAP_FROM_NUMBER;
    $templateNamespace = WHATSAAP_NAMESPACE;
    $toNumber = "0091" . getLast10Digits($client->mobile);

    // Fetch WhatsApp template
    $whatsapp = $CI->db->query("SELECT * FROM " . db_prefix() . "whatsapptemplates WHERE status= 1 AND id = ?", [$whatsapp_template_id])->row();
    if (!$whatsapp) {
        log_message('error', "WhatsApp template not found: ID $whatsapp_template_id");
        return json_encode(["error" => "Template not found."]);
    }

    // Extract template details
    $templateName = $whatsapp->template_name;
    $languageCode = $whatsapp->languageCode ?? "en"; // Default to English if not set
    $documentName = $whatsapp->documentName ?? "";
    $documentURL = $document_data["url"] ?? "";
    $mimeType = $whatsapp->mimeType ?? "application/pdf";

    // Fetch counsellor details
    $staff_data = $CI->db->select("CONCAT(firstname,' ',lastname) as name, phonenumber")
        ->where('staffid', get_staff_user_id())
        ->get(db_prefix() . 'staff')
        ->row();

    if (!$staff_data) {
        log_message('error', "Staff data not found for user ID " . get_staff_user_id());
        return json_encode(["error" => "Staff details not found."]);
    }

    $amount_details = get_clients_fees_details(2, $client_id, REGISTRATION_AMOUNT_ID);
    // Replace variables in template
    $applicant_name = trim($client->first_name . " " . $client->last_name);
    $variables = str_replace(
        [
            "{applicant_name}",
            "{counsellor_phonennumber}",
            "{primary_country}",
            "{primary_university}",
            "{registration_amount}",
            "{acadmic_year}",
            "{entrance_exam_details}",
            "{counsellor_name}",
            "{orignal_documents_received}"
        ],
        [
            $applicant_name ?? "",
            !empty($staff_data->phonenumber) ? $staff_data->phonenumber : "7217219100",
            $admission_preferences->primary_country ?? "",
            $admission_preferences->primary_university ?? "",
            $amount_details[0]["total_amount"] ?? "",
            $admission_preferences->acadmic_year ?? "",
            $entrance_exam_details ?? "",
            $counsellor_name ?? "",
            "*" . str_replace(",", "#@", $documents_name_list) . "*" ?? ""
        ],
        $whatsapp->variables_name ?? ""
    );

    // Prepare parameters
    $parameters = [];
    if (!empty($variables)) {
        $variables_array = array_map('trim', explode(",", $variables));
        foreach ($variables_array as $data) {
            if (empty($data)) {
                log_message('error', "Whatsapp parameter not found");
                return json_encode(["error" => "Whatsapp parameter not found"]);
            }
            $data =   str_replace("#@", ",", $data) ?? "";
            $parameters[] = ["type" => "text", "text" => $data];
        }
    }

    // Construct JSON payload
    $data = [
        "messages" => [
            "authentication" => ["producttoken" => $productToken],
            "msg" => [
                [
                    "from" => $fromNumber,
                    "to" => [["number" => $toNumber]],
                    "body" => [
                        "type" => "auto",
                        "content" => $templateName
                    ],
                    "allowedChannels" => ["WhatsApp"],
                    "richContent" => [
                        "conversation" => [
                            [
                                "template" => [
                                    "whatsapp" => [
                                        "namespace" => $templateNamespace,
                                        "element_name" => $templateName,
                                        "language" => [
                                            "policy" => "deterministic",
                                            "code" => $languageCode
                                        ],
                                        "components" => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    // Add document attachment if URL exists
    if (!empty($documentURL)) {
        $data["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
            "type" => "header",
            "parameters" => [
                [
                    "type" => "document",
                    "media" => [
                        "mediaName" => $documentName,
                        "mediaUri" => base_url() . $documentURL,
                        "mimeType" => $mimeType
                    ]
                ]
            ]
        ];
    }

    // Add body parameters if they exist
    if (!empty($parameters)) {
        $data["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
            "type" => "body",
            "parameters" => $parameters
        ];
    }




    // Initialize cURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://gw.messaging.cm.com/v1.0/message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10, // Set timeout to prevent hanging
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);

    // Execute cURL request
    $response = curl_exec($curl);
    $curlError = curl_error($curl);

    curl_close($curl);

    // Handle cURL errors
    if ($curlError) {
        log_message('error', "WhatsApp API cURL error: " . $curlError);
        return json_encode(["error" => "Failed to send message. API request error."]);
    }

    // Decode API response and check for errors
    $responseArray = json_decode($response, true);
    if (!$responseArray || isset($responseArray['error'])) {
        log_message('error', "WhatsApp API response error: " . $response);
        return json_encode(["error" => "WhatsApp API error", "details" => $responseArray]);
    }

    $insert_data = [];
    $insert_data["type"] = "whatsapp";
    $insert_data["template_id"] = $whatsapp_template_id;
    $insert_data["clientid"] = $client_id;
    $insert_data["datetime"] = date("Y-m-d H:i:s");
    $inserted = $CI->db->insert(db_prefix() . 'whatsapp_email_logs', $insert_data);

    return json_encode(["success" => "Message sent successfully.", "response" => $responseArray]);
}

function welcome_whatsapp_message_send($contact_number, $staff_id, $leadid, $whatsapp_template_id)
{
    $CI = &get_instance();

    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    try {
        // Get lead
        $lead = $CI->leads_model->get($leadid);
        if (!$lead) {
            throw new Exception("Lead not found.");
        }



        // Skip if welcome message already sent
        if ($lead->welcome_message_status == 1) {
            return true;
        }


        // Get staff
        $staff_data = $CI->db->select("CONCAT(firstname,' ',lastname) as name, phonenumber, whatsapp_status")
            ->where('staffid', $staff_id)
            ->get(db_prefix() . 'staff')
            ->row();

        if (!$staff_data) {
            throw new Exception("Staff details not found.");
        }

        if ((int)$staff_data->whatsapp_status == 0) {
            return true;
        }


//   if(!empty($lead->type) && $lead->type == 1){
//                             welcome_whatsapp_channel_study_abroad($contact_number, $staff_id, $leadid,8);
//                             }

        $CI->db->where('id', $leadid);
        $CI->db->update(db_prefix() . 'leads', ['welcome_message_status' => 1]);

        // Build WhatsApp data
        $productToken      = WHATSAAP_PRODUCT_KEY;
        $fromNumber        = WHATSAAP_FROM_NUMBER;
        $templateNamespace = WHATSAAP_NAMESPACE;
        $toNumber          = "0091" . getLast10Digits($contact_number);

        $whatsapp = $CI->db->query("SELECT * FROM " . db_prefix() . "whatsapptemplates WHERE status=1 AND id = ?", [$whatsapp_template_id])->row();
        if (!$whatsapp) {
            throw new Exception("WhatsApp template not found: ID $whatsapp_template_id");
        }

        $templateName = $whatsapp->template_name;
        $languageCode = $whatsapp->languageCode ?? "en";
        $documentName = $whatsapp->documentName ?? "";

        // Replace variables
        $variables = str_replace(
            ["{counsellor_name}", "{counsellor_phonennumber}"],
            [
                !empty($staff_data->name) ? $staff_data->name : "",
                !empty($staff_data->phonenumber) ? $staff_data->phonenumber : "7217219100"
            ],
            $whatsapp->variables_name ?? ""
        );

        $parameters = [];
        if (!empty($variables)) {
            $variables_array = array_map('trim', explode(",", $variables));
            foreach ($variables_array as $data) {
                if (empty($data)) {
                    throw new Exception("Missing WhatsApp parameter.");
                }
                $parameters[] = ["type" => "text", "text" => str_replace("#@", ",", $data)];
            }
        }

        // Build API payload
        $payload = [
            "messages" => [
                "authentication" => ["producttoken" => $productToken],
                "msg" => [[
                    "from" => $fromNumber,
                    "to"   => [["number" => $toNumber]],
                    "body" => [
                        "type"    => "auto",
                        "content" => $templateName
                    ],
                    "allowedChannels" => ["WhatsApp"],
                    "richContent" => [
                        "conversation" => [[
                            "template" => [
                                "whatsapp" => [
                                    "namespace"    => $templateNamespace,
                                    "element_name" => $templateName,
                                    "language"     => [
                                        "policy" => "deterministic",
                                        "code"   => $languageCode
                                    ],
                                    "components" => []
                                ]
                            ]
                        ]]
                    ]
                ]]
            ]
        ];

        // Attach document if exists
        if (!empty($whatsapp->documentURL)) {
            $payload["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
                "type" => "header",
                "parameters" => [[
                    "type"  => "document",
                    "media" => [
                        "mediaName" => $documentName,
                        "mediaUri"  => base_url() . $whatsapp->documentURL,
                        "mimeType"  => $whatsapp->mimeType ?? 'application/pdf'
                    ]
                ]]
            ];
        }

        // Add body text parameters
        if (!empty($parameters)) {
            $payload["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
                "type" => "body",
                "parameters" => $parameters
            ];
        }

        // Send the API request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://gw.messaging.cm.com/v1.0/message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }

        $responseArray = json_decode($response, true);
        if (!$responseArray || isset($responseArray['error'])) {
            throw new Exception("API Error: " . json_encode($responseArray));
        }

        // Insert log (fallback client ID to 0 if not available)
        $insert_data = [
            "type"       => "whatsapp",
            "template_id" => $whatsapp_template_id,
            "clientid"   => !empty($lead->clientid) ? $lead->clientid : 0,
            "datetime"   => date("Y-m-d H:i:s"),
            "contact"   => $toNumber
        ];
        $CI->db->insert(db_prefix() . 'whatsapp_email_logs', $insert_data);
        $CI->leads_model->log_lead_activity($leadid, "WhatsApp message successfully triggered to {$toNumber}.", true);

        return json_encode(["success" => "Message sent successfully.", "response" => $responseArray]);
    } catch (Exception $e) {
        log_message('error', 'WhatsApp Message Error: ' . $e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}

function welcome_whatsapp_channel_study_abroad($contact_number, $staff_id, $leadid,$whatsapp_template_id)
{
    $CI = &get_instance();

    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    try {
        // Get lead
        $lead = $CI->leads_model->get($leadid);
        if (!$lead) {
            throw new Exception("Lead not found.");
        }



        // Skip if welcome message already sent
        if ($lead->welcome_message_status == 1) {
            return true;
        }
        
           if ($lead->type != 1) {
            return true;
        }


        // Get staff
        $staff_data = $CI->db->select("CONCAT(firstname,' ',lastname) as name, phonenumber, whatsapp_status")
            ->where('staffid', $staff_id)
            ->get(db_prefix() . 'staff')
            ->row();

        if (!$staff_data) {
            throw new Exception("Staff details not found.");
        }

        // if ((int)$staff_data->whatsapp_status == 0) {
        //     return true;
        // }



        // $CI->db->where('id', $leadid);
        // $CI->db->update(db_prefix() . 'leads', ['welcome_message_status' => 1]);

        // Build WhatsApp data
        $productToken      = WHATSAAP_PRODUCT_KEY;
        $fromNumber        = WHATSAAP_FROM_NUMBER;
        $templateNamespace = WHATSAAP_NAMESPACE;
        $toNumber          = "0091" . getLast10Digits($contact_number);

        $whatsapp = $CI->db->query("SELECT * FROM " . db_prefix() . "whatsapptemplates WHERE status=1 AND id = ?", [$whatsapp_template_id])->row();
        if (!$whatsapp) {
            throw new Exception("WhatsApp template not found: ID $whatsapp_template_id");
        }

        $templateName = $whatsapp->template_name;
        $languageCode = $whatsapp->languageCode ?? "en";
        $documentName = $whatsapp->documentName ?? "";

        // Replace variables
        $variables = str_replace(
            [],
            [],
            $whatsapp->variables_name ?? ""
        );

        $parameters = [];
        if (!empty($variables)) {
            $variables_array = array_map('trim', explode(",", $variables));
            foreach ($variables_array as $data) {
                if (empty($data)) {
                    throw new Exception("Missing WhatsApp parameter.");
                }
                $parameters[] = ["type" => "text", "text" => str_replace("#@", ",", $data)];
            }
        }

        // Build API payload
        $payload = [
            "messages" => [
                "authentication" => ["producttoken" => $productToken],
                "msg" => [[
                    "from" => $fromNumber,
                    "to"   => [["number" => $toNumber]],
                    "body" => [
                        "type"    => "auto",
                        "content" => $templateName
                    ],
                    "allowedChannels" => ["WhatsApp"],
                    "richContent" => [
                        "conversation" => [[
                            "template" => [
                                "whatsapp" => [
                                    "namespace"    => $templateNamespace,
                                    "element_name" => $templateName,
                                    "language"     => [
                                        "policy" => "deterministic",
                                        "code"   => $languageCode
                                    ],
                                    "components" => []
                                ]
                            ]
                        ]]
                    ]
                ]]
            ]
        ];

        // Attach document if exists
        if (!empty($whatsapp->documentURL)) {
            $payload["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
                "type" => "header",
                "parameters" => [[
                    "type"  => "document",
                    "media" => [
                        "mediaName" => $documentName,
                        "mediaUri"  => base_url() . $whatsapp->documentURL,
                        "mimeType"  => $whatsapp->mimeType ?? 'application/pdf'
                    ]
                ]]
            ];
        }

        // Add body text parameters
        if (!empty($parameters)) {
            $payload["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
                "type" => "body",
                "parameters" => $parameters
            ];
        }

        // Send the API request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://gw.messaging.cm.com/v1.0/message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }

        $responseArray = json_decode($response, true);
        if (!$responseArray || isset($responseArray['error'])) {
            throw new Exception("API Error: " . json_encode($responseArray));
        }

        // Insert log (fallback client ID to 0 if not available)
        $insert_data = [
            "type"       => "whatsapp",
            "template_id" => $whatsapp_template_id,
            "clientid"   => !empty($lead->clientid) ? $lead->clientid : 0,
            "datetime"   => date("Y-m-d H:i:s"),
            "contact"   => $toNumber
        ];
        $CI->db->insert(db_prefix() . 'whatsapp_email_logs', $insert_data);
        $CI->leads_model->log_lead_activity($leadid, "WhatsApp message successfully triggered to {$toNumber}.", true);

        return json_encode(["success" => "Message sent successfully.", "response" => $responseArray]);
    } catch (Exception $e) {
        log_message('error', 'WhatsApp Message Error: ' . $e->getMessage());
        return json_encode(["error" => $e->getMessage()]);
    }
}



function extractYear($date)
{
    if (strpos($date, '-') !== false) {
        // If the date contains a hyphen, it's in YYYY-MM format
        list($year, $month) = explode('-', $date);
        return $year; // Return only the year
    }
    return $date; // Already in YYYY format
}



function get_visitor_leads_summary_filter_neww($params)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
        $CI->load->model('staff_model');
    }

    $current_date_time = date('Y-m-d H:i:s');
    $current_date = date('Y-m-d');


    $statuses = $CI->leads_model->get_status();
    $visitor_statuses = $CI->staff_model->visitor_status();
    $visitor_type = $CI->staff_model->visitor_type();
    $tblleads = db_prefix() . 'leads';
    $schedule = [];
    $schedule[] = array("id" => "0", "name" => "Previous", "color" => "Red");
    $schedule[] = array("id" => "1", "name" => "Today", "color" => "green");
    $schedule[] = array("id" => "2", "name" => "Upcoming", "color" => "orange");

    $has_permission_view   = has_permission('leads', '', 'view');
    $whereNoViewPermission = '(' . db_prefix() . 'visitor_request.assigned = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'visitor_request.created_by=' . get_staff_user_id() . ')';

    // Fetch role and handle reporting persons for role ID 3
    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    $tids = '';
    if ($role == 3) {
        $sid = get_staff_user_id();
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = !empty($sids) ? " AND ( " . db_prefix() . "visitor_request.assigned IN ($sid, $sids) OR " . db_prefix() . "visitor_request.created_by IN ($sid, $sids)  )" : " AND ( " . db_prefix() . "visitor_request.assigned IN ($sid) OR " . db_prefix() . "visitor_request.created_by IN ($sid) ) ";
    }

    // Base query
    // Base query with visitor_request as main table
    $select = 'SELECT 
IFNULL(' . db_prefix() . 'leads_status.id, "unknown") AS status_id, 
COUNT( ' . db_prefix() . 'visitor_request.lead_id) AS total 
';
    $sql = "";
    $sql .= 'FROM ' . db_prefix() . 'visitor_request ';
    $sql .= 'LEFT JOIN ' . db_prefix() . 'visitor_status ON ' . db_prefix() . 'visitor_status.id = ' . db_prefix() . 'visitor_request.status ';
    $sql .= 'LEFT JOIN ' . db_prefix() . 'visitor_type ON ' . db_prefix() . 'visitor_type.id = ' . db_prefix() . 'visitor_request.visitor_type ';
    $sql .= ' JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'visitor_request.lead_id ';
    $sql .= ' JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads.status = ' . db_prefix() . 'leads_status.id ';



    if (!empty($params['location']) || !empty($params['department'])) {
        $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'visitor_request.assigned ';
    }

    // WHERE clause
    $conditions = [];
    if (!$has_permission_view) {
        $conditions[] = $whereNoViewPermission;
    }
    if (!empty($params['status'])) {
        $conditions[] = db_prefix() . 'visitor_request.status IN (' . implode(',', $params['status']) . ')';
    }

    if (!empty($params['location'])) {
        $conditions[] = db_prefix() . 'visitor_request.location IN (' . implode(',', $params['location']) . ')';
    }

    if (!empty($params['type'])) {
        $conditions[] = db_prefix() . 'visitor_request.visitor_type IN (' . implode(',', $params['type']) . ')';
    }

    if (!empty($params['attendee'])) {
        $conditions[] = db_prefix() . 'visitor_request.assigned IN (' . implode(',', $params['attendee']) . ')';
    } else if ($role == 3) {
        $conditions[] = substr($tids, 4); // Remove the leading " AND"
    }

    if (!empty($params['lead_status'])) {
        $conditions[] = $tblleads . '.status IN (' . implode(',', $CI->db->escape_str($params['lead_status'])) . ')';
    }
    if (!empty($params['lead_type'])) {
        $conditions[] = $tblleads . '.type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    }

    if (!empty($params['assigned'])) {
        $conditions[] = db_prefix() . 'visitor_request.created_by IN (' . implode(',', $CI->db->escape_str($params['assigned'])) . ')';
    }

    if (!empty($params['category'])) {
        if ($params['category'] < 0) {
            $conditions[] = db_prefix() . 'visitor_request.date_of_visit < "' . $current_date_time . '" ';
        } else if ($params['category'] == 1) {
            $conditions[] = ' Date(' . db_prefix() . 'visitor_request.date_of_visit) = "' . $current_date . '" ';
        } else if ($params['category'] == 2) {
            $conditions[] = db_prefix() . 'visitor_request.date_of_visit > "' . $current_date_time . '" ';
        }
    }

    if (!empty($params['from_date']) && !empty($params['to_date'])) {
        $conditions[] = 'DATE(' . db_prefix() . 'visitor_request.date_of_visit) BETWEEN "' . $CI->db->escape_str($params['from_date']) . '" AND "' . $CI->db->escape_str($params['to_date']) . '"';
    }
    if (!empty($params['source_type'])) {
        $conditions[] = $tblleads . '.source IN (' . implode(',', $CI->db->escape_str($params['source_type'])) . ')';
    }




    // Apply conditions to WHERE clause
    if (!empty($conditions)) {
        $sql .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
    }

    // GROUP BY and ORDER BY
    $group_by = 'GROUP BY ' . $tblleads . '.status ';
    $group_by .= 'ORDER BY ' . db_prefix() . 'leads_status.statusorder';
    // Execute query
    $result = $CI->db->query($select . $sql . $group_by)->result();



    $select = " Select " . db_prefix() . "visitor_status.name," . db_prefix() . "visitor_status.id,count(1) total ";
    $group_by = 'GROUP BY ' . db_prefix() . 'visitor_status.id ';
    $group_by .= 'ORDER BY ' . db_prefix() . 'visitor_status.id';
    // echo $select . $sql . $group_by; die;
    $result_status = $CI->db->query($select . $sql . $group_by)->result();


    $select = " Select " . db_prefix() . "visitor_type.name," . db_prefix() . "visitor_type.id,count(1) total ";
    $group_by = 'GROUP BY ' . db_prefix() . 'visitor_type.id ';
    $group_by .= 'ORDER BY ' . db_prefix() . 'visitor_type.id';
    $result_type = $CI->db->query($select . $sql . $group_by)->result();


    $select = "
    SELECT 
        CASE 
            WHEN date_of_visit < '" . $current_date_time . "' THEN 0
            WHEN DATE(date_of_visit) = '" . $current_date . "' THEN 1
            WHEN date_of_visit > '" . $current_date_time . "' THEN 2
            ELSE 0
        END AS id,
        COUNT(1) AS total
";

    $group_by = "
    GROUP BY id
    ORDER BY id
";

    // echo $select . $sql . $group_by;

    $result_schedule = $CI->db->query($select . $sql . $group_by)->result();

    $response_data = [];
    // Prepare results
    if (!empty($result)) {
        $result = array_column($result, "total", "status_id");
    }

    $statuses[] = ["id" => "unknown", "name" => "Unknown Status"];

    $totalLeads = 0;
    foreach ($statuses as $key => $status) {
        $statuses[$key]['total'] = $result[$status["id"]] ?? 0;
        $totalLeads += $statuses[$key]['total'];
    }

    $statuses[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads];
    $response_data["lead_status"] = $statuses;

    if (!empty($result_status)) {
        $result_status = array_column($result_status, "total", "id");
        // print_r($result_status);
        // die;
    }

    $totalStatus = 0;

    foreach ($visitor_statuses as $key => $status) {
        if (!empty($status['name'])) {
            $visitor_statuses[$key]['total'] = $result_status[$status["id"]] ?? 0;
            $totalStatus += $visitor_statuses[$key]['total'];
        }
    }
    $visitor_statuses[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalStatus];
    $response_data["visitor_status"] = $visitor_statuses;

    if (!empty($result_type)) {
        $result_type = array_column($result_type, "total", "id");
    }

    $totalType = 0;
    foreach ($visitor_type as $key => $status) {
        if (!empty($status['name'])) {
            $visitor_type[$key]['total'] = $result_type[$status["id"]] ?? 0;
            $totalType += $visitor_type[$key]['total'];
        }
    }
    $visitor_type[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalType];
    $response_data["visitor_type"] = $visitor_type;


    if (!empty($result_schedule)) {
        $result_schedule = array_column($result_schedule, "total", "id");
    }



    $totalSchedule = 0;
    foreach ($schedule as $key => $status) {
        if (!empty($status['name'])) {
            $schedule[$key]['total'] = $result_schedule[$status["id"]] ?? 0;
            $totalSchedule += $schedule[$key]['total'];
        }
    }

    $schedule[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalSchedule];
    $response_data["schedule"] = $schedule;
    return $response_data;
}


function get_departure_list()
{
    $CI = &get_instance();

    return $CI->db->query("SELECT * FROM  " . db_prefix() . "departure_location")->result_array();
}

function get_states()
{
    $CI = &get_instance();

    return $CI->db->query("SELECT * from " . db_prefix() . "states ")->result_array();
}


function visitor_status()
{
    $CI = &get_instance();

    $CI->db->where('status', 1);
    return $CI->db->get(db_prefix() . 'visitor_status')->result_array();
}

function get_relationShip()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $board_dropdown = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'client_relationship')
            ->get()
            ->result_array();

        return $board_dropdown; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching RelationShip: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}
function get_degree()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $board_dropdown = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'admission_program')
            ->get()
            ->result_array();

        return $board_dropdown; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching RelationShip: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_examList()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $board_dropdown = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'admission_entrance')
            ->get()
            ->result_array();

        return $board_dropdown; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching RelationShip: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}
