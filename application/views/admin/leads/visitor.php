<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
?>
<style>
    a {
        cursor: pointer;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row" id="leads-table ">
                            <div id="filterArea" class="col-md-12 hidden-xs">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="bold"><?php echo _l('filter_by'); ?></p>
                                    </div>


                                    <div class="col-md-2 leads-filter-column">
                                        <?php
                                        echo render_select('status[]', $visitor_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column">
                                        <?php
                                        echo render_select('location[]', $location, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Location'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column">
                                        <?php
                                        echo render_select('type[]', $visitor_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column">
                                        <?php
                                        echo render_select('attendee[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Attendee'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>


                                    <div class="col-md-2 leads-filter-column">
                                        <?php
                                        echo render_select('lead_type[]', $lead_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From Visitor Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2 leads-filter-column">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To Visitor Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <?php if (has_permission('leads', '', 'view')) { ?>
                                        <div class="col-md-2 leads-filter-column mb-5">
                                            <?php echo render_select('assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'assigned'); ?>
                                        </div>
                                    <?php } ?>


                                    <div class="col-md-4 text-center leads-filter-column">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" onclick="filter_data();" id="apply_filter">Apply Filter</button>

                                            <!-- <button class="btn btn-primary" id="apply_filter">Apply Filter</button> -->
                                            <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <h4>Request Generate</h4>
                        <hr>

                        <?php
                        render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type')), 'lead-visitor-genrate-table');
                        ?>
                        <?php if (!is_admin()) { ?>
                            <h4>Request Received</h4>
                            <hr>

                            <?php
                            render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type')), 'lead-visitor-request-table');
                            ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    var r = {
        status: "[name='status[]']",
        location: "[name='location[]']",
        type: "[name='type[]']",
        attendee: "[name='attendee[]']",
        lead_type: "[name='lead_type[]']",
        from_date: "[name='from_date']",
        to_date: "[name='to_date']",
        assigned: "[name='assigned[]']",

    };
    $(document).ready(function() {
        initDataTable('.table-lead-visitor-genrate-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', r, [0, 'desc']);
        <?php if (!is_admin()) { ?>
            initDataTable('.table-lead-visitor-request-table', admin_url + 'leads/table_lead_visitor/1', 'undefined', 'undefined', r, [0, 'desc']);
        <?php } ?>

    })

    function filter_data() {
        $('.table-lead-visitor-genrate-table').DataTable().destroy();
        $('.table-lead-visitor-genrate-table tbody').empty();
        initDataTable('.table-lead-visitor-genrate-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', r, [0, 'desc']);
        <?php if (!is_admin()) { ?>
            $('.table-lead-visitor-request-table').DataTable().destroy();
            $('.table-lead-visitor-request-table tbody').empty();
            initDataTable('.table-lead-visitor-request-table', admin_url + 'leads/table_lead_visitor/1', 'undefined', 'undefined', r, [0, 'desc']);
        <?php } ?>

    }


    function delete_visit(id) {
        show_loader();
        $.ajax({
            type: "POST",
            url: admin_url + "leads/delete_visit",
            data: {
                id: id
            },
            dataType: "JSON",
            cache: false,
            success: function(data) {
                hide_loader();
                if (data.success) {
                    alert_float('success', data.message);
                    filter_data();
                } else {
                    alert_float('danger', data.message);
                }

            }
        }); // you have missed this bracket
        return false;
    }
</script>