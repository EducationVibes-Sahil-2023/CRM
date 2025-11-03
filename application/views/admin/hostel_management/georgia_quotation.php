<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    [id^="nested-applicant-table-"] div.row {
        display: none !important;
    }

    .dataTables_wrapper div.row {
        display: none !important;
    }
</style>
<div class="panel_s">
    <div class="panel-body">
        <?php
        $quotation_table = array(
            "Quotation Number",
            "Start Date",
            "End Date",
            "Months",
            "Room Capacity",
            "Month Rent",
            "Amount",
            "Currency",
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

            tAPI = initDataTable('.table-quotation-table', admin_url + 'hostel_management/quotation_table/georgia/' + <?= $getId ?>);

        });
    });
</script>

<?php

$get_currencies = get_currencies();
$university_applicant_fees = $university_applicant_fees = university_applicant_fees("", 1, [
    "university_name" => $hostelData->vendor_update,
    "acadmic_year"    => $acadmic_year
]);
$selected_mod = 0;
$transaction_type  = transaction_type(array("hostel_status" => 1));
$quotation_paymente_mode = $this->db
    ->select('*')
    ->from(db_prefix() . 'quotation_paymente_mode')
    ->get()
    ->result_array();
$currency_lookup   = array_column($get_currencies, null, 'id');
$modes =  $this->quotation_model->payment_mod();
$modes_vendor =  $this->quotation_model->payment_mode_vendors();
$fees_details_array = [
    ["label" => "Total Service Charge", "name" => "total_service_charge", "readonly" => true, "add_btn" => true],
];
$hostel = getDataInformation('hostel', 'id, name', 'status = 1');
$hostel_company = getDataInformation('hostel_company', 'id, name', 'status = 1');
array_unshift($modes, array("id" => "", "name" => "Select Mode"));

$sql = "
    SELECT 
        aq.*,
        CONCAT('Q', ROW_NUMBER() OVER (PARTITION BY aq.university_name ORDER BY aq.id ASC)) AS quotation_label,
        CONCAT(aq.university_name, '-', aq.start_date,'-',aq.end_date,'-', aq.room_capacity, ' - ',
               'Q', ROW_NUMBER() OVER (PARTITION BY aq.university_name ORDER BY aq.id ASC)
        ) AS unique_id
    FROM " . db_prefix() . "hostel_quotation aq
    WHERE aq.hostel_info_id = ? AND aq.status > 0
    ORDER BY aq.id DESC
";


$hostel_quotations = $this->db->query($sql, [$getId])->result_array();
array_unshift($hostel_quotations, array("id" => "", "name" => ""));


$quotation_id = !empty($_GET['quotation_id']) ? $_GET['quotation_id'] : '';
$hostel_quotation_data = [];
$exchange_value_array = [];
$university_due_array = [];
$company_due_array = [];
$fees_data = [];
if (!empty($_GET['quotation_id'])) {
    $hostel_quotation_data =  $this->Hostel_model->hostel_quotation_data($getId, $_GET['quotation_id']);
    $exchange_value_array = json_decode($hostel_quotation_data->exchange_value, true);
    $university_due_array = json_decode($hostel_quotation_data->hostel_due, true);
    $company_due_array = json_decode($hostel_quotation_data->company_due, true);
}

$serviceList = $this->Hostel_model->get_hostel_services();


?>
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">


            <div class="panel-body">


                <div class="row">
                    <div class="form-group col-md-6">
                        <?php
                        echo render_select(
                            'hostel_quotation',
                            $hostel_quotations,
                            ['id', 'unique_id'], // first = value, second = label
                            'Hostel Quotations',
                            [$quotation_id ?? ''],
                            [
                                'data-width' => '100%',
                                'data-none-selected-text' => 'Hostel Quotations',
                                'onchange' => 'check_quotations(this.value)'
                            ],
                            [],
                            'no-mbot',
                            '',
                            false,
                            'hostel_quotation'
                        );
                        ?>
                    </div>
                    <?php if (has_permission('hostel_management', '', 'quotation')) { ?>
                        <div class="form-group col-md-3 text-right  ">
                            <label for="release_to_counsellor"><br>Release to Counsellor</label>
                            <input type="checkbox" value="1" id="release_to_counsellor" <?= !empty($hostel_quotation_data->release_to_counsellor) ? 'checked' : '' ?> name="release_to_counsellor">
                        </div>
                        <?php if (!empty($quotation_id) && !empty($hostel_quotation_data) && has_permission("hostel_management", "", "hostel_invoice_generate")) { ?>
                            <div class="form-group col-md-3 text-right  ">
                                <button class="btn btn-primary"
                                    onclick="window.open('<?= $hostel_quotation_data->pdf ?>', '_blank')">
                                    <i class="fa fa-eye"></i>
                                </button>

                                <button class="btn btn-primary" onclick="GeneratePDF('<?= $getId ?>','<?= $quotation_id ?>')">Generate PDF</button>
                            </div>
                        <?php } ?>
                    <?php } ?>

                </div>

                <h4>Hostel Quotation</h4>
                <hr class="hr-panel-heading" />

                <!-- The form below must have id="applicant-quotation-form" for JS to work. Do not remove or change this ID. -->
                <?= form_open(admin_url('hostel_management/quotation'), ['id' => 'hostel_quotation_form', 'onsubmit' => 'return false;']); ?>
                <input hidden name="hostal_info_id" value="<?= $getId ?>">
                <!-- University Info -->
                <div id="formInformationGet">
                    <div class="row">
                        <div class="col-md-4">
                            <?= render_input('university_name', 'University Name ', $hostelData->university_name ?? '', 'text', ["placeholder" => "Enter University Name", "readonly" => true]); ?>
                        </div>

                        <div class="col-md-2">
                            <?= render_input('start_date', 'Start Date ', $hostel_quotation_data->start_date ?? '', 'date', ["placeholder" => "Select Start Date"]); ?>
                        </div>

                        <div class="col-md-2">
                            <?= render_input('end_date', 'End Date ', $hostel_quotation_data->end_date ?? '', 'date', ["placeholder" => "Select End Date"]); ?>
                        </div>


                        <div class="col-md-2 hide ">
                            <label>Room Rent <span class="text-danger">*</span></label><br>
                            <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                                <input type="text" name="rent" <?= $required ?> class="form-control currency-amount fees_rent" placeholder="0.00" id="rent" value="<?= $hostel_quotation_data->rent ?? '' ?>" size="8" onkeypress="return acceptText(this,'number')">
                                <div class="input-group-addon currency-addon">
                                    <select name="rent_currency_type" id="rent" class="currency-selector currency-selector-rent" onchange="updateSymbol('rent')">
                                        <?php foreach ($get_currencies as $c) {
                                        ?>
                                            <option
                                                data-symbol="<?= $c['symbol'] ?>"
                                                value="<?= $c['id'] ?>"
                                                data-placeholder="0.00" <?= $hostel_quotation_data->currency == $c['id'] ? 'selected' : '' ?>>
                                                <?= $c['name'] ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?php
                                $roomCapacity = json_decode($hostelRentelData[$hostelData->hostel ?? '']["rooms"], true) ?? [];
                                echo render_select(
                                    'room_capacity',
                                    $roomCapacity,
                                    ['room_capacity', 'room_capacity'],
                                    html_entity_decode('Room Capacity '),
                                    $hostel_quotation_data->room_capacity ?? '',
                                    [
                                        'data-width'              => '100%',
                                        'data-none-selected-text' => 'Select room capacity',
                                        'required'                => true,
                                        'onchange'                => 'selectRoomCapacity(this.value)',
                                    ]
                                );
                                ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?php
                                echo render_select(
                                    'services',
                                    $serviceList,
                                    ['id', 'name'],
                                    html_entity_decode('Services'),
                                    !empty($hostel_quotation_data->services) ? explode(',', $hostel_quotation_data->services) : '',
                                    [
                                        'data-width'              => '100%',
                                        'data-none-selected-text' => 'Select Services',
                                        'multiple'                => true,
                                        'required'                => true,
                                    ]
                                );
                                ?>
                            </div>
                        </div>

                    </div>
                    <div class="row">

                        <div class="col-md-3">
                            <?= render_input('floor_No', 'Floor No', $hostel_quotation_data->floor_no ?? '', 'number', ["placeholder" => "Enter Floor No"]); ?>
                        </div>

                        <div class="col-md-3">
                            <?= render_input('room_No', 'Room No', $hostel_quotation_data->room_no ?? '', 'number', ["placeholder" => "Enter Room No"]); ?>
                        </div>


                        <div class="col-md-3">
                            <?= render_select(
                                'company',
                                $hostel_company,
                                ['id', 'name'],
                                'Company',
                                $hostel_quotation_data->company ?? $hostelData->company ?? '',
                                ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, "disabled" => true]
                            );
                            ?>
                        </div>

                        <div class="col-md-3">
                            <?= render_select(
                                'hostel',
                                $hostel,
                                ['id', 'name'],
                                'Hostel',
                                $hostel_quotation_data->hostel ?? $hostelData->hostel ?? '',
                                ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, "disabled" => true]
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
                                    <th style="width: 40%; text-align: center;">Credit Currency</th>
                                    <th style="width: 40%; text-align: center;">Document Currency</th>
                                    <th style="width: 40%; text-align: center;">Exchange Value</th>
                                    <th style="width: 20%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="exchangeTableBody">

                                <?php if (!empty($exchange_value_array[0])) : ?>
                                    <?php foreach ($exchange_value_array as $key => $exchange): ?>
                                        <tr>
                                            <td>
                                                <select name="credit_currency[]" class="form-control" onchange="calculateInrValue()">
                                                    <?php foreach ($get_currencies as $c): ?>
                                                        <option value="<?= $c['id'] ?>"
                                                            data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                            <?= ($c['id'] == $exchange['credit_currency']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($c['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="document_currency[]" class="form-control" onchange="calculateInrValue()">
                                                    <?php foreach ($get_currencies as $c): ?>
                                                        <option value="<?= $c['id'] ?>"
                                                            data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                            <?= ($c['id'] == $exchange['document_currency']) ? 'selected' : '' ?>>
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
                                            <select name="credit_currency[]" class="form-control" onchange="calculateInrValue()">
                                                <?php foreach ($get_currencies as $c): ?>
                                                    <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                        <?= htmlspecialchars($c['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="document_currency[]" class="form-control" onchange="calculateInrValue()">
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
                        <?php if (!empty($university_applicant_fees) && !empty($get_currencies)) :  ?>
                            <div id="applicant_fees">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-6">
                                        <h4 class="mb-0">Food/Mess and Accommodation</h4>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <!--<button type="button" class="btn btn-primary" onclick="newUniversityDue()">-->
                                        <!--    <i class="fa fa-plus"></i>-->
                                        <!--</button>-->
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
                                                <th class="text-center">Document Value</th>
                                            </tr>
                                        </thead>
                                        <tbody id="university_dues">
                                            <?php if (!empty($university_due_array)) {
                                                foreach ($university_due_array["main"]["fees_info"] as $fees) {

                                                    $id         = $fees["id"];
                                                    if (!in_array($id, [5, 6])) {
                                                        continue;
                                                    }

                                                    $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                                    $symbol     = $currency_lookup[$fees["currency_id"]]["symbol"]
                                                        ?? $currency_lookup[$fees["university_quotation_currency"]]["symbol"]
                                                        ?? '$';
                                                    $fees['quotation_name'] == 'Hostel'  ? $fees['quotation_name'] = 'Month Rent' : $fees['quotation_name'] = $fees['quotation_name'];
                                            ?>
                                                    <tr class="fee-row" data-id="<?= $id ?>">
                                                        <td>
                                                            <label><?= htmlspecialchars($fees['quotation_name']) ?> <small class="text-danger">*</small></label>
                                                            <div class="input-group form-group credit-currency-change">
                                                                <input type="hidden" name="applicant_fees[]" value="<?= $field_name ?>">
                                                                <input type="hidden" name="quotation_name" value="<?= htmlspecialchars($fees['quotation_name']) ?>">
                                                                <input type="hidden" name="<?= $field_name ?>_id" value="<?= $fees['id'] ?>">
                                                                <input type="hidden" name="<?= $field_name ?>_detail_id_<?= $fees['id'] ?>" value="<?= $fees['detail_id'] ?? '' ?>">

                                                                <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                                    <?= htmlspecialchars($symbol) ?>
                                                                </div>

                                                                <input type="text" name="<?= $field_name ?>_amount" <?= $id == 5 ? 'required' : '' ?>
                                                                    oninput="calculateInrValue()"
                                                                    class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                                    placeholder="0.00" value="<?= $fees["amount"] ?? 0 ?>">

                                                                <div class="input-group-addon">
                                                                    <select name="<?= $field_name ?>_currency_type"
                                                                        class="currency-selector currency-selector-<?= $id ?>"
                                                                        onchange="calculateInrValue(); updateSymbol_(this,<?= $id ?>)">
                                                                        <?php foreach ($get_currencies as $c): ?>
                                                                            <option data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                                value="<?= $c['id'] ?>"
                                                                                <?= ((!empty($fees['credit_currency']) && $fees['credit_currency'] == $c['id'])
                                                                                    || (empty($fees['credit_currency']) && ($fees['university_quotation_currency'] ?? '') == $c['id']))
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
                                                            <!-- <label>Value INR <small class="text-danger">*</small></label>
                                                            <div class="input-group form-group">
                                                                <div class="input-group-addon">₹</div>
                                                                <input type="number" step="0.01" name="<?= $field_name ?>_inr_value"
                                                                    required class="form-control currency-amount  fees-inr-value-<?= $id ?>" readonly
                                                                    placeholder="0.00" value="<?= $fees["inr_value"] ?? '' ?>">
                                                            </div> -->

                                                            <label>&nbsp;</label>
                                                            <div class="input-group form-group document-currency-change">

                                                                <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                                    <?= htmlspecialchars($symbol) ?>
                                                                </div>

                                                                <input type="text" name="<?= $field_name ?>_amount" <?= $id == 5 ? 'required' : '' ?>
                                                                    oninput="calculateInrValue()"
                                                                    readonly
                                                                    class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                                    placeholder="0.00" value="<?= $fees["inr_value"] ?? 0 ?>">

                                                                <div class="input-group-addon">
                                                                    <select disabled name="<?= $field_name ?>_currency_type"
                                                                        class="currency-selector currency-selector-<?= $id ?>"
                                                                        onchange="calculateInrValue(); updateSymbol_(this,<?= $id ?>)">
                                                                        <?php foreach ($get_currencies as $c): ?>
                                                                            <option data-symbol="<?= htmlspecialchars($c['symbol']) ?>"
                                                                                value="<?= $c['id'] ?>"
                                                                                <?= ((!empty($fees['document_currency']) && $fees['document_currency'] == $c['id'])
                                                                                    || (empty($fees['document_currency']) && ($fees['university_quotation_currency'] ?? '') == $c['id']))
                                                                                    ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($c['name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                            } else { ?>
                                                <?php foreach ($university_applicant_fees as $fees):
                                                    $id         = $fees["id"];
                                                    if (!in_array($id, [5, 6])) {
                                                        continue;
                                                    }
                                                    $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                                    $symbol     = $currency_lookup[$fees["currency_id"]]["symbol"]
                                                        ?? $currency_lookup[$fees["university_quotation_currency"]]["symbol"]
                                                        ?? '$';
                                                    $fees['quotation_name'] == 'Hostel'  ? $fees['quotation_name'] = 'Month Rent' : $fees['quotation_name'] = $fees['quotation_name'];

                                                ?>
                                                    <tr class="fee-row" data-id="<?= $id ?>">
                                                        <td>
                                                            <label><?= htmlspecialchars($fees['quotation_name']) ?> <small class="text-danger">*</small></label>
                                                            <div class="input-group form-group credit-currency-change">
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
                                                                    placeholder="0.00" value="<?= $fees["amount"] ?? 0 ?>">

                                                                <div class="input-group-addon">
                                                                    <select name="<?= $field_name ?>_currency_type"
                                                                        class="currency-selector currency-selector-<?= $id ?>"
                                                                        onchange="calculateInrValue(); updateSymbol_(this,<?= $id ?>)">
                                                                        <?php foreach ($get_currencies as $c):  $selectedCurrency = ($c['id'] == $rentalInfo[$id]['currency']) ? 'selected' : '' ?>
                                                                            <option data-symbol="<?= htmlspecialchars($c['symbol']) ?>" <?= $selectedCurrency ?>
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
                                                            <!-- <label>Value INR <small class="text-danger">*</small></label>
                                                            <div class="input-group form-group">
                                                                <div class="input-group-addon">₹</div>
                                                                <input type="number" step="0.01" name="<?= $field_name ?>_inr_value"
                                                                    required class="form-control currency-amount fees-inr-value-<?= $id ?>" readonly
                                                                    placeholder="0.00" value="<?= $fees["value_inr"] ?? '' ?>">
                                                            </div> -->


                                                            <label><?= htmlspecialchars($fees['quotation_name']) ?> <small class="text-danger">*</small></label>
                                                            <div disabled class="input-group form-group document-currency-change">

                                                                <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                                    <?= htmlspecialchars($symbol) ?>
                                                                </div>

                                                                <input type="text" name="<?= $field_name ?>_amount" <?= $id == 5 ? 'required' : '' ?>
                                                                    oninput="calculateInrValue()"
                                                                    readonly
                                                                    class="form-control currency-amount fees_<?= $fees['id'] ?>"
                                                                    placeholder="0.00" value="<?= $fees["amount"] ?? 0 ?>">

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
                                                    </tr>
                                            <?php
                                                endforeach;
                                            }
                                            ?>

                                        </tbody>
                                        <tfoot id="main-university-due-pay">

                                            <tr>
                                                <td colspan="" class="d-flex">
                                                    <div>Pay To <small class="text-danger">*</small></div>
                                                    <div class="col-md-8 form-group trans-div" style="display:<?= !empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == 1 ? '' : 'none' ?>;">

                                                        <select class="form-control selectpicker electpicker-new transaction_type"
                                                            data-live-search="true"
                                                            data-actions-box="false"
                                                            title="Select Transaction Type"
                                                            name="transaction_type"
                                                            data-name='transaction_type'
                                                            required>
                                                            <?php foreach ($transaction_type as $t_type): ?>
                                                                <option value="<?= $t_type['id'] ?>"
                                                                    <?= ($university_due_array["main"]['pay_info'][0]["transaction_type"] == $t_type['id']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($t_type['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    </div>

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
                                                        if (!empty($university_due_array["main"]['pay_info'][0]["payMode"]) && $university_due_array["main"]['pay_info'][0]["payMode"] == 1 || $university_due_array["main"]['pay_info'][0]["payMode"] == 4 || $university_due_array["main"]['pay_info'][0]["payMode"] == 6) {
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
                                                    <!-- <input type="input" readonly name="totalINRValue" class="form-control" value="<?= !empty($university_due_array["main"]['pay_info'][0]["totalINRValue"]) ? $university_due_array["main"]['pay_info'][0]["totalINRValue"] : 0 ?>"> -->
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>



                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Company Dues -->
                <div class="panel_s hide">
                    <div class="panel-body">
                        <div class="row align-items-center mb-2">
                            <div class="col-md-6">
                                <h4 class="mb-0">Hostel Dues</h4>
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
                                    <th style="width:25%; text-align:center;">Document Value</th>
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

                                                <?php if (in_array($payMode, [1, 4, 6])): ?>
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
                                                <button type="button" class="btn btn-danger btn-remove-table" onclick="$(this).closest('.aditional_university_dues').remove();">
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

                                                                <?php if (!empty($l_array["payMode"]) && in_array($l_array["payMode"], [1, 4, 6])): ?>
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

                <?php if (has_permission('hostel_management', '', 'quotation')) { ?>
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
    var get_university_rentData = <?= json_encode($hostelRentelData) ?>;
    var selectedUniversityRoomData = [];

    function get_hostel_rentInfo(id) {
        console.log(id);
        console.log(get_university_rentData[id]);
        if (get_university_rentData[id]) {
            console.log(get_university_rentData[id]);
            let rooms = get_university_rentData[id].rooms ? JSON.parse(get_university_rentData[id].rooms) : [];
            console.log(rooms);
            selectedUniversityRoomData = rooms;


        }
    }

    $(function() {

        // Initialize form validation
        appValidateForm($('#hostel_quotation_form'), {
            university_name: 'required',
            // floor_No: 'required',
            // room_No: 'required',
            company: 'required',
            hostel: 'required',
            room_capacity: 'required',
            start_date: 'required',
            end_date: 'required',
            services: 'required'
        });

        get_hostel_rentInfo(<?= $hostelData->hostel ?>);
        const applicantForm = document.getElementById("hostel_quotation_form");

        if (applicantForm) {
            console.log("check");
            applicantForm.addEventListener("submit", function(event) {
                event.preventDefault(); // prevent page reload
                console.log("check okk");

                handleFormSubmission(applicantForm, event);
            });
        }



    });

    let calculationTimeout;

    function debounceCalculate() {
        clearTimeout(calculationTimeout);
        calculationTimeout = setTimeout(calculateInrValue, 300);
    }

    document.addEventListener("input", function(event) {
        // Check if the target matches any of the selectors
        if (
            event.target.matches("input[name='exchange_value[]']") ||
            event.target.matches(".currency-amount") ||
            event.target.matches("input[name='fee_value[]']")
        ) {
            debounceCalculate(); // Call your debounce function
        }
    });


    function selectRoomCapacity(id) {
        let roomData = selectedUniversityRoomData.find(r => r.room_capacity == id);
        if (roomData) {
            console.log("roomData", roomData);
            $("input[name='rent']").val(roomData.rent);
            $("select[name='rent_currency_type']").val(roomData.currency);
            $(".fees_5").val(roomData.rent);
            $(".currency-selector-5").val(roomData.currency);
            updateSymbol_($(".currency-selector-5"), 5);
        } else {
            $(".fees_5").val(0);
            updateSymbol_($(".currency-selector-5"), 5);
            $(".currency-selector-5").val('');
            $("input[name='rent']").val('');
            $("select[name='rent_currency_type']").val('');
        }
        calculateInrValue();
    }

    // --- Server-side data ---
    const universityApplicantFeesArray = <?php echo json_encode($university_applicant_fees_array); ?>;
    const getClientsFees = <?php echo json_encode($get_clients_fees); ?>;
    var exchangeRates = {};
    const TSC = 0;
    // --- Utility functions ---
    const toInt = val => parseInt(val) || 0;
    const toFloat = val => parseFloat(val) || 0;
    const formatCurrency = (value, decimals = 2) => value.toFixed(decimals);
    var hostel_quotation_data = <?php echo json_encode($hostel_quotation_data); ?>;
    var payment_mod = <?php echo json_encode($modes); ?>;
    var payment_mode_vendors = <?php echo json_encode($modes_vendor); ?>;

    function vendor_update(obj, modeId) {
        // 🔹 Find the vendor select in the same row as the changed mode
        let vendor_select = $(obj).closest("tr").find("select[name='university_pay_vendor']");
        vendor_select.empty();
        vendor_select.show();
        $(obj).closest("tr").find("input.manually-cash").hide();
        $(obj).closest("tr").find("input.manually-cash").remove();

        // $(obj).parents('.main-university-due,.aditional-university-due-table').find('.trans-div select').val('').selectpicker('refresh');
        if (modeId != 1) {
            // $(obj).parents('.main-university-due,.aditional-university-due-table').find('.trans-div').hide();
        }

        // 🔹 Filter vendors by mode
        let vendors = payment_mode_vendors.filter(v => v.mode == modeId);

        if (modeId == 1 || modeId == 4 || modeId == 6) {
            if (modeId == 1) {
                // $(obj).parents('.main-university-due,.aditional-university-due-table').find('.trans-div').show();

            }
            if (vendors.length > 0) {
                vendor_select.append('<option value="">-- Select Vendor --</option>');
                vendors.forEach(v => {
                    vendor_select.append(`<option value="${v.id}">${v.name}</option>`);
                });
            } else {
                vendor_select.append('<option value="">No vendors available</option>');
            }
        } else if (modeId == 2) {
            vendor_select.append(
                '<option value="<?= htmlspecialchars($hostelData->university_name) ?>" selected>' +
                '<?= htmlspecialchars($hostelData->university_name) ?>' +
                '</option>'
            );

            // vendor_select.val("<?= htmlspecialchars($hostelData->university_name) ?>");
            vendor_select.selectpicker('refresh');
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


    // --- Calculate INR values for all fee rows ---
    function calculateInrValue() {
        let exchangeRates = {};

        // Build exchange rate map
        document.querySelectorAll("#exchangeTableBody tr").forEach(row => {
            const credit_currency = row.querySelector("select[name='credit_currency[]']");
            const document_currency = row.querySelector("select[name='document_currency[]']");
            const amountInput = row.querySelector("input[name='exchange_value[]']");

            if (credit_currency && document_currency && amountInput) {
                const credit_currencyId = credit_currency.value;
                const document_currencyId = document_currency.value;
                const rate = parseFloat(amountInput.value) || 0;
                exchangeRates[credit_currencyId + "_" + document_currencyId] = rate;
            }
        });

        console.log(exchangeRates);

        // 🔹 Update all fee rows inside each university table
        document.querySelectorAll(".university-feesTable .fee-row").forEach(feeRow => {

            const feeId = feeRow.dataset.id;
            const amountInput = feeRow.querySelector(`.credit-currency-change .fees_${feeId}`);
            const credit_currency = feeRow.querySelector(`.credit-currency-change .currency-selector-${feeId}`).value;
            const document_currency = feeRow.querySelector(`.document-currency-change .currency-selector-${feeId}`).value;
            const inrInput = feeRow.querySelector(`.document-currency-change .fees_${feeId}`);
            const exchangeInput = feeRow.querySelector(`input[name$='_exchangeValue']`);

            console.log("amountInput", amountInput);
            console.log("credit_currency", credit_currency);
            console.log("document_currency", document_currency);
            console.log("inrInput", inrInput);

            if (!amountInput || !credit_currency || !inrInput) return;

            const amount = toFloat(amountInput.value);
            const currencyId = credit_currency;
            const exchangeRate = exchangeRates[credit_currency + "_" + document_currency] || 0;
            console.log("exchangeRate", exchangeRate);

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
        let missingFields = [];



        $(form)
            .find("input[required]:not([type='hidden']):visible, select[required]:visible, textarea[required]:visible")
            .each(function() {
                let value = $(this).val();

                if (!value || String(value).trim() === "") {
                    $(this).addClass("is-invalid");

                    // Try to get a readable label (check <label for=""> or placeholder or name)
                    let fieldLabel =
                        $("label[for='" + $(this).attr("id") + "']").text().trim() ||
                        $(this).attr("placeholder") ||
                        $(this).attr("name") ||
                        "Unnamed field";

                    // Add field to list of missing fields
                    missingFields.push(fieldLabel);

                    console.warn("Missing required:", fieldLabel);
                } else {
                    $(this).removeClass("is-invalid");
                }
            });

        // After checking all fields
        if (missingFields.length > 0) {
            // Scroll to first invalid field
            $('html, body').animate({
                scrollTop: $(".is-invalid").first().offset().top - 100
            }, 400);

            // Show alert (assuming alert_float is defined)
            alert_float(
                "danger",
                "Please fill the following required fields"
            );

            return false;
        }


        show_loader();
        try {
            const formData = new FormData();

            // 🔹 Currency Exchange (array)
            let currency_exchange = [];
            let seenCurrencies = new Set();
            let hasDuplicate = false;

            $("#exchangeTable tbody tr").each(function() {
                let credit_currency = $(this).find("select[name='credit_currency[]']").val() || null;
                let document_currency = $(this).find("select[name='document_currency[]']").val() || null;
                let exchangeValue = $(this).find("input[name='exchange_value[]']").val() || null;
                if (credit_currency || exchangeValue) {
                    if (seenCurrencies.has(credit_currency)) {
                        hasDuplicate = true;
                        $(this).find("select[name='exchange_currency[]']").addClass("is-invalid"); // highlight duplicate
                    } else {
                        seenCurrencies.add(credit_currency + "_" + document_currency);
                        currency_exchange.push({
                            credit_currency: credit_currency,
                            document_currency: document_currency,
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
                    credit_currency: $(this).find(".credit-currency-change select[name$='_currency_type']").val() || null,
                    document_currency: $(this).find(".document-currency-change select[name$='_currency_type']").val() || null,
                    exchange_value: $(this).find("input[name$='_exchangeValue']").val() || null,
                    payment_option: paymentOptionSelect.val() || null,
                    payment_option_status: paymentOptionSelect.is('[disabled]') ? 1 : 0,
                    inr_value: $(this).find(".document-currency-change input.currency-amount").val() || null,
                    amount: $(this).find("input[name$='_amount']").val() || null,
                    quotation_name: $(this).find("input[name='quotation_name']").val() || null,
                };
                university_dues.main.fees_info.push(rowData);
            });

            // ✅ Collect main pay info
            $(".main-university-due tfoot tr").each(function() {
                let rowData = {
                    transaction_type: $(this).find("select[name='transaction_type']").val() || null,
                    payMode: $(this).find("select[name='university_pay_mode']").val() || null,
                    payVendor: $(this).find("select[name='university_pay_vendor']").val() || null,
                    payAmount: $(this).find("input[name='university_pay_amount']").val() || null,
                    totalINRValue: $(this).find("input[name='totalINRValue']").val() || null
                };
                university_dues.main.pay_info.push(rowData);
            });
            $("#formInformationGet input, #formInformationGet select, #formInformationGet textarea").each(function() {
                let name = $(this).attr("name");
                if (name) {
                    formData.append(name, $(this).val());
                }
            });

            formData.append("hostel_info_id", <?= $getId ?>);


            // 🔹 Append to formData
            formData.append("currency_exchange", JSON.stringify(currency_exchange));
            formData.append("university_dues", JSON.stringify(university_dues));
            formData.append("company_dues", JSON.stringify(company_dues));
            formData.append("release_to_counsellor", $("#release_to_counsellor").prop("checked") ? 1 : 0);




            <?php if (!empty($quotation_id) && !empty($hostel_quotation_data)) { ?>
                formData.append("quotation_id", <?= !empty($quotation_id) ? $quotation_id : 0 ?>);
            <?php } ?>
            formData.append(csrfData.token_name, csrfData.hash);




            const response = await fetch(form.action, {
                method: "POST",
                body: formData
            });
            const data = await response.json();
            hide_loader();
            if (data.resp_code === "RCS") {
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



        // Delegate input events to parent container for better performance


        document.addEventListener("change", function(event) {
            if (event.target.matches("select[name='exchange_currency[]'], .currency-selector, select[name='fee_currency[]']")) {
                calculateInrValue();
            }
        });

        // Form submission handlers




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

        $("input[name='fee_value[]'],input[name='fee_value_inr[]'], .currency-amount").each(function() {
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
            $row.find("input[name='fee_value[]']").val(parseInt((fee.amount).replace(/,/g, ""), 10) ?? 0);

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



    function GeneratePDF(hostel_info_id, quotation_id) {
        $.ajax({
            url: "<?= admin_url('hostel_management/quotationGenerate') ?>", // your controller method
            type: "POST",
            data: {
                hostel_info_id: hostel_info_id,
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

    function DeleteQuotation(hostel_info_id, quotation_id) {
        $.ajax({
            url: "<?= admin_url('hostel_management/quotationDelete') ?>", // your controller method
            type: "POST",
            data: {
                hostel_info_id: hostel_info_id,
                quotation_id: quotation_id
            },
            beforeSend: function() {
                show_loader();
                // Optional: show loader
                // console.log("Generating PDF...");
            },
            success: function(response) {
                hide_loader();
                let data = JSON.parse(response);
                // console.log(data);
                if (data.resp_code || data.resp_code === "RCS") {
                    alert_float("success", data.resp_desc);
                    // Get current URL
                    // Get current URL
                    const url = new URL(window.location.href);

                    // Remove the "quotation_id" parameter
                    url.searchParams.delete("quotation_id");

                    // Reload the page with updated URL
                    window.location.href = url.toString();

                } else {
                    alert_float("danger", "Quotation not delete sucessfully");

                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert_float("danger", "Something went wrong. Please try again.");
            }
        });
    }
</script>