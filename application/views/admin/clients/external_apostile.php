<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * External Apostille Documents — corrected view (v2)
 *
 * FIXES vs your version:
 *
 * 1. FATAL BUG: PHP inside HTML comments still executes. Your commented-out
 *    whatsapp block read $client_infomation->orignal_document_status and the
 *    bottom block did the same — $client_infomation is never defined on this
 *    external page, so PHP 8 throws "Attempt to read property on null".
 *    Wrapping code in <!-- --> does NOT stop PHP. Dead blocks removed.
 *
 * 2. $client_id was undefined but used in JS:  var ids = [<?= $client_id ?>];
 *    -> PHP warning + fragile JS. Now defaulted to 0 and cast to int.
 *
 * 3. Access check ran AFTER init_head() and AFTER all DB queries, and used
 *    die; (page ends without init_tail). Moved to the top, before any
 *    data loading, with a clean early return.
 *
 * 4. array_unshift($apostille_vendors, array()) ran TWICE (top of file and
 *    again inside the modal) -> two blank options in the dropdown. Now once.
 *
 * 5. updateApostileData() is called with NO arguments by the "New Create"
 *    button -> JSON.parse(atob("")) threw a SyntaxError on every open.
 *    Now guarded: empty encodedDoc = clean "create" mode, no error.
 *
 * 6. Default-currency logic compared $c['default_currency'] == $c['id']
 *    (flag vs id — almost never true) and referenced $fees which doesn't
 *    exist on this page. Now simply selects the default currency.
 *
 * 7. document_cost_div() and the vendor-doc mismatch alert crashed if a
 *    doc id wasn't in apostille_documents_list (doc.name of undefined).
 *    Both guarded.
 *
 * 8. currencyHtml gave every cloned <select> the same id="currency_type"
 *    (duplicate IDs across rows). Id removed — the class selector is used
 *    everywhere anyway.
 *
 * 9. complete_application was the string " 0"/" 1" compared with == 1,
 *    plus an empty if-block. Now an int; empty block removed.
 */

init_head();

// ---- 3) Access check FIRST: before any queries, with a proper page close ----
if (!is_postsale() && !is_admin()) { ?>
    <div id="wrapper">
        <div class="content">
            <h2 class="text-center">External Apostille Document - Accessible Only for Post-Sale &amp; Admin</h2>
        </div>
    </div>
    <?php init_tail(); ?>
</body></html>
<?php
    return; // stop rendering; nothing below runs for unauthorized staff
}

// ---- 2) This is the external (non client-specific) page ----
$client_id = isset($client_id) ? (int)$client_id : 0;

$orignal_document_status = orignal_document_status();
$office_location         = $this->staff_model->office_location();
$activity_apostille_document = activity_apostille_document_external('');
$apostille_documents = get_orignal_document_list(0, 0, 1);
$apostille_visa_apostile_documents = get_orignal_document_list(0, 0, 0, 0, 0, 0, 1, ["status" => 0]);
$apostille_vendors = get_vendor_list(1);
$get_currencies = get_currencies();
$get_currencies = array_column($get_currencies, null, 'id');
$payment_mode = get_payment_mode();

if (!is_array($apostille_documents)) {
    $apostille_documents = [];
}
if (!is_array($apostille_visa_apostile_documents)) {
    $apostille_visa_apostile_documents = [];
}
$apostille_documents_new = array_merge($apostille_documents, $apostille_visa_apostile_documents);

// ---- 4) unshift blank option ONCE per list ----
array_unshift($apostille_vendors, array());
array_unshift($office_location, array());
array_unshift($orignal_document_status, array());
array_unshift($payment_mode, array());
?>
<style>
   
    .table-responsive {
        width: 100%;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="form-container">
                    <h4 class="fs-title">External Apostille Documents  <?php if (is_postsale() || is_admin()) { ?>
                            <a href="#" data-toggle="modal" data-target="#customers_apostille" onclick="updateApostileData()" class="bulk-actions-btn  pull-right table-btn btn btn-primary">New Create</a>
                        <?php } ?></h4>

                 
                    <hr>

                    <form method="post" id="orignal-document-form">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div id="orignal_documents" class="table-responsive">
                            <table class="table table-bordered dataTable no-footer table-striped table-external-apostille">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Document Name</th>
                                        <th scope="col">Cost</th>
                                        <th scope="col">Currency</th>
                                        <th scope="col">Exchange Rate</th>
                                        <th scope="col">Total Amount</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Vendor</th>
                                        <th scope="col">Apply By Vendor</th>
                                        <th scope="col">Courier Date</th>
                                        <th scope="col">Receiving Date</th>
                                        <th scope="col">Payment Mode</th>
                                        <th scope="col">Payment Date</th>
                                        <th scope="col">Created By</th>
                                        <th scope="col">Created Date</th>
                                    </tr>
                                </thead>
                                <tbody class="document_upload_div">
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <br><br>

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
                                            <?php echo staff_profile_image($log['staffid'], array('staff-profile-xs-image pull-left mright5')); ?>
                                        </a>
                                    <?php }
                                    echo get_staff_user_name_by_id($log['staffid']) . ' - ' . $log['description'];
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
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
                    <div class="apostille_update">
                        <div class="apostille_status_update">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Name <small class='text-danger'>*</small></label>
                                    <input type="text" name="name" required-check  required class="form-control" id="client_name" value="" placeholder="Client Name">
                                    </div>
                                <div class="col-md-4">
                                    <input type="hidden" name="apostile_id" id="apostile_id" value="">
                                    <label>Apostille Vendor <small class='text-danger'>*</small></label>
                                    <?php echo render_select('apostille_vendor', $apostille_vendors, ['id', 'name'], '', [], [
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
                                    <label>Documents By Vendor</label>
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
                                <div class="col-md-4">
                                    <label>Payment Mode</label>
                                    <?php echo render_select('apostile_payment_mode', $payment_mode, ['id', 'name'], '', [], [
                                        'data-width' => '100%',
                                        'data-none-selected-text' => 'Payment Mode',
                                        'data-actions-box' => true,
                                    ], [], 'no-mbot', '', false, 'payment_mode'); ?>
                                </div>
                                <div class="col-md-4">
                                    <label>Exchange Rate</label>
                                    <?php echo render_input('apostile_exchange_rate', '', '', 'number'); ?>
                                </div>
                                <div class="clearfix"></div>
                                <div class="doc-cost-section"></div>
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
    // ---- 6) & 8) currency selector: no duplicate id, sane default-currency logic ----
    $currencyHtml = '<div class="input-group-addon currency-addon">
    <select name="currency_type[]" class="currency-selector currency-selector-currency_type" onchange="updateSymbol(this.value)">';
    foreach ($get_currencies as $c) {
        $selected = !empty($c['isdefault']) || !empty($c['default_currency']) ? 'selected' : '';
        $currencyHtml .= '<option data-symbol="' . html_escape($c['symbol']) . '" value="' . (int)$c['id'] . '" data-placeholder="0.00" ' . $selected . '>' . html_escape($c['name']) . '</option>';
    }
    $currencyHtml .= '</select></div>';
    ?>
    let currencyHtml = `<?= $currencyHtml ?>`;
    var apostille_documents_list = <?= !empty($apostille_documents_new) ? json_encode(array_column($apostille_documents_new, null, 'id'), JSON_UNESCAPED_UNICODE) : '{}' ?>;

    function updateApostileData(id = "", encodedDoc = "") {
        // Reset the form
        $("#apostille-document-form")[0].reset();
        $(".doc-cost-section").html('');
        $('#apostille-document-form .selectpicker').val('').prop("disabled", false).selectpicker('refresh');
        $("#apostile_id").val(id);

        // ---- 5) "New Create" passes no data: stop here with a clean form ----
        if (!encodedDoc) {
            return;
        }

        setTimeout(() => {
            let apostileData;
            try {
                apostileData = JSON.parse(atob(encodedDoc));
            } catch (e) {
                alert_float("danger", "Could not read the apostille record data.");
                return;
            }
            let {
                name,
                doc_id,
                vendor_id,
                by_vendor,
                courier_date,
                apostille_received,
                payment_date,
                apostille_cost,
                currency_type,
                exchange_rate,
                payment_mode_id
            } = apostileData;

            if ($("#apostille_vendor").length) {
                $("#apostille_vendor").val(vendor_id).selectpicker("refresh");
            }
            if ($("#apostille_document").length) {
                $("#apostille_document")
                    .val(doc_id)
                    .prop("disabled", true)
                    .selectpicker("refresh").trigger("change");
            }
            if (by_vendor == 1 && $("#apostille_document_vendor").length) {
                $("#apostille_document_vendor")
                    .val(doc_id)
                    .prop("disabled", true)
                    .selectpicker("refresh");
            }
            console.log(name);
            console.log(apostileData);
             $("#client_name").val(name || "");
            $("#apostille_date").val(courier_date || "");
            $("#apostille_receiving_date").val(apostille_received || "");
            $("#apostille_payment_date").val(payment_date || "");
            if ($("#payment_mode").length) {
                $("#payment_mode").val(payment_mode_id).selectpicker("refresh").trigger("change");
            }
            $("#apostile_exchange_rate").val(exchange_rate || "");

            setTimeout(() => {
                let costInput = $(`input[name='document_cost[${doc_id}]']`);
                if (costInput.length) {
                    costInput.val(apostille_cost || "");
                }
                let currencyInput = $(`#cost-doc-div-${doc_id} .currency-selector-currency_type`);
                if (currencyInput.length) {
                    currencyInput.val(currency_type || "");
                }
            }, 200);
        }, 100);
    }

    function document_cost_div(obj) {
        let selected_documents = $(obj).val() || [];
        $(".doc-cost-section").empty();
        selected_documents.forEach(function(doc_id) {
            let doc = apostille_documents_list[doc_id];
            if (!doc) return; // ---- 7) unknown id: skip instead of crashing ----
            $(".doc-cost-section").append(`
            <div class='col-md-4' id='cost-doc-div-${doc_id}'>
                <label>${doc.name} Cost</label>
                <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                    <input class='form-control' type='number' data-name='${doc.name} Cost' placeholder='100' name='document_cost[${doc.id}]'>
                    ${currencyHtml}
                </div>
            </div>`);
        });
    }

    function customers_apostille(event) {
        var apostille_status = true;
        var apostille_data = {};
        var is_valid = true;
        var currency_id_apostile = $(".currency-selector-currency_type").first().val();
        var currency_text_apostile = $(".currency-selector-currency_type option:selected").first().text();

        $('.apostille_status_update').find('input, select').each(function() {
            var name = $(this).attr("name");
            var show_name = $(this).data("name") || $(this).attr("name");
            var value = $(this).val();
            var required = $(this).attr('required') || $(this).attr('requried');
            if (name) {
                apostille_data[name] = value;
            }
            if (required && !String(value ?? '').trim()) {
                $(this).focus();
                alert_float("warning", "Please fill the required field: " + show_name);
                is_valid = false;
                return false;
            }
        });
        if (!is_valid) return false;

        apostille_data["manual_status"] = 1;
        apostille_data["apostile_id"] = $("#apostile_id").val();

        let ApostileDocuments = $("#apostille_document").val() || [];
        let ApostileDocumentVendor = $("#apostille_document_vendor").val() || [];
        ApostileDocuments = Array.isArray(ApostileDocuments) ? ApostileDocuments.map(String) : [String(ApostileDocuments)];
        ApostileDocumentVendor = Array.isArray(ApostileDocumentVendor) ? ApostileDocumentVendor.map(String) : [String(ApostileDocumentVendor)];

        let notFound = ApostileDocumentVendor.filter(id => !ApostileDocuments.includes(id));
        if (notFound.length > 0) {
            // ---- 7) safe lookup even when the id isn't in the list ----
            let docName = (apostille_documents_list[notFound[0]] || {}).name || `ID ${notFound[0]}`;
            alert_float("warning", `Please select the Apostille document: ${docName} before choosing a vendor documents.`);
            return false;
        }

        if (!confirm(app.lang.confirm_action_prompt)) return false;

        // ---- 2) external page: 0 when no specific client context ----
        var ids = [<?= (int)$client_id ?>];
        var data = {
            ids,
            apostille_status,
            ...apostille_data,
            currency_id_apostile,
            currency_text_apostile
        };

        $(event.target).prop('disabled', true);
        $.post(admin_url + 'clients/external_client', data)
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
    }
    
    var ExternalApostilleParams = {
        "vendor_id":       "[name='filter_vendor_id']",
        "received_status": "[name='filter_received_status']",
        "payment_mode":    "[name='filter_payment_mode']",
        "from_date":       "[name='filter_from_date']",
        "to_date":         "[name='filter_to_date']"
    };

    var apostille_table;
   function set_table() {
        console.log("set_table update");
        apostille_table = initDataTable(
            '.table-external-apostille',
            admin_url + 'clients/external_apostille_docs',
            [],                       // every column searchable
            [],                       // every column sortable
            ExternalApostilleParams,  // filters posted on each draw
            [0, 'ASC']                // default sort: Name
        );

        // Re-query the server whenever a filter changes
        $('[name="filter_vendor_id"], [name="filter_received_status"], [name="filter_payment_mode"], [name="filter_from_date"], [name="filter_to_date"]')
            .on('change', function () {
                apostille_table.DataTable().ajax.reload();
            });
   }
   
  async function deleteApostileRecord(id, name) {
    if (!id) {
        alert_float('warning', 'Invalid record ID');
        return;
    }
const confirmed = await showConfirmation(
    'You are about to delete apostile record for: ' + name + '\n\n.This action cannot be undone!'
);

  if (!confirmed) {
            hide_loader(); // if loader is already shown
            return false;
            }
            
             $.ajax({
                url: admin_url + 'clients/delete_apostile_record', // Update controller name
                type: 'POST',
                data: {
                    id: id,
                    csrf_token_name: $('input[name="csrf_token_name"]').val() // If using CSRF
                },
                dataType: 'json',
                success: function(response) {
                    if (response.resp_code === 'RCS' || response.status === 'success') {
                      location.reload();
                    } else {
                        
                    }
                },
                error: function(xhr, status, error) {
                   
                    console.error('Delete error:', xhr.responseText);
                }
            });
    // Show confirmation dialog
    // Swal.fire({
    //     title: 'Are you sure?',
    //     html: 'You are about to delete apostile record for: <strong>' + name + '</strong><br><br>This action cannot be undone!',
    //     icon: 'warning',
    //     showCancelButton: true,
    //     confirmButtonColor: '#d33',
    //     cancelButtonColor: '#3085d6',
    //     confirmButtonText: 'Yes, delete it!',
    //     cancelButtonText: 'Cancel'
    // }).then((result) => {
    //     if (result.isConfirmed) {
    //         // Show loading
    //         Swal.fire({
    //             title: 'Deleting...',
    //             text: 'Please wait',
    //             allowOutsideClick: false,
    //             didOpen: () => {
    //                 Swal.showLoading();
    //             }
    //         });

    //         // Send AJAX request
    //         $.ajax({
    //             url: admin_url + 'your_controller/delete_apostile_record', // Update controller name
    //             type: 'POST',
    //             data: {
    //                 id: id,
    //                 csrf_token_name: $('input[name="csrf_token_name"]').val() // If using CSRF
    //             },
    //             dataType: 'json',
    //             success: function(response) {
    //                 if (response.status === 'RCS' || response.status === 'success') {
    //                     Swal.fire({
    //                         icon: 'success',
    //                         title: 'Deleted!',
    //                         text: response.message || 'Record deleted successfully.',
    //                         timer: 2000,
    //                         showConfirmButton: false
    //                     }).then(() => {
    //                         // Reload datatable or page
    //                         if (typeof table !== 'undefined' && table) {
    //                             table.ajax.reload(null, false);
    //                         } else {
    //                             location.reload();
    //                         }
    //                     });
    //                 } else {
    //                     Swal.fire({
    //                         icon: 'error',
    //                         title: 'Error',
    //                         text: response.message || 'Failed to delete record.'
    //                     });
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 Swal.fire({
    //                     icon: 'error',
    //                     title: 'Error',
    //                     text: 'An error occurred while deleting: ' + error
    //                 });
    //                 console.error('Delete error:', xhr.responseText);
    //             }
    //         });
    //     }
    // });
}
   
   console.log("set_table");
   
   set_table();
</script>