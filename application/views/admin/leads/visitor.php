<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
array_unshift($location, array());
$category = [];
$category[] = array("id" => "", "name" => "");
$category[] = array("id" => "-1", "name" => "Previous");
$category[] = array("id" => "1", "name" => "Today");
$category[] = array("id" => "2", "name" => "Upcoming");
?>
<style>
    a {
        cursor: pointer;
    }

    .margin-top {
        margin-top: 10px;
    }

    .border-card {
        margin-bottom: 10px;
        text-align: center;
        padding: 5px 0px;
        box-shadow: 1px 1px 6px 1px lightgray;
    }

    .border-card h3.bold {
        margin: 5px !important;
    }

    .border-card span {
        margin: 5px !important;
    }

    #leadSum h4.no-margin {
        font-size: 18px;
        padding: 10px 0px;
    }

    #leadSum .panel-body {
        border-radius: 0px;
        padding: 10px 0px;
    }

    table .dropdown-menu-right {
        right: auto !important
    }
</style>
<script>
    var role_type = "<?= !empty($role) ? $role : 0 ?>";
</script>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">


                        <div class="col-md-4">
                            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="Lead Status" data-placement="bottom" onclick="slideToggle('.leads-overview');  summary(1); return false;"><i class="fa fa-bar-chart"></i></a>


                        </div>

                        <div class="clearfix"></div>
                        <div class=" hide leads-overview">
                            <hr class="hr-panel-heading" />
                            <div id="leadSum"></div>
                        </div>

                        <div class="clearfix"></div>
                        <hr>
                        <div class="row" id="leads-table ">
                            <div id="filterArea" class="col-md-12 hidden-xs">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="bold"><?php echo _l('filter_by'); ?></p>
                                    </div>


                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('status[]', $visitor_status, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Visit Status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>



                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('location[]', $location, array('id', 'name'), '', [], array('data-width' => '100%', 'multiple' => true, 'data-none-selected-text' => _l('Location'), 'data-actions-box' => true), array(), 'no-mbot', '', false,  'location');
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('type[]', $visitor_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Visit Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('attendee[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Attendee'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('view_status[]', $statuses, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Lead status', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, '');
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('lead_type[]', $lead_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Lead Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <?php
                                        echo render_select('source_type[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Source Type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                        ?>
                                    </div>




                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From Visitor Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To Visitor Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2 leads-filter-column margin-top mb-5">
                                        <?php echo render_select('assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'assigned'); ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top mb-5">
                                        <?php echo render_select('category', $category, array('id', array('name')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Schedule'), 'data-actions-box' => true), array(), 'no-mbot', '', false, 'category'); ?>
                                    </div>

                                    <div class="col-md-2 leads-filter-column margin-top">
                                        <div class="form-group">
                                            <input type="text" class="form-control datepicker" name="last_update_date" id="last_update_date" placeholder="Last Update Date" autocomplete="off">
                                        </div>
                                    </div>


                                    <div class="col-md-12 text-center leads-filter-column margin-top">
                                        <div class="form-group pull-right">
                                            <button type="button" class="btn btn-primary" onclick="filter_data();" id="apply_filter">Apply Filter</button>
                                            <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <h4>Request Generate</h4>
                        <hr>

                        <?php
                        render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", "Update Count", "Duration", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type'), "Lead Status", "Lead Source", "Fb Form Name", "Created Date", "Updated Date", "connected date"), 'lead-visitor-genrate-table');
                        ?>
                        <?php if (!is_admin()) { ?>
                            <h4>Request Received</h4>
                            <hr>

                            <?php
                            render_datatable(array("Status", _l('Date Of Visit'), _l('Student Name'), "Contact no.", "Update Count", "Duration", _l('Place of Visit'), _l('Visit Type'), _l('Attendee'), "Assignee", _l('Lead type'), "Lead Status", "Lead Source"), 'lead-visitor-request-table');
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
        source_type: "[name='source_type[]']",
        lead_status: "[name='view_status[]']",
        from_date: "[name='from_date']",
        to_date: "[name='to_date']",
        assigned: "[name='assigned[]']",
        category: "[name='category']",
        last_update_date: "[name='last_update_date']",

    };

    function refresh_visitor_table() {
        initDataTable('.table-lead-visitor-genrate-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', r, [0, 'desc']);
        <?php if (!is_admin()) { ?>
            initDataTable('.table-lead-visitor-request-table', admin_url + 'leads/table_lead_visitor/1', 'undefined', 'undefined', r, [0, 'desc']);
        <?php } ?>
    }

    $(document).ready(function() {
        refresh_visitor_table();
        set_search_cities();
    })

    function filter_data() {

        let fromDate = $("input[name='from_date']").val().trim();
        let toDate = $("input[name='to_date']").val().trim();
        if (fromDate === "" && toDate === "") {} else {
            if (fromDate === "" || toDate === "") {
                if (fromDate === "") {
                    $("input[name='from_date']").focus();
                }
                if (toDate === "") {
                    $("input[name='to_date']").focus();
                }
                alert_float("danger", "Both From Date and To Date are required.");
                return false;
            }
        }

        $('.table-lead-visitor-genrate-table').DataTable().destroy();
        $('.table-lead-visitor-genrate-table tbody').empty();
        initDataTable('.table-lead-visitor-genrate-table', admin_url + 'leads/table_lead_visitor', 'undefined', 'undefined', r, [0, 'desc']);
        <?php if (!is_admin()) { ?>
            $('.table-lead-visitor-request-table').DataTable().destroy();
            $('.table-lead-visitor-request-table tbody').empty();
            initDataTable('.table-lead-visitor-request-table', admin_url + 'leads/table_lead_visitor/1', 'undefined', 'undefined', r, [0, 'desc']);
        <?php } ?>

        $("#leadSum").html('');
        $(".leads-overview").hide();

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

    function set_search_cities() {

        // Bind event to search input ONLY inside #visitor_location selectpicker
        $('#location').parent().find('.bs-searchbox input').on('input', function() {
            let searchQuery = $(this).val();

            if (searchQuery.length > 2) { // Start AJAX after 3+ characters
                let formData = new FormData(); // Correct FormData initialization

                formData.append("csrf_token_name", csrfData.hash);
                formData.append("value", searchQuery); // Corrected `.val()` issue

                $.ajax({
                    url: "<?php echo base_url('admin/leads/search_cities'); ?>", // Replace with actual API URL
                    method: "POST", // FormData requires POST (not GET)
                    data: formData,
                    processData: false, // Prevent jQuery from transforming FormData
                    contentType: false, // Ensure correct Content-Type is set for FormData
                    dataType: "JSON",
                    success: function(response) { // 'data' is already parsed as JSON

                        $('#location').empty(); // Clear old options
                        let data = response.data;
                        if (data.length > 0) {
                            $.each(data, function(index, item) {
                                $('#location').append(`<option value="${item.id}">${item.name}</option>`);
                            });
                        } else {
                            $('#location').append('<option disabled>No results found</option>'); // Handle no results case
                        }

                        $('#location').selectpicker('refresh'); // Refresh selectpicker
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: ", error);
                    }
                });

            }
        });

    }

    let xhr = null;

    function summary(status = "") {
        show_loader();

        const requestData = {};
        if (r && typeof r === "object") {
            Object.keys(r).forEach(key => {
                const value = r[key];

                if (typeof value === "string") {
                    // Use jQuery to get input value
                    requestData[key] = $(value).val();
                } else if (typeof value === "object" && value !== null) {
                    // Serialize nested object
                    requestData[key] = JSON.stringify(value);
                }
            });
        }

        // Add CSRF protection if available
        if (typeof csrfData !== "undefined" && csrfData.token_name && csrfData.hash) {
            requestData[csrfData.token_name] = csrfData.hash;
        }

        // Abort any ongoing request to prevent race conditions
        if (xhr !== null) {
            xhr.abort();
        }
        if ($("#leadSum").html() === "") {
            xhr = $.ajax({
                type: "POST",
                url: `${admin_url}leads/visitor_lead_summary_filter`,
                data: requestData,
                dataType: "JSON",
                cache: false,
                success: function(data) {

                    if ($("#leadSum").html() === "" && data.status) {
                        $("#leadSum").html(data.status);
                    }
                    hide_loader();
                },
                error: function() {
                    hide_loader();
                    console.error("An error occurred while fetching the summary.");
                }
            });
        }
        hide_loader();
        return false;
    }

    function visit_lead_mark_as(e, t) {
        var a = {};
        a.status = e, a.id = t, $.post(admin_url + "leads/update_visit_lead_status", a).done(function(e) {
            filter_data();
        })
    }
</script>