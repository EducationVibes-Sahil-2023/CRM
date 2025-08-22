<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
    .table>tbody>tr>td,
    .table>tbody>tr>th,
    .table>tfoot>tr>td,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>thead>tr>th {
        text-wrap: wrap !important;
    }
</style>
<div id="wrapper">
    <div class="content">


        <div class="modal fade" id="whatsapp_template_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">
                            <?php echo $title; ?>
                        </h4>
                    </div>
                    <div class="panel_s">
                        <div class="panel-body">
                            <form method="POST" id="whatsappp_template_form" onsubmit="create_whatsapp_template(); return false;">
                                <div class="col-md-12">
                                    <input id="template_id" name="template_id" value="" type="hidden">
                                    <!-- Input for template name -->
                                    <?php echo render_input('template_name', 'Template Name', '', 'text'); ?>
                                    <!-- Input for template subject -->
                                    <?php echo render_input('template_subject', 'Template Subject', '', 'text'); ?>
                                    <!-- Label for WhatsApp message -->
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="1">Enable</option>
                                            <option value="0">Disable</option>
                                        </select>
                                    </div>

                                    <!-- Textarea for WhatsApp message -->
                                    <?php echo render_textarea('template_message', 'Template Message', '', array('placeholder' => _l('Template Message')), array(), 'mtop15', 'whatsapp-template-message'); ?>
                                    <!-- Submit button -->
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal" data-target="#whatsapp_template_modal" onclick="set_whatsapp_modal()"><?php echo _l('create_whatsapp_template'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('Name'),
                            _l('Subject'),
                            _l('Message'),
                            _l('Status'),
                            _l('Action'),
                        ), 'whatsapp-template'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    // init_editor('textarea[name="<?php echo $id; ?>"]', {
    //     urlconverter_callback: 'merge_field_format_url'
    // });

    // init_editor('#message');

    function set_whatsapp_modal(status = 0) {
        if (status === 0) {
            $("#whatsappp_template_form")[0].reset();
        }
    }

    function create_whatsapp_template() {
        // Get values from form inputs
        let template_name = $("input[name='template_name']").val();
        let template_subject = $("input[name='template_subject']").val();
        let template_message = $('#template_message').val();
        let status = $("select[name='status']").val();
        let template_id = $("input[name='template_id']").val();

        var formData = new FormData();
        formData.append('template_name', template_name);
        formData.append('template_subject', template_subject);
        formData.append('template_message', template_message);
        formData.append('status', status);
        formData.append('template_id', template_id);
        formData.append(csrfData.token_name, csrfData.hash);
        for (var pair of formData.entries()) {
            // console.log(pair[0] + ': ' + pair[1]);
        }
        // Check if required fields are empty
        if (!template_name || !template_subject || !template_message || !status) {
            alert_float('danger', 'All fields are required');
            return false;
        }

        show_loader();
        // Create FormData object


        // Example of displaying FormData content (for debugging)


        // Perform AJAX submission
        $.ajax({
            type: 'POST',
            url: "<?= admin_url('whatsapp/create_template') ?>", // Replace with your backend processing URL
            data: formData,
            processData: false, // Prevent jQuery from processing the FormData
            contentType: false, // Ensure the Content-Type header is correct for FormData
            dataType: 'json', // Corrected the data type
            success: function(response) {
                // console.log(response);
                // Handle success response
                if (response.status === '1') {
                    alert_float('success', response.message);
                    // Optionally, reset the form
                    $("#whatsappp_template_form")[0].reset();
                    $('#whatsapp_template_modal').modal('hide');
                    whatsapp_template.ajax.reload(null, false);
                } else {
                    alert_float('danger', response.message);
                }
                hide_loader();
            },
            error: function(xhr, status, error) {
                // Handle error
                // console.error('Error submitting form:', error);
                alert_float('danger', 'An error occurred while submitting the form');
                hide_loader();
            }
        });
    }

    function edit_whatsapp_template(id, name, subject, status, message) {
        try {
            // Set template name and subject
            $("input[name='template_name']").val(name);
            $("input[name='template_subject']").val(subject);

            // Set the content of the TinyMCE editor if available
            if (message != "") {
                $('#template_message').val(atob(message));
            } else {
                // console.warn('TinyMCE editor not initialized or message not provided:', message);
            }

            // Set the value of the select element
            $("select[name='status']").val(status).change();

            // Set the template ID
            $("input[name='template_id']").val(id);
        } catch (error) {
            // console.error('Error editing WhatsApp template:', error);
            alert_float('danger', 'An error occurred while editing the template');
        }
    }


    function delete_whatsapp_template(id) {
        if (!id) {
            alert_float('danger', 'Template ID is required for deletion.');
            return;
        }

        show_loader();
        $.ajax({
            type: 'POST',
            url: "<?= admin_url('whatsapp/delete_template') ?>", // Replace with your backend processing URL
            data: {
                id: id
            },
            dataType: 'json', // Expected response type
            success: function(response) {
                console.log(response);
                // Handle success response
                if (response.status === '1') {
                    alert_float('success', response.message);
                    whatsapp_template.ajax.reload(null, false); // Reload DataTable
                } else {
                    alert_float('danger', response.message);
                }
                hide_loader();
            },
            error: function(xhr, status, error) {
                // Handle error
                // console.error('Error deleting template:', error);
                alert_float('danger', 'An error occurred while deleting the template');
                hide_loader();
            }
        });
    }


    window.addEventListener('load', function() {


    });

    var whatsapp_template = "";
    $(function() {
        whatsapp_template = initDataTable('.table-whatsapp-template', window.location.href, [1], [1]);
    });
    // Form handler function for knowledgebase group
    // function manage_kb_groups(form) {
    //     var data = $(form).serialize();
    //     var url = form.action;
    //     var articleAddEdit = $('body').hasClass('kb-article');
    //     if(articleAddEdit) {
    //         data+='&article_add_edit=true';
    //     }
    //     $.post(url, data).done(function(response) {
    //         if(!articleAddEdit) {
    //            window.location.reload();
    //         } else {
    //             response = JSON.parse(response);
    //             if(response.success == true){
    //                 if(typeof(response.id) != 'undefined') {
    //                     var group = $('#articlegroup');
    //                     group.find('option:first').after('<option value="'+response.id+'">'+response.name+'</option>');
    //                     group.selectpicker('val',response.id);
    //                     group.selectpicker('refresh');
    //                 }
    //             }
    //             $('#kb_group_modal').modal('hide');
    //         }
    //     });
    //     return false;
    // }

    // // New knowledgebase group, opens modal
    // function new_kb_group() {
    //     $('#kb_group_modal').modal('show');
    //     $('.edit-title').addClass('hide');
    // }

    // // Edit KB group, 2 places groups view or articles view directly click on kanban
    // function edit_kb_group(invoker, id) {
    //     $('#additional').append(hidden_input('id', id));
    //     $('#kb_group_slug').removeClass('hide');
    //     $('#kb_group_slug input').rules('add', {required:true});
    //     $('#kb_group_slug input').val($(invoker).data('slug'));
    //     $('#kb_group_modal input[name="name"]').val($(invoker).data('name'));
    //     $('#kb_group_modal textarea[name="description"]').val($(invoker).data('description'));
    //     $('#kb_group_modal .colorpicker-input').colorpicker('setValue', $(invoker).data('color'));
    //     $('#kb_group_modal input[name="group_order"]').val($(invoker).data('order'));
    //     $('input[name="disabled"]').prop('checked', ($(invoker).data('active') == 0 ? true : false));
    //     $('#kb_group_modal').modal('show');
    //     $('.add-title').addClass('hide');
    // }
</script>