<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!empty($score_value)) {
    $score_value = array_column($score_value, null, "type");
}
$passport_stages = get_passport_stages();
$caste_category = get_caste_category();
$neet_status = get_neet_status();

$board_dropdown = get_board_dropdown();
$staff_list              = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list, null, "staffid");
if (!empty($board_dropdown)) {
    array_unshift($board_dropdown, array("id" => "", "name" => "Select Board"));
}

$documents_type =  get_documents($lead_type_status, !empty($admissionpreferences->primary_country) ? explode(",", $admissionpreferences->primary_country) : [], 1);


$profile_section = [];
foreach ($documents_type as $documents) {
    $profile_section[$documents["profile_stages"]][] = $documents;
}

array_push($documents_type, array("id" => "application", "name" => "Admission Letter", "file_type" => ".pdf,image/*"));
array_push($documents_type, array("id" => "invitation", "name" => "Invitation Letter", "file_type" => ".pdf,image/*"));
array_push($documents_type, array("id" => "visa", "name" => "Visa", "file_type" => ".pdf,image/*"));

$staff_id = array_column($customer_admins, "staff_id");
$final_sumbit = $client->submission_status;
$read_only = "readonly";

$admin_status = 0;
$visa_details =  visa_details($client_id, 0, 1);
if (is_admin() ||  !empty($staff_list[get_staff_user_id()]["post_sales"])) {
    $final_sumbit = 0;
    $read_only = "";
    $admin_status = 1;
}
// if (in_array(get_staff_user_id(), $staff_id)) {
//     $final_sumbit = 0;
//     $read_only = "";
//     $admin_status = 1;
// }


$applicant_documents =  get_clients_documents($client_id);
if (!empty($applicant_documents[0]["data"])) {
    $applicant_documents = json_decode($applicant_documents[0]["data"], true);

    array_push($applicant_documents, array("id" => "application", "document_file" => !empty($university_shortlisting[0]['application_file']) ? $university_shortlisting[0]['application_file'] : ''));
    array_push($applicant_documents, array("id" => "invitation", "document_file" => !empty($visa_details[0]['file']) ? $visa_details[0]['file'] : ''));
    array_push($applicant_documents, array("id" => "visa", "document_file" => !empty($university_shortlisting[0]['invitation_letter']) ? $university_shortlisting[0]['invitation_letter'] : ''));

    if (!empty($applicant_documents)) {
        $applicant_documents = array_column($applicant_documents, null, "id");
    }
}






$years_array = [];
$currentYear = date("Y");

// Generate an array of the last 15 years
for ($i = 0; $i < 25; $i++) {
    $years_array[]["year"] = $currentYear - $i;
}
array_unshift($years_array, array(""));


$years_array_entrance = [];
$currentYear = date("Y");
$startYear = 2020;
$endYear = $currentYear + 4; // 4 years greater than current year

// Generate an array of years from 2020 to (current year + 4)
for ($i = $startYear; $i <= $endYear; $i++) {
    $years_array_entrance[]["year"] = $i;
}

// Add an empty first element
array_unshift($years_array_entrance, ["year" => ""]);



$markingSchemes = [];

$markingSchemes[]["name"] = "Percentage";
$markingSchemes[]["name"]  = "CGPA";

array_unshift($markingSchemes, array(""));

$resultStatus = [];

$resultStatus[]["name"] = "Awaited";
$resultStatus[]["name"] = "Declared";
array_unshift($resultStatus, array(""));

$neetResultStatus = [];

$neetResultStatus[]["name"] = "Awaited";
$neetResultStatus[]["name"] = "Declared";
$neetResultStatus[]["name"] = "Fail";
$neetResultStatus[]["name"] = "Not Appeared";
array_unshift($neetResultStatus, array(""));

?>
<!-- <script src="https://code.jquery.com/jquery-3.6.3.js"></script> -->
<script>
    var final_sumbit = <?= !empty($final_sumbit) ? $final_sumbit : 0 ?>;
    var admin_status = <?= $admin_status ?>;
    console.log("final_sumbit", final_sumbit);
    var admissionpreferences_freeze = "<?= !empty($admissionpreferences->freeze) ? 1 : 0 ?>";
</script>
<?php
$text_danger_mbbs = "";
$text_danger_mbbs_required = "";
if ($lead_type_status == 2) {
    $text_danger_mbbs = "<small class='text-danger'>*</small>";
    $text_danger_mbbs_required = "required-check";
}

?>
<style>
    .margin-top {
        margin-top: 5px;
    }

    .error-highlight {
        border: 2px solid red;
        background-color: #ffe6e6;
    }

    .accadmic-education-div {
        padding: 10px;
        /* box-shadow: 0px 0px 12px lightgrey; */
        /* background: lightgrey; */
        /* margin-bottom: 30px; */
        /* padding: 30px; */
        border-radius: 10px;
    }

    .accadmic-education-div h4 {
        /* text-align: center; */
    }

    .tags-input-wrapper {
        background: transparent;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ccc
    }

    .tags-input-wrapper input {
        border: none;
        background: transparent;
        outline: none;
        width: 140px;
        margin-left: 8px;
    }

    .tags-input-wrapper .tag {
        display: inline-block;
        background-color: #337ab7;
        color: white;
        border-radius: 40px;
        padding: 0px 3px 0px 7px;
        margin-right: 5px;
        margin-bottom: 5px;
        box-shadow: 0 5px 15px -2px rgb(51 122 183)
    }

    .tags-input-wrapper .tag a {
        margin: 0 7px 3px;
        display: inline-block;
        cursor: pointer;
        color: white !important;
    }

    .suggestions-container {
        position: absolute;
        /* border: 1px solid black; */
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0px 0px 1px;
        background: white;
        z-index: 999;
    }

    .hide_sugg {
        display: none;
    }

    .suggestions-container li {
        padding: 10px;

    }

    .suggestions-container li :hover {
        padding: 10px;
        overflow: hidden;
        background: lightgrey;
    }

    .c2 {
        min-height: 50px;
        vertical-align: middle;
    }

    #entrance_exam_div label {
        margin: 8px 0px 0px 0px;
    }

    .qualification-div {
        border-radius: 10px;
        padding: 10px;
        margin: 10px 0px;
        /* box-shadow: 0px 0px 10px lightgrey; */
    }
</style>

<style>
    .currency-selector {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        padding-left: .5rem;
        border: 0;
        background: transparent;

        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;

        background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='1024' height='640'><path d='M1017 68L541 626q-11 12-26 12t-26-12L13 68Q-3 49 6 24.5T39 0h952q24 0 33 24.5t-7 43.5z'></path></svg>") 90%/12px 6px no-repeat;

        font-family: inherit;
        color: inherit;
    }

    .currency-amount {
        text-align: right;
    }

    .currency-addon {
        width: 6em;
        text-align: left;
        position: relative;
    }

    #applicant_fees .dropdown.bootstrap-select {
        width: 100%;
        padding: 0px;
    }

    .scholarship-details .dropdown.bootstrap-select {
        width: 100% !important;
        padding: 0px;
    }

    #applicant_fees .bootstrap-select>.dropdown-toggle {
        /* border: 0px !important; */
    }
</style>

<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<div class="row">
    <input type="hidden" name="clientid" id="clientid" value="<?php echo $client_id ?>">
    <div class="additional"></div>
    <div class="col-md-12">
        <div class="horizontal-scrollable-tabs">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#student_details" class="active" aria-controls="student_details" role="tab" data-toggle="tab">Student Details</a>
                    </li>
                    <li role="presentation">
                        <a href="#passport" aria-controls="passport" role="tab" data-toggle="tab">Passport</a>
                    </li>
                    <li role="presentation">
                        <a href="#admission_preferences" aria-controls="admission_preferences" role="tab" data-toggle="tab">Admission Preferences</a>
                    </li>
                    <li role="presentation">
                        <a href="#academic_details" aria-controls="academic_details" role="tab" data-toggle="tab">Academic Details</a>
                    </li>
                    <li role="presentation">
                        <a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a>
                    </li>
                    <li role="presentation">
                        <a href="#welcome_message" aria-controls="welcome_message" role="tab" data-toggle="tab">Welcome Message</a>
                    </li>
                    <li role="presentation">
                        <a href="#fees_details" aria-controls="fees_details" role="tab" data-toggle="tab">Fees Details</a>
                    </li>
                    <?php hooks()->do_action('after_customer_billing_and_shipping_tab', isset($client) ? $client : false); ?>
                    <?php if (isset($client)) { ?>
                        <!--<li role="presentation">-->
                        <!--    <a href="#customer_admins" aria-controls="customer_admins" role="tab" data-toggle="tab">-->
                        <!--        <?php echo _l('customer_admins'); ?>-->
                        <!--    </a>-->
                        <!--</li>-->
                        <?php hooks()->do_action('after_customer_admins_tab', $client); ?>
                    <?php } ?>
                    <?php if (empty($client->submission_status) && $client->submission_status == 0) { ?>
                        <li role="presentation" onclick="show_all_data()">
                            <a href="#preview" aria-controls="preview" role="tab" data-toggle="tab">
                                Preview
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="tab-content mtop15">
            <div role="tabpanel" class="tab-pane student-data-div active" id="student_details">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <h4>Personal Informations</h4>
                            <hr>
                            <div class="">
                                <form id="basic-information-form" class="form-disabled" onsubmit=" return false;">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputFirstName">First Name <small class="text-danger">*</small></label>
                                                <input class="form-control" <?= $read_only ?> type="text" class="form-group" required-check required placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->first_name : $contact->firstname; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputLastName">Last Name</label>
                                                <input class="form-control " <?= $read_only ?> type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->last_name : $contact->lastname; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail">Email Address <small class="text-danger">*</small></label>
                                                <input class="form-control " <?= $read_only ?> type="text" class="form-group" required-check required placeholder="Email Address" name="email" value='<?php echo (isset($basicdetails)) ? $basicdetails->email : $contact->email; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Mobile Number <small class="text-danger">*</small></label>
                                                <input class="form-control check-phonenumber check-phonenumber-validation" <?= $read_only ?> type="tel" class="form-group" required-check required placeholder="Mobile Number" name="mobile" pattern="\d{10}" onkeypress="formatPhoneNumber(this.value)" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    maxlength="10" value='<?php echo (isset($basicdetails)) ? $basicdetails->mobile : $contact->phonenumber; ?>'>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputDateOfBirth">Date Of Birth <small class="text-danger">*</small></label>
                                                <input type="date" class="form-control" name="dob" id="dob" required value='<?php echo ($basicdetails->dob != '') ? $basicdetails->dob : ''; ?>' required required-check>
                                            </div>
                                        </div>


                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputPassword1">Gender <small class="text-danger">*</small></label>
                                                <select class="form-control" name="gender" id="gender" required required-check>
                                                    <option value="">Select</option>
                                                    <option <?php echo ($basicdetails->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                    <option <?php echo ($basicdetails->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                    <option <?php echo ($basicdetails->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputPassword1">Category <small class="text-danger">*</small></label>
                                                <?php
                                                array_unshift($caste_category, array("id" => "", "value" => "", "name" => "Select Category"));
                                                $selected_category[] = !empty($basicdetails->category) ? $basicdetails->category : '';

                                                echo render_select('category', $caste_category, array('id', 'name'), "", $selected_category, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "category");
                                                ?>

                                            </div>
                                        </div>


                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Address <small class="text-danger">*</small></label>
                                                <textarea <?= $read_only ?> name="address" class="form-control"><?php echo (isset($client)) ? $client->address : ''; ?></textarea>
                                            </div>
                                        </div>



                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Parent's Name <small class="text-danger">*</small></label>
                                                <input class="form-control" required required-check type="text" class="form-group" placeholder="Parents Name" name="father_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->father_name : ''; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Parent's Contact <small class="text-danger">*</small></label>
                                                <input class="form-control check-phonenumber check-phonenumber-validation" onkeypress="formatPhoneNumber(this.value)" required required-check type="tel" pattern="\d{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    maxlength="10" class="form-group" placeholder="Parents Contact" name="fathers_mobile" value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_mobile : ''; ?>'>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Parent's Email</label>
                                                <input class="form-control" type="text" class="form-group" placeholder="Parents Email" name="fathers_email" value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_email : ''; ?>'>
                                            </div>
                                        </div>

                                        <?php if ($lead_data->source == REFERENCE_ID) { ?>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="exampleInputDateOfBirth">Refrence Name <small class="text-danger">*</small></label>
                                                    <input type="text" <?= $read_only ?> class="form-control" name="reference_name" id="reference_name" required value='<?php echo ($client->reference_name != '') ? $client->reference_name : ''; ?>' required required-check>
                                                </div>
                                            </div>
                                        <?php } ?>


                                    </div>
                                    <div class="row">
                                        <?php
                                        foreach ($profile_section["student_details"] as $s_stage) {
                                            $doc_type = $s_stage["name"] ?? '';
                                            $doc_id = $s_stage["id"] ?? '';
                                            $info = $s_stage["info"] ?? '';
                                            $accept = $s_stage["file_type"] ?? '';
                                            $is_mandatory = !empty($s_stage["mandatry"]);
                                            $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                            $required_attr = $is_mandatory ? "required required-check" : '';
                                            $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                            $required_attr = !empty($file_url) ? "" : $required_attr;

                                        ?>
                                            <div class="col-lg-3 media-files">
                                                <div class="form-group">
                                                    <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?> <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?> <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?></label>
                                                    <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                    <?php
                                                    if (!empty($file_url)) {
                                                    ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                            <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="btn-save-fun margin-top">
                                        <div class="col-md-12">
                                            <button type="submit" onclick="save_basic_details()" class="btn btn-primary button-22 pull-right margin-top">Save changes</button>
                                        </div>
                                    </div>
                                </form>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane student-data-div disabled-form" id="passport">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <h4>Passport Informations</h4>
                            <?php
                            $show_passport_details = 0;
                            ?>
                            <hr>
                            <form id="passport-form" class="form-disabled" onsubmit=" return false;">
                                <div class="">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Passport <small class="text-danger">*</small></label>
                                            <select class="form-control" name="passport_status" onchange="change_passport_status()" id="passport" required required-check>
                                                <option value="">Select Passport Status</option>
                                                <?php
                                                foreach ($passport_stages as $p) {
                                                    $selected = "";
                                                    if ($p["id"] == $passport_info->passport_status) {
                                                        $selected = "selected";
                                                        $show_passport_details = $p['show_status'];
                                                    }
                                                ?>
                                                    <option value="<?= $p["id"] ?>" data-passport_number_status="<?= $p['show_status'] ?>" <?= $selected ?>><?= $p["name"] ?></option>
                                                <?php

                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="passport_number">Passport Number <small class="text-danger">*</small></label>
                                            <input class="form-control passport-info"
                                                type="text"
                                                placeholder="Enter Passport Number"
                                                name="passport_number"
                                                id="passport_number"
                                                pattern="^[A-Z0-9]{6,9}$"
                                                title="Passport number must be 6 to 9 characters, only uppercase letters (A-Z) and numbers (0-9)."
                                                maxlength="9" minlength="6"
                                                value="<?= isset($passport_info) ? htmlspecialchars($passport_info->passport_number) : '' ?>"
                                                required-check>

                                        </div>
                                    </div>

                                    <div class="col-lg-3 passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="issue_date">Issue Date <small class="text-danger">*</small></label>
                                            <input class="form-control passport-info" type="Date" class="form-group" placeholder="Enter Passport Number" name="issue_date" value="<?= (isset($passport_info) ? $passport_info->issue_date : '') ?>" required-check>
                                        </div>
                                    </div>


                                    <div class="col-lg-3 passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="exp_date">Expiry Date <small class="text-danger">*</small></label>
                                            <input class="form-control passport-info" type="Date" class="form-group" placeholder="Enter Passport Number" name="exp_date" value="<?= (isset($passport_info) ? $passport_info->exp_date : '') ?>" required-check>
                                        </div>
                                    </div>
                                    <?php
                                    foreach ($profile_section["passport"] as $s_stage) {
                                        $doc_type = $s_stage["name"] ?? '';
                                        $doc_id = $s_stage["id"] ?? '';
                                        $info = $s_stage["info"] ?? '';
                                        $accept = $s_stage["file_type"] ?? '';
                                        $is_mandatory = !empty($s_stage["mandatry"]);
                                        $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                        $required_attr = $is_mandatory ? "required required-check" : '';
                                        $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                        $required_attr = !empty($file_url) ? "" : $required_attr;

                                    ?>
                                        <div class="col-lg-3 media-files passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?> <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?> <?php if (!empty($info)) : ?>
                                                        &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                    <?php endif; ?></label>
                                                <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                <?php
                                                if (!empty($file_url)) {
                                                ?>
                                                    <div class="margin-top">
                                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="btn-save-fun margin-top">
                                    <div class="col-md-12">
                                        <button type="submit" onclick="save_passport_details()" class="btn btn-primary button-22 pull-right margin-top">Save changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="admission_preferences">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">

                            <h4>Admission Preferences</h4>
                            <hr>
                            <form id="admission-preferences-form" class="form-disabled" onsubmit=" return false;">
                                <div class="">
                                    <div class="col-lg-4" style="display:none">
                                        <div class="form-group">
                                            <label for="program">Segment</label>
                                            <?php
                                            array_unshift($lead_type, array("id" => "", "name" => "Select Lead Type"));

                                            echo render_select('lead_type', $lead_type, array('id', 'name'), "", $lead_type_status, [], [], "", "", "", "lead_type");
                                            ?>
                                            <input type="hidden" name="admissionpreferencesid" id="admissionpreferencesid" value="<?php echo $admissionpreferences->id ?>">
                                            <input type="hidden" name="client_id" id="client_id" value="<?php echo $client_id ?>">

                                        </div>
                                    </div>
                                    <div class="row">


                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="course">Course <small class="text-danger">*</small></label>
                                                <input name="course" id="course" type="hidden" class="form-control" value="<?= !empty($admissionpreferences->course) ? $admissionpreferences->course : '' ?>">
                                                <input type="text" class="form-control" readonly disabled required-check value="<?= !empty($admissionpreferences->course) ? $admissionpreferences->course : '' ?>">

                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="session_intake">Session Intake <small class="text-danger">*</small></label>
                                                <input type="month" class="form-control" required-check id="session_intake" name="session_intake"
                                                    value="<?= !empty($admissionpreferences->session_intake) ? date('Y-m', strtotime($admissionpreferences->session_intake)) : '' ?>"
                                                    placeholder="Select Month and Year">
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="acadmic_year">Acadmic Year <small class="text-danger">*</small></label>
                                                <?php
                                                $currentYear = date("Y");
                                                $years = [
                                                    ($currentYear - 1) . " - " . $currentYear, // Previous Year
                                                    $currentYear . " - " . ($currentYear + 1), // Current Year
                                                    ($currentYear + 1) . " - " . ($currentYear + 2), // Next Year
                                                    ($currentYear + 2) . " - " . ($currentYear + 3)  // Next +1 Year
                                                ];
                                                $selectedYear = !empty($admissionpreferences->acadmic_year) ? $admissionpreferences->acadmic_year : ($currentYear . " - " . ($currentYear + 1));
                                                ?>

                                                <select class="form-control" id="acadmic_year" name="acadmic_year" required>
                                                    <?php foreach ($years as $year): ?>
                                                        <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>><?= $year ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">



                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="hidden" name="countries" id="countries">
                                                <label for="study_country">Where would you like to study? <small class="text-danger">*</small></label>
                                                <select class="form-control selectpicker  required required-check" required required-check name="study_country" id="study_country" multiple required>
                                                    <option value="">Select country </option>
                                                </select>
                                            </div>
                                        </div>

                                        <?php if (is_admin() || !empty($staff_list[get_staff_user_id()]["post_sales"])) { ?>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="primary_university">Primary University<small class="text-danger">*</small></label>
                                                    <select class="form-control selectpicker" required-check name="primary_university" onchange="select_primary_university(this)" id="primary_university" required>
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

                                    </div>
                                    <div class="universities row">

                                    </div>
                                </div>
                                <div class="row btn-save-fun margin-top">
                                    <div class="col-md-12 text-right  btn-save-fun margin-top">
                                        &nbsp; <button type="submit" onclick="save_admission_preferences()" class="btn btn-primary button-22">Save changes</button>
                                        &nbsp;
                                        <!-- <button type="button" id="freeze_admission_preferences" class="btn btn-warning button-22"><?php echo $admissionpreferences->freeze == 0 ? 'Freeze' : 'Unfreeze'; ?></button> -->
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane disabled-form" id="academic_details">
                <form id="admission-details-form" class="form-disabled" onsubmit="return false;">
                    <input name="academicDetailsId" type="hidden" value="<?= $academicdetails->id ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <h4>Academic Details </h4>
                                <hr>
                                <div class="row accadmic-education-div">
                                    <h4> 10<sup>th</sup> Academic Details </h4>
                                    <hr>
                                    <div class="col-lg-4 border2 border1">
                                        <div class="c1">
                                            <p>Board </p>
                                        </div>
                                        <div class="c2">
                                            <?php
                                            $selected = [];
                                            $selected[] = $academicdetails->tenth_board;
                                            echo render_select('tenth_board', $board_dropdown, array('id', 'name'), "", $selected, [], [], "", "", "", "tenth_board"); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 border2 border1">
                                        <div class="c1">
                                            <p>Year of Passing </p>
                                        </div>
                                        <div class="c2">
                                            <?php
                                            $selected = [];
                                            $selected[] = $academicdetails->tenth_passing_year;
                                            echo render_select('tenth_passing_year', $years_array, array('year', 'year'), "", $selected, [], [], "", "", "", "tenth_passing_year"); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 border2 border1">
                                        <div class="c1">
                                            <p>Marking Scheme </p>
                                        </div>
                                        <div class="c2">
                                            <?php
                                            $selected = [];
                                            $selected[] = $academicdetails->tenth_marking_scheme;
                                            echo render_select('tenth_marking_scheme', $markingSchemes, array('name', 'name'), "", $selected, [], [], "", "", "", "tenth_marking_scheme"); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 border2 border1">
                                        <div class="c1">
                                            <p>Percentage / CGPA </p>
                                        </div>
                                        <div class="c2">
                                            <input class="form-control" type="float" class="form-group" placeholder="Enter Percentage / CGPA" name="tenth_percentage" value="<?= $academicdetails->tenth_percentage; ?>">
                                        </div>
                                    </div>
                                    <?php
                                    foreach ($profile_section["10_stage"] as $s_stage) {
                                        $doc_type = $s_stage["name"] ?? '';
                                        $doc_id = $s_stage["id"] ?? '';
                                        $info = $s_stage["info"] ?? '';
                                        $accept = $s_stage["file_type"] ?? '';
                                        $is_mandatory = !empty($s_stage["mandatry"]);
                                        $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                        $required_attr = $is_mandatory ? "required required-check" : '';
                                        $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                        $required_attr = !empty($file_url) ? "" : $required_attr;

                                    ?>
                                        <div class="col-lg-4 media-files  ">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?> <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?> <?php if (!empty($info)) : ?>
                                                        &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                    <?php endif; ?></label>
                                                <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                <?php
                                                if (!empty($file_url)) {
                                                ?>
                                                    <div class="margin-top">
                                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>

                                <div class="row after accadmic-education-div">

                                    <h4>12<sup>th</sup> Academic Details<small class="text-danger">*</small></h4>
                                    <hr>

                                    <div class="row qualification-div" id="twelthAcademicDetails" style="display:<?= ($academicdetails->after_x_status == '12th' || $academicdetails->after_x_status == 'Both' || empty($academicdetails->after_x_status)) ? 'block' : 'none' ?>">

                                        <div class="col-lg-4 border2 border1">
                                            <div class="c1">
                                                <p>Board / University <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->twelth_board;
                                                echo render_select('twelth_board', $board_dropdown, array('id', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "twelth_board"); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 border2 border1">
                                            <div class="c1">
                                                <p>Year of Passing <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->twelth_passing_year;
                                                echo render_select('twelth_passing_year', $years_array, array('year', 'year'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "twelth_passing_year"); ?>

                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Result Status <?= $text_danger_mbbs ?></p>

                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->twelth_result_status;
                                                echo render_select('twelth_result_status', $resultStatus, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "twelth_result_status"); ?>
                                            </div>
                                        </div>
                                        <div class="twelth_result_status_div" style="display:<?= ($academicdetails->twelth_result_status == 'Awaited') ? 'none' : '' ?>">
                                            <div class="col-lg-3 border2 border1 " id="twelth_marking_scheme_div">
                                                <div class="c1">
                                                    <p>Marking Scheme <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [$academicdetails->twelth_marking_scheme];
                                                    // Correcting the attribute array handling
                                                    $attributes = [];
                                                    if ($academicdetails->twelth_result_status == 'Declared') {
                                                        $attributes["required-check"] = "required-check";
                                                    }
                                                    echo render_select(
                                                        'twelth_marking_scheme',
                                                        $markingSchemes,
                                                        ['name', 'name'],
                                                        "",
                                                        $selected,
                                                        $attributes,
                                                        [],
                                                        "",
                                                        "",
                                                        "",
                                                        "twelth_marking_scheme"
                                                    );
                                                    ?>

                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Percentage / CGPA <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input class="form-control" <?= $text_danger_mbbs_required ?> type="float" placeholder="Enter Your 12th Percentage" name="twelth_percentage" id="twelth_percentage" value="<?= $academicdetails->twelth_percentage; ?>">
                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>PCB <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input class="form-control" <?= $text_danger_mbbs_required ?> type="float" placeholder="PCB Marks" name="pcb" id="pcb" value="<?= $academicdetails->pcb; ?>">
                                                </div>
                                            </div>
                                            <?php
                                            foreach ($profile_section["12_stage"] as $s_stage) {
                                                $doc_type = $s_stage["name"] ?? '';
                                                $doc_id = $s_stage["id"] ?? '';
                                                $info = $s_stage["info"] ?? '';
                                                $accept = $s_stage["file_type"] ?? '';
                                                $is_mandatory = !empty($s_stage["mandatry"]);
                                                $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                                $required_attr = $is_mandatory ? "required required-check" : '';
                                                $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                                $required_attr = !empty($file_url) ? "" : $required_attr;
                                            ?>
                                                <div class="col-lg-4 border2 media-files  ">
                                                    <div class="form-group">
                                                        <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?> <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?> <?php if (!empty($info)) : ?>
                                                                &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                            <?php endif; ?></label>
                                                        <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                        <?php
                                                        if (!empty($file_url)) {
                                                        ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>

                                                    </div>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php

                                ?>
                                <div id="entrance_exam_div" class="row accadmic-education-div ">
                                    <h4>NEET Exam</h4>
                                    <hr>

                                    <div class="col-lg-2 border2 border1">
                                        <div class="c1">
                                            <p>Result Status <?= $text_danger_mbbs ?></p>
                                        </div>
                                        <div class="c2">
                                            <?php
                                            $selected = [];
                                            $selected[] = $academicdetails->entrance_result_status;
                                            echo render_select('entrance_result_status', $neetResultStatus, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "entrance_result_status"); ?>
                                        </div>

                                    </div>

                                    <div class="col-lg-3 border2 border1 hide_ " style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Fail') ? 'none' : '' ?>">
                                        <div class="c1">
                                            <p>Registration Number <?= $text_danger_mbbs ?></p>
                                        </div>
                                        <div class="c2">
                                            <input class="form-control" required-check type="number" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'readonly' : ''; ?> class="form-group" pattern="\d{12}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                minlength="12" maxlength="12" placeholder="Enter Entrance Roll No" name="entrance_roll" value="<?= $academicdetails->entrance_roll; ?>">
                                        </div>

                                    </div>
                                    <div class="col-lg-3 border2 border1 hide_" style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Fail') ? 'none' : '' ?>">
                                        <div class="c1">
                                            <p>Year <?= $text_danger_mbbs ?></p>
                                        </div>
                                        <div class="c2">
                                            <!-- <input class="form-control" type="text" placeholder="Enter Entrance Year" name="entrance_year" value="<?= $academicdetails->entrance_year; ?>"> -->
                                            <!--<input type="number" required-check name="entrance_year" min="2000" max="2025" step="1" placeholder="YYYY"  id="entrance_year" class="form-control" value="<?= ($academicdetails->entrance_year) ? $academicdetails->entrance_year : '' ?>" placeholder="Select Month and Year">-->


                                            <?php
                                            $selected = [];
                                            $selected[] = ($academicdetails->entrance_year) ? extractYear($academicdetails->entrance_year) : '';
                                            echo render_select('entrance_year', $years_array_entrance, array('year', 'year'), "", $selected, ["required" => "required", "required-check" => "required-check", "readonly" => "<?= ($academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status =='Fail') ? 'true' : 'false'; ?>"], [], "", "", "", "entrance_year");
                                            ?>
                                        </div>

                                    </div>

                                    <div class="col-lg-3 border2 border1 hide_ " style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Fail') ? 'none' : '' ?>">
                                        <div class="c1">
                                            <p>Marks <?= $text_danger_mbbs ?></p>
                                        </div>
                                        <div class="c2 ">
                                            <input type="number" required-check <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'readonly' : ''; ?> class="form-control" placeholder="Marks" name="entrance_percentage" id="entrance_percentage" value="<?= $academicdetails->entrance_percentage; ?>">

                                            <?php

                                            if (!empty($score_columns)) {
                                                foreach ($score_columns as $column) {
                                                    if (!empty($entrance_data[$entrance_names[0]]["academic_type"]) && $entrance_data[$entrance_names[0]]["academic_type"] == $column["exam_type"]) {
                                            ?>
                                                        <label class="multiple_score_label"><?= $column['name'] ?></label>
                                                        <input required-check type="text" style="margin-top:3px" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> class="form-control column_score multiple_score" placeholder="<?= $column['name'] ?>" name="score_column-<?= $column["id"] ?>" id="score_column-<?= $column["id"] ?>" value="<?= !empty($score_value[$column["id"]]["value"]) ? $score_value[$column["id"]]["value"] : '' ?>">

                                            <?php
                                                    }
                                                }
                                            }

                                            // score_columns
                                            ?>
                                        </div>

                                    </div>

                                    <div class="col-lg-3 border2 border1 hide_" style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Fail') ? 'none' : '' ?>">
                                        <div class="c1">
                                            <p>Neet Status <?= $text_danger_mbbs ?></p>
                                        </div>
                                        <?php
                                        // Add "Select Neet Status" as the first option
                                        array_unshift($neet_status, ["id" => "", "value" => "", "name" => "Select Neet Status"]);

                                        // Set the selected value
                                        $selected_neet_status = !empty($academicdetails->neet_status) ? [$academicdetails->neet_status] : [''];

                                        // Handle the "required-check" attribute conditionally
                                        $attributes = [];
                                        if (in_array($academicdetails->entrance_result_status, ['Declared'])) {
                                            $attributes["required-check"] = "required-check";
                                        }

                                        // Render the select field
                                        echo render_select(
                                            'neet_status',
                                            $neet_status,
                                            ['id', 'name'],
                                            "",
                                            $selected_neet_status,
                                            $attributes,
                                            [],
                                            "",
                                            "",
                                            "",
                                            "neet_status"
                                        );
                                        ?>

                                    </div>
                                    <?php
                                    foreach ($profile_section["neet_exam_stage"] as $s_stage) {
                                        $doc_type = $s_stage["name"] ?? '';
                                        $doc_id = $s_stage["id"] ?? '';
                                        $info = $s_stage["info"] ?? '';
                                        $accept = $s_stage["file_type"] ?? '';
                                        $is_mandatory = !empty($s_stage["mandatry"]);
                                        $required_attr = $is_mandatory ? "required-check" : '';
                                        $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                        $required_attr = $is_mandatory ? "required required-check" : '';
                                        $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                        $required_attr = !empty($file_url) ? "" : $required_attr;
                                    ?>

                                        <div class="col-lg-3 border2 border1 media-files hide_ " style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Fail') ? 'none' : '' ?>">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?> <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?> <?php if (!empty($info)) : ?>
                                                        &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                    <?php endif; ?></label>
                                                <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="file" name="files[<?= $doc_id ?>]" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'readonly' : ''; ?> class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                <?php
                                                if (!empty($file_url)) {
                                                ?>
                                                    <div class="margin-top">
                                                        <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>

                                                        <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>

                                </div>
                                <!-- end ug details-->
                                <hr>


                                <div class="row" style="padding-top: 30px;padding-bottom: 20px;">
                                    <div class="col-lg-6 col-xs-6" style="padding-left: 0px;">

                                    </div>
                                    <div class="col-lg-6 col-xs-6" style="padding-right: 0px;">

                                    </div>
                                </div>
                                <?php // echo form_close(); 
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="btn-save-fun margin-top">
                        <div class="col-md-12">
                            <button type="submit" onclick="save_admission_details()" class="btn btn-primary button-22 pull-right margin-top">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>

            <div role="tabpanel" class="tab-pane" id="documents">
                <div class="">
                    <div class="col-md-12">
                        <form id="documents-form" onsubmit="return false;">
                            <div class="row">
                                <h4>Documents Required <small class="text-danger">*</small></h4>
                                <hr>
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">S.No</th>
                                            <th scope="col">Document Type</th>
                                            <th scope="col">Stage</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Upload</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="document_upload_div">

                                        <?php

                                        if (!empty($documents_type)) : ?>
                                            <?php foreach ($documents_type as $key => $doc_files) :

                                                $doc_type = $doc_files["name"] ?? '';
                                                $doc_id = $doc_files["id"] ?? '';
                                                $info = $doc_files["info"] ?? '';
                                                $accept = $doc_files["file_type"] ?? '';
                                                $is_mandatory = !empty($doc_files["mandatry"]);
                                                $mandatry_text = $is_mandatory ? "<small class='text-danger'>*</small>" : '';
                                                $required_attr = "";
                                                // $required_attr = $is_mandatory ? "required required-check" : '';
                                                $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
                                                $required_attr = !empty($file_url) ? "" : $required_attr;
                                            ?>
                                                <tr>
                                                    <td><?= ($key + 1) ?></td>
                                                    <td>
                                                        <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">


                                                        <?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') . ' ' . $mandatry_text  . "  (" . $doc_files["file_type"] . ")" ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= $doc_files["stage"] ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($doc_files["lead_type"])) {
                                                            $status = isset($applicant_documents[$doc_id]["approval_status"])
                                                                ? ($applicant_documents[$doc_id]["approval_status"] == 1 ? 'Approved' : 'Rejected')
                                                                : (!empty($file_url) ? 'Pending' : '');

                                                            $class = $status === 'Approved' ? 'text-success'
                                                                : ($status === 'Rejected' ? 'text-danger'
                                                                    : ($status === 'Pending' ? 'text-warning' : ''));

                                                        ?>

                                                            <span class="<?= $class; ?>"><?= $status; ?></span>
                                                        <?php } ?>

                                                    </td>
                                                    <td>
                                                        <?php if (!empty($doc_files["lead_type"])) { ?>
                                                            <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        if (!empty($file_url)) {
                                                        ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>

                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
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
                            <div class="row  margin-top">
                                <div class="col-md-12">
                                    <button type="submit" onclick="save_documents()" class="btn btn-primary button-22 pull-right margin-top">Save changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane" id="welcome_message">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">

                            <h4>Welcome Message</h4>
                            <hr>
                            <form id="welcome-information-form" class="" onsubmit=" return false;">
                                <?php

                                $get_clients_fees = get_clients_fees_details($lead_type_status, $client_id, REGISTRATION_AMOUNT_ID);
                                $registration_amount = !empty($get_clients_fees[0]["total_amount"]) ? $get_clients_fees[0]["total_amount"] : 0;
                                ?>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <label class="form-check-label">Registration Amount Cash Deposite
                                            <input type="checkbox" <?= !empty($final_sumbit) ? 'disabled' : '' ?> value="<?= !empty($client->registration_slip_cash_status) && $client->registration_slip_cash_status == 1 ? 1 : 0 ?>" class="form-check-input <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>" onclick="check_registration_cash_status(this,'hide-show-regi')" <?= !empty($client->registration_slip_cash_status) && $client->registration_slip_cash_status == 1 ? 'checked' : '' ?> name="registration_slip_cash_status" <?= !empty($client->registration_slip_cash_status && $client->registration_slip_cash_status == 1) ? 'checked' : '' ?>>

                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Date of payment <small class="text-danger">*</small></label>
                                            <input <?= $text_danger_mbbs_required ?> <?= !empty($final_sumbit) ? 'disabled' : '' ?> class="form-control <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>" type="date" name="date_of_payment" value="<?= $client->date_of_payment ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Regisration amount <small class="text-danger">*</small> </label>
                                            <input class="form-control" disabled <?= $text_danger_mbbs_required ?> type="text" value="<?= $registration_amount ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Payment received from <small class="text-danger">*</small></label>
                                            <input class="form-control <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>" <?= !empty($final_sumbit) ? 'disabled' : '' ?> type="text" name="payment_recevied_from" <?= $text_danger_mbbs_required ?> value="<?= !empty($client->payment_recevied_from) ? $client->payment_recevied_from : '' ?>">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Quotation <small class="text-danger">*</small></label>
                                            <input <?= !empty($client->quotation) ? '' : $text_danger_mbbs_required ?> class="form-control" type="file" accept=".pdf, image/*" name="quotation" value="" <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>>
                                            <?php
                                            if (!empty($client->quotation)) {
                                            ?>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-primary btn-xs m-2" onclick="show_media_files('<?= base_url($client->quotation) ?>');"></i>
                                                    <i class="fa fa-download btn btn-primary btn-xs m-2" onclick="download_media_files(`<?= base_url($client->quotation) ?>`, '_blank');"></i>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Fees Structure <small class="text-danger">*</small></label>
                                            <input <?= !empty($client->fees_structure) ? '' : $text_danger_mbbs_required ?> class="form-control" type="file" accept=".pdf, image/*" name="fees_structure" value="" <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>>
                                            <?php
                                            if (!empty($client->fees_structure)) {
                                            ?>
                                                <div class="margin-top">
                                                    <i class="fa fa-eye btn btn-primary btn-xs m-2" onclick="show_media_files('<?= base_url($client->fees_structure) ?>');"></i>
                                                    <i class="fa fa-download btn btn-primary btn-xs m-2" onclick="download_media_files(`<?= base_url($client->fees_structure) ?>`, '_blank');"></i>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 hide-show-regi" style="display: <?= !empty($client->registration_slip_cash_status) ? 'none' : 'block' ?>;">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Registration Proof <small class="text-danger">*</small></label>
                                            <input <?= !empty($final_sumbit) ? 'disabled' : '' ?> <?= !empty($client->registration_slip) ? '' : $text_danger_mbbs_required ?> class="form-control <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>" type="file" accept=".pdf, image/*" name="registration_slip" value="">
                                            <?php
                                            if (!empty($client->registration_slip)) {
                                            ?>
                                                <div class="margin-top">
                                                    <i onclick="show_media_files('<?= base_url($client->registration_slip) ?>');" class="fa fa-eye btn btn-xs btn-primary"></i>
                                                    <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($client->registration_slip) ?>`, '_blank');"></i>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row btn-save-fun">
                                    <div class="col-md-12 ">
                                        <button type="submit" onclick="save_welcome_info()" class="btn btn-primary button-22 pull-right margin-top">Save changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane" id="fees_details">
                <div class="row">
                    <div class="col-md-12">
                        <form id="fees-details-form" class="form-disabled" onsubmit=" return false;">

                            <div class="card">
                                <h4>Fees Details</h4>
                                <hr>

                                <?php
                                $get_clients_fees = get_clients_fees((isset($lead_type_status) ? $lead_type_status : ''), $client_id);
                                $get_currencies = get_currencies();
                                $get_currencies = array_column($get_currencies, null, 'id');




                                if (!empty($get_clients_fees) && !empty($get_currencies)) {
                                ?>
                                    <div id="applicant_fees">

                                        <div class="row">
                                            <?php
                                            foreach ($get_clients_fees as $fees) {

                                                $id = $fees["id"];
                                                $amount = $fees["amount"];
                                                // Prepare the field name by replacing spaces with underscores and converting to lowercase
                                                $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                                                // Set the required attribute based on the "mandatry" field
                                                $required = !empty($fees["mandatry"]) ? "required" : "false";
                                                $mandatry = !empty($fees["mandatry"]) ? "<small class='text-danger'>*</small>" : "";


                                            ?>
                                                <div class="col-lg-4 col-md-4 col-6 fees-block-<?= $id ?>">
                                                    <label><?= $fees['name'] ?> <?= $mandatry ?><span class="fees_label_<?= $id ?>"></span></label><br>
                                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                                                        <input type="hidden" value="<?= $field_name ?>" name="applicant_fees[]">
                                                        <input type="hidden" value="<?= $fees['id'] ?>" name="<?= $field_name ?>_id">
                                                        <input type="hidden" value="<?= $fees['detail_id'] ?>" name="<?= $field_name ?>_detail_id_<?= $fees['id'] ?>">

                                                        <div class="input-group-addon currency-symbol-<?= $id ?>">
                                                            <?php
                                                            $symbol = '$'; // default

                                                            if (!empty($fees["currency_id"]) && !empty($get_currencies[$fees["currency_id"]]["symbol"])) {
                                                                $symbol = $get_currencies[$fees["currency_id"]]["symbol"];
                                                            } elseif (empty($fees["currency_id"])) {
                                                                $symbol = $get_currencies[$fees["default_currency"]]["symbol"];
                                                            }
                                                            ?>

                                                            <?= $symbol ?>


                                                        </div>
                                                        <input type="text" name="<?= $field_name ?>" <?= $required ?> class="form-control currency-amount fees_<?= $fees['id'] ?>" placeholder="0.00" id="<?= $field_name ?>" value="<?= $fees["amount"] ?>" size="8">
                                                        <div class="input-group-addon currency-addon">

                                                            <select name="<?= $field_name ?>_currency_type" id="<?= $field_name ?>" class="currency-selector currency-selector-<?= $id ?>" onchange="updateSymbol(<?= $id ?>)">
                                                                <?php foreach ($get_currencies as $c) {
                                                                ?>
                                                                    <option
                                                                        data-symbol="<?= $c['symbol'] ?>"
                                                                        value="<?= $c['id'] ?>"
                                                                        data-placeholder="0.00"
                                                                        <?=
                                                                        (!empty($fees['currency_id']) && $fees['currency_id'] == $c['id']) ||
                                                                            (empty($fees['currency_id']) && !empty($fees['default_currency']) && $fees['default_currency'] == $c['id'])
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
                                            <?php
                                            }
                                            ?>
                                            <div class="col-lg-4 col-md-4 col-6 fees-block-8">
                                                <label>&nbsp;</label>

                                                <div class="form-check checkbox">
                                                    <input
                                                        class="form-check-input checkbox-group"
                                                        type="checkbox"
                                                        id="air_ticket_include"
                                                        name="air_ticket_include"
                                                        <?= ($client->air_ticket_include == 1) ? 'checked' : '' ?>
                                                        <?= (strtolower($admissionpreferences->primary_country) != "georgia") ? 'disabled' : '' ?>>
                                                    <label class="form-check-label" for="air_ticket_include">
                                                        Air ticket inc. in Service Charge <span class="text-danger">*</span>
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                <?php } ?>
                            </div>

                            <div class="card scholarship-details margin-top">
                                <h4>Scholarship Details</h4>
                                <hr>
                                <div class="col-md-12">
                                    <div class="form-check checkbox">
                                        <checkbox class="form-check">
                                            <input type="checkbox" value="1" onchange="scholarshipCase(this)" class="form-check-input checkbox-group" id="scholarship_status" name="scholarship_status" <?= !empty($client->scholarship_status) && $client->scholarship_status == 1 ? 'checked' : '' ?> <?= !empty($final_sumbit) ? 'disabled' : '' ?>>
                                            <label class="form-check-label" for="scholarship_status">Scholarship Case</label>

                                    </div>

                                    <div class="row scholarship-case <?= !empty($client->scholarship_status) && $client->scholarship_status == 1 ? '' : 'hide' ?> margin-top">
                                        <div class="col-md-4 fees-block-scholarship">
                                            <label>Scholarship Amount</label>
                                            <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">

                                                <div class="input-group-addon currency-symbol-scholarship">
                                                    <?php
                                                    $symbol = '$'; // default

                                                    if (!empty($client->scholarship_currency) && !empty($get_currencies[$client->scholarship_currency]["symbol"])) {
                                                        $symbol = $get_currencies[$client->scholarship_currency]["symbol"];
                                                    } elseif (empty($client->scholarship_currency)) {
                                                        $symbol = $get_currencies[3]["symbol"];
                                                    }
                                                    ?>

                                                    <?= $symbol ?>


                                                </div>

                                                <input type="text" name="scholarship_amount" <?= $required ?> class="form-control scholarship_amount" placeholder="0.00" id="scholarship_amount" value="<?= $client->scholarship_amount ?>" size="8">

                                                <div class="input-group-addon currency-addon">
                                                    <select name="scholarship_currency_type" id="scholarship_currency_type" class="currency-selector currency-selector-scholarship" onchange="updateSymbol('scholarship')">
                                                        <?php foreach ($get_currencies as $c) {
                                                        ?>
                                                            <option
                                                                data-symbol="<?= $c['symbol'] ?>"
                                                                value="<?= $c['id'] ?>"
                                                                data-placeholder="0.00"
                                                                <?=
                                                                (!empty($client->scholarship_currency) && $client->scholarship_currency == $c['id']) ||
                                                                    (empty($client->scholarship_currency) && !empty(3) && 3 == $c['id'])
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

                                        <div class="col-lg-8">
                                            <label for="scholarship_reason">Scholarship Reason</label>
                                            <textarea name="scholarship_reason" id="scholarship_reason" class="form-control" rows="3" <?= !empty($final_sumbit) ? 'disabled' : '' ?>><?= !empty($client->scholarship_reason) ? $client->scholarship_reason : '' ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    </div>
                    <div class=" row btn-save-fun margin-top">
                        <div class="col-md-12 ">
                            <button type="submit" onclick="fees_details()" class="btn btn-primary button-22 pull-right margin-top">Save changes</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            
               <div role="tabpanel" class="tab-pane" id="preview">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <br>
                        <br>
                        <form id="preview-form" onsubmit="return false;" class="<?= !empty($final_sumbit) ? 'hide' : '' ?>">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" onclick="final_submission()" class="btn btn-primary button-22 pull-right margin-top">Final Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        </div>


        <?php if (isset($client)) { ?>
            <div role="tabpanel" class="tab-pane hide" id="customer_admins">
                <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit') && (isset($final_sumbit) && $final_sumbit == 0)) { ?>
                    <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
                        <a href="#" data-toggle="modal" data-target="#customer_admins_assign" class="btn btn-info mbot30"><?php echo _l('assign_admin'); ?></a>
                    <?php } ?>
                    <table class="table dt-table">
                        <thead>
                            <tr>
                                <th><?php echo _l('staff_member'); ?></th>
                                <th><?php echo _l('customer_admin_date_assigned'); ?></th>
                                <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
                                    <th><?php echo _l('options'); ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer_admins as $c_admin) { ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('profile/' . $c_admin['staff_id']); ?>">
                                            <?php echo staff_profile_image($c_admin['staff_id'], array(
                                                'staff-profile-image-small',
                                                'mright5'
                                            ));
                                            echo get_staff_full_name($c_admin['staff_id']); ?></a>
                                    </td>
                                    <td data-order="<?php echo $c_admin['date_assigned']; ?>"><?php echo _dt($c_admin['date_assigned']); ?></td>
                                    <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
                                        <td>
                                            <a href="<?php echo admin_url('clients/delete_customer_admin/' . $client->userid . '/' . $c_admin['staff_id']); ?>" class="btn btn-danger _delete btn-icon"><i class="fa fa-remove"></i></a>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        <?php } ?>
     
    </div>
</div>

</div>
<?php if (isset($client)) { ?>
    <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
        <div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <input type="hidden" name="clientid" id="clientid" value="<?php echo $client_id ?>">

                <?php echo form_open(admin_url('clients/assign_admins/' . $client->userid)); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <?php
                        $selected = array();
                        foreach ($customer_admins as $c_admin) {
                            array_push($selected, $c_admin['staff_id']);
                        }
                        echo render_select('customer_admins[]', $staff, array('staffid', array('firstname', 'lastname')), '', $selected, array('multiple' => true), array(), '', '', false); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                    </div>
                </div>
                <!-- /.modal-content -->
                <?php echo form_close(); ?>
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    <?php } ?>
<?php } ?>

<?php //$this->load->view('admin/clients/client_group'); 
?>

<script>
    var primary_country = "<?= !empty($admissionpreferences->primary_country) ? $admissionpreferences->primary_country : 0 ?>";
    var primary_university = "<?= !empty($admissionpreferences->primary_university) ? $admissionpreferences->primary_university : 0 ?>";

    var select_segment_default = "";
    var user_id = "<?= !empty($admissionpreferences->user_id) ? $admissionpreferences->user_id : '' ?>";
    var study_country_selected = <?= !empty(json_encode(explode(",", $admissionpreferences->study_country))) ? json_encode(explode(",", $admissionpreferences->study_country), true) : "" ?>;
    // console.log(study_country_selected.length);
    if (study_country_selected.length > 0) {
        study_country_selected = study_country_selected.map(function(value) {
            return value.trim().toLowerCase();
        });
    }
    var dropdown_country_university_selection = <?= !empty($dropdown_country_university_selection) ? json_encode($dropdown_country_university_selection, true) : [] ?>;
    // console.log(dropdown_country_university_selection);
    var complete_application = " <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 1 : 0 ?>";

    if (complete_application == 1) {
        setTimeout(function() {
            $("form").find("input, select, textarea,button").prop("disabled", true).selectpicker("refresh");
        }, 1500);
    }


    document.addEventListener("DOMContentLoaded", function() {
        let feesMandatory_single = "";

        <?php if ($lead_data->source == REFERENCE_ID) { ?>
            feesMandatory_single = "<?= REFERENCE_AMOUNT_ID ?>"; // Assigning the value
        <?php } else { ?>
            let feeBlock = document.querySelector(".fees-block-<?= REFERENCE_AMOUNT_ID ?>");
            if (feeBlock) {
                feeBlock.style.display = "none"; // Hiding the element
            }
        <?php } ?>

        $(".check-phonenumber").on("input", function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });

    });


    function formatPhoneNumber(input) {
        console.log("phonenumber validation");
        // Remove all non-digit characters
        const digits = input.replace(/\D/g, '');

        // Remove country code if present (e.g., leading '91' or '0' for Indian numbers)
        let trimmed = digits;

        // If it starts with '91' and total is more than 10 digits, trim it
        if (trimmed.length > 10 && trimmed.startsWith('91')) {
            trimmed = trimmed.slice(2);
        }

        // If it starts with '0' and total is more than 10 digits, trim it
        if (trimmed.length > 10 && trimmed.startsWith('0')) {
            trimmed = trimmed.slice(1);
        }

        // Final check: return only if it's exactly 10 digits
        if (trimmed.length === 10) {
            return trimmed;
        } else {
            return null; // Invalid number
        }
    }


    function show_country_dropdown(select_segment) {
        select_segment_default = select_segment;
        var filteredData = dropdown_country_university_selection.filter(function(entry) {
            return entry.name.toLowerCase() === select_segment.trim().toLowerCase();
        });

        var uniqueCountries = [];
        var uniqueData = filteredData.filter(function(entry) {
            if (!uniqueCountries.includes(entry.country_name.toLowerCase())) {
                uniqueCountries.push(entry.country_name.charAt(0).toUpperCase() + entry.country_name.slice(1));
                return true;
            }
            return false;
        });

        uniqueCountries = [...new Set(uniqueCountries)];
        var study_country = $("#study_country");
        // Clear current options
        study_country.empty();

        // Create new option elements
        uniqueCountries.forEach(function(country_name) {
            // let disabled = '';
            // console.log(country_name.trim().toLowerCase() + "==" +primary_country.trim().toLowerCase() );
            // if (country_name.trim().toLowerCase() === primary_country.trim().toLowerCase()) {
            // disabled = 'disabled not-change';
            // }

            var option = $('<option  value="' + country_name + '">').text(country_name);
            if (study_country_selected.length > 0) {
                if (study_country_selected.indexOf(country_name.trim().toLowerCase()) !== -1) {
                    option.prop('selected', true);
                }
            }
            study_country.append(option);
        });

        // Refresh selectpicker
        study_country.selectpicker('refresh');

        // study_country.trigger('change');
        // set_university();
    }



    function set_primary_diabled() {
        if (admin_status == 0) {
            $('#study_country option[value="' + primary_country + '"]').prop('disabled', true);
            $("#study_country").selectpicker('refresh');
        }
    }


    function set_primary_enabled() {
        $('#study_country option[value="' + primary_country + '"]').prop('disabled', false);
        $("#study_country").selectpicker('refresh');
    }


    function set_university_diabled() {
        if (admin_status == 0) {
            $(".universities .tag").each(function() {
                let plainText = $(this).text().replace(/\s+/g, ' ').trim(); // Clean up spaces

                // Remove the last '×' if it exists
                if (plainText.endsWith('×')) {
                    plainText = plainText.slice(0, -1).trim();
                }

                if (plainText === primary_university.trim()) {
                    console.log(plainText);
                    $(this).addClass('disabled');
                    $(this).find("a").hide();
                    $(this).css('pointer-events', 'none');
                }
            });
        }

    }





    function show_university_dropdown(select_segment, country) {
        // console.log("select_segment" + select_segment);
        // console.log("country" + country);
        return new Promise(function(resolve, reject) {
            var filteredData = dropdown_country_university_selection.filter(function(entry) {
                return entry.name.toLowerCase() === select_segment.trim().toLowerCase() && entry.country_name.toLowerCase() === country.trim().toLowerCase();
            });

            var uniqueUniversity = [];
            var uniqueData = filteredData.filter(function(entry) {
                if (!uniqueUniversity.includes(entry.university_name.toLowerCase())) {
                    uniqueUniversity.push(entry.university_name.charAt(0).toUpperCase() + entry.university_name.slice(1));
                    return true;
                }
                return false;
            });
            uniqueUniversity = [...new Set(uniqueUniversity)];

            resolve(uniqueUniversity);
        });
    }


    document.addEventListener("DOMContentLoaded", function() {
        var leadTypeSelect = document.getElementById("lead_type");
        var selectedValue = leadTypeSelect.options[leadTypeSelect.selectedIndex].text.trim().toLowerCase();
        select_segment_default = selectedValue;
        show_country_dropdown(selectedValue);

    });

    document.getElementById("lead_type").addEventListener("change", function() {
        var select_segment = this.options[this.selectedIndex].text.trim().toLowerCase();
        show_country_dropdown(select_segment);
    });

    document.addEventListener("DOMContentLoaded", function() {
        let dobInput = document.getElementById("dob");
        if (dobInput) {
            let today = new Date();
            let minAgeDate = new Date(today.getFullYear() - 15, today.getMonth(), today.getDate());
            dobInput.setAttribute("max", minAgeDate.toISOString().split("T")[0]);
        }
    });






    document.getElementById("passport_number").addEventListener("input", function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Convert to uppercase & remove invalid characters
    });

    function updateSymbol(id) {
        var selected = $(".currency-selector-" + id + " option:selected");
        $(".currency-symbol-" + id).text(selected.data("symbol"));
    }

    function scholarshipCase(event) {
        if (event.checked) {
            $(".scholarship-case").removeClass("hide");
        } else {
            $(".scholarship-case").addClass("hide");
            $(".scholarship-case").find("input, select, textarea").val("");
        }
    }
</script>