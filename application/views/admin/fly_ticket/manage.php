<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$table_data = array(
    _l('Country Name'),
    _l('university_name'),
    _l('Batch Name'),
    _l('Vendor Name'),
    _l('Cost'),
    _l('Payment Date'),
    _l('Payment Mode'),
    _l('Fly Date'),
    _l('Departure'),
    _l('Students'),
    "Action",
);
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body _buttons">
                        <?php if (has_permission('fly_ticket', '', 'create')) { ?>
                            <a href="<?php echo admin_url('Fly_batch/create'); ?>" class="btn btn-info pull-left display-block"><?= "Fly Ticket Batch Create" ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <?php
                            render_datatable($table_data, 'fly-batch');
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
            exam_data_table = initDataTable('.table-fly-batch', window.location.href);
        });


    });
</script>
</body>

</html>