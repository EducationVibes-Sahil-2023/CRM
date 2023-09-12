<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'vendor',
    'status'
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'vendor_visa';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        $attributes = [
            'data-toggle'             => 'modal',
            'data-target'             => '#vendor',
            'data-id'                 => $aRow['id'],
            'data-name'                 => $aRow['vendor']
        ];
        $status = 'Inactive';
        if ($aRow["status"] == 1) {
            $status = 'Active';
        }
        $row[]              = $aRow["vendor"];
        $row[]              = $status;
        $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', $attributes);
        $row[]              = $options;
        // .= icon_btn('currencies/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $output['aaData'][] = $row;
}
