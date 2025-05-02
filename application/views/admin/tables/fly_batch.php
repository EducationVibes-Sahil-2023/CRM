<?php
defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('fly_batch', '', 'delete');

$manually = !empty($_POST["manually"]) ? 1 : 0;
$aColumns = [
    db_prefix() . 'ticket_batch.name as name',
    'Date(' . db_prefix() . 'ticket_data.payment_date) as payment_date',
    db_prefix() . 'ticket_data.fly_date as fly_date',
    db_prefix() . 'ticket_data.ticket_cost as cost',
    db_prefix() . 'departure_location.name as departure_location',
    db_prefix() . 'vendor_list.name as vendor_name',
    db_prefix() . 'payment_mode.name as payment_mode',
    'COUNT(' . db_prefix() . 'ticket_data.id) as student_count',
    db_prefix() . 'ticket_batch.created_at as created_at',
    'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as created_by',
    db_prefix() . 'ticket_batch.id as id',
    db_prefix() . 'ticket_data.id as data_id',
    db_prefix() . 'ticket_data.vendor_id as vendor_id',
    db_prefix() . 'ticket_data.payment_mode as payment_mode_id',
    db_prefix() . 'ticket_data.departure_location as departure_location_id',
    db_prefix() . 'ticket_data.auto as auto',
    db_prefix() . 'ticket_data.ticket_status as ticket_status',
];

if ($manually == 1) {
    $aColumns[] = db_prefix() . 'ticket_data.country_name as country_name';
    $aColumns[] = db_prefix() . 'ticket_data.university_name as university_name';
} else {
    $aColumns[] = db_prefix() . 'ticket_batch.country_name as country_name';
    $aColumns[] = db_prefix() . 'ticket_batch.university_name as university_name';
}

$sIndexColumn = 'id';
if ($manually == 1) {
    $sTable = db_prefix() . 'ticket_data';
    $join[] = 'LEFT JOIN ' . db_prefix() . 'ticket_batch ON ' . db_prefix() . 'ticket_data.batch_id = ' . db_prefix() . 'ticket_data.batch_id';
} else {
    $sTable = db_prefix() . 'ticket_batch';
    $join[] = 'LEFT JOIN ' . db_prefix() . 'ticket_data ON ' . db_prefix() . 'ticket_data.batch_id = ' . db_prefix() . 'ticket_batch.id';
}

// Append additional joins without overwriting
$join[] = 'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'ticket_batch.created_by = ' . db_prefix() . 'staff.staffid';
$join[] = 'LEFT JOIN ' . db_prefix() . 'vendor_list ON FIND_IN_SET(' . db_prefix() . 'vendor_list.id, ' . db_prefix() . 'ticket_data.vendor_id)';
$join[] = 'LEFT JOIN ' . db_prefix() . 'payment_mode ON ' . db_prefix() . 'ticket_data.payment_mode = ' . db_prefix() . 'payment_mode.id';
$join[] = 'LEFT JOIN ' . db_prefix() . 'departure_location ON ' . db_prefix() . 'departure_location.id = ' . db_prefix() . 'ticket_data.departure_location';


// Optional WHERE conditions
$where = [];

if (!empty($_POST["client_id"])) {
    $client_ids = $_POST["client_id"];
    if (is_array($client_ids)) {
        $where[] = " AND " . db_prefix() . "ticket_data.client_id in (${client_ids}) ";
    } else {
        $where[] = " AND " . db_prefix() . "ticket_data.client_id = ${client_ids} ";
    }
}

if ($manually == 1) {
    $group_by = 'GROUP BY ' . db_prefix() . 'ticket_data.id';
} else {
    // Grouping by ticket_batch.id to avoid row duplication due to joins
    $group_by = 'GROUP BY ' . db_prefix() . 'ticket_batch.id';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $group_by);

$output  = $result['output'];
$rResult = $result['rResult'];

if ($manually == 1) {
    foreach ($rResult as $aRow) {
        $row = []; // Corrected initialization

        $row[] = $aRow["country_name"];
        $row[] = $aRow["university_name"];
        $row[] = $aRow["vendor_name"];
        $row[] = $aRow["cost"];
        $row[] = !empty($aRow["payment_date"] && $aRow["payment_date"] != '0000-00-00 00:00:00') ? $aRow["payment_date"] : '';
        $row[] = $aRow["payment_mode"];
        $row[] = !empty($aRow["fly_date"] && $aRow["fly_date"] != '0000-00-00 00:00:00') ? $aRow["fly_date"] : '';
        $row[] = $aRow["departure_location"];

        if (empty($aRow['auto']) && $manually == 1 && $aRow["ticket_status"] != 3) {
            $id = $aRow['data_id'];
            $encodedData = base64_encode(json_encode($aRow));
            $row[] = "<div>
                <a class='btn btn-xs btn-primary' href='javascript:void(0)' onclick='edit_ticket($id, \"" . $encodedData . "\")'>
                    <i class='fa fa-eye'></i>
                </a>
            </div>";
        } else if (!empty($aRow['auto']) && $manually != 1) {
            $row[] = "<div>
        <a class='btn btn-xs btn-primary' href='" . base_url('admin/fly_batch/create/') . $aRow['id'] . "'>
            <i class='fa fa-eye'></i>
        </a>
        &nbsp;
    </div>
    ";
        } else {
            $row[] = "";
        }

        $output['aaData'][] = $row;
    }
} else {
    foreach ($rResult as $aRow) {
        $row = []; // Corrected initialization

        $row[] = $aRow["country_name"];
        $row[] = $aRow["university_name"];
        $row[] = $aRow["name"];
        $row[] = $aRow["vendor_name"];
        $row[] = $aRow["cost"];
        $row[] = !empty($aRow["payment_date"] && $aRow["payment_date"] != '0000-00-00 00:00:00') ? $aRow["payment_date"] : '';
        $row[] = $aRow["payment_mode"];
        $row[] = !empty($aRow["fly_date"] && $aRow["fly_date"] != '0000-00-00 00:00:00') ? $aRow["fly_date"] : '';
        $row[] = $aRow["departure_location"];
        $row[] = $aRow["student_count"];

        if ($manually == 1) {
            $id = $aRow['data_id'];
            $encodedData = base64_encode(json_encode($aRow));
            $row[] = "<div>
                <a class='btn btn-xs btn-primary' href='javascript:void(0)' onclick='edit_ticket($id, \"" . $encodedData . "\")'>
                    <i class='fa fa-eye'></i>
                </a>
            </div>";
        } else if ($aRow['id'] != "" && $manually != 1) {
            $row[] = "<div>
        <a class='btn btn-xs btn-primary' href='" . base_url('admin/fly_batch/create/') . $aRow['id'] . "'>
            <i class='fa fa-eye'></i>
        </a>
        &nbsp;
    </div>
    ";
        } else {
            $row[] = "";
        }

        $output['aaData'][] = $row;
    }
}
