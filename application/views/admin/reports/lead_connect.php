   <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
   <?php init_head(); ?>
   <div id="wrapper">

       <div class="content">
           <div class="row">
               <div class="panel_s">
                   <div class="panel-body">
                       <div id="filterArea" class=" hidden-xs">
                           <div class="row">
                               <div class="col-md-12">
                                   <p class="bold"><?php echo _l('filter_by'); ?></p>
                               </div>


                               <?php if (has_permission('leads', '', 'view') || is_postSale()) { ?>
                                   <div class="col-md-2  margin-top leads-filter-column">
                                       <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Counsellor", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                   </div>
                               <?php } ?>

                               <div class="col-md-2  margin-top leads-filter-column">
                                   <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('lead_type[]', $leadType, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
                                    echo '</div>';
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
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('status[]', $status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "status");
                                    echo '</div>';
                                    ?>
                               </div>

                               <div class="col-md-2  margin-top leads-filter-column">
                                   <div class="form-group">
                                       <input type="text" class="form-control datepicker" name="last_from_date" id="last_from_date" placeholder="From Last Update Date" autocomplete="off">
                                   </div>
                               </div>
                               <div class="col-md-2  margin-top leads-filter-column">
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
           </div>

           <div class="row">
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

   <?php
    init_tail();
    ?>