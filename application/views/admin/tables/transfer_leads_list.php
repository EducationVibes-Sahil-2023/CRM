<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$sTable       = db_prefix() . 'leads';
$sIndexColumn = 'id';

// Precompute latest call per contact/staff
$latestCalls = '(SELECT 
                    contact, 
                    staffid, 
                    MAX(call_start) AS call_start
                 FROM tblcalls_activity_logs
                 GROUP BY contact, staffid
                ) AS c';

$aColumns = [
    $sTable . '.id as id',
    $sTable . '.name as name',
    $sTable . '.phonenumber as phonenumber',
    $sTable . '.alternative_phonenumber as alternative_phonenumber',
    $sTable . '.email as email',
    $sTable . '.dateadded as dateadded',
    $sTable . '.lastcontact as lastcontact',
    $sTable . '.dateassigned as dateassigned',
    $sTable . '.lastupdate_date as lastupdate_date',
    $sTable . '.update_count as update_count',
    $sTable . '.call_duration as call_duration',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'leads_sources.name as source_name',
    db_prefix() . 'leads_type.name as type_name',
    'CONCAT(' . db_prefix() . 'staff.firstname," ",' . db_prefix() . 'staff.lastname) as assigned_name',
    'FROM_UNIXTIME(c.call_start + 19800) AS call_time', // latest call time
];

$join = [];
$join[] = 'LEFT JOIN ' . db_prefix() . 'staff 
           ON ' . db_prefix() . 'staff.staffid = ' . $sTable . '.assigned';

$join[] = 'LEFT JOIN ' . $latestCalls . '
           ON c.staffid = ' . $sTable . '.assigned
           AND c.contact IN (
               COALESCE(NULLIF(' . $sTable . '.phonenumber, ""), "NULL"),
               COALESCE(NULLIF(' . $sTable . '.alternative_phonenumber, ""), "NULL")
           )';

$join[] = 'JOIN ' . db_prefix() . 'leads_status 
           ON ' . db_prefix() . 'leads_status.id = ' . $sTable . '.status';
$join[] = 'JOIN ' . db_prefix() . 'leads_sources 
           ON ' . db_prefix() . 'leads_sources.id = ' . $sTable . '.source AND ' . db_prefix() . 'leads_sources.lead_transfer_status = 1';
$join[] = 'JOIN ' . db_prefix() . 'leads_type 
           ON ' . db_prefix() . 'leads_type.id = ' . $sTable . '.type';

$where = [];
$where[] = 'AND ' . $sTable . '.update_count = 0 
            AND ' . $sTable . '.call_duration = 0';
$where[] = 'AND (
                ' . $sTable . '.lastupdate_date = "0000-00-00"
                OR ' . $sTable . '.lastupdate_date >= ' . $sTable . '.dateassigned
            )';

// ✅ Only leads with no call
$where[] = 'AND c.call_start IS NULL'; // <-- fast and simple
$where[] = ' AND '.$sTable.'.status = 2 '; // <-- fast and simple\

$where[] = ' AND date('.$sTable.'.dateassigned) > "'.START_AUTO_LEAD_TRANSFER_DATE.'" ';

$result = data_tables_init_(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Lead name
    $leadName = '<a href="' . admin_url('leads/lead/' . $aRow['id']) . '" target="_blank">'
        . $aRow['name'] . '</a>';
    $leadName .= '<div class="row-options"></div>';
$seconds = calculate_business_seconds(
    $aRow['dateassigned'],
    date('Y-m-d H:i:s')
);

// Calculate total hours, minutes, seconds
$hours = floor($seconds / 3600);          // total hours, not modulo 24
$minutes = floor(($seconds % 3600) / 60);
$secondsOnly = $seconds % 60;

// Format as "HH:MM:SS"
$idleTime = sprintf('%02dh:%02d:%02d', $hours, $minutes, $secondsOnly);

// Determine badge color based on total seconds
if ($seconds >= 6300) { // 1h 45m
    $badgeClass = 'label-danger';
} elseif ($seconds >= 5400) { // 1h 30m
    $badgeClass = 'label-warning';
} else {
    $badgeClass = 'label-primary';
}

// Generate badge HTML
$badge = '<span class="label ' . $badgeClass . '">' . $idleTime . '</span>';

    $row[] = $leadName;
    $row[] = $aRow['phonenumber'];
    $row[] = $aRow['update_count'];
    $row[] = $aRow['call_duration'];
    $row[] = $badge;
    $row[] = $aRow['type_name'];
    $row[] = $aRow['source_name'];
    $row[] = $aRow['status_name'];
    $row[] = $aRow['assigned_name'];
    $row[] = _dt($aRow['dateadded']);
    $row[] = _dt($aRow['dateassigned']);
    $row[] = $aRow['call_time']; // show latest call time

    $output['aaData'][] = $row;
}

echo json_encode($output);
die;
