<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!empty($score_value)) {
	$score_value = array_column($score_value, null, "type");
}
?>
<!-- <script src="https://code.jquery.com/jquery-3.6.3.js"></script> -->
<script>
	var admissionpreferences_freeze = "<?= !empty($admissionpreferences->freeze) ? 1 : 0 ?>";
</script>
<style>
	.accadmic-education-div {
		padding: 10px;
		background: lightgrey;
		margin-bottom: 10px;
		padding-bottom: 30px;
	}

	.accadmic-education-div h4 {
		text-align: center;
	}

	.tags-input-wrapper {
		background: transparent;
		padding: 10px;
		border-radius: 4px;
		max-width: 400px;
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
</style>


<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<div class="row">

	<?php echo form_open($this->uri->uri_string(), array('class' => 'client-form', 'autocomplete' => 'off')); ?>
	<div class="additional"></div>
	<div class="col-md-12">
		<div class="horizontal-scrollable-tabs">
			<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
			<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
			<div class="horizontal-tabs">
				<ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal" role="tablist">
					<li role="presentation" class="<?php if (!$this->input->get('tab')) {
														echo 'active';
													}; ?>">
						<a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
							<?php echo _l('customer_profile_details'); ?>
						</a>
					</li>
					<?php
					$customer_custom_fields = false;
					if (total_rows(db_prefix() . 'customfields', array('fieldto' => 'customers', 'active' => 1)) > 0) {
						$customer_custom_fields = true;
					?>
						<li role="presentation" class="<?php if ($this->input->get('tab') == 'custom_fields') {
															echo 'active';
														}; ?> hide">
							<a href="#custom_fields" aria-controls="custom_fields" role="tab" data-toggle="tab">
								<?php echo hooks()->apply_filters('customer_profile_tab_custom_fields_text', _l('custom_fields')); ?>
							</a>
						</li>
					<?php } ?>
					<li role="presentation">
						<a href="#student_details" aria-controls="student_details" role="tab" data-toggle="tab">Student Details</a>
					</li>
					<li role="presentation">
						<a href="#admission_preferences" aria-controls="admission_preferences" role="tab" data-toggle="tab">Admission Preferences</a>
					</li>
					<li role="presentation">
						<a href="#academic_details" aria-controls="academic_details" role="tab" data-toggle="tab">Academic Details</a>
					</li>
					<li role="presentation">
						<a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a>
					</li>
					<li role="presentation">
						<a href="#declaration" aria-controls="declaration" role="tab" data-toggle="tab">Declaration</a>
					</li>
					<li role="presentation">
						<a href="#billing_and_shipping" aria-controls="billing_and_shipping" role="tab" data-toggle="tab">
							<?php echo _l('billing_shipping'); ?>
						</a>
					</li>
					<?php hooks()->do_action('after_customer_billing_and_shipping_tab', isset($client) ? $client : false); ?>
					<?php if (isset($client)) { ?>
						<li role="presentation">
							<a href="#customer_admins" aria-controls="customer_admins" role="tab" data-toggle="tab">
								<?php echo _l('customer_admins'); ?>
							</a>
						</li>
						<?php hooks()->do_action('after_customer_admins_tab', $client); ?>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="tab-content mtop15">
			<?php hooks()->do_action('after_custom_profile_tab_content', isset($client) ? $client : false); ?>
			<?php if ($customer_custom_fields) { ?>
				<div role="tabpanel" class="tab-pane <?php if ($this->input->get('tab') == 'custom_fields') {
															echo ' active';
														}; ?>" id="custom_fields">
					<?php $rel_id = (isset($client) ? $client->userid : false); ?>
					<?php echo render_custom_fields('customers', $rel_id); ?>
				</div>
			<?php } ?>
			<div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab')) {
													echo ' active';
												}; ?>" id="contact_info">
				<div class="row">
					<div class="col-md-12 mtop15 <?php if (isset($client) && (!is_empty_customer_company($client->userid) && total_rows(db_prefix() . 'contacts', array('userid' => $client->userid, 'is_primary' => 1)) > 0)) {
														echo '';
													} else {
														echo ' hide';
													} ?>" id="client-show-primary-contact-wrapper">
						<div class="checkbox checkbox-info mbot20 no-mtop">
							<input type="checkbox" name="show_primary_contact" <?php if (isset($client) && $client->show_primary_contact == 1) {
																					echo ' checked';
																				} ?> value="1" id="show_primary_contact">
							<label for="show_primary_contact"><?php echo _l('show_primary_contact', _l('invoices') . ', ' . _l('estimates') . ', ' . _l('payments') . ', ' . _l('credit_notes')); ?></label>
						</div>
					</div>
					<div class="col-md-6">
						<div class="hide">
							<?php $value = (isset($client) ? $client->company : ''); ?>
							<?php $attrs = (isset($client) ? array() : array('autofocus' => true)); ?>
							<?php echo render_input('company', 'client_company', $value, 'text', $attrs); ?>
							<div id="company_exists_info" class="hide"></div>
							<?php if (get_option('company_requires_vat_number_field') == 1) {
								$value = (isset($client) ? $client->vat : '');
								echo render_input('vat', 'client_vat_number', $value);
							} ?>
							<?php if ((isset($client) && empty($client->website)) || !isset($client)) {
								$value = (isset($client) ? $client->website : '');
								echo render_input('website', 'client_website', $value);
							} else { ?>
								<div class="form-group">
									<label for="website"><?php echo _l('client_website'); ?></label>
									<div class="input-group">
										<input type="text" name="website" id="website" value="<?php echo $client->website; ?>" class="form-control">
										<div class="input-group-addon">
											<span><a href="<?php echo maybe_add_http($client->website); ?>" target="_blank" tabindex="-1"><i class="fa fa-globe"></i></a></span>
										</div>
									</div>
								</div>
							<?php }
							$selected = array();
							if (isset($customer_groups)) {
								foreach ($customer_groups as $group) {
									array_push($selected, $group['groupid']);
								}
							}
							if (is_admin() || get_option('staff_members_create_inline_customer_groups') == '1') {
								echo render_select_with_input_group('groups_in[]', $groups, array('id', 'name'), 'customer_groups', $selected, '<a href="#" data-toggle="modal" data-target="#customer_group_modal"><i class="fa fa-plus"></i></a>', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
							} else {
								echo render_select('groups_in[]', $groups, array('id', 'name'), 'customer_groups', $selected, array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
							}
							?>
							<?php if (!isset($client)) { ?>
								<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('customer_currency_change_notice'); ?>"></i>
							<?php }
							$s_attrs = array('data-none-selected-text' => _l('system_default_string'));
							$selected = '';
							if (isset($client) && client_have_transactions($client->userid)) {
								$s_attrs['disabled'] = true;
							}
							foreach ($currencies as $currency) {
								if (isset($client)) {
									if ($currency['id'] == $client->default_currency) {
										$selected = $currency['id'];
									}
								}
							}
							// Do not remove the currency field from the customer profile!
							echo render_select('default_currency', $currencies, array('id', 'name', 'symbol'), 'invoice_add_edit_currency', $selected, $s_attrs); ?>
							<?php if (get_option('disable_language') == 0) { ?>
								<div class="form-group select-placeholder">
									<label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
									</label>
									<select name="default_language" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
										<option value=""><?php echo _l('system_default_string'); ?></option>
										<?php foreach ($this->app->get_available_languages() as $availableLanguage) {
											$selected = '';
											if (isset($client)) {
												if ($client->default_language == $availableLanguage) {
													$selected = 'selected';
												}
											}
										?>
											<option value="<?php echo $availableLanguage; ?>" <?php echo $selected; ?>><?php echo ucfirst($availableLanguage); ?></option>
										<?php } ?>
									</select>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="col-md-12 profile-data-div">
						<input type="hidden" name="clientid" id="clientid" value="<?php echo $client_id ?>">
						<div class="col-md-6">
							<?php $value = (isset($client) ? $client->phonenumber : ''); ?>
							<?php echo render_input('phonenumber', 'client_phonenumber', $value, "tel", ["readonly" => "readonly"]); ?>
						</div>

						<div class="col-md-6">
							<?php $value = (isset($client) ? $client->city : ''); ?>
							<?php echo render_input('city', 'client_city', $value); ?>
						</div>
						<div class="col-md-6">
							<?php $value = (isset($client) ? $client->state : ''); ?>
							<?php echo render_input('state', 'client_state', $value); ?>
						</div>
						<div class="col-md-6">
							<?php $value = (isset($client) ? $client->zip : ''); ?>
							<?php echo render_input('zip', 'client_postal_code', $value); ?>
						</div>
						<div class="col-md-12">
							<?php $value = (isset($client) ? $client->address : ''); ?>
							<?php echo render_textarea('address', 'client_address', $value); ?>
						</div>
						<div class="col-md-12">
							<?php $countries = get_all_countries();
							$customer_default_country = get_option('customer_default_country');
							$selected = (isset($client) ? $client->country : $customer_default_country);
							echo render_select('country', $countries, array('country_id', array('short_name')), 'clients_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
							?>
							<?php
							echo render_custom_fields('customers', $client->userid, "", "", !empty($lead_data->type) ? $lead_data->type : '');
							?>
						</div>
						<div class="">
							<div class="col-md-12">
								<button type="button" id="save_profile_data" class="btn btn-primary button-22">Save changes</button>
							</div>
						</div>

					</div>

				</div>
			</div>
			<?php if (isset($client)) { ?>
				<div role="tabpanel" class="tab-pane" id="customer_admins">
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
				</div>
			<?php } ?>
			<div role="tabpanel" class="tab-pane student-data-div" id="student_details">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<?php // echo form_open($this->uri->uri_string()); 
							?>
							<h4>Personal Informations</h4>
							<div class="row">
								<div class="col-lg-3">
									<div class="form-group">
										<label for="title">Title</label>
										<select class="form-control" name="title" id="title">
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
										<input class="form-control" type="text" class="form-group" placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->first_name : $contact->firstname; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputLastName">Last Name</label>
										<input class="form-control" type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->last_name : $contact->lastname; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputEmail">Email Address</label>
										<input class="form-control" type="text" class="form-group" placeholder="Email Address" name="email" value='<?php echo (isset($basicdetails)) ? $basicdetails->email : $contact->email; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Mobile Number</label>
										<input class="form-control" type="tel" class="form-group" placeholder="Mobile Number" name="mobile" pattern="[0-9]{10}" maxlength="10" value='<?php echo (isset($basicdetails)) ? $basicdetails->mobile : $contact->phonenumber; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputDateOfBirth">Date Of Birth</label>
										<input type="date" class="form-control" name="dob" value='<?php echo ($basicdetails->dob != '') ? $basicdetails->dob : ''; ?>' required="required">
										<?php echo form_error('dob'); ?>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputPassword1">Gender</label>
										<select class="form-control" name="gender" id="gender" required>
											<option value="">Select</option>
											<option <?php echo ($basicdetails->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
											<option <?php echo ($basicdetails->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
											<option <?php echo ($basicdetails->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
										</select>
										<?php echo form_error('gender'); ?>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Father Name</label>
										<input class="form-control" type="text" maxlength="10" class="form-group" placeholder="Father Name" name="father_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->father_name : ''; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Father's Mobile</label>
										<input class="form-control" type="tel" pattern="[0-9]{10}" class="form-group" placeholder="Father's Mobile" name="fathers_mobile" value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_mobile : ''; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Father's Email</label>
										<input class="form-control" type="text" class="form-group" placeholder="Father's Email" name="fathers_email" value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_email : ''; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Mother Name</label>
										<input class="form-control" type="text" class="form-group" placeholder="Mother Name" name="mother_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->mother_name : ''; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Mother's Mobile</label>
										<input class="form-control" type="tel" pattern="[0-9]{10}" maxlength="10" class="form-group" placeholder="Mother's Mobile" name="mothers_mobile" value='<?php echo (isset($basicdetails)) ? $basicdetails->mothers_mobile : ''; ?>'>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="exampleInputMobileNumber">Mother's Email</label>
										<input class="form-control" type="text" class="form-group" placeholder="Mother's Email" name="mothers_email" value='<?php echo (isset($basicdetails)) ? $basicdetails->mothers_email : ''; ?>'>
									</div>
								</div>
								<div class="">
									<div class="col-md-12">
										<button type="button" id="save_student_data" class="btn btn-primary button-22">Save changes</button>
									</div>
								</div>

								<?php // echo form_close(); 
								?>
							</div>
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
								<div class="col-lg-4">
									<div class="form-group">
										<label for="program">Program</label>

										<input type="hidden" name="admissionpreferencesid" id="admissionpreferencesid" value="<?php echo $admissionpreferences->id ?>">
										<input type="hidden" name="client_id" id="client_id" value="<?php echo $client_id ?>">
										<select class="form-control" name="program" id="program" required>
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
										<select class="form-control" name="course" id="course">
											<option value="">Select a course </option>
										</select>
										<?php echo form_error('course'); ?>

									</div>
								</div>

								<div class="col-lg-4 course_name_field" style="display:<?= !empty($admissionpreferences->course_name) ? 'block' : 'none' ?>">
									<div class="form-group">
										<label for="course_name">Course Name/Specialization</label>
										<input type="text" class="form-control" name="course_name" id="course_name" value="<?= !empty($admissionpreferences->course_name) ? $admissionpreferences->course_name : '' ?>">
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label for="session_intake">Session Intake</label>
										<input type="month" class="form-control" id="session_intake" name="session_intake" value="<?= !empty($admissionpreferences->session_intake) ? $admissionpreferences->session_intake : '' ?>" placeholder="Select Month and Year">
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<input type="hidden" name="countries" id="countries">
										<label for="study_country">Where would you like to study?</label>
										<select class="form-control selectpicker" name="study_country" id="study_country" multiple required>
											<?php
											// $allCountriesArr = array("USA" => "USA", "UK" => "UK", "Canada" => "Canada", "Australia" => "Australia", "New_Zealand" => "New zealand", "Germany" => "Germany", "Italy" => "Italy", "France" => "France", "UAE" => "UAE", "Russia" => "Russia", "Georgia" => "Georgia", "Kazakhstan" => "Kazakhstan", "Krygstan" => "Krygstan", "Bangladesh" => "Bangladesh", "Nepal" => "Nepal");
											?>
											<option value="">Select country </option>
											<?php
											/*
											if (!empty($admissionpreferences)) {
												$countries = $admissionpreferences->study_country;
												$countriesArr = explode(",", $countries);
											}
											foreach ($allCountriesArr as $key => $val) {
											?>
												<option value="<?php echo $key; ?>" <?php echo !empty($admissionpreferences) ? ((in_array($val, $countriesArr)) ? 'selected' : '') : ''; ?>><?php echo $val; ?></option>
											<?php
											}*/
											?>
										</select>
									</div>
								</div>

								<!-- <div class="col-lg-4">
									<div class="form-group">
										<label for="entrance_exam_given">Have You Given any Entrance Exam?</label>
										<select class="form-control" name="entrance_exam_given" id="entrance_exam_given">
											<option value="">Select</option>
											<option value="YES" <?php echo ($admissionpreferences->entrance_exam_given == 'YES') ? 'selected' : ''; ?>>YES</option>
											<option value="NO" <?php echo ($admissionpreferences->entrance_exam_given == 'NO') ? 'selected' : ''; ?>>NO</option>
										</select>
									</div>
								</div> -->
								<div class="col-lg-4">
									<div class="form-group" id="entrance_exam_details_div">
										<label for="exampleInputCourse">Entrance exam details</label>
										<select class="form-control selectpicker" name="entrance_exam_details" id="entrance_exam_details" multiple required>
											<option value="">Select</option>


										</select>
									</div>
								</div>

								<div class="universities">
									<?php
									/*if ($admissionpreferences->university != '') {
										$universitiesArr = json_decode($admissionpreferences->university, true);
										foreach ($universitiesArr as $key => $val) {
											if ($val != '') {
									?>
												<div class="col-lg-4">
													<div class="form-group">
														<label for="university<?php echo $key ?>"><?php  ?> <?php echo $key; ?> University</label>
														<input type="hidden" class="form-control" name="university<?php echo $key ?>" id="university<?php echo $key ?>" value="<?php echo $val ?>" required>
													</div>
												</div>
									<?php
											}
										}
									} */
									?>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<button type="button" id="save_admission_preferences" class="btn btn-primary button-22">Save changes</button>
									<button type="button" id="freeze_admission_preferences" class="btn btn-warning button-22"><?php echo $admissionpreferences->freeze == 0 ? 'Freeze' : 'Unfreeze'; ?></button>
								</div>
							</div>
							<?php // echo form_close(); 
							?>
						</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="academic_details">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="row accadmic-education-div">
								<h4>10th Academic Details</h4>
								<div class="col-lg-3 border2 border1">
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
								<div class="col-lg-3 border2 border1">
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
								<div class="col-lg-2 border2 border1">
									<div class="c1">
										<p>Obtained Percentage / CGPA</p>
									</div>
									<div class="c2">
										<input class="form-control" type="text" class="form-group" placeholder="Enter Percentage / CGPA" name="tenth_percentage" value="<?= $academicdetails->tenth_percentage; ?>">
									</div>
								</div>
							</div>
							<div class="row after accadmic-education-div">

								<label>After Xth Qualification *</label><br>
								<input type="radio" name="after_x_status" <?= ($academicdetails->after_x_status == "12th" ? "checked" : '') ?> <?= empty($academicdetails->after_x_status) ? 'checked' : '' ?> value="12th">&nbsp;&nbsp;12th
								<input type="radio" name="after_x_status" <?= ($academicdetails->after_x_status == "Diploma" ? "checked" : '') ?> value="Diploma">&nbsp;&nbsp;Diploma
								<input type="radio" name="after_x_status" <?= ($academicdetails->after_x_status == "Both" ? "checked" : '') ?> value="Both">&nbsp;&nbsp;Both
							</div>

							<div class="row accadmic-education-div" id="twelthAcademicDetails" style="display:<?= ($academicdetails->after_x_status == '12th' || $academicdetails->after_x_status == 'Both' || empty($academicdetails->after_x_status)) ? 'block' : 'none' ?>">
								<h4>12th Academic Details</h4>
								<div class="col-lg-2 border2 border1">
									<div class="c1">
										<p>School Name</p>
									</div>
									<div class="c2">
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
								<div class="col-lg-2 border2 border1">
									<div class="c1">
										<p>Result Status</p>

									</div>
									<div class="c2">
										<select class="form-control" name="twelth_result_status" id="twelth_result_status">
											<option>Select</option>
											<option value="Awaited" <?= ($academicdetails->twelth_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
											<option value="Declared" <?= ($academicdetails->twelth_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
											<!-- <option value="Not Appeared" <?= ($academicdetails->twelth_result_status == 'Not Appeared') ? 'selected' : ''; ?>>Not Appeared</option> -->


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

							<div class="row accadmic-education-div" id="diplomaAcademicDetails" style="display:<?= ($academicdetails->after_x_status == 'Diploma' || $academicdetails->after_x_status == 'Both') ? 'block' : 'none' ?>">
								<h4>Diploma Academic Details</h4>

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
								<div class="col-lg-2 border2 border1">
									<div class="c1">
										<p>Result Status</p>
									</div>
									<div class="c2">
										<select class="form-control" name="diploma_result_status" id="diploma_result_status">
											<option>Select</option>
											<option value="Awaited" <?= ($academicdetails->diploma_result_status == 'Awaited') ? 'selected' : ''; ?>>Awaited</option>
											<option value="Declared" <?= ($academicdetails->diploma_result_status == 'Declared') ? 'selected' : ''; ?>>Declared</option>
											<!-- <option value="Not Appeared" <?= ($academicdetails->diploma_result_status == 'Not Appeared') ? 'selected' : ''; ?>>Not Appeared</option> -->
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
										<input class="form-control" type="text" class="form-group" placeholder="Enter Course Name" name="graduation_course" value="<?= $academicdetails->graduation_course; ?>" required>
									</div>
								</div>
								<div class="col-lg-2 border2 border1">
									<div class="c1">
										<p>Board / University</p>
									</div>
									<div class="c2">
										<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="graduation_board" value="<?= $academicdetails->graduation_board; ?>" required>
									</div>
								</div>
								<div class="col-lg-2 border2 border1">
									<div class="c1">
										<p>Year of Passing </p>
									</div>
									<div class="c2">
										<select class="form-control" name="graduation_passing_year" id="graduation_passing_year" required>
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
										<select class="form-control" name="graduation_result_status" id="graduation_result_status" required>
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

							<hr>
							<?php
							$entrance_names = array_values(array_filter(explode(",", $admissionpreferences->entrance_exam_details), 'strlen'));
							$entrance_data = array_column($entrance_data, null, 'id');
							?>
							<div id="entrance_exam_div" class="row accadmic-education-div <?php echo ($admissionpreferences->entrance_exam_details == '') ? 'hide' : ''; ?>">
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
										<input class="form-control" type="text" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> class="form-group" placeholder="Enter Entrance Roll No" name="entrance_roll" value="<?= $academicdetails->entrance_roll; ?>">
									</div>
									<div class="c2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
										<input class="form-control" type="text" class="form-group" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'disabled' : ''; ?> placeholder="Enter Entrance Roll No" name="entrance_roll_1" value="<?= !empty($academicdetails->entrance_roll_1) ? $academicdetails->entrance_roll_1 : ''; ?>">
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
									<div class="c2 hide_">
										<input type="text" <?= ($academicdetails->entrance_result_status == 'Not Appeared') ? 'disabled' : ''; ?> class="form-control" placeholder="Marks/ AIR" name="entrance_percentage" id="entrance_percentage" value="<?= $academicdetails->entrance_percentage; ?>">

										<?php

										if (!empty($score_columns)) {
											foreach ($score_columns as $column) {
												if (!empty($entrance_data[$entrance_names[0]]["id"]) && $entrance_data[$entrance_names[0]]["id"] == $column["exam_type"]) {
										?>
													<input type="text" style="margin-top:3px" class="form-control column_score" placeholder="<?= $column['name'] ?>" name="score_column-<?= $column["id"] ?>" id="score_column-<?= $column["id"] ?>" value="<?= !empty($score_value[$column["id"]]["value"]) ? $score_value[$column["id"]]["value"] : '' ?>">

										<?php
												}
											}
										}

										// score_columns
										?>
									</div>
									<div class="c2 hide_2" style="display:<?= !empty($entrance_data[$entrance_names[1]]) ? 'block' : 'none'; ?>" ;>
										<input type="text" <?= ($academicdetails->entrance_result_status_1 == 'Not Appeared') ? 'disabled' : ''; ?> class="form-control " placeholder="Marks/ AIR" name="entrance_percentage_1" id="entrance_percentage_1" value="<?= $academicdetails->entrance_percentage_1; ?>">

										<?php


										if (!empty($score_columns)) {
											foreach ($score_columns as $column) {
												if (!empty($entrance_data[$entrance_names[1]]["id"]) && $entrance_data[$entrance_names[1]]["id"] == $column["exam_type"]) {
										?>
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
							<!-- end ug details-->
							<hr>
							<?php // if($basicdetails->entrance_exam_details !=''){ 
							?>

							<div class="row" style="padding-top: 30px;padding-bottom: 20px;">
								<div class="col-lg-6 col-xs-6" style="padding-left: 0px;">
									<!-- <a href="/clients/academic_details" class="btn btn-primary button-23">Edit Academic Details</a> -->
									<!-- <button type="submit" class="btn btn-primary">Save & Next</button> -->
								</div>
								<div class="col-lg-6 col-xs-6" style="padding-right: 0px;">
									<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
									<!-- <a href="/clients/upload_documents" class="btn btn-primary button-23 pull-right">Next</a> -->
								</div>
							</div>
							<?php // echo form_close(); 
							?>
						</div>
					</div>
				</div>
				<div class="">
					<div class="col-md-12">
						<button type="button" id="save_admission_details" class="btn btn-primary button-22">Save changes</button>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="documents">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<table class="table datatable">
								<tbody class="document_upload_div ">
									<?php
									$document_files = !empty($documents[0]["documents"]) ? json_decode($documents[0]["documents"], true) : '';
									if (!empty($document_files)) {
										foreach ($document_files as $doc_files) {
											$doc_type = $doc_files["title"]
									?>
											<tr class="row">
												<td class="col-6">
													<?= $doc_type ?>
												</td>
												<td class="col-6">
													<a class="col-md-12 download_document" accept="image/*,application/pdf" href="javascript:void(0);" onclick="window.open(`<?= base_url($doc_files['file_path']) ?>`, '_blank');" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
												</td>
											</tr>
										<?php
										}
									} else {
										?>
										<tr class="row">
											<td class="col-12" colspan="2">
												<h3 class="text-center">No Documents</h3>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="declaration">
				<div class="row">
					<div class="col-md-12">
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
									<!-- <a href="/clients/declaration" class="btn btn-primary button-23">Edit Declaration Details</a> -->
									<!-- <button type="submit" class="btn btn-primary ">Save & Next</button> -->
								</div>
								<div class="col-lg-6 col-xs-6">
									<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
									<!-- <a href="/clients/invoices" class="btn btn-primary button-23 pull-right">Final Submit</a> -->
								</div>
							</div>
							<?php // echo form_close(); 
							?>
						</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="billing_and_shipping">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<h4 class="no-mtop"><?php echo _l('billing_address'); ?> <a href="#" class="pull-right billing-same-as-customer"><small class="font-medium-xs"><?php echo _l('customer_billing_same_as_profile'); ?></small></a></h4>
								<hr />
								<?php $value = (isset($client) ? $client->billing_street : ''); ?>
								<?php echo render_textarea('billing_street', 'billing_street', $value); ?>
								<?php $value = (isset($client) ? $client->billing_city : ''); ?>
								<?php echo render_input('billing_city', 'billing_city', $value); ?>
								<?php $value = (isset($client) ? $client->billing_state : ''); ?>
								<?php echo render_input('billing_state', 'billing_state', $value); ?>
								<?php $value = (isset($client) ? $client->billing_zip : ''); ?>
								<?php echo render_input('billing_zip', 'billing_zip', $value); ?>
								<?php $selected = (isset($client) ? $client->billing_country : ''); ?>
								<?php echo render_select('billing_country', $countries, array('country_id', array('short_name')), 'billing_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex'))); ?>
							</div>
							<div class="col-md-6">
								<h4 class="no-mtop">
									<i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('customer_shipping_address_notice'); ?>"></i>
									<?php echo _l('shipping_address'); ?> <a href="#" class="pull-right customer-copy-billing-address"><small class="font-medium-xs"><?php echo _l('customer_billing_copy'); ?></small></a>
								</h4>
								<hr />
								<?php $value = (isset($client) ? $client->shipping_street : ''); ?>
								<?php echo render_textarea('shipping_street', 'shipping_street', $value); ?>
								<?php $value = (isset($client) ? $client->shipping_city : ''); ?>
								<?php echo render_input('shipping_city', 'shipping_city', $value); ?>
								<?php $value = (isset($client) ? $client->shipping_state : ''); ?>
								<?php echo render_input('shipping_state', 'shipping_state', $value); ?>
								<?php $value = (isset($client) ? $client->shipping_zip : ''); ?>
								<?php echo render_input('shipping_zip', 'shipping_zip', $value); ?>
								<?php $selected = (isset($client) ? $client->shipping_country : ''); ?>
								<?php echo render_select('shipping_country', $countries, array('country_id', array('short_name')), 'shipping_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex'))); ?>
							</div>
							<?php if (
								isset($client) &&
								(total_rows(db_prefix() . 'invoices', array('clientid' => $client->userid)) > 0 || total_rows(db_prefix() . 'estimates', array('clientid' => $client->userid)) > 0 || total_rows(db_prefix() . 'creditnotes', array('clientid' => $client->userid)) > 0)
							) { ?>
								<div class="col-md-12">
									<div class="alert alert-warning">
										<div class="checkbox checkbox-default">
											<input type="checkbox" name="update_all_other_transactions" id="update_all_other_transactions">
											<label for="update_all_other_transactions">
												<?php echo _l('customer_update_address_info_on_invoices'); ?><br />
											</label>
										</div>
										<b><?php echo _l('customer_update_address_info_on_invoices_help'); ?></b>
										<div class="checkbox checkbox-default">
											<input type="checkbox" name="update_credit_notes" id="update_credit_notes">
											<label for="update_credit_notes">
												<?php echo _l('customer_profile_update_credit_notes'); ?><br />
											</label>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>
<?php if (isset($client)) { ?>
	<?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
		<div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog">
			<div class="modal-dialog">
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

<?php $this->load->view('admin/clients/client_group'); ?>
<script>
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
</script>