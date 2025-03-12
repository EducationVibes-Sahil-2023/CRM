<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$staff_list              = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list, null, "staffid");

$applicant_tracker = applicant_tracker_mbbs($lead_type_status);
$applicant_status = !empty($client->tracker_id) ? $client->tracker_id : 0;
$profile_creation_data = !empty($profile_creation_data) ? $profile_creation_data : "";
$university_partner_names = get_university_partner_names();
// $documents_type =  get_documents($lead_type_status, [], 1);

$documents_type =  get_documents($lead_type_status, !empty($admissionpreferences->study_country) ? explode(",", $admissionpreferences->study_country) : [], 1);

$documents_type_dropdown = $documents_type =  array_column($documents_type, null, 'id');
$applicant_documents =  get_clients_documents($client_id);

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

// if (empty($customer_admins)  && !is_admin()) { 
?>
<!--<h2 class='text-center'><?= _l("no_admin_assign_tracker") ?></h2>-->
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
                <li data-id="<?= $track['id'] ?>" data-show="<?= !empty($track["show_div_name"]) ? $track["show_div_name"] : '' ?>"><?= $track["name"] ?></li>
            <?php
            }
            ?>
        </ul>


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
                            <button type="button" class="btn btn-primary btn-xs" onclick="registration_slip_generate(<?= $client_id ?>,0,1)"><i class="fa fa-whatsapp"></i> </button>
                            <button type="button" class="btn btn-primary btn-xs" onclick="registration_slip_generate(<?= $client_id ?>,0,0,1)"><i class="fa fa-envelope"></i> </button>
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
                                                <td class="d-flex">

                                                    <?php if (!empty($file_url)) : ?>
                                                        <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                        <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>&nbsp;

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
                                                <td>
                                                    <?= !empty($staff_list[$applicant_documents[$doc_id]["updated_by"]]["firstname"]) ? $staff_list[$applicant_documents[$doc_id]["updated_by"]]["firstname"] . " " . $staff_list[$applicant_documents[$doc_id]["updated_by"]]["lastname"] : '' ?>
                                                </td>
                                                <td>
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
                                                    <input type="file" class="form-control" accept=".pdf" name="admission_letter_<?= $short_list["id"] ?>">
                                                    <input type="hidden" class="form-control" value="<?= $file_url ?>" name="admission_letter_path_<?= $short_list["id"] ?>">

                                                    <?php
                                                    if (!empty($file_url)) { ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                            <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>
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
                                <?php if (!empty($entrance_exams)): ?>
                                    <?php foreach ($entrance_exams as $university => $exams): ?>
                                        <div class="entrance_exam_university_div shadow">
                                            <h4 class="text-left "><?= htmlspecialchars($university) ?>
                                            </h4>
                                            <?php if (!empty($entrance_exams)): ?>
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 3,'','<?= htmlspecialchars($university) ?>')"><i class="fa fa-whatsapp"></i> </button>
                                                    <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(<?= !empty($client_id) ? $client_id : '' ?>, 2,'','<?= htmlspecialchars($university) ?>')"><i class="fa fa-envelope"></i> </button>
                                                </div>
                                            <?php endif; ?>

                                            <?php foreach ($exams as $exam): ?>
                                                <div class="row university-entrance-exam">

                                                    <div class="col-md-3">
                                                        <label>Batch Name</label>
                                                        <input type="text" value="<?= htmlspecialchars($exam["batch_name"]) ?>" readonly class="form-control">

                                                        <input type="hidden" name="batch_id" value="<?= $exam['batch_id'] ?>" class="form-control">
                                                        <input type="hidden" name="client_id" value="<?= $exam['client_id'] ?>" class="form-control">
                                                        <input type="hidden" name="exam_id" value="<?= $exam['exam_id'] ?>" class="form-control">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>Exam Name</label>
                                                        <input type="text" value="<?= htmlspecialchars($exam["exam_name"]) ?>" readonly class="form-control">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>Exam Date</label>
                                                        <input type="date" value="<?= htmlspecialchars($exam["exam_date"]) ?>" readonly class="form-control">
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
                                                </div>
                                                <br>

                                            <?php endforeach; ?>

                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                                                        <label class="form-check-label">Ministry Order of Documents Received
                                                            <input type="checkbox" class="form-check-input" <?= $mand_re ?> <?= !empty($leg["ministry_document_recived"]) && $leg["ministry_document_recived"] == 1 ? 'checked' : '' ?> name="ministry_doc_received_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">
                                                            <?= $mand ?>
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
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="col-md-3">
                                                    <label>Payment Amount <?= $mand ?></label>
                                                    <input type="number" <?= $mand_re ?> <?= empty($file_url_university_payment) ? '' : '' ?> class="form-control" name="payment_amount_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>" value="<?= !empty($leg["payment_amount"]) ? $leg["payment_amount"] : '' ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>University Payment Receipt </label>
                                                    <input type="file" <?= empty($file_url_university_payment) ? '' : '' ?> class="form-control" accept=".pdf,image/*" name="university_payment_slip_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                    <?php if (!empty($file_url_university_payment)) { ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_university_payment) ?>');"></i>&nbsp;
                                                            <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_university_payment) ?>', '_blank');"></i>
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
                                                <button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(<?= !empty($client_id) ? $client_id : '' ?>, 4,'','<?= htmlspecialchars($university) ?>')"><i class="fa fa-whatsapp"></i> </button>
                                                <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(<?= !empty($client_id) ? $client_id : '' ?>, 3,<?= $leg['invitation_letter'] ?>)"><i class="fa fa-envelope"></i></button>
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
                    <?php } ?>
                    <?php if ($k > 0) { ?>
                        <input type="button" name="previous" class="previous text-center action-button-previous" value="Previous" />
                    <?php } ?>
                    <?php
                    if (($k + 1) < count($applicant_tracker)) { ?>
                        <input type="button" name="next" class="next text-center action-button next-<?= $track['id'] ?>" onclick="next_step('<?= $track['id'] ?>',this)" value="Save & Next" />
                        <?php if (!empty($track['skip']) && $track['skip'] == 1) { ?>
                            <input type="button" name="next" class=" text-center btn-warning action-button next-<?= $track ?>" onclick="next_step('<?= $track['id'] ?>',this,'<?= $track['skip'] ?>')" value="Skip" />
                        <?php } ?>
                    <?php } else if (($k + 2) == count($applicant_tracker)) {  ?>
                        <input type="button" name="next" class="next text-center action-button next-<?= $track['id'] ?>" onclick="next_step('<?= $track['id'] ?>',this)" value="Update" />
                    <?php } ?>


                </fieldset>
            <?php
            }
            ?>
        </section>

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
    var admissionpreferences_freeze = 0;
    var base_url = "<?= base_url() ?>";
    //jQuery time
    let lead_type_status = "<?= $lead_type_status ?>";
    console.log(lead_type_status);
    let documents_type_dropdown = <?= json_encode($documents_type_dropdown) ?>; // Get your data from PHP
    var applicant_status = "<?= $applicant_status ?>";
    // console.log(applicant_status);
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches
    var client_id = <?= !empty($client_id) ? $client_id : '' ?>;
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>"; // Replace with the actual CSRF token value
    var step_stage = 0;

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

    async function document_approved(obj, doc_id, status) {
        let upload_data = new FormData();
        try {
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("status", status);
            upload_data.append("doc_id", doc_id);


            let response = await $.ajax({
                url: "<?= base_url("admin/clients/documents_approval") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });
            response = JSON.parse(response);
            if (response.resp_code === "RCS") {
                alert_float("success", response.resp_desc);
                if (status == 1) {
                    $(".action_button_" + doc_id).html("<span class='text-success'>Approved</span>");
                } else {
                    $(".action_button_" + doc_id).html("<span class='text-danger'>Rejected</span>");
                }
                let date = new Date();
                let formattedDate = formatDate(date);
                $(".approved_by_" + doc_id).text("<?= !empty($staff_list[get_staff_user_id()]["firstname"]) ? $staff_list[get_staff_user_id()]["firstname"] . " " . $staff_list[get_staff_user_id()]["lastname"] : '' ?>");
                $(".approved_date_" + doc_id).text(formattedDate);
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

    async function next_step(id, obj, skip = 0) {
        id = $.trim(id);
        let upload_data = new FormData();

        try {
            // Ensure ID is retrieved from the progress bar if not provided
            id = $("#progressbar .active").data("id");

            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("tracker_id", id);
            upload_data.append("lead_type", <?= $lead_type_status ?>);
            upload_data.append("skip", skip);

            if (id == 2) {
                let result = await university_shortlisting_dropdown();
                if (!result) return false;
                await check_university_shortlisting(upload_data);
            }

            if (id == 3) {
                let check_validation = await check_required_fields("application-form");
                if (!check_validation) return false;
                await check_university_admission(upload_data);
            }

            if (id == 4 && skip == 0) {
                await check_entrance_exam(upload_data);
            }

            if (id == 5) {
                let check_validation = await check_required_fields("legalization-form");
                if (!check_validation) return false;
                await check_legalization(upload_data);
            }

            if (id == 6 && skip == 0) {
                let check_validation = await check_required_fields("fees-deposite-form");
                if (!check_validation) return false;
                await check_fees_deposite(upload_data);
            }
            if (id == 7) {
                let check_validation = await check_required_fields("invitation-form");
                if (!check_validation) return false;
                await check_invitation_letter(upload_data);
            }
            // Perform AJAX request
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

            if (response.resp_code === "RCS") {
                alert_float("success", response.resp_desc);



                if (response.pass_stage != undefined) {
                    show_next_stage(response.pass_stage);
                } else {
                    show_next_previous(obj);
                }
                if (id == 2) {
                    set_application(response);
                }
                if (id == 3 && response.entrance_exams != undefined) {
                    createEntranceExamList(response.entrance_exams);
                }
                if (id == 4 && response.legalization != undefined) {

                    createLegalization(response.legalization);
                }
                if (id == 5 && response.fees_deposite != undefined) {
                    createFeesDeposite(response.fees_deposite);
                }
                if (id == 6 && response.invitation != undefined) {
                    createInvitationLetter(response.invitation);
                }
            } else {
                alert_float("danger", response.resp_desc || "An error occurred.");
            }
        } catch (error) {
            console.error("Error in next_step:", error);
            // alert_float("danger", "An unexpected error occurred.");
        }
    }

    $(".previous").click(function() {
        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();
        //de-activate current step on progressbar
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active").addClass("inactive");
        $("#progressbar li").eq($("fieldset").index(previous_fs)).addClass("active").removeClass("previous").removeClass("inactive");
        previous_fs.slideDown();
        current_fs.slideUp("slow");
    });

    function createEntranceExamList(data) {
        const $container = $(".entrance_div");
        $container.html(""); // Clear the container

        $.each(data, function(university, exams) {
            const $universityDiv = $("<div>").addClass("entrance_exam_university_div shadow");
            let email_button = `<div class="text-right"><button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(${client_id}, 3,'','${university}')"><i class="fa fa-whatsapp"></i></button> <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(${client_id}, 2,'','${university}')"><i class="fa fa-envelope"></i></button> </div>`;
            const $title = $("<h4>").addClass("text-left").text(university);
            $universityDiv.append($title);
            $universityDiv.append(email_button);

            $.each(exams, function(index, exam) {
                const $examRow = $("<div>").addClass("row university-entrance-exam");
                console.log(exam.status);
                $examRow.append(`
                <div class="col-md-3">
                    <label>Batch Name</label>
                    <input type="text" value="${exam.batch_name}" readonly class="form-control">
                    <input type="hidden" name="batch_id" value="${exam.batch_id}" readonly class="form-control">
                    <input type="hidden" name="client_id" value="${exam.client_id}" readonly class="form-control">
                    <input type="hidden" name="exam_id" value="${exam.exam_id}" readonly class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Exam Name</label>
                    <input type="text" value="${exam.exam_name}" readonly class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Exam Date</label>
                    <input type="date" value="${exam.exam_date}" readonly class="form-control">
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

                $universityDiv.append($examRow);
                $universityDiv.append("<br>"); // Add spacing
            });

            $container.append($universityDiv);
            $container.append("<hr>"); // Divider
        });

        $(".selectpicker").selectpicker("refresh"); // Refresh bootstrap-select
    }

    function createLegalization(legalizationData) {
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
                                                        <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${base_url}${file}', '_blank');"></i></div>`;
                    }
                    html += `
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="form-check-label">&nbsp;</p>
                            <label class="form-check-label">
                                Ministry Order of Documents Received
                                <input type="checkbox" class="form-check-input" ${check_min_doc} ${mand_re} name="ministry_doc_received_${(leg.id)}">
                                ${mand}
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
                console.log(html);
                legalizationContainer.append(html);
            });
        } else {
            legalizationContainer.html('<p class="text-muted">No Fees Data available.</p>');
        }
    }

    function createFeesDeposite(legalization) {
        let feesDepositeDiv = $(".fees_deposite_div");
        feesDepositeDiv.html(""); // Clear existing content

        if (legalization.length > 0) {
            legalization.forEach(leg => {
                let mand = leg.primary_university == 1 ? '<small class="text-danger">*</small>' : '';
                let mand_re = leg.primary_university == 1 ? 'required required-check' : '';
                let base_url = "<?= base_url() ?>";
                let file_url_payment = leg.fees_deposite_slip ? base_url + leg.fees_deposite_slip : "";
                let file_url_university_payment = leg.university_fees_payment_slip ? base_url + leg.university_fees_payment_slip : "";

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
                                </div>` : ""}
                        </div>
                        <div class="col-md-3">
                             <label>Payment Amount ${mand}</label>
                             <input type="number" value="${leg.payment_amount}" class="form-control" name="payment_amount_<?= htmlspecialchars($leg["id"], ENT_QUOTES, 'UTF-8') ?>" ${mand_re}>
                        </div>
                        <div class="col-md-3">
                            <label>University Payment Receipt </label>
                            <input type="file" class="form-control" accept=".pdf,image/*" name="university_payment_slip_${leg.id}" ${file_url_university_payment ? "" : ""}>
                            ${file_url_university_payment ? `
                                <div class="margin-top">
                                    <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('${file_url_university_payment}');"></i>&nbsp;
                                    <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${file_url_university_payment}', '_blank');"></i>
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

    function createInvitationLetter(legalization) {
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
                let file_url_university_payment = leg.invitation_letter ? leg.invitation_letter : "";
                let email_button = `<div class="text-right"><button type="button" class="btn btn-primary btn-xs hide" onclick="whatsapp_message_send(${client_id}, 4,'','${leg.id}')"><i class="fa fa-whatsapp"></i></button> <button type="button" class="btn btn-primary btn-xs hide" onclick="email_send(${client_id}, 3,${leg.id})"><i class="fa fa-envelope"></i></button> </div>`;
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


    function set_application(update_university_status) {
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
                                                        <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('${file}', '_blank');"></i></div>`;
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
            <input type="file" class="form-control" accept=".pdf" name="admission_letter_${university.id}">
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

                if ($(this).is(":checkbox")) {
                    if (!$(this).is(":checked")) { // Check if checkbox is NOT checked
                        form_status = false;
                        $(this).addClass("error"); // Highlight the checkbox
                    } else {
                        $(this).removeClass("error"); // Remove error highlight if checked
                    }
                }

                if (isRequired && name) {
                    additional_fields[name] = "required";

                    if ($.trim(value) === "") {
                        form_status = false;
                        $(this).addClass("error"); // Highlight invalid fields
                    } else {
                        $(this).removeClass("error");
                    }
                }
            });
            console.log(additional_fields);
            if (!form_status) {
                appValidateForm($("#" + id), additional_fields);
                $("#" + id).submit()

                reject(false);
            } else {
                resolve(true);
            }
        });
    }



    function check_entrance_exam(upload_data) {
        return new Promise((resolve, reject) => {
            let entrance_exam_data = [];

            $(".entrance_exam_university_div .university-entrance-exam").each(function() {
                let batch_id = $(this).find("input[name='batch_id']").val() || "";
                let client_id = $(this).find("input[name='client_id']").val() || "";
                let exam_id = $(this).find("input[name='exam_id']").val() || "";
                let status = $(this).find("select[name='entrance_status']").val() || "";

                // Push only if required fields are present
                if (batch_id && client_id && exam_id) {
                    entrance_exam_data.push({
                        batch_id: batch_id.trim(),
                        client_id: client_id.trim(),
                        exam_id: exam_id.trim(),
                        status: status.trim(),
                    });
                }
            });

            // Append data only if there's valid input
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

    async function whatsapp_message_send(client_id, type, s_university_id = "", s_university_name = "") {
        show_loader();

        let upload_data = new FormData();
        upload_data.append("client_id", client_id);
        upload_data.append("type", type);
        upload_data.append("s_university_id", s_university_id);
        upload_data.append("s_university_name", s_university_name);
        upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);


        try {
            let response = await $.ajax({
                url: "<?= base_url('admin/clients/whatsapp_message_send') ?>",
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
</script>