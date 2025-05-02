<?php
defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('exam_batch', '', 'delete');
$exams = get_university_exam();
$exam_names = array_column($exams, "name", "id");
$aColumns = [

    db_prefix() . 'exam_batch.university_name as university_name',
    db_prefix() . 'exam_batch.name as exam_name',
    db_prefix() . 'exam_batch.name as exam_name',
    db_prefix() . 'exam_batch.exam_date as exam_date',
    db_prefix() . 'exam_batch.student_count as student_count',
    db_prefix() . 'exam_batch.created_at as created_at',
    'CONCAT(' . db_prefix() . 'staff.firstname," ",' . db_prefix() . 'staff.lastname) as created_by',
    db_prefix() . 'exam_batch.id as id',
    db_prefix() . 'exam_batch.exam_id as exam_id'
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'exam_batch';
$join         = ['LEFT JOIN ' . db_prefix() . 'clients_exam ON ' . db_prefix() . 'clients_exam.batch_id = ' . db_prefix() . 'exam_batch.id'];
$join         = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'exam_batch.created_by = ' . db_prefix() . 'staff.staffid'];
$i            = 0;
$group_by = ' Group By ' . db_prefix() . 'exam_batch.id ';
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $group_by);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = []; // Corrected initialization

    $row[] = $aRow["university_name"];
    $row[] = $aRow["exam_name"];
    $row[] = !empty($exam_names[$aRow["exam_id"]]) ? $exam_names[$aRow["exam_id"]] : '';
    $row[] = !empty( $aRow["exam_date"] && $aRow["exam_date"] !='0000-00-00')?$aRow["exam_date"]:'';
    $row[] = $aRow["student_count"];
    $row[] = $aRow["created_at"];
    $row[] = $aRow["created_by"];
    $row[] = "<div>
    <a class='btn btn-xs btn-primary' href='" . base_url('admin/exam_batch/create/') . $aRow['id'] . "'>
        <i class='fa fa-eye'></i>
    </a>
    &nbsp;
</div>
";

    $output['aaData'][] = $row;
}
