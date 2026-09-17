<?php
defined('BASEPATH') or exit('No direct script access allowed');

// $has_permission_delete = has_permission('fly_batch', '', 'delete');
$aColumns = [
     db_prefix() . 'departure_location.id as id',
    db_prefix() . 'departure_location.name as name',
    'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as created_by',
    db_prefix() . 'departure_location.created_at as created_date'
];


$sIndexColumn = 'id';
$sTable = db_prefix() . 'departure_location';
$join[] = 'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'departure_location.created_by = ' . db_prefix() . 'staff.staffid';



// Optional WHERE conditions
$where = [];
$search_column = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $group_by, [], 1, $search_column);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $encodedData = base64_encode(json_encode($aRow));
    $row = []; // Corrected initialization
    $id = $aRow['id'];
    $row[] = !empty($aRow["name"]) ? $aRow["name"] : '';
    $row[] = !empty($aRow["created_by"]) ? $aRow["created_by"] : '';
    $row[] = !empty($aRow["created_date"]) ? $aRow["created_date"] : '';


    $action = "<div> ";
    if ($has_permission_delete == 1) {
        $action .= "<a class='btn btn-xs btn-danger' href='javascript:void(0)' onclick='delete($id)'>
                    <i class='fa fa-trash'></i>
                </a>";
    }

    $action .= "<a class='btn btn-xs btn-primary' href='javascript:void(0)' onclick='edit($id, \"" . $encodedData . "\")'>
                    <i class='fa fa-eye'></i>
                </a>
            </div>";



    $row[] = $action;

    $output['aaData'][] = $row;
}
