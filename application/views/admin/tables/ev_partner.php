<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'ev_partner.id as id',
    db_prefix() . 'ev_partner.name as name',
    db_prefix() . 'ev_partner.status as status',
    'GROUP_CONCAT(' . db_prefix() . 'leads_type.name SEPARATOR ", ") AS lead_type'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'ev_partner';

$joins = [];
$joins[] = 'LEFT JOIN ' . db_prefix() . 'leads_type 
            ON FIND_IN_SET(' . db_prefix() . 'leads_type.id, ' . db_prefix() . 'ev_partner.lead_type)';

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $joins,
    [],
    [],
    'GROUP BY ' . db_prefix() . 'ev_partner.id'
);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        $attributes = [
            'data-toggle'             => 'modal',
            'data-target'             => '#partner',
            'data-id'                 => $aRow['id'],
            'data-name'                 => $aRow['name'],
            'data-status'                 => $aRow['status'],
            'data-lead_type'                 => $aRow['lead_type']
        ];
        $status = 'Inactive';
        if ($aRow["status"] == 1) {
            $status = 'Active';
        }
        $row[]              = $aRow["name"];
        $row[]              = $aRow["lead_type"];
        $row[]              = $status;
        $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', $attributes);
        $row[]              = $options;
        // .= icon_btn('currencies/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $output['aaData'][] = $row;
}
