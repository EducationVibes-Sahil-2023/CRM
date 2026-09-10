<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$orignal_document  = get_orignal_document_data($client_id);

$orignal_document_status  = orignal_document_status();
$office_location  = $this->staff_model->office_location("", 1);
$activity_orignal_document = activity_orignal_document($client_id);
$return_document_status = 0;
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
    <!--<h2 class="text-center">Orignal Document - Accessible Only for Post-Sale & Admin</h2>-->
    
      <table class="table table-bordered table-striped">
                        <thead class="thead-dark ">
                            <tr class="">
                                <th scope="col">S.No</th>
                                <th scope="col">Document Name</th>
                                <th scope="col">Received By</th>
                                <th scope="col">Received Date</th>
                                <th scope="col">Received Location</th>
                                <th scope="col">Transit Location</th>
                                <!--<th scope="col">Location</th>-->
                            </tr>
                        </thead>
                        <tbody class="document_upload_div">

                            <?php if (!empty($orignal_document)) : ?>
                                <?php
                                $index = 1;
                                $document_received = 0;
                                foreach ($orignal_document as $key => $doc) :
                                    if ($document_received == 0) {
                                        $document_received = !empty($doc['received_id']) ? 1 : 0;
                                    }

                                    if ($doc["l_status"] == 2) {
                                        $return_document_status = 1;
                                    }

                                ?>
                                    <tr>
                                        <td class="d-flex align-items-center">
                                            <div class="checkbox">
                                                <input type="hidden" name="received_id" value="<?= !empty($doc['received_id']) ? $doc['received_id'] : '' ?>">
                                                <input type="checkbox" name="doc_ids" <?= !empty($doc["disabled"]) && $doc["disabled"] == 1 ? 'disabled' : '' ?> data-name="<?= $doc["name"] ?>" value="<?= $doc["id"] ?>"><label> </label>


                                            </div>
                                        </td>
                                        <td>
                                            <?= $doc["name"] ?> <?= !empty($doc["info"]) ? '<i class="fa fa-info-circle" title="' . $doc["info"] . '"></i>' : '' ?></td>
                                        <td><?= !empty($doc["received_by"]) ? $doc["received_by"] : '' ?></td>
                                        <td><?= !empty($doc["received_date"]) ? $doc["received_date"] : '' ?></td>
                                        <td><?= !empty($doc["received_location"]) ? $doc["received_location"] : '' ?></td>
                                        <td><?= !empty($doc["in_transit"]) ? $doc["in_transit"] : '' ?></td>
                                        
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
<?php
    die;
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Orignal Documents</h4>
            <div class="text-right">

                <?php if ($client->client_type == 1) {
                    echo "Document Received Notification";

                    echo getLastEmailWhatsappDate("email", ORIGNAL_DOCUMENT_RECEIVED, $client_id);
                ?>
                    <button type="button" class="btn btn-primary btn-xs" onclick="orignal_document_received_notification(<?= $client_id ?>)"><i class="fa fa-envelope"></i> </button>
                <?php }

                echo "<br> <div class='mt-5 margin-top return-documents'>";
                if ($client->client_type == 1) {
                    echo "Document Return Notification";
                    echo  getLastEmailWhatsappDate("email", ORIGNAL_DOCUMENT_RETURN, $client_id);
                ?>
                    <button type="button" class="btn btn-primary btn-xs" onclick="orignal_document_received_notification(<?= $client_id ?>,1)"><i class="fa fa-envelope"></i> </button>
                <?php }
                echo "</div>";
                ?>

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
                                $document_received = 0;
                                foreach ($orignal_document as $key => $doc) :
                                    if ($document_received == 0) {
                                        $document_received = !empty($doc['received_id']) ? 1 : 0;
                                    }

                                    if ($doc["l_status"] == 2) {
                                        $return_document_status = 1;
                                    }

                                ?>
                                    <tr>
                                        <td class="d-flex align-items-center">
                                            <div class="checkbox">
                                                <input type="hidden" name="received_id" value="<?= !empty($doc['received_id']) ? $doc['received_id'] : '' ?>">
                                                <input type="checkbox" name="doc_ids" <?= !empty($doc["disabled"]) && $doc["disabled"] == 1 ? 'disabled' : '' ?> data-name="<?= $doc["name"] ?>" value="<?= $doc["id"] ?>"><label> </label>


                                            </div>
                                        </td>
                                        <td>
                                            
                                            <?= $doc["name"] ?> <?= !empty($doc["info"]) ? '<i class="fa fa-info-circle" title="' . $doc["info"] . '"></i>' : '' ?>
                                            <div class="row-options">
              <?php
if ((has_permission('customers', '', 'orignal_document_delete') || is_admin()) && !empty($doc['received_date'])) {
  echo   ' <a href="javascript:void(0)" onclick="delete_org_doc(' . (int)$doc['id'] . ')" class="text-danger">' . _l('delete') . '</a>';
}
?>
</div>
                                            </td>
                                        <td><?= !empty($doc["received_by"]) ? $doc["received_by"] : '' ?></td>
                                        <td><?= !empty($doc["received_date"]) ? $doc["received_date"] : '' ?></td>
                                        <td><?= !empty($doc["received_location"]) ? $doc["received_location"] : '' ?></td>
                                        <td><?= !empty($doc["in_transit"]) ? $doc["in_transit"] : '' ?></td>
                                        <td><?= render_select(
                                                'location',
                                                $office_location,
                                                array('id', 'name'),
                                                '',
                                                [],
                                                [
                                                    'data-width' => '100%',
                                                    'data-none-selected-text' => 'Select Office Location',
                                                    !empty($doc["disabled"]) && $doc["disabled"] == 1 ? 'disabled' : '' => !empty($doc["disabled"]) && $doc["disabled"] == 1 ? true : false
                                                ],
                                                [],
                                                'no-mbot',
                                                '',
                                                false,
                                                "office_location"
                                            ); ?>
                                        </td>
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
                <div class="row col-md-12">
                    <div class="col-md-8 ">
                        <textarea rows="3" cols="50" id="description" name="description" class="form-control" placeholder="Add a note about the document update (optional)"><?= !empty($client->orignal_doc_remark) ? $client->orignal_doc_remark : '' ?></textarea>
                    </div>
                    <div class="col-md-3 ">
                        <?php
                        $selected_value = [1];
                        if (!empty($client_infomation->orignal_document_status)) {
                            $selected_value = [];
                            $selected_value[] = $client_infomation->orignal_document_status;
                        }
                        echo render_select('status', $orignal_document_status, array('id', 'name'), "", $selected_value);

                        ?>
                    </div>
                    <div class="col-md-1 text-right ">
                        <button type=" button" class="btn btn-primary" onclick="check_update(this)">Update</button>
                    </div>


                </div>
            </form>
            <br>
            <br>
            <br>
            <div class="activity-feed margin-top">
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
    var complete_application = " <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 1 : 0 ?>";
    var document_received = "<?= $document_received ?>";
    var return_document_status = "<?= $return_document_status ?>";
    if (return_document_status == 1) {
        $(".return-documents").show();
    } else {
        $(".return-documents").hide();
    }

    if (document_received == 1) {
        $(".email-hide").removeClass("hide");
    }
    if (complete_application == 1) {
        $("form").find("input, select, textarea,button").prop("disabled", true).selectpicker("refresh");

    }
    <?php if ($client_infomation->orignal_document_status == 4 && !has_permission('customers', '', 'return_document')): ?>

        $("select[name='status']").attr("disabled", true).selectpicker("refresh");


    <?php elseif (!has_permission('customers', '', 'return_document')): ?>

        $("select[name='status'] option[value='4']").attr("disabled", true);
        $("select[name='status']").selectpicker("refresh");


    <?php endif; ?>




    function check_update(obj) {
        $(obj).addClass("disabled");
        let isValid = true;
        let isValid_check = false;
        let formData = new FormData(); // Create a FormData object
        let status = $("select[name='status']").val();
        let status_text = $("select[name='status'] option:selected").text();
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
                    $(obj).removeClass("disabled");
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
        var description = $("#description").val();
        if (description) {
            formData.append("description", description); // Append description if provided
        } else {
            formData.append("description", ""); // Append empty string if no description
        }
        formData.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>"); // Append corresponding location

        if (!isValid) {
            $(obj).removeClass("disabled");
            return false; // Stop form submission
        }

        if ($(".document_upload_div tr input[type='checkbox']:checked").length === 0 && isValid_check == false) {
            alert_float("danger", "Please check at least one checkbox before saving!");
            $(obj).removeClass("disabled");

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
                    $(obj).removeClass("disabled");
                }
            },
            error: function() {
                alert_float("danger", "Error updating documents.");
            },
        });

        return false; // Prevent default form submission
    }

 async function delete_org_doc(orignal_doc_id) {

    if (!orignal_doc_id) {
        return false;
    }

const confirmed = await showConfirmation(
    "Deleting this Original document will permanently remove the document and its related information.\n\nAre you sure you want to continue?"
);

if (!confirmed) {
    hide_loader(); // if loader is already shown
    return false;
}
    let formData = new FormData();

    formData.append("orignal_doc_id", orignal_doc_id);
    formData.append("client_id", <?= $client_id ?>);

    formData.append(
        "<?= $this->security->get_csrf_token_name(); ?>",
        "<?= $this->security->get_csrf_hash(); ?>"
    );

    show_loader();

    $.ajax({
        url: "<?= base_url('admin/clients/delete_org_doc') ?>",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,

        success: function(response) {

            hide_loader();

            try {
                response = JSON.parse(response);
            } catch (e) {
                alert_float("danger", "Invalid server response.");
                return;
            }

            if (response.resp_code == "RCS") {

                alert_float("success", response.resp_desc);

                // Reload after successful deletion
                location.reload();

            } else {

                alert_float("danger", response.resp_desc);
            }
        },

        error: function(xhr, status, error) {

            hide_loader();

            alert_float(
                "danger",
                "Error deleting document."
            );
        }
    });
}


   function orignal_document_received_notification(client_id, status = "") {
        let formData = new FormData(); // Create a FormData object
        formData.append("client_id", <?= $client_id ?>); // Append corresponding location
        formData.append("status", status); // Append corresponding location
        formData.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>"); // Append corresponding location
        show_loader();
        $.ajax({
            url: "<?= base_url('admin/clients/orignal_document_received_notification') ?>", // Replace with your actual AJAX URL
            type: "POST",
            data: formData,
            contentType: false, // Important for FormData
            processData: false, // Prevents jQuery from converting FormData to a query string
            success: function(response) {
                response = JSON.parse(response);

                console.log(response.resp_code);
                if (response.resp_code == "RCS") {
                    hide_loader();
                    alert_float("success", response.resp_desc);
                    location.reload(); // Reload page after success
                } else {
                    hide_loader();
                    alert_float("danger", response.resp_desc);
                }
            },
            error: function() {
                hide_loader();

                alert_float("danger", "Error updating documents.");
            },
        });
    }
</script>