<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link
   rel="stylesheet"
   href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.css" />
<?php init_head();
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
$reference_name = $this->db
   ->select("reference_name as name")
   ->where('reference_name !=', "")
   ->group_by("reference_name")
   ->order_by("reference_name", "ASC")
   ->get(db_prefix() . 'leads')
   ->result_array();

?>

<link href="<?= base_url("assets/css/uislider.css") ?>" rel="stylesheet">
<script src="<?= base_url("assets/js/uislider.js") ?>"></script>


<style>
   div#rangeSlider {
      margin: 0px 0px 30px !important;
   }

   .noUi-horizontal {
      height: 10px !important;
   }

   .border-right {
      text-align: center;
      border-right: 1px solid #f0f0f0;
      /* border: 1px solid black; */
      margin: 10px;
      padding: 10px;
      border-radius: 8px 20px;
      box-shadow: 1px 1px 6px 1px lightgray;
   }

   .noUi-horizontal .noUi-handle {
      width: 20px;
      height: 20px;
      top: -7px;
   }

   .lead-transfer-table .table-responsive {
      /*overflow: unset !important;*/
      /*overflow-x: unset !important;*/
   }

   .dropup .dropdown-menu {
      height: 200px;
      overflow: auto;
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

   .dropdown-menu-right {
      bottom: unset !important;
      z-index: 9;
   }

   #filter-right-side .bootstrap-select .dropdown-menu {
      width: -webkit-fill-available !important;
   }
</style>
<style>
   .date-picker-container {
      display: flex;
      flex-direction: column;
      gap: 15px;
      max-width: 400px;
      margin: auto;
   }

   .date-label {
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
   }

   .date-filter {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 5px 5px;
      border: 1px solid #ddd;
      background: #fff;
      cursor: pointer;
      transition: 0.3s;
      width: 100%;
   }

   .pull-right>.dropdown-menu li {
      padding: 5px 10px;
   }

   .width250 {
      padding: 10px;
      width: 1000px;
   }


   .right-menu-filter .bootstrap-select .btn-default,
   .right-menu-filter li .form-control span,
   .right-menu-filter .set_disabled_date,
   .right-menu-filter button.btn,
   .right-menu-filter .form-control::placeholder {
      padding: 4px 10px;
      line-height: 2;
      height: 30px;
      text-transform: inherit;
      padding-left: 10px;
      font-size: 12px;
   }

   .right-menu-filter ._filter_data .dropdown-menu li a,
   .right-menu-filter .bootstrap-select .dropdown-menu li a {
      font-size: 12px;
      padding: 0px !important;
   }

   .border-card {
      margin-bottom: 10px;
      text-align: center;
      padding: 5px 0px;
      box-shadow: 1px 1px 6px 1px lightgray;
   }

   .border-card h3.bold {
      margin: 5px !important;
   }

   .border-card span {
      margin: 5px !important;
   }

   #leadSum h4.no-margin {
      font-size: 18px;
      padding: 10px 0px;
   }

   #leadSum .panel-body {
      border-radius: 0px;
      padding: 10px 0px;
   }

   .admin #side-filter.is_fixed {
      padding-top: 20px;
      position: fixed;
      height: 100vh !important;
      overflow: auto;
      top: 0px;
      background: white;
      width: -webkit-fill-available;
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
                     <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1' || has_permission('leads', '', 'import')) { ?>
                        <a href="<?php echo admin_url('leads/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                           <?php echo _l('import_leads'); ?>
                        </a>
                     <?php } ?>
                     <div class="row">
                        <div class="col-md-4">
                           <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview');  summary(1); return false;"><i class="fa fa-bar-chart"></i></a>

                           <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo "Show Update count and Call Duration"; ?>" data-placement="bottom" onclick="slideToggle('.leads-count-overview'); summary(2); return false; "><i class="fa fa-clock-o"></i></a>

                           <!-- <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('sources_summary'); ?>" data-placement="bottom" onclick="slideToggle('.source-overview'); return false;"><i class="fa fa-bar-chart"></i></a> -->
                           <!-- <a href="<?php echo admin_url('leads/switch_kanban/' . $switch_kanban); ?>" class="btn btn-default mleft10 hidden-xs">
                           <?php if ($switch_kanban == 1) {
                              echo _l('leads_switch_to_kanban');
                           } else {
                              echo _l('switch_to_list_view');
                           }; ?>
                           </a> -->



                        </div>

                        <div class="col-md-4 col-xs-12 pull-right leads-search">
                           <?php if ($this->session->userdata('leads_kanban_view') == 'true' && 1 == 0) { ?>
                              <!-- <div data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                              <?php echo render_input('search', '', '', 'search', array('data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')), array(), 'no-margin') ?>
                           </div> -->
                           <?php } ?>
                           <?php echo form_hidden('sort_type'); ?>
                           <?php echo form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                           <!-- <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data hide" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                             
                              <ul class="dropdown-menu dropdown-menu-right width250 right-menu-filter">




                              </ul> 
                           </div> -->
                        </div>
                     </div>
                     <div class="clearfix"></div>
                     <div class="row hide leads-overview">
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                           <hr class="hr-panel-heading" />

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
                     <div class="row hide leads-count-overview">
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                        </div>
                        <div class="row">

                           <div class="text-center  col-md-6">
                              <h3><span id="updationCounter"><?php echo $updateCount; ?></span></h3><br>
                              <span id="updationCounterText">Update Count</span>
                           </div>
                           <div class="text-center  col-md-6">
                              <h3><span id="updationCounter_time"><?php echo $call_count; ?></span></h3><br>
                              <span id="updationCounterText_time">Updates Calls Duration</span>
                           </div>
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
                           <!-- <p class="bold mFilterBtn"><?php echo _l('filter_by'); ?></p> -->
                           <div id="filterArea" class="col-md-12 hidden-xs hide">
                              <div class="row">
                                 <div class="col-md-12">
                                    <p class="bold"><?php echo _l('filter_by'); ?></p>
                                 </div>
                                 <?php if (has_permission('leads', '', 'view')) { ?>
                                    <!-- <div class="col-md-2 leads-filter-column">
                                  
                                       <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                    </div> -->
                                 <?php } ?>
                                 <!-- <div class="col-md-2 leads-filter-column">
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
                                    echo render_select('view_status[]', $statuses, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_status');
                                    echo '</div>';
                                    ?>
                                 </div> -->
                                 <!-- <div class="col-md-2 leads-filter-column">
                                 <?php
                                 // echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source')), array(), 'no-mbot');
                                 ?>
                              </div> -->

                                 <!-- <div class="col-md-2 leads-filter-column">
                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_source");
                                    echo '</div>';
                                    ?>
                                 </div> -->
                                 <!-- <div class="col-md-2 leads-filter-column">
                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('lead_type[]', $type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
                                    echo '</div>';

                                    // die;
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

                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="select-placeholder">
                                       <select name="custom_view" title="<?php echo _l('additional_filters'); ?>" id="custom_view" class="selectpicker" data-width="100%">
                                          <option value=""></option>
                                          <option value="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></option>
                                          <option value="created_today"><?php echo _l('created_today'); ?></option>
                                          <?php if (!has_permission('leads', '', 'view')) { ?>
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
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From Created Date" autocomplete="off">
                                    </div>

                                 </div>
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To Created Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="up_from_date" id="up_from_date" placeholder="From Update Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-   ">
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
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="followup_from_date" id="followup_from_date" placeholder="From Followup Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="followup_to_date" id="followup_to_date" placeholder="To Followup Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="assign_from_date" id="assign_from_date" placeholder="From Assignation Date" autocomplete="off">
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-   ">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="assign_to_date" id="assign_to_date" placeholder="To Assignation Date" autocomplete="off">
                                    </div>
                                 </div>

                                 <!-- <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker set_disabled_date" name="last_contact_date" onchange="set_disabled_date(this.value)" id="last_contact_date" placeholder="Last Connected Date" autocomplete="off">
                                    </div>
                                 </div> -->

                                 <!-- <div class="col-md-2 leads-filter-column">
                                    <div class="form-group">
                                       <input type="text" class="form-control datepicker set_disabled_date" onchange="set_disabled_date(this.value)" name="last_update_date" id="last_update_date" placeholder="Last Updated Date" autocomplete="off">
                                    </div>
                                 </div> -->
                                 <!-- <div class="col-md-3 leads-filter-column">
                                    <label>Update Count Range <input type="checkbox" name="show_update_counts" value="1" class="set_disabled_date disabled_checkbox" id="show_update_counts" onclick="show_update_count_range(this); set_disabled_date(this.checked ? 1 : '');"> </label>
                                    <div id="rangeSlider" style="display:none;"></div>
                                    <input type="hidden" id="update_count_min" name="update_count_min">
                                    <input type="hidden" id="update_count_max" name="update_count_max">
                                 </div> -->
                                 <div class="col-md-3 text-center leads-filter-column">
                                    <!-- <div class="form-group">
                                       <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>
                                       <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                    </div> -->
                                 </div>
                              </div>
                           </div>



                           <!-- 
                           <div class="clearfix"></div>
                           <hr class="hr-panel-heading" /> -->
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                           <div>
                              <button class="btn mright5 btn-info pull-left display-block" data-toggle="tooltip" data-title="<?php echo _l('Lead Transfer Request'); ?>" onclick="show_lead_request()" data-placement="bottom">Lead Transfer Request</button>
                           </div>
                           <hr>

                           <div class="lead-transfer-table hide">
                              <br>
                              <br>
                              <?php
                              if (is_admin()) {
                                 render_datatable(array(_l('Raised by'), _l('Lead Type'), "Lead Source", _l('Assignation'), _l('PhoneNumber'), _l('New Lead Type'), "New Lead Source", _l('Reason'), _l('Status'), _l('Created Date'), _l("Action")), 'lead-transfer-table');
                              } else {
                                 render_datatable(array(_l('Lead Type'), "Lead Source", _l('Assignation'), _l('PhoneNumber'), _l('Reason'), _l('Status'), _l('Created By'), _l('Created Date'), _l("Action")), 'lead-transfer-table');
                              }
                              ?>
                              <hr class="hr-panel-heading" />

                           </div>
                           <br>
                           <br>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12 hide">
                           <div>
                              <button class="btn mright5 btn-info pull-left display-block" data-toggle="tooltip" data-title="<?php echo _l('Lead Visitor Request'); ?>" onclick="show_lead_request_visitor()" data-placement="bottom">Lead Visitor Request</button>
                           </div>
                           <hr>

                           <div class="lead-visitor-table hide">
                              <br>
                              <br>
                              <?php
                              if (is_admin()) {
                                 render_datatable(array(_l('Raised by'), _l('Lead Type'), "Lead Source", _l('Assignation'), _l('PhoneNumber'), _l('New Lead Type'), "New Lead Source", _l('Reason'), _l('Status'), _l('Created Date'), _l("Action")), 'lead-transfer-table');
                              } else {
                                 render_datatable(array(_l('Lead Type'), "Lead Source", _l('Assignation'), _l('PhoneNumber'), _l('Reason'), _l('Status'), _l('Created By'), _l('Created Date'), _l("Action")), 'lead-transfer-table');
                              }
                              ?>
                              <hr class="hr-panel-heading" />

                           </div>
                           <br>
                           <br>
                        </div>

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
                                       <?php if (has_permission('leads', '', 'mass_assign')) { ?>
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
                                          <?php
                                          if (has_permission('leads', '', 'assign')) {
                                          ?>
                                             <div class="checkbox checkbox-danger delete_created_date hide">
                                                <input type="checkbox" name="delete_created_date" id="delete_created_date">
                                                <label for="delete_created_date"><?php echo _l('Remove Created Date'); ?></label>
                                             </div>
                                          <?php
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
                              '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap">
                                     <input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label>
                                 </div>',
                              array(
                                 'name' => _l('Flag'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                              )
                           );

                           /// change follow date


                           // Common columns for both roles
                           $_table_data = array_merge($_table_data, array(
                              array(
                                 'name' => _l('Count'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                              ),
                              array(
                                 'name' => _l('Durations'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                              ),
                              array(
                                 'name' => _l('Connected'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                              ),
                              array(
                                 'name' => _l('First Conn Diff'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                              ),
                              array(
                                 'name' => _l('leads_dt_datecreated'),
                                 'th_attrs' => array('class' => 'date-created toggleable', 'id' => 'th-date-created')
                              ),
                              array(
                                 'name' => _l('Updated'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-last-contact')
                              ),
                              array(
                                 'name' => _l('tags'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-tags')
                              ),
                              array(
                                 'name' => _l('leads_dt_name'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-name')
                              )
                           ));
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

                           if (is_admin()) {
                              foreach ($custom_fields as $key => $field) {
                                 array_push($_table_data, $field['name']);
                              }
                           }

                           $_table_data[] = array(
                              'name' => _l('Lead Type'),

                              'th_attrs' => array('class' => 'toggleable', 'id' => 'th-lead-type')

                           );
                           if (is_admin()) {
                              $_table_data[] = array(
                                 'name' => _l('lead_website'),

                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-website')

                              );
                           }
                           if (is_admin() || $role == 3) {
                              $_table_data[] = array(
                                 'name' => "Reference Name",

                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-reference')

                              );
                           }
                           $_table_data[] = array(
                              'name' => _l('leads_source'),
                              'th_attrs' => array('class' => 'toggleable', 'id' => 'th-source')
                           );

                           if ($role != 1) {

                              $_table_data[] =   array(
                                 'name' => _l('leads_dt_email'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-email')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_assigned'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-assigned')
                              );
                           }
                           $_table_data[] = array(
                              'name' => _l('Assigned Date'),
                              'th_attrs' => array('class' => 'toggleable', 'id' => 'th-dateassigned')
                           );
                           $_table_data[] = array(
                              'name' => _l('lead_city'),

                              'th_attrs' => array('class' => 'toggleable', 'id' => 'th-city')

                           );
                           $_table_data[] = array(
                              'name' => _l('lead_state'),

                              'th_attrs' => array('class' => 'toggleable', 'id' => 'th-state')

                           );
                           // $_table_data[] =  array(
                           //    'name' => _l('tags'),
                           //    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-tags')
                           // );

                           if ($role != 1) {
                              $_table_data[] = array(
                                 'name' => _l('Followup Date'),
                                 'th_attrs' => array('class' => 'date-created toggleable', 'id' => 'th-period')
                              );
                           }

                           foreach ($_table_data as $_t) {
                              array_push($table_data, $_t);
                           }

                           $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                           render_datatable(
                              $table_data,
                              'leads',
                              array('customizable-table sticky-header'),
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

<aside id="filter-right-side" class="sidefilter-right">

   <ul class="nav metis-filter sticky-fixed" style="display:none;" id="side-filter">
      <li>
         <h5><?php echo _l('filter_by'); ?></h2>
      </li>
      <?php if (has_permission('leads', '', 'view')) { ?>
         <li class="">
            <div class="leads-filter-column">
               <?php echo render_select(
                  'view_assigned[]',
                  $staff,
                  array('staffid', array('firstname', 'lastname')),
                  '',
                  '',
                  array(
                     'data-width' => '100%',
                     'data-none-selected-text' => _l('leads_dt_assigned'),
                     'multiple' => true,
                     'data-actions-box' => true
                  ),
                  array(),
                  'no-mbot',
                  '',
                  false,
                  'view_assigned'
               ); ?>
            </div>
         </li>
      <?php } ?>
      <li>
         <div class="leads-filter-column">
            <?php
            $selected = array();
            echo '<div id="leads-filter-status">';
            echo render_select(
               'view_status[]',
               $statuses,
               array('id', 'name'),
               '',
               '',
               array(
                  'data-width' => '100%',
                  'data-none-selected-text' => _l('leads_all'),
                  'multiple' => true,
                  'data-actions-box' => true
               ),
               array(),
               'no-mbot',
               '',
               false,
               'view_status'
            );
            echo '</div>';
            ?>
         </div>
      </li>
      <li>
         <div class="leads-filter-column">
            <div id="leads-filter-source">
               <?php
               echo render_select(
                  'view_source[]',
                  $sources,
                  array('id', 'name'),
                  '',
                  '',
                  array(
                     'data-width' => '100%',
                     'data-none-selected-text' => _l('leads_source'),
                     'multiple' => true,
                     'data-actions-box' => true
                  ),
                  array(),
                  'no-mbot',
                  '',
                  false,
                  "view_source"
               );
               ?>
            </div>
         </div>
      </li>
      <li class="">
         <div class="leads-filter-column">
            <div id="leads-filter-type">
               <?php
               echo render_select(
                  'lead_type[]',
                  $type,
                  array('id', 'name'),
                  '',
                  '',
                  array(
                     'data-width' => '100%',
                     'data-none-selected-text' => _l('lead_import_type'),
                     'multiple' => true,
                     'data-actions-box' => true
                  ),
                  array(),
                  'no-mbot',
                  '',
                  false,
                  "lead_type"
               );
               ?>
            </div>
         </div>
      </li>
      <li class="">
         <div class="leads-filter-column">
            <div id="leads-filter-type">
               <?php
               echo render_select(
                  'view_form[]',
                  $view_form,
                  array('id', 'name'),
                  '',
                  '',
                  array(
                     'data-width' => '100%',
                     'data-none-selected-text' => "Form Name",
                     'multiple' => true,
                     'data-actions-box' => true
                  ),
                  array(),
                  'no-mbot',
                  '',
                  false,
                  "view_form"
               );
               ?>
            </div>
         </div>
      </li>
      <?php if (is_admin() || $role == 3) { ?>
         <li class="">
            <div class="leads-filter-column">
               <div id="leads-filter-refrence">
                  <?php
                  echo render_select(
                     'reference_name[]',
                     $reference_name,
                     array('name', 'name'),
                     '',
                     '',
                     array(
                        'data-width' => '100%',
                        'data-none-selected-text' => "Reference Name",
                        'multiple' => true,
                        'data-actions-box' => true
                     ),
                     array(),
                     'no-mbot',
                     '',
                     false,
                     "reference_name"
                  );
                  ?>
               </div>
            </div>
         </li>
      <?php } ?>
      <li class="">
         <div id="from_date_right" data-from="from_date" data-to="to_date" class="date-filter form-control">
            <i class="fa fa-calendar"></i>
            <span data-label="Created Date">Created Date</span>
            <i class="fa fa-chevron-down"></i>
         </div>
      </li>
      <li class="">
         <div id="update_date_right" data-from="up_from_date" data-to="up_to_date" class="date-filter form-control">
            <i class="fa fa-calendar"></i>
            <span data-label="Update Date">Update Date</span>
            <i class="fa fa-chevron-down"></i>
         </div>
      </li>
      <?php if (is_admin()) {
      ?>
      <?php
      }
      ?>
      <li class="">
         <div id="follow_date_right" data-from="followup_from_date" data-to="followup_to_date" class="date-filter form-control">
            <i class="fa fa-calendar"></i>
            <span data-label="Follow-up Date">Follow-up Date</span>
            <i class="fa fa-chevron-down"></i>
         </div>
      </li>
      <li class="">
         <div id="assign_date_right" data-from="assign_from_date" data-to="assign_to_date" class="date-filter form-control">
            <i class="fa fa-calendar"></i>
            <span data-label="Assignation Date">Assignation Date</span>
            <i class="fa fa-chevron-down"></i>
         </div>
      </li>
      <li class="">
         <input type="text" class="form-control datepicker set_disabled_date" name="last_contact_date" onchange="set_disabled_date(this.value)" id="last_contact_date" placeholder="Last Connected Date" autocomplete="off">
      </li>
      <li class="">
         <input type="text" class="form-control datepicker set_disabled_date" onchange="set_disabled_date(this.value)" name="last_update_date" id="last_update_date" placeholder="Last Updated Date" autocomplete="off">
      </li>
      <li class="">
         <div class="leads-filter-column col-md-12" style="margin-bottom:20px;">
            <div class="checkbox" style="margin-bottom: 10px;">

               <input type="checkbox" name="show_update_counts" value="1"
                  class="set_disabled_date disabled_checkbox"
                  id="show_update_counts"
                  onclick="show_update_count_range(this); set_disabled_date(this.checked ? 1 : '');">
               <label> Update Count Range
               </label>
            </div>

            <div id="rangeSlider" style="display: none;"></div>

            <input type="hidden" id="update_count_min" name="update_count_min">
            <input type="hidden" id="update_count_max" name="update_count_max">
         </div>
         <!-- <div class="form-group" style="margin-top: 10px; text-align: right;">
                                       <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>
                                       <button type="button" class="btn btn-default" onclick="window.location.reload();">Reset</button>
                                    </div> -->
      </li>

      <li class="">
         <div class="form-group" style="margin-top: 10px; text-align: right;">
            <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>
            <button type="button" class="btn btn-default" onclick="window.location.reload();">Reset</button>
         </div>
      </li>

   </ul>

</aside>

<script id="hidden-columns-table-leads" type="text/json">
   <?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>

<script>
   var max_count = parseInt("<?= !empty($updateCount_max) ? $updateCount_max : 0 ?>");

   function set_disabled_date(value) {
      $(".set_disabled_date").removeAttr("disabled");
      if (value != "") {
         $(".set_disabled_date").each(function() {
            if ($(this).hasClass("disabled_checkbox")) {
               if (!$(this).prop("checked")) {
                  $(this).attr("disabled", "disabled");
               }
            } else {
               if ($(this).val() != "") {
                  $(this).removeAttr("disabled");
               } else {
                  $(this).attr("disabled", "disabled");
               }
            }
         });
      }
   }



   $('#leads_bulk_actions').on('shown.bs.modal', function(e) {
      $("#re-assignation_div").hide();
      $("#bulk_change").removeClass("hide").show();
      $('.delete_created_date').addClass('hide');
      $('#delete_created_date').prop("checked", false);
   })


   // Global on change for mass delete to hide all other elements for bulk actions
   $('.bulk_actions').on('change', 'input[name="mass_delete"]', function() {
      var $bulkChange = $('#bulk_change');

      if ($(this).prop('checked') === true) {
         $bulkChange.find('select').selectpicker('val', '');
         $("#re-assignation_div").hide();
         $("#bulk_change").hide();
         $('#delete_created_date').prop("checked", false);

      } else {

         if ($('input[name="mass_re-assignation"]').prop('checked') === true) {
            $("#bulk_change").show();
            // $("#re-assignation_div").show();
            $("#re-assignation_div").find('select').selectpicker('val', '');


         } else {
            $("#bulk_change").show();
            $("#bulk_change").find('select').selectpicker('val', '');
            // $("#re-assignation_div").hide();
         }

      }
   });



   $('input[name="mass_re-assignation"]').click(function() {
      var $bulkChange = $('#bulk_change');
      if ($(this).prop('checked') === true) {
         $('#input[name="mass_delete"]').prop("checked", false);
         $bulkChange.find('select').selectpicker('val', '');
         $('#delete_created_date').prop("checked", false);
         $('.delete_created_date').removeClass('hide');
         // $bulkChange.hide();
         // $("#re-assignation_div").show();
      } else {
         $("#re-assignation_div").find('select').selectpicker('val', '');
         $('#delete_created_date').prop("checked", false);
         $('.delete_created_date').addClass('hide');
         // $("#re-assignation_div").hide();
         // $bulkChange.show();
      }

      // $('.mass_delete_separator').toggleClass('hide');
   });

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
      max_count = 30;
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
         max_count = 30;
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
      maxs = 30;
      max_count = 30;

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

   function periodFilter() {
      return new Promise((resolve, reject) => {
         try {
            table_leads.DataTable().page(0).draw(false).ajax.reload(null, false).on('draw.dt', function() {
               hide_loader("apply_filter");
               $("#leadSum").innerHTML = "";
               $("#leadSum").html('')
               $("#updationCounter").html('');
               $("#updationCounter_time").html('');
               $(".leads-overview").css("display", "none");
               $(".leads-count-overview").css("display", "none");
               set_datatable_string();
               status_summury_filter = 0;

               resolve(); // Resolve the promise when the draw event is triggered
            });
         } catch (error) {
            reject(error); // Reject the promise if an error occurs
         }
      });
   }

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




      $('#apply_filter').on('click', async function() {

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
         var last_contact_date = document.getElementById("last_contact_date").value;
         var last_update_date = document.getElementById("last_update_date").value;
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
         right_filter('right-menu-filter');
         show_loader("apply_filter");
         await periodFilter();

         set_datatable_string();
         // summary();
      });



      var status_summury_filter = 0;

      $("#leadSum").html('')
      $("#updationCounter").html('');
      $("#updationCounter_time").html('');





      // summary();
   });

   function set_datatable_string() {

      setTimeout(function() {
         var table_leads = $('table.table-leads').DataTable();

         // Check if there is data to update the text
         if (table_leads.page.info().recordsTotal === 0) {
            // Change the 'Showing 0 to 0' text dynamically
            $('div.dataTables_info').text('Showing 0 to 0');
         } else {

            // For cases where data exists, update the text
            $('div.dataTables_info').text('Showing ' + (table_leads.page.info().start + 1) + ' to ' + table_leads.page.info().end);

            // Once data is set, clear the interval
            // clearInterval(dataCheckInterval);
         }
      }, 500);
   }

   // Set an interval to check every 500ms until data is available
   var dataCheckInterval = setInterval(function() {
      set_datatable_string();
   }, 0);

   $(document).ready(function() {
      // Optionally, you can ensure this starts only once the page is fully loaded
      set_datatable_string();
      $(".hide-lead-filter").removeClass("hide");
   });
</script>
<script>
   var xhr = null;

   function summary(status = "") {
      show_loader();
      var element_view_assign = document.getElementById("view_assigned");
      var element_view_source = document.getElementById("view_source");
      var element_view_status = document.getElementById("view_status");
      var element_lead_type = document.getElementById("lead_type");
      var element_form_name = document.getElementById("view_form");
      var element_reference_name = document.getElementById("reference_name");

      var view_assigned_options = "";
      var view_source_options = "";
      var view_status_options = "";
      var view_lead_type_options = "";
      var view_view_form_options = "";
      var view_reference_name_options = "";
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

      if (typeof(element_form_name) != 'undefined' && element_form_name != null) {
         view_view_form_options = document.getElementById('view_form').selectedOptions;
         view_view_form_options = Array.from(view_view_form_options).map(({
            value
         }) => value);
      }
      if (typeof(element_reference_name) != 'undefined' && element_reference_name != null) {
         view_reference_name_options = document.getElementById('reference_name').selectedOptions;
         view_reference_name_options = Array.from(view_reference_name_options).map(({
            value
         }) => value);
      }

      if ($("#leadSum").html() != '' && status == 1) {
         hide_loader();
         return false;
      }



      if ($("#updationCounter").html() != '' != '' && status == 2) {
         hide_loader();
         return false;
      }

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
      var last_contact_date = document.getElementById("last_contact_date").value;
      var last_update_date = document.getElementById("last_update_date").value;

      if ($("#show_update_counts").is(":checked")) {
         update_count_min = document.getElementById("update_count_min").value;
         update_count_max = document.getElementById("update_count_max").value;
      }

      if (xhr != null) {
         xhr.abort();
      }
      xhr = $.ajax({
         type: "POST",
         url: admin_url + "leads/lead_summary_filter",
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
            last_contact_date: last_contact_date,
            last_update_date: last_update_date,
            show_lead_status: status,
            view_form: view_view_form_options,
            reference_name: view_reference_name_options
         },
         dataType: "JSON",
         cache: false,
         success: function(data) {

            //alert(data);  //as a debugging message.
            if ($("#leadSum").html() == '' && data.status != '') {
               $("#leadSum").html('');
               $("#leadSum").html(data.status);
            }
            if ($("#updationCounter").html() == '' && data.update_count != undefined) {
               $("#updationCounter").html(data.update_count);
               $("#updationCounter_time").html(data.call_count);
            }
            if (data.max_count != undefined && parseInt(data.max_count) > 0) {
               recreate_range_slider(data.max_count);
            }

            hide_loader();
         }
      }); // you have missed this bracket
      return false;
   }


   $(".mFilterBtn").click(function() {
      var element = document.getElementById("filterArea");
      // console.log(element.classList);
      element.classList.remove("hidden-xs");
      // $("#filterArea").toggle();
   });
   // });

   function show_lead_request() {

      slideToggle('.lead-transfer-table');
      setTimeout(() => {
         if ($(".lead-transfer-table").length > 0 && $(".lead-transfer-table").is(':visible')) {
            if ($.fn.DataTable.isDataTable('.table-lead-transfer-table')) {
               $('.table-lead-transfer-table').DataTable().destroy();
            }
            initDataTable('.table-lead-transfer-table', admin_url + 'leads/table_lead_transfer/0/<?= (is_admin()) ? 'admin' : 'counsellor' ?>/1', 'undefined', 'undefined', 'undefined', [0, 'desc']);
            return false;
         }
      }, 1000);


   }

   function show_lead_request_visitor() {

      slideToggle('.lead-visitor-table');
      setTimeout(() => {
         if ($(".lead-visitor-table").length > 0 && $(".lead-visitor-table").is(':visible')) {
            if ($.fn.DataTable.isDataTable('.table-lead-visitor-table')) {
               $('.table-lead-visitor-table').DataTable().destroy();
            }
            initDataTable('.table-lead-visitor-table', admin_url + 'leads/table_lead_transfer/0/<?= (is_admin()) ? 'admin' : 'counsellor' ?>/1', 'undefined', 'undefined', 'undefined', [0, 'desc']);
            return false;
         }
      }, 1000);


   }

   function change_lead_request_table() {
      if ($.fn.DataTable.isDataTable('.table-lead-transfer-table')) {
         $('.table-lead-transfer-table').DataTable().destroy();
      }
      initDataTable('.table-lead-transfer-table', admin_url + 'leads/table_lead_transfer/0/<?= (is_admin()) ? 'admin' : 'counsellor' ?>/1', 'undefined', 'undefined', 'undefined', [0, 'desc']);
      return false;
   }




   $(function() {

      function updateDateText(element, start, end) {

         let from = element.data("from");
         let to = element.data("to");

         if (start && end) {

            $("#" + from).val(start.format("YYYY-MM-DD"));
            $("#" + to).val(end.format("YYYY-MM-DD"));

            element.find("span").html(
               start.format("YYYY-MM-DD") + " - " + end.format("YYYY-MM-DD")
            );

         } else {

            $("#" + from).val('');
            $("#" + to).val('');

            let label_name = element.find("span").data('label') || "Select Date Range";
            element.find("span").html(label_name);
         }
      }


      //  function initDatePicker(selector, extraRanges = {}) {
      //    $(selector).daterangepicker({
      //       autoUpdateInput: false,
      //       locale: {
      //          cancelLabel: "Clear"
      //       },
      //       opens: "left",
      //       parentEl: "body",
      //       ranges: Object.assign({
      //          "Today": [moment(), moment()],
      //          "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
      //          "Last 7 Days": [moment().subtract(6, "days"), moment()],
      //          "Last 30 Days": [moment().subtract(29, "days"), moment()],
      //          "This Month": [moment().startOf("month"), moment().endOf("month")],
      //          "Last Month": [
      //             moment().subtract(1, "month").startOf("month"),
      //             moment().subtract(1, "month").endOf("month")
      //          ],
      //          "Clear": [null, null]
      //       }, extraRanges)
      //    }, function(start, end, label) {
      //       if (label === "Clear") {
      //          const from = this.element.data("from");
      //          const to = this.element.data("to");
      //          $("#" + from).val('');
      //          $("#" + to).val('');
      //          let label_name = this.element.find("span").data('label');
      //          this.element.find("span").html(label_name);
      //       } else {
      //          updateDateText(this.element, start, end);
      //       }
      //    });

      //    // Cancel button click handler
      //    $(selector).on('cancel.daterangepicker', function(ev, picker) {
      //       const $this = $(this); // jQuery wrapper
      //       const from = $this.data("from");
      //       const to = $this.data("to");

      //       $("#" + from).val('');
      //       $("#" + to).val('');

      //       let label_name = $this.find("span").data('label') || 'Select Date Range';
      //       $this.find("span").html(label_name);
      //    });

      //    $(selector).on("apply.daterangepicker", function(ev, picker) {
      //       const $this = $(this); // Wrap the DOM element with jQuery
      //       updateDateText($this, picker.startDate, picker.endDate);
      //    });

      // }


      function initDatePicker(selector, extraRanges = {}) {

         const $el = $(selector);

         $el.daterangepicker({

            // autoUpdateInput: false,
            // autoApply: true,
            // showDropdowns: true,
            // linkedCalendars: false,
            // alwaysShowCalendars: true,

            autoUpdateInput: false,
            autoApply: false,
            showDropdowns: true, // Year & Month dropdown
            linkedCalendars: false, // Both calendars independent
            alwaysShowCalendars: false, // Show calendars only when opened
            startDate: moment("2023-01-01"),
            endDate: moment("2023-01-01"),

            minDate: moment("2023-01-01"), // 🔥 Start from Jan 1, 2023
            maxDate: moment(),


            opens: "left",
            parentEl: "body",

            locale: {
               cancelLabel: "Clear",
               format: "YYYY-MM-DD"
            },

            ranges: Object.assign({

               "Today": [moment(), moment()],

               "Yesterday": [
                  moment().subtract(1, "days"),
                  moment().subtract(1, "days")
               ],

               "Last 7 Days": [
                  moment().subtract(6, "days"),
                  moment()
               ],

               "Last 30 Days": [
                  moment().subtract(29, "days"),
                  moment()
               ],

               "This Month": [
                  moment().startOf("month"),
                  moment().endOf("month")
               ],

               "Last Month": [
                  moment().subtract(1, "month").startOf("month"),
                  moment().subtract(1, "month").endOf("month")
               ],
               "Clear": [null, null], // 👈 add this first
            }, extraRanges)

         });



         // APPLY EVENT
         $el.on("apply.daterangepicker", function(ev, picker) {

            let start = picker.startDate;
            let end = picker.endDate;

            // 🔥 Allow reverse selection (fix main issue)
            if (end.isBefore(start)) {
               let temp = start;
               start = end;
               end = temp;
            }

            updateDateText($el, start, end);
         });

         // CANCEL EVENT
         $el.on("cancel.daterangepicker", function() {

            const from = $el.data("from");
            const to = $el.data("to");

            if (from) $("#" + from).val("");
            if (to) $("#" + to).val("");

            const defaultLabel =
               $el.find("span").data("label") || "Select Date Range";

            $el.find("span").html(defaultLabel);
         });

         $el.on('show.daterangepicker', function(ev, picker) {

            // When opening picker, show current month & year
            picker.leftCalendar.month = moment();
            picker.rightCalendar.month = moment();

            picker.updateCalendars();

         });
         $el.on("apply.daterangepicker", function(ev, picker) {

            const label = picker.chosenLabel;

            if (label === "Clear") {

               const from = $el.data("from");
               const to = $el.data("to");

               if (from) $("#" + from).val('');
               if (to) $("#" + to).val('');

               // Reset internal dates
               picker.setStartDate(moment());
               picker.setEndDate(moment());

               // Reset UI text
               $el.find("span").html("Select Date Range");

               return;
            }

            updateDateText($el, picker.startDate, picker.endDate);

         });

      }


      // Initialize all inputs
      initDatePicker("#from_date_right");
      initDatePicker("#update_date_right");
      initDatePicker("#assign_date_right");

      // With extra ranges
      initDatePicker("#follow_date_right", {
         "Tomorrow": [moment().add(1, 'days'), moment().add(1, 'days')],
         "Next 7 Days": [moment(), moment().add(6, 'days')],
         "Next 15 Days": [moment(), moment().add(14, 'days')]
      });

   });

   function right_filter(className) {
      $("." + className).toggle();
   }


   function right_lead_filter() {
      if ($('body').hasClass('hide-sidefilter')) {
         $('body').removeClass('hide-sidefilter').addClass('show-sidefilter');
         $('body').removeClass('show-sidebar').addClass('hide-sidebar');

         // Set full height to #filter-right-side
         $("#filter-right-side").css('height', $(".content").height() + 'px');
      } else {
         $('body').removeClass('show-sidefilter').addClass('hide-sidefilter');
      }

      // Toggle visibility with fade
      $("#filter-right-side ul.nav").fadeToggle("slow");

      // Fix columns going out of the table
      delay(function() {
         $($.fn.dataTable.tables(true)).DataTable().responsive.recalc();
      }, 300);
   }

   document.body.classList.add("hide-sidefilter");
</script>

</body>

</html>