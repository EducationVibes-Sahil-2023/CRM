<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$table_data = array(
    "University Name",
    "Session Intake",
    "Year",
    "Action",
);
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body _buttons">
                        <?php if (has_permission('quotation', '', 'create')) { ?>
                            <a href="<?php echo admin_url('excel/create'); ?>" class="btn btn-info pull-left display-block"><?php echo "Create" ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <?php
                            render_datatable($table_data, 'university-quotation');
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
        var university_quotation = ""
        $(function() {
            university_quotation = initDataTable('.table-university-quotation', window.location.href);
        });


    });
</script>