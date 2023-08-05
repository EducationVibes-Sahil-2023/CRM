<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$applicant_tracker = applicant_tracker();
$applicant_status = !empty($client->applicant_status) ? $client->applicant_status : 0;
$profile_creation_data = !empty($profile_creation_data) ? $profile_creation_data : "";

?>
<style>
    /*basic reset*/
    * {
        margin: 0;
        padding: 0;
    }

    textarea#note_data {
        height: 70px;
        resize: none;
    }

    .table-loading table thead tr {
        min-height: 44px;
        height: 44px;
    }

    .table-loading {
        background: none !important;
    }

    html {
        height: 100%;
        background: #eee;
    }

    .dt-table-loading.table,
    .table-loading .dataTables_filter,
    .table-loading .dataTables_length,
    .table-loading .dt-buttons,
    .table-loading table tbody tr,
    .table-loading table thead th {
        opacity: none !important;
        opacity: unset !important;
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



    #university_div .add_university_div_block .university_div {
        padding: 10px;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .university_div.bg-success {
        background-color: #dff0d8;
    }

    .university_div.bg-danger {
        background-color: #f2dede;
    }

    .university_div.bg-warning {
        background-color: #fcf8e3;
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

    .note_activity_section {
        margin-top: 20px;
        background: white;
        border: 0 none;
        border-radius: 8px;
        box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
        padding: 20px 30px;
        box-sizing: border-box;
        width: 100%;
        /* margin: 0 10%; */
        position: relative;
    }

    .table-application-notes thead tr th {
        width: 100%;
    }

    .lead-note .note-box {
        margin-left: 1%;
        width: 98%;
        /* text-align: center; */
        margin-bottom: 10px;
        margin-top: 5px;
        padding: 10px;
        box-shadow: 1px 1px 5px -1px black;
        border-radius: 10px;
    }

    .btn-toggle {
        margin: 0 7rem;
        padding: 0;
        position: relative;
        border: none;
        height: 1.5rem;
        width: 3rem;
        border-radius: 1.5rem;
        color: #6b7381;
        background: #bdc1c8;
    }

    .btn-toggle:focus,
    .btn-toggle.focus,
    .btn-toggle:focus.active,
    .btn-toggle.focus.active {
        outline: none;
    }

    .btn-toggle:before,
    .btn-toggle:after {
        line-height: 1.5rem;
        width: 4rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        position: absolute;
        bottom: 0;
        transition: opacity 0.25s;
    }

    .btn-toggle:before {
        content: 'Notes';
        left: -7rem;
    }

    .btn-toggle:after {
        content: 'Activity';
        right: -5rem;
        opacity: 0.5;
    }

    .btn-toggle:before,
    .btn-toggle:after {
        color: #6b7381;
    }

    .btn-toggle.active {
        background-color: #29b5a8;
    }

    .btn-toggle>.handle {
        position: absolute;
        top: 0.1875rem;
        left: 0.1875rem;
        width: 1.125rem;
        height: 1.125rem;
        border-radius: 1.125rem;
        background: #fff;
        transition: left 0.25s;
    }

    .btn-toggle.active {
        transition: background-color 0.25s;
    }

    .btn-toggle.active>.handle {
        left: 1.6875rem;
        transition: left 0.25s;
    }

    .btn-toggle.active:before {
        opacity: 0.5;
    }

    .btn-toggle.active:after {
        opacity: 1;
    }

    small.note-edit {
        /* border: 0px solid green; */
        margin-left: 10px;
        color: green;
        padding: 2px 10px;
        font-weight: 700;
        border-radius: 10px;
        box-shadow: 1px 1px 4px 0px;
    }

    .document_approval_message_action,
    .university_approval_message_action,
    .profile_approval_message_action,
    .acceptance_text_approval_message_action {
        display: inline-block !important;
    }

    .acceptance_text_approval_message_action {
        padding-top: 14px;
        margin: 0px;
    }

    .hide-fee-div {
        border-top: 1px solid #b4b4b4;
        /* background: black; */
        margin-top: 25px;
        line-height: 10px;
    }


    #offer_div .university_div_application.bg-success {
        background-color: #dff0d8;
    }

    /* .university_div.bg-danger {
        background-color: #f2dede;
    }

    .university_div.bg-warning {
        background-color: #fcf8e3;
    } */
</style>
<!-- MultiStep Form -->
<?php

if (empty($customer_admins)) { ?>
    <h2 class='text-center'><?= _l("no_admin_assign_tracker") ?></h2>
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
                        <h2 class="fs-title" style="margin-bottom: 20px!important;"><?= !empty($track["name"]) ? $track["name"] : 'Document' ?>
                            <?php if ($track["show_div_name"] == "document_div") { ?> <button class="col-md-2 add_document add_document_btn float-right" style="display:block!important;" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button> <?php } else if ($track["show_div_name"] == "university_div") { ?>
                                <button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" style="display:none;" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>

                            <?php } ?>

                        </h2>

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
                                                <?php if (!empty($docs['document_file'])) { ?>
                                                    <div class="row media-text-div">
                                                        <div class="col-md-10">
                                                            <p class="document-file-name"><?= $file_name ?></p>
                                                        </div>
                                                        <div class="col-md-2 file-download-block">
                                                            <a class="col-md-12 download_document" onclick="window.open(`<?= base_url($docs['document_file']) ?>`, '_blank');" href="javascript:void(0);" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                            </div>

                                            <div class="col-md-2">
                                                <!-- <a class="col-md-2 download_document" download href="<?= base_url($docs["document_file"]) ?>" type="button"><i class="fa fa-download" aria-hidden="true"></i></a> -->

                                                <button class="col-md-2 add_document remove_document_btn" type="button" onclick="remove_document(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>

                                                <!-- <button class="col-md-2 add_document add_document_btn" style="display:none;" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button> -->
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
                                                <!-- <button class="col-md-2 add_document add_document_btn" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button> -->
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
                                    <?php if ($profile_creation_data[0]["email_verified"] != 1) : ?>
                                        <div class="col-md-4 edit_save_block email_creation_block">
                                            <i class="fa fa-pencil-square-o fa-pencil-square-o-hide col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
                                            <i class="fa fa-file fa-file-hide col-md-1" style="display:none;" onclick="save_data(this,'email')"></i>
                                        </div>
                                    <?php else : ?>
                                        <div class="col-md-4 edit_save_block email_creation_block">
                                            <i class="fa fa-pencil-square-o fa-pencil-square-o col-md-1" style="display:none; cursor:not-allowed!important"></i>
                                        </div>
                                    <?php endif; ?>

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
                                        <i class="fa fa-pencil-square-o  col-md-1" style="display:none;" onclick="edit_data(this,1)"></i>
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
                                                <?php if (!empty($profile_creation_data[0]['sop'])) { ?>
                                                    <div class="row mt-5 margin-bottom">
                                                        <div class="col-md-10">
                                                            <p class="document-file-name"><?= $file_name ?></p>
                                                        </div>
                                                        <div class="col-md-2 file-download-block">
                                                            <a class="col-md-12 download_document" href="javascript:void(0);" onclick="window.open(`<?= base_url($profile_creation_data[0]['sop']) ?>`, '_blank');" type=" button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                        </div>
                                                    </div>
                                                <?php } ?>

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
                                        <div class="col-md-12 university_div <?= ($short_list["university_status"] == 1) ? '' : 'university_div_'; ?>   bg-<?= ($short_list["university_status"] == 1) ? 'success' : (($short_list["university_status"] == 2) ? 'danger' : 'warning') ?>">
                                            <div class="col-md-2">

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
                                                    <!-- <button class="col-md-2 add_document add_university_btn" style="display:none;" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button> -->
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
                                            <!-- <button class="col-md-2 add_document add_university_btn" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button> -->
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

                                        $fees_file_name = "";
                                        if (!empty($short_list["fee_media"])) {
                                            $fees_file_name =  trim(explode("_", basename($short_list["fee_media"]))[2]);
                                        }
                                ?>
                                        <div class="col-md-12 university_div_application mt-2 <?= $short_list["fee_status"] == 1 ? "bg-success pre-select-offer" : '' ?>">
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
                                                <input type="file" disabled data-file-name="<?= !empty($short_list["media_file"]) ? $short_list["media_file"] : '' ?>" class="form-control" id="offer_letter" accept="images/*,application/pdf" onchange="real_time_media_show_offer(this)" name="offer_letter">
                                                <?php if (!empty($short_list['media_file'])) { ?>
                                                    <div class="row media-text-div-offer">
                                                        <div class="col-md-8">
                                                            <p class="document-file-name"><?= $file_name ?></p>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <?php if (!empty($short_list["media_file"])) { ?>
                                                                <a class="col-md-12 download_document" accept="image/*,application/pdf" href="javascript:void(0);" onclick="window.open(`<?= base_url($short_list['media_file']) ?>`, '_blank');" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                            <?php }
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
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
                                                                <?php if (!empty($con['file'])) { ?>
                                                                    <div class="row media-text-div-offer-condition">
                                                                        <div class="col-md-8">
                                                                            <p class="document-file-name"><?= $file_name ?></p>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <?php if (!empty($con["file"])) { ?>
                                                                                <a class="col-md-12 download_document" accept="image/*,application/pdf" href="javascript:void(0);" onclick="window.open(`<?= base_url($con['file']) ?>`, '_blank');" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                                            <?php }
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>

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

                                            <div class="col-lg-12 hide-fee-div" style="display:<?= $short_list["fee_status"] == 1 ? "block" : 'none' ?>">
                                                <h3 class="text-center mb-5 p-5">Upload Fees Slip</h3>
                                                <div class="col-lg-6">

                                                    <input type="file" class="form-control" name="university_<?= $short_list['id'] ?>_file">
                                                    <?php if (!empty($short_list['fee_media'])) { ?>
                                                        <div class="row">
                                                            <div class="col-md-10">
                                                                <p class="document-file-name"><?= $fees_file_name ?></p>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <?php if (!empty($fees_file_name)) { ?>
                                                                    <a class="col-md-12 download_document" accept="image/*,application/pdf" onclick="window.open(`<?= base_url($short_list['fee_media']) ?>`, '_blank');" href="javascript:void(0);" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                                <?php }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-lg-6">
                                                    <button class="btn btn-success" onclick="fee_upload(this)">Fee Submitted</button>
                                                </div>
                                            </div>
                                            <?php if ($short_list["fee_status"] == 1) {
                                                $selected_university_name = $short_list["university_name"];
                                            ?>
                                                <div class="col-lg-12 hide-fee-div- " style="margin-top:5px; padding-top:10px;">
                                                    <div class="col-lg-4"></div>
                                                    <div class="col-lg-4">

                                                        <button type="button" <?= (!empty($short_list["acceptance_status"]) && $short_list["acceptance_status"] == 1) ? "disabled" : '' ?> class="btn btn-success" onclick="validate_fees(1,<?= $short_list['id'] ?>)">Approved</button>
                                                    </div>
                                                    <div class="col-lg-4"></div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php }
                                    ?>

                                <?php } ?>

                            </div>
                        <?php } else if ($track["show_div_name"] == "fees_div") { ?>
                            <div class="fees_div">
                                <h2 class="text-center text-success">Congratulations !!!!</h2>
                                <h4 class="text-success">
                                    The applicant's payment has been successfully submitted under the <?= !empty($selected_university_name) ? $selected_university_name : '' ?>
                                </h4>
                            </div>
                        <?php } ?>
                        <?php if ($k > 0) { ?>
                            <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                        <?php } ?>
                        <?php
                        if (($k + 1) < count($applicant_tracker)) { ?>
                            <input type="button" name="next" class="next action-button next-<?= $track ?>" onclick="next_step('<?= $track['show_div_name'] ?>',this,<?= $track['orderby'] ?>)" value="Save & Next" />
                        <?php } else if (($k + 2) == count($applicant_tracker)) {  ?>
                            <input type="button" name="next" class="next action-button next-<?= $track ?>" onclick="next_step('<?= $track['show_div_name'] ?>',this,<?= $track['orderby'] ?>)" value="Update" />
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

            <section class="note_activity_section mt-5">
                <div class="col-12 text-right" style="margin:25px;"><button type="checked" class="btn btn-lg btn-toggle btn-switch-toggle" data-toggle="button" aria-pressed="false" autocomplete="off">
                        <div class="handle"></div>
                    </button></div>
                <div class="note_section">
                    <div class="parrent-div">
                        <div class="panel-body">
                            <div class="create_notes row" style="margin-bottom:30px;">
                                <input type="hidden" id="notes_id">
                                <div class="col-md-9"><textarea id="note_data" class="form-control"></textarea></div>
                                <div class="col-md-3"><button class="btn btn-primary" onclick="create_notes()">Update Notes</button></div>
                            </div>
                            <h4>Notes</h4>
                            <div class="media lead-note">

                            </div>
                        </div>
                        <div class="panel-body lead-modal" style="display:none;">
                            <h4>Activity</h4>
                            <div class="media lead-activity activity-feed">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
<?php } ?>
<?php init_tail(); ?>
<!-- /.MultiStep Form -->
