<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$staff_list              = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list, null, "staffid");

$applicant_tracker = applicant_tracker_mbbs($lead_type_status);
$applicant_status = !empty($client->tracker_id) ? $client->tracker_id : 0;
$profile_creation_data = !empty($profile_creation_data) ? $profile_creation_data : "";
$university_partner_names = get_university_partner_names();
// $documents_type =  get_documents($lead_type_status, [], 1);
$delete_document_status = has_permission('customers', '', 'delete_documents');
$documents_type =  get_documents($lead_type_status, !empty($admissionpreferences->study_country) ? explode(",", $admissionpreferences->study_country) : [], 1);

$documents_type_dropdown = $documents_type =  array_column($documents_type, null, 'id');
$applicant_documents =  get_clients_documents($client_id);
$visa_details =  visa_details($client_id, 0, 1);
$visa_vendors = get_vendor_list(2);
$courier_type = get_courier_list();
$payment_mode = get_payment_mode();

if (!empty($applicant_documents[0]["data"])) {
    $applicant_documents = json_decode($applicant_documents[0]["data"], true);

    if (!empty($applicant_documents)) {
        $applicant_documents = array_column($applicant_documents, null, "id");
    }
}

$staff_id = array_column($customer_admins, "staff_id");
$final_sumbit = $client->submission_status;
$read_only = "readonly";
if (is_admin()) {
    $final_sumbit = 0;
    $read_only = "";
}
if (in_array(get_staff_user_id(), $staff_id)) {
    $final_sumbit = 0;
    $read_only = "";
}

?>
<style>
    /*basic reset*/
    * {
        margin: 0;
        padding: 0;
    }

    .margin-top {
        margin-top: 10px;
    }

    li.col-md-3.checkbox-select-doc.d-flex.align-items-center {
        padding: 10px 0px;
    }

    i.fa.btn.btn-xs {
        height: 30px;
        line-height: 20px;
        margin-right: 3px;
        margin-left: 3px;
        /* margin: -1px; */
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
        /* text-align: center; */
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
        cursor: pointer;
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

    .application_div div.university_div_application,
    .visa_div_application,
    .entrance_exam_university_div,
    .legalization-item,
    .feesDeposite-item,
    .invitation-item {
        margin-top: 10px !important;
        margin-top: 30px !important;
        /* border: 1px solid black; */
        box-shadow: 1px 0px 5px 1px lightgrey;
        padding: 20px 10px;
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
    .list-autocomplete {
        padding: 0;
    }

    .list-autocomplete em {
        font-style: normal;
        background-color: #e1f2f9;
    }

    .hasNoResults {
        color: #aaa;
    }

    .hasNoResults {
        display: block;
        padding: 30px 15px;
    }

    .hasNoResults {
        color: #aaa;
    }

    .dropdown-menu {
        padding: 0;
    }

    .dropdown-item {
        background: transparent;
        border: none;
        width: 100%;
        text-align: left;
        padding: 5px 5px;
        border-top: 1px solid;
    }

    .dropdown-menu hr {
        margin: 0;
    }
</style>
<!-- MultiStep Form -->
<?php
if ($client->submission_status != 1) {
?>
    <h2 class='text-center'>No final submission from counselor.</h2>
<?php
    die;
}
// if (empty($customer_admins)  && !is_admin()) { 
?>
<!--<h2 class='text-center'></h2>-->
<?php

// } else {

if (empty($staff_list[get_staff_user_id()]["post_sales"]) && !is_admin()) {
?>
    <h2 class="text-center">Applicant Tracker - Accessible Only for Post-Sale & Admin</h2>
<?php
}
?>
<div class="row">
    <div id="msform" class="col-md-12 ">
        <!-- <form id="msform" onsubmit="return false;"> -->
        <ul id="progressbar" class="d-flex justify-content-center">
            <?php
            foreach ($applicant_tracker as $key => $track) {
            ?>
                <li data-id="<?= $track['id'] ?>" onclick="goToStep(<?= $key ?>)" data-show="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '' ?>"><?= $track["name"] ?></li>
            <?php
            }
            ?>
        </ul>
        <?php


        if ($client_infomation->active == 4 || $client_infomation->active == 2) {
        ?>
            <?php if ($client_infomation->active == 4) { ?>
                <section>
                    <fieldset id="refund_stage">
                        <h2 class="fs-title text-center mb-4">Refund Stage</h2>
                        <div class="row margin-top">

                            <!-- Refund Date -->
                            <div class="col-md-4 mb-3 margin-top ">
                                <label class=" mb-2 margin-top"><b>Refund Date</b></label>
                                <div><?= $client_infomation->refund_payment_date ?></div>
                            </div>

                            <!-- Refund Payment Proof -->
                            <div class="col-md-4 mb-3 margin-top">
                                <label class=" mb-2 margin-top"><b>Refund Payment Proof</b></label>
                                <div>
                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url() . $client_infomation->refund_payment_proof ?>');"></i>&nbsp;
                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url() . $client_infomation->refund_payment_proof ?>', '_blank');"></i>
                                </div>
                            </div>

                            <!-- Comment -->
                            <div class="col-md-4 mb-3 margin-top">
                                <label class=" mb-2 margin-top"><b>Comment</b></label>
                                <div><?= $client_infomation->canceled_comment ?></div>
                            </div>

                        </div>
                    </fieldset>
                </section>
            <?php } ?>

            <?php if ($client_infomation->active == 2) { ?>
                <section>
                    <fieldset id="refund_stage">
                        <h2 class="fs-title text-center mb-4">Cancel Stage</h2>
                        <div class="row margin-top">

                            <div class="col-md-12 margin-top">
                                <label class=" mb-2 margin-top"><b>Comment</b></label>
                                <div><?= $client_infomation->canceled_comment ?></div>
                            </div>

                        </div>
                    </fieldset>
                </section>
            <?php } ?>

        <?php
        } else {
        ?>

            <section style="display:<?= (empty($staff_list[get_staff_user_id()]["post_sales"]) && !is_admin()) ? 'none' : 'block' ?>">
                <?php
                foreach ($applicant_tracker as $k => $track) {

                ?>
                    <fieldset id="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '12' ?>" style="display:<?= ($applicant_status == $k) ? "show" : "none" ?>">
                        <h2 class="fs-title text-center" style="margin-bottom: 20px!important;"><?= !empty($track["name"]) ? $track["name"] : 'Document' ?>
                            <?php
                            if ($track["show_div_name"] == "university_div") { ?>
                                <button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" style="display:none;" type="button" onclick="add_university_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>

                            <?php } ?>
                            <?php
                            if ($track["show_div_name"] == "visa_div") { ?>
                                <button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" style="display:none;" type="button" onclick="add_visa_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>

                            <?php } ?>
                        </h2>
                        <?php if ($track["show_div_name"] == "document_div") { ?>

                            <div class="text-right">

                                <div class="registration-slip-invoice">
                                    <?php if (!empty($client_infomation->registration_slip_invoice)) { ?>
                                        <label>Registration Slip</label>
                                        <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url() . $client_infomation->registration_slip_invoice ?>');"></i>&nbsp;
                                        <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url() . $client_infomation->registration_slip_invoice ?>', '_blank');"></i>
                                    <?php } ?>
                                </div>

                                <button type="button" class="btn btn-primary btn-xs" onclick="registration_slip_generate(<?= $client_id ?>,1)">Generate Registration Slip </button>
                                <?= getLastEmailWhatsappDate("whatsapp", 1, $client_id) ?><button type="button" class="btn btn-primary btn-xs" onclick="registration_slip_generate(<?= $client_id ?>,0,1)"><i class="fa fa-whatsapp hide-client-type"></i> </button>
                                <?= getLastEmailWhatsappDate("email", REGISTRATION_TEMPLATE_ID, $client_id) ?><button type="button" class="btn btn-primary btn-xs" onclick="registration_slip_generate(<?= $client_id ?>,0,0,1)"><i class="fa fa-envelope hide-client-type"></i> </button>
                            </div>
                            <div id="upload_documents" class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark ">
                                        <tr class="">
                                            <th scope="col">S.No</th>
                                            <th scope="col">Document Type</th>
                                            <th scope="col">Stage</th>

                                            <th scope="col">Approved By</th>
                                            <th scope="col">Approved Date</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Upload By</th>
                                            <th scope="col">Upload Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="document_upload_div">

                                        <?php if (!empty($documents_type)) : ?>
                                            <?php
                                            $index = 1;
                                            foreach ($documents_type as $key => $doc_files) :
                                                $doc_type = $doc_files["name"] ?? '';
                                                $doc_id = $doc_files["id"] ?? '';
                                                $info = $doc_files["info"] ?? '';
                                                $accept = $doc_files["file_type"] ?? '';
                                                $is_mandatory = !empty($doc_files["mandatry"]);
                                                $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                                $required_attr = $is_mandatory ? "required required-check" : '';
                                                $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                                $required_attr = !empty($file_url) ? "" : $required_attr;
                                            ?>
                                                <tr>
                                                    <td><?= ($index) ?></td>
                                                    <td>
                                                        <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">


                                                        <?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') . ' ' . $mandatry_text ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= $doc_files["stage"] ?>
                                                    </td>

                                                    <td class="approved_by_<?= $doc_id ?>">
                                                        <?= !empty($staff_list[$applicant_documents[$doc_id]["approval_by"]]["firstname"]) ? $staff_list[$applicant_documents[$doc_id]["approval_by"]]["firstname"] . " " . $staff_list[$applicant_documents[$doc_id]["approval_by"]]["lastname"] : '' ?>
                                                    </td>
                                                    <td class="approved_date_<?= $doc_id ?>">
                                                        <?= !empty($applicant_documents[$doc_id]["approval_date"]) ? date("Y-m-d H:i:s", strtotime($applicant_documents[$doc_id]["approval_date"])) : '';
                                                        ?>
                                                    </td>
                                                    <!-- <td>
                                                        <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                    </td> -->
                                                    <td class="d-flex action_<?= $doc_id ?>">

                                                        <?php if (!empty($file_url)) : ?>
                                                            <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                            <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>&nbsp;
                                                            <?php if ($delete_document_status) { ?>
                                                                <button class="btn-xs btn btn-danger" onclick="document_approved(this,<?= $doc_id ?>)"><i class="fa fa-trash"></i></button>
                                                            <?php } ?>

                                                            <?php if (empty($applicant_documents[$doc_id]["approval_status"])) : ?>
                                                                <div class="action_button_<?= $doc_id ?>">
                                                                    <button class="btn-xs btn btn-success" onclick="document_approved(this, <?= $doc_id ?>, 1)"><i class="fa fa-check"></i></button>
                                                                    <button class="btn-xs btn btn-danger" onclick="document_approved(this, <?= $doc_id ?>, 2)"><i class="fa fa-times"></i></button>
                                                                </div>
                                                            <?php else :
                                                                $approval_status = $applicant_documents[$doc_id]["approval_status"];
                                                                $status_text = ($approval_status == 1) ? 'Approved' : 'Rejected';
                                                                $status_text_color = ($approval_status == 1) ? 'text-success' : 'text-danger';
                                                            ?>
                                                                <span class="<?= $status_text_color ?>"><b><?= $status_text ?></b></span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                    </td>
                                                    <td class=" updated_by_<?= $doc_id ?>">
                                                        <?= !empty($staff_list[$applicant_documents[$doc_id]["updated_by"]]["firstname"]) ? $staff_list[$applicant_documents[$doc_id]["updated_by"]]["firstname"] . " " . $staff_list[$applicant_documents[$doc_id]["updated_by"]]["lastname"] : '' ?>
                                                    </td>
                                                    <td class=" updated_at_<?= $doc_id ?>">
                                                        <?= !empty($applicant_documents[$doc_id]["updated_date"]) ? date("Y-m-d H:i:s", strtotime($applicant_documents[$doc_id]["updated_date"])) : '';
                                                        ?>
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

                            <div class="document_approval_message_action">
                            </div>
                        <?php
                        } else if ($track["show_div_name"] == "university_div") {
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
                                                <input type="hidden" name="id" value="<?= $short_list["id"] ?>">
                                                <select class="selectpicker from-control" onchange="university_shortlisting_dropdown()" data-width="100%" name="select_university" id="select_university" data-live-search="true">
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
                                                                            <option <?= $selected_university ?> data-country-name="<?= $country_name ?>" value='<?= $university_name ?>'><?= $university_name ?></option>
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
                                            <div class="col-md-2">
                                                <button class="col-md-2 add_document remove_university_btn" type="button" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    <?php }
                                    ?>

                                <?php } else { ?>
                                    <div class="col-md-12 university_div university_div_  bg-warning">
                                        <div class="col-md-2">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="hidden" name="id">
                                            <select class="selectpicker from-control" data-width="100%" onchange="university_shortlisting_dropdown()" name="select_university" id="select_university" data-live-search="true">
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
                                                                        <option data-country-name="<?= $country_name ?>" value='<?= $university_name ?>'><?= $university_name ?></option>
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

                                        <div class="col-md-2">
                                            <button class="col-md-2 add_document remove_university_btn" type="button" style="display:none;" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
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
                            <form id="application-form" class="form-disabled" onsubmit=" return false;">

                                <div class="application_div">
                                    <?php if (!empty($university_shortlisting)) {
                                        foreach ($university_shortlisting as $key_u => $short_list) {
                                            $selected_university_application = !empty($short_list["university_status"]) ? $short_list["university_status"] : "";
                                            if (!empty($selected_university_application) && $selected_university_application == 1) {

                                                $mand = "";
                                                $mand_re = "";
                                                if ($short_list["primary_university"] == 1) {
                                                    $mand = '<small class="text-danger">*</small>';
                                                    $mand_re = "required required-check";
                                                }

                                    ?>
                                                <div class="col-md-12 university_div_application mt-2 d-flex">
                                                    <div class="col-md-3">
                                                        <label>Country Name <?= $mand ?></label>
                                                        <input type="input" name="country_<?= $short_list["id"] ?>" readonly <?= $mand_re ?> class="form-control" value="<?= $short_list["country_name"] ?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>University Name <?= $mand ?></label>
                                                        <input type="hidden" name="id" value="<?= $short_list["id"] ?>">
                                                        <input type="input" name="university_<?= $short_list["id"] ?>" class="form-control" <?= $mand_re ?> readonly value="<?= $short_list["university_name"] ?>">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label>Partner Name <?= $mand ?></label>
                                                        <?php
                                                        $selected_value = [];
                                                        $selected_value[] =  !empty($short_list["partner"]) ? $short_list["partner"] : '';
                                                        if (!empty($mand)) {
                                                            echo render_select('partner_' . $short_list["id"], $university_partner_names, array('id', 'name'), "", $selected_value, ["required" => "required", "required-check" => "required-check"]);
                                                        } else {
                                                            echo render_select('partner_' . $short_list["id"], $university_partner_names, array('id', 'name'), "", $selected_value);
                                                        }

                                                        ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>Application Date <?= $mand ?> </label>
                                                        <input type="date" class="form-control" name="date_<?= $short_list["id"] ?>" <?= $mand_re ?> value="<?= $short_list["application_date"] ?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <?php
                                                        $file_url = !empty($short_list["application_file"]) ? $short_list["application_file"] : '';

                                                        ?>
                                                        <label>Admission Letter <?= $mand ?> </label>
                                                        <input type="file" class="form-control" accept=".pdf,image/*" name="admission_letter_<?= $short_list["id"] ?>">
                                                        <input type="hidden" class="form-control" value="<?= $file_url ?>" name="admission_letter_path_<?= $short_list["id"] ?>">

                                                        <?php
                                                        if (!empty($file_url)) { ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                                <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>
                                                                <?php if ($delete_document_status) { ?>
                                                                    <button class="btn-xs btn btn-danger" onclick="delete_documents(1,<?= $track['id'] ?>,<?= $short_list['id'] ?>)"><i class="fa fa-trash"></i></button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>



                                                </div>
                                        <?php }
                                        }
                                        ?>

                                    <?php } ?>
                                </div>
                            </form>
                        <?php } else if ($track["show_div_name"] == "entrance_div") { ?>
                            <form id="entrance-form" class="form-disabled" onsubmit=" return false;">
                                <div class="entrance_div">
                                    <?php if (!empty($entrance_exams)) { ?>
                                        <?php foreach ($entrance_exams as $university => $exams) { ?>
                                            <div class="entrance_exam_university_div shadow">
                                                <h4 class="text-left "><?= htmlspecialchars(empty($university) ? $exams[0]["m_university_name"] : $university) ?>
                                                    <?php if ($exams[0]["batch_id"] == 0) { ?>
                                                        <button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" type="button" onclick="addPrimaryUniversityExamBlock(this)"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                                    <?php } ?>
                                                </h4>
                                                <?php if (!empty($entrance_exams)) { ?>
                                                    <div class="text-right">
                                                        <button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 3,'','<?= htmlspecialchars($university) ?>')"><i class="fa fa-whatsapp hide-client-type"></i> </button>
                                                        <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(<?= !empty($client_id) ? $client_id : '' ?>, 2,'','<?= htmlspecialchars($university) ?>')"><i class="fa fa-envelope hide-client-type"></i> </button>
                                                    </div>
                                                <?php } ?>

                                                <?php foreach ($exams as $index_key => $exam) { ?>
                                                    <div class="row university-entrance-exam">
                                                        <input type="hidden" name="batch_id" value="<?= $exam['batch_id'] ?>" class="form-control">
                                                        <input type="hidden" name="client_id" value="<?= $exam['client_id'] ?>" class="form-control">
                                                        <?php
                                                        if ($exam['batch_id'] == 0) {
                                                        } else { ?>
                                                            <input type="hidden" name="exam_id" value="<?= $exam['exam_id'] ?>" class="form-control">
                                                        <?php } ?>
                                                        <input type="hidden" name="m_university_name" value="<?= htmlspecialchars($exam["m_university_name"]) ?>" class="form-control">
                                                        <?php if ($exam['batch_id'] != 0) { ?>
                                                            <div class="col-md-3">
                                                                <label>Batch Name</label>
                                                                <input type="text" value="<?= htmlspecialchars($exam["batch_name"]) ?>" readonly class="form-control">
                                                            </div>
                                                        <?php } ?>
                                                        <div class="col-md-3">
                                                            <label>Exam Name</label>

                                                            <?php
                                                            if ($exam['batch_id'] == 0) {
                                                                $get_university_exam = get_university_exam();
                                                                array_unshift($get_university_exam, array());

                                                                echo render_select('exam_id', $get_university_exam, ['id', 'name'], '', [$exam['exam_id']], [
                                                                    'data-width' => '100%',
                                                                    'data-none-selected-text' => 'Exam Name',
                                                                    'data-actions-box' => true,
                                                                    'required-check' => 'required-check',
                                                                    'required' => 'required',
                                                                ], [], 'no-mbot', '', false, 'exam_id');  ?>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <input type="text" value="<?= htmlspecialchars($exam["exam_name"]) ?>" <?= $exam['batch_id'] == 0 ? '' : 'readonly' ?> class="form-control">
                                                            <?php } ?>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label>Exam Date</label>
                                                            <input type="date" name="exam_date" value="<?= htmlspecialchars($exam["exam_date"]) ?>" <?= $exam['batch_id'] == 0 ? '' : 'readonly' ?> class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Status</label>

                                                            <select name="entrance_status" class="selectpicker form-control">
                                                                <option value="Pending" <?= (strtolower($exam["status"]) == "pending") ? 'selected' : '' ?>>Pending</option>
                                                                <option value="pass" <?= (strtolower($exam["status"]) == "pass") ? 'selected' : '' ?>>Pass</option>
                                                                <option value="fail" <?= (strtolower($exam["status"]) == "fail") ? 'selected' : '' ?>>Fail</option>
                                                                <option value="reschedule" <?= (strtolower($exam["status"]) == "reschedule") ? 'selected' : '' ?>>Re-schedule</option>
                                                            </select>



                                                        </div>
                                                        <?php if ($exam['batch_id'] == 0 && $index_key > 0) { ?>
                                                            <div class="col-md-3"><br><button class="col-md-2 add_document remove_university_btn" type="button" onclick="remove_entrance_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></div>

                                                        <?php } ?>
                                                    </div>
                                                    <br>

                                                <?php } ?>

                                            </div>
                                            <hr>
                                        <?php } ?>
                                    <?php } else if ($admissionpreferences->primary_country == 'Georgia') { ?>
                                        <div class="entrance_exam_university_div shadow">
                                            <h4 class="text-left "><?= htmlspecialchars($admissionpreferences->primary_university) ?>

                                                <button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" type="button" onclick="addPrimaryUniversityExamBlock(this)"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                            </h4>
                                            <div class="row university-entrance-exam">
                                                <div class="col-md-3">
                                                    <input type="hidden" name="client_id" value="<?= $client_id ?>" class="form-control">
                                                    <input type="hidden" name="m_university_name" value="<?= $admissionpreferences->primary_university ?>" class="form-control">
                                                    <label>Exam Name</label>
                                                    <?php
                                                    $get_university_exam = get_university_exam();
                                                    array_unshift($get_university_exam, array());

                                                    echo render_select('exam_id', $get_university_exam, ['id', 'name'], '', [], [
                                                        'data-width' => '100%',
                                                        'data-none-selected-text' => 'Exam Name',
                                                        'data-actions-box' => true,
                                                        'required-check' => 'required-check',
                                                        'required' => 'required',
                                                    ], [], 'no-mbot', '', false, 'exam_id');  ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Exam Date</label>
                                                    <input type="date" name="exam_date" value="" class="form-control">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Status</label>

                                                    <select name="entrance_status" class="selectpicker form-control">
                                                        <option value="Pending" selected>Pending</option>
                                                        <option value="pass">Pass</option>
                                                        <option value="fail">Fail</option>
                                                        <option value="reschedule">Re-schedule</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <br>
                                        </div>
                                    <?php } ?>
                                </div>

                            </form>
                        <?php } else if ($track["show_div_name"] == "legalization_div") { ?>
                            <form id="legalization-form" class="form-disabled" onsubmit=" return false;">
                                <div class="legalization_div">
                                    <?php if (!empty($legalization)) : ?>
                                        <?php foreach ($legalization as $leg) :
                                            $mand = "";
                                            $mand_re = "";
                                            if ($leg["primary_university"] == 1) {
                                                $mand = '<small class="text-danger">*</small>';
                                                $mand_re = "required required-check";
                                            }
                                        ?>
                                            <div class="legalization-item card shadow-sm p-3 mb-3">
                                                <h4 class="university-name"><?= htmlspecialchars($leg["university_name"], ENT_QUOTES, 'UTF-8') ?></h4>
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">
                                                <?php if (!empty($leg["ministry_document_status"]) && $leg["ministry_document_status"] == 1) : ?>
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <p class="form-check-label">&nbsp;</p>
                                                            <label class="form-check-label">Ministry Order of Documents Received <?= $mand ?>
                                                                <input type="checkbox" class="form-check-input" <?= $mand_re ?> <?= !empty($leg["ministry_document_recived"]) && $leg["ministry_document_recived"] == 1 ? 'checked' : '' ?> name="ministry_doc_received_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                            </label>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>MD Payment Proof <?= $mand ?> </label>
                                                            <input type="file" class="form-control" <?= empty($leg["ministry_payment"]) ? $mand_re : "" ?> accept=".pdf,image/*" name="ministry_doc_payment_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">
                                                            <?php
                                                            $file_url = !empty($leg["ministry_payment"]) ? $leg["ministry_payment"] : "";
                                                            if (!empty($file_url)) { ?>
                                                                <div class="margin-top">
                                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>
                                                                    <?php if ($delete_document_status) { ?>
                                                                        <button class="btn-xs btn btn-danger" onclick="delete_documents(2,<?= $track['id'] ?>,<?= $leg['id'] ?>)"><i class="fa fa-trash"></i></button>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <p class="form-check-label">&nbsp;</p>
                                                            <label class="form-check-label">
                                                                Contract Signed <?= $mand ?>
                                                                <input type="checkbox" <?= !empty($leg["contract_signed"]) && $leg["contract_signed"] == 1 ? 'checked' : '' ?> class="form-check-input" <?= $mand_re ?> name="contract_signed_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="text-muted">No legalizations available.</p>
                                    <?php endif; ?>
                                </div>


                            </form>
                        <?php } else if ($track["show_div_name"] == "fees_deposite_div") {  ?>
                            <form id="fees-deposite-form" class="form-disabled" onsubmit="return false;">
                                <div class="fees_deposite_div">
                                    <?php if (!empty($legalization)) : ?>
                                        <?php foreach ($legalization as $leg) :
                                            $mand = "";
                                            $mand_re = "";
                                            if ($leg["primary_university"] == 1) {
                                                $mand = '<small class="text-danger">*</small>';
                                                $mand_re = "required required-check";
                                            }
                                            $file_url_payment = !empty($leg["fees_deposite_slip"]) ? $leg["fees_deposite_slip"] : "";
                                            $file_url_university_payment = !empty($leg["university_fees_payment_slip"]) ? $leg["university_fees_payment_slip"] : "";
                                        ?>
                                            <div class="feesDeposite-item card shadow-sm p-3 mb-3">
                                                <h4 class="university-name">
                                                    <?= htmlspecialchars($leg["university_name"], ENT_QUOTES, 'UTF-8') ?>
                                                </h4>
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                <div class="row mt-2">
                                                    <div class="col-md-3">
                                                        <label>Date of Payment <?= $mand ?></label>
                                                        <input type="date" <?= $mand_re ?> class="form-control" value="<?= !empty($leg["fees_deposite_date"]) ? $leg["fees_deposite_date"] : '' ?>" name="date_of_payment_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label>Payment Proof <?= $mand ?></label>
                                                        <input type="file" <?= empty($file_url_payment) ? $mand_re : '' ?> class="form-control" accept=".pdf,image/*" name="payment_slip_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">
                                                        <?php if (!empty($file_url_payment)) { ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_payment) ?>');"></i>&nbsp;
                                                                <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_payment) ?>', '_blank');"></i>
                                                                <?php if ($delete_document_status) { ?>
                                                                    <button class="btn-xs btn btn-danger" onclick="delete_documents(3,<?= $track['id'] ?>,<?= $leg['id'] ?>)"><i class="fa fa-trash"></i></button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label>Payment Amount <?= $mand ?></label>
                                                        <input type="number" <?= $mand_re ?> <?= empty($file_url_university_payment) ? '' : '' ?> class="form-control" name="payment_amount_<?= $leg["id"] ?>" value="<?= !empty($leg["payment_amount"]) ? $leg["payment_amount"] : '' ?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>University Payment Receipt </label>
                                                        <input type="file" <?= empty($file_url_university_payment) ? '' : '' ?> class="form-control" accept=".pdf,image/*" name="university_payment_slip_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                        <?php if (!empty($file_url_university_payment)) { ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_university_payment) ?>');"></i>&nbsp;
                                                                <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_university_payment) ?>', '_blank');"></i>
                                                                <?php if ($delete_document_status) { ?>
                                                                    <button class="btn-xs btn btn-danger" onclick="delete_documents(4,<?= $track['id'] ?>,<?= $leg['id'] ?>)"><i class="fa fa-trash"></i></button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="text-muted">No Fees available.</p>
                                    <?php endif; ?>
                                </div>
                            </form>

                        <?php } else if ($track["show_div_name"] == "invitation_div") {  ?>
                            <form id="invitation-form" class="form-disabled" onsubmit="return false;">

                                <div class="invitation_div">
                                    <?php if (!empty($legalization)) : ?>
                                        <?php foreach ($legalization as $leg) :
                                            $mand = "";
                                            $mand_re = "";

                                            $file_url = !empty($leg["invitation_letter"]) ? $leg["invitation_letter"] : '';
                                            if ($leg["primary_university"] == 1) {
                                                $mand = '<small class="text-danger">*</small>';
                                                $mand_re = "required required-check";
                                            }
                                        ?>
                                            <div class="invitation-item card shadow-sm p-3 mb-3">
                                                <h4 class="university-name">
                                                    <?= htmlspecialchars($leg["university_name"], ENT_QUOTES, 'UTF-8') ?>

                                                </h4>
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 4,'','<?= htmlspecialchars($university) ?>')"><i class="fa fa-whatsapp hide-client-type"></i> </button>
                                                    <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(<?= !empty($client_id) ? $client_id : '' ?>, 3,<?= $leg['invitation_letter'] ?>)"><i class="fa fa-envelope hide-client-type"></i></button>
                                                </div>
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                <div class="row mt-2">
                                                    <div class="col-md-3">
                                                        <label>Date of Receiving <?= $mand ?></label>
                                                        <input type="date" <?= $mand_re ?> class="form-control" value="<?= !empty($leg["invitation_receiving_date"]) ? $leg["invitation_receiving_date"] : '' ?>" name="invitation_receiving_date_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label>Invitation Letter Upload <?= $mand ?></label>
                                                        <input type="file" <?= !empty($file_url) ? '' : $mand_re ?> class="form-control" accept=".pdf,image/*" name="invitation_letter_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                        <?php
                                                        if (!empty($file_url)) { ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                                <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>
                                                                <?php if ($delete_document_status) { ?>
                                                                    <button class="btn-xs btn btn-danger" onclick="delete_documents(5,<?= $track['id'] ?>,<?= $leg['id'] ?>)"><i class="fa fa-trash"></i></button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <?php if (!empty($leg["telex_status"]) && $leg["telex_status"] == 1) { ?>
                                                        <div class="col-md-3">
                                                            <label>Telex No. <?= $mand ?></label>
                                                            <input type="text" <?= $mand_re ?> class="form-control" name="telex_no_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>" value="<?= !empty($leg["telex_no"]) ? $leg["telex_no"] : '' ?>">
                                                        </div>
                                                    <?php } ?>

                                                    <div class="col-md-3">
                                                        <label>Entry Date </label>
                                                        <input type="date" class="form-control" name="entry_date_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>" value="<?= !empty($leg["entry_date"]) ? $leg["entry_date"] : '' ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="text-muted">No Fees available.</p>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php } else if ($track["show_div_name"] == "3_payment") {
                        ?>
                            <form id="3-payment-form" class="form-disabled" onsubmit="return false;">

                                <label for="3_payment"> <small class="text-danger">*</small> 3rd Payment Received </label> <input type="checkbox" class="form-check-input" required name="3_payment" id="3_payment" <?= !empty($client_infomation->payment_3_received) ? 'checked' : '' ?>>
                            </form>
                        <?php

                        } else if ($track["show_div_name"] == "visa_div") { ?>
                            <form id="visa-form" class="form-disabled" onsubmit="return false;">

                                <div class="visa_div">

                                    <div id="visa-details" class="visa-details row">
                                        <?php if (!empty($visa_details)) {
                                            foreach ($visa_details as $key => $visa) {
                                                $visa_id = $visa["id"];
                                                $file_url = !empty($visa["file"]) ? $visa["file"] : '';

                                        ?>
                                                <div class="col-md-12 visa_div_application <?= $visa['status'] == 4 ? 'visa-rejected-div' : '' ?>">
                                                    <?php if ($key > 0 || ($key > 0 && is_admin())) { ?>
                                                        <div class="text-right">
                                                            <i class='fa fa-trash btn btn-danger' onclick="remove_visa_div(this,<?= $visa_id ?>)"></i>
                                                        </div>
                                                    <?php } ?>
                                                    <?php echo render_input('id', '', $visa["id"], 'hidden'); ?>

                                                    <div class="d-flex">
                                                        <div class="col-md-4">
                                                            <label>Visa Vendor <small class='text-danger'>*</small></label>
                                                            <?php
                                                            array_unshift($visa_vendors, array());
                                                            echo render_select('visa_vendor_' . $visa_id, $visa_vendors, ['id', 'name'], '', [$visa["vendor_id"]], [
                                                                'data-width' => '100%',
                                                                'data-none-selected-text' => 'Vendor',
                                                                'data-actions-box' => true,
                                                                'required-check' => 'required-check',
                                                                'required' => 'required',
                                                            ], [], 'no-mbot', '', false, 'visa_vendor'); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Courier Date <small class='text-danger'>*</small></label>
                                                            <?php echo render_input('visa_date_' . $visa_id, '', $visa["courier_date"], 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Courier Type <small class='text-danger'>*</small></label>
                                                            <?php
                                                            array_unshift($courier_type, array());
                                                            echo render_select('visa_courier_type_' . $visa_id, $courier_type, ['id', 'name'], '', [$visa["courier_type"]], [
                                                                'data-width' => '100%',
                                                                'data-none-selected-text' => 'Courier Type',
                                                                'data-actions-box' => true,
                                                                'required-check' => 'required-check',
                                                                'required' => 'required',
                                                            ], [], 'no-mbot', '', false, 'visa_courier_type'); ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex visa-payment-details">
                                                        <div class="col-md-4">
                                                            <label>Payment Date <small class='text-danger'>*</small></label>
                                                            <?php echo render_input('visa_payment_date_' . $visa_id, '',  $visa["payment_date"], 'date', [
                                                                'required-check' => 'required-check',
                                                                'required' => 'required'
                                                            ]); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Cost <small class='text-danger'>*</small></label>
                                                            <?php echo render_input('visa_cost_' . $visa_id, '',  !empty($visa["cost"]) ? $visa["cost"] : '', 'number', [
                                                                'required-check' => 'required-check',
                                                                'required' => 'required'
                                                            ]); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Payment Mode <small class='text-danger'>*</small></label>
                                                            <?php
                                                            array_unshift($payment_mode, array());
                                                            echo render_select('visa_payment_mode_' . $visa_id, $payment_mode, ['id', 'name'], '', [$visa["payment_mode"]], [
                                                                'data-width' => '100%',
                                                                'data-none-selected-text' => 'Payment Mode',
                                                                'data-actions-box' => true,
                                                                'required-check' => 'required-check',
                                                                'required' => 'required',
                                                            ], [], 'no-mbot', '', false, 'visa_payment_mode'); ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex visa-receving-details">
                                                        <div class="col-md-4">
                                                            <label>Visa Received <small class='text-danger'>*</small></label>
                                                            <?php echo render_input('visa_receiving_date_' . $visa_id, '',  $visa["receiving_date"], 'date', [
                                                                'required-check' => 'required-check',
                                                                'required' => 'required'
                                                            ]); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Visa Document <small class='text-danger'>*</small></label>
                                                            <?php
                                                            $re = !empty($file_url) ? 'false' : 'true';
                                                            echo render_input('visa_file_' . $visa_id, '', '', 'file', ["data-file" => $file_url, "required" => $re]); ?>
                                                            <?php
                                                            if (!empty($file_url)) { ?>
                                                                <div class="margin-top">
                                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>
                                                                    <?php if ($delete_document_status) { ?>
                                                                        <button class="btn-xs btn btn-danger" onclick="delete_documents(6,<?= $track['id'] ?>,<?= $visa['id'] ?>)"><i class="fa fa-trash"></i></button>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Visa Entry Date <small class='text-danger'>*</small></label>
                                                            <?php echo render_input('visa_entry_date_' . $visa_id, '',  $visa["entry_date"], 'date', [
                                                                'required-check' => 'required-check',
                                                                'required' => 'required'
                                                            ]); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-right">
                                                        <label class="form-check-label">Visa Rejected

                                                            <input type="checkbox" class="form-check-input"
                                                                <?= ($visa["status"] && $visa["status"] == 4) ? 'checked' : '' ?>
                                                                name="visa_rejected_<?= $visa_id ?>"
                                                                id="visa_rejected_<?= $visa_id ?>">
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php }
                                        } else { ?>
                                            <div class="col-md-12 visa_div_application">
                                                <div class="d-flex">
                                                    <div class="col-md-4">
                                                        <label>Visa Vendor <small class='text-danger'>*</small></label>
                                                        <?php
                                                        array_unshift($visa_vendors, array());
                                                        echo render_select('visa_vendor', $visa_vendors, ['id', 'name'], '', [], [
                                                            'data-width' => '100%',
                                                            'data-none-selected-text' => 'Vendor',
                                                            'data-actions-box' => true,
                                                            'required-check' => 'required-check',
                                                            'required' => 'required',
                                                        ], [], 'no-mbot', '', false, 'visa_vendor'); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Courier Date <small class='text-danger'>*</small></label>
                                                        <?php echo render_input('visa_date', '', '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Courier Type <small class='text-danger'>*</small></label>
                                                        <?php
                                                        array_unshift($courier_type, array());
                                                        echo render_select('visa_courier_type', $courier_type, ['id', 'name'], '', [], [
                                                            'data-width' => '100%',
                                                            'data-none-selected-text' => 'Courier Type',
                                                            'data-actions-box' => true,
                                                            'required-check' => 'required-check',
                                                            'required' => 'required',
                                                        ], [], 'no-mbot', '', false, 'visa_courier_type'); ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex visa-payment-details">
                                                    <div class="col-md-4">
                                                        <label>Payment Date <small class='text-danger'>*</small></label>
                                                        <?php echo render_input('visa_payment_date', '', '', 'date', [
                                                            'required-check' => 'required-check',
                                                            'required' => 'required'
                                                        ]); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Cost <small class='text-danger'>*</small></label>
                                                        <?php echo render_input('visa_cost', '', '', 'number', [
                                                            'required-check' => 'required-check',
                                                            'required' => 'required'
                                                        ]); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Payment Mode <small class='text-danger'>*</small></label>
                                                        <?php
                                                        array_unshift($payment_mode, array());
                                                        echo render_select('visa_payment_mode', $payment_mode, ['id', 'name'], '', [], [
                                                            'data-width' => '100%',
                                                            'data-none-selected-text' => 'Payment Mode',
                                                            'data-actions-box' => true,
                                                            'required-check' => 'required-check',
                                                            'required' => 'required',
                                                        ], [], 'no-mbot', '', false, 'visa_payment_mode'); ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex visa-receving-details">
                                                    <div class="col-md-4">
                                                        <label>Visa Received <small class='text-danger'>*</small></label>
                                                        <?php echo render_input('visa_receiving_date', '', '', 'date', [
                                                            'required-check' => 'required-check',
                                                            'required' => 'required'
                                                        ]); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Visa Document <small class='text-danger'>*</small></label>
                                                        <?php echo render_input('visa_file', '', '', 'file', [
                                                            'required-check' => 'required-check',
                                                            'required' => 'required'
                                                        ]); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Visa Entry Date <small class='text-danger'>*</small></label>
                                                        <?php echo render_input('visa_entry_date', '', '', 'date', [
                                                            'required-check' => 'required-check',
                                                            'required' => 'required'
                                                        ]); ?>
                                                    </div>

                                                </div>
                                                <div class="col-md-12 text-right">
                                                    <label class="form-check-label">Visa Rejected
                                                        <input type="checkbox" class="form-check-input" name="visa_rejected" id="visa_rejected">
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div id="visa-details-add" class="visa-details-add row">
                                    </div>

                                </div>
                            </form>
                            <?php } else if ($track["show_div_name"] == "sc_div") {

                            if (!has_permission($track['check_permission'], '', 'edit')) {

                                echo ' <div class="col-md-12"><h3>You do not have permission to continue to the next step.</h3></div>';
                            } else {
                            ?>
                                <form id="final-form" class="form-disabled" onsubmit="return false;">
                                    <div class="col-md-12">
                                        <?php $mand_re = "required required-check"; ?>
                                        <?php if (!empty($client->sc_100) && $client->sc_100 == 1) { ?>
                                            <h4 class="text-success text-center">Congratulations! Your application to <b><?= $admissionpreferences->primary_university ?>, <?= $admissionpreferences->primary_country ?></b> has been completed successfully.</h4>
                                        <?php } ?>
                                        <p class="form-check-label">&nbsp;</p>
                                        <label class="form-check-label">
                                            <?= $mand ?> Received 100% service charge clearance.
                                            <input type="checkbox"
                                                id="sc_100" class="form-check-input <?= $mand_re ?>"
                                                name="sc_100"
                                                <?= !has_permission($track['check_permission'], '', 'edit') ? 'disabled' : '' ?>
                                                <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 'checked' : '' ?>>
                                        </label>
                                    </div>
                                </form>



                        <?php }
                        } ?>
                        <?php if ($k > 0 && $k < 5) { ?>
                            <p class='col-12 margin-top'>
                                <label class="margin-top">Secondary University Remarks</label>
                                <textarea rows="4" class="form-control secondary_university_remark" onkeyup="update_remark(this.value)"><?= !empty($client->secondary_university_remark) ? $client->secondary_university_remark : '' ?></textarea>

                            </p>
                        <?php } ?>

                        <?php if ($k > 0) { ?>
                            <input type="button" name="previous" class="previous text-center action-button-previous" value="Previous" />
                        <?php } ?>
                        <?php
                        if (($k + 1) < count($applicant_tracker)) { ?>
                            <input type="button" name="next" class="next text-center action-button next-<?= $track['id'] ?>" onclick="next_step('<?= $track['id'] ?>',this)" value="<?= !empty($client->sc_100) && $client->sc_100 == 1 ? 'Next' : 'Save & Next' ?>" />
                            <?php if (!empty($track['save']) && $track['save'] == 1) { ?>
                                <input type="button" name="next" class="next btn-hide-complete  text-center action-button next-save-<?= $track['id'] ?>" onclick="next_step('<?= $track['id'] ?>',this,'','',1)" value="Save" />
                            <?php } ?>

                            <?php if (!empty($track['id']) && $track['id'] == 2) { ?>
                                <input type="button" name="next" class="next btn-hide-complete  text-center btn-danger action-button next-reset-<?= $track['id'] ?>" onclick="reset_university_shortlisting()" value="Reset" />
                            <?php } ?>
                            <?php if (!empty($track['skip']) && $track['skip'] == 1) { ?>
                                <input type="button" name="next" class=" btn-hide-complete text-center btn-warning action-button next-<?= $track ?>" onclick="next_step('<?= $track['id'] ?>',this,'<?= $track['skip'] ?>')" value="Skip" />
                            <?php } ?>
                        <?php } else if (($k + 2) == count($applicant_tracker)) {  ?>
                            <input type="button" name="next" class="next btn-hide-complete  text-center action-button next-<?= $track['id'] ?>" onclick="next_step('<?= $track['id'] ?>',this)" value="Update" />
                        <?php } else {
                        ?>
                            <?php if (has_permission("application_tracker_mbbbs_sc", '', 'edit') &&  empty($client->sc_100)) { ?>
                                <input type="button" name="next" class="next text-center action-button next-<?= $track['id'] ?>" onclick="next_step('<?= $track['id'] ?>',this,0,1)" value="Complete" />
                            <?php } ?>
                        <?php
                        } ?>

                        <?php if ((is_admin() || !empty($staff_list[get_staff_user_id()]["post_sales"])) && !empty($track['save']) && $track['id'] == 2) { ?>
                            <div class="col-lg-5 pull-right">
                                <div class="form-group">
                                    <!-- <label for="primary_university">Primary University<small class="text-danger">*</small></label> -->
                                    <select class="form-control selectpicker" required-check name="primary_university" id="primary_university" required>
                                        <option value="">Select University</option>
                                        <?php
                                        $university_p = json_decode($admissionpreferences->university, true);

                                        if (!empty($university_p)) {
                                            foreach ($university_p as $key => $country) {
                                                $universities = array_filter(explode(",", $country)); // Remove empty values
                                                foreach ($universities as $uni) { ?>
                                                    <option data-country="<?= $key ?>" <?= ($admissionpreferences->primary_university == $uni) ? 'selected' : '' ?> value="<?= htmlspecialchars($uni) ?>"><?= htmlspecialchars($uni) ?></option>
                                        <?php }
                                            }
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>
                            <div class="col-lg-4 hide">
                                <div class="form-group">
                                    <label for="primary_university">Primary Country<small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" id="primary_country" name="primary_country" value="<?= $admissionpreferences->primary_country ?>">
                                </div>
                            </div>
                        <?php } ?>

                    </fieldset>
                <?php
                }
                ?>


            </section>

        <?php } ?>

        <!-- </form> -->

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
<?php
// }
?>
<?php init_tail(); ?>
<!-- /.MultiStep Form -->
<script>
    var complete_application = " <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 1 : 0 ?>";
    var is_admin = <?= is_admin() ? 1 : 0 ?>;
    if (complete_application == 1) {
        setTimeout(function() {
            $(".btn-hide-complete").hide();
            $(".secondary_university_remark").prop("disabled", true)
            $("fieldset form").find("input, select, textarea").prop("disabled", true).selectpicker("refresh");
            $(".remove_university_btn,.add_university_btn,.add_university_btn,.add_university_btn").hide();
            $("#primary_university").prop("disabled", true).selectpicker("refresh");
        }, 500);

    }
    var delete_document_status = <?= $delete_document_status ?>;
    var admissionpreferences_freeze = 0;
    var base_url = "<?= base_url() ?>";
    //jQuery time
    let lead_type_status = "<?= $lead_type_status ?>";
    // console.log(lead_type_status);
    let documents_type_dropdown = <?= json_encode($documents_type_dropdown) ?>; // Get your data from PHP
    var applicant_status = "<?= $applicant_status ?>";
    // console.log(applicant_status);
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches
    var client_id = <?= !empty($client_id) ? $client_id : '' ?>;
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>"; // Replace with the actual CSRF token value
    var step_stage = 0;
    var get_university_list = <?= json_encode(array_column(get_university_list("mbbs abroad"), "university_id", "university_name"), true); ?>

    var customer_admins = <?= !empty($customer_admins) ? json_encode($customer_admins, true) : [] ?>;
    var upload_documents_button = <?= !empty($upload_documents_button) ? json_encode($upload_documents_button, true) : "" ?>;
    var upload_documents = <?= !empty($upload_documents[0]) ? json_encode($upload_documents[0], true) : '0' ?>;
    var staff_id = "<?= get_staff_user_id() ?>";
    var profile_creation_data = <?= !empty($profile_creation_data[0]) ? json_encode($profile_creation_data[0], true) : '0' ?>;
    var profile_verification_button = <?= !empty($profile_verification_button) ? json_encode($profile_verification_button, true) : "" ?>;
    var university_shortlisting = <?= !empty($university_shortlisting[0]) ? json_encode($university_shortlisting) : '[]'; ?>;

    var document_verification = "<?= !empty($upload_documents[0]["document_status"]) ? $upload_documents[0]["document_status"] : 0 ?>";
    var profile_verification = "<?= !empty($profile_creation_data[0]["profile_status"]) ? $profile_creation_data[0]["profile_status"] : 0 ?>";
    var university_partner_names = <?= !empty($university_partner_names) ? json_encode($university_partner_names) : '[]'; ?>;
    var documents_type = <?= !empty($documents_type) ? json_encode(array_values($documents_type)) : '[]'; ?>;


    var admin_ids = [];
    var check_university_status = false;
    var check_university_status_direct = false;
    var check_university_status_submit = false;
    var table_notes = "";
    var notes_url = "";
    var activity_url = "";
    var check_offer_letter = true;
    var fee_status = "<?= !empty($short_list["fee_status"]) ? $short_list["fee_status"] : 0 ?> ";


    notes_url = "<?= base_url() ?>admin/clients/get_application_notes/<?= $client_id ?>";
    activity_url = "<?= base_url() ?>admin/clients/get_application_activity/<?= $client_id ?>";

    reloadNote_list(notes_url);
    reloadActivity_list(activity_url);

    function update_remark(text) {
        $(".secondary_university_remark").val(text);
    }

    $(document).on('click', '.btn-switch-toggle', function() {
        var parentDiv = $(".note_activity_section .parrent-div");
        parentDiv.find(".panel-body").toggle();
    });


    $("#progressbar li").addClass("inactive");
    $("#progressbar li:eq(" + applicant_status + ")").removeClass("inactive").removeClass("previous").addClass("active");

    $("#progressbar li:eq(" + applicant_status + ")")
        .removeClass("inactive").removeClass("previous")
        .addClass("active")
        .prevAll().removeClass("inactive").removeClass("active")
        .addClass("previous permanent_previous");

    async function document_approved(obj, doc_id, status = 0) {
        let upload_data = new FormData();
        try {
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("status", status);
            upload_data.append("doc_id", doc_id);
            show_loader();

            if (status == 0) {
                if (confirm("Are you sure you want to download " + documents_type_dropdown[doc_id].name + " document?")) {

                } else {
                    hide_loader();
                    return false;
                }
            }

            let response = await $.ajax({
                url: "<?= base_url("admin/clients/documents_approval") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });
            response = JSON.parse(response);
            hide_loader();
            if (response.resp_code === "RCS") {
                alert_float("success", response.resp_desc);
                if (status == 1) {
                    $(".action_button_" + doc_id).html("<span class='text-success'>Approved</span>");
                } else if (status == 0) {

                    $(".approved_by_" + doc_id).html("");
                    $(".approved_date_" + doc_id).html("");
                    $(".action_" + doc_id).html("");
                    $(".updated_by_" + doc_id).html("");
                    $(".updated_at_" + doc_id).html("");
                } else {
                    $(".action_button_" + doc_id).html("<span class='text-danger'>Rejected</span>");
                }
                if (status == 0) {
                    // location.reload();
                } else {
                    let date = new Date();
                    let formattedDate = formatDate(date);
                    $(".approved_by_" + doc_id).text("<?= !empty($staff_list[get_staff_user_id()]["firstname"]) ? $staff_list[get_staff_user_id()]["firstname"] . " " . $staff_list[get_staff_user_id()]["lastname"] : '' ?>");
                    $(".approved_date_" + doc_id).text(formattedDate);
                }
            } else {
                if (response.resp_code !== undefined) {
                    alert_float("danger", response.resp_desc);
                } else {
                    alert_float("danger", response);
                }
            }
        } catch (error) {
            // Handle the error response from the server
            // console.error(error);
            hide_loader();
            reject(error);
        }
    }


    // Function to format date as "d-m-Y H:i:s"
    function formatDate(date) {
        let d = date.getDate().toString().padStart(2, '0');
        let m = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-based
        let y = date.getFullYear();
        let h = date.getHours().toString().padStart(2, '0');
        let min = date.getMinutes().toString().padStart(2, '0');
        let s = date.getSeconds().toString().padStart(2, '0');

        return `${d}-${m}-${y} ${h}:${min}:${s}`;
    }


    function reloadNote_list(url) {
        $.ajax({
            url: url,
            success: function(data) {

                $(".lead-note").html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error loading data:", error);
            }
        });

    }

    function reloadActivity_list(url) {
        $.ajax({
            url: url,
            success: function(data) {

                $(".lead-activity").html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error loading data:", error);
            }
        });

    }

    function edit_notes(id, obj) {
        let notes = $(obj).data('notes');
        let notes_id = $(obj).data('id');
        $("#note_data").val(notes);
        $("#notes_id").val(notes_id);
    }

    async function create_notes() {
        let stage_id = $("#progressbar").find("li.active").index() + 1;
        let notes = $("#note_data").val();
        let notes_id = $("#notes_id").val();

        if (stage_id === "" || stage_id === undefined) {
            alert_float("danger", "Applicant stage id required");
            return false;
        }

        if (notes === "" || notes === undefined) {
            alert_float("danger", "Notes are required");
            $("#note_data").focus();
            return false;
        }

        show_loader();

        try {
            let uploadResponse = await update_notes(stage_id, notes, notes_id);

            if (uploadResponse.resp_code === "RCS") {
                $("#note_data").val("");
                reloadNote_list(notes_url);
                alert_float("success", uploadResponse.resp_desc);
            } else {
                if (uploadResponse.resp_code !== undefined) {
                    alert_float("danger", uploadResponse.resp_desc);
                } else {
                    alert_float("danger", uploadResponse);
                }
            }
        } catch (error) {
            console.error(error);
            alert_float("danger", "An error occurred while updating notes.");
        } finally {
            hide_loader();
        }
    }


    function update_notes(stage_id, notes, notes_id) {
        let upload_data = new FormData();
        return new Promise(async (resolve, reject) => {
            try {
                upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
                upload_data.append("client_id", <?= $client_id ?>);
                upload_data.append("stage_id", stage_id);
                upload_data.append("applicant_notes", notes);
                upload_data.append("notes_id", notes_id);

                let response = await $.ajax({
                    url: "<?= base_url("admin/clients/update_notes") ?>",
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


    function show_next_previous(obj) {
        let current_fs = $(obj).parent();
        let next_fs = $(obj).parent().next();

        // Activate next step on progressbar using the index of next_fs
        $("#progressbar li").removeClass("active").addClass("inactive");
        $("#progressbar li").eq($("fieldset").index(next_fs)).prevAll().removeClass("active").removeClass("inactive").addClass("previous");


        let nextIndex = $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active").removeClass("inactive").removeClass("previous");
        // $("#progressbar li.active").prevAll().addClass("previous");

        // location.reload();
        hide_loader();
        current_fs.slideUp("slow");
        next_fs.slideDown("slow");
    }

    function set_validation_visa() {
        return new Promise((resolve, reject) => {
            try {
                $(".visa_div_application").each(function() {
                    let paymentFields = $(this).find(".visa-payment-details input, .visa-payment-details select");
                    let receivingFields = $(this).find(".visa-receving-details input, .visa-receving-details select");

                    paymentFields.removeClass("error-validation");
                    receivingFields.removeClass("error-validation");

                    let hasPaymentValue = false;
                    let hasReceivingValue = false;

                    // Check payment fields
                    paymentFields.each(function() {
                        if ($(this).val().trim() !== "") {
                            hasPaymentValue = true;
                        }
                    });

                    // Check receiving fields
                    receivingFields.each(function() {
                        const inputType = $(this).attr("type");
                        const inputVal = $(this).val().trim();
                        const dataFileValue = $(this).attr("data-file");

                        if (inputType === "file") {
                            // Check if either the file has been selected or the data-file attribute is set
                            if ((inputVal !== "" || (dataFileValue && dataFileValue.trim() !== ""))) {
                                hasReceivingValue = true;
                            }
                        } else {
                            // For non-file inputs, just check if the input has a value
                            if (inputVal !== "") {
                                hasReceivingValue = true;
                            }
                        }
                    });

                    // Apply required attributes based on whether any value exists

                    if (hasPaymentValue) {
                        paymentFields.each(function() {
                            $(this).attr("required", true).attr("required-check", true);
                        });
                    } else {
                        paymentFields.each(function() {
                            $(this).removeAttr("required").removeAttr("required-check");
                        });
                    }

                    if (hasReceivingValue) {
                        receivingFields.each(function() {
                            const inputType = $(this).attr("type");
                            const dataFileValue = $(this).attr("data-file");

                            if (inputType === "file") {
                                // Check if file has been selected or data-file attribute exists
                                if (dataFileValue && dataFileValue.trim() !== "") {
                                    $(this).removeAttr("required").removeAttr("required-check");
                                } else {
                                    $(this).attr("required", true).attr("required-check", true);
                                }
                            } else {
                                // For non-file inputs, just mark them as required if they have a value
                                $(this).attr("required", true).attr("required-check", true);
                            }
                        });
                    } else {
                        receivingFields.each(function() {
                            $(this).removeAttr("required").removeAttr("required-check");
                        });
                    }
                });

                // console.log("✅ Visa validation rules applied successfully.");
                resolve("Validation rules applied successfully.");
            } catch (error) {
                // console.error("❌ Error applying visa validation rules:", error);
                reject("Error applying validation rules: " + error.message);
            }
        });
    }


    function show_next_stage(index = 5) {
        let current_fs = $("fieldset:visible"); // Get the currently visible fieldset
        let next_fs = $("fieldset").eq(index); // Get the target fieldset by index

        if (next_fs.length === 0) {
            return; // Stop if the index is out of bounds
        }

        // Activate next step on progress bar
        $("#progressbar li").removeClass("active").addClass("inactive");
        $("#progressbar li").eq(index).addClass("active").removeClass("inactive").removeClass("previous");
        $("#progressbar li").eq(index).prevAll().addClass("previous").removeClass("active").removeClass("inactive");

        hide_loader(); // Hide the loader (assuming this is defined)

        current_fs.slideUp("slow");
        next_fs.slideDown("slow");
    }

    async function next_step(id, obj, skip = 0, completed = 0, same_step = 0) {
        id = $.trim(id) || $("#progressbar .active").data("id");

        let upload_data = new FormData();
        show_loader();
        if (complete_application == 1) {
            skip == 1;
            skip == 1;
            show_next_stage(id);
            return false;
        }
        try {
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("tracker_id", id);
            upload_data.append("lead_type", <?= $lead_type_status ?>);
            upload_data.append("skip", skip);
            upload_data.append("save", same_step);
            if (completed === 1) {
                let check_validation = await check_required_fields("final-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
                upload_data.append("sc_100", $("#sc_100").is(":checked") ? 1 : 0);
                upload_data.append("completed", 1);
            } else if (id == 2) {
                let result = await university_shortlisting_dropdown();
                if (!result) {
                    hide_loader();
                    return false;
                }
                await check_university_shortlisting(upload_data);
            }

            if (id == 3) {
                if (skip == 1 || same_step == 1) {

                } else {
                    let check_validation = await check_required_fields("application-form");
                    if (!check_validation) {
                        hide_loader();
                        return false;
                    }
                }
                await check_university_admission(upload_data);
            }

            if (id == 4 && skip == 0) {
                if (skip == 1) {} else {
                    await check_entrance_exam(upload_data);

                    let check_validation = await check_required_fields("entrance-form");
                    if (!check_validation) {
                        hide_loader();
                        return false;
                    }
                }
            }

            if (id == 5) {
                let check_validation = await check_required_fields("legalization-form");
                console.log("check_validation:", check_validation);
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
                await check_legalization(upload_data);
            }

            if (id == 6 && skip == 0) {
                let check_validation = await check_required_fields("fees-deposite-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
                await check_fees_deposite(upload_data);
            }

            if (id == 7) {




                if (same_step == 1) {

                } else {

                    if (university_shortlisting[0].application_file == "") {
                        if (confirm("Admission letter is not uploaded. Are you sure you want to proceed without it?")) {} else {
                            goToStep(2);
                            return false;
                        }
                    }

                    let check_validation = await check_required_fields("invitation-form");
                    if (!check_validation) {
                        hide_loader();
                        return false;
                    }

                }

                await check_invitation_letter(upload_data);
            }

            if (id == 8) {
                let check_validation = await check_required_fields("3-payment-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
                upload_data.append("3_payment", 1);
            }

            if (id == 9) {
                await set_validation_visa();
                let check_validation = await check_required_fields("visa-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
                await check_visa_letter(upload_data);
            }

            let secondary_university_remark = $('.secondary_university_remark').first().val();
            upload_data.append("secondary_university_remark", secondary_university_remark);
            upload_data.append("complete_application", complete_application);

            // AJAX request
            let response = await $.ajax({
                url: "<?= base_url('admin/clients/mbbs_tracker') ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            try {
                response = JSON.parse(response);
            } catch (error) {
                console.error("Error parsing JSON response:", error);
                alert_float("danger", "Invalid server response.");
                return false;
            }

            hide_loader();

            if (response.resp_code === "RCS") {
                if (response.resp_desc != "") {
                    alert_float("success", response.resp_desc);
                }



                if (id == 2) {
                    set_application(response, id);
                }
                if (id == 3 && response.entrance_exams !== undefined) {
                    $(".entrance_div").html('');

                    if (response.entrance_exams != "") {
                        // console.log("createEntranceExamList");
                        createEntranceExamList(response.entrance_exams);

                    } else {
                        // console.log("addPrimaryUniversityExamBlock");

                        addPrimaryUniversityExamBlock()
                    }
                }
                if (id == 4 && response.legalization !== undefined) {
                    createLegalization(response.legalization, id);
                }
                if (id == 5 && response.fees_deposite !== undefined) {
                    createFeesDeposite(response.fees_deposite);
                }
                if (id == 6 && response.invitation !== undefined) {
                    createInvitationLetter(response.invitation);
                }
                if (response.visa_details !== undefined) {
                    set_visa_section(response.visa_details, id);
                }


                if (same_step == 1) {
                    return false;
                }
                if (response.pass_stage !== undefined) {
                    if (response.stage_next_permission !== undefined && response.stage_next_permission == 0) {
                        alert_float("danger", "You do not have permission to view the next stage or take action.");
                        return false;

                    }
                    show_next_stage(response.pass_stage);
                } else {
                    if (completed == 1) {
                        location.reload();
                        return false;
                    }
                    show_next_previous(obj);
                }
            } else {
                if (response.visa_details !== undefined) {
                    set_visa_section(response.visa_details);
                }
                alert_float("danger", response.resp_desc || "An error occurred.");
            }
        } catch (error) {
            hide_loader();
            console.error("Error in next_step:", error);
            alert_float("danger", "An unexpected error occurred.");
        }
    }

    var get_university_exam = <?= json_encode($get_university_exam, true) ?>;
    $("input.previous").click(function() {
        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();
        //de-activate current step on progressbar
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active").addClass("inactive");
        $("#progressbar li").eq($("fieldset").index(previous_fs)).addClass("active").removeClass("previous").removeClass("inactive");
        previous_fs.slideDown();
        current_fs.slideUp("slow");
    });

    function goToStep(index) {

        // Prevent forward navigation
        if ($("#progressbar li.active").index() <= index && complete_application != 1) {
            return false;
        }

        const fieldsets = $("fieldset");
        const currentIndex = fieldsets.index($("fieldset:visible"));
        const current_fs = fieldsets.eq(currentIndex);
        const target_fs = fieldsets.eq(index);

        // Transition fieldsets
        current_fs.slideUp("slow");
        target_fs.slideDown();

        const $progressItems = $("#progressbar li");

        // Remove all step-related classes
        $progressItems.removeClass("active inactive previous permanent_previous");

        // Add class to all steps by default
        $progressItems.addClass("inactive");

        // // Mark previous steps
        $("#progressbar li:lt(" + index + ")").removeClass("inactive").addClass("previous permanent_previous");

        // Mark current step
        $("#progressbar li").eq(index).removeClass("inactive").addClass("active");
        hide_loader();
    }




    function addPrimaryUniversityExamBlock(obj = "") {
        let university_name = "<?= addslashes($admissionpreferences->primary_university) ?>";
        let primary_country = "<?= addslashes($admissionpreferences->primary_country) ?>";
        let client_id = "<?= $client_id ?>"; // Ensure this PHP variable is available

        if (primary_country !== 'Georgia') {
            return false;
        }

        $(obj).parents(".entrance_exam_university_div").last().append("");
        const $container = obj ?
            $(obj).parents(".entrance_exam_university_div").last() :
            $(".entrance_div");

        const $universityDiv = obj ? $("<div>") : $("<div>").addClass("entrance_exam_university_div shadow");

        const $titleRow = $("<div>").addClass("d-flex justify-content-between align-items-center");
        const $title = $("<h4>").addClass("text-left mb-0").text(university_name);
        const $addBtn = $(`
        <button style="display:block!important;" class="btn btn-sm btn-primary add_university_btn" type="button" onclick="addPrimaryUniversityExamBlock(this)">
            <i class="fa fa-plus" aria-hidden="true"></i>
        </button>
    `);
        if (obj == "") {
            $titleRow.append($title).append($addBtn);
        }
        $universityDiv.append($titleRow);

        const $examRow = $("<div>").addClass("row university-entrance-exam mt-3");

        // Hidden fields
        const hiddenFields = `
        <input type="hidden" name="client_id" value="${client_id}" class="form-control">
        <input type="hidden" name="m_university_name" value="${university_name}" class="form-control">
    `;

        // Build exam select options
        let examOptions = `<option value="">Exam Name</option>`;
        $.each(get_university_exam, function(i, exam) {
            if (exam && exam.id && exam.name) {
                examOptions += `<option value="${exam.id}">${exam.name}</option>`;
            }
        });

        const $examCol = $(`
        <div class="col-md-3">
            ${hiddenFields}
            <label>Exam Name</label>
            <select name="exam_id" class="form-control selectpicker" data-width="100%" data-none-selected-text="Exam Name" data-actions-box="true" required>
                ${examOptions}
            </select>
        </div>
    `);

        const $dateCol = $(`
        <div class="col-md-3">
            <label>Exam Date</label>
            <input type="date" name="exam_date" class="form-control">
        </div>
    `);

        var $statusCol = $(`
        <div class="col-md-3">
            <label>Status</label>
            <select name="entrance_status" class="selectpicker form-control">
                <option value="Pending" selected>Pending</option>
                <option value="pass">Pass</option>
                <option value="fail">Fail</option>
                <option value="reschedule">Re-schedule</option>
            </select>
        </div>
    `);


        $examRow.append($examCol, $dateCol, $statusCol);
        // $universityDiv.append($examRow).append("<br>");

        if ($(obj).parents(".entrance_exam_university_div").length > 0) {
            $examRow.append(` <div class="col-md-3"><br><button class="col-md-2 add_document remove_university_btn" type="button" onclick="remove_entrance_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></div>`);
        }
        // Add new university block with its first exam row
        $universityDiv.append($examRow).append("<br>");
        $container.append($universityDiv);



        // Refresh selectpicker
        $(".selectpicker").selectpicker("refresh");
    }


    function createEntranceExamList(data) {
        const $container = $(".entrance_div");

        $container.html(""); // Clear the container

        $.each(data, function(university, exams) {
            if (!university && exams.length > 0) {
                university = exams[0].m_university_name || "Unknown University";
            }

            const $universityDiv = $("<div>").addClass("entrance_exam_university_div shadow");

            const email_button = `
            <div class="text-right">
                <button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(${exams[0].client_id}, 3, '', '${university}')">
                    <i class="fa fa-whatsapp hide-client-type"></i>
                </button>
                <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(${exams[0].client_id}, 2, '', '${university}')">
                    <i class="fa fa-envelope hide-client-type"></i>
                </button>
            </div>`;

            const $title = $("<h4>").addClass("text-left").text(university);
            if (exams[0].batch_id == 0) {
                $title.append(`<button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" type="button" onclick="addPrimaryUniversityExamBlock(this)"><i class="fa fa-plus" aria-hidden="true"></i></button>`);
            }
            $universityDiv.append($title).append(email_button);

            $.each(exams, function(index, exam) {
                const $examRow = $("<div>").addClass("row university-entrance-exam");

                if (exam.batch_id == 0) {
                    // Build the exam options
                    let examOptions = "";
                    $.each(get_university_exam, function(i, examData) {
                        const selected = examData.id == exam.exam_id ? "selected" : "";
                        examOptions += `<option value="${examData.id}" ${selected}>${examData.name}</option>`;
                    });

                    var html = `
                    <input type="hidden" name="batch_id" value="${exam.batch_id}" class="form-control">
                    <input type="hidden" name="client_id" value="${exam.client_id}" class="form-control">
                    <input type="hidden" name="m_university_name" value="${exam.m_university_name}" class="form-control">
                    <input type="hidden" name="m_university_id" value="${exam.m_university_id}" class="form-control">

                    <div class="col-md-3">
                        <label>Exam Name</label>
                        <select name="exam_id" class="form-control selectpicker" data-width="100%" data-none-selected-text="Exam Name" required>
                            ${examOptions}
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Exam Date</label>
                        <input type="date" name="exam_date" value="${exam.exam_date || ''}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Status</label>
                        <select name="entrance_status" class="selectpicker form-control" data-status="${exam.status}">
                            <option value="Pending" ${exam.status === "Pending" ? "selected" : ""}>Pending</option>
                            <option value="pass" ${exam.status === "pass" ? "selected" : ""}>Pass</option>
                            <option value="fail" ${exam.status === "fail" ? "selected" : ""}>Fail</option>
                            <option value="reschedule" ${exam.status === "reschedule" ? "selected" : ""}>Re-schedule</option>
                        </select>
                    </div>
                `;

                    if (exam.batch_id == 0 && index > 0) {
                        html += `<div class="col-md-3"><br><button class="col-md-2  add_document remove_university_btn" type="button" onclick="remove_entrance_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button></div>`;
                    }

                    $examRow.append(html);
                } else {
                    $examRow.append(`
                    <div class="col-md-3">
                        <label>Batch Name</label>
                        <input type="text" value="${exam.batch_name || ''}" readonly class="form-control">
                        <input type="hidden" name="batch_id" value="${exam.batch_id}" class="form-control">
                        <input type="hidden" name="client_id" value="${exam.client_id}" class="form-control">
                        <input type="hidden" name="exam_id" value="${exam.exam_id}" class="form-control">
                         <input type="hidden" name="m_university_name" value="${exam.m_university_name}" class="form-control">
                    <input type="hidden" name="m_university_id" value="${exam.m_university_id}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Exam Name</label>
                        <input type="text" value="${exam.exam_name}" readonly class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Exam Date</label>
                        <input type="date" value="${exam.exam_date || ''}" readonly class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Status</label>
                        <select name="entrance_status" class="selectpicker form-control" data-status="${exam.status}">
                            <option value="Pending" ${exam.status === "Pending" ? "selected" : ""}>Pending</option>
                            <option value="pass" ${exam.status === "pass" ? "selected" : ""}>Pass</option>
                            <option value="fail" ${exam.status === "fail" ? "selected" : ""}>Fail</option>
                            <option value="reschedule" ${exam.status === "reschedule" ? "selected" : ""}>Re-schedule</option>
                        </select>
                    </div>
                `);
                }

                $universityDiv.append($examRow).append("<br>");
            });

            $container.append($universityDiv).append("<hr>");
        });

        // Refresh bootstrap selectpicker
        $(".selectpicker").selectpicker("refresh");
    }


    function createLegalization(legalizationData, tracker_id) {
        let legalizationContainer = $(".legalization_div"); // Target container

        legalizationContainer.html('');
        if (legalizationData.length > 0) {


            legalizationData.forEach((leg) => {

                let mand = "";
                let mand_re = "";
                let base_url = "<?= base_url() ?>";

                if (leg.primary_university == 1) {
                    mand = '<small class="text-danger">*</small>';
                    mand_re = "required required-check";
                }

                let html = `
                <div class="legalization-item card shadow-sm p-3 mb-3">
                    <h4 class="university-name">${leg.university_name}</h4>
                    <input type="hidden" name="id" value="${leg.id}">
            `;

                if (leg.ministry_document_status && leg.ministry_document_status == 1) {
                    let check_min_doc = leg.ministry_document_recived == 1 ? 'checked' : '';
                    let media_view = "";
                    let file = leg.ministry_payment;
                    if (file != "") {
                        media_view = `<div class='margin-top'><i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${base_url}${file}');"></i>&nbsp;
                                                        <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${base_url}${file}', '_blank');"></i>`;

                        if (delete_document_status) {
                            media_view += ` <button class="btn-xs btn btn-danger" onclick="delete_documents(1,${tracker_id},${leg.id})"><i class="fa fa-trash"></i></button>`;
                        }
                        media_view += `</div>`;
                    }
                    html += `
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="form-check-label">&nbsp;</p>
                            <label class="form-check-label">
                                Ministry Order of Documents Received  ${mand}
                                <input type="checkbox" class="form-check-input" ${check_min_doc} ${mand_re} name="ministry_doc_received_${(leg.id)}">
                               
                            </label>
                        </div>
                         <div class="col-md-6">
                            <label>MD Payment Proof ${mand} </label>
                            <input type="file" class="form-control" ${(media_view ?? "") === "" ? mand_re : ""} accept=".pdf,image/*" name="ministry_doc_payment_${(leg.id)}">
                            ${media_view}
                        </div>
                    </div>
                `;
                } else {
                    let check_min_doc = leg.contract_signed == 1 ? 'checked' : '';
                    html += `
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="form-check-label">&nbsp;</p>
                            <label class="form-check-label">
                            Contract Signed ${mand}
                                <input type="checkbox" class="form-check-input" ${check_min_doc} ${mand_re} name="contract_signed_${(leg.id)}">
                                
                            </label>
                        </div>
                    </div>
                `;
                }

                html += `</div>`;
                // console.log(html);
                legalizationContainer.append(html);
            });
        } else {
            legalizationContainer.html('<p class="text-muted">No Fees Data available.</p>');
        }
    }

    function createFeesDeposite(legalization, tracker_id) {
        let feesDepositeDiv = $(".fees_deposite_div");
        feesDepositeDiv.html(""); // Clear existing content

        if (legalization.length > 0) {
            legalization.forEach(leg => {
                let mand = leg.primary_university == 1 ? '<small class="text-danger">*</small>' : '';
                let mand_re = leg.primary_university == 1 ? 'required required-check' : '';
                let base_url = "<?= base_url() ?>";
                let file_url_payment = leg.fees_deposite_slip ? base_url + leg.fees_deposite_slip : "";
                let file_url_university_payment = leg.university_fees_payment_slip ? base_url + leg.university_fees_payment_slip : "";
                let delete_pay = '';

                let payment_delete = "";
                if (delete_document_status) {
                    payment_delete = '<button class="btn-xs btn btn-danger" onclick="delete_documents(3,${tracker_id})"><i class="fa fa-trash"></i></button>';
                }

                let univer_payment_delete = "";
                if (delete_document_status) {
                    univer_payment_delete = `<button class="btn-xs btn btn-danger" onclick="delete_documents(3,${tracker_id},${leg.id})"><i class="fa fa-trash"></i></button>`;
                }
                let itemHtml = `
                <div class="feesDeposite-item card shadow-sm p-3 mb-3">
                    <h4 class="university-name">${$("<div>").text(leg.university_name).html()}</h4>
                    <input type="hidden" name="id" value="${$("<div>").text(leg.id).html()}">

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label>Date of Payment ${mand}</label>
                            <input type="date" class="form-control" value="${leg.fees_deposite_date}" name="date_of_payment_${leg.id}" ${mand_re}>
                        </div>

                        <div class="col-md-3">
                            <label>Payment Proof ${mand}</label>
                            <input type="file" class="form-control" accept=".pdf,image/*" name="payment_slip_${leg.id}" ${file_url_payment ? "" : mand_re}>
                            ${file_url_payment ? `
                                <div class="margin-top">
                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${file_url_payment}');"></i>&nbsp;
                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${file_url_payment}', '_blank');"></i>
                                    ${payment_delete}
                                </div>` : ""}
                        </div>
                        <div class="col-md-3">
                             <label>Payment Amount ${mand}</label>
                             <input type="number" value="${leg.payment_amount}" class="form-control" name="payment_amount_${leg.id}" ${mand_re}>
                        </div>
                        <div class="col-md-3">
                            <label>University Payment Receipt </label>
                            <input type="file" class="form-control" accept=".pdf,image/*" name="university_payment_slip_${leg.id}" ${file_url_university_payment ? "" : ""}>
                            ${file_url_university_payment ? `
                                <div class="margin-top">
                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${file_url_university_payment}');"></i>&nbsp;
                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${file_url_university_payment}', '_blank');"></i>
        ${univer_payment_delete}
                                </div>` : ""}
                        </div>
                    </div>
                </div>`;

                feesDepositeDiv.append(itemHtml);
            });
        } else {
            feesDepositeDiv.html('<p class="text-muted">No Fees available.</p>');
        }
    }

    function createInvitationLetter(legalization, tracker_id) {
        let container = $(".invitation_div");
        container.empty(); // Clear previous content



        if (legalization.length > 0) {
            legalization.forEach(leg => {
                let mand = leg.primary_university == 1 ? '<small class="text-danger">*</small>' : "";
                let mandRe = leg.primary_university == 1 ? "required required-check" : "";
                let telexField = leg.telex_status == 1 ?
                    `
                    <div class="col-md-3">
                        <label>Telex No. ${mand}</label>
                        <input type="date" class="form-control" ${mandRe} name="telex_no_${leg.id}" value="${leg.telex_no ?? ''}">
                    </div>
                ` :
                    "";

                let invitation_letter = "";
                if (delete_document_status) {
                    invitation_letter = `<button class="btn-xs btn btn-danger" onclick="delete_documents(5,${tracker_id},${leg.id})"><i class="fa fa-trash"></i></button>`;
                }

                let file_url_university_payment = leg.invitation_letter ? leg.invitation_letter : "";
                let email_button = `<div class="text-right"><button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(${client_id}, 4,'','${leg.id}')"><i class="fa fa-whatsapp hide-client-type"></i></button> <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(${client_id}, 3,${leg.id})"><i class="fa fa-envelope hide-client-type"></i></button> </div>`;
                let card = `
                <div class="invitation-item card shadow-sm p-3 mb-3">
                    <h4 class="university-name">${leg.university_name}</h4>
            ${email_button}
                    <input type="hidden" name="id" value="${leg.id}">

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label>Date of Receiving ${mand}</label>
                            <input type="date" class="form-control " ${mandRe}
                                name="invitation_receiving_date_${leg.id}" 
                                value="${leg.invitation_receiving_date ?? ''}">
                        </div>

                        <div class="col-md-3">
                            <label>Invitation Letter Upload ${mand}</label>
                            <input type="file" class="form-control " ${file_url_university_payment?'':mandRe}
                                accept=".pdf,image/*" name="invitation_letter_${leg.id}">
                                ${file_url_university_payment ? `
                                <div class="margin-top">
                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${file_url_university_payment}');"></i>&nbsp;
                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${file_url_university_payment}', '_blank');"></i>
                                    ${invitation_letter}
                                </div>` : ""}
                        </div>

                        ${telexField} <!-- Conditionally inserted -->

                        <div class="col-md-3">
                            <label>Entry Date </label>
                            <input type="date" class="form-control "  name="entry_date_${leg.id}"  value="${leg.entry_date ?? ''}">
                        </div>
                    </div>
                </div>
            `;

                container.append(card);
            });
        } else {
            container.text("No Invitation Letter available.");
        }
    }

    function check_university_shortlisting(upload_data) {
        return new Promise(async (resolve, reject) => {
            university_shortlisting = [];
            let stop_status = true;
            $(".add_university_div_block .university_div").each(function() {
                let id = $(this).find("input[name='id']").val();
                let university_name = $(this).find("select[name='select_university']").val();
                let country_name = $(this).find("select[name='select_university'] option:selected").data("country-name");



                if (id === undefined) {
                    id = "";
                }

                // Check for duplicate university and vendor
                const duplicateEntry = university_shortlisting.find(entry => entry.university === university_name);
                if (duplicateEntry) {
                    hide_loader();
                    alert_float("danger", "Duplicate entry found: university '" + university_name + "'.");
                    stop_status = false;
                    return false;
                }

                university_shortlisting.push({
                    "id": id,
                    "university_name": university_name,
                    "country_name": country_name
                });
                // console.log(university_shortlisting);

            });

            upload_data.append("university_shortlisting", JSON.stringify(university_shortlisting));
            resolve(upload_data);

        });
    }

    function check_university_admission(upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let admission = [];

                $(".university_div_application").each(function() {
                    let id = $(this).find("input[name='id']").val();
                    let university = $(this).find(`select[name='university_${id}']`).val();
                    let country = $(this).find(`select[name='country_${id}']`).val();
                    let admission_partner = $(this).find(`select[name='partner_${id}']`).val();
                    let admission_date = $(this).find(`input[name='date_${id}']`).val();
                    let addmission_letter_url = $(this).find(`input[name='admission_letter_path_${id}']`).val();

                    let admission_letter_input = $(this).find(`input[name='admission_letter_${id}']`)[0];

                    let admission_letter = admission_letter_input && admission_letter_input.files.length > 0 ? admission_letter_input.files[0] : null;
                    let admission_docs = $(this).find(`input[name='documents[]']:checked`).map(function() {
                        return $(this).val();
                    }).get().join(","); // Collects checked document values as a comma-separated string

                    // console.log("ID:", id);
                    // console.log("Partner:", admission_partner);
                    // console.log("Application Date:", admission_date);
                    // console.log("Documents:", admission_docs);
                    // console.log("Admission Letter:", admission_letter);

                    // Push admission details to array
                    admission.push({
                        id: id,
                        university: university,
                        country: country,
                        partner: admission_partner,
                        application_date: admission_date,
                        documents: admission_docs,
                        addmission_letter_url: addmission_letter_url
                    });

                    // Append admission letter file if selected
                    if (admission_letter) {
                        upload_data.append(`admission_letter_${id}`, admission_letter);
                    }
                });

                // Append JSON stringified admission data
                upload_data.append("admission", JSON.stringify(admission));

                resolve(upload_data); // Return the collected data
            } catch (error) {
                reject(error); // Handle errors
            }
        });
    }

    // window.onbeforeunload = function() {
    //     return null;
    // };



    // add university 
    async function add_university_div() {
        let response = await is_validate_university();
        if (response) {
            let html = `<div class="col-md-12 university_div university_div_  bg-warning">
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
                                <input type="hidden" name="id">
                                    <select class="selectpicker from-control"  onchange="university_shortlisting_dropdown()" data-width="100%" name="select_university" id="" data-live-search="true">
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
                                                                <option  data-country-name="<?= $country_name ?>" value='<?= $university_name ?>'><?= $university_name ?></option>
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
                                </div>`;


            html += `<div class="col-md-2">
                                <button class="col-md-2 add_document remove_university_btn" type="button" style="display:none;" onclick="remove_university_div(this)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></button>
                                         
                                    
                                </div>
                            </div>`;
            $(".add_university_div_block").append(html);
            select_reinit();
        }

        $(".university_div_").find(".remove_university_btn").show();
        $(".university_div_").find(".add_university_btn").hide();
        $(".university_div_:last").find(".add_university_btn").show();
        if ($(".university_div_").length == 1) {}

    }

    function university_shortlisting_dropdown() {
        let check_university_duplicate = [];
        let stop_status = true;

        $(".add_university_div_block .university_div").each(function() {
            let university_id = $(this).find("input[name='id']").val();
            let select_university = $(this).find("select[name='select_university']").val();

            if (check_university_duplicate.some(entry => entry.university === select_university)) {
                hide_loader();
                alert_float("danger", `Duplicate entry found: university '${select_university}'.`);
                stop_status = false;
                return false; // Stops iteration
            }

            check_university_duplicate.push({
                university: select_university,
                university_id: university_id
            });
        });

        return stop_status;
    }


    function is_validate_university() {
        return new Promise((resolve, reject) => {
            $(".add_university_div_block .university_div").each(function() {
                let select_university = $(this).find("select[name='select_university']").val();
                let select_university_vendor = $(this).find("select[name='select_university_vendor']").val()
                if (select_university === undefined || $.trim(select_university) === "") {
                    $(this).find("select[name='select_university']").focus();
                    alert_float("danger", "Select university is required.");
                    resolve(false);
                    return;
                }
            });

            resolve(true);
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
        if ($(".university_div").length == 1) {}
        is_validate_application_status();
    }

    function remove_entrance_div(obj) {
        $(obj).parents(".university-entrance-exam").remove();
    }

    function is_validate_application_status(status = 0) {
        if (university_shortlisting != undefined && university_shortlisting.length > 0) {
            // let html = '<h3 class="message-notification">Your University under Processing</h3>';
            // $(".university_approval_message_action").html(html);
        }
        return new Promise((resolve) => {
            if ($("#application_div select[name='university_application_status']").length > 0) {
                let check_status = true;
                let university_count = 0;
                let university_count_not = 0;
                let total_university = university_shortlisting.length;

                $("#application_div select[name='university_application_status']").each(function() {
                    if ($(this).val() != 1) {
                        university_count_not++;
                        check_status = false;
                    } else {
                        university_count++;
                    }
                });

                university_count_not = university_shortlisting.filter(obj => obj.university_status === "2").length
                university_count = university_shortlisting.filter(obj => obj.university_status === "1").length

                total_university = ((total_university - university_count) - university_count_not);

                if (check_status) {
                    $("#university_application_status").find("input.next").attr("disabled", false);
                } else {
                    $("#university_application_status").find("input.next").attr("disabled", true);
                }

                // let html = '';

                if (university_count > 0) {
                    if (university_count_not === 0) {
                        check_university_status = true;
                        check_university_status_direct = true;
                        // html = '<h3 class="message-notification success">Your all university is Approved.</h3>';
                        $("#university_div").find("button.next").attr("disabled", false);

                    } else {
                        // html = '<h3 class="message-notification">Your ' + university_count + ' University is Approved. ' + university_count_not + ' is Reject.';

                        if (university_count > 0) {
                            check_university_status = true;
                        }

                        if (total_university > 0) {
                            // html += total_university + ' under processing.tab-content';
                        }
                        // html += '</h3>';
                    }
                } else {
                    // html = '<h3 class="message-notification">Your University under Processing</h3>';
                }
                // $(".university_approval_message_action").html(html);
            }

            resolve();
        });
    }


    function set_application(update_university_status, tracker_id) {
        let ids = update_university_status.ids;

        // Update existing university divs with new IDs
        $(".add_university_div_block .university_div").each(function(index) {
            if (ids[index] !== undefined) {
                $(this).find("input[name='id']").val(ids[index]);
            }
        });

        let university_list = update_university_status.university_shortlisting;
        let html = ""; // Initialize the HTML variable
        $(".application_div").html(html);
        for (let i = 0; i < university_list.length; i++) {
            html = "";
            let university = university_list[i];
            let base_url = "<?= base_url() ?>";
            let file = university.application_file ? base_url + university.application_file : '';

            let mand = "";
            let mand_re = "";
            if (university.primary_university == 1) {
                mand = '<small class="text-danger">*</small>';
                mand_re = "required required-check";
            }


            let media_view = "";
            if (file != "") {
                media_view = `<div class='margin-top'><i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${file}');"></i>&nbsp;
                                                        <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${file}', '_blank');"></i>`;
                if (delete_document_status) {
                    media_view += ` <button class="btn-xs btn btn-danger" onclick="delete_documents(1,${tracker_id},${university.id})"><i class="fa fa-trash"></i></button>`;
                }

                media_view += `</div>`;
            }
            html = `<div class="row university_div_application mt-2 d-flex">
        <div class="col-md-3">
            <label>Country Name ${mand}</label>
            <input type="text" name="country_${university.id}" class="form-control" disabled value="${university.country_name}">
        </div>  
        <div class="col-md-3">
            <label>University Name ${mand}</label>
            <input type="hidden" name="id" value="${university.id}">
            <input type="text" name="university_${university.id}" class="form-control" disabled value="${university.university_name}">
        </div> 
        <div class="col-md-3">
            <label>Partner Name ${mand}</label>
            <select name="partner_${university.id}" class="form-control" ${mand_re}>
                <option value="">Select Partner</option>`;

            for (let partner of university_partner_names) {
                let selected = partner.id == university.partner ? "selected" : "";
                html += `<option value="${partner.id}" ${selected}>${partner.name}</option>`;
            }

            html += `</select>
        </div>
        <div class="col-md-3">
            <label>Application Date ${mand}</label>
            <input type="date" name="date_${university.id}" class="form-control ${mand_re}" value="${university.application_date || ''}" ${mand_re}>
        </div>
        <div class="col-md-3">
            <label>Admission Letter ${mand}</label>
            <input type="hidden" class="form-control" value="${file}" name="admission_letter_path_${university.id}">
            <input type="file" class="form-control" accept=".pdf,image/*" name="admission_letter_${university.id}">
            ${media_view}
        </div>
    </div>`;
            $(".application_div").append(html);
        }

        // Append the generated HTML

    }



    function check_required_fields(id = "application-form") {
        return new Promise((resolve, reject) => {
            let form_status = true;
            let additional_fields = {}; // Ensure additional_fields is defined

            // Validate visible input, select, and date fields
            $("#" + id + " input:visible, #" + id + " select:visible, #" + id + " date:visible").each(function() {
                const value = $(this).val(); // Get the value of the field
                const isRequired = $(this).attr("required-check") !== undefined; // Check for 'required-check' attribute
                const name = $(this).attr("name"); // Get the name attribute

                if ($(this).is(":checkbox") && isRequired) {
                    if (!$(this).is(":checked")) { // Check if checkbox is NOT checked
                        form_status = false;
                        $(this).addClass("error"); // Highlight the checkbox
                    } else {
                        $(this).removeClass("error"); // Remove error highlight if checked
                    }
                }


                if (isRequired && name) {
                    additional_fields[name] = "required";
                    // console.log(additional_fields);
                    if ($.trim(value) === "") {
                        form_status = false;
                        $(this).addClass("error"); // Highlight invalid fields
                    } else {
                        $(this).removeClass("error");
                    }
                }
            });

            if (!form_status) {
                appValidateForm($("#" + id), additional_fields);
                $("#" + id).submit()


                resolve(false);
            } else {
                resolve(true);
            }
        });
    }



    function check_entrance_exam(upload_data) {
        return new Promise((resolve, reject) => {
            let entrance_exam_data = [];

            $(".entrance_exam_university_div .university-entrance-exam").each(function() {
                const batch_id = ($(this).find("input[name='batch_id']").val() || "").trim();
                const client_id = ($(this).find("input[name='client_id']").val() || "").trim();
                const exam_id_input = ($(this).find("input[name='exam_id']").val() || "").trim();
                const exam_id_select = ($(this).find("select[name='exam_id']").val() || "").trim();
                const status = ($(this).find("select[name='entrance_status']").val() || "").trim();
                const exam_date = ($(this).find("input[name='exam_date']").val() || "").trim();
                const m_university_name = ($(this).find("input[name='m_university_name']").val() || "").trim();
                const m_university_id = get_university_list[m_university_name] || "";

                // Prefer input exam_id, fallback to select
                const exam_id = exam_id_input || exam_id_select;

                if (client_id && exam_id) {
                    let exam_obj = {
                        batch_id: batch_id || "0",
                        client_id,
                        exam_id,
                        status,
                        m_university_name,
                        m_university_id,
                    };

                    // Include exam_date only if it's not empty
                    if (exam_date !== "") {
                        exam_obj.exam_date = exam_date;
                    }

                    // Mark manually if batch_id is 0
                    if (batch_id === "" || batch_id === "0") {
                        exam_obj.manually = 1;
                    }

                    entrance_exam_data.push(exam_obj);
                }
            });

            // Append only if data is collected
            if (entrance_exam_data.length > 0) {
                upload_data.append("entrance_exam", JSON.stringify(entrance_exam_data));
            }

            resolve(upload_data);
        });
    }


    function check_legalization(upload_data) {
        return new Promise((resolve, reject) => {
            let legalization = [];

            $(".legalization_div .legalization-item").each(function() {
                let id = $(this).find("input[name='id']").val() || "";
                let ministry_doc_received = $(this).find("input[name='ministry_doc_received_" + id + "']").is(":checked") ? 1 : 0;
                let contract_signed = $(this).find("input[name='contract_signed_" + id + "']").is(":checked") ? 1 : 0;

                let ministryDocPaymentInput = $(this).find("input[name='ministry_doc_payment_" + id + "']")[0];
                let ministry_doc_payment = ministryDocPaymentInput && ministryDocPaymentInput.files.length > 0 ?
                    ministryDocPaymentInput.files[0] :
                    null;

                // Push only if required fields are present
                if (id) {
                    let entry = {
                        id: id.trim(),
                        ministry_doc_received: ministry_doc_received,
                        contract_signed: contract_signed
                    };

                    // Add file separately
                    if (ministry_doc_payment) {
                        upload_data.append("ministry_doc_payment_" + id, ministry_doc_payment);
                    }

                    legalization.push(entry);
                }
            });

            // Append legalization data as JSON
            if (legalization.length > 0) {
                upload_data.append("legalization", JSON.stringify(legalization));
            }

            resolve(upload_data);
        });
    }

    function check_fees_deposite(upload_data) {
        return new Promise((resolve, reject) => {
            let fees_deposite = [];

            $(".fees_deposite_div .feesDeposite-item").each(function() {
                let id = $(this).find("input[name='id']").val() || "";
                let date_of_payment = $(this).find("input[name='date_of_payment_" + id + "']").val() || "";
                let payment_slipInput = $(this).find("input[name='payment_slip_" + id + "']")[0];
                let payment_amount = $(this).find("input[name='payment_amount_" + id + "']").val() || "";
                let university_payment_slipInput = $(this).find("input[name='university_payment_slip_" + id + "']")[0];

                // Validate if ID exists
                if (id.trim()) {
                    let entry = {
                        id: id.trim(),
                        date_of_payment: date_of_payment.trim(),
                        payment_amount: payment_amount
                    };

                    // Append files to FormData if available
                    if (payment_slipInput && payment_slipInput.files.length > 0) {
                        upload_data.append("payment_slip_" + id, payment_slipInput.files[0]);
                    }
                    if (university_payment_slipInput && university_payment_slipInput.files.length > 0) {
                        upload_data.append("university_payment_slip_" + id, university_payment_slipInput.files[0]);
                    }

                    fees_deposite.push(entry);
                }
            });

            // Append fees_deposite data as JSON
            if (fees_deposite.length > 0) {
                upload_data.append("fees_deposite", JSON.stringify(fees_deposite));
            }

            resolve(upload_data);
        });
    }

    function check_invitation_letter(upload_data) {
        return new Promise((resolve, reject) => {
            let invitation = [];

            $(".invitation_div .invitation-item").each(function() {
                let id = $(this).find("input[name='id']").val() || "";
                let receiving_date = $(this).find("input[name='invitation_receiving_date_" + id + "']").val() || "";
                let invitation_letter = $(this).find("input[name='invitation_letter_" + id + "']")[0];
                let entry_date = $(this).find("input[name='entry_date_" + id + "']").val() || "";

                // Validate if ID exists
                if (id.trim()) {
                    let entry = {
                        id: id.trim(),
                        receiving_date: receiving_date.trim(),
                        entry_date: entry_date.trim()
                    };

                    // Append files to FormData if available
                    if (invitation_letter && invitation_letter.files.length > 0) {
                        upload_data.append("invitation_letter_" + id, invitation_letter.files[0]);
                    }
                    invitation.push(entry);
                }
            });

            // Append invitation_letter data as JSON
            if (invitation.length > 0) {
                upload_data.append("invitation", JSON.stringify(invitation));
            }

            resolve(upload_data);
        });
    }

    const visa_vendors = <?= json_encode($visa_vendors, true) ?>;
    const courier_type = <?= json_encode($courier_type, true) ?>;
    const payment_mode = <?= json_encode($payment_mode, true) ?>;

    function set_visa_section(visa_data = [], create = 0, tracker_id = "") {
        let container = document.getElementById('visa-details');
        if (create === 0) {
            container.innerHTML = ''; // Clear existing content
        }

        const createSelect = (name, options, required = false, selectedValue = '', visa_id = '') => {
            // Ensure visa_id is defined as an empty string if not provided
            visa_id = visa_id ?? '';
            let selectName = (visa_id !== '') ? name + "_" + visa_id : name;
            let html = `<select id="${selectName}" name="${selectName}" class="form-control selectpicker" id="" data-live-search="true" data-width="100%" ${required ? 'required-check="true" required="true"' : ''}>`;
            html += `<option value=""></option>`;
            options.forEach(opt => {
                if (opt && opt.id !== undefined) {
                    let selected = (opt.id == selectedValue) ? 'selected' : '';
                    html += `<option value="${opt.id}" ${selected}>${opt.name}</option>`;
                }
            });
            html += `</select>`;
            return html;
        };


        const renderVisaBlock = (visa = {}, index = 1) => {
            let media_view = '';
            let requried = 'required-check="true" required="true"';
            let visa_id = visa.id ?? Math.floor(Math.random() * (999 - 0 + 1)) + 0; // Default to empty string if visa.id is undefined or null
            // console.log(visa_id);
            let random = Math.floor(Math.random() * (999 - 0 + 1)) + 0;
            if (visa.file && visa.file !== "") {
                media_view = `
                <div class='margin-top'>
                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${visa.file}');"></i>&nbsp;
                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${visa.file}', '_blank');"></i>`;

                if (delete_document_status) {
                    media_view += `<button class="btn-xs btn btn-danger" onclick="delete_documents(6,${tracker_id},${visa.id})"><i class="fa fa-trash"></i></button>`;
                }

                media_view += `</div>`;
            }
            let delete_ = ``;
            if ((index > 0 && visa.id != "") || (index == 0 && <?= is_admin() ? 1 : 0 ?> == 1)) {
                delete_ = `<div class="text-right">
        <i class='fa fa-trash btn btn-danger' onclick="remove_visa_div(this,${visa.id ?? 0})"></i>
        </div>`;
            }
            return `
        <div class="col-md-12 visa_div_application  ${visa.status == 4 ? 'visa-rejected-div' : ''}"  >
        ${delete_}
            <input type='hidden' name='id' value='${visa.id ?? ''}'>
            <div class="d-flex">
                <div class="col-md-4">
                <div class="form-group">
                    <label>Visa Vendor <small class='text-danger'>*</small></label>
                    ${createSelect('visa_vendor', visa_vendors, true, visa.vendor_id ?? '', visa_id)}
                    </div>
                </div>
                <div class="col-md-4">
                <div class="form-group">
                    <label>Courier Date <small class='text-danger'>*</small></label>
                    <input type="date" id="visa_date_${visa_id}" name="visa_date_${visa_id}" value="${visa.courier_date ?? ''}" class="form-control" required />
                    </div>
                </div>
                <div class="col-md-4">
                <div class="form-group">
                    <label>Courier Type <small class='text-danger'>*</small></label>
                    ${createSelect('visa_courier_type', courier_type, true, visa.courier_type ?? '', visa_id)}
                    </div>
                </div>
            </div>

            <div class="d-flex visa-payment-details">
                <div class="col-md-4">
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" id="visa_payment_date_${visa_id}" name="visa_payment_date_${visa_id}" value="${visa.payment_date ?? ''}" class="form-control"  ${requried}/>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="form-group">
                    <label>Cost</label>
                    <input type="number" id="visa_cost_${visa_id}" name="visa_cost_${visa_id}" value="${visa.cost ?? ''}" class="form-control"  ${requried}/>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="form-group">
                    <label>Payment Mode</label>
                    ${createSelect('visa_payment_mode', payment_mode, false, visa.payment_mode ?? '', visa_id)}
                    </div>
                </div>
            </div>

            <div class="d-flex visa-receving-details">
                <div class="col-md-4">
                <div class="form-group">
                    <label>Visa Received</label>
                    <input type="date" id="visa_receiving_date_${visa_id}" name="visa_receiving_date_${visa_id}" value="${visa.receiving_date ?? ''}" class="form-control"  ${requried}/>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="form-group">
                    <label>Visa Document</label>
                    <input type="file" data-file="${visa.file ?? '' }" id="visa_file_${visa_id}" name="visa_file_${visa_id}" class="form-control" ${!media_view ? 'required' : ''} />
                    ${media_view}
                    </div>
                </div>
                <div class="col-md-4">
                <div class="form-group">
                    <label>Visa Entry Date</label>
                    <input type="date" id="visa_entry_date_${visa_id}" name="visa_entry_date_${visa_id}" value="${visa.entry_date ?? ''}" class="form-control" ${requried}/>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-right">
             <label class="form-check-label">Visa Rejected
                                                          <input type="checkbox" class="form-check-input" name="visa_rejected_${visa_id}"  id="visa_rejected_${visa_id}"  ${visa.status == 4 ? 'checked' : ''}>
                                                        </label>
            </div>
        </div>`;
        };

        // Render blocks based on visa_data
        if (visa_data.length > 0) {
            let index = 0;
            visa_data.forEach((visa) => {
                container.insertAdjacentHTML('beforeend', renderVisaBlock(visa, index));
                index++;
                if (is_admin == 0) {
                    $(".visa-rejected-div").last().find("select.selectpicker").attr('disabled', true).selectpicker("refresh");
                    $(".visa-rejected-div").find('input').attr("disabled", true);

                }
                $(".visa-details .visa_div_application").last().find("select.selectpicker").selectpicker("refresh");

            });
        } else {
            container.insertAdjacentHTML('beforeend', renderVisaBlock());
            if (is_admin == 0) {
                $(".visa-rejected-div").find('input').attr("disabled", true);
                $(".visa-rejected-div").last().find("select.selectpicker").attr('disabled', true).selectpicker("refresh");
            }
            $(".visa-details .visa_div_application").last().find("select.selectpicker").selectpicker("refresh");
        }
    }

    if (is_admin == 0) {
        $(".visa-rejected-div").find('input').attr("disabled", true);
        $(".visa-rejected-div").find("select.selectpicker").attr('disabled', true).selectpicker("refresh");
    }


    function check_visa_letter(upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let invitation = [];
                $(".visa-rejected-div").find('input').attr("disabled", false);
                $(".visa-rejected-div").find("select.selectpicker").attr('disabled', false).selectpicker("refresh");
                $(".visa_div .visa_div_application").each(function(index) {
                    let container = $(this);
                    let entry = {};
                    let id = container.find("input[name='id']").val() || "";

                    // if (!id.trim()) return;

                    // Automatically collect all input/select values
                    container.find("input[name], select[name]").each(function() {
                        let name = $(this).attr("name");
                        name = name.replace(/_\d+$/, '');
                        let value = $(this).val();

                        // Handle file input separately
                        if ($(this).attr("type") === "file") {
                            let fileInput = this;
                            if (fileInput.files.length > 0) {
                                upload_data.append(name + "_" + index, fileInput.files[0]);
                            }
                        } else if ($(this).attr("type") === "checkbox") {
                            // For checkboxes, store 1 if checked, 0 if not
                            value = $(this).is(":checked") ? 1 : 0;
                            entry[name] = value;
                        } else {
                            value = $(this).val();
                            entry[name] = value;
                        }
                    });

                    invitation.push(entry);
                });

                if (invitation.length > 0) {
                    upload_data.append("visa", JSON.stringify(invitation));
                }
                $(".visa-rejected-div").find('input').attr("disabled", true);
                $(".visa-rejected-div").find("select.selectpicker").attr('disabled', true).selectpicker("refresh");

                resolve(upload_data);
            } catch (err) {
                reject("Error collecting visa letter data: " + err.message);
            }
        });
    }


    async function email_send(client_id, type, s_university_id = "", s_university_name = "") {
        show_loader();

        let upload_data = new FormData();
        upload_data.append("client_id", client_id);
        upload_data.append("type", type);
        upload_data.append("s_university_id", s_university_id);
        upload_data.append("s_university_name", s_university_name);
        upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);


        try {
            let response = await $.ajax({
                url: "<?= base_url('admin/clients/email_send_trigger') ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            let uploadResponse = JSON.parse(response);

            if (uploadResponse.success) {
                alert_float("success", uploadResponse.message || "Email sent successfully!");
            } else {
                alert_float("danger", uploadResponse.message || "Failed to send email.");
            }

            return uploadResponse;
        } catch (error) {
            console.error("Email send error:", error);
            alert_float("danger", "An error occurred while sending the email.");
            return {
                success: false,
                message: "An error occurred while sending the email."
            };
        } finally {
            hide_loader();
        }
    }



    async function registration_slip_generate(client_id, slip = 0, whatsapp = 0, email = 0) {
        show_loader();

        let upload_data = new FormData();
        upload_data.append("client_id", client_id);
        upload_data.append("slip_generate", slip);
        upload_data.append("whatsapp_send", whatsapp);
        upload_data.append("email_send", email);
        upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

        try {
            let response = await $.ajax({
                url: "<?= base_url('admin/clients/generate_registration_slip') ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false,
            });

            let uploadResponse = typeof response === "string" ? JSON.parse(response) : response;

            if (uploadResponse.resp_code == "RCS") {
                let slip_data = uploadResponse.slip_data;
                if (uploadResponse.slip_generate == 1 && slip_data && slip_data.url) {
                    let html = `<label>Registration Slip</label>
                            <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url() ?>${slip_data.url}');"></i>&nbsp;
                            <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url() ?>${slip_data.url}', '_blank');"></i>`;
                    // Assuming you need to display the generated HTML somewhere
                    $(".registration-slip-invoice").html(html);
                }
                alert_float("success", uploadResponse.resp_desc || "Email sent successfully!");
            } else {
                alert_float("danger", uploadResponse.resp_desc || "Failed to send email.");
            }

            return uploadResponse;
        } catch (error) {
            console.error("Email send error:", error);
            alert_float("danger", "An error occurred while sending the email.");
            return {
                success: false,
                message: "An error occurred while sending the email.",
            };
        } finally {
            hide_loader();
        }
    }

    async function reset_university_shortlisting() {

        if (confirm("Are you sure you want to proceed? This action will reset the university shortlisting for the selected client.")) {

            show_loader();

            let upload_data = new FormData();
            upload_data.append("client_id", client_id);
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);

            try {
                let response = await $.ajax({
                    url: "<?= base_url('admin/clients/reset_university_shortlisting') ?>",
                    method: "POST",
                    data: upload_data,
                    contentType: false,
                    processData: false,
                });

                response = typeof response === "string" ? JSON.parse(response) : response;

                if (response.resp_code == "RCS") {
                    $(".add_university_div_block").html('');
                    add_university_div();
                    alert_float("success", response.resp_desc);

                } else {
                    alert_float("danger", response.resp_desc);
                }

                return Response;
            } catch (error) {
                console.error("Email send error:", error);
                alert_float("danger", "An error occurred while sending the email.");
                return {
                    success: false,
                    message: "An error occurred while sending the email.",
                };
            } finally {
                hide_loader();
            }
        }
    }


    async function add_visa_div() {
        await set_validation_visa();
        let check_validation = await check_required_fields("visa-form");
        if (!check_validation) return false;
        set_visa_section([], 1);
    }

    async function remove_visa_div(obj, id) {

        if (id == "" || id == 0) {
            $(obj).parents("div.visa_div_application").remove();
            return false;
        }

        let upload_data = new FormData();
        try {

            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("visa_id", id);


            let response = await $.ajax({
                url: "<?= base_url("admin/clients/remove_visa_details") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });
            response = JSON.parse(response);
            if (response.resp_code === "RCS") {
                alert_float("success", response.resp_desc);
                $(obj).parents("div.visa_div_application").remove();

            } else {
                if (response.resp_code !== undefined) {
                    alert_float("danger", response.resp_desc);
                } else {
                    alert_float("danger", response);
                }
            }
        } catch (error) {
            // Handle the error response from the server
            console.error(error);
            reject(error);
        }

    }

    $("#primary_university").on("change", async function() {
        const country_name = $(this).find("option:selected").data("country") || "";
        $("#primary_country").val(country_name);

        const primaryUniversity = $('#primary_university').val();
        const primaryCountry = $('#primary_country').val();

        // console.log(primaryUniversity);
        // console.log(primaryCountry);

        let upload_data = new FormData();

        try {
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("primaryUniversity", primaryUniversity);
            upload_data.append("primaryCountry", primaryCountry);

            const response = await $.ajax({
                url: "<?= base_url("admin/clients/select_primary_university") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });

            const parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;

            if (parsedResponse.resp_code === "RCS") {
                alert_float("success", parsedResponse.resp_desc);
            } else {
                alert_float("danger", parsedResponse.resp_desc || "Something went wrong.");
            }
        } catch (error) {
            console.error("AJAX error:", error);
            alert_float("danger", "Server error occurred. Please try again.");
        }
    });
</script>