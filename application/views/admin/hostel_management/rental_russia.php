<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$get_currencies = get_currencies();
$currency_lookup = array_column($get_currencies, NULL, 'id');
$get_clients_fees = get_clients_fees(2);
$table_data = array(
    array('name' => 'Hostel Name'),
    array('name' => 'Rental Details'),
    array('name' => 'Action'),
);

$years_array = [];

for ($i = 1; $i <= 6; $i++) {
    $years_array[] = [
        'id' => $i,
        'name' => $i
    ];
}
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
        <div class="modal-content fullscreen">
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

                    <div class="col-md-4">
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
                    <div class="col-lg-4">
                        <label for="acadmic_year">Academic Year</label>
                        <div class="form-group">
                            <?php
                            $startYear = 2023; // Always start from 2023
                            $endYear = date("Y") + 2; // End at current year + 2

                            $years = [];
                            $years[] = ['id' => '', 'name' => 'Select Academic Year']; // default option

                            for ($year = $startYear; $year < $endYear; $year++) {
                                $label = $year . ' - ' . ($year + 1);
                                $years[] = ['id' => $label, 'name' => $label];
                            }


                            ?>

                            <select
                                class="form-control selectpicker"
                                id="acadmic_year"
                                name="acadmic_year"
                                required
                                data-width="100%"
                                data-none-selected-text="No Selection"
                                data-actions-box="true">
                                <?php foreach ($years as $year): ?>
                                    <option value="<?= htmlspecialchars($year['id']) ?>">
                                        <?= htmlspecialchars($year['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <?= render_select(
                            'year',
                            $years_array,
                            ['id', 'name'],
                            'Year',
                            $hostel_quotation_data->year ?? '',
                            ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, "required" => "required"]
                        );
                        ?>
                    </div>

                    <?php

                    if (!empty($get_clients_fees)) {
                        foreach ($get_clients_fees as $fee) {
                            if (in_array($fee['id'], [3, 5, 6, 7])) {
                    ?>
                                <div class="col-md-6 form-group rental-details-fee rental-details-fee-<?= $fee['id'] ?>">
                                    <label><?= $fee["quotation_name"] ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-addon currency-symbol-amount_<?= $fee['id'] ?>">
                                            <?= $currency_lookup[!empty($applicant_payment_data->ex_currency) ? $applicant_payment_data->ex_currency : 3]["symbol"] ?? '' ?>
                                        </div>
                                        <input type="hidden" class="fee_id" name="fee_id_<?= $fee['id'] ?>" value="<?= $fee['id'] ?>">
                                        <input type="hidden" class="fees_name" name="fees_name_<?= $fee['id'] ?>" value="<?= $fee['name'] ?>">
                                        <input type="number" name="amount_<?= $fee['id'] ?>" data-name="amount" class="form-control amount currency-amount"
                                            placeholder="0.00" required value="">
                                        <div class="input-group-addon">
                                            <select name="ex_currency_<?= $fee['id'] ?>" data-id="amount_<?= $fee['id'] ?>" data-name="ex_currency"
                                                class="currency-selector currency-selector-amount disabled ex_currency"
                                                onchange="updateSymbol_(this,'amount_<?= $fee['id'] ?>')">
                                                <?php foreach ($get_currencies as $c): ?>
                                                    <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                        <?= htmlspecialchars($c['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                    <?php
                            }
                        }
                    }
                    ?>


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
        tAPI = initDataTable('.table-hostel_rental', admin_url + 'hostel_management/rental_table/russia', [0], [0], CustomersServerParams);

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


            let rentals = [];

            $('.rental-details-fee').each(function() {
                var feeId = $(this).find('input.fee_id').val();
                var amount = $(this).find('input.amount').val();
                var currency = $(this).find('select.ex_currency').val();
                var currency_name = $(this).find('select.ex_currency option:selected').text().trim();
                var name = $(this).find('input.fees_name').val();


                rentals.push({
                    fee_id: feeId,
                    amount: amount,
                    currency: currency,
                    currency_name: currency_name,
                    name: name
                });
            });

            formData.append('rental_details', JSON.stringify(rentals));

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
                        // $("#hostel_rental").find('select').selectpicker('refresh');
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
        if (form.validate) {
            form.validate().resetForm();
        }
        form.find('.error').removeClass('error');

        let decodedData = {};
        let feesDetails = [];

        try {
            // Decode base64 data safely
            decodedData = JSON.parse(atob(base64_decode));
        } catch (error) {
            console.error("❌ Failed to decode base64 data:", error);
            alert("Error: Invalid data received.");
            return;
        }

        // Safely parse rental_details (may be null or empty)
        if (decodedData.rental_details) {
            try {
                feesDetails = JSON.parse(decodedData.rental_details);
                if (!Array.isArray(feesDetails)) {
                    feesDetails = [];
                }
            } catch (error) {
                console.warn("⚠️ Invalid JSON in rental_details, using empty array.");
                feesDetails = [];
            }
        }

        // Populate form fields with safe fallbacks
        form.find('input[name="rental_id"]').val(id || '');
        form.find('select[name="hostel_id"]').val(decodedData.hostel_id || '').selectpicker('refresh');
        form.find('select[name="academic_year"]').val(decodedData.acadmic_year || '').selectpicker('refresh');
        form.find('select[name="year"]').val(decodedData.year || '').selectpicker('refresh');
        form.find('select[name="room_capacity"]').val(decodedData.room_capacity || '').selectpicker('refresh');
        form.find('select[name="currency"]').val(decodedData.currency || '').selectpicker('refresh');
        form.find('input[name="rent"]').val(decodedData.rent || '');

        // Populate fee details if available
        if (feesDetails.length > 0) {
            for (let fee of feesDetails) {
                if (!fee || !fee.fee_id) continue; // skip invalid entries

                form.find(`input[name="amount_${fee.fee_id}"]`).val(fee.amount || '');
                form.find(`select[name="ex_currency_${fee.fee_id}"]`).val(fee.currency || '').trigger('change');
            }
        } else {
            console.warn("ℹ️ No rental fee details found or rental_details was null.");
        }

        console.log("✅ Decoded data:", decodedData);
        console.log("✅ Fee details:", feesDetails);

        // Show modal
        $('#hostel_rental').modal('show');
    }


    $('#hostel_rental').on('hidden.bs.modal', function() {
        // Clear hidden ID
        $(this).find('input[name="rental_id"]').val('');
        // Optional: reset the entire form
        $(this).find('form')[0].reset();
        $(this).find('form select.selectpicker').selectpicker('refresh');
        // $(this).find('form select').selectpicker('refresh');
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