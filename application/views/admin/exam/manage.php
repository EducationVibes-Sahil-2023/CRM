<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$table_data = array(
    _l('university_name'),
    _l('Batch Name'),
    _l('Exam Name'),
    _l('batch_exam_date'),
    _l('batch_exam_student'),
    _l('batch_exam_created'),
    _l('batch_exam_created_by'),
    "Action",
);
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body _buttons">
                        <?php if (has_permission('exam_batch', '', 'create')) { ?>
                            <a href="<?php echo admin_url('exam_batch/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('exam_batch'); ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <?php
                            render_datatable($table_data, 'entrance-exam');
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {


        var exam_data_table = ""
        $(function() {
            exam_data_table = initDataTable('.table-entrance-exam', window.location.href);
        });


    });
</script>
</body>

</html>