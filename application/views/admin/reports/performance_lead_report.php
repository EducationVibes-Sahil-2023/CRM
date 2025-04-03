<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$staff_details = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row();
// $role = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row()->role;
$role = $staff_details->role;
$staff_department = $staff_details->department;
$fb_query = $this->db->select('DISTINCT(website) as fb_name')->where("website!=", "")->get('leads');
$facebook_names = $fb_query->result_array();
$source_marketing = array(array("name" => "Google Ads"), array("name" => "Youtube"), array("name" => "Meta"), array("name" => "Organic"), array("name" => "Direct"));
$date_type = array(array("name" => "Daily"), array("name" => "Week"), array("name" => "Month"), array("name" => "Year"));

$source_type = $this->leads_model->get_source();

$marketing_type =  $this->leads_model->get_marketing_type();
$performance_array = array_column($marketing_type, null, 'id');

$conversion_type = $this->leads_model->get_conversion_type();
$conversion_type_ = array_column($conversion_type, null, "id");

$staff_list     = $this->leads_model->get_staff_list();
$staff_list = array_column($staff_list, 'staff_name', "staffid");

$status_list = $this->leads_model->get_status();
$status_list_ = array_column($status_list, null, "id");
$tbllead_performance_column = $this->leads_model->tbllead_performance_column();
$selected_performance_column = array_slice(array_column($tbllead_performance_column, "id"), 0, 5);

?>
<style>
    span.show-persentage {
        float: right;
        font-size: 15px;
    }
</style>


<div id="wrapper">

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- <form action="<?= base_url("admin/reports/leads_reports_generate") ?>" method="POST"> -->
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="row ">
                            <div class="col-md-2 leads-filter-column filter_reset ">
                                <?php echo render_select('column_show[]', $tbllead_performance_column, array('id', 'label_name'), '', $selected_performance_column, array('data-width' => '100%', 'data-none-selected-text' => 'Show Column', 'multiple' => true, 'data-actions-box' => true, 'selected'), array(), 'no-mbot', '', false, 'column_show'); ?>
                            </div>

                            <?php if (has_permission('leads', '', 'view')) { ?>
                                <div class="col-md-2 leads-filter-column filter_reset">
                                    <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>
                                </div>
                            <?php } ?>
                            <div class="col-md-2 leads-filter-column">
                                <?php
                                $selected = array();
                                echo '<div id="leads-filter-status">';
                                echo render_select('view_status[]', $status, array('id', 'name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_status');
                                echo '</div>';
                                ?>
                            </div>

                            <div class="col-md-2 leads-filter-column">
                                <?php
                                echo '<div id="leads-filter-source">';
                                echo render_select('view_source[]', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_source");
                                echo '</div>';
                                ?>
                            </div>

                            <div class="col-md-2 leads-filter-column hide_show_utm hide">
                                <?php
                                echo '<div id="leads-filter-campaign">';
                                echo render_select('view_campaign[]', "", "", '', '', array('data-width' => '100%', 'data-none-selected-text' => 'Campaign Name', 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_campaign");
                                echo '</div>';
                                ?>
                            </div>
                            <div class="col-md-2 leads-filter-column hide_show_utm hide">
                                <?php
                                echo '<div id="leads-filter-adsset">';
                                echo render_select('view_adsset[]', "", "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Ads Set Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_adsset");
                                echo '</div>';
                                ?>
                            </div>
                            <div class="col-md-2 leads-filter-column hide_show_utm hide">
                                <?php
                                echo '<div id="leads-filter-ads">';
                                echo render_select('view_ads[]', "", "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Ads Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_ads");
                                echo '</div>';
                                ?>
                            </div>
                            <div class="col-md-2 leads-filter-column hide_show_utm hide">
                                <?php
                                echo '<div id="leads-filter-form">';
                                echo render_select('view_form[]', "", "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Form Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_form");
                                echo '</div>';
                                ?>
                            </div>
                            <div class="col-md-2 leads-filter-column hide_show_utm hide">
                                <?php
                                echo '<div id="leads-filter-term">';
                                echo render_select('view_term[]', "", "", '', '', array('data-width' => '100%', 'data-none-selected-text' => "Search Term", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_term");
                                echo '</div>';
                                ?>
                            </div>

                            <?php if (is_admin()) { ?>
                                <div class="col-md-2 leads-filter-column filter_reset">
                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('department[]', $department, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Department'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "department");
                                    echo '</div>';
                                    ?>
                                </div>
                            <?php } ?>
                            <?php if ($role == 3 || is_admin()) { ?>


                                <div class="col-md-2 leads-filter-column filter_reset">
                                    <?php
                                    echo '<div id="leads-filter-source">';
                                    echo render_select('location[]', $location, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Location'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "location");
                                    echo '</div>';
                                    ?>
                                </div>
                            <?php } ?>

                            <div class="col-md-2 leads-filter-column">
                                <?php
                                echo '<div id="leads-filter-source">';
                                echo render_select('lead_type[]', $type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('lead_import_type'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "lead_type");
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
                            <div class="col-md-2 leads-filter-column">
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="up_from_date" id="up_from_date" placeholder="From Update Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2 leads-filter-column">
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="up_to_date" id="up_to_date" placeholder="To Update Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2 leads-filter-column">
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="assign_from_date" id="assign_from_date" placeholder="From Assignation Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2 leads-filter-column">
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="assign_to_date" id="assign_to_date" placeholder="To Assignation Date" autocomplete="off">
                                </div>
                            </div>



                            <div class="col-md-6 leads-filter-column">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>
                                    <!-- <button class="btn btn-primary" id="apply_filter">Apply Filter</button> -->
                                    <button class="btn btn-primary" onclick="window.location.reload();">Reset</button>
                                    <!-- <button class="btn btn-xs btn-danger hide-btn-response" onclick="generatePDF()" id="generate_pdf" style="display:none;"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Generate Pdf</button> -->
                                    <?php if (is_admin()) { ?>
                                        <button class="btn btn-xs btn-success hide-btn-response" onclick="RunExcelJSExport()" id="generate_excel" style="display:none;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export to Excel</button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <!-- </form> -->
                        <div class="row ">
                            <div class="col-md-8">
                                <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview');  summary(1); return false;"><i class="fa fa-bar-chart"></i></a>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                        <div class="row hide leads-overview">
                            <hr class="hr-panel-heading" />
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                            </div>
                            <div id="leadSum">

                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-md-12">

                </div> -->
                </div>
                <div class="col-md-12 row">
                    <div class="panel_s">
                        <div class="panel-body">
                            <table id="dynamicTable" class="table table-lead-performance-table " style="width:100%">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php init_tail(); ?>
    <!-- DataTable CSS -->
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css"> -->

    <!-- DataTable JS -->
    <!-- <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script> -->
    <style>
        .leads-filter-column {
            margin-bottom: 1rem;
        }

        table.dataTable thead .sorting {
            background-image: unset !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling for touch devices */
        }

        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }
    </style>
    <script>
        var lead_performance_table;
        var performance_related_dropdown = <?= !empty($performance_related_dropdown) ? json_encode($performance_related_dropdown, true) : [] ?>;
        // console.log(performance_related_dropdown);
        var columnHeaders = [];
        var column_names = {}; // Object to store column name mappings
        var r = {
            custom_view: "[name='custom_view']",
            assigned: "[name='view_assigned[]']",
            status: "[name='view_status[]']",
            course: "[name='view_course[]']",
            degree: "[name='view_degree[]']",
            source: "[name='view_source[]']",
            lead_type: "[name='lead_type[]']",
            from_date: "[name='from_date']",
            to_date: "[name='to_date']",
            up_from_date: "[name='up_from_date']",
            up_to_date: "[name='up_to_date']",
            up_from_date_call: "[name='up_from_date_call']",
            up_to_date_call: "[name='up_to_date_call']",
            followup_from_date: "[name='followup_from_date']",
            followup_to_date: "[name='followup_to_date']",
            assign_from_date: "[name='assign_from_date']",
            assign_to_date: "[name='assign_to_date']",
            neet_score: "[name='neet_score']",
            show_update_counts: "[name='show_update_counts']:checked",
            update_count_min: "[name='update_count_min']",
            update_count_max: "[name='update_count_max']",
            last_contact_date: "[name='last_contact_date']",
            last_update_date: "[name='last_update_date']",
            utm_campaign_name: "[name='view_campaign[]']",
            utm_ads_set_name: "[name='view_adsset[]']",
            utm_ads_name: "[name='view_ads[]']",
            utm_form_name: "[name='view_form[]']",
            utm_term: "[name='view_term[]']",
            columnNames: "[name='column_show[]']", // Include dynamically generated column names
            department: "[name='department[]']",
            location: "[name='location[]']", // Include dynamically generated column names
        };
        // var tbllead_performance_column = <?= json_encode($tbllead_performance_column, true) ?>;
        var tbllead_performance_column = [];
        $("#column_show").on("change", function() {
            tbllead_performance_column = [];
            // Get the selected values using `selectpicker`
            var selectedValues = $(this).selectpicker('val');

            if (selectedValues && selectedValues.length > 0) {
                // Initialize an array to hold the objects for each selected value

                // Iterate over the selected values
                selectedValues.forEach((value) => {
                    // Find the corresponding label/text for the current value
                    const selectedLabel = $(this).find(`option[value="${value}"]`).text();

                    // Construct the object for the current selection
                    const columnObject = {
                        tbl_column_name: value, // Set the column name
                        label_name: selectedLabel // Set the label name
                    };

                    // Add the constructed object to the array
                    tbllead_performance_column.push(columnObject);
                });

                // Log the array of created objects
                console.log("Created Objects:", tbllead_performance_column);

                // Use `tbllead_performance_column` as needed (e.g., send via AJAX or update the UI)
            } else {
                console.log("No value selected.");
            }
        });

        $("#column_show").each(function() {
            // Ensure the element is processed correctly
            if ($(this).is(":input")) {
                // Get the selected values using `selectpicker`
                var selectedValues = $(this).selectpicker('val');

                if (selectedValues && selectedValues.length > 0) {
                    // Initialize an array to hold the objects for each selected value

                    // Iterate over the selected values
                    selectedValues.forEach((value) => {
                        // Find the corresponding label/text for the current value
                        const selectedLabel = $(this).find(`option[value="${value}"]`).text();

                        // Construct the object for the current selection
                        const columnObject = {
                            tbl_column_name: value, // Set the column name
                            label_name: selectedLabel // Set the label name
                        };

                        // Add the constructed object to the array
                        tbllead_performance_column.push(columnObject);
                    });

                    // Log the array of created objects
                    console.log("Created Objects:", tbllead_performance_column);

                    // Use `tbllead_performance_column` as needed (e.g., send via AJAX or update the UI)
                } else {
                    console.log("No value selected.");
                }
            }
        });


        function set_column_table() {

            if (Array.isArray(tbllead_performance_column) && tbllead_performance_column.length > 0) {

                tbllead_performance_column.forEach(column => {
                    // Get the label, fallback to column name if label is empty
                    let label = column.label_name && column.label_name.trim() !== "" ?
                        column.label_name :
                        column.tbl_column_name.replace(".", "_");

                    // Add to column_names object
                    column_names[column.tbl_column_name] = label.replace(/ /g, "_");

                    // Add to columnHeaders array
                    columnHeaders.push({
                        title: label,
                        data: label.toLowerCase().replace(/ /g, "_") // Assuming lowercase data keys
                    });
                });
            }

            // Populate the <thead> of the table only if columnHeaders has entries
            if (columnHeaders.length > 0) {
                let thead = "<tr>";
                columnHeaders.forEach(header => {
                    thead += "<th>" + header.title + "</th>";
                });
                thead += "</tr>";
                $("#dynamicTable thead").html(thead); // Add the generated HTML to the table's <thead>
            } else {
                console.warn("No column headers available to populate the table.");
            }


        }

        set_column_table();
        // Define configuration object


        $(document).ready(function() {
            // Define column headers dynamically
            $('.table-lead-performance-table').DataTable().destroy();
            lead_performance_table = initDataTable('.table-lead-performance-table', admin_url + 'leads/lead_performance_table', 'undefined', 'undefined', r, [0, 'desc']);
        });

        var filter_data;
        $('#apply_filter').on('click', async function() {


            var selectedValues = $("#column_show").selectpicker('val');
            if (selectedValues.length < 3) {
                alert("Select min 3 columns");
                return false;
            }


            var from_date = document.getElementById("from_date").value;
            var to_date = document.getElementById("to_date").value;
            var assign_from_date = document.getElementById("assign_from_date").value;
            var assign_to_date = document.getElementById("assign_to_date").value;
            var up_from_date = document.getElementById("up_from_date").value;
            var up_to_date = document.getElementById("up_to_date").value;

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

            if (assign_to_date != '') {
                if (assign_from_date == '') {
                    $("#assign_from_date").focus();
                    return false;
                }
            }

            if (assign_from_date != '') {
                if (assign_to_date == '') {
                    $("#assign_to_date").focus();
                    return false;
                }
            }


            if (up_to_date != '') {
                if (up_from_date == '') {
                    $("#up_from_date").focus();
                    return false;
                }
            }

            if (up_from_date != '') {
                if (up_to_date == '') {
                    $("#up_to_date").focus();
                    return false;
                }
            }



            $('.table-lead-performance-table').DataTable().destroy();
            $('.table-lead-performance-table tbody').empty();

            columnHeaders = [];
            column_names = [];
            await set_column_table();
            filter_data = r;
            $("#leadSum").html('');
            $(".leads-overview").hide();

            initDataTable('.table-lead-performance-table', admin_url + 'leads/lead_performance_table', 'undefined', 'undefined', r, [0, 'desc']);
        });

        var campaign_names = [];
        var adsset_names = [];
        var ads_names = [];
        var form = [];
        var term = [];


        function set_ads_name(source_id, adsset_name) {
            // Retrieve ads set data for the given source ID
            let ads_data = performance_related_dropdown[source_id]["ads_name"].split(",");
            let source_name = performance_related_dropdown[source_id]["source_name"];
            if (ads_data.length > 0) {
                let options = "";
                ads_data.forEach(adsset => {
                    let adsset_parts = adsset.split("##");
                    if (
                        adsset_parts.length === 2 &&
                        !ads_names.includes(adsset_parts[1].trim()) &&
                        adsset_name === adsset_parts[0].trim()
                    ) {
                        console.log("okk");
                        ads_names.push(adsset_parts[1].trim());
                        options += `<option value="${adsset_parts[1].trim()}">${adsset_parts[1].trim()}</option>`;
                    }
                });
                console.log(options);
                // Append options to the ads set dropdown and refresh
                $("#view_ads").append(options).selectpicker("refresh");
            }
        }


        function set_adsset_name(source_id, campaign) {
            // Retrieve ads set data for the given source ID
            let adsset_data = performance_related_dropdown[source_id]["ads_set_name"].split(",");
            let source_name = performance_related_dropdown[source_id]["source_name"];

            if (adsset_data.length > 0) {
                let options = "";
                adsset_data.forEach(adsset => {
                    let adsset_parts = adsset.split("##");

                    // Check if the campaign matches and avoid duplicate ads set names
                    if (
                        adsset_parts.length === 2 &&
                        !adsset_names.includes(adsset_parts[1].trim()) &&
                        campaign === adsset_parts[0]
                    ) {
                        adsset_names.push(adsset_parts[1].trim());
                        options += `<option data-source="${source_id}"  value="${adsset_parts[1].trim()}">${adsset_parts[1].trim()}</option>`;
                    }
                });
                // set_ads_name(source_id, adsset_parts[1].trim());
                // Append options to the ads set dropdown and refresh
                $("#view_adsset").append(options).selectpicker("refresh");
            }
        }

        function set_campaign(source_id) {
            // Retrieve campaign data for the given source ID
            let campaign_data = performance_related_dropdown[source_id]["campaign_name"].split(",");
            let source_name = performance_related_dropdown[source_id]["source_name"];

            if (campaign_data.length > 0) {
                let options = "";
                campaign_data.forEach(campaign => {
                    // Avoid duplicate campaign names
                    if (!campaign_names.includes(campaign.trim())) {
                        campaign_names.push(campaign.trim());
                        options += `<option data-source="${source_id}" value="${campaign.trim()}">${campaign.trim()}</option>`;
                    }

                    // Set ads set names for the campaign
                    // set_adsset_name(source_id, campaign.trim());
                });

                // Append options to the campaign dropdown and refresh
                $("#view_campaign").append(options).selectpicker("refresh");
            }
        }

        $("#view_campaign").on("change", function() {
            adsset_names = [];
            $("#view_adsset").empty().selectpicker("refresh");
            // Get all selected options
            let selectedOptions = $(this).find(":selected");

            // Loop through each selected option
            selectedOptions.each(function() {
                // Get the value of the current option
                let campaign = $(this).val();

                // Get the data-source attribute of the current option
                let source_id = $(this).attr("data-source");

                // Pass the values one by one to the function
                set_adsset_name(source_id, campaign.trim());

            });
        });

        $("#view_adsset").on("change", function() {
            ads_names = [];
            $("#view_ads").empty().selectpicker("refresh");
            // Get all selected options
            let selectedOptions = $(this).find(":selected");

            // Loop through each selected option
            selectedOptions.each(function() {
                // Get the value of the current option
                let adsset = $(this).val();

                // Get the data-source attribute of the current option
                let source_id = $(this).attr("data-source");

                // Pass the values one by one to the function
                set_ads_name(source_id, adsset.trim());

            });
        });


        function set_form(source_id) {
            // Retrieve campaign data for the given source ID
            let form_data = performance_related_dropdown[source_id]["form_name"].split(",");

            if (form_data.length > 0) {
                let options = "";
                form_data.forEach(form_name => {
                    // Avoid duplicate form_name names
                    if (!form.includes(form_name.trim())) {
                        form.push(form_name.trim());
                        options += `<option data-source="${source_id}" value="${form_name.trim()}">${form_name.trim()}</option>`;
                    }

                    // Set ads set names for the campaign
                    // set_adsset_name(source_id, campaign.trim());
                });

                // Append options to the campaign dropdown and refresh
                $("#view_form").append(options).selectpicker("refresh");
            }
        }

        function set_term(source_id) {
            // Retrieve campaign data for the given source ID
            let term_data = performance_related_dropdown[source_id]["term"].split(",");

            if (term_data.length > 0) {
                let options = "";
                term_data.forEach(term_name => {
                    // Avoid duplicate term_name names
                    if (!term.includes(term_name.trim())) {
                        term.push(term_name.trim());
                        options += `<option data-source="${source_id}" value="${term_name.trim()}">${term_name.trim()}</option>`;
                    }

                    // Set ads set names for the campaign
                    // set_adsset_name(source_id, campaign.trim());
                });

                // Append options to the campaign dropdown and refresh
                $("#view_term").append(options).selectpicker("refresh");
            }
        }


        async function setdropdown_utms(source_id) {
            await set_campaign(source_id);
            await set_form(source_id);
            await set_term(source_id);
        }

        $("#view_source").on("change", function() {
            campaign_names = []; // Reset campaign names on change
            adsset_names = []; // Reset ads set names on change
            ads_names = []; // Reset ads set names on change
            form = []; // Reset ads set names on change
            term = []; // Reset ads set names on change
            $("#view_campaign").empty().selectpicker("refresh");
            $("#view_adsset").empty().selectpicker("refresh");
            $("#view_ads").empty().selectpicker("refresh");
            $("#view_form").empty().selectpicker("refresh");
            $("#view_term").empty().selectpicker("refresh");
            // Get selected values using selectpicker
            let selectedValues = $(this).selectpicker("val");
            if (Array.isArray(selectedValues) && selectedValues.length > 0) {
                // Loop through the selected source IDs and call the function
                selectedValues.forEach(source => {
                    setdropdown_utms(source);
                });
                $(".hide_show_utm").removeClass("hide");

            } else {
                $(".hide_show_utm").addClass("hide");
            }

            // Clear previous campaign and ads set dropdown options
            // $("#view_campaign").empty().selectpicker("refresh");
            // $("#view_adsset").empty().selectpicker("refresh");
        });

        var xhr;

        function summary(status = "") {
            let ajax_data = {}; // Initialize an empty object
            if ($("#leadSum").html().trim() != '') {
                return false;
            }
            if (filter_data && typeof filter_data === "object") {
                Object.keys(filter_data).forEach(key => {
                    // Check if the value in `r[key]` is a string
                    if (typeof filter_data[key] === "string") {
                        // Assign value from DOM element corresponding to the selector in r[key]
                        ajax_data[key] = $(filter_data[key]).val(); // Get value of the element
                    }
                    // Check if `r[key]` is an object
                    else if (typeof filter_data[key] === "object" && filter_data[key] !== null) {
                        // Ensure ajax_data[key] is initialized as an object
                        ajax_data[key] = ajax_data[key] || {};
                        // Assign stringified object for nested values
                        ajax_data[key] = JSON.stringify(filter_data[key]);
                    }
                });

                // Debugging output
                console.log("Populated Object ajax_data:", ajax_data);
            }

            ajax_data["show_lead_status"] = 1;
            ajax_data["utm_status"] = 1;
            ajax_data["show_marketing_status"] = 1;


            if (xhr != null) {
                xhr.abort();
            }
            xhr = $.ajax({
                type: "POST",
                url: admin_url + "leads/lead_summary_filter",
                data: ajax_data,
                dataType: "JSON",
                cache: false,
                success: function(data) {

                    //alert(data);  //as a debugging message.
                    if ($("#leadSum").html().trim() == '' && data.status != '') {
                        $("#leadSum").html('');
                        $("#leadSum").html(data.status);
                    }
                }
            }); // you have missed this bracket
            return false;
        }
    </script>