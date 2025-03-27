<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$orignal_document  = get_orignal_document_data($client_id);
$orignal_document_status  = orignal_document_status();
$office_location  = $this->staff_model->office_location();
$activity_orignal_document = activity_orignal_document($client_id);
// $client = $this->clients_model->get($id);

array_unshift($office_location, array());
?>
<style>
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        text-wrap: auto !important;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Orignal Documents</h4>
            <div class="text-right">
                <button type="button" class="btn btn-primary btn-xs " onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 6,'','')"><i class="fa fa-whatsapp"></i> </button>
            </div>
            <hr>

            <form method="post" id="orignal-document-form">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <div id="orignal_documents" class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark ">
                            <tr class="">
                                <th scope="col">S.No</th>
                                <th scope="col">Document Name</th>
                                <th scope="col">Received By</th>
                                <th scope="col">Received Date</th>
                                <th scope="col">Received Location</th>
                                <th scope="col">Transit Location</th>
                                <th scope="col">Location</th>
                            </tr>
                        </thead>
                        <tbody class="document_upload_div">

                            <?php if (!empty($orignal_document)) : ?>
                                <?php
                                $index = 1;
                                foreach ($orignal_document as $key => $doc) :
                                ?>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <input type="hidden" name="received_id" value="<?= !empty($doc['received_id']) ? $doc['received_id'] : '' ?>">
                                                <input type="checkbox" name="doc_ids" data-name="<?= $doc["name"] ?>" value="<?= $doc["id"] ?>"><label></label>
                                            </div>
                                        </td>
                                        <td><?= $doc["name"] ?></td>
                                        <td><?= !empty($doc["received_by"]) ? $doc["received_by"] : '' ?></td>
                                        <td><?= !empty($doc["received_date"]) ? $doc["received_date"] : '' ?></td>
                                        <td><?= !empty($doc["received_location"]) ? $doc["received_location"] : '' ?></td>
                                        <td><?= !empty($doc["in_transit"]) ? $doc["in_transit"] : '' ?></td>
                                        <td><?= render_select('location', $office_location, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Select Office Location'), array(), 'no-mbot', '', false, "office_location");
                                            ?></td>
                                    </tr>
                                <?php $index++;
                                endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <h5>No Documents Available</h5>
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
                        <button type="button" class="btn btn-primary" onclick="check_update()">Update</button>
                    </div>
                    <div class="col-md-3 pull-right">
                        <?php
                        $selected_value = [1];
                        if (!empty($client_infomation->orignal_document_status)) {
                            $selected_value = [];
                            $selected_value[] = $client_infomation->orignal_document_status;
                        }
                        echo render_select('status', $orignal_document_status, array('id', 'name'), "", $selected_value);

                        ?>
                    </div>
                </div>
            </form>
            <br>
            <br>
            <br>
            <div class="activity-feed">
                <?php foreach ($activity_orignal_document as $log) { ?>
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
<?php init_tail(); ?>
<script>
    function check_update() {
        let isValid = true;
        let isValid_check = false;
        let formData = new FormData(); // Create a FormData object
        let status = $("select[name='status']").val();
        let status_text = $("select[name='status']  option:selected").text();
        $(".document_upload_div tr").each(function() {
            let checkbox = $(this).find("input[type='checkbox']");
            let received_id = $(this).find("input[name='received_id']").val();
            if (received_id != "") {
                isValid_check = true;
            }
            if (checkbox.is(":checked")) {
                let locationInput = $(this).find("select[name='location']");

                if (!locationInput.val()) {
                    isValid = false;
                    locationInput.focus();
                    alert_float("danger", "Please enter a location for the selected document.");
                    return false; // Exit loop early if validation fails
                }

                // Append data to formData
                formData.append("received_id[]", received_id); // Append document ID
                formData.append("document_ids[]", checkbox.val()); // Append document ID
                formData.append("locations[]", locationInput.val()); // Append corresponding location
                formData.append("document_name[]", checkbox.data("name")); // Append corresponding location
                formData.append("locations_name[]", $(this).find("select[name='location'] option:selected").text()); // Append corresponding location
            }
        });
        formData.append("client_id", <?= $client_id ?>); // Append corresponding location
        formData.append("status", status); // Append corresponding location
        formData.append("status_text", status_text); // Append corresponding location
        formData.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>"); // Append corresponding location

        if (!isValid) {
            return false; // Stop form submission
        }

        if ($(".document_upload_div tr input[type='checkbox']:checked").length === 0 && isValid_check == false) {
            alert_float("danger", "Please check at least one checkbox before saving!");
            return false;
        }

        $.ajax({
            url: "<?= base_url('admin/clients/orignal_document') ?>", // Replace with your actual AJAX URL
            type: "POST",
            data: formData,
            contentType: false, // Important for FormData
            processData: false, // Prevents jQuery from converting FormData to a query string
            success: function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    alert_float("success", response.message);
                    location.reload(); // Reload page after success
                } else {
                    alert_float("danger", response.message);

                }
            },
            error: function() {
                alert_float("danger", "Error updating documents.");
            },
        });

        return false; // Prevent default form submission
    }
</script>