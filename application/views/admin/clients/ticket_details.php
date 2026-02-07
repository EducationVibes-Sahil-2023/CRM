<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$ticket_status   = getDataInformation('ticket_status', ['id', 'name'], ['status' => 1]);
$flight_type   = getDataInformation('flight_type', ['id', 'name'], ['status' => 1]);
$ticket_vendor = getDataInformation('external_ticket_vendor', ['id', 'name'], ['status' => 1]);
$airline   = getDataInformation('airline', ['id', 'name'], ['status' => 1]);
$ticket_type   = getDataInformation('external_visa_type', ['id', 'name'], ['status' => 1]);


$table_data = array(
    array('name' => 'Name'),
    array('name' => 'Ticket Vendor'),
    array('name' => 'Ticket Type'),
     array('name' => 'Ticket Name'),
    array('name' => 'Flight Date'),
    array('name' => 'Payment Mode'),
    array('name' => 'Payment Date'),
    array('name' => 'Ticket Cost'),
    array('name' => 'Country'),
    array('name' => 'Departure'),
    array('name' => 'Destination'),
    array('name' => 'Deposite Mode'),
    array('name' => 'Deposite Amount'),
    array('name' => 'Deposite Date')
);
?>
<div id="wrapper" class="ticket_details">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo _l('External Ticket Details'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('external_ticket', '', 'create')) {
                                ?>
                                    <a href="<?php echo admin_url('clients/external_ticket'); ?>" class="btn btn-primary">
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
                                    echo render_select('ticket_status[]', $ticket_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Ticket status', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "ticket_status");
                                    echo '</div>';
                                    ?>
                                    </div>
                                    
                                     <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('flight_type[]', $flight_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Flight type', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "flight_type");
                                    echo '</div>';
                                    ?>
                                    </div>
                                    
                                     <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('ticket_vendor[]', $ticket_vendor, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Ticket vendor', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "ticket_vendor");
                                    echo '</div>';
                                    ?>
                                    </div>
                                    
                                    
                                     <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('airline[]', $airline, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'airline', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "airline");
                                    echo '</div>';
                                    ?>
                                    </div>
                                    
                                    <div class="col-md-2 leads-filter-column">
                         <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('ticket_type[]', $ticket_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Ticket type', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "ticket_type");
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
                        render_datatable($table_data, 'external-ticket', [], [
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
var tAPI = null;

$(document).ready(function () {

    var CustomersServerParams = {};

    $('._hidden_inputs._filters input').each(function () {
        CustomersServerParams[$(this).attr('name')] =
            '[name="' + $(this).attr('name') + '"]';
    });

    CustomersServerParams['ticket_status'] = "[name='ticket_status[]']";
    CustomersServerParams['flight_type'] = "[name='flight_type[]']";
    CustomersServerParams['ticket_vendor'] = "[name='ticket_vendor[]']";
    CustomersServerParams['airline'] = "[name='airline[]']";
    CustomersServerParams['ticket_type'] = "[name='ticket_type[]']";

    tAPI = initDataTable(
        '.table-external-ticket',
        admin_url + 'clients/ticket_details_table',
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


    function deleteTicket(id) {
        if (!confirm('Are you sure you want to delete this ticket record?')) {
            return;
        }

        $.ajax({
            url: '<?= admin_url("clients/delete_ticket/"); ?>' + id,
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