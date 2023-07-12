<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$applicant_tracker = applicant_tracker();
$applicant_status = !empty($client->applicant_status) ? $client->applicant_status : 0;
$profile_creation_data = !empty($profile_creation_data) ? $profile_creation_data : "";
?>
<script type="text/javascript" id="jquery-migrate-js" src="https://localhost/git_crm/assets/plugins/jquery/jquery-migrate.min.js?v=2.6.0"></script>
<!-- <script type="text/javascript" id="vendor-js" src="https://localhost/git_crm/assets/builds/vendor-admin.js?v=2.6.0"></script> -->

<style>
    .nav>li>a {
        color: white !important;
    }

    .dropdown-menu .animated .fadeIn li a {
        color: black !important;
    }

    .customers-nav-item-profile .dropdown-toggle {
        background-color: transparent !important;
    }

    .submenu .customer-top-submenu li a,
    .submenu .customer-top-submenu li a:hover,
    .submenu .customer-top-submenu li:hover {
        text-decoration: none !important;

    }

    a:focus,
    a:hover {
        text-decoration: none !important;
    }

    /*basic reset*/
    * {
        margin: 0;
        padding: 0;
    }

    html {
        height: 100%;
        background: #eee;
    }

    body {
        font-family: Montserrat, arial, verdana;
        background: transparent;
    }

    /*form styles*/
    #msform {
        text-align: center;
        position: relative;
        margin-top: 30px;
    }

    #msform fieldset {
        background: white;
        border: 0 none;
        border-radius: 8px;
        box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
        padding: 20px 30px;
        box-sizing: border-box;
        width: 100%;
        /* margin: 0 10%; */

        /*stacking fieldsets above each other*/
        position: relative;
    }

    /*Hide all except first fieldset*/
    /* #msform fieldset:not(:first-of-type) {
        display: none;
    } */

    /*inputs*/
    #msform input,
    #msform textarea {
        /* padding: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
        width: 100%;
        box-sizing: border-box;
        font-family: montserrat;
        color: #2C3E50;
        font-size: 13px; */
    }

    #msform input:focus,
    #msform textarea:focus {
        -moz-box-shadow: none !important;
        -webkit-box-shadow: none !important;
        box-shadow: none !important;
        border: 1px solid #2098ce;
        outline-width: 0;
        transition: All 0.5s ease-in;
        -webkit-transition: All 0.5s ease-in;
        -moz-transition: All 0.5s ease-in;
        -o-transition: All 0.5s ease-in;
    }

    /*buttons*/
    #msform .action-button {
        width: 100px;
        background: #2098ce;
        font-weight: bold;
        color: white;
        border: 0 none;
        border-radius: 25px;
        cursor: pointer;
        padding: 10px 5px;
        margin: 10px 5px;
    }

    #msform .action-button:hover,
    #msform .action-button:focus {
        box-shadow: 0 0 0 2px white, 0 0 0 3px #2098ce;
    }

    #msform .action-button-previous {
        width: 100px;
        background: #aCbEd0;
        font-weight: bold;
        color: white;
        border: 0 none;
        border-radius: 25px;
        cursor: pointer;
        padding: 10px 5px;
        margin: 10px 5px;
    }

    #msform .action-button-previous:hover,
    #msform .action-button-previous:focus {
        box-shadow: 0 0 0 2px white, 0 0 0 3px #aCbEd0;
    }

    /*headings*/
    .fs-title {
        font-size: 18px;
        text-transform: uppercase;
        color: #2C3E50;
        margin-bottom: 10px;
        letter-spacing: 2px;
        font-weight: bold;
    }

    .fs-subtitle {
        font-weight: normal;
        font-size: 13px;
        color: #666;
        margin-bottom: 20px;
    }

    /*progressbar*/
    #progressbar {
        margin-bottom: 30px;
        overflow: hidden;
        /*CSS counters to number the steps*/
        counter-reset: step;
    }

    #progressbar li {
        list-style-type: none;
        color: #666;
        text-transform: uppercase;
        font-size: 9px;
        /* width: 33.33%; */
        float: left;
        position: relative;
        letter-spacing: 1px;
    }

    #progressbar li:before {
        content: counter(step);
        counter-increment: step;
        width: 24px;
        height: 24px;
        line-height: 26px;
        /* width: 35px;
        height: 35px;
        line-height: 35px; */
        display: block;
        font-size: 12px;
        color: #333;
        background: white;
        border-radius: 25px;
        margin: 0 auto 10px auto;
    }

    /*progressbar connectors*/
    #progressbar li:after {
        content: '';
        width: 100%;
        height: 2px;
        background: white;
        position: absolute;
        left: -50%;
        top: 9px;
        z-index: -1;
        /*put it behind the numbers*/
    }

    #progressbar li:first-child:after {
        /*connector not needed before the first step*/
        content: none;
    }

    /*marking active/completed steps blue*/
    /*The number of the step and the connector before it = blue*/
    #progressbar li.active:before,
    #progressbar li.active:after {
        background: #2098ce;
        color: white;
    }

    #progressbar {
        text-align: center;
    }

    #progressbar li {
        display: block;
        width: 20%;
        text-align: center;
    }

    #progressbar li {
        display: inline-block;
        text-align: center;
        color: grey;
    }

    #progressbar li.active {
        color: orange !important;
    }

    #progressbar li.active:before {
        background-color: orange !important;
    }

    #progressbar li.previous {}

    #progressbar li.permanent_previous {
        color: green;
    }




    #progressbar li.previous:before {
        background-color: green !important;
    }

    #progressbar li.inactive:before {
        background-color: #E7ECF1 !important;
    }

    #progressbar li.inactive:before {
        color: #666 !important;
    }

    .add_document,
    .download_document {
        padding: 7px;
        width: 33px;
        /* line-height: 33px; */
    }

    #profile_div .download_document {
        line-height: 33px;
    }

    #profile_creation_div .fa-pencil-square-o,
    #profile_creation_div .fa-file {
        line-height: 40px;
    }


    .document_upload_files {
        margin: 15px 0px;
    }

    /* .message-notification {
        background: orange;
        padding: 25px;
        font-size: 15px;
        color: white;
        border-radius: 7px;
        box-shadow: 0px 1px 5px 1px grey;
        margin-bottom: 20px;
    } */

    .message-notification {
        padding: 0px;
        font-size: 14px;
        color: orange;
        border-radius: 7px;
    }

    .message-notification.success {
        color: #84c529 !important;
    }

    .message-notification.danger {
        color: #dc3545 !important;
    }

    i.fa {
        cursor: pointer;
    }

    #profile_div i.fa {
        font-size: 18px;

    }

    .edit_save_block {
        position: absolute;
        right: 4%;
        line-height: 55px;
    }

    .next.action-button:disabled {
        background: #aCbEd0 !important;
        cursor: not-allowed !important;
        box-shadow: unset !important;
    }

    .document-file-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
        text-align: left;
    }

    #progressbar li.previous:before {
        background-color: green;
        color: white;
    }

    .application_div div.university_div_application {
        margin-top: 10px !important;
    }

    .margin-bottom {
        margin-bottom: 5px;
    }

    .file-download-block .download_document {
        padding: 0px !important;
    }

    .add_document_btn,
    .add_university_btn {
        margin-left: 5px;
    }

    textarea.conditional_textarea {
        width: 100%;
        height: 50px;
        resize: none;
        padding: 10px;
        margin: 5px 0px;
    }

    .text-area-field-div {
        display: flex;
        align-items: center;
    }

    #offer_div .university_div_application {
        /* border: 1px solid black; */
        padding: 10px 0px;
        margin-top: 25px;
        box-shadow: 0px 1px 5px -2px black;
    }

    .document_approval_message_action {
        display: inline-block !important;
    }
</style>
<!-- MultiStep Form -->
<?php

if (empty($customer_admins)) { ?>
    <h2 class='text-center'>Not Started</h2>
<?php } else { ?>
    <div class="row">
        <div class="col-md-12 ">
            <form id="msform">
                <!-- progressbar -->
                <ul id="progressbar" class="d-flex justify-content-center">

                    <?php
                    foreach ($applicant_tracker as $key => $track) {
                    ?>
                        <li data-show="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '' ?>"><?= $track["name"] ?></li>
                    <?php
                    }
                    ?>
                </ul>
                <!-- fieldsets -->



                <?php
                foreach ($applicant_tracker as $k => $track) {
                ?>
                    <fieldset id="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '12' ?>" style="display:<?= ($applicant_status == $k) ? "show" : "none" ?>">
                        <h2 class="fs-title" style="margin-bottom: 20px!important;"><?= !empty($track["name"]) ? $track["name"] : 'Document' ?></h2>
                        <?php if ($track["show_div_name"] == "document_div") { ?>
                            <div id="upload_documents">
                                <?php
                                $doc_data_array = [];
                                if (!empty($upload_documents[0]["data"])) {
                                    $doc_data_array = json_decode($upload_documents[0]["data"], true);
                                }
                                if (!empty($upload_documents) && !empty($doc_data_array)) {
                                    foreach ($doc_data_array as $d_key => $docs) {
                                        $file_name = "";
                                        if (!empty($docs["document_file"])) {
                                            $file_name =  trim(explode("_", basename($docs["document_file"]))[2]);
                                        }
                                ?>

                                        <div class="row col-md-12 document_upload_files ">
                                            <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name" value="<?= $docs["label_name"] ?>"></div>
                                            <div class="col-md-5">
                                                <div class="margin-bottom ">
                                                    <input class="col-md-5 form-control" type="file" accept="image/*,application/pdf" data-url="<?= $docs["document_file"] ?>" onchange="real_time_media_show(this)" name="document_file[]" placeholder="">
                                                </div>
                                                <div class="row media-text-div">
                                                    <div class="col-md-10">
                                                        <p class="document-file-name"><?= $file_name ?></p>
                                                    </div>
                                                    <div class="col-md-2 file-download-block">
                                                        <a class="col-md-12 download_document" onclick="window.open(`<?= base_url($docs['document_file']) ?>`, '_blank');" href="javascript:void(0);" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-2">
                                                <!-- <a class="col-md-2 download_document" download href="<?= base_url($docs["document_file"]) ?>" type="button"><i class="fa fa-download" aria-hidden="true"></i></a> -->

                                                <button style="display:<?= ($d_key == 0) ? 'none' : '' ?>;" class="col-md-2 add_document remove_document_btn" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>

                                                <button class="col-md-2 add_document add_document_btn" style="display:none;" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                            </div>


                                        </div>
                                    <?php
                                    }
                                } else { ?>
                                    <div id="upload_documents">
                                        <div class="row col-md-12 document_upload_files ">
                                            <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name"></div>
                                            <div class="col-md-5"><input class="col-md-5 form-control" type="file" accept="image/*,application/pdf" onchange="real_time_media_show(this)" name="document_file[]" placeholder=""></div>
                                            <div class="col-md-2">
                                                <button class="col-md-2 add_document remove_document_btn" style="display:none;" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                                <button class="col-md-2 add_document add_document_btn" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                                ?>
                            </div>

                            <div class="document_approval_message_action">
                            </div>
                        <?php
                        } else if ($track["show_div_name"] == "profile_div") { ?>
                            <div id="profile_creation_div">

                                <div class="row profile-div-save">
                                    <div class="col-md-4">
                                        <label>Email <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo render_input('email_creation', "", !empty($profile_creation_data[0]["email"]) ? $profile_creation_data[0]["email"] : '', "Email", ["required" => "required", "placeholder" => "Enter Email"]); ?>
                                    </div>
                                    <div class="col-md-4 edit_save_block email_creation_block">
                                        <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                        <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'email')"></i>
                                    </div>
                                </div>
                                <div class="row profile-div-save">
                                    <div class="col-md-4">
                                        <label>Vendor <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        $selected_vendor = !empty($profile_creation_data[0]["vendor"]) ? explode(",", $profile_creation_data[0]["vendor"]) : [];
                                        echo render_select('profile_creator_vendor[]', $profile_creator_vendor, array('id', 'name'), '', $selected_vendor, array('multiple' => true), array(), '', '', false, "select_vendor"); ?>
                                    </div>
                                    <div class="col-md-4 edit_save_block vendor_creation_block">
                                        <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                        <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'vendor')"></i>
                                    </div>
                                </div>
                                <div class="row profile-div-save">
                                    <div class="col-md-4">
                                        <label>Sop <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="">
                                            <input type="file" id="sop_document" onchange="real_time_media_show_sop(this)" data-url="<?= !empty($profile_creation_data[0]["sop"]) ? $profile_creation_data[0]["sop"] : '' ?>" name="sop_document" class="form-control" accept=".pdf,.doc,.docx">
                                        </div>
                                        <div class="media-text-div-other">
                                            <?php if (!empty($profile_creation_data[0]["sop"])) {
                                                $file_name = "";
                                                if (!empty($profile_creation_data[0]["sop"])) {
                                                    $file_name =  trim(explode("_", basename($profile_creation_data[0]["sop"]))[2]);
                                                }
                                            ?>

                                                <div class="row mt-5 margin-bottom">
                                                    <div class="col-md-10">
                                                        <p class="document-file-name"><?= $file_name ?></p>
                                                    </div>
                                                    <div class="col-md-2 file-download-block">
                                                        <a class="col-md-12 download_document" href="javascript:void(0);" onclick="window.open(`<?= base_url($profile_creation_data[0]['sop']) ?>`, '_blank');" type=" button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>

                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 edit_save_block sop_creation_block">


                                        <i class="fa fa-pencil-square-o col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                        <i class="fa fa-file col-md-1" style="display:none;" onclick="save_data(this,'sop')"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="profile_approval_message_action">
                            </div>
                        <?php } else if ($track["show_div_name"] == "university_div") {
                            $selected_university = json_decode($admissionpreferences->university, true);
                            $university_drop_down = [];
                            foreach ($selected_university as $key => $university) {
                                if (!empty($university)) {
                                    $university_drop_down[$key] = explode(",", $university);
                                }
                            }
                        ?>

                            <div class="add_university_div_block">
                                <?php if (!empty($university_shortlisting)) {
                                    foreach ($university_shortlisting as $key_u => $short_list) {
                                ?>
                                        <div class="col-md-12 university_div <?= ($short_list["university_status"] == 1) ? '' : 'university_div_'; ?>">
                                            <div class="col-md-2">
                                                <?= ($short_list["university_status"] == 1) ? 'Approved' : (($short_list["university_status"] == 2) ? 'Reject' : '') ?>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="hidden" name="university_id" value="<?= $short_list["id"] ?>">
                                                <select class="selectpicker from-control" data-width="100%" name="select_university" id="select_university" data-live-search="true">
                                                    <option value="">Select University</option>
                                                    <?php
                                                    if (!empty($university_drop_down)) {
                                                        foreach ($university_drop_down as $country_name => $university_list) {
                                                            if (!empty($university_list)) {
                                                    ?>
                                                                <optgroup label="<?= $country_name ?>" id="<?= $country_name ?>">
                                                                    <?php
                                                                    foreach ($university_list as $university_name) {
                                                                        $selected_university = strtolower(trim($university_name)) == strtolower(trim($short_list["university_name"])) ? "selected" : '';
                                                                        if (!empty($university_name)) {
                                                                    ?>
                                                                            <option <?= $selected_university ?> value='<?= $university_name ?>'><?= $university_name ?></option>
                                                                    <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </optgroup>
                                                    <?php
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <?php
                                                $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : "";
                                                echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), "", $selected_vendor);
                                                ?>
                                                <input type="hidden" class="university_status_check" value="<?= $short_list["university_status"] ?>">
                                            </div>

                                            <div class="col-md-2">
                                                <!-- <button class="col-md-2 add_document" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button> -->
                                                <?php if ($short_list["university_status"] == 1) { ?>
                                                <?php } else { ?>
                                                    <button class="col-md-2 add_document remove_university_btn" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                                    <button class="col-md-2 add_document add_university_btn" style="display:none;" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                                <?php } ?>

                                            </div>
                                        </div>
                                    <?php }
                                    ?>

                                <?php } else { ?>
                                    <div class="col-md-12 university_div university_div_">
                                        <div class="col-md-2">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="hidden" name="university_id">
                                            <select class="selectpicker from-control" data-width="100%" name="select_university" id="select_university" data-live-search="true">
                                                <option value="">Select University</option>
                                                <?php
                                                if (!empty($university_drop_down)) {
                                                    foreach ($university_drop_down as $country_name => $university_list) {
                                                        if (!empty($university_list)) {
                                                ?>
                                                            <optgroup label="<?= $country_name ?>" id="<?= $country_name ?>">
                                                                <?php
                                                                foreach ($university_list as $university_name) {
                                                                    if (!empty($university_name)) {
                                                                ?>
                                                                        <option value='<?= $university_name ?>'><?= $university_name ?></option>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </optgroup>
                                                <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <?php
                                            // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");

                                            echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'));
                                            ?>
                                        </div>

                                        <div class="col-md-2">
                                            <!-- <button class="col-md-2 add_document" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button> -->
                                            <button class="col-md-2 add_document remove_university_btn" type="button" style="display:none;" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                            <button class="col-md-2 add_document add_university_btn" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="university_approval_message_action">
                            </div>

                        <?php } else if ($track["show_div_name"] == "application_div") {
                            $selected_university = json_decode($admissionpreferences->university, true);
                            $university_drop_down = [];
                            foreach ($selected_university as $key => $university) {
                                if (!empty($university)) {
                                    $university_drop_down[$key] = explode(",", $university);
                                }
                            }
                        ?>

                            <div class="application_div">
                                <?php if (!empty($university_shortlisting)) {
                                    $select_dropdown_value = array_column($customer_vendors, "name", "id");
                                    foreach ($university_shortlisting as $key_u => $short_list) {
                                        $selected_university_application = !empty($short_list["university_status"]) ? $short_list["university_status"] : "";
                                        if (!empty($selected_university_application) && $selected_university_application == 1) {
                                ?>
                                            <div class="col-md-12 university_div_application mt-2">
                                                <div class="col-md-3">
                                                    <input type="hidden" name="university_id" value="<?= $short_list["id"] ?>">
                                                    <input type="input" class="form-control" disabled value="<?= $short_list["university_name"] ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <?php
                                                    // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");
                                                    $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : "";
                                                    // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), "", $selected_vendor);
                                                    // echo $select_dropdown_value[$selected_vendor];

                                                    ?>
                                                    <input type="input" class="form-control" disabled value="<?= $select_dropdown_value[$selected_vendor] ?>">
                                                </div>

                                                <div class="col-md-3">
                                                    <?php


                                                    echo render_select('university_application_status', $university_application_status, array('id', 'name'), "", $selected_university_application); ?>


                                                </div>

                                                <div class="col-md-3">
                                                    <?php

                                                    $university_status_submit_new = array($university_status_submit[0]);
                                                    $selected_university_status_submit = !empty($short_list["university_submit_status"]) ? $short_list["university_submit_status"] : "";
                                                    echo render_select('university_status_submit', $university_status_submit_new, array('id', 'name'), "", $selected_university_status_submit); ?>


                                                </div>

                                            </div>
                                    <?php }
                                    }
                                    ?>

                                <?php } ?>
                            </div>
                        <?php } else if ($track["show_div_name"] == "offer_div") {
                            $selected_university = json_decode($admissionpreferences->university, true);
                            $university_drop_down = [];
                            foreach ($selected_university as $key => $university) {
                                if (!empty($university)) {
                                    $university_drop_down[$key] = explode(",", $university);
                                }
                            }
                        ?>

                            <div class="offer_div">
                                <?php if (!empty($university_shortlisting)) {
                                    $select_dropdown_value = array_column($customer_vendors, "name", "id");
                                    foreach ($university_shortlisting as $key_u => $short_list) {
                                        if ($short_list["university_submit_status"] != 1) {
                                            continue;
                                        }

                                        $file_name = "";
                                        if (!empty($short_list["media_file"])) {
                                            $file_name =  trim(explode("_", basename($short_list["media_file"]))[2]);
                                        }
                                ?>
                                        <div class="col-md-12 university_div_application mt-2">
                                            <div class="col-md-3">
                                                <input type="hidden" name="university_id" value="<?= $short_list["id"] ?>">
                                                <input type="hidden" class="form-control" name="university_status" value="<?= $short_list["university_offer_status"] ?>">
                                                <input type="input" class="form-control" disabled value="<?= $short_list["university_name"] ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <?php $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : ""; ?>
                                                <input type="input" class="form-control" disabled value="<?= $select_dropdown_value[$selected_vendor] ?>">
                                            </div>

                                            <div class="col-md-2">
                                                <?php
                                                // $selected_university_application = 2;
                                                $selected_university_application = !empty($short_list["university_status"]) ? $short_list["university_status"] : "";
                                                echo render_select('university_application_status', $university_application_status, array('id', 'name'), "", $selected_university_application);
                                                ?>
                                            </div>

                                            <div class="col-md-2">
                                                <select name="university_status_submit_offer" class=" university_status_submit_offer selectpicker" data-width="100%" data-none-selected-text="Non selected" data-live-search="true">
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
                                                <input type="file" disabled data-file-name="<?= !empty($docs["document_file"]) ? $docs["document_file"] : '' ?>" class="form-control" id="offer_letter" accept="images/*,application/pdf" onchange="real_time_media_show_offer(this)" name="offer_letter">

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
                                                        <div class="row text-area-field-div u_s_l_<?= $short_list['id'] ?>">
                                                            <input type="hidden" data-condition-id="<?= $con["id"] ?>">
                                                            <div class="col-md-3">Condition</div>
                                                            <div class="col-md-6"><textarea disabled placeholder="Write conditions ...... " class="conditional_textarea form-control" name="condition_text"><?= $con["condition_text"] ?></textarea></div>
                                                            <div class="col-md-3"><input type="file" disabled data-file-url="<?= $con["file"] ?>" class="form-control" onchange="real_time_media_show_offer_condition(this)" name="condition_file" accept="image/*,application/pdf">

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
                                            } else {
                                            ?>
                                                <div class="col-lg-12 mt-2 mb-2 text-area-field  " style="display:none;">
                                                    <div class="row text-area-field-div  u_s_l_<?= $short_list['id'] ?>">
                                                        <div class="col-md-1">Condition</div>
                                                        <div class="col-md-6"><textarea placeholder="Write conditions ...... " class="conditional_textarea form-control" name="condition_text"></textarea></div>
                                                        <div class="col-md-3"><input type="file" class="form-control" onchange="real_time_media_show_offer_condition(this)" name="condition_file" accept="image/*,application/pdf"></div>
                                                        <div class="col-md-2">
                                                            <button class="col-md-2 add_document remove_condition_btn" type="button" style="display:none;" onclick="remove_condition_div(this,<?= $short_list['id'] ?>)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                                            <button class="col-md-2 add_document add_condition_btn" type="button" onclick="add_condition_div(this,<?= $short_list['id'] ?>)"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php }
                                    ?>

                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if ($k > 0) { ?>
                            <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                        <?php } ?>
                        <?php

                        if (($k + 1) < count($applicant_tracker)) {
                            if ($applicant_status >  $k) { ?>
                                <input type="button" name="next" class="next action-button" onclick="next_step('<?= $track['show_div_name'] ?>',this,<?= $track['orderby'] ?>)" value="Next" />
                            <?php }
                        } else if (($k + 1) == count($applicant_tracker)) {  ?>
                            <!-- <input type="button" name="next" class="next action-button" onclick="next_step('<?= $track['show_div_name'] ?>',this,<?= $track['orderby'] ?>)" value="Update" /> -->
                        <?php } ?>

                    </fieldset>
                <?php
                }
                ?>
                <!-- 
            <fieldset>
                <h2 class="fs-title" style="margin-bottom: 20px!important;">Document Details</h2>
                <input type="text" name="fname" placeholder="First Name" />
                <input type="text" name="lname" placeholder="Last Name" />
                <input type="text" name="phone" placeholder="Phone" />
                <input type="button" name="next" class="next action-button" value="Next" />
            </fieldset>
            <fieldset>
                <h2 class="fs-title" style="margin-bottom: 20px!important;">Profile Creation</h2>
                <input type="text" name="twitter" placeholder="Twitter" />
                <input type="text" name="facebook" placeholder="Facebook" />
                <input type="text" name="gplus" placeholder="Google Plus" />
                <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                <input type="button" name="next" class="next action-button" value="Next" />
            </fieldset>
            <fieldset>
                <h2 class="fs-title" style="margin-bottom: 20px!important;">University Shortlisting</h2>
                <input type="text" name="email" placeholder="Email" />
                <input type="password" name="pass" placeholder="Password" />
                <input type="password" name="cpass" placeholder="Confirm Password" />
                <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                <input type="submit" name="submit" class="submit action-button" value="Submit" />
            </fieldset> -->
            </form>
        </div>
    </div>
<?php } ?>
<?php // init_tail();
?>


<link rel="stylesheet" type="text/css" id="vendor-css" href="https://localhost/git_crm/assets/builds/vendor-admin.css?v=2.6.0">
<!-- /.MultiStep Form -->
<!--  -->

<script>
    $(window).load(function() {
        $(".loading-upper").hide();
    });

    function show_loader(id = "") {
        if (id != '') {
            $("#" + id).attr("data-loading-text", "<i class='fa fa-spinner fa-spin '></i> Processing ");
        }
        $(".loading-upper").show();

    }

    function hide_loader(id = "") {
        if (id != '') {
            $("#" + id).button('reset');
        }
        $(".loading-upper").hide();

    }



    //jQuery time
    var applicant_status = "<?= $applicant_status ?>";
    console.log(applicant_status);
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches
    var client_id = <?= !empty($client_id) ? $client_id : '' ?>;
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>"; // Replace with the actual CSRF token value
    var step_stage = 0;

    var customer_admins = <?= !empty($customer_admins) ? json_encode($customer_admins, true) : [] ?>;
    var upload_documents_button = <?= !empty($upload_documents_button) ? json_encode($upload_documents_button, true) : [] ?>;
    var upload_documents = <?= !empty($upload_documents[0]) ? json_encode($upload_documents[0], true) : [] ?>;
    var staff_id = "<?= get_staff_user_id() ?>";
    var profile_creation_data = <?= !empty($profile_creation_data[0]) ? json_encode($profile_creation_data[0], true) : [] ?>;
    var profile_verification_button = <?= !empty($profile_verification_button) ? json_encode($profile_verification_button, true) : [] ?>;
    var document_verification = "<?= !empty($upload_documents[0]["document_status"]) ? $upload_documents[0]["document_status"] : 0 ?>";
    var profile_verification = "<?= !empty($profile_creation_data[0]["profile_status"]) ? $profile_creation_data[0]["profile_status"] : 0 ?>";
    console.log(upload_documents);
    console.log(profile_creation_data);
    var admin_ids = [];
    var check_university_status = false;
    var check_university_status_direct = false;
    var check_university_status_submit = false;
    $("document").ready(function() {
        if (document_verification != 1) {
            $("#profile_creation_div").find('input, select').prop('disabled', true).selectpicker('refresh');;
            $("#profile_creation_div").find(".edit_save_block").hide();
        }
        if (profile_verification != 1) {
            $(".add_university_div_block .university_div").find('input, select').prop('disabled', true).selectpicker('refresh');
            $("#university_div").find(".add_document_btn").hide();
            $("#university_div").find(".next.action-button").prop('disabled', true);

        }

        $("select[name='university_application_status']").each(function() {
            $(this).attr("disabled", true);
            $(this).selectpicker('refresh');
        })


        $("select[name='university_status_submit']").each(function() {
            if ($.trim($(this).val()) != "") {
                $(this).attr("disabled", true);
                $(this).selectpicker('refresh');
            }
        })


        $("#progressbar li").addClass("inactive");
        $("#progressbar li:eq(" + applicant_status + ")").removeClass("inactive").removeClass("previous").addClass("active");

        $("#progressbar li:eq(" + applicant_status + ")")
            .removeClass("inactive").removeClass("previous")
            .addClass("active")
            .prevAll().removeClass("inactive").removeClass("active")
            .addClass("previous permanent_previous");



    })


    var check_offer_letter = true;

    function check_offer_status() {

        $("#offer_div select[name='university_status_submit_offer']").each(function() {
            if ($.trim($(this).val()) != "") {
                $(this).attr("disabled", true);
                $(this).selectpicker('refresh');
            } else {
                check_offer_letter = false;
            }
        })

        if (check_offer_letter == true) {
            $("#progressbar li.active").addClass("previous");
        }

    }


    function check_profile_status() {
        var check_disabled = false;
        var promises = []; // Array to hold the promises

        $("#profile_creation_div .row.profile-div-save").each(function() {
            var hasValue = false;
            var row = $(this);



            row.find('input, select').each(function() {
                console.log($(this).val());
                if ($(this).val().length > 0) {
                    hasValue = true;
                    return false; // Exit the loop if a value is found
                }
            });

            row.find('input[type="file"][data-url]').each(function() {
                if ($(this).attr('data-url')) {
                    hasValue = true;
                    return false; // Exit the loop if a value is found
                }
            });

            var pencilIcon = row.closest('.row').find('.fa-pencil-square-o');
            var fileIcon = row.closest('.row').find('.fa-file');
            console.log(hasValue);
            if (hasValue) {
                pencilIcon.show();
                fileIcon.hide();
                row.find('input, select').prop('disabled', true);
            } else {
                pencilIcon.hide();
                fileIcon.show();
                row.find('input, select').prop('disabled', false);
                check_disabled = true;
            }

            row.find('input, select').selectpicker('refresh');

            // Create a promise for each iteration and add it to the promises array
            var promise = new Promise(function(resolve) {
                resolve(); // Resolve the promise immediately
            });
            promises.push(promise);
        });

        // Use Promise.all to wait for all promises to resolve
        Promise.all(promises).then(function() {

            if (check_disabled && profile_verification != 1) {
                $("#profile_div").find(".next").attr("disabled", true);
            } else {
                $("#profile_div").find(".next").attr("disabled", false);
            }
        });
    }


    // check_profile_status();


    $(".previous").click(function() {


        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();


        //de-activate current step on progressbar
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active").addClass("inactive");
        $("#progressbar li").eq($("fieldset").index(previous_fs)).addClass("active").removeClass("previous").removeClass("inactive");

        previous_fs.slideDown();
        current_fs.slideUp("slow");

    });

    $(".submit").click(function() {
        return false;
    })

    async function add_documents() {

        let response = await is_validate_document();
        if (response) {
            let html = `<div class="row col-md-12 document_upload_files">
                                <div class="col-md-5"><input class="col-md-5 form-control" name="document_label[]" type="input" placeholder="Enter label Name"></div>
                                <div class="col-md-5">
                                <div class="margin-bottom " >
                                <input class="col-md-5 form-control" type="file" accept="image/*,application/pdf" name="document_file[]" onchange="real_time_media_show(this)" placeholder=""></div>
                                           </div>     
                                <div class="col-md-2">
                                <button class="col-md-6 add_document remove_document_btn" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                <button class="col-md-6 add_document add_document_btn" type="button" onclick="add_documents(this)"><i class="fa fa-plus " aria-hidden="true"></i></button>
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

    function remove_document(obj) {
        $(obj).parents(".document_upload_files").remove();
        $("#upload_documents").find(".remove_document_btn").show();
        $("#upload_documents").find(".add_document_btn").hide();
        $("#upload_documents").find(".add_document_btn:last").show();
        if ($(".document_upload_files ").length == 1) {
            $(".document_upload_files ").find(".remove_document_btn").hide();
        }
    }

    function is_validate_application_status(status = 0) {
        return new Promise((resolve) => {
            if ($("#application_div select[name='university_application_status']").length > 0) {
                let check_status = true;
                let university_count = 0;
                let university_count_not = 0;
                let total_university = $("#application_div select[name='university_application_status']").length;


                $("#application_div select[name='university_application_status']").each(function() {
                    if ($(this).val() != 1) {
                        university_count_not++;
                        check_status = false;
                    } else {
                        university_count++;
                    }
                });

                total_university = ((total_university - university_count) - university_count_not);

                if (check_status) {
                    $("#university_application_status").find("input.next").attr("disabled", false);
                } else {
                    $("#university_application_status").find("input.next").attr("disabled", true);
                }

                let html = '';

                if (university_count > 0) {
                    if (university_count_not === 0) {
                        check_university_status = true;
                        check_university_status_direct = true;
                        html = '<h3 class="message-notification success">Your all university is Approved.</h3>';
                        $("#university_div").find("button.next").attr("disabled", false);

                    } else {
                        html = '<h3 class="message-notification">Your ' + university_count + ' University is Approved. ' + university_count_not + ' is Reject.';

                        if (university_count > 0) {
                            check_university_status = true;
                        }

                        if (total_university > 0) {
                            html += total_university + ' under processing.tab-content';
                        }
                        html += '</h3>';
                    }
                } else {
                    html = '<h3 class="message-notification">Your University under Processing</h3>';
                }

                $(".university_approval_message_action").html(html);
            }

            resolve(); // Resolve the promise
        });
    }

    is_validate_application_status();

    function is_validate_application() {
        return new Promise((resolve, reject) => {
            if ($("select[name='university_status_submit']").length > 0) {
                let check_status = false;
                let university_count = 0;
                let university_count_not = 0;
                let total_university = $("select[name='university_status_submit']").length;

                $("select[name='university_status_submit']").each(function() {
                    if ($(this).val() != 1) {
                        university_count_not++;
                    } else {
                        check_status = true;
                        university_count++;
                    }
                });

                if (total_university === university_count) {
                    check_university_status_submit = true;
                    $("#application_div").find("input.next").attr("disabled", false);
                    resolve(true); // Resolving the promise if all universities have a value of 1
                } else if (university_count > 0) {
                    $("#application_div").find("input.next").attr("disabled", false);
                    resolve(true); // Resolving the promise if all universities have a value of 1
                } else {
                    $("#application_div").find("input.next").attr("disabled", true);
                    reject("Some universities have not been selected"); // Rejecting the promise if some universities don't have a value of 1
                }
            } else {
                resolve(true); // Resolving the promise if there are no universities to validate
            }
        });
    }

    is_validate_application();

    $("select[name='university_status_submit']").change(function() {
        is_validate_application();
    })
    async function next_step(type, obj, step) {
        type = $.trim(type);
        step_stage = (step);
        show_loader();



        // if (type === "document_div") {
        //     if (upload_documents.document_status != undefined && upload_documents.document_status == 1) {
        //         hide_loader();
        //     } else {
        //         let isDocumentValid = await is_validate_document();
        //         if (isDocumentValid) {
        //             try {
        //                 let uploadResponse = await upload_document();
        //                 if (uploadResponse.resp_code == "RCS") {
        //                     hide_loader();
        //                     alert_float("success", uploadResponse.resp_desc);
        //                     if ($.inArray(staff_id, admin_ids) !== -1) {} else {
        //                         let html = '<h3 class="message-notification">Your Documents under Processing</h3>';
        //                         $(".document_approval_message_action").html(html);
        //                         window.reload();
        //                     }
        //                 } else {
        //                     if (uploadResponse.resp_code != undefined) {
        //                         alert_float("danger", uploadResponse.resp_desc);
        //                         hide_loader();
        //                         return false;
        //                     } else {
        //                         alert_float("danger", uploadResponse);
        //                         hide_loader();
        //                         return false;
        //                     }
        //                 }
        //             } catch (error) {
        //                 hide_loader();
        //                 console.error(error);
        //                 return false;
        //             }
        //         } else {
        //             hide_loader();
        //             return false;
        //         }
        //         return false
        //     }
        // } else if (type === "profile_div") {
        //     let html = "";
        //     if (profile_creation_data.profile_status != undefined && profile_creation_data.profile_status == 1) {
        //         hide_loader();

        //     } else {
        //         let isprofilevalid = await is_validate_profile();
        //         console.log(isprofilevalid);
        //         if (isprofilevalid) {
        //             try {
        //                 let update_profile_status = await update_profile();
        //                 if (update_profile_status.resp_code == "RCS") {
        //                     alert_float("success", update_profile_status.resp_desc);
        //                     if (profile_creation_data.profile_status == undefined && profile_creation_data.profile_status == "") {
        //                         if ($.inArray(staff_id, admin_ids) !== -1) {} else {
        //                             html = '<h3 class="message-notification">Your Profile under Processing</h3>';
        //                             $(".profile_approval_message_action").html(html);
        //                         }
        //                     }
        //                     hide_loader();
        //                     window.reload();

        //                 } else {
        //                     if (update_profile_status.resp_code != undefined) {
        //                         alert_float("danger", update_profile_status.resp_desc);
        //                         hide_loader();
        //                         return false;
        //                     } else {
        //                         alert_float("danger", update_profile_status);
        //                         hide_loader();
        //                         return false;
        //                     }
        //                 }
        //             } catch (error) {
        //                 hide_loader();
        //                 console.error(error);
        //                 return false;
        //             }
        //         }
        //         return false
        //     }
        // } else if (type === "university_div") {
        //     let isUniversityValid = await is_validate_university();
        //     if (isUniversityValid) {
        //         if (check_university_status_direct == true) {
        //             hide_loader();
        //         } else {
        //             let update_university_status = await update_university();

        //             if (update_university_status.resp_code === "RCS") {
        //                 hide_loader();
        //                 let ids = update_university_status.ids;
        //                 $(".add_university_div_block .university_div").each(function(index) {
        //                     if (ids[index] !== undefined) {
        //                         $(this).find("input[name='university_id']").val(ids[index]);
        //                     }
        //                 });

        //                 let university_list = update_university_status.university_shortlisting;
        //                 let html = '';
        //                 for (let i = 0; i < university_list.length; i++) {
        //                     html += `<div class="col-md-12 university_div_application mt-2">
        //                 <div class="col-md-3">
        //                     <input type="hidden" name="university_id" value="` + university_list[i].id + `" >
        //                     <input type="input" class="form-control" disabled value="` + university_list[i].university_name + `" >
        //                 </div> 
        //                 <div class="col-md-3">
        //                     <input type="input" class="form-control" disabled value="` + university_list[i].vendor_name + `" >
        //                 </div>
        //                 <div class="col-md-3">
        //                 <input type="input" class="form-control" disabled value="` + university_list[i].vendor_name + `" >

        //                     <?php
                                //                     echo render_select('university_application_status', $university_application_status, array('id', 'name'), "");
                                //                     
                                ?>
        //                 </div>
        //                 <div class="col-md-3">
        //                 </div>
        //             </div>`;
        //                 }
        //                 $(".application_div").html(html);

        //                 alert_float("success", update_university_status.resp_desc);

        //                 html = '<h3 class="message-notification">Your University under Processing</h3>';
        //                 $(".university_approval_message_action").html(html);
        //                 if (check_university_status == true) {
        //                     window.reload();
        //                     // hide_loader();
        //                     // current_fs.slideUp("slow");
        //                     // next_fs.slideDown("slow");
        //                 }


        //             } else {
        //                 alert_float("danger", update_university_status.resp_desc);
        //             }
        //             return false;
        //         }

        //     } else {
        //         hide_loader();
        //         return false;
        //     }
        // } else if (type === "application_div") {
        //     let validate_application_status = await is_validate_application();

        //     if (validate_application_status) {
        //         $("select[name='university_status_submit']").attr("disabled", true);
        //         let update_university_application_submit_status = await update_university_application();
        //         if (update_university_application_submit_status.resp_code === "RCS") {
        //             alert_float("success", update_university_application_submit_status.resp_desc);
        //             hide_loader();
        //         } else {
        //             hide_loader();
        //             alert_float("danger", update_university_application_submit_status.resp_desc);
        //         }

        //         $("select[name='university_status_submit']").each(function() {
        //             if ($.trim($(this).val()) != "") {
        //                 location.reload();
        //                 return;
        //             }
        //         });
        //         return false;
        //     } else {
        //         hide_loader();
        //     }

        // } else if (type === "offer_div") {
        //     let validate_offer_letter = await is_validate_offer_letter();

        //     if (validate_offer_letter) {
        //         check_offer_status
        //         let update_university_offer_status = await update_university_offer_application();

        //         if (update_university_offer_status.resp_code === "RCS") {
        //             location.reload();

        //             alert_float("success", update_university_offer_status.resp_desc);
        //             return false;
        //         } else {
        //             hide_loader();
        //             alert_float("danger", update_university_offer_status.resp_desc);
        //         }
        //         return false;
        //     } else {
        //         hide_loader();
        //     }

        // }

        let current_fs = $(obj).parent();
        let next_fs = $(obj).parent().next();

        // Activate next step on progressbar using the index of next_fs
        $("#progressbar li").removeClass("active").addClass("inactive");
        $("#progressbar li").eq($("fieldset").index(next_fs)).prevAll().removeClass("active").removeClass("inactive").addClass("previous");


        let nextIndex = $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active").removeClass("inactive").removeClass("previous");
        // $("#progressbar li.active").prevAll().addClass("previous");


        hide_loader();
        current_fs.slideUp("slow");
        next_fs.slideDown("slow");
    }

    function is_validate_offer_letter() {
        return new Promise(async function(resolve, reject) {
            try {
                $("#offer_div .university_div_application").each(async function() {
                    let offer_letter_status = $(this).find("select[name='university_status_submit_offer']").val();
                    let upload_media_status = $("option:selected", $(this).find("select[name='university_status_submit_offer']")).data("selected-file");
                    let media_file = $(this).find("input[name='offer_letter']").val();
                    let media_file_url = $(this).find("input[name='offer_letter']").data("file-name"); // Fix: Retrieve the 'href' attribute correctly
                    console.log(upload_media_status);
                    console.log(offer_letter_status);
                    if (upload_media_status == 1) {
                        if (media_file === "" && media_file_url === "") {
                            hide_loader();
                            $(this).find("input[name='offer_letter']").focus();
                            alert_float("danger", "Upload offer letter file.");
                            reject("Offer letter file is missing."); // Reject the promise if the offer letter file is missing
                            return false;
                        }
                        if (offer_letter_status == 2) {
                            try {
                                let check_condition = await is_validate_offer_condition(this); // Await the validation of offer conditions
                                hide_loader();
                                console.log(check_condition);
                                if (!check_condition) {
                                    return false;
                                }
                            } catch (error) {
                                hide_loader();
                                reject(error); // Reject the promise if offer conditions are invalid
                                return false;
                            }
                        }
                    }
                });

                resolve(true); // Resolve the promise if all validations pass
            } catch (error) {
                hide_loader();
                reject(error); // Reject the promise in case of any other errors
            }
        });
    }

    function is_validate_offer_condition(obj) {
        return new Promise(function(resolve, reject) {
            var isValid = true;

            $(obj).find(".text-area-field .text-area-field-div").each(function() {
                let condition = $(this).find("textarea[name='condition_text']").val();
                let condition_file = $(this).find("input[name='condition_file']").val();
                let condition_file_url = $(this).find("input[name='condition_file']").data("file-url");

                if (condition === '') {
                    $(this).find("textarea[name='condition_text']").focus();
                    alert_float("danger", "Upload offer letter condition.");
                    isValid = false;
                    resolve(false); // Resolve with 'false' if the offer letter condition is missing
                    return false;
                } else if (condition_file === '' && condition_file_url === "") {
                    $(this).find("input[name='condition_file']").focus();
                    alert_float("danger", "Upload offer letter condition file.");
                    isValid = false;
                    resolve(false); // Resolve with 'false' if the offer letter condition file is missing
                    return false;
                }
            });

            if (isValid) {
                resolve(true); // Resolve with 'true' if all conditions are valid
            }
        });
    }



    function is_validate_profile() {
        return new Promise((resolve, reject) => {
            let email_creation = $("#email_creation").val();
            let select_vendor = $("#select_vendor").val();
            let sop_document = $("#sop_document").val();
            let sop_document_url = $("#sop_document").attr("data-url");

            if (email_creation == "") {
                alert_float("danger", "Email Creation is required");
                reject("Email Creation is required");
            } else if (select_vendor == "") {
                alert_float("danger", "Select Vendor is required");
                reject("Select Vendor is required");
            } else if (sop_document == "" && sop_document_url == "") {
                alert_float("danger", "SOP is required");
                reject("SOP is required");
            } else {
                resolve(true);
            }
        });
    }


    function update_profile_data() {
        return new Promise(async (resolve, reject) => {
            try {
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("applicant_status", step_stage);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/update_profile_data") ?>",
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
                console.log(document_previous_url);
                console.log(document);
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

    function upload_document(status = 0) {
        return new Promise(async (resolve, reject) => {
            let upload_data = new FormData();

            $(".document_upload_files").each(function() {
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
                upload_data.append("document_file[]", document);
                upload_data.append("document_url[]", document_url);


            });
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", (step_stage - 1));

            if (status == 1) {
                upload_data.append("applicant_status", step_stage);

            }
            try {

                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/upload_documents") ?>",
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

    async function save_data(obj, type) {
        var response = [];
        if ($.trim(type.toLowerCase()) == "email") {
            if ($.trim($("#email_creation").val()) == "") {
                hide_loader();
                alert_float("danger", "Email is requried.");
                return false;
            } else {
                show_loader();
                response = await update_email();
            }
        } else if ($.trim(type.toLowerCase()) == "vendor") {
            if ($.trim($("#email_creation").val()) == "") {
                hide_loader();
                alert_float("danger", "Email is requried.");

                return false;
            } else if ($(".email_creation_block").find(".fa-pencil-square-o").is(":visible") === false) {
                hide_loader();
                alert_float("danger", "First save email.");

                return false;
            } else if ($.trim($("#select_vendor").val()) == "") {
                hide_loader();
                alert_float("danger", "Select vendor is requried.");

                return false;
            } else {
                show_loader();
                hide_loader();
                response = await update_vendor();

            }
        } else if ($.trim(type.toLowerCase()) == "sop") {
            if ($.trim($("#select_vendor").val()) == "") {
                hide_loader();
                alert_float("danger", "First select vendor is requried.");

                return false;
            } else if ($(".vendor_creation_block").find(".fa-pencil-square-o").is(":visible") === false) {
                hide_loader();
                alert_float("danger", "First save selected vendor.");

                return false;
            } else if ($.trim($("#sop_document").val()) == "" && $.trim($("#sop_document").attr("data-url") == "")) {
                hide_loader();
                alert_float("danger", "Upload sop file is requried.");

                return false;
            } else {
                show_loader();
                response = await update_sop();
                hide_loader();
            }
        }
        if (response != undefined) {
            if (response.resp_code == "RCS") {
                hide_loader();
                alert_float("success", response.resp_desc);
                $(obj).hide();
                $(obj).closest('input, select').prop('disabled', true);
                $(obj).closest('input, select').selectpicker('refresh');
                $(obj).siblings(".fa").show();

            } else {
                hide_loader();
                if (response.resp_code != undefined) {
                    alert_float("danger", response.resp_desc);
                    hide_loader();
                    return false;
                } else {
                    alert_float("danger", response);
                    hide_loader();
                    return false;
                }
            }

            check_profile_status();
        }
    }



    function update_email() {
        return new Promise(function(resolve, reject) {
            let email = $("#email_creation").val();


            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {

            } else {
                hide_loader();
                alert_float("danger", "invalid email address!");
                return (false);
            }

            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("email_creation", email);
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", 1);

            $.ajax({
                url: "<?= base_url("admin/clients/update_email_creation") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false,
                success: function(response) {
                    resolve(JSON.parse(response));
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }


    function update_vendor() {
        return new Promise(function(resolve, reject) {
            let vendor = $("#select_vendor").val();
            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("vendor", vendor);
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", 1);

            if (vendor != '') {
                $.ajax({
                    url: "<?= base_url("admin/clients/update_vendor") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        resolve(JSON.parse(response));
                    },
                    error: function(error) {
                        reject(error);
                    }
                });
            }
        });
    }

    function update_sop() {
        return new Promise(function(resolve, reject) {
            let document = $("input[name='sop_document']").prop("files")[0];
            let document_url = $("input[name='sop_document']").attr("data-url");
            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("sop_document", document);
            upload_data.append("document_url", document_url);
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", 1);

            if (document != '' || document_url != '') {
                $.ajax({
                    url: "<?= base_url("admin/clients/update_sop") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        resolve(JSON.parse(response));
                    },
                    error: function(error) {
                        reject(error);
                    }
                });
            }
        });
    }


    function edit_data(obj, type) {
        var showAlert = false; // Variable to track whether to show the alert or not

        $("#profile_div .row").each(function() {
            if ($(this).find(".fa-file").is(':visible') && $(this).find("input,select").val() != "") {
                showAlert = true;
                return false; // Exit the each loop if fa-file is visible
            }
        });

        if (showAlert) {
            alert_float("danger", "First save previous one.");
        } else {
            $(obj).hide();
            $(obj).closest(".row").find('input, select').prop('disabled', false);
            $(obj).closest(".row").find('input, select').selectpicker('refresh');
            $(obj).siblings(".fa").show();
            $("#profile_div").find(".next").attr("disabled", true);
        }

    }


    if (customer_admins.length > 0) {

        let email = $("#email_creation").val();
        let vendor = $("#select_vendor").val();
        let sop = $("#sop_document").attr("data-url");

        var html = "";
        for (var i = 0; i < customer_admins.length; i++) {
            admin_ids.push(customer_admins[i].staff_id);
        }
        console.log(staff_id);
        console.log(admin_ids);
        if ($.inArray(staff_id, admin_ids) !== -1 && upload_documents != '') {
            if (upload_documents.document_status != undefined && upload_documents.document_status == 1) {
                html = '<h3 class="message-notification ' + upload_documents.color_name + '"> Documents is ' + upload_documents.document_status_name + '</h3>';
                $(".document_upload_files").find(".add_document").hide();
                $(".document_upload_files").each(function() {
                    $(this).find("input[type='file']").hide();
                    $(".document-file-name").show();
                    $(this).find("input").attr("disabled", true);


                });
            } else {
                html = '<h3 class="message-notification">Take action on document verification ';
                $.each(upload_documents_button, function(index, item) {
                    html += ' <button class="btn btn-' + item.color + '" data-color="' + item.color + '"  data-text="' + item.name + '" type="button" onclick="update_document_status(' + item.id + ',this)">' + item.name + '</button>';
                });
                html += '</h3>';
                $("#upload_documents").find(".add_document_btn:last").show();
            }
            $(".document_approval_message_action").html(html);
        } else {
            if (upload_documents.document_status != undefined && upload_documents.document_status == 1) {
                html = '<h3 class="message-notification ' + upload_documents.color_name + '">Your Documents is ' + upload_documents.document_status_name + ' by ' + upload_documents.staffname + '</h3>';

                $(".document_upload_files").each(function() {
                    $(this).find("input[type='file']").hide();
                    $(".document-file-name").show();
                    $(this).find("input").attr("disabled", true);
                    $(".document_upload_files").find(".add_document").hide();

                });
            } else {
                if (upload_documents.document_status != undefined && upload_documents.document_status != '') {
                    html = '<h3 class="message-notification">Your Documents under Processing</h3>';
                }
                $("#upload_documents").find(".add_document_btn:last").show();

            }
            $(".document_approval_message_action").html(html);
        }





        if (profile_creation_data.profile_status != undefined) {
            let html = "";
            if ($.inArray(staff_id, admin_ids) !== -1) {
                console.log(profile_creation_data);
                if (profile_creation_data.profile_status != undefined && profile_creation_data.profile_status == 1) {
                    if (staff_id == profile_creation_data.approved_by) {
                        html = '<h3 class="message-notification ' + profile_creation_data.color_name + '"> Profile is ' + profile_creation_data.profile_status_name + ' you </h3>';
                    } else {
                        html = '<h3 class="message-notification ' + profile_creation_data.color_name + '"> Profile is ' + profile_creation_data.profile_status_name + ' by ' + profile_creation_data.staffname + '</h3>';
                    }


                    $("#profile_creation_div").find(".fa-pencil-square-o").hide();
                    $("#profile_creation_div").find(".fa-file").hide();
                } else {
                    console.log(profile_creation_data);
                    if (profile_creation_data.email == "" || profile_creation_data.vendor == "" || profile_creation_data.sop == "") {
                        html = '<h3 class="message-notification"> Profile is incompleted.</h3>';
                    } else {
                        html = '<h3 class="message-notification">Take action on profile verification ';
                        $.each(profile_verification_button, function(index, item) {
                            html += ' <button class="btn btn-' + item.color + '" data-color="' + item.color + '"  data-text="' + item.name + '" type="button" onclick="update_profile_status_btn(' + item.id + ',this)">' + item.name + '</button>';
                        });
                        html += '</h3>';
                    }

                }

                console.log("cflqwekeklnwekdfwe");
                console.log(html);
                $(".profile_approval_message_action").html(html);
                if (profile_creation_data.email != "" && profile_creation_data.vendor != "" && profile_creation_data.sop != "") {
                    $("#profile_div").find("input.next").attr("disabled", false);
                }
            } else {
                if (profile_creation_data.profile_status != undefined && profile_creation_data.profile_status == 1) {
                    html = '<h3 class="message-notification ' + profile_creation_data.color_name + '">Your Profile is ' + profile_creation_data.profile_status_name + ' by ' + profile_creation_data.staffname + '</h3>';
                    $("#profile_creation_div").find(".fa-pencil-square-o").hide();
                    $("#profile_creation_div").find(".fa-file").hide();
                } else {

                    if (profile_creation_data.email == "" || profile_creation_data.vendor == "" || profile_creation_data.sop == "") {
                        html = '<h3 class="message-notification">Your Profile is incompleted.</h3>';

                    } else {
                        html = '<h3 class="message-notification">Your Profile under Processing</h3>';
                    }
                }

                if (profile_creation_data.email != "" && profile_creation_data.vendor != "" && profile_creation_data.sop != "") {
                    $("#profile_div").find("input.next").attr("disabled", false);
                }
                $(".profile_approval_message_action").html(html);
            }
        }



    }

    async function update_document_status(status, obj) {
        try {
            const color = $(obj).data("color");
            const text = $(obj).data("text");
            const upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("document_status", status);
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", step_stage);
            show_loader();

            const response = await $.ajax({
                url: "<?= base_url("admin/clients/update_document_verification_status") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            // Handle the success response from the server
            const parsedResponse = JSON.parse(response);
            if (parsedResponse.resp_code === "RCS") {
                hide_loader();
                alert_float("success", parsedResponse.resp_desc);
                let html = `<h3 class="message-notification ${color}"> Documents is ${text}</h3>`;
                $(".document_approval_message_action").html(html);
                setTimeout(
                    function() {
                        $(obj).parents("fieldset").find("button.next").trigger("click");

                    }, 2000);
            }
        } catch (error) {
            // Handle the error response from the server
            console.error(error);
            alert_float("success", error);
        }
    }
    async function update_profile_status_btn(status, obj) {
        try {
            const color = $(obj).data("color");
            const text = $(obj).data("text");
            const upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("profile_status", status);
            upload_data.append("client_id", client_id);
            show_loader();

            const response = await $.ajax({
                url: "<?= base_url("admin/clients/update_profile_verification_status") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            // Handle the success response from the server
            const parsedResponse = JSON.parse(response);
            if (parsedResponse.resp_code === "RCS") {
                hide_loader();
                alert_float("success", parsedResponse.resp_desc);
                let html = `<h3 class="message-notification ${color}"> Profile is ${text}</h3>`;
                $(".profile_approval_message_action").html(html);
            }
        } catch (error) {
            // Handle the error response from the server
            console.error(error);
            alert_float("success", error);
        }
    }



    async function add_university_div() {
        let response = await is_validate_university();
        if (response) {
            let html = `<div class="col-md-12 university_div university_div_">
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
                                <input type="hidden" name="university_id">
                                    <select class="selectpicker from-control"  data-width="100%" name="select_university" id="" data-live-search="true">
                                    <option value="">Select University</option>
                                        <?php
                                        if (!empty($university_drop_down)) {
                                            foreach ($university_drop_down as $country_name => $university_list) {
                                                if (!empty($university_list)) {
                                        ?>
                                                    <optgroup label="<?= $country_name ?>" id="<?= $country_name ?>">
                                                        <?php
                                                        foreach ($university_list as $university_name) {
                                                            if (!empty($university_name)) {
                                                        ?>
                                                                <option value='<?= $university_name ?>'><?= $university_name ?></option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </optgroup>
                                        <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                    echo render_select('select_university_vendor', $customer_vendors, array('id', 'name')); ?>
                                </div>

                                <div class="col-md-2">
                                <button class="col-md-2 add_document remove_university_btn" type="button" style="display:none;" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                            <button class="col-md-2 add_document add_university_btn" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                    
                                </div>
                            </div>`;
            $(".add_university_div_block").append(html);
            select_reinit();
        }

        $(".university_div_").find(".remove_university_btn").show();
        $(".university_div_").find(".add_university_btn").hide();
        $(".university_div_:last").find(".add_university_btn").show();
        if ($(".university_div_").length == 1) {
            $(".university_div_").find(".remove_university_btn").hide();
        }
        // $("#university_div").find('button.next').attr("disabled", false);

    }

    function is_validate_university() {
        return new Promise((resolve, reject) => {
            $(".add_university_div_block .university_div").each(function() {
                let select_university = $(this).find("select[name='select_university']").val();
                let select_university_vendor = $(this).find("select[name='select_university_vendor']").val()

                if (select_university === undefined || $.trim(select_university) === "") {
                    $(this).find("select[name='select_university']").focus();
                    alert_float("danger", "Select university is requried.");
                    resolve(false);
                    return;
                }

                if ((select_university_vendor === undefined || $.trim(select_university_vendor) === "")) {
                    $(this).find("select[name='select_university_vendor']").focus();
                    alert_float("danger", "Select vendor is requried.");
                    resolve(false);
                    return;
                }

                // Additional validation logic or processing can be added here
            });

            resolve(true); // Resolving the promise if all validations pass
        });
    }

    function select_reinit() {
        $(".selectpicker").selectpicker('refresh');
    }

    function remove_university_div(obj) {
        $(obj).parents(".university_div").remove();
        $(".university_div").find(".remove_university_btn").show();
        $(".university_div").find(".add_university_btn").hide();
        $(".university_div:last").find(".add_university_btn").show();
        if ($(".university_div").length == 1) {
            $(".university_div").find(".remove_university_btn").hide();
        }
        is_validate_application_status();
    }


    function update_university() {
        return new Promise(async (resolve, reject) => {
            let upload_data = new FormData();
            let university_shortlisting = [];
            let universityVendorMap = {};
            let stop_status = true;
            $(".add_university_div_block .university_div").each(function() {
                let university_id = $(this).find("input[name='university_id']").val();
                let select_university = $(this).find("select[name='select_university']").val();
                let select_university_vendor = $(this).find("select[name='select_university_vendor']").val();

                if (university_id === undefined) {
                    university_id = "";
                }

                // Check for duplicate university and vendor
                const duplicateEntry = university_shortlisting.find(entry => entry.university === select_university && entry.vendor === select_university_vendor);
                if (duplicateEntry) {
                    hide_loader();
                    alert_float("danger", "Duplicate entry found: university '" + select_university + "' connected with multiple vendors.");
                    stop_status = false;
                    return false;
                }

                university_shortlisting.push({
                    "university": select_university,
                    "vendor": select_university_vendor,
                    "university_id": university_id
                });

                // Update universityVendorMap
                if (!universityVendorMap.hasOwnProperty(select_university_vendor)) {
                    universityVendorMap[select_university_vendor] = select_university_vendor;
                }


            });

            if (stop_status) {
                upload_data.append("university_shortlisting", JSON.stringify(university_shortlisting));
                try {

                    upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                    upload_data.append("client_id", client_id);
                    upload_data.append("applicant_status", (step_stage - 1));
                    let response = await $.ajax({
                        url: "<?= base_url("admin/clients/update_university") ?>",
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
            }
        });
    }


    function update_profile() {
        return new Promise(function(resolve, reject) {

            let upload_data = new FormData();
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", client_id);
            upload_data.append("applicant_status", (step_stage - 1));
            $.ajax({
                url: "<?= base_url("admin/clients/update_profile") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false,
                success: function(response) {
                    resolve(JSON.parse(response));
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }



    $(".university_div_").find(".remove_university_btn").show();
    $(".university_div_").find(".add_university_btn").hide();
    $(".university_div_:last").find(".add_university_btn").show();
    if ($(".university_div_").length == 1) {
        $(".university_div_").find(".remove_university_btn").hide();
    }

    function update_university_offer_application() {
        return new Promise(async (resolve, reject) => {
            try {
                let upload_data = new FormData();
                let university_shortlisting_status = [];
                let universityVendorMap = {};
                let stop_status = true;
                $("#offer_div").find("input,textarea,select").prop("disabled", false);
                $("#offer_div .university_div_application").each(function() {
                    let offer_letter_status = $(this).find("select[name='university_status_submit_offer']").val();
                    let upload_media_status = $("select[name='university_status_submit_offer'] option:selected", this).data("selected-file");
                    let media_file = $(this).find("input[name='offer_letter']").prop("files")[0];
                    let media_file_url = $(this).find("input[name='offer_letter']").data("file-name");
                    let university_id = $(this).find("input[name='university_id']").val();
                    let university_status = $(this).find("input[name='university_status']").val();
                    let conditional_notes = $(this).find(".conditional_textarea").val();

                    if (upload_media_status == "" || upload_media_status == undefined) {
                        upload_media_status = 0;
                    }
                    upload_data.append("offer_letter_status[]", offer_letter_status);
                    upload_data.append("media_file[" + university_id + "]", media_file);
                    upload_data.append("media_file_status[]", upload_media_status);
                    upload_data.append("media_file_url[" + university_id + "]", media_file_url);
                    upload_data.append("university_id[]", university_id);
                    upload_data.append("university_status[]", university_status);
                    upload_data.append("conditional_notes[]", conditional_notes);
                    console.log("media_condition_type", upload_media_status);
                    if (offer_letter_status == 2) {
                        $(this).find(".text-area-field .text-area-field-div").each(function() {
                            let condition = $(this).find("textarea[name='condition_text']").val();
                            let condition_file = $(this).find("input[name='condition_file']").prop("files")[0];
                            let media_url = $(this).find("input[name='condition_file']").data("file-url");
                            let condition_id = $(this).find("input[name='condition_id']").data("condition-id");

                            if (condition_id == undefined || condition_id == "") {
                                condition_id = "";
                            }
                            let media_name = Math.floor(Date.now() / 1000) + "_" + Math.floor(Math.random() * 1001);
                            let conditional_array = {
                                university_id: university_id,
                                condition: condition,
                                condition_id: condition_id,
                                university_status: university_status,
                                media_url: media_url,
                                media_name: media_name
                            };
                            upload_data.append("conditional_array[]", JSON.stringify(conditional_array));
                            upload_data.append("conditional_media_file[" + media_name + "]", condition_file);
                        });
                    }
                });

                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("client_id", client_id);
                upload_data.append("applicant_status", step_stage);
                $("#offer_div").find("input,textarea,select").prop("disabled", true);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/update_university_offer_status") ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false
                });

                resolve(JSON.parse(response)); // Resolve the promise with the parsed JSON response

            } catch (error) {
                console.error(error);
                reject(error); // Reject the promise with the error
            }
        });
    }


    function update_university_application() {
        return new Promise(async (resolve, reject) => {
            console.log("start");
            let upload_data = new FormData();
            let university_shortlisting_status = [];
            let universityVendorMap = {};
            let stop_status = true;
            $(".application_div .university_div_application ").each(function() {
                let university_id = $(this).find("input[name='university_id']").val();
                let university_status_submit = $(this).find("select[name='university_status_submit']").val();
                university_shortlisting_status.push({
                    "university_id": university_id,
                    "university_status_submit": university_status_submit
                });
            });
            upload_data.append("university_shortlisting_status", JSON.stringify(university_shortlisting_status));
            try {
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("client_id", client_id);
                upload_data.append("applicant_status", (step_stage));
                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/update_university_status") ?>",
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
    $("select[name='university_status_submit_offer']").change(function() {
        let upload_media = $("option:selected", this).data("selected-file");
        $(this).parents(".university_div_application").find(".text-area-field").hide();
        $(this).parents(".university_div_application").find(".text-area-field").find(".text-area-field-div").eq(1).remove();
        $(this).parents(".university_div_application").find(".text-area-field").find(".text-area-field-div").find('input, select,texarea').val("").selectpicker('refresh');
        if (upload_media == 1) {
            if ($("option:selected", this).val() == 2) {
                $(this).parents(".university_div_application").find(".text-area-field").show();
                // $(this).parents(".university_div_application").find(".text-area-field").find(".text-area-field-div").eq(1).next().remove();
                // $(this).parents(".university_div_application").find(".text-area-field").find(".text-area-field-div").find('input, select').prop('disabled', true).selectpicker('refresh');
            }
            $(this).parents(".university_div_application").find("input[type='file']").prop("disabled", false);
        } else {
            $(this).parents(".university_div_application").find("input[type='file']").val('');
            $(this).parents(".university_div_application").find("input[type='file']").prop("disabled", true);
        }
    });


    function real_time_media_show(input) {
        var file = input.files[0];

        if (file) {
            var $parent = $(input).parents(".document_upload_files");
            $parent.find(".media-text-div").remove();

            var mediaTextDiv = $('<div class="row media-text-div">' +
                '<div class="col-md-10">' +
                '<p class="document-file-name">' + file.name + '</p>' +
                '</div>' +
                '<div class="col-md-2 file-download-block">' +
                '<a class="col-md-12 download_document" accept="image/*,application/pdf" download href="' + URL.createObjectURL(file) + '" type="button">' +
                '<i class="fa fa-download" aria-hidden="true"></i>' +
                '</a>' +
                '</div>' +
                '</div>');

            $(input).after(mediaTextDiv);
        } else {
            $(input).parents(".document_upload_files").find(".media-text-div").remove();

        }
    }

    function real_time_media_show_sop(input) {
        var file = input.files[0];

        if (file) {
            $(".media-text-div-other").remove();;
            var mediaTextDiv = $('<div class="row media-text-div-other">' +
                '<div class="col-md-10">' +
                '<p class="document-file-name">' + file.name + '</p>' +
                '</div>' +
                '<div class="col-md-2 file-download-block">' +
                '<a class="col-md-12 download_document" accept="image/*,application/pdf" download href="' + URL.createObjectURL(file) + '" type="button">' +
                '<i class="fa fa-download" aria-hidden="true"></i>' +
                '</a>' +
                '</div>' +
                '</div>');

            $(input).after(mediaTextDiv);
        } else {
            $(".media-text-div-other").remove();

        }
    }

    function real_time_media_show_offer(input) {
        var file = input.files[0];

        if (file) {
            $(input).parent("div").find(".media-text-div-offer").remove();
            var mediaTextDiv = $('<div class="row media-text-div-offer">' +
                '<div class="col-md-10">' +
                '<p class="document-file-name">' + file.name + '</p>' +
                '</div>' +
                '<div class="col-md-2 file-download-block">' +
                '<a class="col-md-12 download_document" accept="image/*,application/pdf" download href="' + URL.createObjectURL(file) + '" type="button">' +
                '<i class="fa fa-download" aria-hidden="true"></i>' +
                '</a>' +
                '</div>' +
                '</div>');

            $(input).after(mediaTextDiv);
        } else {
            $(".media-text-div-offer").remove();

        }
    }


    function real_time_media_show_offer_condition(input) {
        var file = input.files[0];

        if (file) {
            $(input).parent("div").find(".media-text-div-offer-condition").remove();
            var mediaTextDiv = $('<div class="row media-text-div-offer-condition">' +
                '<div class="col-md-10">' +
                '<p class="document-file-name">' + file.name + '</p>' +
                '</div>' +
                '<div class="col-md-2 file-download-block">' +
                '<a class="col-md-12 download_document" accept="image/*,application/pdf" download href="' + URL.createObjectURL(file) + '" type="button">' +
                '<i class="fa fa-download" aria-hidden="true"></i>' +
                '</a>' +
                '</div>' +
                '</div>');

            $(input).after(mediaTextDiv);
        } else {
            $(input).find(".media-text-div-offer-condition").remove();

        }
    }

    async function add_condition_div(obj, id) {

        let check_condition = await is_validate_offer_condition($(obj).parents(".university_div_application"));
        console.log(check_condition);
        if (check_condition) {
            html = `<div class="row text-area-field-div u_s_l_` + id + `">
                <div class="col-md-1">Condition</div>
                <div class="col-md-6"><textarea placeholder="Write conditions ...... " class="conditional_textarea form-control" name="condition_text"></textarea></div>
                <div class="col-md-3"><input type="file" class="form-control" onchange="real_time_media_show_offer_condition(this)" name="condition_file" accept="image/*,application/pdf"></div>
                <div class="col-md-2">
                    <button class="col-md-2 add_document remove_condition_btn" type="button" style="" onclick="remove_condition_div(this,` + id + `)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                    <button class="col-md-2 add_document add_condition_btn" type="button" onclick="add_condition_div(this,` + id + `)"><i class="fa fa-plus" aria-hidden="true"></i></button>
                </div>
            </div>`;

            var parentDiv = $(obj).parents(".university_div_application");
            parentDiv.find(".text-area-field").append(html);

            var textAreaFieldDiv = parentDiv.find(".text-area-field-div");
            textAreaFieldDiv.find(".remove_condition_btn").show();
            textAreaFieldDiv.find(".add_condition_btn").hide();
            textAreaFieldDiv.last().find(".add_condition_btn").show();

            if (textAreaFieldDiv.length == 1) {
                textAreaFieldDiv.find(".remove_condition_btn").hide();
            }
        }
    }


    function remove_condition_div(objj, id) {

        $(objj).parents(".text-area-field-div").remove();
        $(".u_s_l_" + id).find(".remove_condition_btn").show();
        $(".u_s_l_" + id).find(".add_condition_btn").hide();
        $(".u_s_l_" + id).last().find(".add_condition_btn").show();
        // $(objj).parents(".university_div_application").find(".text-area-field .text-area-field-div .remove_condition_btn").show();
        // $(objj).parents(".university_div_application").find(".text-area-field .text-area-field-div .add_condition_btn").hide();
        // $(objj).parents(".university_div_application").find(".text-area-field .text-area-field-div:last .add_condition_btn").show();
        // console.log($(objj).parents(".university_div_application .text-area-field .text-area-field-div").length);
        // if ($(objj).parents(".university_div_application .text-area-field .text-area-field-div").length == 1) {
        //     $(objj).parents(".university_div_application").find(".text-area-field-div").find(".add_condition_btn").show();
        //     $(objj).parents(".university_div_application").find(".text-area-field-div").find(".remove_condition_btn").hide();
        // }

        console.log($(".u_s_l_" + id).length);
        if ($(".u_s_l_" + id).length == 1) {
            $(".u_s_l_" + id).find(".remove_condition_btn").hide();
            $(".u_s_l_" + id).find(".add_condition_btn").show();
        }

    }
    $(document).ready(function() {
        check_offer_status();

        $(".university_status_check").each(function() {
            if ($(this).val() == 1) {
                $(this).parents(".university_div").find('input, select').prop('disabled', true).selectpicker('refresh');
            }
        })
        $("body").find("input,select").attr("disabled", true).selectpicker('refresh');
        $("body").find("input[type=button]").attr("disabled", false);

    })
</script>