<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
   .leads-filter-column {
      padding: 0px 5px !important;
   }
</style>
<div class="col-md-12">
   <div class="panel_s mbot10">
      <div class="panel-body _buttons">
         <?php $this->load->view('admin/invoices/invoices_top_stats'); ?>
         <?php if (has_permission('invoices', '', 'create')) { ?>
            <a href="<?php echo admin_url('invoices/invoice'); ?>" class="btn btn-info pull-left new new-invoice-list mright5"><?php echo _l('create_new_invoice'); ?></a>
         <?php } ?>
         <?php if (!isset($project)) { ?>
            <a href="<?php echo admin_url('invoices/recurring'); ?>" class="btn btn-info pull-left">
               <?php echo _l('invoices_list_recurring'); ?>
            </a>
         <?php } ?>
         <div class="display-block text-right">
            <div class="btn-group pull-right mleft4 invoice-view-buttons btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
               <!-- <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-filter" aria-hidden="true"></i>
               </button> -->
               <ul class="dropdown-menu width300">
                  <li>
                     <a href="#" data-cview="all" onclick="dt_custom_view('','.table-invoices',''); return false;">
                        <?php echo _l('invoices_list_all'); ?>
                     </a>
                  </li>
                  <li class="divider"></li>
                  <li class="<?php if ($this->input->get('filter') == 'not_sent') {
                                 echo 'active';
                              } ?>">
                     <a href="#" data-cview="not_sent" onclick="dt_custom_view('not_sent','.table-invoices','not_sent'); return false;">
                        <?php echo _l('not_sent_indicator'); ?>
                     </a>
                  </li>
                  <li>
                     <a href="#" data-cview="not_have_payment" onclick="dt_custom_view('not_have_payment','.table-invoices','not_have_payment'); return false;">
                        <?php echo _l('invoices_list_not_have_payment'); ?>
                     </a>
                  </li>
                  <li>
                     <a href="#" data-cview="recurring" onclick="dt_custom_view('recurring','.table-invoices','recurring'); return false;">
                        <?php echo _l('invoices_list_recurring'); ?>
                     </a>
                  </li>
                  <li class="divider"></li>
                  <?php foreach ($invoices_statuses as $status) { ?>
                     <li class="<?php if ($status == $this->input->get('status')) {
                                    echo 'active';
                                 } ?>">
                        <a href="#" data-cview="invoices_<?php echo $status; ?>" onclick="dt_custom_view('invoices_<?php echo $status; ?>','.table-invoices','invoices_<?php echo $status; ?>'); return false;"><?php echo format_invoice_status($status, '', false); ?></a>
                     </li>
                  <?php } ?>
                  <?php if (count($invoices_years) > 0) { ?>
                     <li class="divider"></li>
                     <?php foreach ($invoices_years as $year) { ?>
                        <li class="active">
                           <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-invoices','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                           </a>
                        </li>
                     <?php } ?>
                  <?php } ?>
                  <?php if (count($invoices_sale_agents) > 0) { ?>
                     <div class="clearfix"></div>
                     <li class="divider"></li>
                     <li class="dropdown-submenu pull-left">
                        <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left">
                           <?php foreach ($invoices_sale_agents as $agent) { ?>
                              <li>
                                 <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view(<?php echo $agent['sale_agent']; ?>,'.table-invoices','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo $agent['full_name']; ?>
                                 </a>
                              </li>
                           <?php } ?>
                        </ul>
                     </li>
                  <?php } ?>
                  <div class="clearfix"></div>
                  <?php if (count($payment_modes) > 0) { ?>
                     <li class="divider"></li>
                  <?php } ?>
                  <?php foreach ($payment_modes as $mode) {
                     if (total_rows(db_prefix() . 'invoicepaymentrecords', array('paymentmode' => $mode['id'])) == 0) {
                        continue;
                     }
                  ?>
                     <li>
                        <a href="#" data-cview="invoice_payments_by_<?php echo $mode['id']; ?>" onclick="dt_custom_view('<?php echo $mode['id']; ?>','.table-invoices','invoice_payments_by_<?php echo $mode['id']; ?>'); return false;">
                           <?php echo _l('invoices_list_made_payment_by', $mode['name']); ?>
                        </a>
                     </li>
                  <?php } ?>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-invoices','#invoice'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
            <a href="#" class="btn btn-default btn-with-tooltip invoices-total" onclick="slideToggle('#stats-top'); init_invoices_total(true); return false;" data-toggle="tooltip" title="<?php echo _l('view_stats_tooltip'); ?>"><i class="fa fa-bar-chart"></i></a>
         </div>
         <p></p>
         <div class="row">
            <div id="filterArea" class="col-md-12  hidden-xs">
               <div class="row">
                  <div class="col-md-12">
                     <p class="bold"><?php echo _l('filter_by'); ?></p>
                  </div>
                  <div class="col-md-2 leads-filter-column">
                     <?php
                     echo '<div id="leads-filter-source">';
                     echo render_select('lead_type[]', $type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
                     echo '</div>';
                     ?>
                  </div>

                  <div class="f_client_id col-md-2">
                     <div class="form-group select-placeholder">
                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if (isset($estimate) && empty($estimate->clientid)) {
                                                                                                                              echo 'customer-removed';
                                                                                                                           } ?>" data-none-selected-text="<?php echo _l('Search Applicant Name'); ?>">
                           <?php $selected = (isset($estimate) ? $estimate->clientid : '');
                           if ($selected == '') {
                              $selected = (isset($customer_id) ? $customer_id : '');
                           }
                           if ($selected != '') {
                              $rel_data = get_relation_data('customer', $selected);
                              $rel_val = get_relation_values($rel_data, 'customer');
                              echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                           } ?>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-2 leads-filter-column">
                     <?php
                     $new_invoices_statuses = [];
                     foreach ($invoices_statuses as $key => $invoice_id) {
                        $new_invoices_statuses[$key]['id'] = $invoice_id;
                        $new_invoices_statuses[$key]['name'] = format_invoice_status($invoice_id, '', false);
                     }
                     echo '<div id="leads-filter-source">';
                     echo render_select('invoice_status[]', $new_invoices_statuses, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "invoice_status");
                     echo '</div>';
                     ?>
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

                  <div class="col-md-2 text-center leads-filter-column">
                     <div class="form-group">
                        <button type="button" class="btn btn-primary" id="apply_filter" onclick="filter()">Apply Filter</button>
                        <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                     </div>
                  </div>

               </div>
            </div>

         </div>
      </div>

   </div>

   <div class="row">
      <div class="col-md-12" id="small-table">
         <div class="panel_s">
            <div class="panel-body">
               <!-- if invoiceid found in url -->
               <?php echo form_hidden('invoiceid', $invoiceid); ?>
               <?php $this->load->view('admin/invoices/table_html'); ?>
            </div>
         </div>
      </div>
      <div class="col-md-7 small-table-right-col">
         <div id="invoice" class="hide">
         </div>
      </div>
   </div>
</div>