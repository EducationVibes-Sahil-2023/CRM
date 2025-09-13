<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">


    <div class="panel-body">
        <?php
        $quotation_table = array(
            "University Name",
            "Acadmic Year",
            "Year",
            "PDF",
        );
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-container">
                    <h4 class="fs-title">Quotations</h4>
                    <hr>

                    <?php
                    render_datatable($quotation_table, 'quotation-table');
                    ?>
                    <br>
                </div>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tAPI = ""
        $(function() {

            // Ensure you get the client_id value dynamically

            tAPI = initDataTable('.table-quotation-table', admin_url + 'clients/quotation_table/' + <?= $client_id ?>);

        });
    });
</script>
<?php
if (has_permission('customers', '', 'quotation_create')) {

    $primary_university = $admissionpreferences->primary_university ?? "";
    $acadmic_year       = $admissionpreferences->acadmic_year ?? "";

    $company_dues_fees_array = $this->db->select("*")->from(db_prefix() . "company_dues_fees")->get()
        ->result_array();
    $fees_details_array = [
        ["label" => "Total Service Charge", "name" => "total_service_charge", "readonly" => true, "add_btn" => true],
    ];

    $university_applicant_fees = university_applicant_fees("", 1, [
        "university_name" => $primary_university,
        "acadmic_year"    => $acadmic_year
    ]);

    $university_applicant_fees_array = university_applicant_fees_details([
        "fd.university_name" => $primary_university,
        "fd.acadmic_year"    => $acadmic_year
    ]);


    $years_array = array_keys($university_applicant_fees_array);

    $get_clients_fees = [];
    $university_shortlisting = [];
    $partnerName = []; // initialize

    if (!empty($client_id)) {
        $get_clients_fees  = get_clients_fees_details($lead_type_status, $client_id);
        $university_shortlisting = $this->clients_model->university_shortlisting($client_id, 1);

        if (!empty($university_shortlisting[0]["partner"])) {
            $partnerName = $this->db
                ->select("id, name")
                ->from(db_prefix() . "university_partner")
                ->where("id", $university_shortlisting[0]["partner"])
                ->get()
                ->row_array(); // ✅ Correct method
        }
    }



    $sql = "
    SELECT 
        aq.*,
        CONCAT('Q', ROW_NUMBER() OVER (PARTITION BY aq.university_name ORDER BY aq.id ASC)) AS quotation_label,
        CONCAT(aq.university_name, '-', aq.acadmic_year, '-', aq.year,' Year', ' - ',
               'Q', ROW_NUMBER() OVER (PARTITION BY aq.university_name ORDER BY aq.id ASC)
        ) AS unique_id
    FROM " . db_prefix() . "applicant_quotation_payment aq
    WHERE aq.client_id = ?
    ORDER BY aq.id DESC
";


    $applicant_quotations = $this->db->query($sql, [$client_id])->result_array();
    array_unshift($applicant_quotations, array("id" => "", "name" => ""));

    $get_currencies    = get_currencies();
    $currency_lookup   = array_column($get_currencies, null, 'id');
    $modes =  $this->quotation_model->payment_mod();
    $modes_vendor =  $this->quotation_model->payment_mode_vendors();
    array_unshift($modes, array("id" => "", "name" => "Select Mode"));
    $applicant_quotation_data = [];
    $exchange_value_array = [];
    $university_due_array = [];
    $company_due_array = [];
    $fees_data = [];
    $quotation_id = !empty($_GET['quotation_id']) ? $_GET['quotation_id'] : '';
    if (!empty($_GET['quotation_id'])) {
        $applicant_quotation_data =  $this->quotation_model->applicant_quotation_data($client_id, $_GET['quotation_id']);
        $exchange_value_array = json_decode($applicant_quotation_data->exchange_value, true);
        $university_due_array = json_decode($applicant_quotation_data->university_due, true);
        $company_due_array = json_decode($applicant_quotation_data->company_due, true);
    }

    $selected_mod = 0;

    $quotation_paymente_mode = $this->db
        ->select('*')
        ->from(db_prefix() . 'quotation_paymente_mode')
        ->get()
        ->result_array();

?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel_s">


                <div class="panel-body">


                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php
                            echo render_select(
                                'applicant_quotation',
                                $applicant_quotations,
                                ['id', 'unique_id'], // first = value, second = label
                                'Applicant Quotations',
                                [$quotation_id ?? ''],
                                [
                                    'data-width' => '100%',
                                    'data-none-selected-text' => 'Applicant Quotations',
                                    'onchange' => 'check_quotations(this.value)'
                                ],
                                [],
                                'no-mbot',
                                '',
                                false,
                                'applicant_quotation'
                            );
                            ?>
                        </div>
                        <?php if (has_permission('customers', '', 'quotation_create')) { ?>
                            <div class="form-group col-md-3 text-right  ">
                                <label for="release_to_counsellor"><br>Release to Counsellor</label>
                                <input type="checkbox" value="1" id="release_to_counsellor" <?= !empty($applicant_quotation_data->release_to_counsellor) ? 'checked' : '' ?> name="release_to_counsellor">
                            </div>
                            <?php if (!empty($quotation_id) && !empty($applicant_quotation_data)) { ?>
                                <div class="form-group col-md-3 text-right  ">
                                    <button class="btn btn-primary"
                                        onclick="window.open('<?= $applicant_quotation_data->pdf ?>', '_blank')">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                    <button class="btn btn-primary" onclick="GeneratePDF('<?= $client_id ?>','<?= $quotation_id ?>')">Generate PDF</button>
                                </div>
                            <?php } ?>
                        <?php } ?>

                    </div>

                    <h4>Applicant Quotation</h4>
                    <hr class="hr-panel-heading" />

                    <!-- The form below must have id="applicant-quotation-form" for JS to work. Do not remove or change this ID. -->
                    <?= form_open(admin_url('clients/quotation'), ['id' => 'applicant-quotation-form']); ?>
                    <input hidden name="client_id" value="<?= $client_id ?>">
                    <!-- University Info -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="university_name">University Name <small class="text-danger">*</small></label>
                                <input type="text" class="form-control" name="university_name" id="university_name" readonly
                                    value="<?= htmlspecialchars(!empty($applicant_quotation_data->university_name) ? $applicant_quotation_data->university_name : $primary_university); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="acadmic_year">Academic Year <small class="text-danger">*</small></label>
                                <input type="text" class="form-control" name="acadmic_year" id="acadmic_year" readonly
                                    value="<?= htmlspecialchars(!empty($applicant_quotation_data->acadmic_year) ? $applicant_quotation_data->acadmic_year : $acadmic_year); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?php
                                // Prepare year options (1 to 8, or from $years_array)
                                $year_options = array_map(fn($year) => [
                                    'id'   => $year,
                                    'name' => $year . ' Year'
                                ], $years_array);

                                echo render_select(
                                    'study_year',
                                    $year_options,
                                    ['id', 'name'],
                                    html_entity_decode('Year <small class="text-danger">*</small>'),
                                    $applicant_quotation_data->year ?? '',
                                    [
                                        'data-width'              => '100%',
                                        'data-none-selected-text' => 'Select Year',
                                        'required'                => true,
                                        'onchange'                => 'fetchApplicantFees(this.value)',
                                    ]
                                );
                                ?>
                            </div>
                        </div>


                    </div>

                    <!-- Currency Exchange -->
                    <div class="panel_s shadow">
                        <div class="panel-body">
                            <h4 class="text-bold">Currency Exchange Rates</h4>
                            <hr>
                            <table class="table table-bordered" id="exchangeTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%; text-align: center;">Currency</th>
                                        <th style="width: 40%; text-align: center;">Exchange Value</th>
                                        <th style="width: 20%; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="exchangeTableBody">

                                    <?php if (!empty($exchange_value_array)) : ?>
                                        <?php foreach ($exchange_value_array as $key => $exchange): ?>
                                            <tr>
                                                <td>
                                                    <select name="exchange_currency[]" class="form-control" onchange="calculateInrValue()">
                                                        <?php foreach ($get_currencies as $c): ?>
                                                            <option value="<?= $c['id'] ?>"
                                                                data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                <?= ($c['id'] == $exchange['currency_id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($c['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control currency-amount"
                                                        oninput="calculateInrValue()" name="exchange_value[]"
                                                        placeholder="0.00" value="<?= htmlspecialchars($exchange['exchange_value']) ?>">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-<?= $key == 0 ? 'success' : 'danger' ?> btn-sm <?= $key == 0 ? 'addRow' : 'removeRow' ?>">
                                                        <i class="fa fa-<?= $key == 0 ? 'plus' : 'minus' ?>"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td>
                                                <select name="exchange_currency[]" class="form-control" onchange="calculateInrValue()">
                                                    <?php foreach ($get_currencies as $c): ?>
                                                        <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                            <?= htmlspecialchars($c['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control currency-amount"
                                                    oninput="calculateInrValue()" name="exchange_value[]" placeholder="0.00">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-success btn-sm addRow">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="panel_s">
                        <div class="panel-body">
                            <!-- Applicant Fees Quotations -->
                            <?php if (!empty($university_applicant_fees) && !empty($get_currencies)) : ?>
                                <div id="applicant_fees">
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-6">
                                            <h4 class="mb-0">Applicant Fees Quotations (University Dues)</h4>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="button" class="btn btn-primary" onclick="newUniversityDue()">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="main-university-due">
                                        <table class="table table-bordered university-feesTable" id="feesTable">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Fees Info</th>
                                                    <th class="text-center">Exchange Value</th>
                                                    <th class="text-center">Payment Option</th>
                                                    <th class="text-center">Value INR</th>
                                                </tr>
                                            </thead>
                                            <tbody id="university_dues">
                                                <?php if (!empty($university_due_array)) {
                                                    foreach ($university_due_array["main"]["fees_info"] as $fees) {
                                                        $id         = $fees["id"];
                                                        $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                                        $symbol     = $currency_lookup[$fees["currency_id"]]["symbol"]
                                                            ?? $currency_lookup[$fees["university_quotation_currency"]]["symbol"]
                                                            ?? '$';
                                                ?>
                                                        <tr class="fee-row" data-id="<?= $id ?>">
                                                            <td>
                                                                <label><?= htmlspecialchars($fees['quotation_name']) ?> <small class="text-danger">*</small></label>
                                                                <div class="input-group form-group">
                                                                    <input type="hidden" name="applicant_fees[]" value="<?= $field_name ?>">
                                                                    <input type="hidden" name="quotation_name" value="<?= htmlspecialchars($fees['quotation_name']) ?>">
                                                                    <input type="hidden" name="<?= $field_name ?>_id" value="<?= $fees['id'] ?>">
                                                                    <input type="hidden" name="<?= $field_name ?>_detail_id_<?= $fees['id'] ?>" value="<?= $fees['detail_id'] ?? '' ?>">

                                                                    <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                                        <?= htmlspecialchars($symbol) ?>
                                                                    </div>

                                                                    <input type="text" name="<?= $field_name ?>_amount" required
                                                                        oninput="calculateInrValue()"
                                                                        class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                                        placeholder="0.00" value="<?= $fees["amount"] ?? '' ?>">

                                                                    <div class="input-group-addon">
                                                                        <select name="<?= $field_name ?>_currency_type"
                                                                            class="currency-selector currency-selector-<?= $id ?>"
                                                                            onchange="calculateInrValue(); updateSymbol_(this,<?= $id ?>)">
                                                                            <?php foreach ($get_currencies as $c): ?>
                                                                                <option data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                                    value="<?= $c['id'] ?>"
                                                                                    <?= ((!empty($fees['currency_id']) && $fees['currency_id'] == $c['id'])
                                                                                        || (empty($fees['currency_id']) && ($fees['university_quotation_currency'] ?? '') == $c['id']))
                                                                                        ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($c['name']) ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <label>Exchange Value</label>
                                                                <input type="text" class="form-control exchangeValue" readonly
                                                                    placeholder="0.00" name="<?= $field_name ?>_exchangeValue"
                                                                    value="<?= $fees['exchange_value'] ?? '' ?>">
                                                            </td>
                                                            <td>
                                                                <label>Payment Option <small class="text-danger">*</small></label>
                                                                <select <?= $fees["disabled"] == 1 ? "disabled" : "" ?> <?= $fees["payment_option_status"] == 1 ? "disabled" : "" ?> name="<?= $field_name ?>_payment_option" class="form-control" required>
                                                                    <option value="">Select Payment Option</option>
                                                                    <?php foreach ($quotation_paymente_mode as $payment_mode) { ?>
                                                                        <option value="<?= $payment_mode['id'] ?>" <?= $fees["payment_option"] == $payment_mode['id'] ? "selected" : "" ?>><?= $payment_mode['name'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <label>Value INR <small class="text-danger">*</small></label>
                                                                <div class="input-group form-group">
                                                                    <div class="input-group-addon">₹</div>
                                                                    <input type="number" step="0.01" name="<?= $field_name ?>_inr_value"
                                                                        required class="form-control currency-amount  fees-inr-value-<?= $id ?>" readonly
                                                                        placeholder="0.00" value="<?= $fees["inr_value"] ?? '' ?>">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else { ?>
                                                    <?php foreach ($university_applicant_fees as $fees):
                                                        $id         = $fees["id"];
                                                        $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                                        $symbol     = $currency_lookup[$fees["currency_id"]]["symbol"]
                                                            ?? $currency_lookup[$fees["university_quotation_currency"]]["symbol"]
                                                            ?? '$';
                                                    ?>
                                                        <tr class="fee-row" data-id="<?= $id ?>">
                                                            <td>
                                                                <label><?= htmlspecialchars($fees['quotation_name']) ?> <small class="text-danger">*</small></label>
                                                                <div class="input-group form-group">
                                                                    <input type="hidden" name="applicant_fees[]" value="<?= $field_name ?>">
                                                                    <input type="hidden" name="quotation_name" value="<?= htmlspecialchars($fees['quotation_name']) ?>">
                                                                    <input type="hidden" name="<?= $field_name ?>_id" value="<?= $fees['id'] ?>">
                                                                    <input type="hidden" name="<?= $field_name ?>_detail_id_<?= $fees['id'] ?>" value="<?= $fees['detail_id'] ?? '' ?>">

                                                                    <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                                        <?= htmlspecialchars($symbol) ?>
                                                                    </div>

                                                                    <input type="text" name="<?= $field_name ?>_amount" required
                                                                        oninput="calculateInrValue()"
                                                                        class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                                        placeholder="0.00" value="<?= $fees["amount"] ?? '' ?>">

                                                                    <div class="input-group-addon">
                                                                        <select name="<?= $field_name ?>_currency_type"
                                                                            class="currency-selector currency-selector-<?= $id ?>"
                                                                            onchange="calculateInrValue(); updateSymbol_(this,<?= $id ?>)">
                                                                            <?php foreach ($get_currencies as $c): ?>
                                                                                <option data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                                    value="<?= $c['id'] ?>"
                                                                                    <?= ((!empty($fees['currency_id']) && $fees['currency_id'] == $c['id'])
                                                                                        || (empty($fees['currency_id']) && ($fees['university_quotation_currency'] ?? '') == $c['id']))
                                                                                        ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($c['name']) ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <label>Exchange Value</label>
                                                                <input type="text" class="form-control exchangeValue" readonly
                                                                    placeholder="0.00" name="<?= $field_name ?>_exchangeValue"
                                                                    value="<?= $fees['exchange_value'] ?? '' ?>">
                                                            </td>
                                                            <td>
                                                                <label>Payment Option <small class="text-danger">*</small></label>
                                                                <select <?= $fees["disabled"] == 1 ? "disabled" : "" ?> name="<?= $field_name ?>_payment_option" class="form-control" required>
                                                                    <option value="">Select Payment Option</option>
                                                                    <?php foreach ($quotation_paymente_mode as $payment_mode) { ?>
                                                                        <option value="<?= $payment_mode['id'] ?>" <?= $fees["option_payment"] == $payment_mode['id'] ? "selected" : "" ?>><?= $payment_mode['name'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <label>Value INR <small class="text-danger">*</small></label>
                                                                <div class="input-group form-group">
                                                                    <div class="input-group-addon">₹</div>
                                                                    <input type="number" step="0.01" name="<?= $field_name ?>_inr_value"
                                                                        required class="form-control currency-amount fees-inr-value-<?= $id ?>" readonly
                                                                        placeholder="0.00" value="<?= $fees["value_inr"] ?? '' ?>">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    endforeach;
                                                }
                                                ?>

                                            </tbody>
                                            <tfoot id="main-university-due-pay">

                                                <tr>
                                                    <td colspan="">Pay To <small class="text-danger">*</small>

                                                    </td>
                                                    <td>
                                                        <select class="form-control" required name="university_pay_mode" onchange="vendor_update(this,this.value);">
                                                            <?php foreach ($modes as $m):
                                                            ?>
                                                                <option value="<?= $m['id'] ?>" <?= $university_due_array["main"]["pay_info"] ?> <?= !empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == $m["id"] ? "selected" : "" ?>>
                                                                    <?= htmlspecialchars($m['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    </td>
                                                    <td>
                                                        <select class="form-control" style="display:<?= (!empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == 5) ? 'none' : 'show' ?>" required id="university_pay_vendor" name="university_pay_vendor">
                                                            <?php if (!empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] != 5) { ?>
                                                                <option value="">Select Vendor</option>
                                                            <?php } ?>
                                                            <?php
                                                            if (!empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == 1 || $university_due_array["main"]['pay_info'][0]["payMode"] == 4) {
                                                                foreach ($modes_vendor as $vendor) {
                                                                    if ($vendor["mode"] == $university_due_array["main"]['pay_info'][0]["payMode"]) {
                                                            ?>
                                                                        <option value="<?= $vendor["id"] ?>" <?= $vendor["id"] == $university_due_array["main"]['pay_info'][0]["payVendor"] ? "selected" : "" ?>><?= $vendor["name"] ?></option>

                                                                <?php
                                                                    }
                                                                }
                                                            } else if (!empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == 2 || $university_due_array["main"]['pay_info'][0]["payMode"] == 3) {
                                                                ?>
                                                                <option value="<?= $university_due_array["main"]['pay_info'][0]["payVendor"] ?>" selected><?= $university_due_array["main"]['pay_info'][0]["payVendor"] ?></option>
                                                            <?php

                                                            } else if (!empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == 5) {
                                                            ?>
                                                                <option value="<?= $university_due_array["main"]['pay_info'][0]["payVendor"] ?>" selected><?= $university_due_array["main"]['pay_info'][0]["payVendor"] ?></option>
                                                            <?php
                                                            }

                                                            ?>
                                                        </select>

                                                        <?php
                                                        $payInfo_new = $university_due_array["main"]['pay_info'][0] ?? null;

                                                        if (!empty($payInfo_new) && isset($payInfo_new["payMode"]) && $payInfo_new["payMode"] == 5): ?>
                                                            <input
                                                                type="text"
                                                                name="manual_cash_vendor"
                                                                required
                                                                class="form-control manually-cash"
                                                                placeholder="Enter Vendor Name"
                                                                value="<?= !empty($payInfo_new["payVendor"]) ? htmlspecialchars($payInfo_new["payVendor"], ENT_QUOTES, 'UTF-8') : '' ?>">
                                                        <?php endif; ?>

                                                    </td>
                                                    <td>
                                                        <input type="input" readonly name="totalINRValue" class="form-control" value="<?= !empty($university_due_array["main"]['pay_info'][0]["totalINRValue"]) ? $university_due_array["main"]['pay_info'][0]["totalINRValue"] : 0 ?>">
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="aditional-university-due">

                                        <?php if (!empty($university_due_array["addition"])) {
                                            foreach ($university_due_array["addition"] as $addition) {

                                        ?>

                                                <?php
                                                ?>
                                                <div class="aditional-university-due-table">
                                                    <button type="button" class="btn btn-danger btn-sm mb-2"
                                                        onclick="$(this).parents('.aditional-university-due-table').remove();">
                                                        <i class="fa fa-minus"></i> Remove Due
                                                    </button>
                                                    <table class="table table-bordered university-feesTable" id="feesTable">



                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">Fees Info</th>
                                                                <th class="text-center">Exchange Value</th>
                                                                <th class="text-center">Payment Option</th>
                                                                <th class="text-center">Value INR</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="university_dues">
                                                            <?php
                                                            foreach ($addition["fees_info"] as $fees) {
                                                                $id         = $fees["id"];
                                                                $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                                                $symbol     = $currency_lookup[$fees["currency_id"]]["symbol"]
                                                                    ?? $currency_lookup[$fees["university_quotation_currency"]]["symbol"]
                                                                    ?? '$';
                                                            ?>
                                                                <tr class="fee-row" data-id="<?= $id ?>">
                                                                    <td>
                                                                        <label><?= htmlspecialchars($fees['quotation_name']) ?> <small class="text-danger">*</small></label>
                                                                        <div class="input-group form-group">
                                                                            <input type="hidden" name="applicant_fees[]" value="<?= $field_name ?>">
                                                                            <input type="hidden" name="quotation_name" value="<?= htmlspecialchars($fees['quotation_name']) ?>">
                                                                            <input type="hidden" name="<?= $field_name ?>_id" value="<?= $fees['id'] ?>">
                                                                            <input type="hidden" name="<?= $field_name ?>_detail_id_<?= $fees['id'] ?>" value="<?= $fees['detail_id'] ?? '' ?>">

                                                                            <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                                                <?= htmlspecialchars($symbol) ?>
                                                                            </div>

                                                                            <input type="text" name="<?= $field_name ?>_amount" required
                                                                                oninput="calculateInrValue()"
                                                                                class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                                                placeholder="0.00" value="<?= $fees["amount"] ?? '' ?>">

                                                                            <div class="input-group-addon">
                                                                                <select name="<?= $field_name ?>_currency_type"
                                                                                    class="currency-selector currency-selector-<?= $id ?>"
                                                                                    onchange="calculateInrValue(); updateSymbol_(this,<?= $id ?>)">
                                                                                    <?php foreach ($get_currencies as $c): ?>
                                                                                        <option data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                                            value="<?= $c['id'] ?>"
                                                                                            <?= ((!empty($fees['currency_id']) && $fees['currency_id'] == $c['id'])
                                                                                                || (empty($fees['currency_id']) && ($fees['university_quotation_currency'] ?? '') == $c['id']))
                                                                                                ? 'selected' : '' ?>>
                                                                                            <?= htmlspecialchars($c['name']) ?>
                                                                                        </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <label>Exchange Value</label>
                                                                        <input type="text" class="form-control exchangeValue" readonly
                                                                            placeholder="0.00" name="<?= $field_name ?>_exchangeValue"
                                                                            value="<?= $fees['exchange_value'] ?? '' ?>">
                                                                    </td>
                                                                    <td>
                                                                        <label>Payment Option <small class="text-danger">*</small></label>
                                                                        <select <?= $fees["disabled"] == 1 ? "disabled" : "" ?> name="<?= $field_name ?>_payment_option" class="form-control" required>
                                                                            <option value="">Select Payment Option</option>
                                                                            <?php foreach ($quotation_paymente_mode as $payment_mode) { ?>
                                                                                <option value="<?= $payment_mode['id'] ?>" <?= $fees["payment_option"] == $payment_mode['id'] ? "selected" : "" ?>><?= $payment_mode['name'] ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <label>Value INR <small class="text-danger">*</small></label>
                                                                        <div class="input-group form-group">
                                                                            <div class="input-group-addon">₹</div>
                                                                            <input type="number" step="0.01" name="<?= $field_name ?>_inr_value"
                                                                                required class="form-control currency-amount fees-inr-value-<?= $id ?>" readonly
                                                                                placeholder="0.00" value="<?= $fees["inr_value"] ?? '' ?>">
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="1">Pay To <small class="text-danger">*</small>
                                                                </td>
                                                                <td>
                                                                    <select class="form-control" required name="university_pay_mode" onchange="vendor_update(this,this.value);">
                                                                        <?php foreach ($modes as $m):
                                                                        ?>
                                                                            <option value="<?= $m['id'] ?>" <?= $addition["pay_info"] ?> <?= !empty($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == $m["id"] ? "selected" : "" ?>>
                                                                                <?= htmlspecialchars($m['name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>

                                                                </td>
                                                                <td>
                                                                    <select class="form-control" style="display:<?= (!empty($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == 5) ? 'none' : 'show' ?>" required id="university_pay_vendor" name="university_pay_vendor">
                                                                        <?php if (!empty($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == 1) { ?>
                                                                            <option value="">Select Vendor</option>
                                                                        <?php } ?>
                                                                        <?php
                                                                        if (!empty($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == 1 || $addition['pay_info'][0]["payMode"] == 4) {
                                                                            foreach ($modes_vendor as $vendor) {
                                                                                if ($vendor["mode"] == $addition['pay_info'][0]["payMode"]) {
                                                                        ?>
                                                                                    <option value="<?= $vendor["id"] ?>" <?= $vendor["id"] == $addition['pay_info'][0]["payVendor"] ? "selected" : "" ?>><?= $vendor["name"] ?></option>

                                                                            <?php
                                                                                }
                                                                            }
                                                                        } else if (!empty($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == 2 || $addition['pay_info'][0]["payMode"] == 3) {
                                                                            ?>
                                                                            <option value="<?= $addition['pay_info'][0]["payVendor"] ?>" selected><?= $addition['pay_info'][0]["payVendor"] ?></option>
                                                                        <?php

                                                                        } else if (!empty($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == 5) {
                                                                        ?>
                                                                            <option value="<?= $addition['pay_info'][0]["payVendor"] ?>" selected><?= $addition['pay_info'][0]["payVendor"] ?></option>
                                                                        <?php
                                                                        }

                                                                        ?>
                                                                    </select>

                                                                    <?php

                                                                    if (!empty($addition['pay_info'][0]) && isset($addition['pay_info'][0]["payMode"]) && $addition['pay_info'][0]["payMode"] == 5): ?>
                                                                        <input
                                                                            type="text"
                                                                            name="manual_cash_vendor"
                                                                            required
                                                                            class="form-control manually-cash"
                                                                            placeholder="Enter Vendor Name"
                                                                            value="<?= !empty($addition['pay_info'][0]["payVendor"]) ? htmlspecialchars($addition['pay_info'][0]["payVendor"], ENT_QUOTES, 'UTF-8') : '' ?>">
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <input type="input" readonly name="totalINRValue" class="form-control" value="<?= !empty($university_due_array["main"]['pay_info'][0]["totalINRValue"]) ? $university_due_array["main"]['pay_info'][0]["totalINRValue"] : 0 ?>">
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Company Dues -->
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="row align-items-center mb-2">
                                <div class="col-md-6">
                                    <h4 class="mb-0">Company Dues</h4>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-primary" onclick="newCompanyDue()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <hr>
                            <table class="table table-bordered" id="universityDue">
                                <thead>
                                    <tr>
                                        <th style="width:25%; text-align:center;">Fees Info</th>
                                        <th style="width:25%; text-align:center;">Value</th>
                                        <th style="width:25%; text-align:center;">Currency</th>
                                        <th style="width:25%; text-align:center;">Value INR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $mainInfo = !empty($company_due_array['main']["info"])
                                        ? array_values(array_filter($company_due_array['main']["info"]))
                                        : [];

                                    // ✅ If data exists, render from DB, else fallback to fees_details_array
                                    $feesSource = !empty($mainInfo) ? $mainInfo : $fees_details_array;

                                    foreach ($feesSource as $index => $fee):
                                        $labelName = strtolower(str_replace(" ", "_", $fee['label_name'] ?? $fee['name']));
                                        $isReadOnly = !empty($fee['readonly']) || $index === 0;
                                        if ($labelName == "" && $index == 0) {
                                            $labelName = "total_service_charge";
                                        }
                                    ?>
                                        <tr class="<?= $index == 0 ? '' : 'calculate' ?>">
                                            <td>
                                                <?php if (!empty($fee['id'])): ?>
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($fee['id']) ?>">
                                                <?php endif; ?>
                                                <select name="name[]" onchange="calculateInrValue();Change_companyDue(this);" class="form-control name" <?= $index == 0 ? 'disabled' : '' ?>>
                                                    <?php foreach ($company_dues_fees_array as $fees_data): ?>
                                                        <?php if ($fees_data["status"] == (!empty($index) ? 1 : 0)): ?>
                                                            <option
                                                                value="<?= htmlspecialchars($fees_data['id']) ?>"
                                                                data-add="<?= $fees_data["add_flag"] ?>"
                                                                <?= (!empty($fee["type"]) && $fees_data["id"] == $fee["type"]) ? "selected" : "" ?>>
                                                                <?= htmlspecialchars($fees_data['name']) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="fee_value[]"
                                                    class="form-control fee_value currency-amount <?= $labelName ?>"
                                                    placeholder="0.00"
                                                    value="<?= htmlspecialchars($fee['fee_value'] ?? '') ?>"
                                                    <?= $isReadOnly ? "readonly" : "" ?>>
                                                <?php if (!empty($fee['label'])): ?>
                                                    <input type="hidden" name="fee_info[]" value="<?= htmlspecialchars($fee['label']) ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select name="fee_currency[]"
                                                    class="form-control fee_currency"
                                                    <?= $isReadOnly ? "disabled" : "" ?>>
                                                    <?php foreach ($get_currencies as $c): ?>
                                                        <option value="<?= $c['id'] ?>"
                                                            data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                            <?= (!empty($fee["fee_currency"]) && $fee["fee_currency"] == $c['id']) ? "selected" : "" ?>>
                                                            <?= htmlspecialchars($c['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td class="d-flex">
                                                <input type="text"
                                                    readonly name="fee_value_inr[]"
                                                    class="form-control fee_value_inr <?= $labelName . '_inr' ?>"
                                                    placeholder="0.00"
                                                    value="<?= htmlspecialchars($fee["fee_value_inr"] ?? '') ?>">
                                                <?php if ($index == 0): ?>
                                                    <button type="button" onclick="university_due_add_column(this)" class="btn btn-primary">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                <?php elseif ($index >= 1): ?>
                                                    <button type="button" onclick="$(this).closest('tr').remove()" class="btn btn-danger">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                                <tfoot>
                                    <?php
                                    $payInfo = $company_due_array["main"]["pay_info"] ?? [$university_due_array["main"]["pay_info"][0] ?? []];
                                    foreach ($payInfo as $l_array):
                                        $payMode = $l_array["payMode"] ?? null;
                                        $payVendor = $l_array["payVendor"] ?? null;
                                        $payAmount = $l_array["payAmount"] ?? 0;
                                    ?>
                                        <tr class="table-warning">
                                            <td>
                                                <select class="form-control" required name="university_pay_mode" onchange="vendor_update(this,this.value);">
                                                    <?php foreach ($modes as $m): ?>
                                                        <option value="<?= $m['id'] ?>"
                                                            <?= (!empty($payMode) && $payMode == $m["id"]) ? "selected" : "" ?>>
                                                            <?= htmlspecialchars($m['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control" style="display:<?= (!empty($payMode) && $payMode == 5) ? 'none' : 'show' ?>" required name="university_pay_vendor" id="university_pay_vendor">
                                                    <?php if ($payMode != 5) { ?>
                                                        <option value="">Select Vendor</option>
                                                    <?php } ?>

                                                    <?php if (in_array($payMode, [1, 4])): ?>
                                                        <?php foreach ($modes_vendor as $vendor): ?>
                                                            <?php if ($vendor["mode"] == $payMode): ?>
                                                                <option value="<?= $vendor["id"] ?>" <?= ($vendor["id"] == $payVendor) ? "selected" : "" ?>>
                                                                    <?= $vendor["name"] ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>

                                                    <?php elseif (in_array($payMode, [2, 3]) && !empty($payVendor)): ?>
                                                        <option value="<?= $payVendor ?>" selected><?= $payVendor ?></option>

                                                    <?php elseif ($payMode == 5 && !empty($payVendor)): ?>
                                                        <option value="<?= $payVendor ?>" selected><?= $payVendor ?></option>
                                                    <?php endif; ?>
                                                </select>

                                                <?php

                                                if (!empty($payMode) && isset($payMode) && $payMode == 5): ?>
                                                    <input
                                                        type="text"
                                                        name="manual_cash_vendor"
                                                        required
                                                        class="form-control manually-cash"
                                                        placeholder="Enter Vendor Name"
                                                        value="<?= !empty($payVendor) ? htmlspecialchars($payVendor, ENT_QUOTES, 'UTF-8') : '' ?>">
                                                <?php endif; ?>

                                            </td>
                                            <td>
                                                <select name="currency[]" class="form-control fee_currency" disabled>
                                                    <?php foreach ($get_currencies as $c): ?>
                                                        <option value="<?= $c['id'] ?>"
                                                            data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                            <?= (!empty($l_array["fee_currency"]) && $l_array["fee_currency"] == $c['id']) ? "selected" : "" ?>>
                                                            <?= htmlspecialchars($c['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    readonly name="total_pending_amount"
                                                    value="<?= htmlspecialchars($payAmount) ?>"
                                                    class="form-control fee_value_inr totalValueINR"
                                                    placeholder="0.00">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tfoot>
                            </table>



                            <div id="aditional_university_dues">
                                <?php if (!empty($company_due_array['addition'])): ?>
                                    <?php foreach ($company_due_array['addition'] as $addition): ?>
                                        <?php
                                        // ✅ Clean arrays
                                        $addition["info"] = array_values(array_filter($addition["info"] ?? []));
                                        $addition["pay_info"] = array_values(array_filter($addition["pay_info"] ?? []));
                                        ?>

                                        <div class="aditional_university_dues mb-4">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <h4 class="mb-0">Company Dues</h4>
                                                </div>
                                                <div class="col-md-6 text-right">
                                                    <button type="button" class="btn btn-danger btn-remove-table">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <hr>

                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th style="width:25%; text-align:center;">Fees Info</th>
                                                        <th style="width:25%; text-align:center;">Value</th>
                                                        <th style="width:25%; text-align:center;">Currency</th>
                                                        <th style="width:25%; text-align:center;">Value INR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($addition["info"] as $index => $fee): ?>
                                                        <?php
                                                        $labelName = strtolower(str_replace(" ", "_", $fee['label_name'] ?? ''));
                                                        ?>
                                                        <tr class="calculate">
                                                            <td>
                                                                <select name="name[]" onchange="calculateInrValue();Change_companyDue(this);" class="form-control name">
                                                                    <?php foreach ($company_dues_fees_array as $fees_data): ?>
                                                                        <?php if (!empty($fees_data["status"])): ?>
                                                                            <option value="<?= htmlspecialchars($fees_data['id']) ?>"
                                                                                data-add="<?= $fees_data["add_flag"] ?>"
                                                                                <?= ($fees_data["id"] == ($fee["type"] ?? null)) ? "selected" : "" ?>>
                                                                                <?= htmlspecialchars($fees_data['name']) ?>
                                                                            </option>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="fee_value[]"
                                                                    class="form-control fee_value currency-amount <?= $labelName ?>"
                                                                    placeholder="0.00"
                                                                    value="<?= htmlspecialchars($fee['fee_value'] ?? '') ?>"
                                                                    <?= !empty($fee['readonly']) ? "readonly" : "" ?>>
                                                            </td>
                                                            <td>
                                                                <select name="fee_currency[]" class="form-control fee_currency"
                                                                    <?= !empty($fee['readonly']) ? "disabled" : "" ?>>
                                                                    <?php foreach ($get_currencies as $c): ?>
                                                                        <option value="<?= $c['id'] ?>"
                                                                            data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                            <?= (($fee["fee_currency"] ?? null) == $c['id']) ? "selected" : "" ?>>
                                                                            <?= htmlspecialchars($c['name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                            <td class="d-flex">
                                                                <input type="text" readonly name="fee_value_inr[]"
                                                                    value="<?= htmlspecialchars($fee["fee_value_inr"] ?? '') ?>"
                                                                    class="form-control fee_value_inr <?= $labelName . '_inr' ?>"
                                                                    placeholder="0.00">
                                                                <?php if ($index === 0): ?>
                                                                    <button type="button" onclick="university_due_add_column(this)" class="btn btn-primary btn-add-row">
                                                                        <i class="fa fa-plus"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button type="button" onclick="$(this).closest('tr').remove()" class="btn btn-danger btn-remove-row">
                                                                        <i class="fa fa-minus"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <?php foreach ($addition["pay_info"] as $l_array): ?>
                                                        <tr class="table-warning">
                                                            <td>
                                                                <select class="form-control" required name="university_pay_mode" onchange="vendor_update(this,this.value);">
                                                                    <?php foreach ($modes as $m): ?>
                                                                        <option value="<?= $m['id'] ?>"
                                                                            <?= (!empty($l_array["payMode"]) && $l_array["payMode"] == $m["id"]) ? "selected" : "" ?>>
                                                                            <?= htmlspecialchars($m['name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control" style="display:<?= (!empty($l_array["payMode"]) && $l_array["payMode"] == 5) ? 'none' : 'show' ?>" required name="university_pay_vendor">
                                                                    <?php if ($l_array["payMode"] != 5) { ?>

                                                                        <option value="">Select Vendor</option>
                                                                    <?php } ?>

                                                                    <?php if (!empty($l_array["payMode"]) && in_array($l_array["payMode"], [1, 4])): ?>
                                                                        <?php foreach ($modes_vendor as $vendor): ?>
                                                                            <?php if ($vendor["mode"] == $l_array["payMode"]): ?>
                                                                                <option value="<?= $vendor["id"] ?>" <?= (!empty($l_array["payVendor"]) && $vendor["id"] == $l_array["payVendor"]) ? "selected" : "" ?>>
                                                                                    <?= $vendor["name"] ?>
                                                                                </option>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>

                                                                    <?php elseif (!empty($l_array["payMode"]) && in_array($l_array["payMode"], [2, 3]) && !empty($l_array["payVendor"])): ?>
                                                                        <option value="<?= $l_array["payVendor"] ?>" selected><?= $l_array["payVendor"] ?></option>

                                                                    <?php elseif (!empty($l_array["payMode"]) && $l_array["payMode"] == 5 && !empty($l_array["payVendor"])): ?>
                                                                        <option value="<?= $l_array["payVendor"] ?>" selected><?= $l_array["payVendor"] ?></option>
                                                                    <?php endif; ?>
                                                                </select>


                                                                <?php

                                                                if (!empty($l_array["payMode"]) && isset($l_array["payMode"]) && $l_array["payMode"] == 5): ?>
                                                                    <input
                                                                        type="text"
                                                                        name="manual_cash_vendor"
                                                                        required
                                                                        class="form-control manually-cash"
                                                                        placeholder="Enter Vendor Name"
                                                                        value="<?= !empty($l_array["payVendor"]) ? htmlspecialchars($l_array["payVendor"], ENT_QUOTES, 'UTF-8') : '' ?>">
                                                                <?php endif; ?>

                                                            </td>
                                                            <td>
                                                                <select name="currency[]" class="form-control fee_currency" disabled>
                                                                    <?php foreach ($get_currencies as $c): ?>
                                                                        <option value="<?= $c['id'] ?>"
                                                                            data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                            <?= (($l_array["fee_currency"] ?? null) == $c['id']) ? "selected" : "" ?>>
                                                                            <?= htmlspecialchars($c['name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" readonly name="total_pending_amount"
                                                                    value="<?= htmlspecialchars($l_array["payAmount"] ?? 0) ?>"
                                                                    class="form-control fee_value_inr totalValueINR"
                                                                    placeholder="0.00">
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tfoot>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <?php if (has_permission('customers', '', 'quotation_create')) { ?>
                        <div class="row text-right">
                            <button type="submit" class="btn btn-info mtop25"><?= !empty($quotation_id) ? 'Update' : 'Create' ?></button>
                        </div>
                    <?php } ?>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>


    <script>
        // --- Server-side data ---
        const universityApplicantFeesArray = <?php echo json_encode($university_applicant_fees_array); ?>;
        const getClientsFees = <?php echo json_encode($get_clients_fees); ?>;
        var exchangeRates = {};
        const TSC = 0;
        // --- Utility functions ---
        const toInt = val => parseInt(val) || 0;
        const toFloat = val => parseFloat(val) || 0;
        const formatCurrency = (value, decimals = 2) => value.toFixed(decimals);
        var applicant_quotation_data = <?php echo json_encode($applicant_quotation_data); ?>;
        var payment_mod = <?php echo json_encode($modes); ?>;
        var payment_mode_vendors = <?php echo json_encode($modes_vendor); ?>;

        function vendor_update(obj, modeId) {
            // 🔹 Find the vendor select in the same row as the changed mode
            let vendor_select = $(obj).closest("tr").find("select[name='university_pay_vendor']");
            vendor_select.empty();
            vendor_select.show();
            $(obj).closest("tr").find("input.manually-cash").hide();
            $(obj).closest("tr").find("input.manually-cash").remove();

            // 🔹 Filter vendors by mode
            let vendors = payment_mode_vendors.filter(v => v.mode == modeId);

            if (modeId == 1 || modeId == 4) {
                if (vendors.length > 0) {
                    vendor_select.append('<option value="">-- Select Vendor --</option>');
                    vendors.forEach(v => {
                        vendor_select.append(`<option value="${v.id}">${v.name}</option>`);
                    });
                } else {
                    vendor_select.append('<option value="">No vendors available</option>');
                }
            } else if (modeId == 2) {
                vendor_select.empty();

                vendor_select.append(
                    '<option selected value="<?= $primary_university ?>"><?= $primary_university ?></option>'
                );
            } else if (modeId == 3) {
                vendor_select.empty();

                <?php
                $partnerId   = !empty($partnerName['id']) ? $partnerName['id'] : '';
                $partnerText = !empty($partnerName['name']) ? $partnerName['name'] : 'No vendors available';
                ?>
                vendor_select.append(
                    '<option selected value="<?= $partnerText ?>"><?= htmlspecialchars($partnerText) ?></option>'
                );
            } else if (modeId == 5) {
                // 🔹 Hide the select
                vendor_select.hide();

                // 🔹 Remove existing manually-input if already added
                $(obj).closest("tr").find("input.manually-cash").remove();

                // 🔹 Add new input for manual cash entry
                $('<input type="text" name="manual_cash_vendor" required class="form-control manually-cash" placeholder="Enter Vendor Name">')
                    .appendTo($(obj).closest("td").next("td"));


            }
        }


        function check_quotations(quotationId) {
            const url = new URL(window.location.href);
            url.searchParams.set("quotation_id", quotationId); // add or replace
            window.location.href = url.toString(); // reload with new param
        }


        function newUniversityDue() {
            // Get the main table
            const originalTable = document.querySelector("#feesTable");
            if (!originalTable) return;

            // Clone the table
            const clone = originalTable.cloneNode(true);

            // Reset all inputs/selects inside cloned table
            clone.querySelectorAll("input, select").forEach(el => {
                if (el.type === "hidden") return; // keep hidden IDs

                if (el.tagName === "SELECT") {
                    // Only reset if not disabled
                    if (!el.disabled) {
                        // Special case: skip reset for .currency-selector if value != "0"
                        if (el.classList.contains("currency-selector") && el.value !== "0") {
                            // do nothing, keep its current value
                        } else {
                            el.selectedIndex = 0;
                        }
                    }
                } else {
                    // reset input only if not readonly/disabled
                    if (!el.disabled && !el.readOnly) {
                        el.value = "0";
                    }
                }
            });

            // Create wrapper div for table + button
            const wrapper = document.createElement("div");
            wrapper.className = "university-due-wrapper mb-3 p-2 border rounded";

            // Add remove button
            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.className = "btn btn-danger btn-sm mb-2";
            removeBtn.innerHTML = '<i class="fa fa-minus"></i> Remove Due';
            removeBtn.onclick = () => wrapper.remove();

            // Append button + cloned table to wrapper
            wrapper.appendChild(removeBtn);
            wrapper.appendChild(clone);

            // Append wrapper to container
            document.querySelector(".aditional-university-due").appendChild(wrapper);

            document.querySelector(".aditional-university-due").appendChild(wrapper);

            // 🔹 Get the last table inside .aditional-university-due
            let lastTable = document.querySelector(".aditional-university-due table:last-of-type");

            if (lastTable) {
                // Find all selects and trigger change on them
                lastTable.querySelectorAll("select").forEach(select => {
                    select.dispatchEvent(new Event("change")); // vanilla JS trigger
                    // Or with jQuery (if you’re using it):
                    // $(select).trigger("change");
                });
            }
        }



        function newCompanyDue() {
            let universityDue = `
        <div class="aditional_university_dues">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h4 class="mb-0">Company Dues</h4>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-danger" 
                        onclick="$(this).closest('.aditional_university_dues').remove();">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>

            <hr>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width:25%; text-align:center;">Fees Info</th>
                        <th style="width:25%; text-align:center;">Value</th>
                        <th style="width:25%; text-align:center;">Currency</th>
                        <th style="width:25%; text-align:center;">Value INR</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="name[]" onchange="calculateInrValue();Change_companyDue(this);" class="form-control name">
                                <?php foreach ($company_dues_fees_array as $fees_data): ?>
                                    <?php if (!empty($fees_data["status"]) && $fees_data["status"] == 1): ?>
                                        <option data-add="<?= $fees_data["add_flag"] ?>" value="<?= htmlspecialchars($fees_data['id']) ?>">
                                            <?= htmlspecialchars($fees_data['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="fee_value[]" 
                                class="form-control fee_value currency-amount" 
                                placeholder="0.00" value="">
                        </td>
                        <td>
                            <select name="fee_currency[]" class="form-control fee_currency" required>
                                <?php foreach ($get_currencies as $c) : ?>
                                    <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="d-flex">
                            <input type="text"  readonly name="fee_value_inr[]" 
                                class="form-control fee_value_inr" 
                                placeholder="0.00" value="" readonly>
                            <button type="button" onclick="university_due_add_column(this)" 
                                class="btn btn-primary ml-2">
                                <i class="fa fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-warning">
                    <td>
                        <select class="form-control" required name="university_pay_mode" onchange="vendor_update(this,this.value);">
                                                <?php foreach ($modes as $m):
                                                ?>
                                                    <option value="<?= $m['id'] ?>" >
                                                        <?= htmlspecialchars($m['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select></td>
                                        <td>
                                            <select class="form-control" required id="university_pay_vendor" name="university_pay_vendor">
                                                <option value="">Select Vendor</option>
                                                </select>
                                        </td>
                        <td>
                            <select name="currency[]" class="form-control fee_currency" disabled>
                                <?php foreach ($get_currencies as $c) : ?>
                                    <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" readonly name="total_pending_amount" 
                                class="form-control fee_value_inr totalValueINR" 
                                placeholder="0.00" value="">
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>`;

            $("#aditional_university_dues").append(universityDue);
        }

        function university_due_add_column(obj) {
            let html_tr = `
        <tr class="calculate">
            <td>
                <select name="name[]" onchange="calculateInrValue();Change_companyDue(this);"  class="form-control name">
                    <?php foreach ($company_dues_fees_array as $fees_data): ?>
                        <?php if (!empty($fees_data["status"]) && $fees_data["status"] == 1): ?>
                            <option data-add="<?= $fees_data["add_flag"] ?>" value="<?= htmlspecialchars($fees_data['id']) ?>">
                                <?= htmlspecialchars($fees_data['name']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="text" name="fee_value[]" 
                    class="form-control currency-amount"  
                    placeholder="0.00" value="">
            </td>
            <td>
                <select name="fee_currency[]" class="form-control" required>
                    <?php foreach ($get_currencies as $c) : ?>
                        <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="d-flex">
                <input type="text"  readonly name="fee_value_inr[]" 
                    class="form-control currency-amount" readonly 
                    placeholder="0.00" value="">
                <button type="button" onclick="$(this).closest('tr').remove()" 
                    class="btn btn-danger ml-2">
                    <i class="fa fa-minus"></i>
                </button>
            </td>
        </tr>`;

            $(obj).closest("tbody").append(html_tr);
        }


        // --- Calculate INR values for all fee rows ---
        function calculateInrValue() {
            let exchangeRates = {};

            // Build exchange rate map
            document.querySelectorAll("#exchangeTableBody tr").forEach(row => {
                const currencySelect = row.querySelector("select[name='exchange_currency[]']");
                const amountInput = row.querySelector("input[name='exchange_value[]']");

                if (currencySelect && amountInput) {
                    const currencyId = currencySelect.value;
                    const rate = parseFloat(amountInput.value) || 0;
                    exchangeRates[currencyId] = rate;
                }
            });

            // 🔹 Update all fee rows inside each university table
            document.querySelectorAll(".university-feesTable .fee-row").forEach(feeRow => {
                const feeId = feeRow.dataset.id;
                const amountInput = feeRow.querySelector(`.fees_${feeId}`);
                const currencySelect = feeRow.querySelector(`.currency-selector-${feeId}`);
                const inrInput = feeRow.querySelector(`input[name$='_inr_value']`);
                const exchangeInput = feeRow.querySelector(`input[name$='_exchangeValue']`);

                if (!amountInput || !currencySelect || !inrInput) return;

                const amount = toFloat(amountInput.value);
                const currencyId = currencySelect.value;
                const exchangeRate = exchangeRates[currencyId] || 0;

                if (exchangeInput) exchangeInput.value = exchangeRate;

                const safeRate = (exchangeRate && exchangeRate !== 0) ? exchangeRate : 1;
                let inrAmount = amount * safeRate;

                // Save raw numeric value in a data attribute (for totals)
                inrInput.dataset.raw = inrAmount;

                // Show formatted string for user
                inrInput.value = formatCurrency(inrAmount);
            });

            // 🔹 Compute totals per university table
            $(".university-feesTable").each(function() {
                let totalValue = 0;

                $(this).find("input[name$='_inr_value']").each(function() {
                    let raw = $(this).val(); // safe numeric value
                    let val = parseFloat(raw) || 0;
                    totalValue += val;
                });

                // Debug
                // console.log("Table total:", totalValue);

                // Set formatted total in the table’s totalINRValue input
                $(this).find("input[name='totalINRValue']").val(formatCurrency(totalValue));
            });

            updateUniversityDue();
        }



        // --- Update University Due / Pending (table by table) ---
        function updateUniversityDue() {
            // Base service charge (global)
            let total_service_charge = toFloat(
                document.querySelector("input.total_service_charge_inr")?.value || 0
            );
            let final_total_service_charge = total_service_charge;


            let exchangeRates = {};
            document.querySelectorAll("#exchangeTableBody tr").forEach(row => {
                const currencySelect = row.querySelector("select[name='exchange_currency[]']");
                const amountInput = row.querySelector("input[name='exchange_value[]']");

                if (currencySelect && amountInput) {
                    const currencyId = currencySelect.value;
                    const rate = parseFloat(amountInput.value) || 0;
                    exchangeRates[currencyId] = rate;
                }
            });
            // console.log(final_total_service_charge);
            // Loop through each table

            var check_firstServiceCharge = 0;

            document.querySelectorAll("#universityDue, .aditional_university_dues table").forEach((table, index) => {
                let totalValue = 0;
                let totalInr = 0;

                // Loop rows inside this table
                table.querySelectorAll("tbody tr").forEach(row => {
                    const selectEl = row.querySelector("select[name='name[]']");
                    const selectedOption = selectEl ? selectEl.options[selectEl.selectedIndex] : null;
                    const addStatus = selectedOption ? parseInt(selectedOption.getAttribute("data-add")) : null;

                    const valueInput = row.querySelector("input[name='fee_value[]']");
                    const currencySelect = row.querySelector("select[name='fee_currency[]']");
                    const inrInput = row.querySelector("input[name='fee_value_inr[]']");

                    if (!valueInput || !currencySelect || !inrInput) return;

                    const value = toFloat(valueInput.value);
                    const currencyId = currencySelect.value;
                    const exchangeRate = exchangeRates[currencyId] || 1;
                    const inrValue = value * exchangeRate;

                    // Add raw value
                    totalValue += value;
                    totalInr += inrValue; // ✅ always add to this table total

                    // Update INR field
                    inrInput.value = formatCurrency(inrValue);

                    // Adjust service charge based on data-add
                    if (!isNaN(addStatus)) {
                        if (addStatus === 1) {
                            if (!row.closest(".aditional_university_dues")) {
                                final_total_service_charge += inrValue;
                            }
                        } else if (addStatus === 0) {
                            final_total_service_charge -= inrValue;
                            if (index === 0) {
                                check_firstServiceCharge = 1;
                            }
                        }
                    }
                });

                // For the first (main) table, deduct base service charge if not adjusted
                if (index === 0 && check_firstServiceCharge === 0) {
                    totalInr = (totalInr - total_service_charge) < 0 ? 0 : totalInr - total_service_charge;
                }

                // Update this table’s total pending input
                const pendingInput = table.querySelector("input[name='total_pending_amount']");
                if (pendingInput) {
                    pendingInput.value = formatCurrency(totalInr);
                }
            });

            // Update global pending (main table) if first service charge was adjusted
            if (check_firstServiceCharge === 1) {
                const mainPending = document.querySelector("#universityDue input[name='total_pending_amount']");
                if (mainPending) {
                    mainPending.value = formatCurrency(final_total_service_charge);
                }
            }
            setNumberDecimal();

        }


        let Orignal_package_amount = 0;
        let Orignal_package_currency_id = 0;




        function update_package_amount() {

            let studyYear = $("#study_year").val();
            if (studyYear > 0) {
                exchangeRates = {};
                document.querySelectorAll("#exchangeTableBody tr").forEach(row => {
                    const currencySelect = row.querySelector("select[name='exchange_currency[]']");
                    const amountInput = row.querySelector("input[name='exchange_value[]']");

                    if (currencySelect && amountInput) {
                        const currencyId = currencySelect.value;
                        const rate = parseFloat(amountInput.value) || 0;
                        exchangeRates[currencyId] = rate;
                    }
                });
                const feesForYear = universityApplicantFeesArray[studyYear] || [];
                feesForYear.forEach(fee => {

                    if (fee.fees_id == <?= PACKAGE_FEES_ID ?>) {
                        //   console.log(fee.amount);
                        if (fee.amount == 0) {
                            $(`.currency-selector-${fee.fees_id}`)
                                .closest("tr") // safer than .parent("tr")
                                .find("select, input")
                                .prop("disabled", true);

                        }
                        Orignal_package_amount = fee.amount;
                        Orignal_package_currency_id = fee.currency_id;
                        let exchangeRate = exchangeRates[fee.currency_id];
                        let safeRate = (exchangeRate !== undefined && exchangeRate !== null) ? exchangeRate : 1;
                        Orignal_package_amount = formatCurrency(fee.amount * safeRate);

                    }
                });
            }
        }
        // --- Fetch applicant fees ---
        function fetchApplicantFees(studyYear) {
            Orignal_package_amount = 0;
            <?php if ($quotation_id != "") { ?>
                // return false;
            <?php } ?>
            if (!studyYear) {
                alert("Please select a study year.");
                return;
            }

            // Reset all currency amounts
            document.querySelectorAll(".currency-amount").forEach(el => el.value = "0.00");

            // Set university applicant fees
            const feesForYear = universityApplicantFeesArray[studyYear] || [];

            feesForYear.forEach(fee => {
                const amountInput = document.querySelector(`.main-university-due .fees_${fee.fees_id}`);
                // if (!amountInput) return;

                if (fee.backend == 1) {
                    console.log("backend", fee);
                    const currencySelect = document.querySelector(`.main-university-due .currency-selector-${fee.fees_id}`);
                    $(`.main-university-due .currency-selector-${fee.fees_id}`)
                        .val(String(fee.currency_id)) // make sure value matches string in <option>
                        .trigger("change"); // fire change event
                    amountInput.value = formatCurrency(toFloat(fee.amount));
                }
                if (fee.fees_id == <?= PACKAGE_FEES_ID ?>) {
                    Orignal_package_amount = fee.amount;
                    Orignal_package_currency_id = fee.currency_id;
                    console.log(fee.amount);
                    if (fee.amount == 0) {
                        $(`.currency-selector-${fee.fees_id}`)
                            .closest("tr") // safer than .parent("tr")
                            .find("select, input")
                            .prop("disabled", true);

                    }
                    // let exchangeRate = exchangeRates[fee.currency_id];
                    // let safeRate = (exchangeRate !== undefined && exchangeRate !== null) ? exchangeRate : 1;

                    // originalPackageAmount = formatCurrency(fee.amount * safeRate);

                }
            });


            getClientsFees.forEach(fee => {
                // Check if fee.fees == 1 AND fee.id is either 3 or 7
                if (fee.fees == 1 && [3, 7].includes(parseInt(fee.id))) {
                    // Set currency selector
                    $(`.main-university-due .currency-selector-${fee.id}`)
                        .val(fee.currency_id)
                        .trigger("change");

                    // Set fee amount
                    $(`.main-university-due .fees_${fee.id}`).val(fee.amount);
                }

                // Handle TOTAL_AMOUNT_ID
                if (parseInt(fee.id) === <?= TOTAL_AMOUNT_ID ?>) {
                    $("#universityDue input.total_service_charge").val(fee.amount);
                }
            });


            // Set client fees

            // update_package_amount();
            // calculateInrValue();
        }

        // --- Add/Remove Exchange Rows ---
        function handleExchangeTableClick(event) {
            const addBtn = event.target.closest(".addRow");
            const removeBtn = event.target.closest(".removeRow");

            if (addBtn) {
                addExchangeRow(addBtn.closest("tr"));
            } else if (removeBtn) {
                removeExchangeRow(removeBtn.closest("tr"));
            }
        }

        function addExchangeRow(templateRow) {
            const clone = templateRow.cloneNode(true);
            clone.querySelectorAll("input").forEach(input => input.value = "");

            const btn = clone.querySelector(".addRow");
            btn.classList.replace("btn-success", "btn-danger");
            btn.classList.replace("addRow", "removeRow");
            btn.innerHTML = '<i class="fa fa-minus"></i>';

            document.getElementById("exchangeTableBody").appendChild(clone);
        }

        function removeExchangeRow(row) {
            row.remove();
            calculateInrValue();
        }

        // --- Update currency symbol ---
        function updateSymbol_(obj, feeId) {
            try {
                const $selector = $(obj); // wrap in jQuery

                if ($selector.length === 0) {
                    // console.warn("updateSymbol_: selector not found", obj, feeId);
                    return;
                }

                const $selectedOption = $selector.find("option:selected");
                if ($selectedOption.length === 0) {
                    // console.warn("updateSymbol_: no selected option", obj, feeId);
                    return;
                }

                const symbol = $selectedOption.data("symbol") || "";

                // Try to find closest container (row or group)
                const $row = $selector.closest("tr, .form-group, .input-group");
                const $symbolEl = $row.find(`.currency-symbol-${feeId}`);

                if ($symbolEl.length) {
                    $symbolEl.text(symbol);
                } else {
                    console.warn("updateSymbol_: symbol element not found", feeId);
                }

            } catch (err) {
                console.error("updateSymbol_ error:", err, obj, feeId);
            }
        }


        // --- Form submission handler ---
        async function handleFormSubmission(form, event) {
            event.preventDefault();
show_loader();
            try {
                const formData = new FormData();

                // 🔹 Currency Exchange (array)
                let currency_exchange = [];
                let seenCurrencies = new Set();
                let hasDuplicate = false;

                $("#exchangeTable tbody tr").each(function() {
                    let currencyId = $(this).find("select[name='exchange_currency[]']").val() || null;
                    let exchangeValue = $(this).find("input[name='exchange_value[]']").val() || null;

                    if (currencyId || exchangeValue) {
                        if (seenCurrencies.has(currencyId)) {
                            hasDuplicate = true;
                            $(this).find("select[name='exchange_currency[]']").addClass("is-invalid"); // highlight duplicate
                        } else {
                            seenCurrencies.add(currencyId);
                            currency_exchange.push({
                                currency_id: currencyId,
                                exchange_value: exchangeValue,
                            });
                        }
                    }
                });





                // Show error if duplicates exist
                if (hasDuplicate) {
                    hide_loader();
                    alert_float("danger", "Duplicate Currency Exchange Rates detected. Please select unique currencies.");
                    return false; // stop further processing
                }

                let package_amount = 0;

                $(".fees-inr-value-<?= PACKAGE_FEES_ID ?>").each(function() {
                    let val = parseFloat($(this).val()) || 0; // convert to float, fallback to 0
                    package_amount += val;
                });

                if (Orignal_package_amount > package_amount) {
                    alert_float(
                        "danger",
                        `Package amount should be greater than or equal to the original package amount (${Orignal_package_amount})`
                    );
                    hide_loader();
                    return false; // stop further processing
                }

                if ($("#universityDue input[name='total_pending_amount']").val() < 0) {
                    alert_float(
                        "danger",
                        `Company due value cannot be negative.`
                    );
                    hide_loader();
                    return false; // stop further processing  
                }




                // 🔹 University dues structure
                let university_dues = {
                    main: {
                        fees_info: [],
                        pay_info: []
                    },
                    addition: [] // must be array
                };

                // 🔹 Company dues structure
                let company_dues = {
                    main: {
                        info: [],
                        pay_info: []
                    },
                    addition: []
                };

                // ✅ Collect main university fees info
                $(".main-university-due tbody tr").each(function() {
                    let paymentOptionSelect = $(this).find("select[name$='_payment_option']");
                    let rowData = {
                        name: $(this).find("input[name='applicant_fees[]']").val() || null,
                        id: $(this).find("input[name$='_id']").val() || null,
                        currency_id: $(this).find("select[name$='_currency_type']").val() || null,
                        exchange_value: $(this).find("input[name$='_exchangeValue']").val() || null,
                        payment_option: paymentOptionSelect.val() || null,
                        payment_option_status: paymentOptionSelect.is('[disabled]') ? 1 : 0,
                        inr_value: $(this).find("input[name$='_inr_value']").val() || null,
                        amount: $(this).find("input[name$='_amount']").val() || null,
                        quotation_name: $(this).find("input[name='quotation_name']").val() || null,
                    };
                    university_dues.main.fees_info.push(rowData);
                });

                // ✅ Collect main pay info
                $(".main-university-due tfoot tr").each(function() {
                    let rowData = {
                        payMode: $(this).find("select[name='university_pay_mode']").val() || null,
                        payVendor: $(this).find("select[name='university_pay_vendor']").val() || null,
                        payAmount: $(this).find("input[name='university_pay_amount']").val() || null,
                        totalINRValue: $(this).find("input[name='totalINRValue']").val() || null
                    };
                    university_dues.main.pay_info.push(rowData);
                });

                // ✅ Collect additional university dues
                $(".aditional-university-due table").each(function() {
                    let tableGroup = {
                        fees_info: [],
                        pay_info: [],
                        other_dues: [] // ✅ added missing key
                    };

                    // Fees info
                    $(this).find("tbody tr").each(function() {
                        let paymentOptionSelect = $(this).find("select[name$='_payment_option']");
                        let rowData = {
                            name: $(this).find("input[name='applicant_fees[]']").val() || null,
                            id: $(this).find("input[name$='_id']").val() || null,
                            currency_id: $(this).find("select[name$='_currency_type']").val() || null,
                            exchange_value: $(this).find("input[name$='_exchangeValue']").val() || null,
                            payment_option: paymentOptionSelect.val() || null,
                            payment_option_status: paymentOptionSelect.prop("readonly") ? 1 : 0,
                            inr_value: $(this).find("input[name$='_inr_value']").val() || null,
                            amount: $(this).find("input[name$='_amount']").val() || null,
                            quotation_name: $(this).find("input[name='quotation_name']").val() || null,
                        };
                        tableGroup.fees_info.push(rowData);
                    });

                    // Pay info
                    $(this).find("tfoot tr").each(function() {
                        let rowData = {
                            payMode: $(this).find("select[name='university_pay_mode']").val() || null,
                            payVendor: $(this).find("select[name='university_pay_vendor']").val() || null,
                            payAmount: $(this).find("input[name='university_pay_amount']").val() || null,
                            totalINRValue: $(this).find("input[name='totalINRValue']").val() || null
                        };
                        tableGroup.pay_info.push(rowData);
                    });

                    // Other dues
                    $(this).find("tr").each(function() {
                        let rowData = {
                            label_name: $(this).find("input.label_name").val() || null,
                            fee_value: $(this).find("input.fee_value").val() || null,
                            fee_currency: $(this).find("select.fee_currency").val() || null,
                            fee_value_inr: $(this).find("input.fee_value_inr").val() || null
                        };
                        if (rowData.label_name || rowData.fee_value) {
                            tableGroup.other_dues.push(rowData);
                        }
                    });

                    if (
                        tableGroup.fees_info.length ||
                        tableGroup.pay_info.length ||
                        tableGroup.other_dues.length
                    ) {
                        university_dues.addition.push(tableGroup);
                    }
                });

                // ✅ Collect company dues (main table - single)
                $("#universityDue tbody tr").each(function() {
                    let rowData = {
                        type: $(this).find("select.name").val() || null,
                        fee_value: $(this).find("input[name='fee_value[]']").val() || null,
                        fee_currency: $(this).find("select[name='fee_currency[]']").val() || null,
                        fee_value_inr: $(this).find("input[name='fee_value_inr[]']").val() || null
                    };
                    company_dues.main.info.push(rowData);
                });

                $("#universityDue tfoot tr").each(function() {
                    let rowData = {
                        payMode: $(this).find("select[name='university_pay_mode']").val() || null,
                        payVendor: $(this).find("select[name='university_pay_vendor']").val() || null,
                        payAmount: $(this).find("input[name='total_pending_amount']").val() || null
                    };
                    company_dues.main.pay_info.push(rowData);
                });

                // ✅ Collect company dues (addition - multiple tables)
                $("#aditional_university_dues .aditional_university_dues table").each(function() {
                    let tableGroup = {
                        info: [],
                        pay_info: []
                    };

                    $(this).find("tbody tr").each(function() {
                        let rowData = {
                            type: $(this).find("select.name").val() || null,
                            fee_value: $(this).find("input[name='fee_value[]']").val() || null,
                            fee_currency: $(this).find("select[name='fee_currency[]']").val() || null,
                            fee_value_inr: $(this).find("input[name='fee_value_inr[]']").val() || null
                        };
                        if (rowData.type || rowData.fee_value) {
                            tableGroup.info.push(rowData);
                        }
                    });

                    $(this).find("tfoot tr").each(function() {
                        let rowData = {
                            payMode: $(this).find("select[name='university_pay_mode']").val() || null,
                            payVendor: $(this).find("select[name='university_pay_vendor']").val() || null,
                            payAmount: $(this).find("input[name='total_pending_amount']").val() || null
                        };
                        if (rowData.payMode || rowData.payAmount) {
                            tableGroup.pay_info.push(rowData);
                        }
                    });

                    if (tableGroup.info.length > 0 || tableGroup.pay_info.length > 0) {
                        company_dues.addition.push(tableGroup);
                    }
                });

                // 🔹 Append to formData
                formData.append("currency_exchange", JSON.stringify(currency_exchange));
                formData.append("university_dues", JSON.stringify(university_dues));
                formData.append("company_dues", JSON.stringify(company_dues));
                formData.append("client_id", <?= $client_id ?>);
                formData.append("university_name", $("#university_name").val());
                formData.append("acadmic_year", $("#acadmic_year").val());
                formData.append("study_year", $("#study_year").val());
                formData.append("release_to_counsellor", $("#release_to_counsellor").prop("checked") ? 1 : 0);


                <?php if (!empty($quotation_id) && !empty($applicant_quotation_data)) { ?>
                    formData.append("quotation_id", <?= !empty($quotation_id) ? $quotation_id : 0 ?>);
                <?php } ?>
                formData.append(csrfData.token_name, csrfData.hash);



                const response = await fetch(form.action, {
                    method: "POST",
                    body: formData
                });
                const data = await response.json();
                hide_loader();
                // console.log(data);
                if (data.resp_code || data.resp_code === "RCS") {
                    alert_float("success", data.resp_desc)
                    let url = new URL(window.location.href);

                    // Remove quotation_id param if exists
                    if (url.searchParams.has("quotation_id")) {
                        url.searchParams.delete("quotation_id");
                        window.location.replace(url.toString()); // redirect to new URL
                    } else {
                        location.reload();
                    }
                } else {
                    alert_float("danger", data.resp_desc)

                }
            } catch (error) {
                hide_loader();
                console.error("Error:", error);
                alert("Something went wrong! Please try again.");
            }
        }



        // --- Initialize application ---
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize currency symbols
            $(".currency-selector").each(function() {
                const match = this.className.match(/currency-selector-(\d+)/);
                if (match) {
                    updateSymbol_(this, match[1]);
                } else {
                    console.warn("currency-selector: no feeId found in class", this);
                }
            });

            $(document).on("keyup", ".manually-cash", function() {
                let $row = $(this).closest("tr");
                let vendor_select = $row.find("select[name='university_pay_vendor']");
                vendor_select.empty();

                let vendorName = $(this).val().trim();
                if (vendorName !== "") {
                    vendor_select.append(`<option value="${vendorName}" selected>${vendorName}</option>`);
                }
            });


            // Setup event listeners
            const exchangeTableBody = document.getElementById("exchangeTableBody");
            if (exchangeTableBody) {
                exchangeTableBody.addEventListener("click", handleExchangeTableClick);
            }

            // Global input/change listeners with debouncing
            let calculationTimeout;

            function debounceCalculate() {
                clearTimeout(calculationTimeout);
                calculationTimeout = setTimeout(calculateInrValue, 300);
            }

            // Delegate input events to parent container for better performance
            document.getElementById("applicant-quotation-form").addEventListener("input", function(event) {
                if (
                    event.target.matches("input[name='exchange_value[]']") ||
                    event.target.matches(".currency-amount") ||
                    event.target.matches("input[name='fee_value[]']")
                ) {
                    debounceCalculate();
                }
            });

            document.addEventListener("change", function(event) {
                if (event.target.matches("select[name='exchange_currency[]'], .currency-selector, select[name='fee_currency[]']")) {
                    calculateInrValue();
                }
            });

            // Form submission handlers
            const applicantForm = document.getElementById("applicant-quotation-form");
            if (applicantForm) {
                applicantForm.addEventListener("submit", (event) => handleFormSubmission(applicantForm, event));
            }

            // Initial calculation
            // calculateInrValue();

            <?php if (!empty($quotation_id)): ?>
                update_package_amount();
            <?php else: ?>
                <?php
                // Ensure $acadmic_year is valid before exploding
                $acadmicYearParts = !empty($acadmic_year) ? explode("-", $acadmic_year) : [];
                $acadmicYear = $acadmicYearParts[0] ?? '';
                ?>
                let acadmicYear = "<?= trim($acadmicYear) ?>";
                if (acadmicYear == <?= Date("Y") ?>) {
                    $("#study_year").val(1).trigger("change");
                }
            <?php endif; ?>

$("input[name='fee_value[]'],input[name='fee_value_inr[]'], .currency-amount").each(function(){
      let $this = $(this);

    // Remove commas from current value
    let val = $this.val();
    console.log(val);
    if (val) {
        $this.val(val.replace(/,/g, ""));
         console.log(val.replace(/,/g, ""));
    }

    // Force numeric input with decimals
    $this.attr({
        type: "number",
        step: "0.0001", // allow up to 4 decimals
        min: "0"
    }); 
})
 


        });

        function Change_companyDue(obj) {
            let value = $(obj).val(); // selected fee id
            let $row = $(obj).closest("tr"); // current row

            console.log("Selected fee ID:", value);

            // Reset fields
            $row.find("input[name='fee_value[]']").val(0);
            $row.find("select[name='fee_currency[]']").val(3).trigger("change");

            // Find fee in getClientsFees
            let fee = getClientsFees.find(f => String(f.id) === String(value));

            if (fee && fee.id == 3) {
                // Set fee value safely
                $row.find("input[name='fee_value[]']").val(fee.amount ?? 0);

                // ✅ Set currency correctly
                if (fee.currency_id) {
                    $row.find("select[name='fee_currency[]']")
                        .val(fee.currency_id)
                        .trigger("change"); // trigger recalculation (e.g. INR value update)
                }
            }

            updateUniversityDue();
            console.log("Row updated:", fee);
        }



        function GeneratePDF(client_id, quotation_id) {
            $.ajax({
                url: "<?= admin_url('clients/quotationGenerate') ?>", // your controller method
                type: "POST",
                data: {
                    client_id: client_id,
                    quotation_id: quotation_id
                },
                beforeSend: function() {
                    // Optional: show loader
                    console.log("Generating PDF...");
                },
                success: function(response) {
                    response = JSON.parse(response);

                    // If backend returns PDF file URL
                    if (response.pdf_url) {
                        window.open(response.pdf_url, "_blank"); // Open in new tab
                    } else {
                        alert("PDF generated successfully.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert("Something went wrong. Please try again.");
                }
            });
        }

        function setNumberDecimal() {
            $(document).on("focus", "input[name='fee_value[]'],input[name='fee_value_inr[]'], .currency-amount", function() {
                // Force input type="number" with step for 4 decimals
                $(this).attr({
                    type: "number",
                    step: "0.0001", // up to 4 decimals
                    min: "0" // optional: prevent negative values
                });
            });
        }
    </script>
<?php } ?>