<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$staff_list              = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list, null, "staffid");
$selected_university_shortlisting = array_column($university_shortlisting, null, "id");
$applicant_tracker = applicant_tracker_study($lead_type_status);
$applicant_pendency = applicant_pendency();
$pendency_status = applicant_pendency_status();
$pendencyStaus = pendency_status();
$offerLetterStatus = offerletterStatus();
$get_currencies = get_currencies();
$get_currencies = array_column($get_currencies, null, 'id');

$applicant_status = !empty($client->tracker_id) ? $client->tracker_id : 0;
$activeShortlistingId = "";
if (!empty($_GET['shortlisting_id'])) {
    $activeShortlistingId = $_GET['shortlisting_id'];
    $selected_university_shortlisting = $selected_university_shortlisting[$_GET['shortlisting_id']];
    $applicant_status = $selected_university_shortlisting["tracker_id"];
}

$study_abroad_vendors = study_abroad_vendors();
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

    .row.pendency-div {
        margin-top: 20px;
    }

    .ms-5 {
        margin-left: 10px;
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
?>
<?php
if (empty($staff_list[get_staff_user_id()]["post_sales"]) && !is_admin()) {
?>
    <h2 class="text-center">Applicant Tracker - Accessible Only for Post-Sale & Admin</h2>
<?php
}
?>
<div class="row">
    <div id="msform" class="col-md-12 ">
        <h1 class="text-center mb-5"><?= !empty($selected_university_shortlisting['university_name']) ? $selected_university_shortlisting['university_name'] : '' ?></h1>
        <br>
        <br>
        <!-- <form id="msform" onsubmit="return false;"> -->
        <ul id="progressbar" class="d-flex justify-content-center">
            <?php
            foreach ($applicant_tracker as $key => $track) {
                if ($track["show_stages"] == 0 || ($track["show_stages"] == 1 && $activeShortlistingId > 0)) {
            ?>
                    <li data-id="<?= $track['id'] ?>" onclick="goToStep(<?= $key ?>)" data-show="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '' ?>"><?= $track["name"] ?></li>
            <?php
                }
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
                    if ($track["show_stages"] == 0 || ($track["show_stages"] == 1 && $activeShortlistingId > 0)) {

                ?>
                        <fieldset id="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '12' ?>" style="display:<?= ($applicant_status == $k) ? "show" : "none" ?>">
                            <h2 class="fs-title text-center" style="margin-bottom: 20px!important;"><?= !empty($track["name"]) ? $track["name"] : 'Document' ?>

                                <?php
                                if ($track["show_div_name"] == "visa_div") { ?>
                                    <button style="display:block!important;" class="col-md-2 add_document add_university_btn float-right" style="display:none;" type="button" onclick="add_visa_div()"><i class="fa fa-plus" aria-hidden="true"></i></button>

                                <?php } ?>
                            </h2>
                            <?php if ($track["show_div_name"] == "document_div") { ?>

                                <div class="text-right hide">

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


                                                                <?php if (empty($applicant_documents[$doc_id]["approval_status"])) : ?>
                                                                    <div class="action_button_<?= $doc_id ?> m">
                                                                        <button class="btn-xs btn btn-success" onclick="document_approved(this, <?= $doc_id ?>, 1)"><i class="fa fa-check"></i></button>
                                                                        <button class="btn-xs btn btn-danger" onclick="document_approved(this, <?= $doc_id ?>, 2)"><i class="fa fa-times"></i></button>
                                                                    </div>
                                                                <?php else :
                                                                    $approval_status = $applicant_documents[$doc_id]["approval_status"];
                                                                    $status_text = ($approval_status == 1) ? 'Approved' : 'Rejected';
                                                                    $status_text_color = ($approval_status == 1) ? 'text-success' : 'text-danger';
                                                                ?>
                                                                    &nbsp;
                                                                    <?php if ($delete_document_status) { ?>
                                                                        <button class="btn-xs btn btn-danger" onclick="document_approved(this,<?= $doc_id ?>)"><i class="fa fa-trash"></i></button>
                                                                    <?php } ?>
                                                                    <span class="<?= $status_text_color ?> ms-5"><b><?= $status_text ?></b></span>
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
                            ?>
                                <table id="dynamicTable" class="table table-clients-shortlisting" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Country Name</th>
                                            <th>University Name</th>
                                            <th>Course Name</th>
                                            <th>Session Intake</th>
                                            <th>Is Primary</th>
                                            <th>Vendor Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($university_shortlisting) && is_array($university_shortlisting)): ?>
                                            <?php foreach ($university_shortlisting as $shortlisting): ?>
                                                <tr>
                                                    <td><input type="radio" <?= !empty($activeShortlistingId) && $activeShortlistingId == $shortlisting['id'] ? "checked" : '' ?> name="universitySelection" onclick="universitySelection(<?= htmlspecialchars($shortlisting['id'] ?? '') ?>)"> &nbsp; <?= htmlspecialchars($shortlisting['country_name'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($shortlisting['university_name'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($shortlisting['course_name'] ?? '-') ?></td>
                                                    <td><?php
                                                        $intake = $shortlisting['session_intake'] ?? '';
                                                        if (!empty($intake) && preg_match('/^\d{4}-\d{2}$/', $intake)) {
                                                            $date = DateTime::createFromFormat('Y-m', $intake);
                                                            echo $date ? $date->format('M Y') : '-';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?></td>
                                                    <td><?= (!empty($shortlisting['is_primary']) && $shortlisting['is_primary'] == 1) ? 'Yes' : 'No' ?></td>
                                                    <td><?= htmlspecialchars($shortlisting['vendor_name'] ?? '-') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No shortlisting data available.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            <?php } else if ($track["show_div_name"] == "st3_div") {

                            ?>
                                <form id="st3-form" class="form-disabled mb-5" onsubmit=" return false;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?= render_select('vendor', $study_abroad_vendors, array('id', 'name'), "Vendor Names <small class='text-danger'>*</small>", [isset($selected_university_shortlisting['vendor_id']) ? $selected_university_shortlisting['vendor_id'] : ''], ["required" => "required", "required-check" => "required-check"]) ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Sop <small class='text-danger'>*</small></label>
                                            <input type="file" class="form-control" <?= isset($selected_university_shortlisting['sop']) ? '' : 'required required-check' ?> accept=".pdf,image/*" name="sop">
                                            <?php
                                            $file_url_sop = isset($selected_university_shortlisting['sop']) ? $selected_university_shortlisting['sop'] : '';
                                            if (!empty($file_url_sop)) { ?>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_sop) ?>');"></i>&nbsp;
                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_sop) ?>', '_blank');"></i>
                                                    <?php if ($delete_document_status) { ?>
                                                        <button class="btn-xs btn btn-danger" onclick="delete_documents_study(1,<?= $track['id'] ?>,<?= $activeShortlistingId ?>)"><i class="fa fa-trash"></i></button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-3">
                                            <?= render_input('application_date', "Application Date <small class='text-danger'>*</small>", isset($selected_university_shortlisting['application_date']) ? $selected_university_shortlisting['application_date'] : '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>
                                        <div class="col-md-3">
                                            <?php
                                            // Determine if the select should be disabled
                                            $isDisabled = !empty($applicant_pendency[$activeShortlistingId][$track['id']]);
                                            $disabledArray = $isDisabled ? ['disabled' => 'disabled'] : [];
                                            $extraAttributes = array_merge(
                                                [
                                                    "required" => "required",
                                                    "required-check" => "required-check",
                                                    "onchange" => "create_pendency(this.value, {$track['id']})"
                                                ],
                                                $disabledArray
                                            );
                                            ?>
                                            <?= render_select(
                                                'pendency_st3',
                                                $pendencyStaus,
                                                ['id', 'name'],
                                                "Pendency <small class='text-danger'>*</small>",
                                                [isset($selected_university_shortlisting['st3_pendency']) ? $selected_university_shortlisting['st3_pendency'] : ''],
                                                $extraAttributes
                                            ) ?>
                                        </div>
                                    </div>
                                    <div id="pendency_<?= $track['id'] ?>" class="panel-body <?= isset($selected_university_shortlisting['st3_pendency']) && $selected_university_shortlisting['st3_pendency'] == 2 ? '' : 'hide' ?> mt-5">
                                        <button class="btn btn-primary float-right" type="button" onclick="new_pendency_create(<?= $track['id'] ?>)"><i class='fa fa-plus'></i></button>
                                        <?php
                                        if (!empty($applicant_pendency[$activeShortlistingId][$track['id']])) {
                                            foreach ($applicant_pendency[$activeShortlistingId][$track['id']] as $pendency_ut3) {
                                        ?>
                                                <div class="row pendency-div">
                                                    <input type="hidden" name="pendency_id" class="pendency_id" value="<?= $pendency_ut3['id'] ?>">
                                                    <div class="col-md-8">
                                                        <label for="remark_<?= $pendency_ut3['id'] ?>">Remark <small class='text-danger'>*</small></label>
                                                        <textarea
                                                            rows="4"
                                                            id="remark_<?= $pendency_ut3['id'] ?>"
                                                            name="remark_<?= $pendency_ut3['id'] ?>"
                                                            class="form-control"
                                                            required
                                                            required-check><?= $pendency_ut3['remark'] ?></textarea>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <?= render_select(
                                                            "pendency_select_{$pendency_ut3['id']}",
                                                            $pendency_status,
                                                            ['id', 'name'],
                                                            "Status <small class='text-danger'>*</small>",
                                                            [$pendency_ut3['status']],
                                                            [
                                                                "required" => "required",
                                                                "required-check" => "required-check",
                                                            ]
                                                        ) ?>
                                                    </div>
                                                </div>
                                            <?php
                                            }
                                        } else {
                                            ?>
                                            <div class="row pendency-div">
                                                <input type="hidden" name="pendency_id" class="pendency_id" value="">
                                                <div class="col-md-8">
                                                    <label for="remark_1">Remark <small class='text-danger'>*</small></label>
                                                    <textarea
                                                        rows="4"
                                                        id="remark_1"
                                                        name="remark_1"
                                                        class="form-control"
                                                        required
                                                        required-check></textarea>
                                                </div>

                                                <div class="col-md-3">
                                                    <?= render_select(
                                                        "pendency_select_1",
                                                        $pendency_status,
                                                        ['id', 'name'],
                                                        "Status <small class='text-danger'>*</small>",
                                                        [],
                                                        [
                                                            "required" => "required",
                                                            "required-check" => "required-check",
                                                        ]
                                                    ) ?>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>

                                    </div>

                                </form>
                            <?php } else if ($track["show_div_name"] == "stu_div") { ?>
                                <form id="stu-form" class="form-disabled mb-5" onsubmit=" return false;">
                                    <div class="row">
                                        <div class="col-md-3">

                                            <?= render_input('submitted_date', "Submitted Date <small class='text-danger'>*</small>", isset($selected_university_shortlisting['submitted_date']) ? $selected_university_shortlisting['submitted_date'] : '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>
                                        <div class="col-md-3">
                                            <?php
                                            // Determine if the select should be disabled
                                            $isDisabled = !empty($applicant_pendency[$activeShortlistingId][$track['id']]);
                                            $disabledArray = $isDisabled ? ['disabled' => 'disabled'] : [];
                                            $extraAttributes = array_merge(
                                                [
                                                    "required" => "required",
                                                    "required-check" => "required-check",
                                                    "onchange" => "create_pendency(this.value, {$track['id']})"
                                                ],
                                                $disabledArray
                                            );
                                            ?>
                                            <?= render_select(
                                                'pendency_stu',
                                                $pendencyStaus,
                                                ['id', 'name'],
                                                "Pendency <small class='text-danger'>*</small>",
                                                [isset($selected_university_shortlisting['stu_pendency']) ? $selected_university_shortlisting['stu_pendency'] : ''],
                                                $extraAttributes
                                            ) ?>
                                        </div>
                                    </div>

                                    <div id="pendency_<?= $track['id'] ?>" class="panel-body <?= isset($selected_university_shortlisting['stu_pendency']) && $selected_university_shortlisting['stu_pendency'] == 2 ? '' : 'hide' ?> mt-5">
                                        <button class="btn btn-primary float-right" type="button" onclick="new_pendency_create(<?= $track['id'] ?>)"><i class='fa fa-plus'></i></button>
                                        <?php
                                        if (!empty($applicant_pendency[$activeShortlistingId][$track['id']])) {
                                            foreach ($applicant_pendency[$activeShortlistingId][$track['id']] as $pendency_stu) {
                                        ?>
                                                <div class="row pendency-div">
                                                    <input type="hidden" name="pendency_id" class="pendency_id" value="<?= $pendency_stu['id'] ?>">
                                                    <div class="col-md-8">
                                                        <label for="remark_<?= $pendency_stu['id'] ?>">Remark <small class='text-danger'>*</small></label>
                                                        <textarea
                                                            rows="4"
                                                            id="remark_<?= $pendency_stu['id'] ?>"
                                                            name="remark_<?= $pendency_stu['id'] ?>"
                                                            class="form-control"
                                                            required
                                                            required-check><?= $pendency_stu['remark'] ?></textarea>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <?= render_select(
                                                            "pendency_select_{$pendency_stu['id']}",
                                                            $pendency_status,
                                                            ['id', 'name'],
                                                            "Status <small class='text-danger'>*</small>",
                                                            [$pendency_stu['status']],
                                                            [
                                                                "required" => "required",
                                                                "required-check" => "required-check",
                                                            ]
                                                        ) ?>
                                                    </div>
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>

                                    </div>
                                </form>
                            <?php } else if ($track["show_div_name"] == "offer_letter_div") {
                                $file_url_offer_letter = isset($selected_university_shortlisting['offer_letter']) ? $selected_university_shortlisting['offer_letter'] : '';
                            ?>
                                <form id="offer-letter-form" class="form-disabled mb-5" onsubmit=" return false;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?= render_input('offer_date', "Offer Letter Receving <small class='text-danger'>*</small>", isset($selected_university_shortlisting['offer_date']) ? $selected_university_shortlisting['offer_date'] : '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label for="university_offer_status">
                                                Status <small class="text-danger">*</small>
                                            </label>
                                            <select
                                                name="university_offer_status"
                                                id="university_offer_status"
                                                class="form-control selectpicker required-check"
                                                required-check
                                                required
                                                onchange="changeOfferStatus(this)">
                                                <option value="">Select an option</option>
                                                <?php foreach ($offerLetterStatus as $item): ?>
                                                    <?php
                                                    // Skip the 'upload_status' non-array element
                                                    if (!is_array($item)) continue;
                                                    ?>
                                                    <option
                                                        data-upload_status="<?= htmlspecialchars($item['upload_status'] ?? '') ?>"
                                                        value="<?= htmlspecialchars($item['id']) ?>"
                                                        <?= isset($selected_university_shortlisting['university_offer_status']) && $item['id'] == $selected_university_shortlisting['university_offer_status'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </option>

                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 offer-letter-div <?= !empty($offerLetterStatus[$selected_university_shortlisting['university_offer_status']]) ? "" : "hide" ?> form-group">
                                            <label for="university_offer_letter">
                                                Offer Upload <small class="text-danger">*</small>
                                            </label>
                                            <input type="file" data-fileUrl="<?= $file_url_offer_letter ?>" class="form-control" <?= !empty($selected_university_shortlisting['offer_letter']) ? '' : 'required required-check' ?> accept=".pdf,image/*" name="offer_letter">
                                            <?php

                                            if (!empty($file_url_offer_letter)) { ?>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_offer_letter) ?>');"></i>&nbsp;
                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_offer_letter) ?>', '_blank');"></i>
                                                    <?php if ($delete_document_status) { ?>
                                                        <button class="btn-xs btn btn-danger" onclick="delete_documents_study(2,<?= $track['id'] ?>,<?= $activeShortlistingId ?>)"><i class="fa fa-trash"></i></button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>

                                    </div>
                                    <div>
                                        <label>Remark</label>
                                        <textarea rows="4" class="form-control" id="remark_offer_letter"><?= !empty($selected_university_shortlisting['conditional_notes']) ? trim($selected_university_shortlisting['conditional_notes']) : '' ?>
                                    </textarea>
                                    </div>


                                </form>
                            <?php } else if ($track["show_div_name"] == "pre_deposite_div") {
                                $file_url_fees_deposite_slip = isset($selected_university_shortlisting['fees_deposite_slip']) ? $selected_university_shortlisting['fees_deposite_slip'] : '';
                            ?>
                                <form id="pre-deposite-form" class="form-disabled mb-5" onsubmit=" return false;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?= render_input('tentative_date', "Tentative Date <small class='text-danger'>*</small>", isset($selected_university_shortlisting['tentative_date']) ? $selected_university_shortlisting['tentative_date'] : '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>



                                        <div class="col-md-3">
                                            <!-- render_input('payment_amount', "Payment Amount <small class='text-danger'>*</small>", !empty($selected_university_shortlisting['payment_amount']) ? $selected_university_shortlisting['payment_amount'] : '', 'number', ['required-check' => 'required-check', 'required' => 'required']);  -->
                                            <label>Payment Amount <small class='text-danger'>*</small></label>
                                            <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                                                <input type="number" name="payment_amount" value="<?= !empty($selected_university_shortlisting['payment_amount']) ? $selected_university_shortlisting['payment_amount'] : '' ?>" required required-check class="form-control" placeholder="0.00" id="" value="" size="8">
                                                <div class="input-group-addon currency-addon">

                                                    <select name="payment_currency_id" id="<?= $field_name ?>" class="currency-selector">
                                                        <?php foreach ($get_currencies as $c) {
                                                        ?>
                                                            <option

                                                                value="<?= $c['id'] ?>"
                                                                data-placeholder="0.00"
                                                                <?=

                                                                (!empty($selected_university_shortlisting['payment_currency_id']) && $selected_university_shortlisting['payment_currency_id'] == $c['id'])
                                                                    ? 'selected'
                                                                    : ''
                                                                ?>>
                                                                <?= $c['name'] ?>
                                                            </option>


                                                        <?php
                                                        }
                                                        ?>

                                                    </select>

                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-md-3">
                                            <?= render_input('fees_deposite_date', "Date of Deposite <small class='text-danger'>*</small>", isset($selected_university_shortlisting['fees_deposite_date']) ? $selected_university_shortlisting['fees_deposite_date'] : '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>






                                        <div class="col-md-3 offer-letter-div form-group">
                                            <label for="proof_deposite">
                                                Proof of deposit <small class="text-danger">*</small>
                                            </label>
                                            <input type="file" data-fileUrl="<?= $file_url_offer_letter ?>" class="form-control" <?= !empty($selected_university_shortlisting['fees_deposite_slip']) ? '' : 'required required-check' ?> accept=".pdf,image/*" name="fees_deposite_slip">
                                            <?php

                                            if (!empty($file_url_fees_deposite_slip)) { ?>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_fees_deposite_slip) ?>');"></i>&nbsp;
                                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_fees_deposite_slip) ?>', '_blank');"></i>
                                                    <?php if ($delete_document_status) { ?>
                                                        <button class="btn-xs btn btn-danger" onclick="delete_documents_study(3,<?= $track['id'] ?>,<?= $activeShortlistingId ?>)"><i class="fa fa-trash"></i></button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>
                            <?php } ?>
                            <?php if ($k > 0 && $k < 5) { ?>
                                <p class='col-12 margin-top hide'>
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
    var client_id = <?= !empty($client_id) ? $client_id : '' ?>;
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
    var delete_document_status = <?= !empty($delete_document_status) ? $delete_document_status : 0 ?>;
    var admissionpreferences_freeze = 0;
    var base_url = "<?= base_url() ?>";
    //jQuery time
    let lead_type_status = "<?= $lead_type_status ?>";
    // console.log(lead_type_status);
    let documents_type_dropdown = <?= json_encode($documents_type_dropdown) ?>; // Get your data from PHP
    var applicant_status = "<?= $applicant_status ?>";
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches

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
    var activeShortlistingId = <?= !empty($activeShortlistingId) ? $activeShortlistingId : 0 ?>;
    var check_university_status = false;
    var check_university_status_direct = false;
    var check_university_status_submit = false;
    var table_notes = "";
    var notes_url = "";
    var activity_url = "";
    var check_offer_letter = true;
    var fee_status = "<?= !empty($short_list["fee_status"]) ? $short_list["fee_status"] : 0 ?> ";


    if (applicant_status >= 2 && activeShortlistingId == 0) {
        applicant_status = 1;
        $('#university_div').show();
        goToStep(applicant_status);
    }



    notes_url = "<?= base_url() ?>admin/clients/get_application_notes_study/<?= $client_id ?>";
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

    var selectedUniversityShortListing = "<?= isset($_GET['shortlisting_id']) ? htmlspecialchars($_GET['shortlisting_id'], ENT_QUOTES, 'UTF-8') : '' ?>";

    const current_active_url = new URL(window.location.href);
    const params = current_active_url.searchParams;

    function universitySelection(shortlisting_id) {
        selectedUniversityShortListing = shortlisting_id;

        // Update URL path (without reloading the page)
        params.set('shortlisting_id', shortlisting_id);
        window.history.pushState({}, '', `${current_active_url.pathname}?${params.toString()}`);

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

        if (id == 2) {

            if (selectedUniversityShortListing == "") {
                alert_float("danger", "Select University first.");
                return false;
            }

        }

        if (id == 3) {
            if (same_step == 0) {
                let check_validation = await check_required_fields("st3-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
            }

            await check_st3(id, upload_data);
        }
        if (id == 4) {
            if (same_step == 0) {
                let check_validation = await check_required_fields("stu-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
            }

            await check_stu(id, upload_data);
        }
        if (id == 5) {
            if (same_step == 0) {
                let check_validation = await check_required_fields("offer-letter-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
            }
            await check_offer_letter_form(upload_data);
        }
        if (id == 6) {
            if (same_step == 0) {
                let check_validation = await check_required_fields("pre-deposite-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
            }

            await check_pre_deposite_form(upload_data);
        }
        try {
            upload_data.append("shortlisting_id", selectedUniversityShortListing);
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
                params.set('shortlisting_id', selectedUniversityShortListing);
                window.history.pushState({}, '', `${current_active_url.pathname}?${params.toString()}`);


            }

            let secondary_university_remark = $('.secondary_university_remark').first().val();
            upload_data.append("secondary_university_remark", secondary_university_remark);
            upload_data.append("complete_application", complete_application);

            // AJAX request
            let response = await $.ajax({
                url: "<?= base_url('admin/clients/study_tracker') ?>",
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
                    location.reload();
                    return false;
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
                if (response.resp_desc != "") {
                    alert_float("danger", response.resp_desc);
                }
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

    function check_st3(id, upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let st3 = [];

                let vendor = $("#st3-form").find("select[name='vendor']").val() || '';
                let sopInput = $("#st3-form").find("input[name='sop']")[0];
                let application_date = $("#st3-form").find("input[name='application_date']").val() || '';
                let pendency = $("#st3-form").find("select[name='pendency_st3']").val() || '';
                let pendencyArray = [];

                if (sopInput && sopInput.files.length > 0) {
                    upload_data.append("sop", sopInput.files[0]);
                }

                if ($("#pendency_" + id + " .pendency-div").length > 0) {
                    $("#pendency_" + id + " .pendency-div").each(function() {
                        let pendency_id = $(this).find("input.pendency_id").val() || '';
                        let remark = $(this).find("textarea").val() || '';
                        let status = $(this).find("select").val() || '';

                        pendencyArray.push({
                            id: pendency_id,
                            remark: remark,
                            status: status
                        });
                    });
                }

                st3.push({
                    vendor_id: vendor,
                    application_date: application_date,
                    st3_pendency: pendency,
                    pendencyArray: pendencyArray
                });

                upload_data.append("st3", JSON.stringify(st3));

                resolve(upload_data);
            } catch (error) {
                reject(error);
            }
        });
    }

    function check_stu(id, upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let stu = [];

                let submitted_date = $("#stu-form").find("input[name='submitted_date']").val() || '';
                let pendency = $("#stu-form").find("select[name='pendency_stu']").val() || '';
                let pendencyArray = [];

                if ($("#pendency_" + id + " .pendency-div").length > 0) {
                    $("#pendency_" + id + " .pendency-div").each(function() {
                        let pendency_id = $(this).find("input.pendency_id").val() || '';
                        let remark = $(this).find("textarea").val() || '';
                        let status = $(this).find("select").val() || '';

                        pendencyArray.push({
                            id: pendency_id,
                            remark: remark,
                            status: status
                        });
                    });
                }

                stu.push({
                    submitted_date: submitted_date,
                    stu_pendency: pendency,
                    pendencyArray: pendencyArray
                });

                upload_data.append("stu", JSON.stringify(stu));

                resolve(upload_data);
            } catch (error) {
                reject(error);
            }
        });
    }

    function check_offer_letter_form(upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let offer_letter_array = [];
                let offer_date = $("#offer-letter-form").find("input[name='offer_date']").val() || '';
                let university_offer_status = $("#offer-letter-form").find("select[name='university_offer_status']").val() || '';
                let selectedOption = $("#offer-letter-form").find("select[name='university_offer_status'] option:selected");
                let upload_status = selectedOption.data('upload_status') || '';
                let offer_letter = $("#offer-letter-form").find("input[name='offer_letter']")[0];
                let offer_letter_url = $("#offer-letter-form").find("input[name='offer_letter']").data("fileurl");
                let remark = $("#remark_offer_letter").val();
                if (offer_letter && offer_letter.files.length > 0 && upload_status == 1) {
                    upload_data.append("offer_letter", offer_letter.files[0]);
                } else {
                    if (offer_letter_url == "") {
                        upload_data.append("offer_letter_url", "");
                    }
                }


                offer_letter_array.push({
                    offer_date: offer_date,
                    university_offer_status: university_offer_status,
                    offer_letter_url: offer_letter_url,
                    remark: remark,
                    upload_status: upload_status
                });

                upload_data.append("offer_letter_data", JSON.stringify(offer_letter_array));

                resolve(upload_data);
            } catch (error) {
                reject(error);
            }
        });
    }

    function check_pre_deposite_form(upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let pre_deposite_array = [];
                let tentative_date = $("#pre-deposite-form").find("input[name='tentative_date']").val() || '';
                let fees_deposite_date = $("#pre-deposite-form").find("input[name='fees_deposite_date']").val() || '';
                let payment_amount = $("#pre-deposite-form").find("input[name='payment_amount']").val() || '';
                let payment_currency_id = $("#pre-deposite-form").find("select[name='payment_currency_id']").val() || '';
                let fees_deposite_slip = $("#pre-deposite-form").find("input[name='fees_deposite_slip']")[0];
                let fees_deposite_slip_url = $("#pre-deposite-form").find("input[name='fees_deposite_slip']").data("fileUrl");

                if (fees_deposite_slip && fees_deposite_slip.files.length > 0) {
                    upload_data.append("fees_deposite_slip", fees_deposite_slip.files[0]);
                } else {
                    if (fees_deposite_slip_url == "") {
                        upload_data.append("fees_deposite_slip_url", "");
                    }
                }


                pre_deposite_array.push({
                    tentative_date: tentative_date,
                    fees_deposite_date: fees_deposite_date,
                    payment_amount: payment_amount,
                    payment_currency_id: payment_currency_id,
                    fees_deposite_slip_url: fees_deposite_slip_url,
                });

                upload_data.append("pre_deposite_data", JSON.stringify(pre_deposite_array));

                resolve(upload_data);
            } catch (error) {
                reject(error);
            }
        });
    }

    function select_reinit() {
        $(".selectpicker").selectpicker('refresh');
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
                    media_view += `<button class="btn-xs btn btn-danger" type="button" onclick="delete_documents_study(6,${tracker_id},${visa.id})"><i class="fa fa-trash"></i></button>`;
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

    $(document).ready(function() {
        $('#dynamicTable').DataTable({
            paging: false, // Disable pagination
            searching: false, // Disable search box
            ordering: false, // Disable column sorting
            info: false, // Disable "Showing X of Y entries"
            responsive: true, // Optional: responsive table
            autoWidth: false // Optional: prevent automatic column sizing
        });
    });

    // Ensure pendencyStatusOptions is safely defined
    var pendencyStatusOptions = <?= !empty($pendency_status) && is_array($pendency_status) ? json_encode($pendency_status, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : '[]' ?>;

    function createPendencyBlock(deletestatus = 0) {
        const pendencyId = Math.floor(Date.now()); // unique ID based on timestamp
        let deleteHtml = ``;
        if (deletestatus == 1) {
            deleteHtml = `<div class='col-md-1 form-group'><p>&nbsp;</p><button class="btn btn-danger" onclick="$(this).parents('.pendency-div').remove()"><i class='fa fa-trash '></i></button></div>`;

        }
        const container = document.createElement('div');
        container.className = 'row pendency-div';
        container.innerHTML = `
        <div class="col-md-8">
            <label for="remark_${pendencyId}">Remark <small class='text-danger'>*</small></label>
            <textarea rows="4"  id="remark_${pendencyId}" name="remark_${pendencyId}" class="form-control" required required-check></textarea>
        </div>
        <div class="col-md-3 form-group">
            <label for="pendency_select_${pendencyId}">Status <small class='text-danger'>*</small></label>
            <select id="pendency_select_${pendencyId}" name="pendency_select_${pendencyId}" class="form-control selectpicker" required required-check>
                ${pendencyStatusOptions.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('')}
            </select>
        </div>
        ` + deleteHtml + `
    `;
        return container;
    }

    function new_pendency_create(id) {
        const pendencyBlock = createPendencyBlock(1);

        $(`#pendency_${id}`).append(pendencyBlock);
        $(`#pendency_${id}`).find("select").selectpicker('refresh');
    }


    function create_pendency(value, id) {
        if (parseInt(value) === 2) {

            const pendencyBlock = createPendencyBlock();

            const targetDiv = document.getElementById(`pendency_${id}`);
            if (targetDiv) {
                $(`#pendency_${id} .pendency-div`).remove();

                targetDiv.appendChild(pendencyBlock);

                // Refresh selectpicker if available

                $(`#pendency_${id}`).find("select").selectpicker('refresh');
                $(`#pendency_${id}`).removeClass("hide");

            }
        } else {
            // Optional: clear content if value is not 2
            const targetDiv = document.getElementById(`pendency_${id}`);
            $(`#pendency_${id} .pendency-div`).remove();
            $(`#pendency_${id}`).addClass("hide");

        }
    }

    function changeOfferStatus(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const upload_status = selectedOption.dataset.upload_status;

        console.log('upload_status:', upload_status);

        if (upload_status == '1') {
            $(".offer-letter-div").removeClass("hide");
        } else {
            $(".offer-letter-div").addClass("hide");
        }
    }
</script>