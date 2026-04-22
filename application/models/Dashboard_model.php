<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('leads_model');
    }

    /**
     * @return array
     * Used in home dashboard page
     * Return all upcoming events this week
     */
    public function get_upcoming_events()
    {
        $monday_this_week = date('Y-m-d', strtotime('monday this week'));
        $sunday_this_week = date('Y-m-d', strtotime('sunday this week'));

        $this->db->where("(start BETWEEN '$monday_this_week' and '$sunday_this_week')");
        $this->db->where('(userid = ' . get_staff_user_id() . ' OR public = 1)');
        $this->db->order_by('start', 'desc');
        $this->db->limit(6);

        return $this->db->get(db_prefix() . 'events')->result_array();
    }

    /**
     * @param  integer (optional) Limit upcoming events
     * @return integer
     * Used in home dashboard page
     * Return total upcoming events next week
     */
    public function get_upcoming_events_next_week()
    {
        $monday_this_week = date('Y-m-d', strtotime('monday next week'));
        $sunday_this_week = date('Y-m-d', strtotime('sunday next week'));
        $this->db->where("(start BETWEEN '$monday_this_week' and '$sunday_this_week')");
        $this->db->where('(userid = ' . get_staff_user_id() . ' OR public = 1)');

        return $this->db->count_all_results(db_prefix() . 'events');
    }

    /**
     * @param  mixed
     * @return array
     * Used in home dashboard page, currency passed from javascript (undefined or integer)
     * Displays weekly payment statistics (chart)
     */
    public function get_weekly_payments_statistics($currency)
    {
        $all_payments                 = [];
        $has_permission_payments_view = has_permission('payments', '', 'view');
        $this->db->select(db_prefix() . 'invoicepaymentrecords.id, amount,' . db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where('YEARWEEK(tblinvoicepaymentrecords.date) = YEARWEEK(CURRENT_DATE)');
        $this->db->where('' . db_prefix() . 'invoices.status !=', 5);
        if ($currency != 'undefined') {
            $this->db->where('currency', $currency);
        }

        if (!$has_permission_payments_view) {
            $this->db->where('invoiceid IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE addedfrom=' . get_staff_user_id() . ')');
        }

        // Current week
        $all_payments[] = $this->db->get()->result_array();
        $this->db->select(db_prefix() . 'invoicepaymentrecords.id, amount,' . db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where('YEARWEEK(tblinvoicepaymentrecords.date) = YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY) ');

        $this->db->where('' . db_prefix() . 'invoices.status !=', 5);
        if ($currency != 'undefined') {
            $this->db->where('currency', $currency);
        }
        // Last Week
        $all_payments[] = $this->db->get()->result_array();

        $chart = [
            'labels'   => get_weekdays(),
            'datasets' => [
                [
                    'label'           => _l('this_week_payments'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                ],
                [
                    'label'           => _l('last_week_payments'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.5)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                ],
            ],
        ];


        for ($i = 0; $i < count($all_payments); $i++) {
            foreach ($all_payments[$i] as $payment) {
                $payment_day = date('l', strtotime($payment['date']));
                $x           = 0;
                foreach (get_weekdays_original() as $day) {
                    if ($payment_day == $day) {
                        $chart['datasets'][$i]['data'][$x] += $payment['amount'];
                    }
                    $x++;
                }
            }
        }

        return $chart;
    }

    public function projects_status_stats()
    {
        $this->load->model('projects_model');
        $statuses = $this->projects_model->get_project_statuses();
        $colors   = get_system_favourite_colors();

        $chart = [
            'labels'   => [],
            'datasets' => [],
        ];

        $_data                         = [];
        $_data['data']                 = [];
        $_data['backgroundColor']      = [];
        $_data['hoverBackgroundColor'] = [];
        $_data['statusLink']           = [];


        $has_permission = has_permission('projects', '', 'view');
        $sql            = '';
        foreach ($statuses as $status) {
            $sql .= ' SELECT COUNT(*) as total';
            $sql .= ' FROM ' . db_prefix() . 'projects';
            $sql .= ' WHERE status=' . $status['id'];
            if (!$has_permission) {
                $sql .= ' AND id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
            }
            $sql .= ' UNION ALL ';
            $sql = trim($sql);
        }

        $result = [];
        if ($sql != '') {
            // Remove the last UNION ALL
            $sql    = substr($sql, 0, -10);
            $result = $this->db->query($sql)->result();
        }

        foreach ($statuses as $key => $status) {
            array_push($_data['statusLink'], admin_url('projects?status=' . $status['id']));
            array_push($chart['labels'], $status['name']);
            array_push($_data['backgroundColor'], $status['color']);
            array_push($_data['hoverBackgroundColor'], adjust_color_brightness($status['color'], -20));
            array_push($_data['data'], $result[$key]->total);
        }

        $chart['datasets'][]           = $_data;
        $chart['datasets'][0]['label'] = _l('home_stats_by_project_status');

        return $chart;
    }

    public function leads_status_stats()
    {
        $chart = [
            'labels'   => [],
            'datasets' => [],
        ];

        $_data                         = [];
        $_data['data']                 = [];
        $_data['backgroundColor']      = [];
        $_data['hoverBackgroundColor'] = [];
        $_data['statusLink']           = [];

        $result = get_leads_summary();

        foreach ($result as $status) {
            if ($status['color'] == '') {
                $status['color'] = '#737373';
            }
            array_push($chart['labels'], $status['name']);
            array_push($_data['backgroundColor'], $status['color']);
            if (!isset($status['junk']) && !isset($status['lost'])) {
                array_push($_data['statusLink'], admin_url('leads?status=' . $status['id']));
            }
            array_push($_data['hoverBackgroundColor'], adjust_color_brightness($status['color'], -20));
            array_push($_data['data'], $status['total']);
        }

        $chart['datasets'][] = $_data;

        return $chart;
    }

    /**
     * Display total tickets awaiting reply by department (chart)
     * @return array
     */
    public function tickets_awaiting_reply_by_department()
    {
        $this->load->model('departments_model');
        $departments = $this->departments_model->get();
        $colors      = get_system_favourite_colors();
        $chart       = [
            'labels'   => [],
            'datasets' => [],
        ];

        $_data                         = [];
        $_data['data']                 = [];
        $_data['backgroundColor']      = [];
        $_data['hoverBackgroundColor'] = [];

        $i = 0;
        foreach ($departments as $department) {
            if (!is_admin()) {
                if (get_option('staff_access_only_assigned_departments') == 1) {
                    $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                    $departments_ids      = [];
                    if (count($staff_deparments_ids) == 0) {
                        $departments = $this->departments_model->get();
                        foreach ($departments as $department) {
                            array_push($departments_ids, $department['departmentid']);
                        }
                    } else {
                        $departments_ids = $staff_deparments_ids;
                    }
                    if (count($departments_ids) > 0) {
                        $this->db->where('department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                    }
                }
            }
            $this->db->where_in('status', [
                1,
                2,
                4,
            ]);

            $this->db->where('department', $department['departmentid']);
            $total = $this->db->count_all_results(db_prefix() . 'tickets');

            if ($total > 0) {
                $color = '#333';
                if (isset($colors[$i])) {
                    $color = $colors[$i];
                }
                array_push($chart['labels'], $department['name']);
                array_push($_data['backgroundColor'], $color);
                array_push($_data['hoverBackgroundColor'], adjust_color_brightness($color, -20));
                array_push($_data['data'], $total);
            }
            $i++;
        }

        $chart['datasets'][] = $_data;

        return $chart;
    }

    /**
     * Display total tickets awaiting reply by status (chart)
     * @return array
     */
    public function tickets_awaiting_reply_by_status()
    {
        $this->load->model('tickets_model');
        $statuses             = $this->tickets_model->get_ticket_status();
        $_statuses_with_reply = [
            1,
            2,
            4,
        ];

        $chart = [
            'labels'   => [],
            'datasets' => [],
        ];

        $_data                         = [];
        $_data['data']                 = [];
        $_data['backgroundColor']      = [];
        $_data['hoverBackgroundColor'] = [];
        $_data['statusLink']           = [];

        foreach ($statuses as $status) {
            if (in_array($status['ticketstatusid'], $_statuses_with_reply)) {
                if (!is_admin()) {
                    if (get_option('staff_access_only_assigned_departments') == 1) {
                        $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                        $departments_ids      = [];
                        if (count($staff_deparments_ids) == 0) {
                            $departments = $this->departments_model->get();
                            foreach ($departments as $department) {
                                array_push($departments_ids, $department['departmentid']);
                            }
                        } else {
                            $departments_ids = $staff_deparments_ids;
                        }
                        if (count($departments_ids) > 0) {
                            $this->db->where('department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                        }
                    }
                }

                $this->db->where('status', $status['ticketstatusid']);
                $total = $this->db->count_all_results(db_prefix() . 'tickets');
                if ($total > 0) {
                    array_push($chart['labels'], ticket_status_translate($status['ticketstatusid']));
                    array_push($_data['statusLink'], admin_url('tickets/index/' . $status['ticketstatusid']));
                    array_push($_data['backgroundColor'], $status['statuscolor']);
                    array_push($_data['hoverBackgroundColor'], adjust_color_brightness($status['statuscolor'], -20));
                    array_push($_data['data'], $total);
                }
            }
        }

        $chart['datasets'][] = $_data;

        return $chart;
    }
    
    public function dailyCallsTracker()
    {
        
        
        
//  $fromDate = $_POST['from'];
//  $toDate = $_POST['to'];
// $yesterdaySql = "";

// $yesterdaySql = "";
// $yesterday = date('Y-m-d', strtotime($fromDate . ' -1 day'));
// if ($fromDate == date('Y-m-d') && $toDate == date('Y-m-d')) {

//     $yesterdaySql = "
//     'yesterday_range',
//     (
//         SELECT JSON_OBJECT(
//             'total_leads', COUNT(DISTINCT lead_id),
//             'answered_leads', SUM(has_answered),
//             'duration', SUM(total_duration)
//         )
//         FROM (
//             SELECT
//                 lead_id,
//                 MAX(call_status = 'answered') AS has_answered,
//                 SUM(duration) AS total_duration
//             FROM (

//                 SELECT 
//                     l.id AS lead_id,
//                     c.call_status,
//                     c.duration
//                 FROM tblcalls_activity_logs c
//                 JOIN tblleads l 
//                     ON (l.phonenumber = c.contact AND adjusted_call_start = '$yesterday')
//                 WHERE l.update_count > 0
//                   AND adjusted_call_start = '$yesterday'

//                 UNION ALL

//                 SELECT 
//                     l.id AS lead_id,
//                     c.call_status,
//                     c.duration
//                 FROM tblcalls_activity_logs c
//                 JOIN tblleads l 
//                     ON (l.alternative_phonenumber = c.contact AND adjusted_call_start = '$yesterday')
//                 WHERE l.update_count > 0 
//                   AND l.alternative_phonenumber IS NOT NULL 
//                   AND l.alternative_phonenumber <> ''
//                   AND adjusted_call_start = '$yesterday'

//             ) x
//             GROUP BY lead_id
//         ) t
//     ),
//     ";
// }

// $sql = "
// WITH base_calls AS (
//     SELECT 
//         l.id AS lead_id,
//         l.status,
//         l.update_count,
//         c.id AS call_id,
//         c.call_status,
//         c.duration,
//         c.call_start,
//         sf.staffid AS staff_id,
//         sf.role AS role_id,
//         sf.lead_type as staff_lead_type,
//         sf.department department,
//         CONCAT(sf.firstname, ' ', sf.lastname) AS staff_name,
//         HOUR(FROM_UNIXTIME(c.call_start + 19800)) AS hour_slot,
//         ss.name AS status_name,
//         ss.color
//     FROM tblcalls_activity_logs c
//     JOIN tblleads l 
//         ON l.phonenumber = c.contact
//     LEFT JOIN tblleads_status ss 
//         ON ss.id = l.status
//     LEFT JOIN tblstaff sf 
//         ON sf.staffid = l.assigned
//     WHERE l.update_count > 0
//       AND c.adjusted_call_start BETWEEN '$fromDate' AND '$toDate'

//     UNION ALL

//     SELECT 
//         l.id AS lead_id,
//         l.status,
//         l.update_count,
//         c.id AS call_id,
//         c.call_status,
//         c.duration,
//         c.call_start,
//         sf.staffid AS staff_id,
//         sf.role AS role_id,
//         sf.lead_type as staff_lead_type,
//         sf.department department,
//         CONCAT(sf.firstname, ' ', sf.lastname) AS staff_name,
//         HOUR(FROM_UNIXTIME(c.call_start + 19800)) AS hour_slot,
//         ss.name AS status_name,
//         ss.color
//     FROM tblcalls_activity_logs c
//     JOIN tblleads l 
//         ON l.alternative_phonenumber = c.contact
//     LEFT JOIN tblleads_status ss 
//         ON ss.id = l.status
//     LEFT JOIN tblstaff sf 
//         ON sf.staffid = l.assigned
//     WHERE l.update_count > 0 
//       AND l.alternative_phonenumber IS NOT NULL 
//       AND l.alternative_phonenumber <> ''
//       AND c.adjusted_call_start BETWEEN '$fromDate' AND '$toDate'
// )

// SELECT JSON_OBJECT(

// 'total_range',
// (
//     SELECT JSON_OBJECT(
//         'total_leads', COUNT(DISTINCT lead_id),
//         'answered_leads', SUM(has_answered),
//         'duration', SUM(total_duration)
//     )
//     FROM (
//         SELECT
//             lead_id,
//             MAX(call_status = 'answered') AS has_answered,
//             SUM(duration) AS total_duration
//         FROM base_calls
//         GROUP BY lead_id
//     ) t
// ),

// ".$yesterdaySql."
// 'hourly_data',
// (
//     SELECT JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'hour_slot', hour_slot,
//             'total_leads', total_leads,
//             'total_duration', total_duration
//         )
//     )
//     FROM (
//         SELECT
//             hour_slot,
//             COUNT(DISTINCT lead_id) AS total_leads,
//             SUM(duration) AS total_duration
//         FROM base_calls
//         WHERE hour_slot BETWEEN 9 AND 21
//         GROUP BY hour_slot
//         ORDER BY hour_slot
//     ) h
// ),

// 'status_data',
// (
//     SELECT JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'status', status,
//             'status_name', status_name,
//             'color', color,
//             'total_leads', total_leads,
//             'total_duration', total_duration
//         )
//     )
//     FROM (
//         SELECT
//             status,
//             status_name,
//             color,
//             COUNT(DISTINCT lead_id) AS total_leads,
//             SUM(duration) AS total_duration
//         FROM base_calls
//         GROUP BY status
//     ) s
// ),
// 'week_avg_duration',
// (
//     SELECT JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'call_date', call_date,
//             'total_duration', total_duration,
//             'lead_count', lead_count
//         )
//     )
//     FROM (

//         SELECT 
//             DATE(call_date) AS call_date,
//             SUM(duration) AS total_duration,
//             COUNT(DISTINCT id) AS lead_count
//         FROM (

//             SELECT 
//                 c.adjusted_call_start AS call_date,
//                 c.duration,
//                 l.id
//             FROM tblcalls_activity_logs c
//             JOIN tblleads l 
//                 ON l.phonenumber = c.contact
//             WHERE l.update_count > 0
//               AND c.call_status = 'answered'
//               AND c.adjusted_call_start BETWEEN DATE_SUB('$fromDate', INTERVAL 6 DAY) AND '$fromDate'

//             UNION ALL

//             SELECT 
//                 c.adjusted_call_start AS call_date,
//                 c.duration,
//                 l.id
//             FROM tblcalls_activity_logs c
//             JOIN tblleads l 
//                 ON l.alternative_phonenumber = c.contact
//             WHERE l.update_count > 0
//               AND l.alternative_phonenumber IS NOT NULL
//               AND l.alternative_phonenumber <> ''
//               AND c.call_status = 'answered'
//               AND c.adjusted_call_start BETWEEN DATE_SUB('$fromDate', INTERVAL 14 DAY) AND '$fromDate'

//         ) x

//         GROUP BY DATE(call_date)
//         ORDER BY DATE(call_date)

//     ) final_data
// ),

// 'top_leads',
// (
//     SELECT JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'staff_id', staff_id,
//             'staff_name', staff_name,
//             'total_leads', total_leads
//         )
//     )
//     FROM (
//         SELECT 
//             staff_id,
//             staff_name,
//             COUNT(DISTINCT lead_id) AS total_leads
//         FROM base_calls
//         GROUP BY staff_id
//         ORDER BY total_leads DESC
//         LIMIT 5
//     ) t1
// ),

// 'top_duration',
// (
//     SELECT JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'staff_id', staff_id,
//             'staff_name', staff_name,
//             'total_duration', total_duration
//         )
//     )
//     FROM (
//         SELECT 
//             staff_id,
//             staff_name,
//             SUM(duration) AS total_duration
//         FROM base_calls
//         GROUP BY staff_id
//         ORDER BY total_duration DESC
//         LIMIT 5
//     ) t2
// ),

// 'top_answered',
// (
//     SELECT JSON_ARRAYAGG(
//         JSON_OBJECT(
//             'staff_id', staff_id,
//             'staff_name', staff_name,
//             'total_answered_calls', total_answered,
//             'total_leads', total_leads
//         )
//     )
//     FROM (
//         SELECT 
//             staff_id,
//             staff_name,
//             COUNT(DISTINCT lead_id) AS total_leads,
//             COUNT(DISTINCT CASE WHEN call_status = 'answered' THEN lead_id END) AS total_answered
//         FROM base_calls
//         WHERE role_id != 3 and department!='' and staff_lead_type!=''
//         GROUP BY staff_id
//         HAVING total_answered > 10 
//           AND total_leads > 10
//         ORDER BY total_answered ASC
//         LIMIT 5
//     ) t3
// )

// ) AS result
// ";




$fromDate = $_POST['from'];
$toDate   = $_POST['to'];

$from = new DateTime($fromDate);
$to   = new DateTime($toDate);

$diff = $from->diff($to)->days;

if ($diff > 31) {
    echo json_encode([
        'status' => false,
        'message' => 'Date range should not be greater than 31 days'
    ]);
    exit;
}

$yesterdaySql = "";
$holidays = holiday_list();
 $yesterday = $this->leads_model->getLastWorkingDay($fromDate,$holidays);
 
 
$today = date('Y-m-d');

$cacheKey = 'last_working_day_' . $today;

// Try cache first
$yesterday = $this->cache->get($cacheKey);

if ($yesterday === FALSE) {

    $holidays = holiday_list();

    $yesterday = $this->leads_model->getLastWorkingDay($fromDate, $holidays);

    // Save cache (expire at end of day ~ 86400 seconds)
    $this->cache->save($cacheKey, $yesterday, 86400);
}



$leadSource     = $_POST['view_source'] ?? [];
$leadStatus     = $_POST['view_status'] ?? [];  
$leadType     = $_POST['lead_type'] ?? [];
$department     = $_POST['staff_department'] ?? [];
$office_location= $_POST['office_location'] ?? [];
$staff          = $_POST['staff'] ?? [];

$whereFilters = "";
$whereFilters_staffId="";

/* =======================
   NORMAL FILTERS
======================= */

// Lead Type
if (!empty($leadType)) {
    $leadTypeList = "'" . implode("','", $leadType) . "'";
    $whereFilters .= " AND l.type IN ($leadTypeList)";
}

// Lead Source
if (!empty($leadSource)) {
    $leadSourceList = "'" . implode("','", $leadSource) . "'";
    $whereFilters .= " AND l.source IN ($leadSourceList)";
}

// Lead Status
if (!empty($leadStatus)) {
    $leadStatusList = "'" . implode("','", $leadStatus) . "'";
    $whereFilters .= " AND l.status IN ($leadStatusList)";
}

// Department
if (!empty($department)) {
    $departmentList = "'" . implode("','", $department) . "'";
    $whereFilters .= " AND sf.department IN ($departmentList)";
}

// Office Location
if (!empty($office_location)) {
    $officeList = "'" . implode("','", $office_location) . "'";
    $whereFilters .= " AND sf.office_location IN ($officeList)";
}


/* =======================
   STAFF / PERMISSION LOGIC
======================= */

$get_staff_user_id = get_staff_user_id();
$selectedStaff = $staff;
$idsarr = [];

// Case 1: User selected staff manually
if (!empty($selectedStaff)) {

    $staffList = "'" . implode("','", $selectedStaff) . "'";
    $whereFilters .= " AND c.staffid IN ($staffList)";
    $whereFilters_staffId  .= " AND staffid IN ($staffList)";

} else {

    // Case 2: Not admin → apply restriction
    if (!is_admin()) {

        $role = $this->db
            ->where('staffid', $get_staff_user_id)
            ->get(db_prefix() . 'staff')
            ->row()
            ->role;

        // Manager → get team
        if ($role == 3) {

            $teamids = $this->db
                ->query('CALL GetReportingPersons(?)', [$get_staff_user_id])
                ->result_array();

            $this->db->close();
            $this->db->initialize();

            $idsarr = array_column($teamids, 'staffid');
        }

        // Apply filter
        if (!empty($idsarr)) {

            $allIds = array_merge($idsarr, [$get_staff_user_id]);
            $staffList = "'" . implode("','", $allIds) . "'";
            $whereFilters .= " AND c.staffid IN ($staffList)";
            
            $whereFilters_staffId  .= " AND staffid IN ($staffList)";

        } else {

            $whereFilters .= " AND c.staffid = '$get_staff_user_id'";
             $whereFilters_staffId  .= " AND staffid ='$get_staff_user_id' ";
        }
    }

    // Admin → no filter
}
// $yesterday = date('Y-m-d', strtotime($fromDate . ' -1 day'));

// $yesterday = "2026-04-11";
if ($fromDate == date('Y-m-d') && $toDate == date('Y-m-d')) {

$yesterdaySql = "
'yesterday_range',
(
    SELECT JSON_OBJECT(
        'total_leads', COUNT(*),
        'answered_leads', SUM(has_answered),
        'duration', SUM(total_duration),
        'callCount', SUM(callCount)
    )
    FROM (
        SELECT
            lead_id,
            MAX(call_status = 'answered') AS has_answered,
            SUM(duration) AS total_duration,
            count(call_id) as callCount 
        FROM (

            SELECT 
                l.id AS lead_id,
                c.call_status,
                c.duration,
                c.id AS call_id
            FROM (
                SELECT contact, call_status, duration,id,staffid
                FROM tblcalls_activity_logs
                WHERE adjusted_call_start = '$yesterday'
            ) c
            JOIN tblleads l 
                ON l.phonenumber = c.contact
                AND l.lastupdate_date > DATE_SUB('$yesterday', INTERVAL 1 DAY)
            LEFT JOIN tblstaff sf ON sf.staffid = c.staffid
            WHERE l.update_count > 0 ".$whereFilters."

            UNION ALL

            SELECT 
                l.id,
                c.call_status,
                c.duration,
                  c.id AS call_id
            FROM (
                SELECT contact, call_status, duration,id,staffid
                FROM tblcalls_activity_logs
                WHERE adjusted_call_start = '$yesterday'
            ) c
            JOIN tblleads l 
                ON l.alternative_phonenumber = c.contact
                AND l.lastupdate_date > DATE_SUB('$yesterday', INTERVAL 1 DAY)
                AND l.alternative_phonenumber IS NOT NULL
                AND l.alternative_phonenumber <> ''
            LEFT JOIN tblstaff sf ON sf.staffid = c.staffid
            WHERE l.update_count > 0 ".$whereFilters."

        ) x
        GROUP BY lead_id
    ) t
),
";
}

$sql = "
WITH filtered_calls AS (
    SELECT 
    id,
        contact, 
        call_status, 
        duration, 
        call_start,
        adjusted_call_start,
        staffid,
        HOUR(FROM_UNIXTIME(call_start + 19800)) AS hour_slot
    FROM tblcalls_activity_logs
    WHERE adjusted_call_start BETWEEN '$fromDate' AND '$toDate'
    $whereFilters_staffId
),

base_calls AS (
    SELECT 
        l.id AS lead_id,
        l.status,
        ss.name AS status_name,
        ss.color,
         c.id As call_id,
        c.call_status,
        c.duration,
        c.hour_slot,
        c.adjusted_call_start,
        c.staffid AS staffid,
        CONCAT(sf.firstname, ' ', sf.lastname) AS staff_name,
        sf.role AS role_id,
        sf.department,
        sf.lead_type AS staff_lead_type
    FROM filtered_calls c
    JOIN tblleads l 
        ON l.phonenumber = c.contact
        AND l.lastupdate_date > DATE_SUB('$fromDate', INTERVAL 1 DAY)
  
    LEFT JOIN tblleads_status ss ON ss.id = l.status
    LEFT JOIN tblstaff sf ON sf.staffid = c.staffid
    WHERE l.update_count > 0 ".$whereFilters." 

    UNION ALL

    SELECT 
        l.id AS lead_id,
        l.status,
        ss.name AS status_name,
        ss.color,
        c.id As call_id,
        c.call_status,
        c.duration,
        c.hour_slot,
        c.adjusted_call_start,
        c.staffid AS staffid,
        CONCAT(sf.firstname, ' ', sf.lastname) AS staff_name,
        sf.role AS role_id,
        sf.department,
        sf.lead_type AS staff_lead_type
    FROM filtered_calls c
    JOIN tblleads l 
        ON l.alternative_phonenumber = c.contact
        AND l.lastupdate_date > DATE_SUB('$fromDate', INTERVAL 1 DAY)
        AND l.alternative_phonenumber IS NOT NULL
        AND l.alternative_phonenumber <> ''

    LEFT JOIN tblleads_status ss ON ss.id = l.status
    LEFT JOIN tblstaff sf ON sf.staffid = c.staffid
    WHERE l.update_count > 0 ".$whereFilters." 
)

SELECT JSON_OBJECT(

'total_range',
(
    SELECT JSON_OBJECT(
        'total_leads', COUNT(DISTINCT lead_id),
        'answered_leads', SUM(has_answered),
        'duration', SUM(total_duration),
        'callCount', count(callCount)
    )
    FROM (
        SELECT
            lead_id,
            MAX(call_status = 'answered') AS has_answered,
            SUM(duration) AS total_duration,
            COUNT(DISTINCT call_id) AS callCount
        FROM base_calls
        GROUP BY call_id
    ) t
),

".$yesterdaySql."

'hourly_data',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'hour_slot', hour_slot,
            'total_leads', total_leads,
            'total_duration', total_duration
        )
    )
    FROM (
        SELECT
            hour_slot,
            COUNT(lead_id) AS total_leads,
            SUM(duration) AS total_duration
        FROM base_calls
        WHERE hour_slot BETWEEN 9 AND 21
        GROUP BY hour_slot
        ORDER BY hour_slot
    ) h
),

'counsellor_stats',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_calls', total_calls,
            'total_connected', total_connected
        )
    )
    FROM (
        SELECT
            staffid,
            staff_name,
            COUNT(*) AS total_calls,
            SUM(CASE 
                WHEN call_status = 'answered' THEN 1 
                ELSE 0 
            END) AS total_connected

        FROM base_calls
        GROUP BY staffid, staff_name
        ORDER BY total_calls DESC limit 20
    ) cs
),
'status_data',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'status', status,
            'status_name', status_name,
            'color', color,
            'total_leads', total_leads,
            'total_duration', total_duration
        )
    )
    FROM (
        SELECT
            status,
            status_name,
            color,
            COUNT(DISTINCT lead_id) AS total_leads,
            SUM(duration) AS total_duration
        FROM base_calls
        GROUP BY status
    ) s
),

'week_avg_duration',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'call_date', call_date,
            'total_duration', total_duration,
            'lead_count', lead_count
        )
    )
    FROM (

        SELECT 
            DATE(call_date) AS call_date,
            SUM(duration) AS total_duration,
            COUNT(DISTINCT id) AS lead_count
        FROM (

            SELECT 
                c.adjusted_call_start AS call_date,
                c.duration,
                l.id
            FROM tblcalls_activity_logs c
            JOIN tblleads l 
                ON l.phonenumber = c.contact and l.lastupdate_date > DATE_SUB('$fromDate', INTERVAL 15 DAY)
                 LEFT JOIN tblstaff sf ON sf.staffid = c.staffid
            WHERE l.update_count > 0
              AND c.call_status = 'answered'
              AND c.adjusted_call_start BETWEEN DATE_SUB('$fromDate', INTERVAL 6 DAY) AND '$fromDate' ".$whereFilters."

            UNION ALL

            SELECT 
                c.adjusted_call_start AS call_date,
                c.duration,
                l.id
            FROM tblcalls_activity_logs c
            JOIN tblleads l 
                ON l.alternative_phonenumber = c.contact and l.lastupdate_date > DATE_SUB('$fromDate', INTERVAL 15 DAY)
                 LEFT JOIN tblstaff sf ON sf.staffid = c.staffid
            WHERE l.update_count > 0
              AND l.alternative_phonenumber IS NOT NULL
              AND l.alternative_phonenumber <> ''
              AND c.call_status = 'answered'
              AND c.adjusted_call_start BETWEEN DATE_SUB('$fromDate', INTERVAL 6 DAY) AND '$fromDate' ".$whereFilters."

        ) x

        GROUP BY DATE(call_date)
        ORDER BY DATE(call_date)

    ) final_data
),
'top_leads',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_leads', total_leads
        )
    )
    FROM (
        SELECT 
            staffid,
            staff_name,
            COUNT( lead_id) AS total_leads
        FROM base_calls
        GROUP BY staffid
        ORDER BY total_leads DESC
        LIMIT 5
    ) t1
),
'least_leads',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_leads', total_leads
        )
    )
    FROM (
        SELECT 
            staffid,
            staff_name,
            COUNT( lead_id) AS total_leads
        FROM base_calls
        GROUP BY staffid
        ORDER BY total_leads ASC
        LIMIT 5
    ) t1
),
'top_duration',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_duration', total_duration
        )
    )
    FROM (
        SELECT 
            staffid,
            staff_name,
            SUM(duration) AS total_duration
        FROM base_calls
        GROUP BY staffid
        ORDER BY total_duration DESC
        LIMIT 5
    ) t2
),
'least_duration',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_duration', total_duration
        )
    )
    FROM (
        SELECT 
            staffid,
            staff_name,
            SUM(duration) AS total_duration
        FROM base_calls
        GROUP BY staffid
        ORDER BY total_duration ASC
        LIMIT 5
    ) t2
),
'top_answered',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_answered_calls', total_answered,
            'total_leads', total_leads
        )
    )
    FROM (
        SELECT 
            staffid,
            staff_name,
            COUNT(DISTINCT lead_id) AS total_leads,
            COUNT(DISTINCT CASE WHEN call_status = 'answered' THEN lead_id END) AS total_answered
        FROM base_calls
        WHERE 1=1
          AND department != ''
          AND staff_lead_type != ''
        GROUP BY staffid
    
        ORDER BY total_answered DESC
        LIMIT 5
    ) t3
),
'least_answered',
(
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'staff_id', staffid,
            'staff_name', staff_name,
            'total_answered_calls', total_answered,
            'total_leads', total_leads
        )
    )
    FROM (
        SELECT 
            staffid,
            staff_name,
            COUNT(DISTINCT lead_id) AS total_leads,
            COUNT(DISTINCT CASE WHEN call_status = 'answered' THEN lead_id END) AS total_answered
        FROM base_calls
        WHERE 1=1
          AND department != ''
          AND staff_lead_type != ''
        GROUP BY staffid
    
        ORDER BY total_answered ASC
        LIMIT 5
    ) t3
)

) AS result
";



    // HAVING total_answered > 10 
    //       AND total_leads > 10
   
 
       return $this->db->query($sql)->row();
    }
    
    public function dailyCallsTracker_Datatable()
    {
            $fromDate = $_POST['from'];
    $toDate   = $_POST['to'];
    
    $from = new DateTime($fromDate);
$to   = new DateTime($toDate);

$diff = $from->diff($to)->days;

if ($diff > 31) {
    echo json_encode([
        'status' => false,
        'message' => 'Date range should not be greater than 31 days'
    ]);
    exit;
}

    // DataTables params
    $start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
    
    
 
$leadSource     = $_POST['view_source'] ?? [];
$leadStatus     = $_POST['view_status'] ?? [];  
$leadType     = $_POST['lead_type'] ?? [];
$department     = $_POST['staff_department'] ?? [];
$office_location= $_POST['office_location'] ?? [];
$staff          = $_POST['staff'] ?? [];

$whereFilters = "";
$whereFilters_assigned = "";

/* =======================
   NORMAL FILTERS
======================= */

// Lead Type
if (!empty($leadType)) {
    $leadTypeList = "'" . implode("','", $leadType) . "'";
    $whereFilters .= " AND l.type IN ($leadTypeList)";
}

// Lead Source
if (!empty($leadSource)) {
    $leadSourceList = "'" . implode("','", $leadSource) . "'";
    $whereFilters .= " AND l.source IN ($leadSourceList)";
}

// Lead Status
if (!empty($leadStatus)) {
    $leadStatusList = "'" . implode("','", $leadStatus) . "'";
    $whereFilters .= " AND l.status IN ($leadStatusList)";
}

// Department
if (!empty($department)) {
    $departmentList = "'" . implode("','", $department) . "'";
    $whereFilters .= " AND sf.department IN ($departmentList)";
}

// Office Location
if (!empty($office_location)) {
    $officeList = "'" . implode("','", $office_location) . "'";
    $whereFilters .= " AND sf.office_location IN ($officeList)";
}


/* =======================
   STAFF / PERMISSION LOGIC
======================= */

$get_staff_user_id = get_staff_user_id();
$selectedStaff = $staff;
$idsarr = [];

// Case 1: User selected staff manually
if (!empty($selectedStaff)) {

    $staffList = "'" . implode("','", $selectedStaff) . "'";
    $whereFilters .= " AND c.staffid IN ($staffList)";
    $whereFilters_assigned .= " AND staffid IN ($staffList)";

} else {

    // Case 2: Not admin → apply restriction
    if (!is_admin()) {

        $role = $this->db
            ->where('staffid', $get_staff_user_id)
            ->get(db_prefix() . 'staff')
            ->row()
            ->role;

        // Manager → get team
        if ($role == 3) {

            $teamids = $this->db
                ->query('CALL GetReportingPersons(?)', [$get_staff_user_id])
                ->result_array();

            $this->db->close();
            $this->db->initialize();

            $idsarr = array_column($teamids, 'staffid');
        }

        // Apply filter
        if (!empty($idsarr)) {

            $allIds = array_merge($idsarr, [$get_staff_user_id]);
            $staffList = "'" . implode("','", $allIds) . "'";
            $whereFilters .= " AND c.staffid IN ($staffList)";
            $whereFilters_assigned .= " AND staffid IN ($staffList)";

        } else {

            $whereFilters .= " AND c.staffid = '$get_staff_user_id'";
            $whereFilters_assigned .= " AND staffid = '$get_staff_user_id' ";
        }
    }

    // Admin → no filter
}

    $sql = "
    SELECT 
        staff_name,
        COUNT(*) AS total_calls,
        SUM(call_status = 'answered') AS answered_calls,
        SUM(CASE WHEN call_status = 'answered' THEN duration ELSE 0 END) AS call_duration
    FROM (
        SELECT 
            CONCAT(sf.firstname, ' ', sf.lastname) AS staff_name,
            c.call_status,
            c.duration,
            c.id,
            c.staffid
        FROM (
            SELECT contact, call_status, duration,id,staffid
            FROM tblcalls_activity_logs
            WHERE adjusted_call_start BETWEEN '$fromDate' AND '$toDate' $whereFilters_assigned
        ) c
        JOIN tblleads l 
            ON l.phonenumber = c.contact
            AND l.lastupdate_date > DATE_SUB('$fromDate', INTERVAL 1 DAY)
            
        JOIN tblstaff sf 
            ON c.staffid = sf.staffid
        WHERE l.update_count > 0 $whereFilters

        UNION ALL

        SELECT 
            CONCAT(sf.firstname, ' ', sf.lastname),
            c.call_status,
            c.duration,
            c.id,
            c.staffid
        FROM (
            SELECT contact, call_status, duration,id,staffid
            FROM tblcalls_activity_logs
            WHERE adjusted_call_start BETWEEN '$fromDate' AND '$toDate' $whereFilters_assigned
        ) c
        JOIN tblleads l 
            ON l.alternative_phonenumber = c.contact
            AND l.lastupdate_date > DATE_SUB('$fromDate', INTERVAL 1 DAY)
            
            AND l.alternative_phonenumber IS NOT NULL
            AND l.alternative_phonenumber <> ''
        JOIN tblstaff sf 
            ON c.staffid = sf.staffid
        WHERE l.update_count > 0 $whereFilters

    ) final

    GROUP BY staff_name
    ORDER BY total_calls DESC
    
    "; 
  
  
      
        return $this->db->query($sql)->result_array();
    }   
    
    
    
public function getFollowupDashboard($param = [])
{
   
   
   $get_staff_user_id = get_staff_user_id();
$selectedStaff = $staff;
$idsarr = [];
$whereFilters="";
 $staffWhere = "";

// Case 1: User selected staff manually
if (!empty($selectedStaff)) {

    $staffList = "'" . implode("','", $selectedStaff) . "'";
    $staffWhere .= " AND r.creator IN ($staffList)";

} else {

    // Case 2: Not admin → apply restriction
    if (!is_admin()) {

        $role = $this->db
            ->where('staffid', $get_staff_user_id)
            ->get(db_prefix() . 'staff')
            ->row()
            ->role;

        // Manager → get team
        if ($role == 3) {

            $teamids = $this->db
                ->query('CALL GetReportingPersons(?)', [$get_staff_user_id])
                ->result_array();

            $this->db->close();
            $this->db->initialize();

            $idsarr = array_column($teamids, 'staffid');
        }

        // Apply filter
        if (!empty($idsarr)) {

            $allIds = array_merge($idsarr, [$get_staff_user_id]);
            $staffList = "'" . implode("','", $allIds) . "'";
            $staffWhere .= " AND r.creator IN ($staffList)";
            

        } else {

            $staffWhere .= " AND r.creator = '$get_staff_user_id'";
        }
    }

    // Admin → no filter
}

     $currentDate = !empty($param["follow_up_date"])
        ? $this->db->escape_str($param["follow_up_date"])
        : date('Y-m-d');

    $start_date = !empty($param['missed_from_date'])
        ? $this->db->escape_str($param['missed_from_date'])
        : date('Y-m-01');

    $end_date = !empty($param['missed_to_date'])
        ? $this->db->escape_str($param['missed_to_date'])
        : date('Y-m-d', strtotime('-1 day'));

    // Staff filter
   
    if (!empty($param['staff'])) {
        $assigned = implode(",", array_map('intval', $param['staff']));
        $staffWhere = " AND r.creator IN ($assigned) ";
    }

    // Status filter
    $statusWhere = "";
    if (!empty($param['view_status'])) {
        $status = implode(",", array_map('intval', $param['view_status']));
        $statusWhere = " AND l.status IN ($status) ";
    }

    // Source filter
    $sourceWhere = "";
    if (!empty($param['view_source'])) {
        $source = implode(",", array_map('intval', $param['view_source']));
        $sourceWhere = " AND l.source IN ($source) ";
    }

    // Lead type filter
    $typeWhere = "";
    if (!empty($param['lead_type'])) {
        $type = implode(",", array_map('intval', $param['lead_type']));
        $typeWhere = " AND l.type IN ($type) ";
    }

    // Department filter
    $deptJoin = "";
    $deptWhere = "";
    $locWhere = "";
    if (!empty($param['staff_department']) || !empty($param['office_location'])) {
        $deptJoin = " JOIN tblstaff stf ON stf.staffid = l.assigned ";
        if (!empty($param['staff_department'])) {
            $deptWhere = " AND stf.department IN ('" . implode("','", $this->db->escape_str($param['staff_department'])) . "') ";
        }
        if (!empty($param['office_location'])) {
            $locWhere = " AND stf.office_location IN ('" . implode("','", $this->db->escape_str($param['office_location'])) . "') ";
        }
    }
    


    $commonFilter = $staffWhere . $statusWhere . $sourceWhere . $typeWhere . $deptWhere . $locWhere;



$sql = "
WITH 
 call_data AS (
    SELECT 
        staffid,
        contact,
        MAX(FROM_UNIXTIME(call_start + 19800)) AS connected_date
    FROM tblcalls_activity_logs
    WHERE CHAR_LENGTH(contact) = 10 and adjusted_call_start >= '{$currentDate}'
    GROUP BY staffid,contact
),
note_data AS (
    SELECT 
       rel_id, addedfrom, MAX(dateadded) AS notedate
    FROM tblnotes 
    WHERE rel_type = 'lead' and date(dateadded) >= '{$currentDate}'
    GROUP BY addedfrom,rel_id
),
base_data AS (
    SELECT
        r.id AS reminder_id,
        r.date AS reminder_date,
        DATE(r.date) AS r_date,
        l.id AS lead_id,
        l.status AS lead_status,
        l.assigned,
        l.lastupdate_date,
        r.creator,
        DATE(l.lastupdate_date) AS upd_date,
        CASE
        WHEN cd1.connected_date IS NOT NULL AND cd1.connected_date >= r.date THEN 1
        WHEN cd2.connected_date IS NOT NULL AND cd2.connected_date >= r.date THEN 1
        WHEN n.notedate IS NOT NULL AND n.notedate >= r.date THEN 1
         ELSE 0 
        END AS is_completed,
        CASE WHEN DATE(r.date) = '{$currentDate}' THEN 1 ELSE 0 END AS is_today,
        CASE WHEN DATE(r.date) < '{$currentDate}' THEN 1 ELSE 0 END AS is_past,
        DATEDIFF('{$currentDate}', DATE(r.date)) AS days_overdue
    FROM tblreminders r
    
    JOIN tblleads l ON l.id = r.rel_id AND r.rel_type = 'lead'
    LEFT JOIN call_data cd1 
        ON cd1.contact = l.phonenumber 
       AND cd1.staffid = r.creator
    LEFT JOIN call_data cd2 
        ON cd2.contact = l.alternative_phonenumber 
       AND cd2.staffid = r.creator
       AND l.alternative_phonenumber IS NOT NULL 
       AND TRIM(l.alternative_phonenumber) != ''
    LEFT Join note_data as n on n.addedfrom = r.creator and n.rel_id =l.id
   
    {$deptJoin}
    WHERE r.rel_type = 'lead'
      AND DATE(r.date) <= '{$currentDate}'
      AND l.junk = 0 AND l.lost = 0
      {$commonFilter}
      GROUP by r.id
),

today_data AS (
    SELECT * FROM base_data WHERE is_today = 1
),

funnel_list AS (
    SELECT id, name, status_name, color, bg
    FROM tblfollow_up_funnel
    ORDER BY id ASC
),

staff_avg30 AS (
    SELECT 
        l2.assigned AS staffid,
        ROUND(
            COUNT(CASE WHEN l2.lastupdate_date IS NOT NULL 
                       AND DATE(l2.lastupdate_date) >= DATE(r2.date) THEN 1 END)
            / NULLIF(COUNT(*), 0) * 100, 2
        ) AS avg30
    FROM tblreminders r2
    JOIN tblleads l2 ON l2.id = r2.rel_id AND r2.rel_type = 'lead'
    WHERE r2.rel_type = 'lead'
      AND DATE(r2.date) BETWEEN DATE_SUB('{$currentDate}', INTERVAL 30 DAY) 
                             AND DATE_SUB('{$currentDate}', INTERVAL 1 DAY)
      AND l2.junk = 0 AND l2.lost = 0   ".str_replace("r.","r2.",$staffWhere)."
    GROUP BY l2.assigned
),

staff_trend AS (
    SELECT 
        staffid,
        JSON_ARRAYAGG(daily_pct) AS trend
    FROM (
        SELECT 
            l3.assigned AS staffid,
            DATE(r3.date) AS day_date,
            ROUND(
                COUNT(CASE WHEN l3.lastupdate_date IS NOT NULL 
                           AND DATE(l3.lastupdate_date) >= DATE(r3.date) THEN 1 END)
                / NULLIF(COUNT(*), 0) * 100, 0
            ) AS daily_pct
        FROM tblreminders r3
        JOIN tblleads l3 ON l3.id = r3.rel_id AND r3.rel_type = 'lead'
        WHERE r3.rel_type = 'lead'
          AND DATE(r3.date) BETWEEN DATE_SUB('{$currentDate}', INTERVAL 6 DAY) 
                               AND '{$currentDate}'
          AND l3.junk = 0 AND l3.lost = 0  ".str_replace("r.","r3.",$staffWhere)."
        GROUP BY l3.assigned, DATE(r3.date)
    ) daily
    GROUP BY staffid
),

staff_funnel_raw AS (
    SELECT 
        td.assigned AS staffid,
        st2.follow_up_status AS funnel_id,
        COUNT(DISTINCT td.lead_id) AS cnt
    FROM today_data td
    JOIN tblleads_status st2 ON st2.id = td.lead_status
    WHERE st2.follow_up_status IS NOT NULL
    GROUP BY td.assigned, st2.follow_up_status
),

staff_customer_raw AS (
    SELECT assigned AS staffid, COUNT(DISTINCT lead_id) AS cnt
    FROM today_data
    WHERE lead_status = 1
    GROUP BY assigned
),

staff_funnel_json AS (
    SELECT 
        s.staffid,
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'funnel_id', tpl.fid,
                'funnel_name', tpl.fname,
                'sort_order', tpl.sorder,
                'count', COALESCE(fc.cnt, 0)
            )
        ) AS funnel_counts
    FROM (SELECT DISTINCT assigned AS staffid FROM today_data) s
    CROSS JOIN (
        SELECT id AS fid, name AS fname, 1 AS sorder FROM funnel_list
        UNION ALL
        SELECT 0, 'Customer', 2
    ) tpl
    LEFT JOIN (
        SELECT staffid, funnel_id, cnt FROM staff_funnel_raw
        UNION ALL
        SELECT staffid, 0, cnt FROM staff_customer_raw
    ) fc ON fc.staffid = s.staffid AND fc.funnel_id = tpl.fid
    GROUP BY s.staffid
)

SELECT JSON_OBJECT(

    'data_stus', (
        SELECT JSON_OBJECT(
            'completed', SUM(is_today * is_completed),
            'due', SUM(is_today * (1 - is_completed)),
            'missed', SUM(is_past * (1 - is_completed)),
            'total_today', SUM(is_today),
            'completion_percentage', ROUND(
                SUM(is_today * is_completed)
                / NULLIF(SUM(is_today), 0) * 100, 2
            )
        )
        FROM base_data
    ),

    'funnel_data', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'funnel_id', f.id,
                'funnel_name', f.name,
                'statusName', f.status_name,
                'color', f.color,
                'bg', f.bg,
                'status_id', st.id,
                'status_name', st.name,
                'status_color', IFNULL(st.color, '#737985'),
                'lead_count', sub.lead_count,
                'due_count', sub.due_count
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                st2.id AS status_id,
                st2.follow_up_status AS funnel_id,
                COUNT(DISTINCT td.lead_id) AS lead_count,
                COUNT(DISTINCT IF(td.is_completed = 0, td.lead_id, NULL)) AS due_count
            FROM today_data td
            JOIN tblleads_status st2 ON st2.id = td.lead_status
            GROUP BY st2.id, st2.follow_up_status
        ) sub
        JOIN tblleads_status st ON st.id = sub.status_id
        JOIN tblfollow_up_funnel f ON f.id = sub.funnel_id
        ORDER BY f.id ASC, sub.lead_count DESC
    ),

    'counsollor', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'staffid', c.staffid,
                'counsellor_name', c.counsellor_name,
                'total_followups', c.total_followups,
                'completed', c.completed,
                'due', c.due,
                'overdue', c.overdue,
                'completion_percentage', c.completion_percentage,
                'avg30', c.avg30,
                'trend', c.trend,
                'funnel_counts', c.funnel_counts
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                s.staffid,
                CONCAT(s.firstname, ' ', s.lastname) AS counsellor_name,
                SUM(bd.is_today) AS total_followups,
                SUM(bd.is_today * bd.is_completed) AS completed,
                SUM(bd.is_today * (1 - bd.is_completed)) AS due,
                SUM(bd.is_past * (1 - bd.is_completed)) AS overdue,
                ROUND(
                    SUM(bd.is_today * bd.is_completed)
                    / NULLIF(SUM(bd.is_today), 0) * 100, 2
                ) AS completion_percentage,
                COALESCE(sa.avg30, 0) AS avg30,
                COALESCE(st2.trend, JSON_ARRAY()) AS trend,
                COALESCE(sf.funnel_counts, JSON_ARRAY()) AS funnel_counts
            FROM base_data bd
            JOIN tblstaff s              ON s.staffid = bd.assigned
            LEFT JOIN staff_avg30 sa     ON sa.staffid = s.staffid
            LEFT JOIN staff_trend st2    ON st2.staffid = s.staffid
            LEFT JOIN staff_funnel_json sf ON sf.staffid = s.staffid
            GROUP BY s.staffid, s.firstname, s.lastname,
                     sa.avg30, st2.trend, sf.funnel_counts
            HAVING total_followups > 0
            ORDER BY completion_percentage ASC
        ) AS c
    ),

    'at_risk_counsellors', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'staffid', staffid,
                'counsellor_name', counsellor_name,
                'total_followups', total_followups,
                'completed', completed,
                'completion_percentage', completion_percentage
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                s.staffid,
                CONCAT(s.firstname, ' ', s.lastname) AS counsellor_name,
                COUNT(*) AS total_followups,
                SUM(td.is_completed) AS completed,
                ROUND(SUM(td.is_completed) / COUNT(*) * 100, 2) AS completion_percentage
            FROM today_data td
            JOIN tblstaff s ON s.staffid = td.assigned
            GROUP BY s.staffid
            HAVING completion_percentage < 50
            ORDER BY completion_percentage ASC
        ) AS bottom3
    ),

    'missed_range', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'staffid', staffid,
                'counsellor_name', counsellor_name,
                'total_reminders', total_reminders,
                'missed', missed
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                s.staffid,
                CONCAT(s.firstname, ' ', s.lastname) AS counsellor_name,
                COUNT(*) AS total_reminders,
                SUM(1 - bd.is_completed) AS missed
            FROM base_data bd
            JOIN tblstaff s ON s.staffid = bd.creator
            WHERE bd.r_date BETWEEN '{$start_date}' AND '{$end_date}'
              AND bd.is_past = 1 ".str_replace("r.","bd.",$staffWhere)."
            GROUP BY s.staffid
            HAVING missed > 0
            ORDER BY missed DESC
            LIMIT 5
        ) AS missed_data
    ),

    'missed_range_least', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'staffid', staffid,
                'counsellor_name', counsellor_name,
                'total_reminders', total_reminders,
                'missed', missed
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                s.staffid,
                CONCAT(s.firstname, ' ', s.lastname) AS counsellor_name,
                COUNT(*) AS total_reminders,
                SUM(1 - bd.is_completed) AS missed
            FROM base_data bd
            JOIN tblstaff s ON s.staffid = bd.creator
            WHERE bd.r_date BETWEEN '{$start_date}' AND '{$end_date}'
              AND bd.is_past = 1 ".str_replace("r.","bd.",$staffWhere)."
            GROUP BY s.staffid
            HAVING total_reminders > 0
            ORDER BY missed ASC
            LIMIT 5
        ) AS missed_data
    ),

    'overdue_aging', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'age_label', age_label,
                'lead_count', lead_count
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                CASE
                    WHEN days_overdue = 0 THEN 'Due today'
                    WHEN days_overdue = 1 THEN '1 day overdue'
                    WHEN days_overdue = 2 THEN '2 days overdue'
                    WHEN days_overdue = 3 THEN '3 days overdue'
                    ELSE '4+ days overdue'
                END AS age_label,
                CASE
                    WHEN days_overdue = 0 THEN 0
                    WHEN days_overdue = 1 THEN 1
                    WHEN days_overdue = 2 THEN 2
                    WHEN days_overdue = 3 THEN 3
                    ELSE 4
                END AS sort_order,
                COUNT(DISTINCT lead_id) AS lead_count
            FROM base_data
            WHERE is_completed = 0
            GROUP BY sort_order, age_label
        ) AS aging
    ),

    'status_chart', (
        SELECT IFNULL(JSON_ARRAYAGG(
            JSON_OBJECT(
                'status_name', status_name,
                'status_color', status_color,
                'completed', completed,
                'pending', pending
            )
        ), JSON_ARRAY())
        FROM (
            SELECT
                st.name AS status_name,
                IFNULL(st.color, '#737985') AS status_color,
                SUM(td.is_completed) AS completed,
                SUM(1 - td.is_completed) AS pending
            FROM today_data td
            JOIN tblleads_status st ON st.id = td.lead_status
            GROUP BY st.id, st.name, st.color
            ORDER BY (completed + pending) DESC
        ) AS schart
    )

) AS result
";

// if(is_admin())
// {
//     echo $sql;
//     die;
// }

    $row = $this->db->query($sql)->row();
    return json_decode($row->result, true);
}

public function getFollow_up_datatable()
{
    // $param = $this->input->post();
    // $currentDate = !empty($param["follow_up_date"])
    // ? $this->db->escape_str($param["follow_up_date"])
    // : date('Y-m-d');
    
    // $now = date('Y-m-d H:i:s');
    
    // $start_date = !empty($param['missed_from_date'])
    // ? $this->db->escape_str($param['missed_from_date'])
    // : date('Y-m-01');
    
    // $end_date = !empty($param['missed_to_date'])
    // ? $this->db->escape_str($param['missed_to_date'])
    // : date('Y-m-d', strtotime('-1 day'));
    
    // // Staff filter
    // $staffWhere = "";
    // if (!empty($param['staff'])) {
    // $assigned = implode(",", array_map('intval', $param['staff']));
    // $staffWhere = " AND r.creator IN ($assigned) ";
    // }
    
    // // Status filter
    // $statusWhere = "";
    // if (!empty($param['view_status'])) {
    // $status = implode(",", array_map('intval', $param['view_status']));
    // $statusWhere = " AND l.status IN ($status) ";
    // }
    
    // // Source filter
    // $sourceWhere = "";
    // if (!empty($param['view_source'])) {
    // $source = implode(",", array_map('intval', $param['view_source']));
    // $sourceWhere = " AND l.source IN ($source) ";
    // }
    
    // // Lead type filter
    // $typeWhere = "";
    // if (!empty($param['lead_type'])) {
    // $type = implode(",", array_map('intval', $param['lead_type']));
    // $typeWhere = " AND l.type IN ($type) ";
    // }

    // // Department filter
    // $deptJoin = "";
    // $deptWhere = "";
    // $locWhere = "";
    // if (!empty($param['staff_department']) || !empty($param['office_location'])) {
    //     if (!empty($param['staff_department'])) {
    //         $deptWhere = " AND st.department IN ('" . implode("','", $this->db->escape_str($param['staff_department'])) . "') ";
    //     }
    //     if (!empty($param['office_location'])) {
    //         $locWhere = " AND st.office_location IN ('" . implode("','", $this->db->escape_str($param['office_location'])) . "') ";
    //     }
    // }
    


    // $commonFilter = $staffWhere . $statusWhere . $sourceWhere . $typeWhere . $deptWhere . $locWhere;
    // ══════════════════════════════════════════════════════════════
    // FIXES:
    //   1. tblnotes → correlated subquery (no row explosion)
    //   2. UNION 2nd side → alternative_phonenumber (was phonenumber)
    //   3. log LEFT JOIN → filter leads where last call > reminder time
    //   4. Removed outer GROUP BY hack
    //   5. MAX(n.description) → ORDER BY dateadded DESC LIMIT 1
    //   6. log join in 2nd UNION uses alternative_phonenumber
    // ══════════════════════════════════════════════════════════════
// $sql = "
// SELECT 
//     d.reminder_id AS reminder_id,
//     d.lead_id,
//     d.name,
//     d.phonenumber,
//     d.alternative_phonenumber,
//     d.assigned,
//     d.lead_type,
//     d.lead_type_color,
//     d.status_name,
//     d.status_color,
//     d.source_name,
//     d.source_color,
//     d.dateadded,
//     MAX(d.lastupdate_date) as lastupdate_date,
//     d.notedate as notedate,
//     d.priority,
//     d.priority_color,
//     d.staff_name,
//     MAX(d.follow_date) AS follow_date,
//   MAX(d.last_call_time) AS last_call_time,
    
//     (
//         SELECT SUBSTRING(n.description, 1, 200)
//         FROM tblnotes n
//         WHERE n.rel_type = 'lead'
//           AND n.rel_id = d.lead_id
//           AND n.addedfrom = d.assigned
//         ORDER BY n.dateadded DESC
//         LIMIT 1
//     ) AS latest_note,

//     (
    
//     select MAX(connected) from (
//         SELECT MAX(FROM_UNIXTIME(c2.call_start + 19800)) connected
//         FROM tblcalls_activity_logs c2
//         WHERE c2.staffid = d.assigned
//           AND c2.call_status = 'answered'
//           AND c2.duration > 0
//           AND c2.contact =  d.phonenumber
          
//           UNION 
          
//           SELECT MAX(FROM_UNIXTIME(c2.call_start + 19800)) connected
//         FROM tblcalls_activity_logs c2
//         WHERE c2.staffid = d.assigned
//           AND c2.call_status = 'answered'
//           AND c2.duration > 0
//           AND c2.contact =  d.alternative_phonenumber
//           )  oo
             
//     ) AS last_connect_dt,
// (
//   SELECT CASE
//     WHEN (
//       SELECT cx.call_status
//       FROM (
//         SELECT staffid, contact, call_status, call_start
//         FROM tblcalls_activity_logs
//         WHERE contact = d.phonenumber

//         UNION ALL

//         SELECT staffid, contact, call_status, call_start
//         FROM tblcalls_activity_logs
//         WHERE contact = d.alternative_phonenumber
//       ) cx
//       WHERE cx.staffid = d.assigned
//       ORDER BY cx.call_start DESC
//       LIMIT 1
//     ) = 'answered' THEN 0

//     ELSE (
//       SELECT COUNT(*)
//       FROM (
//         SELECT staffid, contact, call_status, duration, call_start
//         FROM tblcalls_activity_logs
//         WHERE contact = d.phonenumber

//         UNION ALL

//         SELECT staffid, contact, call_status, duration, call_start
//         FROM tblcalls_activity_logs
//         WHERE contact = d.alternative_phonenumber
//       ) c4
//       WHERE c4.staffid = d.assigned
//         AND (c4.call_status != 'answered' OR c4.duration = 0)
//         AND FROM_UNIXTIME(c4.call_start + 19800) > IFNULL(
//           (
//             SELECT MAX(FROM_UNIXTIME(c5.call_start + 19800))
//             FROM (
//               SELECT staffid, contact, call_status, duration, call_start
//               FROM tblcalls_activity_logs
//               WHERE contact = d.phonenumber

//               UNION ALL

//               SELECT staffid, contact, call_status, duration, call_start
//               FROM tblcalls_activity_logs
//               WHERE contact = d.alternative_phonenumber
//             ) c5
//             WHERE c5.staffid = d.assigned
//               AND c5.call_status = 'answered'
//               AND c5.duration > 0
//           ),
//           '1970-01-01'
//         )
//     )
//   END
// ) AS attempts

   

// FROM (
//      SELECT 
//       r.id AS reminder_id, 
//       l.id AS lead_id, 
//       l.name, 
//       l.phonenumber, 
//       l.alternative_phonenumber, 
//       l.assigned, 
//       t.name AS lead_type, 
//       t.tpcolor AS lead_type_color, 
//       s.name AS status_name, 
//       s.color AS status_color, 
//       so.name AS source_name, 
//       so.color_name AS source_color, 
//       l.dateadded,
//       l.lastupdate_date,
//       n.notedate,
//       IFNULL(f.priority, 'low') AS priority, 
//       IFNULL(f.priority_color, '') AS priority_color, 
//       CONCAT(st.firstname, ' ', st.lastname) AS staff_name, 
//       r.date AS follow_date, 
//      log.max_call_start AS last_call_time
//     FROM 
//       tblreminders r 
//       JOIN tblleads l ON l.id = r.rel_id 
//       AND r.rel_type = 'lead' 
//       AND r.creator = l.assigned 
//      LEFT JOIN tblleads_status s ON s.id = l.status 
//      LEFT JOIN tblfollow_up_funnel f ON f.id = s.follow_up_status 
//      LEFT JOIN tblleads_type t ON t.id = l.type 
//      LEFT JOIN tblleads_sources so ON so.id = l.source 
//      LEFT JOIN (
//         SELECT 
//           rel_id, 
//          MAX(dateadded) AS notedate,
//          addedfrom
//         FROM 
//           tblnotes 
//         WHERE 
//           dateadded >= '{$currentDate}' 
//           AND rel_type ='lead'
//         GROUP BY 
//           rel_id
//       ) n ON n.rel_id = r.rel_id and n.addedfrom = r.creator
//      LEFT JOIN tblstaff st ON st.staffid = r.creator 
//       LEFT JOIN (
//         SELECT 
//           contact, 
//           staffid, 
//          MAX(call_start) AS max_call_start 
//         FROM 
//           tblcalls_activity_logs 
//         WHERE 
//           adjusted_call_start >= '{$currentDate}'
//           AND CHAR_LENGTH(contact) = 10
//         GROUP BY 
//           contact, 
//           staffid
//       ) log ON log.contact = l.phonenumber 
//       AND log.staffid = r.creator 
//     WHERE 
//       l.junk = 0 
//       AND l.lost = 0 
//       AND DATE(r.date) = '{$currentDate}'
//       $commonFilter
//       group by r.id
//     UNION ALL 
//     SELECT 
//       r.id AS reminder_id, 
//       l.id AS lead_id, 
//       l.name, 
//       l.phonenumber, 
//       l.alternative_phonenumber, 
//       l.assigned, 
//       t.name AS lead_type, 
//       t.tpcolor AS lead_type_color, 
//       s.name AS status_name, 
//       s.color AS status_color, 
//       so.name AS source_name, 
//       so.color_name AS source_color, 
//       l.dateadded, 
//       l.lastupdate_date,
//       n.notedate,
//       IFNULL(f.priority, 'low') AS priority, 
//       IFNULL(f.priority_color, '') AS priority_color, 
//       CONCAT(st.firstname, ' ', st.lastname) AS staff_name, 
//       r.date AS follow_date, 
//       log.max_call_start AS last_call_time
//     FROM 
//       tblreminders r 
//       JOIN tblleads l ON l.id = r.rel_id 
//       AND r.rel_type = 'lead' 
//       AND r.creator = l.assigned 
//      LEFT JOIN tblleads_status s ON s.id = l.status 
//      LEFT JOIN tblfollow_up_funnel f ON f.id = s.follow_up_status 
//      LEFT JOIN tblleads_type t ON t.id = l.type 
//      LEFT JOIN tblleads_sources so ON so.id = l.source 
//      LEFT JOIN (
//         SELECT 
//           rel_id, 
//          MAX(dateadded) AS notedate,
//          addedfrom
//         FROM 
//           tblnotes 
//         WHERE 
//           dateadded >= '{$currentDate}' 
//           AND rel_type ='lead'
//         GROUP BY 
//           rel_id
//       ) n ON n.rel_id = r.rel_id and n.addedfrom = r.creator
//      LEFT JOIN tblstaff st ON st.staffid = r.creator 
//       LEFT JOIN (
//         SELECT 
//           contact, 
//           staffid, 
//          MAX(call_start) AS max_call_start 
//         FROM 
//           tblcalls_activity_logs 
//         WHERE 
//           adjusted_call_start >= '{$currentDate}' 
//           AND CHAR_LENGTH(contact) = 10
//         GROUP BY 
//           contact, 
//           staffid
//       ) log ON log.contact = l.alternative_phonenumber 
//       AND log.staffid =  r.creator
//     WHERE 
//       l.junk = 0 
//       AND l.lost = 0 
//       AND l.alternative_phonenumber IS NOT NULL 
//       AND TRIM(l.alternative_phonenumber) != '' 
//       AND DATE(r.date) = '{$currentDate}'
//       $commonFilter
//   group by r.id

// ) d 
// WHERE 1=1
//   AND (
   
//     (d.last_call_time IS NULL AND d.notedate IS NULL)
//     OR

//     (d.last_call_time IS NOT NULL AND FROM_UNIXTIME(d.last_call_time + 19800) < d.follow_date)
//     OR

//     (d.notedate IS NOT NULL AND d.notedate < d.follow_date)
//   )

// GROUP BY 
//   d.reminder_id

// ORDER BY

//     MAX(d.follow_date) ASC
// ";


$get_staff_user_id = get_staff_user_id();
$selectedStaff = $staff;
$idsarr = [];
  $staffWhere = "";

// Case 1: User selected staff manually
if (!empty($selectedStaff)) {

    $staffList = "'" . implode("','", $selectedStaff) . "'";
    $staffWhere .= " AND r.creator IN ($staffList)";

} else {

    // Case 2: Not admin → apply restriction
    if (!is_admin()) {

        $role = $this->db
            ->where('staffid', $get_staff_user_id)
            ->get(db_prefix() . 'staff')
            ->row()
            ->role;

        // Manager → get team
        if ($role == 3) {

            $teamids = $this->db
                ->query('CALL GetReportingPersons(?)', [$get_staff_user_id])
                ->result_array();

            $this->db->close();
            $this->db->initialize();

            $idsarr = array_column($teamids, 'staffid');
        }

        // Apply filter
        if (!empty($idsarr)) {

            $allIds = array_merge($idsarr, [$get_staff_user_id]);
            $staffList = "'" . implode("','", $allIds) . "'";
            $staffWhere .= " AND r.creator IN ($staffList)";
            

        } else {

            $staffWhere .= " AND r.creator = '$get_staff_user_id'";
        }
    }

    // Admin → no filter
}

 $param = $this->input->post();
   
    
    $now = date('Y-m-d H:i:s');
    
    $start_date = !empty($param['missed_from_date'])
    ? $this->db->escape_str($param['missed_from_date'])
    : date('Y-m-01');
    
    $end_date = !empty($param['missed_to_date'])
    ? $this->db->escape_str($param['missed_to_date'])
    : date('Y-m-d', strtotime('-1 day'));
    
    // Staff filter
  
    if (!empty($param['staff'])) {
    $assigned = implode(",", array_map('intval', $param['staff']));
    $staffWhere = " AND r.creator IN ($assigned) ";
    }
    
    // Status filter
    $statusWhere = "";
    if (!empty($param['view_status'])) {
    $status = implode(",", array_map('intval', $param['view_status']));
    $statusWhere = " AND l.status IN ($status) ";
    }
    
    // Source filter
    $sourceWhere = "";
    if (!empty($param['view_source'])) {
    $source = implode(",", array_map('intval', $param['view_source']));
    $sourceWhere = " AND l.source IN ($source) ";
    }
    
    // Lead type filter
    $typeWhere = "";
    if (!empty($param['lead_type'])) {
    $type = implode(",", array_map('intval', $param['lead_type']));
    $typeWhere = " AND l.type IN ($type) ";
    }

    // Department filter
    $deptJoin = "";
    $deptWhere = "";
    $locWhere = "";
    if (!empty($param['staff_department']) || !empty($param['office_location'])) {
        if (!empty($param['staff_department'])) {
            $deptWhere = " AND st.department IN ('" . implode("','", $this->db->escape_str($param['staff_department'])) . "') ";
        }
        if (!empty($param['office_location'])) {
            $locWhere = " AND st.office_location IN ('" . implode("','", $this->db->escape_str($param['office_location'])) . "') ";
        }
    }
    


    $commonFilter = $staffWhere . $statusWhere . $sourceWhere . $typeWhere . $deptWhere . $locWhere . $whereFilters;
    
    $currentDate = !empty($param["follow_up_date"])
    ? $this->db->escape_str($param["follow_up_date"])
    : date('Y-m-d');

$nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));

$sql ="WITH call_data AS (
    SELECT 
        staffid,
        contact,
        MAX(call_start) AS last_call_time,
        MAX(CASE 
            WHEN call_status = 'answered' AND duration > 0 
            THEN call_start END) AS last_connected_time,
        SUM(CASE 
            WHEN call_status != 'answered' OR duration = 0 
            THEN 1 ELSE 0 END) AS total_attempts
    FROM tblcalls_activity_logs
    WHERE 
        contact >= '1000000000' 
        AND contact <= '9999999999'
        AND adjusted_call_start >= '${currentDate} 00:00:00'
    GROUP BY staffid, contact
),

note_data AS (
    SELECT 
        n.rel_id,
        n.addedfrom,
        n.dateadded AS notedate,
        SUBSTRING(n.description,1,200) AS latestnote
    FROM tblnotes n
    JOIN (
        SELECT rel_id, addedfrom, MAX(dateadded) AS max_date
        FROM tblnotes
        WHERE rel_type = 'lead'
          AND dateadded >= '${currentDate} 00:00:00'
        GROUP BY rel_id, addedfrom
    ) x 
    ON n.rel_id = x.rel_id 
    AND n.addedfrom = x.addedfrom 
    AND n.dateadded = x.max_date
    WHERE n.rel_type = 'lead'
)

SELECT 
    r.id AS reminder_id,
    l.id AS lead_id,
    l.name,
    l.phonenumber,
    l.alternative_phonenumber,
    l.assigned,
    t.name AS lead_type,
    t.tpcolor AS lead_type_color,
    s.name AS status_name,
    s.color AS status_color,
    so.name AS source_name,
    so.color_name AS source_color,
    l.dateadded,
    l.lastupdate_date,
    nd.notedate,
    nd.latestnote as latest_note,
    IFNULL(f.priority,'low') AS priority,
    IFNULL(f.priority_color,'') AS priority_color,
    CONCAT(st.firstname,' ',st.lastname) AS staff_name,
    r.date AS follow_date,

    MAX(cd.last_call_time) AS last_call_time,

    
    (  select MAX(connected) from (
        SELECT MAX(FROM_UNIXTIME(c2.call_start + 19800)) connected
        FROM tblcalls_activity_logs c2
        WHERE c2.staffid = r.creator
          AND c2.call_status = 'answered'
          AND c2.duration > 0
          AND c2.contact =  l.phonenumber
          
          UNION 
          
          SELECT MAX(FROM_UNIXTIME(c2.call_start + 19800)) connected
        FROM tblcalls_activity_logs c2
        WHERE c2.staffid = r.creator
          AND c2.call_status = 'answered'
          AND c2.duration > 0
          AND c2.contact =  l.alternative_phonenumber
          )  oo
             
    ) AS last_connect_dt,
   (
  SELECT CASE
    WHEN (
      SELECT cx.call_status
      FROM (
        SELECT staffid, contact, call_status, call_start
        FROM tblcalls_activity_logs
        WHERE contact = l.phonenumber AND staffid = r.creator

        UNION ALL

        SELECT staffid, contact, call_status, call_start
        FROM tblcalls_activity_logs
        WHERE contact = l.alternative_phonenumber AND staffid = r.creator
      ) cx
      ORDER BY cx.call_start DESC
      LIMIT 1
    ) = 'answered' THEN 0

    ELSE (
      SELECT COUNT(*)
      FROM (
        SELECT staffid, contact, call_status, duration, call_start
        FROM tblcalls_activity_logs
        WHERE contact = l.phonenumber AND staffid = r.creator

        UNION ALL

        SELECT staffid, contact, call_status, duration, call_start
        FROM tblcalls_activity_logs
        WHERE contact = l.alternative_phonenumber AND staffid = r.creator
      ) c4
      WHERE 
        (c4.call_status != 'answered' OR c4.duration = 0)
        AND FROM_UNIXTIME(c4.call_start + 19800) > IFNULL(
          (
            SELECT MAX(FROM_UNIXTIME(c5.call_start + 19800))
            FROM (
              SELECT staffid, contact, call_status, duration, call_start
              FROM tblcalls_activity_logs
              WHERE contact = l.phonenumber AND staffid = r.creator

              UNION ALL

              SELECT staffid, contact, call_status, duration, call_start
              FROM tblcalls_activity_logs
              WHERE contact = l.alternative_phonenumber AND staffid = r.creator
            ) c5
            WHERE 
              c5.call_status = 'answered'
              AND c5.duration > 0
          ),
          '1970-01-01'
        )
    )
  END
) AS attempts
    
 

FROM tblreminders r

JOIN tblleads l 
    ON l.id = r.rel_id
    AND r.rel_type = 'lead'
    AND r.creator = l.assigned

LEFT JOIN tblleads_status s ON s.id = l.status
LEFT JOIN tblfollow_up_funnel f ON f.id = s.follow_up_status
LEFT JOIN tblleads_type t ON t.id = l.type
LEFT JOIN tblleads_sources so ON so.id = l.source
LEFT JOIN note_data nd ON nd.rel_id = r.rel_id AND nd.addedfrom = r.creator
LEFT JOIN tblstaff st ON st.staffid = r.creator

LEFT JOIN call_data cd 
    ON cd.staffid = r.creator
    AND cd.contact IN (l.phonenumber, l.alternative_phonenumber)

WHERE 
    l.junk = 0
    AND l.lost = 0
    AND r.date >= '${currentDate} 00:00:00'
    AND r.date <  '${nextDate} 00:00:00'
    
    ${commonFilter}   -- ✅ FIX: apply filters here

GROUP BY 
    r.id

HAVING
(
    (MAX(cd.last_call_time) IS NULL AND nd.notedate IS NULL)

    OR

    (
        (MAX(cd.last_call_time) IS NULL 
         OR FROM_UNIXTIME(MAX(cd.last_call_time) + 19800) < r.date)

        AND

        (nd.notedate IS NULL 
         OR nd.notedate < r.date)
    )
)

ORDER BY r.date ASC";

// echo $sql;
// die;
// HAVING (last_call_time IS NULL AND d.notedate IS NULL)

//   HAVING last_call_time IS NULL
    return $this->db->query($sql)->result_array();
}
}


















