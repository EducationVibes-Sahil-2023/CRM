<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="customer_group_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('customer_group_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('customer_group_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/knowledge_base/knowledge_group', array('id' => 'customer-group-modal')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'customer_group_name'); ?>
                        <?php echo form_hidden('id'); ?>
                    </div>
                </div>

                <div class="form-group select-placeholder">
                    <label for="department" class="control-label">Select Department Type</label>
                    <select name="department[]" data-live-search="true" id="department" class="form-control selectpicker" data-none-selected-text="Select Department Type" multiple="truesss">
                        <option value="">Select Department Type</option>
                        <?php
                        if (!empty($department)) {
                            foreach ($department as $lead) {
                        ?>
                                <option value="<?php echo $lead['id']; ?>"><?php echo $lead['name'] ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_select('staff_ids[]', $members, array('staffid', array('firstname', 'lastname')),  _l('knowledge_member'), 'Staff Members', array('multiple' => true), array(), '', '', false); ?>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label><span class="text-danger">*</span> Status</label>
                        <!-- <select name="status" class="selectpicker" required="1" id="status" required="true" data-width="100%" data-none-selected-text="<?php echo  _l('fillter_by_status'); ?>"> -->
                        <select id="status" name="status" class="selectpicker" required="1" data-width="100%" data-none-selected-text="Non selected" data-live-search="true" tabindex="-98" aria-describedby="status-error">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
        appValidateForm($('#customer-group-modal'), {
            name: 'required'
        }, manage_customer_groups);

        $('#customer_group_modal').on('show.bs.modal', function(e) {
            var invoker = $(e.relatedTarget);
            var group_id = $(invoker).data('id');
            $('#customer_group_modal .add-title').removeClass('hide');
            $('#customer_group_modal .edit-title').addClass('hide');
            $('#customer_group_modal input[name="id"]').val('');
            $('#customer_group_modal input[name="name"]').val('');
            $('#customer_group_modal select[name="status"]').selectpicker('deselectAll');
            $('#customer_group_modal select[name="department[]"]').selectpicker('deselectAll');
            $('#customer_group_modal select[name="staff_ids[]"]').selectpicker('deselectAll');
            // $('#customer_group_modal select[name="status"]').selectpicker('val', 1);
            $('#customer_group_modal select[name="status"]').selectpicker('refresh');
            $('#customer_group_modal select[name="staff_ids[]"]').selectpicker('refresh');
            $('#customer_group_modal select[name="department[]"]').selectpicker('refresh');
            // is from the edit button
            // if (typeof(group_id) !== 'undefined') {
            //     let selected_member = $(invoker).parents('tr').find('td').eq(1).text();
            //     let selected_status = $(invoker).parents('tr').find('td').eq(2).text();
            //     $('#customer_group_modal input[name="id"]').val(group_id);
            //     $('#customer_group_modal .add-title').addClass('hide');
            //     $('#customer_group_modal .edit-title').removeClass('hide');
            //     $('#customer_group_modal input[name="name"]').val($(invoker).parents('tr').find('td').eq(0).text());
            //     // $('#customer_group_modal select[name="staff_ids[]"]').val();

            // }

        });

    });

    function manage_customer_groups(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                if ($.fn.DataTable.isDataTable('.table-customer-groups')) {
                    $('.table-customer-groups').DataTable().ajax.reload();
                }
                if ($('body').hasClass('dynamic-create-groups') && typeof(response.id) != 'undefined') {
                    var groups = $('select[name="groups_in[]"]');
                    groups.prepend('<option value="' + response.id + '">' + response.name + '</option>');
                    groups.selectpicker('refresh');
                }
                alert_float('success', response.message);
            }
            $('#customer_group_modal').modal('hide');
        });
        return false;
    }

    function edit_knowledge_group(id, name, member, status, department) {
        setTimeout(function() {
            $('#customer_group_modal input[name="id"]').val(id);
            $('#customer_group_modal input[name="name"]').val(name);

            // Clear all selections first
            $('#customer_group_modal select[name="status"]').selectpicker('deselectAll');
            $('#customer_group_modal select[name="status"]').selectpicker('val', status);
            $('#customer_group_modal select[name="staff_ids[]"]').selectpicker('deselectAll');

            if (member) {
                var valuesToSelect = member.split(",");
                // Loop through each value and select the corresponding option
                $.each(valuesToSelect, function(index, value) {
                    $('#customer_group_modal select[name="staff_ids[]"]').selectpicker('val', valuesToSelect);
                });
            }

            if (department) {
                var valuesToSelect = department.split(",");
                // Loop through each value and select the corresponding option
                $.each(valuesToSelect, function(index, value) {
                    $('#customer_group_modal select[name="department[]"]').selectpicker('val', valuesToSelect);
                });
            }

            // Refresh the SelectPickers to reflect the changes
            $('#customer_group_modal select[name="status"]').selectpicker('refresh');
            $('#customer_group_modal select[name="staff_ids[]"]').selectpicker('refresh');
            $('#customer_group_modal select[name="department[]"]').selectpicker('refresh');

        }, 300);
    }
</script>