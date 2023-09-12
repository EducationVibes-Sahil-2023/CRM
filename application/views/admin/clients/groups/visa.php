<style>
    /* Custom CSS for a more attractive form */
    body {
        background-color: #f8f9fa;
    }

    .form-container {
        background-color: #ffffff;
        border: 1px solid #ccc;
        padding: 20px;
        margin-top: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-container .mt-5 {
        margin-top: 10px;
    }


    .form-container .datepicker,
    .form-container .form-control,
    .form-container .selectpicker {
        width: 100%;
    }

    .form-container .add-document,
    .form-container .download-document {
        padding: 7px;
        width: 33px;
    }

    .form-container .add-document-btn,
    .form-container .add-university-btn {
        margin-left: 5px;
        margin-top: -5px;
    }

    .form-container .document-upload-files {
        margin-top: 15px;
    }

    .form-container .btn-primary {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .form-container .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .btn.add-document {
        margin-top: -35px;
    }

    .remove_document_btn {
        margin-left: 5px;

        padding: 7px;
        width: 33px;

    }

    .document-file-name {
        overflow: hidden;
    }
</style>
<?php

$country_name = "";
$university_name = "";
if (!empty($selected_university_country->university_name) && !empty($selected_university_country->university)) {
    $university_name = $selected_university_country->university_name;
    $country_array = json_decode($selected_university_country->university, true);
    foreach ($country_array as $key => $con) {
        if (!empty($con)) {
            $university_array = explode(",", $con);
            if (!empty($university_array)) {
                foreach ($university_array as $u) {
                    if (strtolower($university_name) == strtolower($u)) {
                        $country_name = $key;
                    }
                }
            }
        }
    }
}
$visa_documents = !empty($visa_data[0]["visa_documents"]) ? json_decode($visa_data[0]["visa_documents"], true) : [];
$selected_vendor = !empty($visa_data[0]["vendor"]) ? $visa_data[0]["vendor"] : [];

if (empty($country_name)) {
?>
    <h3>Application Tracker Process not completed.</h3>
<?php
    die;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Visa Documents <?php if (!empty($country_name)) { ?><span>(<?= $country_name ?>)</span> <?php } ?></h4>
            <button class="btn btn-primary float-right add-document add-university-btn" type="button" style="display: none1;" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button>
            <hr>

            <div class="visa_documents_upload">
                <div id="upload_documents">
                    <?php if (!empty($visa_documents)) {
                        foreach ($visa_documents as $doc) {
                    ?>
                            <div class="row document_upload_files">
                                <div class="col-md-5">
                                    <input class="form-control" name="document_label[]" type="text" placeholder="Enter Label Name" value="<?= !empty($doc["label_name"]) ? $doc["label_name"] : '' ?>">
                                </div>
                                <div class="col-md-5">
                                    <input class="form-control" type="file" data-url="<?= !empty($doc["document_file"]) ? $doc["document_file"] : "0" ?>" accept="image/*,application/pdf" onchange="real_time_media_show(this)" name="document_file[]" placeholder="">


                                    <div class="row media-text-div">
                                        <div class="col-md-10">
                                            <p class="document-file-name"><?= !empty($doc["document_file"]) ? $doc["document_file"] : '' ?></p>
                                        </div>
                                        <div class="col-md-2 file-download-block">
                                            <a class="col-md-12 download_document" accept="image/*,application/pdf" onclick='window.open("<?= !empty($doc["document_file"]) ? base_url($doc["document_file"]) : "0" ?>", "_blank");' href="javascript:void(0);" type="button">
                                                <i class="fa fa-download" aria-hidden="true"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="col-md-2 add_document remove_document_btn" style="display:none;" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        <?php
                        }
                    } else { ?>
                        <div class="row document_upload_files">
                            <div class="col-md-5">
                                <input class="form-control" name="document_label[]" type="text" placeholder="Enter Label Name">
                            </div>
                            <div class="col-md-5">
                                <input class="form-control" type="file" accept="image/*,application/pdf" onchange="real_time_media_show(this)" name="document_file[]" placeholder="">

                            </div>
                            <div class="col-md-2">
                                <button class="col-md-2 add_document remove_document_btn" style="display:none;" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="button-save-documents mt-5">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <button class="btn btn-primary pull-right" type="button" onclick="visa_update('document')">Documents Update</button>
                    </div>
                </div>
            </div>
            <hr>

            <h4 class="fs-title">Vendor</h4>
            <hr>

            <div class="vendor_select row">
                <div class="col-md-6">
                    <!-- <select class="selectpicker form-control">
                        <option>Select Vendor</option>
                        <option>Vendor 1</option>
                        <option>Vendor 2</option>
                        <option>Vendor 3</option>
                    </select> -->
                    <?php
                    array_unshift($vendor, array("id" => "", "vendor" => "Select Vendor"));
                    ?>
                    <?php echo render_select('vendor', $vendor, array('id', array('vendor')), '', $selected_vendor, array('data-width' => '100%', 'data-none-selected-text' => _l('Vendor'), '', 'data-actions-box' => true), array(), 'no-mbot', '', false, 'vendor'); ?>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary pull-right" type="button" onclick="visa_update('vendor')">Vendor Update</button>
                </div>
            </div>
            <hr>

            <h4 class="fs-title">Visa Application & Fee</h4>
            <hr>

            <div class="visa_select">
                <div class="checkbox">
                    <input type="checkbox" id="ihs_fee" name="visa_type[]" <?= !empty($visa_data[0]["ihs_fee"] && $visa_data[0]["ihs_fee"] == 1) ? 'checked' : '' ?> value="ihs_fee">
                    <label for="ihs_fee">IHS FEE</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="visa_fee" name="visa_type[]" <?= !empty($visa_data[0]["visa_fee"] && $visa_data[0]["visa_fee"] == 1) ? 'checked' : '' ?> value="visa_fee">
                    <label for="visa_fee">VISA FEE</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="vfs_fee" name="visa_type[]" <?= !empty($visa_data[0]["vfs_fee"] && $visa_data[0]["vfs_fee"] == 1) ? 'checked' : '' ?> value="vfs_fee">
                    <label for="vfs_fee">VFS FEE</label>
                </div>
                <div class="row mt-5">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <button class="btn btn-primary pull-right" type="button" onclick="visa_update('visa')">Visa Update</button>
                    </div>
                </div>
            </div>
            <hr>

            <h4 class="fs-title">Biometric and Interview Date</h4>
            <hr>

            <div class="bio-interview-date">
                <div class="form-group row">
                    <div class="col-md-6">
                        <!-- <label for="biometric-date">Biometric Date</label> -->
                        <div class="checkbox">
                            <input type="checkbox" id="biometric_status" onclick="change_bio_metric(this)" name="biometric_status" <?= !empty($visa_data[0]["biometric_status"]) ? 'checked' : '' ?> value="1">
                            <label for="biometric_date">Biometric Date</label>
                        </div>
                    </div>
                    <div class="col-md-6" style="display:<?= !empty($visa_data[0]["biometric_status"]) ? '' : 'none' ?>">
                        <input type="text" id="biometric-date" class="form-control datepicker" value='<?= !empty($visa_data[0]["biometric_date"]) ? $visa_data[0]["biometric_date"] : '' ?>' autocomplete="off" placeholder="Biometric Date">
                    </div>
                </div>
                <div class="form-group row mt-5">
                    <div class="col-md-6">
                        <!-- <label for="interview-date">Interview Date</label> -->

                        <div class="checkbox">
                            <input type="checkbox" id="interview_status" onclick="change_interview(this)" name="interview_status" <?= !empty($visa_data[0]["interview_status"]) ? 'checked' : '' ?> value="1">
                            <label for="interview_date">Interview Date</label>
                        </div>
                    </div>
                    <div class="col-md-6" style="display:<?= !empty($visa_data[0]["interview_status"]) ? '' : 'none' ?>">
                        <input type="text" id="interview-date" value='<?= !empty($visa_data[0]["interview_date"]) ? $visa_data[0]["interview_date"] : '' ?>' class="form-control datepicker" autocomplete="off" placeholder="Interview Date">
                    </div>
                </div>
                <div class="form-group row mt-5">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <button class="btn btn-primary pull-right" type="button" onclick="visa_update('date')">Date Update</button>
                    </div>
                </div>
            </div>
            <hr>
            <h4 class="fs-title">Visa Status</h4>
            <hr>
            <div class="status_update row">
                <div class="col-md-6">
                    <select id="visa_status" class="selectpicker  form-control" onchange="status_change(this)">
                        <option value="pending" <?= !empty($visa_data[0]["visa_status"] && $visa_data[0]["visa_status"] == 'pending') ? "selected" : '' ?>>Pending</option>
                        <option value="reject" <?= !empty($visa_data[0]["visa_status"] && $visa_data[0]["visa_status"] == 'reject') ? "selected" : '' ?>>Reject</option>
                        <option value="stamped" <?= !empty($visa_data[0]["visa_status"] && $visa_data[0]["visa_status"] == 'stamped') ? "selected" : '' ?>>Stamped</option>
                    </select>

                    <div id="reject_div" class="mt-5" style="display:<?= !empty($visa_data[0]["visa_status"] && $visa_data[0]["visa_status"] == 'reject') ? "" : 'none' ?>">
                        <textarea class="form-control" placeholder="write a note">
                        <?= !empty($visa_data[0]["note"]) ? trim($visa_data[0]["note"]) : '' ?>
                        </textarea>
                    </div>
                    <div id="stamped_div" class="mt-5" style="display: <?= !empty($visa_data[0]["visa_status"] && $visa_data[0]["visa_status"] == 'stamped') ? "" : 'none' ?>">
                        <input type="file" class="form-control" data-url="<?= !empty($visa_data[0]["visa_file"]) ? $visa_data[0]["visa_file"] : '' ?>" id="visa_file" accept="image/*,application/pdf" onchange="real_time_media_show(this)">
                        <?php if (!empty($visa_data[0]["visa_file"])) { ?>
                            <div class="row media-text-div">
                                <div class="col-md-10">
                                    <p class="document-file-name"><?= !empty($visa_data[0]["visa_file"]) ? $visa_data[0]["visa_file"] : '' ?></p>
                                </div>
                                <div class="col-md-2 file-download-block">
                                    <a class="col-md-12 download_document" accept="image/*,application/pdf" onclick='window.open("<?= !empty($visa_data[0]["visa_file"]) ? base_url($visa_data[0]["visa_file"]) : "0" ?>", "_blank");' href="javascript:void(0);" type="button">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </a>
                                </div>

                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary pull-right" type="button" onclick="visa_update('status')">Status Update</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var client_id = "<?= $client_id ?>";
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>";
    async function add_documents() {

        let response = await is_validate_document();
        if (response) {
            let html = `<div class="row mt-5 document_upload_files">
                        <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name"></div>
                        <div class="col-md-5">
                        <div class="margin-bottom " >
                        <input class="col-md-5 form-control" type="file" accept="image/*,application/pdf" name="document_file[]" onchange="real_time_media_show(this)" placeholder=""></div>
                                   </div>     
                        <div class="col-md-2">
                        <button class="col-md-6 add_document remove_document_btn" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                      
                        </div>
            </div>`;
            $("#upload_documents").append(html);
            $("#upload_documents").find(".add_document_btn").hide();
            $("#upload_documents").find(".remove_document_btn").show();
            $("#upload_documents").find(".add_document_btn:last").show();
            if ($(".document_upload_files ").length == 1) {
                $(".document_upload_files ").find(".remove_document_btn").hide();
            }
        }

    }

    function status_change(obj) {
        let status = $(obj).val();
        $("#reject_div").hide();
        $("#stamped_div").hide();
        $("#reject_div").find("textarea").val('');
        $("#stamped_div").find(".media-text-div").hide();
        $("#stamped_div").find("input[type=file]").val('');
        if (status == "pending") {

        } else if (status == 'reject') {
            $("#reject_div").show();
        } else if (status == 'stamped') {
            $("#stamped_div").show();
        }
    }

    function change_bio_metric(obj) {
        $("#biometric-date").val('');

        if ($(obj).is(":checked")) {
            $("#biometric-date").parent("div").show();
        } else {
            $("#biometric-date").parent("div").hide();
        }
    }


    function change_interview(obj) {
        $("#interview-date").val('');

        if ($(obj).is(":checked")) {
            $("#interview-date").parent("div").show();
        } else {
            $("#interview-date").parent("div").hide();
        }
    }

    function is_validate_document() {
        return new Promise((resolve, reject) => {
            $(".document_upload_files").each(function() {
                let label_name = $(this).find("input[name='document_label[]']").val();
                let document = $(this).find("input[name='document_file[]']").prop("files")[0];
                let document_previous_url = $(this).find("input[name='document_file[]']").attr("data-url");

                if (document_previous_url === undefined || document_previous_url == "undefined") {
                    document_previous_url = "";
                }
                if (document === undefined || document == "undefined") {
                    document = "";
                }
                // console.log(document_previous_url);
                // console.log(document);
                if (label_name === undefined || $.trim(label_name) === "") {
                    $(this).find("input[name='document_label[]']").focus();
                    alert_float("danger", "Label is required");
                    resolve(false);
                    return;
                }

                if ((document === undefined || $.trim(document) === "") && (document_previous_url == undefined || document_previous_url == "")) {
                    $(this).find("input[name='document_file[]']").focus();
                    alert_float("danger", "Document file is required");
                    resolve(false);
                    return;
                }

                // Additional validation logic or processing can be added here
            });

            resolve(true); // Resolving the promise if all validations pass
        });
    }

    function remove_document(obj) {
        $(obj).parents(".document_upload_files").remove();
        $("#upload_documents").find(".remove_document_btn").show();
        $("#upload_documents").find(".add_document_btn").hide();
        $("#upload_documents").find(".add_document_btn:last").show();
        if ($(".document_upload_files ").length == 1) {
            $(".document_upload_files ").find(".remove_document_btn").hide();
        }
    }



    async function visa_update(type = '') {
        if (type == '') {
            return false;
        }
        if (type == 'document') {
            let isDocumentValid = await is_validate_document();
            if (isDocumentValid) {
                try {
                    let uploadResponse = await upload_document(type);
                    if (uploadResponse.resp_code == "RCS") {
                        hide_loader();
                        alert_float("success", uploadResponse.resp_desc);
                        location.reload();
                    } else {
                        if (uploadResponse.resp_code != undefined) {
                            alert_float("danger", uploadResponse.resp_desc);
                            hide_loader();
                            return false;
                        } else {
                            alert_float("danger", uploadResponse);
                            hide_loader();
                            return false;
                        }
                    }
                } catch (error) {
                    hide_loader();
                    console.error(error);
                    return false;
                }
            } else {
                hide_loader();
                return false;
            }
            return false
        } else if (type == "vendor") {
            let vendor = $("#vendor").val();
            if (vendor == "") {
                alert_float("danger", "Please select vendor");
                hide_loader();
                return false;
            }

            let upload_data = new FormData();
            upload_data.append("type", type);
            upload_data.append("client_id", client_id);
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("vendor", vendor);
            let response = await $.ajax({
                url: "<?= base_url("admin/clients/upload_visa_documents") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            uploadResponse = JSON.parse(response);

            if (uploadResponse.resp_code == "RCS") {
                hide_loader();
                alert_float("success", uploadResponse.resp_desc);
                location.reload();
            } else {
                if (uploadResponse.resp_code != undefined) {
                    alert_float("danger", uploadResponse.resp_desc);
                    hide_loader();
                    return false;
                } else {
                    alert_float("danger", uploadResponse);
                    hide_loader();
                    return false;
                }
            }

        } else if (type == "visa") {
            let checkedValues = [];
            $("input[name='visa_type[]']:checked").each(function() {
                checkedValues.push($(this).val());
            });


            console.log("Checked values:", checkedValues);
            if (checkedValues.includes("visa_fee")) {
                let upload_data = new FormData();
                upload_data.append("type", type);
                upload_data.append("client_id", client_id);
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("visa_type", checkedValues);
                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/upload_visa_documents") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                uploadResponse = JSON.parse(response);

                if (uploadResponse.resp_code == "RCS") {
                    hide_loader();
                    alert_float("success", uploadResponse.resp_desc);
                    location.reload();
                } else {
                    if (uploadResponse.resp_code != undefined) {
                        alert_float("danger", uploadResponse.resp_desc);
                        hide_loader();
                        return false;
                    } else {
                        alert_float("danger", uploadResponse);
                        hide_loader();
                        return false;
                    }
                }


            } else {
                alert_float("danger", "VISA FEE is required.");
                hide_loader();
                return false;

            }

        } else if (type == "date") {
            let biometric_status = 0;
            let interview_status = 0;
            let biometric_date = "";
            let interview_date = "";

            if ($("#biometric_status").is(":checked")) {
                biometric_status = 1;
                biometric_date = $("#biometric-date").val();
                if (biometric_date == "") {
                    alert_float("danger", "Bio-metric date is required.");
                    hide_loader();
                    return false;
                }
            }

            if ($("#interview_status").is(":checked")) {
                interview_status = 1;
                interview_date = $("#interview-date").val();
                if (interview_date == "") {
                    alert_float("danger", "Interview date is required.");
                    hide_loader();
                    return false;
                }
            }

            let upload_data = new FormData();
            upload_data.append("type", type);
            upload_data.append("biometric_status", biometric_status);
            upload_data.append("interview_status", interview_status);
            upload_data.append("biometric_date", biometric_date);
            upload_data.append("interview_date", interview_date);
            upload_data.append("client_id", client_id);

            try {
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/upload_visa_documents") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                // Handle the success response from the server
                let uploadResponse = JSON.parse(response);

                if (uploadResponse.resp_code == "RCS") {
                    hide_loader();
                    alert_float("success", uploadResponse.resp_desc);
                    location.reload();
                } else {
                    if (uploadResponse.resp_code != undefined) {
                        alert_float("danger", uploadResponse.resp_desc);
                        hide_loader();
                        return false;
                    } else {
                        alert_float("danger", uploadResponse);
                        hide_loader();
                        return false;
                    }
                }
            } catch (error) {
                // Handle the error response from the server
                console.error(error);
                reject(error);
            }

        } else if (type == 'status') {
            let visa_status = $("#visa_status").val();
            let note = '';
            let visa_file = '';
            let visa_file_url = ''
            if (visa_status == 'reject') {
                note = $("#reject_div").find("textarea").val();
                if (note == '') {
                    alert_float("danger", "Note is required.");
                    hide_loader();
                    return false;
                }
            }
            if (visa_status == 'stamped') {
                visa_file = $("#visa_file").prop("files")[0];
                visa_file_url = $("#visa_file").attr("data-url");

                if (visa_file == undefined && visa_file_url == '') {
                    alert_float("danger", "visa file is required.");
                    hide_loader();
                    return false;
                }
            }


            let upload_data = new FormData();
            upload_data.append("type", type);
            upload_data.append("visa_status", visa_status);
            upload_data.append("note", note);
            upload_data.append("visa_file", visa_file);
            upload_data.append("visa_file_url", visa_file_url);
            upload_data.append("client_id", client_id);

            try {
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/upload_visa_documents") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                // Handle the success response from the server
                let uploadResponse = JSON.parse(response);

                if (uploadResponse.resp_code == "RCS") {
                    hide_loader();
                    alert_float("success", uploadResponse.resp_desc);
                    location.reload();
                } else {
                    if (uploadResponse.resp_code != undefined) {
                        alert_float("danger", uploadResponse.resp_desc);
                        hide_loader();
                        return false;
                    } else {
                        alert_float("danger", uploadResponse);
                        hide_loader();
                        return false;
                    }
                }
            } catch (error) {
                // Handle the error response from the server
                console.error(error);
                reject(error);
            }
        }

    }

    function upload_document(type) {
        return new Promise(async (resolve, reject) => {
            let upload_data = new FormData();
            upload_data.append("type", type);
            $(".document_upload_files").each(function(index) {
                let label_name = $(this).find("input[name='document_label[]']").val();
                let document = $(this).find("input[name='document_file[]']").prop("files")[0];
                let document_url = $(this).find("input[name='document_file[]']").attr("data-url");
                if (document_url === undefined || document_url == "undefined") {
                    document_url = "";
                }
                if (document === undefined || document == "undefined") {
                    document = document_url;
                }
                upload_data.append("document_label[]", label_name);
                upload_data.append("document_file_" + index, document);
                upload_data.append("document_url[]", document_url);


            });
            upload_data.append("client_id", client_id);

            try {

                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/upload_visa_documents") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                // Handle the success response from the server
                resolve(JSON.parse(response));
            } catch (error) {
                // Handle the error response from the server
                console.error(error);
                reject(error);
            }
        });
    }

    function real_time_media_show(input) {
        // var file = input.files[0];

        // if (file) {
        //     var $parent = $(input).parents(".document_upload_files");
        //     $parent.find(".media-text-div").remove();

        //     var mediaTextDiv = $('<div class="row media-text-div">' +
        //         '<div class="col-md-10">' +
        //         '<p class="document-file-name">' + file.name + '</p>' +
        //         '</div>' +
        //         '<div class="col-md-2 file-download-block">' +
        //         '<a class="col-md-12 download_document" accept="image/*,application/pdf"  onclick="window.open(`' + URL.createObjectURL(file) + ' `, `_blank`);" href="javascript:void(0);"  type="button">' +
        //         '<i class="fa fa-download" aria-hidden="true"></i>' +
        //         '</a>' +
        //         '</div>' +
        //         '</div>');

        //     $(input).after(mediaTextDiv);
        // } else {
        //     $(input).parents(".document_upload_files").find(".media-text-div").remove();

        // }
    }
</script>