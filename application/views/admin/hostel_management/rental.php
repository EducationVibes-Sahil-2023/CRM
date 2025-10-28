<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$get_currencies = get_currencies();

$table_data = array(
    array('name' => 'Hostel Name'),
    array('name' => 'Room Capacity'),
    array('name' => 'Currency'),
    array('name' => 'Monthly Rent Amount'),
    array('name' => 'Action'),
);
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo _l('Hostel Rental'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('hostel_management', '', 'backend')) {
                                ?>
                                    <button type="button" data-toggle="modal" data-target="#hostel_rental" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('create'); ?>
                                    </button>

                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <?php
                        render_datatable($table_data, 'hostel_rental', [], [
                            'data-last-order-identifier' => 'hostel_rental',
                            'data-default-order'         => get_table_last_order('hostel_rental'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hostel_rental" tabindex="-1" role="dialog" aria-labelledby="hostelRentalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="hostelRentalLabel">Hostel Rental Details</h4>
            </div>

            <?= form_open('', ['id' => 'hostel_rental_form']); ?>
            <div class="modal-body">
                <input type="hidden" name="rental_id" value="">
                <div class="row">
                    <!-- <div class="col-md-6">
                        <?= render_select(
                            'hostel_id',
                            $dropdown_country_university_selection,
                            ['hostel_id', 'university_name'],
                            'University Name',
                            '',
                            [
                                'data-width' => '100%',
                                'data-none-selected-text' => 'No Selected',
                                'data-actions-box' => true
                            ]
                        ); ?>
                    </div> -->

                    <div class="col-md-6">
                        <?= render_select(
                            'hostel_id',
                            $hostelData,
                            ['id', 'name'],
                            'Hostal Name',
                            '',
                            [
                                'data-width' => '100%',
                                'data-none-selected-text' => 'No Selected',
                                'data-actions-box' => true
                            ]
                        ); ?>
                    </div>




                    <div class="col-md-6">
                        <?= render_select(
                            'room_capacity',
                            array_map(function ($v) {
                                return ['id' => $v, 'name' => $v];
                            }, range(1, 6)),
                            ['id', 'name'],
                            'Room Capacity',
                            '',
                            [
                                'data-width' => '100%',
                                'data-none-selected-text' => 'No Selected',
                                'data-actions-box' => true
                            ]
                        ); ?>
                    </div>

                    <div class="col-md-6">
                        <?= render_select(
                            'currency',
                            $get_currencies,
                            ['id', array('symbol', 'name')],
                            'Currency',
                            '',
                            [
                                'data-width' => '100%',
                                'data-none-selected-text' => 'No Selected',
                                'data-actions-box' => true
                            ]
                        ); ?>
                    </div>

                    <div class="col-md-6">
                        <?= render_input('rent', 'Monthly Rent Amount', '', 'number', [
                            'min' => 0,
                            'step' => 'any'
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
        tAPI = initDataTable('.table-hostel_rental', admin_url + 'hostel_management/rental_table', [0], [0], CustomersServerParams);

        // Initialize form validation
        appValidateForm($('#hostel_rental_form'), {
            hostel_id: 'required',
            room_capacity: 'required',
            currency: 'required',
            // adhar: 'required',
            rent: 'required'
        });


        $('#hostel_rental_form').on('submit', function(e) {
            e.preventDefault(); // Prevent default submit

            var form = $(this);

            // Check if form is valid
            if (!form.valid()) {
                // If validation fails, stop submission
                return false;
            }

            var url = '<?= admin_url("hostel_management/save_rental_details"); ?>';
            var formData = new FormData(this);

            // Append country_name from select
            var university_text = $('#hostel_id option:selected').text() || '';
            formData.append('university_name', university_text);

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
                        $("#hostel_rental").modal('hide');
                        $("#hostel_rental").find('form')[0].reset();
                        $("#hostel_rental").find('select').selectpicker('refresh');
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




    });

    function Edit(id, base64_decode) {
        // Reset form and validation states
        var form = $('#hostel_rental_form');
        form[0].reset();
        form.validate().resetForm();
        form.find('.error').removeClass('error');

        // Decode base64 data
        var decodedData = JSON.parse(atob(base64_decode));
        console.log(decodedData);
        // Populate form fields
        form.find('input[name="rental_id"]').val(id);
        form.find('select[name="hostel_id"]').val(decodedData.hostel_id).selectpicker('refresh');
        form.find('select[name="room_capacity"]').val(decodedData.room_capacity).selectpicker('refresh');
        form.find('select[name="currency"]').val(decodedData.currency).selectpicker('refresh');
        form.find('input[name="rent"]').val(decodedData.rent);

        // Show modal
        $('#hostel_rental').modal('show');
    }

    $('#hostel_rental').on('hidden.bs.modal', function() {
        // Clear hidden ID
        $(this).find('input[name="rental_id"]').val('');
        // Optional: reset the entire form
        $(this).find('form')[0].reset();
        $(this).find('form select').selectpicker('refresh');
    });

    function Delete(id) {
        if (!confirm('Are you sure you want to delete this hostel rental record?')) {
            return;
        }

        $.ajax({
            url: '<?= admin_url("hostel_management/delete_rental/"); ?>' + id,
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
</script>