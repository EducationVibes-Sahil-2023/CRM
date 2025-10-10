<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
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
                                <h4 class="no-margin"><?php echo _l('Ticket Details'); ?></h4>
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

                        <div class="clearfix mtop20"></div>
                        <?php
                        render_datatable($table_data, 'clients', [], [
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
        $.each($('._hidden_inputs._filters input'), function() {
            CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });
        CustomersServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';
        CustomersServerParams['assigned'] = "[name='view_assigned[]']";
        CustomersServerParams['source'] = "[name='view_source[]']";
        CustomersServerParams['lead_type'] = "[name='lead_type[]']";
        CustomersServerParams['from_date'] = "[name='from_date']";
        CustomersServerParams['to_date'] = "[name='to_date']";
        CustomersServerParams['application_stage'] = "[name='view_application_stage']";
        CustomersServerParams['application_sub_stage'] = "[name='view_application_sub_stage']";
        CustomersServerParams['vendor_type'] = "[name='vendor_type[]']";
        tAPI = initDataTable('.table-clients', admin_url + 'clients/visa_details_table', [0], [0], CustomersServerParams);
        $('input[name="exclude_inactive"]').on('change', function() {
            tAPI.ajax.reload();
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