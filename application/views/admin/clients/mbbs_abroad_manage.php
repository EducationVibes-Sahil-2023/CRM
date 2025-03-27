<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$tbllead_performance_column = $this->leads_model->tblma_applicant_tracker();
// Filter out columns where the value is 1
$filtered_columns = array_filter($tbllead_performance_column, function ($row) {
   return isset($row['selected']) && $row['selected'] == 1;
});

// Extract the 'id' column and limit to 5 results
$selected_performance_column = array_column($filtered_columns, "id");
$fees_data = get_clients_fees(2);
$orignal_document_list = get_orignal_document_list();
$office_location  = $this->staff_model->office_location();
$orignal_document_status  = orignal_document_status();
$university_list = get_university_list("mbbs abroad");
$country_list = get_country_list(7);
$statuses = get_applicant_statuses();
$passport_stages = get_passport_stages();

$yes_no_status = [
   ["id" => "", "name" => ""],
   ["id" => "Yes", "name" => "Yes"],
   ["id" => "No", "name" => "No"]
];

array_unshift($office_location, array());
?>
<div id="wrapper">
   <style>
      .margin-top {
         margin-top: 20px;
      }

      .table>tbody>tr>td,
      .table>tfoot>tr>td {
         text-wrap: auto !important;
      }
   </style>
   <div class="content">
      <div class="row">

         <?php if (!is_admin() && (has_permission('customers', '', 'customers_view') && has_permission('customers', '', 'customers_view_own'))) {
         ?>
            <div class="col-md-12">
               <div class="panel_s">
                  <div class="panel-body">
                     <h4 class="text-center"> You don't have access to Applicant list. Contact your admin for more info.</h4>
                  </div>
               </div>
            </div>
         <?php
            die;
         } ?>
         <div class="col-md-12">
            <div class="_filters _hidden_inputs hidden">
               <?php

               echo form_hidden('my_customers');
               echo form_hidden('requires_registration_confirmation');
               foreach ($groups as $group) {
                  echo form_hidden('customer_group_' . $group['id']);
               }
               foreach ($contract_types as $type) {
                  echo form_hidden('contract_type_' . $type['id']);
               }
               foreach ($invoice_statuses as $status) {
                  echo form_hidden('invoices_' . $status);
               }
               foreach ($estimate_statuses as $status) {
                  echo form_hidden('estimates_' . $status);
               }
               foreach ($project_statuses as $status) {
                  echo form_hidden('projects_' . $status['id']);
               }
               foreach ($proposal_statuses as $status) {
                  echo form_hidden('proposals_' . $status);
               }
               foreach ($customer_admins as $cadmin) {
                  echo form_hidden('responsible_admin_' . $cadmin['staff_id']);
               }
               foreach ($countries as $country) {
                  echo form_hidden('country_' . $country['country_id']);
               }
               ?>
            </div>
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <?php if (has_permission('customers', '', 'create')) { ?>
                        <a href="<?php echo admin_url('clients/client'); ?>" class="btn btn-info mright5 test pull-left display-block">
                           <?php echo _l('new_client'); ?></a>
                        <a href="<?php echo admin_url('clients/import'); ?>" class="btn btn-info pull-left display-block mright5 hidden-xs">
                           <?php echo _l('import_customers'); ?></a>
                     <?php } ?>
                     <!-- <a href="<?php echo admin_url('clients/all_contacts'); ?>" class="btn btn-info pull-left display-block mright5">
                        <?php echo _l('customer_contacts'); ?></a> -->
                     <!-- <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('customers_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview'); return false;"><i class="fa fa-bar-chart"></i></a> -->
                     <div class="visible-xs">
                        <div class="clearfix"></div>
                     </div>
                     <div class="btn-group pull-right btn-with-tooltip-group _filter_data d-none" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" style="width:300px;">
                           <li class="active"><a href="#" data-cview="all" onclick="dt_custom_view('','.table-clients',''); return false;"><?php echo _l('customers_sort_all'); ?></a>
                           </li>
                           <?php if (get_option('customer_requires_registration_confirmation') == '1' || total_rows(db_prefix() . 'clients', 'registration_confirmed=0') > 0) { ?>
                              <li class="divider"></li>
                              <li>
                                 <a href="#" data-cview="requires_registration_confirmation" onclick="dt_custom_view('requires_registration_confirmation','.table-clients','requires_registration_confirmation'); return false;">
                                    <?php echo _l('customer_requires_registration_confirmation'); ?>
                                 </a>
                              </li>
                           <?php } ?>
                           <li class="divider"></li>
                           <li>
                              <a href="#" data-cview="my_customers" onclick="dt_custom_view('my_customers','.table-clients','my_customers'); return false;">
                                 <?php echo _l('customers_assigned_to_me'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php if (count($groups) > 0) { ?>
                              <li class="dropdown-submenu pull-left groups">
                                 <a href="#" tabindex="-1"><?php echo _l('customer_groups'); ?></a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($groups as $group) { ?>
                                       <li><a href="#" data-cview="customer_group_<?php echo $group['id']; ?>" onclick="dt_custom_view('customer_group_<?php echo $group['id']; ?>','.table-clients','customer_group_<?php echo $group['id']; ?>'); return false;"><?php echo $group['name']; ?></a></li>
                                    <?php } ?>
                                 </ul>
                              </li>
                              <div class="clearfix"></div>
                              <li class="divider"></li>
                           <?php } ?>
                           <?php if (count($countries) > 1) { ?>
                              <li class="dropdown-submenu pull-left countries">
                                 <a href="#" tabindex="-1"><?php echo _l('clients_country'); ?></a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($countries as $country) { ?>
                                       <li><a href="#" data-cview="country_<?php echo $country['country_id']; ?>" onclick="dt_custom_view('country_<?php echo $country['country_id']; ?>','.table-clients','country_<?php echo $country['country_id']; ?>'); return false;"><?php echo $country['short_name']; ?></a></li>
                                    <?php } ?>
                                 </ul>
                              </li>
                              <div class="clearfix"></div>
                              <li class="divider"></li>
                           <?php } ?>
                           <li class="dropdown-submenu pull-left invoice">
                              <a href="#" tabindex="-1"><?php echo _l('invoices'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach ($invoice_statuses as $status) { ?>
                                    <li>
                                       <a href="#" data-cview="invoices_<?php echo $status; ?>" onclick="dt_custom_view('invoices_<?php echo $status; ?>','.table-clients','invoices_<?php echo $status; ?>'); return false;"><?php echo _l('customer_have_invoices_by', format_invoice_status($status, '', false)); ?></a>
                                    </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left estimate">
                              <a href="#" tabindex="-1"><?php echo _l('estimates'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach ($estimate_statuses as $status) { ?>
                                    <li>
                                       <a href="#" data-cview="estimates_<?php echo $status; ?>" onclick="dt_custom_view('estimates_<?php echo $status; ?>','.table-clients','estimates_<?php echo $status; ?>'); return false;">
                                          <?php echo _l('customer_have_estimates_by', format_estimate_status($status, '', false)); ?>
                                       </a>
                                    </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left project">
                              <a href="#" tabindex="-1"><?php echo _l('projects'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach ($project_statuses as $status) { ?>
                                    <li>
                                       <a href="#" data-cview="projects_<?php echo $status['id']; ?>" onclick="dt_custom_view('projects_<?php echo $status['id']; ?>','.table-clients','projects_<?php echo $status['id']; ?>'); return false;">
                                          <?php echo _l('customer_have_projects_by', $status['name']); ?>
                                       </a>
                                    </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left proposal">
                              <a href="#" tabindex="-1"><?php echo _l('proposals'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach ($proposal_statuses as $status) { ?>
                                    <li>
                                       <a href="#" data-cview="proposals_<?php echo $status; ?>" onclick="dt_custom_view('proposals_<?php echo $status; ?>','.table-clients','proposals_<?php echo $status; ?>'); return false;">
                                          <?php echo _l('customer_have_proposals_by', format_proposal_status($status, '', false)); ?>
                                       </a>
                                    </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <?php if (count($contract_types) > 0) { ?>
                              <li class="divider"></li>
                              <li class="dropdown-submenu pull-left contract_types">
                                 <a href="#" tabindex="-1"><?php echo _l('contract_types'); ?></a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($contract_types as $type) { ?>
                                       <li>
                                          <a href="#" data-cview="contract_type_<?php echo $type['id']; ?>" onclick="dt_custom_view('contract_type_<?php echo $type['id']; ?>','.table-clients','contract_type_<?php echo $type['id']; ?>'); return false;">
                                             <?php echo _l('customer_have_contracts_by_type', $type['name']); ?>
                                          </a>
                                       </li>
                                    <?php } ?>
                                 </ul>
                              </li>
                           <?php } ?>
                           <?php if (count($customer_admins) > 0 && (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit'))) { ?>
                              <div class="clearfix"></div>
                              <li class="divider"></li>
                              <li class="dropdown-submenu pull-left responsible_admin">
                                 <a href="#" tabindex="-1"><?php echo _l('responsible_admin'); ?></a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($customer_admins as $cadmin) { ?>
                                       <li>
                                          <a href="#" data-cview="responsible_admin_<?php echo $cadmin['staff_id']; ?>" onclick="dt_custom_view('responsible_admin_<?php echo $cadmin['staff_id']; ?>','.table-clients','responsible_admin_<?php echo $cadmin['staff_id']; ?>'); return false;">
                                             <?php echo get_staff_full_name($cadmin['staff_id']); ?>
                                          </a>
                                       </li>
                                    <?php } ?>
                                 </ul>
                              </li>
                           <?php } ?>
                        </ul>
                     </div>
                  </div>
                  <div class="clearfix"></div>
                  <?php if (has_permission('customers', '', 'view') || have_assigned_customers()) {
                     $where_summary = '';
                     if (has_permission('customers', '', 'view')) {
                        $where_summary = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
                     }
                  ?>
                     <div class="row hide leads-overview col-md-12">
                        <hr class="hr-panel-heading" />
                        <div class="row mbot15">
                           <div class="col-md-12">
                              <h4 class="no-margin"><?php echo _l('customers_summary'); ?></h4>
                           </div>
                           <div class="col-md-2 col-xs-6 border-right">
                              <h3 class="bold"><?php echo total_rows(db_prefix() . 'clients', ($where_summary != '' ? substr($where_summary, 5) : '')); ?></h3>
                              <span class="text-dark"><?php echo _l('customers_summary_total'); ?></span>
                           </div>
                           <div class="col-md-2 col-xs-6 border-right">
                              <h3 class="bold"><?php echo total_rows(db_prefix() . 'clients', 'active=1' . $where_summary); ?></h3>
                              <span class="text-success"><?php echo _l('active_customers'); ?></span>
                           </div>
                           <div class="col-md-2 col-xs-6 border-right">
                              <h3 class="bold"><?php echo total_rows(db_prefix() . 'clients', 'active=0' . $where_summary); ?></h3>
                              <span class="text-danger"><?php echo _l('inactive_active_customers'); ?></span>
                           </div>
                           <div class="col-md-2 col-xs-6 border-right">
                              <h3 class="bold"><?php echo total_rows(db_prefix() . 'contacts', 'active=1' . $where_summary); ?></h3>
                              <span class="text-info"><?php echo _l('customers_summary_active'); ?></span>
                           </div>
                           <div class="col-md-2  col-xs-6 border-right">
                              <h3 class="bold"><?php echo total_rows(db_prefix() . 'contacts', 'active=0' . $where_summary); ?></h3>
                              <span class="text-danger"><?php echo _l('customers_summary_inactive'); ?></span>
                           </div>
                           <div class="col-md-2 col-xs-6">
                              <h3 class="bold"><?php echo total_rows(db_prefix() . 'contacts', 'last_login LIKE "' . date('Y-m-d') . '%"' . $where_summary); ?></h3>
                              <span class="text-muted">
                                 <?php
                                 $contactsTemplate = '';
                                 if (count($contacts_logged_in_today) > 0) {
                                    foreach ($contacts_logged_in_today as $contact) {
                                       $url = admin_url('clients/client/' . $contact['userid'] . '?contactid=' . $contact['id']);
                                       $fullName = $contact['firstname'] . ' ' . $contact['lastname'];
                                       $dateLoggedIn = _dt($contact['last_login']);
                                       $html = "<a href='$url' target='_blank'>$fullName</a><br /><small>$dateLoggedIn</small><br />";
                                       $contactsTemplate .= html_escape('<p class="mbot5">' . $html . '</p>');
                                    }
                                 ?>
                                 <?php } ?>
                                 <span <?php if ($contactsTemplate != '') { ?> class="pointer text-has-action" data-toggle="popover" data-title="<?php echo _l('customers_summary_logged_in_today'); ?>" data-html="true" data-content="<?php echo $contactsTemplate; ?>" data-placement="bottom" <?php } ?>><?php echo _l('customers_summary_logged_in_today'); ?>
                                 </span>
                              </span>
                           </div>
                        </div>
                     </div>
                  <?php } ?>
                  <hr class="hr-panel-heading" />
                  <a href="#" data-toggle="modal" data-target="#customers_bulk_action" class="bulk-actions-btn table-btn hide" data-table=".table-clients"><?php echo _l('bulk_actions'); ?></a>

                  <!-- /.modal -->
                  <!-- <div class="checkbox">
                     <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                     <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clients'); ?></label>
                  </div> -->
                  <div class="tab-content">
                     <div class="" id="leads-table ">
                        <!-- <p class="bold mFilterBtn"><?php echo _l('filter_by'); ?></p> -->
                        <!-- <div class="checkbox">
                           <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                           <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clients'); ?></label>
                        </div> -->
                        <div id="filterArea" class=" hidden-xs">
                           <div class="row">
                              <div class="col-md-12">
                                 <p class="bold"><?php echo _l('filter_by'); ?></p>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column filter_reset ">
                                 <?php echo render_select('column_show[]', $tbllead_performance_column, array('id', 'label_name'), '', $selected_performance_column, array('data-width' => '100%', 'data-none-selected-text' => 'Show Column', 'multiple' => true, 'data-actions-box' => true, 'selected'), array(), 'no-mbot', '', false, 'column_show'); ?>
                              </div>

                              <?php if (has_permission('leads', '', 'view')) { ?>
                                 <div class="col-md-2  margin-top leads-filter-column">
                                    <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                 </div>
                              <?php } ?>

                              <div class="col-md-2  margin-top leads-filter-column hide ">
                                 <?php
                                 $selected = [];
                                 $selected[] = 2;
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('lead_type[]', $leadType, array('id', 'name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
                                 echo '</div>';

                                 // die;
                                 ?>
                              </div>


                              <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_source");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2 margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('status_[]', $statuses, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Status", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "status");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('university[]', $university_list, array('university_name', 'university_name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "University", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "university");
                                 echo '</div>';
                                 ?>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('country[]', $country_list, array('country_name', 'country_name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Country", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "country");
                                 echo '</div>';
                                 ?>
                              </div>
                              <div class="col-md-2 margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('doc_status[]', $orignal_document_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Document status", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "doc_status");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2 margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('minor', $yes_no_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Minor", 'data-actions-box' => true), array(), 'no-mbot', '', false, "minor");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2 margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('passport_status[]', $passport_stages, array('id', 'name'), '', '', array('data-width' => '100%', 'multiple' => true, 'data-none-selected-text' => "Passport status", 'data-actions-box' => true), array(), 'no-mbot', '', false, "passport_status");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 array_unshift($application_stage, array());
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_application_stage', $application_stage, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('applicant_name_table'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_stage");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_application_sub_stage', [], array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Application Sub Stage'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_sub_stage");
                                 echo '</div>';
                                 ?>
                              </div>


                              <!-- <div class="col-md-2  margin-top leads-filter-column">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From OnBoarding Date" autocomplete="off">
                                 </div>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To OnBoarding Date" autocomplete="off">
                                 </div>
                              </div> -->
                              <div class="col-md-4 margin-top ">
                                 <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="apply_filter_">Apply Filter</button>

                                    <!-- <button class="btn btn-primary" id="apply_filter">Apply Filter</button> -->
                                    <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="clearfix mtop20"></div>
                  <div class="col-md-12 row">
                     <div class="panel_s">
                        <div class="panel-body">
                           <table id="dynamicTable" class="table table-clients" style="width:100%">
                              <thead></thead>
                              <tbody></tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade bulk_actions" id="customers_bulk_action" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
         </div>
         <div class="modal-body">
            <?php if (has_permission('customers', '', 'delete')) { ?>
               <!-- <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="mass_delete" id="mass_delete">
                  <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
               </div> -->
               <hr class="mass_delete_separator" />
            <?php }
            array_unshift($orignal_document_status, array()); ?>
            <div class="document_status_update">
               <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="in_transit" id="in_transit" onchange="change_transit(this)">
                  <label for="in_transit">In-Transit</label>
               </div>
               <div class="is_transist_location row" style="display:none;">
                  <div class="col-md-4">
                     <label>From Location <span class='text-danger'>*</span></label>
                     <?php echo render_select('from_location', $office_location, array('name', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'From Location', 'data-actions-box' => true), array(), 'no-mbot', '', false, 'from_location'); ?>
                  </div>
                  <div class="col-md-4">
                     <label>To Location <span class='text-danger'>*</span></label>
                     <?php echo render_select('to_location', $office_location, array('name', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'To Location', 'data-actions-box' => true), array(), 'no-mbot', '', false, 'to_location'); ?>
                  </div>
               </div>
               <div class="no_is_transist_location row">
                  <div class="col-md-4">
                     <label>Location <span class='text-danger'>*</span></label>
                     <?php echo render_select('office_location', $office_location, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Location', 'data-actions-box' => true), array(), 'no-mbot', '', false, 'office_location'); ?>
                  </div>
                  <div class="col-md-4">
                     <label>Status <span class='text-danger'>*</span></label>
                     <?php echo render_select('document_status', $orignal_document_status, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Status', 'data-actions-box' => true), array(), 'no-mbot', '', false, 'document_status'); ?>
                  </div>
               </div>
            </div>
            <!-- <hr class="mass_delete_separator" /> -->
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <a href="#" class="btn btn-info" onclick="customers_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<?php
init_tail(); ?>
<script>
   var tAPI = "";
   var applicant_table = "";
   var sub_category = <?= !empty($application_sub_stage_mbbs) ? json_encode($application_sub_stage_mbbs) : [] ?>;
   var columnHeaders = [];
   var column_names = {}; // Object to store column name mappings
   var fees_array = <?= !empty($fees_data) ? json_encode($fees_data, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var orignal_document_list = <?= !empty($orignal_document_list) ? json_encode($orignal_document_list, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var selected_performance_column = <?= !empty($selected_performance_column) ? json_encode($selected_performance_column, JSON_UNESCAPED_UNICODE) : '[]' ?>;

   var tbllead_performance_column = [];


   $(document).ready(async function() {
      enabled_column();

   });


   function enabled_column() {
      $("[name='column_show[]'] option:selected").prop("disabled", false);
      $("[name='column_show[]']").selectpicker("refresh");
   }

   // Example usage


   function disabled_column() {
      $("[name='column_show[]'] option").each(function() {
         $(this).prop("disabled", selected_performance_column.includes($(this).val()));
      });
      $("[name='column_show[]']").selectpicker("refresh");
   }

   $("#column_show").on("change", function() {
      enabled_column();
      tbllead_performance_column = [];
      const columnObject = {
         tbl_column_name: " ", // Set the column name
         label_name: '<div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>' // Set the label name
      };

      // Add the constructed object to the array
      tbllead_performance_column.push(columnObject);
      // Get the selected values using `selectpicker`
      var selectedValues = $(this).selectpicker('val');

      if (selectedValues && selectedValues.length > 0) {
         // Initialize an array to hold the objects for each selected value

         // Iterate over the selected values
         selectedValues.forEach((value) => {
            // Find the corresponding label/text for the current value
            const selectedLabel = $(this).find(`option[value="${value}"]`).text();

            if (selectedLabel == "Fees") {
               fees_array.forEach((fees) => {
                  const columnObject = {
                     tbl_column_name: fees.name, // Set the column name
                     label_name: fees.name // Set the label name
                  };

                  // Add the constructed object to the array
                  tbllead_performance_column.push(columnObject);
               })
            } else if (selectedLabel == "Original Documents") {
               orignal_document_list.forEach((document) => {
                  const columnObject = {
                     tbl_column_name: document.short_name, // Set the column name
                     label_name: document.short_name // Set the label name
                  };

                  // Add the constructed object to the array
                  tbllead_performance_column.push(columnObject);
               })
            } else {
               // Construct the object for the current selection
               const columnObject = {
                  tbl_column_name: value, // Set the column name
                  label_name: selectedLabel // Set the label name
               };

               // Add the constructed object to the array
               tbllead_performance_column.push(columnObject);
            }
         });

         // Log the array of created objects
         console.log("Created Objects:", tbllead_performance_column);

         // Use `tbllead_performance_column` as needed (e.g., send via AJAX or update the UI)
      } else {
         console.log("No value selected.");
      }
      disabled_column();
   });

   $("#column_show").each(async function() {
      // Ensure the element is processed correctly
      enabled_column();
      if ($(this).is(":input")) {
         // Get the selected values using `selectpicker`
         var selectedValues = $(this).selectpicker('val');
         const columnObject = {
            tbl_column_name: " ", // Set the column name
            label_name: '<div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>' // Set the label name
         };

         // Add the constructed object to the array
         tbllead_performance_column.push(columnObject);
         if (selectedValues && selectedValues.length > 0) {
            // Initialize an array to hold the objects for each selected value

            // Iterate over the selected values
            selectedValues.forEach((value) => {
               // Find the corresponding label/text for the current value
               const selectedLabel = $(this).find(`option[value="${value}"]`).text();

               // Construct the object for the current selection
               const columnObject = {
                  tbl_column_name: value, // Set the column name
                  label_name: selectedLabel // Set the label name
               };

               // Add the constructed object to the array
               tbllead_performance_column.push(columnObject);
            });

            // Log the array of created objects
            console.log("Created Objects:", tbllead_performance_column);

            // Use `tbllead_performance_column` as needed (e.g., send via AJAX or update the UI)
         } else {
            console.log("No value selected.");
         }
      }

      disabled_column();
   });


   function set_column_table() {

      if (Array.isArray(tbllead_performance_column) && tbllead_performance_column.length > 0) {

         tbllead_performance_column.forEach(column => {
            // Get the label, fallback to column name if label is empty
            let label = column.label_name && column.label_name.trim() !== "" ?
               column.label_name :
               column.tbl_column_name.replace(".", "_");

            // Add to column_names object
            column_names[column.tbl_column_name] = label.replace(/ /g, "_");

            // Add to columnHeaders array
            columnHeaders.push({
               title: label,
               data: label.toLowerCase().replace(/ /g, "_") // Assuming lowercase data keys
            });
         });
      }

      // Populate the <thead> of the table only if columnHeaders has entries
      if (columnHeaders.length > 0) {
         let thead = "<tr>";
         columnHeaders.forEach(header => {
            thead += "<th>" + header.title + "</th>";
         });
         thead += "</tr>";
         $("#dynamicTable thead").html(thead); // Add the generated HTML to the table's <thead>
      } else {
         console.warn("No column headers available to populate the table.");
      }


   }

   set_column_table();
   // Define configuration object
   var CustomersServerParams = {};

   $(function() {
      enabled_column();
      $.each($('._hidden_inputs._filters input'), function() {
         CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      CustomersServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';
      CustomersServerParams['columnNames'] = "[name='column_show[]']";
      CustomersServerParams['assigned'] = "[name='view_assigned[]']";
      CustomersServerParams['source'] = "[name='view_source[]']";
      CustomersServerParams['lead_type'] = "[name='lead_type[]']";
      CustomersServerParams['from_date'] = "[name='from_date']";
      CustomersServerParams['to_date'] = "[name='to_date']";
      CustomersServerParams['application_stage'] = "[name='view_application_stage']";
      CustomersServerParams['application_sub_stage'] = "[name='view_application_sub_stage']";
      CustomersServerParams['vendor_type'] = "[name='vendor_type[]']";
      CustomersServerParams['university'] = "[name='university[]']";
      CustomersServerParams['country'] = "[name='country[]']";
      CustomersServerParams['status'] = "[name='status_[]']";
      CustomersServerParams['doc_status'] = "[name='doc_status[]']";
      CustomersServerParams['passport_status'] = "[name='passport_status[]']";
      CustomersServerParams['minor_status'] = "[name='minor']";



      applicant_table = initDataTable('.table-clients', admin_url + 'clients/table/2', [0], [0], CustomersServerParams, <?php echo hooks()->apply_filters('customers_table_default_order', json_encode(array(2, 'asc'))); ?>);

      disabled_column();


      $('#view_application_stage').on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
         let selectedValue = $(this).val();
         // Populate the child dropdown with the data for the selected parent
         populateChildDropdown(selectedValue);
      });

      setTimeout(() => {
         $('.btn-dt-reload').on('click', function() {
            console.log("refresh");
            enabled_column();
            refreshApplicantTable();
         });

         enabled_column();
         // When the dropdown is opened, disable selected options
         $('[name="column_show[]"]').on('show.bs.select', function() {
            console.log("show");
            disabled_column();
         });

         // When the dropdown is closed, enable previously disabled options
         $('[name="column_show[]"]').on('hidden.bs.select', function() {
            console.log("hide");
            enabled_column();
         });
      }, 3000);

   });



   function populateChildDropdown(parentValue) {
      let childDropdown = $('#view_application_sub_stage');
      childDropdown.empty();
      let childOptions = sub_category.filter(item => item.application_tracker === parentValue);;
      childDropdown.append($('<option>', {
         value: '',
         text: ''
      }));
      if (childOptions && childOptions.length > 0) {
         childOptions.forEach(option => {
            childDropdown.append($('<option>', {
               value: option.id,
               text: option.name
            }));
         });
      }


      // Refresh the Bootstrap SelectPicker to update the UI
      childDropdown.selectpicker('refresh');
   }

   $('#mass_delete').change(function() {

      $(".document_status_update").find("select").val("").trigger("change");
      $(".document_status_update").find("input[type=checkbox]").prop("checked", false);
      $(".document_status_update").toggle();

   })

   function customers_bulk_action(event) {
      var r = confirm(app.lang.confirm_action_prompt);
      if (!r) {
         return false;
      }

      var mass_delete = $('#mass_delete').prop('checked');
      var transit = $('#in_transit').prop('checked');
      var from_location = $('#from_location').val();
      var to_location = $('#to_location').val();
      var office_location = $('#office_location').val();
      var document_status = $('#document_status').val();
      var status_text = $("#document_status  option:selected").text();
      var locations_name = $("#office_location option:selected").text();

      var ids = [];
      var data = {};

      // Collect selected IDs from the table
      $('.table-clients tbody tr').each(function() {
         var checkbox = $(this).find('td').eq(0).find('input[type="checkbox"]');
         if (checkbox.prop('checked')) {
            ids.push(checkbox.val());
         }
      });

      // Validate if at least one ID is selected
      if (ids.length === 0) {
         alert("Please select at least one customer.");
         return false;
      }

      data.ids = ids;
      data.mass_delete = mass_delete;
      data.in_transit = transit;
      data.from_location = from_location;
      data.to_location = to_location;
      data.office_location = office_location;
      data.document_status = document_status;
      data.status_text = status_text;
      data.locations_name = locations_name;

      // Disable button to prevent multiple clicks
      $(event).prop('disabled', true);

      setTimeout(function() {
         $.post(admin_url + 'clients/bulk_action', data)
            .done(function(response) {
               try {
                  var res = JSON.parse(response);
                  if (res.resp_code === "ERR") {
                     alert_float("danger", res.resp_desc);
                  } else {
                     alert_float("success", res.resp_desc);
                     $("#customers_bulk_action").modal('hide');
                     applicant_reload();
                  }
               } catch (e) {
                  alert_float("danger", "Unexpected error occurred.");
               }
            })
            .fail(function() {
               alert_float("danger", "Failed to process the request.");
            })
            .always(function() {
               $(event).prop('disabled', false);
               // Reload after request completion
            });
      }, 50);
   }



   var filter_data;
   $('#apply_filter_').on('click', async function() {

      applicant_reload()

   });




   async function applicant_reload() {

      enabled_column();

      var selectedValues = $("#column_show").selectpicker('val');


      if (selectedValues.length < 3) {
         alert("Select min 3 columns");
         return false;
      }
      $('.table-clients').DataTable().destroy();
      $('.table-clients tbody').empty();

      columnHeaders = [];
      column_names = [];
      await set_column_table();
      filter_data = CustomersServerParams;
      $("#leadSum").html('');
      $(".leads-overview").hide();

      var from_date = document.getElementById("from_date").value;
      var to_date = document.getElementById("to_date").value;



      if (to_date != '') {
         if (from_date == '') {
            $("#from_date").focus();
            disabled_column();
            return false;

         }
      }

      if (from_date != '') {
         if (to_date == '') {
            $("#to_date").focus();
            disabled_column();
            return false;
         }
      }

      console.log(CustomersServerParams);
      show_loader("apply_filter");
      applicant_table = initDataTable('.table-clients', admin_url + 'clients/table/2', [0], [0], CustomersServerParams, <?php echo hooks()->apply_filters('customers_table_default_order', json_encode(array(2, 'asc'))); ?>);
      disabled_column();

      hide_loader("apply_filter");
   }

   function change_transit(obj) {
      console.log("change");
      let transitInfo = document.getElementById("transit_info");
      $(".is_transist_location").toggle().find("select").val("").selectpicker("refresh");
      $(".no_is_transist_location").toggle().find("select").val("").selectpicker("refresh");

   }

   $('#customers_bulk_action').on('show.bs.modal', function() {
      $(".document_status_update").find("select").val("").selectpicker('refresh');
      $(".document_status_update").find("input[type=checkbox]").prop("checked", false);

      $(".is_transist_location").hide();
      $(".no_is_transist_location").show();

   });

   function refreshApplicantTable() {
      applicant_table.ajax.reload(function() {
         disabled_column();
      }, false);
   }
</script>
</body>

</html>