<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<?php

$documents_data = $documents;
$document_files = !empty($documents_data[0]["documents"]) ? json_decode($documents_data[0]["documents"], true) : '';


if (!empty($score_value)) {
	$score_value = array_column($score_value, null, "type");
}


?>

<style type="text/css">
	.card {
		border: 1px solid #efefef;
		padding: 10px;
	}

	.heading {
		font-size: 25px;
		font-weight: 600;
	}

	.btn-primary {
		color: #fff;
		background-color: #337ab7;
		border-color: #2e6da4;
		height: 40px;
		border-radius: initial;
	}

	.button-22 {
		background: #415165;
		border-color: #415165;
	}
</style>
<style type="text/css">
	.c1 {
		border: 1px solid #cecbcb;
		padding: 5px;
		padding: 10px;
		height: 55px;
	}

	.c2 {
		border: 1px solid #cecbcb;
		padding: 5px;
		padding: 10px;
		height: 55px;
	}

	.border1 {
		padding-left: 0px;
	}

	.border2 {
		padding-right: 0px;
	}

	.after {
		margin-top: 20px;
	}

	.btn-primary {
		color: #fff;
		background-color: #337ab7;
		border-color: #2e6da4;
		height: 40px;
		border-radius: initial;
	}

	p {
		font-size: 15px;
		font-weight: 600;
	}

	.heading {
		font-size: 25px;
		font-weight: 600;
	}

	.button-23 {
		background: #415165;
		border-color: #415165;
	}

	@media only screen and (max-width: 600px) {
		.c1 {
			border: none;
			margin-bottom: -24px;
		}
	}

	@media only screen and (max-width: 600px) {
		.c2 {
			border: none;
			margin-bottom: -10px;
		}
	}

	.card {
		border: 1px solid #d2d2d2;
		padding-left: 25px;
		padding-right: 25px;
	}


	.tags-input-wrapper {
		background: transparent;
		padding: 10px;
		border-radius: 4px;
		max-width: 400px;
		border: 1px solid #ccc;
		pointer-events: <?php echo $admissionpreferences->freeze == 1 ? 'none' : '' ?>
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

	.tags-input-wrapper input {
		display: none;
	}

	.tags-input-wrapper .tag a {
		display: none;
	}
</style>
</head>

<body>

	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="heading">Final Preview</h2>
			</div>
			<div class="col-lg-12">
				<h4 style="color:green"><?php echo $this->session->flashdata('success'); ?></h4>
			</div>

		</div>
	</div>
	<!-- basic details section -->
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<?php // echo form_open($this->uri->uri_string()); 
					?>
					<h4>Personal Informations</h4>
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group">
								<label for="title">Title</label>
								<select class="form-control" name="title" id="title" readonly>
									<option value="Mr" <?php echo ($basicdetails->title == 'Mr') ? 'selected' : ''; ?>>Mr</option>
									<option value="Ms" <?php echo ($basicdetails->title == 'Ms') ? 'selected' : ''; ?>>Ms</option>
									<option value="Mrs" <?php echo ($basicdetails->title == 'Mrs') ? 'selected' : ''; ?>>Mrs</option>
									<option value="Dr" <?php echo ($basicdetails->title == 'Dr') ? 'selected' : ''; ?>>Dr</option>
								</select>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputFirstName">First Name</label>
								<input class="form-control" type="text" class="form-group" placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->first_name : $contact->firstname; ?>' readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputLastName">Last Name</label>
								<input class="form-control" type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->last_name : $contact->lastname; ?>' readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputEmail">Email Address</label>
								<input class="form-control" type="text" class="form-group" placeholder="Email Address" name="email" value='<?php echo (isset($basicdetails)) ? $basicdetails->email : $contact->email; ?>' readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputMobileNumber">Mobile Number</label>
								<input class="form-control" type="text" class="form-group" placeholder="Mobile Number" name="mobile" value='<?php echo (isset($basicdetails)) ? $basicdetails->mobile : $contact->phonenumber; ?>' readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputDateOfBirth">Date Of Birth</label>
								<input type="date" class="form-control" name="dob" value='<?php echo ($basicdetails->dob != '') ? $basicdetails->dob : ''; ?>' required="required" readonly>
								<?php echo form_error('dob'); ?>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Gender</label>
								<select class="form-control" name="gender" id="gender" required readonly>
									<option value="">Select</option>
									<option <?php echo ($basicdetails->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
									<option <?php echo ($basicdetails->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
									<option <?php echo ($basicdetails->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
								</select>
								<?php echo form_error('gender'); ?>

							</div>
						</div>
					</div>

					<h4>Parent Details</h4>
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Father Name</label>
								<input type="text" name="father_name" class="form-control" id="father_name" value="<?php echo $basicdetails->father_name; ?>" readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Mother Name</label>
								<input type="text" name="mother_name" class="form-control" id="mother_name" value="<?php echo $basicdetails->mother_name; ?>" readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Father's Mobile Number</label>
								<input type="text" name="fathers_mobile" class="form-control" id="fathers_mobile" value="<?php echo $basicdetails->fathers_mobile; ?>" readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Mother's Mobile Number</label>
								<input type="text" name="mothers_mobile" class="form-control" id="mothers_mobile" value="<?php echo $basicdetails->mothers_mobile; ?>" readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Father's Email Number</label>
								<input type="text" name="fathers_email" class="form-control" id="fathers_email" value="<?php echo $basicdetails->fathers_email; ?>" readonly>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Mother's Email Number</label>
								<input type="text" name="mothers_email" class="form-control" id="mothers_email" value="<?php echo $basicdetails->mothers_email; ?>" readonly>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6 col-xs-6">
							<a href="<?= base_url() ?>/clients/basic_details" class="btn btn-primary button-23 pull-right">Edit Basic Details</a>
						</div>
						<div class="col-lg-6 col-xs-6">
							<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
							<?php if ($basicdetails->id > 1) { ?>
								<!-- <a href="<?= base_url() ?>/clients/parent_details" class="btn btn-primary button-23 pull-right">Next</a> -->
							<?php } ?>

						</div>
					</div>
					<?php // echo form_close(); 
					?>
				</div>
			</div>
		</div>
	</div>
	<!-- end basic details section -->
	<!-- admission preferences section -->
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<?php // echo form_open($this->uri->uri_string()); 
					?>
					<h4>Admission Preferences</h4>
					<?php $freezed = $admissionpreferences->freeze == 1 ? 'disabled' : ''; ?>
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label for="program">Program</label>
								<input type="hidden" name="basicDetailsId" value="<?= $admissionpreferences->id ?>">
								<select class="form-control" name="program" id="program" required readonly>
									<option value="">Select a Program</option>
									<?php foreach ($program_data as $p) {
										$selected = "";
										if ($admissionpreferences->program == $p["id"]) {
											$selected = "selected";
										}
									?>
										<option value="<?= $p["id"] ?>" <?= $selected ?>><?= $p["name"] ?></option>
									<?php
									}
									?>
								</select>
								<?php echo form_error('program'); ?>
							</div>
						</div>

						<div class="col-lg-4">
							<div class="form-group">
								<label for="course">Course</label>
								<select class="form-control" name="course" id="course" readonly>
									<option value="">Select a course </option>

								</select>
								<?php echo form_error('course'); ?>
							</div>
						</div>
						<div class="col-lg-4 course_name_field" style="display:<?= !empty($admissionpreferences->course_name) ? 'block' : 'none' ?>">
							<div class="form-group">
								<label for="course_name">Course Name</label>
								<input type="text" class="form-control" name="course_name" required id="course_name" value="<?= !empty($admissionpreferences->course_name) ? $admissionpreferences->course_name : '' ?>">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label for="session_intake">Session Intake</label>
								<input type="month" class="form-control" name="session_intake" required value="<?= !empty($admissionpreferences->session_intake) ? $admissionpreferences->session_intake : '' ?>" placeholder="Select Month and Year">
							</div>
						</div>
						<?php
						$allCountriesArr_temp = explode(",", $admissionpreferences->study_country);
						$allCountriesArr = [];
						foreach ($allCountriesArr_temp as $country) {
							$allCountriesArr[$country] = $country;
						}
						?>


						<div class="col-lg-4">
							<div class="form-group">
								<label for="study_country">Where would you like to study?</label>
								<select class="form-control" name="study_country" id="study_country" multiple readonly>

									<option value="">Select country </option>
									<?php
									if (!empty($admissionpreferences)) {
										$countries = $admissionpreferences->study_country;
										$countriesArr = explode(",", $countries);
									}
									foreach ($allCountriesArr as $key => $val) {
									?>
										<option value="<?php echo $key; ?>" <?php echo !empty($admissionpreferences) ? ((in_array($val, $countriesArr)) ? 'selected' : '') : ''; ?>><?php echo $val; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group" id="entrance_exam_details_div">
								<label for="exampleInputCourse">Entrance exam details</label>
								<select class="form-control" name="entrance_exam_details" id="entrance_exam_details" readonly>
									<option value="">Select</option>


								</select>
							</div>
						</div>
					</div>


					<div class="row">
						<div class="universities">
							<?php
							if ($admissionpreferences->university != '') {
								$universitiesArr = json_decode($admissionpreferences->university, true);
								$count = 0;
								foreach ($universitiesArr as $key => $val) {
									if ($val != '') {
							?>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="university<?php echo $key ?>"><?php  ?> <?php echo $key; ?> University</label>
												<input type="text" class="form-control" name="university<?php echo $key ?>" id="university<?php echo $key ?>" value="<?php echo $val ?>" required <?php echo $freezed ?>>
											</div>
										</div>
							<?php
									}
									$count++;
								}
							}
							?>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-6 col-xs-6">
							<a href="<?= base_url() ?>/clients/admission_preferences" class="btn btn-primary button-23 pull-right">Edit Admission Preferences</a>
						</div>
						<div class="col-lg-6 col-xs-6">
							<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
							<?php if ($admissionpreferences->id > 1) { ?>
								<!-- <a href="<?= base_url() ?>/clients/parent_details" class="btn btn-primary button-23 pull-right">Next</a> -->
							<?php } ?>

						</div>
					</div>
					<?php // echo form_close(); 
					?>
				</div>
			</div>
		</div>
	</div>
	<!-- end admission preferences section -->
	<!-- Academic Details Section -->
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="heading">Academic Details</h2>
			</div>
		</div>
	</div>
	<div class="container" style="padding: 15px;">
		<div class="card">
			<?php // echo form_open($this->uri->uri_string()); 
			?>
			<div class="row">
				<!-- <h2 class="heading">10th Academic Details</h2> -->
			</div>
			<div class="row">
				<h4>10th Academic Details</h4>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>&nbsp;</p>
					</div>

					<div class="c2">
						<p>10<sup>th</sup></p>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>School Name</p>
					</div>
					<div class="c2">
						<input type="hidden" name="academicDetailsId" value="<?= $academicdetails->id; ?>">
						<input class="form-control" type="text" class="form-group" placeholder="Enter School Name" name="tenth_school_name" value="<?= $academicdetails->tenth_school_name; ?>" required>
						<?php echo form_error('academicDetailsId'); ?>

					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Board </p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" placeholder="Enter Board Name" name="tenth_board" value="<?= $academicdetails->tenth_board; ?>" required>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Year of Passing </p>
					</div>
					<div class="c2">
						<!-- <input class="form-control tenth_passing_year" type="text" placeholder="YYYY" name="tenth_passing_year" id="tenth_passing_year" value="<?= $academicdetails->tenth_passing_year; ?>"> -->
						<select class="form-control" name="tenth_passing_year" id="tenth_passing_year">
							<option value="">Select</option>
							<?php for ($i = 0; $i < 15; $i++) : ?>
								<option value="<?= date("Y") - $i; ?>" <?= ((date("Y") - $i) == $academicdetails->tenth_passing_year) ? 'selected' : '' ?>><?= date("Y") - $i; ?></option>
							<?php endfor; ?>

						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Marking Scheme</p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" class="form-group" placeholder="Enter Marking Scheme" name="tenth_marking_scheme" value=""> -->
						<select class="form-control" name="tenth_marking_scheme" required>
							<option value="">Select</option>
							<option value="Percentage" <?= ($academicdetails->tenth_marking_scheme == 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
							<option value="CGPA out of 10" <?= ($academicdetails->tenth_marking_scheme == 'CGPA out of 10') ? 'selected' : ''; ?>>CGPA out of 10</option>
							<option value="CGPA out of 9" <?= ($academicdetails->tenth_marking_scheme == 'CGPA out of 9') ? 'selected' : ''; ?>>CGPA out of 9</option>
							<option value="CGPA out of 7" <?= ($academicdetails->tenth_marking_scheme == 'CGPA out of 7') ? 'selected' : ''; ?>>CGPA out of 7</option>
							<option value="CGPA out of 4" <?= ($academicdetails->tenth_marking_scheme == 'CGPA out of 4') ? 'selected' : ''; ?>>CGPA out of 4</option>
						</select>
					</div>
				</div>
				<div class="col-lg-3 border2 border1">
					<div class="c1">
						<p>Obtained Percentage / CGPA</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Percentage / CGPA" name="tenth_percentage" maxlength="3" value="<?= $academicdetails->tenth_marking_scheme; ?>">
					</div>
				</div>
			</div>
			<div class="row after hide">
				<label>After Xth Qualification *</label><br>
				<input type="radio" name="after_tenth" value="12th">&nbsp;&nbsp;12th
				<input type="radio" name="after_tenth" value="Diploma">&nbsp;&nbsp;Diploma
				<input type="radio" name="after_tenth" value="Both">&nbsp;&nbsp;Both
			</div>

			<div class="row <?php echo ($academicdetails->twelth_school_name == '') ? 'hide' : ''; ?>" id="twelthAcademicDetails">
				<h4>12th Academic Details</h4>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>&nbsp;</p>
					</div>

					<div class="c2">
						<p>12<sup>th</sup></p>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Institute Name</p>
					</div>
					<div class="c2">
						<input type="hidden" name="academicDetailsId" value="">
						<input class="form-control" type="text" class="form-group" placeholder="Enter 12th School Name" name="twelth_school_name" value="<?= $academicdetails->twelth_school_name; ?>">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Board / University</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="twelth_board" value="<?= $academicdetails->twelth_board; ?>">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Year of Passing</p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" placeholder="Enter Passing Year"  name="twelth_passing_year" id="twelth_passing_year" value="<?= $academicdetails->twelth_passing_year; ?>"> -->
						<select class="form-control" name="twelth_passing_year" id="twelth_passing_year">
							<option value="">Select</option>
							<?php for ($i = 0; $i < 15; $i++) : ?>
								<option value="<?= date("Y") - $i; ?>" <?= ((date("Y") - $i) == $academicdetails->twelth_passing_year) ? 'selected' : '' ?>><?= date("Y") - $i; ?></option>
							<?php endfor; ?>

						</select>

					</div>
				</div>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>Result Status</p>
					</div>
					<div class="c2">
						<select class="form-control" name="twelth_result_status" id="twelth_result_status">
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->twelth_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->twelth_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1 " id="twelth_marking_scheme_div">
					<div class="c1">
						<p>Marking Scheme</p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" placeholder="CGPA / Percentage" name="twelth_marking_scheme" id="twelth_marking_scheme" value="<?= $academicdetails->twelth_marking_scheme; ?>"> -->
						<select class="form-control" name="twelth_marking_scheme" required>
							<option value="">Select</option>
							<option value="Percentage" <?= ($academicdetails->twelth_marking_scheme == 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
							<option value="CGPA out of 10" <?= ($academicdetails->twelth_marking_scheme == 'CGPA out of 10') ? 'selected' : ''; ?>>CGPA out of 10</option>
							<option value="CGPA out of 9" <?= ($academicdetails->twelth_marking_scheme == 'CGPA out of 9') ? 'selected' : ''; ?>>CGPA out of 9</option>
							<option value="CGPA out of 7" <?= ($academicdetails->twelth_marking_scheme == 'CGPA out of 7') ? 'selected' : ''; ?>>CGPA out of 7</option>
							<option value="CGPA out of 4" <?= ($academicdetails->twelth_marking_scheme == 'CGPA out of 4') ? 'selected' : ''; ?>>CGPA out of 4</option>
						</select>


					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Obtained Percentage / CGPA</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" placeholder="Enter Your 12th Percentage" name="twelth_percentage" id="twelth_percentage" value="<?= $academicdetails->twelth_percentage; ?>">
					</div>
				</div>
			</div>
			<!-- diploma details-->

			<div class="row <?php echo ($academicdetails->diploma_institute == '') ? 'hide' : ''; ?>" id="diplomaAcademicDetails">
				<h4>Diploma Academic Details</h4>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>&nbsp;</p>
					</div>

					<div class="c2">
						<p>Diploma</p>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Institute Name</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Institute Name" name="diploma_institute" value="<?= $academicdetails->diploma_institute; ?>">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Board / University</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="diploma_board" value="<?= $academicdetails->diploma_board; ?>">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Year of Passing </p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" placeholder="Enter Passing Year"  name="diploma_passing_year" value="<?= $academicdetails->diploma_passing_year; ?>"> -->
						<select class="form-control" name="diploma_passing_year" id="diploma_passing_year">
							<option value="">Select</option>
							<?php for ($i = 0; $i < 15; $i++) : ?>
								<option value="<?= date("Y") - $i; ?>" <?= ((date("Y") - $i) == $academicdetails->diploma_passing_year) ? 'selected' : '' ?>><?= date("Y") - $i; ?></option>
							<?php endfor; ?>

						</select>
					</div>
				</div>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>Result Status</p>
					</div>
					<div class="c2">
						<select class="form-control" name="diploma_result_status" id="diploma_result_status">
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->diploma_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->diploma_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1" id="diploma_marking_scheme_div">
					<div class="c1">
						<p>Marking Scheme </p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" class="form-group" placeholder="CGPA / Percentage" name="diploma_marking_scheme" id="diploma_marking_scheme" value="<?= $academicdetails->diploma_marking_scheme; ?>"> -->
						<select class="form-control" name="diploma_marking_scheme" required>
							<option value="">Select</option>
							<option value="Percentage" <?= ($academicdetails->diploma_marking_scheme == 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
							<option value="CGPA out of 10" <?= ($academicdetails->diploma_marking_scheme == 'CGPA out of 10') ? 'selected' : ''; ?>>CGPA out of 10</option>
							<option value="CGPA out of 9" <?= ($academicdetails->diploma_marking_scheme == 'CGPA out of 9') ? 'selected' : ''; ?>>CGPA out of 9</option>
							<option value="CGPA out of 7" <?= ($academicdetails->diploma_marking_scheme == 'CGPA out of 7') ? 'selected' : ''; ?>>CGPA out of 7</option>
							<option value="CGPA out of 4" <?= ($academicdetails->diploma_marking_scheme == 'CGPA out of 4') ? 'selected' : ''; ?>>CGPA out of 4</option>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Obtained Percentage / CGPA</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Diploma Percentage" name="diploma_percentage" maxlength="3" id="diploma_percentage" value="<?= $academicdetails->diploma_percentage; ?>">

					</div>
				</div>
			</div>
			<!-- end diploma details-->

			<!-- Under Graduate details-->

			<div class="row after accadmic-education-div">
				<label>Qualification *</label><br>
				<input type="radio" name="after_xx_status" <?= ($academicdetails->after_xx_status == "Graduation" ? "checked" : '') ?> <?= empty($academicdetails->after_xx_status) ? 'checked' : '' ?> value="Graduation">&nbsp;&nbsp;Graduation
				<input type="radio" name="after_xx_status" <?= ($academicdetails->after_xx_status == "Post Graduation" ? "checked" : '') ?> value="Post Graduation">&nbsp;&nbsp;Post Graduation
				<input type="radio" name="after_xx_status" <?= ($academicdetails->after_xx_status == "Both" ? "checked" : '') ?> value="Both">&nbsp;&nbsp;Both
			</div>
			<!-- end diploma details-->

			<!-- Under Graduate details-->
			<div class="row accadmic-education-div" id="graduationAcademicDetails" style="display:<?= ($academicdetails->after_xx_status == 'Graduation' || $academicdetails->after_xx_status == 'Both' || empty($academicdetails->after_xx_status)) ? 'block' : 'none' ?>">
				<h4>Graduation Details</h4>

				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Course Name</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Course Name" name="graduation_course" value="<?= $academicdetails->graduation_course; ?>">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Board / University</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="graduation_board" value="<?= $academicdetails->graduation_board; ?>">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Year of Passing </p>
					</div>
					<div class="c2">
						<select class="form-control" name="graduation_passing_year" id="graduation_passing_year">
							<option value="">Select</option>
							<?php for ($i = 0; $i < 15; $i++) : ?>
								<option value="<?= date("Y") - $i; ?>" <?= ((date("Y") - $i) == $academicdetails->graduation_passing_year) ? 'selected' : '' ?>><?= date("Y") - $i; ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Result Status</p>
					</div>
					<div class="c2">
						<select class="form-control" name="graduation_result_status" id="graduation_result_status">
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->graduation_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->graduation_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
							<!-- <option value="Not Appeared" <?= ($academicdetails->graduation_result_status == 'Not Appeared') ? 'selected' : ''; ?>>Not Appeared</option> -->
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1" id="graduation_marking_scheme_div">
					<div class="c1">
						<p>Marking Scheme</p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" class="form-group" placeholder="CGPA / Percentage" name="graduation_marking_scheme" id="graduation_marking_scheme" value="<?= $academicdetails->graduation_marking_scheme; ?>"> -->
						<select class="form-control" name="graduation_marking_scheme" id="graduation_marking_scheme">
							<option value="">Select</option>
							<option value="Percentage" <?= ($academicdetails->graduation_marking_scheme == 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
							<option value="CGPA out of 10" <?= ($academicdetails->graduation_marking_scheme == 'CGPA out of 10') ? 'selected' : ''; ?>>CGPA out of 10</option>
							<option value="CGPA out of 9" <?= ($academicdetails->graduation_marking_scheme == 'CGPA out of 9') ? 'selected' : ''; ?>>CGPA out of 9</option>
							<option value="CGPA out of 7" <?= ($academicdetails->graduation_marking_scheme == 'CGPA out of 7') ? 'selected' : ''; ?>>CGPA out of 7</option>
							<option value="CGPA out of 4" <?= ($academicdetails->graduation_marking_scheme == 'CGPA out of 4') ? 'selected' : ''; ?>>CGPA out of 4</option>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Obtained Percentage / CGPA</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Graduation Percentage" name="graduation_percentage" id="graduation_percentage" value="<?= $academicdetails->graduation_percentage; ?>">
					</div>
				</div>
			</div>
			<!-- end ug details-->

			<div class="row accadmic-education-div" id="post_graduationAcademicDetails" style="display:<?= ($academicdetails->after_xx_status == 'Post Graduation' || $academicdetails->after_xx_status == 'Both') ? 'block' : 'none' ?>">
				<h4>Post Graduation Details</h4>

				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Course Name</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Course Name" name="post_graduation_course" value="<?= $academicdetails->post_graduation_course; ?>" ?>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Board / University</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="post_graduation_board" value="<?= $academicdetails->post_graduation_board; ?>" ?>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Year of Passing </p>
					</div>
					<div class="c2">
						<select class="form-control" name="post_graduation_passing_year" id="post_graduation_passing_year" ?>>
							<option value="">Select</option>
							<?php for ($i = 0; $i < 15; $i++) : ?>
								<option value="<?= date("Y") - $i; ?>" <?= ((date("Y") - $i) == $academicdetails->post_graduation_passing_year) ? 'selected' : '' ?>><?= date("Y") - $i; ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Result Status</p>
					</div>
					<div class="c2">
						<select class="form-control" name="post_graduation_result_status" id="post_graduation_result_status">
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->post_graduation_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->post_graduation_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
							<!-- <option value="Not Appeared" <?= ($academicdetails->post_graduation_result_status == 'Not Appeared') ? 'selected' : ''; ?>>Not Appeared</option> -->
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1" id="post_graduation_marking_scheme_div">
					<div class="c1">
						<p>Marking Scheme</p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" class="form-group" placeholder="CGPA / Percentage" name="post_graduation_marking_scheme" id="post_graduation_marking_scheme" value="<?= $academicdetails->post_graduation_marking_scheme; ?>"> -->
						<select class="form-control" name="post_graduation_marking_scheme" id="post_graduation_marking_scheme">
							<option value="">Select</option>
							<option value="Percentage" <?= ($academicdetails->post_graduation_marking_scheme == 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
							<option value="CGPA out of 10" <?= ($academicdetails->post_graduation_marking_scheme == 'CGPA out of 10') ? 'selected' : ''; ?>>CGPA out of 10</option>
							<option value="CGPA out of 9" <?= ($academicdetails->post_graduation_marking_scheme == 'CGPA out of 9') ? 'selected' : ''; ?>>CGPA out of 9</option>
							<option value="CGPA out of 7" <?= ($academicdetails->post_graduation_marking_scheme == 'CGPA out of 7') ? 'selected' : ''; ?>>CGPA out of 7</option>
							<option value="CGPA out of 4" <?= ($academicdetails->post_graduation_marking_scheme == 'CGPA out of 4') ? 'selected' : ''; ?>>CGPA out of 4</option>
						</select>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Obtained Percentage / CGPA</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Post Graduation Percentage" name="post_graduation_percentage" id="post_graduation_percentage" value="<?= $academicdetails->post_graduation_percentage; ?>">
					</div>
				</div>
			</div>

			<!-- end ug details-->

			<hr>
			<?php
			$entrance_names = array_values(array_filter(explode(",", $admissionpreferences->entrance_exam_details), 'strlen'));
			$entrance_data = array_column($entrance_data, null, 'id');
			?>

			<div class="row <?php echo ($admissionpreferences->entrance_exam_details == '') ? 'hide' : ''; ?>" id="entrance_exam_div">
				<h4>Entrance Exam</h4>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>&nbsp;</p>
					</div>

					<div class="c2">
						<p><?= !empty($entrance_data[$entrance_names[0]]) ? $entrance_data[$entrance_names[0]]["name"] : ''; ?></p>
					</div>
					<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
						<p><?= !empty($entrance_data[$entrance_names[1]]) ? $entrance_data[$entrance_names[1]]["name"] : ''; ?></p>

					</div>
				</div>
				<div class="col-lg-3 border2 border1">
					<div class="c1">
						<p>Roll No. / Registration No.</p>
					</div>
					<div class="c2">
						<input class="form-control" type="number" class="form-group" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Enter Entrance Roll No" name="entrance_roll" value="<?= $academicdetails->entrance_roll; ?>">
					</div>
					<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
						<input class="form-control" type="number" class="form-group" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Enter Entrance Roll No" name="entrance_roll_1" value="<?= !empty($academicdetails->entrance_roll_1) ? $academicdetails->entrance_roll_1 : ''; ?>">
					</div>
				</div>
				<div class="col-lg-3 border2 border1">
					<div class="c1">
						<p>Year</p>
					</div>
					<div class="c2">
						<!-- <input class="form-control" type="text" placeholder="Enter Entrance Year" name="entrance_year" value="<?= $academicdetails->entrance_year; ?>"> -->
						<input type="month" name="entrance_year" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> id="entrance_year" class="form-control" value="<?= ($academicdetails->entrance_year) ? $academicdetails->entrance_year : '' ?>" placeholder="Select Month and Year">
					</div>

					<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
						<!-- <input class="form-control" type="text" placeholder="Enter Entrance Year" name="entrance_year" value="<?= $academicdetails->entrance_year; ?>"> -->
						<input type="month" name="entrance_year_1" id="entrance_year_1" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'disabled' : ''; ?> value="<?= ($academicdetails->entrance_year_1) ? $academicdetails->entrance_year_1 : '' ?>" class="form-control" placeholder="Select Month and Year">
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Result Status</p>
					</div>
					<div class="c2">
						<select class="form-control" name="entrance_result_status" id="entrance_result_status">
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->entrance_result_status == 'Awaited') ? 'selected' : '' ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->entrance_result_status == 'Declared') ? 'selected' : '' ?>>Declared</option>
							<option value="Not Appeared" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'selected' : ''; ?>>Not Appeared</option>
						</select>
					</div>
					<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
						<select class="form-control" name="entrance_result_status_1" id="entrance_result_status_1">
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->entrance_result_status_1 == 'Awaited') ? 'selected' : '' ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->entrance_result_status_1 == 'Declared') ? 'selected' : '' ?>>Declared</option>
							<option value="Not Appeared" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'selected' : ''; ?>>Not Appeared</option>
						</select>
					</div>
				</div>
				<div class="col-lg-3 border2 border1">
					<div class="c1">
						<p>Marks / AIR</p>
					</div>
					<div class="c2">
						<input type="text" class="form-control" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Marks/ AIR" name="entrance_percentage" id="entrance_percentage" value="<?= $academicdetails->entrance_percentage; ?>">

						<?php

						if (!empty($score_columns)) {
							foreach ($score_columns as $column) {
								if (!empty($entrance_data[$entrance_names[0]]["academic_type"]) && $entrance_data[$entrance_names[0]]["academic_type"] == $column["exam_type"]) {
						?>
									<label><?= $column['name'] ?></label>
									<input type="text" style="margin-top:3px" class="form-control column_score" placeholder="<?= $column['name'] ?>" name="score_column-<?= $column["id"] ?>" id="score_column-<?= $column["id"] ?>" value="<?= !empty($score_value[$column["id"]]["value"]) ? $score_value[$column["id"]]["value"] : '' ?>">

						<?php
								}
							}
						}

						// score_columns
						?>

					</div>
					<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
						<input type="text" class="form-control" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Marks/ AIR" name="entrance_percentage_1" id="entrance_percentage_1" value="<?= $academicdetails->entrance_percentage_1; ?>">

						<?php

						if (!empty($score_columns)) {
							foreach ($score_columns as $column) {
								if (!empty($entrance_data[$entrance_names[1]]["academic_type"]) && $entrance_data[$entrance_names[1]]["academic_type"] == $column["exam_type"]) {
						?>
									<label><?= $column['name'] ?></label>
									<input type="text" style="margin-top:3px" class="form-control column_score" placeholder="<?= $column['name'] ?>" name="score_column-<?= $column["id"] ?>" id="score_column-<?= $column["id"] ?>" value="<?= !empty($score_value[$column["id"]]["value"]) ? $score_value[$column["id"]]["value"] : '' ?>">

						<?php
								}
							}
						}

						// score_columns
						?>
					</div>
				</div>

			</div>
			<div class="row" style="padding-top: 30px;padding-bottom: 20px;">
				<div class="col-lg-6 col-xs-6" style="padding-left: 0px;">
					<a href="<?= base_url() ?>/clients/academic_details" class="btn btn-primary button-23">Edit Academic Details</a>
					<!-- <button type="submit" class="btn btn-primary">Save & Next</button> -->
				</div>
				<div class="col-lg-6 col-xs-6" style="padding-right: 0px;">
					<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
					<!-- <a href="<?= base_url() ?>/clients/upload_documents" class="btn btn-primary button-23 pull-right">Next</a> -->

				</div>
			</div>
			<?php // echo form_close(); 
			?>

		</div>


	</div>
	</div>
	</div>
	<!-- End Academic Details Section -->
	<!-- documents details section -->
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="row">
						<table class="table">
							<tbody class="document_upload_div">
								<?php if (!empty($document_files)) {
									foreach ($document_files as $doc_files) {
										$doc_type = $doc_files["title"];
										$file_name = "";
										if (!empty($doc_files["file_path"])) {
											$file_name =  trim(explode("_", basename($doc_files["file_path"]))[2]);
										}
								?>
										<tr class="row">
											<td class="col-6">
												<?= $doc_type ?>
											</td>
											<td class="col-6">

												<a class="col-md-12 download_document" accept="image/*,application/pdf" href="javascript:void(0);" onclick="window.open(`<?= base_url($doc_files['file_path']) ?>`, '_blank');" type="button"><?= $file_name ?> <i class="fa fa-download" aria-hidden="true"></i></a>
											</td>

										</tr>
									<?php
									}
								} else {
									?>
									<tr class="row">
										<td class="col-12" colspan="12">
											<h3>No Documents</h3>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="row" style="padding-top: 30px;padding-bottom: 20px;">
						<div class="col-lg-6 col-xs-6" style="padding-left: 0px;">
							<a href="<?= base_url() ?>/clients/upload_documents" class="btn btn-primary button-23">Edit Document</a>
							<!-- <button type="submit" class="btn btn-primary">Save & Next</button> -->
						</div>
						<div class="col-lg-6 col-xs-6" style="padding-right: 0px;">
							<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
							<!-- <a href="<?= base_url() ?>/clients/upload_documents" class="btn btn-primary button-23 pull-right">Next</a> -->

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- ens documents details section -->
	<!-- Declaration details section -->
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<?php // echo  form_open($this->uri->uri_string()); 
					?>
					<input type="hidden" name="declarationDetailsId" value="<?= $declarationdetails->id ?>">
					<h4>Declaration</h4>
					<p>I certify that the information submitted by me in support of this application, is true to the best of knowledge and belief. I understand that in the event of any information being found false or incorrect, my admission is liable to be rejected / cancelled at any stage of the program. I undertake to abide by the disciplinary rules and regulations of the institute.</p>
					<div class="row">


						<div class="col-lg-4">
							<div class="form-group">
								<label for="exampleInputMiddleName">Applicant Name</label>
								<input class="form-control" type="text" name="name" value="<?php echo $declarationdetails->name; ?>">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label for="exampleInputLastName">Parent Name</label>
								<input class="form-control" type="text" placeholder="Enter Parent Name" name="father_name" value="<?= $declarationdetails->father_name ?>">
							</div>
						</div>


						<div class="col-lg-4">
							<div class="form-group">
								<label for="exampleInputMobileNumber">Date</label>
								<input class="form-control" type="text" class="form-group" name="declaration_date" value="<?php echo date("Y-m-d h:i:s"); ?>">
							</div>
						</div>
						<div class="col-lg-6">

						</div>
					</div>
					<div class="row">
						<div class="col-lg-6 col-xs-6">
							<a href="<?= base_url() ?>/clients/declaration" class="btn btn-primary button-23">Edit Declaration Details</a>
							<!-- <button type="submit" class="btn btn-primary ">Save & Next</button> -->
						</div>
						<div class="col-lg-6 col-xs-6">
							<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
							<a href="<?= base_url() ?>/clients/invoices" class="btn btn-primary button-23 pull-right">Final Submit</a>


						</div>
					</div>
					<?php // echo form_close(); 
					?>
				</div>
			</div>
		</div>
	</div>
	<!-- end Declaration details section -->

	<script>
		var getProgram = <?= !empty($program_data) ? (json_encode($program_data, true)) : "[]"; ?>;
		var getCourse = <?= !empty($course_data) ? (json_encode($course_data, true)) : "[]"; ?>;
		var getEntrance = <?= !empty($entrance_data) ? (json_encode($entrance_data, true)) : "[]"; ?>;

		$(document).ready(function() {

			if ($("input.column_score").length > 0) {
				$("#entrance_exam_div").find(".c2").css("height", "210px");
			}
			prog();
			setTimeout(() => {
				course();

			}, 200);
			setTimeout(() => {
				// entrance_exam_given();
				$('#entrance_exam_details').change();

			}, 400);

			$("#program").on('change', function() {
				prog();
			});
			$("#course").on('change', function() {
				course();
			});
		});

		$("input,select").attr("disabled", true);
		const countriesArr = <?php echo !empty($admissionpreferences->study_country) ? json_encode(explode(",", $admissionpreferences->study_country)) : '[]' ?>;
		const universityArr = <?php echo !empty($admissionpreferences->university) ? $admissionpreferences->university : '{}' ?>;
		$('#study_country').on('change select2:opening', function() {
			let value = $(this).val()
			if (value.length > 0) {
				if (value.length <= 3) {
					let selectedUniversityArr = []
					$('#countries').val(value.join(','))
					var str = ''
					$.each(value, function(k, v) {
						var countryName = v.search("_") != -1 ? v.replace("_", " ") : v
						let c = v.replace(" ", "_");
						str += `<div class="col-lg-4">
								<div class="form-group">
									<label for="university${v}">${countryName} University</label>
									<input type="hidden" class="form-control" name="university${v}" id="university${v}" value="" required>
								</div>
							</div>`

						if (Object.keys(universityArr).length > 0) {
							if (c in universityArr) {
								selectedUniversityArr[v] = universityArr[c]
							} else {
								var prevUniversityVal = $(`#university${v}`).val()
								var newPrevUniversityVal = typeof prevUniversityVal != 'undefined' ? prevUniversityVal : ''
								selectedUniversityArr[v] = newPrevUniversityVal
							}
						} else {
							var prevUniversityVal = $(`#university${v}`).val()
							var newPrevUniversityVal = typeof prevUniversityVal != 'undefined' ? prevUniversityVal : ''
							selectedUniversityArr[v] = newPrevUniversityVal
						}
					})

					console.log(selectedUniversityArr)
					$('.universities').html(str)
					if (Object.keys(selectedUniversityArr).length > 0) {
						var count2 = 0
						$.each(Object.keys(selectedUniversityArr), function(k, v) {
							var tagInput1 = new TagsInput({
								selector: `university${v}`,
								duplicate: false,
								max: 5
							});

							if (selectedUniversityArr[v] != '') {
								if (typeof v !== 'undefined') {
									var defaultVal = selectedUniversityArr[v].split(",")
									$.each(defaultVal, function(k1, v1) {
										tagInput1.addData([v1])
									})
								}
							}
							count2++
						})
					} else {
						$.each(value, function(k, v) {
							var tagInput1 = new TagsInput({
								selector: `university${v}`,
								duplicate: false,
								max: 5
							});
						})
					}
				}

				if (value.length >= 3) {
					$(`#study_country option`).prop('disabled', true)
					$.each(value, function(k, v) {
						$(`#study_country option[value="${v}"]`).prop('disabled', false)
					})
				} else {
					$(`#study_country option`).prop('disabled', false)
				}
				$('#study_country').selectpicker('refresh');
			} else {
				$('#countries').val("")
				$('.universities').html('')
			}

		})


		function get_course(program_id) {
			return new Promise((resolve, reject) => {
				let course_array = [];
				course_array.push({
					id: '',
					name: 'Select Course'
				})
				for (let j = 0; j < getCourse.length; j++) {
					if (program_id === getCourse[j].program_id) {
						course_array.push({
							id: getCourse[j].id,
							name: getCourse[j].name
						});
					}
				}

				resolve(course_array);
			});
		}

		function get_entrance() {
			return new Promise((resolve, reject) => {
				let entrance_array = [];
				entrance_array.push({
					id: '',
					name: 'Select Entrance'
				})
				let lead_type = $("#lead_type").val();
				for (let j = 0; j < getEntrance.length; j++) {
					// if (lead_type === getEntrance[j].segment_id) {
					entrance_array.push({
						id: getEntrance[j].id,
						name: getEntrance[j].name
					});
					// }
				}

				resolve(entrance_array);
			});
		}


		async function prog() {
			var program = $("#program").val();
			var getProgram_array = [];
			for (let i = 0; i < getProgram.length; i++) {
				getProgram_array[getProgram[i].id] = await get_course(getProgram[i].id);
			}

			var $select = $('#course');
			$select.val('').selectpicker("refresh")
			var selectedCourse = "<?php echo $admissionpreferences->course ?>";
			console.log(selectedCourse);
			console.log(getProgram_array);
			$select.find('option').remove();
			if (getProgram_array[program] != undefined) {
				$.each(getProgram_array[program], function(key, value) {
					var sel = "";

					if (selectedCourse != '') {
						sel = (value.id == selectedCourse) ? 'selected' : '';
					}
					$select.append('<option value="' + value.id + '"' + sel + ' >' + value.name + '</option>');
				});
			}
			$select.selectpicker("refresh")
			$("#course_name_field input").val('');
			$(".course_name_field").hide();
		}



		async function course() {

			var course = $("#course").val();
			var selectedCourseText = $("#course option:selected").text();
			if (course != "") {
				$(".course_name_field").show();
				if ($.trim(selectedCourseText.toLowerCase()) == 'other') {
					$(".course_name_field").show();
					$(".course_name_field").find("label").text("Course Name with Specialization");
				} else {
					$(".course_name_field").show();
					$(".course_name_field").find("label").text("Specialization Name");

				}
			} else {
				$(".course_name_field").hide();
			}

			var getEntrance_array = [];
			for (let i = 0; i < getCourse.length; i++) {
				getEntrance_array = await get_entrance();
			}

			console.log(getEntrance_array);
			var selectedExam = "<?php echo $admissionpreferences->entrance_exam_details ?>";
			console.log(selectedExam);
			selectedExam_array = selectedExam.split(",");
			var $select = $("#entrance_exam_details");
			$select.find('option').remove();
			$.each(getEntrance_array, function(key, value) {
				var sel = '';
				if (selectedExam_array.length > 0) {
					if (selectedExam_array.includes(value.id.toString()) && parseInt(value.id) > 0) {
						sel = "selected";
					}
				}
				$select.append('<option value="' + value.id + '" ' + sel + '>' + value.name + '</option>');
			});
			$select.selectpicker("refresh");

			let value = $("#entrance_exam_details").val();
			value = value.filter(function(element) {
				return element !== "" && element !== " " && element !== null && element !== undefined;
			});
			if (value.length >= 2) {
				$(`#entrance_exam_details option`).prop('disabled', true);
				for (let k = 0; k < value.length; k++) {
					var v = value[k];
					$(`#entrance_exam_details option[value="${v}"]`).prop('disabled', false);
				}
			} else {
				$(this).find('option').prop('disabled', false);
			}
			$(this).selectpicker("refresh")

		}

		// TAG INPUTS


		// Plugin Constructor
		var TagsInput = function(opts) {
			this.options = Object.assign(TagsInput.defaults, opts);
			this.init();
		}

		// Initialize the plugin
		TagsInput.prototype.init = function(opts) {
			this.options = opts ? Object.assign(this.options, opts) : this.options;

			if (this.initialized)
				this.destroy();
			if (!(this.orignal_input = document.getElementById(this.options.selector))) {
				console.error("tags-input couldn't find an element with the specified ID");
				return this;
			}

			this.arr = [];
			this.wrapper = document.createElement('div');
			this.input = document.createElement('input');
			init(this);
			initEvents(this);

			this.initialized = true;
			return this;
		}

		// Add Tags
		TagsInput.prototype.addTag = function(string) {

			if (this.anyErrors(string))
				return;

			this.arr.push(string);
			var tagInput = this;

			var tag = document.createElement('span');
			tag.className = this.options.tagClass;
			tag.innerText = string;

			var closeIcon = document.createElement('a');
			closeIcon.innerHTML = '&times;';

			// delete the tag when icon is clicked
			closeIcon.addEventListener('click', function(e) {
				e.preventDefault();
				var tag = this.parentNode;

				for (var i = 0; i < tagInput.wrapper.childNodes.length; i++) {
					if (tagInput.wrapper.childNodes[i] == tag)
						tagInput.deleteTag(tag, i);
				}
			})


			tag.appendChild(closeIcon);
			this.wrapper.insertBefore(tag, this.input);
			this.orignal_input.value = this.arr.join(',');

			return this;
		}

		// Delete Tags
		TagsInput.prototype.deleteTag = function(tag, i) {
			tag.remove();
			this.arr.splice(i, 1);
			this.orignal_input.value = this.arr.join(',');
			return this;
		}

		// Make sure input string have no error with the plugin
		TagsInput.prototype.anyErrors = function(string) {
			if (this.options.max != null && this.arr.length >= this.options.max) {
				alert('max university limit reached for this country');
				return true;
			}

			if (!this.options.duplicate && this.arr.indexOf(string) != -1) {
				alert('duplicate found " ' + string + ' " ')
				return true;
			}

			return false;
		}

		// Add tags programmatically 
		TagsInput.prototype.addData = function(array) {
			var plugin = this;

			array.forEach(function(string) {
				plugin.addTag(string);
			})
			return this;
		}

		// Get the Input String
		TagsInput.prototype.getInputString = function() {
			return this.arr.join(',');
		}


		// destroy the plugin
		TagsInput.prototype.destroy = function() {
			this.orignal_input.removeAttribute('hidden');

			delete this.orignal_input;
			var self = this;

			Object.keys(this).forEach(function(key) {
				if (self[key] instanceof HTMLElement)
					self[key].remove();

				if (key != 'options')
					delete self[key];
			});

			this.initialized = false;
		}

		// Private function to initialize the tag input plugin
		function init(tags) {
			tags.wrapper.append(tags.input);
			tags.wrapper.classList.add(tags.options.wrapperClass);
			tags.orignal_input.setAttribute('hidden', 'true');
			// tags.orignal_input.hide();
			tags.orignal_input.parentNode.insertBefore(tags.wrapper, tags.orignal_input);
		}

		// initialize the Events
		function initEvents(tags) {
			tags.wrapper.addEventListener('click', function() {
				tags.input.focus();
			});


			tags.input.addEventListener('keydown', function(e) {
				var str = tags.input.value.trim();

				if (!!(~[9, 13, 188].indexOf(e.keyCode))) {
					e.preventDefault();
					tags.input.value = "";
					if (str != "")
						tags.addTag(str);
				}

			});
		}


		// Set All the Default Values
		TagsInput.defaults = {
			selector: '',
			wrapperClass: 'tags-input-wrapper',
			tagClass: 'tag',
			max: null,
			duplicate: false
		}

		window.TagsInput = TagsInput;



		$(document).ready(function() {

			$('input[type=radio][name=after_tenth]').change(function() {
				if (this.value == 'Both') {
					$('#twelthAcademicDetails').css("display", "block");
					$('#diplomaAcademicDetails').css("display", "block");
				} else if (this.value == '12th') {
					$('#diplomaAcademicDetails').css("display", "none");
					$('#twelthAcademicDetails').css("display", "block");
				} else if (this.value == 'Diploma') {
					$('#twelthAcademicDetails').css("display", "none");
					$('#diplomaAcademicDetails').css("display", "block");
				}
			});
			$("#twelth_result_status").on('change', function() {
				var trs = $("#twelth_result_status").val();
				if (trs == 'Awaited') {
					$("#twelth_marking_scheme_div").hide();
					$("#twelth_marking_scheme_div").find("select").val('').selectpicker("refresh");
					$("#twelth_percentage").hide();
					$("#twelth_percentage").val('');

				} else if (trs == 'Declared') {
					$("#twelth_marking_scheme_div").show();
					$("#twelth_percentage").show();
				}
			})
			$("#diploma_result_status").on('change', function() {
				var drs = $("#diploma_result_status").val();
				if (drs == 'Awaited') {
					$("#diploma_marking_scheme_div").hide();
					$("#diploma_marking_scheme_div").find("select").val('').selectpicker("refresh");
					$("#diploma_percentage").hide();
					$("#diploma_percentage").val('');

				} else if (drs == 'Declared') {
					$("#diploma_marking_scheme_div").show();
					$("#diploma_percentage").show();
				}
			})

			$("#entrance_result_status").on('change', function() {
				var ers = $("#entrance_result_status").val();
				if (ers == 'Awaited') {
					$("#entrance_percentage").hide();
					$("#entrance_percentage").val('');

				} else if (ers == 'Declared') {
					$("#entrance_percentage").show();
				}
			})
			$("#graduation_result_status").on('change', function() {
				var grs = $("#graduation_result_status").val();
				if (grs == 'Awaited') {
					$("#graduation_marking_scheme_div").hide();
					$("#graduation_percentage").hide();
				} else if (grs == 'Declared') {
					$("#graduation_percentage").show();
					$("#graduation_marking_scheme_div").show();
				}
			})

			var selectedRadioButton = $("input[type='radio']");

			// Trigger a click event on the selected radio button
			selectedRadioButton.click();
			$("input[name='after_tenth']").change(function() {
				$("#twelthAcademicDetails").find("input,select").val('').selectpicker("refresh");
				$("#diplomaAcademicDetails").find("input,select").val('').selectpicker("refresh");
			})
			setTimeout(() => {
				$('#twelth_result_status,#diploma_result_status,#entrance_result_status').trigger('change');

			}, 1000);
			$('#study_country').trigger('change');
		})
	</script>