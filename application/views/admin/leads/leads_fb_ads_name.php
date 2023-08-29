<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$status = [array(
    "name" => "Active",
    "id" => 1
), array(
    "name" => "Inactive",
    "id" => 0
)];
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_form(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('fb_new_name'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php if (count($facebook) > 0) { ?>
                            <table class="table dt-table scroll-responsive" data-order-col="1" data-order-type="asc">
                                <thead>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('facebook_form_name'); ?></th>
                                    <th><?php echo _l('Status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </thead>
                                <tbody>
                                    <?php foreach ($facebook as $fb) { ?>
                                        <tr>
                                            <td><?php echo $fb['id']; ?></td>
                                            <td><a href="#" onclick="edit_form(this,<?php echo $fb['id']; ?>,<?php echo $fb['status']; ?>); return false" data-name="<?php echo $fb['name']; ?>" data-type="<?php echo $fb['status']; ?>"><?php echo $fb['name']; ?></a><br />
                                               
                                            </td>
                                            <td ><?php echo (!empty($fb['status']) && !empty($fb['status']) == 1) ? "Active" : 'Inactive'; ?></td>
                                            <td>
                                                <a href="#" onclick="edit_form(this,<?php echo $fb['id']; ?>,<?php echo $fb['status']; ?>); return false" data-name="<?php echo $fb['name']; ?>" data-type="<?php echo $fb['status']; ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                                <!-- <a href="<?php echo admin_url('leads/delete_source/' . $fb['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a> -->
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p class="no-margin"><?php echo _l('leads_sources_not_found'); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="form_name" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('leads/add_edit_fb_form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('Edit Form'); ?></span>
                    <span class="add-title"><?php echo _l('New Form'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name', 'Form Name'); ?>
                    </div>

                    <div class="col-md-12">
                        <div id="additional_type"></div>
                        <?php echo render_select('status', $status, array('id', 'name'), 'Status'); ?>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function() {
        appValidateForm($('form'), {
            name: 'required'
        }, manage_leads_sources);
        $('#form_name').on('hidden.bs.modal', function(event) {
            $('#additional').html('');
            $('#form_name input[name="name"]').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });

    function manage_leads_sources(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            window.location.reload();
        });
        return false;
    }

    function new_form() {
        $('#form_name').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_form(invoker, id, type) {
        var name = $(invoker).data('name');
        $('#status').val("");
        $('#additional').append(hidden_input('id', id));
        $('#form_name input[name="name"]').val(name);
        if (type != undefined && type != 0) {
            $('#status').val(type);
        }
        $('#status').selectpicker('refresh');

        $('#form_name').modal('show');
        $('.add-title').addClass('hide');
    }
</script>
</body>

</html>