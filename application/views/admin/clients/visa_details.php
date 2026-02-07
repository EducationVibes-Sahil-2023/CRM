<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php

$visa_vendor = getDataInformation('external_visa_vendor', ['id', 'name'], ['status' => 1]);
$visa_type   = getDataInformation('external_visa_type', ['id', 'name'], ['status' => 1]);
$visa_status   = getDataInformation('external_visa_status', ['id', 'name'], ['status' => 1]);

$table_data = array(
    array('name' => 'Name'),
    array('name' => 'Visa Vendor'),
    array('name' => 'Visa Type'),
    array('name' => 'Application Date'),
    array('name' => 'Status'),
    array('name' => 'Received Date'),
    array('name' => 'Payment Mode'),
    array('name' => 'Payment Date'),
    array('name' => 'Visa Cost'),
    array('name' => 'Insurance Cost'),
    array('name' => 'Country'),
    array('name' => 'Deposite Mode'),
    array('name' => 'Deposite Amount'),
    array('name' => 'Deposite Date')
);
?>
<div id="wrapper" class="visa_details">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo _l('External Visa Details'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('external_ticket', '', 'create')) {
                                ?>
                                    <a href="<?php echo admin_url('clients/external_visa'); ?>" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('create'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
 <div class="row" id="leads-table ">
                          <div id="filterArea" class="col-md-12 hidden-xs">
                              <div class="row">
                                  <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('visa_status[]', $visa_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Visa status', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "visa_status");
                                    echo '</div>';
                                    ?>
                                    </div>
                         
                                    
                                     <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('visa_vendor[]', $visa_vendor, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Visa vendor', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "visa_vendor");
                                    echo '</div>';
                                    ?>
                                    </div>
                                    
                                    
                                    <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('visa_type[]', $visa_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Visa type', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "visa_type");
                                    echo '</div>';
                                    ?>
                                    </div>
                                    
                                    <div class="col-md-2 text-center leads-filter-column">
                                     <div class="form-group">
                                       <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>
                                       <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                    </div>
                                 </div>
                                    </div>
                                    </div>
                                    </div>
                                     <hr class="hr-panel-heading" />
                        <div class="clearfix mtop20"></div>
                        <?php
                        render_datatable($table_data, 'external-visa', [], [
                            'data-last-order-identifier' => 'customers',
                            'data-default-order'         => get_table_last_order('customers'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?php
init_tail();
?>
<script>
    var tAPI = "";

    $(function() {
         var CustomersServerParams = {};

    $('._hidden_inputs._filters input').each(function () {
        CustomersServerParams[$(this).attr('name')] =
            '[name="' + $(this).attr('name') + '"]';
    });

    CustomersServerParams['visa_status'] = "[name='visa_status[]']";
    CustomersServerParams['visa_vendor'] = "[name='visa_vendor[]']";
    CustomersServerParams['visa_type'] = "[name='visa_type[]']";

    tAPI = initDataTable(
        '.table-external-visa',
        admin_url + 'clients/visa_details_table',
        [0],
        [0],
        CustomersServerParams
    );

    $('#apply_filter').on('click', function () {
        if (tAPI) {
            tAPI.ajax.reload(null, false); // false = keep pagination
        }
    });
     
    });

    function deleteVisa(id) {
        if (!confirm('Are you sure you want to delete this visa record?')) {
            return;
        }

        $.ajax({
            url: '<?= admin_url("clients/delete_visa/"); ?>' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.resp_code === 'RCS') {
                    alert_float('success', response.resp_desc);
                    // Remove row from table (if using DataTables)
                    tAPI.ajax.reload();
                } else {
                    alert_float('danger', response.resp_desc);
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', 'Something went wrong: ' + error);
            }
        });
    }
</script>