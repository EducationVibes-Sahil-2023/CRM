<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
  <style type="text/css">
  	.card{border:1px solid #efefef;padding: 10px;}
  	.heading{    font-size: 25px;
    font-weight: 600;}
    .btn-primary {
    color: #fff;
    background-color: #337ab7;
    border-color: #2e6da4;
    height: 40px;
    width: 120px;
    border-radius: initial;
}
.button-23{background: #415165;border-color: #415165;width: 100px;}

  </style>
<div class="container">
		<div class="row text-center">
			  <div class="col-lg-1"></div>
			<div class="col-lg-2">
				<center>
				<a href="/clients/basic_details">
					<img src="/uploads/company/basic_details.png" class="img-responsive">
					<p>Basic Details</p>
				</a>
				</center>
			</div>
			<div class="col-lg-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Parents_details.png" class="img-responsive">
					<p>Parents Details</p>
				</center>
				</div>
			</div>
			<!-- <div class="col-lg-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Address_details_gray.png" class="img-responsive">
					<p>Address Details</p>
				</center>
				</div>
			</div> -->
			<div class="col-lg-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Academics_details_gray.png" class="img-responsive">
					<p>Academics Details</p>
				</center>
				</div>
			</div>

			<div class="col-lg-2">
				<center>
					<img src="/uploads/company/declaration_gray.png" class="img-responsive">
					<p>Declaration</p>
				</center>
			</div>
			  <div class="col-lg-1"></div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="heading">Online Application Form</h2>
			</div>
			<div class="col-lg-12"><?php echo $this->session->flashdata('success'); ?></div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
				<?php echo form_open($this->uri->uri_string()); ?>
					<h4>Father Details</h4>
				  	<div class="row">
						<div class="col-lg-4">
                      <div class="form-group">
                      	<label for="exampleInputTitle">Title</label>
						  <input type="hidden" name="parentDetailsId" value="<?=$parentdetails->id?>">
				  	<select class="form-control" name="ftitle" id="ftitle">
				  		<option class="form-control" value="Mr" <?php echo ($parentdetails->ftitle == 'Mr')? 'selected': ''; ?>>Mr.</option>
				  		<option class="form-control" value="Dr" <?php echo ($parentdetails->ftitle == 'Dr')? 'selected': ''; ?>>Dr.</option>
				  	</select>
				  </div>
				  </div>
				  <div class="col-lg-4">
				  	   <div class="form-group">
				  	   	<label for="exampleInputFirstName">Father Name</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Enter Your father Name" name="fname" id="fname" value="<?php echo $parentdetails->fname ; ?>" required title="Enter Father Name">
					  <?php echo form_error('fname'); ?>

				  </div>
				  </div>
				  <div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputMiddleName">Father's Email Address</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Enter Your Father Email Address" name="femail" id="femail" value="<?php echo $parentdetails->femail ; ?>">
				  </div>
				</div>
				
				</div>
					<div class="row">
				<div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputLastName">Father's Mobile Number</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Enter Your Father Mobile Number" name="fmobile" id="fmobile" value="<?php echo $parentdetails->fmobile ; ?>" required title="Enter Father Mobile Number">
					  <?php echo form_error('fmobile'); ?>

				  </div>
				</div>
					
						<div class="col-lg-4">
                      <div class="form-group">
                      	<label for="exampleInputEmail">Father's Occupation</label>
                      	<input type="text" class="form-control" name="foccupation" placeholder="Enter Occupation" id="foccupation" value="<?php echo $parentdetails->foccupation ; ?>">
				  		
				  </div>
				  </div>
				  <!-- <div class="col-lg-3">
				  	   <div class="form-group">
				  	   	<label for="exampleInputMobileNumber">Father's Designation</label>
				  		<input class="form-control" type="text" class="form-group" placeholder="Enter Your Father's Designation" name="fdesignation" id="fdesignation" value="<?php echo $parentdetails->fdesignation ; ?>">
				  </div>
				  </div> -->
				  <div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputDateOfBirth">Father's Annual Income</label>
				  	<select class="form-control" name="fannual_income" id="fannual_income">
				  		<option value="">Select</option>
						<option value="below 1 lakhs" <?php echo ($parentdetails->fannual_income == 'below 1 lakhs')? 'selected': ''; ?>>below 1 lakhs</option>
						<option value="1 lakhs - 2 lakhs" <?php echo ($parentdetails->fannual_income == '1 lakhs - 2 lakhs')? 'selected': ''; ?>>1 lakhs - 2 lakhs</option>
						<option value="2 lakhs - 3 lakhs" <?php echo ($parentdetails->fannual_income == '2 lakhs - 3 lakhs')? 'selected': ''; ?>>2 lakhs - 3 lakhs</option>
						<option value="3 lakhs - 4 lakhs" <?php echo ($parentdetails->fannual_income == '3 lakhs - 4 lakhs')? 'selected': ''; ?>>3 lakhs - 4 lakhs</option>
						<option value="4 lakhs - 5 lakhs" <?php echo ($parentdetails->fannual_income == '4 lakhs - 5 lakhs')? 'selected': ''; ?>>4 lakhs - 5 lakhs</option>
						<option value="above 5 lakhs" <?php echo ($parentdetails->fannual_income == 'above 5 lakhs')? 'selected': ''; ?>>above 5 lakhs</option>
				  	</select>
				  </div>
				</div><div class="col-lg-3">

				</div>
					</div>
					<hr>
                <h4>Mother Details</h4>
				  	<div class="row">
						<div class="col-lg-4">
                      <div class="form-group">
                      	<label for="exampleInputTitle">Title</label>
				  	<select class="form-control" name="mtitle">
				  		<option class="form-control" value="Mrs" <?php echo ($parentdetails->mtitle == 'Mrs')? 'selected': ''; ?>>Mrs.</option>
				  		<option class="form-control" value="Dr" <?php echo ($parentdetails->mtitle == 'Dr')? 'selected': ''; ?>>Dr.</option>
				  	</select>
				  </div>
				  </div>
				  <div class="col-lg-4">
				  	   <div class="form-group">
				  	   	<label for="exampleInputFirstName">Mother Name</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Enter Your Mother Name" name="mname" value="<?php echo $parentdetails->mname ; ?>">
					  <?php echo form_error('mname'); ?>

				  </div>
				  </div>
				  <div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputMiddleName">Mother's Email Address</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Enter Your Mother Email Address" name="memail" value="<?php echo $parentdetails->memail ; ?>">
				  </div>
				</div>
				</div>
					<div class="row">
				<div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputLastName">Mother's Mobile Number</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Enter Your Mother Mobile Number" name="mmobile" value="<?php echo $parentdetails->mmobile ; ?>">
				  </div>
				</div>
					
						<div class="col-lg-4">
                      <div class="form-group">
                      	<label for="exampleInputEmail">Mother's Occupation</label>
                      	<input type="text" class="form-control" placeholder="Enter Occupation" name="moccupation" value="<?php echo $parentdetails->moccupation ; ?>">
				  		
				  </div>
				  </div>
				  <!-- <div class="col-lg-3">
				  	   <div class="form-group">
				  	   	<label for="exampleInputMobileNumber">Mother's Designation</label>
				  		<input class="form-control" type="text" class="form-group" placeholder="Enter Your Mother's Designation" name="mdesignation" value="<?php echo $parentdetails->mdesignation ; ?>">
				  </div>
				  </div> -->
				  <div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputDateOfBirth">Mother's Annual Income</label>
				  		<select class="form-control" name="mannual_income">
							<option value="">Select</option>
							<option value="below 1 lakhs" <?php echo ($parentdetails->mtitle == 'below 1 lakhs')? 'selected': ''; ?>>below 1 lakhs</option>
							<option value="1 lakhs - 2 lakhs" <?php echo ($parentdetails->mtitle == '1 lakhs - 2 lakhs')? 'selected': ''; ?>>1 lakhs - 2 lakhs</option>
							<option value="2 lakhs - 3 lakhs" <?php echo ($parentdetails->mtitle == '2 lakhs - 3 lakhs')? 'selected': ''; ?>>2 lakhs - 3 lakhs</option>
							<option value="3 lakhs - 4 lakhs" <?php echo ($parentdetails->mtitle == '3 lakhs - 4 lakhs')? 'selected': ''; ?>>3 lakhs - 4 lakhs</option>
							<option value="4 lakhs - 5 lakhs" <?php echo ($parentdetails->mtitle == '4 lakhs - 5 lakhs')? 'selected': ''; ?>>4 lakhs - 5 lakhs</option>
							<option value="above 5 lakhs" <?php echo ($parentdetails->mtitle == 'above 5 lakhs')? 'selected': ''; ?>>above 5 lakhs</option>
				  		</select>
				  </div>
				</div><div class="col-lg-3">

				</div>
					</div><hr>
				  <div class="row">
				  	<div class="col-lg-6 col-xs-6">
					  <a href="/clients/basic_details" class="btn btn-primary button-23">Back</a>

				  		<button type="submit" class="btn btn-primary">Save & Next</button>
				  	</div>
				  	<div class="col-lg-6 col-xs-6" >
					  	<?php if($parentdetails->id > 0){ ?>
							<!-- <a href="/clients/academic_details" class="btn btn-primary button-23 pull-right">Next</a> -->
						<?php } ?>				  		
				  	</div>
				  </div>
				  <?php echo form_close(); ?>
			</div>
			</div>
		</div>
	</div>

