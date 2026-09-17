<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
// ✅ Fetch dropdown data safely
$visa_vendor = getDataInformation('external_visa_vendor', ['id', 'name'], ['status' => 1]);
$visa_type   = getDataInformation('external_visa_type', ['id', 'name'], ['status' => 1]);
$payment_mode   = getDataInformation('external_payment_mode', ['id', 'name'], ['status' => 1]);
$visa_status   = getDataInformation('external_visa_status', ['id', 'name'], ['status' => 1]);
$payment_mode_deposite   = getDataInformation('external_payment_mode', ['id', 'name'], ['deposite' => 1]);

// $visa_status = [
//     ['id' => 1, 'name' => 'Yes'],
//     ['id' => 0, 'name' => 'No']
// ];

if (!empty(!empty($country))) {
    $country[] = array('country_id' => '999', 'country_name' => 'India');
}
?>

<div id="wrapper" class="visa_details">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('External Visa Details'); ?></h4>
                        <hr>

                        <?= form_open('', ['id' => 'visa_form']); ?>
                        <div class="row">
                            <input type="hidden" name="id" value="<?= isset($id) ? $id : ''; ?>">
                            <div class="col-md-3">
                                <?= render_input('name', 'Name', $visaData->name ?? '', 'text', ['placeholder' => 'Student Name']); ?>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Gender <small class="text-danger">*</small></label>
                                    <select class="form-control" name="gender" id="gender" required required-check>
                                        <option value="">Select</option>
                                        <option <?php echo ($visaData->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option <?php echo ($visaData->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option <?php echo ($visaData->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('dob', 'Date of Birth', $visaData->dob ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input(
                                    'adhar',
                                    'Adhar Card Front/Back (.pdf)',
                                    $visaData->adhar ?? '',
                                    'file',
                                    [
                                        "accept" => "image/*,application/pdf",
                                        // empty($visaData->adhar) ? 'required' : false => "true"
                                    ]
                                ); ?>

                                <?php
                                if (!empty($visaData->adhar)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($visaData->adhar) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($visaData->adhar) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('visa_file', 'Visa (image/*,application/pdf)', $visaData->visa_file ?? '', 'file', ["accept" => "image/*,application/pdf"]); ?>

                                <?php
                                if (!empty($visaData->visa_file)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($visaData->visa_file) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($visaData->visa_file) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select(
                                    'visa_vendor',
                                    $visa_vendor,
                                    ['id', 'name'],
                                    'Visa Vendor',
                                    [$visaData->visa_vendor ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected',
                                        'data-actions-box' => true
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'visa_type',
                                    $visa_type,
                                    ['id', 'name'],
                                    'Visa Type',
                                    [$visaData->visa_type ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected',
                                        'data-actions-box' => true
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select(
                                    'visa_status',
                                    $visa_status,
                                    ['id', 'name'],
                                    'Visa Status',
                                    [$visaData->visa_status ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('visa_app_date', 'Visa Application Date', $visaData->visa_app_date ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('visa_rec_date', 'Receiving Date', $visaData->visa_rec_date ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'payment_mode',
                                    $payment_mode,
                                    ['id', 'name'],
                                    'Payment Mode',
                                    [$visaData->payment_mode ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('payment_date', 'Payment Date',  $visaData->payment_date ?? '', 'date'); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('visa_cost', 'Visa Cost',   $visaData->visa_cost ?? '', 'number', ['placeholder' => 'Visa Cost']); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('insurance_cost', 'Insurance Cost',  $visaData->insurance_cost ?? '', 'number', ['placeholder' => 'Insurance Cost']); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_select(
                                    'country',
                                    $country,
                                    ['country_id', 'country_name'],
                                    'Country',
                                    [$visaData->country ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>
                            
                            <div class="col-md-3">
                               <?= render_input(
    'reference_name',
    'Reference Name',
    $visaData->reference_name ?? '',
    'text',
    [
        'placeholder' => 'Reference Name',
        'pattern' => '[A-Za-z\s]+',
        'title' => 'Only letters and spaces are allowed'
    ]
); ?>

                            </div>

                         
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input(
                                    'passport',
                                    'Passport Number',
                                    $visaData->passport ?? '',
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
                                <?= render_input('issue_date', 'Issue Date', $visaData->issue_date ?? '', 'date'); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('exp_date', 'Exp Date', $visaData->exp_date ?? '', 'date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_input('passport_file', 'Passport Card Front/Back (.pdf)', $visaData->passport_file ?? '', 'file', ["accept" => "image/*,application/pdf"]); ?>

                                <?php
                                if (!empty($visaData->passport_file)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($visaData->passport_file) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($visaData->passport_file) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <?= render_input('deposite_amount', 'Deposit Amount', $visaData->deposite_amount ?? '', 'number', ['placeholder' => 'Deposit Amount']); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('deposite_date', 'Deposit Date', $visaData->deposite_date ?? '', 'date'); ?>
                            </div>
                            
                               <div class="col-md-3">
                                <?= render_select(
                                    'deposite_mode',
                                    $payment_mode_deposite,
                                    ['id', 'name'],
                                    'Deposit Mode',
                                    [$visaData->deposite_mode ?? ''] ?? '',
                                    [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'No Selected'
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-3 hide">
                                <?= render_input('minor', 'Minor Aff (image/*,application/pdf)', $visaData->minor ?? '', 'file', ["accept" => "image/*,application/pdf"]); ?>

                                <?php
                                if (!empty($visaData->minor)) {
                                ?>
                                    <div class="margin-top">
                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($visaData->minor) ?>');"></i>
                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($visaData->minor) ?>`, '_blank');"></i>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_textarea('remark', 'Remark', $visaData->remark ?? '', ['placeholder' => 'Enter Remark']); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right mtop20">
                                <button type="submit" class="btn btn-primary"><?= !empty($id) ? "Update Visa Details" : 'Create Visa Details'; ?></button>
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
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("reference_name");

    input.addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z\s]/g, ""); 
    });
});
</script>


<script>
    // Optional form validation or AJAX submission
    $(function() {
        // Initialize form validation
        appValidateForm($('#visa_form'), {
            name: 'required',
            gender: 'required',
            // dob: 'required',
            // adhar: 'required',
            visa_vendor: 'required',
            visa_type: 'required'
        });

        // Form submit handler
        $('#visa_form').on('submit', function(e) {
            e.preventDefault(); // Prevent default submit

            var form = $(this);

            // Check if form is valid
            if (!form.valid()) {
                // If validation fails, stop submission
                return false;
            }

            var url = '<?= admin_url("clients/save_visa_details"); ?>';
            var formData = new FormData(this);

            // Append country_name from select
            var countryText = $('#country option:selected').text() || '';
            formData.append('country_name', countryText);

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
                        window.location.href = '<?= admin_url("clients/visa_details"); ?>';
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