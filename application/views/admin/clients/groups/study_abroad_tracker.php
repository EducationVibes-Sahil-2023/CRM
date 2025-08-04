<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Initialize and sanitize main values
$selected_university_shortlisting = array_column($university_shortlisting ?? [], null, "id");
$applicant_status = !empty($client->tracker_id) ? $client->tracker_id : 0;
$activeShortlistingId = $_GET['shortlisting_id'] ?? '';

// Handle shortlisting logic
if (!empty($activeShortlistingId) && isset($selected_university_shortlisting[$activeShortlistingId])) {
    $selected = $selected_university_shortlisting[$activeShortlistingId];
    $selected_university_shortlisting = $selected;
    $applicant_status = $selected['tracker_id'] ?? $applicant_status;
}

// Staff list and mapping
$staff_list_raw = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list_raw, null, "staffid");

// Tracker & pendency
$applicant_tracker   = applicant_tracker_study($lead_type_status);
$applicant_pendency  = applicant_pendency();
$pendency_status     = applicant_pendency_status();
$pendencyStaus       = pendency_status(); // Consider renaming for clarity
$offerLetterStatus   = offerletterStatus();

// Currency list
$get_currencies_raw = get_currencies();
$get_currencies     = array_column($get_currencies_raw, null, 'id');

// Offer letter and pre-deposit retrieval (only if shortlisting ID exists)
$offer_letters = !empty($activeShortlistingId) ? get_offer_letters($client_id, $activeShortlistingId) : [];
$fessDeposite  = !empty($activeShortlistingId) ? get_pre_deposite($client_id, $activeShortlistingId) : [];

$interviewDetails = !empty($activeShortlistingId) ? get_interview($client_id, $activeShortlistingId) : [];
// Other supporting data
$study_abroad_vendors   = study_abroad_vendors();
$profile_creation_data  = $profile_creation_data ?? '';
$university_partner_names = get_university_partner_names();

// Document permissions and types
$delete_document_status = has_permission('customers', '', 'delete_documents');

$country_ids = !empty($admissionpreferences->study_country) ? explode(",", $admissionpreferences->study_country) : [];
$documents_type_list = get_documents($lead_type_status, $country_ids, 1);
$documents_type = array_column($documents_type_list, null, 'id');
$documents_type_dropdown = $documents_type;

// Applicant documents
$applicant_documents_raw = get_clients_documents($client_id);
$applicant_documents = [];

if (!empty($applicant_documents_raw[0]['data'])) {
    $decoded = json_decode($applicant_documents_raw[0]['data'], true);
    if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
        $applicant_documents = array_column($decoded, null, "id");
    }
}

// Visa-related
$visa_details = visa_details($client_id, 0, 1);
$visa_vendors = get_vendor_list(2);
$courier_type = get_courier_list();
$payment_mode = get_payment_mode();
$fundsStatus = get_status_table("funds_status");
$interviewType = get_status_table("interview_status");
$conformation_letter_status = get_status_table("application_conformation_letter_status");

$interviewResultStatus = [
    array("id" => "Pass", "name" => "Pass"),
    array("id" => "Fail", "name" => "Fail"),
    array("id" => "Pending", "name" => "Pending")
];

// Final submit and access logic
$staff_id_list = array_column($customer_admins ?? [], "staff_id");
$final_sumbit = $client->submission_status ?? 0;
$read_only = "readonly";

if (is_admin() || in_array(get_staff_user_id(), $staff_id_list)) {
    $final_sumbit = 0;
    $read_only = "";
}

?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/study_abroad.css">

<!-- MultiStep Form -->
<?php if ($client->submission_status != 1): ?>
    <h2 class="text-center">No final submission from counselor.</h2>
    <?php die; ?>
<?php endif; ?>

<?php
$staffData = $staff_list[get_staff_user_id()] ?? [];
if (empty($staffData["post_sales"]) && !is_admin()):
?>
    <h2 class="text-center">Applicant Tracker - Accessible Only for Post-Sale & Admin</h2>
<?php endif; ?>

<div class="row">
    <div id="msform" class="col-md-12 ">
        <h1 class="text-center mb-5"><?= !empty($selected_university_shortlisting['university_name']) ? $selected_university_shortlisting['university_name'] : '' ?></h1>
        <br>
        <br>
        <ul id="progressbar" class="d-flex justify-content-center">
            <?php foreach ($applicant_tracker as $key => $track): ?>
                <?php if ((int)$track["show_stages"] === 0 || ((int)$track["show_stages"] === 1 && (int)$activeShortlistingId > 0)): ?>
                    <li
                        data-id="<?= (int)$track['id'] ?>"
                        onclick="goToStep(<?= (int)$key ?>)"
                        data-show="<?= htmlspecialchars($track["show_div_name"] ?? '', ENT_QUOTES) ?>">
                        <?= htmlspecialchars($track["name"], ENT_QUOTES) ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
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
                                    <p class="text-right"><button style="display:block!important;" class="col-md-2 add_document add_university_btn" style="display:none;" type="button" onclick="add_visa_div()"><i class="fa fa-plus" aria-hidden="true"></i></button></p>

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

                                                                    <span class="<?= $status_text_color ?> ms-5"><b><?= $status_text ?></b></span>
                                                                <?php endif; ?>
                                                                &nbsp;
                                                                <?php if ($delete_document_status) { ?>
                                                                    <button class="btn-xs btn btn-danger" onclick="document_approved(this,<?= $doc_id ?>)"><i class="fa fa-trash"></i></button>
                                                                <?php } ?>
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
                            ?>
                                <form id="offer-letter-form" class="form-disabled mb-5" onsubmit="return false;">
                                    <?php if (!empty($offer_letters)): ?>
                                        <?php foreach ($offer_letters as $key => $o_letter):
                                            $file_url_offer_letter = $o_letter['offer_letter'] ?? '';
                                        ?>
                                            <div class="row offer-letter-form">
                                                <!-- Offer Date -->
                                                <div class="col-md-3">
                                                    <?= render_input(
                                                        "offer_date[]",
                                                        "Offer Letter Receiving <small class='text-danger'>*</small>",
                                                        $o_letter['offer_date'] ?? '',
                                                        'date',
                                                        ['required-check' => 'required-check', 'required' => 'required']
                                                    ); ?>
                                                </div>

                                                <!-- Offer Status -->
                                                <div class="col-md-3 form-group">
                                                    <label>Status <small class="text-danger">*</small></label>
                                                    <select name="university_offer_status[]"
                                                        class="form-control selectpicker required-check"
                                                        required
                                                        onchange="changeOfferStatus(this)">
                                                        <option value="">Select an option</option>
                                                        <?php foreach ($offerLetterStatus as $item): ?>
                                                            <?php if (!is_array($item)) continue; ?>
                                                            <option
                                                                data-upload_status="<?= htmlspecialchars($item['upload_status'] ?? '') ?>"
                                                                value="<?= htmlspecialchars($item['id']) ?>"
                                                                <?= ($item['id'] == ($o_letter['university_offer_status'] ?? '')) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($item['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <!-- Offer Upload -->
                                                <div class="col-md-3 offer-letter-div <?= !empty($offerLetterStatus[$o_letter['university_offer_status']]) ? '' : 'hide' ?> form-group">
                                                    <label>Offer Upload <small class="text-danger">*</small></label>
                                                    <input type="file"
                                                        data-fileUrl="<?= $file_url_offer_letter ?>"
                                                        class="form-control"
                                                        name="offer_letter_<?= $key ?>"
                                                        accept=".pdf,image/*"
                                                        <?= !empty($o_letter['offer_letter']) ? '' : 'required required-check' ?>>
                                                    <?php if (!empty($file_url_offer_letter)): ?>
                                                        <div class="margin-top">
                                                            <i class="fa fa-eye btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url_offer_letter) ?>');"></i>
                                                            <i class="fa fa-download btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($file_url_offer_letter) ?>', '_blank');"></i>
                                                            <?php if ($delete_document_status): ?>
                                                                <button class="btn-xs btn btn-danger" type="button" onclick="delete_documents_study(2, <?= $track['id'] ?>, <?= $activeShortlistingId ?>)">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Add/Remove Buttons -->
                                                <div class="col-md-3">
                                                    <label>&nbsp;</label>
                                                    <p class="text-right">
                                                        <?php if ($key == 0): ?>
                                                            <button class="col-md-2 add_document add_university_btn pull-right" type="button" onclick="addofferLetter(this)">
                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-danger add_document add_university_btn" type="button" onclick="removeOfferLetter(this)">
                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>

                                                <!-- Condition -->
                                                <div class="col-md-12 form-group condition_div">
                                                    <label>Condition</label>
                                                    <textarea rows="4"
                                                        class="form-control"
                                                        name="remark_offer_letter[]"><?= trim($o_letter['conditional_notes'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Blank default row -->
                                        <div class="row offer-letter-form">
                                            <div class="col-md-3">
                                                <?= render_input(
                                                    "offer_date[]",
                                                    "Offer Letter Receiving <small class='text-danger'>*</small>",
                                                    '',
                                                    'date',
                                                    ['required-check' => 'required-check', 'required' => 'required']
                                                ); ?>
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Status <small class="text-danger">*</small></label>
                                                <select name="university_offer_status[]"
                                                    class="form-control selectpicker required-check"
                                                    required
                                                    onchange="changeOfferStatus(this)">
                                                    <option value="">Select an option</option>
                                                    <?php foreach ($offerLetterStatus as $item): ?>
                                                        <?php if (!is_array($item)) continue; ?>
                                                        <option data-upload_status="<?= htmlspecialchars($item['upload_status'] ?? '') ?>" value="<?= htmlspecialchars($item['id']) ?>">
                                                            <?= htmlspecialchars($item['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-3 offer-letter-div hide form-group">
                                                <label>Offer Upload <small class="text-danger">*</small></label>
                                                <input type="file"
                                                    data-fileUrl=""
                                                    class="form-control"
                                                    name="offer_letter_0"
                                                    accept=".pdf,image/*"
                                                    required required-check>
                                            </div>

                                            <div class="col-md-3">
                                                <label>&nbsp;</label>
                                                <p class="text-right">
                                                    <button class="col-md-2 add_document add_university_btn pull-right" type="button" onclick="addofferLetter(this)">
                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                    </button>
                                                </p>
                                            </div>

                                            <div class="col-md-12 form-group condition_div offer-letter-div hide">
                                                <label>Condition</label>
                                                <textarea rows="4"
                                                    class="form-control"
                                                    name="remark_offer_letter[]"></textarea>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </form>

                            <?php } else if ($track["show_div_name"] == "pre_deposite_div") {
                                $file_url_fees_deposite_slip = isset($selected_university_shortlisting['fees_deposite_slip']) ? $selected_university_shortlisting['fees_deposite_slip'] : '';
                            ?>
                                <form id="pre-deposite-form" class="form-disabled mb-5" onsubmit="return false;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?= render_input(
                                                'tentative_date',
                                                "Tentative Date <small class='text-danger'>*</small>",
                                                $selected_university_shortlisting['tentative_date'] ?? '',
                                                'date',
                                                ['required-check' => 'required-check', 'required' => 'required']
                                            ); ?>
                                        </div>
                                    </div>

                                    <div class="fees-deposite-section">
                                        <?php if (!empty($fessDeposite)): ?>
                                            <?php foreach ($fessDeposite as $key => $deposite): ?>
                                                <div class="row fees-deposite-item">
                                                    <!-- Payment -->
                                                    <div class="col-md-3">
                                                        <label>Payment Amount <small class='text-danger'>*</small></label>
                                                        <div class="input-group form-group">
                                                            <input type="number" name="payment_amount[<?= $key ?>]" value="<?= ($deposite['payment_amount'] ?? '') ?>" required required-check class="form-control" placeholder="0.00">
                                                            <div class="input-group-addon currency-addon">
                                                                <select name="payment_currency_id[<?= $key ?>]" class="currency-selector" required required-check>
                                                                    <?php foreach ($get_currencies as $c): ?>
                                                                        <option value="<?= $c['id'] ?>" <?= (!empty($deposite['currency_type']) && $deposite['currency_type'] == $c['id']) ? 'selected' : '' ?>>
                                                                            <?= ($c['name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Deposit Date -->
                                                    <div class="col-md-3">
                                                        <?= render_input(
                                                            "fees_deposite_date[$key]",
                                                            "Date of Deposit <small class='text-danger'>*</small>",
                                                            $deposite['date_of_deposite'] ?? '',
                                                            'date',
                                                            ['required' => 'required', 'required-check' => 'required-check']
                                                        ); ?>
                                                    </div>

                                                    <!-- File Upload -->
                                                    <div class="col-md-3 form-group">
                                                        <label>Proof of Deposit <small class="text-danger">*</small></label>
                                                        <input type="file"
                                                            name="proof_of_deposite[<?= $key ?>]"
                                                            class="form-control"
                                                            accept=".pdf,image/*"
                                                            data-fileUrl="<?= ($deposite['proof_of_deposite'] ?? '') ?>"
                                                            <?= empty($deposite['proof_of_deposite']) ? 'required required-check' : '' ?>>

                                                        <?php if (!empty($deposite['proof_of_deposite'])): ?>
                                                            <div class="margin-top">
                                                                <button type="button" class="btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($deposite['proof_of_deposite']) ?>');">
                                                                    <i class="fa fa-eye"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-xs btn-primary" onclick="download_media_files('<?= base_url($deposite['proof_of_deposite']) ?>', '_blank');">
                                                                    <i class="fa fa-download"></i>
                                                                </button>
                                                                <?php if ($delete_document_status): ?>
                                                                    <button type="button" class="btn btn-xs btn-danger" onclick="delete_documents_study(3, <?= $track['id'] ?? 0 ?>, <?= $activeShortlistingId ?>)">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Controls -->
                                                    <div class="col-md-3 form-group">
                                                        <label>&nbsp;</label>
                                                        <p class="text-right">
                                                            <?php if ($key === 0): ?>
                                                                <button type="button" class="btn btn-success" onclick="addFeesDeposite(this)">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-danger" onclick="removeFeesDeposite(this)">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <!-- Default Blank -->
                                            <div class="row fees-deposite-item">
                                                <div class="col-md-3">
                                                    <label>Payment Amount <small class="text-danger">*</small></label>
                                                    <div class="input-group form-group">
                                                        <input type="number" name="payment_amount[0]" class="form-control" placeholder="0.00" required required-check>
                                                        <div class="input-group-addon currency-addon">
                                                            <select name="payment_currency_id[0]" class="currency-selector" required required-check>
                                                                <?php foreach ($get_currencies as $c): ?>
                                                                    <option value="<?= $c['id'] ?>"><?= ($c['name']) ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <?= render_input('fees_deposite_date[0]', "Date of Deposit <small class='text-danger'>*</small>", '', 'date', ['required' => 'required', "required-check" => "required-check"]); ?>
                                                </div>

                                                <div class="col-md-3 form-group">
                                                    <label>Proof of Deposit <small class="text-danger">*</small></label>
                                                    <input type="file" class="form-control" name="fees_deposite_slip[0]" accept=".pdf,image/*" required required-check>
                                                </div>

                                                <div class="col-md-3 form-group">
                                                    <label>&nbsp;</label>
                                                    <p class="text-right">
                                                        <button type="button" class="btn btn-success" onclick="addFeesDeposite(this)">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </form>

                            <?php } else if ($track["show_div_name"] == "funds_div") { ?>
                                <form id="funds-form" class="form-disabled mb-5" onsubmit="return false;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <?= render_select(
                                                'funds_status',
                                                $fundsStatus,
                                                ['id', 'name'],
                                                "Funds Status <small class='text-danger'>*</small>",
                                                isset($selected_university_shortlisting['funds_status']) ? $selected_university_shortlisting['funds_status'] : '',
                                                ['required-check' => 'required-check', 'required' => 'required']
                                            ); ?>
                                        </div>
                                        <div class="col-md-9">
                                            <label for="funds_remark">Funds Remark <small class='text-danger'>*</small></label>
                                            <textarea rows="4" name="funds_remark" class="form-control funds_remark" required><?= isset($selected_university_shortlisting['funds_remark']) ? $selected_university_shortlisting['funds_remark'] : '' ?></textarea>
                                        </div>
                                    </div>
                                </form>
                            <?php } else if ($track["show_div_name"] == "interview_div") { ?>

                                <form id="interview-form" class="form-disabled mb-5" onsubmit="return false;">

                                    <div class="interview-section-div">
                                        <?php if (!empty($interviewDetails)) {
                                            foreach ($interviewDetails as $key => $interview) {
                                                $checkHide_Show = 0; ?>
                                                <div class="interview-section-inter mb-3 border p-3 rounded col-md-12">

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Interview Status <small class="text-danger">*</small></label>
                                                            <select name="interview_status[]" class="form-control selectpicker interview_status" data-live-search="true" data-none-selected-text="Non selected" onchange="changeInterviewStatus(this)" required required-check>
                                                                <option value="">Select...</option>
                                                                <?php

                                                                foreach ($interviewType as $type):
                                                                    $isSelected = ($interview['interview_status'] ?? '') == $type['id'];
                                                                    if ($isSelected) {
                                                                        $checkHide_Show = $type['show_status'];
                                                                    }
                                                                ?>
                                                                    <option
                                                                        data-show-status="<?= $type['show_status'] ?>"
                                                                        value="<?= htmlspecialchars($type['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                                        <?= $isSelected ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($type['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="interview-section-hide <?= ($checkHide_Show == 1) ? '' : 'hide' ?>">
                                                        <div class="col-md-3">
                                                            <?= render_input('interview_date[]', "Interview Date <small class='text-danger'>*</small>", $interview['interview_date'] ?? '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label>Remark <small class="text-danger">*</small></label>
                                                            <textarea name="interview_remark[]" class="form-control interview_remark" required><?= htmlspecialchars($interview['interview_remark'] ?? '') ?></textarea>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="result_status">Result <small class="text-danger">*</small></label>
                                                                <select name="result_status_<?= $key ?>" class="form-control selectpicker result_status" required required-check>
                                                                    <option value="">Select...</option>
                                                                    <?php foreach ($interviewResultStatus as $status): ?>
                                                                        <option value="<?= htmlspecialchars($status['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                                            <?= (isset($interview['interview_result_status']) && $interview['interview_result_status'] == $status['id']) ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($status['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="col-md-1">
                                                        <p>&nbsp;</p>
                                                        <button type="button" class="btn btn-success add_interview_btn" onclick="addInterview()">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php }
                                        } else { ?>
                                            <div class="interview-section-inter mb-3 border p-3 rounded">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Interview Status <small class="text-danger">*</small></label>
                                                            <select name="interview_status" class="form-control selectpicker interview_status" data-live-search="true" data-none-selected-text="Non selected" onchange="changeInterviewStatus(this)" required required-check>
                                                                <option value="">Select...</option>
                                                                <?php foreach ($interviewType as $type): ?>
                                                                    <option data-show-status="<?= $type['show_status'] ?>" value="<?= htmlspecialchars($type['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                                        <?= htmlspecialchars($type['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="interview-section-hide hide">
                                                        <div class="col-md-3">
                                                            <?= render_input('interview_date[]', "Interview Date <small class='text-danger'>*</small>", '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label>Remark <small class="text-danger">*</small></label>
                                                            <textarea name="interview_remark[]" class="form-control interview_remark" required></textarea>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <?= render_select(
                                                                'result_status',
                                                                $interviewResultStatus,
                                                                ['id', 'name'],
                                                                "Result <small class='text-danger'>*</small>",
                                                                '',
                                                                ['required-check' => 'required-check', 'required' => 'required'],
                                                                [],
                                                                '',
                                                                'result_status'
                                                            ); ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-1">
                                                        <p>&nbsp;</p>
                                                        <button type="button" class="btn btn-success add_interview_btn" onclick="addInterview()">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>


                                </form>
                            <?php } else if ($track["show_div_name"] == "confirmation_div") { ?>
                                <form id="confirmation-form" class="form-disabled mb-5" onsubmit="return false;">
                                    <div class="row col-md-12">
                                        <div class="col-md-3">
                                            <?= render_input('confirmation_date', "Date of Application <small class='text-danger'>*</small>", '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>

                                        <div class="col-md-3">
                                            <?= render_input('confirmation_receving_date', "Receiving Date <small class='text-danger'>*</small>", '', 'date', ['required-check' => 'required-check', 'required' => 'required']); ?>
                                        </div>

                                        <div class="col-md-2">
                                            <?= render_select(
                                                'confirmation_status',
                                                $conformation_letter_status,
                                                ['id', 'name'],
                                                "Result <small class='text-danger'>*</small>",
                                                '',
                                                ['required-check' => 'required-check', 'required' => 'required'],
                                                [],
                                                '',
                                                'confirmation_status'
                                            ); ?>
                                        </div>
                                    </div>
                                </form>
                            <?php } ?>
                            <?php if ($k > 0 && $k < 5) { ?>
                                <p class='col-md-12 margin-top hide'>
                                    <label class=" margin-top">Secondary University Remarks</label>
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
            <div class="col-md-12 text-right" style="margin:25px; z-index:999;"><button type="checked" class="btn btn-lg btn-toggle btn-switch-toggle" data-toggle="button" aria-pressed="false" autocomplete="off">
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



    notes_url = "<?= base_url() ?>admin/clients/get_application_notes_study/<?= $client_id ?>/<?= !empty($activeShortlistingId) ? $activeShortlistingId : '' ?>";
    activity_url = "<?= base_url() ?>admin/clients/get_application_activity/<?= $client_id ?>/<?= !empty($activeShortlistingId) ? $activeShortlistingId : '' ?>";

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
                upload_data.append("shortlisting_id", selectedUniversityShortListing);

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
        if (id == 7) {
            if (same_step == 0) {
                let check_validation = await check_required_fields("funds-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
            }

            await check_funds_form(upload_data);
        }
        if (id == 8) {
            if (same_step == 0) {
                let check_validation = await check_required_fields("interview-form");
                if (!check_validation) {
                    hide_loader();
                    return false;
                }
            }

            await check_interview_form(upload_data);
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

                $("#offer-letter-form div.offer-letter-form").each(function(index) {
                    let offer_date = $(this).find("input[type='date']").val() || '';
                    let university_offer_status = $(this).find("select.selectpicker").val() || '';
                    let selectedOption = $(this).find("select.selectpicker option:selected");
                    let upload_status = selectedOption.data('upload_status') || '';
                    let offer_letter = $(this).find("input[type='file']")[0];
                    let offer_letter_url = $(this).find("input[type='file']").data("fileurl") || '';
                    let remark = $(this).find("textarea").val() || '';

                    if (offer_letter && offer_letter.files.length > 0 && upload_status == 1) {
                        upload_data.append(`offer_letter_${index}`, offer_letter.files[0]);
                    } else {
                        if (offer_letter_url === "") {
                            upload_data.append(`offer_letter_url_${index}`, "");
                        }
                    }

                    offer_letter_array.push({
                        offer_date: offer_date,
                        university_offer_status: university_offer_status,
                        offer_letter_url: offer_letter_url,
                        remark: remark,
                        upload_status: upload_status
                    });
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
                upload_data.append("tentative_date", tentative_date);

                $(".fees-deposite-item").each(function(index) {
                    const $row = $(this); // current row context

                    let fees_deposite_date = $row.find("input[type='date']").val() || '';
                    let payment_amount = $row.find("input[type='number']").val() || '';
                    let payment_currency_id = $row.find("select.currency-selector").val() || '';
                    let fileInput = $row.find("input[type='file']")[0];
                    let fileUrl = $row.find("input[type='file']").data("fileurl") || '';

                    if (fileInput && fileInput.files.length > 0) {
                        // Use indexed field name for multiple file uploads
                        upload_data.append(`fees_deposite_slip_${index}`, fileInput.files[0]);
                    } else {
                        // Send file URL if no new upload
                        upload_data.append(`fees_deposite_slip_url_${index}`, fileUrl);
                    }

                    pre_deposite_array.push({
                        fees_deposite_date: fees_deposite_date,
                        payment_amount: payment_amount,
                        payment_currency_id: payment_currency_id,
                        fees_deposite_slip_url: fileUrl
                    });
                });

                upload_data.append("pre_deposite_data", JSON.stringify(pre_deposite_array));
                resolve(upload_data);

            } catch (error) {
                reject(error);
            }
        });
    }

    function check_funds_form(upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let pre_deposite_array = [];
                let funds_status = $("#funds-form").find("select[name='funds_status']").val() || '';
                let funds_remark = $("#funds-form").find("textarea[name='funds_remark']").val() || '';
                upload_data.append("funds_status", funds_status);
                upload_data.append("funds_remark", funds_remark);



                upload_data.append("pre_deposite_data", JSON.stringify(pre_deposite_array));
                resolve(upload_data);

            } catch (error) {
                reject(error);
            }
        });
    }

    function check_interview_form(upload_data) {
        return new Promise((resolve, reject) => {
            try {
                let interview_array_data = [];

                $(".interview-section-inter").each(function() {
                    const $row = $(this);

                    const interview_status = $row.find("select.interview_status").val()?.trim() || '';
                    const interview_date = $row.find("input[type='date']").val()?.trim() || '';
                    const interview_remark = $row.find("textarea.interview_remark").val()?.trim() || '';
                    const interview_result_status = $row.find("select.result_status").val()?.trim() || '';

                    interview_array_data.push({
                        interview_status,
                        interview_date,
                        interview_remark,
                        interview_result_status
                    });
                });

                upload_data.append("interview_data", JSON.stringify(interview_array_data));
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
        return new Promise((resolve) => {
            let form_status = true;
            let additional_fields = {};

            const $formElements = $("#" + id).find(
                "input:visible, select:visible, textarea:visible, input[type='date']:visible"
            );

            $formElements.each(function() {
                const $field = $(this);
                const value = $field.val();
                const isRequired = $field.is("[required-check]");
                const name = $field.attr("name");

                if (!isRequired || !name) return;

                additional_fields[name] = "required";

                // Handle checkboxes
                if ($field.is("textarea")) {
                    const value = $.trim($field.val()); // Use .val() to get the value of a textarea
                    if (value === "") {
                        form_status = false;
                        $field.addClass("error");
                    } else {
                        $field.removeClass("error");
                    }
                }

                if ($field.is(":checkbox")) {
                    if (!$field.is(":checked")) {
                        form_status = false;
                        $field.addClass("error");
                    } else {
                        $field.removeClass("error");
                    }
                } else {
                    // For text, select, textarea, date inputs
                    if ($.trim(value) === "") {
                        form_status = false;
                        $field.addClass("error");
                    } else {
                        $field.removeClass("error");
                    }
                }
            });

            if (!form_status) {
                appValidateForm($("#" + id), additional_fields);
                $("#" + id).submit(); // If desired, remove this line to prevent auto-submit
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
            $(".offer-letter-div").val('');
        } else {
            $(".offer-letter-div").addClass("hide");
            $(".offer-letter-div").val('');

        }
    }

    function renderInput(name, labelHTML, value = '', type = 'text') {
        return `
        <div class="form-group">
            <label for="${name}">${labelHTML}</label>
            <input type="${type}" name="${name}" class="form-control required-check" required value="${value}">
        </div>
    `;
    }


    const offerLetterStatus = <?= json_encode($offerLetterStatus) ?>;

    function generateOfferLetterOptions() {
        let options = '';
        offerLetterStatus.forEach(item => {
            if (typeof item === 'object') {
                options += `<option data-upload_status="${item.upload_status || ''}" value="${item.id}">${item.name}</option>`;
            }
        });
        return options;
    }

    function removeOfferLetter(event) {
        $(event).parents(".offer-letter-form").remove();
    }

    function addofferLetter() {
        const offerLetterHTML = `
        <div class="row offer-letter-form">
            <!-- Offer Date -->
            <div class="col-md-3">
                <?= render_input(
                    'offer_date_' . time(),
                    "Offer Letter Receiving <small class='text-danger'>*</small>",
                    $o_letter['receving_date'] ?? '',
                    'date',
                    ['required-check' => 'required-check', 'required' => 'required']
                ); ?>

            </div>

         <div class="col-md-3 form-group">
    <label for="university_offer_status">
        Status <small class="text-danger">*</small>
    </label>
    <select
        name="university_offer_status_<?= time() ?>"
        class="form-control selectpicker required-check"
        required
        onchange="changeOfferStatus(this)">
        <option value="">Select an option</option>
        <?php foreach ($offerLetterStatus as $item): ?>
            <?php if (!is_array($item)) continue; ?>
            <option
                data-upload_status="<?= htmlspecialchars($item['upload_status'] ?? '') ?>"
                value="<?= htmlspecialchars($item['id']) ?>">
                <?= htmlspecialchars($item['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


            <div class="col-md-3 offer-letter-div hide form-group">
                <label for="university_offer_letter">
                    Offer Upload <small class="text-danger">*</small>
                </label>
                <input
                    type="file"
                    data-fileUrl=""
                    class="form-control"
                    name="offer_letter_<?= time() ?>"
                    accept=".pdf,image/*" required-check required>
            </div>

            <div class="col-md-3">
                <label>&nbsp;</label>
               <p class="text-right"> <button class="btn btn-danger add_document add_university_btn" type="button" onclick="removeOfferLetter(this)">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button></p>
            </div>
            <div class="col-md-12 form-group condition_div offer-letter-div hide">
                <label>Condition</label>
                <textarea
                    rows="4"
                    class="form-control"
                    id="remark_offer_letter"
                    name="remark_offer_letter"></textarea>
            </div>
        </div>
    `;

        $('#offer-letter-form').append(offerLetterHTML); // Append to a container in your HTML
        $('.selectpicker').selectpicker('refresh'); // If using Bootstrap select
    }

    window.currencyOptions = <?= json_encode($get_currencies) ?>;
    let currencyOptions = '';

    if (typeof window.currencyOptions === 'object' && window.currencyOptions !== null) {
        currencyOptions = Object.values(window.currencyOptions).map(c =>
            `<option value="${c.id}">${c.name}</option>`
        ).join('');
    }


    function addFeesDeposite() {
        const uniqueId = Date.now();

        const html = `
        <div class="row mt-3 fees-deposite-item" id="fees_row_${uniqueId}">
            <div class="col-md-3">
                                                    <label>Payment Amount <small class='text-danger'>*</small></label>
                                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                                                        <input type="number" name="payment_amount_${uniqueId}[]" value="" required required-check class="form-control" placeholder="0.00" size="8">
                                                        <div class="input-group-addon currency-addon">
                                                            <select name="payment_currency_id_${uniqueId}[]" required required-check class="currency-selector">
                                                                <?php foreach ($get_currencies as $c): ?>
                                                                    <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

            <div class="col-md-3">
                <label>Date of Deposit <small class='text-danger'>*</small></label>
                <input type="date" name="fees_deposite_date_${uniqueId}" required required-check class="form-control">
            </div>

            <div class="col-md-3 offer-letter-div form-group">
                <label>Proof of deposit <small class="text-danger">*</small></label>
                <input type="file" name="fees_deposite_slip_${uniqueId}" class="form-control" accept=".pdf,image/*" required required-check>
            </div>

            <div class="col-md-3 form-group">
                <label>&nbsp;</label>
                <p class="text-right">
                    <button class="btn btn-danger" type="button" onclick="removeFeesDeposite(this)">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </p>
            </div>
        </div>
    `;

        document.querySelector('.fees-deposite-section').insertAdjacentHTML('beforeend', html);
    }

    function removeFeesDeposite(event) {
        $(event).parents(".fees-deposite-item").remove();
    }


    const interviewTypeOptions = <?= json_encode($interviewType) ?>;
    const interviewResultStatusOptions = <?= json_encode($interviewResultStatus) ?>;


    function changeInterviewStatus(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const showStatus = selectedOption.dataset.showStatus;

        const $section = $(selectElement).closest('.interview-section-inter');

        // Clear all input, select, and textarea values inside this section (excluding the one just changed)
        $section.find("input, select, textarea").not(selectElement).val('').prop('checked', false);

        // Toggle visibility based on showStatus
        if (showStatus === '1') {
            $section.find('.interview-section-hide').removeClass('hide');
        } else {
            $section.find('.interview-section-hide').addClass('hide');
        }
    }


    function addInterview() {
        const uniqueId = Date.now();

        // Build Interview Status Options
        let interviewStatusHTML = `<option value="">Select...</option>`;
        interviewTypeOptions.forEach(type => {
            interviewStatusHTML += `<option value="${type.id}" data-show-status="${type.show_status}">${type.name}</option>`;
        });

        // Build Result Status Options
        let resultStatusHTML = `<option value="">Select...</option>`;
        interviewResultStatusOptions.forEach(status => {
            resultStatusHTML += `<option value="${status.id}">${status.name}</option>`;
        });

        const html = `
    <div class="interview-section-inter col-md-12">
        <div class="col-md-3">
            <div class="form-group">
                <label>Interview Status <small class="text-danger">*</small></label>
                <select name="interview_status_${uniqueId}" class="form-control selectpicker interview_status"
                        data-live-search="true" data-none-selected-text="Non selected"
                        required required-check onchange="changeInterviewStatus(this)">
                    ${interviewStatusHTML}
                </select>
            </div>
        </div>
<div class="interview-section-hide hide">
        <div class="col-md-3">
            <label>Date of Interview</label>
            <input type="date" name="interview_date_${uniqueId}" class="form-control interview_date" required required-check>
        </div>

        <div class="col-md-3">
            <label>Remark</label>
            <textarea name="interview_remark_${uniqueId}" class="form-control interview_remark"></textarea>
        </div>

        <div class="col-md-2">
            <label>Result <small class="text-danger">*</small></label>
            <select name="result_status_${uniqueId}" class="form-control result_status selectpicker" required required-check>
                ${resultStatusHTML}
            </select>
        </div>
</div>
        <div class="col-md-1">
            <p>&nbsp;</p>
            <button type="button" class="btn btn-danger" onclick="$(this).closest('.interview-section-inter').remove()">
                <i class='fa fa-trash'></i>
            </button>
        </div>
    </div>`;

        document.querySelector('.interview-section-div').insertAdjacentHTML('beforeend', html);
        $('.interview-section-div select.selectpicker').selectpicker('refresh');
    }
</script>