<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$orignal_document  = get_orignal_document_data($client_id);
$orignal_document_status  = orignal_document_status();
$location  = orignal_document_status();
$office_location  = $this->staff_model->office_location();
array_unshift($office_location, array());
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Orignal Documents</h4>
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
                                            <div class="checkbox"><input type="checkbox" name="doc_ids[]" value="<?= $doc["id"] ?>"><label></label></div>
                                        </td>
                                        <td><?= $doc["name"] ?></td>
                                        <td></td>
                                        <td></td>
                                        <td><?= render_select('location[]', $office_location, array('id', 'name'), 'Office Location', [], array('data-width' => '100%', 'data-none-selected-text' => 'Select Office Location'), array(), 'no-mbot', '', false, "office_location");
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
                <div class="col-md-12 text-right">

                    <div class="pull-right">
                        <button type="button" class="btn btn-primary" onclick="check_update()">Update</button>
                    </div>
                    <div class="col-md-3 pull-right">
                        <?php
                        $selected_value = [1];
                        echo render_select('status', $orignal_document_status, array('id', 'name'), "", $selected_value);

                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    function check_update() {
        let isValid = true;

        $(".document_upload_div tr").each(function() {
            let checkbox = $(this).find("input[type='checkbox']");
            if (checkbox.is(":checked")) {
                let locationInput = $(this).find("select[name='location[]']");
                console.log(locationInput.val());
                if (locationInput.val() === "") {
                    isValid = false;
                    $(this).find("select[name='location']").focus();
                    alert_float("danger", "Please enter a location for the selected document.");
                    return false; // Exit loop early if validation fails
                }
            }
        });

        if (!isValid) {
            return false; // Stop form submission
        }

        if ($(".document_upload_div tr input[type='checkbox']:checked").length > 0) {
            $("#orignal-document-form").submit();
        } else {
            alert_float("danger", "Please check at least one checkbox before saving!");
            return false;
        }
    }
</script>