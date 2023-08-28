<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


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
		width: 120px;
		border-radius: initial;
	}

	.button-22 {
		background: #415165;
		border-color: #415165;
		width: 120px;
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
			<div class="col-lg-4" style="display:none">
				<div class="form-group">
					<label for="program">Segment</label>
					<?php
					array_unshift($lead_type, array("id" => "", "name" => "Select Lead Type"));
					echo render_select('lead_type', $lead_type, array('id', 'name'), "", $lead_type_status, [], [], "", "", "", "lead_type");
					?>

				</div>
			</div>

			<div class="col-lg-12">
				<div class="card">
					<?php // print_r($contact); 
					?>
					<?php // echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); 
					?>

					<!-- <form id="basicDetailsForm" method="POST" action="basicDetailsSave"> -->
					<?php echo form_open($this->uri->uri_string()); ?>

					<?php $freezed = $admissionpreferences->freeze == 1 ? 'disabled' : ''; ?>

					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label for="program">Program</label>

								<input type="hidden" name="admissionPreferencesId" value="<?= $admissionpreferences->id ?>">
								<input type="hidden" name="direct_pass" value="<?= $admissionpreferences->freeze == 1 ? '1' : '0' ?>">
								<select class="form-control" name="program" id="program" required <?php echo $freezed ?>>
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
								<select class="form-control" name="course" id="course" required <?php echo $freezed ?>>
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

								<!-- <select class="form-control" name="session_intake" id="session_intake" <?php echo $freezed ?>>
									<option value="">Select</option> -->
								<?php
								/*$intakeArr = array(
										"May_2023" => "May 2023",
										"July_2023" => "July 2023",
										"September_2023" => "September 2023",
										"November_2023" => "November 2023",
									);

									foreach ($intakeArr as $key => $val) {
									?>
										<option value="<?php echo $key ?>" <?php echo ($admissionpreferences->session_intake == $key) ? 'selected' : ''; ?>><?php echo $val ?></option>
									<?php
									}*/
								?>
								<!-- </select> -->
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group" id="study_countries">
								<label for="study_country">Where would you like to study?</label>
								<input type="hidden" name="countries" id="countries">
								<select class="form-control" name="study_country" id="study_country" multiple required <?php echo $freezed ?>>
									<option value="">Select country </option>

								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group" id="entrance_exam_details_div">
								<label for="exampleInputCourse">Entrance exam details</label>
								<select class="form-control" name="entrance_exam_details[]" id="entrance_exam_details" <?php echo $freezed ?> multiple>
									<option value="">Select</option>


								</select>
							</div>
						</div>
					</div>
					<div class="row" id="add_university">

						<div class="universities">

						</div>
					</div>

					<div class="row">
						<div class="col-lg-6 col-xs-6">
							<a href="<?= base_url() ?>/clients/basic_details" class="btn btn-primary button-23">Back</a>
							<button type="submit" class="btn btn-primary button-22">Save & Next</button>
						</div>
						<div class="col-lg-6 col-xs-6">
							<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
							<?php if ($admissionpreferences->id > 1) { ?>
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
		var getProgram = <?= !empty($program_data) ? (json_encode($program_data, true)) : "[]"; ?>;
		var getCourse = <?= !empty($course_data) ? (json_encode($course_data, true)) : "[]"; ?>;
		var getEntrance = <?= !empty($entrance_data) ? (json_encode($entrance_data, true)) : "[]"; ?>;


		var select_segment_default = "";
		var user_id = "<?= !empty($admissionpreferences->user_id) ? $admissionpreferences->user_id : '' ?>";
		var study_country_selected = <?= !empty(json_encode(explode(",", $admissionpreferences->study_country))) ? json_encode(explode(",", $admissionpreferences->study_country), true) : "" ?>;

		if (study_country_selected.length > 0) {
			study_country_selected = study_country_selected.map(function(value) {
				return value.trim().toLowerCase();
			});
		}
		var dropdown_country_university_selection = <?= !empty($dropdown_country_university_selection) ? json_encode($dropdown_country_university_selection, true) : [] ?>;
		// console.log(dropdown_country_university_selection);



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
				var option = $('<option value="' + country_name + '">').text(country_name);
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

		$(document).ready(function() {
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



		var getProgram = <?= !empty($program_data) ? (json_encode($program_data, true)) : "[]"; ?>;
		var getCourse = <?= !empty($course_data) ? (json_encode($course_data, true)) : "[]"; ?>;
		var getEntrance = <?= !empty($entrance_data) ? (json_encode($entrance_data, true)) : "[]"; ?>;

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
					if (lead_type === getEntrance[j].segment_id) {
						entrance_array.push({
							id: getEntrance[j].id,
							name: getEntrance[j].name
						});
					}
				}

				resolve(entrance_array);
			});
		}


		async function prog() {
			// console.log("start");
			var program = $("#program").val();
			var getProgram_array = [];
			// console.log(getProgram);
			for (let i = 0; i < getProgram.length; i++) {
				getProgram_array[getProgram[i].id] = await get_course(getProgram[i].id);
			}

			var $select = $('#course');
			$select.val('').selectpicker("refresh")
			var selectedCourse = "<?php echo $admissionpreferences->course ?>";
			// console.log(selectedCourse);
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

			// $("#course_name_field input").val('');
			// if ($.trim(selectedCourseText.toLowerCase()) == 'other') {
			//     $(".course_name_field").show();
			// } else {
			//     $(".course_name_field").hide();
			// }

			var getEntrance_array = [];
			for (let i = 0; i < getCourse.length; i++) {
				getEntrance_array = await get_entrance();
			}

			console.log(getEntrance_array);
			var selectedExam = "<?php echo $admissionpreferences->entrance_exam_details ?>";
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
			}
			$(this).selectpicker("refresh")

		});


		// UNIVERSITY AND COUNTRY JS

		var suggetions_university = [];
		const countriesArr = <?php echo !empty($admissionpreferences) ? json_encode(explode(",", $admissionpreferences->study_country)) : '[]' ?>;
		const universityArr = <?php echo !empty($admissionpreferences->university) ? $admissionpreferences->university : '{}'
								?>;
		var selectedUniversityArr = [];
		// const universityArr = {};
		$('#study_country').on('change select2:opening', async function() {
			let value = $(this).val();
			if (value.length > 0) {
				if (value.length <= 2) {
					selectedUniversityArr = [];
					$('#countries').val(value.join(','));
					var str = '';
					for (let k = 0; k < value.length; k++) {
						var v = value[k];
						var countryName = v.search("_") != -1 ? v.replace("_", " ") : v;
						let c = v.replace(" ", "_");
						str += `<div class="col-lg-4">
						<div class="form-group">
						<label for="university${k}">${countryName} University</label>
						<input type="hidden" class="form-control suggest_university" data-country-name="${countryName}" name="university${k}" id="university${k}" value="" required>
						</div>
						</div>`;

						if (Object.keys(universityArr).length > 0) {
							if (c in universityArr) {
								selectedUniversityArr[v] = universityArr[c];
							} else {
								selectedUniversityArr[v] = "";
							}
						}
					}

					$('.universities').html(str);

					if (Object.keys(selectedUniversityArr).length > 0) {
						//console.log(selectedUniversityArr);
						var count2 = 0;

						for (const k in selectedUniversityArr) {
							const v = selectedUniversityArr[k];
							var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;

							let university_list = await show_university_dropdown(select_segment_default, k);
							//console.log(university_list);
							//console.log(university_list);
							var tagInput1 = new TagsInput({
								selector: `university${count2}`,
								duplicate: false,
								max: 5,
								suggestions: university_list
							});

							if (v !== '') {
								//console.log(v);
								if (typeof v !== 'undefined') {
									var defaultVal = v.split(",");
									defaultVal.forEach(function(k1, v1) {
										tagInput1.addData([k1]);
									});
								}
							}
							count2++;
							suggetions_university[countryName] = university_list;
						}
					} else {
						for (let k = 0; k < value.length; k++) {
							var v = value[k];
							//console.log(value);
							let university_list = await show_university_dropdown(select_segment_default, v);
							//console.log(university_list);
							var countryName = v.search("_") != -1 ? v.replace("_", " ") : v;

							//console.log(university_list);
							var tagInput1 = new TagsInput({
								selector: `university${k}`,
								duplicate: false,
								max: 5,
								suggestions: university_list
							});

							suggetions_university[countryName] = university_list;
						}
					}
				}

				if (value.length >= 2) {
					$(`#study_country option`).prop('disabled', true);
					for (let k = 0; k < value.length; k++) {
						var v = value[k];
						$(`#study_country option[value="${v}"]`).prop('disabled', false);
					}
				} else {
					$(`#study_country option`).prop('disabled', false);
				}
				$('#study_country').selectpicker('refresh');
			} else {
				$('#countries').val("");
				$('.universities').html("");
			}
			// set_university();
		});

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
			//console.log(this)
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


			// tags.input.addEventListener('keydown', function(e) {
			//     var str = tags.input.value.trim();

			//     if (!!(~[9, 13, 188].indexOf(e.keyCode))) {
			//         e.preventDefault();
			//         tags.input.value = "";
			//         if (str != "")
			//             tags.addTag(str);
			//     }

			// });

			tags.input.addEventListener('click', function() {
				var div_elements = document.querySelectorAll('.suggestions-container');
				div_elements.forEach((div_elements) => {
					div_elements.classList.add('hide_sugg');
				});
			});

			tags.input.addEventListener('keyup', function(event) {
				var closestFormGroup = event.target.closest('.form-group');
				var suggestUniversityElement = "";
				var country_name = "";

				if (closestFormGroup) {
					suggestUniversityElement = closestFormGroup.querySelector('.suggest_university').id;
					country_name = closestFormGroup.querySelector('.suggest_university').getAttribute("data-country-name");
				}

				var str = event.target.value.trim();
				var suggestions = suggetions_university[country_name];

				if (suggestions && suggestions.length > 0) {
					var matchedSuggestions = suggetions_university[country_name].filter(function(suggestion) {
						// return suggestion.toLowerCase().startsWith(str.toLowerCase());
						return suggestion.toLowerCase().includes(str.toLowerCase());
					});

					var div_elements = document.querySelectorAll('.suggestions-container');
					div_elements.forEach((div_elements) => {
						div_elements.classList.add('hide_sugg');
					});
					if (str.trim() != "") {
						displaySuggestions(matchedSuggestions, suggestUniversityElement, event, tags);
					}
				}
			});
		}

		window.addEventListener('click', function(event) {
			var div_elements = document.querySelectorAll('.suggestions-container');
			div_elements.forEach((div_elements) => {
				div_elements.classList.add('hide_sugg');
			});
		});

		// Display the auto-suggestions
		function displaySuggestions(suggestions, id, event, tags) {

			var suggestionsContainer = document.getElementById('suggestions-container-' + id);

			if (!suggestionsContainer) {
				suggestionsContainer = document.createElement('ul');
				suggestionsContainer.id = 'suggestions-container-' + id;
				suggestionsContainer.classList.add('suggestions-container');
				suggestionsContainer.setAttribute('data-university', id);
				document.getElementById(id).after(suggestionsContainer);
			}
			suggestionsContainer.closest(".suggestions-container").classList.remove('hide_sugg');
			suggestionsContainer.innerHTML = '';

			suggestions.forEach(function(suggestion) {
				var suggestionItem = document.createElement('li');
				suggestionItem.innerText = suggestion;

				suggestionItem.addEventListener('click', function() {
					var selectedSuggestion = this.innerText;
					let u_id = $(this).closest(".suggestions-container").attr("data-university");
					//console.log(country_name);

					tags.addTag(selectedSuggestion);
					let country_name = $("#" + u_id).attr("data-country-name");
					country_name = country_name.search("_") != -1 ? country_name.replace("_", " ") : country_name;
					let selected_university = $("#" + u_id).val();
					event.target.value = '';
					selectedUniversityArr[country_name] = selected_university;
					universityArr[country_name] = selected_university;
					suggestionsContainer.closest(".suggestions-container").classList.add('hide_sugg');

				});

				suggestionsContainer.appendChild(suggestionItem);
			});

		}
		// Set All the Default Values
		TagsInput.defaults = {
			selector: '',
			wrapperClass: 'tags-input-wrapper',
			tagClass: 'tag',
			max: null,
			duplicate: false,
			suggestions: []
		}

		window.TagsInput = TagsInput;

		// var count = 0;
		function set_university_div() {
			return new Promise((resolve, reject) => {
				let set_count = 0;
				let str = "";

				for (const k in universityArr) {
					var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;

					str += `<div class="col-lg-4">
        <div class="form-group">
          <label for="university${set_count}">${countryName} University</label>
          <input type="hidden" class="form-control suggest_university" data-country-name="${countryName}" name="university${set_count}" id="university${set_count}" value="" required>
        </div>
      </div>`;


					set_count++;
				}

				if (str !== "") {
					resolve(str);
				} else {
					reject(new Error("Failed to generate university HTML."));
				}
			});
		}

		async function set_university() {
			try {
				const str = await set_university_div();
				$('.universities').html(str);

				let set_count = 0;
				let leadTypeSelect = document.getElementById("lead_type");
				let selectedValue = leadTypeSelect.options[leadTypeSelect.selectedIndex].text.trim().toLowerCase();

				for (const k in universityArr) {
					let university_list = await show_university_dropdown(selectedValue, k);
					//console.log(university_list);
					var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;
					if (universityArr.hasOwnProperty(k)) {
						var tagInput1 = new TagsInput({
							selector: `university${set_count}`,
							duplicate: false,
							max: 5,
							suggestions: university_list
						});

						var defaultVal = universityArr[k].split(",");
						//console.log(defaultVal);

						if (defaultVal != "") {
							defaultVal.forEach(function(k1, v1) {
								tagInput1.addData([k1]);
							});
						}
						set_count++;
						suggetions_university[countryName] = university_list;
					}
				}

				//console.log("University setup completed.");
				// Place your subsequent code here
			} catch (error) {
				console.error("An error occurred during university setup:", error);
			}
		}

		set_university();
		setTimeout(() => {
			$('#study_country').trigger('change')

		}, 500);
	</script>