<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


   <style type="text/css">
  	.card{border:1px solid #efefef;padding: 10px;}
  	.heading{    
		font-size: 25px;
    	font-weight: 600;
	}
    .btn-primary {
		color: #fff;
		background-color: #337ab7;
		border-color: #2e6da4;
		height: 40px;
		width: 120px;
		border-radius: initial;
	}
	.button-22{background: #415165;border-color: #415165;width: 120px;}
  </style>
</head>
<body>
<div class="container">
		<div class="row text-center">
			  <div class="col-lg-1"></div>
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/basic_details.png" class="img-responsive">
					<p>Student Details</p>
				
				</center>
				</div>
			</div>
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Parents_details_gray.png" class="img-responsive">
					<p>Admission Preferences </p>
				</center>
				</div>
			</div>
			<!-- 
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Address_details_gray.png" class="img-responsive">
					<p>Address Details</p>
				</center>
				</div>
			</div> 
			-->
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Academics_details_gray.png" class="img-responsive">
					<p>Academics Details</p>
				</center>
				</div>
			</div>
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Address_details_gray.png" class="img-responsive">
					<p>Upload Documents</p>
				</center>
				</div>
			</div> 
			<div class="col-lg-2 col-xs-2">
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
				<h2 class="heading">Student Details</h2>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
				<?php // print_r($contact); ?>
				<?php // echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>
 
				<!-- <form id="basicDetailsForm" method="POST" action="basicDetailsSave"> -->
				<?php echo form_open($this->uri->uri_string()); ?>


					
					<h4>Personal Informations</h4>
				  	<div class="row">
						<div class="col-lg-3">
                      <div class="form-group">
                      	<label for="title">Title</label>
						  <input type="hidden" name="basicDetailsId" value="<?=$basicdetails->id?>">
						<select class="form-control" name="title" id="title">
							<option value="Mr">Mr</option>
							<option value="Ms">Ms</option>
							<option value="Mrs">Mrs</option>
							<option value="Dr">Dr</option>
						</select>
				  </div>
				  </div>
				  <div class="col-lg-3">
				  	   <div class="form-group">
				  	   	<label for="exampleInputFirstName">First Name</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails))?$basicdetails->first_name: $contact->firstname;?>' >
				  </div>
				  </div>
				  <div class="col-lg-3">
				  	<div class="form-group">
				  		<label for="exampleInputLastName">Last Name</label>
				  	<input class="form-control" type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails))?$basicdetails->last_name: $contact->lastname;?>' >
				  </div>
				</div>
						<div class="col-lg-3">
                      <div class="form-group">
                      	<label for="exampleInputEmail">Email Address</label>
                      	<input class="form-control" type="text" class="form-group" placeholder="Email Address" name="email" value='<?php echo (isset($basicdetails))?$basicdetails->email: $contact->email;?>' readonly>
				  </div>
				  </div>
				  <div class="col-lg-3">
				  	   <div class="form-group">
				  	   	<label for="exampleInputMobileNumber">Mobile Number</label>
				  		<input class="form-control" type="text" class="form-group" placeholder="Mobile Number" name="mobile" value='<?php echo (isset($basicdetails))?$basicdetails->mobile: $contact->phonenumber;?>' readonly>
				  </div>
				  </div>
				  <div class="col-lg-3">
				  	<div class="form-group">
				  		<label for="exampleInputDateOfBirth">Date Of Birth</label>
				  		<input type="date" class="form-control" name="dob" value='<?php echo ($basicdetails->dob != '')? $basicdetails->dob : '';?>' required="required">
						<?php echo form_error('dob'); ?>

				  </div>
				</div><div class="col-lg-3">
				  	<div class="form-group">
				  		<label for="exampleInputPassword1">Gender</label>
				  	<select class="form-control" name="gender" id="gender" required>
					  	<option value="">Select</option>
				  		<option <?php echo ($basicdetails->gender == 'Male')? 'selected': ''; ?>>Male</option>
				  		<option <?php echo ($basicdetails->gender == 'Female')? 'selected': ''; ?>>Female</option>
				  		<option <?php echo ($basicdetails->gender == 'Other')? 'selected': ''; ?>>Other</option>
				  	</select>
					  <?php echo form_error('gender'); ?>

				  </div>
				</div>
				
				
					</div>
					<hr>

					<h4>Parent Details</h4>
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Father Name</label>
								<input type="text" name="father_name" class="form-control" id="father_name" value="<?php echo $basicdetails->father_name ;?>">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Mother Name</label>
								<input type="text" name="mother_name" class="form-control" id="mother_name" value="<?php echo $basicdetails->mother_name ;?>">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Father's Mobile Number</label>
								<input type="text" name="fathers_mobile" class="form-control" id="fathers_mobile" value="<?php echo $basicdetails->fathers_mobile ;?>">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Mother's Mobile Number</label>
								<input type="text" name="mothers_mobile" class="form-control" id="mothers_mobile" value="<?php echo $basicdetails->mothers_mobile ;?>">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Father's Email Number</label>
								<input type="text" name="fathers_email" class="form-control" id="fathers_email" value="<?php echo $basicdetails->fathers_email ;?>">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label for="exampleInputPassword1">Mother's Email Number</label>
								<input type="text" name="mothers_email" class="form-control" id="mothers_email" value="<?php echo $basicdetails->mothers_email ;?>">
							</div>
						</div>
					</div>
				  <div class="row">
				  	<div class="col-lg-6 col-xs-6">
				  		<button type="submit" class="btn btn-primary button-22">Save & Next</button>
				  	</div>
				  	<div class="col-lg-6 col-xs-6" >
				  		<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
						<?php if($basicdetails->id > 1){?>
						  <!-- <a href="/clients/parent_details" class="btn btn-primary button-23 pull-right">Next</a> -->
						<?php } ?>
				  		
				  	</div>
				  </div>
				  <?php echo form_close(); ?>
			</div>
			</div>
		</div>
	</div>
<script>
	$(document).ready(function(){
		prog();
		course();
		study_country();
		entrance_exam_given();
	});
	$("#program").on('change', function(){
		prog();
	});
	$("#course").on('change', function(){
		course();
	});
	$("#study_country").on('change', function(){
		study_country();
	});
	$("#entrance_exam_given").on('change', function(){
		entrance_exam_given();
	});

	function prog(){
		var program = $("#program").val();
		var ug = {"0":"Select","1":"MBBS","2":"B.Tech","3":"OTHER"};
		var pg = {"1":"MD/MS","2":"MBA/PGDM","3":"OTHER"};
		var $select = $('#course'); 
		var selectedCourse = "<?php echo $basicdetails->course ?>";
		console.log(selectedCourse);
		if(program == 'Under Graduate'){
			$select.find('option').remove();  
			$.each(ug,function(key, value) 
			{
				var sel = (value == selectedCourse)?'selected':'';				
				$select.append('<option value="' + value + '"'+ sel +' >' + value + '</option>');
			});
			$select.selectpicker("refresh")
		}
		else if(program == 'Post Graduate'){
			$select.find('option').remove();  
			$.each(pg,function(key, value) 
			{
				var sel = (value == selectedCourse)?'selected':'';				
				$select.append('<option value="' + value + '"'+ sel +'>' + value + '</option>');
			});
			$select.selectpicker("refresh")
		}
		else{
			$select.find('option').remove();  
			$select.append('<option>Select a Course</option>');
			$select.selectpicker("refresh")
		}		
	}

	function course(){
		var course = $("#course").val();
		var study_country = $("#study_country").val();

		var mbbs = {"0":"Select","1":"NEET UG","2":"OTHER"};
		var btech = {"1":"JEE","2":"VITEEE","3":"UPSEE","4":"SRMJEE","5":"BITSAT","6":"MET","7":"MAT CET","8":"IPUCET","9":"COMEDK"};
		var mdms = {"1":"NEET PG","2":"OTHER"};
		var mbapgdm = {"1":"CAT","2":"MAT","3":"XAT","4":"CMAT","5":"SNAP"};
		var abroad = {"1":"IELTS","2":"TOEFL","3":"PTE","4":"SAT","5":"GMAT","6":"GRE","7":"OTHER"};

		var selectedExam = "<?php echo $basicdetails->entrance_exam_details ?>";

		var $select = $('#entrance_exam_details'); 
		// console.log($select);
		
		if(study_country == 'ABROAD'){
			if(course == 'MBBS'){
				$select.find('option').remove();  
				$.each(mbbs,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}else if(course == 'MD/MS'){
				$select.find('option').remove();  
				$.each(mdms,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';				
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}else{
				$select.find('option').remove();  
				$.each(abroad,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}
		}else{                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
		if(course == 'MBBS'){
			$select.find('option').remove();  
			$.each(mbbs,function(key, value) 
			{
				var sel = (value == selectedExam)?'selected':'';
				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
			});
			$select.selectpicker("refresh")
		}
		else if(course == 'B.Tech'){
			$select.find('option').remove();  
			$.each(btech,function(key, value) 
			{
				var sel = (value == selectedExam)?'selected':'';				
				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
			});
			$select.selectpicker("refresh")
		}
		else if(course == 'MD/MS'){
			$select.find('option').remove();  
			$.each(mdms,function(key, value) 
			{
				var sel = (value == selectedExam)?'selected':'';				
				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
			});
			$select.selectpicker("refresh")
		}
		else if(course == 'MBA/PGDM'){
			$select.find('option').remove();  
			$.each(mbapgdm,function(key, value) 
			{
				var sel = (value == selectedExam)?'selected':'';				
				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
			});
			$select.selectpicker("refresh")
		}
		else{
			$select.find('option').remove();  
			$select.append('<option value="OTHER">OTHER</option>');
			$select.selectpicker("refresh")
		}
	}		
	}

	function study_country(){
		var course = $("#course").val();
		var study_country = $("#study_country").val();

		var mbbs = {"0":"Select","1":"NEET UG","2":"OTHER"};
		var btech = {"1":"JEE","2":"VITEEE","3":"UPSEE","4":"SRMJEE","5":"BITSAT","6":"MET","7":"MAT CET","8":"IPUCET","9":"COMEDK"};
		var mdms = {"1":"NEET PG","2":"OTHER"};
		var mbapgdm = {"1":"CAT","2":"MAT","3":"XAT","4":"CMAT","5":"SNAP"};
		var abroad = {"1":"IELTS","2":"TOEFL","3":"PTE","4":"SAT","5":"GMAT","6":"GRE","7":"OTHER"};
		var selectedExam = "<?php echo $basicdetails->entrance_exam_details ?>";
		var $select = $('#entrance_exam_details'); 
		// console.log($select);
		
		if(study_country == 'ABROAD'){
			if(course == 'MBBS'){
				$select.find('option').remove();  
				$.each(mbbs,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}else if(course == 'MD/MS'){
				$select.find('option').remove();  
				$.each(mdms,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';				
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}else{
				$select.find('option').remove();  
				$.each(abroad,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}
		}else{                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
			if(course == 'MBBS'){
				$select.find('option').remove();  
				$.each(mbbs,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}
			else if(course == 'B.Tech'){
				$select.find('option').remove();  
				$.each(btech,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';				
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}
			else if(course == 'MD/MS'){
				$select.find('option').remove();  
				$.each(mdms,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';				
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}
			else if(course == 'MBA/PGDM'){
				$select.find('option').remove();  
				$.each(mbapgdm,function(key, value) 
				{
					var sel = (value == selectedExam)?'selected':'';				
					$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
				});
				$select.selectpicker("refresh")
			}
			else{
				$select.find('option').remove();  
				$select.append('<option value="OTHER">OTHER</option>');
				$select.selectpicker("refresh")
			}
		}
	}
	function entrance_exam_given(){
		var eeg = $("#entrance_exam_given").val();
		if(eeg == 'YES'){
			$("#entrance_exam_details_div").show();
		}else{
			$("#entrance_exam_details_div").hide();
		}
	}
</script>