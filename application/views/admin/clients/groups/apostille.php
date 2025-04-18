<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$apostille_document  = get_apostille_document_data($client_id);
$orignal_document_status  = orignal_document_status();
$office_location  = $this->staff_model->office_location();
$activity_apostille_document = activity_apostille_document($client_id);
$apostille_documents = get_orignal_document_list(0, 0, 1);
$apostille_vendors = get_vendor_list(1);
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
                                <th scope="col">Status</th>
                                <th scope="col">Vendor</th>
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
                                foreach ($apostille_document as $key => $doc) :
                                ?>
                                    <tr>
                                        <!-- <td>
                                            <div class="checkbox">
                                                <input type="hidden" name="received_id" value="<?= !empty($doc['received_id']) ? $doc['received_id'] : '' ?>">
                                                <input type="checkbox" name="doc_ids" data-name="<?= $doc["name"] ?>" value="<?= $doc["id"] ?>"><label> </label>
                                            </div>
                                        </td> -->
                                        <td><?= $doc["name"] ?> <?= !empty($doc["info"]) ? '<i class="fa fa-info-circle" title="' . $doc["info"] . '"></i>' : '' ?></td>
                                        <td><?= !empty($doc["original_received"]) ? $doc["original_received"] : '' ?></td>
                                        <td><?= !empty($doc["apostille_cost"]) ? $doc["apostille_cost"] : '' ?></td>
                                        <td><?= !empty($doc["apostille_status"]) ? $doc["apostille_status"] : '' ?></td>
                                        <td><?= !empty($doc["vendor_name"]) ? $doc["vendor_name"] : '' ?></td>
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

                        <a href="#" data-toggle="modal" data-target="#customers_apostille" class="bulk-actions-btn table-btn btn btn-primary ">processed</a>
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
                                    <?php echo render_select('apostille_document[]', $apostille_documents, ['id', 'name'], '', [], [
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
    var apostille_documents_list = <?= !empty($apostille_documents) ? json_encode(array_column($apostille_documents, null, 'id'), JSON_UNESCAPED_UNICODE) : '[]' ?>;

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
                <div class='form-group'>
                    <input class='form-control' type='number' placeholder='100' name='document_cost[${doc.id}]'>
                </div>
            </div>
        `);
        });
    }

    function customers_apostille(event) {
        var apostille_status = true;
        var apostille_data = {};
        var is_valid = true;

        $('.apostille_status_update').find('input, select').each(function() {
            var name = $(this).attr('name');
            var required = $(this).attr('required') || $(this).attr('requried');
            var value = $(this).val();

            if (name) {
                apostille_data[name] = value;
            }

            if (required && !String(value).trim()) {
                $(this).focus();
                alert_float("warning", "Please fill the required field: " + name);
                is_valid = false;
                return false; // Exit loop early
            }
        });


        if (!is_valid) return false;

        if (!confirm(app.lang.confirm_action_prompt)) return false;

        var ids = [<?= $client_id ?>];

        var data = {
            ids,
            apostille_status,
            ...apostille_data
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