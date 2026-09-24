<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$lead_type_status = "2";
if (!empty($score_value)) {
	$score_value = array_column($score_value, null, "type");
}
$passport_stages = get_passport_stages();
$pcc_stages = get_pcc_stages();
$caste_category = get_caste_category();
$neet_status = get_neet_status();
$states = get_states();
$board_dropdown = get_board_dropdown();
$ev_partner = get_ev_partner();
$courseName = get_client_courseName()??[];
$scholarshipsData = scholarshipsData($admissionpreferences->primary_university??'');
$important_dates = get_client_important_dates($client_id);
$ev_type = [array("id"=>1,"name"=>"Agent","singleUniversityStatus"=>1,"ReferralDropdown"=>0),array("id"=>2,"name"=>"Referral","singleUniversityStatus"=>0,"ReferralDropdown"=>1)];
$staff_list              = $this->leads_model->get_staff_list();

$referralCounsollor = $this->leads_model->get_staff_list(["active"=>1,"referralCounsollor"=>1]);
$staff_list = array_column($staff_list, null, "staffid");
if (!empty($board_dropdown)) {
	array_unshift($board_dropdown, array("id" => "", "name" => "Select Board"));
}

$documents_type =  get_documents($lead_type_status, !empty($admissionpreferences->primary_country) ? explode(",", $admissionpreferences->primary_country) : [], 1);
$profile_section = [];
foreach ($documents_type as $documents) {
	if (empty($profile_section[$documents["profile_stages"]][$documents['id']])) {
		$profile_section[$documents["profile_stages"]][$documents['id']] = $documents;
	}
}


array_push($documents_type, array("id" => "application", "disabled" => 1, "disabledd" => 1, "stage" => "", "name" => "Admission Letter", "file_type" => ".pdf,image/*"));
array_push($documents_type, array("id" => "University_Payment_Slip", "disabled" => 1, "disabledd" => 1, "stage" => "", "name" => "University Payment Slip", "file_type" => ".pdf,image/*"));
array_push($documents_type, array("id" => "invitation", "disabled" => 1, "disabledd" => 1, "stage" => "Visa", "name" => "Invitation Letter", "file_type" => ".pdf,image/*"));
array_push($documents_type, array("id" => "visa", "disabled" => 1, "disabledd" => 1, "stage" => "", "name" => "Visa", "file_type" => ".pdf,image/*"));
$staff_id = [];
if (!empty($customer_admins)) {
	$staff_id = array_column($customer_admins, "staff_id");
}
$final_sumbit = $client->submission_status;
$read_only = "readonly";

$admin_status = 0;
$visa_details =  visa_details($client_id, 0, 1);

if (is_admin() ||  !empty($staff_list[get_staff_user_id()]["post_sales"]) || has_permission('customers', '', 'create')) {
	$final_sumbit = 0;
	$read_only = "";
	$admin_status = 1;
}
// if (in_array(get_staff_user_id(), $staff_id)) {
//     $final_sumbit = 0;
//     $read_only = "";
//     $admin_status = 1;
// }

$last_index = array_key_last($visa_details);

// if (!empty($client_id)) {
// 	$applicant_documents =  get_clients_documents($client_id);

// 	if (!empty($applicant_documents[0]["data"])) {
// 		$applicant_documents = json_decode($applicant_documents[0]["data"], true);
// 		array_push($applicant_documents, array("id" => "application", "document_file" => !empty($university_shortlisting[0]['application_file']) ? $university_shortlisting[0]['application_file'] : ''));
// 		array_push($applicant_documents, array("id" => "invitation", "document_file" => !empty($university_shortlisting[0]['invitation_letter']) ? $university_shortlisting[0]['invitation_letter'] : ''));
// 		array_push($applicant_documents, array("id" => "visa", "document_file" => !empty($visa_details[$last_index]['file']) ? $visa_details[$last_index]['file'] : ''));
// 		array_push($applicant_documents, array("id" => "University_Payment_Slip", "document_file" => !empty($university_shortlisting[0]['university_fees_payment_slip']) ? $university_shortlisting[0]['university_fees_payment_slip'] : ''));

// 		if (!empty($applicant_documents)) {
// 			$applicant_documents = array_column($applicant_documents, null, "id");
// 		}
// 	}
// } else {
// 	$applicant_documents = [];
// }

$applicant_documents =  !empty($client_id)?get_clients_documents($client_id):[];
if (!empty($applicant_documents[0]["data"])) {
    $applicant_documents = json_decode($applicant_documents[0]["data"], true);
    
    // if(is_admin())
    // {
    //     print_r($visa_details);
    // }
    
    foreach ($visa_details as $k=>$visaD)
    {
        if(!empty($visa_details[$k]['file'])){
        array_push($documents_type, array("id" => "visa_".$k, "disabled" => 1, "disabledd" => 1, "stage" => "", "name" => "Visa ".($k+1), "file_type" => ".pdf,image/*"));
        array_push($applicant_documents, array("id" => "visa_".$k, "document_file" => !empty($visa_details[$k]['file']) ? $visa_details[$k]['file'] : '')); 
        }
    }



// foreach ($university_shortlisting as $shortlistingD) {

//     $universityStatus = "Secondary";

//     if ($shortlistingD['primary_university'] == 1) {
//         $universityStatus = "Primary";
//     }

//     // Admission Letter
    

// if(!empty($shortlistingD['application_file'])){
//     $applicationId = "application_" . $universityStatus . '_' . $shortlistingD['id'];
//     array_push($documents_type, array(
//         "id"         => $applicationId,
//         "disabled"   => 1,
//         "disabledd"  => 1,
//         "stage"      => "",
//         "name"       => "Admission Letter ($universityStatus)",
//         "file_type"  => ".pdf,image/*"
//     ));

//     array_push($applicant_documents, array(
//         "id"            => $applicationId,
//         "document_file" => !empty($shortlistingD['application_file'])
//             ? $shortlistingD['application_file']
//             : ''
//     ));
// }

// if(!empty($shortlistingD['invitation_letter'])){
//     // Invitation Letter
//     $invitationId = "invitation_" . $universityStatus . '_' . $shortlistingD['id'];

//     array_push($documents_type, array(
//         "id"         => $invitationId,
//         "disabled"   => 1,
//         "disabledd"  => 1,
//         "stage"      => "",
//         "name"       => "Invitation Letter ($universityStatus)",
//         "file_type"  => ".pdf,image/*"
//     ));

//     array_push($applicant_documents, array(
//         "id"            => $invitationId,
//         "document_file" => !empty($shortlistingD['invitation_letter'])
//             ? $shortlistingD['invitation_letter']
//             : ''
//     ));
    
// }

// if(!empty($shortlistingD['university_fees_payment_slip'])){
//     // University Payment Slip
//     $paymentId = "University_Payment_Slip_" . $universityStatus . '_' . $shortlistingD['id'];

//     array_push($documents_type, array(
//         "id"         => $paymentId,
//         "disabled"   => 1,
//         "disabledd"  => 1,
//         "stage"      => "",
//         "name"       => "University Payment Slip ($universityStatus)",
//         "file_type"  => ".pdf,image/*"
//     ));

//     array_push($applicant_documents, array(
//         "id"            => $paymentId,
//         "document_file" => !empty($shortlistingD['university_fees_payment_slip'])
//             ? $shortlistingD['university_fees_payment_slip']
//             : ''
//     ));
// }
// }

   
   $priorityLabels = [
    1  => 'Primary',
    2  => 'Secondary',
    3  => 'Third',
    4  => 'Fourth',
    5  => 'Fifth',
    6  => 'Sixth',
    7  => 'Seventh',
    8  => 'Eighth',
    9  => 'Ninth',
    10 => 'Tenth'
];

foreach ($university_shortlisting as $shortlistingD) {

    // Default (old logic)
    $universityStatus = ($shortlistingD['primary_university'] == 1)
        ? 'Primary'
        : 'Secondary';

    // New logic using university_priority JSON
    if (!empty($shortlistingD['university_priority'])) {

        $universityPriority = json_decode($shortlistingD['university_priority'], true);

        if (is_array($universityPriority)) {

            foreach ($universityPriority as $priorityData) {

                if (
                    !empty($priorityData['university']) &&
                    !empty($shortlistingD['university_name']) &&
                    trim($priorityData['university']) === trim($shortlistingD['university_name'])
                ) {
                    $priority = (int)$priorityData['priority'];

                    if (isset($priorityLabels[$priority])) {
                        $universityStatus = $priorityLabels[$priority];
                    }

                    break;
                }
            }
        }
    }



    // Admission Letter
    if (!empty($shortlistingD['application_file'])) {

        $applicationId = "application_" . $universityStatus . '_' . $shortlistingD['id'];

        $documents_type[] = [
            "id"        => $applicationId,
            "disabled"  => 1,
            "disabledd" => 1,
            "stage"     => "",
            "name"      => "Admission Letter ($universityStatus)",
            "file_type" => ".pdf,image/*"
        ];

        $applicant_documents[] = [
            "id"            => $applicationId,
            "document_file" => $shortlistingD['application_file']
        ];
    }

    // Invitation Letter
    if (!empty($shortlistingD['invitation_letter'])) {

        $invitationId = "invitation_" . $universityStatus . '_' . $shortlistingD['id'];

        $documents_type[] = [
            "id"        => $invitationId,
            "disabled"  => 1,
            "disabledd" => 1,
            "stage"     => "",
            "name"      => "Invitation Letter ($universityStatus)",
            "file_type" => ".pdf,image/*"
        ];

        $applicant_documents[] = [
            "id"            => $invitationId,
            "document_file" => $shortlistingD['invitation_letter']
        ];
    }

    // University Payment Slip
    if (!empty($shortlistingD['university_fees_payment_slip'])) {

        $paymentId = "University_Payment_Slip_" . $universityStatus . '_' . $shortlistingD['id'];

        $documents_type[] = [
            "id"        => $paymentId,
            "disabled"  => 1,
            "disabledd" => 1,
            "stage"     => "",
            "name"      => "University Payment Slip ($universityStatus)",
            "file_type" => ".pdf,image/*"
        ];

        $applicant_documents[] = [
            "id"            => $paymentId,
            "document_file" => $shortlistingD['university_fees_payment_slip']
        ];
    }
}


    if (!empty($applicant_documents)) {
        $applicant_documents = array_column($applicant_documents, null, "id");
    }
}


$years_array = [];
$currentYear = date("Y");

// Generate an array of the last 15 years
for ($i = 0; $i < 15; $i++) {
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
// $neetResultStatus[]["name"] = "Not Appeared";
// $neetResultStatus[]["name"] = "Fail";
$neetResultStatus[]["name"] = "Without Neet";
array_unshift($neetResultStatus, array(""));


$visasectionDetails = $this->db
	->select("file,tracking_receipt,application_form")
	->from(db_prefix() . "visa_details")
	->where(array("userid" => $client_id))
	->order_by("id", "ASC")
	->get()
	->result_array();

if (!empty($visasectionDetails)) {

	foreach ($visasectionDetails as $k => $visaInfo) {

		foreach ($visaInfo as $key => $value) {

			if (!empty($value)) {   // skip empty columns

				// Custom display name
				if ($key == "file") {
					$display_name = "Visa Stamp";
				} else {
					$display_name = ucfirst(str_replace("_", " ", $key));
				}

				$display_name .= " " . ($k + 1);

				$documents_type[] = array(
					"id"        => "Visa Section",
					"disabled"  => 1,
					"disabledd" => 1,
					"stage"     => "Visa",
					"name"      => $display_name,
					"file_type" => ".pdf,image/*"
				);
			}
		}
	}
}


?>
<!-- <script src="https://code.jquery.com/jquery-3.6.3.js"></script> -->
<script>
     var isCounsollor = <?= (!is_admin() && empty($staff_list[get_staff_user_id()]['post_sales'])) ? 1 : 0 ?>;
	var final_sumbit = <?= !empty($final_sumbit) ? $final_sumbit : 0 ?>;
	var admin_status = <?= $admin_status ?>;
	console.log("final_sumbit", final_sumbit);
	var admissionpreferences_freeze = "<?= !empty($admissionpreferences->freeze) ? 1 : 0 ?>";
</script>
<?php
$text_danger_mbbs = "";
$text_danger_mbbs_required = "";
if ($lead_type_status == 2) {
	$text_danger_mbbs = "<small class='text-danger'></small>";
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
		/ box-shadow: 0px 0px 12px lightgrey;/ / background: lightgrey;/ / margin-bottom: 30px;/ / padding: 30px;/ border-radius: 10px;
	}

	.accadmic-education-div h4 {
		/ text-align: center;/
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
		/ border: 1px solid black;/ width: 100%;
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
		/ box-shadow: 0px 0px 10px lightgrey;/
	}
	
	.scholarship-details .dropdown.bootstrap-select {
    width: 100% !important;
    padding: 0px;
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

	#applicant_fees .bootstrap-select>.dropdown-toggle {
		/ border: 0px !important;/
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
				 <li role="presentation" type="7" section="Basic Information" class="active">
                        <a href="#student_details" class="active" aria-controls="student_details" role="tab" data-toggle="tab">Student Details</a>
                    </li>
                    <li role="presentation" type="8" section="Passport Information">
                        <a href="#passport" aria-controls="passport" role="tab" data-toggle="tab">Passport</a>
                    </li>
                    <li role="presentation" type="6" section="Admission Preferences">
                        <a href="#admission_preferences" aria-controls="admission_preferences" role="tab" data-toggle="tab">Admission Preferences</a>
                    </li>
                    <li role="presentation" type="9" section="Academic Details">
                        <a href="#academic_details" aria-controls="academic_details" role="tab" data-toggle="tab">Academic Details</a>
                    </li>
                    <li role="presentation" type="5" section="document">
                        <a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a>
                    </li>
                    <li role="presentation" type="2" section="Welcome message">
                        <a href="#welcome_message" aria-controls="welcome_message" role="tab" data-toggle="tab">Welcome Message</a>
                    </li>
                    <li role="presentation" type="1" section="Fees data updated" >
                        <a href="#fees_details" aria-controls="fees_details"  role="tab" data-toggle="tab">Fees Details</a>
                    </li>
                    <!-- <li role="presentation" type="1" section="Important Dates" >-->
                    <!--    <a href="#imp_date" aria-controls="imp_date"  role="tab" data-toggle="tab">Important Dates</a>-->
                    <!--</li>-->

					<?php hooks()->do_action('after_customer_billing_and_shipping_tab', isset($client) ? $client : false); ?>
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
		
        <?php 
           
           if(!empty($client->fees_error) && $client->fees_error==1 ) { ?>
        <div class="alert alert-warning d-flex align-items-start warning-message-fees" role="alert">
        <div>
        <strong> <i class="fa fa-exclamation-triangle"></i> &nbsp; Attention!</strong><br>
        Your applicant fee and scholarship details have been cleared because the Primary Country or University was changed. Please review and complete the fee and scholarship details again before proceeding.
        </div>
        </div>
         <?php } if(!empty($admissionpreferences->primary_country) && strtolower($admissionpreferences->primary_country)=="russia" && (empty($academicdetails->school_name) || empty($academicdetails->school_address)) ) { ?>
         
          <div class="alert alert-warning d-flex align-items-start warning-message-fees" role="alert">
        <div>
        <strong> <i class="fa fa-exclamation-triangle"></i> &nbsp; Attention!</strong><br>
        **School Name or School Address is mandatory for applicants whose primary country is Russia. Please enter the required information in the Academic Details section before proceeding.**

        </div>
        </div>
         <?php 
             
         }?>
         
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
												<?php
												array_unshift($ev_partner, array("id" => "", "value" => "", "name" => "Select Partner"));
												$selected_agent[] = !empty($client->agent_id) ? $client->agent_id : '';

												echo render_select('agent_id', $ev_partner, array('id', 'name'), "Partner", $selected_agent, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "agent_id");
												?>
											</div>
										</div>
										
							
											<div class="col-lg-3">
								<div class="form-group">
<?php
array_unshift($ev_type, [
    "id" => "",
    "value" => "",
    "name" => "Select Partner Type"
]);

$selected_partner[] = !empty($client->partner_type) ? $client->partner_type : '';

// Default: mandatory
$attributes = [];

// Old records (before May 2026) => not mandatory
if (!empty($client->datecreated) && strtotime($client->datecreated) < strtotime('2026-01-01') ) {
    $attributes = [];
} else {
    $attributes = [
        "required" => "required",
        "required-check" => "required-check",
         "onchange"       => "partnerTypeChange(this)"
    ];
}

echo render_select(
    'partner_type',
    $ev_type,
    ['id', 'name'],
    'Partner Type',
    $selected_partner,
    $attributes,
    [],
    "",
    "",
    "",
    "partner_type"
);
?>
</div>
										</div>
										
										<div class="col-lg-3 <?=!empty($client->partner_type) && $client->partner_type==1 ? 'hide' : ''?>" id="referralCounsollorDiv">
                                                    <div class="form-group">
                                                    <?php 
                                                    	array_unshift($referralCounsollor, array("id" => "", "value" => "", "name" => "Select Referral"));
                                                    
                                                    $attributes = [
        "required" => "required",
        "required-check" => "required-check",
    ];
    
                                                    echo render_select(
                                                    'referralCounsollor',
                                                    $referralCounsollor??[],
                                                    ['staffid', 'staff_name'],
                                                    'Referral Counsollor',
                                                    [$client->referralCounsollor??''],
                                                    $attributes,
                                                    [],
                                                    "",
                                                    "",
                                                    "",
                                                    "ReferralDropdown"
                                                    );
                                                    ?>
                                                    </div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputFirstName">First Name <small class="text-danger"></small></label>
												<input class="form-control" <?= $read_only ?> type="text" class="form-group" required-check required placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->first_name : $contact->firstname; ?>'>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputLastName">Last Name</label>
												<input class="form-control " <?= $read_only ?> type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->last_name : $contact->lastname; ?>'>
											</div>
										</div>
								


									</div>
									<div class="row">
									    
									    		<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputEmail">Student's email id <small class="text-danger"></small></label>
												<input class="form-control " <?= $read_only ?> type="text" class="form-group" placeholder="Student's email id" name="email" value='<?php echo (isset($basicdetails)) ? $basicdetails->email : $contact->email; ?>'>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputMobileNumber">Mobile Number <small class="text-danger"></small></label>
												<input class="form-control check-phonenumber" <?= $read_only ?> type="tel" class="form-group" placeholder="Mobile Number" name="mobile" pattern="\d{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
													maxlength="10" value='<?php echo (isset($basicdetails)) ? $basicdetails->mobile : $contact->phonenumber; ?>'>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputDateOfBirth">Date Of Birth <small class="text-danger"></small></label>
												<input type="date" class="form-control" name="dob" id="dob" value='<?php echo ($basicdetails->dob != '') ? $basicdetails->dob : ''; ?>'>
											</div>
										</div>


									   <div class="col-lg-2">
    <div class="form-group">
        <label>Gender <small class="text-danger">*</small></label>

        <?php
        $gender_list = [
            ['id' => 'Male', 'name' => 'Male'],
            ['id' => 'Female', 'name' => 'Female'],
            ['id' => 'Other', 'name' => 'Other'],
        ];

        array_unshift($gender_list, [
            'id' => '',
            'name' => 'Select Gender'
        ]);

        $selected_gender = !empty($basicdetails->gender)
            ? [$basicdetails->gender]
            : [];

        echo render_select(
            'gender',
            $gender_list,
            ['id', 'name'],
            '',
            $selected_gender,
            [
                'required' => 'required',
                'required-check' => 'required-check'
            ],
            [],
            '',
            '',
            '',
            'gender'
        );
        ?>
    </div>
    </div>
    

                                        <div class="col-lg-2">
    <div class="form-group">
        <label>Cource Name <small class="text-danger">*</small></label>

       <?php

array_unshift($courseName, [
    'id'   => '',
    'name' => 'Select Course'
]);

$selected_course = !empty($client->course_name)
    ? [$client->course_name]
    : [];

echo render_select(
    'course_name',
    $courseName,
    ['id', 'name'],
    '',
    $selected_course,
    [
        'required' => 'required',
        'required-check' => 'required-check'
    ],
    [],
    '',
    '',
    '',
    'course_name'
);
?>
    </div>
</div>
  
</div>
                                        

    <div class="row">
        
          	<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputPassword1">Category <small class="text-danger"></small></label>
												<?php
												array_unshift($caste_category, array("id" => "", "value" => "", "name" => "Select Category"));
												$selected_category[] = !empty($basicdetails->category) ? $basicdetails->category : '';

												echo render_select('category', $caste_category, array('id', 'name'), "", $selected_category, [], [], "", "", "", "category");
												?>

											</div>
										</div>
						
    
    <div class="col-lg-3">
<div class="form-group">
    
    <label>Loan Required <?php
        $isRequired = empty($client->datecreated)
            || strtotime($client->datecreated) >= strtotime('2026-05-01');

        if ($isRequired) {
            echo '<small class="text-danger">*</small>';
        }
    ?></label>

    <?php
    $loan_required_list = [
        ['id' => '1', 'name' => 'Yes'],
        ['id' => '2', 'name' => 'No'],
    ];

    array_unshift($loan_required_list, [
        'id' => '',
        'name' => 'Select Loan Required'
    ]);

    $selected_loan_required = !empty($client->loan_required)
        ? [$client->loan_required]
        : [];

    $attributes = [
        'onchange' => 'toggleLoanType()'
    ];

    if ($isRequired) {
        $attributes['required'] = 'required';
        $attributes['required-check'] = 'required-check';
    }

    echo render_select(
        'loan_required',
        $loan_required_list,
        ['id', 'name'],
        '',
        $selected_loan_required,
        $attributes,
        [],
        '',
        '',
        '',
        'loan_required'
    );
    ?>
</div>
    </div>
    
    <div class="col-lg-3 loan_type_div <?=!empty($client->loan_required) && $client->loan_required==1?'':'hide'?>">
    <div class="form-group">
    <label>Loan Type <small class="text-danger">*</small></label>
    
    <?php
    $loan_type_list = [
    // ['id' => '3', 'name' => 'Not Required'],
    ['id' => '1', 'name' => 'EV'],
    ['id' => '2', 'name' => 'Outside'],
    ];
    
    array_unshift($loan_type_list, [
    'id' => '',
    'name' => 'Select Loan Type'
    ]);
    
    $selected_loan_type = !empty($client->loan_type)
    ? [$client->loan_type]
    : [];
    
    echo render_select(
    'loan_type',
    $loan_type_list,
    ['id', 'name'],
    '',
    $selected_loan_type,
    [
    // 'required' => 'required',
    // 'required-check' => 'required-check'
    ],
    [],
    '',
    '',
    '',
    'loan_type'
    );
    ?>
    </div>
    </div>
    
    
    </div>
									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputPassword1">State <small class="text-danger"></small></label>
												<?php
												array_unshift($states, array("id" => "", "value" => "", "name" => "Select States"));
												$selectedState[] = !empty($client_infomation->state) ? $client_infomation->state : '';

												echo render_select('state', $states, array('name', 'name'), "", $selectedState, ["required" => "required", "required-check" => "required-check"], [], "", "", "", "state");
												?>

											</div>

										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputMobileNumber">Parent's Name <small class="text-danger"></small></label>
												<input class="form-control" type="text" class="form-group" placeholder="Parents Name" name="father_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->father_name : ''; ?>'>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputMobileNumber">Parent's Contact <small class="text-danger"></small></label>
												<input class="form-control check-phonenumber" type="tel" pattern="\d{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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
													<label for="exampleInputDateOfBirth">Refrence Name <small class="text-danger"></small></label>
													<input type="text" <?= $read_only ?> class="form-control" name="reference_name" id="reference_name" required value='<?php echo ($client->reference_name != '') ? $client->reference_name : ''; ?>'>
												</div>
											</div>
										<?php } ?>


									</div>
									<div class="row">

										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputMobileNumber">Address </label>
												<textarea <?= $read_only ?> name="address" class="form-control"><?php echo (isset($client)) ? $client->address : ''; ?></textarea>
											</div>
										</div>
										<?php
										foreach ($profile_section["student_details"] as $s_stage) {
											$doc_type = $s_stage["name"] ?? '';
											$doc_id = $s_stage["id"] ?? '';
											$info = $s_stage["info"] ?? '';
											$accept = $s_stage["file_type"] ?? '';
											$is_mandatory = !empty($s_stage["mandatry"]);
											$mandatry_text = $is_mandatory ? "<small class='text-danger'></small>" : '';
											$required_attr = $is_mandatory ? "" : '';
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
									<div class="btn-save-fun <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id())?'':'hide' ?>">
										<div class="col-md-12">
											<button type="submit" onclick="save_basic_details(1)" class="btn btn-primary button-22 pull-right">Save changes</button>
										</div>
									</div>
								</form>


							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if (!empty($client_id)) { ?>
				<div role="tabpanel" class="tab-pane student-data-div disabled-form" id="passport">
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<h4>Passport Informations</h4>
								<?php
								$show_passport_details = 0;
								$show_arn =0;
								$show_fields = [];
								?>
								<hr>
								<form id="passport-form" class="form-disabled" onsubmit=" return false;">
									<div class="">
										<div class="col-lg-3">
											<div class="form-group">
												<label>Passport <small class="text-danger"></small></label>
												<select class="form-control" name="passport_status" onchange="change_passport_status()" id="passport" required required-check>
													<option value="">Select Passport Status</option>
													<?php
													foreach ($passport_stages as $p) {
														$selected = "";
														if ($p["id"] == $passport_info->passport_status) {
															$selected = "selected";
															$show_passport_details = $p['show_status'];
															$show_arn =$p['arn'];
															$show_fields = !empty($p['show_field'])?explode(",",$p["show_field"]):[];
														}
													?>
														<option value="<?= $p["id"] ?>"  data-passport_showing_data="<?=$p['show_field']??''?>" data-passport_number_status="<?= $p['show_status'] ?>"  data-passport_arn_status="<?= $p['arn'] ?>" <?= $selected ?>><?= $p["name"] ?></option>
													<?php

													}
													?>
												</select>
											</div>
										</div>
								
										      <div class="col-md-3  passport-div-status passport-arn-status <?=(in_array("new_passport_arn", $show_fields) )? '' : 'hide' ?>">

                                            <div class="form-group">
                                                <label for="new_passport_arn">Passport ARN <small
                                                        class="text-danger">*</small></label>
                                                <input class="form-control passport-info text-uppercase" type="text"
                                                    placeholder="Enter Passport ARN" name="new_passport_arn" id="new_passport_arn"
                                                    pattern="^[A-Z0-9-]{15,20}$"
                                                    title="Passport ARN must be 15 to 20 characters, using uppercase letters (A-Z), numbers (0-9), and hyphens (-) only."
                                                    maxlength="20" onkeyup="isValidARN()"
                                                    value="<?= isset($passport_info) ? htmlspecialchars($passport_info->new_passport_arn) : '' ?>">

                                            </div>
                                        </div>
                                        
                                        <?php if(is_admin()){ ?>
                                               <div class="col-lg-3 passport-div-status <?= (in_array("appointment_date", $show_fields)) ? '' : 'hide' ?>">
                                        <div class="form-group">
                                            <label for="appointment_date">Appointment Date <small class="text-danger">*</small></label>
                                            <input class="form-control passport-info" type="Date" class="form-group" placeholder="Enter Appointment Date" name="appointment_date" value="<?= (isset($passport_info) ? $passport_info->appointment_date : '') ?>" required-check>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
										<div class="col-lg-3 passport-div-status <?= (in_array("passport_number", $show_fields))  ? '' : 'hide' ?>">
											<div class="form-group">
												<label for="passport_number">Passport Number <small class="text-danger"></small></label>
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

    										<div class="col-lg-3 passport-div-status <?= (in_array("issue_date", $show_fields))  ? '' : 'hide' ?>">
											<div class="form-group">
												<label for="issue_date">Issue Date <small class="text-danger"></small></label>
												<input class="form-control passport-info" type="Date" class="form-group" placeholder="Enter Passport Number" name="issue_date" value="<?= (isset($passport_info) ? $passport_info->issue_date : '') ?>" required-check>
											</div>
										</div>


										<div class="col-lg-3 passport-div-status <?= (in_array("exp_date", $show_fields))  ? '' : 'hide' ?>">
											<div class="form-group">
												<label for="exp_date">Expiry Date <small class="text-danger"></small></label>
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
											$mandatry_text = $is_mandatory ? "<small class='text-danger'></small>" : '';
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
										<div class="col-lg-3 ">
											<div class="form-group">
												<label>PCC status</label>
												<select class="form-control" onchange="change_pcc_status()" name="pcc_status" id="pcc_status">
													<option value="">Select PCC Status</option>
													<?php
													foreach ($pcc_stages as $pcc) {
														$selected = "";
														if ($pcc["id"] == $client->pcc_status) {
															$selected = "selected";
															$show_pcc_details = $pcc['upload'];
														}
													?>
														<option value="<?= $pcc["id"] ?>" <?= $selected ?> data-pcc_orignal_doc_id="<?= $pcc['orignal_doc_id'] ?? 0 ?>" data-pcc_status="<?= $pcc['upload'] ?? 0 ?>"><?= $pcc["name"] ?></option>
													<?php

													}
													?>
												</select>
											</div>
										</div>

										<?php
										foreach ($profile_section["pcc"] as $s_stage) {
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
											<div class="col-lg-3 media-files pcc-div-status <?= !empty($show_pcc_details && $show_pcc_details == 1) ? '' : 'hide' ?>">
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
									 <div class="row col-md-12 hide">

                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" onclick="apply_new_passport()" name="new_passport_status" id="new_passport" value="1" <?= !empty($passport_info->new_passport_status && $passport_info->new_passport_status == 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="new_passport">
                                                I have applied for passport renewal
                                            </label>
                                        </div>
                                    </div>

                                    <div class="new-passport-info " style="display:<?= !empty($passport_info->new_passport_status && $passport_info->new_passport_status == 1) ? '' : 'none' ?>">
                                        <div class="col-md-4">

                                            <div class="form-group">
                                                <label for="new_passport_arn">Passport ARN <small
                                                        class="text-danger">*</small></label>
                                                <input class="form-control passport-info text-uppercase" type="text"
                                                    placeholder="Enter Passport ARN" name="new_passport_arn" id="new_passport_arn"
                                                    pattern="^[A-Z0-9-]{15,20}$"
                                                    title="Passport ARN must be 15 to 20 characters, using uppercase letters (A-Z), numbers (0-9), and hyphens (-) only."
                                                    maxlength="20" onkeyup="isValidARN()"
                                                    value="<?= isset($passport_info) ? htmlspecialchars($passport_info->new_passport_arn) : '' ?>">


                                            </div>
                                        </div>
                                        <?php
                                        foreach ($profile_section["new_passport"] as $s_stage) {
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
                                                class="col-lg-4 media-files passport-div-status ">
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
									<div class="btn-save-fun <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id())?'':'hide' ?>">
										<div class="col-md-12">
											<button type="submit" onclick="save_passport_details()" class="btn btn-primary button-22 pull-right">Save changes</button>
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
									<input name="academicDetailsId" type="hidden" value="<?= $academicdetails->id ?>">
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
													<label for="course">Course <small class="text-danger"></small></label>
													<input name="course" id="course" type="hidden" class="form-control" value="<?= !empty($admissionpreferences->course) ? $admissionpreferences->course : 'MBBS' ?>">
													<input type="text" class="form-control" readonly disabled required-check value="<?= !empty($admissionpreferences->course) ? $admissionpreferences->course : 'MBBS' ?>">

												</div>
											</div>
											<div class="col-lg-4">
												<div class="form-group">
													<label for="session_intake">Session Intake <small class="text-danger"></small></label>
													<input type="month" class="form-control" required-check id="session_intake" name="session_intake"
														value="<?= !empty($admissionpreferences->session_intake) ? date('Y-m', strtotime($admissionpreferences->session_intake)) : '' ?>"
														placeholder="Select Month and Year">
												</div>
											</div>
											<div class="col-lg-4">
												<div class="form-group">
													<label for="acadmic_year">Acadmic Year <small class="text-danger"></small></label>
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
													<label for="study_country">Where would you like to study? <small class="text-danger"></small></label>
													<select class="form-control selectpicker  required required-check" required required-check name="study_country" id="study_country" multiple required>
														<option value="">Select country </option>
													</select>
												</div>
											</div>

											<?php if (is_admin() || !empty($staff_list[get_staff_user_id()]["post_sales"]) || has_permission('customers', '', 'create')) { ?>
												<div class="col-lg-4">
													<div class="form-group">
														<label for="primary_university">Primary University<small class="text-danger"></small></label>
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
														<label for="primary_university">Primary Country<small class="text-danger"></small></label>
														<input type="text" class="form-control" id="primary_country" name="primary_country" value="<?= $admissionpreferences->primary_country ?>">
													</div>
												</div>
											<?php } ?>

										</div>
										<div class="universities row">

										</div>
										
										       <h4>University Preferences</h4>
                            <hr>
                                    <div id="universityPrefrences">
                                        
                                    </div>
                                    
									</div>
									<div class="row btn-save-fun <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id())?'':'hide' ?>">
										<div class="col-md-12 text-right  btn-save-fun">
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
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<h4>Academic Details </h4>
									<hr>
									<div class="row accadmic-education-div">
										<h4> 10<sup>th</sup> Academic Details </h4>
										<hr>
										<div class="col-lg-4 border2 border1 hide">
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
										<div class="col-lg-3 border2 border1 hide">
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
										<div class="col-lg-3 border2 border1 hide">
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
										<div class="col-lg-2 border2 border1 hide ">
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
											$is_mandatory = '';
											$mandatry_text = $is_mandatory ? "<small class='text-danger'></small>" : '';
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

										<h4>12<sup>th</sup> Academic Details<small class="text-danger"></small></h4>
										<hr>

										<div class="row qualification-div" id="twelthAcademicDetails" style="display:<?= ($academicdetails->after_x_status == '12th' || $academicdetails->after_x_status == 'Both' || empty($academicdetails->after_x_status)) ? 'block' : 'none' ?>">

											<div class="col-lg-3 border2 border1">
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
													<p>School Name</p>
												</div>
												<div class="c2">
													<input class="form-control" required required-check  type="text" placeholder="School Name" name="school_name" id="school_name" value="<?= $academicdetails->school_name; ?>">
												</div>
											</div>
											<div class="col-lg-3 border2 border1">
												<div class="c1">
													<p>School Adress</p>
												</div>
												<div class="c2">
													<textarea class="form-control" required required-check placeholder="School Address" name="school_address" id="school_address"><?= $academicdetails->school_address; ?></textarea>
												</div>
											</div>
											<div class="col-lg-2 border2 border1">
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
												<div class="col-lg-2 border2 border1 " id="twelth_marking_scheme_div">
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
												<div class="col-lg-1 border2 border1">
													<div class="c1">
														<p>PCB <?= $text_danger_mbbs ?></p>
													</div>
													<div class="c2">
														<input class="form-control" <?= $text_danger_mbbs_required ?> type="float" placeholder="PCB Marks" name="pcb" id="pcb" value="<?= $academicdetails->pcb; ?>">
													</div>
												</div>
												<div class="col-lg-1 border2 border1">
													<div class="c1">
														<p>Online Result</p>
													</div>
													<div class="c2">
														<input
															class="check-group"
															type="checkbox"
															name="online_result"
															id="online_result"
															value="1"
															placeholder="Online Result"
															<?= (!empty($academicdetails->online_result) && $academicdetails->online_result == 1) ? 'checked' : '' ?>>
													</div>
												</div>
												<?php
												foreach ($profile_section["12_stage"] as $s_stage) {
													$doc_type = $s_stage["name"] ?? '';
													$doc_id = $s_stage["id"] ?? '';
													$info = $s_stage["info"] ?? '';
													$accept = $s_stage["file_type"] ?? '';
													$is_mandatory = !empty($s_stage["mandatry"]);
													$mandatry_text = $is_mandatory ? "<small class='text-danger'></small>" : '';
													$required_attr = $is_mandatory ? "required required-check" : '';
													$file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
													$required_attr = !empty($file_url) ? "" : $required_attr;
												?>
													<div class="col-lg-3 border2 media-files  ">
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
									<div id="entrance_exam_div" class="row accadmic-education-div  ">
										<h4>NEET Exam</h4>
										<hr>
										<div class="col-lg-12 row hide">
											<h4 class="col-lg-12">NEET Credentials</h4>
											<div class="col-lg-3 border2 border1">
												<div class="c1">
													<p>Id</p>
												</div>
												<div class="c2">
													<!--<input class="form-control" type="text" class="form-group"-->
													<!--	placeholder="Enter Neet User ID" name="neet_user_id" value="<?= $academicdetails->neet_user_id; ?>">-->
												</div>
											</div>

											<div class="col-lg-3 border2 border1">
												<div class="c1">
													<p>Password</p>
												</div>
												<div class="c2">
													<!--<input class="form-control" type="text" class="form-group"-->
													<!--	placeholder="Enter Neet User Password" name="neet_user_password" value="<?= $academicdetails->neet_user_password; ?>">-->
												</div>
											</div>

										</div>
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

										<div class="col-lg-3 border2 border1 hide_ " style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Without Neet') ? 'none' : '' ?>">
											<div class="c1">
												<p>Registration Number <?= $text_danger_mbbs ?></p>
											</div>
											<div class="c2">
												<input class="form-control " required-check type="number" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'readonly' : ''; ?> class="form-group" pattern="\d{12}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
													minlength="12" maxlength="12" placeholder="Enter Entrance Roll No" name="entrance_roll" value="<?= $academicdetails->entrance_roll; ?>">
											</div>

										</div>
										<div class="col-lg-3 border2 border1 hide_" style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Without Neet') ? 'none' : '' ?>">
											<div class="c1">
												<p>Year <?= $text_danger_mbbs ?></p>
											</div>
											<div class="c2">
												<!-- <input class="form-control" type="text" placeholder="Enter Entrance Year" name="entrance_year" value="<?= $academicdetails->entrance_year; ?>"> -->
												<!--<input type="number" required-check name="entrance_year" min="2000" max="2025" step="1" placeholder="YYYY"  id="entrance_year" class="form-control" value="<?= ($academicdetails->entrance_year) ? $academicdetails->entrance_year : '' ?>" placeholder="Select Month and Year">-->


												<?php
												$selected = [];
												$selected[] = ($academicdetails->entrance_year) ? extractYear($academicdetails->entrance_year) : '';
												echo render_select('entrance_year', $years_array_entrance, array('year', 'year'), "", $selected, ["required" => "required", "required-check" => "required-check", "readonly" => "<?= ($academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Without Neet') ? 'true' : 'false'; ?>"], [], "", "", "", "entrance_year");
												?>
											</div>

										</div>

										<div class="col-lg-3 border2 border1 hide_ " style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Without Neet') ? 'none' : '' ?>">
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

										<div class="col-lg-3 border2 border1 hide_" style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Without Neet') ? 'none' : '' ?>">
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
												["onchange"=>"checkNeetStatus()"],
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
											$mandatry_text = $is_mandatory ? "<small class='text-danger'></small>" : '';
											$required_attr = $is_mandatory ? "required required-check" : '';
											$file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';
											$required_attr = !empty($file_url) ? "" : $required_attr;
										?>

											<div class="col-lg-3 border2 border1 media-files hide_ " style="display: <?= ($academicdetails->entrance_result_status == 'Awaited' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Not Appeared' || $academicdetails->entrance_result_status == 'Without Neet') ? 'none' : '' ?>">
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
						<div class="btn-save-fun <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id())?'':'hide' ?>">
							<div class="col-md-12">
								<button type="submit" onclick="save_admission_details()" class="btn btn-primary button-22 pull-right">Save changes</button>
							</div>
						</div>
					</form>
				</div>

				<div role="tabpanel" class="tab-pane" id="documents">
					<div class="">
						<div class="col-md-12">
							<form id="documents-form" onsubmit="return false;">
								<div class="row">
									<h4>Documents Required <small class="text-danger"></small></h4>
									<hr>
								 <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">S.No</th>
                                            <th scope="col">Document Type</th>
                                            <th>MAX file size</th>
                                            <th scope="col">Stage</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Upload</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="document_upload_div">

                                        <?php

                                        if (!empty($documents_type)) : ?>
                                            <?php
                                            $index = 1;
                                            foreach ($documents_type as $key => $doc_files) :

                                                $whatsapp_message_status = $doc_files["whatsapp_message"]??0;
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
                                                $upload_assign = [];

                                                if (!empty($doc_files['upload_assign'])) {
                                                    if (is_array($doc_files['upload_assign'])) {
                                                        $upload_assign = $doc_files['upload_assign'];
                                                    } else {
                                                        $upload_assign = explode(",", (string)$doc_files['upload_assign']);
                                                    }
                                                }




                                            ?>
                                                <tr>
                                                    <td><?= ($index) ?></td>
                                                    <td>
                                                        <input type="hidden" name="doc_type[]" value="<?= htmlspecialchars($doc_id, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="doc_url[]" value="<?= htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8') ?>">
                                                        
                                                         <input type="hidden" name="doc_whatsaapStatus[]" value="<?= htmlspecialchars($whatsapp_message_status, ENT_QUOTES, 'UTF-8') ?>">
                                                        
                                                        


                                                        <?= htmlspecialchars($doc_type, ENT_QUOTES, 'UTF-8') . ' ' . $mandatry_text  . "  (" . $doc_files["file_type"] . ")" ?>
                                                        <?php if (!empty($info)) : ?>
                                                            &nbsp;<i class="fa fa-info-circle" title="<?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                     <td>
                                                        <?= $doc_files["media_size"]?$doc_files["media_size"].' MB':'' ?>
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

                                                       
                                                        
                                                        
                                                                                                               <?php
// ---- Precompute state (readable, single source of truth) ----
$is_privileged   = is_admin() || has_permission('customers', '', 'applicant_doc_upload') ||  !empty($staff_list[get_staff_user_id()]["post_sales"]);
$is_doc_disabled = !empty($doc_files['disabled']) && $doc_files['disabled'] == 1;
$is_approved     = ($status === "Approved");
 
// Staff is NOT in the allowed uploader list
$not_assigned = !is_admin()
    && !empty($upload_assign)
    && !in_array(get_staff_user_id(), $upload_assign);
 
if ($is_privileged) {
    // Privileged users: never hard-disabled; only soft "disabledd" class
    $disabled_attr = '';
    $disabled_cls  = ($is_doc_disabled && $not_assigned) ? 'disabledd' : '';
} else {
    // Non-privileged users
    $disabled_attr = ($is_doc_disabled || $is_approved) ? 'disabled' : '';
    $disabled_cls  = ($is_doc_disabled || (!$not_assigned && $is_approved)) ? 'disabledd' : '';
}
?>
<input
    type="file"
    name="files[<?= $doc_id ?>]"
    value="<?= $file_url ?>"
    class="form-control <?= $disabled_cls ?>"
    onchange="updateDate(this, <?= $doc_files['upload_date'] ?>,<?= $doc_files["media_size"]?$doc_files["media_size"]:'' ?>)"
    accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
    <?= $disabled_attr ?>
    <?= $required_attr ?>>


                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        if (!empty($file_url)) {
                                                        ?>
                                                            <div class="margin-top">
                                                                <?php if (!empty($doc_files['upload_date']) && $doc_files['upload_date'] != '') { ?>
                                                                    <i class="fa fa-calendar  btn btn-xs btn-primary" onclick="$('#sample_collect_modal').modal('show');"></i>
                                                                <?php } ?>
                                                                <i class="fa fa-eye  btn btn-xs btn-primary" onclick="show_media_files('<?= base_url($file_url) ?>');"></i>
                                                                <i class="fa fa-download  btn btn-xs btn-primary" onclick="download_media_files(`<?= base_url($file_url) ?>`, '_blank');"></i>
                                                            </div>
                                                        <?php
                                                        }
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
								<div class="row <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id() || has_permission('customers', '', 'applicant_doc_upload') )?  '':'hide' ?>">
									<div class="col-md-12">
										<button type="submit" onclick="save_documents()" class="btn btn-primary button-22 pull-right hide-btn ">Save changes</button>
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
								<form id="welcome-information-form" class="form-disabled" onsubmit=" return false;">
									<?php

									$get_clients_fees = get_clients_fees_details($lead_type_status, $client_id, REGISTRATION_AMOUNT_ID);
									$registration_amount = !empty($get_clients_fees[0]["total_amount"]) ? $get_clients_fees[0]["total_amount"] : 0;
									?>

									<div class="row">
										<div class="col-lg-4">
											<label class="form-check-label">Registration Amount Cash Deposite
												<input type="checkbox" value="<?= !empty($client->registration_slip_cash_status) && $client->registration_slip_cash_status == 1 ? 1 : 0 ?>" class="form-check-input" onclick="check_registration_cash_status(this,'hide-show-regi')" <?= !empty($client->registration_slip_cash_status) && $client->registration_slip_cash_status == 1 ? 'checked' : '' ?> name="registration_slip_cash_status" <?= !empty($client->registration_slip_cash_status && $client->registration_slip_cash_status == 1) ? 'checked' : '' ?>>

											</label>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="exampleInputMiddleName">Date of payment <small class="text-danger"></small></label>
												<input class="form-control" type="date" name="date_of_payment" value="<?= $client->date_of_payment ?>">
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="exampleInputMiddleName">Regisration amount <small class="text-danger"></small> </label>
												<input class="form-control" disabled type="text" value="<?= $registration_amount ?>">
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="exampleInputMiddleName">Payment received from <small class="text-danger"></small></label>
												<input class="form-control" type="text" onkeypress="return acceptText(this,'text')" name="payment_recevied_from" value="<?= !empty($client->payment_recevied_from) ? $client->payment_recevied_from : '' ?>">
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="exampleInputMiddleName">Quotation <small class="text-danger"></small></label>
												<input <?= !empty($client->quotation) ? '' : '' ?> class="form-control" type="file" accept=".pdf, image/" name="quotation" value="">
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
												<label for="exampleInputMiddleName">Fees Structure <small class="text-danger"></small></label>
												<input <?= !empty($client->fees_structure) ? '' : '' ?> class="form-control" type="file" accept=".pdf, image/" name="fees_structure" value="">
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
												<label for="exampleInputMiddleName">Registration Proof <small class="text-danger"></small></label>
												<input <?= !empty($client->registration_slip) ? '' : '' ?> class="form-control" type="file" accept=".pdf, image/" name="registration_slip" value="">
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
									<div class="row btn-save-fun <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id())?'':'hide' ?>">
										<div class="col-md-12 ">
											<button type="submit" onclick="save_welcome_info()" class="btn btn-primary button-22 pull-right">Save changes</button>
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
													$mandatry = !empty($fees["mandatry"]) ? "<small class='text-danger'></small>" : "";
													$disabled = (strtolower($admissionpreferences->primary_country) == 'georgia') && $id == 6 ? 'disabled' : '';

												?>
												 <div class="col-lg-4 col-md-4 col-6 fees-block-<?= $id ?>">
                                                    <label for="<?= $field_name ?>" ><?= $fees['name'] ?> <?= $mandatry ?><span class="fees_label_<?= $id ?>"></span></label><br>
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
                                                                 $symbol = $get_currencies[$fees["default_currency"]]["symbol"]??$get_currencies[1]["symbol"];
                                                            }
                                                            ?>

                                                            <?= $symbol ?>


                                                        </div>
                                                        <input <?= $disabled ?> type="text" name="<?= $field_name ?>" <?= $required ?> class="form-control currency-amount fees_<?= $fees['id'] ?>  <?= $field_name ?>" placeholder="0.00" id="<?= $field_name ?>" value="<?= $fees["amount"] ?>" size="8" onkeyup="checkHostalCapacity(this.value,<?= $fees["show_hostel_capacity"] ?>)" onkeypress="return acceptText(this,'number')">
                                                        <div class="input-group-addon currency-addon">

                                                            <select <?= $disabled ?> name="<?= $field_name ?>_currency_type" id="<?= $field_name ?>" class="currency-selector <?= $disabled ?> currency-selector-<?= $id ?>  <?= $field_name ?>" onchange="updateSymbol(<?= $id ?>)">
                                                                <?php foreach ($get_currencies as $c) {
                                                                    if($fees["default_currency"] == $c["id"] ){
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
                                                                    
                                                              if (empty($fees["default_currency"])) { ?>
    <option
        data-symbol="<?= $c['symbol'] ?>"
        value="<?= $c['id'] ?>"
        data-placeholder="0.00"
        <?= (!empty($fees['currency_id']) && $fees['currency_id'] == $c['id']) || ($c['id'] == 1 && empty($fees['currency_id'])) ? 'selected' : '' ?>>
        <?= $c['name'] ?>
    </option>
<?php } 
                                                                }
                                                                ?>

                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>
												<?php
												}
												?>
												<div class="col-lg-4 col-md-4 col-6 fees-block-8 room-capacity-secton <?= !empty($client->hostel_capacity) && $client->hostel_capacity > 0 ? '' : 'hide' ?>">
												    <div class="form-group">
											<?php
$hostelRoom = [];

$hostelRoom[] = [
    "id"   => "",
    "value" => "",
    "name" => "Select Room Capacity"
];

for ($i = 2; $i <= 6; $i++) {
    $hostelRoom[] = [
        "id"    => $i,
        "value" => $i,
        "name"  => $i
    ];
}

$selected_hostel = [
    !empty($client->hostel_capacity)
        ? $client->hostel_capacity
        : ''
];

echo render_select(
    'hostel_capacity',
    $hostelRoom,
    ['id', 'name'],
    'Hostel Capacity',
    $selected_hostel,
    [
        "required" => "required",
        "required-check" => "required-check"
    ],
    [],
    "",
    "",
    "",
    "agent_id"
);
?>
											</div>
												</div>
												
												<div class="col-lg-4 col-md-4 col-6 fees-block-8">
													<label>&nbsp;</label>

													<div class="form-check checkbox">
														<input
															class="form-check-input checkbox-group <?= (strtolower($admissionpreferences->primary_country) != "georgia") ? 'disabledd' : '' ?>"
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
									
									 <div class="card scholarship-details margin-top hide">
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
                                            <label>Scholarship Amount <small class='text-danger'>*</small></label>
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

                                                <input type="text" name="scholarship_amount" required class="form-control scholarship_amount" placeholder="0.00" id="scholarship_amount" value="<?= $client->scholarship_amount ?>" size="8">

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
    <label for="scholarship_reason_id">
        Scholarship Justification <small class="text-danger">*</small>
    </label>

    <select
        name="scholarship_reason_id"
        id="scholarship_reason_id"
        class="form-control selectpicker"
        data-live-search="true"
        required 
        <?= !empty($final_sumbit) ? 'disabled' : '' ?>>

        <option value="">Select Scholarship Justification</option>

        <?php 
        if(!empty($scholarshipsData)){
        foreach ($scholarshipsData as $scholarship) { print_r($scholarship); ?>
            <option
                value="<?= $scholarship['id']; ?>"
                <?= (!empty($client->scholarship_reason_id) && $client->scholarship_reason_id == $scholarship['id']) ? 'selected' : ''; ?>>
                <?= $scholarship['text'] ?>
            </option>
        <?php } } ?>

    </select>
</div>

                                        <div class="col-lg-8 hide">
                                            <label for="scholarship_reason">Scholarship Justification <small class='text-danger'>*</small></label>
                                            <textarea name="scholarship_reason" required id="scholarship_reason" class="form-control" rows="3" <?= !empty($final_sumbit) ? 'disabled' : '' ?>><?= !empty($client->scholarship_reason) ? $client->scholarship_reason : '' ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
								</div>
								<div class="row btn-save-fun <?=(has_permission('customers', '', 'edit') || is_postSale() || $client->addedfrom == get_staff_user_id())?'':'hide' ?>">
									<div class="col-md-12 ">
										<button type="submit" onclick="fees_details()" class="btn btn-primary button-22 pull-right">Save changes</button>
									</div>
								</div>
							</form>
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
				<div role="tabpanel" class="tab-pane" id="final-form">
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<br>
								<br>
								<form id="final-form" onsubmit="return false;" class="<?= !empty($final_sumbit) ? 'hide' : '' ?>">
									<div class="row">
										<div class="col-md-12">
											<button type="submit" onclick="final_submission()" class="btn btn-primary button-22 pull-right">Final Submit</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

			<?php } else {

				echo "<div class='hide'>" . render_select('lead_type', $lead_type, array('id', 'name'), "", $lead_type_status, [], [], "", "", "", "lead_type") . "</div>";
			} ?>

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
</div>
</div>
</div>
<div class="panel_s mt-5">
    <div class="panel-body">
        <h4 class="customer-profile-group-heading">Activity Log</h4>
        <div class="lead-activity activity-feed" style="height:400px; overflow:scroll;">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>
</div>

<div class="modal fade" id="sample_collect_modal" data-backdrop="static" data-backdrop="true" tabindex="-1" role="dialog">
    <form method="POST" onsubmit="return false;" id="sample_form">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Sample Collect Date</h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label for="sample_collect_date">Sample Collect Date</label>
                        <input type="date" class="form-control"
                            name="sample_collect_date" value="<?= !empty($client->sample_collect_date) ? $client->sample_collect_date : '' ?>"
                            id="sample_collect_date" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" onclick="checkCollectionDate()" class="btn btn-info">
                        Save
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>


<script>
      var activity_url = "<?= base_url() ?>admin/clients/activity_logs/<?= $client_id ?>";
    var isCounsollor = <?= (!is_admin() && empty($staff_list[get_staff_user_id()]['post_sales'])) ? 1 : 0 ?>;
    var university_priority_array = <?= json_encode(
    !empty($admissionpreferences->university_priority) &&
    $admissionpreferences->university_priority !== 'null'
        ? json_decode($admissionpreferences->university_priority, true)
        : []
); ?>;
   var getClientsFees = <?= json_encode($get_clients_fees, JSON_UNESCAPED_UNICODE) ?>;

	document.addEventListener("DOMContentLoaded", function() {
		var documentAccessOnly = "<?= !empty($documentAccessOnly) ? $documentAccessOnly : 0 ?>";
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


 function toggleLoanType() {

        if ($("#loan_required").val() == '1') {
    $(".loan_type_div").removeClass('hide');
    $(".loan_type_div select, .loan_type_div input").prop("required", true);

} else {
    $(".loan_type_div").addClass('hide');
    $(".loan_type_div select, .loan_type_div input").prop("required", false);
}
    }
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





	<?php if (!empty($client_id)) {
	?>
		document.getElementById("passport_number").addEventListener("input", function() {
			this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Convert to uppercase & remove invalid characters
		});
	<?php
	}
	?>

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

	function updateSymbol(id) {
		var selected = $(".currency-selector-" + id + " option:selected");
		$(".currency-symbol-" + id).text(selected.data("symbol"));
	}
	
	   let canEditRussiaPackage = <?= (get_staff_user_id() == 89 || is_admin() || !empty($staff_list[get_staff_user_id()]["post_sales"])) ? 'true' : 'false' ?>;
    
      function checkCollectionDate() {
        var selectedDate = $('#sample_collect_date').val();
        if (!selectedDate) {
            alert('Please select a date.');
            return;
        }

        $('#sample_collect_modal').modal('hide');
        // You can also perform additional actions here, such as sending the selected date to the server
    }
    
    
    
    
// function checkFeesDisable()
// {
    
//      let country = ($("#primary_country").val() || "").toLowerCase();
//     let university = ($("#primary_university").val() || "").toLowerCase();

//     let allowedUniversities = [
//         "smolensk state medical university",
//         "izhevsk state medical academy"
//     ];
    
    
// if ($("#partner_type option:selected").val() != undefined && $("#partner_type option:selected").val() == 1) {
//      $("#total_service_charge")
//         .removeAttr("required")
//         .removeAttr("required-check").selectpicker("refresh");
        
//         $("#registration_amount")
//         .removeAttr("required")
//         .removeAttr("required-check").selectpicker("refresh");
        
        
//         $("#medical_insurance")
//         .removeAttr("required")
//         .removeAttr("required-check").selectpicker("refresh");
        
        
// }

//     $(".info-details-icon").remove();


//     if(canEditRussiaPackage)
//     {
//           if (country === "georgia")
//     {
//         $('label[for="medical_insurance"]')
//             .append(
//                 ' <i class="medical-info info-details-icon fa fa-info-circle" title="Medical Insurance package includes Medical Insurance, TRC and Ministry charges"></i>'
//             ); 
//     }
//     if (
//         ["uzbekistan", "kazakhstan", "kyrgyzstan"].includes(country)
//     ) {
//          $('label[for="university_package"]')
//             .append(
//                 ' <i class="package-info info-details-icon fa fa-info-circle" title="Complete package with Tution fee, Hostel, One Time Charge, Documentation and Visa Extension"></i>'
//             );
//     }
//         return false;
//     }
  

   
    

//     // Reset all fields first
//     $("#university_package,#one_time_charge,#medical_insurance,#ev_hostel,#ev_mess")
//         .prop("disabled", false);

//     // Russia
//     if (
//         country === "russia" &&
//         !allowedUniversities.includes(university)
//     ) {
//         $("#university_package")
//             .prop("disabled", true)
//             .selectpicker("refresh");
//     }

//     // Georgia
//     if (country === "georgia") {

//         $("#university_package,#ev_mess")
//             .prop("disabled", true)
//             .selectpicker("refresh");

//         $('label[for="medical_insurance"]')
//             .append(
//                 ' <i class="medical-info info-details-icon fa fa-info-circle" title="Medical Insurance package includes Medical Insurance, TRC and Ministry charges"></i>'
//             );
//     }

//     // Uzbekistan / Kazakhstan / Kyrgyzstan
//     if (
//         ["uzbekistan", "kazakhstan", "kyrgyzstan","bangladesh","nepal"].includes(country)
//     ) {
//         $("#one_time_charge,#medical_insurance,#ev_hostel")
//             .prop("disabled", true)
//             .selectpicker("refresh");

//         $('label[for="university_package"]')
//             .append(
//                 ' <i class="package-info info-details-icon fa fa-info-circle" title="Complete package with Tution fee, Hostel, One Time Charge, Documentation and Visa Extension"></i>'
//             );
//     }

//     // Smolensk & Izhevsk - OTC disabled
//     if (allowedUniversities.includes(university)) {

//         $("#one_time_charge")
//             .prop("disabled", true)
//             .selectpicker("refresh");

//         $('label[for="one_time_charge"]')
//             .append(
//                 ' <i class="otc-info info-details-icon fa fa-info-circle" title="OTC Disabled"></i>'
//             );
//     }

//     $(".selectpicker").selectpicker("refresh");
// }

// function checkFeesDisable()
// {
//     if(final_sumbit==1)
//     {
//         if(!canEditRussiaPackage){
//         return false;
//         }
//     }
//      let country = ($("#primary_country").val() || "").toLowerCase();
//     let university = ($("#primary_university").val() || "").toLowerCase();

//     let allowedUniversities = [
//         "smolensk state medical university",
//         "izhevsk state medical academy"
//     ];
    

//     $(".info-details-icon").remove();


//     if(canEditRussiaPackage)
//     {
//           if (country === "georgia")
//     {
//         $('label[for="medical_insurance"]')
//             .append(
//                 ' <i class="medical-info info-details-icon fa fa-info-circle" title="Medical Insurance package includes Medical Insurance, TRC and Ministry charges"></i>'
//             ); 
//     }
//     if (
//         ["uzbekistan", "kazakhstan", "kyrgyzstan"].includes(country)
//     ) {
//          $('label[for="university_package"]')
//             .append(
//                 ' <i class="package-info info-details-icon fa fa-info-circle" title="Complete package with Tution fee, Hostel, One Time Charge, Documentation and Visa Extension"></i>'
//             );
//     }
//         return false;
//     }
  

   
    

//     // Reset all fields first
//     $("#university_package,#one_time_charge,#medical_insurance,#ev_hostel,#ev_mess")
//         .prop("disabled", false);

//     // Russia
//     if (
//         country === "russia" &&
//         !allowedUniversities.includes(university)
//     ) {
//         $("#university_package")
//             .prop("disabled", true)
//             .selectpicker("refresh");
            
            
//              $('label[for="medical_insurance"]')
//             .append(
//                 ' <i class="otc-info info-details-icon fa fa-info-circle" title="Medical Insurance includes --  Medical Test and visa Extension"></i>'
//             );
            
//     }

//     // Georgia
//     if (country === "georgia") {

//         $("#university_package,#ev_mess")
//             .prop("disabled", true)
//             .selectpicker("refresh");

//         $('label[for="medical_insurance"]')
//             .append(
//                 ' <i class="medical-info info-details-icon fa fa-info-circle" title="Medical Insurance package includes Medical Insurance, TRC and Ministry charges"></i>'
//             );
//     }

//     // Uzbekistan / Kazakhstan / Kyrgyzstan
//     if (
//         ["uzbekistan", "kazakhstan", "kyrgyzstan","bangladesh"].includes(country)
//     ) {
//         $("#one_time_charge,#medical_insurance,#ev_hostel")
//             .prop("disabled", true)
//             .selectpicker("refresh");

//         $('label[for="university_package"]')
//             .append(
//                 ' <i class="package-info info-details-icon fa fa-info-circle" title="Complete package with Tution fee, Hostel, One Time Charge, Documentation and Visa Extension"></i>'
//             );
//     }

//     // Smolensk & Izhevsk - OTC disabled
//     if (allowedUniversities.includes(university)) {

//         $("#one_time_charge")
//             .prop("disabled", true)
//             .selectpicker("refresh");
            
//             $("#medical_insurance")
//             .prop("disabled", true);
            
//             $("#ev_hostel")
//             .prop("disabled", true);

//         // $('label[for="one_time_charge"]')
//         //     .append(
//         //         ' <i class="otc-info info-details-icon fa fa-info-circle" title="OTC Disabled"></i>'
//         //     );
//     }

//     $(".selectpicker").selectpicker("refresh");
// }
var neetStatus = <?= json_encode(!empty($neet_status) ? array_column($neet_status, null, 'id') : []) ?>;

function checkNeetStatus() {
    let selectedValue = $("#neet_status").val();
 let fileValue = $('#entrance_exam_div [name="doc_url[]"]').val();
    // Check if the selected value exists in neetStatus object
    if (!neetStatus[selectedValue]) return;

    // Determine if validation should be disabled (mandatory = 0) or enabled (mandatory = 1)
    let enableValidation = parseInt(neetStatus[selectedValue].mandatry) === 1;

    // Target all input and select elements (except the neet_status itself)
    $("#entrance_exam_div .hide_")
        .find("input, select")
        .not("#neet_status")
        .each(function () {
            let $field = $(this);
            let originalClasses = $field.data("validation") || "";
            let wasRequired = $field.data("required") == 1;

            if (!enableValidation) {
                // Remove validation
                $field.removeClass("required required-check");
                $field.removeAttr("required-check");
                // Optionally remove other validation attributes
                $field.removeAttr("data-required");
            } else {
               
                if ($(this).is('input[type="file"]') && fileValue !== "") {
                    console.log("set validation");
     $field.removeClass("required required-check");
                $field.removeAttr("required-check");
                // Optionally remove other validation attributes
                $field.removeAttr("data-required");
}
else{
                // Add validation
                $field.addClass("required required-check");
                
                // Only add original classes if they exist
                if (originalClasses) {
                    $field.addClass(originalClasses);
                }

 $field.attr("required-check", true);
                // Set required-check attribute based on original required state
                
            }
            }
        });

    // Refresh select picker for all select elements (except neet_status)
    $("#entrance_exam_div .hide_")
        .find("select")
        .not("#neet_status")
        .selectpicker("refresh");
}


   function checkFeesDisable()
{
    
      
    if(canEditRussiaPackage){
        return false;
        }

  
    
    let country = ($("#primary_country").val() || "").toLowerCase();
    
    getClientsFees.forEach(function (fee) {

    // "One time Charge" -> "one_time_charge"
    let feeName = fee.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_|_$/g, "");

    let disabledCountries = [];
    let infoData = {};

    // Disabled countries
    if (fee.disabled_country) {
        try {
            disabledCountries = JSON.parse(fee.disabled_country)
                .map(c => c.toLowerCase());
        } catch (e) {
            disabledCountries = [];
        }
    }

    // Info JSON
    if (fee.info) {
        try {
            infoData = JSON.parse(fee.info);
        } catch (e) {
            infoData = {};
        }
    }

    // Enable by default
    $('.' + feeName).prop('disabled', false).selectpicker('refresh');
    

    // Disable if country matches (case-insensitive)
    if (disabledCountries.includes(country)) {
        $('.' + feeName).prop('disabled', true).selectpicker('refresh');;
    }

    // Find country key ignoring case
    let countryKey = Object.keys(infoData).find(
        key => key.toLowerCase() === country
    );

    if (countryKey) {
        console.log(infoData[countryKey]);

        // Loop through all fields for that country
        Object.keys(infoData[countryKey]).forEach(function (key) {
            console.log(key);                    // e.g. university_package
            console.log(infoData[countryKey][key]); // Description

            // Example: add/update tooltip
            $('label[for="' + key + '"] .package-info').remove();

            $('label[for="' + key + '"]').append(
                ' <i class="package-info info-details-icon fa fa-info-circle" title="' +
                infoData[countryKey][key] +
                '"></i>'
            );
        });
    }

});

$(".selectpicker").selectpicker("refresh");
}


var ReferralDropdownSelection = <?=json_encode(array_column($ev_type,null,'id'),true)??[]?>;


function partnerTypeChange(element) {
    // Validate element
    if (!element || typeof element.value === "undefined") {
        console.error("Invalid element.");
        return;
    }

    const selectedValue = parseInt(element.value, 10);

    // Reset and hide the dropdown
    $("#referralCounsollorDiv")
        .addClass("hide").find("select")
        .val("")
        .selectpicker("refresh");

    // Validate selected value
    if (isNaN(selectedValue) || selectedValue <= 0) {
        return;
    }

    // Validate ReferralDropdownSelection
    if (
        typeof ReferralDropdownSelection !== "object" ||
        !ReferralDropdownSelection[selectedValue]
    ) {
        console.error("Invalid referral configuration.");
        return;
    }

    const checkType = ReferralDropdownSelection[selectedValue];

    // Show dropdown only if required
    if (Number(checkType.ReferralDropdown) === 1) {
        $("#referralCounsollorDiv")
            .removeClass("hide")
    }
}


  function apply_new_passport() {
        const checkbox = document.getElementById('new_passport');
        $(".new-passport-info input").val('');
        if (checkbox.checked) {
            $(".new-passport-info").show();
            // console.log('New passport applied');
        } else {
            // console.log('New passport not applied');
            $(".new-passport-info").hide();
        }
    }
    
        function handleActivityChange() {
        checkFeesDisable();
        console.log("start activity");

        var selectedType = 4;

        // Safely get the active section
        var section = $(".profile-tabs li.active").attr("section") || "";

        console.log("section:", section);

        // Make sure activity_url exists
        if (typeof activity_url === "undefined") {
            console.error("activity_url is not defined");
            return;
        }

        reloadActivity_list(activity_url, {
            type: selectedType,
            section: section
        });
    }
    
        function reloadActivity_list(url, postData = {}) {
        $(".lead-activity").html('');
        // show_loader();
        $.ajax({
            url: url,
            type: 'POST',
            data: postData,
            success: function(data) {
                $(".lead-activity").html(data);
                hide_loader();
            },
            error: function(xhr, status, error) {
                hide_loader();
                $(".lead-activity").html("Error loading data");
                console.error("Error loading data:", error);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        handleActivityChange();
        checkNeetStatus();
    });
    
    
        function scholarshipCase(event) {
        if (event.checked) {
            $(".scholarship-case").removeClass("hide");
        } else {
            $(".scholarship-case").addClass("hide");
            $(".scholarship-case").find("input, select, textarea").val("");
        }
    }
    


</script>