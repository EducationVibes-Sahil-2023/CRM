<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    [id^="nested-applicant-table-"] div.row {
        display: none !important;
    }

    .dataTables_wrapper div.row {
        display: none !important;
    }
</style>
<?php if (!has_permission('hostel_management', '', 'payment') && !has_permission('hostel_management', '', 'payment')) {

    echo ' <div class="row">
            <div class="col-md-12">
            <h4 class="fs-title text-center">No Payments View Access</h4>
            </div>
            </div>';
    die;
} ?>


<div class="panel_s">
    <div class="panel-body">
        <h4 class="fs-title">Payments Dues <a data-toggle="tooltip" data-title="Payment Summary" data-placement="bottom" class="btn btn-default btn-with-tooltip" onclick="getPayementInformation(<?= $getId ?>)"><i class="fa fa-bar-chart"></i></a></h4>
        <hr>

        <div class="PaymentInformationShow" style="display:none;">

        </div>
    </div>
</div>




<div class="panel_s">

    <input type="hidden" value='1' name="fess_info">
    <input type="hidden" value='' id="paymentIdFetch" name="paymentIdFetch">
    <div class="panel-body">
        <?php
        $payment_table = array(
            "Pay Date",
            "Type",
            "Mode",
            "Vendor",
            "Start Date",
            "End Date",
            "Room Capacity",
            "Months",
            "Amount",
            "Currency",
            "Status",
            "Proof",
            "Action",
        );
        ?>

        <style>
            select.disabled {
                pointer-events: none;
                /* blocks clicks */
                background-color: #e9ecef;
                /* Bootstrap-like gray */
                /* color: #6c757d; */
                opacity: 1;
            }
        </style>

        <div class="row">
            <div class="col-md-12">
                <div class="form-container">
                    <h4 class="fs-title">Payments</h4>
                    <hr>

                    <?php
                    render_datatable($payment_table, 'payment-table');
                    ?>
                    <br>
                </div>
            </div>
        </div>

    </div>
</div>

<?php init_tail(); ?>

<script>
    function getPayementInformation(hostel_info_id) {
        console.log("Client ID:", hostel_info_id);

        let $container = $(".PaymentInformationShow");

        if ($container.is(":visible")) {
            // Already visible → just hide
            $container.slideUp();
        } else {
            // Hidden → fetch API, inject content, then show
            $.ajax({
                url: admin_url + "hostel_management/payment_information", // CI controller method
                type: "POST",
                data: {
                    hostel_info_id: hostel_info_id
                },
                dataType: "json",
                success: function(response) {
                    if (response.resp_code === "RCS") {
                        $container.html(response.data).slideDown();
                    } else {
                        $container.html(
                            '<div class="alert alert-warning">' + response.resp_desc + '</div>'
                        ).slideDown();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $container.html(
                        '<div class="alert alert-danger">Error loading payment info.</div>'
                    ).slideDown();
                }
            });
        }
    }





    document.addEventListener("DOMContentLoaded", function() {
        var tAPI = "";
        $(function() {
            tAPI = initDataTable('.table-payment-table', admin_url + 'hostel_management/payment_table/' + <?= $getId ?>);
        });

        window.refreshPaymentTable = function() {
            if (tAPI) {
                tAPI.ajax.reload(null, false); // false = keep pagination, true = reset to first page
            }
        };

    });
    var nestedTableIdArray = {}; // Use object to store nested tables

    async function showSplit($select, jsonData, payment_id) {
        $("#paymentIdFetch").val(payment_id);
        const $tr = $($select).closest('tr');
        const mainTable = $('.table-payment-table').DataTable();
        const row = mainTable.row($tr);
        let CustomersServerParamsArray = {
            "fess_info": true
        }; // Add any server params if needed
        CustomersServerParamsArray.fess_info = "[name='fess_info']";
        CustomersServerParamsArray.payment_id = "[name='paymentIdFetch']";
        // Parse JSON if needed
        let feeData = jsonData;
        if (typeof feeData === "string") {
            try {
                feeData = JSON.parse(feeData);
            } catch (e) {
                console.error("Invalid fees JSON:", e);
                return;
            }
        }

        // Toggle child row visibility
        if (row.child.isShown()) {
            row.child.hide();
            $tr.removeClass('shown');
            return;
        }

        const secondaryTableColumns = ["Type", "Amount", "Currency", "INR Value"];
        const nestedTableId = `nested-applicant-table-${payment_id}`;

        // Create child row HTML
        const childHtml = `
        <div style="padding:0;">
            <table id="${nestedTableId}" class="table table-striped nested-applicant-table" style="width:100%; margin:0;">
                <thead>
                    <tr>
                        ${secondaryTableColumns.map(col => `<th>${col}</th>`).join("")}
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>`;

        row.child(childHtml).show();
        $tr.addClass('shown');

        // Destroy existing nested table if exists
        if ($.fn.DataTable.isDataTable(`#${nestedTableId}`)) {
            $(`#${nestedTableId}`).DataTable().clear().destroy();
        }


        // Initialize nested DataTable with server-side options
        nestedTableIdArray[nestedTableId] = await initDataTable(
            `#${nestedTableId}`,
            admin_url + 'hostel_management/payment_table/' + <?= $getId ?>,
            [0], // columns not orderable
            [0], // columns not searchable
            CustomersServerParamsArray, {
                fixedHeader: true,
                scrollY: "300px",
                scrollCollapse: true,
                paging: false,
                searching: false,
                info: false
            }
        );
    }
</script>

<?php
if (has_permission('hostel_management', '', 'payment')) {
    // Initialize data with null coalescing for safety
    $office_location  = $this->staff_model->office_location("", 1);


    $sql = "
    SELECT 
        aq.*,
        CONCAT('Q', ROW_NUMBER() OVER (PARTITION BY aq.university_name ORDER BY aq.id ASC)) AS quotation_label,
        CONCAT(aq.university_name, '-', aq.start_date,'-',aq.end_date,'-', aq.room_capacity, ' - ',
               'Q', ROW_NUMBER() OVER (PARTITION BY aq.university_name ORDER BY aq.id ASC)
        ) AS unique_id
    FROM " . db_prefix() . "hostel_quotation aq
    WHERE aq.hostel_info_id = ? and aq.status > 0
    ORDER BY aq.id DESC
";


    $hostel_quotations = $this->db->query($sql, [$getId])->result_array();
    array_unshift($hostel_quotations, array("id" => "", "name" => ""));
    $hostel_quotations = array_column($hostel_quotations, null, "id");
    // Cache database queries
    $ci = &get_instance();

    $transaction_type  = transaction_type(array("hostel_status" => 1));
    // Get all required data in optimized queries
    $company_dues_fees_array = $ci->db->get(db_prefix() . "company_dues_fees")->result_array();

    $university_applicant_fees = university_applicant_fees_payments([
        "payment_quotation_split" => 1
    ]);


    $university_applicant_fees_type = university_applicant_fees_payments([
        "payment_quotation_type" => 1
    ]);


    $university_applicant_fees_ = array_column($university_applicant_fees, NULL, 'id');
    $university_applicant_fees_array = university_applicant_fees_details([
        "fd.university_name" => $hostelData->university_name,
        "fd.acadmic_year"    => $acadmic_year
    ]);

    $years_array =  range(1, 6);
    // Get client data with optimized queries
    $get_clients_fees = [];
    $university_shortlisting = [];
    $partnerName = [];

    if (!empty($getId)) {
        $get_clients_fees = get_clients_fees_details($lead_type_status, $getId);
        $university_shortlisting = $ci->clients_model->university_shortlisting($getId, 1);

        if (!empty($university_shortlisting[0]["partner"])) {
            $partnerName = $ci->db
                ->select("id, name")
                ->from(db_prefix() . "university_partner")
                ->where("id", $university_shortlisting[0]["partner"])
                ->get()
                ->row_array();
        }
    }


    // print_r($FessAmounts);

    // Cache currencies and payment modes
    $get_currencies = get_currencies();
    $currency_lookup = array_column($get_currencies, NULL, 'id');
    $modes = $ci->quotation_model->payment_mod();
    $modes_vendor = $ci->quotation_model->payment_mode_vendors();

    // die;

    // Initialize variables with default values
    $applicant_payment_data = [];
    $exchange_value_array = [];
    $university_due_array = [];
    $company_due_array = [];
    $fees_data = [];
    $selectedType = [];
    $splitData = [];
    $payment_id = !empty($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;

    if (!empty($payment_id)) {
        $applicant_payment_data = $ci->Hostel_model->hostel_paymentData($getId, $payment_id);

        if (!empty($applicant_payment_data->exchange_value)) {
            $exchange_value_array = json_decode($applicant_payment_data->exchange_value, true) ?? [];
        }

        if (!empty($applicant_payment_data->university_due)) {
            $university_due_array = json_decode($applicant_payment_data->university_due, true) ?? [];
        }

        if (!empty($applicant_payment_data->company_due)) {
            $company_due_array = json_decode($applicant_payment_data->company_due, true) ?? [];
        }

        if (!empty($applicant_payment_data->fess_infomation)) {
            $splitData = json_decode($applicant_payment_data->fess_infomation, true);
        }

        $selectedType = explode(",", $applicant_payment_data->type);
    }

    $payment_payment_mode = $ci->db->get(db_prefix() . 'quotation_paymente_mode')->result_array();



?>








    <div class="row">
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-body">
                    <h4>Quotation Payments</h4>
                    <hr class="hr-panel-heading" />

                    <?= form_open(admin_url('hostel_management/payment_quotation'), ['id' => 'applicant-payment-form', 'enctype' => 'multipart/form-data']); ?>
                    <input type="hidden" name="hostel_info_id" value="<?= $getId ?>">
                    <!-- University Info -->
                    <div id="formInformationGet">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="university_name">University Name <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="university_name" id="university_name" readonly
                                        value="<?= htmlspecialchars($hostelData->university_name) ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('start_date', 'Start Date ', $applicant_payment_data->start_date ?? '', 'date', ["placeholder" => "Select Start Date", "readonly" => true]); ?>
                            </div>

                            <div class="col-md-3">
                                <?= render_input('end_date', 'End Date ', $applicant_payment_data->end_date ?? '', 'date', ["placeholder" => "Select End Date", "readonly" => true]); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_input('room_capacity', 'Room Capacity ', $applicant_payment_data->room_capacity ?? '', 'number', ["placeholder" => "Enter Room capacity", "readonly" => true]); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Currency Exchange -->
                    <div class="panel_s shadow">
                        <div class="panel-body">
                            <h4 class="text-bold">Currency Exchange Rates</h4>
                            <label>Disable Conversion <input type="checkbox" value='1' name="currency_disabled" <?= !empty($applicant_payment_data->currency_disabled) ? 'checked' : '' ?> onclick="currencyDisabled(this)"></label>
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
                                    <?php if (!empty($exchange_value_array)): ?>
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

                            <div id="applicant_fees">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-6">
                                        <h4 class="mb-0">Hostel Quotation Payments</h4>
                                    </div>
                                    <?php if (empty($payment_id)) { ?>
                                        <div class="col-md-6 text-right hide">
                                            <button type="button" class="btn btn-primary" onclick="newClone()">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <hr>
                                <div class="payment_payment">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <?php
                                            echo render_select(
                                                'quotation_id',
                                                $hostel_quotations,
                                                ['id', 'unique_id'], // first = value, second = label
                                                'Hostel Quotations',
                                                [$applicant_payment_data->quotation_id ?? ''],
                                                [
                                                    'data-width' => '100%',
                                                    'data-none-selected-text' => 'Hostel Quotations',
                                                    'onchange' => 'check_quotations(this.value)',
                                                    'class' => 'electpicker-new quotation_id',
                                                    'required' => true

                                                ],
                                                [],
                                                'no-mbot',
                                                'electpicker-new quotation_id',
                                                false,
                                                'quotation_id'
                                            );
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2 form-group">
                                            <label>Payment Date <span class="text-danger">*</span></label>
                                            <input type="date" name="pay_date" data-name="pay_date" class="form-control pay_date" value="<?= htmlspecialchars($applicant_payment_data->pay_date ?? $pay_date) ?>" required>
                                        </div>

                                        <div class="col-md-2">
                                            <div class=" form-group">
                                                <label>Mode <span class="text-danger">*</span></label>
                                                <select class="form-control selectpicker electpicker-new mode"
                                                    name="mode" data-name="mode"
                                                    required data-live-search="true" data-size="5"
                                                    title="Select Mode"
                                                    onchange="vendor_update(this,this.value); check_tt_copy(this)">
                                                    <?php foreach ($modes as $m): ?>
                                                        <option value="<?= $m['id'] ?>"
                                                            <?= (!empty($applicant_payment_data->mode) &&
                                                                $applicant_payment_data->mode == $m["id"]) ? "selected" : "" ?>
                                                            <?= !empty($applicant_payment_data->mode) && $applicant_payment_data->mode == $m["id"] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($m['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>



                                        <?php if (!empty($applicant_payment_data->mode) &&  $applicant_payment_data->mode == 5) { ?>
                                            <script>
                                                $(document).ready(function() {
                                                    console.log("hide vendor");
                                                    $(".vendor_id").hide();
                                                })
                                            </script>
                                        <?php
                                        }

                                        ?>

                                        <div class="col-md-2 form-group">
                                            <label>Vendor <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new vendor_id"
                                                onchange="check_tt_copy(this)"
                                                style="display:<?= (!empty($applicant_payment_data->mode) &&
                                                                    $applicant_payment_data->mode == 5) ? 'none' : 'block' ?>"
                                                id="vendor_id" name="vendor_id" data-name="vendor_id"
                                                required data-live-search="true" title="Select Vendor">
                                                <?php
                                                if (!empty($applicant_payment_data->mode) && $applicant_payment_data->mode == 1 || $applicant_payment_data->mode == 4  || $applicant_payment_data->mode == 6) {
                                                ?>
                                                    <option value="">Select Vendor</option>
                                                    <?php
                                                    foreach ($modes_vendor as $vendor) {
                                                        if ($vendor["mode"] == $applicant_payment_data->mode) {
                                                    ?>
                                                            <option value="<?= $vendor["id"] ?>" <?= $vendor["id"] == $applicant_payment_data->vendor_id ? "selected" : "" ?>><?= $vendor["name"] ?></option>

                                                    <?php
                                                        }
                                                    }
                                                } else if (!empty($applicant_payment_data->mode) && $applicant_payment_data->mode == 2 || $applicant_payment_data->mode == 3) {
                                                    ?>
                                                    <option value="<?= $applicant_payment_data->vendor_name ?>" selected><?= $applicant_payment_data->vendor_name ?></option>
                                                <?php

                                                }

                                                ?>
                                            </select>
                                            <?php
                                            if (!empty($applicant_payment_data->mode) && $applicant_payment_data->mode == 5) {
                                            ?>
                                                <input type="text" data-name="vendor_name" name="vendor_name" required class="form-control manually-cash" placeholder="Enter Vendor Name" value="<?= $applicant_payment_data->vendor_name ?>">
                                            <?php
                                            }
                                            ?>
                                        </div>

                                        <!-- <div class="col-md-2 form-group trans-div" style="display:<?= !empty($applicant_payment_data->mode) && $applicant_payment_data->mode == 1 ? '' : 'none' ?>;"> -->
                                        <div class="col-md-2 form-group trans-div">
                                            <label>Transaction Type <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new transaction_type"
                                                onchange="check_tt_copy(this)"
                                                data-live-search="true"
                                                data-actions-box="false"
                                                title="Select Transaction Type"
                                                name="transaction_type"
                                                data-name='transaction_type'
                                                required>
                                                <?php foreach ($transaction_type as $t_type): ?>
                                                    <option value="<?= $t_type['id'] ?>"
                                                        <?= ($applicant_payment_data->transaction_type == $t_type['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($t_type['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label>Payment Type <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new payment_type"
                                                data-live-search="true"
                                                data-actions-box="false"
                                                title="Select Payment Type"
                                                name="payment_type"
                                                data-name='payment_type'
                                                required
                                                onchange="split_data(this, this.value)">
                                                <?php foreach ($university_applicant_fees_type as $fees): if (!in_array($fees['id'], [5, 6, 11, 16])) {
                                                        continue;
                                                    } ?>
                                                    <option value="<?= $fees['id'] ?>"
                                                        <?= ($applicant_payment_data->payment_type == $fees['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($fees['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                        </div>




                                        <div class="col-md-2 form-group split-type-dropdown" style="display:<?= !empty($applicant_payment_data->payment_type) && $applicant_payment_data->payment_type == PACKAGE_FEES_ID || RETURN_FEES_ID ? 'show' : 'none' ?>">
                                            <label>Payment Fees Type <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new type"
                                                multiple
                                                data-live-search="true"
                                                data-actions-box="true"
                                                data-selected-text-format="count > 3"
                                                name="type" ,
                                                data-name="type"

                                                onchange="split_data(this)">
                                                <?php foreach ($university_applicant_fees as $fees): if (!in_array($fees['id'], [5, 6, 11])) {
                                                        continue;
                                                    } ?>
                                                    <option value="<?= $fees['id'] ?>"
                                                        <?= (is_array($selectedType) && in_array($fees['id'], $selectedType)) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($fees['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                    </div>
                                    <div class="row">

                                        <div class="col-md-3 form-group">
                                            <label>Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-addon currency-symbol-amount_<?= time() ?>">
                                                    <?= $currency_lookup[!empty($applicant_payment_data->ex_currency) ? $applicant_payment_data->ex_currency : 3]["symbol"] ?? '' ?>
                                                </div>
                                                <input type="number" name="amount" data-name="amount" class="form-control amount currency-amount"
                                                    placeholder="0.00" required oninput="calculateInrValue()" value="<?= $applicant_payment_data->amount ? $applicant_payment_data->amount : '' ?>">
                                                <div class="input-group-addon">
                                                    <select name="ex_currency" data-id="amount_<?= time() ?>" data-name="ex_currency"
                                                        class="currency-selector currency-selector-amount disabled ex_currency"
                                                        onchange="calculateInrValue(); updateSymbol_(this,'amount_<?= time() ?>')">
                                                        <?php foreach ($get_currencies as $c): ?>
                                                            <option value="<?= $c['id'] ?>" <?= $applicant_payment_data->ex_currency == $c['id'] ? 'selected' : '' ?> data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                                <?= htmlspecialchars($c['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>Proof <span class="text-danger">*</span></label>
                                            <input type="file" name="proof" data-name="proof" class="form-control proof" <?= !empty($applicant_payment_data->pdf) ? '' : 'required' ?>>
                                            <?php
                                            $file_url = !empty($applicant_payment_data->pdf) ? $applicant_payment_data->pdf : "";
                                            if (!empty($file_url)) { ?>
                                                <br>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>

                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>INR Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-addon currency-symbol">
                                                    <?= htmlspecialchars($currency_lookup[3]["symbol"] ?? '') ?>
                                                </div>
                                                <input type="number" name="inr_value" required value="<?= $applicant_payment_data->inr_value ?? $applicant_payment_data->inr_value ?>" data-name="inr_value" id="inr_value" oninput="calculateInrValue()" class="form-control inr_value" <?= $applicant_payment_data->currency_disabled == 1 ? '' : 'readonly' ?>>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 form-group hide">
                                            <label>TT copy</label><br>
                                            <input type="checkbox" class="from-control tt_copy" <?= !empty($applicant_payment_data->tt_copy) ? 'checked' : '' ?> value="1" name="tt_copy" data-name="tt_copy">
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>TT Proof </label>

                                            <input type="file" name="tt_proof" <?= (
                                                                                    !empty($applicant_payment_data->mode) &&
                                                                                    (
                                                                                        $applicant_payment_data->mode == 2 ||
                                                                                        ($applicant_payment_data->mode == 4 && $applicant_payment_data->vendor_id == 5) ||
                                                                                        ($applicant_payment_data->mode == 1 && $applicant_payment_data->transaction_type == 1)
                                                                                    )
                                                                                ) ? '' : 'disabled' ?>
                                                data-name="tt_proof" class="form-control tt_proof">
                                            <?php
                                            $file_url = !empty($applicant_payment_data->tt_pdf) ? $applicant_payment_data->tt_pdf : "";
                                            if (!empty($file_url)) { ?>
                                                <br>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>

                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>Location</label>
                                            <select name="location_id" data-name="location_id" <?= !empty($applicant_payment_data->location_id) ? '' : 'disabled' ?>
                                                data-live-search="true" data-actions-box="true" title="Select Location"
                                                class="selectpicker electpicker-new form-control location_id">
                                                <?php foreach ($office_location as $l): ?>
                                                    <option value="<?= $l['id'] ?>" <?= $applicant_payment_data->location_id && $applicant_payment_data->location_id ==  $l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label>Remark</label>
                                            <textarea class="form-control remark" name="remark" data-name="remark"><?= $applicant_payment_data->remark ? $applicant_payment_data->remark : '' ?></textarea>
                                        </div>
                                    </div>

                                    <div id="applicant_fees_split" class="payment-split-data" style="display:<?= !empty($splitData) ? 'show' : 'none' ?>">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-6">
                                                <h4 class="mb-0">Hostel Quotation Payments Split Data</h4>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="payment_payment_split">
                                            <table class="table table-bordered payment_payment_split_table">
                                                <thead>
                                                    <tr>
                                                        <td>Fees type</td>
                                                        <td>Fees Amount</td>
                                                        <td>Ex-currency</td>
                                                        <td>INR Value</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $totalINRAmount = 0;
                                                    foreach ($splitData as $split) {
                                                        $totalINRAmount += $split['fee_inr_value'] ?? 0;
                                                    ?>
                                                        <tr data-fee-id="<?= $split['fee_id'] ?>">
                                                            <td>
                                                                <select class="form-control selectpicker" disabled>
                                                                    <option selected value="<?= $split['fee_id'] ?>"><?= $university_applicant_fees_[$split['fee_id']]['name'] ?></option>
                                                                </select>
                                                                <input type="hidden" name="selected_fees[]" value="<?= $split['fee_id'] ?>">
                                                            </td>
                                                            <td>
                                                                <div class="input-group">
                                                                    <div class="input-group-addon currency-symbol-<?= $split['fee_id'] ?>">

                                                                        <?= $currency_lookup[!empty($split['fee_currency']) ? $split['fee_currency'] : 3]["symbol"] ?>
                                                                    </div>
                                                                    <input type="number" step="0.01"
                                                                        name="fee_amount[<?= $split['fee_id'] ?>]"
                                                                        requried
                                                                        class="form-control fee-amount currency-amount  <?= $applicant_payment_data->payment_type != PACKAGE_FEES_ID ? 'auto-populated' : '' ?>"
                                                                        placeholder="0.00"
                                                                        value="<?= $split['fee_amount'] ?? 0 ?>"
                                                                        <?= $applicant_payment_data->payment_type != PACKAGE_FEES_ID ? 'readonly' : '' ?>
                                                                        oninput="calculateInrValue()">
                                                                    <div class="input-group-addon">
                                                                        <select name="amount_currency_type[<?= $split['fee_id'] ?>]"
                                                                            class="currency-selector disabled currency-selector-amount <?= $applicant_payment_data->payment_type != PACKAGE_FEES_ID ? 'auto-populated-select' : 'auto-populated-select' ?>"
                                                                            readonly
                                                                            onchange="calculateInrValue(); updateSymbol_(this,<?= $split['fee_id'] ?>)">
                                                                            <?php foreach ($get_currencies as $c): ?>
                                                                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $split['fee_currency'] ? 'selected' : '' ?> data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                                                    <?= htmlspecialchars($c['name']) ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <select disabled name="fee_currency[<?= $split['fee_id'] ?>]" class="form-control">
                                                                    <?php foreach ($get_currencies as $c): ?>
                                                                        <option value="<?= $c['id'] ?>" <?= $c['id'] == 3 ? 'selected' : '' ?> data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                                            <?= htmlspecialchars($c['name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" required name="fee_inr_value[<?= $split['fee_id'] ?>]"
                                                                    oninput="calculateInrValue()" class="form-control fee-inr" value="<?= $split['fee_inr_value'] ?? 0 ?>" <?= $applicant_payment_data->currency_disabled == 1 ? '' : 'readonly' ?>>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3">Total Amount</td>
                                                        <td>
                                                            <div class="input-group">
                                                                <div class="input-group-addon currency-symbol">
                                                                    <?= htmlspecialchars($currency_lookup[3]["symbol"] ?? '') ?>
                                                                </div>
                                                                <input type="text" name="total_inr_amount" data-name="total_inr_amount" value="<?= $totalINRAmount ?? 0 ?>" id="total_inr_amount" class="total_inr_amount form-control" readonly>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="payment_payment_aditional">
                    </div>

                    <?php if (has_permission('hostel_management', '', 'payment')): ?>
                        <div class="row text-right">
                            <button type="submit" class="btn btn-info mtop25"><?= !empty($payment_id) ? 'Update' : 'Create' ?></button>
                        </div>
                    <?php endif; ?>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            appValidateForm($('#applicant-payment-form'), {
                quotation_id: 'required',
                start_date: 'required',
                end_date: 'required',
                room_capacity: 'required',
                university_name: 'required',
            });
        });
        // --- Server-side data ---
        const hostel_quotations = <?= json_encode($hostel_quotations ?? []) ?>;
        const universityApplicantFeesArray = <?= json_encode($university_applicant_fees_array ?? []) ?>;
        const university_applicant_fees = <?= json_encode($university_applicant_fees ?? []) ?>;
        const get_currencies = <?= json_encode($get_currencies ?? []) ?>;
        const getClientsFees = <?= json_encode($get_clients_fees ?? []) ?>;
        const TSC = 0;

        let exchangeRates = {};
        const applicant_payment_data = <?= json_encode($applicant_payment_data ?? []) ?>;
        const payment_mod = <?= json_encode($modes ?? []) ?>;
        const payment_mode_vendors = <?= json_encode($modes_vendor ?? []) ?>;

        // Cache DOM elements for better performance
        let $exchangeTableBody, $applicantForm;

        // --- Utility functions ---
        const toInt = val => parseInt(val) || 0;
        const toFloat = val => parseFloat(val) || 0;
        const formatCurrency = (value, decimals = 2) => value.toFixed(decimals);

        // global counter for unique IDs
        window._cloneCounter = window._cloneCounter || 0;

        var currencyDisabledStatus = <?= !empty($applicant_payment_data->currency_disabled) ? $applicant_payment_data->currency_disabled : 0 ?>;


        function check_quotations(id) {
            try {
                // 🧩 Validate that hostel_quotations exists and has the given id
                if (!hostel_quotations || !hostel_quotations[id]) {
                    alert_float("danger", "Quotation not found or invalid selection.");
                    return;
                }

                let selected_quotation = hostel_quotations[id];

                // 🧠 Safely extract values (with fallback to empty string)
                const start_date = selected_quotation.start_date || "";
                const end_date = selected_quotation.end_date || "";
                const room_capacity = selected_quotation.room_capacity || "";
                console.log(selected_quotation);
                // 🏷️ Update input fields only if they exist
                const $startInput = $("input[name='start_date']");
                const $endInput = $("input[name='end_date']");
                const $roomCapacityInput = $("input[name='room_capacity']");
                if (id != "") {
                    $(".currency-selector-amount").val(selected_quotation.currency).change().addClass('disabled');
                } else {
                    $(".currency-selector-amount").removeClass('disabled');
                }
                if ($startInput.length) $startInput.val(start_date);
                if ($endInput.length) $endInput.val(end_date);
                if ($roomCapacityInput.length) $roomCapacityInput.val(room_capacity);

            } catch (error) {
                console.error("Error in check_quotations:", error);
                alert_float("danger", "Something went wrong while loading quotation details.");
            }
        }


        function currencyDisabled(obj) {
            $(".payment_payment").each(function() {
                var $paymentpayment = $(this);
                var paymentType = $paymentpayment.find("select[name='payment_type']").val();

                if ($(obj).is(":checked")) {
                    currencyDisabledStatus = 1;
                    if (paymentType != <?= PACKAGE_FEES_ID ?>) {
                        $paymentpayment.find(".inr_value")
                            .val('')
                            .removeAttr('readonly');
                    } else {
                        $paymentpayment.find(".inr_value, .fee-inr")
                            .val('')
                            .removeAttr('readonly');
                    }
                } else {
                    currencyDisabledStatus = 0;
                    if (paymentType != <?= PACKAGE_FEES_ID ?>) {
                        $paymentpayment.find(".inr_value")
                            .val('')
                            .attr('readonly', 'readonly');
                    } else {
                        $paymentpayment.find(".inr_value, .fee-inr")
                            .val('')
                            .attr('readonly', 'readonly');
                    }
                }
            });
            calculateInrValue();
        }


        function check_tt_copy(obj) {
            let paymentSection = $(obj).parents('.payment_payment');
            let modeId = paymentSection.find("select.mode").val();
            let vendor_select = paymentSection.find("select.vendor_id").val();
            let transaction_type = paymentSection.find("select.transaction_type ").val();

            if (modeId == 2 || (modeId == 4 && vendor_select == 5) || (modeId == 1 && transaction_type == 1)) {
                paymentSection.find("input.tt_proof ").attr("disabled", false).attr("required", true);
            } else {
                paymentSection.find("input.tt_proof ").val('').attr("disabled", true).attr("required", false);
            }
        }


        function setPaymentDate() {
            var today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input.pay_date[type="date"]').forEach(function(el) {
                el.setAttribute('max', today);
            });

        }
        // Set max date to today for all .pay_date inputs
        document.addEventListener('DOMContentLoaded', function() {
            setPaymentDate();
        });

        function newClone() {
            let html = `<div class="panel_s">
                        <div class="panel-body">
                      <div class="row text-right">
    <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.panel_s').remove();">
        <i class="fa fa-trash"></i>
    </button>
</div>

                        <div class="payment_payment">
                         <div class="row">
                                        <div class="form-group col-md-6">
                                            <?php
                                            echo render_select(
                                                'quotation_id_' . time(),
                                                $hostel_quotations,
                                                ['id', 'unique_id'], // first = value, second = label
                                                'Hostel Quotations',
                                                [''],
                                                [
                                                    'data-width' => '100%',
                                                    'data-none-selected-text' => 'Hostel Quotations',
                                                    'onchange' => 'check_quotations(this.value)',

                                                ],
                                                [],
                                                'no-mbot',
                                                'electpicker-new quotation_id',
                                                false,
                                                'quotation_id' . time()
                                            );
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2 form-group">
                                            <label>Payment Date <span class="text-danger">*</span></label>
                                            <input type="date" name="pay_date_<?= time() ?>" data-name="pay_date" class="form-control pay_date" value="" required>
                                        </div>

                                        <div class="col-md-2">
                                            <div class=" form-group">
                                                <label>Mode <span class="text-danger">*</span></label>
                                                <select data-name="mode" class="form-control selectpicker electpicker-new mode"
                                                    name="mode_<?= time() ?>"mode"
                                                    required data-live-search="true" data-size="5"
                                                    title="Select Mode"
                                                    onchange="vendor_update(this,this.value); check_tt_copy(this);">
                                                    <?php foreach ($modes as $m): ?>
                                                        <option value="<?= $m['id'] ?>">
                                                            <?= htmlspecialchars($m['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label>Vendor <span class="text-danger">*</span></label>
                                            <select data-name="vendor_id" onchange="check_tt_copy(this)" class="form-control selectpicker electpicker-new vendor_id"
                                                style="display:none"
                                                id="vendor_id" name="vendor_id_<?= time() ?>"vendor_id"
                                                required data-live-search="true" title="Select Vendor">
                                               
                                            </select>
                                        </div>

 <div class="col-md-2 form-group trans-div" style="display:none">
                                            <label>Transaction Type <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new transaction_type"
                                            onchange="check_tt_copy(this)"
                                                data-live-search="true"
                                                data-actions-box="false"
                                                title="Select Transaction Type"
                                                name="transaction_type"
                                                data-name='transaction_type'
                                                required>
                                                <?php foreach ($transaction_type as $t_type): ?>
                                                    <option value="<?= $t_type['id'] ?>">
                                                        <?= htmlspecialchars($t_type['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                        </div>
                                         <div class="col-md-3 form-group">
                                            <label>Payment Type <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new payment_type"
                                                data-live-search="true"
                                                data-actions-box="false"
                                                title="Select Payment Type"
                                                name="payment_type_<?= time() ?>"
                                                data-name='payment_type'
                                                required
                                                onchange="split_data(this, this.value)">
                                                <?php foreach ($university_applicant_fees_type as $fees): ?>
                                                    <option value="<?= $fees['id'] ?>" >
                                                        <?= htmlspecialchars($fees['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 form-group split-type-dropdown" style="display:none">
                                            <label>Payment Fees Type <span class="text-danger">*</span></label>
                                            <select class="form-control selectpicker electpicker-new type"
                                                multiple
                                                data-live-search="true"
                                                data-actions-box="true"
                                                data-selected-text-format="count > 3"
                                                name="type_<?= time() ?>" ,
                                                data-name="type"
                                                required
                                                onchange="split_data(this)">
                                                <?php foreach ($university_applicant_fees as $fees): ?>
                                                    <option value="<?= $fees['id'] ?>">
                                                        <?= htmlspecialchars($fees['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        </div>
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-addon currency-symbol-amount_<?= time() ?>">
                                                    <?= htmlspecialchars($currency_lookup[3]["symbol"] ?? '') ?>
                                                </div>
                                                <input type="number" data-name="amount" name="amount_<?= time() ?>"amount" class="form-control amount currency-amount"
                                                    placeholder="0.00" required oninput="calculateInrValue()" value="">
                                                <div class="input-group-addon">
                                                    <select data-name="ex_currency" name="ex_currency_<?= time() ?>"ex_currency"
                                                        class="currency-selector currency-selector-amount disabled ex_currency"
                                                        onchange="calculateInrValue(); updateSymbol_(this,'amount_<?= time() ?>')">
                                                        <?php foreach ($get_currencies as $c): ?>
                                                            <option value="<?= $c['id'] ?>" data-symbol="<?= htmlspecialchars($c['symbol']) ?>">
                                                                <?= htmlspecialchars($c['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>Proof <span class="text-danger">*</span></label>
                                            <input type="file" data-name='proof' name="proof_<?= time() ?>"proof" class="form-control proof" 
                                            required>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>INR Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-addon currency-symbol">
                                                    <?= htmlspecialchars($currency_lookup[3]["symbol"] ?? '') ?>
                                                </div>
                                                <input type="number" required data-name='inr_value' name="inr_value_<?= time() ?>" value=""inr_value" id="inr_value" class="form-control inr_value" oninput="calculateInrValue()" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>TT copy</label><br>
                                            <input type="checkbox" class="from-control tt_copy"  value="1" name="tt_copy_<?= time() ?>" data-name="tt_copy">
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label>Location</label>
                                            <select name="location_id_<?= time() ?>" data-name="location_id" disabled title="Select Location"
                                                data-live-search="true" data-actions-box="true"
                                                class="selectpicker electpicker-new form-control location_id">
                                                <?php foreach ($office_location as $l): ?>
                                                    <option value="<?= $l['id'] ?>" ><?= htmlspecialchars($l['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label>Remark</label>
                                            <textarea class="form-control remark" name="remark_<?= time() ?>" data-name="remark"></textarea>
                                        </div>
                                    </div>

                                    <div id="applicant_fees_split" class="payment-split-data" style="display:none">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-6">
                                                <h4 class="mb-0">Applicant payments Payments Split Data</h4>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="payment_payment_split">
                                            <table class="table table-bordered payment_payment_split_table">
                                                <thead>
                                                    <tr>
                                                        <td>Fees type</td>
                                                        <td>Fees Amount</td>
                                                        <td>Ex-currency</td>
                                                        <td>INR Value</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                  
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3">Total Amount</td>
                                                        <td>
                                                            <div class="input-group">
                                                                <div class="input-group-addon currency-symbol">
                                                                    <?= htmlspecialchars($currency_lookup[3]["symbol"] ?? '') ?>
                                                                </div>
                                                                <input type="text" required name="total_inr_amount_<?= time() ?>" data-name="total_inr_amount" id="total_inr_amount" class="total_inr_amount form-control" readonly>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                </div>`;
            $(".payment_payment_aditional").append(html);
            let $newSelects = $(".payment_payment_aditional .payment_payment").last().find("select.electpicker-new");

            // Initialize selectpicker on newly appended selects
            $newSelects.selectpicker();

            // Refresh UI
            $newSelects.selectpicker("refresh");
            setPaymentDate();
            // $(".payment_payment").append(html);
        }

        function vendor_update(obj, modeId) {
            let $formGroup = $(obj).closest(".form-group");
            let vendor_select = $formGroup.closest(".row").find("select.vendor_id");
            // $(obj).parents('.payment_payment').find('.trans-div select').val('').selectpicker('refresh');
            $(obj).parents('.payment_payment').find("input[name='proof']").attr("required", true);
            if (modeId != 1) {
                // $(obj).parents('.payment_payment').find('.trans-div').hide();
            }

            if (modeId == 5) {
                $(obj).parents('.payment_payment').find("input[name='proof']").removeAttr("required");
                vendor_select.removeAttr('required');
                $(obj).closest('.payment_payment')
                    .find("[name='location_id']")
                    .prop("disabled", false)
                    .prop("required", true)
                    .selectpicker('refresh'); // only if it's a <select>
            } else {
                vendor_select.attr('required');
                $(obj).closest('.payment_payment')
                    .find("[name='location_id']")
                    .val('')
                    .prop("disabled", true)
                    .prop("required", false)
                    .selectpicker('refresh'); // only if it's a <select>
            }

            // Clear vendor select
            vendor_select.empty().show();

            $(".vendor_id").show();
            // Remove any old manual input
            $formGroup.closest(".row").find("input.manually-cash").remove();

            // 🔹 Filter vendors by mode
            let vendors = payment_mode_vendors.filter(v => v.mode == modeId);

            if (modeId == 1 || modeId == 4 || modeId == 6) {
                if (modeId == 1) {
                    // $(obj).parents('.payment_payment').find('.trans-div').show();

                }
                if (vendors.length > 0) {
                    vendors.forEach(v => {
                        vendor_select.append(`<option value="${v.id}">${v.name}</option>`);
                    });

                    // ✅ auto-select first vendor
                    vendor_select.val(vendors[0].id);
                } else {
                    vendor_select.append('<option value="">No vendors available</option>');
                }
                vendor_select.val(vendor_select.find("option:first").val());

                vendor_select.selectpicker('refresh');

            } else if (modeId == 2) {
                vendor_select.append(
                    '<option value="<?= htmlspecialchars($hostelData->university_name) ?>" selected>' +
                    '<?= htmlspecialchars($hostelData->university_name) ?>' +
                    '</option>'
                );

                vendor_select.val("<?= htmlspecialchars($hostelData->university_name) ?>");
                vendor_select.selectpicker('refresh');

            } else if (modeId == 3) {
                vendor_select.append(
                    '<option value="<?= htmlspecialchars(!empty($partnerName['name']) ? $partnerName['name'] : "") ?>" selected>' +
                    '<?= htmlspecialchars(!empty($partnerName['name']) ? $partnerName['name'] : "No vendors available") ?>' +
                    '</option>'
                );

                vendor_select.val("<?= htmlspecialchars(!empty($partnerName['id']) ? $partnerName['id'] : "") ?>");
                vendor_select.selectpicker('refresh');

            } else if (modeId == 5) {
                // Destroy selectpicker before hiding
                vendor_select.selectpicker('destroy');
                vendor_select.hide();
                // Add manual input
                $('<input type="text" data-name="vendor_name" name="vendor_name" required class="form-control manually-cash" placeholder="Enter Vendor Name">')
                    .insertBefore(vendor_select);
            }
        }


        // helper to build currency select options
        function getCurrencyOptions(selectedId = 3) {
            let html = "";
            get_currencies.forEach(c => {
                html += `<option data-symbol="${c.symbol}" value="${c.id}" ${c.id == selectedId ? "selected" : ""}>${c.name}</option>`;
            });
            return html;
        }

        function split_data(obj, feesID = 0) {
            let selectedFeesIds = $(obj).val() || [];
            let singleSelectedValue = "";

            // Ensure always an array
            if (!Array.isArray(selectedFeesIds)) {
                singleSelectedValue = selectedFeesIds;
                selectedFeesIds = [selectedFeesIds];
            } else if (selectedFeesIds.length === 1) {
                singleSelectedValue = selectedFeesIds[0];
            }

            let $paymentpayment = $(obj).closest('.payment_payment');
            let paymentType = $paymentpayment.find("select[name='payment_type']").val();

            // If payment_type is NOT PACKAGE_FEES_ID, clear the split table
            if (paymentType != <?= PACKAGE_FEES_ID ?>) {
                $paymentpayment.find("table.payment_payment_split_table tbody").html('');
            }
            let $tbody = $paymentpayment.find("table.payment_payment_split_table tbody");

            // Remove rows NOT selected anymore
            $tbody.find("tr").each(function() {
                let feeId = $(this).data("fee-id") || 0;
                if (!selectedFeesIds.includes(feeId.toString())) {
                    $(this).remove();
                }
            });

            // Toggle split-data visibility
            $paymentpayment.find(".payment-split-data").toggle(selectedFeesIds.length > 0);

            // Build/update fee split rows
            selectedFeesIds.forEach(function(feeId) {
                feeId = feeId.toString();
                let unique = Date.now() + "_" + feeId;
                // Skip if already exists
                if ($tbody.find(`tr[data-fee-id='${feeId}']`).length > 0) {
                    return;
                }

                let readonly = 0;
                if (feesID > 0 && singleSelectedValue !== '' && singleSelectedValue != <?= PACKAGE_FEES_ID ?>) {
                    readonly = 1;
                }
                // Example: if (currencyDisabledStatus == 1) readonly = 0;

                let feeData = university_applicant_fees.find(f => f.id == feeId);
                if (feeData) {
                    let inrValue = 0;
                    if (paymentType != <?= PACKAGE_FEES_ID ?>) {
                        inrValue = parseFloat(feeData.inr_value || 0);
                    }

                    let row = `
            <tr data-fee-id="${feeData.id}">
                <td>
                    <select class="form-control selectpicker" disabled>
                        <option selected value="${feeData.id}">${feeData.name}</option>
                    </select>
                    <input type="hidden" name="selected_fees[]" value="${feeData.id}">
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon currency-symbol-${unique}">
                            <?= htmlspecialchars($currency_lookup[3]["symbol"] ?? '') ?>
                        </div>
                       <input 
    type="number" 
    step="0.01"
    name="fee_amount[${feeData.id}]"
    ${feeData.id == 5 ? 'required' : ''}
    class="form-control fee-amount currency-amount ${readonly == 1 ? 'auto-populated' : ''}"
    placeholder="0.00"
    ${readonly == 1 ? 'readonly' : ''}
    value="${feeData.amount || 0}"
    oninput="calculateInrValue()"
/>

                        <div class="input-group-addon">
                            <select name="amount_currency_type[${feeData.id}]"
                                    ${readonly == 1 ? 'readonly' : ''}
                                    data-id="${unique}" class="currency-selector disabled currency-selector-amount ${readonly == 1 ? 'auto-populated-select' : 'auto-populated-select'}"
                                    onchange="calculateInrValue(); updateSymbol_(this, '${unique}')">
                                ${getCurrencyOptions(3)}
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <select disabled name="fee_currency[${feeData.id}]" class="form-control">
                        ${getCurrencyOptions(3)}
                    </select>
                </td>
                <td>
                    <input type="number" required name="fee_inr_value[${feeData.id}]"
                           oninput="calculateInrValue()" class="form-control fee-inr" value="${inrValue.toFixed(2)}" ${currencyDisabledStatus == 1 ? '' : 'readonly'}>
                </td>
            </tr>
            `;
                    $tbody.append(row);
                    $tbody.find(".selectpicker").selectpicker("refresh");
                }
            });

            // Handle split-type dropdown
            let $panel = $(obj).closest('.panel_s');
            let $splitTypeDropdown = $panel.find(".split-type-dropdown");
            let $splitTypeSelect = $splitTypeDropdown.find("select.electpicker");
            if (feesID == <?= PACKAGE_FEES_ID ?> || feesID == <?= RETURN_FEES_ID ?>) {
                $splitTypeSelect.val('').selectpicker('refresh');
                $splitTypeDropdown.show();
                splitTypeDropdown.attr("required", true);

                $tbody.html('');
            } else if (feesID > 0 && singleSelectedValue !== '' && singleSelectedValue != <?= PACKAGE_FEES_ID ?>) {
                $splitTypeDropdown.hide();
                splitTypeDropdown.removeAttr("required");
                $splitTypeSelect.val('').selectpicker('refresh');
            }

            calculateInrValue();
        }




        // 🔹 Recalc INR when user edits amounts/currency
        $(document).on("input change", ".fee-amount,.fee-inr, select[name^='fee_currency'], select[name^='amount_currency_type'],.inr_value", function() {
            let $paymentpayment = $(this).closest('.payment_payment');
            recalcTotalINR($paymentpayment);
        });


        function calculateExchangeRate() {
            exchangeRates = {};
            document.querySelectorAll("#exchangeTableBody tr").forEach(row => {
                const currencySelect = row.querySelector("select[name='exchange_currency[]']");
                const amountInput = row.querySelector("input[name='exchange_value[]']");

                if (currencySelect && amountInput) {
                    const currencyId = currencySelect.value;
                    const rate = parseFloat(amountInput.value) || 1;
                    exchangeRates[currencyId] = rate;
                }
            });
        }

        function recalcTotalINR($paymentpayment) {
            let $tbody = $paymentpayment.find("table.payment_payment_split_table tbody tr");

            // Build exchange rate map from exchange table
            let exchangeRates = {};
            document.querySelectorAll("#exchangeTableBody tr").forEach(row => {
                const currencySelect = row.querySelector("select[name='exchange_currency[]']");
                const amountInput = row.querySelector("input[name='exchange_value[]']");
                if (currencySelect && amountInput) {
                    const currencyId = currencySelect.value;
                    const rate = parseFloat(amountInput.value) || 1;
                    exchangeRates[currencyId] = rate;
                }
            });

            let totalINR = 0;

            // Recalculate INR for each fee row
            $tbody.each(function() {
                let $row = $(this);
                let feeId = $row.data("fee-id") || 0;

                let amount = parseFloat($row.find(`input[name='fee_amount[${feeId}]']`).val()) || 0;
                let currency = $row.find(`select[name='amount_currency_type[${feeId}]']`).val();
                let rate = exchangeRates[currency] || 1;
                let inrValue = 0;

                if (typeof currencyDisabledStatus !== "undefined" && currencyDisabledStatus == 1) {
                    // If disabled, trust user-entered INR value
                    inrValue = parseFloat($row.find(".fee-inr").val()) || 0;
                } else {
                    // Otherwise, calculate
                    inrValue = amount * rate;
                    $row.find(".fee-inr").val(inrValue.toFixed(2));
                }

                totalINR += inrValue;
            });

            // Update total INR
            $paymentpayment.find("input.total_inr_amount").val(totalINR.toFixed(2));
        }



        // --- Fetch applicant fees ---
        function fetchApplicantFees(studyYear) {
            // Implementation if needed
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

            $exchangeTableBody.appendChild(clone);
            calculateInrValue();
        }

        function calculateInrValue() {
            calculateExchangeRate();

            $('.payment_payment').each(function() {
                const $entry = $(this);

                // Get main input values
                let amount = parseFloat($entry.find("input.amount").val()) || 0;
                let currency_id = $entry.find("select.ex_currency").val();

                // Get exchange rate for selected currency
                let rate = typeof exchangeRates !== "undefined" ? (exchangeRates[currency_id] || 1) : 1;

                // Calculate INR value
                let inrValue = amount * rate;

                // Set main INR value (only if not in manual/disabled mode)
                if (currencyDisabledStatus !== 1) {
                    $entry.find("input.inr_value").val(inrValue.toFixed(2));
                }

                // Auto-populated amount fields (e.g. split fee rows)
                $entry.find("input.auto-populated").val(amount);

                // If special package fee and disabled mode, propagate INR
                if (
                    currencyDisabledStatus == 1 &&
                    $entry.find("select[name='payment_type']").val() != 'undefined' && $entry.find("select[name='payment_type']").val() != <?= PACKAGE_FEES_ID ?>
                ) {

                    console.log($entry.find("select[name='payment_type']").val());
                    console.log(<?= PACKAGE_FEES_ID ?>);
                    console.log("Same INR Value");
                    $entry.find("input.fee-inr").val($entry.find("input.inr_value").val());
                }

                // Refresh read-only and force currency select state where needed
                $entry.find("select.auto-populated-select").each(function() {
                    let idd = $(this).data("id");
                    $(this)
                        .attr("readonly", true)
                        .val(currency_id);
                    updateSymbol_($(this), idd);
                });

                // Recalculate totals for this payment entry
                recalcTotalINR($entry);
            });
        }



        // Helper: robustly extract numeric id (with optional _suffix) from the addon element
        function getCurrencyNumericFromAddon($addon) {
            if (!$addon || !$addon.length) return null;

            // read class attribute and normalize whitespace / remove control chars
            let classAttr = $addon.attr('class') || '';
            classAttr = classAttr.replace(/[\r\n\t]+/g, ' ').replace(/\s+/g, ' ').trim();

            // debug: uncomment to see exact string (shows hidden chars)
            // console.log("raw classAttr (json):", JSON.stringify(classAttr));

            // tokenise by space and test each token
            const tokens = classAttr.split(' ');
            const re = /currency-symbol(?:-amount)?_(\d+(?:_\d+)?)/;
            const matches = [];

            for (let t of tokens) {
                const m = t.match(re);
                if (m) matches.push(m[1]); // capture group 1: digits or digits_suffix
            }

            if (matches.length === 0) return null;
            // prefer a match that contains underscore (the one with suffix)
            const withSuffix = matches.find(x => x.indexOf('_') !== -1);
            return withSuffix || matches[0];
        }


        function removeExchangeRow(row) {
            row.remove();
            calculateInrValue();
        }

        // --- Update currency symbol ---
        function updateSymbol_(obj, feeId) {
            try {
                const $selector = $(obj);

                if ($selector.length === 0) {
                    return;
                }

                const $selectedOption = $selector.find("option:selected");
                if ($selectedOption.length === 0) {
                    return;
                }

                const symbol = $selectedOption.data("symbol") || "";

                const $row = $selector.closest("tr, .form-group, .input-group");
                const $symbolEl = $row.find(`.currency-symbol-${feeId}`);

                if ($symbolEl.length) {
                    $symbolEl.text(symbol);
                }

            } catch (err) {
                console.error("updateSymbol_ error:", err, obj, feeId);
            }
        }

        async function validation_set(form_id) {
            return new Promise((resolve, reject) => {
                let form_status = true;
                let additional_fields = {};

                $("#" + form_id + " input:visible, #" + form_id + " select:visible, #" + form_id + " textarea:visible").each(function() {
                    let rawVal = $(this).val();
                    let value = "";

                    if (Array.isArray(rawVal)) {
                        // join multiple values and trim
                        value = rawVal.map(v => (v || "").toString().trim()).join(",");
                    } else {
                        value = (rawVal || "").toString().trim();
                    }

                    const name = $(this).attr("name");
                    const isRequired = $(this).is("[required]") ? 1 : 0;


                    if (isRequired && name) {
                        additional_fields[name] = "required";
                        if (!value) {
                            form_status = false;
                        }
                    }
                });

                if (!form_status) {
                    appValidateForm($("#" + form_id), additional_fields);
                    reject("Form validation failed.");
                } else {
                    resolve("Form validation passed.");
                }
            });
        }

        // --- Form submission handler ---
        async function handleFormSubmission(form, event) {
            event.preventDefault();


            let missingFields = [];

            $(form)
                .find("input[required]:not([type='hidden']):visible, select[required]:visible, textarea[required]:visible")
                .each(function() {
                    let value = $(this).val(); // safely get value
                    if (!value || String(value).trim() === "") {
                        $(this).addClass("is-invalid");

                        // Try to get readable label
                        let label = $(this).closest(".form-group").find("label").text().trim();
                        let fieldName = label || $(this).attr("name");

                        // Collect field name or handle it as you wish
                        console.warn("Missing required:", fieldName);
                    } else {
                        $(this).removeClass("is-invalid");
                    }
                });


            if (missingFields.length > 0) {
                $('html, body').animate({
                    scrollTop: $(".is-invalid").first().offset().top - 100
                }, 400);

                alert_float(
                    "danger",
                    "Please fill the following required fields:<br><b>" +
                    missingFields.join(", ") +
                    "</b>"
                );
                return false;
            }







            try {
                show_loader();
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
                            $(this).find("select[name='exchange_currency[]']").addClass("is-invalid");
                        } else {
                            seenCurrencies.add(currencyId);
                            currency_exchange.push({
                                currency_id: currencyId,
                                exchange_value: exchangeValue,
                            });
                        }
                    }
                });

                if (hasDuplicate) {
                    hide_loader();
                    alert_float("danger", "Duplicate Currency Exchange Rates detected. Please select unique currencies.");
                    return false;
                }

                // 🔹 Collect all payment payment data

                let paymentpayments = [];
                let error = false;

                $(".payment_payment").each(function(index) {

                    let q_id = $(this).find("select.quotation_id").val() || '';
                    let mode_id = $(this).find("select.mode").val() || '';
                    let payment_type_id = $(this).find("select.payment_type").val() || '';

                    // let $quotationSelect = $(this).find("select.quotation_id");

                    // if (mode_id === "1" && payment_type_id === "<?= PACKAGE_FEES_ID ?>") {
                    //     $quotationSelect.prop("required", true);
                    // } else {
                    //     $quotationSelect.prop("required", false);
                    // }

                    // refresh the Bootstrap select UI
                    // $quotationSelect.selectpicker("refresh");

                    let $payment = $(this);
                    let paymentData = {};
                    let totalAmountCheck = $(this).find("input[name='amount']").val() || 0;
                    let totalAmountCheck_ = 0;
                    let totalINRCheck = $(this).find("input[name='inr_value']").val() || 0;
                    let totalINRCheck_ = 0;
                    // 🔹 Collect all form data
                    $payment.find("input, select, textarea").each(function() {
                        let name = $(this).attr("data-name");
                        if (!name) return;

                        let value;

                        if ($(this).is(":checkbox")) {
                            // ✅ For checkboxes
                            value = $(this).is(":checked") ? 1 : 0;
                        } else if ($(this).is(":radio")) {
                            // ✅ Only use checked radio
                            if (!$(this).is(":checked")) return;
                            value = $(this).val();
                        } else if ($(this).is("select[multiple]")) {
                            // ✅ Handle multiple select
                            value = $(this).val() || [];
                        } else {
                            value = $(this).val();
                        }

                        if (value !== undefined) {
                            if (name.endsWith("[]")) {
                                // Normalize array fields
                                name = name.replace("[]", "");
                                if (!paymentData[name]) {
                                    paymentData[name] = [];
                                }
                                if (Array.isArray(value)) {
                                    paymentData[name] = paymentData[name].concat(value);
                                } else {
                                    paymentData[name].push(value);
                                }
                            } else {
                                paymentData[name] = value;
                            }
                        }
                    });

                    // 🔹 Collect split data rows
                    let splitData = [];
                    $payment.find("table.payment_payment_split_table tbody tr").each(function() {
                        let rowData = {
                            fee_id: $(this).data("fee-id") || null,
                            fee_amount: $(this).find(".fee-amount").val() || 0,
                            fee_currency: $(this).find("select.currency-selector-amount").val() || '',
                            fee_inr_value: $(this).find(".fee-inr").val() || 0
                        };
                        totalAmountCheck_ += parseFloat($(this).find(".fee-amount").val()) || 0;
                        totalINRCheck_ += parseFloat($(this).find(".fee-inr").val()) || 0;

                        splitData.push(rowData);
                    });

                    paymentData.split_data = splitData;

                    // ✅ Push payment object into main array
                    paymentpayments.push(paymentData);

                    // 🔹 Collect files
                    let fileInput = $(this).find("input[name='proof']")[0];
                    if (fileInput && fileInput.files.length > 0) {
                        $.each(fileInput.files, function(fIndex, file) {
                            formData.append("proof_" + index, file);
                        });
                    }


                    let fileInput_tt_proof = $(this).find("input[name='tt_proof']")[0];
                    if (fileInput_tt_proof && fileInput_tt_proof.files.length > 0) {
                        $.each(fileInput_tt_proof.files, function(fIndex, file) {
                            formData.append("tt_proof_" + index, file);
                        });
                    }
                    console.log(totalAmountCheck);
                    console.log(totalAmountCheck_);
                    if (parseFloat(totalAmountCheck) !== parseFloat(totalAmountCheck_)) {
                        error = true;
                        hide_loader();
                        alert_float("danger", "Hostel Quotation Payments Section " + (index + 1) + " Not match Amount.");
                        return false;
                    }

                    if (parseFloat(totalINRCheck) !== parseFloat(totalINRCheck_)) {
                        error = true;
                        hide_loader();
                        alert_float("danger", "Hostel Quotation Payments Section " + (index + 1) + " Not match INR Value. ");
                        return false;
                    }

                });

                $("#formInformationGet input, #formInformationGet select, #formInformationGet textarea").each(function() {
                    let name = $(this).attr("name");
                    if (name) {
                        formData.append(name, $(this).val());
                    }
                });

                await validation_set("applicant-payment-form");
                if (error == true) {
                    return false;
                }
                // 🔹 Add all form data to FormData
                $("#applicant-payment-form").serializeArray().forEach(function(field) {
                    formData.append(field.name, field.value);
                });

                // 🔹 Add files


                // 🔹 Add structured data
                formData.append("currency_exchange", JSON.stringify(currency_exchange));
                formData.append("payment_quotations", JSON.stringify(paymentpayments));
                formData.append("hostel_info_id", <?= $getId ?>);

                <?php if (!empty($payment_id) && !empty($applicant_payment_data)): ?>
                    formData.append("payment_id", <?= $payment_id ?>);
                <?php endif; ?>

                formData.append(csrfData.token_name, csrfData.hash);

                const response = await fetch(form.action, {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();
                hide_loader();

                if (data.resp_code === "RCS") {
                    alert_float("success", data.resp_desc);
                    let url = new URL(window.location.href);

                    if (url.searchParams.has("payment_id")) {
                        url.searchParams.delete("payment_id");
                        window.location.replace(url.toString());
                    } else {
                        location.reload();
                    }
                } else {
                    alert_float("danger", data.resp_desc);
                }
            } catch (error) {
                hide_loader();
                console.error("Error:", error);
                alert_float("danger", "Something went wrong! Please try again.");
            }
        }

        // --- Initialize application ---
        document.addEventListener("DOMContentLoaded", function() {
            // Cache frequently used DOM elements
            $exchangeTableBody = document.getElementById("exchangeTableBody");
            $applicantForm = document.getElementById("applicant-payment-form");

            // Initialize currency symbols
            $(".currency-selector").each(function() {
                const match = this.className.match(/currency-selector-(\d+)/);
                if (match) {
                    updateSymbol_(this, match[1]);
                }
            });

            $(document).on("mousedown", "select.readonly", function(e) {
                e.preventDefault(); // stop dropdown from opening
                this.blur(); // remove focus
            });

            // $(document).on("keyup", ".manually-cash", function() {
            //     let $row = $(this).closest("tr");
            //     let vendor_select = $row.find("select.vendor_id");
            //     vendor_select.empty();

            //     let vendorName = $(this).val().trim();
            //     if (vendorName !== "") {
            //         vendor_select.append(`<option value="${vendorName}" selected>${vendorName}</option>`);
            //     }
            // });

            // Setup event listeners
            if ($exchangeTableBody) {
                $exchangeTableBody.addEventListener("click", handleExchangeTableClick);
            }

            // Global input/change listeners with debouncing
            let calculationTimeout;



            // Delegate input events to parent container for better performance
            if ($applicantForm) {
                $applicantForm.addEventListener("input", function(event) {
                    if (
                        event.target.matches("input[name='exchange_value[]']") ||
                        event.target.matches(".currency-amount") ||
                        event.target.matches("input[name='fee_value[]']")
                    ) {
                        calculateInrValue();
                    }
                });

                $applicantForm.addEventListener("submit", (event) => handleFormSubmission($applicantForm, event));
            }

            // Initial calculation
            calculateInrValue();

            <?php if (!empty($payment_id)): ?>
                // update_package_amount();
            <?php else: ?>
                <?php
                $acadmicYearParts = !empty($acadmic_year) ? explode("-", $acadmic_year) : [];
                $acadmicYear = $acadmicYearParts[0] ?? '';
                ?>
                let acadmicYear = "<?= trim($acadmicYear) ?>";
                if (acadmicYear == <?= Date("Y") ?>) {
                    $("#study_year").val(1).trigger("change");
                }
            <?php endif; ?>

            appValidateForm($("#applicant-payment-form"));
        });


        function GeneratePDF(hostel_info_id, payment_id) {

            show_loader();
            $.ajax({
                url: "<?= admin_url('hostel_management/paymentGenerate') ?>",
                type: "POST",
                data: {
                    hostel_info_id: hostel_info_id,
                    payment_id: payment_id
                },
                beforeSend: function() {
                    // Optional: show loader
                    console.log("Generating PDF...");
                },
                success: function(response) {
                    hide_loader();

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
            $(document).on("focus", "input[name='fee_value[]'], .currency-amount,.inr_value", function() {
                // Force input type="number" with step for 4 decimals
                $(this).attr({
                    type: "number",
                    step: "0.0001", // up to 4 decimals
                    min: "0" // optional: prevent negative values
                });
            });
        }
        async function document_approved(obj, status = 0, quotation_payment_id, doc_id = null) {
            let upload_data = new FormData();
            try {
                // ✅ Ensure token and hash are strings
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>");
                upload_data.append("hostel_info_id", "<?= $getId ?>");
                upload_data.append("status", status);
                upload_data.append("quotation_payment_id", quotation_payment_id);

                show_loader();

                // ✅ Handle delete confirm
                if (status == 0) {
                    if (!confirm("Are you sure you want to delete Payments ?")) {
                        hide_loader();
                        return false;
                    }
                }

                // ✅ Ajax with dataType: "json"
                let response = await $.ajax({
                    url: "<?= base_url("admin/hostel_management/quotation_payment_approved") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false,
                    dataType: "json"
                });

                hide_loader();

                if (response.resp_code === "RCS") {
                    alert_float("success", response.resp_desc);
                    if (refreshPaymentTable()) {
                        refreshPaymentTable();
                    }

                } else {
                    alert_float("danger", response.resp_desc || "Unknown error occurred");
                }

            } catch (error) {
                hide_loader();
                console.error(error);
                alert_float("danger", error.responseText || error.statusText || "Something went wrong");
            }
        }
    </script>
<?php } ?>