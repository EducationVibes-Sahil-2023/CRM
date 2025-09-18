<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php


if (!empty($score_value)) {
    $score_value = array_column($score_value, null, "type");
}
$passport_stages = get_passport_stages();
$caste_category = get_caste_category();
$neet_status = get_neet_status();
$relationshipArray = get_relationShip();
$yesNO_Array = [array("id" => 0, "name" => "No"), array("id" => 1, "name" => "Yes")];
$degreeArray = get_degree();
$universities_list = get_universities_list();
$diploma_board = get_diploma_board_list();
$board_dropdown = get_board_dropdown();
$offerLetersDownload = get_offerLetters($client_id);
$PreDepositeDownload = get_preDeposite($client_id);

$staff_list              = $this->leads_model->get_staff_list();
$get_entrance_exams_list              = $this->clients_model->get_entrance_exam_list();
$get_entrance_exam              = $this->clients_model->get_entrance_exam($client_id);
$get_entrance_exam_scrore              = $this->clients_model->get_entrance_exam_scrore($client_id);
$get_entrance_exams_status = get_status_table("entrance_status");

// $get_entrance_exams_status = [array("id" => "1", "selected" => "0", "name" => "Not Given"), array("id" => "2", "selected" => "1", "name" => "Given")];
$staff_list = array_column($staff_list, null, "staffid");
if (!empty($board_dropdown)) {
    array_unshift($board_dropdown, array("id" => "", "name" => "Select Board"));
}
$getWorkExperience    = $this->clients_model->getWorkExperience($client_id);

$documents_type =  get_documents($lead_type_status, [], 1);
$profile_section = [];
foreach ($documents_type as $documents) {
    $profile_section[$documents["profile_stages"]][] = $documents;
}

$priority_array = [];
for ($i = 1; $i <= PRIORITY_ARRAY_STUDY_ABROAD; $i++) {
    $priority_array[] = [
        'id' => $i,
        'name' => "P $i",
    ];
}
array_unshift($priority_array, array("id" => "", "name" => ""));




// array_push($documents_type, array("id" => "application", "name" => "Admission Letter", "file_type" => ".pdf,image/*"));
// array_push($documents_type, array("id" => "invitation", "name" => "Invitation Letter", "file_type" => ".pdf,image/*"));
// array_push($documents_type, array("id" => "visa", "name" => "Visa", "file_type" => ".pdf,image/*"));



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

    // array_push($applicant_documents, array("id" => "application", "document_file" => !empty($university_shortlisting[0]['application_file']) ? $university_shortlisting[0]['application_file'] : ''));
    // array_push($applicant_documents, array("id" => "invitation", "document_file" => !empty($visa_details[0]['file']) ? $visa_details[0]['file'] : ''));
    // array_push($applicant_documents, array("id" => "visa", "document_file" => !empty($university_shortlisting[0]['invitation_letter']) ? $university_shortlisting[0]['invitation_letter'] : ''));

    if (!empty($applicant_documents)) {
        $applicant_documents = array_column($applicant_documents, null, "id");
    }
}






$startYear = 2000;
$currentYear = date('Y');
$years_array = [];

for ($year = $currentYear; $year >= $startYear; $year--) {
    $years_array[] = ["year" => $year];
}

array_unshift($years_array, array(""));

array_unshift($diploma_board, array("id" => "", "name" => "Select Diploma"));

array_unshift($get_entrance_exams_list, array("id" => "", "name" => "Select Entrance Exams"));



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


array_unshift($course_list_ug, ["id" => "", "course_name" => "Select Course"]);
array_unshift($course_list_pg, ["id" => "", "course_name" => "Select Course"]);
array_unshift($universities_list, ["id" => "", "name" => "Select University"]);


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
    var admissionpreferences_freeze = "<?= !empty($admissionpreferences->freeze) ? 1 : 0 ?>";
</script>
<?php
$text_danger_mbbs = "";
$text_danger_mbbs_required = "";
if ($lead_type_status == 1) {
    $text_danger_mbbs = "<small class='text-danger'>*</small>";
    $text_danger_mbbs_required = "required-check";
}

?>
<style>
    select.ui-datepicker-year {
        color: black;
    }

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
    .p-0 {
        padding: 0px;
    }

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
                        <a href="#student_details" class="active" aria-controls="student_details" role="tab"
                            data-toggle="tab">Student Details</a>
                    </li>
                    <li role="presentation">
                        <a href="#passport" aria-controls="passport" role="tab" data-toggle="tab">Passport</a>
                    </li>
                    <li role="presentation">
                        <a href="#admission_preferences" aria-controls="admission_preferences" role="tab"
                            data-toggle="tab">Admission Preferences</a>
                    </li>
                    <li role="presentation">
                        <a href="#academic_details" aria-controls="academic_details" role="tab"
                            data-toggle="tab">Academic Details</a>
                    </li>
                    <li role="presentation">
                        <a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a>
                    </li>
                    <li role="presentation">
                        <a href="#welcome_message" aria-controls="welcome_message" role="tab" data-toggle="tab">Welcome
                            Message</a>
                    </li>
                    <!-- <li role="presentation">
                        <a href="#fees_details" aria-controls="fees_details" role="tab" data-toggle="tab">Fees Details</a>
                    </li> -->
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
                                                <label for="exampleInputFirstName">First Name <small
                                                        class="text-danger">*</small></label>
                                                <input class="form-control" <?= $read_only ?> type="text"
                                                    class="form-group" required-check required placeholder="First Name"
                                                    name="first_name" id="first_name"
                                                    value='<?php echo (isset($basicdetails)) ? $basicdetails->first_name : $contact->firstname; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputLastName">Last Name</label>
                                                <input class="form-control " <?= $read_only ?> type="text"
                                                    class="form-group" placeholder="Last Name" name="last_name"
                                                    id="last_name"
                                                    value='<?php echo (isset($basicdetails)) ? $basicdetails->last_name : $contact->lastname; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail">Email Address <small
                                                        class="text-danger">*</small></label>
                                                <input class="form-control " <?= $read_only ?> type="text"
                                                    class="form-group" required-check required
                                                    placeholder="Email Address" name="email"
                                                    value='<?php echo (isset($basicdetails)) ? $basicdetails->email : $contact->email; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Mobile Number <small
                                                        class="text-danger">*</small></label>
                                                <input
                                                    class="form-control check-phonenumber check-phonenumber-validation"
                                                    <?= $read_only ?> type="tel" class="form-group" required-check
                                                    required placeholder="Mobile Number" name="mobile" pattern="\d{10}"
                                                    onkeypress="formatPhoneNumber(this.value)"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    maxlength="10"
                                                    value='<?php echo (isset($basicdetails)) ? $basicdetails->mobile : $contact->phonenumber; ?>'>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputDateOfBirth">Date Of Birth <small
                                                        class="text-danger">*</small></label>
                                                <input type="date" class="form-control" name="dob" id="dob" required
                                                    value='<?php echo ($basicdetails->dob != '') ? $basicdetails->dob : ''; ?>'
                                                    required required-check>
                                            </div>
                                        </div>


                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputPassword1">Gender <small
                                                        class="text-danger">*</small></label>
                                                <select class="form-control" name="gender" id="gender" required
                                                    required-check>
                                                    <option value="">Select</option>
                                                    <option
                                                        <?php echo ($basicdetails->gender == 'Male') ? 'selected' : ''; ?>>
                                                        Male</option>
                                                    <option
                                                        <?php echo ($basicdetails->gender == 'Female') ? 'selected' : ''; ?>>
                                                        Female</option>
                                                    <option
                                                        <?php echo ($basicdetails->gender == 'Other') ? 'selected' : ''; ?>>
                                                        Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <?php
                                                echo render_select('marital_status', $yesNO_Array, array('id', 'name'), "Marital Status",  $basicdetails->marital_status, [], [], "", "", "", "marital_status");
                                                ?>
                                            </div>
                                        </div>
                                        <!-- <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputPassword1">Category <small class="text-danger">*</small></label>
                                                <?php
                                                array_unshift($caste_category, array("id" => "", "value" => "", "name" => "Select Category"));
                                                $selected_category[] = !empty($basicdetails->category) ? $basicdetails->category : '';

                                                echo render_select('category', $caste_category, array('id', 'name'), "", $selected_category, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "category");
                                                ?>

                                            </div>
                                        </div> -->


                                        <!-- <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber">Address <small
                                                        class="text-danger">*</small></label>
                                                <textarea <?= $read_only ?> name="address"
                                                    class="form-control"><?php echo (isset($client)) ? $client->address : ''; ?></textarea>
                                            </div>
                                        </div> -->



                                    </div>
                                    <div class="row col-md-12">
                                        <labe>Emergency Contact information</label>

                                            <div class="row">
                                                <hr class="mtop5 mbot10" />
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="exampleInputMobileNumber">Name <small
                                                                class="text-danger">*</small></label>
                                                        <input class="form-control name-validation-check" required required-check type="text"
                                                            class="form-group" placeholder="Parents Name"
                                                            name="father_name"
                                                            value='<?php echo (isset($basicdetails)) ? $basicdetails->father_name : ''; ?>'>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="exampleInputMobileNumber">Contact <small
                                                                class="text-danger">*</small></label>
                                                        <input
                                                            class="form-control check-phonenumber check-phonenumber-validation PHONE-validation-check"
                                                            onkeypress="formatPhoneNumber(this.value)" required
                                                            required-check type="tel" pattern="\d{10}"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                            maxlength="10" class="form-group"
                                                            placeholder="Parents Contact" name="fathers_mobile"
                                                            value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_mobile : ''; ?>'>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="exampleInputMobileNumber">Email</label>
                                                        <input class="form-control" type="email" class="form-group email-validation-check"
                                                            placeholder="Parents Email" name="fathers_email"
                                                            value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_email : ''; ?>'>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <?php
                                                        echo render_select('relationship_id', $relationshipArray, array('id', 'name'), "Relationship",  $basicdetails->relationship_id, [], [], "", "", "", "relationship_id");
                                                        ?>
                                                    </div>
                                                </div>



                                                <?php if ($lead_data->source == REFERENCE_ID) { ?>
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label for="exampleInputDateOfBirth">Refrence Name <small
                                                                    class="text-danger">*</small></label>
                                                            <input type="text" <?= $read_only ?> class="form-control"
                                                                name="reference_name" id="reference_name" required
                                                                value='<?php echo ($client->reference_name != '') ? $client->reference_name : ''; ?>'
                                                                required required-check>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
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
                                                    <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                        <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle"
                                                                title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?></label>
                                                    <input type="hidden" name="doc_type[]"
                                                        value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_name[]"
                                                        value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_url[]"
                                                        value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>"
                                                        class="form-control"
                                                        accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                        <?= $required_attr ?>>
                                                    <?php
                                                    if (!empty($file_url)) {
                                                    ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                            <i class="fa fa-download  btn btn-xs btn-primary"
                                                                onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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
                                    <div class="row">
                                        <hr class="mtop5 mbot10" />
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?php
                                                echo render_select('tagging', $yesNO_Array, array('id', 'name'), "Tagging Status",  $client->tagging, [], [], "", "", "", "tagging");
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?php
                                                echo render_select('loan_required', $yesNO_Array, array('id', 'name'), "Loan Required",  $client->loan_required, [], [], "", "", "", "loan_required");
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn-save-fun">
                                        <div class="col-md-12">
                                            <button type="submit" onclick="save_basic_details()"
                                                class="btn btn-primary button-22 pull-right">Save & Next</button>
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
                                            <select class="form-control" name="passport_status"
                                                onchange="change_passport_status(1)" id="passport" required
                                                required-check>
                                                <option value="">Select Passport Status</option>
                                                <?php
                                                $showPasswordArn = 0;
                                                foreach ($passport_stages as $p) {
                                                    $selected = "";
                                                    if ($p["id"] == $passport_info->passport_status) {
                                                        $selected = "selected";
                                                        $show_passport_details = $p['show_status'];
                                                        $showPasswordArn = $p['arn'];
                                                    }
                                                ?>
                                                    <option value="<?= $p["id"] ?>"
                                                        data-passport_number_status="<?= $p['show_status'] ?>"
                                                        data-passport_arn_status="<?= $p['arn'] ?>" <?= $selected ?>>
                                                        <?= $p["name"] ?></option>
                                                <?php

                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        class="col-lg-3 passport-div-ARN <?= !empty($showPasswordArn && $showPasswordArn == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="passport_number">Passport ARN <small
                                                    class="text-danger">*</small></label>
                                            <input class="form-control passport-info text-uppercase" type="text"
                                                placeholder="Enter Passport ARN" name="passport_arn" id="passport_arn"
                                                pattern="^[A-Z0-9-]{15,20}$"
                                                title="Passport ARN must be 15 to 20 characters, using uppercase letters (A-Z), numbers (0-9), and hyphens (-) only."
                                                maxlength="20" onkeyup="isValidARN()"
                                                value="<?= isset($passport_info) ? htmlspecialchars($passport_info->passport_arn) : '' ?>"
                                                required-check>


                                        </div>
                                    </div>
                                    <div
                                        class="col-lg-3 passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="passport_number">Passport Number <small
                                                    class="text-danger">*</small></label>
                                            <input class="form-control passport-info capitalText" type="text"
                                                placeholder="Enter Passport Number" name="passport_number"
                                                id="passport_number" pattern="^[A-Z0-9]{6,9}$"
                                                title="Passport number must be 6 to 9 characters, only uppercase letters (A-Z) and numbers (0-9)."
                                                maxlength="9" minlength="6"
                                                value="<?= isset($passport_info) ? htmlspecialchars($passport_info->passport_number) : '' ?>"
                                                required-check>

                                        </div>
                                    </div>

                                    <div
                                        class="col-lg-3 passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="issue_date">Issue Date <small
                                                    class="text-danger">*</small></label>
                                            <input class="form-control passport-info" type="Date" class="form-group"
                                                placeholder="Enter Passport Number" name="issue_date"
                                                value="<?= (isset($passport_info) ? $passport_info->issue_date : '') ?>"
                                                required-check>
                                        </div>
                                    </div>


                                    <div
                                        class="col-lg-3 passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="exp_date">Expiry Date <small
                                                    class="text-danger">*</small></label>
                                            <input class="form-control passport-info" type="Date" class="form-group"
                                                placeholder="Enter Passport Number" name="exp_date"
                                                value="<?= (isset($passport_info) ? $passport_info->exp_date : '') ?>"
                                                required-check>
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
                                        <div
                                            class="col-lg-3 media-files passport-div-status <?= !empty($show_passport_details && $show_passport_details == 1) ? '' : 'hide' ?>">
                                            <div class="form-group">
                                                <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                    <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                    <?php if (!empty($info)) : ?>
                                                        &nbsp;<i class="fa fa-info-circle"
                                                            title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                    <?php endif; ?></label>
                                                <input type="hidden" name="doc_type[]"
                                                    value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_name[]"
                                                    value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="doc_url[]"
                                                    value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>"
                                                    class="form-control"
                                                    accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= $required_attr ?>>
                                                <?php
                                                if (!empty($file_url)) {
                                                ?>
                                                    <div class="margin-top">
                                                        <i class="fa fa-eye  btn btn-xs btn-primary"
                                                            onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                        <i class="fa fa-download  btn btn-xs btn-primary"
                                                            onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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
                                <div class="btn-save-fun">
                                    <div class="col-md-12">
                                        <button type="submit" onclick="save_passport_details()"
                                            class="btn btn-primary button-22 pull-right">Save & Next</button>
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

                            <h4>Admission Preferences <span class="float-right h4">Budget Range : <?= !empty($client->budget_range) ? $client->budget_range . " LPA " : '' ?></span></h4>
                            <hr>
                            <form id="admission-preferences-form" class="" onsubmit=" return false;">
                                <div class="">
                                    <div class="col-lg-4" style="display:none">
                                        <div class="form-group">
                                            <label for="program">Segment</label>
                                            <?php
                                            array_unshift($lead_type, array("id" => "", "name" => "Select Lead Type"));

                                            echo render_select('lead_type', $lead_type, array('id', 'name'), "", $lead_type_status, [], [], "", "", "", "lead_type");
                                            ?>
                                            <input type="hidden" name="admissionpreferencesid"
                                                id="admissionpreferencesid"
                                                value="<?php echo $admissionpreferences->id ?>">
                                            <input type="hidden" name="client_id" id="client_id"
                                                value="<?php echo $client_id ?>">

                                        </div>
                                    </div>
                                    <div class="row">


                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="course">Course <small class="text-danger">*</small></label>
                                                <input name="course" id="course" type="hidden" class="form-control"
                                                    value="<?= !empty($admissionpreferences->course) ? $admissionpreferences->course : '' ?>">
                                                <input type="text" class="form-control" readonly disabled required-check
                                                    value="<?= !empty($admissionpreferences->course) ? $admissionpreferences->course : '' ?>">

                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="degree">Degree</label>

                                                <select class="form-control selectpicker" onchange="degreeChange()"
                                                    name="degree" id="degree" required required-check>
                                                    <option value="">Select a Degree</option>
                                                    <?php foreach ($degreeArray as $p) {
                                                        $selected = "";
                                                        if ($admissionpreferences->degree == $p["id"]) {
                                                            $selected = "selected";
                                                        }
                                                    ?>
                                                        <option value="<?= $p["id"] ?>" data-type="<?= $p["type"] ?>"
                                                            <?= $selected ?>><?= $p["name"] ?></option>
                                                    <?php

                                                    }
                                                    ?>
                                                </select>
                                                <?php echo form_error('program'); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="session_intake">Session Intake <small
                                                        class="text-danger">*</small></label>
                                                <input type="month" class="form-control" required-check
                                                    id="session_intake" name="session_intake"
                                                    value="<?= !empty($admissionpreferences->session_intake) ? date('Y-m', strtotime($admissionpreferences->session_intake)) : '' ?>"
                                                    placeholder="Select Month and Year">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="acadmic_year">Acadmic Year <small
                                                        class="text-danger">*</small></label>
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

                                                <select class="form-control" id="acadmic_year" name="acadmic_year"
                                                    required>
                                                    <?php foreach ($years as $year): ?>
                                                        <option value="<?= $year ?>"
                                                            <?= ($year == $selectedYear) ? 'selected' : '' ?>><?= $year ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            </div>
                                        </div>
                                    </div>


                                    <h5 class="mtop20">Application Shortlisting</h5> <small> Choose one university as the primary by selecting its checkbox <span class="text-danger">*</span></small>
                                    <hr class="mtop5 mbot10" />
                                    <div class="row applicantion-combinations mbot20">
                                        <?php if (!empty($university_shortlisting)) {
                                            foreach ($university_shortlisting as $key => $shortlisting) { ?>

                                                <div class="university-combinations col-md-12">

                                                    <div class="col-lg-1 p-0">

                                                        <?= render_select('priority', $priority_array, array('id', 'name'), "Priority", [$shortlisting["is_primary"]], ["onchange" => "isPrimaryUniversity(this)"], [], "", "priority-selection", "", "priority") ?>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <input type="hidden" class="shortlisting_id"
                                                                value="<?= $shortlisting["id"] ?>">
                                                            <label for="study_country"> Country <small
                                                                    class="text-danger">*</small></label>

                                                            <select
                                                                class="form-control selectpicker required required-check study_country"
                                                                required-check name="study_country_<?= $key ?>"
                                                                id="study_country_<?= $key ?>" data-live-search="true"
                                                                title="Select a country" required
                                                                onchange="handleCountryChange(this,'#study_universities_<?= $key ?>')">
                                                                <?php if (!empty($shortlisting["country_id"])) { ?>
                                                                    <option selected value="<?= $shortlisting["country_id"] ?>">
                                                                        <?= $shortlisting["country_name"] ?></option>
                                                                <?php } ?>

                                                            </select>


                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label for="study_universities">Universities</label>
                                                            <select class="form-control selectpicker study_universities"
                                                                name="study_universities_<?= $key ?>"
                                                                id="study_universities_<?= $key ?>" data-live-search="true"
                                                                onchange="handleUniversityChange(this,'#study_courses_<?= $key ?>')"
                                                                title="Select a University">
                                                                <?php if (!empty($shortlisting["university_id"])) { ?>
                                                                    <option selected value="<?= $shortlisting["university_id"] ?>">
                                                                        <?= $shortlisting["university_name"] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label for="study_courses">Courses</label>
                                                            <select class="form-control selectpicker study_courses "
                                                                name="study_courses_<?= $key ?>" id="study_courses_<?= $key ?>"
                                                                data-live-search="true" title="Select a Courses">
                                                                <?php if (!empty($shortlisting["course_id"])) { ?>
                                                                    <option selected value="<?= $shortlisting["course_id"] ?>">
                                                                        <?= $shortlisting["course_name"] ?></option>
                                                                <?php } ?>

                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label for="session_intake_combination">Session Intake <small
                                                                    class="text-danger">*</small></label>
                                                            <input type="month" class="form-control session_intake_combination"
                                                                id="session_intake_combination_<?= $key ?>"
                                                                name="session_intake_combination_<?= $key ?>"
                                                                value="<?= !empty($shortlisting["session_intake"]) ? date('Y-m', strtotime($shortlisting["session_intake"])) : '' ?>"
                                                                placeholder="Select Month and Year">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-1">
                                                        <div class="form-group">
                                                            <p>&nbsp;</p>
                                                            <?php if ($key > 0) { ?>
                                                                <span class="btn btn-danger fa-fa-icons"
                                                                    onclick="removeApplication(this,<?= $shortlisting['id'] ?>)"><i
                                                                        class="fa fa-trash"></i></span>
                                                            <?php } else { ?>
                                                                <span class="btn btn-ex btn-primary fa-fa-icons"
                                                                    onclick="createNewApplication(this)"><i
                                                                        class="fa fa-plus"></i></span>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php }
                                        } else { ?>
                                            <div class="university-combinations  col-md-12">
                                                <div class="col-lg-1 p-0">

                                                    <?= render_select('priority', $priority_array, array('id', 'name'), "Priority", [], ["onchange" => "isPrimaryUniversity(this)"], [], "", "priority-selection", "", "priority") ?>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="study_country"> Country <small
                                                                class="text-danger">*</small></label>
                                                        <select
                                                            class="form-control selectpicker required required-check study_country"
                                                            required-check name="study_country" id="study_country"
                                                            data-live-search="true" title="Select a country" required
                                                            onchange="handleCountryChange(this,'#study_universities')">
                                                        </select>


                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="study_universities">Universities</label>
                                                        <select class="form-control selectpicker study_universities"
                                                            name="study_universities" id="study_universities"
                                                            data-live-search="true"
                                                            onchange="handleUniversityChange(this,'#study_courses')"
                                                            title="Select a University">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <label for="study_courses">Courses</label>
                                                        <select class="form-control selectpicker study_courses "
                                                            name="study_courses" id="study_courses" data-live-search="true"
                                                            title="Select a Courses">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="session_intake_combination">Session Intake <small
                                                                class="text-danger">*</small></label>
                                                        <input type="month" class="form-control session_intake_combination"
                                                            id="session_intake_combination"
                                                            name="session_intake_combination"
                                                            value="<?= !empty($admissionpreferences->session_intake) ? date('Y-m', strtotime($admissionpreferences->session_intake)) : '' ?>"
                                                            placeholder="Select Month and Year">
                                                    </div>
                                                </div>
                                                <div class="col-lg-1">
                                                    <div class="form-group">
                                                        <p>&nbsp;</p>

                                                        <span class="btn btn-ex btn-primary fa-fa-icons"
                                                            onclick="createNewApplication(this)"><i
                                                                class=" fa fa-plus"></i></span>

                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        } ?>




                                    </div>

                                </div>
                                <div class="row ">
                                    <div class="col-md-12 text-right  btn-save-fun">
                                        &nbsp; <button type="submit" onclick="save_admission_preferences()"
                                            class="btn btn-primary button-22">Save & Next</button>
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
                                    <h5> 10<sup>th</sup> Academic Details <small class="text-danger">*</small></h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-4 border2 border1">
                                            <div class="c1">
                                                <p>Board <?= $text_danger_mbbs ?> </p>
                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->tenth_board;
                                                echo render_select('tenth_board', $board_dropdown, array('id', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "tenth_board"); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Year of Starting <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <input type="text"
                                                    class="form-control yearpicker"
                                                    name="tenth_starting_year"
                                                    id="tenth_starting_year"
                                                    value="<?= htmlspecialchars($academicdetails->tenth_starting_year ?? '') ?>"
                                                    required
                                                    data-required-check="required-check"
                                                    placeholder="Select Year">


                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Year of Passing <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <input type="text"
                                                    name="tenth_passing_year"
                                                    id="tenth_passing_year"
                                                    class="form-control yearpicker"
                                                    value="<?= htmlspecialchars($academicdetails->tenth_passing_year ?? '') ?>"
                                                    placeholder="Select Year"
                                                    required
                                                    readonly>

                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Marking Scheme <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->tenth_marking_scheme;
                                                echo render_select('tenth_marking_scheme', $markingSchemes, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "tenth_marking_scheme"); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Percentage / CGPA <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <input class="form-control" type="float" <?= $text_danger_mbbs_required ?>
                                                    class="form-group" placeholder="Enter Marks" name="tenth_percentage"
                                                    value="<?= $academicdetails->tenth_percentage; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
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
                                                    <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                        <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle"
                                                                title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?></label>
                                                    <input type="hidden" name="doc_type[]"
                                                        value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_name[]"
                                                        value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_url[]"
                                                        value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>"
                                                        class="form-control"
                                                        accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                        <?= $required_attr ?>>
                                                    <?php
                                                    if (!empty($file_url)) {
                                                    ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                            <i class="fa fa-download  btn btn-xs btn-primary"
                                                                onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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

                                <div class=" after accadmic-education-div">
                                    <h5>After Xth Qualification <span class='text-danger'>*</span></h5>
                                    <hr>
                                    <input type="radio" name="after_x_status"
                                        <?= ($academicdetails->after_x_status == "12th" ? "checked" : '') ?>
                                        <?= empty($academicdetails->after_x_status) ? 'checked' : '' ?>
                                        value="12th">&nbsp;&nbsp;12th
                                    <input type="radio" name="after_x_status"
                                        <?= ($academicdetails->after_x_status == "Diploma" ? "checked" : '') ?>
                                        value="Diploma">&nbsp;&nbsp;Diploma
                                    <input type="radio" name="after_x_status"
                                        <?= ($academicdetails->after_x_status == "Both" ? "checked" : '') ?>
                                        value="Both">&nbsp;&nbsp;Both
                                </div>

                                <div class="row after accadmic-education-div">
                                    <div class="row qualification-div" id="twelthAcademicDetails"
                                        style="display:<?= ($academicdetails->after_x_status == '12th' || $academicdetails->after_x_status == 'Both' || empty($academicdetails->after_x_status)) ? 'block' : 'none' ?>">
                                        <h5>12<sup>th</sup> Academic Details <small class="text-danger">*</small></h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-4 border2 border1">
                                                <div class="c1">
                                                    <p>University / Broad <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->twelth_board;
                                                    echo render_select('twelth_board', $board_dropdown, array('id', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "twelth_board"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Year of Starting <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input type="text"
                                                        name="twelth_starting_year"
                                                        id="twelth_starting_year"
                                                        class="form-control yearpicker"
                                                        value="<?= htmlspecialchars($academicdetails->twelth_starting_year ?? '') ?>"
                                                        placeholder="Select Year"
                                                        required
                                                        readonly>


                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Year of Passing <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input type="text"
                                                        name="twelth_passing_year"
                                                        id="twelth_passing_year"
                                                        class="form-control yearpicker"
                                                        value="<?= htmlspecialchars($academicdetails->twelth_passing_year ?? '') ?>"
                                                        placeholder="Select Year"
                                                        required
                                                        readonly>


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
                                        </div>
                                        <div class="row">
                                            <div class="twelth_result_status_div"
                                                style="display:<?= ($academicdetails->twelth_result_status == 'Awaited') ? 'none' : '' ?>">
                                                <div class="col-lg-2 border2 border1 " id="twelth_marking_scheme_div">
                                                    <div class="c1">
                                                        <p>Marking Scheme <?= $text_danger_mbbs ?></p>
                                                    </div>
                                                    <div class="c2">
                                                        <?php
                                                        $selected = [$academicdetails->twelth_marking_scheme];
                                                        // Correcting the attribute array handling
                                                        $attributes = [];
                                                        $attributes["required-check"] = "required-check";

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
                                                        <input class="form-control" <?= $text_danger_mbbs_required ?>
                                                            type="float" placeholder="Enter Marks"
                                                            name="twelth_percentage" id="twelth_percentage"
                                                            value="<?= $academicdetails->twelth_percentage; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 border2 border1">
                                                    <div class="c1">
                                                        <p>ENG</p>
                                                    </div>
                                                    <div class="c2">
                                                        <input class="form-control" <?= $text_danger_mbbs_required ?>
                                                            type="float" placeholder="ENG Marks" name="pcb" id="pcb"
                                                            value="<?= $academicdetails->pcb; ?>">
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
                                                    <div class="col-lg-3 border2 media-files  ">
                                                        <div class="form-group">
                                                            <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                                <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                                <?php if (!empty($info)) : ?>
                                                                    &nbsp;<i class="fa fa-info-circle"
                                                                        title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                                <?php endif; ?></label>
                                                            <input type="hidden" name="doc_type[]"
                                                                value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="doc_name[]"
                                                                value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="doc_url[]"
                                                                value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="file" name="files[<?= $doc_id ?>]"
                                                                value="<?= $file_url ?>" class="form-control"
                                                                accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                                <?= $required_attr ?>>
                                                            <?php
                                                            if (!empty($file_url)) {
                                                            ?>
                                                                <div class="margin-top">
                                                                    <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                        onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                    <i class="fa fa-download  btn btn-xs btn-primary"
                                                                        onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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
                                </div>

                                <div class="accadmic-education-div" id="diplomaAcademicDetails"
                                    style="display:<?= ($academicdetails->after_x_status == 'Diploma' || $academicdetails->after_x_status == 'Both') ? 'block' : 'none' ?>">
                                    <h5>Diploma Academic Details <span class="text-danger">*</span></h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 border2 border1">
                                            <div class="c1">
                                                <p>Institute Name <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <input class="form-control" required type="text" class="form-group"
                                                    placeholder="Enter Institute Name" name="diploma_institute"
                                                    value="<?= $academicdetails->diploma_institute; ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>University <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">


                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->diploma_board;
                                                echo render_select('diploma_board', $diploma_board, array('id', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "diploma_board"); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Year of Starting <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <!-- <input class="form-control" type="text" placeholder="Enter Passing Year"  name="diploma_passing_year" value="<?= $academicdetails->diploma_starting_year; ?>"> -->
                                                <input type="text"
                                                    name="diploma_starting_year"
                                                    id="diploma_starting_year"
                                                    class="form-control yearpicker"
                                                    value="<?= htmlspecialchars($academicdetails->diploma_starting_year ?? '') ?>"
                                                    placeholder="Select Year"
                                                    required
                                                    readonly>

                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Year of Passing <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <!-- <input class="form-control" type="text" placeholder="Enter Passing Year"  name="diploma_passing_year" value="<?= $academicdetails->diploma_passing_year; ?>"> -->
                                                <input type="text"
                                                    name="diploma_passing_year"
                                                    id="diploma_passing_year"
                                                    class="form-control yearpicker"
                                                    value="<?= htmlspecialchars($academicdetails->diploma_passing_year ?? '') ?>"
                                                    placeholder="Select Year"
                                                    required
                                                    readonly>

                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Result Status <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->diploma_result_status;
                                                echo render_select('diploma_result_status', $resultStatus, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "diploma_result_status"); ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 border2 border1 p-0">
                                            <div class="c1">
                                                <p>Backlogs <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <input class="form-control" required type="number" class="form-group"
                                                    placeholder="Backlock" name="diploma_backlock"
                                                    id="diploma_backlock"
                                                    value="<?= $academicdetails->diploma_backlock; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row result-change-hide" style="display:<?= ($academicdetails->diploma_result_status == 'Awaited') ? 'none' : '' ?>">
                                        <div class="col-lg-2 border2 border1" id="diploma_marking_scheme_div">
                                            <div class="c1">
                                                <p>Marking Scheme <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <?php
                                                $selected = [$academicdetails->diploma_marking_scheme];
                                                // Correcting the attribute array handling
                                                $attributes = [];

                                                $attributes["required-check"] = "required-check";
                                                $attributes["required"] = "required";

                                                echo render_select(
                                                    'diploma_marking_scheme',
                                                    $markingSchemes,
                                                    ['name', 'name'],
                                                    "",
                                                    $selected,
                                                    $attributes,
                                                    [],
                                                    "",
                                                    "",
                                                    "",
                                                    "diploma_marking_scheme"
                                                );
                                                ?>

                                            </div>
                                        </div>
                                        <div class="col-lg-2 border2 border1">
                                            <div class="c1">
                                                <p>Percentage / CGPA <?= $text_danger_mbbs ?></p>
                                            </div>
                                            <div class="c2">
                                                <input class="form-control" required type="text" class="form-group"
                                                    placeholder="Enter Marks" name="diploma_percentage"
                                                    id="diploma_percentage"
                                                    value="<?= $academicdetails->diploma_percentage; ?>">

                                            </div>
                                        </div>
                                        <?php
                                        foreach ($profile_section["diploma_stage"] as $s_stage) {
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
                                            <div class="col-lg-3 media-files border2 border1 ">
                                                <div class="form-group">
                                                    <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                        <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle"
                                                                title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?></label>
                                                    <input type="hidden" name="doc_type[]"
                                                        value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_name[]"
                                                        value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="doc_url[]"
                                                        value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="file" name="files[<?= $doc_id ?>]" value="<?= $file_url ?>"
                                                        class="form-control"
                                                        accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                        <?= $required_attr ?>>
                                                    <?php
                                                    if (!empty($file_url)) {
                                                    ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                            <i class="fa fa-download  btn btn-xs btn-primary"
                                                                onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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

                                <div id="Qualification-section-div" style="display:none;">
                                    <div class="after accadmic-education-div">
                                        <h4>Qualification <span class="text-danger">*</span></h4>
                                        <hr>
                                        <input type="radio" name="after_xx_status"
                                            <?= ($academicdetails->after_xx_status == "Graduation" ? "checked" : '') ?>
                                            <?= empty($academicdetails->after_xx_status) ? 'checked' : '' ?>
                                            value="Graduation">&nbsp;&nbsp;Graduation

                                        <input type="radio" name="after_xx_status"
                                            <?= ($academicdetails->after_xx_status == "Both" ? "checked" : '') ?>
                                            value="Both">&nbsp;&nbsp;Both (Post Graduation)
                                    </div>
                                    <!-- end diploma details-->

                                    <!-- Under Graduate details-->
                                    <div class=" accadmic-education-div" id="graduationAcademicDetails"
                                        style="display:<?= ($academicdetails->after_xx_status == 'Graduation' || $academicdetails->after_xx_status == 'Both' || empty($academicdetails->after_xx_status)) ? 'block' : 'none' ?>">
                                        <h5>Graduation Details <span class="text-danger">*</span></h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Course Name <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->graduation_course;
                                                    echo render_select('graduation_course', $course_list_ug, array('id', 'course_name'), "", $selected, ["required" => "required", "required-check" => "required-check", "data-select" => !empty($academicdetails->graduation_course) ? $academicdetails->graduation_course : ''], [], "", "coursesLoads", "", "graduation_course"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 border2 border1">
                                                <div class="c1">
                                                    <p>University <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">

                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->graduation_board;
                                                    echo render_select('graduation_board', $universities_list, array('id', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check", "data-select" => !empty($academicdetails->graduation_board) ? $academicdetails->graduation_board : ''], [], "", "universityLoad", "", "graduation_board"); ?>

                                                    </dsiv>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Year of Starting <?= $text_danger_mbbs ?> </p>
                                                </div>
                                                <div class="c2">
                                                    <input type="text"
                                                        name="graduation_starting_year"
                                                        id="graduation_starting_year"
                                                        class="form-control yearpicker"
                                                        value="<?= htmlspecialchars($academicdetails->graduation_starting_year ?? '') ?>"
                                                        placeholder="Select Year"
                                                        required
                                                        readonly>


                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Year of Passing <?= $text_danger_mbbs ?> </p>
                                                </div>
                                                <div class="c2">
                                                    <input type="text"
                                                        name="graduation_passing_year"
                                                        id="graduation_passing_year"
                                                        class="form-control yearpicker"
                                                        value="<?= htmlspecialchars($academicdetails->graduation_passing_year ?? '') ?>"
                                                        placeholder="Select Year"
                                                        required
                                                        readonly>


                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Result Status <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->graduation_result_status;
                                                    echo render_select('graduation_result_status', $resultStatus, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "graduation_result_status"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-1 border2 border1 p-0">
                                                <div class="c1">
                                                    <p>Backlogs <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input class="form-control" required type="number" class="form-group"
                                                        placeholder="Backlock" name="graduation_backlock"
                                                        id="graduation_backlock"
                                                        value="<?= $academicdetails->graduation_backlock; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row result-change-hide" style="display:<?= ($academicdetails->graduation_result_status == 'Awaited') ? 'none' : '' ?>">
                                            <div class="col-lg-2 border2 border1" id="graduation_marking_scheme_div">
                                                <div class="c1">
                                                    <p>Marking Scheme <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->graduation_marking_scheme;
                                                    echo render_select('graduation_marking_scheme', $markingSchemes, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "graduation_marking_scheme"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Percentage / CGPA <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input class="form-control" type="text" class="form-group"
                                                        placeholder="Enter Marks" name="graduation_percentage"
                                                        id="graduation_percentage" required
                                                        value="<?= $academicdetails->graduation_percentage; ?>">
                                                </div>
                                            </div>

                                            <?php
                                            foreach ($profile_section["graduation_stage"] as $s_stage) {
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
                                                <div class="col-lg-3 media-files  ">
                                                    <div class="form-group">
                                                        <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                            <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                            <?php if (!empty($info)) : ?>
                                                                &nbsp;<i class="fa fa-info-circle"
                                                                    title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                            <?php endif; ?></label>
                                                        <input type="hidden" name="doc_type[]"
                                                            value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]"
                                                            value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]"
                                                            value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="file" name="files[<?= $doc_id ?>]"
                                                            value="<?= $file_url ?>" class="form-control"
                                                            accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                            <?= $required_attr ?>>
                                                        <?php
                                                        if (!empty($file_url)) {
                                                        ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                    onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                <i class="fa fa-download  btn btn-xs btn-primary"
                                                                    onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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
                                    <!-- end ug details-->

                                    <div class="accadmic-education-div" id="post_graduationAcademicDetails"
                                        style="display:<?= ($academicdetails->after_xx_status == 'Post Graduation' || $academicdetails->after_xx_status == 'Both') ? 'block' : 'none' ?>">
                                        <h5>Post Graduation Details <span class="text-danger">*</span></h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Course Name <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <!-- <div class="c2">
                                                    <input class="form-control" required type="text" class="form-group"
                                                        placeholder="Enter Course Name" name="post_graduation_course"
                                                        value="<?= $academicdetails->post_graduation_course; ?>" ?>
                                                </div> -->

                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->post_graduation_course;
                                                echo render_select('post_graduation_course', $course_list_pg, array('id', 'course_name'), "", $selected, ["required" => "required", "required-check" => "required-check", "data-select" => !empty($academicdetails->post_graduation_course) ? $academicdetails->post_graduation_course : ''], [], "", "coursesLoadsPG", "", "post_graduation_course"); ?>

                                            </div>
                                            <div class="col-lg-3 border2 border1">
                                                <div class="c1">
                                                    <p>University <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->post_graduation_board;
                                                    echo render_select('post_graduation_board', $universities_list, array('id', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check", "data-select" =>  !empty($academicdetails->post_graduation_board) ? $academicdetails->post_graduation_board : ''], [], "", "universityLoad", "", "post_graduation_board"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Year of Starting <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input type="text"
                                                        name="post_graduation_starting_year"
                                                        id="post_graduation_starting_year"
                                                        class="form-control yearpicker"
                                                        value="<?= htmlspecialchars($academicdetails->post_graduation_starting_year ?? '') ?>"
                                                        placeholder="Select Year"
                                                        required
                                                        readonly>


                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Year of Passing <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input type="text"
                                                        name="post_graduation_passing_year"
                                                        id="post_graduation_passing_year"
                                                        class="form-control yearpicker"
                                                        value="<?= htmlspecialchars($academicdetails->post_graduation_passing_year ?? '') ?>"
                                                        placeholder="Select Year"
                                                        required
                                                        readonly>


                                                </div>
                                            </div>

                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Result Status <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <?php
                                                $selected = [];
                                                $selected[] = $academicdetails->post_graduation_result_status;
                                                echo render_select('post_graduation_result_status', $resultStatus, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "post_graduation_result_status"); ?>
                                            </div>
                                            <div class="col-lg-1 border2 border1 p-0">
                                                <div class="c1">
                                                    <p>Backlogs <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input class="form-control" required type="number" class="form-group"
                                                        placeholder="Backlock" name="post_graduation_backlock"
                                                        id="post_graduation_backlock"
                                                        value="<?= $academicdetails->post_graduation_backlock; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row result-change-hide" style="display:<?= ($academicdetails->graduation_result_status == 'Awaited') ? 'none' : '' ?>">
                                            <div class="col-lg-2 border2 border1"
                                                id="post_graduation_marking_scheme_div">
                                                <div class="c1">
                                                    <p>Marking Scheme <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <?php
                                                    $selected = [];
                                                    $selected[] = $academicdetails->post_graduation_marking_scheme;
                                                    echo render_select('post_graduation_marking_scheme', $markingSchemes, array('name', 'name'), "", $selected, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "post_graduation_marking_scheme"); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 border2 border1">
                                                <div class="c1">
                                                    <p>Percentage / CGPA <?= $text_danger_mbbs ?></p>
                                                </div>
                                                <div class="c2">
                                                    <input class="form-control" type="text" class="form-group"
                                                        placeholder="Enter Marks" name="post_graduation_percentage"
                                                        id="post_graduation_percentage"
                                                        value="<?= $academicdetails->post_graduation_percentage; ?>"
                                                        required>
                                                </div>
                                            </div>
                                            <?php
                                            foreach ($profile_section["post_graduation_stage"] as $s_stage) {
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
                                                <div class="col-lg-3 media-files  ">
                                                    <div class="form-group">
                                                        <label for="exampleInputMobileNumber"><?= $s_stage["name"] ?>
                                                            <?= $mandatry_text  . "  (" . $s_stage["file_type"] . ")" ?>
                                                            <?php if (!empty($info)) : ?>
                                                                &nbsp;<i class="fa fa-info-circle"
                                                                    title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                            <?php endif; ?></label>
                                                        <input type="hidden" name="doc_type[]"
                                                            value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]"
                                                            value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]"
                                                            value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="file" name="files[<?= $doc_id ?>]"
                                                            value="<?= $file_url ?>" class="form-control"
                                                            accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
                                                            <?= $required_attr ?>>
                                                        <?php
                                                        if (!empty($file_url)) {
                                                        ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                    onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                <i class="fa fa-download  btn btn-xs btn-primary"
                                                                    onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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

                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="work_status" value="1" name="work_status" onclick="changework_status(this)" <?= !empty($academicdetails->work_status) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="work_status">Work Experience</label>
                                    </div>

                                    <!-- Work Experience Container -->
                                    <div id="work-div" class="col-md-12 <?= !empty($academicdetails->work_status) ? '' : 'hide' ?>">
                                        <div id="work-experience-container" class="w-100">
                                            <?php if (!empty($getWorkExperience)) { ?>
                                                <?php foreach ($getWorkExperience as $key => $work) { ?>
                                                    <div class="row work-exp-div mb-3">
                                                        <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                                                            <label for="currently_working_<?= $key ?>" class="form-label">Working</label>
                                                            <input type="checkbox"
                                                                name="currently_working[]"
                                                                id="currently_working_<?= $key ?>"
                                                                onchange="currently_working(this)"
                                                                value="1"
                                                                class="mt-1"
                                                                <?= !empty($work['currently_working']) && $work['currently_working'] == 1 ? 'checked' : '' ?>>
                                                        </div>

                                                        <div class="form-group col-md-1">
                                                            <label for="work_experience_<?= $key ?>">Years <span class="text-danger">*</span></label>
                                                            <input type="number"
                                                                class="form-control"
                                                                name="work_experience[]"
                                                                id="work_experience_<?= $key ?>"
                                                                value="<?= htmlspecialchars($work['year'] ?? '') ?>"
                                                                required>
                                                        </div>

                                                        <div class="form-group col-md-9">
                                                            <label for="work_profile_<?= $key ?>">Role/Profile <span class="text-danger">*</span></label>
                                                            <textarea class="form-control"
                                                                name="work_profile[]"
                                                                id="work_profile_<?= $key ?>"
                                                                rows="3"
                                                                required><?= htmlspecialchars($work['remark'] ?? '') ?></textarea>
                                                        </div>
                                                        <?php if ($key == 0) { ?>
                                                            <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-primary" onclick="createNewWorkExperience()">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        <?php } else { ?>
                                                            <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-danger" onclick="removeWorkExperience(this)">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        <?php } ?>

                                                    </div>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <div class="row work-exp-div mb-3">
                                                    <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                                                        <label for="currently_working_0" class="form-label">Working</label>
                                                        <input type="checkbox"
                                                            name="currently_working[]"
                                                            id="currently_working_0"
                                                            onchange="currently_working(this)"
                                                            value="1"
                                                            class="mt-1"
                                                            <?= !empty($academicdetails->currently_working) ? 'checked' : '' ?>>
                                                    </div>

                                                    <div class="form-group col-md-1">
                                                        <label for="work_experience_0">Years <span class="text-danger">*</span></label>
                                                        <input type="number"
                                                            class="form-control"
                                                            name="work_experience[]"
                                                            id="work_experience_0"
                                                            value="<?= htmlspecialchars($academicdetails->work_experience ?? '') ?>"
                                                            required>
                                                    </div>

                                                    <div class="form-group col-md-9">
                                                        <label for="work_profile_0">Role/Profile <span class="text-danger">*</span></label>
                                                        <textarea class="form-control"
                                                            name="work_profile[]"
                                                            id="work_profile_0"
                                                            rows="3"
                                                            required><?= htmlspecialchars($academicdetails->work_profile ?? '') ?></textarea>
                                                    </div>

                                                    <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-primary" onclick="createNewWorkExperience()">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-12" style="padding-top: 30px; padding-bottom: 20px;">
                                    <!-- <label>
                                        ELT Status &nbsp;
                                        <input type="checkbox" value="1" onclick="changeELS_status(this)" name="elt_status" <?= !empty($academicdetails->elt_status) ? 'checked' : '' ?>>
                                    </label> -->

                                    <h4 id="entrance-exam-div-title">Entrance Exams <span class="text-danger">*</span></h4>
                                    <hr>

                                    <div id="entrance-exam-div">
                                        <?php if (!empty($get_entrance_exam)) { ?>
                                            <?php foreach ($get_entrance_exam as $key => $entrance) {
                                                $show_entrance_div = 0;
                                                $file_url = $entrance["file"] ?? '';
                                                $required_attr = !empty($file_url) ? '' : 'required required-check';
                                            ?>
                                                <div class="entrance-exams row mb-4" id="entrance-exam-<?= $key ?>">
                                                    <!-- Hidden ID -->
                                                    <input type="hidden" class="entrance_id" name="entrance_id[<?= $key ?>]" value="<?= htmlspecialchars($entrance["id"], ENT_QUOTES, 'UTF-8') ?>">

                                                    <!-- Exam Status -->
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label for="entrance_exams_status_<?= $key ?>">Exam Status <small class="text-danger">*</small></label>
                                                            <select
                                                                name="entrance_exams_status[<?= $key ?>]"
                                                                id="entrance_exams_status_<?= $key ?>"
                                                                data="entrance_exams_status_<?= $key ?>"
                                                                class="form-control entrance_exams_status"
                                                                required
                                                                required-check
                                                                onchange="changeEntranceStatus(this)">
                                                                <option value="">Select...</option>
                                                                <?php foreach ($get_entrance_exams_status as $status):
                                                                    if (!empty($status['selected']) && $status['id'] == $entrance['status']) {
                                                                        $show_entrance_div = 1;
                                                                    }
                                                                ?>
                                                                    <option
                                                                        data-selected="<?= htmlspecialchars($status['selected'], ENT_QUOTES, 'UTF-8') ?>"
                                                                        value="<?= htmlspecialchars($status['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                                        <?= $status['id'] == $entrance['status'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($status['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Dependent Fields -->
                                                    <div class="entrance-exams-status-active row col-lg-9 ms-0 ps-0 <?= $show_entrance_div == 1 ? '' : 'hide' ?>">
                                                        <!-- Exam Name -->
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <label for="entrance_exams_<?= $key ?>">Exam Name <small class="text-danger">*</small></label>
                                                                <?= render_select(
                                                                    "entrance_exams[$key]",
                                                                    $get_entrance_exams_list,
                                                                    ['id', 'name'],
                                                                    '',
                                                                    [$entrance["exam_id"]],
                                                                    [
                                                                        'required' => 'required',
                                                                        'required-check' => 'required-check',
                                                                        'onchange' => 'changeEntranceExam(this)',
                                                                        'id' => "entrance_exams_$key"
                                                                    ],
                                                                    [],
                                                                    '',
                                                                    'entrance_exams',
                                                                    '',
                                                                    'entrance_exams'
                                                                ) ?>
                                                            </div>
                                                        </div>

                                                        <!-- Exam Date -->
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <label for="entrance_date_<?= $key ?>">Exam Date <small class="text-danger">*</small></label>
                                                                <input type="date"
                                                                    class="form-control entrance_date"
                                                                    name="entrance_date[<?= $key ?>]"
                                                                    id="entrance_date_<?= $key ?>"
                                                                    value="<?= htmlspecialchars($entrance["date"], ENT_QUOTES, 'UTF-8') ?>"
                                                                    required
                                                                    required-check>
                                                            </div>
                                                        </div>

                                                        <!-- Marks -->
                                                        <div class="col-lg-2">
                                                            <div class="form-group">
                                                                <label for="entrance_marks_<?= $key ?>">Marks <small class="text-danger">*</small></label>
                                                                <input type="number"
                                                                    class="form-control entrance_marks"
                                                                    name="entrance_marks[<?= $key ?>]"
                                                                    id="entrance_marks_<?= $key ?>"
                                                                    value="<?= htmlspecialchars($entrance["marks"], ENT_QUOTES, 'UTF-8') ?>"
                                                                    required
                                                                    required-check>
                                                            </div>
                                                        </div>

                                                        <!-- Marksheet Upload -->
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <label for="entrance_file_<?= $key ?>">Marksheet <small class="text-danger">*</small></label>
                                                                <input type="file"
                                                                    class="form-control"
                                                                    name="entrance_file[<?= $key ?>]"
                                                                    id="entrance_file_<?= $key ?>"
                                                                    data-fileurl="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>"
                                                                    <?= $required_attr ?>
                                                                    accept=".pdf,.jpg,.jpeg,.png">
                                                                <?php if (!empty($file_url)) { ?>
                                                                    <div class="mt-2 margin-top">
                                                                        <button type="button" class="btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>')">
                                                                            <i class="fa fa-eye"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url) ?>', '_blank')">
                                                                            <i class="fa fa-download"></i>
                                                                        </button>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Add / Remove Buttons -->
                                                    <div class="col-lg-1 d-flex align-items-end">
                                                        <div class="form-group">
                                                            <p></p>
                                                            <?php if ($key > 0) { ?>
                                                                <button type="button" class="btn btn-danger" onclick="removeEntrance(this, <?= $entrance['id'] ?>)">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            <?php } else { ?>
                                                                <button type="button" class="btn btn-primary" onclick="createNewEntrance()">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            <?php } ?>
                                                        </div>
                                                    </div>

                                                    <!-- Optional Entrance Score Section -->
                                                    <?php if (
                                                        !empty($get_entrance_exams_list[$entrance['exam_id']]['academic_type']) &&
                                                        $get_entrance_exams_list[$entrance['exam_id']]['academic_type'] > 0
                                                    ): ?>
                                                        <div class="row col-md-12 mt-3 entrance-score-div">
                                                            <?php foreach ($get_entrance_exam_scrore as $score): ?>
                                                                <div class="col-md-3 form-group">
                                                                    <label><?= htmlspecialchars($score["name"]) ?> <span class="text-danger">*</span></label>
                                                                    <input
                                                                        type="text"
                                                                        class="form-control entrance-score-input"
                                                                        name="<?= bin2hex(random_bytes(16)) ?>"
                                                                        data-name="<?= htmlspecialchars($score["name"]) ?>"
                                                                        data-id="<?= $score["id"] ?>"
                                                                        value="<?= htmlspecialchars($score["value"], ENT_QUOTES, 'UTF-8') ?>"
                                                                        required
                                                                        required-check>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>



                                            <?php } ?>
                                        <?php } else { ?>
                                            <div class="entrance-exams row mb-3" id="entrance-exam-0">

                                                <!-- Exam Status -->
                                                <div class="col-lg-2">
                                                    <div class="form-group">
                                                        <label for="entrance_exams_status_0">Exam Status <small class="text-danger">*</small></label>
                                                        <select
                                                            name="entrance_exams_status[0]"
                                                            id="entrance_exams_status_0"
                                                            data="entrance_exams_status_0"
                                                            class="form-control entrance_exams_status"
                                                            required
                                                            required-check
                                                            onchange="changeEntranceStatus(this)">
                                                            <option value="">Select...</option>
                                                            <?php foreach ($get_entrance_exams_status as $status): ?>
                                                                <option data-selected="<?= htmlspecialchars($status['selected'], ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($status['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                                    <?= htmlspecialchars($status['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    </div>
                                                </div>

                                                <!-- Dependent Fields (hidden until status is selected) -->
                                                <div class="entrance-exams-status-active row col-lg-9 ms-0 ps-0 hide">
                                                    <!-- Exam Name -->
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label for="entrance_exams_0">Exam Name <small class="text-danger">*</small></label>
                                                            <?= render_select(
                                                                'entrance_exams[0]',
                                                                $get_entrance_exams_list,
                                                                ['id', 'name'],
                                                                '',
                                                                [],
                                                                [
                                                                    'required' => 'required',
                                                                    'required-check' => 'required-check',
                                                                    'onchange' => 'changeEntranceExam(this)',
                                                                    'id' => 'entrance_exams_0'
                                                                ],
                                                                [],
                                                                '',
                                                                'entrance_exams',
                                                                '',
                                                                'entrance_exams'
                                                            ) ?>
                                                        </div>
                                                    </div>

                                                    <!-- Exam Date -->
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label for="entrance_date_0">Exam Date <small class="text-danger">*</small></label>
                                                            <input type="date" class="form-control entrance_date" name="entrance_date[0]" id="entrance_date_0" required required-check>
                                                        </div>
                                                    </div>

                                                    <!-- Marks -->
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label for="entrance_marks_0">Marks <small class="text-danger">*</small></label>
                                                            <input type="number" class="form-control entrance_marks" name="entrance_marks[0]" id="entrance_marks_0" required required-check>
                                                        </div>
                                                    </div>

                                                    <!-- Marksheet File -->
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label for="entrance_file_0">Marksheet <small class="text-danger">*</small></label>
                                                            <input type="file" class="form-control" name="entrance_file[0]" id="entrance_file_0" required required-check accept=".pdf,.jpg,.jpeg,.png">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Add Button -->
                                                <div class="col-lg-1">
                                                    <div class="form-group">
                                                        <p>&nbsp;</p>
                                                        <button type="button" class="btn btn-primary" onclick="createNewEntrance()">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php } ?>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="btn-save-fun">
                        <div class="col-md-12 text-right">
                            <button type="submit" onclick="save_admission_details(1)"
                                class="btn btn-primary button-22 ">Save</button> &nbsp;
                            &nbsp; <button type="submit" onclick="save_admission_details()"
                                class="btn btn-primary button-22">Save & Next</button> &nbsp;
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
                                                        <input type="hidden" name="doc_type[]"
                                                            value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]"
                                                            value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]"
                                                            value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">


                                                        <?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') . ' ' . $mandatry_text  . "  (" . $doc_files["file_type"] . ")" ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle"
                                                                title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
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
                                                        <input type="file" <?= !empty($doc_files["disabled"] == 1) ? 'disabled' : '' ?> name="files[<?= $doc_id ?>]" value="<?= $file_url ?>" class="form-control <?= !empty($doc_files["disabled"] == 1) ? 'disabledd' : '' ?>" accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>" <?= $required_attr ?>>

                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        if (!empty($file_url)) {
                                                        ?>
                                                            <div class="margin-top">
                                                                <i class="fa fa-eye  btn btn-xs btn-primary"
                                                                    onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                <i class="fa fa-download  btn btn-xs btn-primary"
                                                                    onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
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

                                <hr>
                                <div class="row">
                                    <?php if (!empty($offerLetersDownload)) { ?>
                                        <div class="col-md-12">
                                            <h4>Offer Letters</h4>
                                            <table class="table table-bordered table-striped">
                                                <thead class="thead-dark">
                                                    <tr></tr>
                                                    <th>S.No</th>
                                                    <th>Country Name</th>
                                                    <th>University Name</th>
                                                    <th>Course Name</th>
                                                    <th> <a href="javascript:void(0);" onclick="multiple_document_download('offer-letter-download','<?= sanitizeFileName($basicdetails->first_name . '_' . $basicdetails->last_name . '_Offer_Letters.zip') ?>'); return false;"
                                                            class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($offerLetersDownload)) : ?>
                                                        <?php foreach ($offerLetersDownload as $key => $offerLetter) : ?>
                                                            <tr>
                                                                <td>
                                                                    <?= ($key + 1) ?>
                                                                    <div class="offer-letter-download">
                                                                        <input type="hidden" class="file-path"
                                                                            value="<?= base_url(htmlspecialchars($offerLetter['file'], ENT_QUOTES, 'UTF-8')) ?>">
                                                                        <input type="hidden" class="file-name" value="offer_letter_<?= str_replace(' ', '_', htmlspecialchars($offerLetter['university_name'] ?? '', ENT_QUOTES, 'UTF-8')) ?>_<?= (int)($key + 1) ?>">


                                                                    </div>
                                                                </td>
                                                                <td><?= htmlspecialchars($offerLetter['country_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($offerLetter['university_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($offerLetter['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td class="text-center">
                                                                    <a href="javascript:void(0);" onclick="show_media_files('<?= base_url($offerLetter['file']) ?>');"
                                                                        class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>
                                                                    <a href="javascript:void(0);" onclick="download_media_files(`<?= base_url($offerLetter['file']) ?>`, '_blank'); return false;"
                                                                        class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No Offer Letters Available</td>
                                                        </tr>
                                                    <?php endif; ?>
                                            </table>

                                        </div>
                                    <?php } ?>

                                    <?php if (!empty($PreDepositeDownload)) { ?>
                                        <div class="col-md-12">
                                            <h4>Pre-deposite</h4>
                                            <table class="table table-bordered table-striped">
                                                <thead class="thead-dark">
                                                    <tr></tr>
                                                    <th>S.No</th>
                                                    <th>Country Name</th>
                                                    <th>University Name</th>
                                                    <th>Course Name</th>
                                                    <th><a href="javascript:void(0);" onclick="multiple_document_download('pre-deposite-download','<?= sanitizeFileName($basicdetails->first_name . '_' . $basicdetails->last_name . '_Pre_Deposite.zip') ?>'); return false;"
                                                            class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($PreDepositeDownload)) : ?>
                                                        <?php foreach ($PreDepositeDownload as $key => $preDeposite) : ?>
                                                            <tr>
                                                                <td><?= ($key + 1) ?>
                                                                    <div class="pre-deposite-download">
                                                                        <input type="hidden" class="file-path"
                                                                            value="<?= base_url(htmlspecialchars($preDeposite['file'], ENT_QUOTES, 'UTF-8')) ?>">
                                                                        <input type="hidden" class="file-name" value="pre_deposit_<?= str_replace(' ', '_', htmlspecialchars($preDeposite['university_name'] ?? '', ENT_QUOTES, 'UTF-8')) ?>_<?= (int)($key + 1) ?>">


                                                                    </div>
                                                                </td>
                                                                <td><?= htmlspecialchars($preDeposite['country_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($preDeposite['university_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($preDeposite['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td class="text-center">
                                                                    <a href="javascript:void(0);" onclick="show_media_files('<?= base_url($preDeposite['file']) ?>');"
                                                                        class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>
                                                                    <a href="javascript:void(0);" onclick="download_media_files(`<?= base_url($preDeposite['file']) ?>`, '_blank'); return false;"
                                                                        class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No Offer Letters Available</td>
                                                        </tr>
                                                    <?php endif; ?>
                                            </table>

                                        </div>
                                    <?php } ?>

                                </div>


                            </div>
                            <div class="row ">
                                <div class="col-md-12">
                                    <button type="submit" onclick="save_documents()"
                                        class="btn btn-primary button-22 pull-right hide-btn btn-save-funn">Save
                                        changes</button>
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

                                $get_clients_fees = get_clients_fees_details($lead_type_status, $client_id, REGISTRATION_AMOUNT_STUDY_ID);
                                $registration_amount = !empty($get_clients_fees[0]) ? $get_clients_fees[0] : [];
                                ?>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <label class="form-check-label">Registration Amount Cash Deposite
                                            <input type="checkbox" <?= !empty($final_sumbit) ? 'disabled' : '' ?>
                                                value="<?= !empty($client->registration_slip_cash_status) && $client->registration_slip_cash_status == 1 ? 1 : 0 ?>"
                                                class="form-check-input <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>"
                                                onclick="check_registration_cash_status(this,'hide-show-regi')"
                                                <?= !empty($client->registration_slip_cash_status) && $client->registration_slip_cash_status == 1 ? 'checked' : '' ?>
                                                name="registration_slip_cash_status"
                                                <?= !empty($client->registration_slip_cash_status && $client->registration_slip_cash_status == 1) ? 'checked' : '' ?>>

                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Date of payment <small
                                                    class="text-danger">*</small></label>
                                            <input <?= $text_danger_mbbs_required ?>
                                                <?= !empty($final_sumbit) ? 'disabled' : '' ?>
                                                class="form-control <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>"
                                                type="date" name="date_of_payment"
                                                value="<?= $client->date_of_payment ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label for="registrationAmount" class="form-label">
                                                Registration Amount <small class="text-danger">*</small>
                                            </label>
                                            <div class="f-flex">
                                                <div class="row align-items-left mb-3">
                                                    <div class="col-md-1">
                                                        <label class="form-label" style="font-size: 20px;">
                                                            <?= isset($registration_amount["symbol"]) ? htmlspecialchars($registration_amount["symbol"]) : '₹' ?>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-10" style="padding-left:3px;">
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="registrationAmount"
                                                            name="registration_amount"
                                                            value="<?= htmlspecialchars($registration_amount["amount"]) ?>"
                                                            data-currency_id="3"
                                                            data-id="<?= isset($registration_amount["id"]) ? htmlspecialchars($registration_amount["id"]) : REGISTRATION_AMOUNT_STUDY_ID ?>"
                                                            <?= $text_danger_mbbs_required ?>>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>



                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Payment received from <small
                                                    class="text-danger">*</small></label>
                                            <input
                                                class="form-control <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>"
                                                <?= !empty($final_sumbit) ? 'disabled' : '' ?> type="text"
                                                name="payment_recevied_from" <?= $text_danger_mbbs_required ?>
                                                value="<?= !empty($client->payment_recevied_from) ? $client->payment_recevied_from : '' ?>">
                                        </div>
                                    </div>



                                    <div class="col-lg-3 hide-show-regi"
                                        style="display: <?= !empty($client->registration_slip_cash_status) ? 'none' : 'block' ?>;">
                                        <div class="form-group">
                                            <label for="exampleInputMiddleName">Registration Proof <small
                                                    class="text-danger">*</small></label>
                                            <input <?= !empty($final_sumbit) ? 'disabled' : '' ?>
                                                <?= !empty($client->registration_slip) ? '' : $text_danger_mbbs_required ?>
                                                class="form-control <?= !empty($final_sumbit) ? 'disabled-form-welcome' : '' ?>"
                                                type="file" accept=".pdf, image/*" name="registration_slip" value="">
                                            <?php
                                            if (!empty($client->registration_slip)) {
                                            ?>
                                                <div class="margin-top">
                                                    <i onclick="show_media_files('<?= base_url($client->registration_slip) ?>');"
                                                        class="fa fa-eye btn btn-xs btn-primary"></i>
                                                    <i class="fa fa-download  btn btn-xs btn-primary"
                                                        onclick="download_media_files(`<?= base_url($client->registration_slip) ?>`, '_blank');"></i>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row">
                                    <div class="col-md-12 ">
                                        <button type="submit" onclick="save_welcome_info()"
                                            class="btn btn-primary button-22 pull-right hide-btn btn-save-funn">Save & Next</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <?php if (isset($client)) { ?>
                <div role="tabpanel" class="tab-pane hide" id="customer_admins">
                    <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit') && (isset($final_sumbit) && $final_sumbit == 0)) { ?>
                        <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
                            <a href="#" data-toggle="modal" data-target="#customer_admins_assign"
                                class="btn btn-info mbot30"><?php echo _l('assign_admin'); ?></a>
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
                                        <td data-order="<?php echo $c_admin['date_assigned']; ?>">
                                            <?php echo _dt($c_admin['date_assigned']); ?></td>
                                        <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
                                            <td>
                                                <a href="<?php echo admin_url('clients/delete_customer_admin/' . $client->userid . '/' . $c_admin['staff_id']); ?>"
                                                    class="btn btn-danger _delete btn-icon"><i class="fa fa-remove"></i></a>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="final-form">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <br>
                            <br>
                            <form id="final-form" onsubmit="return false;"
                                class="<?= !empty($final_sumbit) ? 'hide' : '' ?>">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" onclick="final_submission()"
                                            class="btn btn-primary button-22 pull-right">Final Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
<?php if (isset($client)) { ?>
    <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
        <div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog" data-backdrop="static"
            data-keyboard="false">
            <div class="modal-dialog">
                <input type="hidden" name="clientid" id="clientid" value="<?php echo $client_id ?>">

                <?php echo form_open(admin_url('clients/assign_admins/' . $client->userid)); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
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
    var primary_country =
        "<?= !empty($admissionpreferences->primary_country) ? $admissionpreferences->primary_country : 0 ?>";
    var primary_university =
        "<?= !empty($admissionpreferences->primary_university) ? $admissionpreferences->primary_university : 0 ?>";
    var courseArray = <?= !empty($dropdown_courses) ? json_encode($dropdown_courses, true) : [] ?>;
    var select_segment_default = "";
    var user_id = "<?= !empty($admissionpreferences->user_id) ? $admissionpreferences->user_id : '' ?>";
    var study_country_selected =
        <?= !empty(json_encode(explode(",", $admissionpreferences->study_country))) ? json_encode(explode(",", $admissionpreferences->study_country), true) : "" ?>;

    var get_entrance_exams_list = <?= !empty($get_entrance_exams_list) ? json_encode(array_column($get_entrance_exams_list, null, 'id'), true) : [] ?>;

    var get_entrance_exam_scrore = <?= !empty($get_entrance_exam_scrore) ? json_encode($get_entrance_exam_scrore, true) : [] ?>;
    // console.log(study_country_selected.length);
    if (study_country_selected.length > 0) {
        study_country_selected = study_country_selected.map(function(value) {
            return value.trim().toLowerCase();
        });
    }
    var dropdown_country_university_selection =
        <?= !empty($dropdown_country_university_selection) ? json_encode($dropdown_country_university_selection, true) : [] ?>;
    // console.log(dropdown_country_university_selection);
    var complete_application = " <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 1 : 0 ?>";

    if (complete_application == 1) {
        setTimeout(function() {
            $("form").find("input, select, textarea,button").prop("disabled", true).selectpicker("refresh");
        }, 1500);
    }
    
    
    
    document.addEventListener("DOMContentLoaded", function() {
    var documentAccessOnly = "<?=!empty($documentAccessOnly)?$documentAccessOnly:0?>";
    console.log(documentAccessOnly);

    if (documentAccessOnly == "1") {
        $('.nav-tabs-horizontal li').each(function() {
            var $li = $(this);
            var $a = $li.find('a[href="#documents"]');
            if ($a.length === 0) {
                $li.hide();
            } else {
                $li.show();
                $a.trigger("click"); // More robust to use $a not $li
            }
        });
        
        $(".btn-save-funn").hide();
    }
});


    function multiple_document_download(className, zipFileName = '<?= sanitizeFileName($basicdetails->first_name . ' ' . $basicdetails->last_name) ?>.zip') {
        const files = [];

        $("." + className).each(function() {
            const filePath = $(this).find(".file-path").val();
            const fileNameRaw = $(this).find(".file-name").val();

            if (filePath && fileNameRaw) {
                const safeName = sanitizeFileName(fileNameRaw);
                files.push({
                    url: filePath,
                    name: safeName
                });
            }
        });

        if (files.length === 0) {
            alert("No valid files found to download.");
            return;
        }

        downloadAndZipFiles(files, zipFileName);
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

        if ($("#degree option:selected").data("type") == "PG") {
            $("#Qualification-section-div").show();
        }

    });


    function formatPhoneNumber(input) {
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

    var countryDropdownArray = [];

    function show_country_dropdown(select_segment, selectCountry = ".study_country") {

        let filteredData = [];

        // Step 1: Filter data
        if (countryDropdownArray.length === 0) {
            filteredData = dropdown_country_university_selection.filter(function(entry) {
                return entry.name.toLowerCase().trim() === select_segment_default.toLowerCase().trim();
            });
        } else {
            filteredData = countryDropdownArray;
        }

        // Step 2: Create unique list of countries
        let seenCountries = new Set();
        let uniqueData = [];

        try {
            filteredData.forEach(function(entry) {
                if (entry && typeof entry.country_name === 'string') {
                    let countryLower = entry.country_name.toLowerCase().trim();

                    if (countryLower && !seenCountries.has(countryLower)) {
                        seenCountries.add(countryLower);
                        uniqueData.push({
                            id: entry.country_id || null,
                            name: entry.country_name.charAt(0).toUpperCase() + entry.country_name.slice(1)
                        });
                    }
                }
            });
        } catch (error) {
            console.error("Error processing filteredData:", error);
        }


        countryDropdownArray = uniqueData;

        // Step 3: Loop through all matching selects (can be multiple)
        $(selectCountry).each(function() {
            let $dropdown = $(this);

            // Store selected values before clearing
            let selectedVals = $dropdown.val() || [];

            // Clear current options
            $dropdown.empty();

            // Re-append options with selection retained
            countryDropdownArray.forEach(function(country) {
                let isSelected = selectedVals.includes(country.id.toString());

                let option = $('<option>', {
                    value: country.id,
                    text: country.name,
                    selected: isSelected
                });

                $dropdown.append(option);
            });

            // Refresh selectpicker (Bootstrap Select)
            $dropdown.selectpicker('refresh');
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
        // show_country_dropdown(select_segment);
    });

    document.addEventListener("DOMContentLoaded", function() {
        let dobInput = document.getElementById("dob");
        if (dobInput) {
            let today = new Date();
            let minAgeDate = new Date(today.getFullYear() - 15, today.getMonth(), today.getDate());
            dobInput.setAttribute("max", minAgeDate.toISOString().split("T")[0]);
        }
    });






    document.querySelectorAll(".capitalText").forEach(function(el) {
        el.addEventListener("input", function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    });


    function updateSymbol(id) {
        var selected = $(".currency-selector-" + id + " option:selected");
        $(".currency-symbol-" + id).text(selected.data("symbol"));
    }

    async function handleCountryChange(selectElement, universitySelectElement) {
        const selectedCountry = selectElement.value;
        const $countrySelect = $(selectElement);
        const $universitySelect = $(universitySelectElement);

        if (selectedCountry > 0) {
            try {
                const universityList = await show_university_dropdown(select_segment_default, selectedCountry);
                $universitySelect.empty();

                const $coursesSelect = $universitySelect
                    .closest(".university-combinations")
                    .find("select.study_courses");

                const selectedCourseValue = $coursesSelect.val();

                // Append university options
                universityList.forEach(uni => {
                    $universitySelect.append(
                        $('<option>', {
                            value: uni.id,
                            text: uni.name,
                            'data-country_id': uni.country_id
                        })
                    );
                });

                // Refresh the university dropdown
                $universitySelect.selectpicker('refresh');

                // 🔁 Delay needed to ensure UI selects any previously selected option (if auto-restored)
                setTimeout(async () => {
                    const selectedUniversityVal = $universitySelect.val(); // ✅ Check AFTER options rendered

                    if (!selectedUniversityVal) {
                        $coursesSelect.empty().selectpicker('refresh');
                    } else {
                        await handleUniversityChange($countrySelect, $coursesSelect, selectedCourseValue);
                    }
                }, 100); // Small timeout gives browser/UI time to update selectpicker

            } catch (error) {
                console.error("Error loading universities:", error);
            }
        } else {
            $universitySelect.empty().selectpicker('refresh');
        }
    }





    function show_university_dropdown(select_segment, countryid) {
        return new Promise(function(resolve, reject) {
            var filteredData = dropdown_country_university_selection.filter(function(entry) {
                return (
                    entry.name.toLowerCase() === select_segment.trim().toLowerCase() &&
                    entry.country_id.toLowerCase() === countryid.trim().toLowerCase()
                );
            });
            var uniqueUniversityNames = new Set();
            var uniqueData = [];

            filteredData.forEach(function(entry) {
                var uniLower = entry.university_name.toLowerCase();
                if (!uniqueUniversityNames.has(uniLower)) {
                    uniqueUniversityNames.add(uniLower);
                    uniqueData.push({
                        id: entry.university_id,
                        country_id: entry.country_id,
                        name: entry.university_name.charAt(0).toUpperCase() + entry.university_name
                            .slice(1)
                    });
                }
            });

            resolve(uniqueData); // ✅ Return the array of objects, not the Set
        });
    }

    function removeApplication(element, applicationId = "") {
        if (!confirm("Are you sure you want to delete the application?")) {
            return;
        }

        if (applicationId && parseInt(applicationId) > 0) {
            $.ajax({
                url: '<?= base_url() ?>/admin/clients/delete_application',
                method: 'POST',
                data: {
                    id: applicationId,
                    client_id: <?= $client_id ?>
                },
                dataType: 'json',
                success: function(response) {
                    // response = JSON.parse(response);
                    if (response.resp_code === 'RCS') {
                        $(element).parents(".university-combinations").remove();
                        alert_float("success", response.resp_desc);
                    } else {
                        console.warn("Delete failed:", response.resp_desc);
                        alert_float("danger", "Failed to delete application: " + response.resp_desc);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    alert_float("danger",
                        "An error occurred while trying to delete the application. Please try again.");
                }
            });
        } else {
            // No shortlisting ID, just remove the element from the DOM
            $(element).parents(".university-combinations").remove();
        }
    }



    var applicationIndex = 0; // Incrementing index for uniqueness

    function createNewApplication(selectElement) {
        applicationIndex++;

        let html = `
    <div class="university-combinations  col-md-12 mt-3">
     <div class="col-lg-1 p-0">
        <?= render_select('priority', $priority_array, array('id', 'name'), "Priority", [], ["onchange" => "isPrimaryUniversity(this)"], [], "", "priority-selection", "", "priority") ?>
            </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label for="study_country_${applicationIndex}"> Country <small class="text-danger">*</small></label>
                <select
                    class="form-control selectpicker required required-check study_country"
                    name="study_country[]"
                    id="study_country_${applicationIndex}"
                    data-live-search="true"
                    title="Select a country"
                    required
                    onchange="handleCountryChange(this,'#study_universities_${applicationIndex}')">
                </select>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="form-group">
                <label for="study_universities_${applicationIndex}">Universities</label>
                <select
                    class="form-control selectpicker study_universities"
                    name="study_universities[]"
                    id="study_universities_${applicationIndex}"
                    data-live-search="true"
                    onchange="handleUniversityChange(this,'#study_courses_${applicationIndex}')"
                    title="Select a University">
                </select>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="form-group">
                <label for="study_courses_${applicationIndex}">Courses</label>
                <select
                    class="form-control selectpicker study_courses"
                    name="study_courses[]"
                    id="study_courses_${applicationIndex}"
                    data-live-search="true"
                    title="Select a Course" >
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <div class="form-group">
                <label for="session_intake_combination_${applicationIndex}">Session Intake <small class="text-danger">*</small></label>
                <input type="month" class="form-control session_intake_combination"
                    id="session_intake_combination_${applicationIndex}"
                    name="session_intake_combination[]"
                    placeholder="Select Month and Year">
            </div>
        </div>

        <div class="col-lg-1">
            <div class="form-group">
                <p>&nbsp;</p>
                <span class="btn btn-danger fa-fa-icons" onclick="removeApplication(this)"><i class="fa fa-trash"></i></span>
            </div>
        </div>
    </div>`;

        // Append to container
        $('.university-combinations').last().after(html);


        let $dropdown = $(`#study_country_${applicationIndex}`);

        // Store selected values before clearing
        let selectedVals = $dropdown.val() || [];

        // Clear current options
        $dropdown.empty();

        // Re-append options with selection retained
        countryDropdownArray.forEach(function(country) {
            let isSelected = selectedVals.includes(country.id.toString());

            let option = $('<option>', {
                value: country.id,
                text: country.name,
                selected: isSelected
            });

            $dropdown.append(option);
        });

        $('.selectpicker').selectpicker('refresh');


    }

    function handleUniversityChange(select, coursesSelected, selectedCourseValue = "") {
        let courseSelect = $(coursesSelected);
        let selectedCourse = courseSelect.val();
        let selectedCourseText = courseSelect.find("option:selected").text();
        // Use JavaScript's split instead of PHP's explode
        let searchTerm = selectedCourseText ? selectedCourseText.trim().split(" ")[0] : '';
        loadCourses(searchTerm, courseSelect, selectedCourse); // pass the specific select element
    }


    const degree = "";

    function loadCourses(searchTerm = '', courseSelect, selectedCourse = '') {
        try {
            console.log("searchTerm:", searchTerm);
            console.log("courseSelect:", courseSelect);
            console.log("selectedCourse:", selectedCourse);

            const degreeElement = $("#degree option:selected");
            let degreeType = degreeElement.data("type")?.trim();

            console.log("Selected degree element:", degreeElement);
            console.log("Raw degree type:", degreeType);

            if (!degreeType) {
                console.warn("No degree type found.");
                return;
            }

            // Normalize degree type
            degreeType = degreeType === "UG" ? "Bachelor" : "Master";

            if (!courseSelect || courseSelect.length === 0) {
                console.warn("Invalid courseSelect element.");
                return;
            }

            const $container = $(courseSelect).closest(".university-combinations");
            const $countrySelect = $container.find("select.study_country");
            const $universitySelect = $container.find("select.study_universities");
            const $courseSelect = $container.find("select.study_courses");

            const selectedCountryId = $countrySelect.val();
            const selectedUniversity = $universitySelect.val();

            if (!selectedCountryId || !selectedUniversity) {
                console.warn("Country or university not selected.");
                return;
            }

            $courseSelect.empty(); // Clear existing options

            $.ajax({
                url: '<?= base_url('admin/clients/get_courses') ?>',
                method: 'POST',
                data: {
                    degree: degreeType,
                    search: searchTerm
                },
                dataType: 'json',
                success: function(response) {
                    const courseData = Array.isArray(response?.filter_data) ? response.filter_data : [];

                    if (courseData.length === 0) {
                        $courseSelect.append(
                            $('<option>', {
                                value: '',
                                text: '-- No Courses Found --'
                            })
                        );
                    } else {
                        courseData.forEach(course => {
                            $courseSelect.append(
                                $('<option>', {
                                    value: course.id,
                                    text: course.course_name,
                                    selected: selectedCourse == course.id
                                })
                            );
                        });
                    }

                    // Refresh the select picker if using Bootstrap Select
                    $courseSelect.selectpicker('refresh');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error loading courses:", {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });

                    $courseSelect.empty().append(
                        $('<option>', {
                            value: '',
                            text: '-- Error Loading Courses --'
                        })
                    ).selectpicker('refresh');
                }
            });
        } catch (err) {
            console.error("Unexpected error in loadCourses:", err);
            if ($(courseSelect).length) {
                $(courseSelect).empty().append(
                    $('<option>', {
                        value: '',
                        text: '-- Unexpected Error --'
                    })
                ).selectpicker('refresh');
            }
        }
    }






    function degreeChange() {
        $("select.study_country").val('').selectpicker('refresh');
        $("select.study_universities").html('<option value="">Select University</option>').selectpicker('refresh');
        $("select.study_courses").html('<option value="">Select Course</option>').selectpicker('refresh');



    }

    // Attach an event handler for when the select is opened (using Bootstrap Select 'shown.bs.select' event)




    <?php
    // Build entrance status options first
    $entrance_status_options = '<option value="">Select...</option>';
    foreach ($get_entrance_exams_status as $status) {
        $value = htmlspecialchars($status['id'], ENT_QUOTES, 'UTF-8');
        $selected = htmlspecialchars($status['selected'], ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($status['name'], ENT_QUOTES, 'UTF-8');
        $entrance_status_options .= "<option  data-selected=\"$selected\" value=\"$value\">$label</option>";
    }

    // Main HTML block
    $html = '
<div class="entrance-exams row mb-3" id="entrance-exam-__index__">
    <!-- Hidden Entrance ID -->
    <input type="hidden" class="entrance_id" name="entrance_id[__index__]" value="">

    <!-- Exam Status -->
    <div class="col-lg-2">
        <div class="form-group">
            <label for="entrance_exams_status___index__">Exam Status <small class="text-danger">*</small></label>
            <select
                name="entrance_exams_status[__index__]"
                id="entrance_exams_status___index__"
                class="form-control entrance_exams_status"
                required
                required-check
                onchange="changeEntranceStatus(this)">
                ' . $entrance_status_options . '
            </select>
        </div>
    </div>

    <!-- Dependent Fields (hidden until status is selected) -->
    <div class="entrance-exams-status-active row col-lg-9 ms-0 ps-0 hide">
        <!-- Exam Name -->
        <div class="col-lg-3">
            <div class="form-group">
                <label for="entrance_exams___index__">Exam Name <small class="text-danger">*</small></label>' .
        render_select(
            'entrance_exams[__index__]',
            $get_entrance_exams_list,
            ['id', 'name'],
            '',
            [],
            [
                'required' => 'required',
                'required-check' => 'required-check',
                'onchange' => 'changeEntranceExam(this)',
                'id' => 'entrance_exams___index__'
            ],
            [],
            '',
            'entrance_exams',
            '',
            'entrance_exams'
        ) .
        '</div>
        </div>

        <!-- Exam Date -->
        <div class="col-lg-3">
            <div class="form-group">
                <label for="entrance_date___index__">Exam Date <small class="text-danger">*</small></label>
                <input type="date" class="form-control entrance_date" name="entrance_date[__index__]" id="entrance_date___index__" required required-check>
            </div>
        </div>

        <!-- Marks -->
        <div class="col-lg-2">
            <div class="form-group">
                <label for="entrance_marks___index__">Marks <small class="text-danger">*</small></label>
                <input type="number" class="form-control entrance_marks" name="entrance_marks[__index__]" id="entrance_marks___index__" required required-check>
            </div>
        </div>

        <!-- Marksheet File -->
        <div class="col-lg-3">
            <div class="form-group">
                <label for="entrance_file___index__">Marksheet <small class="text-danger">*</small></label>
                <input type="file" class="form-control" name="entrance_file[__index__]" id="entrance_file___index__" required required-check accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>
    </div>
';

    // Add/Remove buttons
    $html_add = '
<div class="col-lg-1">
    <div class="form-group">
    <p>&nbsp;</p>
        <button type="button" class="btn btn-primary" onclick="createNewEntrance(this)">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div> </div>';

    $html_remove = '
<div class="col-lg-1">
    <div class="form-group">
    <p>&nbsp;</p>
        <button type="button" class="btn btn-danger" onclick="removeEntrance(this)">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div> </div>';


    ?>



    var entranceHTMLTemplate = `<?= addslashes($html . $html_remove) ?>`;
    var entranceHTMLAddTemplate = `<?= addslashes($html . $html_add) ?>`;

    var entranceIndex = "";

    function createNewEntrance(status = 0) {
        entranceIndex = ($(".entrance-exams").length + 10);

        let template = (status === 1 ? entranceHTMLAddTemplate : entranceHTMLTemplate);
        if (!template) {
            console.error("Template is undefined!");
            return;
        }

        let newHTML = template.replace(/__index__/g, entranceIndex);
        $("#entrance-exam-div").append(newHTML);
        $("#entrance-exam-div").find("select").selectpicker('refresh');
        entranceIndex++;
    }



    function removeEntrance(element, entranceId = "") {
        if (!confirm("Are you sure you want to delete the entrance?")) {
            return;
        }

        if (entranceId && parseInt(entranceId) > 0) {
            $.ajax({
                url: '<?= base_url() ?>/admin/clients/delete_entrance',
                method: 'POST',
                data: {
                    id: entranceId,
                    client_id: <?= $client_id ?>
                },
                dataType: 'json',
                success: function(response) {
                    // response = JSON.parse(response);
                    if (response.resp_code === 'RCS') {
                        $(element).parents(".entrance-exams").remove();
                        alert_float("success", response.resp_desc);
                    } else {
                        console.warn("Delete failed:", response.resp_desc);
                        alert_float("danger", "Failed to delete entrance: " + response.resp_desc);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    alert_float("danger",
                        "An error occurred while trying to delete the entrance. Please try again.");
                }
            });
        } else {
            // No shortlisting ID, just remove the element from the DOM
            $(element).parents(".entrance-exams").remove();
        }

    }


    // Attach live search to selectpicker

    function changeELS_status(event) {
        // Clear the container
        $("#entrance-exam-div").html("");
        // Create a new entrance block
        createNewEntrance(1);
        // Use standard JavaScript/jQuery property to check if checkbox is checked
        if ($(event).is(":checked")) {
            $("#entrance-exam-div").removeClass("hide");
            $("#entrance-exam-div-title").removeClass("hide");
        } else {
            $("#entrance-exam-div").addClass("hide");
            $("#entrance-exam-div-title").addClass("hide");

        }
    }

    function isPrimaryUniversity(event) {

    }







    function genrateEntranceBlock(getAcadmicType) {
        let html = `<div class='row col-md-12 mb-5 entrance-score-div'>`;

        get_entrance_exam_scrore.forEach(get_entr => {
            let uniqueId = Math.floor(Date.now() / 1000) + Math.floor(Math.random() * 1000)
            if (get_entr.exam_type == getAcadmicType) {
                html += `<div class='col-md-3 form-group'>`;
                html += `<label>${get_entr.name || ''} <span class='text-danger'>*</span></label>`;
                html += `<input class="form-control entrance-score-input" data-name="${get_entr.name}" data-id="${get_entr.id}" value="" required-check required name="` + uniqueId + `" type="text" min="0" value="0" step="any">`;
                html += `</div>`;
            }
        });

        html += `</div>`; // close row

        // Return HTML string instead of directly inserting into DOM
        return html;
    }

    function changeEntranceExam(triggerElement) {
        var entranceId = $(triggerElement).val()
        let getAcadmicType = get_entrance_exams_list[entranceId]?.academic_type;
        $(triggerElement).parents(".entrance-exams").find(".entrance-score-div").remove();
        if (getAcadmicType && getAcadmicType.length > 0) {
            const html = genrateEntranceBlock(getAcadmicType);
            $(triggerElement).parents(".entrance-exams").append(html);
        }
    }

    function currently_working(event) {
        // Uncheck all other checkboxes except the one clicked
        $(".work-exp-div input[name='currently_working[]']").not(event).prop("checked", false);
    }




    function createNewWorkExperience() {
        const timestamp = Date.now(); // Unique suffix for IDs and names

        let html = `
        <div class="row work-exp-div mb-3" id="work-exp-${timestamp}">
            <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                <label for="currently_working_${timestamp}" class="form-label">Working</label>
                <input type="checkbox" name="currently_working[]" onchange = "currently_working(this)" id="currently_working_${timestamp}" value="1" class="mt-1">
            </div>

            <div class="form-group col-md-1">
                <label for="work_experience_${timestamp}">Years <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="work_experience[]" id="work_experience_${timestamp}" required>
            </div>

            <div class="form-group col-md-9">
                <label for="work_profile_${timestamp}">Role/Profile <span class="text-danger">*</span></label>
                <textarea class="form-control" name="work_profile[]" id="work_profile_${timestamp}" rows="3" required></textarea>
            </div>

            <div class="form-group col-md-1 d-flex flex-column justify-content-center">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger" onclick="deleteWorkExp(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>`;

        document.getElementById('work-experience-container').insertAdjacentHTML('beforeend', html);
    }


    function removeWorkExperience(element) {
        if (confirm("Are you sure you want to delete this work experience?")) {
            $(element).parents(".work-exp-div").remove();
        }
    }

    function loadCoursesUniversity(searchTerm = '', $select, type = "") {
        let degreeType = ""
        if (type == 2) {
            degreeType = "Bachelor";
        }
        if (type == 3) {
            degreeType = "Master";
        }
        $.ajax({
            url: '<?= base_url('admin/clients/get_universities_course_list') ?>',
            method: 'POST',
            data: {
                search: searchTerm,
                type: type,
                degree: degreeType
            },
            dataType: 'json',
            success: function(response) {
                const dataArray = Array.isArray(response?.filter_data) ? response.filter_data : [];

                $select.empty();

                if (dataArray.length === 0) {
                    $select.append($('<option>', {
                        value: '',
                        text: '-- No Found --'
                    }));
                } else {

                    $select.append($('<option>', {
                        value: '',
                        text: '-- Select Option --'
                    }));
                    dataArray.forEach(data => {
                        $select.append($('<option>', {
                            value: data.id,
                            text: data.name
                        }));
                    });
                }

                if (!isNaN(searchTerm) && $.trim(searchTerm) !== "") {
                    $select.selectpicker('val', searchTerm); // ✅ Correct syntax
                } else {
                    console.log("It's text");
                }
                $select.selectpicker('refresh');
            },
            error: function(xhr) {
                console.error("Error fetching", xhr);
                $select.empty().append($('<option>', {
                    value: '',
                    text: '-- Error Loading --'
                })).selectpicker('refresh');
            }
        });
    }

    function changework_status(event) {
        $("#work-div .work-exp-div").remove();
        createNewWorkExperience();
        if ($(event).is(":checked")) {
            $("#work-div").removeClass("hide");
            $("#work-div").find(".work-exp-div").removeClass("hide");
        } else {
            $("#work-div").addClass("hide");
            $("#work-div").find(".work-exp-div").addClass("hide");
        }

    }

    function changeEntranceStatus(event) {
        const $parent = $(event).closest(".entrance-exams");
        const selectedData = $(event).find("option:selected").data("selected");
        const $statusActive = $parent.find(".entrance-exams-status-active");
        const $inputs = $statusActive.find(".entrance_marks, input[type='file']");

        $statusActive.find("select, input").val("").selectpicker('refresh');

        if (selectedData == 1 || selectedData == 2) {
            $statusActive.removeClass("hide");
            $inputs.prop("required", selectedData == 1).attr("required-check", selectedData == 1 ? "required-check" : null);
        } else {
            $statusActive.addClass("hide");
        }
    }
</script>