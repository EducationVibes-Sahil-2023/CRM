<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$tbllead_performance_column = $this->leads_model->tblma_applicant_tracker();
$selected_performance_column = array_slice(array_column($tbllead_performance_column, "id"), 0, 5);
$fees_data = get_clients_fees(2);

?>
<div id="wrapper">
   <style>
      .margin-top {
         margin-top: 20px;
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
                     <a href="<?php echo admin_url('clients/all_contacts'); ?>" class="btn btn-info pull-left display-block mright5">
                        <?php echo _l('customer_contacts'); ?></a>
                     <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('customers_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview'); return false;"><i class="fa fa-bar-chart"></i></a>
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
                  <div class="modal fade bulk_actions" id="customers_bulk_action" tabindex="-1" role="dialog">
                     <div class="modal-dialog" role="document">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                           </div>
                           <div class="modal-body">
                              <?php if (has_permission('customers', '', 'delete')) { ?>
                                 <div class="checkbox checkbox-danger">
                                    <input type="checkbox" name="mass_delete" id="mass_delete">
                                    <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                 </div>
                                 <hr class="mass_delete_separator" />
                              <?php } ?>
                              <div id="bulk_change">
                                 <?php echo render_select('move_to_groups_customers_bulk[]', $groups, array('id', 'name'), 'customer_groups', '', array('multiple' => true), array(), '', '', false); ?>
                                 <p class="text-danger"><?php echo _l('bulk_action_customers_groups_warning'); ?></p>
                              </div>
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
                  <!-- /.modal -->
                  <!-- <div class="checkbox">
                     <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                     <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clients'); ?></label>
                  </div> -->
                  <div class="tab-content">
                     <div class="" id="leads-table ">
                        <p class="bold mFilterBtn"><?php echo _l('filter_by'); ?></p>
                        <div class="checkbox">
                           <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                           <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clients'); ?></label>
                        </div>
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
                                 echo render_select('view_application_sub_stage', [], array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Application Sub Category'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_sub_stage");
                                 echo '</div>';
                                 ?>
                              </div>


                              <div class="col-md-2  margin-top leads-filter-column">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From OnBoarding Date" autocomplete="off">
                                 </div>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To OnBoarding Date" autocomplete="off">
                                 </div>
                              </div>
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
<?php
init_tail(); ?>
<script>
   var tAPI = "";
   var sub_category = <?= !empty($application_sub_stage) ? json_encode($application_sub_stage) : [] ?>;
   var columnHeaders = [];
   var column_names = {}; // Object to store column name mappings
   var fees_array = <?= !empty($fees_data) ? json_encode($fees_data, JSON_UNESCAPED_UNICODE) : '[]' ?>;

   var tbllead_performance_column = [];
   $("#column_show").on("change", function() {
      tbllead_performance_column = [];
      // Get the selected values using `selectpicker`
      var selectedValues = $(this).selectpicker('val');

      if (selectedValues && selectedValues.length > 0) {
         // Initialize an array to hold the objects for each selected value

         // Iterate over the selected values
         selectedValues.forEach((value) => {
            // Find the corresponding label/text for the current value
            const selectedLabel = $(this).find(`option[value="${value}"]`).text();
            console.log(selectedLabel);
            if (selectedLabel == "Fees") {
               fees_array.forEach((fees) => {
                  const columnObject = {
                     tbl_column_name: fees.name, // Set the column name
                     label_name: fees.name // Set the label name
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
   });

   $("#column_show").each(function() {
      // Ensure the element is processed correctly
      if ($(this).is(":input")) {
         // Get the selected values using `selectpicker`
         var selectedValues = $(this).selectpicker('val');

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


      tAPI = initDataTable('.table-clients', admin_url + 'clients/table/2', [0], [0], CustomersServerParams, <?php echo hooks()->apply_filters('customers_table_default_order', json_encode(array(2, 'asc'))); ?>);
      $('input[name="exclude_inactive"]').on('change', function() {
         tAPI.ajax.reload();
      });



      $('#view_application_stage').on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
         let selectedValue = $(this).val();
         // Populate the child dropdown with the data for the selected parent
         populateChildDropdown(selectedValue);
      });

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
               value: option.name,
               text: option.name
            }));
         });
      }


      // Refresh the Bootstrap SelectPicker to update the UI
      childDropdown.selectpicker('refresh');
   }

   function customers_bulk_action(event) {
      var r = confirm(app.lang.confirm_action_prompt);
      if (r == false) {
         return false;
      } else {
         var mass_delete = $('#mass_delete').prop('checked');
         var ids = [];
         var data = {};
         if (mass_delete == false || typeof(mass_delete) == 'undefined') {
            data.groups = $('select[name="move_to_groups_customers_bulk[]"]').selectpicker('val');
            if (data.groups.length == 0) {
               data.groups = 'remove_all';
            }
         } else {
            data.mass_delete = true;
         }
         var rows = $('.table-clients').find('tbody tr');
         $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') == true) {
               ids.push(checkbox.val());
            }
         });
         data.ids = ids;
         $(event).addClass('disabled');
         setTimeout(function() {
            $.post(admin_url + 'clients/bulk_action', data).done(function() {
               window.location.reload();
            });
         }, 50);
      }
   }

   // $('#apply_filter').on('click', function() {

   //    var from_date = document.getElementById("from_date").value;
   //    var to_date = document.getElementById("to_date").value;


   //    if (to_date != '') {
   //       if (from_date == '') {
   //          $("#from_date").focus();
   //          return false;
   //       }
   //    }

   //    if (from_date != '') {
   //       if (to_date == '') {
   //          $("#to_date").focus();
   //          return false;
   //       }
   //    }


   //    show_loader("apply_filter");
   //    periodFilter();
   //    // summary();
   // });


   var filter_data;
   $('#apply_filter_').on('click', async function() {

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
            return false;
         }
      }

      if (from_date != '') {
         if (to_date == '') {
            $("#to_date").focus();
            return false;
         }
      }


      show_loader("apply_filter");
      tAPI = initDataTable('.table-clients', admin_url + 'clients/table/2', [0], [0], CustomersServerParams, <?php echo hooks()->apply_filters('customers_table_default_order', json_encode(array(2, 'asc'))); ?>);
      $('input[name="exclude_inactive"]').on('change', function() {
         tAPI.ajax.reload();
      });
      hide_loader("apply_filter");

   });
</script>
</body>

</html>