<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$vendor_type = vendor_types();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_department(); return false;" class="btn btn-info pull-left display-block">
                                <?php echo _l('new_vendor'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('vendor_name'),
                            _l('type'),
                            _l('status'),
                            _l('action'),
                        ), 'vendor'); ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="vendor" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('vendor/ma_vendor_name')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_vendor'); ?></span>
                    <span class="add-title"><?php echo _l('new_vendor'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>

                        <div class="form-group">
                            <input type="hidden" class="form-control" name="type" value="<?= $type ?>">
                            <input type="hidden" class="form-control" name="vendor_id" id="vendor_id" placeholder="Enter vendor id">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="vendor_name" id="vendor_name" placeholder="Enter vendor name" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Vendor Type <span class="text-danger">*</span></label>

                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('vendor_type[]', $vendor_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Vendor Type", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "Vendor Type");
                                    echo '</div>';
                                    ?>
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
            initDataTable('.table-vendor', window.location.href, [2], [2], undefined, [1, 'asc']);
            appValidateForm($('form'), {
                name: 'required',
                email: {
                    email: true,
                    remote: {
                        url: admin_url + "vendor/name_exists",
                        type: 'post',
                        data: {
                            email: function() {
                                return $('input[name="vendor_name"]').val();
                            }
                        }
                    }
                }
            }, manage_vendor);
            $('#vendor').on('hidden.bs.modal', function(event) {
                $('#vendor input[name="vendor_name"]').val('');
                $('#vendor input[name="vendor_id"]').val('');
                $('#vendor select[name="vendor_type[]"]').val('').selectpicker('refresh');
                $('.add-title').removeClass('hide');
                $('.edit-title').removeClass('hide');
            });

            $('#vendor').on('show.bs.modal', function(event) {

                var button = $(event.relatedTarget)
                var id = button.data('id');
                $('#vendor input[name="text"]').val('');
                $('#currency_modal .add-title').removeClass('hide');
                $('#currency_modal .edit-title').addClass('hide');

                if (typeof(id) !== 'undefined') {
                    $('input[name="vendor_id"]').val(id);
                    var name = $(button).data("name")
                    var type = [];
                    var rawType = $(button).data("type") || ""; // fallback if undefined
                    var type = [];

                    // Ensure it's a string before splitting
                    if (typeof rawType === "string" && rawType.trim() !== "") {
                        // If rawType contains a comma → split, otherwise push as single value
                        if (rawType.indexOf(",") !== -1) {
                            type = rawType.split(",").map(function(val) {
                                return val.trim(); // clean spaces
                            }).filter(function(val) {
                                return val !== ""; // remove empty entries
                            });
                        } else {
                            type = [rawType.trim()]; // single value as array
                        }
                    }
                    if (type.length === 0 && rawType.trim() !== "") {
                        type = [rawType.trim()];
                    }

                    $('input[name="vendor_name"]').val(name);
                    $('#currency_modal .add-title').addClass('hide');
                    $('#currency_modal .edit-title').removeClass('hide');
                    $('#currency_modal input[name="vendor_name"]').val(name);
                    $('#vendor select[name="vendor_type[]"]').val(type).selectpicker('refresh');


                }
            });
        });

        function manage_vendor(form) {
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

                $('.table-vendor').DataTable().ajax.reload();
                $('#vendor').modal('hide');
            }).fail(function(data) {
                var error = JSON.parse(data.responseText);
                alert_float('danger', error.message);
            });
            return false;
        }

        function new_department() {
            $('#vendor').modal('show');
            $('.edit-title').addClass('hide');
        }
    </script>
    </body>

    </html>