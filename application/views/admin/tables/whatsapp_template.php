<?php

defined('BASEPATH') or exit('No direct script access allowed');
$has_permission_create = has_permission('whatsapp_template', '', 'create');
$has_permission_edit = has_permission('whatsapp_template', '', 'edit');
$has_permission_delete = has_permission('whatsapp_template', '', 'delete');

$aColumns = [db_prefix() . 'whatsapp_template.name', db_prefix() . 'whatsapp_template.subject', db_prefix() . 'whatsapp_template.message', db_prefix() . 'whatsapp_template.status'];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'whatsapp_template';
$join = [];
array_push($join, 'LEFT JOIN ' . db_prefix() . 'staff s ON  ( s.staffid = ' . db_prefix() . 'whatsapp_template.created_by ) ');

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id'], 'GROUP BY ' . db_prefix() . 'whatsapp_template.id');

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow[db_prefix() . 'whatsapp_template.name'];
    $row[] = $aRow[db_prefix() . 'whatsapp_template.subject'];
    $row[] = nl2br($aRow[db_prefix() . 'whatsapp_template.message']);

    $row[] = '<span class="'
        . (!empty($aRow[db_prefix() . 'whatsapp_template.status']) && $aRow[db_prefix() . 'whatsapp_template.status'] == 1 ? 'text-success' : 'text-danger')
        . '">'
        . (!empty($aRow[db_prefix() . 'whatsapp_template.status']) && $aRow[db_prefix() . 'whatsapp_template.status'] == 1 ? 'Active' : 'Inactive')
        . '</span>';


    // $options = icon_btn('#' . $aRow['id'], 'eye', 'btn-danger', ['data-toggle' => 'modal', 'data-target' => '#customer_group_modal', 'data-id' => $aRow['id']]);
    $options = '';
    if ($has_permission_edit || $has_permission_create || is_admin()) {
        $options .= icon_btn(
            '#',
            'pencil-square-o',
            'btn-default',
            [
                'data-toggle' => 'modal',
                'data-target' => '#whatsapp_template_modal',
                'onclick' => "edit_whatsapp_template(" .
                    $aRow['id'] . ",'" .
                    addslashes($aRow[db_prefix() . 'whatsapp_template.name']) . "','" .
                    addslashes($aRow[db_prefix() . 'whatsapp_template.subject']) . "'," .
                    $aRow[db_prefix() . 'whatsapp_template.status'] . ",'" .
                    base64_encode($aRow[db_prefix() . 'whatsapp_template.message']) . "')",
                'data-id' => $aRow['id']
            ]
        );
    }
    if ($has_permission_delete || is_admin()) {
        $options .= icon_btn('#', 'remove', 'btn-danger _delete', array('onclick' => "delete_whatsapp_template(" . $aRow['id'] . ")"));
    }
    $row[]   = $options;

    $output['aaData'][] = $row;
}
