<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$table_data = array(
    _l('Location Name'),
    _l('Create By'),
    _l('Create Date'),
    "Action",
);
?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?= $title ?></h4>
                    </div>
                    <div class="panel-body _buttons">
                        <?php if (has_permission('fly_batch_departure', '', 'create')) { ?>
                            <a href="<?php echo admin_url('Fly_batch/create'); ?>" class="btn btn-info pull-left display-block"><?= "Fly Departure Create" ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <?php
                            render_datatable($table_data, 'fly-departure');
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
            exam_data_table = initDataTable('.table-fly-departure', window.location.href);
        });


    });
</script>
</body>

</html>