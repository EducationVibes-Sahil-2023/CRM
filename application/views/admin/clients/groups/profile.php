<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- <script src="https://code.jquery.com/jquery-3.6.3.js"></script> -->

<style>
	.tags-input-wrapper{
		background: transparent;
		padding: 10px;
		border-radius: 4px;
		max-width: 400px;
		border: 1px solid #ccc
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

<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<div class="row">
<?php echo form_open($this->uri->uri_string(),array('class'=>'client-form','autocomplete'=>'off')); ?>
<div class="additional"></div>
<div class="col-md-12">
   <div class="horizontal-scrollable-tabs">
      <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
      <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
      <div class="horizontal-tabs">
         <ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="<?php if(!$this->input->get('tab')){echo 'active';}; ?>">
               <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
               <?php echo _l( 'customer_profile_details'); ?>
               </a>
            </li>
            <?php
               $customer_custom_fields = false;
               if(total_rows(db_prefix().'customfields',array('fieldto'=>'customers','active'=>1)) > 0 ){
                    $customer_custom_fields = true;
                ?>
            <li role="presentation" class="<?php if($this->input->get('tab') == 'custom_fields'){echo 'active';}; ?> hide">
               <a href="#custom_fields" aria-controls="custom_fields" role="tab" data-toggle="tab">
               <?php echo hooks()->apply_filters('customer_profile_tab_custom_fields_text', _l( 'custom_fields')); ?>
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
               <?php echo _l( 'billing_shipping'); ?>
               </a>
            </li>
            <?php hooks()->do_action('after_customer_billing_and_shipping_tab', isset($client) ? $client : false); ?>
            <?php if(isset($client)){ ?>
            <li role="presentation">
               <a href="#customer_admins" aria-controls="customer_admins" role="tab" data-toggle="tab">
               <?php echo _l( 'customer_admins' ); ?>
               </a>
            </li>
            <?php hooks()->do_action('after_customer_admins_tab',$client); ?>
            <?php } ?>
         </ul>
      </div>
   </div>
   <div class="tab-content mtop15">
      <?php hooks()->do_action('after_custom_profile_tab_content',isset($client) ? $client : false); ?>
      <?php if($customer_custom_fields) { ?>
      <div role="tabpanel" class="tab-pane <?php if($this->input->get('tab') == 'custom_fields'){echo ' active';}; ?>" id="custom_fields">
         <?php $rel_id=( isset($client) ? $client->userid : false); ?>
         <?php echo render_custom_fields( 'customers',$rel_id); ?>
      </div>
      <?php } ?>
      <div role="tabpanel" class="tab-pane<?php if(!$this->input->get('tab')){echo ' active';}; ?>" id="contact_info">
         <div class="row">
            <div class="col-md-12 mtop15 <?php if(isset($client) && (!is_empty_customer_company($client->userid) && total_rows(db_prefix().'contacts',array('userid'=>$client->userid,'is_primary'=>1)) > 0)) { echo ''; } else {echo ' hide';} ?>" id="client-show-primary-contact-wrapper">
               <div class="checkbox checkbox-info mbot20 no-mtop">
                  <input type="checkbox" name="show_primary_contact"<?php if(isset($client) && $client->show_primary_contact == 1){echo ' checked';}?> value="1" id="show_primary_contact">
                  <label for="show_primary_contact"><?php echo _l('show_primary_contact',_l('invoices').', '._l('estimates').', '._l('payments').', '._l('credit_notes')); ?></label>
               </div>
            </div>
            <div class="col-md-6">
               <div class="hide">
                  <?php $value=( isset($client) ? $client->company : ''); ?>
                  <?php $attrs = (isset($client) ? array() : array('autofocus'=>true)); ?>
                  <?php echo render_input( 'company', 'client_company',$value,'text',$attrs); ?>
                  <div id="company_exists_info" class="hide"></div>
                  <?php if(get_option('company_requires_vat_number_field') == 1){
                     $value=( isset($client) ? $client->vat : '');
                     echo render_input( 'vat', 'client_vat_number',$value);
                     } ?>
                  <?php if((isset($client) && empty($client->website)) || !isset($client)){
                     $value=( isset($client) ? $client->website : '');
                     echo render_input( 'website', 'client_website',$value);
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
                     if(isset($customer_groups)){
                       foreach($customer_groups as $group){
                          array_push($selected,$group['groupid']);
                       }
                     }
                     if(is_admin() || get_option('staff_members_create_inline_customer_groups') == '1'){
                      echo render_select_with_input_group('groups_in[]',$groups,array('id','name'),'customer_groups',$selected,'<a href="#" data-toggle="modal" data-target="#customer_group_modal"><i class="fa fa-plus"></i></a>',array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                      } else {
                        echo render_select('groups_in[]',$groups,array('id','name'),'customer_groups',$selected,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                      }
                     ?>
                  <?php if(!isset($client)){ ?>
                  <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('customer_currency_change_notice'); ?>"></i>
                  <?php }
                     $s_attrs = array('data-none-selected-text'=>_l('system_default_string'));
                     $selected = '';
                     if(isset($client) && client_have_transactions($client->userid)){
                        $s_attrs['disabled'] = true;
                     }
                     foreach($currencies as $currency){
                        if(isset($client)){
                          if($currency['id'] == $client->default_currency){
                            $selected = $currency['id'];
                         }
                      }
                     }
                            // Do not remove the currency field from the customer profile!
                     echo render_select('default_currency',$currencies,array('id','name','symbol'),'invoice_add_edit_currency',$selected,$s_attrs); ?>
                  <?php if(get_option('disable_language') == 0){ ?>
                  <div class="form-group select-placeholder">
                     <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
                     </label>
                     <select name="default_language" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value=""><?php echo _l('system_default_string'); ?></option>
                        <?php foreach($this->app->get_available_languages() as $availableLanguage){
                           $selected = '';
                           if(isset($client)){
                              if($client->default_language == $availableLanguage){
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
            <div class="col-md-6">
               <?php $value=( isset($client) ? $client->phonenumber : ''); ?>
               <?php echo render_input( 'phonenumber', 'client_phonenumber',$value); ?>
               <?php $value=( isset($client) ? $client->address : ''); ?>
               <?php echo render_textarea( 'address', 'client_address',$value); ?>
               <?php $value=( isset($client) ? $client->city : ''); ?>
               <?php echo render_input( 'city', 'client_city',$value); ?>
               <?php $value=( isset($client) ? $client->state : ''); ?>
               <?php echo render_input( 'state', 'client_state',$value); ?>
               <?php $value=( isset($client) ? $client->zip : ''); ?>
               <?php echo render_input( 'zip', 'client_postal_code',$value); ?>
               <?php $countries= get_all_countries();
                  $customer_default_country = get_option('customer_default_country');
                  $selected =( isset($client) ? $client->country : $customer_default_country);
                  echo render_select( 'country',$countries,array( 'country_id',array( 'short_name')), 'clients_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex')));
                  ?>
            </div>
         </div>
      </div>
      <?php if(isset($client)){ ?>
      <div role="tabpanel" class="tab-pane" id="customer_admins">
         <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
         <a href="#" data-toggle="modal" data-target="#customer_admins_assign" class="btn btn-info mbot30"><?php echo _l('assign_admin'); ?></a>
         <?php } ?>
         <table class="table dt-table">
            <thead>
               <tr>
                  <th><?php echo _l('staff_member'); ?></th>
                  <th><?php echo _l('customer_admin_date_assigned'); ?></th>
                  <?php if(has_permission('customers','','create') || has_permission('customers','','edit')){ ?>
                  <th><?php echo _l('options'); ?></th>
                  <?php } ?>
               </tr>
            </thead>
            <tbody>
               <?php foreach($customer_admins as $c_admin){ ?>
               <tr>
                  <td><a href="<?php echo admin_url('profile/'.$c_admin['staff_id']); ?>">
                     <?php echo staff_profile_image($c_admin['staff_id'], array(
                        'staff-profile-image-small',
                        'mright5'
                        ));
                        echo get_staff_full_name($c_admin['staff_id']); ?></a>
                  </td>
                  <td data-order="<?php echo $c_admin['date_assigned']; ?>"><?php echo _dt($c_admin['date_assigned']); ?></td>
                  <?php if(has_permission('customers','','create') || has_permission('customers','','edit')){ ?>
                  <td>
                     <a href="<?php echo admin_url('clients/delete_customer_admin/'.$client->userid.'/'.$c_admin['staff_id']); ?>" class="btn btn-danger _delete btn-icon"><i class="fa fa-remove"></i></a>
                  </td>
                  <?php } ?>
               </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
      <?php } ?>
      <div role="tabpanel" class="tab-pane" id="student_details">
         <div class="row">
            <div class="col-md-12">
               <div class="card">
                  <?php // echo form_open($this->uri->uri_string()); ?>
                  <h4>Personal Informations</h4>
                  <div class="row">
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="title">Title</label>
                           <select class="form-control" name="title" id="title" readonly>
                              <option value="Mr" <?php echo ($basicdetails->title == 'Mr')? 'selected': ''; ?>>Mr</option>
                              <option value="Ms" <?php echo ($basicdetails->title == 'Ms')? 'selected': ''; ?>>Ms</option>
                              <option value="Mrs" <?php echo ($basicdetails->title == 'Mrs')? 'selected': ''; ?>>Mrs</option>
                              <option value="Dr" <?php echo ($basicdetails->title == 'Dr')? 'selected': ''; ?>>Dr</option>
                           </select>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputFirstName">First Name</label>
                           <input class="form-control" type="text" class="form-group" placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails))?$basicdetails->first_name: $contact->firstname;?>' readonly>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputLastName">Last Name</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails))?$basicdetails->last_name: $contact->lastname;?>' readonly>
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
                           <input type="date" class="form-control" name="dob" value='<?php echo ($basicdetails->dob != '')? $basicdetails->dob : '';?>' required="required" readonly>
                           <?php echo form_error('dob'); ?>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputPassword1">Gender</label>
                           <select class="form-control" name="gender" id="gender" required readonly>
                              <option value="">Select</option>
                              <option <?php echo ($basicdetails->gender == 'Male')? 'selected': ''; ?>>Male</option>
                              <option <?php echo ($basicdetails->gender == 'Female')? 'selected': ''; ?>>Female</option>
                              <option <?php echo ($basicdetails->gender == 'Other')? 'selected': ''; ?>>Other</option>
                           </select>
                           <?php echo form_error('gender'); ?>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputMobileNumber">Father Name</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Father Name" name="father_name" value='<?php echo (isset($basicdetails))?$basicdetails->father_name: '';?>' readonly>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputMobileNumber">Father's Mobile</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Father's Mobile" name="fathers_mobile" value='<?php echo (isset($basicdetails))?$basicdetails->fathers_mobile: '';?>' readonly>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputMobileNumber">Father's Email</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Father's Email" name="fathers_email" value='<?php echo (isset($basicdetails))?$basicdetails->fathers_email: '';?>' readonly>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputMobileNumber">Mother Name</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Mother Name" name="mother_name" value='<?php echo (isset($basicdetails))?$basicdetails->mother_name: '';?>' readonly>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputMobileNumber">Mother's Mobile</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Mother's Mobile" name="mothers_mobile" value='<?php echo (isset($basicdetails))?$basicdetails->mothers_mobile: '';?>' readonly>
                        </div>
                     </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                           <label for="exampleInputMobileNumber">Mother's Email</label>
                           <input class="form-control" type="text" class="form-group" placeholder="Mother's Email" name="mothers_email" value='<?php echo (isset($basicdetails))?$basicdetails->mothers_email: '';?>' readonly>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-6 col-xs-6">
                           <!-- <a href="/clients/basic_details" class="btn btn-primary button-23 pull-right">Edit Basic Details</a> -->
                        </div>
                        <div class="col-lg-6 col-xs-6" >
                           <!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
                           <?php if($basicdetails->id > 1){?>
                           <!-- <a href="/clients/parent_details" class="btn btn-primary button-23 pull-right">Next</a> -->
                           <?php } ?>
                        </div>
                     </div>
                     <?php // echo form_close(); ?>
                  </div>
               </div>
            </div>
         </div>
	</div>
	<div role="tabpanel" class="tab-pane" id="admission_preferences">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<?php // echo form_open($this->uri->uri_string()); ?>
					<h4>Admission Preferences</h4>
					<hr>
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
							<label for="program">Program</label>
							<input type="hidden" name="admissionpreferencesid" id="admissionpreferencesid" value="<?php echo $admissionpreferences->id ?>">
							<select class="form-control" name="program" id="program" required>
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
								<select class="form-control" name="course" id="course">
									<option value="">Select a course </option>

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
								<select class="form-control" name="session_intake" id="session_intake">
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

						<div class="col-lg-4">
							<div class="form-group">
								<input type="hidden" name="countries" id="countries">
								<label for="study_country">Where would you like to study?</label>
								<select class="form-control selectpicker" name="study_country" id="study_country" multiple required>
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
								<select class="form-control" name="entrance_exam_given" id="entrance_exam_given">
									<option value="">Select</option>
									<option value="YES" <?php echo ($admissionpreferences->entrance_exam_given == 'YES')? 'selected': ''; ?>>YES</option>
									<option value="NO" <?php echo ($admissionpreferences->entrance_exam_given == 'NO')? 'selected': ''; ?>>NO</option>
								</select>
							</div>
				  		</div>
						<div class="col-lg-4">
							<div class="form-group" id="entrance_exam_details_div">
								<label for="exampleInputCourse">Entrance exam details</label>
								<select class="form-control" name="entrance_exam_details" id="entrance_exam_details">
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
									foreach($universitiesArr as $key => $val){
										if($val != ''){
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
								}
							?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<button type="button" id="save_admission_preferences" class="btn btn-primary button-22">Save changes</button>
							<button type="button" id="freeze_admission_preferences" class="btn btn-warning button-22"><?php echo $admissionpreferences->freeze == 0 ? 'Freeze' : 'Unfreeze'; ?></button>
						</div>
					</div>
					<?php // echo form_close(); ?>
				</div>
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane" id="academic_details">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
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
							<input type="hidden" name="academicDetailsId" value="<?=$academicdetails->id;?>">
							<input class="form-control" type="text" class="form-group" placeholder="Enter School Name" name="tenth_school_name" value="<?=$academicdetails->tenth_school_name;?>" required>
							<?php echo form_error('academicDetailsId'); ?>
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Board </p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" placeholder="Enter Board Name" name="tenth_board" value="<?=$academicdetails->tenth_board;?>" required>
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Year of Passing 	</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control tenth_passing_year" type="text" placeholder="YYYY" name="tenth_passing_year" id="tenth_passing_year" value="<?=$academicdetails->tenth_passing_year;?>"> -->
							<select class="form-control" name="tenth_passing_year" id="tenth_passing_year">
								<option value="">Select</option>
								<?php for($i = 0; $i<15; $i++): ?>
								<option value="<?=date("Y")-$i;?>" <?=((date("Y")-$i) ==$academicdetails->tenth_passing_year)? 'selected': '' ?>><?=date("Y")-$i;?></option>
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
								<option value="Percentage" <?=($academicdetails->tenth_marking_scheme =='Percentage')? 'selected': ''; ?>>Percentage</option>
								<option value="CGPA out of 10" <?=($academicdetails->tenth_marking_scheme == 'CGPA out of 10')? 'selected': ''; ?>>CGPA out of 10</option>
								<option value="CGPA out of 9" <?=($academicdetails->tenth_marking_scheme == 'CGPA out of 9')? 'selected': ''; ?>>CGPA out of 9</option>
								<option value="CGPA out of 7" <?=($academicdetails->tenth_marking_scheme == 'CGPA out of 7')? 'selected': ''; ?>>CGPA out of 7</option>
								<option value="CGPA out of 4" <?=($academicdetails->tenth_marking_scheme == 'CGPA out of 4')? 'selected': ''; ?>>CGPA out of 4</option>
							</select>
						</div>
					</div>
					<div class="col-lg-3 border2 border1">
						<div class="c1">
							<p>Obtained Percentage / CGPA</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Percentage / CGPA" name="tenth_percentage" maxlength="3" value="<?=$academicdetails->tenth_marking_scheme;?>">
						</div>
					</div>
					</div>
					<div class="row after hide" >
					<label>After Xth Qualification *</label><br>
					<input type="radio" name="after_tenth" value="12th">&nbsp;&nbsp;12th
					<input type="radio" name="after_tenth" value="Diploma">&nbsp;&nbsp;Diploma 
					<input type="radio" name="after_tenth" value="Both">&nbsp;&nbsp;Both
					</div>
					<div class="row <?php echo ($academicdetails->twelth_school_name == '')? 'hide':''; ?>" id="twelthAcademicDetails" >
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
							<input class="form-control" type="text" class="form-group" placeholder="Enter 12th School Name" name="twelth_school_name" value="<?=$academicdetails->twelth_school_name;?>">
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Board / University</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="twelth_board" value="<?=$academicdetails->twelth_board;?>">
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Year of Passing</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control" type="text" placeholder="Enter Passing Year"  name="twelth_passing_year" id="twelth_passing_year" value="<?=$academicdetails->twelth_passing_year;?>"> -->
							<select class="form-control" name="twelth_passing_year" id="twelth_passing_year">
								<option value="">Select</option>
								<?php for($i = 0; $i<15; $i++): ?>
								<option value="<?=date("Y")-$i;?>" <?=((date("Y")-$i) ==$academicdetails->twelth_passing_year)? 'selected': '' ?>><?=date("Y")-$i;?></option>
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
								<option >Select</option>
								<option value="Awaited">Awaited</option>
								<option value="Declared">Declared</option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1 " id="twelth_marking_scheme_div">
						<div class="c1">
							<p>Marking Scheme</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control" type="text" placeholder="CGPA / Percentage" name="twelth_marking_scheme" id="twelth_marking_scheme" value="<?=$academicdetails->twelth_marking_scheme;?>"> -->
							<select class="form-control" name="twelth_marking_scheme" required>
								<option value="">Select</option>
								<option value="Percentage" <?=($academicdetails->twelth_marking_scheme =='Percentage')? 'selected': ''; ?>>Percentage</option>
								<option value="CGPA out of 10" <?=($academicdetails->twelth_marking_scheme == 'CGPA out of 10')? 'selected': ''; ?>>CGPA out of 10</option>
								<option value="CGPA out of 9" <?=($academicdetails->twelth_marking_scheme == 'CGPA out of 9')? 'selected': ''; ?>>CGPA out of 9</option>
								<option value="CGPA out of 7" <?=($academicdetails->twelth_marking_scheme == 'CGPA out of 7')? 'selected': ''; ?>>CGPA out of 7</option>
								<option value="CGPA out of 4" <?=($academicdetails->twelth_marking_scheme == 'CGPA out of 4')? 'selected': ''; ?>>CGPA out of 4</option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Obtained Percentage / CGPA</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" placeholder="Enter Your 12th Percentage" name="twelth_percentage" id="twelth_percentage" value="<?=$academicdetails->twelth_percentage;?>">
						</div>
					</div>
					</div>
					<!-- diploma details-->
					<div class="row <?php echo ($academicdetails->diploma_institute == '')? 'hide':''; ?>" id="diplomaAcademicDetails">
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
							<input class="form-control" type="text" class="form-group" placeholder="Enter Institute Name" name="diploma_institute" value="<?=$academicdetails->diploma_institute;?>">
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Board / University</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="diploma_board" value="<?=$academicdetails->diploma_board;?>">
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Year of Passing 	</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control" type="text" placeholder="Enter Passing Year"  name="diploma_passing_year" value="<?=$academicdetails->diploma_passing_year;?>"> -->
							<select class="form-control" name="diploma_passing_year" id="diploma_passing_year">
								<option value="">Select</option>
								<?php for($i = 0; $i<15; $i++): ?>
								<option value="<?=date("Y")-$i;?>" <?=((date("Y")-$i) ==$academicdetails->diploma_passing_year)? 'selected': '' ?>><?=date("Y")-$i;?></option>
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
								<option >Select</option>
								<option value="Awaited">Awaited</option>
								<option value="Declared">Declared</option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1" id="diploma_marking_scheme_div">
						<div class="c1">
							<p>Marking Scheme 	</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control" type="text" class="form-group" placeholder="CGPA / Percentage" name="diploma_marking_scheme" id="diploma_marking_scheme" value="<?=$academicdetails->diploma_marking_scheme;?>"> -->
							<select class="form-control" name="diploma_marking_scheme" required>
								<option value="">Select</option>
								<option value="Percentage" <?=($academicdetails->diploma_marking_scheme =='Percentage')? 'selected': ''; ?>>Percentage</option>
								<option value="CGPA out of 10" <?=($academicdetails->diploma_marking_scheme == 'CGPA out of 10')? 'selected': ''; ?>>CGPA out of 10</option>
								<option value="CGPA out of 9" <?=($academicdetails->diploma_marking_scheme == 'CGPA out of 9')? 'selected': ''; ?>>CGPA out of 9</option>
								<option value="CGPA out of 7" <?=($academicdetails->diploma_marking_scheme == 'CGPA out of 7')? 'selected': ''; ?>>CGPA out of 7</option>
								<option value="CGPA out of 4" <?=($academicdetails->diploma_marking_scheme == 'CGPA out of 4')? 'selected': ''; ?>>CGPA out of 4</option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Obtained Percentage / CGPA</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Diploma Percentage" name="diploma_percentage" maxlength="3" id="diploma_percentage" value="<?=$academicdetails->diploma_percentage;?>">
						</div>
					</div>
					</div>
					<!-- end diploma details-->
					<!-- Under Graduate details-->
					<div class="row <?php echo ($admissionpreferences->program == 'Post Graduate')? '':'hide'; ?>" id="graduationAcademicDetails">
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
							<p>Institute Name</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Course Name" name="graduation_course" value="<?=$academicdetails->graduation_course;?>">
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Board / University</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Board Name" name="graduation_board" value="<?=$academicdetails->graduation_board;?>">
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Year of Passing 	</p>
						</div>
						<div class="c2">
							<select class="form-control" name="graduation_passing_year" id="graduation_passing_year">
								<option value="">Select</option>
								<?php for($i = 0; $i<15; $i++): ?>
								<option value="<?=date("Y")-$i;?>" <?=((date("Y")-$i) ==$academicdetails->graduation_passing_year)? 'selected': '' ?>><?=date("Y")-$i;?></option>
								<?php endfor; ?>
							</select>
						</div>
					</div>
					<div class="col-lg-1 border2 border1">
						<div class="c1">
							<p>Result Status</p>
						</div>
						<div class="c2">
							<select class="form-control" name="graduation_result_status" id="graduation_result_status">
								<option >Select</option>
								<option value="Awaited">Awaited</option>
								<option value="Declared">Declared</option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1" id="graduation_marking_scheme_div">
						<div class="c1">
							<p>Marking Scheme</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control" type="text" class="form-group" placeholder="CGPA / Percentage" name="graduation_marking_scheme" id="graduation_marking_scheme" value="<?=$academicdetails->graduation_marking_scheme;?>"> -->
							<select class="form-control" name="graduation_marking_scheme" required id="graduation_marking_scheme">
								<option value="">Select</option>
								<option value="Percentage" <?=($academicdetails->graduation_marking_scheme =='Percentage')? 'selected': ''; ?>>Percentage</option>
								<option value="CGPA out of 10" <?=($academicdetails->graduation_marking_scheme == 'CGPA out of 10')? 'selected': ''; ?>>CGPA out of 10</option>
								<option value="CGPA out of 9" <?=($academicdetails->graduation_marking_scheme == 'CGPA out of 9')? 'selected': ''; ?>>CGPA out of 9</option>
								<option value="CGPA out of 7" <?=($academicdetails->graduation_marking_scheme == 'CGPA out of 7')? 'selected': ''; ?>>CGPA out of 7</option>
								<option value="CGPA out of 4" <?=($academicdetails->graduation_marking_scheme == 'CGPA out of 4')? 'selected': ''; ?>>CGPA out of 4</option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Obtained Percentage / CGPA</p>
						</div>
						<div class="c2">
							<input class="form-control" type="text" class="form-group" placeholder="Enter Graduation Percentage" name="graduation_percentage" maxlength="3" id="graduation_percentage" value="<?=$academicdetails->graduation_percentage;?>">
						</div>
					</div>
					</div>
					<!-- end ug details-->
					<hr>
					<?php // if($basicdetails->entrance_exam_details !=''){ ?>
					<div class="row <?php echo ($admissionpreferences->entrance_exam_details == '')? 'hide':''; ?>" >
					<h4>Entrance Exam</h4>
					<div class="col-lg-1 border2 border1">
						<div class="c1">
							<p>&nbsp;</p>
						</div>
						<div class="c2">
							<p><?=$admissionpreferences->entrance_exam_details ;?></p>
						</div>
					</div>
					<div class="col-lg-3 border2 border1">
						<div class="c1">
							<p>Roll No. / Registration No.</p>
						</div>
						<div class="c2">
							<input class="form-control" type="number" class="form-group" placeholder="Enter Entrance Roll No" name="entrance_roll" value="<?=$academicdetails->entrance_roll;?>">
						</div>
					</div>
					<div class="col-lg-3 border2 border1">
						<div class="c1">
							<p>Year</p>
						</div>
						<div class="c2">
							<!-- <input class="form-control" type="text" placeholder="Enter Entrance Year" name="entrance_year" value="<?=$academicdetails->entrance_year;?>"> -->
							<select  name="entrance_year" id="entrance_year">
								<option value="">Select</option>
								<option value="<?=date("Y")-3;?>" <?=((date("Y")-3) ==$academicdetails->entrance_year)? 'selected': '' ?>><?=date("Y")-3;?></option>
								<option value="<?=date("Y")-2;?>" <?=((date("Y")-2) ==$academicdetails->entrance_year)? 'selected': '' ?>><?=date("Y")-2;?></option>
								<option value="<?=date("Y")-1;?>" <?=((date("Y")-1) ==$academicdetails->entrance_year)? 'selected': '' ?>><?=date("Y")-1;?></option>
								<option value="<?=date("Y");?>" <?=((date("Y")) ==$academicdetails->entrance_year)? 'selected': '' ?>><?=date("Y");?></option>
							</select>
						</div>
					</div>
					<div class="col-lg-2 border2 border1">
						<div class="c1">
							<p>Result Status</p>
						</div>
						<div class="c2">
							<select class="form-control" name="entrance_result_status" id="entrance_result_status" >
								<option >Select</option>
								<option value="Awaited" <?=($academicdetails->entrance_result_status=='Awaited')? 'selected':''?>>Awaited</option>
								<option value="Declared" <?=($academicdetails->entrance_result_status=='Declared')? 'selected':''?>>Declared</option>
							</select>
						</div>
					</div>
					<div class="col-lg-3 border2 border1">
						<div class="c1">
							<p>Marks / AIR</p>
						</div>
						<div class="c2">
							<input type="text" class="form-control" placeholder="Marks/ AIR" name="entrance_percentage" id="entrance_percentage" value="<?=$academicdetails->tenth_board;?>">
						</div>
					</div>
					</div>
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
					<?php // echo form_close(); ?>
				</div>
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane" id="document">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
				</div>
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane" id="declaration">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<?php // echo  form_open($this->uri->uri_string()); ?>
				<input type="hidden" name="declarationDetailsId" value="<?=$declarationdetails->id?>">
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
						<input class="form-control" type="text" placeholder="Enter Parent Name" name="father_name" value="<?=$declarationdetails->father_name?>">
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
					<!-- <a href="/clients/declaration" class="btn btn-primary button-23">Edit Declaration Details</a> -->
					<!-- <button type="submit" class="btn btn-primary ">Save & Next</button> -->
				</div>
				<div class="col-lg-6 col-xs-6" >
					<!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button> -->
					<!-- <a href="/clients/invoices" class="btn btn-primary button-23 pull-right">Final Submit</a> -->
				</div>
				</div>
				<?php // echo form_close(); ?>
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
				<?php $value=( isset($client) ? $client->billing_street : ''); ?>
				<?php echo render_textarea( 'billing_street', 'billing_street',$value); ?>
				<?php $value=( isset($client) ? $client->billing_city : ''); ?>
				<?php echo render_input( 'billing_city', 'billing_city',$value); ?>
				<?php $value=( isset($client) ? $client->billing_state : ''); ?>
				<?php echo render_input( 'billing_state', 'billing_state',$value); ?>
				<?php $value=( isset($client) ? $client->billing_zip : ''); ?>
				<?php echo render_input( 'billing_zip', 'billing_zip',$value); ?>
				<?php $selected=( isset($client) ? $client->billing_country : '' ); ?>
				<?php echo render_select( 'billing_country',$countries,array( 'country_id',array( 'short_name')), 'billing_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
				</div>
				<div class="col-md-6">
				<h4 class="no-mtop">
					<i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('customer_shipping_address_notice'); ?>"></i>
					<?php echo _l('shipping_address'); ?> <a href="#" class="pull-right customer-copy-billing-address"><small class="font-medium-xs"><?php echo _l('customer_billing_copy'); ?></small></a>
				</h4>
				<hr />
				<?php $value=( isset($client) ? $client->shipping_street : ''); ?>
				<?php echo render_textarea( 'shipping_street', 'shipping_street',$value); ?>
				<?php $value=( isset($client) ? $client->shipping_city : ''); ?>
				<?php echo render_input( 'shipping_city', 'shipping_city',$value); ?>
				<?php $value=( isset($client) ? $client->shipping_state : ''); ?>
				<?php echo render_input( 'shipping_state', 'shipping_state',$value); ?>
				<?php $value=( isset($client) ? $client->shipping_zip : ''); ?>
				<?php echo render_input( 'shipping_zip', 'shipping_zip',$value); ?>
				<?php $selected=( isset($client) ? $client->shipping_country : '' ); ?>
				<?php echo render_select( 'shipping_country',$countries,array( 'country_id',array( 'short_name')), 'shipping_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
				</div>
				<?php if(isset($client) &&
				(total_rows(db_prefix().'invoices',array('clientid'=>$client->userid)) > 0 || total_rows(db_prefix().'estimates',array('clientid'=>$client->userid)) > 0 || total_rows(db_prefix().'creditnotes',array('clientid'=>$client->userid)) > 0)){ ?>
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
<?php if(isset($client)){ ?>
<?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
<div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open(admin_url('clients/assign_admins/'.$client->userid)); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
         </div>
         <div class="modal-body">
            <?php
               $selected = array();
               foreach($customer_admins as $c_admin){
                  array_push($selected,$c_admin['staff_id']);
               }
               echo render_select('customer_admins[]',$staff,array('staffid',array('firstname','lastname')),'',$selected,array('multiple'=>true),array(),'','',false); ?>
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

</script>