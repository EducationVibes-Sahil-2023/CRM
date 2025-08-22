<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="client_app_config_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="add-title">Add / <?php echo _l('client_app_config_edit_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/App_config/client_update', array('id' => 'update_client_app_config')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('company_name', 'Company Name'); ?>
                        <?php echo form_hidden('id'); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('logo', 'Logo', '', 'file'); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('base_url', 'Base URL'); ?>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button group="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('load', function() {
        appValidateForm($('#update_client_app_config'), {
            name: 'required'
        }, update_client_app_config);

    });

</script>