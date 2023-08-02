<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
   .leads-filter-column {
      padding: 0px 5px !important;
   }
</style>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('expenses', '', 'create')) { ?>
                     <a href="<?php echo admin_url('expenses/expense'); ?>" class="btn btn-info"><?php echo _l('new_expense'); ?></a>
                  <?php } ?>
                  <?php $this->load->view('admin/expenses/filter_by_template'); ?>
                  <a href="#" onclick="slideToggle('#stats-top'); return false;" class="pull-right btn btn-default mleft5 btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('view_stats_tooltip'); ?>"><i class="fa fa-bar-chart"></i></a>
                  <a href="#" class="btn btn-default pull-right btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-expenses','#expense'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  <div id="stats-top" class="hide">
                     <hr />
                     <div id="expenses_total"></div>
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
                           <div class="form-group  select-placeholder col-md-2">
                              <?php if (count($payment_modes) > 0) { ?>
                                 <select class="selectpicker" data-toggle="<?php echo $this->input->get('allowed_payment_modes'); ?>" name="allowed_payment_modes[]" data-actions-box="true" multiple="true" data-width="100%" data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php foreach ($payment_modes as $mode) {
                                    ?>
                                       <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                                    <?php } ?>
                                 </select>
                              <?php } else { ?>
                                 <p><?php echo _l('invoice_add_edit_no_payment_modes_found'); ?></p>
                                 <a class="btn btn-info" href="<?php echo admin_url('paymentmodes'); ?>">
                                    <?php echo _l('new_payment_mode'); ?>
                                 </a>
                              <?php } ?>
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
                        <div class="clearfix"></div>
                        <!-- if expenseid found in url -->
                        <?php echo form_hidden('expenseid', $expenseid); ?>
                        <?php $this->load->view('admin/expenses/table_html'); ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="expense" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="expense_convert_helper_modal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('additional_action_required'); ?></h4>
         </div>
         <div class="modal-body">
            <div class="radio radio-primary">
               <input type="radio" checked id="expense_convert_invoice_type_1" value="save_as_draft_false" name="expense_convert_invoice_type">
               <label for="expense_convert_invoice_type_1"><?php echo _l('convert'); ?></label>
            </div>
            <div class="radio radio-primary">
               <input type="radio" id="expense_convert_invoice_type_2" value="save_as_draft_true" name="expense_convert_invoice_type">
               <label for="expense_convert_invoice_type_2"><?php echo _l('convert_and_save_as_draft'); ?></label>
            </div>
            <div id="inc_field_wrapper">
               <hr />
               <p><?php echo _l('expense_include_additional_data_on_convert'); ?></p>
               <p><b><?php echo _l('expense_add_edit_description'); ?> +</b></p>
               <div class="checkbox checkbox-primary inc_note">
                  <input type="checkbox" id="inc_note">
                  <label for="inc_note"><?php echo _l('expense'); ?> <?php echo _l('expense_add_edit_note'); ?></label>
               </div>
               <div class="checkbox checkbox-primary inc_name">
                  <input type="checkbox" id="inc_name">
                  <label for="inc_name"><?php echo _l('expense'); ?> <?php echo _l('expense_name'); ?></label>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-info" id="expense_confirm_convert"><?php echo _l('confirm'); ?></button>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
   var hidden_columns = [4, 5, 6, 7, 8, 9];
</script>
<?php init_tail(); ?>
<script>
   Dropzone.autoDiscover = false;
   $(function() {
      // Expenses additional server params
      var Expenses_ServerParams = {};
      $.each($('._hidden_inputs._filters input'), function() {
         Expenses_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      initDataTable('.table-expenses', admin_url + 'expenses/table', 'undefined', 'undefined', Expenses_ServerParams, <?php echo hooks()->apply_filters('expenses_table_default_order', json_encode(array(5, 'desc'))); ?>).column(0).visible(false, false).columns.adjust();

      init_expense();

      $('#expense_convert_helper_modal').on('show.bs.modal', function() {
         var emptyNote = $('#tab_expense').attr('data-empty-note');
         var emptyName = $('#tab_expense').attr('data-empty-name');
         if (emptyNote == '1' && emptyName == '1') {
            $('#inc_field_wrapper').addClass('hide');
         } else {
            $('#inc_field_wrapper').removeClass('hide');
            emptyNote === '1' && $('.inc_note').addClass('hide') || $('.inc_note').removeClass('hide')
            emptyName === '1' && $('.inc_name').addClass('hide') || $('.inc_name').removeClass('hide')
         }
      });

      $('body').on('click', '#expense_confirm_convert', function() {
         var parameters = new Array();
         if ($('input[name="expense_convert_invoice_type"]:checked').val() == 'save_as_draft_true') {
            parameters['save_as_draft'] = 'true';
         }
         parameters['include_name'] = $('#inc_name').prop('checked');
         parameters['include_note'] = $('#inc_note').prop('checked');
         window.location.href = buildUrl(admin_url + 'expenses/convert_to_invoice/' + $('body').find('.expense_convert_btn').attr('data-id'), parameters);
      });


      $("select[name='lead_type[]']").change(function() {
         let lead_type = $(this).val();
         lead_type = Array.isArray(lead_type) ? lead_type.join(",") : "";
         $("._filters._hidden_inputs").find("input[name='lead_types']").val(lead_type);
      });

      // Handler for changes to the select element with name "clientid"
      $("select[name='clientid']").change(function() {
         let applicant_id = $(this).val();
         $("._filters._hidden_inputs").find("input[name='applicant_id']").val(applicant_id);
      });

      $("select[name='allowed_payment_modes[]']").change(function() {
         let allowed_payment_modes = $(this).val();
         $("._filters._hidden_inputs").find("input[name='allowed_payment_modes']").val(allowed_payment_modes);
      });

      $("input[name='from_date']").change(function() {
         let from_date = $(this).val();
         $("._filters._hidden_inputs").find("input[name='from_date']").val(from_date);
      });

      $("input[name='to_date']").change(function() {
         let to_date = $(this).val();
         $("._filters._hidden_inputs").find("input[name='to_date']").val(to_date);
      });



   });


   function filter() {

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
      // get_invoice_summary();
      periodFilter();

   }

   function periodFilter() {
      $(".table-expenses").DataTable().ajax.reload(null, false).on('draw.dt', function() {
         hide_loader("apply_filter");
      });
   }
</script>
</body>

</html>