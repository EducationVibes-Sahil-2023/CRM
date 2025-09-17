<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'vendor_list.vendor_type as vendor_type',
    db_prefix() . 'vendor_list.id as vendor_id',
    'GROUP_CONCAT( ' . db_prefix() . 'vendor_types.name) AS vendor_type_name',
    db_prefix() . 'vendor_list.name as vendor_name',
    db_prefix() . 'vendor_list.status as vendor_status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'vendor_list';
$join         = [];

// ✅ Corrected JOIN with FIND_IN_SET
$join[] = "LEFT JOIN " . db_prefix() . "vendor_types 
           ON FIND_IN_SET(" . db_prefix() . "vendor_types.id, " . db_prefix() . "vendor_list.vendor_type)";


// $join[] = " LEFT JOIN " . db_prefix() . "vendor_types 
//            ON " . db_prefix() . "vendor_types.id  IN (" . db_prefix() . "vendor_list.vendor_type)";

// ✅ Force ordering on first column if invalid
if (!empty($_POST["order"][0]["column"])) {
    $_POST["order"][0]["column"] = 0;
}

// ✅ Pass only the column name for GROUP BY
$groupBy = " Group by " . db_prefix() . "vendor_list.id ";

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [], $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {

    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        $attributes = [
            'data-toggle'             => 'modal',
            'data-target'             => '#vendor',
            'data-id'                 => $aRow['vendor_id'],
            'data-name'                 => $aRow['vendor_name'],
            'data-type'                 => $aRow['vendor_type']
        ];
        $status = 'Inactive';
        if ($aRow["vendor_status"] == 1) {
            $status = 'Active';
        }
        $row[]              = $aRow["vendor_name"];
        $row[]              = $aRow["vendor_type_name"];
        $row[]              = $status;
        $options = icon_btn('#' . $aRow['vendor_id'], 'pencil-square-o', 'btn-default', $attributes);
        $row[]              = $options;
        // .= icon_btn('currencies/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $output['aaData'][] = $row;
}
