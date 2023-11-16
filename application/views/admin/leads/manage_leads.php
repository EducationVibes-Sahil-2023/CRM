<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link href="<?= base_url("assets/css/uislider.css") ?>" rel="stylesheet">
<script src="<?= base_url("assets/js/uislider.js") ?>"></script>
<style>
   div#rangeSlider {
      margin: 0px 0px 30px !important;
   }

   .noUi-horizontal {
      height: 10px !important;
   }

   .noUi-horizontal .noUi-handle {
      width: 20px;
      height: 20px;
      top: -7px;
   }

   .noUi-tooltip {
      width: 30px !important;
      bottom: -35px !important;
      top: auto !important;
   }

   .noUi-tooltip {
      width: auto !important;
      min-width: 30px !important;
   }
</style>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="#" onclick="init_lead(); return false;" class="btn mright5 btn-info pull-left display-block">
                        <?php echo _l('new_lead'); ?>
                     </a>
                     <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                        <a href="<?php echo admin_url('leads/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                           <?php echo _l('import_leads'); ?>
                        </a>
                     <?php } ?>
                     <div class="row">
                        <div class="col-md-8">
                           <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview'); return false;"><i class="fa fa-bar-chart"></i></a>
                           <!-- <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('sources_summary'); ?>" data-placement="bottom" onclick="slideToggle('.source-overview'); return false;"><i class="fa fa-bar-chart"></i></a> -->
                           <!-- <a href="<?php echo admin_url('leads/switch_kanban/' . $switch_kanban); ?>" class="btn btn-default mleft10 hidden-xs">
                           <?php if ($switch_kanban == 1) {
                              echo _l('leads_switch_to_kanban');
                           } else {
                              echo _l('switch_to_list_view');
                           }; ?>
                           </a> -->
                           <div class="row">

                              <div class="text-center  col-md-6">
                                 <h3><span id="updationCounter"><?php echo $updateCount; ?></span></h3><br>
                                 <span id="updationCounterText">Updates Count</span>
                              </div>
                              <div class="text-center  col-md-6">
                                 <h3><span id="updationCounter_time"><?php echo $call_count; ?></span></h3><br>
                                 <span id="updationCounterText_time">Updates Calls Duration</span>
                              </div>
                           </div>


                        </div>

                        <div class="col-md-4 col-xs-12 pull-right leads-search">
                           <?php if ($this->session->userdata('leads_kanban_view') == 'true' && 1 == 0) { ?>
                              <!-- <div data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                              <?php echo render_input('search', '', '', 'search', array('data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')), array(), 'no-margin') ?>
                           </div> -->
                           <?php } ?>
                           <?php echo form_hidden('sort_type'); ?>
                           <?php echo form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                        </div>
                     </div>
                     <div class="clearfix"></div>
                     <div class="row hide leads-overview">
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                        </div>
                        <div id="leadSum">
                           <?php
                           foreach ($summary as $status) { ?>
                              <div class="col-md-2 col-xs-6 border-right">
                                 <h3 class="bold">
                                    <?php
                                    if (isset($status['percent'])) {
                                       echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                    } else {
                                       // Is regular status
                                       echo $status['total'];
                                    }
                                    ?>
                                 </h3>
                                 <span style="color:<?php echo $status['color']; ?>" class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>"><?php echo $status['name']; ?></span>
                              </div>
                           <?php } ?>
                        </div>
                     </div>

                     <!-- <div class="row hide source-overview">
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <h4 class="no-margin"><?php echo _l('sources_summary'); ?></h4>
                        </div>
                        <div id="leadSum">
                        <?php
                        foreach ($summary as $status) { ?>
                              <div class="col-md-2 col-xs-6 border-right">
                                    <h3 class="bold">
                                       <?php
                                       if (isset($status['percent'])) {
                                          echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                       } else {
                                          // Is regular status
                                          echo $status['total'];
                                       }
                                       ?>
                                    </h3>
                                   <span style="color:<?php echo $status['color']; ?>" class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>"><?php echo $status['name']; ?></span>
                              </div>
                           <?php } ?>
                           </div>
                     </div> -->
                  </div>
                  <hr class="hr-panel-heading" />
                  <div class="tab-content">
                     <?php
                     if ($this->session->has_userdata('leads_kanban_view') && $this->session->userdata('leads_kanban_view') == 'true' && 1 == 0) { ?>
                        <!-- <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                        <div class="kanban-leads-sort">
                           <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                           <a href="#" onclick="leads_kanban_sort('dateadded'); return false" class="dateadded">
                           <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                              echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                           } ?><?php echo _l('leads_sort_by_datecreated'); ?>
                           </a>
                           |
                           <a href="#" onclick="leads_kanban_sort('leadorder');return false;" class="leadorder">
                           <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                              echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                           } ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                           </a>
                           |
                           <a href="#" onclick="leads_kanban_sort('lastcontact');return false;" class="lastcontact">
                           <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                              echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                           } ?><?php echo _l('leads_sort_by_lastcontact'); ?>
                           </a>
                        </div>
                        <div class="row">
                           <div class="container-fluid leads-kan-ban">
                              <div id="kan-ban"></div>
                           </div>
                        </div>
                     </div> -->
                     <?php } else { ?>
                        <div class="row" id="leads-table ">
                           <p class="bold mFilterBtn"><?php echo _l('filter_by'); ?></p>
                           <div id="filterArea" class="col-md-12 hidden-xs">
                              <div class="row">
                                 <div class="col-md-12">
                                    <p class="bold"><?php echo _l('filter_by'); ?></p>
                                 </div>
                                 <?php if (has_permission('leads', '', 'view')) { ?>
                                    <div class="col-md-2 leads-filter-column">
                                       <?php //echo render_select('view_assigned',$staff,array('staffid',array('firstname','lastname')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_dt_assigned')),array(),'no-mbot'); 
                                       ?>
                                       <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                    </div>
                                 <?php } ?>
                                 <div class="col-md-2 leads-filter-column">
                                    <?php
                                    $selected = array();
                                    if ($this->input->get('status')) {
                                       $selected[] = $this->input->get('status');
                                    } else {
                                       foreach ($statuses as $key => $status) {
                                          if ($status['isdefault'] == 0) {
                                             $selected[] = $status['id'];
                                          } else {
                                             $statuses[$key]['option_attributes'] = array('data-subtext' => _l('leads_converted_to_client'));
                                          }
                                       }
                                    }
                                    echo '<div id="leads-filter-status">';
                                    echo render_select('view_status[]', $statuses, array('id', 'name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_status');
                                    echo '</div>';
                                    ?>
                                 </div>
                                 <!-- <div class="col-md-2 leads-filter-column">
                                 <?php
                                 echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source')), array(), 'no-mbot');
                                 ?>
                              </div> -->

                                 <div class="col-md-2 leads-filter-column">
                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_source");
                                    echo '</div>';

                                    // die;
                                    ?>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('lead_type[]', $type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
                                    echo '</div>';

                                    // die;
                                    ?>
                                 </div>
                                 <!-- <div class="col-md-2 leads-filter-column">
                                    <select name="lead_type" id="lead_type" class="selectpicker" data-width="100%">
                                       <option value="">Select Lead Type</option>
                                       <?php foreach ($type as $tp => $vl) {
                                          // print_r($vl['name']);   
                                       ?>
                                          <option value="<?php echo $vl['id']; ?>"><?php echo $vl['name']; ?></option>
                                       <?php } ?>
                                    </select>
                                    <?php
                                    // echo render_leads_type_select($type, ($this->input->post('type') ? $this->input->post('type') : 'Select Lead Type'),'lead_import_type','type', [], true);
                                    ?>
                                 </div> -->
                                 <?php
                                 $neet_score_range = [];
                                 $min_range = 0;
                                 $max_range = 0;
                                 for ($i = 0; $i < 100; $i++) {
                                    $max_range = ($min_range + 150);
                                    if ($max_range > 720) {
                                       $max_range = 720;
                                       $i = 100;
                                    }
                                    $neet_score_range[] = ($min_range + 1) . " - " . $max_range;
                                    $min_range = $max_range;
                                 }
                                 ?>
                                 <div class="col-md-2 leads-filter-column">
                                    <select name="neet_score" id="neet_score" class="selectpicker" data-width="100%">
                                       <option value="">Select Neet Score</option>
                                       <?php foreach ($neet_score_range as $r_val) {
                                          // print_r($vl['name']);   
                                       ?>
                                          <option value="<?php echo $r_val; ?>"><?php echo $r_val; ?></option>
                                       <?php } ?>
                                    </select>
                                    <?php
                                    // echo render_leads_type_select($type, ($this->input->post('type') ? $this->input->post('type') : 'Select Lead Type'),'lead_import_type','type', [], true);
                                    ?>
                                 </div>




                                 <?php /*                
							<!--    <div class="col-md-2 leads-filter-column">-->
       <!--                          <?php-->
       <!--                             $selected1 = array();-->
       <!--                             if($this->input->get('degree')) {-->
       <!--                              $selected1[] = $this->input->get('degree');-->
       <!--                             } else {-->
       <!--                              foreach($degrees as $key => $status) {-->
       <!--                               $selected1[] = $status['value'];-->
                                       
       <!--                              }-->
       <!--                             }-->
       <!--                             echo '<div id="leads-filter-status">';-->
       <!--                             echo render_select('view_degree[]',$degrees,array('value','value'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_all'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false);-->
       <!--                             echo '</div>';-->
       <!--                             ?>-->
       <!--                       </div>-->
							<!--    <div class="col-md-2 leads-filter-column">-->
       <!--                          <?php-->
       <!--                             $selected1 = array();-->
       <!--                             if($this->input->get('course')) {-->
       <!--                              $selected1[] = $this->input->get('course');-->
       <!--                             } else {-->
       <!--                              foreach($courses as $key => $status) {-->
       <!--                               $selected1[] = $status['value'];-->
                                       
       <!--                              }-->
       <!--                             }-->
       <!--                             echo '<div id="leads-filter-status">';-->
       <!--                             echo render_select('view_course[]',$courses,array('value','value'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_all'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false);-->
       <!--                             echo '</div>';-->
       <!--                             ?>-->
       <!--                       </div>-->

       */ ?>

                                 <!--<p>&nbsp;</p>-->
                                 <div class="col-md-2 leads-filter-column hide">
                                    <div class="select-placeholder">
                                       <select name="custom_view" title="<?php echo _l('additional_filters'); ?>" id="custom_view" class="selectpicker" data-width="100%">
                                          <option value=""></option>
                                          <!--
                                       <option value="lost"><?php echo _l('lead_lost'); ?></option>
                                       <option value="junk"><?php echo _l('lead_junk'); ?></option>
                                       <option value="public"><?php echo _l('lead_public'); ?></option>
                                      -->
                                          <option value="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></option>
                                          <option value="created_today"><?php echo _l('created_today'); ?></option>
                                          <?php if (has_permission('leads', '', 'edit')) { ?>
                                             <option value="not_assigned"><?php echo _l('leads_not_assigned'); ?></option>
                                          <?php } ?>
                                          <?php if (isset($consent_purposes)) { ?>
                                             <optgroup label="<?php echo _l('gdpr_consent'); ?>">
                                                <?php foreach ($consent_purposes as $purpose) { ?>
                                                   <option value="consent_<?php echo $purpose['id']; ?>">
                                                      <?php echo $purpose['name']; ?>
                                                   </option>
                                                <?php } ?>
                                             </optgroup>
                                          <?php } ?>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From Created Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To Created Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="up_from_date" id="up_from_date" placeholder="From Update Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="up_to_date" id="up_to_date" placeholder="To Update Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column" style="display: none;">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="up_from_date_call" id="up_from_date_call" placeholder="From Call Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column" style="display: none;">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="up_to_date_call" id="up_to_date_call" placeholder="To Call Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="followup_from_date" id="followup_from_date" placeholder="From Followup Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="followup_to_date" id="followup_to_date" placeholder="To Followup Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-3 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="assign_from_date" id="assign_from_date" placeholder="From Assignation Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-3 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="assign_to_date" id="assign_to_date" placeholder="To Assignation Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-3 leads-filter-column">
                                    <label>Update Count Range <input type="checkbox" name="show_update_counts" value="1" id="show_update_counts" onclick="show_update_count_range(this)"> </label>
                                    <div id="rangeSlider" style="display:none;"></div>
                                    <input type="hidden" id="update_count_min" name="update_count_min">
                                    <input type="hidden" id="update_count_max" name="update_count_max">
                                 </div>
                                 <div class="col-md-3 text-center leads-filter-column">
                                    <div class="form-group">
                                       <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>

                                       <!-- <button class="btn btn-primary" id="apply_filter">Apply Filter</button> -->
                                       <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="clearfix"></div>
                           <hr class="hr-panel-heading" />
                           <div class="col-md-12">
                              <a href="#" data-toggle="modal" data-table=".table-leads" data-target="#leads_bulk_actions" class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                              <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1" role="dialog">
                                 <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                       <div class="modal-header">
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                       </div>
                                       <div class="modal-body">
                                          <?php if (has_permission('leads', '', 'delete')) { ?>
                                             <div class="checkbox checkbox-danger">
                                                <input type="checkbox" name="mass_delete" id="mass_delete">
                                                <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                             </div>
                                          <?php } ?>
                                          <?php if (has_permission('leads', '', 'delete')) { ?>
                                             <div class="checkbox checkbox-danger">
                                                <input type="checkbox" name="mass_re-assignation" id="mass_re-assignation">
                                                <label for="mass_re-assignation"><?php echo _l('mass_re-assignation'); ?></label>
                                             </div>
                                             <hr class="mass_delete_separator" />
                                          <?php } ?>
                                          <div id="bulk_change">
                                             <div class="form-group hide">
                                                <div class="checkbox checkbox-primary checkbox-inline">
                                                   <input type="checkbox" name="leads_bulk_mark_lost" id="leads_bulk_mark_lost" value="1">
                                                   <label for="leads_bulk_mark_lost">
                                                      <?php echo _l('lead_mark_as_lost'); ?>
                                                   </label>
                                                </div>
                                             </div>
                                             <?php echo render_select('move_to_status_leads_bulk', $statuses, array('id', 'name'), 'ticket_single_change_status'); ?>
                                             <?php
                                             echo render_select('move_to_source_leads_bulk', $sources, array('id', 'name'), 'lead_source');
                                             ?>
                                             <div class="form-group">
                                                <label for="leadtype" class="control-label">Lead Type</label>
                                                <select name="leadtype" id="leadtype" class="selectpicker" data-width="100%">
                                                   <option value="">Select Lead Type</option>
                                                   <?php foreach ($type as $tp => $vl) {
                                                   ?>
                                                      <option value="<?php echo $vl['id']; ?>"><?php echo $vl['name']; ?></option>
                                                   <?php } ?>
                                                </select>
                                             </div>


                                             <div class="hide">
                                                <?php
                                                echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                                ?>
                                             </div>
                                             <?php
                                             if (has_permission('leads', '', 'assign')) {
                                                echo render_select('assign_to_leads_bulk', $staff, array('staffid', array('firstname', 'lastname')), 'leads_dt_assigned');
                                             }
                                             ?>
                                             <div class="form-group">
                                                <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value="" data-role="tagsinput">
                                             </div>
                                             <!-- <hr /> -->
                                             <div class="form-group no-mbot hide">
                                                <div class="radio radio-primary radio-inline">
                                                   <input type="radio" name="leads_bulk_visibility" id="leads_bulk_public" value="public">
                                                   <label for="leads_bulk_public">
                                                      <?php echo _l('lead_public'); ?>
                                                   </label>
                                                </div>
                                                <div class="radio radio-primary radio-inline">
                                                   <input type="radio" name="leads_bulk_visibility" id="leads_bulk_private" value="private">
                                                   <label for="leads_bulk_private">
                                                      <?php echo _l('private'); ?>
                                                   </label>
                                                </div>
                                             </div>
                                          </div>
                                          <div id="re-assignation_div" style="display:none;">
                                             <?php echo render_select('mass_assigned', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot', '', false, 'mass_assigned'); ?>

                                          </div>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                          <a href="#" class="btn btn-info" onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                       </div>
                                    </div>
                                    <!-- /.modal-content -->
                                 </div>
                                 <!-- /.modal-dialog -->
                              </div>
                              <!-- /.modal -->
                              <?php
                              $table_data = array();
                              $_table_data = array(
                                 '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label></div>',
                                 array(
                                    'name' => _l('Reminder Flag'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                                 ),
                                 array(
                                    'name' => _l('Update Count'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                                 ),
                                 array(
                                    'name' => _l('Call Durations'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                                 ),
                                 array(
                                    'name' => _l('Last-Call-Date'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                                 ),
                                 array(
                                    'name' => _l('leads_dt_name'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-name')
                                 ),
                              );
                              if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                                 $_table_data[] = array(
                                    'name' => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
                                    'th_attrs' => array('id' => 'th-consent', 'class' => 'not-export')
                                 );
                              }
                              $_table_data[] =  array(
                                 'name' => _l('leads_dt_phonenumber'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-phone')
                              );

                              $_table_data[] = array(
                                 'name' => _l('leads_dt_status'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-status')
                              );

                              $custom_fields = get_custom_fields('leads', array('show_on_table' => 1));
                              //          foreach($custom_fields as $field){
                              //   if($field['name'] == 'Degree'){
                              //          array_push($_table_data,$field['name']);
                              //   }
                              //          } 
                              // foreach ($custom_fields as $field) {
                              //    if ($field['name'] == 'NEET Score') {
                              //       array_push($_table_data, $field['name']);
                              //    }
                              // }
                              // foreach ($custom_fields as $field) {
                              //    if ($field['name'] == 'Intake') {
                              //       array_push($_table_data, $field['name']);
                              //    }
                              // }
                              // foreach ($custom_fields as $field) {
                              //    if ($field['name'] == 'Course') {
                              //       array_push($_table_data, $field['name']);
                              //    }
                              // }
                              foreach ($custom_fields as $key => $field) {
                                 array_push($_table_data, $field['name']);
                              }
                              //   foreach($custom_fields as $field){
                              //          if($field['name'] == 'Course'){
                              //          array_push($_table_data,$field['name']);
                              //   }
                              //          }
                              $_table_data[] = array(
                                 'name' => _l('Lead Type'),

                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-lead-type')

                              );
                              $_table_data[] = array(
                                 'name' => _l('lead_website'),

                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-website')

                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_source'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-source')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_datecreated'),
                                 'th_attrs' => array('class' => 'date-created toggleable', 'id' => 'th-date-created')
                              );

                              $_table_data[] = array(
                                 'name' => _l('Last Updated'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-last-contact')
                              );

                              $_table_data[] =   array(
                                 'name' => _l('leads_dt_email'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-email')
                              );

                              // foreach ($custom_fields as $field) {
                              //    if ($field['name'] == 'Call Type') {
                              //       array_push($_table_data, $field['name']);
                              //    }
                              // }

                              $_table_data[] = array(
                                 'name' => _l('leads_dt_assigned'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-assigned')
                              );

                              $_table_data[] = array(
                                 'name' => _l('Assigned Date'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-dateassigned')
                              );






                              // foreach($custom_fields as $field){
                              //          if($field['name'] == 'Gender'){
                              //          array_push($_table_data,$field['name']);
                              //   }
                              //          }

                              ///////////////////////////////////////////////////////////////////////////////////
                              // foreach ($custom_fields as $field) {
                              //    if ($field['name'] == 'Destination') {
                              //       array_push($_table_data, $field['name']);
                              //    }
                              // }

                              $_table_data[] = array(
                                 'name' => _l('lead_city'),

                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-city')

                              );
                              $_table_data[] = array(
                                 'name' => _l('lead_state'),

                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-state')

                              );

                              /*
                              <?php echo _l('lead_website'); ?>
                              $_table_data[] =  array(
                                 'name'=>_l('leads_dt_lead_value'),
                                 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-lead-value')
                                );
                              */
                              $_table_data[] =  array(
                                 'name' => _l('tags'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-tags')
                              );






                              $_table_data[] = array(
                                 'name' => _l('Followup Date'),
                                 'th_attrs' => array('class' => 'date-created toggleable', 'id' => 'th-period')
                              );
                              foreach ($_table_data as $_t) {
                                 array_push($table_data, $_t);
                              }

                              $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                              render_datatable(
                                 $table_data,
                                 'leads',
                                 array('customizable-table'),
                                 array(
                                    'id' => 'table-leads',
                                    'data-last-order-identifier' => 'leads',
                                    'data-default-order' => get_table_last_order('leads'),
                                 )
                              ); ?>
                           </div>
                        </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script id="hidden-columns-table-leads" type="text/json">
   <?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>
<script>
   var max_count = parseInt("<?= !empty($updateCount_max) ? $updateCount_max : 0 ?>");

   function show_update_count_range(obj) {
      if ($(obj).is(":checked")) {
         $("#rangeSlider").show();
         setMinMaxValues();
      } else {
         $("#rangeSlider").hide();

      }
   }

   function setMinMaxValues() {
      // Get the current values of the slider
      var currentValues = rangeSlider.noUiSlider.get();

      // Update the options with new min and max values
      rangeSlider.noUiSlider.updateOptions({
         range: {
            'min': 0,
            'max': max_count
         },
         start: [0, max_count] // Preserve the current slider values
      });
   }

   function recreate_range_slider(max) {
      if (max != undefined && parseInt(max) != max_count) {
         rangeSlider.noUiSlider.destroy();
         max_count = parseInt(max);
         let min_ = document.getElementById("update_count_min").value;
         let max_ = document.getElementById("update_count_max").value;
         make_range_slider(min_, max_);
      }
   }
   // Initialize the range slider
   function make_range_slider(min = 0, max = 0) {
      var rangeSlider = document.getElementById('rangeSlider');
      if (max == 0) {
         max = max_count;
      }
      noUiSlider.create(rangeSlider, {
         start: [min, max], // Initial values for min and max
         connect: true,
         tooltips: [true, true],
         format: {
            to: function(value) {
               return Math.round(value); // Round the tooltip values
            },
            from: function(value) {
               return parseFloat(value); // Convert tooltip values to numbers
            }
         },
         step: 1,
         range: {
            'min': 0,
            'max': max_count
         }
      });

      // Get handles for min and max sliders
      var sliderHandles = rangeSlider.getElementsByClassName('noUi-handle');
      var minSliderHandle = sliderHandles[0];
      var maxSliderHandle = sliderHandles[1];

      // Set event listeners for slider change
      rangeSlider.noUiSlider.on('update', function(values, handle) {
         var minValue = parseFloat(values[0]);
         var maxValue = parseFloat(values[1]);

         // Update the hidden input values
         document.getElementById('update_count_min').value = minValue;
         document.getElementById('update_count_max').value = maxValue;
      });

      // Set event listeners for slider handle drag
      minSliderHandle.addEventListener('drag', function() {
         var minValue = parseFloat(rangeSlider.noUiSlider.get()[0]);
         rangeSlider.noUiSlider.set([minValue, null]);
      });

      maxSliderHandle.addEventListener('drag', function() {
         var maxValue = parseFloat(rangeSlider.noUiSlider.get()[1]);
         rangeSlider.noUiSlider.set([null, maxValue]);
      });
   }
   make_range_slider("", "");

   var openLeadID = '<?php echo $leadid; ?>';
   $(function() {
      leads_kanban();
      $('#leads_bulk_mark_lost').on('change', function() {
         $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
         $('#move_to_status_leads_bulk').selectpicker('refresh')
      });
      $('#move_to_status_leads_bulk').on('change', function() {
         if ($(this).selectpicker('val') != '') {
            $('#leads_bulk_mark_lost').prop('disabled', true);
            $('#leads_bulk_mark_lost').prop('checked', false);
         } else {
            $('#leads_bulk_mark_lost').prop('disabled', false);
         }
      });
   

      $('#apply_filter').on('click', function() {

         var from_date = document.getElementById("from_date").value;
         var to_date = document.getElementById("to_date").value;
         var assign_from_date = document.getElementById("assign_from_date").value;
         var assign_to_date = document.getElementById("assign_to_date").value;
         var followup_from_date = document.getElementById("followup_from_date").value;
         var followup_to_date = document.getElementById("followup_to_date").value;
         var up_from_date = document.getElementById("up_from_date").value;
         var up_to_date = document.getElementById("up_to_date").value;
         var up_from_date_call = document.getElementById("up_from_date_call").value;
         var up_to_date_call = document.getElementById("up_to_date_call").value;

         if (to_date != '') {
            if (from_date == '') {
               $("#from_date").focus();
               return false;
            }
         }

         if (from_date != '') {
            if (to_date == '') {
               $("#to_date").focus();
               return false;
            }
         }

         if (assign_to_date != '') {
            if (assign_from_date == '') {
               $("#assign_from_date").focus();
               return false;
            }
         }

         if (assign_from_date != '') {
            if (assign_to_date == '') {
               $("#assign_to_date").focus();
               return false;
            }
         }
         if (followup_to_date != '') {
            if (followup_from_date == '') {
               $("#followup_from_date").focus();
               return false;
            }
         }

         if (followup_from_date != '') {
            if (followup_to_date == '') {
               $("#followup_to_date").focus();
               return false;
            }
         }

         if (up_to_date != '') {
            if (up_from_date == '') {
               $("#up_from_date").focus();
               return false;
            }
         }

         if (up_from_date != '') {
            if (up_to_date == '') {
               $("#up_to_date").focus();
               return false;
            }
         }

         if (up_to_date_call != '') {
            if (up_from_date_call == '') {
               $("#up_from_date_call").focus();
               return false;
            }
         }

         if (up_from_date_call != '') {
            if (up_to_date_call == '') {
               $("#up_to_date_call").focus();
               return false;
            }
         }
         show_loader("apply_filter");
         periodFilter();
         summary();
      });

      function periodFilter() {


         table_leads.DataTable().ajax.reload(null, false).on('draw.dt', function() {
            hide_loader("apply_filter");
         });

      }
      var xhr = null;

      function summary() {
         var element_view_assign = document.getElementById("view_assigned");
         var element_view_source = document.getElementById("view_source");
         var element_view_status = document.getElementById("view_status");
         var element_lead_type = document.getElementById("lead_type");
         // if (typeof(element) != 'undefined' && element != null)
         // {
         //    var view_assigned = document.getElementById("view_assigned").value;
         // }else{
         //    var view_assigned = '';
         // }
         // var view_source = document.getElementById("view_source").value;
         // var view_status = document.getElementById("view_status").value;
         var view_assigned_options = "";
         var view_source_options = "";
         var view_status_options = "";
         var view_lead_type_options = "";
         if (typeof(element_view_source) != 'undefined' && element_view_source != null) {
            view_source_options = document.getElementById('view_source').selectedOptions;
            view_source_options = Array.from(view_source_options).map(({
               value
            }) => value);
         }
         if (typeof(element_view_status) != 'undefined' && element_view_status != null) {
            view_status_options = document.getElementById('view_status').selectedOptions;
            view_status_options = Array.from(view_status_options).map(({
               value
            }) => value);
         }
         if (typeof(element_lead_type) != 'undefined' && element_lead_type != null) {
            view_lead_type_options = document.getElementById('lead_type').selectedOptions;
            view_lead_type_options = Array.from(view_lead_type_options).map(({
               value
            }) => value);
         }

         if (typeof(element_view_assign) != 'undefined' && element_view_assign != null) {
            view_assigned_options = document.getElementById('view_assigned').selectedOptions;
            view_assigned_options = Array.from(view_assigned_options).map(({
               value
            }) => value);
         }

         /* var view_course = document.getElementById("view_course").value;
          var courseid ='16';
          var view_degree = document.getElementById("view_degree").value;
          var degreeid ='20';*/
         // console.log(view_status);
         var custom_view = document.getElementById("custom_view").value;
         var from_date = document.getElementById("from_date").value;
         var to_date = document.getElementById("to_date").value;
         var up_from_date = document.getElementById("up_from_date").value;
         var up_to_date = document.getElementById("up_to_date").value;
         var followup_from_date = document.getElementById("followup_from_date").value;
         var followup_to_date = document.getElementById("followup_to_date").value;
         var assign_from_date = document.getElementById("assign_from_date").value;
         var assign_to_date = document.getElementById("assign_to_date").value;
         var update_count_min, update_count_max = '';
         var up_from_date_call = document.getElementById("up_from_date_call").value;
         var up_to_date_call = document.getElementById("up_to_date_call").value;
         if ($("#show_update_counts").is(":checked")) {
            update_count_min = document.getElementById("update_count_min").value;
            update_count_max = document.getElementById("update_count_max").value;
         }

         if (xhr != null) {
            xhr.abort();
         }
         xhr = $.ajax({
            type: "POST",
            url: "leads/lead_summary_filter",
            // data: {lead_type: $("#lead_type").val()},
            /* data : {lead_type: $("#lead_type").val(), assigned: view_assigned,source:view_source,course:view_course,courseid:courseid,degree:view_degree,degreeid:degreeid, from_date: from_date, to_date:to_date, up_from_date: up_from_date, up_to_date: up_to_date, followup_from_date: followup_from_date, followup_to_date: followup_to_date, assign_from_date: assign_from_date, assign_to_date: assign_to_date},*/
            //  data : {lead_type: $("#lead_type").val(), assigned: view_assigned,source:view_source_options,from_date: from_date, to_date:to_date, up_from_date: up_from_date, up_to_date: up_to_date, followup_from_date: followup_from_date, followup_to_date: followup_to_date, assign_from_date: assign_from_date, assign_to_date: assign_to_date,status:view_status_options},
            data: {
               lead_type: view_lead_type_options,
               assigned: view_assigned_options,
               source: view_source_options,
               from_date: from_date,
               to_date: to_date,
               up_from_date: up_from_date,
               up_to_date: up_to_date,
               followup_from_date: followup_from_date,
               followup_to_date: followup_to_date,
               assign_from_date: assign_from_date,
               assign_to_date: assign_to_date,
               status: view_status_options,
               update_count_min: update_count_min,
               update_count_max: update_count_max,
               neet_score: $("#neet_score").val(),
               up_from_date_call: up_from_date_call,
               up_to_date_call: up_to_date_call,


            },
            dataType: "JSON",
            cache: false,
            success: function(data) {
               //alert(data);  //as a debugging message.
               $("#leadSum").html('');
               $("#leadSum").html(data.status);
               $("#leadSum").innerHTML = data.status;
               $("#updationCounter").html(data.update_count);
               $("#updationCounter_time").html(data.call_count);
               if (data.max_count != undefined && parseInt(data.max_count) > 0) {
                  recreate_range_slider(data.max_count);
               }
            }
         }); // you have missed this bracket
         return false;
      }

      summary();
   });
</script>
<script>
   $(".mFilterBtn").click(function() {
      var element = document.getElementById("filterArea");
      // console.log(element.classList);
      element.classList.remove("hidden-xs");
      // $("#filterArea").toggle();
   });
   // });
</script>

</body>

</html>