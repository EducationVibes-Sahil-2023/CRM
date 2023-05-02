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

	.tags-input-wrapper{
		background: transparent;
		padding: 10px;
		border-radius: 4px;
		max-width: 400px;
		border: 1px solid #ccc;
		pointer-events:<?php echo $admissionpreferences->freeze == 1 ? 'none' : '' ?>
	}
	.tags-input-wrapper input{
		border: none;
		background: transparent;
		outline: none;
		width: 140px;
		margin-left: 8px;
	}
	.tags-input-wrapper .tag{
		display: inline-block;
		background-color: #337ab7;
		color: white;
		border-radius: 40px;
		padding: 0px 3px 0px 7px;
		margin-right: 5px;
		margin-bottom:5px;
		box-shadow: 0 5px 15px -2px rgb(51 122 183)
	}
	.tags-input-wrapper .tag a {
		margin: 0 7px 3px;
		display: inline-block;
		cursor: pointer;
		color:white!important;
	}
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
					<img src="/uploads/company/Parents_details.png" class="img-responsive">
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
				<h2 class="heading">Admission Preferences</h2>
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
					
					<?php $freezed = $admissionpreferences->freeze == 1 ? 'disabled' : ''; ?>

					<div class="row">
						<div class="col-lg-4">
                      <div class="form-group">
                      	<label for="program">Program</label>
						  <input type="hidden" name="admissionPreferencesId" value="<?=$admissionpreferences->id?>">
				  	<select class="form-control" name="program" id="program" required <?php echo $freezed ?>>
					  	<option value="">Select a Program</option>
				  		<option value="Under Graduate" <?php echo ($admissionpreferences->program == 'Under Graduate')? 'selected': ''; ?>>Under Graduate</option>
				  		<option vaue="Post Graduate" <?php echo ($admissionpreferences->program == 'Post Graduate')? 'selected': ''; ?>>Post Graduate</option>
				  	</select>
					  <?php echo form_error('program'); ?>

				  </div>
				  </div>
				  <div class="col-lg-4">
				  	   <div class="form-group">
				  	   	<label for="course">Course</label>
						<select class="form-control" name="course" id="course" <?php echo $freezed ?>>
							<option value="">Select a course </option>
							<!-- <option  value="MBBS" <?php echo ($admissionpreferences->course == 'MBBS')? 'selected': ''; ?>>MBBS</option>
							<option  value="MBA" <?php echo ($admissionpreferences->course == 'MBA')? 'selected': ''; ?>>MBA</option>
							<option  value="B.Tech" <?php echo ($admissionpreferences->course == 'B.Tech')? 'selected': ''; ?>>B.Tech</option> -->

							<?php if($admissionpreferences->course !=''): ?>
							<option value="<?=$admissionpreferences->course ;?>" selected><?=$admissionpreferences->course ;?></option>
							<?php endif; ?>

						</select>
						<?php echo form_error('course'); ?>

				  </div>
				  </div>
				  <div class="col-lg-4">
					<div class="form-group">
						<label for="session_intake">Session Intake</label>
						<select class="form-control" name="session_intake" id="session_intake" <?php echo $freezed ?>>
							<option value="">Select</option>
							<?php 
								$intakeArr = array(
									"May_2023"=>"May 2023",
									"July_2023"=>"July 2023",
									"September_2023"=>"September 2023",
									"November_2023"=>"November 2023",
								);

								foreach($intakeArr as $key => $val){
									?>
										<option value="<?php echo $key ?>" <?php echo ($admissionpreferences->session_intake == $key)? 'selected': ''; ?>><?php echo $val ?></option>
									<?php
								}
							?>
						</select>
					</div>
				</div>
					</div>
				 	<div class="row" id="add_university">
					 	<div class="col-lg-4">
							<div class="form-group" id="study_countries">
								<label for="study_country">Where would you like to study?</label>
								<input type="hidden" name="countries" id="countries">
								<select class="form-control" name="study_country" id="study_country" multiple required <?php echo $freezed ?>>
									<?php 
										$allCountriesArr = array("USA"=>"USA","UK"=>"UK","Canada"=>"Canada","Australia"=>"Australia","New_Zealand"=>"New zealand","Germany"=>"Germany","Italy"=>"Italy","France"=>"France","UAE"=>"UAE","Russia"=>"Russia","Georgia"=>"Georgia","Kazakhstan"=>"Kazakhstan","Krygstan"=>"Krygstan","Bangladesh"=>"Bangladesh","Nepal"=>"Nepal");
									?>
									<option value="">Select country </option>
									<?php 
										if(!empty($admissionpreferences)){
											$countries = $admissionpreferences->study_country;
											$countriesArr = explode(",",$countries);
										}
										foreach($allCountriesArr as $key => $val){
											?>
												<option value="<?php echo $key; ?>"  <?php echo !empty($admissionpreferences) ? ((in_array($val,$countriesArr))? 'selected': '') : ''; ?>><?php echo $val; ?></option>
											<?php
										}
									?>
								</select>
							</div>
				  		</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label for="entrance_exam_given">Have You Given any Entrance Exam?</label>
								<select class="form-control" name="entrance_exam_given" id="entrance_exam_given" <?php echo $freezed ?>>
									<option value="">Select</option>
									<option value="YES" <?php echo ($admissionpreferences->entrance_exam_given == 'YES')? 'selected': ''; ?>>YES</option>
									<option value="NO" <?php echo ($admissionpreferences->entrance_exam_given == 'NO')? 'selected': ''; ?>>NO</option>
								</select>
							</div>
				  		</div>
						<div class="col-lg-4">
							<div class="form-group" id="entrance_exam_details_div">
								<label for="exampleInputCourse">Entrance exam details</label>
								<select class="form-control" name="entrance_exam_details" id="entrance_exam_details" <?php echo $freezed ?>>
									<option value="">Select</option>
									<?php if($admissionpreferences->entrance_exam_details !=''): ?>
									<option value="<?=$admissionpreferences->entrance_exam_details ;?>" selected><?=$admissionpreferences->entrance_exam_details ;?></option>
									<?php else: ?>
										<option value="NEET UG">NEET UG</option>	
									<?php endif; ?>
									
								</select>
							</div>
						</div>
						
						<div class="universities">
								<?php 
									if($admissionpreferences->university != ''){
										$universitiesArr = json_decode($admissionpreferences->university,true);
										$count = 0;
										foreach($universitiesArr as $key => $val){
											if($val != ''){
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
					  	<a href="/clients/basic_details" class="btn btn-primary button-23">Back</a>
				  		<button type="submit" class="btn btn-primary button-22">Save & Next</button>
				  	</div>
				  	<div class="col-lg-6 col-xs-6" >
				  		<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
						<?php if($admissionpreferences->id > 1){?>
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
		// study_country();
		entrance_exam_given();
	});
	$("#program").on('change', function(){
		prog();
	});
	$("#course").on('change', function(){
		course();
	});
	// $("#study_country").on('change', function(){
	// 	study_country();
	// });
	$("#entrance_exam_given").on('change', function(){
		entrance_exam_given();
	});

	function prog(){
		var program = $("#program").val();
		var ug = {"0":"Select","1":"MBBS","2":"B.Tech","3":"OTHER"};
		var pg = {"1":"MD/MS","2":"MBA/PGDM","3":"OTHER"};
		var $select = $('#course'); 
		var selectedCourse = "<?php echo $admissionpreferences->course ?>";
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

		var selectedExam = "<?php echo $admissionpreferences->entrance_exam_details ?>";

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

	// function study_country(){
	// 	var course = $("#course").val();
	// 	var study_country = $("#study_country").val();

	// 	var mbbs = {"0":"Select","1":"NEET UG","2":"OTHER"};
	// 	var btech = {"1":"JEE","2":"VITEEE","3":"UPSEE","4":"SRMJEE","5":"BITSAT","6":"MET","7":"MAT CET","8":"IPUCET","9":"COMEDK"};
	// 	var mdms = {"1":"NEET PG","2":"OTHER"};
	// 	var mbapgdm = {"1":"CAT","2":"MAT","3":"XAT","4":"CMAT","5":"SNAP"};
	// 	var abroad = {"1":"IELTS","2":"TOEFL","3":"PTE","4":"SAT","5":"GMAT","6":"GRE","7":"OTHER"};
	// 	var selectedExam = "<?php echo $admissionpreferences->entrance_exam_details ?>";
	// 	var $select = $('#entrance_exam_details'); 
	// 	// console.log($select);
		
	// 	if(study_country == 'ABROAD'){
	// 		if(course == 'MBBS'){
	// 			$select.find('option').remove();  
	// 			$.each(mbbs,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}else if(course == 'MD/MS'){
	// 			$select.find('option').remove();  
	// 			$.each(mdms,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';				
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}else{
	// 			$select.find('option').remove();  
	// 			$.each(abroad,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}
	// 	}else{                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
	// 		if(course == 'MBBS'){
	// 			$select.find('option').remove();  
	// 			$.each(mbbs,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}
	// 		else if(course == 'B.Tech'){
	// 			$select.find('option').remove();  
	// 			$.each(btech,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';				
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}
	// 		else if(course == 'MD/MS'){
	// 			$select.find('option').remove();  
	// 			$.each(mdms,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';				
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}
	// 		else if(course == 'MBA/PGDM'){
	// 			$select.find('option').remove();  
	// 			$.each(mbapgdm,function(key, value) 
	// 			{
	// 				var sel = (value == selectedExam)?'selected':'';				
	// 				$select.append('<option value="' + value + '"'+ sel + '>' + value + '</option>');
	// 			});
	// 			$select.selectpicker("refresh")
	// 		}
	// 		else{
	// 			$select.find('option').remove();  
	// 			$select.append('<option value="OTHER">OTHER</option>');
	// 			$select.selectpicker("refresh")
	// 		}
	// 	}
	// }
	function entrance_exam_given(){
		var eeg = $("#entrance_exam_given").val();
		if(eeg == 'YES'){
			$("#entrance_exam_details_div").show();
		}else{
			$("#entrance_exam_details_div").hide();
		}
	}

	const countriesArr = <?php echo !empty($admissionpreferences) ? json_encode(explode(",",$admissionpreferences->study_country)) : '[]' ?>;
	const universityArr = <?php echo !empty($admissionpreferences->university) ? $admissionpreferences->university : '{}' ?>;
	$('#study_country').on('change select2:opening',function(){
		let value = $(this).val()
		if(value.length > 0){
			if(value.length <= 3){
				let selectedUniversityArr = []
				$('#countries').val(value.join(','))
				var str = ''
				$.each(value, function(k,v){
					var countryName = v.search("_") != -1 ? v.replace("_"," ") : v

					str += `<div class="col-lg-4">
								<div class="form-group">
									<label for="university${v}">${countryName} University</label>
									<input type="hidden" class="form-control" name="university${v}" id="university${v}" value="" required>
								</div>
							</div>`

					if(Object.keys(universityArr).length > 0){
						if(v in universityArr){
							selectedUniversityArr[v] = universityArr[v]
						}else{
							var prevUniversityVal = $(`#university${v}`).val()
							var newPrevUniversityVal = typeof prevUniversityVal != 'undefined' ? prevUniversityVal : ''
							selectedUniversityArr[v] = newPrevUniversityVal
						}	
					}else{
						var prevUniversityVal = $(`#university${v}`).val()
						var newPrevUniversityVal = typeof prevUniversityVal != 'undefined' ? prevUniversityVal : ''
						selectedUniversityArr[v] = newPrevUniversityVal
					}
				})

				console.log(selectedUniversityArr)
				$('.universities').html(str)
				if(Object.keys(selectedUniversityArr).length > 0){
					var count2 = 0
					$.each(Object.keys(selectedUniversityArr), function(k,v){
						var tagInput1 = new TagsInput({
							selector: `university${v}`,
							duplicate : false,
							max : 3
						});

						if(selectedUniversityArr[v] != ''){
							if(typeof v !== 'undefined'){
								var defaultVal = selectedUniversityArr[v].split(",")
								$.each(defaultVal, function(k1,v1){
									tagInput1.addData([v1])
								})
							}
						}
						count2++
					})
				}else{
					$.each(value, function(k,v){
						var tagInput1 = new TagsInput({
							selector: `university${v}`,
							duplicate : false,
							max : 3
						});
					})
				}
			}

			if(value.length >= 3){
				$(`#study_country option`).prop('disabled',true)
				$.each(value, function(k,v){
					$(`#study_country option[value="${v}"]`).prop('disabled',false)
				})
			}else{
				$(`#study_country option`).prop('disabled',false)
			}
			$('#study_country').selectpicker('refresh');
		}else{
			$('#countries').val("")
			$('.universities').html('')
		}

	})

	// TAG INPUTS

	
    // Plugin Constructor
    var TagsInput = function(opts){
        this.options = Object.assign(TagsInput.defaults , opts);
        this.init();
    }

    // Initialize the plugin
    TagsInput.prototype.init = function(opts){
        this.options = opts ? Object.assign(this.options, opts) : this.options;

        if(this.initialized)
            this.destroy();
        if(!(this.orignal_input = document.getElementById(this.options.selector)) ){
            console.error("tags-input couldn't find an element with the specified ID");
            return this;
        }

        this.arr = [];
        this.wrapper = document.createElement('div');
        this.input = document.createElement('input');
        init(this);
        initEvents(this);

        this.initialized =  true;
        return this;
    }

    // Add Tags
    TagsInput.prototype.addTag = function(string){

        if(this.anyErrors(string))
            return ;

        this.arr.push(string);
        var tagInput = this;

        var tag = document.createElement('span');
        tag.className = this.options.tagClass;
        tag.innerText = string;

        var closeIcon = document.createElement('a');
        closeIcon.innerHTML = '&times;';
        
        // delete the tag when icon is clicked
        closeIcon.addEventListener('click' , function(e){
            e.preventDefault();
            var tag = this.parentNode;

            for(var i =0 ;i < tagInput.wrapper.childNodes.length ; i++){
                if(tagInput.wrapper.childNodes[i] == tag)
                    tagInput.deleteTag(tag , i);
            }
        })


        tag.appendChild(closeIcon);
        this.wrapper.insertBefore(tag , this.input);
        this.orignal_input.value = this.arr.join(',');

        return this;
    }

    // Delete Tags
    TagsInput.prototype.deleteTag = function(tag , i){
        tag.remove();
        this.arr.splice( i , 1);
        this.orignal_input.value =  this.arr.join(',');
        return this;
    }

    // Make sure input string have no error with the plugin
    TagsInput.prototype.anyErrors = function(string){
        if( this.options.max != null && this.arr.length >= this.options.max ){
            alert('max university limit reached for this country');
            return true;
        }
        
        if(!this.options.duplicate && this.arr.indexOf(string) != -1 ){
            alert('duplicate found " '+string+' " ')
            return true;
        }

        return false;
    }

    // Add tags programmatically 
    TagsInput.prototype.addData = function(array){
        var plugin = this;
        
        array.forEach(function(string){
            plugin.addTag(string);
        })
        return this;
    }

    // Get the Input String
    TagsInput.prototype.getInputString = function(){
        return this.arr.join(',');
    }


    // destroy the plugin
    TagsInput.prototype.destroy = function(){
        this.orignal_input.removeAttribute('hidden');

        delete this.orignal_input;
        var self = this;
        
        Object.keys(this).forEach(function(key){
            if(self[key] instanceof HTMLElement)
                self[key].remove();
            
            if(key != 'options')
                delete self[key];
        });

        this.initialized = false;
    }

    // Private function to initialize the tag input plugin
    function init(tags){
        tags.wrapper.append(tags.input);
        tags.wrapper.classList.add(tags.options.wrapperClass);
        tags.orignal_input.setAttribute('hidden' , 'true');
        // tags.orignal_input.hide();
        tags.orignal_input.parentNode.insertBefore(tags.wrapper , tags.orignal_input);
    }

    // initialize the Events
    function initEvents(tags){
        tags.wrapper.addEventListener('click' ,function(){
            tags.input.focus();           
        });
        

        tags.input.addEventListener('keydown' , function(e){
            var str = tags.input.value.trim(); 

            if( !!(~[9 , 13 , 188].indexOf( e.keyCode ))  )
            {
                e.preventDefault();
                tags.input.value = "";
                if(str != "")
                    tags.addTag(str);
            }

        });
    }


    // Set All the Default Values
    TagsInput.defaults = {
        selector : '',
        wrapperClass : 'tags-input-wrapper',
        tagClass : 'tag',
        max : null,
        duplicate: false
    }

    window.TagsInput = TagsInput;

	$('#study_country').trigger('change')
</script>