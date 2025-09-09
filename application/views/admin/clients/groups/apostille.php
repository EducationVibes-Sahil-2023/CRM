<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$apostille_document  = get_apostille_document_data($client_id, 1);
$orignal_document_status  = orignal_document_status();
$office_location  = $this->staff_model->office_location();
$activity_apostille_document = activity_apostille_document($client_id);
$apostille_documents = get_orignal_document_list(0, 0, 1);
$apostille_visa_apostile_documents = get_orignal_document_list(0, 0, 0, 0, 0, 0, 1, ["status" => 0]);
$apostille_vendors = get_vendor_list(1);
$get_currencies = get_currencies();
$get_currencies = array_column($get_currencies, null, 'id');
if (!is_array($apostille_documents)) {
    $apostille_documents = [];
}
if (!is_array($apostille_visa_apostile_documents)) {
    $apostille_visa_apostile_documents = [];
}

$apostille_documents_new = array_merge($apostille_documents, $apostille_visa_apostile_documents);
array_unshift($apostille_vendors, array());

// $client = $this->clients_model->get($id);

array_unshift($office_location, array());
?>
<style>
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        text-wrap: auto !important;
    }
</style>

<?php
if (!is_postSale() && !is_admin()) {
?>
    <h2 class="text-center">Apostille Document - Accessible Only for Post-Sale & Admin</h2>
<?php
    die;
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Apostille Documents</h4>
            <!-- <div class="text-right">
                <?php
                if ($client_infomation->orignal_document_status == 3) { ?>
                    <?= getLastEmailWhatsappDate("whatsapp", 6, $client_id) ?><button type="button" class="btn btn-primary btn-xs " onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 6,'','')"><i class="fa fa-whatsapp"></i> </button>
                <?php }
                ?>
            </div> -->
            <hr>

            <form method="post" id="orignal-document-form">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <div id="orignal_documents" class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark ">
                            <tr>
                                <!-- <th scope="col">S.No</th> -->
                                <th scope="col">Document Name</th>
                                <th scope="col">Orignal Status</th>
                                <th scope="col">Cost</th>
                                <th scope="col">Currency</th>
                                <th scope="col">Status</th>
                                <th scope="col">Vendor</th>
                                <th scope="col">Apply By Vendor</th>
                                <th scope="col">Courier Date</th> <!-- Corrected "Courior" to "Courier" -->
                                <th scope="col">Receiving Date</th>
                                <th scope="col">Payment Date</th>
                                <th scope="col">Created By</th>
                                <th scope="col">Created Date</th>
                            </tr>

                        </thead>
                        <tbody class="document_upload_div">

                            <?php if (!empty($apostille_document)) : ?>
                                <?php
                                $index = 1;
                                foreach ($apostille_document as $key => $doc) :;
                                ?>
                                    <tr>

                                        <td>
                                            <?= $doc["name"] ?>

                                            <?php if (!empty($doc["info"])): ?>
                                                <i class="fa fa-info-circle" title="<?= htmlspecialchars($doc["info"]) ?>"></i>
                                            <?php endif; ?>

                                            <?php if (empty($doc["bulk"]) && !empty($doc["id"])): ?>
                                                <br>
                                                <a href="#"
                                                    data-toggle="modal"
                                                    data-target="#customers_apostille"
                                                    onclick='updateApostileData(<?= $doc["id"] ?>, "<?= base64_encode(json_encode($doc)) ?>")'>
                                                    Edit
                                                </a>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= !empty($doc["original_received"]) ? $doc["original_received"] : '' ?></td>
                                        <td><?= !empty($doc["apostille_cost"]) ? $doc["apostille_cost"] : '' ?></td>
                                        <td><?= !empty($doc["currency_text"]) ? $doc["currency_text"] : '' ?></td>
                                        <td><?= !empty($doc["apostille_status"]) ? $doc["apostille_status"] : '' ?></td>
                                        <td><?= !empty($doc["vendor_name"]) ? $doc["vendor_name"] : '' ?></td>
                                        <td><?= !empty($doc["by_vendor"]) ? 'Yes' : 'No' ?></td>
                                        <td><?= !empty($doc["courier_date"]) ? $doc["courier_date"] : '' ?></td>
                                        <td><?= !empty($doc["apostille_received"]) & $doc["apostille_received"] != "0000-00-00"  ? $doc["apostille_received"] : '' ?></td>
                                        <td><?= !empty($doc["payment_date"]) && $doc["payment_date"] != "0000-00-00" ? $doc["payment_date"] : '' ?></td>
                                        <td><?= !empty($doc["created_by"]) ? $doc["created_by"] : '' ?></td>
                                        <td><?= !empty($doc["created_at"]) ? $doc["created_at"] : '' ?></td>

                                    </tr>
                                <?php $index++;
                                endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <h5>No Apostille Documents Available</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <br>
                <br>
                <br>
                <div class="col-md-12 text-right">

                    <div class="pull-right">
                        <!-- <button type="button" class="btn btn-primary" onclick="check_update()">Update</button> -->
                        <?php if (is_postsale() || is_admin()) { ?>
                            <a href="#" data-toggle="modal" data-target="#customers_apostille" onclick="updateApostileData()" class="bulk-actions-btn table-btn btn btn-primary ">processed</a>
                        <?php } ?>
                    </div>
                    <!-- <div class="col-md-3 pull-right">
                        <?php
                        $selected_value = [1];
                        if (!empty($client_infomation->orignal_document_status)) {
                            $selected_value = [];
                            $selected_value[] = $client_infomation->orignal_document_status;
                        }
                        echo render_select('status', $orignal_document_status, array('id', 'name'), "", $selected_value);

                        ?>
                    </div> -->
                </div>
            </form>
            <br>
            <br>
            <br>
            <div class="activity-feed">
                <?php foreach ($activity_apostille_document as $log) { ?>
                    <div class="feed-item">
                        <div class="date">
                            <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                                <?php echo time_ago($log['date']); ?>
                            </span>
                        </div>
                        <div class="text">
                            <?php if ($log['staffid'] != 0) { ?>
                                <a href="<?php echo admin_url('profile/' . $log["staffid"]); ?>">
                                    <?php echo staff_profile_image($log['staffid'], array('staff-profile-xs-image pull-left mright5'));
                                    ?>
                                </a>
                            <?php
                            }

                            echo  get_staff_user_name_by_id($log['staffid']) . ' - ' . $log['description'];

                            ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade customers_apostille" id="customers_apostille" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Apostille update</h4>
            </div>
            <form id="apostille-document-form" class="form-disabled" onsubmit="return false;">

                <div class="modal-body h-auto">

                    <?php array_unshift($orignal_document_status, array()); ?>
                    <!-- Apostille Section -->
                    <div class="apostille_update">
                        <div class="apostille_status_update">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="hidden" name="apostile_id" id="apostile_id" value="">
                                    <label>Apostille Vendor <small class='text-danger'>*</small></label>
                                    <?php
                                    array_unshift($apostille_vendors, array());
                                    echo render_select('apostille_vendor', $apostille_vendors, ['id', 'name'], '', [], [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'Vendor',
                                        'data-actions-box' => true,
                                        'required-check' => 'required-check',
                                        'required' => 'required',
                                    ], [], 'no-mbot', '', false, 'apostille_vendor'); ?>
                                </div>
                                <div class="col-md-4">
                                    <label>Apostille Documents <small class='text-danger'>*</small></label>
                                    <?php echo render_select('apostille_document[]', $apostille_documents_new, ['id', 'name'], '', [], [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'Documents',
                                        'multiple' => true,
                                        'data-actions-box' => true,
                                        'required-check' => 'required-check',
                                        'required' => 'required',
                                        'onchange' => 'document_cost_div(this)'

                                    ], [], 'no-mbot', '', false, 'apostille_document'); ?>
                                </div>
                                <div class="col-md-4">
                                    <label>Documents By Vender</label>
                                    <?php echo render_select('apostille_document_vendor[]', $apostille_documents_new, ['id', 'name'], '', [], [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'Documents',
                                        'multiple' => true,
                                        'data-actions-box' => true,

                                    ], [], 'no-mbot', '', false, 'apostille_document_vendor'); ?>
                                </div>
                                <div class="col-md-4">
                                    <label>Courier Date</label>
                                    <?php echo render_input('apostille_date', '', '', 'date'); ?>
                                </div>
                                <div class="col-md-4">
                                    <label>Apostille Received</label>
                                    <?php echo render_input('apostille_receiving_date', '', '', 'date'); ?>
                                </div>

                                <div class="col-md-4">
                                    <label>Payment Date</label>
                                    <?php echo render_input('apostille_payment_date', '', '', 'date'); ?>
                                </div>
                                <div class="clearfix"></div>
                                <div class="doc-cost-section">


                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <a href="#" class="btn btn-info" onclick="customers_apostille(this); return false;"><?php echo _l('confirm'); ?></a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    <?php
    $currencyHtml = '<div class="input-group-addon currency-addon">
    <select name="currency_type[]" id="currency_type" class="currency-selector currency-selector-currency_type" onchange="updateSymbol(this.value)">
';

    foreach ($get_currencies as $c) {
        $selected = (!empty($fees['currency_id']) && $fees['currency_id'] == $c['id']) ||
            (empty($fees['currency_id']) && !empty($c['default_currency']) && $c['default_currency'] == $c['id'])
            ? 'selected' : '';

        $currencyHtml .= '<option data-symbol="' . $c['symbol'] . '" value="' . $c['id'] . '" data-placeholder="0.00" ' . $selected . '>' . $c['name'] . '</option>';
    }

    $currencyHtml .= '</select></div>';
    ?>

    let currencyHtml = `<?= $currencyHtml ?>`;

    var apostille_documents_list = <?= !empty($apostille_documents_new) ? json_encode(array_column($apostille_documents_new, null, 'id'), JSON_UNESCAPED_UNICODE) : '[]' ?>;
    var complete_application = " <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 1 : 0 ?>";

    if (complete_application == 1) {

    }

    function updateApostileData(id = "", encodedDoc = "") {
        // Reset the form
        $("#apostille-document-form")[0].reset();
        $(".doc-cost-section").html('');

        // Reset and refresh all selectpickers inside the form
        $('#apostille-document-form .selectpicker').val('').prop("disabled", false).selectpicker('refresh');

        setTimeout(() => {
            $("#apostile_id").val(id);
            const apostileData = JSON.parse(atob(encodedDoc));

            let {
                doc_id,
                vendor_id,
                by_vendor,
                courier_date,
                apostille_received,
                payment_date,
                apostille_cost,
                currency_type
            } = apostileData;

            // Vendor dropdown
            if ($("#apostille_vendor").length) {
                $("#apostille_vendor")
                    .val(vendor_id)
                    .selectpicker("refresh");
            }

            // Document dropdown
            if ($("#apostille_document").length) {
                $("#apostille_document")
                    .val(doc_id)
                    .prop("disabled", true) // disable select
                    .selectpicker("refresh").trigger("change");
            }

            // Vendor-specific document dropdown
            if (by_vendor == 1 && $("#apostille_document_vendor").length) {
                $("#apostille_document_vendor")
                    .val(doc_id)
                    .prop("disabled", true)
                    .selectpicker("refresh");
            }

            // Dates
            $("#apostille_date").val(courier_date || "");
            $("#apostille_receiving_date").val(apostille_received || "");
            $("#apostille_payment_date").val(payment_date || "");

            setTimeout(() => {
                // Cost input
                let costInput = $(`input[name='document_cost[${doc_id}]']`);
                if (costInput.length) {
                    costInput.val(apostille_cost || "");
                }

                // Currency type input (assuming it's a separate input/select aligned with cost)
                let currencyInput = $(`select[name='currency_type[${doc_id}]'], input[name='currency_type[${doc_id}]']`);
                if (currencyInput.length) {
                    currencyInput.val(currency_type || "").selectpicker?.("refresh");
                }
            }, 200);




        }, 100);
    }

    function document_cost_div(obj) {
        let selected_documents = $(obj).val() || [];

        // Clear all existing doc cost sections
        $(".doc-cost-section").empty();

        // Re-add only the selected ones
        selected_documents.forEach(function(doc_id) {
            let doc = apostille_documents_list[doc_id];

            $(".doc-cost-section").append(`
            <div class='col-md-4' id='cost-doc-div-${doc_id}'>
                <label>${doc.name} Cost</label>
                                                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">

                    <input class='form-control' type='number' data-name='${doc.name} Cost' placeholder='100' name='document_cost[${doc.id}]'>
                    ${currencyHtml}
                </div>
            </div>
        `);
        });
    }

    function customers_apostille(event) {
        var apostille_status = true;
        var apostille_data = {};
        var is_valid = true;
        var currency_id_apostile = $(".currency-selector-currency_type").first().val();
        // Get text of the selected option
        var currency_text_apostile = $(".currency-selector-currency_type option:selected").first().text();

        $('.apostille_status_update').find('input, select').each(function() {
            var name = $(this).attr("name");
            var show_name = $(this).data("name") || $(this).attr("name");
            var value = $(this).val();
            var required = $(this).attr('required') || $(this).attr('requried');
            if (name) {
                apostille_data[name] = value;
            }
            // console.log(value);
            // console.log(required);
            if (required && !String(value).trim()) {
                $(this).focus();
                alert_float("warning", "Please fill the required field: " + show_name);
                is_valid = false;
                return false; // Exit loop early
            }
        });
        apostille_data["manual_status"] = 1;
        apostille_data["apostile_id"] = $("#apostile_id").val();


        let ApostileDocuments = $("#apostille_document").val() || [];
        let ApostileDocumentVendor = $("#apostille_document_vendor").val() || [];

        // Ensure both are arrays
        ApostileDocuments = Array.isArray(ApostileDocuments) ? ApostileDocuments.map(String) : [String(ApostileDocuments)];
        ApostileDocumentVendor = Array.isArray(ApostileDocumentVendor) ? ApostileDocumentVendor.map(String) : [String(ApostileDocumentVendor)];

        // Find vendor docs not in selected docs
        let notFound = ApostileDocumentVendor.filter(id => !ApostileDocuments.includes(id));

        if (notFound.length > 0) {
            let docName = apostille_documents_list[notFound[0]]['name'] || `ID ${notFound[0]}`;
            alert_float("warning", `Please select the Apostille document: ${docName} before choosing a vendor documents.`);
            is_valid = false;
            return false; // Exit loop early
        }




        if (!is_valid) return false;

        if (!confirm(app.lang.confirm_action_prompt)) return false;

        var ids = [<?= $client_id ?>];


        var data = {
            ids,
            apostille_status,
            ...apostille_data,
            currency_id_apostile,
            currency_text_apostile
        };

        $(event.target).prop('disabled', true);

        setTimeout(() => {
            $.post(admin_url + 'clients/bulk_action', data)
                .done(function(response) {
                    try {
                        var res = JSON.parse(response);
                        if (res.resp_code === "RCS") {
                            alert_float("success", res.resp_desc);
                            $("#customers_apostille").modal('hide');
                            location.reload();

                        } else {
                            alert_float("danger", res.resp_desc);
                        }
                    } catch (e) {
                        alert_float("danger", "Unexpected error occurred.");
                    }
                })
                .fail(() => alert_float("danger", "Failed to process the request."))
                .always(() => $(event.target).prop('disabled', false));
        }, 50);
    }
</script>