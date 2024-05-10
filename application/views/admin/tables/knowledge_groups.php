<?php

defined('BASEPATH') or exit('No direct script access allowed');
$has_permission_create = has_permission('knowledge_base', '', 'create');
$has_permission_edit = has_permission('knowledge_base', '', 'edit');
$has_permission_delete = has_permission('knowledge_base', '', 'delete');

$aColumns = [db_prefix() . 'knowledge_group.name', db_prefix() . 'knowledge_group.staff_ids', db_prefix() . 'knowledge_group.status', db_prefix() . 'knowledge_group.department', 'count(s.staffid) lead_type_count'];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'knowledge_group';
$join = [];
array_push($join, 'LEFT JOIN ' . db_prefix() . 'staff s ON  FIND_IN_SET(s.department,' . db_prefix() . 'knowledge_group.department)');

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id'], 'GROUP BY ' . db_prefix() . 'knowledge_group.id');

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<a href="#" >' . $aRow[db_prefix() . 'knowledge_group.name'] . '</a>';
    $row[] = ((!empty($aRow[db_prefix() . 'knowledge_group.staff_ids']) ? count(array_unique(explode(",", $aRow[db_prefix() . 'knowledge_group.staff_ids']))) : '0') + ($aRow['lead_type_count']));
    $row[] = !empty($aRow[db_prefix() . 'knowledge_group.status']) && $aRow[db_prefix() . 'knowledge_group.status'] == 1 ? 'Actine' : 'Inactive';

    // $options = icon_btn('#' . $aRow['id'], 'eye', 'btn-danger', ['data-toggle' => 'modal', 'data-target' => '#customer_group_modal', 'data-id' => $aRow['id']]);
    $options = '';
    if ($has_permission_edit || $has_permission_create || is_admin()) {
        $options .= icon_btn('#', 'pencil-square-o', 'btn-default', ['data-toggle' => 'modal', 'data-target' => '#customer_group_modal', 'onclick' => "edit_knowledge_group({$aRow['id']},'{$aRow[db_prefix() . 'knowledge_group.name']}','{$aRow[db_prefix() . 'knowledge_group.staff_ids']}',{$aRow[db_prefix() . 'knowledge_group.status']},'{$aRow[db_prefix() . 'knowledge_group.department']}')", 'data-id' => $aRow['id']]);
    }
    if ($has_permission_delete || is_admin()) {
        $options .= icon_btn('knowledge_base/delete_kmowledge_group/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $row[]   = $options;

    $output['aaData'][] = $row;
}
