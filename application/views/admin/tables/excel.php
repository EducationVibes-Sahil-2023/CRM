<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'spreadsheetId',
    'name',
    'status',
    'fromDate',
    'toDate',
    'lastSync',
    'sheet_name',
    'acadmic_year'
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'excel_data_update';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        $attributes = [];
        $status = 'Inactive';
        if ($aRow["status"] == 1) {
            $status = 'Active';
        }
        $row[]              = $aRow["sheet_name"];
        $row[]              = $aRow["spreadsheetId"];
        $row[]              = $status;
        $row[]              = $aRow["fromDate"] != "0000-00-00" ? $aRow["fromDate"] : '';
        $row[]              = $aRow["toDate"] != "0000-00-00" ? $aRow["toDate"] : '';
        $row[]              = $aRow["lastSync"];
        $row[]              = $aRow["acadmic_year"];
        $options = icon_btn(base_url('/admin/excel/create/') . $aRow['id'], 'pencil-square-o', 'btn-default', $attributes);
        $row[]              = $options;
        // .= icon_btn('currencies/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $output['aaData'][] = $row;
}
