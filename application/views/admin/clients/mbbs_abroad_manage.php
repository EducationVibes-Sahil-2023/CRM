<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$tbllead_performance_column = $this->leads_model->tblma_applicant_tracker();
// Filter out columns where the value is 1
$filtered_columns = array_filter($tbllead_performance_column, function ($row) {
   return isset($row['selected']) && $row['selected'] == 1;
});

// Extract the 'id' column and limit to 5 results
$selected_performance_column = [];
$fees_data = get_clients_fees(2);
$orignal_document_list = get_orignal_document_list();
$orignal_document_list_rest = get_orignal_document_list(1);
$orignal_document_list_georgia = get_orignal_document_list(0, 1);
$orignal_document_visa_rest = get_orignal_document_list(0, 0, 0, "", 1);
$orignal_document_visa_georgia = get_orignal_document_list(0, 0, 0, "", 0, 1);
$apostille_documents = get_orignal_document_list(0, 0, 1);
$office_location  = $this->staff_model->office_location();
$orignal_document_status  = orignal_document_status();
$university_list = get_university_list("mbbs abroad");
$country_list = get_country_list(7);
$statuses = get_applicant_statuses();
$passport_stages = get_passport_stages();
$table_view = array_column(get_view_columns(), null, "id");

$apostille_vendors = get_vendor_list(1);

$visa_vendors = get_vendor_list(2);
$fly_vendors = get_vendor_list(3);
$courier_type = get_courier_list();
$payment_mode = get_payment_mode();
$fly_batch = fly_batch();
$fly_departure = fly_departure();

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

      table.dataTable thead .sorting:after {
         display: none;
      }

      div#customers_bulk_action .form-group {
         margin-bottom: 20px !important;
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
                  <?php if (is_admin() || is_postSale()) { ?>
                     <a href="#" data-toggle="modal" data-target="#customers_bulk_action" class="bulk-actions-btn table-btn hide" data-table=".table-clients"><?php echo _l('bulk_actions'); ?></a>
                  <?php } ?>
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
                                 <?php echo render_select('table_view[]', $table_view, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Table View'), array(), 'no-mbot', '', false, 'table_view'); ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column filter_reset ">
                                 <?php echo render_select('column_show[]', [], array('id', 'label_name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Show Column', 'multiple' => true, 'data-actions-box' => true, 'selected'), array(), 'no-mbot', '', false, 'column_show'); ?>
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
                              <?php if (has_permission('leads', '', 'view')) { ?>
                                 <div class="col-md-2  margin-top leads-filter-column">
                                    <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Counsellor", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
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


                              <div class="col-md-2 margin-top leads-filter-column ">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('minor', $yes_no_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Minor", 'data-actions-box' => true), array(), 'no-mbot', '', false, "minor");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2 margin-top leads-filter-column ">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('passport_status[]', $passport_stages, array('id', 'name'), '', '', array('data-width' => '100%', 'multiple' => true, 'data-none-selected-text' => "Passport status", 'data-actions-box' => true), array(), 'no-mbot', '', false, "passport_status");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2 margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('status_[]', $statuses, array('id', 'name'), '', array(1), array('data-width' => '100%', 'data-none-selected-text' => "Applicant Status", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "status");
                                 echo '</div>';
                                 ?>
                              </div>


                              <div class="col-lg-2 margin-top leads-filter-column">
                                 <input type="month" class="form-control" required-check id="session_intake" name="session_intake"
                                    value=""
                                    placeholder="Select Month and Year Session Intake">
                              </div>


                              <div class="col-md-2  margin-top leads-filter-column hide">
                                 <?php
                                 array_unshift($application_stage, array());
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_application_stage', $application_stage, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('applicant_name_table'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_stage");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column hide">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_application_sub_stage', [], array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Application Sub Stage'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_sub_stage");
                                 echo '</div>';
                                 ?>
                              </div>


                              <div class="col-md-2  margin-top leads-filter-column filter-hide-default filter-ap-vendor hide">
                                 <?php

                                 echo '<div id="leads-filter-source">';
                                 echo render_select('apostille_vendors_filter[]', $apostille_vendors, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Apostille Vendors", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "apostille_vendors_filter");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column filter-hide-default filter-ap-status hide">
                                 <?php
                                 $apostille_status = [array("id" => "Pending", "name" => "Pending"), array("id" => "Sent", "name" => "Sent"), array("id" => "Received", "name" => "Received")];
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('apostille_status[]', $apostille_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Apostille Status", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "apostille_status");
                                 echo '</div>';
                                 ?>
                              </div>



                              <div class="col-md-2 margin-top leads-filter-column filter-hide-default filter-org-status hide">
                                 <?php
                                 $orignal_document_status[] = array("id" => "-1", "name" => "In-Transit");
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('doc_status[]', $orignal_document_status, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => "Org. Doc. status", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "doc_status");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column filter-hide-default filter-visa-vendor hide">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('visa_vendors_filter[]', $visa_vendors, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Visa Vendors", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "visa_vendors_filter");
                                 echo '</div>';
                                 ?>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column  filter-hide-default filter-visa-payment hide">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="visa_payment_date" id="visa_payment_date" placeholder="Visa Payment Update Date" autocomplete="off">
                                 </div>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column filter-hide-default filter-fly-batch hide">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('fly_batch_filter[]', $fly_batch, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Fly Batch", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "fly_batch_filter");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column filter-hide-default filter-fly-departure hide">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('fly_departure_filter[]', $fly_departure, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Fly Departure", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "fly_departure_filter");
                                 echo '</div>';
                                 ?>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column filter-hide-default filter-fly-vendor hide">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('fly_vendors_filter[]', $fly_vendors, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Fly Vendors", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "fly_vendors_filter");
                                 echo '</div>';
                                 ?>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column  filter-hide-default filter-fly-date hide">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="fly_date" id="fly_date" placeholder="Fly Date" autocomplete="off">
                                 </div>
                              </div>

                              <div class="col-md-2  margin-top leads-filter-column hide">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="last_from_date" id="last_from_date" placeholder="From Last Update Date" autocomplete="off">
                                 </div>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column hide">
                                 <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="last_to_date" id="last_to_date" placeholder="To Last Update Date" autocomplete="off">
                                 </div>
                              </div>

                              <div class="col-md-2 margin-top ">
                                 <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="apply_filter_">Apply Filter</button>
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
                           <table id="dynamicTable" class="table table-clients sticky-header" style="width:100%">
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
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
         </div>
         <div class="modal-body h-auto">
            <!-- Apostille Section -->
            <div class="apostille_update">
               <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="apostille_status_check" id="apostille_status_check" onchange="Update_apostille(this)">
                  <label for="apostille_status">Apostille</label>
               </div>

               <div class="apostille_status_update" style="display:none;">
                  <div class="row">
                     <div class="col-md-4">
                        <label>Apostille Vendor <small class='text-danger'>*</small></label>
                        <?php
                        array_unshift($apostille_vendors, array());
                        echo render_select('apostille_vendor', $apostille_vendors, ['id', 'name'], '', [], [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Vendor',
                           'data-actions-box' => true,
                           'required-check' => 'required-check',
                           'required' => 'required',
                        ], [], 'no-mbot', '', false, 'apostille_vendor'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Apostille Documents <small class='text-danger'>*</small></label>
                        <?php echo render_select('apostille_document[]', $apostille_documents, ['id', 'name'], '', [], [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Documents',
                           'multiple' => true,
                           'data-actions-box' => true,
                           'required-check' => 'required-check',
                           'required' => 'required',
                           'onchange' => 'document_cost_div(this)'

                        ], [], 'no-mbot', '', false, 'apostille_document'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Courier Date</label>
                        <?php echo render_input('apostille_date', '', '', 'date'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Apostille Received</label>
                        <?php echo render_input('apostille_receiving_date', '', '', 'date'); ?>
                     </div>

                     <div class="col-md-4">
                        <label>Payment Date</label>
                        <?php echo render_input('apostille_payment_date', '', '', 'date'); ?>
                     </div>
                     <div class="clearfix"></div>
                     <div class="doc-cost-section">


                     </div>
                  </div>
               </div>
            </div>
            <div class="visa_update">
               <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="visa_status_check" id="visa_status_check" onchange="Update_visa(this)">
                  <label for="visa_status">Visa</label>
               </div>

               <div class="visa_status_update" style="display:none;">
                  <div class="row">
                     <div class="col-md-4">
                        <label>Visa Vendor <small class='text-danger'>*</small></label>
                        <?php
                        array_unshift($visa_vendors, array());
                        echo render_select('visa_vendor', $visa_vendors, ['id', 'name'], '', [], [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Vendor',
                           'data-actions-box' => true,
                           'required-check' => 'required-check',
                           'required' => 'required',
                        ], [], 'no-mbot', '', false, 'visa_vendor'); ?>
                     </div>

                     <div class="col-md-4">
                        <label>Courier Date</label>
                        <?php echo render_input('visa_date', '', '', 'date'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Courier Type <small class='text-danger'>*</small></label>
                        <?php
                        array_unshift($courier_type, array());
                        echo render_select('visa_courier_type', $courier_type, ['id', 'name'], '', [$visa["courier_type"]], [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Courier Type',
                           'data-actions-box' => true,
                        ], [], 'no-mbot', '', false, 'visa_courier_type'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Payment Date</label>
                        <?php echo render_input('visa_payment_date', '', '', 'date'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Visa Cost</label>
                        <?php echo render_input('visa_cost', '', '', 'number'); ?>
                     </div>
                     <div class="col-md-4">
                        <label>Payment Mode</label>
                        <?php
                        array_unshift($payment_mode, array());
                        echo render_select('visa_payment_mode', $payment_mode, ['id', 'name'], '', [$visa["payment_mode"]], [
                           'data-width' => '100%',
                           'data-none-selected-text' => 'Payment Mode',
                           'data-actions-box' => true,
                        ], [], 'no-mbot', '', false, 'visa_payment_mode'); ?>
                     </div>

                     <div class="clearfix"></div>
                     <div class="doc-cost-section">


                     </div>
                  </div>
               </div>
            </div>
            <!-- Document Status Section -->
            <div class="document_status_update">
               <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="in_transit" id="in_transit" onchange="change_transit(this)">
                  <label for="in_transit">In-Transit</label>
               </div>

               <!-- In Transit Locations -->
               <div class="is_transist_location row" style="display:none;">
                  <div class="col-md-4">
                     <label>From Location <span class="text-danger">*</span></label>
                     <?php echo render_select('from_location', $office_location, ['name', 'name'], '', [], [
                        'data-width' => '100%',
                        'data-none-selected-text' => 'From Location',
                        'data-actions-box' => true
                     ], [], 'no-mbot', '', false, 'from_location'); ?>
                  </div>
                  <div class="col-md-4">
                     <label>To Location <span class="text-danger">*</span></label>
                     <?php echo render_select('to_location', $office_location, ['name', 'name'], '', [], [
                        'data-width' => '100%',
                        'data-none-selected-text' => 'To Location',
                        'data-actions-box' => true
                     ], [], 'no-mbot', '', false, 'to_location'); ?>
                  </div>
               </div>

               <!-- Default Location and Status -->
               <div class="no_is_transist_location row">
                  <div class="col-md-4">
                     <label>Location <span class="text-danger">*</span></label>
                     <?php echo render_select('office_location', $office_location, ['id', 'name'], '', [], [
                        'data-width' => '100%',
                        'data-none-selected-text' => 'Location',
                        'data-actions-box' => true
                     ], [], 'no-mbot', '', false, 'office_location'); ?>
                  </div>

               </div>
            </div>

         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <a href="#" class="btn btn-info" onclick="customers_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
         </div>
      </div>
   </div>
</div>


<div class="modal fade applicant_status_change" id="applicant_status_change" tabindex="-1" role="dialog" aria-labelledby="applicantStatusModal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="applicantStatusModal">Applicant Status</h4>
         </div>

         <div class="modal-body">
            <form id="applicant_status_change_form" onsubmit="return false;">
               <input type="hidden" name="userid" value="">
               <input type="hidden" name="status" value="">

               <!-- Canceled Comment Section -->
               <div class="canceled_div applicant_status_modal_div">
                  <div class="form-group">
                     <?= render_textarea('canceled_comment', 'Cancellation Comment', '', ["required-check" => "required-check", "placeholder" => "Enter comment"]) ?>
                  </div>
               </div>



               <!-- Refund Section -->
               <div class="refund_div applicant_status_modal_div">
                  <div class="form-group">
                     <?= render_input('refund_payment_proof', 'Payment Proof', '', 'file', ["required-check" => "required-check"]) ?>
                  </div>
                  <div class="form-group">
                     <?= render_input('refund_payment_date', 'Payment Date', '', 'date', ["required-check" => "required-check"]) ?>
                  </div>
               </div>
            </form>
         </div>

         <!-- Modal Footer -->
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="button" class="btn btn-info" onclick="applicant_status_change()"><?php echo _l('confirm'); ?></button>
         </div>
      </div>
   </div>
</div>

<?php
init_tail();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>


<script>
   var tAPI = "";
   var applicant_table = "";
   var sub_category = <?= !empty($application_sub_stage_mbbs) ? json_encode($application_sub_stage_mbbs) : '[]' ?>;
   var columnHeaders = [];
   var column_names = {};
   var fees_array = <?= !empty($fees_data) ? json_encode($fees_data, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var orignal_document_list = <?= !empty($orignal_document_list) ? json_encode($orignal_document_list, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var orignal_document_list_rest = <?= !empty($orignal_document_list_rest) ? json_encode($orignal_document_list_rest, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var orignal_document_list_georgia = <?= !empty($orignal_document_list_georgia) ? json_encode($orignal_document_list_georgia, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var orignal_document_visa_rest = <?= !empty($orignal_document_visa_rest) ? json_encode($orignal_document_visa_rest, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var orignal_document_visa_georgia = <?= !empty($orignal_document_visa_georgia) ? json_encode($orignal_document_visa_georgia, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var apostille_documents = <?= !empty($apostille_documents) ? json_encode(array_values($apostille_documents), JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var apostille_documents_list = <?= !empty($apostille_documents) ? json_encode(array_column($apostille_documents, null, 'id'), JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var selected_performance_column = <?= !empty($selected_performance_column) ? json_encode($selected_performance_column, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var tbllead_performance_column = [];
   var tbllead_performance_column_array = <?= !empty($table_view) ? json_encode($table_view, JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var selected_view = $("#table_view option:selected").val() || 0;
   var show_column_array = [];
   var selected_column_array = [];
   var default_columns = <?= !empty($tbllead_performance_column) ? json_encode(array_column($tbllead_performance_column, null, "id"), JSON_UNESCAPED_UNICODE) : '[]' ?>;
   var group_selection = [];

   $(document).ready(function() {
      if (tbllead_performance_column_array[selected_view]) {
         show_column_array = (tbllead_performance_column_array[selected_view].column_ids || "").split(",");
         selected_column_array = (tbllead_performance_column_array[selected_view].selected_ids || "").split(",");
         selected_performance_column = selected_column_array;
      } else {
         //console.warn("No table view data found for selected view:", selected_view);
      }
      column_name_update();
      $("#column_show").each(function() {
         if ($(this).is(":input")) {
            updateColumns($(this));
         }
      });
      setTimeout(() => {
         set_column_table();
         set_table();
      }, 500);
   });

   // Update column name dropdown
   function column_name_update() {
      let columnSelect = $("[name='column_show[]']");

      if (columnSelect.length === 0) {
         //console.warn("Column select element not found.");
         return;
      }

      columnSelect.empty();
      //console.log(selected_column_array);
      show_column_array.forEach(id => {
         let value = default_columns[id];
         // if (show_column_array.includes(value.id)) {
         let isDisabled = selected_column_array.includes(String(value.id));
         //console.log(isDisabled);
         let option = `<option data-columns="${value.columns}" value="${value.id}" ${isDisabled ? 'disabled Selected' : ''} >${value.label_name}</option>`;
         columnSelect.append(option);
         // }
      });

      columnSelect.selectpicker("refresh");

   }



   // Event Listener for Table View Change
   $("#table_view").change(function() {
      $(".filter-hide-default").find("select").val('').selectpicker("refresh");
      $(".filter-hide-default").addClass('hide');
      let select_view = $("#table_view option:selected").val() || 0;
      if (tbllead_performance_column_array[select_view]) {
         selected_performance_column = [];
         show_column_array = (tbllead_performance_column_array[select_view].column_ids || "").split(",");
         selected_column_array = (tbllead_performance_column_array[select_view].selected_ids || "").split(",");
         selected_performance_column = selected_column_array;

         let show_filters = tbllead_performance_column_array[select_view]?.filter_show;

         // Check if show_filters is a non-empty string
         if (typeof show_filters === 'string' && show_filters.trim() !== '') {
            let filters = show_filters.split(',').map(f => f.trim()).filter(f => f !== '');

            if (filters.length > 0) {
               filters.forEach(function(filter) {
                  $("." + filter).removeClass("hide");
               });
            }
         }


      }
      column_name_update();
      $("#column_show").each(function() {
         if ($(this).is(":input")) {
            updateColumns($(this));
         }
      });
      setTimeout(() => {
         enabled_column();
         set_column_table();
         set_table();
      }, 500);
   });

   function enabled_column() {
      let columnSelect = $("[name='column_show[]']");
      if (columnSelect.length === 0) return;
      columnSelect.find("option").prop("disabled", false);
      columnSelect.selectpicker("refresh");
   }

   function disabled_column() {
      let columnSelect = $("[name='column_show[]']");
      if (columnSelect.length === 0) return;
      columnSelect.find("option").each(function() {
         $(this).prop("disabled", selected_performance_column.includes($(this).val()));
      });

      columnSelect.selectpicker("refresh");
   }

   // Listen for column selection change
   // $("#column_show").on("change", function() {
   //    updateColumns($(this));
   // });

   function updateColumns(element) {
      enabled_column();
      tbllead_performance_column = [];
      group_selection = [];
      <?php if (is_postSale() || is_admin()) { ?>
         tbllead_performance_column.push({
            tbl_column_name: " ",
            label_name: '<div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>'
         });
      <?php } ?>

      //       let selectedValues = element.selectpicker('val') || [];
      //       let selectedOptions = element.find('option:selected');
      // console.log(selectedOptions);
      //       let addedColumns = new Set();
      //       selectedValues.forEach(value => {
      //          // if (!show_column_array.includes(String(value))) return;

      //          let selectedLabel = element.find(`option[value="${value}"]`).text();

      //          if (selectedLabel.toLowerCase() === "fees") {
      //             fees_array.forEach(fees => addColumn(fees.name, fees.name));
      //          } else if (selectedLabel === "Original Documents") {
      //             orignal_document_list.forEach(document => {
      //                if (!addedColumns.has(document.short_name)) {
      //                   addColumn(document.short_name, document.short_name);
      //                   addedColumns.add(document.short_name);
      //                }
      //             });
      //          } else if (selectedLabel.toLowerCase().includes("<?= ORG_REST ?>".toLowerCase())) {
      //             orignal_document_list_rest.forEach(document => {
      //                if (!addedColumns.has(document.short_name)) {
      //                   addColumn(document.short_name, document.short_name);
      //                   addedColumns.add(document.short_name);
      //                }
      //             });
      //          } else if (selectedLabel.toLowerCase().includes("<?= ORG_GEORGIA ?>".toLowerCase())) {
      //             orignal_document_list_georgia.forEach(document => {
      //                if (!addedColumns.has(document.short_name)) {
      //                   addColumn(document.short_name, document.short_name);
      //                   addedColumns.add(document.short_name);
      //                }
      //             });
      //          } else if (selectedLabel.toLowerCase().includes("<?= APOSTILE_DOC ?>".toLowerCase())) {
      //             apostille_documents.forEach(document => {
      //                if (!addedColumns.has(document.short_name)) {
      //                   addColumn(document.short_name, document.short_name);
      //                   addedColumns.add(document.short_name);
      //                }
      //             });
      //          } else if (selectedLabel.toLowerCase().includes("<?= VISA_GEORGIA ?>".toLowerCase())) {
      //             orignal_document_visa_georgia.forEach(document => {
      //                if (!addedColumns.has(document.short_name)) {
      //                   addColumn(document.short_name, document.short_name);
      //                   addedColumns.add(document.short_name);
      //                }
      //             });
      //          } else if (selectedLabel.toLowerCase().includes("<?= VISA_REST ?>".toLowerCase())) {
      //             orignal_document_visa_rest.forEach(document => {
      //                if (!addedColumns.has(document.short_name)) {
      //                   addColumn(document.short_name, document.short_name);
      //                   addedColumns.add(document.short_name);
      //                }
      //             });
      //          } else {
      //             console.log();
      //             addColumn(value, selectedLabel);
      //          }
      //       });
      //       // disabled_column();


      let selectedOptions = element.find('option:selected'); // Get selected option elements
      let addedColumns = new Set();

      selectedOptions.each(function() {
         let value = $(this).val();
         let text = $(this).text().trim().toLowerCase();
         let additional_columns = $(this).attr("data-columns") || "";

         if (text === "fees") {
            fees_array.forEach(fees => addColumn(fees.name, fees.name));
         } else if (text === "original documents") {
            orignal_document_list.forEach(document => {
               if (!addedColumns.has(document.short_name)) {
                  addColumn(document.short_name, document.short_name);
                  addedColumns.add(document.short_name);
               }
            });
         } else if (text.includes("<?= strtolower(ORG_REST) ?>")) {
            orignal_document_list_rest.forEach(document => {
               if (!addedColumns.has(document.short_name)) {
                  addColumn(document.short_name, document.short_name);
                  addedColumns.add(document.short_name);
               }
            });
         } else if (text.includes("<?= strtolower(ORG_GEORGIA) ?>")) {
            orignal_document_list_georgia.forEach(document => {
               if (!addedColumns.has(document.short_name)) {
                  addColumn(document.short_name, document.short_name);
                  addedColumns.add(document.short_name);
               }
            });
         } else if (text.includes("<?= strtolower(APOSTILE_DOC) ?>")) {
            apostille_documents.forEach(document => {
               if (!addedColumns.has(document.short_name)) {
                  addColumn(document.short_name, document.short_name);
                  addedColumns.add(document.short_name);
               }
            });
         } else if (text.includes("<?= strtolower(VISA_GEORGIA) ?>")) {
            orignal_document_visa_georgia.forEach(document => {
               if (!addedColumns.has(document.short_name)) {
                  addColumn(document.short_name, document.short_name);
                  addedColumns.add(document.short_name);
               }
            });
         } else if (text.includes("<?= strtolower(VISA_REST) ?>")) {
            orignal_document_visa_rest.forEach(document => {
               if (!addedColumns.has(document.short_name)) {
                  addColumn(document.short_name, document.short_name);
                  addedColumns.add(document.short_name);
               }
            });
         } else {
            if (additional_columns !== "") {
               let columns = additional_columns.split(",");
               columns.forEach(function(key) {
                  let columnValue = key.trim();
                  if (columnValue && default_columns[columnValue]) {
                     let label = default_columns[columnValue].label_name;
                     addColumn(columnValue, label);
                     group_selection.push(columnValue);

                  }
               });
            } else {
               addColumn(value, $(this).text().trim());
            }
         }
      });

      // Finally call your function to disable columns
      disabled_column();



   }

   function addColumn(tbl_column_name, label_name) {
      tbllead_performance_column.push({
         tbl_column_name,
         label_name
      });
   }

   function set_column_table() {
      columnHeaders = [];

      if (!Array.isArray(tbllead_performance_column) || tbllead_performance_column.length === 0) {
         //console.warn("No column data available to set the table.");
         return;
      }

      tbllead_performance_column.forEach(column => {
         let label = column.label_name && column.label_name.trim() !== "" ? column.label_name : column.tbl_column_name.replace(".", "_");
         column_names[column.tbl_column_name] = label.replace(/ /g, "_");

         columnHeaders.push({
            title: label,
            data: label.toLowerCase().replace(/ /g, "_")
         });
      });

      if (columnHeaders.length > 0) {
         let thead = "<tr>";
         columnHeaders.forEach(header => {
            thead += "<th>" + header.title + "</th>";
         });
         thead += "</tr>";
         $("#dynamicTable thead").html(thead);
      } else {
         //console.warn("No column headers available to populate the table.");
      }
   }
   // Define configuration object
   var CustomersServerParams = {};

   function set_table() {

      enabled_column();
      // Destroy existing DataTable instance
      if ($.fn.DataTable.isDataTable('.table-clients')) {
         $('.table-clients').DataTable().clear().destroy();
      }

      $('.table-clients tbody').empty();

      // Populate CustomersServerParams dynamically
      $('._hidden_inputs._filters input').each(function() {
         CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });

      Object.assign(CustomersServerParams, {
         'exclude_inactive': '[name="exclude_inactive"]:checked',
         'columnNames': "[name='column_show[]']",
         'assigned': "[name='view_assigned[]']",
         'source': "[name='view_source[]']",
         'apostille_status': "[name='apostille_status[]']",
         'lead_type': "[name='lead_type[]']",
         'last_from_date': "[name='last_from_date']",
         'last_to_date': "[name='last_to_date']",
         'application_stage': "[name='view_application_stage']",
         'application_sub_stage': "[name='view_application_sub_stage']",
         'vendor_type': "[name='vendor_type[]']",
         'university': "[name='university[]']",
         'country': "[name='country[]']",
         'status_': "[name='status_[]']",
         'doc_status': "[name='doc_status[]']",
         'passport_status': "[name='passport_status[]']",
         'minor_status': "[name='minor']",
         'session_intake': "[name='session_intake']",
         'apostille_vendors_filter': "[name='apostille_vendors_filter[]']",
         'visa_vendors_filter': "[name='visa_vendors_filter[]']",
         'visa_payment_date': "[name='visa_payment_date']",
         'fly_batch_filter': "[name='fly_batch_filter[]']",
         'fly_departure_filter': "[name='fly_departure_filter[]']",
         'fly_vendors_filter': "[name='fly_vendors_filter[]']",
         'fly_date': "[name='fly_date']"
      });

      applicant_table = initDataTable(
         '.table-clients',
         admin_url + 'clients/table/2',
         [0],
         [0],
         CustomersServerParams, {
            fixedHeader: true, // Enables fixed header
            scrollY: "400px", // Enables vertical scrolling
            scrollCollapse: true
         }
      );



      applicant_table = initDataTable('.table-clients', admin_url + 'clients/table/2', [0], [0], CustomersServerParams, [0, "DESC"]);

      // disabled_column();


      $('#view_application_stage').on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
         let selectedValue = $(this).val();
         populateChildDropdown(selectedValue);
      });

      setTimeout(() => {
         $('.btn-dt-reload').on('click', function() {
            enabled_column();
            refreshApplicantTable();
         });

         $('[name="column_show[]"]').on('show.bs.select', disabled_column);
         $('[name="column_show[]"]').on('hidden.bs.select', enabled_column);
      }, 1000);


   }

   function populateChildDropdown(parentValue) {
      let childDropdown = $('#view_application_sub_stage');
      childDropdown.empty().append($('<option>', {
         value: '',
         text: ''
      }));

      let childOptions = sub_category.filter(item => item.application_tracker === parentValue);
      if (childOptions.length > 0) {
         childOptions.forEach(option => {
            childDropdown.append($('<option>', {
               value: option.id,
               text: option.name
            }));
         });
      }

      childDropdown.selectpicker('refresh');
   }

   $('#mass_delete').change(function() {
      let documentStatusUpdate = $('.document_status_update');
      documentStatusUpdate.find("select").val("").trigger("change");
      documentStatusUpdate.find("input[type=checkbox]").prop("checked", false);
      documentStatusUpdate.toggle();
   });

   function Update_apostille(obj) {
      // Check if the checkbox is checked
      if ($(obj).prop('checked')) {
         // Hide elements related to document status update
         $('.document_status_update').hide();
         $(".document_status_update").find("select").val("").selectpicker('refresh');
         $(".document_status_update").find("input[type=checkbox]").prop("checked", false);

         $('.visa_update').hide();
         $(".visa_update").find("select").val("").selectpicker('refresh');
         $(".visa_update").find("input[type=checkbox]").prop("checked", false);

         // Toggle visibility of transition location elements
         $(".is_transist_location").hide();
         $(".no_is_transist_location").show();
         $(".apostille_status_update").show();
      } else {
         // Hide elements related to document status update
         $('.document_status_update').show();
         $('.visa_update').show();


         // Toggle visibility of transition location elements
         $(".is_transist_location").hide();
         $(".no_is_transist_location").show();
         $(".apostille_status_update").hide();
         $(".apostille_status_update").find("select").val("").selectpicker('refresh');
         $(".apostille_status_update").find("input").val("");
         $(".apostille_status_update").find("input[type=checkbox]").prop("checked", false);

      }
   }


   function Update_visa(obj) {
      // Check if the checkbox is checked
      if ($(obj).prop('checked')) {
         // Hide elements related to document status update
         $('.document_status_update').hide();
         $(".document_status_update").find("select").val("").selectpicker('refresh');
         $(".document_status_update").find("input[type=checkbox]").prop("checked", false);

         $('.apostille_update').hide();
         $(".apostille_update").find("select").val("").selectpicker('refresh');
         $(".apostille_update").find("input[type=checkbox]").prop("checked", false);

         // Toggle visibility of transition location elements
         $(".is_transist_location").hide();
         $(".no_is_transist_location").show();
         $(".visa_status_update").show();
      } else {
         // Hide elements related to document status update
         $('.document_status_update').show();


         // Toggle visibility of transition location elements
         $(".is_transist_location").hide();
         $(".no_is_transist_location").show();
         $(".apostille_update").show();
         $(".visa_status_update").find("select").val("").selectpicker('refresh');
         $(".visa_status_update").find("input").val("");
         $(".visa_status_update").find("input[type=checkbox]").prop("checked", false);
         $(".visa_status_update").hide();


      }
   }

   function customers_bulk_action(event) {


      var mass_delete = $('#mass_delete').prop('checked');
      var transit = $('#in_transit').prop('checked');
      var from_location = $('#from_location').val();
      var to_location = $('#to_location').val();
      var office_location = $('#office_location').val();
      // var document_status = $('#document_status').val();
      var status_text = $("#document_status option:selected").text();
      var locations_name = $("#office_location option:selected").text();
      var apostille_status = $("#apostille_status_check").prop('checked');
      var visa_status = $("#visa_status_check").prop('checked');
      var is_valid = true;
      var apostille_data = {};
      var visa_data = {};

      if (apostille_status === true) {
         $('.apostille_status_update').find('input, select').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            var required = $(this).attr('required') || $(this).attr('requried');
            if (name) {
               apostille_data[name] = value;
            }
            // console.log(value);
            // console.log(required);
            if (required && !String(value).trim()) {
               $(this).focus();
               alert_float("warning", "Please fill the required field: " + name);
               is_valid = false;
               return false; // Exit loop early
            }
         });
      }

      if (visa_status === true) {
         $('.visa_status_update').find('input, select').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            var required = $(this).attr('required') || $(this).attr('requried');
            if (name) {
               visa_data[name] = value;
            }
            // console.log(value);
            // console.log(required);
            if (required && !String(value).trim()) {
               $(this).focus();
               alert_float("warning", "Please fill the required field: " + name);
               is_valid = false;
               return false; // Exit loop early
            }
         });
      }

      // console.log(apostille_status);

      if (!is_valid) return false;

      if (!confirm(app.lang.confirm_action_prompt)) {
         return false;
      }

      var ids = [];
      $('.table-clients tbody tr').each(function() {
         var checkbox = $(this).find('td').eq(0).find('input[type="checkbox"]');
         if (checkbox.prop('checked')) {
            ids.push(checkbox.val());
         }
      });

      if (ids.length === 0) {
         alert("Please select at least one customer.");
         return false;
      }

      var data = {
         ids,
         mass_delete,
         in_transit: transit,
         from_location,
         to_location,
         office_location,
         // document_status,
         status_text,
         locations_name,
         apostille_status,
         visa_status,
      };

      // Merge Apostille data
      Object.assign(data, apostille_data);
      Object.assign(data, visa_data);

      $(event.target).prop('disabled', true);

      setTimeout(() => {
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
            .fail(() => alert_float("danger", "Failed to process the request."))
            .always(() => $(event.target).prop('disabled', false));
      }, 50);
   }


   // Apply filter click event
   $('#apply_filter_').on('click', async function() {
      enabled_column();
      $("#column_show").each(function() {
         if ($(this).is(":input")) {
            updateColumns($(this));
         }
      });
      await applicant_reload();
   });

   async function applicant_reload() {
      enabled_column();
      var selectedValues = $("#column_show").selectpicker('val');

      if (selectedValues.length < 3) {
         alert("Select at least 3 columns");
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

      var from_date = $("#last_from_date").val();
      var to_date = $("#last_to_date").val();

      if (to_date && !from_date) {
         $("#last_from_date").focus();
         return false;
      }

      if (from_date && !to_date) {
         $("#last_to_date").focus();
         return false;
      }

      show_loader("apply_filter");
      applicant_table = initDataTable('.table-clients', admin_url + 'clients/table/2', [0], [0], CustomersServerParams, [0, "DESC"]);
      // disabled_column();

      hide_loader("apply_filter");
      // disabled_column();
   }

   function change_transit(obj) {
      $(".is_transist_location, .no_is_transist_location").toggle().find("select").val("").selectpicker("refresh");
   }

   $('#customers_bulk_action').on('show.bs.modal', function() {
      $("#customers_bulk_action").find("select").val("").selectpicker('refresh');
      $("#customers_bulk_action").find("input[type=checkbox]").prop("checked", false);
      $("#customers_bulk_action").find("input").val("");

      $(".document_status_update").show();
      $(".no_is_transist_location").show();
      $(".visa_update,.visa_update div.checkbox").show();
      $(".apostille_update,.apostille_update div.checkbox").show();

      $(".is_transist_location").hide();
      $(".visa_status_update").hide();
      $(".apostille_status_update").hide()
      // $(".document_status_update").hide();

      $(".doc-cost-section").html('');

   });

   function refreshApplicantTable() {
      applicant_table.ajax.reload(null, false);
   }

   function applicant_status_change(status = 0) {
      let additional_fields = {};
      let form_status = true;

      show_loader();

      $("#applicant_status_change_form input:visible, #applicant_status_change_form textarea:visible, #applicant_status_change_form select:visible, #applicant_status_change_form input[type='date']:visible").each(function() {
         const value = $(this).val()?.trim(); // Get trimmed value
         const isRequired = $(this).is("[required-check]"); // Check if 'required-check' exists
         const name = $(this).attr("name"); // Get name attribute
         let label = $(this).closest("div.form-group").find("label").text().trim().replace(/\*/g, ""); // Remove * from label

         if (isRequired && name) {
            additional_fields[name] = "required";
            if (!value) {
               if (form_status && status == 0) {
                  alert_float("danger", `"${label}" is mandatory.`);
                  $(this).focus();
               }
               form_status = false;
            }
         }
      });

      if (!form_status) {
         appValidateForm($("#applicant_status_change_form"), additional_fields);
         hide_loader();
         return false;
      }

      if (status === 1) {
         hide_loader();
         return false;
      }

      let formData = new FormData(document.getElementById("applicant_status_change_form"));

      // Append CSRF token if it exists
      formData.append(csrfData.token_name, csrfData.hash);

      // AJAX request to update client status
      $.ajax({
         url: "<?php echo base_url('admin/clients/update_client_status'); ?>",
         type: "POST",
         data: formData,
         processData: false, // Prevent jQuery from transforming FormData
         contentType: false, // Ensure correct Content-Type is set for FormData
         dataType: "JSON",
         success: function(res) {
            hide_loader();
            if (res.resp_code === "RCS") {
               $("#applicant_status_change").modal("hide");
               applicant_reload();
               alert_float("success", res.resp_desc);
            } else {
               alert_float("danger", res.resp_desc || "An unknown error occurred.");
            }
         },
         error: function(xhr, status, error) {
            hide_loader();
            let errorMessage = xhr.responseText ? xhr.responseText : "An error occurred while processing the request.";
            alert_float("danger", errorMessage);
            console.error("Error:", error);
         },
      });
   }

   function document_cost_div(obj) {
      let selected_documents = $(obj).val() || [];

      // Clear all existing doc cost sections
      $(".doc-cost-section").empty();

      // Re-add only the selected ones
      selected_documents.forEach(function(doc_id) {
         let doc = apostille_documents_list[doc_id];

         $(".doc-cost-section").append(`
            <div class='col-md-4' id='cost-doc-div-${doc_id}'>
                <label>${doc.name} Cost</label>
                <div class='form-group'>
                    <input class='form-control' type='number' placeholder='100' name='document_cost[${doc.id}]'>
                </div>
            </div>
        `);
      });
   }

   $(document).ready(function() {
      $("#session_intake").on("change", function() {
         let selectedDate = $(this).val(); // Get selected value (YYYY-MM)

         if (selectedDate) {
            let [year, month] = selectedDate.split("-"); // Extract year and month

            if (month !== "02" && month !== "09") {
               // If not February or September, auto-correct to the nearest allowed month
               // let correctedMonth = (month < "06") ? "02" : "09"; // Before June → February, After → September
               $(this).val('');
               alert_float("danger", "Only February and September are allowed.");
            }
         }
      });

   });


   function downloadAndZipFiles(files, zipFileName = "documents.zip") {
   const zip = new JSZip();
   const folder = zip.folder("files");

   const downloadPromises = files.map(({ url, name }, index) =>
      fetch(url)
         .then(response => {
            if (!response.ok) throw new Error(`Failed to fetch: ${url}`);
            return response.blob().then(blob => {
               const originalName = url.split('/').pop().split('?')[0] || `file${index}`;
               const extension = originalName.includes('.') ? '.' + originalName.split('.').pop() : '';
               
               // Use the provided name if available, or default to 'fileX' where X is the index
               const finalName = (name || `file${index}`) + extension;

               folder.file(finalName, blob);
            });
         })
         .catch(err => console.error("Error downloading file:", err))
   );

   Promise.all(downloadPromises).then(() => {
      // Generate the zip and save it with the provided name (or default to 'documents.zip')
      zip.generateAsync({ type: "blob" }).then(content => {
         saveAs(content, zipFileName);
      });
   });
}




   function download_documents(userid, userName) {
      let formData = new FormData();

      // Append CSRF token if it exists
      formData.append(csrfData.token_name, csrfData.hash);
      formData.append("userid", userid);

      // AJAX request to fetch approved documents
      $.ajax({
         url: "<?php echo base_url('admin/clients/download_approved_documents'); ?>",
         type: "POST",
         data: formData,
         processData: false,
         contentType: false,
         dataType: "json",
         success: function(res) {
            hide_loader();
            // console.log(res);

            if (res.resp_code === "RCS") {
               let files = res.data;
               downloadAndZipFiles(files, userName + '.zip'); // Assuming this function handles zipping and downloading
               alert_float("success", res.resp_desc);
            } else {
               alert_float("danger", res.resp_desc || "An unknown error occurred.");
            }
         },
         error: function(xhr, status, error) {
            hide_loader();
            let errorMessage = xhr.responseText ? xhr.responseText : "An error occurred while processing the request.";
            alert_float("danger", errorMessage);
            console.error("Error:", error);
         },
      });
   }
</script>
</body>

</html>