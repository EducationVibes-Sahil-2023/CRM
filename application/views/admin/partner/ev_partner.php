<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_department(); return false;" class="btn btn-info pull-left display-block">
                                <?php echo "New Partner"; ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            "Partner Name",
                            _l('Lead Type'),
                            _l('status'),
                            _l('action'),
                        ), 'partner'); ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="partner" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('partner/partner_name')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo "Edit Partner"; ?></span>
                    <span class="add-title"><?php echo "Create Partner"; ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>

                        <div class="form-group">
                            <input type="hidden" class="form-control" name="type" value="<?= $type ?>">
                            <input type="hidden" class="form-control" name="partner_id" id="partner_id" placeholder="Enter partner id">
                            <div class="form-group mb-3">
                                <label for="name">Partner Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="partner_name" placeholder="Enter partner name" required>
                            </div>


                            <div class="leads-filter-column">
                                <?php

                                echo '<div id="leads-filter-source">';
                                echo render_select('lead_type[]', $lead_type, array('id', 'name'), 'Lead Type <span class="text-danger">*</span>', '', array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true, "required" => "required"), array(), 'no-mbot', '', false, "lead_type");
                                echo '</div>';

                                // die;
                                ?>
                            </div>
                            <div class="form-group mb-3">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">In-Active</option>
                                </select>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-partner', window.location.href, [2], [2], undefined, [1, 'asc']);
        appValidateForm($('form'), {
            name: 'required',
            email: {
                email: true,
                remote: {
                    url: admin_url + "partner/name_exists",
                    type: 'post',
                    data: {
                        email: function() {
                            return $('input[name="name"]').val();
                        }
                    }
                }
            }
        }, manage_partner);
        $('#partner').on('hidden.bs.modal', function(event) {
            $('#partner input[name="name"]').val('');
            $('#partner input[name="partner_id"]').val('');
            $('#lead_type').val('').selectpicker('refresh');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });

        $('#partner').on('show.bs.modal', function(event) {

            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#partner input[name="text"]').val('');
            $('#partner select[name="status"]').val(1);
            $('#currency_modal .add-title').removeClass('hide');
            $('#currency_modal .edit-title').addClass('hide');

            if (typeof(id) !== 'undefined') {
                $('input[name="partner_id"]').val(id);

                var name = $(button).data("name");
                var status = $(button).data("status");
                var lead_type = $(button).data("lead_type");

                $('input[name="name"]').val(name);
                $('select[name="status"]').val(status);
                if (lead_type !== undefined && lead_type !== null && lead_type !== '') {

                    // Normalize lead_type to array
                    if (Array.isArray(lead_type)) {
                        lead_type = lead_type;
                    } else if (typeof lead_type === 'string') {
                        lead_type = lead_type.includes(',') ?
                            lead_type.split(',').map(v => v.trim()) :
                            [lead_type];
                    } else {
                        lead_type = [lead_type.toString()];
                    }

                    $('#lead_type').val(lead_type).selectpicker('refresh');

                } else {
                    // Clear selection
                    $('#lead_type').val([]).selectpicker('refresh');
                }


                $('#currency_modal .add-title').addClass('hide');
                $('#currency_modal .edit-title').removeClass('hide');
                $('#currency_modal input[name="name"]').val(name);

            }
        });
    });

    function manage_partner(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            } else {
                alert_float('danger', response.message);
                return false;
            }

            $('.table-partner').DataTable().ajax.reload();
            $('#partner').modal('hide');
        }).fail(function(data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message);
        });
        return false;
    }

    function new_department() {
        $('#partner').modal('show');
        $('.edit-title').addClass('hide');
    }
</script>
</body>

</html>