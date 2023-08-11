<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
		width: 120px;
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
		width: 120px;
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
</style>

<body>
	<div class="container">
		<div class="row text-center">
			<div class="col-lg-1"></div>
			<div class="col-lg-2">
				<center>
					<a href="<?= base_url() ?>/clients/basic_details">
						<img src="/uploads/company/basic_details.png" class="img-responsive">
						<p>Basic Details</p>
					</a>
				</center>
			</div>
			<div class="col-lg-2">
				<div class="icon-box">
					<center>
						<a href="<?= base_url() ?>/clients/admission_preferences">
							<img src="/uploads/company/Parents_details.png" class="img-responsive">
							<p>Admission Preferences</p>
						</a>
					</center>
				</div>
			</div>
			<!-- <div class="col-lg-2">
        <div class="icon-box">
        <center>
          <img src="/uploads/company/Address_details.png" class="img-responsive">
          <p>Address Details</p>
        </center>
        </div>
      </div> -->
			<div class="col-lg-2">
				<div class="icon-box">
					<center>
						<img src="<?= base_url() ?>/uploads/company/Academics_details.png" class="img-responsive">
						<p>Academics Details</p>
					</center>
				</div>
			</div>
			<div class="col-lg-2">
				<div class="icon-box">
					<center>
						<img src="<?= base_url() ?>/uploads/company/Address_details_gray.png" class="img-responsive">
						<p>Document Details</p>
					</center>
				</div>
			</div>
			<div class="col-lg-2">
				<center>
					<img src="<?= base_url() ?>/uploads/company/declaration_gray.png" class="img-responsive">
					<p>Declaration</p>
				</center>
			</div>
			<div class="col-lg-1"></div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="heading">Academic Details</h2>
			</div>
		</div>
	</div>
	<div class="container" style="padding: 15px;">
		<div class="card">
			<?php echo form_open($this->uri->uri_string()); ?>
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
						<input class="form-control" type="text" class="form-group" placeholder="Enter Percentage / CGPA" name="tenth_percentage" value="<?= $academicdetails->tenth_percentage; ?>">
					</div>
				</div>
			</div>
			<div class="row after">

				<label>After Xth Qualification *</label><br>
				<input type="radio" name="after_tenth" <?= ($academicdetails->after_x_status == "12th" ? "checked" : '') ?> <?= empty($academicdetails->after_x_status) ? 'checked' : '' ?> value="12th">&nbsp;&nbsp;12th
				<input type="radio" name="after_tenth" <?= ($academicdetails->after_x_status == "Diploma" ? "checked" : '') ?> value="Diploma">&nbsp;&nbsp;Diploma
				<input type="radio" name="after_tenth" <?= ($academicdetails->after_x_status == "Both" ? "checked" : '') ?> value="Both">&nbsp;&nbsp;Both
			</div>

			<div class="row" id="twelthAcademicDetails" style="display:<?= ($academicdetails->after_x_status == '12th' || $academicdetails->after_x_status == 'Both') ? 'block' : 'none' ?>">
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
						<p>School Name</p>
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
						<select class="form-control" name="twelth_marking_scheme">
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

			<div class="row" id="diplomaAcademicDetails" style="display:<?= ($academicdetails->after_x_status == 'Diploma' || $academicdetails->after_x_status == 'Both') ? 'block' : 'none' ?>">
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
						<select class="form-control" name="diploma_marking_scheme">
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
						<input class="form-control" type="text" class="form-group" placeholder="Enter Diploma Percentage" name="diploma_percentage" id="diploma_percentage" value="<?= $academicdetails->diploma_percentage; ?>">

					</div>
				</div>
			</div>
			<!-- end diploma details-->

			<!-- Under Graduate details-->
			<div class="row <?php echo ($admissionpreferences->program != "") ? '' : 'hide'; ?>" id="graduationAcademicDetails">
				<h4>Graduation Details</h4>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>&nbsp;</p>
					</div>

					<div class="c2">
						<p>Graduation</p>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Course Name</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Course Name" name="graduation_course" value="<?= $academicdetails->graduation_course; ?>" <?php echo ($admissionpreferences->program == 'Post Graduate') ? 'required' : ''; ?>>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Board / University</p>
					</div>
					<div class="c2">
						<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="graduation_board" value="<?= $academicdetails->graduation_board; ?>" <?php echo ($admissionpreferences->program == 'Post Graduate') ? 'required' : ''; ?>>
					</div>
				</div>
				<div class="col-lg-2 border2 border1">
					<div class="c1">
						<p>Year of Passing </p>
					</div>
					<div class="c2">
						<select class="form-control" name="graduation_passing_year" id="graduation_passing_year" <?php echo ($admissionpreferences->program == 'Post Graduate') ? 'required' : ''; ?>>
							<option value="">Select</option>
							<?php for ($i = 0; $i < 15; $i++) : ?>
								<option value="<?= date("Y") - $i; ?>" <?= ((date("Y") - $i) == $academicdetails->graduation_passing_year) ? 'selected' : '' ?>><?= date("Y") - $i; ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
				<div class="col-lg-1 border2 border1">
					<div class="c1">
						<p>Result Status</p>
					</div>
					<div class="c2">
						<select class="form-control" name="graduation_result_status" id="graduation_result_status" <?php echo ($admissionpreferences->program == 'Post Graduate') ? 'required' : ''; ?>>
							<option>Select</option>
							<option value="Awaited" <?= ($academicdetails->graduation_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
							<option value="Declared" <?= ($academicdetails->graduation_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
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

			<hr>
			<?php
			$entrance_names = array_values(array_filter(explode(",", $admissionpreferences->entrance_exam_details), 'strlen'));
			$entrance_data = array_column($entrance_data, null, 'id');
			?>
			<div class="row <?php echo ($admissionpreferences->entrance_exam_details == '') ? 'hide' : ''; ?>">
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
						<input class="form-control" type="text" class="form-group" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Enter Entrance Roll No" name="entrance_roll" value="<?= $academicdetails->entrance_roll; ?>">
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
					</div>
					<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
						<input type="text" class="form-control" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Marks/ AIR" name="entrance_percentage_1" id="entrance_percentage_1" value="<?= $academicdetails->entrance_percentage_1; ?>">
					</div>
				</div>

			</div>
			<div class="row" style="padding-top: 30px;padding-bottom: 20px;">
				<div class="col-lg-6 col-xs-6" style="padding-left: 0px;">
					<a href="<?= base_url() ?>/clients/admission_preferences" class="btn btn-primary button-23">Back</a>
					<button type="submit" class="btn btn-primary">Save & Next</button>
				</div>
				<div class="col-lg-6 col-xs-6" style="padding-right: 0px;">
					<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
					<!-- <a href="/clients/upload_documents" class="btn btn-primary button-23 pull-right">Next</a> -->

				</div>
			</div>
			<?php echo form_close(); ?>

		</div>


	</div>
	</div>
	</div>
	<script type="text/javascript">
		// $('input[type=radio][name=after_tenth]').change(function() {
		// 	if (this.value == 'Both') {
		// 		$('#twelthAcademicDetails').css("display", "block");
		// 		$('#diplomaAcademicDetails').css("display", "block");
		// 	} else if (this.value == '12th') {
		// 		$('#diplomaAcademicDetails').css("display", "none");
		// 		$("#diplomaAcademicDetails").find("input,select").val("").selectpicker("refresh");
		// 		$('#twelthAcademicDetails').css("display", "block");
		// 	} else if (this.value == 'Diploma') {
		// 		$('#twelthAcademicDetails').css("display", "none");
		// 		$("#twelthAcademicDetails").find("input,select").val("").selectpicker("refresh");
		// 		$('#diplomaAcademicDetails').css("display", "block");
		// 	}
		// });
		$(document).ready(function() {
			$('#entrance_exam_details').change();
		})
		$('input[type=radio][name=after_tenth]').change(function() {
			let selected_value = $(this).val(); // Use 'this' to get the value of the selected radio input.
			console.log(selected_value);
			// Hide both academic details by default.
			$('#twelthAcademicDetails, #diplomaAcademicDetails').removeClass("show").addClass("hide");

			if (selected_value == 'Both') {
				$('#twelthAcademicDetails, #diplomaAcademicDetails').removeClass("hide").addClass("show");
			} else if (selected_value == '12th') {
				console.log("12 select");
				$('#twelthAcademicDetails').removeClass("hide").addClass("show");
			} else if (selected_value == 'Diploma') {
				$('#diplomaAcademicDetails').removeClass("hide").addClass("show");
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
			$("input[name='entrance_roll']").attr("disabled", false);
			$("input[name='entrance_year']").attr("disabled", false);
			$("input[name='entrance_percentage']").attr("disabled", false);
			var ers = $("#entrance_result_status").val();
			if (ers == 'Awaited') {
				$(".hide_ input").hide();
				$("#entrance_percentage").val('');
				$("#entrance_percentage").hide();


			} else if (ers == 'Declared') {
				$(".hide_ input").show();
				$("#entrance_percentage").show();

			} else if (ers == 'Not Appeared') {
				$("input[name='entrance_roll']").val('').attr("disabled", true);
				$("input[name='entrance_year']").val('').attr("disabled", true);
				$("input[name='entrance_percentage']").val('').attr("disabled", true);
			}
		})

		$("#entrance_result_status_1").on('change', function() {
			var ers = $("#entrance_result_status_1").val();
			$("input[name='entrance_roll_1']").attr("disabled", false);
			$("input[name='entrance_year_1']").attr("disabled", false);
			$("input[name='entrance_percentage_1']").attr("disabled", false);
			if (ers == 'Awaited') {
				$(".hide_2 input").hide();
				$("#entrance_percentage_1").val('');
				$("#entrance_percentage_1").show();

			} else if (ers == 'Declared') {
				$(".hide_2 input").show();
				$("#entrance_percentage_1").show();

			} else if (ers == 'Not Appeared') {
				$("input[name='entrance_roll_1']").val('').attr("disabled", true);
				$("input[name='entrance_year_1']").val('').attr("disabled", true);
				$("input[name='entrance_percentage_1']").val('').attr("disabled", true);
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

		// var selectedRadioButton = $("input[type='radio']");

		// // Trigger a click event on the selected radio button
		// selectedRadioButton.click();

		var selectedRadioButton = $("input[type='radio']:checked");

		selectedRadioButton.prop("checked", true).trigger("change");

		$("input[name='after_tenth']").change(function() {
			$("#twelthAcademicDetails").find("input,select").val('').selectpicker("refresh");
			$("#diplomaAcademicDetails").find("input,select").val('').selectpicker("refresh");
		})
		setTimeout(() => {
			$('#twelth_result_status,#diploma_result_status,#entrance_result_status').trigger('change');

		}, 1000);


		$('#entrance_exam_details').on('change select2:opening', async function() {
			let value = $(this).val();
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
				$(".course_name_field").hide();
			}
			$(this).selectpicker("refresh")

		});
	</script>