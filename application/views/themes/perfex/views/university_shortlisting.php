<div class="application_div">
    <div class="col-md-12">
        <h3>University Shortlisting</h3>
        <hr>
    </div>
    <form id="university_shortlisting_form" onsubmit="return false;">

        <?php if (!empty($university_shortlisting)) {
            $selected_vendor = !empty($profile_creation_data[0]["vendor"]) ? explode(",", $profile_creation_data[0]["vendor"]) : [];
            $select_dropdown_value = array_column($customer_vendors, "name", "id");
            foreach ($university_shortlisting as $key_u => $short_list) {
                $file_name = "";
                if (!empty($short_list["media_file"])) {
                    $file_name =  trim(explode("_", basename($short_list["media_file"]))[2]);
                }
        ?>
                <div class="col-md-12 university_div_application mt-2">
                    <div class="col-md-3">
                        <input type="hidden" name="university_id[]" value="<?= $short_list["id"] ?>">
                        <input type="input" name="university_name" class="form-control" disabled value="<?= $short_list["university_name"] ?>">
                    </div>
                    <div class="col-md-2">
                        <?php
                        // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");
                        $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : "";
                        // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), "", $selected_vendor);
                        // echo $select_dropdown_value[$selected_vendor];

                        ?>
                        <input type="input" name="vendor_id" class="form-control" disabled value="<?= $select_dropdown_value[$selected_vendor] ?>">
                    </div>

                    <div class="col-md-2  <?= ($short_list["university_status"] == 1) ? 'check_university' : '' ?>">
                        <?php

                        $selected_university_status_update = !empty($short_list["university_status"]) ? $short_list["university_status"] : "";
                        echo render_select('university_application_status[]', $university_status_update, array('id', 'name'), "", $selected_university_status_update); ?>
                    </div>


                    <div class="col-md-2">
                        <select name="university_status_submit_offer" disabled class=" university_status_submit_offer selectpicker" data-width="100%" data-none-selected-text="Non selected" data-live-search="true">
                            <option></option>
                            <?php

                            $offer_status = $university_status_submit;
                            unset($offer_status[0]);

                            foreach ($offer_status as $u_a_s) {
                                $selected_university_application = !empty($short_list["university_offer_status"]) ? $short_list["university_offer_status"] : "";
                                $select_s = ($selected_university_application == $u_a_s['id']) ? "Selected" : "";
                            ?>
                                <option value="<?= $u_a_s['id'] ?>" <?= $select_s ?> data-selected-file='<?= $u_a_s['file_upload_status'] ?>'><?= $u_a_s['name'] ?></option>
                            <?php
                            }
                            ?>
                        </select>

                    </div>
                    <div class="col-md-3">
                        <input type="file" disabled data-file-name="<?= $file_name ?>" class="form-control" id="offer_letter" accept="images/*,application/pdf" onchange="real_time_media_show_offer(this)" name="offer_letter">

                        <div class="row media-text-div-offer">
                            <div class="col-md-8">

                                <p class="document-file-name"><?= $file_name ?></p>
                            </div>
                            <div class="col-md-2">
                                <?php if (!empty($short_list["media_file"])) { ?>
                                    <a class="col-md-12 download_document" accept="image/*,application/pdf" download href="<?= base_url($docs["document_file"]) ?>" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                <?php }
                                ?>
                            </div>

                        </div>
                    </div>
                </div>

                <?php
                $condition_array = get_condition_offer($short_list["client_id"], $short_list["id"]);
                if (!empty($condition_array)) {
                ?>
                    <div class="col-lg-12 mt-2 mb-2 text-area-field">
                        <?php
                        foreach ($condition_array as $con) {

                            $file_name = "";
                            if (!empty($con["file"])) {
                                $file_name =  trim(explode("_", basename($con["file"]))[2]);
                            }
                        ?>
                            <div class=" text-area-field-div u_s_l_<?= $short_list['id'] ?>">
                                <input type="hidden" data-condition-id="<?= $con["id"] ?>">
                                <div class="col-md-1">Condition</div>
                                <div class="col-md-8"><textarea disabled placeholder="Write conditions ...... " class="conditional_textarea form-control" name="condition_text"><?= $con["condition_text"] ?></textarea></div>
                                <div class="col-md-3"><input type="file" disabled data-file-url<?= $con["file"] ?> class="form-control" onchange="real_time_media_show_offer_condition(this)" name="condition_file" accept="image/*,application/pdf">

                                    <div class="row media-text-div-offer-condition">
                                        <div class="col-md-8">
                                            <p class="document-file-name"><?= $file_name ?></p>
                                        </div>
                                        <div class="col-md-2">
                                            <?php if (!empty($con["file"])) { ?>
                                                <a class="col-md-12 download_document" accept="image/*,application/pdf" download href="<?= base_url($con["file"]) ?>" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                            <?php }
                                            ?>
                                        </div>
                                    </div>

                                </div>
                                <!-- <div class="col-md-2"> -->
                                <!-- <button class="col-md-2 add_document remove_condition_btn" type="button" style="display:none;" onclick="remove_condition_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                                                <button class="col-md-2 add_document add_condition_btn" type="button" onclick="add_condition_div(this)"><i class="fa fa-plus" aria-hidden="true"></i></button> -->
                                <!-- </div> -->
                            </div>

                        <?php
                        } ?>
                    </div>
            <?php
                }
            }
            ?>

        <?php } ?>

        <div class="col-12 text-center mt-5">

            <button class="btn btn-primary" onclick="handleSubmit(event)">Update</button>
        </div>
    </form>
</div>

<script>
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>";
    async function handleSubmit(event) {
        event.preventDefault();

        try {
            // Call the validateForm function and store the result in a variable
            let validationResult = await validateForm();
            console.log(validationResult);

            if (validationResult) {
                $(".check_university").each(function() {
                    $(this).find('input, select').prop('disabled', false).selectpicker('refresh');
                })
                // Collect form data
                const formElement = document.getElementById('university_shortlisting_form');
                const formData = new FormData(formElement);

                formData.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

                $(".check_university").each(function() {
                    $(this).find('input, select').prop('disabled', true).selectpicker('refresh');
                })
                let response = await $.ajax({
                    url: "<?= base_url("clients/university_shortlisting_update") ?>",
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false
                });
                response = JSON.parse(response);
                if (response.resp_code == "RCS") {
                    alert_float("success", response.resp_desc);
                    location.reload();
                } else {
                    alert_float("danger", response.resp_desc);
                }
            }
        } catch (error) {
            console.error(error);
            alert_float("danger", error);
            // Handle the error (e.g., display an error message to the user)
        }
    }

    function validateForm() {
        return new Promise((resolve, reject) => {
            $("select[name='university_application_status']").each(function(index) {
                if ($(this).val() == "") {
                    $(this).focus();
                    alert_float("danger", "Select university " + (index + 1) + " status is required.");
                    reject(false);
                    return false;
                }
            });
            resolve(true);
        });
    }

    $(document).ready(function() {
        $(".check_university").each(function() {
            $(this).find('input, select').prop('disabled', true).selectpicker('refresh');
        })
    })
</script>