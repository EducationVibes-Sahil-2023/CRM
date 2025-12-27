<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php

// ✅ Fetch dropdown data safely
$ticket_vendor = getDataInformation('external_ticket_vendor', ['id', 'name'], ['status' => 1]);
$ticket_type   = getDataInformation('external_visa_type', ['id', 'name'], ['status' => 1]);
$payment_mode   = getDataInformation('external_payment_mode', ['id', 'name'], ['status' => 1]);
$ticket_status   = getDataInformation('external_visa_status', ['id', 'name'], ['status' => 1]);
$payment_mode_deposite   = getDataInformation('external_payment_mode', ['id', 'name'], ['deposite' => 1]);
$flight_type   = getDataInformation('flight_type', ['id', 'name'], ['status' => 1]);
$flight_departure   = getDataInformation('departure_location', ['id', 'name'], ['status' => 1]);
$airline   = getDataInformation('airline', ['id', 'name'], ['status' => 1]);


        if(is_admin())
    {
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// print_r($ticket_vendor);
    }
// $visa_status = [
//     ['id' => 1, 'name' => 'Yes'],
//     ['id' => 0, 'name' => 'No']
// ];

if (!empty(!empty($country))) {
    $country[] = array('country_id' => '999', 'country_name' => 'India');
}
?>

<div id="wrapper" class="ticket_details">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-9"><h4 class=""><?php echo _l('External Ticket Details'); ?></h4></div>
                            <div class="col-md-3">
                                  <?php 
             if (has_permission('fly_batch', '', 'create')) {        
                    
                        ?>
                         <div class="">
                                <?= render_select(
    'ticket_status',
    $ticketStatus,
    ['id', 'name'],
    '',
    $ticketData->ticket_status ?? '', // selected value
    [
        'data-width' => '100%',
        'data-none-selected-text' => false, // remove blank
        'data-actions-box' => false
    ]
); ?>

                            </div>
                        <?php
                    }
                    ?>
                            </div>
                        </div>
                        <hr>

                        <?= form_open('', ['id' => 'ticket_form']); ?>
                        <div class="row">
                            <input type="hidden" name="id" value="<?= isset($id) ? $id : ''; ?>">
                            <div class="col-md-3">
                                <?= render_input('name', 'Name', $ticketData->name ?? '', 'text', ['placeholder' => 'Name']); ?>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Gender <small class="text-danger">*</small></label>
                                    <select class="form-control" name="gender" id="gender" required required-check>
                                        <option value="">Select</option>
                                        <option <?php echo ($ticketData->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option <?php echo ($ticketData->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option <?php echo ($ticketData->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('dob', 'Date of Birth', $ticketData->dob ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input(
                                    'adhar',
                                    'Adhar Card Front/Back (.pdf)',
                                    $ticketData->adhar ?? '',
                                    'file',
                                    [
                                        "accept" => "image/*,application/pdf",
                                        empty($ticketData->adhar) ? '' : false => ""
                                    ]
                                ); ?>

                                <?php
                                if (!empty($ticketData->adhar)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($ticketData->adhar) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($ticketData->adhar) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('ticket_file', 'Ticket (image/*,application/pdf)', $ticketData->ticket_file ?? '', 'file', ["accept" => "image/*,application/pdf"]); ?>

                                <?php
                                if (!empty($ticketData->ticket_file)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($ticketData->ticket_file) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($ticketData->ticket_file) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select(
                                    'ticket_vendor',
                                    $ticket_vendor,
                                    ['id', 'name'],
                                    'Ticket Vendor',
                                    [$ticketData->ticket_vendor ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected',
                                        'data-actions-box' => true
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'ticket_type',
                                    $ticket_type,
                                    ['id', 'name'],
                                    'Ticket Type',
                                    [$ticketData->ticket_type ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected',
                                        'data-actions-box' => true
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                               <?= render_select(
    'airline',
    $airline,
    ['id', 'name'],
    'Airline',
    !empty($ticketData->airline) ? explode(',', $ticketData->airline) : [],
    [
        'data-width' => '100%',
        'data-none-selected-text' => 'No Selected',
        'multiple' => false,
        'data-max-options' => '1'
    ],
    [],
    'no-mbot',
    '',
    false,
    'airline'
); ?>

                            </div>
                           

                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('flight_date', 'Flight Date', $ticketData->flight_date ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'flight_type',
                                    $flight_type,
                                    ['id', 'name'],
                                    'Flight Type',
                                    [$ticketData->flight_type ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'departure_id',
                                    $flight_departure,
                                    ['id', 'name'],
                                    'Flight Departure',
                                    [$ticketData->departure_id ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'destination_id',
                                    $flight_departure,
                                    ['id', 'name'],
                                    'Flight Destination',
                                    [$ticketData->destination_id ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('ticket_cost', 'Ticket Cost',   $ticketData->ticket_cost ?? '', 'number', ['placeholder' => 'Ticket Cost']); ?>
                            </div>



                            <div class="col-md-3">
                                <?= render_select(
                                    'country',
                                    $country,
                                    ['country_id', 'country_name'],
                                    'Country',
                                    [$ticketData->country ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select(
                                    'payment_mode',
                                    $payment_mode,
                                    ['id', 'name'],
                                    'Payment Mode',
                                    [$ticketData->payment_mode ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('payment_date', 'Payment Date',  $ticketData->payment_date ?? '', 'date'); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input(
                                    'passport',
                                    'Passport Number',
                                    $ticketData->passport ?? '',
                                    'text',
                                    [
                                        "placeholder" => "Passport Number",
                                        "pattern"   => "^[A-Z0-9]{6,9}$",
                                        "title"     => "Passport number must be 6 to 9 characters, only uppercase letters (A-Z) and numbers (0-9).",
                                        "maxlength" => "9",
                                        "minlength" => "6"
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_input('issue_date', 'Issue Date', $ticketData->issue_date ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('exp_date', 'Exp Date', $ticketData->exp_date ?? '', 'date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_input('passport_file', 'Passport Card Front/Back (.pdf)', $ticketData->passport_file ?? '', 'file', ["accept" => "image/*,application/pdf"]); ?>

                                <?php
                                if (!empty($ticketData->passport_file)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($ticketData->passport_file) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($ticketData->passport_file) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <?= render_select(
                                    'deposite_mode',
                                    $payment_mode_deposite,
                                    ['id', 'name'],
                                    'Deposit Mode',
                                    [$ticketData->deposite_mode ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('deposite_amount', 'Deposit Amount', $ticketData->deposite_amount ?? '', 'number', ['placeholder' => 'Deposit Amount']); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('deposite_date', 'Deposit Date', $ticketData->deposite_date ?? '', 'date'); ?>
                            </div>


                            <div class="col-md-3">
                                <?= render_textarea('remark', 'Remark', $ticketData->remark ?? '', ['placeholder' => 'Enter Remark']); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right mtop20">
                                <button type="submit" class="btn btn-primary"><?= !empty($id) ? "Update Tickey Details" : 'Create Ticket Details'; ?></button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    // Optional form validation or AJAX submission
    $(function() {
        // Initialize form validation
        appValidateForm($('#ticket_form'), {
            name: 'required',
            gender: 'required',
            // dob: 'required',
            // adhar: 'required',
            ticket_vendor: 'required',
            ticket_type: 'required'
        });

        // Form submit handler
        $('#ticket_form').on('submit', function(e) {
            e.preventDefault(); // Prevent default submit

            var form = $(this);

            // Check if form is valid
            if (!form.valid()) {
                // If validation fails, stop submission
                return false;
            }

            var url = '<?= admin_url("clients/save_ticket_details"); ?>';
            var formData = new FormData(this);

            // Append country_name from select
            var countryText = $('#country option:selected').text() || '';
            formData.append('country_name', countryText);
            var ticket_status = $('#ticket_status').val() || 0;
formData.append('ticket_status', ticket_status);

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
                        window.location.href = '<?= admin_url("clients/ticket_details"); ?>';
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


    document.getElementById("passport").addEventListener("input", function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Convert to uppercase & remove invalid characters
    });
</script>