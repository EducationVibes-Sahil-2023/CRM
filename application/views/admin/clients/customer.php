<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$currentYear = date('Y');
$years = [];

for ($i = 0; $i <= 5; $i++) {
    $year = $currentYear - $i;
    $years[] = ['id' => $year, 'name' => $year];
}
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <div id="filterArea" class=" hidden-xs">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="bold"><?php echo _l('filter_by'); ?></p>
                                    </div>
                                    <?php if (has_permission('leads', '', 'view')) { ?>
                                        <div class="col-md-2  margin-top leads-filter-column">
                                            <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                        </div>
                                    <?php } ?>

                                    <div class="col-md-2  margin-top leads-filter-column">
                                        <?php
                                        echo '<div id="leads-filter-source">';
                                        echo render_select('lead_type[]', $leadType, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
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
                                        echo render_select('session_year', $years, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Session Year"), array(), 'no-mbot', '', false, "session_year");
                                        echo '</div>';
                                        ?>
                                    </div>

                                    <div class="col-md-4 margin-top leads-filter-column">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>
                                            <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix mtop20"></div>
                        <?php
                        $table_data = array();
                        $_table_data = array(
                            array(
                                'name' => _l('leads_dt_name'),
                            ),
                            array(
                                'name' =>  _l('PhoneNumber'),
                            ),
                            array(
                                'name' => _l('leads_dt_email'),
                            ),
                            array(
                                'name' => _l('leads_dt_status'),
                            ),
                            array(
                                'name' => _l('Lead Type'),
                            ),
                            array(
                                'name' => _l('leads_source'),
                            ),

                        );

                        foreach ($_table_data as $_t) {
                            array_push($table_data, $_t);
                        }

                        $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                        render_datatable(
                            $table_data,
                            'clients-customers',
                            array('customizable-table sticky-header'),
                            array(
                                'id' => 'table-clients-customers',
                                'data-last-order-identifier' => 'custumers',
                            )
                        );

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>

    <script>
        var tAPI = "";
        $(function() {
            var CustomersServerParams = {};

            CustomersServerParams['assigned'] = "[name='view_assigned[]']";
            CustomersServerParams['source'] = "[name='view_source[]']";
            CustomersServerParams['lead_type'] = "[name='lead_type[]']";
            CustomersServerParams['session_year'] = "[name='session_year']";

            tAPI = initDataTable('.table-clients-customers', admin_url + 'clients/customers_table', [0], [0], CustomersServerParams);

        })
        $('#apply_filter').on('click', function() {
            show_loader("apply_filter");
            tAPI.ajax.reload(null, false).on('draw.dt', function() {
                hide_loader("apply_filter");
            });
        });
    </script>