<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$staff_list              = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list, null, "staffid");

$applicant_tracker = applicant_tracker_mbbs($lead_type_status);
$applicant_status = !empty($client->tracker_id) ? $client->tracker_id : 0;
$profile_creation_data = !empty($profile_creation_data) ? $profile_creation_data : "";
$university_partner_names = get_university_partner_names();
// $documents_type =  get_documents($lead_type_status, [], 1);

$documents_type =  get_documents($lead_type_status, !empty($admissionpreferences->study_country) ? explode(",", $admissionpreferences->study_country) : []);

$documents_type_dropdown = $documents_type =  array_column($documents_type, null, 'id');
$applicant_documents =  get_clients_documents($client_id);

if (!empty($applicant_documents[0]["data"])) {
    $applicant_documents = json_decode($applicant_documents[0]["data"], true);

    if (!empty($applicant_documents)) {
        $applicant_documents = array_column($applicant_documents, null, "id");
    }
}

?>
<style>
    /*basic reset*/
    * {
        margin: 0;
        padding: 0;
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

    .application_div div.university_div_application {
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

if (empty($customer_admins)) { ?>
    <h2 class='text-center'><?= _l("no_admin_assign_tracker") ?></h2>
<?php } else { ?>
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
                        <div id="upload_documents">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark ">
                                    <tr class="">
                                        <th scope="col">S.No</th>
                                        <th scope="col">Document Type</th>
                                        <th scope="col">Stage</th>
                                        <th scope="col">Upload By</th>
                                        <th scope="col">Upload Date</th>
                                        <th scope="col">Approved By</th>
                                        <th scope="col">Approved Date</th>
                                        <th scope="col">Action</th>
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
                                                <td>
                                                    <?= !empty($staff_list[$applicant_documents[$doc_id]["updated_by"]]["firstname"]) ? $staff_list[$applicant_documents[$doc_id]["updated_by"]]["firstname"] . " " . $staff_list[$applicant_documents[$doc_id]["updated_by"]]["lastname"] : '' ?>
                                                </td>
                                                <td>
                                                    <?= !empty($applicant_documents[$doc_id]["updated_date"]) ? date("Y-m-d H:i:s", strtotime($applicant_documents[$doc_id]["updated_date"])) : '';
                                                    ?>
                                                </td>
                                                <td class="approved_by_<?= $doc_id ?>">

                                                </td>
                                                <td class="approved_date_<?= $doc_id ?>">

                                                </td>
                                                <!-- <td>
                                                        <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                    </td> -->
                                                <td class="d-flex">

                                                    <?php if (!empty($file_url)) : ?>
                                                        <i class="fa fa-eye" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                        <i class="fa fa-download" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>&nbsp;

                                                        <?php if (empty($applicant_documents[$doc_id]["approval_status"])) : ?>
                                                            <div class="action_button_<?= $doc_id ?>">
                                                                <button class="btn-xs btn btn-success" onclick="document_approved(this, <?= $doc_id ?>, 1)">Approved</button>
                                                                <button class="btn-xs btn btn-danger" onclick="document_approved(this, <?= $doc_id ?>, 2)">Rejected</button>
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
                                ?>
                                            <div class="col-md-12 university_div_application mt-2">
                                                <div class="col-md-3">
                                                    <label>Country Name <small class='text-danger'>*</small></label>
                                                    <input type="input" name="country_<?= $short_list["id"] ?>" readonly required required-check class="form-control" value="<?= $short_list["country_name"] ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>University Name <small class='text-danger'>*</small></label>
                                                    <input type="hidden" name="id" value="<?= $short_list["id"] ?>">
                                                    <input type="input" name="university_<?= $short_list["id"] ?>" class="form-control" required required-check readonly value="<?= $short_list["university_name"] ?>">
                                                </div>

                                                <div class="col-md-3">
                                                    <label>Partner Name <small class='text-danger'>*</small></label>
                                                    <?php
                                                    $selected_value = [];
                                                    $selected_value[] =  !empty($short_list["partner"]) ? $short_list["partner"] : '';
                                                    echo render_select('partner_' . $short_list["id"], $university_partner_names, array('id', 'name'), "", $selected_value, ["requried" => "requried", "required-check" => "required-check"]);
                                                    ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Application Date <small class='text-danger'>*</small> </label>
                                                    <input type="date" class="form-control" name="date_<?= $short_list["id"] ?>" requried required-check value="<?= $short_list["application_date"] ?>">
                                                </div>
                                                <br>
                                                <div class="col-md-12">
                                                    <label>Documents Attach <small class='text-danger'>*</small> </label>
                                                    <br>
                                                    <ul class=" list-unstyled">
                                                        <?php
                                                        // Ensure $documents_type is an array to prevent errors
                                                        $documents_type = !empty($documents_type) ? $documents_type : [];

                                                        $doc_selected = !empty($short_list["documents"]) ? explode(",", $short_list["documents"]) : [];

                                                        foreach ($documents_type as $doc_ty) {
                                                            // Check if the document ID is in the selected list
                                                            $checked = in_array($doc_ty['id'], $doc_selected) ? 'checked' : '';
                                                        ?>
                                                            <li class="col-md-3 checkbox-select-doc d-flex align-items-center">
                                                                <input type="checkbox" name="documents[]" id="doc_<?= $doc_ty['id'] ?>" value="<?= $doc_ty['id'] ?>" class="me-2" <?= $checked ?>>
                                                                <label for="doc_<?= $doc_ty['id'] ?>" class="mb-0"><?= htmlspecialchars($doc_ty['name']) ?></label>
                                                            </li>
                                                        <?php } ?>

                                                    </ul>

                                                </div>
                                                <div class="col-md-3">
                                                    <?php
                                                    $file_url = !empty($short_list["application_file"]) ? $short_list["application_file"] : '';

                                                    ?>
                                                    <label>Admission Letter <small class='text-danger'>*</small> </label>
                                                    <input type="file" class="form-control" accept=".pdf" name="admission_letter_<?= $short_list["id"] ?>">
                                                    <input type="hidden" class="form-control" value="<?= $file_url ?>" name="admission_letter_path_<?= $short_list["id"] ?>">

                                                    <?php
                                                    if (!empty($file_url)) { ?>
                                                        <i class="fa fa-eye" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>&nbsp;
                                                        <i class="fa fa-download" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank');"></i>
                                                    <?php } ?>
                                                </div>



                                            </div>
                                    <?php }
                                    }
                                    ?>

                                <?php } ?>
                            </div>
                        </form>
                    <?php } ?>
                    <?php if ($k > 0) { ?>
                        <input type="button" name="previous" class="previous text-center action-button-previous" value="Previous" />
                    <?php } ?>
                    <?php
                    if (($k + 1) < count($applicant_tracker)) { ?>
                        <input type="button" name="next" class="next text-center action-button next-<?= $track ?>" onclick="next_step('<?= $track['id'] ?>',this)" value="Save & Next" />
                    <?php } else if (($k + 2) == count($applicant_tracker)) {  ?>
                        <input type="button" name="next" class="next text-center action-button next-<?= $track ?>" onclick="next_step('<?= $track['id'] ?>',this)" value="Update" />
                    <?php } ?>


                </fieldset>
            <?php
            }
            ?>

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
<?php } ?>
<?php init_tail(); ?>
<!-- /.MultiStep Form -->
<script>
    var admissionpreferences_freeze = "<?= !empty($admissionpreferences->freeze) ? 1 : 0 ?>";

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

    var customer_admins = <?= !empty($customer_admins) ? json_encode($customer_admins, true) : "" ?>;
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
    async function next_step(id, obj) {
        id = $.trim(id);

        let upload_data = new FormData();
        try {

            id = $("#progressbar .active").data("id");
            upload_data.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);
            upload_data.append("client_id", <?= $client_id ?>);
            upload_data.append("tracker_id", id);
            upload_data.append("lead_type", <?= $lead_type_status ?>);

            if (id == 2) {

                await check_university_shortlisting(upload_data);

            }
            if (id == 3) {
                let check = await check_requried_fields("application-form");
                await check_university_admission(upload_data);
            }
            let response = await $.ajax({
                url: "<?= base_url("admin/clients/mbbs_tracker") ?>",
                method: "POST",
                data: upload_data,
                contentType: false,
                processData: false
            });
            response = JSON.parse(response);
            if (response.resp_code === "RCS") {
                alert_float("success", response.resp_desc);
                show_next_previous(obj);
                if (id == 3) {
                    set_application(response);
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
            console.error(error);
            reject(error);
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
                console.log(university_shortlisting);

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

    window.onbeforeunload = function() {
        return null;
    };



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

            const duplicateEntry = check_university_duplicate.find(entry => entry.university === select_university);
            if (duplicateEntry) {
                hide_loader();
                alert_float("danger", "Duplicate entry found: university '" + select_university + "' connected with multiple vendors.");
                stop_status = false;
                return false;
            }
            check_university_duplicate.push({
                "university": select_university,
                "university_id": university_id
            });

        });

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
            let university = university_list[i]; // Assign variable for readability
            console.log(university)
            let base_url = "<?= $base_url ?>";
            let file = university.application_file;
            let media_view = "";
            if (file != "") {
                media_view = `<i class="fa fa-eye" onclick="show_media_files('${base_url}${file}');"></i>&nbsp;
                                                        <i class="fa fa-download" onclick="download_media_files('${base_url}${file}', '_blank');"></i>`;
            }
            html += `<div class="row university_div_application mt-2">
        <div class="col-md-3">
            <label>Country Name <small class='text-danger'>*</small></label>
            <input type="text" name="country_${university.id}" class="form-control" disabled value="${university.country_name}">
        </div>  
        <div class="col-md-3">
            <label>University Name <small class='text-danger'>*</small></label>
            <input type="hidden" name="id[]" value="${university.id}">
            <input type="text" name="university_${university.id}" class="form-control" disabled value="${university.university_name}">
        </div> 
        <div class="col-md-3">
            <label>Partner Name <small class='text-danger'>*</small></label>
            <select name="partner_${university.id}" class="form-control" required-check required>
                <option value="">Select Partner</option>`;

            // Loop through `university_partner_names` correctly
            for (let partner of university_partner_names) {
                let selected = "";
                if (partner.id == university.partner) {
                    selected = "selected";
                }
                html += `<option ` + selected + ` value="${partner.id}">${partner.name}</option>`;
            }

            html += `</select>
        </div>
        <div class="col-md-3">
            <label>Application Date <small class='text-danger'>*</small></label>
            <input type="date" name="date_${university.id}" class="form-control required-check" required value="${university.application_date || ''}">
        </div>
        <br>
        <div class="col-md-12">
            <label>Documents Attach <small class='text-danger'>*</small></label>
            <br>
            <ul class="list-unstyled">`;

            if (!Array.isArray(documents_type)) {
                console.error("Error: documents_type is not an array!", documents_type);
            } else {

                let selected_values = university_list[i].documents ? university_list[i].documents.split(",") : [];
                for (let doc of documents_type) {
                    let selected = selected_values.includes(doc.id.toString()) ? "checked" : "";
                    html += `<li class="col-md-3 checkbox-select-doc d-flex align-items-center">
                <input type="checkbox" ${selected} name="documents[]" name="docs_${university_list[i].id}" id="doc_${doc.id}" value="${doc.id}" class="me-2">
                <label for="doc_${doc.id}" class="mb-0">${doc.name}</label>
            </li>`;
                }

            }


            html += `</ul>
        </div>  
        <div class="col-md-3">
          <label>Admission Letter <small class='text-danger'>*</small> </label>
        <input type="hidden" class="form-control" value="${file}" name="admission_letter_path_${university_list[i].application_file}">
        <input type="file" class="form-control" accept=".pdf" name="admission_letter_${university_list[i].id}">
        ${media_view}
        </div>
    </div>`; // Close row div
            $(".application_div").append(html);
        }

        // Append the generated HTML

    }


    function check_requried_fields(id = "application-form") {
        return new Promise((resolve, reject) => {
            let form_status = true;
            let additional_fields = {}; // Ensure additional_fields is defined

            // Validate visible input, select, and date fields
            $("#" + id + " input:visible, #" + id + " select:visible, #" + id + " date:visible").each(function() {
                const value = $(this).val(); // Get the value of the field
                const isRequired = $(this).attr("required-check") !== undefined; // Check for 'required-check' attribute
                const name = $(this).attr("name"); // Get the name attribute
                console.log(name);
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
                $("#application-form").submit()

                reject(false);
            } else {
                resolve(true);
            }
        });
    }
</script>