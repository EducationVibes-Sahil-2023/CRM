<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$get_currencies = get_currencies();

$table_data = array(
    array('name' => 'Hostel Name'),
    array('name' => 'Short form'),
    array('name' => 'Contact Number'),
    array('name' => 'Email')

);
?>
<div id="wrapper">
    <style>
        #hostel_management {
            z-index: 9990 !important;
        }

        .margin-top {
            margin-top: 20px;
        }

        .modal-dialog.fullscreen {
            width: 80%;
            max-width: 80%;


        }

        .modal-content {
            height: 100%;
            border-radius: 0;
        }
    </style>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo _l('Hostel'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('hostel', '', 'create')) {
                                ?>
                                    <button type="button" onclick="modalOpen('hostel_management')" data-bs-toggle="modal" data-bs-target="#hostel_management" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('create'); ?>
                                    </button>

                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />

                        <div class="clearfix mtop20"></div>
                        <?php
                        render_datatable($table_data, 'hostel', [], [
                            'data-last-order-identifier' => 'hostel',
                            'data-default-order'         => get_table_last_order('hostel'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hostel_management" tabindex="-1" role="dialog">
    <div class="modal-dialog fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="hostelManagementLabel">Hostel Information</h4>
                <button type="button" class="btn-close" onclick="modalClose('hostel_management')" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <?= form_open('', ['id' => 'hostel_management_form']); ?>
            <div class="modal-body">
                <input type="hidden" name="hostel_management_id" value="">
                <h3>Hostel Information</h3>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <?= render_input('hostel_name', 'Hostel Name', '', 'text', ["placeholder" => "Enter Hostel Name"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('hostel_logo', 'Hostel Logo', '', 'file', ["placeholder" => "Enter Hostel Name", "required" => true]); ?>
                    </div>

                    <div class="col-md-2">
                        <?= render_input('hostel_stamp', 'Hostel Stamp', '', 'file', ["placeholder" => "Enter Hostel Name", "required" => true]); ?>
                    </div>
                </div>
                <h3>Company Information</h3>
                <hr>
                <div class="row">
                    <div class="col-md-2">
                        <?= render_input('company_name', 'Company Name', '', 'text', ["placeholder" => "Enter Company Name"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('company_id', 'ID', '', 'text', ["placeholder" => "Enter Company ID"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('address', 'Hostal Address', '', 'text', ["placeholder" => "Enter Hostel Address"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('contact_number', 'Contact Number', '', 'text', ["placeholder" => "+995 592 XX XXXX"]); ?>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?= render_input('email', 'Email', '', 'email', ["placeholder" => "Enter Email"]); ?>
                        </div>
                    </div>
                </div>

                <h3>Bank Information</h3>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <?= render_textarea('beneficiary_headline', 'Headline', '', ["placeholder" => "Enter Headline"]); ?>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <?= render_input('beneficiary_bank', ' Beneficiary’s Bank', '', 'text', ["placeholder" => "Enter  Beneficiary’s Bank"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('beneficiary_code', 'Bank Code', '', 'text', ["placeholder" => "Enter Bank Code"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('beneficiary_iban', 'Beneficiary’s IBAN', '', 'text', ["placeholder" => "Enter  Beneficiary’s IBAN"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('beneficiary_name', 'Name of Beneficiary', '', 'text', ["placeholder" => "Enter Name of Beneficiary"]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= render_input('beneficiary_address', ' Beneficiary’s Address', '', 'text', ["placeholder" => "Enter  Beneficiary’s Bank Address"]); ?>
                    </div>
                </div>

                <h3>Bank Information International</h3>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <?= render_textarea('usd_beneficiary_headline', 'Headline', '', ["placeholder" => "Enter Headline"]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <?= render_input('usd_beneficiary_bank', 'Beneficiary’s Bank', '', 'text', ["placeholder" => "Enter  Beneficiary’s Bank"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('usd_beneficiary_code', 'Bank Code', '', 'text', ["placeholder" => "Enter Bank Code"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('usd_beneficiary_iban', 'Beneficiary’s IBAN', '', 'text', ["placeholder" => "Enter  Beneficiary’s IBAN"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('usd_beneficiary_name', 'Name of Beneficiary', '', 'text', ["placeholder" => "Enter Name of Beneficiary"]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= render_input('usd_beneficiary_address', 'Beneficiary’s Address', '', 'text', ["placeholder" => "Enter  Beneficiary’s Bank Address"]); ?>
                    </div>
                </div>

                <h3>Intermediary bank</h3>
                <hr>
                <div class="row">
                    <div class="col-md-2">
                        <?= render_input('intermediary_bank', 'Intermediary Bank Name', '', 'text', ["placeholder" => "Enter Intermediary Bank"]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_input('intermediary_swift_code', 'Intermediary Swift Code', '', 'text', ["placeholder" => "Enter Intermediary Swift Code"]); ?>
                    </div>
                </div>

                <h3>Footer</h3>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <?= render_textarea('footer_note', 'Footer Note', '', ["placeholder" => "Enter Footer Note"]); ?>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="modalClose('hostel_management')" data-bs-dismiss="modal">Close</button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>


    <?php init_tail(); ?>
    <script>
        var tAPI = "";

        $(function() {
            var CustomersServerParams = {};
            $.each($('._hidden_inputs._filters input'), function() {
                CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
            });
            tAPI = initDataTable('.table-hostel', admin_url + 'hostel_management/hostel_information', [0], [0], CustomersServerParams);

            // Initialize form validation
            appValidateForm($('#hostel_management_form'), {
                hostel_name: 'required',
                // hostel_logo: 'required',
                // hostel_stamp: 'required',

                company_name: 'required',
                company_id: 'required',
                hostel_address: 'required',
                contact_number: 'required',
                email: 'required',

                beneficiary_headline: 'required',
                beneficiary_bank: 'required',
                beneficiary_code: 'required',
                beneficiary_iban: 'required',
                beneficiary_name: 'required',
                beneficiary_address: 'required',


                usd_beneficiary_headline: 'required',
                usd_beneficiary_bank: 'required',
                usd_beneficiary_code: 'required',
                usd_beneficiary_iban: 'required',
                usd_beneficiary_name: 'required',
                usd_beneficiary_address: 'required',

                intermediary_bank: 'required',
                intermediary_swift_code: 'required',

                footer_note: 'required'
            });


        });

        function Delete(id) {
            if (!confirm('Are you sure you want to delete this hostel record?')) {
                return;
            }

            $.ajax({
                url: '<?= admin_url("hostel_management/delete_hostel/"); ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.resp_code === 'RCS') {
                        alert_float('success', response.resp_desc);
                        // Remove row from table (if using DataTables)
                        tAPI.ajax.reload();
                    } else {
                        alert_float('danger', response.resp_desc);
                    }
                },
                error: function(xhr, status, error) {
                    alert_float('danger', 'Something went wrong: ' + error);
                }
            });
        }

        function modalOpen(id) {
            // Reset form
            var form = $('#' + id + '_form');
            form[0].reset();
            form.find('input[name="hostel_management_id"]').val('');
            form.find('select.selectpicker').selectpicker('refresh');
            $(".logo-preview").remove();
            // Open modal
            $('#' + id).modal('show');
        }


        function modalClose(id) {
            // Reset form
            var form = $('#' + id + '_form');
            form[0].reset();
            form.find('input[name="hostel_management_id"]').val('');
            form.find('select.selectpicker').selectpicker('refresh');

            // Open modal
            $('#' + id).modal('hide');
        }


        $('#hostel_management').on('hidden.bs.modal', function() {
            // Clear hidden ID
            $(this).find('input[name="hostel_management_id"]').val('');
            $(".logo-preview").remove();
            $("form [type='file']").attr("required", true);
            // Optional: reset the entire form
            $(this).find('form')[0].reset();
            $(this).find('form select.selectpicker').selectpicker('refresh');
        });

        $('#hostel_management_form').on('submit', function(e) {
            e.preventDefault(); // Prevent default submit

            var form = $(this);

            // Check if form is valid
            if (!form.valid()) {
                // If validation fails, stop submission
                return false;
            }



            var url = '<?= admin_url("hostel_management/save_hostel"); ?>';
            var formData = new FormData(this);


            show_loader();

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    hide_loader();

                    if (response.resp_code === 'RCS') {
                        alert_float('success', response.resp_desc);
                        $("#hostel_management").modal('hide');
                        $("#hostel_management").find('form')[0].reset();
                        $("#hostel_management").find('select.selectpicker').selectpicker('refresh');
                        tAPI.ajax.reload(null, false); // Reload DataTable
                        // window.location.href = '<?= admin_url("clients/visa_details"); ?>';
                    } else {
                        alert_float('danger', 'Error: ' + response.resp_desc);
                    }
                },
                error: function(xhr, status, error) {
                    hide_loader();
                    alert_float('danger', 'Something went wrong: ' + error);
                }
            });
        });


        function Edit(id, base64_decode) {
            // Reset form and validation states
            var form = $('#hostel_management_form');
            form[0].reset();
            form.validate().resetForm();
            form.find('.error').removeClass('error');

            var decodedData = JSON.parse(atob(base64_decode));
            console.log(decodedData);
            $(".logo-preview").remove();
            $("form [type='file']").attr("required", true);
            // Decode base64 data
            $.each(decodedData, function(key, value) {
                // Find any matching input, select, textarea by name or id
                var $field = $("[name='" + key + "'], #" + key);

                if (key == "id") {
                    $("[name='hostel_management_id']").val(value);
                }
                if ($field.length > 0) {
                    // Handle file inputs separately

                    if ($field.attr("type") === "file") {
                        // Remove required attribute


                        // Append preview/download buttons (if value is not empty)
                        if (value) {
                            var fileUrl = "<?= base_url() ?>" + value; // assuming value contains the file URL
                            console.log(fileUrl);
                            var $container = $('<div class="margin-top  logo-preview"></div>');
                            var $preview = $('<i class="fa fa-eye btn btn-xs btn-primary"></i>')
                                .on('click', function() {
                                    show_media_files(fileUrl);
                                });
                            var $download = $('<i class="fa fa-download btn btn-xs btn-primary"></i>')
                                .on('click', function() {
                                    download_media_files(fileUrl, '_blank');
                                });

                            $container.append($preview).append(' ').append($download);
                            $field.after($container);
                            $field.prop("required", false);
                        }
                    }
                    // Handle checkboxes
                    else if ($field.is(":checkbox")) {
                        $field.prop("checked", value ? true : false);
                    }
                    // Handle radio buttons
                    else if ($field.is(":radio")) {
                        $field.filter("[value='" + value + "']").prop("checked", true);
                    }
                    // All other fields
                    else {
                        $field.val(value);
                    }

                    // Trigger change for UI plugins (like Select2)
                    $field.trigger("change");
                } else {
                    console.warn("No matching field found for:", key);
                }
            });

            // Show modal
            $('#hostel_management').modal('show');
        }
    </script>