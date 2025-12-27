<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$states = get_states();
$yesNO_Array = [array("id" => 0, "name" => "No"), array("id" => 1, "name" => "Yes")];
$countryCode = get_country_code();
?>
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
</style>

<?php
$relationshipArray = [];
$degreeArray = [];
$examList = [];
if ($lead->type == 1) {
   $relationshipArray = get_relationShip();
   $degreeArray = get_degree();
   $examList = get_examList();
}
?>
<div class="modal fade" id="convert_lead_to_client_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog modal-lg" role="document">
      <?php echo form_open('admin/leads/convert_to_customer', array('id' => 'lead_to_client_form')); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">
               <?php echo _l('lead_convert_to_client'); ?>
            </h4>
         </div>
         <div class="modal-body">
            <?php echo form_hidden('leadid', $lead->id); ?>
            <?php if (mb_strpos($lead->name, ' ') !== false) {
               $_temp = explode(' ', $lead->name);
               $firstname = $_temp[0];
               if (isset($_temp[2])) {
                  $lastname = $_temp[1] . ' ' . $_temp[2];
               } else {
                  $lastname = $_temp[1];
               }
            } else {
               $lastname = '';
               $firstname = $lead->name;
            }
            ?>

            <div class="clearfix"></div>
            <hr class="mtop5 mbot10" />
            <div class="row">

               <?php echo form_hidden('default_language', $lead->default_language); ?>
               <div class="row col-md-12">
                  <div class="col-lg-4 col-md-6 col-12">
                     <?php echo render_input('firstname', 'lead_convert_to_client_firstname', $firstname); ?>
                  </div>
                  <div class="col-lg-4 col-md-6 col-12">
                     <?php echo render_input('lastname', 'lead_convert_to_client_lastname', $lastname); ?>
                  </div>

                  <div class="col-lg-4 col-md-6 col-12">
                     <?php echo render_input('email', 'lead_convert_to_email', $lead->email); ?>
                  </div>
               </div>
               <div class="row col-md-12">

 <div class="col-lg-3 col-md-6 col-12">

                     <?php
                     array_unshift($countryCode, ['name' => '']); // Add an empty option at the beginning

                     echo render_select(
                        'a_country',
                        $countryCode,
                        ['country_id', 'short_name'],
                        'Applicant Country <small class="text-danger">*</small>',
                        '',
                        [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Applicant Country',
                           'data-actions-box' => true,
                            "required" => "required"
                        ],
                        [],
                        'no-mbot',
                        '',
                        false,
                        'a_country'
                     );


                     // echo render_select('country', $countries, array('country_id', array('short_name')), 'clients_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                     ?>
                  </div>
                   <div class="col-lg-3 col-md-6 col-12">

                     <?php
                     array_unshift($countryCode, ['name' => '']); // Add an empty option at the beginning

                     echo render_select(
                        'country_code',
                        $countryCode,
                        ['country_id', array('short_name','calling_code')],
                        'Country Code <small class="text-danger">*</small>',
                        '',
                        [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Country Code',
                           'data-actions-box' => true,
                           "required" => "required"
                        ],
                        [],
                        'no-mbot',
                        '',
                        false,
                        'country_code'
                     );


                     // echo render_select('country', $countries, array('country_id', array('short_name')), 'clients_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                     ?>
                  </div>
                  <div class="col-lg-2 col-md-6 col-12">
                     <?php echo render_input('phonenumber', 'lead_convert_to_client_phone', $lead->phonenumber, "", ["required" => "required"]); ?>
                  </div>

                  <div class="col-lg-2 col-md-6 col-12">
                     <?php echo render_input('city', 'client_city', $lead->city); ?>
                  </div>
                  <div class="col-lg-2 col-md-6 col-12">

                     <?php
                     array_unshift($states, ['name' => '']); // Add an empty option at the beginning

                     echo render_select(
                        'state',
                        $states,
                        ['name', 'name'],
                        'State',
                        '',
                        [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'States',
                           'data-actions-box' => true,
                          
                        ],
                        [],
                        'no-mbot',
                        '',
                        false,
                        'state'
                     );


                     // echo render_select('country', $countries, array('country_id', array('short_name')), 'clients_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                     ?>
                  </div>
               </div>
               <div class="row col-md-12">
                  <?php if ($lead->type == 2) { ?>
                     <div class="col-lg-4 col-md-6 col-12">
                        <label>University Name <small class="text-danger">*</small></label>
                        <select name="university_name" required id="university_name" class="form-control selectpicker" data-actions-box="true" data-live-search="true" onclick="select_university_country(this)">
                           <option value="">Select university</option>
                           <?php foreach ($university_list as $uni) {
                           ?>
                              <option data-country="<?= $uni["country_name"] ?>" data-mandatory="<?= $uni["fees_mandatory"] ?>" value="<?= $uni['university_name'] ?>"><?= $uni["university_name"] ?> - <?= $uni["country_name"] ?></option>
                           <?php
                           }
                           ?>

                        </select>
                     </div>
                     <div class="col-lg-4 col-md-6 col-12">
                        <label>Country/Destination Name <small class="text-danger">*</small></label>
                        <input type="text" required name="university_country" id="university_country" class="form-control" readonly placeholder="Country/Destination" value="">
                     </div>
                  <?php } ?>
                  <div class="col-lg-4 col-md-6 col-12">
                     <label>Passport Status <small class="text-danger">*</small></label>
                     <select name="Passport_status" required id="Passport_status" class="form-control">
                        <option value="">Select Passport</option>
                        <?php foreach ($passpost_status as $pass) {
                        ?>
                           <option value="<?= $pass['id'] ?>"><?= $pass["name"] ?></option>
                        <?php
                        }
                        ?>

                     </select>
                  </div>


               </div>

               <div class="mtop15 mbot10 col-md-12">
                  <div class="clearfix"></div>
                  <label>Addition Details</label>
                  <hr class="mtop5 mbot10" />
                  <?php if ($lead->type == 1) { ?>
                     <div class="row">

                        <div class="col-lg-4 col-md-6 col-12 ">
                           <label>Gender <small class="text-danger">*</small></label>
                           <select name="gender" required id="gender" class="form-control">
                              <option value="">Select Gender</option>
                              <option <?php echo ($basicdetails->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                              <option <?php echo ($basicdetails->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                              <option <?php echo ($basicdetails->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                           </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                           <?php echo render_input('dob', 'DOB <span class="req text-danger">*</span>', '', 'date', ["required" => "required"]); ?>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 ">
                           <label>Marital Status <small class="text-danger">*</small></label>
                           <select name="marital_status" required id="marital_status" class="form-control">
                              <option value="">Select Marital</option>
                              <?php foreach ($yesNO_Array as $marital_s) {
                              ?>
                                 <option value="<?= $marital_s['id'] ?>"><?= $marital_s["name"] ?></option>
                              <?php
                              }
                              ?>

                           </select>
                        </div>


                     </div>
                     <div class="row">
                        <div class="col-lg-4 col-md-6 col-12 form-group">
                           <label>Budget Range <small class="text-danger">*</small></label>
                           <select name="budget_range" required id="budget_range" class="form-control">
                              <option value="">Select Budget</option>
                              <option value="10-15">10 - 15 Lac</option>
                              <option value="15-20">15 - 20 Lac</option>
                              <option value="20-25">20 - 25 Lac</option>
                              <option value="25-above">25 Lac and Above</option>
                           </select>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12 form-group ">
                           <label>Loan Requried <small class="text-danger">*</small></label>
                           <select name="loan_required" required id="loan_required" class="form-control">
                              <?php foreach ($yesNO_Array as $loan_s) {
                              ?>
                                 <option value="<?= $loan_s['id'] ?>"><?= $loan_s["name"] ?></option>
                              <?php
                              }
                              ?>

                           </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 form-group">
                           <label>Tagging <small class="text-danger">*</small></label>
                           <select name="tagging" required id="tagging" class="form-control">
                              <?php foreach ($yesNO_Array as $loan_s) {
                              ?>
                                 <option value="<?= $loan_s['id'] ?>"><?= $loan_s["name"] ?></option>
                              <?php
                              }
                              ?>

                           </select>
                        </div>

                        <div class="col-lg-4 form-group">
                           <div class="">
                              <label for="session_intake">Session Intake <small class="text-danger">*</small></label>
                              <input type="month" class="form-control" required required-check id="session_intake" name="session_intake"
                                 value=""
                                 placeholder="Select Month and Year">
                           </div>
                        </div>

                        <div class="col-lg-4 form-group">
                           <div class="">
                              <label for="degree">Degree <small class="text-danger">*</small></label>
                              <select name="degree" required id="degree" class="form-control">
                                 <option value="">Select Degree</option>
                                 <?php foreach ($degreeArray as $degree) {
                                 ?>
                                    <option value="<?= $degree['id'] ?>"><?= $degree["name"] ?></option>
                                 <?php
                                 }
                                 ?>

                              </select>
                           </div>
                        <?php } ?>
                        </div>


                     </div>

                     <?php if ($lead->type == 1) { ?>
                        <div class="row">
                           <div class="col-md-2">
                              <div class="checkbox">
                                 <input class="form-check-input checkbox-group" type="checkbox"  onchange="change_refusal(this)" value="1" id="visa_refusal" name="visa_refusal">
                                 <label class="form-check-label" for="visa_refusal">
                                    Visa Refusal
                                 </label>
                              </div>
                           </div>
                           <div id="visa-refusal-data" style="display:none">
                              <div class="col-md-3">
                                 <?php echo render_input('visa_year', 'Visa Year <small class="text-danger">*</small>', '', '', ["placeholder" => "Visa Year"]); ?>

                              </div>

                              <div class="col-md-5">
                                 <?php echo render_input('visa_country', 'Visa Country <small class="text-danger">*</small>', '', '', [ "placeholder" => "Visa Country"]); ?>

                              </div>
                           </div>
                        </div>
                     <?php } ?>

                     <?php if ($lead->source == REFERENCE_ID) { ?>
                        <div class="col-lg-4 col-md-6 col-12 form-group">
                           <label></label>
                           <?php echo render_input('reference_name', 'Reference Name <small class="text-danger">*</small>', !empty($lead->reference_name) ? $lead->reference_name : '', '', ["required" => "required", "placeholder" => "reference Name"]); ?>
                        </div>
                     <?php } ?>
                     <!-- 
               <div class="col-lg-4 col-md-6 col-12">
                  <?php
                  $countries = get_all_countries();
                  $customer_default_country = get_option('customer_default_country');
                  $selected = ($lead->country != 0 ? $lead->country : $customer_default_country);
                  echo render_select('country', $countries, array('country_id', array('short_name')), 'clients_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                  ?>
               </div> -->
                     <div class="clearfix"></div>
                     <br>
                     <?php if ($lead->type == 2) { ?>
                        <div class="col-lg-12 col-md-12 col-12 row">
                           <?php echo render_textarea('address', 'client_address', $lead->address); ?>
                        </div>
                     <?php } ?>
                     <div class="clearfix"></div>
                     <div class="mtop15 mbot10 col-md-12 row">

                        <?php if ($lead->type == 1) { ?>
                           <label>Emergency Details</label>
                           <hr class="mtop5 mbot10" />
                           <div class="row">
                              <div class="col-lg-3 col-md-6 col-12">
                                 <?php echo render_input('father_name', 'Emergency Contact Name <span class="req text-danger">*</span>', '', 'text', ["required" => "required"], [], '', 'name-validation-check'); ?>
                              </div>
                              <div class="col-lg-3 col-md-6 col-12">
                                 <?php echo render_input('fathers_mobile', 'Emergency Contact Number <span class="req text-danger">*</span>', '', 'number', ["required" => "required"], [], '', 'phone-validation-check'); ?>
                              </div>

                              <div class="col-lg-3 col-md-6 col-12">
                                 <?php echo render_input('fathers_email', 'Emergency Contact Email <span class="req text-danger">*</span>', '', 'email', ["required" => "required"], [], '', 'email-validation-check'); ?>
                              </div>
                              <div class="col-lg-3 col-md-6 col-12">
                                 <label>RelationShip <small class="text-danger">*</small></label>
                                 <select name="relationship_id" required id="relationship_id" class="form-control">
                                    <option value="">Select RelationShip</option>
                                    <?php foreach ($relationshipArray as $relation) {
                                    ?>
                                       <option value="<?= $relation['id'] ?>"><?= $relation["name"] ?></option>
                                    <?php
                                    }
                                    ?>

                                 </select>
                              </div>
                           </div>
                        <?php } ?>

                        <?php $rel_id = (isset($lead) ? $lead->id : false);
                        ?>
                        <?php echo render_custom_fields('customers', $rel_id, "", "", (isset($lead->type) ? $lead->type : ''), $lead, 1); ?>
                     </div>
               </div>

               <div class="clearfix">

                  <br>
                  <div class="row">
                     <?php
                     $not_mergable_customer_fields  = array('userid', 'datecreated', 'leadid', 'default_language', 'default_currency', 'active');
                     $not_mergable_contact_fields  = array('id', 'userid', 'datecreated', 'is_primary', 'password', 'new_pass_key', 'new_pass_key_requested', 'last_ip', 'last_login', 'last_password_change', 'active', 'profile_image', 'direction');
                     $customer_fields = $this->db->list_fields(db_prefix() . 'clients');
                     $contact_fields = $this->db->list_fields(db_prefix() . 'contacts');
                     $custom_fields = get_custom_fields('leads');
                     $found_custom_fields = false;
                     foreach ($custom_fields as $field) {
                        $value = get_custom_field_value($lead->id, $field['id'], 'leads');
                        if ($value == '') {
                           continue;
                        } else {
                           $found_custom_fields = true;
                        }
                     }

                     ?>
                  </div>

                  <?php echo form_hidden('original_lead_email', $lead->email); ?>
                  <?php
                  $get_clients_fees = get_clients_fees((isset($lead) ? $lead->type : ''));

                  $get_currencies = get_currencies();
                  $get_currencies = array_column($get_currencies, null, 'id');

                  if (!empty($get_clients_fees) && !empty($get_currencies)) {
                  ?>
                     <div id="applicant_fees" class="col-md-12">
                        <label>Fees Details</label>
                        <hr class="mtop5 mbot10" />
                        <div class="row">
                           <?php
                           foreach ($get_clients_fees as $fees) {
                              $id = $fees["id"];
                              // Prepare the field name by replacing spaces with underscores and converting to lowercase
                              $field_name = strtolower(str_replace(" ", "_", $fees["name"]));
                              // Set the required attribute based on the "mandatry" field
                              $required = !empty($fees["mandatry"]) ? "required" : "false";
                              $mandatry = !empty($fees["mandatry"]) ? "<small class='text-danger'>*</small>" : "";
                              if (!empty($lead->type) && $lead->type == 2 && $fees["id"] == 6) {
                                 $required .= " readonly ";
                              }


                           ?>
                              <div class="col-lg-4 col-md-4 col-6 fees-block-<?= $id ?>">
                                 <label><?= $fees['name'] ?> <?= $mandatry ?><span class="fees_label_<?= $id ?>"></span></label><br>
                                 <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                                    <input type="hidden" value="<?= $field_name ?>" name="applicant_fees[]">
                                    <input type="hidden" value="<?= $fees['id'] ?>" name="<?= $field_name ?>_id">

                                    <div class="input-group-addon currency-symbol-<?= $id ?>"><?= !empty($get_currencies[$fees["default_currency"]]["symbol"]) ? $get_currencies[$fees["default_currency"]]["symbol"] : '$' ?></div>
                                    <input type="text" onkeypress="return acceptText(this,'number')" name="<?= $field_name ?>" <?= $required ?> class="form-control currency-refefees_<?= $fees['id'] ?>" placeholder="0.00" id="<?= $field_name ?>" size="8">
                                    <div class="input-group-addon currency-addon">

                                       <select name="<?= $field_name ?>_currency_type" id="<?= $field_name ?>" class="currency-selector currency-selector-<?= $id ?>" onchange="updateSymbol(<?= $id ?>)">
                                          <?php foreach ($get_currencies as $c) {

                                          ?>
                                             <option data-symbol="<?= $c["symbol"] ?>" value="<?= $c['id'] ?>" data-placeholder="0.00" <?= !empty($fees["default_currency"]) && $fees["default_currency"] == $c["id"]  ? "selected" : "" ?>><?= $c["name"] ?></option>
                                          <?php
                                          }
                                          ?>

                                       </select>

                                    </div>
                                 </div>
                              </div>
                           <?php
                           }
                           ?>

                           <?php if ($lead->type == 2) { ?>
                              <div class="col-md-12">
                                 <div class="checkbox">
                                    <input type="hidden" value="0" id="air_ticket_include" name="air_ticket_include">
                                    <input class="form-check-input checkbox-group" type="checkbox" value="1" id="air_ticket_include_check" name="air_ticket_include_check">
                                    <label class="form-check-label" for="air_ticket_include_check">
                                       Air ticket inc. in Service Charge <span class="text-danger">*</span>
                                    </label>
                                 </div>
                              </div>
                           <?php } ?>
                        </div>
                     </div>
                  <?php } ?>
                  <div class="clearfix"></div>
                  <?php if ($lead->type == 1) { ?>
                     <hr class="mtop5 mbot10" />
                     <!-- <div class="mtop15 mbot10 col-md-12">
                        <div class="clearfix"></div>
                        <label>Exam Details</label>
                        <hr class="mtop5 mbot10" />
                        <div class="col-lg-4 col-md-6 col-12 ">
                           <label>ELT Status <small class="text-danger">*</small></label>
                           <select name="exam_status" required id="exam_status" class="form-control">
                              <?php foreach ($yesNO_Array as $exam_s) {
                              ?>
                                 <option value="<?= $exam_s['id'] ?>"><?= $exam_s["name"] ?></option>
                              <?php
                              }
                              ?>

                           </select>
                        </div>
                        <div class="col-md-12 mtop5 mbot10 exams-details-section">

                        </div>

                     </div> -->
                  <?php } ?>

                  <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                  <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1" />
                  <!--<input type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1" />-->

                  <!--<div class="client_password_set_wrapper">-->
                  <!--   <label for="password" class="control-label"><?php echo _l('client_password'); ?></label>-->
                  <!--   <div class="input-group">-->
                  <!--      <input type="password" class="form-control password" name="password" autocomplete="off">-->
                  <!--      <span class="input-group-addon">-->
                  <!--         <a href="#password" class="show_password" onclick="showPassword('password');return false;"><i class="fa fa-eye"></i></a>-->
                  <!--      </span>-->
                  <!--      <span class="input-group-addon">-->
                  <!--         <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>-->
                  <!--      </span>-->
                  <!--   </div>-->
                  <!--</div>-->
                  <?php if (total_rows(db_prefix() . 'emailtemplates', array('slug' => 'contact-set-password', 'active' => 0)) == 0) { ?>
                     <!--<div class="checkbox checkbox-primary">-->
                     <!--   <input type="checkbox" name="send_set_password_email" id="send_set_password_email">-->
                     <!--   <label for="send_set_password_email">-->
                     <!--      <?php echo _l('client_send_set_password_email'); ?>-->
                     <!--   </label>-->
                     <!--</div>-->
                  <?php } ?>
                  <?php if (total_rows(db_prefix() . 'emailtemplates', array('slug' => 'new-client-created', 'active' => 0)) == 0) { ?>
                     <!--<div class="checkbox checkbox-primary hide">-->
                     <!--   <input type="checkbox" checked name="donotsendwelcomeemail" id="donotsendwelcomeemail">-->
                     <!--   <label for="donotsendwelcomeemail"><?php echo _l('client_do_not_send_welcome_email'); ?></label>-->
                     <!--</div>-->
                  <?php } ?>
                  <?php if (total_rows(db_prefix() . 'notes', array('rel_type' => 'lead', 'rel_id' => $lead->id)) > 0) { ?>
                     <!--<div class="checkbox checkbox-primary">-->
                     <!--   <input type="checkbox" name="transfer_notes" id="transfer_notes">-->
                     <!--   <label for="transfer_notes"><?php echo _l('transfer_lead_notes_to_customer'); ?></label>-->
                     <!--</div>-->
                  <?php } ?>
                  <?php if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1' && count($purposes) > 0) { ?>
                     <!--<div class="checkbox checkbox-primary">-->
                     <!--   <input type="checkbox" name="transfer_consent" id="transfer_consent">-->
                     <!--   <label for="transfer_consent"><?php echo _l('transfer_consent'); ?></label>-->
                     <!--</div>-->
                  <?php } ?>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" onclick="init_lead(<?php echo $lead->id; ?>); return false;" data-dismiss="modal"><?php echo _l('back_to_lead'); ?></button>
                  <button type="submit" data-form="#lead_to_client_form" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('submit'); ?></button>
               </div>
            </div>
            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
   <script>
      validate_lead_convert_to_client_form();
      init_selectpicker();


      $(document).ready(function() {
         let feesMandatory_single = "";
         <?php if ($lead->source == REFERENCE_ID) { ?>
            feesMandatory_single = "<?= REFERENCE_AMOUNT_ID ?>"; // Concatenating the value
         <?php } else {
         ?>
            $(".fees-block-<?= REFERENCE_AMOUNT_ID ?>").hide();

         <?php
         } ?>
         if (feesMandatory_single != "") {
            let mandatoryArray = feesMandatory_single.split(',').map(item => item.trim()); // Split and trim values

            mandatoryArray.forEach(item => {
               let feeElement = $(".fees_" + item);
               let feeLabel = $(".fees_label_" + item);

               feeElement.addClass("external_requried").attr("required", true);
               feeLabel.addClass("external_requried_label").html("<small class='text-danger'>*</small>");
            });
            validate_lead_convert_to_client_form();
         }
         loadValidations();
      })

      function updateSymbol(id) {
         var selected = $(".currency-selector-" + id + " option:selected");
         $(".currency-symbol-" + id).text(selected.data("symbol"));
      }

      $("#university_name").change(function() {
         // Remove required attribute and reset classes
         $(".external_requried_label").closest('div').find("#inlineFormInputGroup-error").remove();
         $(".external_requried").removeAttr("required").removeClass("external_requried");
         $(".external_requried_label").removeClass("external_requried_label").html("");
         $("#inlineFormInputGroup-error").remove();

         let selectedOption = $(this).find(":selected");
         let selectedValue = selectedOption.val(); // Get selected value
         let countryName = selectedOption.data("country"); // Get selected option's data-country attribute
         let feesMandatory = selectedOption.data("mandatory"); // Get selected option's data-mandatory attribute

         <?php if ($lead->source == REFERENCE_ID) { ?>
            feesMandatory += ",<?= REFERENCE_AMOUNT_ID ?>"; // Concatenating the value
         <?php } ?>

         if (feesMandatory) {
            let mandatoryArray = feesMandatory.split(',').map(item => item.trim()); // Split and trim values

            mandatoryArray.forEach(item => {
               let feeElement = $(".fees_" + item);
               let feeLabel = $(".fees_label_" + item);

               feeElement.addClass("external_requried").attr("required", true);
               feeLabel.addClass("external_requried_label").html("<small class='text-danger'>*</small>");
            });
         }

         if (countryName.toLowerCase() === "georgia") {
            // $("#air_ticket_include_check").removeAttr("onclick="return false;");
            $("#air_ticket_include_check").removeAttr("onclick");
            $("#air_ticket_include_check").prop("checked", false);
            $("#air_ticket_include").val(0);

         } else {
            // $("#air_ticket_include_check").attr("disabled", true);
            $("#air_ticket_include_check").attr("onclick", "return false;");
            $("#air_ticket_include_check").prop("checked", true);
            $("#air_ticket_include").val(1);

         }

         $("#university_country").val(countryName || ""); // Set university country value, default to empty string if undefined
         validate_lead_convert_to_client_form();
      });

      var examList = <?= !empty($examList) ? json_encode($examList, true) : '[]' ?>;

      $(document).ready(function() {
         // Load exam list from PHP for potential future use

         // Trigger when exam_status is changed
         $("#exam_status").change(function() {
            let status = $(this).val();
            if (status == 1) {
               $(".exams-details-section").html(''); // Clear section
               addExam(); // Add initial exam input set
            } else {
               $(".exams-details-section").html(''); // Clear if status is not 1
            }
            validate_lead_convert_to_client_form();
         });

         // Function to add an exam input group
         // function addExam() {
         // $(".add-exam-button").addClass("hide");

         // let examGroup = `
         // <div class="exam-group row mb-2 mtop5 mbot10">
         // <div class="col-lg-4 col-md-6 col-12">
         // <select name="exam_type[]" required class="form-control selectpicker">
         // <?php foreach ($examList as $exams) { ?>
         // <option value="<?= $exams['id'] ?>"><?= $exams["name"] ?></option>
         // <?php } ?>
         // </select>
         // </div>
         // <div class="col-lg-4 col-md-6 col-12">
         // <input type="number" step="any" name="exam_marks[]" class="form-control" required>
         // </div>
         // <div class="col-lg-4 col-md-12 col-12 d-flex align-items-end">
         // <button type="button" class="btn btn-danger remove-exam"><i class="fa fa-trash"></i></button>
         // <button type="button" onclick="addExam()" class="btn btn-primary hide add-exam-button"><i class="fa fa-plus"></i></button>
         // </div>
         // </div>`;
         // $('.exams-details-section').append(examGroup);

         // $('.exams-details-section .selectpicker').selectpicker();

         // $('.exams-details-section .exam-group').first().find(".add-exam-button").removeClass("hide");
         // }

         // // Delegated event handler for removing exam groups
         // $(document).on('click', '.remove-exam', function() {
         // $(this).closest('.exam-group').remove();
         // $(".add-exam-button").addClass("hide");
         // $('.exams-details-section .exam-group').first().find(".add-exam-button").removeClass("hide");
         // });
      });

      $(document).ready(function() {
         // Function to add an exam input group
         window.addExam = function() {
            // Hide all current "+" buttons
            $(".add-exam-button").addClass("hide");

            let examGroup = `
      <div class="exam-group row mb-2 mtop5 mbot10">
         <div class="col-lg-4 col-md-6 col-12">
            <label>Exam Name <span class='text-danger req'>*</span></label>
            <select name="exam_type[]" required class="form-control selectpicker">
               <option value="">Select Exam</option>
               <?php foreach ($examList as $exams) { ?>
                  <option value="<?= $exams['id'] ?>"><?= $exams["name"] ?></option>
               <?php } ?>
            </select>
         </div>
         <div class="col-lg-4 col-md-6 col-12">
            <label>Exam Marks <span class='text-danger req'>*</span></label>
            <input type="number" step="any" name="exam_marks[]" class="form-control" required>
         </div>
         <div class="col-lg-4 col-md-12 col-12 align-items-end gap-2">
            <p>&nbsp;</p>
            <button type="button" class="btn btn-danger remove-exam"><i class="fa fa-trash"></i></button>
            <button type="button" class="btn btn-primary add-exam-button" onclick="addExam()"><i class="fa fa-plus"></i></button>
         </div>
      </div>
      `;

            $('.exams-details-section').append(examGroup);

            // Refresh selectpicker
            $('.selectpicker').selectpicker('refresh');

            // Show "+" button only on the last group
            updateAddButtonVisibility();
            updateExamTypeOptions(); // Optional: keep unique exam selections
            validate_lead_convert_to_client_form();

         };

         // Remove exam group
         $(document).on('click', '.remove-exam', function() {
            $(this).closest('.exam-group').remove();
            updateAddButtonVisibility();
            updateExamTypeOptions();
         });

         // Handle dynamically added "+" button

         // Show "+" only on the last exam group
         function updateAddButtonVisibility() {
            $(".add-exam-button").addClass("hide");
            $('.exams-details-section .remove-exam').removeClass("hide");
            $('.exams-details-section .exam-group').first().find(".remove-exam").addClass("hide");
            $('.exams-details-section .exam-group').first().find(".add-exam-button").removeClass("hide");
         }

         // Optional: disable duplicate options
         function updateExamTypeOptions() {
            let selectedValues = [];

            $("select[name='exam_type[]']").each(function() {
               let val = $(this).val();
               if (val) selectedValues.push(val);
            });

            $("select[name='exam_type[]']").each(function() {
               let currentVal = $(this).val();
               $(this).find("option").each(function() {
                  const val = $(this).val();
                  $(this).prop("disabled", selectedValues.includes(val) && val !== currentVal);
               });
               $(this).selectpicker('refresh');
            });
         }

         // Optional: keep selects clean when changed
         $(document).on("change", "select[name='exam_type[]']", function() {
            updateExamTypeOptions();
         });
      });


      // Optional: check as user changes a select
      $(document).on("change", "select[name='exam_type[]']", function() {
         if (hasDuplicateExamTypes()) {
            alert("You cannot select the same exam name more than once.");
            $(this).val('');
            e.preventDefault();
         }
      });
      // Check for duplicate exam name
      function hasDuplicateExamTypes() {
         let values = [];
         let isDuplicate = false;

         $("select[name='exam_type[]']").each(function() {
            let val = $(this).val();
            if (values.includes(val)) {
               isDuplicate = true;
               $(this).addClass('is-invalid'); // optional: red border
            } else {
               values.push(val);
               $(this).removeClass('is-invalid');
            }
         });

         return isDuplicate;
      }

      function change_refusal(event) {

    const isChecked = $(event).is(':checked');
    const $refusalDiv = $("#visa-refusal-data");
$refusalDiv.find('input')
            .removeAttr('required')
            .val('');
    if (isChecked) {
        // Show section
        $refusalDiv.show();

        // Add required to inputs inside
        $refusalDiv.find('input').attr('required', true);
    } else {
        // Hide section
        $refusalDiv.hide();

        // Remove required and clear values
        
    }
}

   </script>