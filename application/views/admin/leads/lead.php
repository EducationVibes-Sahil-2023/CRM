<?php defined('BASEPATH') or exit('No direct script access allowed');

$staff_members     = get_all_staff();
$whatsapp_template = get_whatsapp_template();
array_unshift($staff_members, array());
array_unshift($type, array());
array_unshift($visitor_type, array());
if (empty($location)) {
   $location = [];
}
array_unshift($location, array());

$last_lead_request = last_lead_request($lead->id);

$officeAddress = get_office_locations();
$streets = json_encode(array_column($officeAddress, 'street'))??[];

?>
<style>
   ul#suggestion-list {
      display: block;
      border: 1px solid rgb(204, 204, 204);
      padding: 5px 10px;
      position: absolute;
      background: white;
      top: 72px;
      left: 15px;
      width: 90%;
      font-size: 14px;
      line-height: 25px;
      z-index: 99999;
      border-radius: 10px;
      box-shadow: 2px 3px 10px 0px lightgray;
   }

   ul#suggestion-list li:hover {
      background: lightgray;
   }
</style>
<div class="modal-header">
   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
   <h4 class="modal-title">
      <?php if (isset($lead)) {
         if (!empty($lead->name)) {
            $name = $lead->name;
         } else if (!empty($lead->company)) {
            $name = $lead->company;
         } else {
            $name = _l('lead');
         }
         echo '#' . $lead->id . ' - ' .  $name;
         $web_activity_log_data = $this->db->select("value")->where(array("fieldid" => WEB_HISTORY_ID, "fieldto" => "leads", "relid" => $lead->id))->get(db_prefix() . "customfieldsvalues")->result_array();
      } else {
         echo _l('add_new', _l('lead_lowercase'));
         $web_activity_log_data = [];
      }


      ?>
   </h4>
</div>
<div class="modal-body">
   <?php
   if (isset($lead)) {
      if ($lead->lost == 1) {
         echo '<div class="ribbon danger"><span>' . _l('lead_lost') . '</span></div>';
      } else if ($lead->junk == 1) {
         echo '<div class="ribbon warning"><span>' . _l('lead_junk') . '</span></div>';
      } else {
         if (total_rows(db_prefix() . 'clients', array(
            'leadid' => $lead->id
         ))) {
            echo '<div class="ribbon success"><span>' . _l('lead_is_client') . '</span></div>';
         }
      }
   }
   ?>
   <div class="row">
      <div class="col-md-12">
         <?php if (isset($lead)) {
            echo form_hidden('leadid', $lead->id);
         } ?>
         <div class="top-lead-menu">
            <div class="horizontal-scrollable-tabs preview-tabs-top">
               <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
               <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
               <div class="horizontal-tabs">

                  <ul class="nav-tabs-horizontal nav nav-tabs<?php if (!isset($lead)) {
                                                                  echo ' lead-new';
                                                               } ?>" role="tablist">
                     <li role="presentation" class="active">
                        <a href="#tab_lead_profile" aria-controls="tab_lead_profile" role="tab" data-toggle="tab">
                           <?php echo _l('lead_profile'); ?>
                        </a>
                     </li>
                     <?php if (isset($lead)) { ?>
                        <?php if (count($mail_activity) > 0 || isset($show_email_activity) && $show_email_activity) { ?>
                           <li role="presentation">
                              <a href="#tab_email_activity" aria-controls="tab_email_activity" role="tab" data-toggle="tab">
                                 <?php echo hooks()->apply_filters('lead_email_activity_subject', _l('lead_email_activity')); ?>
                              </a>
                           </li>
                        <?php } ?>

                        <!-- sms -->
                        <!-- <li role="presentation">
                           <a href="#tab_sms_leads" onclick="initDataTable('.table-sms-lead', admin_url + 'proposals/proposal_relations/' + <?php echo $lead->id; ?> + '/lead','undefined', 'undefined','undefined',[6,'desc']);" aria-controls="tab_proposals_leads" role="tab" data-toggle="tab">
                              <?php echo _l('SMS'); ?>
                           </a>
                        </li> -->
                        <!--end sms-->
                        <?php if (has_permission('whatsapp', '', 'view') && 1 == 2) { ?>
                           <!-- <li role="presentation">
                              <a id="tab_proposals_whatsapp_li" href="#tab_proposals_whatsapp" onclick="get_whatsapp_message(<?= get_staff_phonenumber(get_staff_user_id())->phonenumber ?>,<?= (isset($lead) && $lead->phonenumber != '' ? $lead->phonenumber : '') ?>);" aria-controls="tab_proposals_whatsapp" role="tab" data-toggle="tab">
                                 <?php echo _l('Whatsapp'); ?>
                              </a>
                           </li> -->
                        <?php } ?>

                        <!-- <li role="presentation">
                           <a href="#tab_proposals_leads" onclick="initDataTable('.table-proposals-lead', admin_url + 'proposals/proposal_relations/' + <?php echo $lead->id; ?> + '/lead','undefined', 'undefined','undefined',[6,'desc']);" aria-controls="tab_proposals_leads" role="tab" data-toggle="tab">
                              <?php echo _l('proposals'); ?>
                           </a>
                        </li> -->
                        <!-- <li role="presentation">
                           <a href="#tab_tasks_leads" onclick="init_rel_tasks_table(<?php echo $lead->id; ?>,'lead','.table-rel-tasks-leads');" aria-controls="tab_tasks_leads" role="tab" data-toggle="tab">
                              <?php echo _l('tasks'); ?>
                           </a>
                        </li> -->
                        <!-- <li role="presentation">
                           <a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
                              <?php echo _l('lead_attachments'); ?>
                           </a>
                        </li> -->
                        <li role="presentation">
                           <a href="#lead_reminders" onclick="initDataTable('.table-reminders-leads', admin_url + 'misc/get_reminders/' + <?php echo $lead->id; ?> + '/' + 'lead', undefined, undefined,undefined,[1, 'asc']);" aria-controls="lead_reminders" role="tab" data-toggle="tab">
                              <?php echo _l('leads_reminders_tab'); ?> &
                              <?php
                              $total_reminders = total_rows(
                                 db_prefix() . 'reminders',
                                 array(
                                    'isnotified' => 0,
                                    'staff' => get_staff_user_id(),
                                    'rel_type' => 'lead',
                                    'rel_id' => $lead->id
                                 )
                              );
                              if ($total_reminders > 0) {
                                 echo '<span class="badge">' . $total_reminders . '</span>';
                              }
                              ?>
                              <?php echo _l('lead_add_edit_notes'); ?>
                           </a>
                        </li>
                        <!--  <li role="presentation">
            <a href="#lead_notes" aria-controls="lead_notes" role="tab" data-toggle="tab">
            <?php echo _l('lead_add_edit_notes'); ?>
            </a>
         </li>-->
                        <li role="presentation">
                           <a href="#lead_activity" aria-controls="lead_activity" role="tab" data-toggle="tab">
                              <?php echo _l('lead_add_edit_activity'); ?>
                           </a>
                        </li>

                        <li role="presentation">
                           <a href="#lead_call_activity" aria-controls="lead_call_activity" role="tab" data-toggle="tab">
                              <?php echo _l('lead_add_edit_call_activity'); ?>
                           </a>
                        </li>

                        <?php if (!empty($web_activity_log_data)) { ?>
                           <li role="presentation">
                              <a href="#lead_web_activity" aria-controls="lead_web_activity" role="tab" data-toggle="tab">
                                 <?php echo _l('Web History'); ?>
                              </a>
                           </li>
                        <?php } ?>
                        <li role="presentation">
                           <a href="#lead_transfer_lead_request" id="show_transfer_lead_div" aria-controls="lead_transfer_lead_request" role="tab" data-toggle="tab">
                              <?php echo _l('lead_add_edit_lead_transfer_request'); ?>
                           </a>
                        </li>
                        <li role="presentation">
                           <a href="#lead_visitor_lead_request" id="show_visitor_lead_div" onclick="set_validation_form()" aria-controls="lead_visitor_lead_request" role="tab" data-toggle="tab">
                              <?php echo _l('Lead Visitor Request'); ?>
                           </a>
                        </li>
                        <?php if (is_gdpr() && (get_option('gdpr_enable_lead_public_form') == '1' || get_option('gdpr_enable_consent_for_leads') == '1')) { ?>
                           <li role="presentation">
                              <a href="#gdpr" aria-controls="gdpr" role="tab" data-toggle="tab">
                                 <?php echo _l('gdpr_short'); ?>
                              </a>
                           </li>
                        <?php } ?>
                     <?php } ?>
                  </ul>
               </div>
            </div>
         </div>
         <!-- Tab panes -->
         <div class="tab-content mtop20">
            <!-- from leads modal -->
            <div role="tabpanel" class="tab-pane active" id="tab_lead_profile">
               <?php $this->load->view('admin/leads/profile'); ?>
            </div>
            <?php if (isset($lead)) { ?>
               <?php if (count($mail_activity) > 0 || isset($show_email_activity) && $show_email_activity) { ?>
                  <div role="tabpanel" class="tab-pane" id="tab_email_activity">
                     <?php hooks()->do_action('before_lead_email_activity', array('lead' => $lead, 'email_activity' => $mail_activity)); ?>
                     <?php foreach ($mail_activity as $_mail_activity) { ?>
                        <div class="lead-email-activity">
                           <div class="media-left">
                              <i class="fa fa-envelope"></i>
                           </div>
                           <div class="media-body">
                              <h4 class="bold no-margin lead-mail-activity-subject">
                                 <?php echo $_mail_activity['subject']; ?>
                                 <br />
                                 <small class="text-muted display-block mtop5 font-medium-xs"><?php echo _dt($_mail_activity['dateadded']); ?></small>
                              </h4>
                              <div class="lead-mail-activity-body">
                                 <hr />
                                 <?php echo $_mail_activity['body']; ?>
                              </div>
                              <hr />
                           </div>
                        </div>
                        <div class="clearfix"></div>
                     <?php } ?>
                     <?php hooks()->do_action('after_lead_email_activity', array('lead_id' => $lead->id, 'emails' => $mail_activity)); ?>
                  </div>
               <?php } ?>
               <?php if (is_gdpr() && (get_option('gdpr_enable_lead_public_form') == '1' || get_option('gdpr_enable_consent_for_leads') == '1' || (get_option('gdpr_data_portability_leads') == '1') && is_admin())) { ?>
                  <div role="tabpanel" class="tab-pane" id="gdpr">

                     <?php if (get_option('gdpr_enable_lead_public_form') == '1') { ?>
                        <a href="<?php echo $lead->public_url; ?>" target="_blank" class="mtop5">
                           <?php echo _l('view_public_form'); ?>
                        </a>
                     <?php } ?>
                     <?php if (get_option('gdpr_data_portability_leads') == '1' && is_admin()) { ?>
                        <?php
                        if (get_option('gdpr_enable_lead_public_form') == '1') {
                           echo ' | ';
                        }
                        ?>
                        <a href="<?php echo admin_url('leads/export/' . $lead->id); ?>">
                           <?php echo _l('dt_button_export'); ?>
                        </a>
                     <?php } ?>
                     <?php if (get_option('gdpr_enable_lead_public_form') == '1' || (get_option('gdpr_data_portability_leads') == '1' && is_admin())) { ?>
                        <hr class="hr-margin-n-15" />
                     <?php } ?>
                     <?php if (get_option('gdpr_enable_consent_for_leads') == '1') { ?>
                        <h4 class="no-mbot">
                           <?php echo _l('gdpr_consent'); ?>
                        </h4>
                        <?php $this->load->view('admin/gdpr/lead_consent'); ?>
                        <hr />
                     <?php } ?>
                  </div>
               <?php } ?>
               <div role="tabpanel" class="tab-pane" id="lead_activity">
                  <div class="panel_s no-shadow">
                     <div class="activity-feed">
                        <?php foreach ($activity_log as $log) { ?>
                           <div class="feed-item">
                              <div class="date">
                                 <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                                    <?php echo time_ago($log['date']); ?>
                                 </span>
                              </div>
                              <div class="text">
                                 <?php if ($log['staffid'] != 0) { ?>
                                    <a href="<?php echo admin_url('profile/' . $log["staffid"]); ?>">
                                       <?php echo staff_profile_image($log['staffid'], array('staff-profile-xs-image pull-left mright5'));
                                       ?>
                                    </a>
                                 <?php
                                 }
                                 $additional_data = '';
                                 if (!empty($log['additional_data'])) {
                                    $additional_data = unserialize($log['additional_data']);

                                    // echo $log['description'];
                                    // print_r($log);
                                    // print_r($additional_data);
                                    echo ($log['staffid'] == 0) ? _l($log['description'], $additional_data) : $log['full_name'] . ' - ' . _l($log['description'], $additional_data);
                                 } else {
                                    echo $log['full_name'] . ' - ';
                                    if ($log['custom_activity'] == 0) {
                                       echo _l($log['description']);
                                    } else {
                                       echo _l($log['description'], '', false);
                                    }
                                 }
                                 ?>
                              </div>
                           </div>
                        <?php } ?>
                     </div>
                     <!-- <div class="col-md-12">
                        <?php echo render_textarea('lead_activity_textarea', '', '', array('placeholder' => _l('enter_activity')), array(), 'mtop15'); ?>
                        <div class="text-right">
                           <button id="lead_enter_activity" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
                     </div> -->
                     <div class="clearfix"></div>
                  </div>
               </div>
               <?php if (!empty($web_activity_log_data)) { ?>
                  <div role="tabpanel" class="tab-pane" id="lead_web_activity">
                     <div class="panel_s no-shadow">
                        <div class="activity-feed">
                           <div class="activity-feed">
                              <?php
                              $status_no_history = true;
                              if (!empty($web_activity_log_data)) {
                                 foreach ($web_activity_log_data as $web_data) {
                                    $web_history_ = str_replace("],[", "]+/+[", $web_data["value"]);
                                    $web_history_ = explode("+/+", $web_history_);

                                    foreach ($web_history_ as $web_h) {
                                       $web_activity_log = json_decode($web_h, true);
                                       // Check if JSON decoding was successful
                                       if ($web_activity_log !== null) {
                                          $log_count = count($web_activity_log);
                                          foreach ($web_activity_log as $key => $log) {
                                             $status_no_history = false;

                              ?>

                                             <div class="feed-item">
                                                <div class="date">
                                                   <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['datetime']); ?>">
                                                      <?php echo time_ago($log['datetime']); ?>
                                                   </span>
                                                </div>
                                                <div class="text">
                                                   <a target="_blank" href="<?= $log["url"] ?>"><?= $log["url"] ?></a>
                                                </div>
                                                <?php
                                                if ($key === ($log_count - 1)) {
                                                   echo "<div class='text'><p>New Lead Imported from Web to Lead Form</div>";
                                                }
                                                ?>
                                             </div>
                              <?php
                                          }
                                       } else {
                                          if ($status_no_history) {
                                             echo "<h3 class='text-center'>No Web History</h3>";
                                          }
                                       }
                                    }
                                 }
                              } else {
                                 // No web activity log data available, display "No Web History"
                                 echo "<h3 class='text-center'>No Web History</h3>";
                              }
                              ?>



                           </div>
                        </div>
                        <!-- <div class="clearfix"></div> -->
                     </div>
                  </div>
               <?php } ?>
               <div role="tabpanel" class="tab-pane" id="lead_call_activity">
                  <div class="panel_s no-shadow">
                     <div class="activity-feed">
                        <?php
                        $len = count($call_activity_log);
                        $i = 0;
                        if (!empty($call_activity_log)) {
                           foreach ($call_activity_log as $call) { ?>
                              <div class="media lead-note">
                                 <a href="<?php echo admin_url('profile/' . $call["staffid"]); ?>" target="_blank">
                                    <?php echo staff_profile_image($call['profile_image'], array('staff-profile-image-small', 'pull-left mright10')); ?>
                                 </a>
                                 <div class="media-body">

                                    <?php if (!empty($call["call_status"]) && (strtolower(trim($call["call_status"])) == "missed" || str_contains(strtolower(trim($call["call_status"])), 'disconnected'))) {
                                       $call['type_icon'] = "assets/images/missed.png";
                                    } ?>
                                    <a href="javascript:void(0)" class="pull-right"><img style="height: 50px;" src='<?php echo base_url($call['source_icon']); ?>'><img style="height: 25px;" src='<?php echo base_url($call['image_icon']); ?>'>
                                       <small><?= $call["source_name"] ?></small>

                                    </a>
                                    <!-- <a href="#" class="pull-right"><img style="height: 40px;" src='<?php echo base_url($call['image_icon']); ?>'>
                                       <small><?= $call["contact_type"] ?></small>

                                    </a> -->

                                    <?php if (!empty($call['datetime'])) { ?>
                                       <span data-toggle="tooltip" data-title="<?php echo $call['datetime']; ?>">
                                          <small><?php echo $call['datetime']; ?></small>
                                       </span>
                                    <?php } ?>
                                    <a href="<?php echo admin_url('profile/' . $call["staffid"]); ?>" target="_blank">
                                       <!-- <h5 class="media-heading bold"><?php echo get_staff_full_name($call['staffid']); ?> - ( <?= $call["contact_type"] ?> )</h5> -->

                                       <h5 class="media-heading bold"><?php echo get_staff_full_name($call['staffid']); ?></h5>
                                    </a>
                                    <?php
                                    $color = "primary";
                                    $show_time  = true;
                                    if (!empty($call["call_status"])) {

                                       if (strtolower(trim($call["call_status"])) == "busy") {
                                          $color = "warning";
                                          $show_time  = true;
                                       } else if (strtolower(trim($call["call_status"])) == "answered") {
                                          $color = "success";
                                          $show_time  = true;
                                       } else if ((strtolower(trim($call["call_status"])) == "missed" || str_contains(strtolower(trim($call["call_status"])), 'disconnected'))) {
                                          $color = "danger";
                                          $show_time  = true;
                                       }
                                    ?>
                                       <a href="javascript:void(0);">
                                          <img style="height: 25px;" src='<?php echo base_url($call['type_icon']); ?>'>

                                          <small class="text-<?= $color ?>"><?= $call["call_status"] ?> </small>-<small>(<?= $call['call_type_name'] ?>)</small>
                                       </a>
                                    <?php } ?>
                                    <h5>
                                       <small data-toggle="tooltip" data-title="<?= !empty($call["call_start"]) ? date('Y-m-d', ($call['call_start'])) : '' ?>"><?= !empty($call["call_start"]) ? date('Y-m-d', ($call['call_start'])) : '' ?></small><br>
                                       <?php
                                       if ($show_time) {
                                          if (!empty($call["call_start"])) { ?>
                                             <small data-toggle="tooltip" data-title="<?= !empty($call["call_start"]) ? date('Y-m-d H:i:s', ($call['call_start'])) : '' ?>">
                                                <?= !empty($call["call_start"]) ? date('H:i:s', ($call['call_start'])) : '' ?>
                                             </small>
                                          <?php } ?>
                                          <?php if (!empty($call["call_end"])) { ?>
                                             - <small data-toggle="tooltip" data-title="<?= !empty($call["call_end"]) ? date('Y-m-d H:i:s', ($call['call_end'])) : '' ?>">
                                                <?= !empty($call["call_end"]) ? date('H:i:s', ($call['call_end'])) : '' ?>
                                             </small>
                                          <?php }
                                          ?>

                                          <?php if (!empty($call["duration"])) {
                                          ?>
                                             <small> - (<?= convertSeconds($call["duration"]) ?>)</small>
                                       <?php
                                          }
                                       }
                                       ?>
                                    </h5>

                                 </div>
                                 <?php if ($i >= 0 && $i != $len - 1) {
                                    echo '<hr />';
                                 }
                                 ?>
                              </div>
                           <?php }
                        } else { ?>
                           <div>
                              <h4>No Calls Activity</h4>
                           </div>
                        <?php } ?>
                     </div>
                     <!-- <div class="clearfix"></div> -->
                  </div>
               </div>
               <!-- sms new-->
               <!-- <div role="tabpanel" class="tab-pane" id="tab_sms_leads">
         <?php //init_relation_tasks_table(array('data-new-rel-id'=>$lead->id,'data-new-rel-type'=>'lead')); 
         ?>
      </div> -->
               <div role="tabpanel" class="tab-pane" id="tab_sms_leads">
                  <?php echo form_open(admin_url('leads/add_sms/' . $lead->id), array('id' => 'lead-sms')); ?>

                  <div class="form-group">
                     <select name="smsTemplate" id="smsTemplate" class="form-control">
                        <option value="">Select an SMS Template</option>
                        <?php 
                        foreach($officeAddress as $address)
                        {
                            
                            ?>
                             <option value="<?=$address['street']?>">Send <?=$address['city']?> Office Address</option>
                            <?php
                        }
                        
                        ?>
                        <!--<option value="4th floor, The Corenthum, Tower-B, Educationvibes ,34/2, Sector 62, Noida, Uttar Pradesh 201309">Send Noida Office Address</option>-->
                        <!--<option value="Diamond Chambers, 9N, 9th floor,Block-1&2, 4, Chowringhee Ln,Park Street area, Kolkata, 700016">Send Kolkata Office Address</option>-->
                        <!--<option value="Office no 19, Second Floor, Aditya Centeegra, FC road next to Mantri house, Shivaji Nagar, Pune - 411004">Send Pune Office Address</option>-->
                     </select>
                  </div>
                  <div class="form-group">
                     <textarea id="lead_sms_description" name="lead_sms_description" class="form-control" rows="4"></textarea>
                     <input type="hidden" name="mobile" value="<?php echo (isset($lead) && $lead->phonenumber != '' ? $lead->phonenumber : '') ?>">
                  </div>
                  <!-- <div class="lead-select-date-contacted hide">
            <?php // echo render_datetime_input('custom_contact_date','lead_add_edit_datecontacted','',array('data-date-end-date'=>date('Y-m-d'))); 
            ?>
         </div>
         <div class="radio radio-primary">
            <input type="radio" name="contacted_indicator" id="contacted_indicator_yes" value="yes">
            <label for="contacted_indicator_yes"><?php echo _l('lead_add_edit_contacted_this_lead'); ?></label>
         </div>
         <div class="radio radio-primary">
            <input type="radio" name="contacted_indicator" id="contacted_indicator_no" value="no" checked>
            <label for="contacted_indicator_no"><?php echo _l('lead_not_contacted'); ?></label>
         </div> -->
                  <button type="submit" class="btn btn-info pull-right"><?php echo _l('SEND NOW'); ?></button>
                  <?php echo form_close(); ?>
                  <div class="clearfix"></div>
                  <hr />
                  <div class="panel_s no-shadow">
                     <?php
                     $len = count($notes);
                     $i = 0;
                     foreach ($notes as $note) { ?>
                        <div class="media lead-note">
                           <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                              <?php echo staff_profile_image($note['addedfrom'], array('staff-profile-image-small', 'pull-left mright10')); ?>
                           </a>
                           <div class="media-body">
                              <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                                 <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo $note['id']; ?>, <?php echo $lead->id; ?>);return false;"><i class="fa fa fa-times"></i></a>
                                 <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                              <?php } ?>
                              <?php if (!empty($note['date_contacted'])) { ?>
                                 <span data-toggle="tooltip" data-title="<?php echo _dt($note['date_contacted']); ?>">
                                    <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
                                 </span>
                              <?php } ?>
                              <small><?php echo _l('lead_note_date_added', _dt($note['dateadded'])); ?></small>
                              <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                                 <h5 class="media-heading bold"><?php echo get_staff_full_name($note['addedfrom']); ?></h5>
                              </a>
                              <div data-note-description="<?php echo $note['id']; ?>" class="text-muted">
                                 <?php echo check_for_links(app_happy_text($note['description'])); ?>
                              </div>
                              <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide mtop15">
                                 <?php echo render_textarea('note', '', $note['description']); ?>
                                 <div class="text-right">
                                    <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                                    <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                                 </div>
                              </div>
                           </div>
                           <?php if ($i >= 0 && $i != $len - 1) {
                              echo '<hr />';
                           }
                           ?>
                        </div>
                     <?php $i++;
                     } ?>
                  </div>
               </div>
               <!-- end sms -->

               <?php if (has_permission('whatsapp', '', 'view') && 1 == 2) { ?>
                  <div role="tabpanel" class="tab-pane" id="tab_proposals_whatsapp">
                     <form id="whatsapp_message_form" enctype="multipart/form-data" action="/send-message" method="POST" onsubmit="send_whatsapp_message(this.id); return false;">
                        <div class="form-group">
                           <select name="whatsapp_template" id="whatsapp_template" class="form-control" onchange="set_whatsapp_template_value('#message', this.value)">
                              <option value="">Select a WhatsApp Template</option>
                              <?php if (!empty($whatsapp_template)) {
                                 foreach ($whatsapp_template as $template) {
                              ?>
                                    <option value="<?= base64_encode($template['message']) ?>"><?= $template['name'] . ' - ' . $template['subject'] ?></option>
                              <?php
                                 }
                              } ?>
                           </select>

                        </div>
                        <div class="form-group">
                           <!-- <textarea id="message" name="message" class="form-control" rows="4"></textarea> -->
                           <?php echo render_textarea('message', 'Template Message', '', array('placeholder' => _l('Template Message'), 'id' => "template_message"), array(), 'mtop15', 'whatsapp-template-message'); ?>
                           <input type="hidden" id="phoneNumber" name="phoneNumber" value="<?= get_staff_phonenumber(get_staff_user_id())->phonenumber ?>">
                           <input type="hidden" id="contact" name="contact" value="<?php echo (isset($lead) && $lead->phonenumber != '' ? $lead->phonenumber : '') ?>">
                           <br>
                           <input type="file" id="mediaFiles" multiple class="form-control">
                        </div>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('SEND NOW'); ?></button>
                     </form>
                     <div class="clearfix"></div>
                     <div class="a1-column a1-long a1-elastic message-preview-box">
                        <div data-last_messgae_id="" id="message-<?= (isset($lead) && $lead->phonenumber != '' ? $lead->phonenumber : '') ?>" class="chat-container a1-column a1-long a1-elastic chat-main a1-spaced-items">

                        </div>
                     </div>
                     <hr />

                  </div>
               <?php } ?>

               <div role="tabpanel" class="tab-pane" id="lead_transfer_lead_request">
                  <?php echo form_open(admin_url('leads/add_lead_transfer_request'), array('id' => 'lead-transfer')); ?>
                  <input type="hidden" id="transfer_lead_id" name="transfer_lead_id" value="<?= !empty($last_lead_request->id) ? $last_lead_request->id : '' ?>">
                  <input type="hidden" name="lead_id" value="<?= $lead->id ?>">
                  <div class='row'>
                     <div class="form-group col-md-3">
                        <?php
                        echo render_select('transfer_lead_type', $type, array('id', 'name'), 'Lead Type <span class="text-danger">*</span>', [$last_lead_request->lead_type], array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Type')), array(), 'no-mbot', '', false,  'transfer_lead_type');
                        ?>
                     </div>
                     <div class="form-group col-md-3">
                        <?php
                        $assigned_attrs = array();
                        $selected = [];
                        echo render_select('transfer_lead_assign', [], array('staffid', array('firstname', 'lastname')), 'Assigned <span class="text-danger">*</span>', [$last_lead_request->assign], array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot', '', false, 'transfer_lead_assign');
                        ?>
                     </div>

                     <div class="form-group col-md-3">
                        <label>Reason <span class='text-danger'>*</span></label>
                        <textarea id="reason" name="reason" class='form-control' placeholder="reason"><?= $last_lead_request->reason ?></textarea>
                     </div>

                     <div class="form-group col-md-2">
                        <label> &nbsp;</label> <?php
                                                $button_text = !empty($last_lead_request->id)
                                                   ? (is_admin() || has_permission('leads', '', 'approval') ? _l('Update & Approve') : _l('Update NOW'))
                                                   : _l('Request NOW');
                                                ?>
                        <?php if (empty($last_lead_request->automatic) || is_admin() || has_permission('leads', '', 'approval')) { ?>
                           <button type="submit" class="btn btn-info pull-right"><?= $button_text ?></button>
                        <?php } ?>


                     </div>

                  </div>
                  <?php echo form_close(); ?>
                  <div class="clearfix"></div>
                  <hr />
               </div>

               <div role="tabpanel" class="tab-pane" id="lead_visitor_lead_request">

                  <?php echo form_open(admin_url('leads/add_lead_transfer_request'), array('id' => 'lead-visitor', "onsubmit" => "return false;")); ?>
                  <input type="hidden" id="visitor_lead_id" name="visitor_lead_id" value="<?= !empty($visitor_request->id) ? $visitor_request->id : '' ?>">
                  <input type="hidden" name="lead_id" value="<?= $lead->id ?>">
                  <div class='row'>
                     <div class="form-group col-md-3">
                        <?php
                        echo render_input('date_of_visit', '<small class="req text-danger">* </small> Date of Visit', !empty($visitor_request->date_of_visit) ? $visitor_request->date_of_visit : '', 'datetime-local', array('placeholder' => _l('Date of visitor')), array(), 'no-mbot', '', false) ?>
                     </div>
                     <div class="form-group col-md-3">
                        <?php
                        echo render_select('visitor_location', $location, array('id', 'name'), '<small class="req text-danger">* </small> Location ', [$visitor_request->location], array('data-width' => '100%', 'data-none-selected-text' => _l('Location')), array(), 'no-mbot', '', false,  'visitor_location');
                        ?>
                     </div>



                     <div class="form-group col-md-3">
                        <?php
                        echo render_select('visitor_type', $visitor_type, array('id', 'name'), '<small class="req text-danger">* </small> Visitor Type ', [$visitor_request->visitor_type], array('data-width' => '100%', 'data-none-selected-text' => _l('Visitor Type')), array(), 'no-mbot', '', false,  'visitor_type');
                        ?>
                     </div>
                     <div class="form-group col-md-3">
                        <?php

                        echo render_select('visitor_lead_assign', $staff_members, array('staffid', array('firstname', 'lastname')), '<small class="req text-danger">* </small> Attendee ', [$visitor_request->assigned], array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot', '', false, 'transfer_lead_assign');
                        ?>
                     </div>
                     <div class="form-group col-md-4">
                        <label><small class="req text-danger">* </small> Address </label>
                        <textarea id="address" name="address" class='form-control' placeholder="address"><?= $visitor_request->address ?></textarea>
                        <ul id="suggestion-list" style="display:none; border: 1px solid #ccc; padding: 5px; position: absolute; background: white;"></ul>

                     </div>

                     <div class="form-group col-md-5">
                        <label><small class="req text-danger">* </small> Purpose </label>
                        <textarea id="reason" name="reason" col="4" class='form-control' placeholder="Write your purpose"><?= $visitor_request->description ?></textarea>
                     </div>


                     <?php if (!empty($visitor_request->id)) { ?>
                        <div class="form-group col-md-3">
                           <?php
                           echo render_select('visitor_lead_status', $visitor_status, array('id', 'name'), '<small class="req text-danger">* </small> Status ', [$visitor_request->status], array('data-width' => '100%', 'data-none-selected-text' => _l('Status select'), "onchange" => "change_comment()"), array(), 'no-mbot', '', false, 'visitor_lead_status');
                           ?>
                        </div>
                     <?php } ?>

                     <div class="form-group col-md-12 comment-box" style="display:none;">
                        <label><small class="req text-danger">* </small> Comment </label>
                        <textarea id="visitor_comment" name="visitor_comment" col="4" class='form-control' placeholder="Write your comment"></textarea>
                     </div>


                     <div class="form-group col-md-12 text-right">
                        <label> &nbsp;</label> <?php
                                                $button_text = !empty($visitor_request->id)
                                                   ? (is_admin() ? _l('Update NOW') : _l('Update NOW'))
                                                   : _l('Request NOW');
                                                ?>
                        <button type="submit" class="btn btn-info pull-right"><?= $button_text ?></button>
                     </div>

                  </div>
                  <?php echo form_close(); ?>
                  <div class="clearfix"></div>
                  <div class="activity-feed">
                     <?php foreach ($activity_log_visitor as $log) { ?>
                        <div class="feed-item">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                                 <?php echo time_ago($log['date']); ?>
                              </span>
                           </div>
                           <div class="text">
                              <?php if ($log['staffid'] != 0) { ?>
                                 <a href="<?php echo admin_url('profile/' . $log["staffid"]); ?>">
                                    <?php echo staff_profile_image($log['staffid'], array('staff-profile-xs-image pull-left mright5'));
                                    ?>
                                 </a>
                              <?php
                              }

                              echo  get_staff_user_name_by_id($log['staffid']) . ' - ' . $log['description'];

                              ?>
                           </div>
                        </div>
                     <?php } ?>
                  </div>
                  <hr />
               </div>

               <div role="tabpanel" class="tab-pane" id="tab_proposals_leads">
                  <?php if (has_permission('proposals', '', 'create')) { ?>
                     <a href="<?php echo admin_url('proposals/proposal?rel_type=lead&rel_id=' . $lead->id); ?>" class="btn btn-info mbot25"><?php echo _l('new_proposal'); ?></a>
                  <?php } ?>
                  <?php if (total_rows(db_prefix() . 'proposals', array('rel_type' => 'lead', 'rel_id' => $lead->id)) > 0 && (has_permission('proposals', '', 'create') || has_permission('proposals', '', 'edit'))) { ?>
                     <a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#sync_data_proposal_data"><?php echo _l('sync_data'); ?></a>
                     <?php $this->load->view('admin/proposals/sync_data', array('related' => $lead, 'rel_id' => $lead->id, 'rel_type' => 'lead')); ?>
                  <?php } ?>
                  <?php
                  $table_data = array(
                     _l('proposal') . ' #',
                     _l('proposal_subject'),
                     _l('proposal_total'),
                     _l('proposal_date'),
                     _l('proposal_open_till'),
                     _l('tags'),
                     _l('proposal_date_created'),
                     _l('proposal_status')
                  );
                  $custom_fields = get_custom_fields('proposal', array('show_on_table' => 1));
                  foreach ($custom_fields as $field) {
                     array_push($table_data, $field['name']);
                  }
                  $table_data = hooks()->apply_filters('proposals_relation_table_columns', $table_data);
                  render_datatable($table_data, 'proposals-lead', [], [
                     'data-last-order-identifier' => 'proposals-relation',
                     'data-default-order'         => get_table_last_order('proposals-relation'),
                  ]);
                  ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_tasks_leads">
                  <?php init_relation_tasks_table(array('data-new-rel-id' => $lead->id, 'data-new-rel-type' => 'lead')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="lead_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-lead-<?php echo $lead->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('lead_set_reminder_title'); ?></a>
                  <hr />
                  <?php render_datatable(array(_l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')), 'reminders-leads'); ?>
                  <hr />
                  <div id="lead_notes">
                     <?php echo form_open(admin_url('leads/add_note/' . $lead->id), array('id' => 'lead-notes')); ?>
                     <div class="form-group">
                        <textarea id="lead_note_description" name="lead_note_description" class="form-control" rows="4" placeholder="Enter Comment..."></textarea>
                     </div>
                     <div class="lead-select-date-contacted hide">
                        <?php echo render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', '', array('data-date-end-date' => date('Y-m-d'))); ?>
                     </div>
                     <div class="radio radio-primary hide">
                        <input type="radio" name="contacted_indicator" id="contacted_indicator_yes" value="yes">
                        <label for="contacted_indicator_yes"><?php echo _l('lead_add_edit_contacted_this_lead'); ?></label>
                     </div>
                     <div class="radio radio-primary hide">
                        <input type="radio" name="contacted_indicator" id="contacted_indicator_no" value="no" checked>
                        <label for="contacted_indicator_no"><?php echo _l('lead_not_contacted'); ?></label>
                     </div>
                     <button type="submit" class="btn btn-info pull-right"><?php echo _l('lead_add_edit_add_note'); ?></button>
                     <?php echo form_close(); ?>
                     <div class="clearfix"></div>
                     <hr />
                     <div class="panel_s no-shadow">
                        <?php
                        $len = count($notes);
                        $i = 0;
                        foreach ($notes as $note) { ?>
                           <div class="media lead-note">
                              <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                                 <?php echo staff_profile_image($note['addedfrom'], array('staff-profile-image-small', 'pull-left mright10')); ?>
                              </a>
                              <div class="media-body">
                                 <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                                    <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo $note['id']; ?>, <?php echo $lead->id; ?>);return false;"><i class="fa fa fa-times"></i></a>
                                    <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                                 <?php } ?>
                                 <?php if (!empty($note['date_contacted'])) { ?>
                                    <span data-toggle="tooltip" data-title="<?php echo _dt($note['date_contacted']); ?>">
                                       <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
                                    </span>
                                 <?php } ?>
                                 <small><?php echo _l('lead_note_date_added', _dt($note['dateadded'])); ?></small>
                                 <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                                    <h5 class="media-heading bold"><?php echo get_staff_full_name($note['addedfrom']); ?></h5>
                                 </a>
                                 <div data-note-description="<?php echo $note['id']; ?>" class="text-muted">
                                    <?php echo check_for_links(app_happy_text($note['description'])); ?>
                                 </div>
                                 <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide mtop15">
                                    <?php echo render_textarea('note', '', $note['description']); ?>
                                    <div class="text-right">
                                       <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                                       <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                                    </div>
                                 </div>
                              </div>
                              <?php if ($i >= 0 && $i != $len - 1) {
                                 echo '<hr />';
                              }
                              ?>
                           </div>
                        <?php $i++;
                        } ?>
                     </div>
                  </div>


               </div>
               <div role="tabpanel" class="tab-pane" id="attachments">
                  <?php echo form_open('admin/leads/add_lead_attachment', array('class' => 'dropzone mtop15 mbot15', 'id' => 'lead-attachment-upload')); ?>
                  <?php echo form_close(); ?>
                  <?php if (get_option('dropbox_app_key') != '') { ?>
                     <hr />
                     <div class="text-right">
                        <button class="gpicker">
                           <i class="fa fa-google" aria-hidden="true"></i>
                           <?php echo _l('choose_from_google_drive'); ?>
                        </button>
                        <div id="dropbox-chooser-lead"></div>
                     </div>
                  <?php } ?>
                  <?php if (count($lead->attachments) > 0) { ?>
                     <div class="mtop20" id="lead_attachments">
                        <?php $this->load->view('admin/leads/leads_attachments_template', array('attachments' => $lead->attachments)); ?>
                     </div>
                  <?php } ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="lead_notes">
                  <?php echo form_open(admin_url('leads/add_note/' . $lead->id), array('id' => 'lead-notes')); ?>
                  <div class="form-group">
                     <textarea id="lead_note_description" name="lead_note_description" class="form-control" rows="4" placeholder="Enter Comment..."></textarea>
                  </div>
                  <div class="lead-select-date-contacted hide">
                     <?php echo render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', '', array('data-date-end-date' => date('Y-m-d'))); ?>
                  </div>
                  <div class="radio radio-primary hide">
                     <input type="radio" name="contacted_indicator" id="contacted_indicator_yes" value="yes">
                     <label for="contacted_indicator_yes"><?php echo _l('lead_add_edit_contacted_this_lead'); ?></label>
                  </div>
                  <div class="radio radio-primary hide">
                     <input type="radio" name="contacted_indicator" id="contacted_indicator_no" value="no" checked>
                     <label for="contacted_indicator_no"><?php echo _l('lead_not_contacted'); ?></label>
                  </div>
                  <button type="submit" class="btn btn-info pull-right"><?php echo _l('lead_add_edit_add_note'); ?></button>
                  <?php echo form_close(); ?>
                  <div class="clearfix"></div>
                  <hr />
                  <div class="panel_s no-shadow">
                     <?php
                     $len = count($notes);
                     $i = 0;
                     foreach ($notes as $note) { ?>
                        <div class="media lead-note">
                           <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                              <?php echo staff_profile_image($note['addedfrom'], array('staff-profile-image-small', 'pull-left mright10')); ?>
                           </a>
                           <div class="media-body">
                              <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                                 <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo $note['id']; ?>, <?php echo $lead->id; ?>);return false;"><i class="fa fa fa-times"></i></a>
                                 <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                              <?php } ?>
                              <?php if (!empty($note['date_contacted'])) { ?>
                                 <span data-toggle="tooltip" data-title="<?php echo _dt($note['date_contacted']); ?>">
                                    <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
                                 </span>
                              <?php } ?>
                              <small><?php echo _l('lead_note_date_added', _dt($note['dateadded'])); ?></small>
                              <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                                 <h5 class="media-heading bold"><?php echo get_staff_full_name($note['addedfrom']); ?></h5>
                              </a>
                              <div data-note-description="<?php echo $note['id']; ?>" class="text-muted">
                                 <?php echo check_for_links(app_happy_text($note['description'])); ?>
                              </div>
                              <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide mtop15">
                                 <?php echo render_textarea('note', '', $note['description']); ?>
                                 <div class="text-right">
                                    <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                                    <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                                 </div>
                              </div>
                           </div>
                           <?php if ($i >= 0 && $i != $len - 1) {
                              echo '<hr />';
                           }
                           ?>
                        </div>
                     <?php $i++;
                     } ?>
                  </div>
               </div>
            <?php } ?>
         </div>
      </div>
   </div>
</div>
<?php hooks()->do_action('lead_modal_profile_bottom', (isset($lead) ? $lead->id : '')); ?>
<script>
   // init_editor('#template_message');
   var visitorStatus = <?= !empty($visitorStatus) ? $visitorStatus : 0 ?>;

   var staff_members = <?= json_encode($staff_members, true) ?>;
   var last_lead_request_assign = "<?= !empty($last_lead_request->assign) ? $last_lead_request->assign : '' ?>";

   $(document).ready(function() {
      console.log(visitorStatus);
      if (visitorStatus == 1) {
         const intervalId = setInterval(() => {
            if ($("#lead-modal").is(":visible")) {
               $("#show_visitor_lead_div").trigger("click");
               if (visitorStatus == 1) {
                  // $(".horizontal-tabs ul.nav-tabs-horizontal li").not(".active").remove();
                  $(".horizontal-tabs ul.nav-tabs-horizontal li").not(".active").addClass("disabled").css("pointer-events", "none");

               }
               clearInterval(intervalId);
               clearTimeout(timeoutId); // Clear the timeout if the interval is cleared first
            }
         }, 500);

         // Set a timeout to clear the interval after 30 seconds
         const timeoutId = setTimeout(() => {
            clearInterval(intervalId);
         }, 30000); // 30000 milliseconds = 30 seconds
      }
   });

   function set_staff_dropdown() {
      let lead_type = $("#transfer_lead_type").val();
      let transfer_lead_assign = $("#transfer_lead_assign");

      // First option should be blank
      transfer_lead_assign.html('<option value=""></option>');

      if (lead_type === "") {
         transfer_lead_assign.selectpicker('refresh');
      } else {
         let new_staff_list = staff_members.filter(function(staff_member) {
            return staff_member.lead_type === lead_type;
         });

         let options = new_staff_list.map(function(staff_member) {
            if (staff_member.full_name != "") {
               return `<option value="${staff_member.staffid}">${staff_member.full_name}</option>`;
            }
         }).join('');

         // Append the new options
         transfer_lead_assign.append(options).selectpicker('refresh');

         // Set last_lead_request_assign if not null
         if (last_lead_request_assign !== '') {
            transfer_lead_assign.val(last_lead_request_assign).selectpicker('refresh');
         }
      }
   }
   $("#transfer_lead_type").change(function() {
      set_staff_dropdown();
   });

   set_staff_dropdown();


   $('#smsTemplate').on('change', function() {
      var x = document.getElementById('lead_sms_description');
      if (this.value != '') {
         x.value = this.value;
      } else {
         x.value = '';
      }
   })

   function removeTags(html) {
      return new Promise((resolve, reject) => {
         var doc = new DOMParser().parseFromString(html, 'text/html');
         resolve(doc.body.textContent || "");
         //  return doc.body.textContent || "";
      });
   }


   async function set_whatsapp_template_value(selector, message = '') {

      if (message === "") {
         $('#message').val('');
         // $(selector).val(''); // Set value to empty if message is empty
      } else {
         $('#message').val(atob(message));
         // $(selector).val(message); // Decode and set the base64 encoded message
      }
   }

   function validation_set(form_id) {
      return new Promise((resolve, reject) => {
         let form_status = true;
         let additional_fields = {};

         $("#" + form_id + " input:visible, #" + form_id + " select:visible, #" + form_id + " textarea:visible").each(function() {
            const value = $(this).val()?.trim(); // Get trimmed value
            const name = $(this).attr("name"); // Get name attribute
            const isRequired = 1; // Assuming all fields are required

            if (isRequired && name) {
               additional_fields[name] = "required";
               if (!value) {
                  form_status = false;
               }
            }
         });

         if (!form_status) {
            appValidateForm($("#" + form_id), additional_fields);
            reject("Form validation failed."); // Reject the promise if validation fails
         } else {
            resolve("Form validation passed."); // Resolve if all fields are valid
         }
      });
   }

   var set_validation = false;

   function set_validation_form() {
      if (set_validation == false) {
         console.log("start");
         setTimeout(async () => {

            set_validation = true;
            set_search_cities();
            set_search_location();

            await validation_set("lead-visitor");


         }, 200);

      }
   }

   function set_search_cities() {

      // Bind event to search input ONLY inside #visitor_location selectpicker
      $('#visitor_location').parent().find('.bs-searchbox input').on('input', function() {
         let searchQuery = $(this).val();

         if (searchQuery.length > 2) { // Start AJAX after 3+ characters
            let formData = new FormData(document.getElementById('lead-visitor')); // Correct FormData initialization

            formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
            formData.append("value", searchQuery); // Corrected `.val()` issue

            $.ajax({
               url: "<?php echo base_url('admin/leads/search_cities'); ?>", // Replace with actual API URL
               method: "POST", // FormData requires POST (not GET)
               data: formData,
               processData: false, // Prevent jQuery from transforming FormData
               contentType: false, // Ensure correct Content-Type is set for FormData
               dataType: "JSON",
               success: function(response) { // 'data' is already parsed as JSON

                  $('#visitor_location').empty(); // Clear old options
                  let data = response.data;
                  if (data.length > 0) {
                     $.each(data, function(index, item) {
                        $('#visitor_location').append(`<option value="${item.id}">${item.name}</option>`);
                     });
                  } else {
                     $('#visitor_location').append('<option disabled>No results found</option>'); // Handle no results case
                  }

                  $('#visitor_location').selectpicker('refresh'); // Refresh selectpicker
               },
               error: function(xhr, status, error) {
                  console.error("AJAX Error: ", error);
               }
            });

         }
      });

   }

   function create_visitor_request() {
      let formData = new FormData(document.getElementById('lead-visitor')); // Correct way to initialize FormData
      // Append CSRF token and client ID
      formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());

      // AJAX request to upload documents
      $.ajax({
         url: "<?php echo base_url('admin/leads/visitor_request'); ?>",
         type: "POST",
         data: formData,
         processData: false, // Prevent jQuery from transforming FormData
         contentType: false, // Ensure correct Content-Type is set for FormData
         dataType: "JSON",
         success: function(res) {
            hide_loader();
            if (res.success) {
               alert_float("success", res.message);
               $(".lead-modal").modal("hide");
               setTimeout(() => {
                  init_lead(res.lead_id, "", "#show_visitor_lead_div", 1);
                  visitorStatus = 0;
               }, 200);
               if (window.filter_data) {
                  filter_data();
               }
            } else {
               const message = res.message || "An unknown error occurred.";
               alert_float("danger", message);
            }
         },
         error: function(xhr, status, error) {
            console.error("Error: ", error);
            hide_loader();
            alert_float("danger", "An error occurred while processing the request.");
         },
      });
   }


   $("#lead-visitor").submit(async function(e) {
      try {
         let response = await validation_set("lead-visitor");
         if (response) {
            create_visitor_request();
         }
      } catch (error) {
         console.log(error); // Log validation failure
      }
   });

   function change_comment() {
      $(".comment-box").hide();
      $(".comment-box").find("textarea").val("");

      <?php if (!empty($visitor_request->status)) : ?>
         let status = $("#visitor_lead_status").val();
         if ("<?= $visitor_request->status ?>" != status) {
            $(".comment-box").show();
         }
      <?php endif; ?>
   }

var suggestions = <?=$streets??[]?>;
   function set_search_location() {
       
    //   const suggestions = ["Office no 19, Second Floor, Aditya Centeegra, FC road next to Mantri house, Shivaji Nagar, Pune - 411004", "Office No. 303, 4th Floor, Sapphire Twins, AB Rd, opposite C21 Mall, Vijay Nagar, Scheme No 54, Indore, Madhya Pradesh 452011", "4th floor, The Corenthum, Tower-B, Educationvibes ,34/2, Sector 62, Noida, Uttar Pradesh 201309", "9th floor, Rajhans Helix, 901, 3, Lal Bahadur Shastri Marg, near Shreyas, Ghatkopar West, Mumbai, Maharashtra 400086", "Office no 1, Shivling smriti, opposite Udyog Bhavan, near urban co-op Bank Ltd, Shivaji Nagar, Narayan Nagar, Latur, Maharashtra 413531", "EducationVibes, Office no 408, Vishwakarma Sankul, near Gold City Hospital, The Co-operative Housieng Society, Jalgaon, Maharashtra 425001", "4th floor, Central Plaza, Door no. 6, 3-902/A, Raj Bhavan Rd, Somajiguda, Hyderabad, Telangana 500082","5 Th Floor, Akarshan Busiplex, Central Bazar Road, above ICICI Bank, Ramdaspeth, Nagpur, Maharashtra 440010"];

      const textarea = document.getElementById("address");
      const suggestionList = document.getElementById("suggestion-list");

      textarea.addEventListener("input", function() {
         let query = this.value.toLowerCase();
         suggestionList.innerHTML = "";

         if (query.length > 0) {
            let filtered = suggestions.filter(item => item.toLowerCase().includes(query));

            if (filtered.length) {
               suggestionList.style.display = "block";
               suggestionList.style.top = textarea.offsetTop + textarea.offsetHeight + "px";
               suggestionList.style.left = textarea.offsetLeft + "px";

               filtered.forEach(item => {
                  let li = document.createElement("li");
                  li.textContent = item;
                  li.style.cursor = "pointer";
                  li.onclick = () => {
                     textarea.value = item;
                     suggestionList.style.display = "none";
                  };
                  suggestionList.appendChild(li);
               });
            } else {
               suggestionList.style.display = "none";
            }
         } else {
            suggestionList.style.display = "none";
         }
      });

      document.addEventListener("click", function(e) {
         if (!suggestionList.contains(e.target) && e.target !== textarea) {
            suggestionList.style.display = "none";
         }
      });
   }
</script>