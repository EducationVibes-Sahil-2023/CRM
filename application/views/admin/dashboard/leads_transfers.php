<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php $this->load->helper('leads'); ?>

<div id="wrapper">
    <div class="screen-options-area"></div>

    <div class="content">
        <div class="row">
            <!-- Table Section -->
            <div class="col-md-12 mtop15">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="pull-left no-margin"><i class="fa fa-filter"></i> Filters</h5>
                                <button type="button" class="btn btn-sm btn-default pull-right" onclick="resetFilters()">
                                    <i class="fa fa-refresh"></i> Reset Filters
                                </button>
                                <div class="clearfix"></div>
                                <hr class="hr-panel-heading" />
                            </div>

                            <!-- Transfer Date Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_range" class="control-label">
                                        <i class="fa fa-calendar"></i> Transfer Date
                                    </label>
                                    <input type="text" readonly name="transfer_date" id="date_range" class="form-control date-range"
                                        placeholder="Select date range" autocomplete="off">
                                </div>
                            </div>

                            <!-- Assignation Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="assignation_filter" class="control-label">
                                        <i class="fa fa-user"></i> Assignation
                                    </label>
                                    <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status_filter" class="control-label">
                                        <i class="fa fa-tag"></i> Status
                                    </label>
                                    <?php
                                    echo render_select('view_status[]', $lead_statuses, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_status');
                                    ?>
                                </div>
                            </div>

                            <!-- Update Count Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="update_count_filter" class="control-label">
                                        <i class="fa fa-chart-line"></i> Update Count
                                    </label>
                                    <?php
                                    $update_counts = [
                                        ['id' => 0, 'name' => '0'],
                                        ['id' => 1, 'name' => '1'],
                                        ['id' => 2, 'name' => '2'],
                                        ['id' => 3, 'name' => '3'],
                                        ['id' => 4, 'name' => '4'],
                                    ];
                                    echo render_select('view_update_count[]', $update_counts, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_update_count');
                                    ?>
                                </div>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                    <i class="fa fa-filter"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportData()">
                                    <i class="fa fa-download"></i> Export
                                </button>
                            </div>
                        </div>

                        <h4 class="no-margin">
                            <i class="fa fa-exchange"></i> Leads Transfer Logs
                            <small class="text-muted">- Not Reachable Leads</small>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <div class="table-responsive">
                            <?php
                            $_table_data = [];

                            $_table_data[] = [
                                'name' => 'ID',
                                'th_attrs' => ['class' => 'id']
                            ];

                            $_table_data[] = [
                                'name' => _l('Name'),
                            ];

                            $_table_data[] = [
                                'name' => _l('Phone Number'),
                            ];

                            $_table_data[] = [
                                'name' => _l('Update Count'),
                            ];

                            $_table_data[] = [
                                'name' => _l('Old Status'),
                            ];

                            $_table_data[] = [
                                'name' => _l('New Status'),
                            ];

                            $_table_data[] = [
                                'name' => _l('Old Assigned'),
                            ];

                            $_table_data[] = [
                                'name' => _l('New Assigned'),
                            ];

                            $_table_data[] = [
                                'name' => _l('Old Assigned Date'),
                            ];

                            $_table_data[] = [
                                'name' => _l('New Assigned Date'),
                            ];

                            $_table_data[] = [
                                'name' => _l('Transfer Date'),
                                'th_attrs' => ['class' => 'date-created toggleable']
                            ];

                            render_datatable(
                                $_table_data,
                                'leads-transfers',
                                ['customizable-table', 'sticky-header']
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- Include Date Range Picker CSS and JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    var r = {
        status: "[name='view_status[]']",
        assigned: "[name='view_assigned[]']",
        update_count: "[name='view_update_count[]']",
        transfer_date: "[name='transfer_date']"
    };

    var leadsTable;
    var selectedDateRange = null;

    function refresh_lead_transfer_table() {
        var tableSelector = '.table-leads-transfers'; // ✅ Fixed: use ID instead of class

        // ✅ Check if table exists
        if (!$(tableSelector).length) {
            console.log('Table not found');
            return;
        }

        // ✅ Prevent reinitialization
        if ($.fn.DataTable.isDataTable(tableSelector)) {
            leadsTable = $(tableSelector).DataTable();
            leadsTable.ajax.reload(null, false);
            return;
        }

        leadsTable = initDataTable(
            tableSelector,
            admin_url + 'dashboard/leads_transfers',
            'undefined',
            'undefined',
            r,
            [0, 'desc']
        );
    }

    function applyFilters() {
        if (leadsTable) {
            leadsTable.ajax.reload();
            alert_float('success', 'Filters applied successfully!');
        } else {
            refresh_lead_transfer_table();
            setTimeout(function() {
                if (leadsTable) {
                    leadsTable.ajax.reload();
                    alert_float('success', 'Filters applied successfully!');
                }
            }, 500);
        }
    }

    function resetFilters() {
        // Reset all filter inputs
        $('[name="view_status[]"]').val('').trigger('change');
        $('[name="view_assigned[]"]').val('').trigger('change');
        $('[name="view_update_count[]"]').val([]).trigger('change');
        $('[name="transfer_date"]').val('');

        // Reset date range picker
        if ($('#date_range').data('daterangepicker')) {
            $('#date_range').data('daterangepicker').setStartDate('');
            $('#date_range').data('daterangepicker').setEndDate('');
            $('#date_range').val('');
        }
        selectedDateRange = null;

        // Refresh selectpickers if any
        if ($.fn.selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
        initDateRangePicker();
        // Reload table
        if (leadsTable) {
            leadsTable.ajax.reload();
            alert_float('info', 'Filters reset successfully!');
        } else {
            refresh_lead_transfer_table();
            setTimeout(function() {
                if (leadsTable) {
                    alert_float('info', 'Filters reset successfully!');
                }
            }, 500);
        }
    }

    function exportData() {
        if (!leadsTable) {
            alert_float('warning', 'Please wait for table to load before exporting');
            return;
        }

        // Get current filter values
        var filters = {
            status: $('[name="view_status[]"]').val(),
            assigned: $('[name="view_assigned[]"]').val(),
            update_count: $('[name="view_update_count[]"]').val(),
            transfer_date: $('[name="transfer_date"]').val()
        };

        var queryString = $.param(filters);
        window.location.href = admin_url + 'dashboard/export_leads_transfers?' + queryString;
    }

    // Initialize date range picker
    function initDateRangePicker() {
        if ($('#date_range').length) {
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                var dateString = picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD');
                $(this).val(dateString);
                selectedDateRange = picker;
                $(this).trigger('change');
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                selectedDateRange = null;
                $(this).trigger('change');
            });
        }
    }

    // Initialize selectpickers
    function initSelectpickers() {
        if ($.fn.selectpicker) {
            $('.selectpicker').selectpicker({
                iconBase: 'fa',
                tickIcon: 'fa-check'
            });
        }
    }

    // Auto-apply filters on change (optional)
    function enableAutoApply() {
        $('[name="view_status[]"], [name="view_assigned[]"], [name="view_update_count[]"], [name="transfer_date"]').on('change', function() {
            if (leadsTable) {
                leadsTable.ajax.reload();
            }
        });
    }

    $(document).ready(function() {
        // Initialize date range picker
        initDateRangePicker();

        // Initialize selectpickers
        initSelectpickers();

        // Initialize table
        refresh_lead_transfer_table();

        // Uncomment below to enable auto-apply on filter change
        // enableAutoApply();
    });
</script>

<style>
    /* Custom styling for dashboard */
    .small-box {
        border-radius: 4px;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
        padding: 15px;
    }

    .small-box .inner {
        padding: 10px;
    }

    .small-box h3 {
        font-size: 38px;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }

    .small-box p {
        font-size: 15px;
        margin-bottom: 0;
    }

    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 70px;
        opacity: 0.3;
    }

    .bg-aqua {
        background-color: #00c0ef;
        color: #fff;
    }

    .bg-green {
        background-color: #00a65a;
        color: #fff;
    }

    .bg-yellow {
        background-color: #f39c12;
        color: #fff;
    }

    .bg-red {
        background-color: #dd4b39;
        color: #fff;
    }

    .filter-section {
        background: #f9f9f9;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .date-range {
        background-color: #fff;
    }

    .mtop15 {
        margin-top: 15px;
    }
</style>