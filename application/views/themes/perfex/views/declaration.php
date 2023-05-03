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
			<div class="col-lg-2 col-xs-2">
				<center>
				<a href="/clients/basic_details">
					<img src="/uploads/company/basic_details.png" class="img-responsive">
					<p>Basic Details</p>
				</a>
				</center>
			</div>
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
				<a href="/clients/admission_preferences">
					<img src="/uploads/company/Parents_details.png" class="img-responsive">
					<p>Admission Preferences</p>
				</a>
				</center>
				</div>
			</div>
			<!-- <div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Address_details.png" class="img-responsive">
					<p>Address Details</p>
				</center>
				</div>
			</div> -->
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
				<a href="/clients/academic_details">
					<img src="/uploads/company/Academics_details.png" class="img-responsive">
					<p>Academics Details</p>
				</a>
				</center>
				</div>
			</div>
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
				<a href="/clients/upload_documents">
					<img src="/uploads/company/Address_details.png" class="img-responsive">
					<p>Upload Documents</p>
				</a>
				</center>
				</div>
			</div>
			<div class="col-lg-2 col-xs-2">
				<center>
					<img src="/uploads/company/declaration.png" class="img-responsive">
					<p>Declaration</p>
				</center>
			</div>
			  <div class="col-lg-1"></div>
		</div>
	</div>
	<!-- <div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="heading">Online Application Form</h2>
			</div>
		</div>
	</div> -->
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
				<?php echo form_open($this->uri->uri_string()); ?>
					<input type="hidden" name="declarationDetailsId" value="<?=$declarationdetails->id?>">
					<h4>Declaration</h4>
					<p>I certify that the information submitted by me in support of this application, is true to the best of knowledge and belief. I understand that in the event of any information being found false or incorrect, my admission is liable to be rejected / cancelled at any stage of the program. I undertake to abide by the disciplinary rules and regulations of the institute.</p>
				  	<div class="row">


				  <div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputMiddleName">Applicant Name</label>
				  	<input class="form-control" type="text" name="name" value="<?php echo $contact->firstname." ".$contact->lastname; ?>">
				  </div>
				</div><div class="col-lg-4">
				  	<div class="form-group">
				  		<label for="exampleInputLastName">Parent Name</label>
				  	<input class="form-control" type="text" placeholder="Enter Parent Name" name="father_name" value="<?php echo $parentdetails->fname ; ?>">
				  </div>
				</div>
					
						
				  <div class="col-lg-4">
				  	   <div class="form-group">
				  	   	<label for="exampleInputMobileNumber">Date</label>
				  		<input class="form-control" type="text" class="form-group"  name="declaration_date" value="<?php echo date("Y-m-d h:i:s");?>">
				  </div>
				  </div>
				  <div class="col-lg-6">
				   
				</div>
					</div>
				  <div class="row">
				  	<div class="col-lg-6 col-xs-6">
						<a href="/clients/upload_documents" class="btn btn-primary button-23">Back</a>
				  		<button type="submit" class="btn btn-primary">Save & Next</button>
				  	</div>
				  	<div class="col-lg-6 col-xs-6" >
				  		<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
						  <!-- <a href="/clients/invoices" class="btn btn-primary button-23">Next</a> -->

				  		
				  	</div>
				  </div>
				<?php echo form_close(); ?>
			</div>
			</div>
		</div>
	</div>
