<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="panel_s">
                <div class="panel-body">
                    <h4 class="no-margin">
                        <?php echo $title; ?> &nbsp;
                        <a href="#" class="btn btn-info" data-toggle="modal" data-target="#client_app_config_modal"><?php echo _l('create_client_config'); ?></a>
                    </h4>

                    <hr class="hr-panel-heading" />
                    <div class="clearfix"></div>
                    <?php render_datatable(array(
                        _l('Name'),
                        _l('Logo'),
                        _l('Version'),
                        _l('Created Date'),
                        _l('Created By'),
                        _l('Action')
                    ), 'client-app-config'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/app/client_config'); ?>

<?php init_tail(); ?>

<script>
    // var config_table = $('.table-client-app-config'); // Global variable to hold the DataTable instance
    var config_table;

    $(function() {
        config_table = $('table.table-client-app-config');
        initDataTable(config_table, window.location.href, [1], [1]);
    });

    function update_client_app_config(form) {
        var formData = new FormData(form); // Create FormData object

        // Append file data if any
        var fileInput = $('input[type="file"]', form)[0];
        if (fileInput && fileInput.files.length > 0) {
            formData.append(fileInput.name, fileInput.files[0]);
        }

        var url = form.action;

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false, // Important for FormData
            contentType: false, // Important for FormData
            success: function(response) {
                try {
                    response = JSON.parse(response); // Parse JSON response
                    if (response.status == 1) {
                        // Reload DataTable after successful update

                        // table.ajax.reload(null, false); // Reload DataTable without resetting page
                        config_table.fnDestroy()
                        initDataTable(config_table, window.location.href, [1], [1]);

                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    alert_float('danger', 'Error parsing server response.');
                }
                $('#client_app_config_modal').modal('hide'); // Hide modal after update
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                alert_float('danger', 'An error occurred while processing the request.');
            }
        });

        return false; // Prevent form submission
    }

    function edit_client_app_config(id, company_name, base_url) {
        setTimeout(function() {
            $('#client_app_config_modal input[name="id"]').val(id);
            $('#client_app_config_modal input[name="company_name"]').val(company_name);
            $('#client_app_config_modal input[name="base_url"]').val(base_url);
            $('#client_app_config_modal').modal('show'); // Show edit modal
        }, 300); // Delay to ensure modal is fully loaded
    }
</script>