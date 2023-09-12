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

        $teamids = $CI->db->query("select staffid
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

    // $statuses[] = [
    //     'lost'  => true,
    //     'name'  => _l('lost_leads'),
    //     'color' => '#f0f0f0',
    // ];


    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query("select staffid
        	from    (select * from tblstaff
        	where active = '1' order by reporting_person, staffid) products_sorted,
        			(select @pv := $sid) initialisation
        	where   find_in_set(reporting_person, @pv)
        	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        // $query = [];
        // $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
        // $staff_ids = implode(",", array_column($query_sql, 'staffid'));

        // if (!empty($staff_ids)) {
        //     $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
        // }
        // $idsarr = array_column($query, 'staffid');
        // $sids = implode(",", $idsarr);
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }

        //         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
    }

    foreach ($statuses as $status) {
        $sql .= ' SELECT SUM(subquery.total) AS total FROM ( ';
        $sql .= ' SELECT COUNT(DISTINCT(' . db_prefix() . 'leads.id)) as total';
        $sql .= ' FROM ' . db_prefix() . 'leads';

        if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
            $sql .= ' join tblcustomfieldsvalues ON  tblleads.id=tblcustomfieldsvalues.relid ';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date_join = $params['up_from_date'];
            $up_to_date_join = $params['up_to_date'];
            $sql .= ' left join ' . db_prefix() . 'notes n  ON  (' . db_prefix() . 'leads.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';
        } else if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $sql .= ' left join ' . db_prefix() . 'notes n  ON  (' . db_prefix() . 'leads.id = n.rel_id ) ';
        }
        if (!empty($params['followup_to_date'])) {
            $sql .= ' join tblreminders  on  tblreminders.rel_id = tblleads.id ';
        }

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
        if (!empty($params['assigned'])) {
            // $tids = " AND assigned = " . $params['assigned'];
            $tids = " AND assigned IN ( " . implode(",", $params['assigned']) . ") ";
            $sql .= $tids;
        } else {
            if ($role == 3) {
                $sql .= $tids;
            }
        }

        if (!empty($params['source'])) {
            $sql .= ' AND source in (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
        }

        if (!empty($params['neet_score'])) {
            $neet_range = explode("-", $params['neet_score']);
            // $sql .= ' AND  ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN "' . $CI->db->escape_str(trim($neet_range[0])) . '" AND "' . $CI->db->escape_str(trim($neet_range[1])) . '" order by id desc limit 1';

            $sql .= ' AND ( ' . db_prefix() . 'customfieldsvalues.fieldid = 8 AND  ' . db_prefix() . 'customfieldsvalues.value BETWEEN ' . $CI->db->escape_str(trim($neet_range[0])) . ' AND ' . $CI->db->escape_str(trim($neet_range[1])) . ' AND ' . db_prefix() . 'customfieldsvalues.value!="" )';
        }
        if (!empty($params['lead_type'])) {
            $sql .= ' AND type in (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
            // $sql .= ' AND type =' . $CI->db->escape_str($params['lead_type']);
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


        if (!empty($params['to_date'])) {
            $from_date = $params['from_date'];
            $to_date = $params['to_date'];
            $sql .= ' AND DATE(' . db_prefix() . 'leads.dateadded) BETWEEN "' . $CI->db->escape_str($from_date) . '" AND "' . $CI->db->escape_str($to_date) . '"';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date = $params['up_from_date'];
            $up_to_date = $params['up_to_date'];
            //  $sql .= ' AND DATE(lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
            //             $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
            $sql .= ' AND DATE(' . db_prefix() . 'leads.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
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

        if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
            $min = $params['update_count_min'];
            $max = $params['update_count_max'];
            $sql .= ' GROUP BY tblleads.id HAVING COUNT(tblleads.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
        }

        $grup_by = "";
        if (!empty($params['neet_score'])) {
            // $grup_by = db_prefix() . 'customfieldsvalues.relid';
            // $sql .= ' group by ' . $grup_by;
        }

        $sql .= " ) AS subquery ";
        $sql .= ' UNION ALL ';
        $sql = trim($sql);
    }
    $result = [];

    // Remove the last UNION ALL
    $sql    = substr($sql, 0, -10);

    $result = $CI->db->query($sql)->result();

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

    $totalLeads = 0;

    foreach ($statuses as $key => $status) {
        // if (isset($status['lost']) || isset($status['junk'])) {
        //     $statuses[$key]['percent'] = ($total_leads > 0 ? number_format(($result[$key]->total * 100) / $total_leads, 2) : 0);
        // }

        $statuses[$key]['total'] = 0;
        if (!empty($_POST["status"])) {
            if (in_array($status["id"], $_POST["status"])) {
                $statuses[$key]['total'] = !empty($result[$key]->total) ? $result[$key]->total : 0;
            } else {
                $statuses[$key]['total'] = 0;
            }
        } else {
            $statuses[$key]['total']  = !empty($result[$key]->total) ? $result[$key]->total : 0;
        }

        $totalLeads += !empty($statuses[$key]['total']) ? $statuses[$key]['total'] : 0;
    }


    $statuses[] = array("name" => "Total Leads", "color" => "#28B8DA", "isdefault" => 0, "total" => $totalLeads);

    return $statuses;
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
    $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

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

        if (!empty($params['course']) || !empty($params['degree'])) {
            $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date_join = $params['up_from_date'];
            $up_to_date_join = $params['up_to_date'];
            $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';
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
            $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
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

function leads_update_count($params = false, $max_status = 0)
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
        $teamids = $CI->db->query("select staffid
			from    (select * from tblstaff
			where active = '1' order by reporting_person, staffid) products_sorted,
					(select @pv := $sid) initialisation
			where   find_in_set(reporting_person, @pv)
			and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        // $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
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
    } else {
        $sql .= " SELECT  count(DISTINCT(calls.id)) as total ";
    }
    $sql .= ",concat(DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d'),'-',calls.contact) uni_dates FROM " . db_prefix() . "leads as l inner join " . db_prefix() . "calls_activity_logs as calls on ( l.phonenumber = calls.contact  ";

    if (!empty($params['up_to_date'])) {
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';

        $sql .= " AND  DATE_FORMAT(DATE_ADD('1970-01-01', INTERVAL (calls.call_start+(5 * 3600 + 30 * 60)) SECOND), '%Y-%m-%d')  between '{$up_from_date}' AND '{$up_to_date}' AND staffid = l.assigned ";
    }
    $sql .= ' ) ';
    if (!empty($params['course']) || !empty($params['degree']) || !empty($params['neet_score'])) {
        $sql .= ' join  ' . db_prefix() . 'customfieldsvalues ON  l.id= ' . db_prefix() . 'customfieldsvalues.relid ';
    }

    if (!empty($params['followup_to_date'])) {
        $sql .= ' join tblreminders  on  tblreminders.rel_id = l.id ';
    }

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
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
    } else if (!empty($params['up_to_date'])) {
        // $up_from_date = $params['up_from_date'];
        // $up_to_date = $params['up_to_date'];
        // $sql .= ' AND DATE(l.lastcontact) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
    }/*else{
            $today = date("Y-m-d");
            $sql .= " AND n.dateadded LIKE '%" .$today."%'";
        }*/
    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }
    $sql_add = "";
    if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
        $min = $params['update_count_min'];
        $max = $params['update_count_max'];
        $sql_add = ' HAVING COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
    }


    if (!empty($max_status) && $max_status == 1) {
        $sql .= " group by l.id " . $grup_by . " order by total desc limit 1 ";
        $sql = trim($sql);
        $sql = "SELECT sum(total) as total_sum FROM ( {$sql} )  as subquery ";
    } else {
        $sql .= " group by l.id,uni_dates " . $grup_by . " " . $sql_add . "   ";
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
        $teamids = $CI->db->query("select staffid
			from    (select * from tblstaff
			where active = '1' order by reporting_person, staffid) products_sorted,
					(select @pv := $sid) initialisation
			where   find_in_set(reporting_person, @pv)
			and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
        // $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
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
    $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

    // $statuses[] = [
    //     'lost'  => true,
    //     'name'  => _l('lost_leads'),
    //     'color' => '#f0f0f0',
    // ];


    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query("select staffid
        	from    (select * from tblstaff
        	where active = '1' order by reporting_person, staffid) products_sorted,
        			(select @pv := $sid) initialisation
        	where   find_in_set(reporting_person, @pv)
        	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        // $query = [];
        // $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
        // $staff_ids = implode(",", array_column($query_sql, 'staffid'));

        // if (!empty($staff_ids)) {
        //     $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
        // }
        // $idsarr = array_column($query, 'staffid');
        // $sids = implode(",", $idsarr);

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

        if (!empty($params['course']) || !empty($params['degree'])) {
            $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date_join = $params['up_from_date'];
            $up_to_date_join = $params['up_to_date'];
            $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';
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
            $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
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
    $whereNoViewPermission = '(' . db_prefix() . 'leads.addedfrom = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.assigned=' . get_staff_user_id() . ' OR ' . db_prefix() . 'leads.is_public = 1)';

    // $statuses[] = [
    //     'lost'  => true,
    //     'name'  => _l('lost_leads'),
    //     'color' => '#f0f0f0',
    // ];


    $role = $CI->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
    if ($role == 3) {
        // $this->load->database();
        $sid = get_staff_user_id(); //48;//get_staff_user_id();
        $teamids = $CI->db->query("select staffid
        	from    (select * from tblstaff
        	where active = '1' order by reporting_person, staffid) products_sorted,
        			(select @pv := $sid) initialisation
        	where   find_in_set(reporting_person, @pv)
        	and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';

        // $query = [];
        // $query_sql = $CI->db->query("select staffid from " . db_prefix() . "staff where reporting_person = {$sid} and active = '1' ")->result_array();
        // $staff_ids = implode(",", array_column($query_sql, 'staffid'));

        // if (!empty($staff_ids)) {
        //     $query = $CI->db->query("select * from " . db_prefix() . "staff where reporting_person in ({$staff_ids}) or staffid in ({$staff_ids}) or staffid='{$sid}' and active = '1' order by reporting_person, staffid")->result_array();
        // }
        // $idsarr = array_column($query, 'staffid');
        // $sids = implode(",", $idsarr);
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
        //         $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
    }

    foreach ($statuses as $status) {
        $sql .= ' SELECT COUNT(DISTINCT(l.id)) as total,c.id conversion_id,m.id marketing_id,m.name marketing_name,c.name conversion_name, ls.name status_name ,s.name source_name,concat(ls.name,"-",s.name) index_name,s.id source_id,ls.id status_id,concat(s.name,"-",c.name) index_conversion_name,concat(m.name,"-",c.name) index_performance_name ';
        $sql .= ' FROM ' . db_prefix() . 'leads l inner join  ' . db_prefix() . 'leads_status ls ON  ls.id = l.status inner join ' . db_prefix() . 'leads_sources s ON s.id = l.source left join ' . db_prefix() . 'lead_marketing m ON m.id = s.marketing_type left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

        // $sql .=' FROM ' . db_prefix() . 'lead_marketing m left join ' . db_prefix() . 'leads_sources s ON s.marketing_type = m.id left join ' . db_prefix() . 'leads l ON s.id = l.source left join ' . db_prefix() . 'leads_status ls ON  ls.id = l.status left join ' . db_prefix() . 'lead_conversion_type c ON c.id = ls.conversion_type ';

        if (!empty($params['course']) || !empty($params['degree'])) {
            $sql .= ' join tblcustomfieldsvalues ON  l.id=tblcustomfieldsvalues.relid ';
        }
        if (!empty($params['up_to_date'])) {
            $up_from_date_join = $params['up_from_date'];
            $up_to_date_join = $params['up_to_date'];
            $sql .= ' left join ' . db_prefix() . 'notes n  ON  (l.id = n.rel_id AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date_join) . '" AND "' . $CI->db->escape_str($up_to_date_join) . '")';
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
            $sql .= ' AND DATE(n.dateadded) BETWEEN "' . $CI->db->escape_str($up_from_date) . '" AND "' . $CI->db->escape_str($up_to_date) . '"';
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
        if ($conversion_status) {
            $sql .= '  GROUP BY l.source,c.id ';
        } else {
            $sql .= '  GROUP BY m.id,c.id ';
        }
        $sql .= ' UNION ALL ';
        $sql = trim($sql);
    }
    $result = [];

    // Remove the last UNION ALL
    $sql    = substr($sql, 0, -10);

    $result = $CI->db->query($sql)->result_array();


    return $result;
}


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
        $teamids = $CI->db->query("select staffid
			from    (select * from tblstaff
			where active = '1' order by reporting_person, staffid) products_sorted,
					(select @pv := $sid) initialisation
			where   find_in_set(reporting_person, @pv)
			and     length(@pv := concat(@pv, ',', staffid))")->result_array();
        $idsarr = array_column($teamids, 'staffid');
        $sids = implode(",", $idsarr);
        // $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        if (!empty($sids)) {
            $tids = ' AND assigned in (' . $sid . ',' . $sids . ')';
        } else {
            $tids = ' AND assigned in (' . $sid . ')';
        }
    }

    $sql = "SELECT IFNULL(SUM(call_duration), 0) AS call_duration FROM (";
    $sql .= "SELECT SUM(calls.duration) AS call_duration FROM " . db_prefix() . "leads l ";
    // $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.assigned = calls.staffid AND RIGHT(TRIM(REPLACE(REPLACE(calls.contact, ' ', ''), ',', '')), 10) = RIGHT(TRIM(REPLACE(REPLACE(l.phonenumber, ' ', ''), ',', '')), 10) AND LOWER(TRIM(call_status)) IN ('answered', 'status_unknown')) ";
    $sql .= "JOIN " . db_prefix() . "calls_activity_logs calls ON (l.phonenumber = calls.contact ";

    if (!empty($params['assigned'])) {
        $sql .= " AND l.assigned IN (" . implode(",", $params['assigned']) . ") ";
    }
    $sql .= " ) ";
    $sql .= " WHERE LOWER(TRIM(call_status)) IN ('answered', 'status_unknown') ";

    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }

    if (!empty($params['status'])) {
        $sql .= ' AND l.status IN (' . implode(",", $CI->db->escape_str($params['status'])) . ')';
    }

    if (!empty($params['source'])) {
        $sql .= ' AND l.source IN (' . implode(",", $CI->db->escape_str($params['source'])) . ')';
    }

    if (!empty($params['lead_type'])) {
        $sql .= ' AND l.type IN (' . implode(",", $CI->db->escape_str($params['lead_type'])) . ')';
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
        $up_from_date = $params['up_from_date'];
        $up_to_date = $params['up_to_date'];
        $sql .= " AND (DATE_FORMAT(FROM_UNIXTIME(calls.call_start + (5 * 3600 + 30 * 60)), '%Y-%m-%d') BETWEEN '"
            . $CI->db->escape_str($up_from_date) . "' AND '" . $CI->db->escape_str($up_to_date) . "')";
    }

    $grup_by = "";
    if (!empty($params['neet_score'])) {
        $grup_by = ',' . db_prefix() . 'customfieldsvalues.relid';
    }

    $sql_add = "";
    // if (isset($params['update_count_max']) && $params['update_count_max'] != "") {
    //     $min = $params['update_count_min'];
    //     $max = $params['update_count_max'];
    //     $sql_add = ' HAVING COUNT(l.id) BETWEEN "' . $CI->db->escape_str($min) . '" AND "' . $CI->db->escape_str($max) . '"';
    // }

    $sql .= " GROUP BY calls.contact" . $grup_by . " " . $sql_add . ") AS subquery";

    $sql = "SELECT IFNULL(SUM(call_duration), 0) AS total_sum FROM (" . $sql . ") AS subquery";

    $update_count = $CI->db->query($sql)->row()->total_sum;

    return !empty($update_count) ? convertToHMS($update_count) : convertToHMS(0);
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
