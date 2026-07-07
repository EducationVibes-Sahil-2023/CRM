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
    
    if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = " . db_prefix() . "leads.id 
            AND tg.rel_type = 'lead'
    ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND tg.tag_id IN (" . implode(',', $escapedTags) . ")";
}
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
    
    
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = ".db_prefix()."leads.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
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
        
        
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
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
        
        
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
}


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
        
        
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
}


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
    
    
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
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

    
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
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


if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
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
    
    
if (!empty($params['tags'])) {

    $sql .= "
        INNER JOIN tbltaggables tg 
            ON tg.rel_id = l.id 
            AND tg.rel_type = 'lead'
           ";

    $escapedTags = array_map([$CI->db, 'escape'], $params['tags']);

    $sql .= " AND  tg.tag_id IN (" . implode(',', $escapedTags) . ")";
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
    else
    {
          $check_today = false;
      
        if ( $role == 3) {
            $sql .= ' AND l.assigned in (' . get_staff_user_id() . ',' . $sids . ')';
        } else {
            if(!is_admin()){
            $sql .= ' AND l.assigned in (' . get_staff_user_id(). ')';
            }
        }
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


  $dataParam = $params;
  
  unset($dataParam['show_lead_status']);
$filtered = array_filter($dataParam);
    

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
    
    // if(is_admin()){
    //     $sql .= "SELECT calls.id call_id,call_status,duration,adjusted_call_start,l.id lead_id FROM " . db_prefix() . "leads l ";
    // }
    // else{
 
    //      $sql .= "SELECT  COUNT(CASE WHEN calls.call_status = 'answered' THEN 1 END) AS answered_count,count(calls.id) total_calls,SUM(IF(call_status IN ('answered', 'status_unknown'), IFNULL(duration, 0), 0)) AS total_call_duration,adjusted_call_start,
    // COUNT(DISTINCT l.id) AS update_count FROM " . db_prefix() . "leads l ";
    // }
    
    
    $sql .= "SELECT calls.id call_id,call_status,duration,adjusted_call_start,l.id lead_id,calls.staffid FROM " . db_prefix() . "leads l ";
     


 if (
    isset($params['time_condition'], $params['time_minutes']) &&
    $params['time_condition'] !== '' &&
    is_numeric($params['time_minutes'])
) {

    $condition = $params['time_condition'];
    $minutes   = (int)$params['time_minutes'];

    // Allow only safe operators
    if (!in_array($condition, ['>', '<', '=', '>=', '<='])) {
        $condition = '>';
    }

    // Required joins
    $sql .= ' JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = l.assigned ';
    $sql .= ' JOIN ' . db_prefix() . 'staff_department 
              ON ' . db_prefix() . 'staff_department.id = ' . db_prefix() . 'staff.department ';

}


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
    else
    {
          $check_today = false;
      
        if ( $role == 3) {
            $sql .= ' AND l.assigned in (' . get_staff_user_id() . ',' . $sids . ')';
        } else {
            if(!is_admin()){
            $sql .= ' AND l.assigned in (' . get_staff_user_id(). ')';
            }
        }
    }
    
    if (isset($params['sub_status'])) {
    $check_today = false;

    if (empty($params['sub_status'])) {
        $sql .= " AND (l.sub_status IS NULL || l.sub_status = '') ";
    } else {
        $escaped = array_map(
            [$CI->db, 'escape'],
            (array) $params['sub_status']
        );

        $sql .= " AND l.sub_status IN (" . implode(",", $escaped) . ") ";
    }
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

    if (empty($filtered) && empty($params['up_to_date']) && empty($params['last_update_date']) && empty($params['to_date']) && empty($params['last_contact_date']) &&  empty($params['followup_to_date']) && empty($params['assign_to_date'])) {
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
 $check_today = false;
    $reference_name = $params['reference_name'];

    // Convert to array if it's a string
    if (!is_array($reference_name)) {
        $reference_name = explode(',', $reference_name);
    }

    // Trim + escape values
    $escaped_reference_name = array_map(function ($w) use ($CI) {
        return "'" . $CI->db->escape_str(trim($w)) . "'";
    }, $reference_name);

    $conditions[] = "l.reference_name IN (" . implode(',', $escaped_reference_name) . ")";
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
        $grup_by .= ',staffid';
    }

    $sql_add = "";

    $having = "";
    if (!empty($params['last_contact_date']) ||  (isset($params['update_count_max']) && $params['update_count_max'] != '') || !empty($params['last_update_date'])) {

        // if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        //     $min = $params['update_count_min'];
        //     $max = $params['update_count_max'];
        //     $having .= ' HAVING COUNT(call_id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        // }
    }
    
    
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $having .= ' HAVING COUNT(call_id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }


    // $sql .= " GROUP BY l.id " . $grup_by . " " . $having . $sql_add;

    $alt =  str_replace("l.phonenumber", "l.alternative_phonenumber", $sql);
    $sql .= " UNION ALL ";

    $sql = $sql . " " . $alt;
    
   
        
       $sql=   "SELECT  COUNT(CASE WHEN call_status = 'answered' THEN 1 END) AS answered_count,count(call_id) total_calls,SUM(IF(call_status IN ('answered', 'status_unknown'), IFNULL(duration, 0), 0)) AS total_call_duration,adjusted_call_start,
    COUNT(DISTINCT lead_id) AS update_count,staffid from ( ". $sql." ) t  GROUP BY lead_id " . $grup_by . " " . $having . $sql_add;
     
    


    $duration = $CI->db->query($sql)->result_array();




    return !empty($duration) ? $duration : [];
}

function graphDataCalls($params)
{
    $CI = &get_instance();
    $showStatus = false;
    $where = [];

$dataParam = $params;
  unset($dataParam['show_lead_status']);
$filtered = array_filter($dataParam);




    // ✅ Assigned filter
    if (!empty($params['assigned'])) {
        $assigned = implode(",", array_map('intval', $params['assigned']));
        $where[] = "l.assigned IN ($assigned)";
    }

    // ✅ Call Date filter
    if (!empty($params['up_from_date']) && !empty($params['up_to_date'])) {
        $from = $CI->db->escape_str($params['up_from_date']);
        $to   = $CI->db->escape_str($params['up_to_date']);
        $showStatus = true;
        $fromDate = new DateTime($from);
        $toDate   = new DateTime($to);
        $diff = $fromDate->diff($toDate)->days;
        if ($diff > 31) $showStatus = false;
        $where[] = "DATE(FROM_UNIXTIME(calls.call_start + 19800)) BETWEEN '$from' AND '$to'";
    } elseif (!empty($params['last_update_date'])) {
        $date = $CI->db->escape_str($params['last_update_date']);
        $where[] = "DATE(FROM_UNIXTIME(calls.call_start + 19800)) <= '$date'";
    } else {
        if(empty($filtered)){
        $today = date('Y-m-d');
        $where[] = " adjusted_call_start = '$today' ";
        $showStatus = true;
        }
                $showStatus = true;
    }

    // ✅ Lead Created Date
    if (!empty($params['to_date'])) {
        $from_date = $CI->db->escape_str($params['from_date']);
        $to_date   = $CI->db->escape_str($params['to_date']);
        $showStatus = true;
        $fromDate = new DateTime($from_date);
        $toDate   = new DateTime($to_date);
        $diff = $fromDate->diff($toDate)->days;
        if ($diff > 31) $showStatus = false;
        $where[] = "DATE(l.dateadded) BETWEEN '$from_date' AND '$to_date'";
    }

    // ✅ Assign Date
    if (!empty($params['assign_to_date'])) {
        $from = $CI->db->escape_str($params['assign_from_date']);
        $to   = $CI->db->escape_str($params['assign_to_date']);
        $showStatus = true;
        $fromDate = new DateTime($from);
        $toDate   = new DateTime($to);
        $diff = $fromDate->diff($toDate)->days;
        if ($diff > 31) $showStatus = false;
        $where[] = "DATE(l.dateassigned) BETWEEN '$from' AND '$to'";
    }

    // ✅ Follow-up Date (requires JOIN)
    $joinReminder = "";
    if (!empty($params['followup_to_date'])) {
        $from = $CI->db->escape_str($params['followup_from_date']);
        $to   = $CI->db->escape_str($params['followup_to_date']);
        $joinReminder = "LEFT JOIN tblreminders r ON r.rel_id = l.id";
        $where[] = "DATE(r.date) BETWEEN '$from' AND '$to'";
        $showStatus = true;
        $fromDate = new DateTime($from);
        $toDate   = new DateTime($to);
        $diff = $fromDate->diff($toDate)->days;
        if ($diff > 31) $showStatus = false;
    }

    // ✅ Other filters
    if (!empty($params['status'])) {
        $status = implode(",", array_map('intval', $params['status']));
        $where[] = "l.status IN ($status)";
    }
    if (!empty($params['source'])) {
        $source = implode(",", array_map('intval', $params['source']));
        $where[] = "l.source IN ($source)";
    }
    if (!empty($params['lead_type'])) {
        $type = implode(",", array_map('intval', $params['lead_type']));
        $where[] = "l.type IN ($type)";
    }
    if (isset($params['sub_status'])) {

    if (empty($params['sub_status'])) {
        $where[] = "(l.sub_status = '' OR l.sub_status IS NULL)";
    } else {
        $type = implode(',', array_map('intval', (array) $params['sub_status']));
        $where[] = "l.sub_status IN ($type)";
    }
}

    if (!is_admin()) {
        $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
        if ($role == 3) {
            $sid = get_staff_user_id();
            $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($sid))->result_array();
            $CI->db->close();
            $CI->db->initialize();
            $idsarr = array_column($teamids, 'staffid');
            $sids = implode(",", $idsarr);
            $where[] = !empty($sids) ? " assigned IN ($sid, $sids)" : " assigned IN ($sid)";
        } else {
            $where[] = " assigned = " . get_staff_user_id();
        }
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // ========= HOURLY DATA =========
   $hourlySql = "
SELECT 
    hour,
    SUM(total_duration) AS total_call_duration,
    COUNT( lead_id) AS unique_calls
FROM (
    
    SELECT 
        HOUR(FROM_UNIXTIME(call_start + 19800)) AS hour,
        lead_id,
        duration AS total_duration
        
    FROM (
        
        -- Primary phone
        SELECT 
            calls.call_start,
            l.id AS lead_id,
            IF(calls.call_status IN ('answered','status_unknown'), IFNULL(calls.duration,0), 0) AS duration
        FROM tblleads l
        $joinReminder
        INNER JOIN tblcalls_activity_logs calls 
            ON calls.contact = l.phonenumber
            AND l.assigned = calls.staffid
        $whereSql

        UNION

        -- Alternative phone
        SELECT 
            calls.call_start,
            l.id AS lead_id,
            IF(calls.call_status IN ('answered','status_unknown'), IFNULL(calls.duration,0), 0) AS duration
        FROM tblleads l
        $joinReminder
        INNER JOIN tblcalls_activity_logs calls 
            ON calls.contact = l.alternative_phonenumber
            AND l.assigned = calls.staffid
        $whereSql

    ) AS merged

    GROUP BY call_start,lead_id,hour

) AS final

WHERE hour BETWEEN 9 AND 21
GROUP BY hour
ORDER BY hour ASC
";
    // if(is_admin())
    // {
    //     echo $hourlySql;
    //     die;
    // }
    
    
$statusSql = "
SELECT 
    s.id AS status_id,
    s.name AS status_name,
    s.color AS status_color,
    COUNT(DISTINCT lead_id) AS lead_count,
    SUM(total_duration) AS total_call_duration
FROM (
    
    SELECT 
        status_id,
        lead_id,
        MAX(duration) AS total_duration
    FROM (
        
        -- Primary phone
        SELECT 
            l.status AS status_id,
            l.id AS lead_id,
            IF(calls.call_status IN ('answered','status_unknown'), IFNULL(calls.duration,0), 0) AS duration
        FROM tblleads l
        $joinReminder
        INNER JOIN tblcalls_activity_logs calls 
            ON calls.contact = l.phonenumber
            AND l.assigned = calls.staffid
        $whereSql

        UNION

        -- Alternative phone
        SELECT 
            l.status AS status_id,
            l.id AS lead_id,
            IF(calls.call_status IN ('answered','status_unknown'), IFNULL(calls.duration,0), 0) AS duration
        FROM tblleads l
        $joinReminder
        INNER JOIN tblcalls_activity_logs calls 
            ON calls.contact = l.alternative_phonenumber
            AND l.assigned = calls.staffid
        $whereSql

    ) AS merged

    GROUP BY status_id, lead_id

) AS combined

LEFT JOIN tblleads_status s ON s.id = combined.status_id

GROUP BY s.id, s.name
ORDER BY s.id ASC
";

    if ($showStatus == false) {
        return [
            'hourly' => [],
            'status' => [],
        ];
    }

    return [
        'hourly' => $CI->db->query($hourlySql)->result_array(),
        'status' => $CI->db->query($statusSql)->result_array(),
    ];
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
    // $sql = 'SELECT IFNULL(' . db_prefix() . 'leads_status.id, "unknown") AS status_id, COUNT(DISTINCT ' . $tblleads . '.id) AS total ';
    // if(is_admin())
    // {
          $sql = 'SELECT  
        
        tblleads.id,
        tblleads.status,
        IFNULL(COUNT(tblcalls_activity_logs.id), 0) AS call_count ';
    // }
    $sql .= 'FROM ' . $tblleads . ' ';
    $sql .= 'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . $tblleads . '.status = ' . db_prefix() . 'leads_status.id ';
    if (!empty($params['up_to_date']) || (isset($params['update_count_min']) && $params['update_count_min'] != '')) {
        
         $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs ON  ' . db_prefix() . 'calls_activity_logs.contact = ' . $tblleads . '.phonenumber AND ' . db_prefix() . 'calls_activity_logs.staffid = ' . $tblleads . '.assigned ';
    }
    else{
     $sql .= 'LEFT JOIN ' . db_prefix() . 'calls_activity_logs ON  ' . db_prefix() . 'calls_activity_logs.contact = ' . $tblleads . '.phonenumber AND ' . db_prefix() . 'calls_activity_logs.staffid = ' . $tblleads . '.assigned ';
    }
     
    


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


    // if (!empty($params['location']) || !empty($params['department'])) {
    //     $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $tblleads . '.assigned ';
    // }
    
      if (!empty($params['location']) || !empty($params['department'])) {
        $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $tblleads . '.assigned ';
    } else if ((isset($params['time_condition']) && $params['time_condition'] != '') && (isset($params['time_minutes']) && $params['time_minutes'] >= 0)) {
        $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $tblleads . '.assigned ';
    }

    if ((isset($params['time_condition']) && $params['time_condition'] != '') && (isset($params['time_minutes']) && $params['time_minutes'] >= 0)) {
        $sql .= ' JOIN  ' . db_prefix() . 'staff_department on ' . db_prefix() . 'staff_department.id = ' . db_prefix() . 'staff.department ';
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
    
     if (isset($params['sub_status'])) {

    if (empty($params['sub_status'])) {
        $conditions[] = "(sub_status = '' OR sub_status IS NULL )";
    } else {
        $escaped = array_map(
            [$CI->db, 'escape'],
            (array) $params['sub_status']
        );

        $conditions[] = 'sub_status IN (' . implode(',', $escaped) . ')';
    }
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
    // if (!empty($params['reference_name'])) {
    //     $reference_name = $params['reference_name'];
    //     $escaped_reference_name = array_map(function ($w) {
    //         return "'" . trim($w) . "'";
    //     }, $reference_name);

    //     // $conditions[] = " " . $tblleads . ".reference_name IN (" . implode(',', $escaped_reference_name) . ")";
    // }
    
    
    $having_query ="";

// if(is_admin()){
     if (isset($params['update_count_min']) && $params['update_count_min'] != '') {
   $having_query = ' having IFNULL(COUNT(tblcalls_activity_logs.id), 0)  BETWEEN "' . $CI->db->escape_str($params['update_count_min']) . '" AND "' . $CI->db->escape_str($params['update_count_max']) . '" ';
     }
// }
// else{
//      if (isset($params['update_count_min']) && $params['update_count_min'] != '') {
//         $conditions[] =  $tblleads . '.update_count Between "' . $CI->db->escape_str($params['update_count_min']) . '" AND "' . $CI->db->escape_str($params['update_count_max']) . '"';
//     }
// }
   
    


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

    // Convert to array if it's a string
    if (!is_array($reference_name)) {
        $reference_name = explode(',', $reference_name);
    }

    // Trim + escape values
    $escaped_reference_name = array_map(function ($w) use ($CI) {
        return "'" . $CI->db->escape_str(trim($w)) . "'";
    }, $reference_name);

    $conditions[] = $tblleads . ".reference_name IN (" . implode(',', $escaped_reference_name) . ")";
}
    
    //     if(is_admin())
    // {
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
        if (!empty($params['up_to_date'])) {
                        $up_to_date = $params['up_to_date'];
                        $up_from_date   = $params['up_from_date'];
                        $up_from_date = $CI->db->escape_str($up_from_date); // Start date
                        $up_to_date = $CI->db->escape_str($up_to_date);     // End date
                        
                        $conditions[] =  " ".db_prefix(). "calls_activity_logs.adjusted_call_start BETWEEN '{$up_from_date}' AND '{$up_to_date}' ";
    
        }
    // }

    
    

     if ((isset($params['time_condition']) && $params['time_condition'] != '') && (isset($params['time_minutes']) && $params['time_minutes'] >= 0)) {

        $condition = $params['time_condition'];
        $minutes   = $params['time_minutes'];

          if (!in_array($condition, ['>', '<', '=', '>=', '<='])) {
            $condition = '>';
        }

        $minutes = (int)$minutes;
        if (!empty($condition) && is_numeric($minutes)) {
            $conditions[] = " (
        TIMESTAMPDIFF(
        SECOND,

        CASE 
        WHEN DAYNAME(" . db_prefix() . "leads.dateassigned) = 'Sunday'
        THEN CONCAT(
        DATE_ADD(DATE(" . db_prefix() . "leads.dateassigned), INTERVAL 1 DAY),
        ' ',
        COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00')
        )

        WHEN TIME(" . db_prefix() . "leads.dateassigned) >
        COALESCE(" . db_prefix() . "staff_department.office_end_time,'20:00:00')

        THEN CONCAT(
        DATE_ADD(DATE(" . db_prefix() . "leads.dateassigned), INTERVAL 1 DAY),
        ' ',
        COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00')
        )

        WHEN TIME(" . db_prefix() . "leads.dateassigned) <
        COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00')

        THEN CONCAT(
        DATE(" . db_prefix() . "leads.dateassigned),
        ' ',
        COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00')
        )

        ELSE " . db_prefix() . "leads.dateassigned
        END,

        FROM_UNIXTIME(
        (
        SELECT MIN(call_start + 19800)
        FROM " . db_prefix() . "calls_activity_logs c
        WHERE c.contact = " . db_prefix() . "leads.phonenumber
        AND (c.call_start + 19800) > UNIX_TIMESTAMP(" . db_prefix() . "leads.dateassigned)
        AND c.staffid = " . db_prefix() . "leads.assigned
        )
        )

        )/60
        ) {$condition} {$minutes}";
        }
    }
    


    // if (!empty($params['up_to_date'])) {
    //     $up_to_date = $params['up_to_date'];
    //     $up_from_date   = $params['up_from_date'];

    //     $up_from_date = $CI->db->escape_str($up_from_date); // Start date
    //     $up_to_date = $CI->db->escape_str($up_to_date);     // End date


    //     $where_c = "";
    //     $join_type = "";
    //     if ($params['update_count_min']  && $params['update_count_min'] != '') {

    //         $min = isset($params['update_count_min']) ? $params['update_count_min'] : 0;
    //         $max = isset($params['update_count_max']) ? $params['update_count_max'] : 0;
    //         $where_c = " AND ifnull(calls.update_count,0) between {$min} AND {$max} ";


    //         if ($min == 0) {
    //             $join_type = "RIGHT";
    //         }
    //     }

    //     if (!empty($params['assigned'])) {
    //         $where_c .= " AND calls.staffid IN (" . implode(',', $params['assigned']) . ") ";
    //     }

    //     $where_c .= " AND calls.staffid = leads.assigned";

    //     // SQL Queries
    //     $sql_p1 = " SELECT id 
    // FROM  ( SELECT leads.id FROM {$tblleads} leads  {$join_type} JOIN " . db_prefix() . "calls_activity_logs calls ON (calls.contact IN (leads.phonenumber)  AND DATE(calls.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}'  {$where_c}) WHERE 1=1
    // {$where_c} ";

    //     $sql_p1 .= " UNION ALL ";

    //     $sql_p1 .= "SELECT leads.id FROM {$tblleads} leads  {$join_type} JOIN " . db_prefix() . "calls_activity_logs calls ON ( calls.contact IN (leads.alternative_phonenumber) AND DATE(calls.adjusted_call_start) BETWEEN '{$up_from_date}' AND '{$up_to_date}'  {$where_c}) WHERE 1=1
    //  AND '{$up_to_date}' {$where_c} ";

    //     $sql_p1 .= " ) AS combined_result ";

    //     $conditions[] = "  {$tblleads}.id IN ($sql_p1) ";
    // }

    // Apply conditions to WHERE clause
    if (!empty($conditions)) {
        $sql .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
    }

// if(is_admin())
// {
    $sql .= 'GROUP BY ' . $tblleads . '.id '.$having_query.' ';
// } else{
//     // GROUP BY and ORDER BY
//     $sql .= 'GROUP BY ' . $tblleads . '.status ';
    
// }
    
    $sql .= 'ORDER BY ' . db_prefix() . 'leads_status.statusorder';
    
    // if(is_admin())
    // {
    $oldSql = " ( ".$sql." ) UNION ALL ( ".str_replace('phonenumber','alternative_phonenumber',$sql)." ) " ;
    $sql ="SELECT 
    IFNULL(tblleads_status.id, 'unknown') AS status_id,
    COUNT(*) AS total FROM ( 
     SELECT 
        combined.id,
        combined.status,
        SUM(combined.call_count) AS total_calls
    FROM (
    $oldSql
    ) AS combined

    GROUP BY combined.id
    ) AS lead_data

LEFT JOIN tblleads_status 
    ON lead_data.status = tblleads_status.id

GROUP BY lead_data.status
";
    // }


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

function get_leads_summary_filter_neww_test($params)
{
    $params['update_count_min'] = (isset($params['update_count_min']) && is_numeric($params['update_count_min']) && $params['update_count_min'] !== 'NaN')
        ? $params['update_count_min'] : '';
    $params['update_count_max'] = (isset($params['update_count_max']) && is_numeric($params['update_count_max']) && $params['update_count_max'] !== 'NaN')
        ? $params['update_count_max'] : '';

    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }

    $statuses     = $CI->leads_model->get_status();
    $sub_statuses = $CI->db->query('SELECT id, name FROM tblsub_lead_status')->result_array();

    $tblleads              = db_prefix() . 'leads';
    $has_permission_view   = has_permission('leads', '', 'view');
    $whereNoViewPermission = '(' . $tblleads . '.addedfrom = ' . get_staff_user_id() . ' OR ' . $tblleads . '.assigned=' . get_staff_user_id() . ' OR ' . $tblleads . '.is_public = 1)';

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

    // Base inner query: returns id, status, sub_status, call_count per lead
    $sql  = 'SELECT  
        ' . $tblleads . '.id,
        ' . $tblleads . '.status,
        ' . $tblleads . '.sub_status,
        IFNULL(COUNT(' . db_prefix() . 'calls_activity_logs.id), 0) AS call_count ';
    $sql .= 'FROM ' . $tblleads . ' ';
    $sql .= 'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . $tblleads . '.status = ' . db_prefix() . 'leads_status.id ';

    if (!empty($params['up_to_date']) || (isset($params['update_count_min']) && $params['update_count_min'] != '' && $params['update_count_max'] != 0) ) {
        $sql .= ' JOIN ' . db_prefix() . 'calls_activity_logs ON  ' . db_prefix() . 'calls_activity_logs.contact = ' . $tblleads . '.phonenumber AND ' . db_prefix() . 'calls_activity_logs.staffid = ' . $tblleads . '.assigned ';
    } else {
        $sql .= 'LEFT JOIN ' . db_prefix() . 'calls_activity_logs ON  ' . db_prefix() . 'calls_activity_logs.contact = ' . $tblleads . '.phonenumber AND ' . db_prefix() . 'calls_activity_logs.staffid = ' . $tblleads . '.assigned ';
    }

    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score']) || !empty($params['google_source'])) {
        $sql .= 'JOIN ' . db_prefix() . 'customfieldsvalues ON ' . $tblleads . '.id = ' . db_prefix() . 'customfieldsvalues.relid ';
    }
    if (!empty($params['followup_to_date'])) {
        $sql .= 'JOIN ' . db_prefix() . 'reminders ON ' . db_prefix() . 'reminders.rel_id = ' . $tblleads . '.id ';
    }
    if (!empty($params['location']) || !empty($params['department'])) {
        $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $tblleads . '.assigned ';
    } else if ((isset($params['time_condition']) && $params['time_condition'] != '') && (isset($params['time_minutes']) && $params['time_minutes'] >= 0)) {
        $sql .= 'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $tblleads . '.assigned ';
    }
    if ((isset($params['time_condition']) && $params['time_condition'] != '') && (isset($params['time_minutes']) && $params['time_minutes'] >= 0)) {
        $sql .= ' JOIN  ' . db_prefix() . 'staff_department on ' . db_prefix() . 'staff_department.id = ' . db_prefix() . 'staff.department ';
    }

    // WHERE
    $conditions = ['junk = 0', 'lost = 0'];
    if (!$has_permission_view)               $conditions[] = $whereNoViewPermission;
    if (!empty($params['status']))           $conditions[] = db_prefix() . 'leads_status.id IN (' . implode(',', $params['status']) . ')';
    if (!empty($params['assigned']))         $conditions[] = 'assigned IN (' . implode(',', $params['assigned']) . ')';
    elseif ($role == 3)                      $conditions[] = substr($tids, 4);
    if (!empty($params['source']))           $conditions[] = 'source IN (' . implode(',', $CI->db->escape_str($params['source'])) . ')';

    if (!empty($params['neet_score'])) {
        $neet_range = explode('-', $params['neet_score']);
        $conditions[] = db_prefix() . 'customfieldsvalues.fieldid = 8 AND ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' .
            $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' .
            db_prefix() . 'customfieldsvalues.value != ""';
    }
    if (!empty($params['lead_type']))        $conditions[] = 'type IN (' . implode(',', $CI->db->escape_str($params['lead_type'])) . ')';
    // if (!empty($params['sub_status']))       $conditions[] = 'sub_status IN (' . implode(',', $CI->db->escape_str($params['sub_status'])) . ')';
    
if (isset($params['sub_status'])) {

    $sub_status = (array) $params['sub_status'];

    // Only one item and it's blank
    if (count($sub_status) === 1 && $sub_status[0] === '') {

        $conditions[] = "( sub_status IS NULL OR sub_status='' )";

        // OR use NULL if your DB stores null:
        // $conditions[] = "sub_status IS NULL";

    } else {

        $sub_status = array_filter($sub_status, function ($v) {
            return $v !== '';
        });

        if (!empty($sub_status)) {
            $escaped = array_map(
                [$CI->db, 'escape'],
                $sub_status
            );

            $conditions[] = "sub_status IN (" . implode(',', $escaped) . ")";
        }
    }
}
    if (!empty($params['from_date']) && !empty($params['to_date'])) $conditions[] = 'DATE(' . $tblleads . '.dateadded) BETWEEN "' . $CI->db->escape_str($params['from_date']) . '" AND "' . $CI->db->escape_str($params['to_date']) . '"';
    if (!empty($params['followup_to_date'])) $conditions[] = 'DATE(' . db_prefix() . 'reminders.date) BETWEEN "' . $CI->db->escape_str($params['followup_from_date']) . '" AND "' . $CI->db->escape_str($params['followup_to_date']) . '"';
    if (!empty($params['assign_from_date'])) $conditions[] = 'DATE(' . $tblleads . '.dateassigned) BETWEEN "' . $CI->db->escape_str($params['assign_from_date']) . '" AND "' . $CI->db->escape_str($params['assign_to_date']) . '"';
    if (!empty($params['last_contact_date']))$conditions[] = 'DATE(' . $tblleads . '.lastconnect_date) <= "' . $CI->db->escape_str($params['last_contact_date']) . '"';
    if (!empty($params['last_update_date'])) $conditions[] = 'DATE(' . $tblleads . '.lastupdate_date) <= "' . $CI->db->escape_str($params['last_update_date']) . '"';

    $having_query = "";
    if (isset($params['update_count_min']) && $params['update_count_min'] != '') {
        $having_query = ' HAVING IFNULL(call_count, 0)  BETWEEN "' . $CI->db->escape_str($params['update_count_min']) . '" AND "' . $CI->db->escape_str($params['update_count_max']) . '" ';
    }

    if (!empty($params["utm_status"]) && $params["utm_status"] == 1) $conditions[] = ' ' . $tblleads . '.utm_campaign_name != " " ';
    if (!empty($params['utm_campaign_name']))$conditions[] = " " . $tblleads . ".utm_campaign_name IN ('" . implode("','", $CI->db->escape_str($params['utm_campaign_name'])) . "')";
    if (!empty($params['utm_ads_set_name'])) $conditions[] = " " . $tblleads . ".utm_ads_set_name IN ('" . implode("','", $CI->db->escape_str($params['utm_ads_set_name'])) . "')";
    if (!empty($params['utm_ads_name']))     $conditions[] = " " . $tblleads . ".utm_ads_name IN ('" . implode("','", $CI->db->escape_str($params['utm_ads_name'])) . "')";
    if (!empty($params['utm_term']))         $conditions[] = " " . $tblleads . ".utm_term IN ('" . implode("','", $CI->db->escape_str($params['utm_term'])) . "')";
    if (!empty($params['utm_form_name']))    $conditions[] = " " . $tblleads . ".utm_form_name IN ('" . implode("','", $CI->db->escape_str($params['utm_form_name'])) . "')";
    if (!empty($params['department']))       $conditions[] = " " . db_prefix() . "staff.department IN ('" . implode("','", $CI->db->escape_str($params['department'])) . "')";
    if (!empty($params['location']))         $conditions[] = " " . db_prefix() . "staff.office_location IN ('" . implode("','", $CI->db->escape_str($params['location'])) . "')";

    if (!empty($params['view_form'])) {
        $websites = $params['view_form'];
        $escaped_websites = array_map(function ($w) { return "'" . trim($w) . "'"; }, $websites);
        $conditions[] = " " . $tblleads . ".website IN (" . implode(',', $escaped_websites) . ")";
    }
    if (!empty($params['reference_name'])) {
        $reference_name = $params['reference_name'];
        if (!is_array($reference_name)) $reference_name = explode(',', $reference_name);
        $escaped_reference_name = array_map(function ($w) use ($CI) {
            return "'" . $CI->db->escape_str(trim($w)) . "'";
        }, $reference_name);
        $conditions[] = $tblleads . ".reference_name IN (" . implode(',', $escaped_reference_name) . ")";
    }

    if (!empty($params['up_to_date'])) {
        $up_from_date = $CI->db->escape_str($params['up_from_date']);
        $up_to_date   = $CI->db->escape_str($params['up_to_date']);
        $conditions[] = " " . db_prefix() . "calls_activity_logs.adjusted_call_start BETWEEN '{$up_from_date}' AND '{$up_to_date}' ";
    }

    if ((isset($params['time_condition']) && $params['time_condition'] != '') && (isset($params['time_minutes']) && $params['time_minutes'] >= 0)) {
        $condition = in_array($params['time_condition'], ['>', '<', '=', '>=', '<=']) ? $params['time_condition'] : '>';
        $minutes   = (int)$params['time_minutes'];
        $conditions[] = " (
        TIMESTAMPDIFF(SECOND,
        CASE 
        WHEN DAYNAME(" . db_prefix() . "leads.dateassigned) = 'Sunday'
        THEN CONCAT(DATE_ADD(DATE(" . db_prefix() . "leads.dateassigned), INTERVAL 1 DAY),' ',COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00'))
        WHEN TIME(" . db_prefix() . "leads.dateassigned) > COALESCE(" . db_prefix() . "staff_department.office_end_time,'20:00:00')
        THEN CONCAT(DATE_ADD(DATE(" . db_prefix() . "leads.dateassigned), INTERVAL 1 DAY),' ',COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00'))
        WHEN TIME(" . db_prefix() . "leads.dateassigned) < COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00')
        THEN CONCAT(DATE(" . db_prefix() . "leads.dateassigned),' ',COALESCE(" . db_prefix() . "staff_department.office_start_time,'10:00:00'))
        ELSE " . db_prefix() . "leads.dateassigned
        END,
        FROM_UNIXTIME((SELECT MIN(call_start + 19800) FROM " . db_prefix() . "calls_activity_logs c
        WHERE c.contact = " . db_prefix() . "leads.phonenumber
        AND (c.call_start + 19800) > UNIX_TIMESTAMP(" . db_prefix() . "leads.dateassigned)
        AND c.staffid = " . db_prefix() . "leads.assigned))
        )/60
        ) {$condition} {$minutes}";
    }

    if (!empty($conditions)) {
        $sql .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
    }

    $sql .= 'GROUP BY ' . $tblleads . '.id  ';

    // UNION phone + alternative_phonenumber
    $unionSql = " ( " . $sql . " ) UNION ALL ( " . str_replace('phonenumber', 'alternative_phonenumber', $sql) . " ) ";

    // Dedupe by lead id in outer query (one row per lead)
    $finalSql = "
        SELECT 
            combined.id,
            combined.status,
            combined.sub_status,
            sum(combined.call_count) as call_count
        FROM (
            $unionSql
        ) AS combined
        GROUP BY combined.id " . $having_query . " 
    ";
    
    //   if (is_admin()) {
    //     echo $finalSql;
    //     die;
    // }
$rows = $CI->db->query($finalSql)->result();


// Build lookup map from tblsub_lead_status (by id AND by name — safety net)
$subById   = [];
$subByName = [];
foreach ($sub_statuses as $ss) {
    $subById[(string)$ss['id']]             = $ss;
    $subByName[strtolower(trim($ss['name']))] = $ss;
}

$statusTotals    = [];
$subStatusTotals = [];

foreach ($rows as $r) {
    // ---- Status ----
    $statusKey = (!empty($r->status) && $r->status != 0) ? (string)$r->status : 'unknown';
    $statusTotals[$statusKey] = ($statusTotals[$statusKey] ?? 0) + 1;

    // ---- Sub-status ----
    $rawSub = trim((string)$r->sub_status);

    if ($rawSub === '' || $rawSub === '0') {
        $subKey = 'unknown';
    } elseif (isset($subById[$rawSub])) {
        // Matched by id
        $subKey = (string)$subById[$rawSub]['id'];
    } elseif (isset($subByName[strtolower($rawSub)])) {
        // Matched by name (fallback if sub_status column stores name)
        $subKey = (string)$subByName[strtolower($rawSub)]['id'];
    } else {
        $subKey = 'unknown';
    }

    $subStatusTotals[$subKey] = ($subStatusTotals[$subKey] ?? 0) + 1;
}

// ----- DEBUG (remove after verifying) -----
// echo "<pre>Raw rows:\n"; print_r($rows);
// echo "\nsub_statuses table:\n"; print_r($sub_statuses);
// echo "\nsubStatusTotals:\n"; print_r($subStatusTotals);
// die;

// Build status output
$statuses[] = ["id" => "unknown", "name" => "Unknown Status"];
$totalLeads = 0;
foreach ($statuses as $key => $status) {
    $statuses[$key]['total'] = $statusTotals[(string)$status["id"]] ?? 0;
    $totalLeads += $statuses[$key]['total'];
}
$statuses[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads];

// Build sub-status output
$sub_statuses[] = ["id" => "unknown", "name" => "Unknown Sub Status"];
$totalSubLeads  = 0;
foreach ($sub_statuses as $k => $ss) {
    $sub_statuses[$k]['total'] = $subStatusTotals[(string)$ss['id']] ?? 0;
    $totalSubLeads += $sub_statuses[$k]['total'];
}
$sub_statuses[] = ["name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalSubLeads];

return [
    'statuses'     => $statuses,
    'sub_statuses' => $sub_statuses,
];
}

function get_university_list($lead_type)
{
    $CI = &get_instance();
    return $CI->s_db->query("SELECT 
    c.id AS country_id,
    co.name,
    c.country_name,
    u.university_name,
    u.id AS university_id,
    u.fees_mandatory,
    u.exam
FROM course co
LEFT JOIN countries c 
    ON co.id = c.segment_id
LEFT JOIN universities u 
    ON u.country_id = c.id
WHERE (
        co.name = '$lead_type'
        AND c.country_name != ''
        AND u.university_name != ''
        AND u.show_crm = 1
      )
  ")->result_array();
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
            str_replace(",","",$amount_details[0]["total_amount"]) ?? "",
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
        // if ($lead->welcome_message_status == 1) {
        //     return true;
        // }
  
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


// function whatsapp_message_send_visitor_logs($visitorId)
// {
//     try {
//         $CI = &get_instance();
 
//         // Validate Inputs
//         if (empty($visitorId) || !is_numeric($visitorId)) {
//             log_message('error', 'Invalid Visitor ID');
//             return false;
//         }
 
//         // WhatsApp Configuration
//         $productToken      = WHATSAAP_PRODUCT_KEY;
//         $fromNumber        = WHATSAAP_FROM_NUMBER;
//         $templateNamespace = WHATSAAP_NAMESPACE;
 
//         if (empty($productToken) || empty($fromNumber) || empty($templateNamespace)) {
//             log_message('error', 'WhatsApp configuration missing');
//             return false;
//         }
 
//         // Fetch Visitor Details
//         // NOTE: no trailing comma after the last selected column.
//         $visitorDetails = $CI->db
//             ->select("
//                 visitor_request.id,
//                 visitor_request.lead_id,
//                 visitor_request.address,
//                 visitor_request.date_of_visit,
//                 tblleads.name AS lead_name,
//                 tblleads.phonenumber,
//                 tblvisitor_type.name AS visitor_type_name,
//                 CONCAT(tblstaff.firstname,' ',tblstaff.lastname,' - (',tblstaff.phonenumber,')') AS staff_name,
//                 CONCAT(tblstaff_assigned.firstname,' ',tblstaff_assigned.lastname,' - (',tblstaff_assigned.phonenumber,')') AS staff_name_assigned
//             ", false)
//             ->from(db_prefix() . 'visitor_request AS visitor_request')
//             ->join(
//                 db_prefix() . 'leads AS tblleads',
//                 'tblleads.id = visitor_request.lead_id',
//                 'left'
//             )
//             ->join(
//                 db_prefix() . 'visitor_type AS tblvisitor_type',
//                 'tblvisitor_type.id = visitor_request.visitor_type',
//                 'left'
//             )
//             ->join(
//                 db_prefix() . 'staff AS tblstaff',
//                 'tblstaff.staffid = visitor_request.created_by',
//                 'left'
//             )
//             ->join(
//                 db_prefix() . 'staff AS tblstaff_assigned',
//                 'tblstaff_assigned.staffid = visitor_request.assigned',
//                 'left'
//             )
//             ->where('visitor_request.id', $visitorId)
//             ->where_in('visitor_request.status', [1, 3])
//             ->where_in('visitor_request.visitor_type', [2, 3])
//             ->limit(1)
//             ->get()
//             ->row();
 
//         if (!$visitorDetails) {
//             log_message('error', 'Visitor record not found');
//             return false;
//         }
 
//         $whatsapp_template_id = 15;
 
//         if (empty($whatsapp_template_id) || !is_numeric($whatsapp_template_id)) {
//             log_message('error', 'Invalid WhatsApp Template ID');
//             return false;
//         }
 
//         // Validate Lead Mobile Number
//         if (empty($visitorDetails->phonenumber)) {
//             log_message('error', 'Lead phone number not found');
//             return false;
//         }
 
//         $toNumber = '91' . getLast10Digits($visitorDetails->phonenumber);
// //  $toNumber = "919871159668";
//         // Fetch WhatsApp Template
//         $whatsapp = $CI->db
//             ->where('id', $whatsapp_template_id)
//             ->where('status', 1)
//             ->get(db_prefix() . 'whatsapptemplates')
//             ->row();
 
//         if (!$whatsapp) {
//             log_message('error', 'WhatsApp template not found');
//             return false;
//         }
 
//         $templateName = $whatsapp->template_name;
//         $languageCode = !empty($whatsapp->languageCode)
//             ? $whatsapp->languageCode
//             : 'en';
 
//         // Protect any commas inside the address so explode(',') below
//         // does not split a single address value into multiple parameters.
//         $address = str_replace(',', '#COMMA#', $visitorDetails->address ?? '');
 
//         // Replace Template Variables
//         $variables = str_replace(
//             [
//                 '{lead_name}',
//                 '{address}',
//                 '{visit_date}',
//                 '{visit_type}',
//                 '{counsellor_name}',
//                 '{counsellor_attend_name}',
//             ],
//             [
//                 $visitorDetails->lead_name ?? '',
//                 $address,
//                 !empty($visitorDetails->date_of_visit)
//                     ? date('d-m-Y', strtotime($visitorDetails->date_of_visit))
//                     : '',
//                 $visitorDetails->visitor_type_name ?? '',
//                 $visitorDetails->staff_name ?? '',
//                 $visitorDetails->staff_name_assigned ?? '',
//             ],
//             $whatsapp->variables_name ?? ''
//         );
 
//         // Prepare Parameters
//         $parameters = [];
//         if (!empty($variables)) {
//             $variablesArray = explode(',', $variables);
//             foreach ($variablesArray as $value) {
//                 $value = trim($value);
//                 if ($value === '') {
//                     continue;
//                 }
//                 $parameters[] = [
//                     'type' => 'text',
//                     'text' => str_replace('#COMMA#', ',', $value),
//                 ];
//             }
//         }
 
//         // Construct Payload
//         $payload = [
//             'messages' => [
//                 'authentication' => [
//                     'producttoken' => $productToken,
//                 ],
//                 'msg' => [
//                     [
//                         'from' => $fromNumber,
//                         'to' => [
//                             ['number' => $toNumber],
//                         ],
//                         'body' => [
//                             'type'    => 'auto',
//                             'content' => $templateName,
//                         ],
//                         'allowedChannels' => ['WhatsApp'],
//                         'richContent' => [
//                             'conversation' => [
//                                 [
//                                     'template' => [
//                                         'whatsapp' => [
//                                             'namespace'    => $templateNamespace,
//                                             'element_name' => $templateName,
//                                             'language' => [
//                                                 'policy' => 'deterministic',
//                                                 'code'   => $languageCode,
//                                             ],
//                                             'components' => [],
//                                         ],
//                                     ],
//                                 ],
//                             ],
//                         ],
//                     ],
//                 ],
//             ],
//         ];
 
//         // Add Body Parameters
//         if (!empty($parameters)) {
//             $payload['messages']['msg'][0]['richContent']['conversation'][0]['template']['whatsapp']['components'][] = [
//                 'type'       => 'body',
//                 'parameters' => $parameters,
//             ];
//         }
 
//         // Initialize cURL
//         $curl = curl_init();
//         curl_setopt_array($curl, [
//             CURLOPT_URL            => 'https://gw.messaging.cm.com/v1.0/message',
//             CURLOPT_RETURNTRANSFER => true,
//             CURLOPT_ENCODING       => '',
//             CURLOPT_MAXREDIRS      => 10,
//             CURLOPT_TIMEOUT        => 10,
//             CURLOPT_FOLLOWLOCATION => true,
//             CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
//             CURLOPT_CUSTOMREQUEST  => 'POST',
//             CURLOPT_POSTFIELDS     => json_encode($payload),
//             CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
//         ]);
 
//         // Execute cURL request
//         $response  = curl_exec($curl);
//         $curlError = curl_error($curl);
//         curl_close($curl);
 

//         // Handle cURL errors
//         if ($curlError) {
//             log_message('error', 'WhatsApp API cURL error: ' . $curlError);
//             return false;
//             // return json_encode(['error' => 'Failed to send message. API request error.']);
//         }
 
//         // Decode API response and check for errors
//         $responseArray = json_decode($response, true);
        

//         if (!$responseArray || isset($responseArray['error'])) {
//             log_message('error', 'WhatsApp API response error: ' . $response);
//             return false;
//             return json_encode(['error' => 'WhatsApp API error', 'details' => $responseArray]);
//         }
 
//         // Log the sent message
//         $insert_data = [
//             'type'        => 'whatsapp',
//             'template_id' => $whatsapp_template_id,
//             'lead_id'     => $visitorDetails->lead_id ?? '',
//             'visitor_id'  => $visitorId ?? '',
//             'datetime'    => date('Y-m-d H:i:s'),
//         ];
//         $CI->db->insert(db_prefix() . 'whatsapp_email_logs', $insert_data);
        
//         $message  = sprintf(
//     'WhatsApp notification sent to %s (%s). Visit Type: %s, Visit Date: %s, Address: %s, Created By: %s, Assigned Counsellor: %s.',
//     $visitorDetails->lead_name ?? 'Visitor',
//     $visitorDetails->phonenumber ?? '',
//     $visitorDetails->visitor_type_name ?? 'N/A',
//     !empty($visitorDetails->date_of_visit)
//         ? date('d-m-Y', strtotime($visitorDetails->date_of_visit))
//         : 'N/A',
//     $visitorDetails->address ?? 'N/A',
//     $visitorDetails->staff_name ?? 'N/A',
//     $visitorDetails->staff_name_assigned ?? 'N/A'
// );
//         $CI->db->insert(db_prefix() . 'visitor_activity_log', array("description" => $message, "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "lead_id" => $visitorDetails->lead_id, "visit_id" => $visitorId));
//  return true;
//         return json_encode(['status'=>true,'success' => 'Message sent successfully.', 'response' => $responseArray]);
//     } catch (Exception $e) {
//         log_message('error', 'WhatsApp Send Exception: ' . $e->getMessage());
//         return false;
//     }
// }


function whatsapp_message_send_visitor_logs()
{
    
    $CI = &get_instance();
 
    // effective time: updated_date if it has a real value, otherwise created_date
 $effectiveTime = "
COALESCE(
    NULLIF(visitor_request.updated_at, '0000-00-00 00:00:00'),
    visitor_request.created_at
)";

$currentDateTime = date('Y-m-d H:i:s');
 
    $dueVisitors = $CI->db
        ->select('visitor_request.id', false)
        ->from(db_prefix() . 'visitor_request AS visitor_request')
        ->where_in('visitor_request.status', [1, 3])
        ->where_in('visitor_request.visitor_type', [3])
       ->where('(visitor_request.whatsapp_status IS NULL OR visitor_request.whatsapp_status = 0)', NULL, FALSE)
        ->where("{$effectiveTime} <= DATE_SUB('{$currentDateTime}', INTERVAL 1 HOUR)", null, false)
        ->where('visitor_request.date_of_visit >=', $currentDateTime)
        ->order_by($effectiveTime, 'ASC', false)
        ->limit(10)
        ->get()
        ->result();
      
       
 
    if (empty($dueVisitors)) {
        log_message('debug', 'WhatsApp batch: no due visitor records.');
        return ['processed' => 0, 'sent' => 0, 'failed' => 0];
    }
    
   
 
    $sent   = 0;
    $failed = 0;
 
    foreach ($dueVisitors as $visitor) {
        $ok = whatsapp_send_visitor_single((int) $visitor->id);
        if ($ok) {
            $sent++;
        } else {
            $failed++;
        }
    }
 
    return [
        'processed' => count($dueVisitors),
        'sent'      => $sent,
        'failed'    => $failed,
    ];
}
 
/**
 * Sends the WhatsApp template for a single visitor record.
 * On success, sets visitor_request.whatsapp_status = 4 and writes the logs.
 *
 * @return bool
 */
function whatsapp_send_visitor_single($visitorId)
{
    try {
        $CI = &get_instance();
 
        // Validate Inputs
        if (empty($visitorId) || !is_numeric($visitorId)) {
            log_message('error', 'Invalid Visitor ID');
            return false;
        }
 
        // WhatsApp Configuration
        $productToken      = WHATSAAP_PRODUCT_KEY;
        $fromNumber        = WHATSAAP_FROM_NUMBER;
        $templateNamespace = WHATSAAP_NAMESPACE;
 
        if (empty($productToken) || empty($fromNumber) || empty($templateNamespace)) {
            log_message('error', 'WhatsApp configuration missing');
            return false;
        }
        
         $CI->db
            ->where('id', $visitorId)
            ->update(db_prefix() . 'visitor_request', [
                'whatsapp_status' => 5
            ]);
 
        // Fetch Visitor Details
        $visitorDetails = $CI->db
            ->select("
                visitor_request.id,
                visitor_request.lead_id,
                visitor_request.address,
                visitor_request.date_of_visit,
                tblleads.name AS lead_name,
                tblleads.phonenumber,
                tblvisitor_type.name AS visitor_type_name,
                CONCAT(tblstaff.firstname,' ',tblstaff.lastname,' - (',tblstaff.phonenumber,')') AS staff_name,
                CONCAT(tblstaff_assigned.firstname,' ',tblstaff_assigned.lastname,' - (',tblstaff_assigned.phonenumber,')') AS staff_name_assigned
            ", false)
            ->from(db_prefix() . 'visitor_request AS visitor_request')
            ->join(db_prefix() . 'leads AS tblleads', 'tblleads.id = visitor_request.lead_id', 'left')
            ->join(db_prefix() . 'visitor_type AS tblvisitor_type', 'tblvisitor_type.id = visitor_request.visitor_type', 'left')
            ->join(db_prefix() . 'staff AS tblstaff', 'tblstaff.staffid = tblleads.assigned', 'left')
            ->join(db_prefix() . 'staff AS tblstaff_assigned', 'tblstaff_assigned.staffid = visitor_request.assigned', 'left')
            ->where('visitor_request.id', $visitorId)
            ->where_in('visitor_request.status', [1, 3])
            ->where_in('visitor_request.visitor_type', [3])
            ->limit(1)
            ->get()
            ->row();
            
            // print_r($visitorDetails);
            // die;
 
        if (!$visitorDetails) {
            log_message('error', 'Visitor record not found: ' . $visitorId);
            return false;
        }
 
        $whatsapp_template_id = 15;
 
        if (empty($whatsapp_template_id) || !is_numeric($whatsapp_template_id)) {
            log_message('error', 'Invalid WhatsApp Template ID');
            return false;
        }
 
        // Validate Lead Mobile Number
        if (empty($visitorDetails->phonenumber)) {
            log_message('error', 'Lead phone number not found for visitor ' . $visitorId);
            return false;
        }
 
        $toNumber = '91' . getLast10Digits($visitorDetails->phonenumber);
 
        // Fetch WhatsApp Template
        $whatsapp = $CI->db
            ->where('id', $whatsapp_template_id)
            ->where('status', 1)
            ->get(db_prefix() . 'whatsapptemplates')
            ->row();
 
        if (!$whatsapp) {
            log_message('error', 'WhatsApp template not found');
            return false;
        }
 
        $templateName = $whatsapp->template_name;
        $languageCode = !empty($whatsapp->languageCode) ? $whatsapp->languageCode : 'en';
 
        // Protect commas inside the address so explode(',') doesn't split it.
        $address = str_replace(',', '#COMMA#', $visitorDetails->address ?? '');
 
        // Replace Template Variables
        $variables = str_replace(
            [
                '{lead_name}',
                '{address}',
                '{visit_date}',
                '{visit_type}',
                '{counsellor_name}',
                '{counsellor_attend_name}',
            ],
            [
                $visitorDetails->lead_name ?? '',
                $address,
                !empty($visitorDetails->date_of_visit) ? date('d-m-Y', strtotime($visitorDetails->date_of_visit)) : '',
                $visitorDetails->visitor_type_name ?? '',
                $visitorDetails->staff_name ?? '',
                $visitorDetails->staff_name_assigned ?? '',
            ],
            $whatsapp->variables_name ?? ''
        );
 
        // Prepare Parameters
        $parameters = [];
        if (!empty($variables)) {
            $variablesArray = explode(',', $variables);
            foreach ($variablesArray as $value) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
                $parameters[] = [
                    'type' => 'text',
                    'text' => str_replace('#COMMA#', ',', $value),
                ];
            }
        }
 
        // Construct Payload
        $payload = [
            'messages' => [
                'authentication' => [
                    'producttoken' => $productToken,
                ],
                'msg' => [
                    [
                        'from' => $fromNumber,
                        'to'   => [
                            ['number' => $toNumber],
                        ],
                        'body' => [
                            'type'    => 'auto',
                            'content' => $templateName,
                        ],
                        'allowedChannels' => ['WhatsApp'],
                        'richContent'     => [
                            'conversation' => [
                                [
                                    'template' => [
                                        'whatsapp' => [
                                            'namespace'    => $templateNamespace,
                                            'element_name' => $templateName,
                                            'language'     => [
                                                'policy' => 'deterministic',
                                                'code'   => $languageCode,
                                            ],
                                            'components' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
 
        // Add Body Parameters
        if (!empty($parameters)) {
            $payload['messages']['msg'][0]['richContent']['conversation'][0]['template']['whatsapp']['components'][] = [
                'type'       => 'body',
                'parameters' => $parameters,
            ];
        }
 
        // Initialize cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://gw.messaging.cm.com/v1.0/message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
 
        $response  = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);
 
        if ($curlError) {
            log_message('error', 'WhatsApp API cURL error: ' . $curlError);
            return false;
        }
 
        $responseArray = json_decode($response, true);
 
        if (!$responseArray || isset($responseArray['error'])) {
            log_message('error', 'WhatsApp API response error: ' . $response);
            return false;
        }
 
        // SUCCESS — mark this visitor as sent (whatsapp_status = 4)
        $CI->db
            ->where('id', $visitorId)
            ->update(db_prefix() . 'visitor_request', [
                'whatsapp_status' => 4,
                'whatsapp_notify' =>date('Y-m-d H:i:s')
            ]);
 
        // Log the sent message
        $CI->db->insert(db_prefix() . 'whatsapp_email_logs', [
            'type'        => 'whatsapp',
            'template_id' => $whatsapp_template_id,
            'lead_id'     => $visitorDetails->lead_id ?? '',
            'visitor_id'  => $visitorId ?? '',
            'datetime'    => date('Y-m-d H:i:s'),
        ]);
 
        $message = sprintf(
            'WhatsApp notification sent to %s (%s). Visit Type: %s, Visit Date: %s, Address: %s, Created By: %s, Assigned Counsellor: %s.',
            $visitorDetails->lead_name ?? 'Visitor',
            $visitorDetails->phonenumber ?? '',
            $visitorDetails->visitor_type_name ?? 'N/A',
            !empty($visitorDetails->date_of_visit) ? date('d-m-Y', strtotime($visitorDetails->date_of_visit)) : 'N/A',
            $visitorDetails->address ?? 'N/A',
            $visitorDetails->staff_name ?? 'N/A',
            $visitorDetails->staff_name_assigned ?? 'N/A'
        );
 
        $CI->db->insert(db_prefix() . 'visitor_activity_log', [
            'description' => $message,
            'date'        => date('Y-m-d H:i:s'),
            'staffid'     => get_staff_user_id(),
            'lead_id'     => $visitorDetails->lead_id,
            'visit_id'    => $visitorId,
        ]);
 
        return true;
    } catch (Exception $e) {
        log_message('error', 'WhatsApp Send Exception: ' . $e->getMessage());
        return false;
    }
}
 



function visitor_seminar_whatsapp_notification()
{
    try {

        $CI = &get_instance();

        $productToken      = WHATSAAP_PRODUCT_KEY;
        $fromNumber        = WHATSAAP_FROM_NUMBER;
        $templateNamespace = WHATSAAP_NAMESPACE;

        if (empty($productToken) || empty($fromNumber) || empty($templateNamespace)) {
            log_message('error', 'WhatsApp configuration missing');
            return false;
        }

        $visitorDetails = $CI->db
            ->select("
                visitor_request.id,
                visitor_request.lead_id,
                visitor_request.address,
                visitor_request.date_of_visit,
                visitor_request.whatsapp_notify,
                visitor_request.seminar_address,
                visitor_request.whatsapp_status,
                tblleads.name AS lead_name,
                tblleads.phonenumber,
                tblleads_type.name lead_type,
                tblvisitor_type.name AS visitor_type_name,
                visitor_request.image AS image,
                CONCAT(tblstaff.firstname,' ',tblstaff.lastname) AS staff_name,
                tblstaff.phonenumber AS staff_contact
            ", false)
            ->from(db_prefix() . 'visitor_request AS visitor_request')
            ->join(db_prefix() . 'leads AS tblleads', 'tblleads.id = visitor_request.lead_id', 'left')
            ->join(db_prefix() . 'leads_type', 'tblleads_type.id = tblleads.type', 'left')
            ->join(db_prefix() . 'visitor_type AS tblvisitor_type', 'tblvisitor_type.id = visitor_request.visitor_type', 'left')
            ->join(db_prefix() . 'staff AS tblstaff', 'tblstaff.staffid = tblleads.assigned', 'left')
            ->join(db_prefix() . 'staff AS tblstaff_assigned', 'tblstaff_assigned.staffid = visitor_request.assigned', 'left')
            ->where_in('visitor_request.status', [1, 3])
            ->where('visitor_request.visitor_type', 2)
            ->where('visitor_request.whatsapp_status', 3) 
            ->where('visitor_request.seminar_address !=' , '')
            ->where('visitor_request.image !=' , '')
            ->where('date(visitor_request.date_of_visit) >', date('Y-m-d'))
            ->where('visitor_request.whatsapp_notify IS NOT NULL', null, false)
            ->where('visitor_request.whatsapp_notify <=', date('Y-m-d H:i:s'))
            ->group_by("visitor_request.id")
            ->limit(20)
            ->get()
            ->result();


        if (empty($visitorDetails)) {
            log_message('error', 'No visitor records found');
            return false;
        }
        
    

        $whatsapp_template_id = 17;

        $whatsapp = $CI->db
            ->where('id', $whatsapp_template_id)
            ->where('status', 1)
            ->get(db_prefix() . 'whatsapptemplates')
            ->row();

        if (!$whatsapp) {
            log_message('error', 'WhatsApp template not found');
            return false;
        }

        foreach ($visitorDetails as $visitor) {

            // Default failed status
            $status = 2;

            if (empty($visitor->phonenumber)) {

                log_message(
                    'error',
                    'Phone number missing for Visitor ID: ' . $visitor->id
                );

                $CI->db->where('id', $visitor->id)
                    ->update(db_prefix() . 'visitor_request', [
                        'whatsapp_status' => 2
                    ]);

                continue;
            }

            $toNumber = '91' . getLast10Digits($visitor->phonenumber);
//  $toNumber = "919871159668";
            $address = str_replace(',', '#COMMA#', $visitor->address ?? '');
 $imageURL = $visitor->image;


            $variables = str_replace(
                [
                    '{lead_name}',
                    '{lead_type}',
                    '{address}',
                    '{visit_date}',
                    '{visit_time}',
                    '{counsellor_name}',
                    '{counsellor_contact}',
                ],
                [
                    $visitor->lead_name ?? '',
                    $visitor->lead_type ?? '',
                    $address,
                    !empty($visitor->date_of_visit)
                        ? date('d-m-Y', strtotime($visitor->date_of_visit))
                        : '',
                    !empty($visitor->date_of_visit)
                        ? date('h:i A', strtotime($visitor->date_of_visit))
                        : '',
                    $visitor->staff_name ?? '',
                    $visitor->staff_contact ?? '',
                ],
                $whatsapp->variables_name ?? ''
            );

            $parameters = [];

            if (!empty($variables)) {
                foreach (explode(',', $variables) as $value) {

                    $value = trim($value);

                    if ($value === '') {
                        continue;
                    }

                    $parameters[] = [
                        'type' => 'text',
                        'text' => str_replace('#COMMA#', ',', $value)
                    ];
                }
            }

            $payload = [
                'messages' => [
                    'authentication' => [
                        'producttoken' => $productToken
                    ],
                    'msg' => [[
                        'from' => $fromNumber,
                        'to' => [[
                            'number' => $toNumber
                        ]],
                        'body' => [
                            'type' => 'auto',
                            'content' => $whatsapp->template_name
                        ],
                        'allowedChannels' => ['WhatsApp'],
                        'richContent' => [
                            'conversation' => [[
                                'template' => [
                                    'whatsapp' => [
                                        'namespace' => $templateNamespace,
                                        'element_name' => $whatsapp->template_name,
                                        'language' => [
                                            'policy' => 'deterministic',
                                            'code' => !empty($whatsapp->languageCode)
                                                ? $whatsapp->languageCode
                                                : 'en'
                                        ],
                                        'components' => [[
                                            'type' => 'body',
                                            'parameters' => $parameters
                                        ]]
                                    ]
                                ]
                            ]]
                        ]
                    ]]
                ]
            ];
            
            
                // Add document attachment if URL exists
 if (!empty($imageURL)) {
     
     $mimeType = mime_content_type(FCPATH . $imageURL);
// or from extension:
$ext      = strtolower(pathinfo($imageURL, PATHINFO_EXTENSION));
$mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';


    $payload["messages"]["msg"][0]["richContent"]["conversation"][0]["template"]["whatsapp"]["components"][] = [
        "type" => "header",
        "parameters" => [
            [
                "type" => "image",
                "media" => [
                    "mediaName" => $imageName??'Seminar Image',                 // e.g. "seat-confirmed.jpg"
                    "mediaUri"  => base_url() . $imageURL,
                    "mimeType"  => $mimeType                   // "image/jpeg" or "image/png"
                ]
            ]
        ]
    ];
}


            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://gw.messaging.cm.com/v1.0/message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json'
                ]
            ]);

            $response = curl_exec($curl);
            $error    = curl_error($curl);
            // print_r($response);
            // die;

            curl_close($curl);

            if ($error) {

                log_message(
                    'error',
                    'Visitor ID ' . $visitor->id . ' cURL Error: ' . $error
                );

                $CI->db->where('id', $visitor->id)
                    ->update(db_prefix() . 'visitor_request', [
                        'whatsapp_status' => 2
                    ]);

                continue;
            }

            $responseArray = json_decode($response, true);

            if (
                isset($responseArray['error']) ||
                isset($responseArray['errors'])
            ) {

                log_message(
                    'error',
                    'Visitor ID ' . $visitor->id . ' API Error: ' . $response
                );

                $CI->db->where('id', $visitor->id)
                    ->update(db_prefix() . 'visitor_request', [
                        'whatsapp_status' => 2
                    ]);

                continue;
            }

            // SUCCESS
            $status = 1;

            $CI->db->insert(db_prefix() . 'whatsapp_email_logs', [
                'type'        => 'whatsapp',
                'template_id' => $whatsapp_template_id,
                'lead_id'     => $visitor->lead_id,
                'visitor_id'  => $visitor->id,
                'datetime'    => date('Y-m-d H:i:s'),
            ]);

            $CI->db->where('id', $visitor->id)
                ->update(db_prefix() . 'visitor_request', [
                    'whatsapp_status' => $status
                ]);
                
         $assignedCounsellor = trim(
    ($visitor->staff_name ?? '') .
    (!empty($visitor->staff_contact) ? ' (' . $visitor->staff_contact . ')' : '')
);

$message = sprintf(
    'WhatsApp notification sent to %s (%s). Lead Type: %s, Visit Date: %s, Address: %s, Assigned Counsellor: %s.',
    $visitor->lead_name ?? 'Visitor',
    $visitor->phonenumber ?? '',
    $visitor->lead_type ?? 'N/A',
    !empty($visitor->date_of_visit) ? date('d-m-Y', strtotime($visitor->date_of_visit)) : 'N/A',
    $visitor->address ?? 'N/A',
    $assignedCounsellor ?: 'N/A'
);
 
        $CI->db->insert(db_prefix() . 'visitor_activity_log', [
            'description' => $message,
            'date'        => date('Y-m-d H:i:s'),
            'staffid'     => get_staff_user_id(),
            'lead_id'     => $visitor->lead_id,
            'visit_id'    => $visitor->id,
        ]);

            log_message(
                'info',
                'WhatsApp sent successfully to Visitor ID: ' . $visitor->id
            );

            echo "WhatsApp sent to Visitor ID: {$visitor->id}\n";

            sleep(1);
        }

        return true;

    } catch (Exception $e) {

        log_message('error', 'WhatsApp Cron Exception: ' . $e->getMessage());

        return false;
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

function get_country_code()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $countryCode = $CI->db
            ->select("country_id,short_name,calling_code")
            ->from(db_prefix() . 'countries')
            ->get()
            ->result_array();

        return $countryCode; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching RelationShip: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function holiday_list()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $holidayList = ['2026-03-04', '2026-08-15', '2026-08-28', "2026-09-04", "2026-10-02", "2026-10-20", "2026-11-08", "2026-11-09", "2026-11-10", "2026-11-11", "2026-12-25"];

        return $holidayList; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching RelationShip: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}


function calculate_business_seconds($start, $end)
{
    
    if (!$start) return 0;

    $start = new DateTime($start);
    $end   = new DateTime($end);

    $totalSeconds = 0;

    $holidayDates = holiday_list();


    while ($start < $end) {

        $currentDate = $start->format('Y-m-d');

        // Skip Sunday
        if ($start->format('N') == 7) {
            $start->modify('+1 day')->setTime(11,0,0);
            continue;
        }

        // Skip Holiday
        if (in_array($currentDate, $holidayDates)) {
            $start->modify('+1 day')->setTime(11,0,0);
            continue;
        }

        $workStart = clone $start;
        $workStart->setTime(11,0,0);

        $workEnd = clone $start;
        $workEnd->setTime(20,0,0);

        if ($start < $workStart) {
            $start = clone $workStart;
        }

        if ($start > $workEnd) {
            $start->modify('+1 day')->setTime(11,0,0);
            continue;
        }

        $periodEnd = min($workEnd, $end);

        $totalSeconds += $periodEnd->getTimestamp() - $start->getTimestamp();

        $start->modify('+1 day')->setTime(11,0,0);
    }

    return $totalSeconds;
}




function send_whatsaap_notification_lead_transfer($contact_number,$whatsapp_template_id,$informationData)
{
    try {

        $CI = &get_instance();

        if(empty($contact_number) || empty($whatsapp_template_id)){
            throw new Exception("Contact number or template id missing.");
        }

        $informationData = !empty($informationData) ? json_decode($informationData,true) : [];

        $productToken      = WHATSAAP_PRODUCT_KEY;
        $fromNumber        = WHATSAAP_FROM_NUMBER;
        $templateNamespace = WHATSAAP_NAMESPACE;
        $toNumber          = "0091".getLast10Digits($contact_number);

        $whatsapp = $CI->db
        ->where('status',1)
        ->where('id',$whatsapp_template_id)
        ->get(db_prefix().'whatsapptemplates')
        ->row();

        if(!$whatsapp){
            throw new Exception("WhatsApp template not found.");
        }

        $templateName = $whatsapp->template_name;
        $languageCode = $whatsapp->languageCode ?? "en";

        $variables = str_replace(
            [
                "{time}",
                "{time_45}",
                "{lead_name}",
                "{phonenumber}",
                "{lead_source}",
                "{left_time}",
                "{counsellor}"
            ],
            [
                "01 Hour 30",
                "01 Hour 45",
                $informationData['name'] ?? 'unknown',
                $informationData['phonenumber'] ?? '',
                $informationData['source_name'] ?? '',
                "30",
                $informationData['assigned_name']
            ],
            $whatsapp->variables_name ?? ""
        );

        $parameters = [];

        if(!empty($variables)){
            $variables_array = array_map('trim', explode(",", $variables));

            foreach ($variables_array as $data) {

                if(empty($data)) continue;

                $parameters[] = [
                    "type"=>"text",
                    "text"=>$data
                ];
            }
        }

        $payload = [
            "messages"=>[
                "authentication"=>[
                    "producttoken"=>$productToken
                ],
                "msg"=>[
                    [
                        "from"=>$fromNumber,
                        "to"=>[
                            ["number"=>$toNumber]
                        ],
                        "body"=>[
                            "type"=>"auto",
                            "content"=>$templateName
                        ],
                        "allowedChannels"=>["WhatsApp"],
                        "richContent"=>[
                            "conversation"=>[
                                [
                                    "template"=>[
                                        "whatsapp"=>[
                                            "namespace"=>$templateNamespace,
                                            "element_name"=>$templateName,
                                            "language"=>[
                                                "policy"=>"deterministic",
                                                "code"=>$languageCode
                                            ],
                                            "components"=>[
                                                [
                                                    "type"=>"body",
                                                    "parameters"=>$parameters
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];


        $curl = curl_init();

        curl_setopt_array($curl,[
            CURLOPT_URL=>'https://gw.messaging.cm.com/v1.0/message',
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_TIMEOUT=>15,
            CURLOPT_POST=>true,
            CURLOPT_POSTFIELDS=>json_encode($payload),
            CURLOPT_HTTPHEADER=>[
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if($curlError){
            throw new Exception("cURL Error: ".$curlError);
        }

        $responseArray = json_decode($response,true);

        if($httpCode != 200){
            throw new Exception("WhatsApp API HTTP Error: ".$httpCode);
        }

        if(isset($responseArray['error'])){
            throw new Exception("WhatsApp API Error");
        }

        return [
            "status"=>true,
            "message"=>"Message sent successfully",
            "contact"=>$toNumber,
            "response"=>$responseArray
        ];

    } catch(Exception $e){

        log_message('error','WhatsApp Send Error: '.$e->getMessage());

        return [
            "status"=>false,
            "message"=>$e->getMessage()
        ];
    }
}

function sec_to_hms_label($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    return sprintf('%02dh:%02dm:%02ds', $hours, $minutes, $secs);
}



function update_lead_performace_feedback($leadid)
{
    $CI = &get_instance();

    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    if(empty($leadid))
    {
        return false;
    }

    $lead = $CI->leads_model->get($leadid);

    if (empty($lead)) {
        return [
            'status' => false,
            'message' => 'Lead not found'
        ];
    }
    

if (
    ($lead->dateadded > CAPI_DATE_START || $lead->upcomming_date > CAPI_DATE_START) &&
    $lead->from_form_id > 0 &&
    (
        strtotime($lead->dateadded) >= strtotime('-1 year') ||
        strtotime($lead->upcomming_date) >= strtotime('-1 year')
    )
) {
    $feedback_name = check_feedback($lead);

    if (empty($feedback_name)) {
        return [
            'status' => false,
            'message' => 'Feedback rule not matched'
        ];
    }

    if ($lead->source != 35 && $lead->source != 39) {
        return [
            'status' => false,
            'message' => 'Lead source not valid'
        ];
    }

    // ✅ Check if already sent
   

if($lead->source == 35){
     if (check_meta_feedback_log($lead->phonenumber, $feedback_name)) {
        return [
            'status' => false,
            'message' => 'Meta event already sent'
        ];
    }
    return metaFeedback_Api($lead, $feedback_name);
}
else if($lead->source == 39){
    
     if (check_google_feedback_log($lead->phonenumber, $feedback_name)) {
        return [
            'status' => false,
            'message' => 'Google event already sent'
        ];
    }
    return googleFeedback_Api($lead, $feedback_name);
}
}
return true;
}

function check_meta_feedback_log($phonenumber, $feedback_name)
{
    $CI = &get_instance();

    $CI->db->where('phonenumber', $phonenumber);
    $CI->db->where('feedback_name', $feedback_name);

    $row = $CI->db
        ->get(db_prefix() . 'lead_performace_logs')
        ->row();

    return !empty($row);
}

function check_google_feedback_log($phonenumber, $feedback_name)
{
    $CI = &get_instance();

    $CI->db->where('phonenumber', $phonenumber);
    $CI->db->where('feedback_name', $feedback_name);

    $row = $CI->db
        ->get(db_prefix() . 'leads_google_performnce_logs')
        ->row();

    return !empty($row);
}
function check_feedback($lead)
{
    $CI = &get_instance();

    $CI->db->where('id', $lead->type);
    $CI->db->where('feedback_status', 1);
    $CI->db->where("FIND_IN_SET(" . $lead->status . ", lead_status) !=", 0, false);
    $CI->db->where("FIND_IN_SET(" . $lead->source . ", lead_source) !=", 0, false);

    $data = $CI->db
        ->get(db_prefix() . 'leads_type')
        ->row();

if($lead->source ==35){
    return $data->meta_qualified_name ?? false;
}
else if($lead->source ==39){
     return $data->google_qualified_name ?? false;
}
}
function metaFeedback_Api($lead, $event_name)
{
    $meta_feedback_token = META_FEEDBACK_TOKEN;
    $meta_access_token   = META_FEEDBACK_ACCESS_TOKEN;

    $url = "https://graph.facebook.com/v18.0/" . $meta_feedback_token . "/events?access_token=" . $meta_access_token;

    $email = strtolower(trim($lead->email));
    $phone = preg_replace('/[^0-9]/', '', $lead->phonenumber);

    $hashed_email = hash('sha256', $email);
    $hashed_phone = hash('sha256', $phone);

    $payload = [
        "data" => [
            [
                "event_name" => $event_name,
                "event_time" => time(),
                "action_source" => "system_generated",
                "user_data" => [
                    "em" => $hashed_email,
                    "ph" => $hashed_phone
                ]
            ]
        ]
    ];

    try {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {

            $error = curl_error($curl);
            curl_close($curl);

            log_message('error', 'Meta CURL Error: ' . $error);

            return [
                'status' => false,
                'message' => 'Curl error',
                'data' => $error
            ];
        }

        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $response_data = json_decode($response, true);

        if ($http != 200) {

            log_message('error', 'Meta API Error: ' . $response);

            return [
                'status' => false,
                'message' => 'Meta API failed',
                'data' => $response_data
            ];
        }

        // ✅ Save log after success
        insert_lead_performance_feedback($lead, $event_name, $response);

        return [
            'status' => true,
            'message' => 'Meta event sent successfully',
            'data' => $response_data
        ];
    } catch (Exception $e) {

        log_message('error', 'Meta API Exception: ' . $e->getMessage());

        return [
            'status' => false,
            'message' => 'Exception occurred',
            'data' => $e->getMessage()
        ];
    }
}

function insert_lead_performance_feedback($lead, $feedback_name, $response)
{
    $CI = &get_instance();

    $data = [
        'leadid' => $lead->id,
        'source' => $lead->source,
        'status' => $lead->status,
        'phonenumber' => $lead->phonenumber,
        'type' => $lead->type,
        'data' => json_encode($lead),
        'api_response' => $response,
        'feedback_name' => $feedback_name,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $CI->db->insert(db_prefix() . 'lead_performace_logs', $data);
}


function googleFeedback_Api($lead, $feedback_name)
{
    $CI = &get_instance();

    $data = [
        'lead_id' => $lead->id,
        'source' => $lead->source,
        'status' => $lead->status,
        'phonenumber' => $lead->phonenumber,
        'email' => $lead->email,
        'type' => $lead->type,
        'data' => json_encode($lead),
        'lead_created_date' => $lead->dateadded,
        'feedback_name' => $feedback_name,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $CI->db->insert(db_prefix() . 'leads_google_performnce_logs', $data);
}

// function getleadsCounts_by_staff($data)
// {
//     $CI = &get_instance();

// $leadStatus = $data['status']??'';
// $leadAssigned = $data['assigned']??'';

// $where = " where 1= 1 ";
// $whereStatus = " where 1= 1 ";

// if (!empty($leadStatus) && is_array($leadStatus)) {
//     $statusList = implode(',', array_map('intval', $leadStatus));
//     $whereStatus .= " AND l.status IN ($statusList)";
// }

// if (!empty($leadAssigned) && is_array($leadAssigned)) {
//     $assignedList = implode(',', array_map('intval', $leadAssigned));
//     $where .= " AND st.staffid IN ($assignedList) ";
// }


// $sql ="WITH lead_base AS (
//     SELECT 
//         l.id,
//         l.assigned,
//         l.source,
//         l.status,
//         COALESCE(NULLIF(l.website,''), 'No Form') AS website
//     FROM tblleads l
//     $whereStatus
// ),

// source_counts AS (
//     SELECT 
//         assigned,
//         source,
//         COUNT(*) AS lead_count
//     FROM lead_base
//     GROUP BY assigned, source
// ),

// status_counts AS (
//     SELECT 
//         assigned,
//         source,
//         status,
//         COUNT(*) AS lead_count
//     FROM lead_base
//     GROUP BY assigned, source, status
// ),

// form_counts AS (
//     SELECT 
//         assigned,
//         website,
//         COUNT(*) AS lead_count
//     FROM lead_base
//     WHERE source = 35
//     GROUP BY assigned, website
// ),

// status_summary AS (
//     SELECT 
//         assigned,
//         status,
//         COUNT(*) AS lead_count
//     FROM lead_base
//     GROUP BY assigned, status
// )

// SELECT 
//     loc.id AS region_id,
//     loc.name AS region_name,

//     JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'staff_id', st.staffid,
//             'staff_name', CONCAT(st.firstname, ' ', st.lastname),

//             'lead_count', COALESCE(sl.lead_count, 0),

//             'sources', (
//                 SELECT JSON_ARRAYAGG(
//                     JSON_OBJECT(
//                         'source_id', sc.source,
//                         'source_name', s.name,
//                         'lead_count', sc.lead_count,

//                         'status', (
//                             SELECT JSON_ARRAYAGG(
//                                 JSON_OBJECT(
//                                     'status_id', stc.status,
//                                     'status_name', sta.name,
//                                     'count', stc.lead_count
//                                 )
//                             )
//                             FROM status_counts stc
//                             JOIN tblleads_status sta ON sta.id = stc.status
//                             WHERE stc.assigned = st.staffid
//                               AND stc.source = sc.source
//                         ),

//                         'forms', CASE 
//                             WHEN sc.source = 35 THEN (
//                                 SELECT JSON_ARRAYAGG(
//                                     JSON_OBJECT(
//                                         'form_name', fc.website,
//                                         'count', fc.lead_count
//                                     )
//                                 )
//                                 FROM form_counts fc
//                                 WHERE fc.assigned = st.staffid
//                             )
//                             ELSE JSON_ARRAY()
//                         END
//                     )
//                 )
//                 FROM source_counts sc
//                 JOIN tblleads_sources s ON s.id = sc.source
//                 WHERE sc.assigned = st.staffid
//             ),

//             'status_summary', (
//                 SELECT JSON_ARRAYAGG(
//                     JSON_OBJECT(
//                         'status_id', ss.status,
//                         'status_name', sta.name,
//                         'count', ss.lead_count
//                     )
//                 )
//                 FROM status_summary ss
//                 JOIN tblleads_status sta ON sta.id = ss.status
//                 WHERE ss.assigned = st.staffid
                
//             )

//         )
//     ) AS staff_data

// FROM tblstaff_location_region loc

// LEFT JOIN tblstaff st 
//     ON st.office_location_region = loc.id

// LEFT JOIN (
//     SELECT assigned, COUNT(*) AS lead_count
//     FROM lead_base
//     GROUP BY assigned
// ) sl ON sl.assigned = st.staffid
//  $where
// GROUP BY loc.id
// ORDER BY loc.name ";

//     $query = $CI->db->query($sql);

//     return $query->result_array(); // ✅ IMPORTANT
// }


function getleadsCounts_by_staff($data)
{
    $CI = &get_instance();
    
    
     $get_staff_user_id = get_staff_user_id();
    $idsarr =[];
   if (!empty($_POST['view_assigned'])) {
}else
{
      $role = $CI->db->where('staffid', $get_staff_user_id)->get(db_prefix() . 'staff')->row()->role;
    
     $sid = $get_staff_user_id;
        if ($role == 3) {
        $teamids = $CI->db->query('CALL GetReportingPersons(?)', array($get_staff_user_id))->result_array();
        $CI->db->close();
        $CI->db->initialize();
        $idsarr = array_column($teamids, 'staffid');
        }
}


    $leadStatus   = $data['status'] ?? [];
    $leadAssigned = $data['assigned'] ?? [];
    $leadSource   = $data['source'] ?? [];
    $created_date   = $data['created_date'] ?? '';
    $assigned_date   = $data['assigned_date'] ?? '';
    $campaign = $data['campaign'] ?? '';
    $adsset = $data['adsset'] ?? '';
    $ads = $data['ads'] ?? '';
    $form = $data['form'] ?? '';
    $department = $data['department'] ?? [];
    // $created_date   = $data['created_date'] ?? '';
    // $created_date   = $data['created_date'] ?? '';

    // ✅ SINGLE FILTER (ONLY HERE)
    $whereLead = " WHERE 1=1 ";
    $whereAssigned = " WHERE 1=1 ";

    if (!empty($leadStatus) && is_array($leadStatus)) {
        $statusList = implode(',', array_map('intval', $leadStatus));
        $whereLead .= " AND l.status IN ($statusList)";
    }

if (!empty($leadAssigned) && is_array($leadAssigned)) {

    $assignedList = implode(',', array_map('intval', $leadAssigned));

    $whereLead     .= " AND l.assigned IN ($assignedList)";
    $whereAssigned .= " AND st.staffid IN ($assignedList)";

} else {
    
    if(!is_admin()){

    if (!empty($idsarr)) {

        $ids = array_unique(array_merge($idsarr, [$get_staff_user_id]));
        $assignedList = implode(',', array_map('intval', $ids));

        $whereLead     .= " AND l.assigned IN ($assignedList)";
        $whereAssigned .= " AND st.staffid IN ($assignedList)";

    } else {

        $sid = (int)$get_staff_user_id;

        $whereLead     .= " AND l.assigned = $sid";
        $whereAssigned .= " AND st.staffid = $sid";
    }
    }
    
            if (!empty($department)) {
        $departmentList = implode(',', array_map('intval', $department));
         $whereAssigned .= " AND st.department IN ($departmentList)";
      
    }
    
}

    if (!empty($leadSource) && is_array($leadSource)) {
        $sourceList = implode(',', array_map('intval', $leadSource));
        $whereLead .= " AND l.source IN ($sourceList)";
        $whereSources .= " AND sc.source IN ($sourceList)";
    }
    
    if (!empty($form) && is_array($form)) {
    // Escape and quote each string value
    $formList = implode(',', array_map(function($f) use ($CI) {
        return "'" . $CI->db->escape_str($f) . "'";
    }, $form));
    
    $whereLead .= " AND l.website IN ($formList)";
}


  if (!empty($campaign) && is_array($campaign)) {
    // Escape and quote each string value
    $campaignList = implode(',', array_map(function($f) use ($CI) {
        return "'" . $CI->db->escape_str($f) . "'";
    }, $campaign));
    
    $whereLead .= " AND l.utm_campaign_name IN ($campaignList)";
}


  if (!empty($adsset) && is_array($adsset)) {
    // Escape and quote each string value
    $adssetList = implode(',', array_map(function($f) use ($CI) {
        return "'" . $CI->db->escape_str($f) . "'";
    }, $adsset));
    
    $whereLead .= " AND l.utm_ads_set_name IN ($adssetList)";
}


  if (!empty($ads) && is_array($ads)) {
    // Escape and quote each string value
    $adsList = implode(',', array_map(function($f) use ($CI) {
        return "'" . $CI->db->escape_str($f) . "'";
    }, $ads));
    
    $whereLead .= " AND l.utm_ads_name IN ($adsList)";
}
    
  if (!empty($created_date)) {

    // ✅ split date range
    $dates = explode(' to ', $created_date);

    if (count($dates) == 2) {

        $start = trim($dates[0]);
        $end   = trim($dates[1]);

        $whereLead .= " AND DATE(l.dateadded) BETWEEN '$start' AND '$end'";
    }
}

  if (!empty($assigned_date)) {

    // ✅ split date range
    $dates = explode(' to ', $assigned_date);

    if (count($dates) == 2) {

        $start = trim($dates[0]);
        $end   = trim($dates[1]);

        $whereLead .= " AND DATE(l.dateassigned) BETWEEN '$start' AND '$end'";
    }
}

  $sql = "
WITH lead_base AS (
    SELECT 
        l.id,
        l.assigned,
        l.source,
        l.status,
        COALESCE(NULLIF(l.website, ''), '') AS website,
        l.state,
        l.utm_campaign_name,
        l.utm_ads_set_name,
        l.utm_ads_name
    FROM tblleads l  
    $whereLead and from_form_id > 0
        GROUP BY id,assigned, source, status, website, utm_campaign_name, utm_ads_set_name, utm_ads_name

),

source_counts AS (
    SELECT assigned, source, COUNT(*) AS lead_count
    FROM lead_base
    GROUP BY assigned, source
),

status_counts AS (
    SELECT assigned, source, status, COUNT(*) AS lead_count
    FROM lead_base
    GROUP BY assigned, source, status
),

form_counts AS (
    SELECT assigned, website, COUNT(*) AS lead_count
    FROM lead_base
    WHERE source = 35 AND website != ''
    GROUP BY assigned, website
),

google_counts AS (
    SELECT 
        assigned, 
        utm_campaign_name,
        utm_ads_set_name,
        utm_ads_name,
        COUNT(*) AS lead_count
    FROM lead_base
    WHERE source = 39 AND utm_campaign_name != ''
    GROUP BY assigned, utm_campaign_name, utm_ads_set_name, utm_ads_name
),

status_summary AS (
    SELECT assigned, status, COUNT(*) AS lead_count
    FROM lead_base
    GROUP BY assigned, status
)

SELECT 
    loc.id AS region_id,
    loc.name AS region_name,

    JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', st.staffid,
            'staff_name', CONCAT(st.firstname, ' ', st.lastname),
            'lead_count', COALESCE(sl.lead_count, 0),
            'office_region_id',st.office_location_region,
            'sources', (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'source_id', sc.source,
                        'source_name', s.name,
                        'lead_count', sc.lead_count,

                        'status', (
                            SELECT JSON_ARRAYAGG(
                                JSON_OBJECT(
                                    'status_id', stc.status,
                                    'status_name', sta.name,
                                    'count', stc.lead_count
                                )
                            )
                            FROM status_counts stc
                            JOIN tblleads_status sta ON sta.id = stc.status
                            WHERE stc.assigned = st.staffid
                              AND stc.source = sc.source $whereSources
                        ),

                        'forms', CASE 
                            WHEN sc.source = 35 THEN (
                                SELECT JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                        'form_name', fc.website,
                                        'count', fc.lead_count
                                    )
                                )
                                FROM form_counts fc
                                WHERE fc.assigned = st.staffid
                            )
                            ELSE JSON_ARRAY()
                        END,
'google', CASE
    WHEN sc.source = 39 THEN (
        SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'utm_campaign_name', gc.utm_campaign_name,
                'utm_ads_set_name', gc.utm_ads_set_name,
                'utm_ads_name', gc.utm_ads_name,
                'count', gc.lead_count
            )
        )
        FROM google_counts gc
        WHERE gc.assigned = st.staffid
    )
    ELSE JSON_ARRAY()
END
                    )
                )
                FROM source_counts sc
                JOIN tblleads_sources s ON s.id = sc.source
                WHERE sc.assigned = st.staffid and s.paid_sources=1 $whereSources
            ),

            'status_summary', (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'status_id', ss.status,
                        'status_name', sta.name,
                        'count', ss.lead_count
                    )
                )
                FROM status_summary ss
                JOIN tblleads_status sta ON sta.id = ss.status
                WHERE ss.assigned = st.staffid
            )
        )
    ) AS staff_data

FROM tblstaff_location_region loc
LEFT JOIN tblstaff st ON st.office_location_region = loc.id
LEFT JOIN (
    SELECT assigned, COUNT(*) AS lead_count
    FROM lead_base
    GROUP BY assigned
) sl ON sl.assigned = st.staffid
$whereAssigned
GROUP BY loc.id
ORDER BY loc.name
";

    $query = $CI->db->query($sql);

    return $query->result_array();
}

function getleadsCounts_by_source($data)
{
    

    $CI = &get_instance();

    $get_staff_user_id = get_staff_user_id();
    $idsarr = [];

    // 🔹 Get team members if role = 3
    if (empty($_POST['view_assigned'])) {
        $role = $CI->db->where('staffid', $get_staff_user_id)
            ->get(db_prefix() . 'staff')
            ->row()
            ->role;

        if ($role == 3) {
            $teamids = $CI->db->query('CALL GetReportingPersons(?)', [$get_staff_user_id])->result_array();
            $CI->db->close();
            $CI->db->initialize();
            $idsarr = array_column($teamids, 'staffid');
        }
    }

    // 🔹 Inputs
    $leadStatus   = $data['status'] ?? [];
    $leadAssigned = $data['assigned'] ?? [];
    $leadSource   = $data['source'] ?? [];
    $created_date = $data['created_date'] ?? '';
    $assigned_date = $data['assigned_date'] ?? '';
    $campaign = $data['campaign'] ?? [];
    $adsset = $data['adsset'] ?? [];
    $ads = $data['ads'] ?? [];
    $form = $data['form'] ?? [];
    $department = $data['department'] ?? [];

    // 🔹 WHERE
    $whereLead = " WHERE 1=1 ";

    // ✅ Status
    if (!empty($leadStatus)) {
        $statusList = implode(',', array_map('intval', $leadStatus));
        $whereLead .= " AND l.status IN ($statusList)";
    }

    // ✅ Assigned
    if (!empty($leadAssigned)) {

        $assignedList = implode(',', array_map('intval', $leadAssigned));
        $whereLead .= " AND l.assigned IN ($assignedList)";

    } else {

if(!is_admin()){
        if (!empty($idsarr)) {
            $ids = array_unique(array_merge($idsarr, [$get_staff_user_id]));
            $assignedList = implode(',', array_map('intval', $ids));
            $whereLead .= " AND l.assigned IN ($assignedList)";
        } else {
            $sid = (int)$get_staff_user_id;
            $whereLead .= " AND l.assigned = $sid";
        }
        

        
  
}

        if (!empty($department)) {
        $departmentList = implode(',', array_map('intval', $department));
         $whereLead .= " AND sst.department IN ($departmentList)";
      
    }
    
    }

    // ✅ Source
    if (!empty($leadSource)) {
        $sourceList = implode(',', array_map('intval', $leadSource));
        $whereLead .= " AND l.source IN ($sourceList)";
    }

    // ✅ Form (remove blank automatically)
    if (!empty($form)) {
        $formList = implode(',', array_map(function ($f) use ($CI) {
            return "'" . $CI->db->escape_str(trim($f)) . "'";
        }, array_filter($form)));
        if (!empty($formList)) {
            $whereLead .= " AND l.website IN ($formList)";
        }
    }

    // ✅ Campaign
    if (!empty($campaign)) {
        $campaignList = implode(',', array_map(function ($f) use ($CI) {
            return "'" . $CI->db->escape_str($f) . "'";
        }, $campaign));
        $whereLead .= " AND l.utm_campaign_name IN ($campaignList)";
    }

    // ✅ Ads Set
    if (!empty($adsset)) {
        $adssetList = implode(',', array_map(function ($f) use ($CI) {
            return "'" . $CI->db->escape_str($f) . "'";
        }, $adsset));
        $whereLead .= " AND l.utm_ads_set_name IN ($adssetList)";
    }

    // ✅ Ads
    if (!empty($ads)) {
        $adsList = implode(',', array_map(function ($f) use ($CI) {
            return "'" . $CI->db->escape_str($f) . "'";
        }, $ads));
        $whereLead .= " AND l.utm_ads_name IN ($adsList)";
    }

    // ✅ Created Date
    if (!empty($created_date)) {
        $dates = explode(' to ', $created_date);
        if (count($dates) == 2) {
            $whereLead .= " AND DATE(l.dateadded) BETWEEN '{$dates[0]}' AND '{$dates[1]}'";
        }
    }

    // ✅ Assigned Date
    if (!empty($assigned_date)) {
        $dates = explode(' to ', $assigned_date);
        if (count($dates) == 2) {
            $whereLead .= " AND DATE(l.dateassigned) BETWEEN '{$dates[0]}' AND '{$dates[1]}'";
        }
    }

    // 🔹 FINAL QUERY
    $sql = "
WITH lead_base AS (
    SELECT 
        l.id,
        l.assigned,
        l.source,
        l.status,
        l.state,
        COALESCE(NULLIF(l.website,''), '') AS website,
        l.utm_campaign_name,
        l.utm_ads_set_name,
        l.utm_ads_name,
        sst.department -- include department
    FROM tblleads l
    JOIN tblleads_sources ss 
        ON ss.id = l.source AND ss.paid_sources = 1
    JOIN tblstaff sst 
        ON sst.staffid = l.assigned
   
    $whereLead
    AND from_form_id > 0
),

staff_counts AS (
    SELECT assigned, source, COUNT(*) AS lead_count
    FROM lead_base
    GROUP BY assigned, source
),

status_counts AS (
    SELECT assigned, source, status, COUNT(*) AS lead_count
    FROM lead_base
    GROUP BY assigned, source, status
),

form_counts AS (
    SELECT website, COUNT(*) AS lead_count
    FROM lead_base
    WHERE source = 35 AND website != ''
    GROUP BY website
),

form_staff_counts AS (
    SELECT assigned, website, COUNT(*) AS lead_count
    FROM lead_base
    
    WHERE source = 35 AND website != ''
    GROUP BY assigned, website
),

google_region_staff AS (
    SELECT 
        lb.assigned,
        COALESCE(sst.id, 0) AS region_id,
        COALESCE(sst.name, 'Unknown Region') AS region_name,
        COUNT(*) AS lead_count
    FROM lead_base lb
    JOIN tblleads_sources so 
        ON so.id = lb.source AND so.google_type = 1
    LEFT JOIN tblstates st 
        ON st.name = lb.state
    LEFT JOIN tblstaff_state_region sst 
        ON FIND_IN_SET(st.id, sst.state)
    GROUP BY lb.source, COALESCE(sst.id, 0), lb.assigned
)

SELECT 
    s.id AS source_id,
    s.name AS source_name,

    CASE 

        -- FB FORMS
        WHEN s.id = 35 THEN COALESCE((
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'form_name', fc.website,
                    'staff', (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'staff_id', st.staffid,
                                'staff_name', CONCAT(st.firstname, ' ', st.lastname),
                                'count', fsc.lead_count,
                                'office_region_id', st.office_location_region,
                                'department', st.department
                            )
                        )
                        FROM form_staff_counts fsc
                        JOIN tblstaff st ON st.staffid = fsc.assigned
                        WHERE fsc.website = fc.website
                    )
                )
            )
            FROM form_counts fc
        ), JSON_ARRAY())

        -- GOOGLE → REGION → STAFF
        WHEN s.google_type = 1 THEN COALESCE((
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'region_id', gr.region_id,
                    'region_name', gr.region_name,
                    'staff', (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'staff_id', st.staffid,
                                'staff_name', CONCAT(st.firstname, ' ', st.lastname),
                                'count', gr2.lead_count,
                                'office_region_id', st.office_location_region,
                                'department', st.department
                            )
                        )
                        FROM google_region_staff gr2
                        JOIN tblstaff st ON st.staffid = gr2.assigned
                        WHERE gr2.region_id = gr.region_id
                    )
                )
            )
            FROM (
                SELECT DISTINCT region_id, region_name 
                FROM google_region_staff
            ) gr
        ), JSON_ARRAY())

        -- OTHER SOURCES
        ELSE COALESCE((
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'staff_id', st.staffid,
                    'staff_name', CONCAT(st.firstname, ' ', st.lastname),
                    'lead_count', COALESCE(sc.lead_count, 0),
                    'office_region_id', st.office_location_region,
                    'department', st.department,
                    'status_summary', COALESCE((
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'status_id', ls.id,
                                'status_name', ls.name,
                                'count', sc2.lead_count
                            )
                        )
                        FROM status_counts sc2
                        JOIN tblleads_status ls ON ls.id = sc2.status
                        WHERE sc2.assigned = st.staffid 
                          AND sc2.source = s.id
                    ), JSON_ARRAY())
                )
            )
            FROM staff_counts sc
            JOIN tblstaff st ON st.staffid = sc.assigned
            WHERE sc.source = s.id
        ), JSON_ARRAY())

    END AS data

FROM tblleads_sources s
WHERE s.paid_sources = 1
ORDER BY s.id
    ";
// Join tblfacebook_name  fbname on (fbname.name = lead_base.website and fbname.status=1)
   

    return $CI->db->query($sql)->result_array();
}

function get_lastCall_sync($staffId)
{
    $CI = &get_instance();

if(empty($staffId))
{
     return null; // or return '0000-00-00'
}
    $sql = "
       SELECT 
   call_start AS call_datetime
FROM tblcalls_activity_logs
WHERE staffid = ?
ORDER BY call_start DESC
LIMIT 1;
    ";

    $query = $CI->db->query($sql, [$staffId]);

    // ✅ check query success
    if (!$query) {
        return null; // or return '0000-00-00'
    }

    $row = $query->row();

    // ✅ handle no data
    if (!$row) {
        return null; // no calls found
    }

    return $row->call_datetime;
}

function get_todayCalls()
{

        
     $CI = &get_instance();
     $current_date = date('Y-m-d');
    $sql ="SELECT 
    COUNT(id) AS total_calls,
    SUM(duration) AS total_seconds,
    SEC_TO_TIME(SUM(duration)) AS duration_hms,
    FROM_UNIXTIME(MAX(call_start) + 19800) AS last_call_time
FROM tblcalls_activity_logs 
WHERE staffid = '".get_staff_user_id()."'  AND (
    calls_source = 2
)                                                               
  AND adjusted_call_start = '${current_date}'";

  
  return $CI->db->query($sql)->row();
  
}

/* Normalize Numbers */
function normalizeNumber($number)
{
    $number = preg_replace('/\D/', '', $number);
    return substr($number, -10);
}

function not_reachable_notification()
{
     $CI = &get_instance();
    $today = date('Y-m-d');
    $sql ="SELECT CONCAT(s.firstname,' ',s.lastname) staffname,s.fcm_token,count(id) leadCount,s.staffid FROM ".db_prefix()."leads_transfer_logs l join ".db_prefix()."staff s on l.old_assignation = s.staffid where date(created_at) = '{$today}' and l.old_assignation!='".IVR_AUTO_ASIGNATION."' and l.old_status= 20 and s.fcm_token!=''  GROUP by old_assignation";
     return $CI->db->query($sql)->result_array();
}

function fresh_notification()
{
     $CI = &get_instance();
    $today = date('Y-m-d');
    $sql ="SELECT CONCAT(s.firstname,' ',s.lastname) staffname,s.fcm_token,count(id) leadCount,s.staffid FROM ".db_prefix()."leads_transfer_logs l join ".db_prefix()."staff s on l.old_assignation = s.staffid where date(created_at) = '{$today}' and l.old_assignation!='".IVR_AUTO_ASIGNATION."' and l.old_status= 2 and s.fcm_token!=''  GROUP by old_assignation";
     return $CI->db->query($sql)->result_array();
}

function send_Fcm_Notification($bulkNotification,$type)
{
    $CI = &get_instance();
    $CI->load->library('Fcm_lib');
    $CI->load->driver('cache', ['adapter' => 'file']);
    if(!empty($bulkNotification))
    {
        $logs=[];
        foreach($bulkNotification as $bN)
        {
             $logs[] = array("staff_id"=>$bN["staffid"],"type"=>$type,"data"=>json_encode($bN,true),"created_at"=>date('Y-m-d H:i:s'));

        }
        
    }
    
    if (!empty($bulkNotification)) {
       $CI->fcm_lib->sendBulk_message($bulkNotification);
 
        if(!empty($logs))
        {
            $CI->db->insert_batch(db_prefix()."fcm_notifications_log",$logs);
        }
    }
    
    return true;
    
}


function update_reminder_data()
{
    
    $current_date_time = date('Y-m-d');

$CI = &get_instance();

$sql = "

UPDATE tblreminders r

JOIN (

    SELECT 
        x.reminder_id,

        MIN(x.call_date) AS min_call_start,

        MIN(n.dateadded) AS min_note_date,

        CASE 
            WHEN MIN(n.dateadded) IS NOT NULL
                 AND MIN(n.dateadded) < MIN(x.call_date)
            THEN 'notes'
            ELSE 'calls'
        END AS update_type,

        CASE 
            WHEN MIN(n.dateadded) IS NOT NULL
                 AND MIN(n.dateadded) < MIN(x.call_date)
            THEN MIN(n.dateadded)
            ELSE MIN(x.call_date)
        END AS final_update_date

    FROM (

        /* Primary phone */
        SELECT 
            r.id AS reminder_id,
            r.rel_id,
            r.creator,
            FROM_UNIXTIME(c.call_start + 19800) AS call_date

        FROM tblreminders r

        INNER JOIN tblleads l 
            ON l.id = r.rel_id

        INNER JOIN tblcalls_activity_logs c 
            ON c.contact = l.phonenumber
           AND c.staffid = r.creator

        WHERE r.rel_type = 'lead'
          AND r.status = 0
          AND r.date >= '2026-03-01 00:00:00'
          AND c.adjusted_call_start >= '{$current_date_time}'
          AND r.date < FROM_UNIXTIME(c.call_start + 19800)

        UNION ALL

        /* Alternative phone */
        SELECT 
            r.id AS reminder_id,
            r.rel_id,
            r.creator,
            FROM_UNIXTIME(c.call_start + 19800) AS call_date

        FROM tblreminders r

        INNER JOIN tblleads l 
            ON l.id = r.rel_id

        INNER JOIN tblcalls_activity_logs c 
            ON c.contact = l.alternative_phonenumber
           AND c.staffid = r.creator

        WHERE r.rel_type = 'lead'
          AND r.status = 0
          AND r.date >= '2026-03-01 00:00:00'
          AND c.adjusted_call_start >= '{$current_date_time}'
          AND r.date < FROM_UNIXTIME(c.call_start + 19800)

    ) x

    LEFT JOIN tblnotes n
        ON n.rel_id = x.rel_id
       AND n.addedfrom = x.creator
       AND n.rel_type = 'lead'
       AND n.dateadded >= '{$current_date_time} 00:00:00'
       AND n.dateadded > x.call_date

    GROUP BY x.reminder_id

) z 
ON z.reminder_id = r.id

SET
    r.status = 1,
    r.type = z.update_type,
    r.updated_date = z.final_update_date

WHERE r.status = 0


";


$CI->db->query($sql);

return $CI->db->affected_rows();
}


function sendCallData(string $endpoint, string $apiKey, array $call, int $timeout = 15): array
{
    $fail = static function (int $status, string $error, $raw = null): array {
        return [
            'ok'        => false,
            'status'    => $status,
            'inserted'  => 0,
            'skipped'   => 0,
            'message'   => $error,
            'error'     => $error,
            'raw'       => $raw
        ];
    };

    if ($endpoint === '' || $apiKey === '') {
        return $fail(0, 'Endpoint and API Key are required.');
    }

    // Wrap the single call in the expected payload
    $payload = json_encode([
        'calls' => [$call]
    ]);

    if ($payload === false) {
        return $fail(0, 'JSON Encode Error: ' . json_last_error_msg());
    }

    log_message('info', 'Call API Request: ' . $payload);

    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => $payload,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_CONNECTTIMEOUT  => $timeout,
        CURLOPT_TIMEOUT         => $timeout,
        CURLOPT_HTTPHEADER      => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: ' . $apiKey
        ]
    ]);

    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);

    curl_close($ch);

    log_message('info', 'Call API Response (' . $status . '): ' . $body);

    if ($body === false) {
        return $fail(0, 'cURL Error: ' . $error);
    }

    $data = json_decode($body, true);

    if (!is_array($data)) {
        return $fail($status, 'Invalid JSON Response', $body);
    }

    if ($status < 200 || $status >= 300) {

        $msg = $data['messages']['error']
            ?? $data['message']
            ?? 'API Request Failed';

        return $fail($status, $msg, $data);
    }

    return [
        'ok'        => true,
        'status'    => $status,
        'inserted'  => $data['inserted'] ?? 0,
        'skipped'   => $data['skipped'] ?? 0,
        'message'   => $data['message'] ?? 'Success',
        'error'     => null,
        'raw'       => $data
    ];
}
