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


?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@3.4.0/dist/exceljs.min.js"></script>
<link href="<?= base_url("assets/css/uislider.css") ?>" rel="stylesheet">
<script src="<?= base_url("assets/js/uislider.js") ?>"></script>
<style>
    .mb-3 {
        margin-bottom: 5px;
    }

    .report-data {
        /* padding: 10px !important; */
        /* box-shadow: 0px 0px 10px lightgray; */
        /* margin-top: 20px; */

        box-shadow: 2px 2px 5px 1px lightgray;
        ;
        padding: 15px;
        margin: 15px 0px;

    }

    .leads-filter-column {
        margin-bottom: 5px;
    }

    /* .leadSum .panel_s .panel-body {
        min-height: 400px;
    } */

    .leadSum .panel_s .panel-body {
        /* min-height: 360px; */
        padding: 10px !important;
    }

    .border-right h3 {
        margin: 10px 0px;
    }

    .leadSum {
        font-size: 14px;
    }

    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }

    .panel_s {
        margin-bottom: 15px !important;
    }

    .pdf_generate>h3 {
        margin-top: 0px !important;
    }

    hr {
        margin: 5px 0px !important;
    }


    .btn-toggle {
        margin: 0 7rem;
        padding: 0;
        position: relative;
        border: none;
        height: 1.5rem;
        width: 3rem;
        border-radius: 1.5rem;
        color: #6b7381;
        background: #bdc1c8;
    }

    .btn-toggle:focus,
    .btn-toggle.focus,
    .btn-toggle:focus.active,
    .btn-toggle.focus.active {
        outline: none;
    }

    .btn-toggle:before,
    .btn-toggle:after {
        line-height: 1.5rem;
        width: 4rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        position: absolute;
        bottom: 0;
        transition: opacity 0.25s;
    }

    .btn-toggle:before {
        content: 'Conversion';
        left: -7rem;
    }

    .btn-toggle:after {
        content: 'Marketing';
        right: -5rem;
        opacity: 0.5;
    }

    .btn-toggle:before,
    .btn-toggle:after {
        color: #6b7381;
    }

    .btn-toggle.active {
        background-color: #29b5a8;
    }

    .btn-toggle>.handle {
        position: absolute;
        top: 0.1875rem;
        left: 0.1875rem;
        width: 1.125rem;
        height: 1.125rem;
        border-radius: 1.125rem;
        background: #fff;
        transition: left 0.25s;
    }

    .btn-toggle.active {
        transition: background-color 0.25s;
    }

    .btn-toggle.active>.handle {
        left: 1.6875rem;
        transition: left 0.25s;
    }

    .btn-toggle.active:before {
        opacity: 0.5;
    }

    .btn-toggle.active:after {
        opacity: 1;
    }

    hr.hr-3 {
        border: 0;
        height: 0;
        border-top: 1px solid #8c8c8c;

    }

    .scroll-div {
        padding: 0px 50px;
        display: -webkit-inline-box;
        margin: 10px 10px;
        width: 98%;
        overflow-x: auto;
        overflow-y: auto;
        white-space: nowrap;
    }


    .show-daily-update {
        padding: 15px 10px;
        line-height: 10px;
        border-radius: 10px;
        box-shadow: 0px 1px 6px lightgrey;
        margin: 5px;
        margin-bottom: 25px;
        background: lightgray;
        font-weight: 500;
    }

    .border-right h3,
    h4,
    .border-right span {
        font-size: 16px !important;
    }

    .leadSum .panel_s .panel-body,
    .leadSum .panel_s,
    .leadSum .report-data {
        border: unset !important;
        border-radius: unset !important;
        /* box-shadow: unset !important; */
        padding: 10px !important;
    }

    .leadSum .border-right {
        background: #fff;
        border: 1px solid #dce1ef;
        border-radius: 4px;
        padding: 5px 10px;
        position: relative;
        margin: 3px;
        width: 19%;
        box-shadow: 1px 1px 3px lightgray;

    }

    .leadSum .col-md-6 {
        padding: 0px !important;
        width: 100%;
        /* border-right: 1px solid black; */
    }

    .col-md-12.parrent-div {
        padding: 0px !important;
    }

    .assignation-total h4 {
        margin: 5px 0px;

    }

    .leadSum .col-md-6 {
        width: 100% !important;
        padding: 0px;
        margin-bottom: 10px;

    }

    .assignation-total .panel-body {
        padding: 5px 10px;
    }

    .leadSum .parrent-div .col-md-12 {
        padding: 0px !important;
    }

    .leadSum .col-md-12.border-right {
        width: 100%;

    }

    span.show-persentage {
        float: right;
    }

    [class^="leads-overview-"]::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE and Edge */
    [class^="leads-overview-"] {
        -ms-overflow-style: none;
    }

    /* Hide scrollbar for Firefox */
    [class^="leads-overview-"] {
        scrollbar-width: none;
    }

    div#show_hide_staff_list {

        margin-top: 20px;
    }

    div#show_hide_staff_list .leadSum {
        padding: 0px;
        border: none;
    }

    .marketing-type {
        width: 16% !important;
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
                            <?php if (has_permission('leads', '', 'view')) { ?>
                                <div class="col-md-2 leads-filter-column filter_reset">
                                    <?php //echo render_select('view_assigned',$staff,array('staffid',array('firstname','lastname')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_dt_assigned')),array(),'no-mbot'); 
                                    ?>
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
                            <div class="col-md-2 leads-filter-column hide_show_35 hide">
                                <?php
                                $selected = array();
                                echo '<div id="leads-filter-status">';
                                echo render_select('view_facebook_names[]', $facebook_names, array('fb_name', 'fb_name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('Facebook Names'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_facebook_names');
                                echo '</div>';
                                ?>
                            </div>

                            <div class="col-md-2 leads-filter-column hide_show_39 hide">
                                <?php
                                $selected = array();
                                echo '<div id="leads-filter-status">';
                                echo render_select('view_source_marketing[]', $source_marketing, array('name', 'name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('Source Marketing'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_source_marketing');
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

                            <div class="col-md-2 leads-filter-column">
                                <div class="form-group">
                                    <?php
                                    echo '<div id="leads-daily-filter">';
                                    echo render_select('date_type', $date_type, array('name', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Date Type')), array(), 'no-mbot', '', false, "date_type");
                                    echo '</div>';
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-6 leads-filter-column">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="apply_filter" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Processing ">Apply Filter</button>
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
                        <div class="">

                        </div>
                    </div>
                    <!-- <div class="col-md-12">

                </div> -->
                </div>
                <div class="col-md-12 row">
                    <div class="panel_s">
                        <div class="" id="pdf_generate">
                            <div class="panel-body">
                                <h3>Report Generate</h3>

                                <div class="col-md-12 row">
                                    <a href="#" class="btn btn-default btn-with-tooltip hide-graph hide-graph-calls hide" data-toggle="tooltip" data-title="<?php echo _l('Calls Leads Chart'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview-calls'); set_call_filter_data(); return false;">Show Calls Chart <i class="fa fa-bar-chart"></i></a>
                                    <div class="clearfix"></div>
                                    <div class="row hide col-md-12 leads-overview-calls">
                                        <hr class="hr-panel-heading" />
                                        <div class="col-md-12">
                                            <h4 class="no-margin">Report Summary</h4>
                                        </div>
                                        <br>
                                        <br>
                                        <div id="leadSum">
                                            <div class="col-md-9 leads-filter-column">
                                                <label>Update Count Range <input type="checkbox" value="checked" style="display:none;" name="show_update_counts" value="1" id="show_update_counts" onclick="show_update_count_range(this)"> </label>
                                                <div id="rangeSlider"></div>
                                                <input type="hidden" id="update_count_min" onchange="set_slider()" name="update_count_min">
                                                <input type="hidden" id="update_count_max" onchange="set_slider()" name="update_count_max">
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <button type="button" class="btn btn-primary" id="apply_filter_update_count" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Processing ">Apply Filter</button>
                                            </div>
                                            <canvas id="canvas"></canvas>


                                        </div>
                                    </div>
                                    <br>
                                    <br>
                                    <a href="#" class="btn btn-default btn-with-tooltip hide-graph hide-graph-daily hide" data-toggle="tooltip" data-title="<?php echo _l('Calls Leads Chart'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview-calls-daily'); graph_represent_(); return false;">Show Date Wise Chart <i class="fa fa-bar-chart"></i></a>

                                    <div class="row hide col-md-12 leads-overview-calls-daily">
                                        <hr class="hr-panel-heading" />
                                        <div class="col-md-12">
                                            <h4 class="no-margin">Daily wise Report Summary</h4>
                                        </div>
                                        <br>
                                        <br>
                                        <div id="leadSum_daily">
                                            <canvas id="canvas_daily"></canvas>
                                        </div>
                                        <br>
                                        <br>
                                        <div id="leadSum_marketing">
                                            <canvas id="canvas_marketing"></canvas>
                                        </div>
                                        <br>
                                        <br>
                                        <div id="leadSum_conversion">
                                            <canvas id="canvas_conversion"></canvas>
                                        </div>
                                        <br>
                                        <br>
                                        <br>
                                    </div>
                                    <div id="show_hide_staff_list" class="hide">
                                        <h4 class="bold">Staff List</h4>
                                        <hr>
                                        <div id="total_staff_list" class="col-12 leadSum panel-body mt-3">
                                        </div>
                                    </div>
                                    <hr>
                                    <br>
                                    <br>
                                    <div class="total_staff_report hide leadSum">

                                    </div>
                                    <hr>

                                    <a href="#" class="btn btn-default btn-with-tooltip hide-graph hide-graph-leads hide" data-toggle="tooltip" data-title="<?php echo _l('Calls Leads Chart'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview-data'); return false;">Show Leads Chart <i class="fa fa-bar-chart"></i></a>
                                    <div class="row col-md-12  hide leads-overview-data">
                                        <h4>Total leads summary</h4>
                                        <hr>
                                        <canvas id="canvas_"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="leadSum report_list">
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php init_tail(); ?>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.0.2/chart.min.js"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script> -->
    <script>
        <?php
        $conversion_type_color = array_column($conversion_type, 'color', 'name');
        $marketing_type_color = array_column($marketing_type, 'color', 'name');

        ?>
        var conversion_type_color = <?= !empty($conversion_type_color) ? json_encode($conversion_type_color, true) : '' ?>;
        var marketing_type_color = <?= !empty($marketing_type_color) ? json_encode($marketing_type_color, true) : '' ?>;
        const numberFormat = '#,##0.00';
        // Convert all colors in conversion_type_color to rgba
        // for (let name in conversion_type_color) {
        //     if (conversion_type_color.hasOwnProperty(name)) {
        //         conversion_type_color[name] = hexToRgba(conversion_type_color[name]);
        //     }
        // }

        // console.log(conversion_type_color); // Output the converted colors



        var source_name = <?= !empty($sources) ? json_encode($sources, true) : '' ?>;
        var status_name = <?= !empty($status) ? json_encode($status, true) : '' ?>;
        var status_name_color = <?php echo !empty($status) ? json_encode(array_column($status, null, "name")) : '[]'; ?>;
        var conversion_type = <?= !empty($conversion_type) ? json_encode($conversion_type, true) : '' ?>;
        var marketing_type = <?= !empty($marketing_type) ? json_encode($marketing_type, true) : '' ?>;
        var excel_data_array = [];
        var excel_data_array_total = [];
        var conversion_total = [];
        var summary_daily_excel = [];
        const max_count = 30;
        const max = 30;
        var excel_array = [];

        // Initialize an object to store chart instances
        if (!window.myCharts) {
            window.myCharts = {};
        }


        function set_call_filter_data() {
            setTimeout(() => {
                if ($(".leads-overview-calls").is(":visible")) {
                    console.log("click call filter");
                    slider_data = true;
                    ajax_filter(1);

                }
            }, 1000); // Check every 2 seconds
        }



        // Function to create or update a chart
        function createOrUpdateChart(chartKey, ctx, config) {
            // Destroy the existing chart instance if it exists
            if (window.myCharts[chartKey]) {
                window.myCharts[chartKey].destroy();
            }

            // Create a new chart instance and store it in the object
            window.myCharts[chartKey] = new Chart(ctx, config);
        }
        // const workbook = new ExcelJS.Workbook();

        function parseConversionCounts(conversionCounts) {
            const counts = conversionCounts.split(', ').map(item => item.split(': '));
            const result = {};
            counts.forEach(([key, value]) => {
                result[key] = parseInt(value, 10);
            });
            return result;
        }

        function randomColor() {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            return `rgba(${r}, ${g}, ${b}, 0.5)`;
        }

        function hexToRgba(hex, alpha = 1) {
            // Remove the hash at the start if it's there
            hex = hex.replace(/^#/, '');

            // Parse the r, g, b values
            let r = parseInt(hex.substring(0, 2), 16);
            let g = parseInt(hex.substring(2, 4), 16);
            let b = parseInt(hex.substring(4, 6), 16);

            // Return the rgba string
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }



        function getWeekRange(yearWeek) {
            try {
                // Check if input is null or not a string
                if (!yearWeek || typeof yearWeek !== 'string') {
                    throw new Error("Invalid input: yearWeek must be a non-null string");
                }

                // Check if the input has the correct format
                if (yearWeek.length !== 6 || isNaN(yearWeek)) {
                    throw new Error("Invalid input format: yearWeek should be a 6-digit string in the format 'YYYYWW'");
                }

                // Parse the year and week number from the input
                const year = parseInt(yearWeek.slice(0, 4), 10);
                const week = parseInt(yearWeek.slice(4), 10);

                // Check if the year and week number are valid
                if (isNaN(year) || isNaN(week) || week < 1 || week > 53) {
                    throw new Error("Invalid year or week number");
                }

                // Create a date object set to January 1st of the given year
                const firstDayOfYear = new Date(year, 0, 1);

                // Calculate the day offset to get the first Monday of the year
                const dayOffset = (firstDayOfYear.getDay() <= 4 ? 0 : 7) + 1 - firstDayOfYear.getDay();

                // Calculate the start date of the given week
                const startDate = new Date(firstDayOfYear);
                startDate.setDate(firstDayOfYear.getDate() + dayOffset + (week - 1) * 7);

                // Calculate the end date of the given week
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);

                // Format the dates to 'YYYY-MM-DD'
                const formatDate = (date) => date.toISOString().slice(0, 10);

                return `${formatDate(startDate)} - ${formatDate(endDate)}`;
            } catch (error) {
                return `Error: ${error.message}`;
            }
        }





        var xhr = null;
        var slider_data = false;

        function show_update_count_range(obj) {
            if ($(obj).is(":checked")) {
                $("#rangeSlider").show();
                setMinMaxValues();
            } else {
                $("#rangeSlider").hide();

            }
        }

        function setMinMaxValues() {
            // Get the current values of the slider
            var currentValues = rangeSlider.noUiSlider.get();
            // max_count = 30;
            // Update the options with new min and max values
            rangeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,
                    'max': max_count
                },
                start: [0, max_count] // Preserve the current slider values
            });
        }

        function recreate_range_slider(max) {

            if (max != undefined && parseInt(max) != max_count) {
                // max_count =30;
                rangeSlider.noUiSlider.destroy();
                // max_count = parseInt(max);
                let min_ = document.getElementById("update_count_min").value;
                let max_ = document.getElementById("update_count_max").value;
                make_range_slider(min_, max_);
            }
        }
        // Initialize the range slider
        function make_range_slider(min = 0, max = 0) {
            var rangeSlider = document.getElementById('rangeSlider');

            noUiSlider.create(rangeSlider, {
                start: [min, max], // Initial values for min and max
                connect: true,
                tooltips: [true, true],
                format: {
                    to: function(value) {
                        return Math.round(value); // Round the tooltip values
                    },
                    from: function(value) {
                        return parseFloat(value); // Convert tooltip values to numbers
                    }
                },
                step: 1,
                range: {
                    'min': 0,
                    'max': max_count
                }
            });


            // Get handles for min and max sliders
            var sliderHandles = rangeSlider.getElementsByClassName('noUi-handle');
            var minSliderHandle = sliderHandles[0];
            var maxSliderHandle = sliderHandles[1];

            // Set event listeners for slider change
            rangeSlider.noUiSlider.on('update', function(values, handle) {
                var minValue = parseFloat(values[0]);
                var maxValue = parseFloat(values[1]);
                // Update the hidden input values
                document.getElementById('update_count_min').value = minValue;
                document.getElementById('update_count_max').value = maxValue;

            });

            rangeSlider.noUiSlider.on('change', function(values, handle) {


            });

            // Set event listeners for slider handle drag
            minSliderHandle.addEventListener('drag', function() {
                var minValue = parseFloat(rangeSlider.noUiSlider.get()[0]);
                rangeSlider.noUiSlider.set([minValue, null]);
            });

            maxSliderHandle.addEventListener('drag', function() {
                var maxValue = parseFloat(rangeSlider.noUiSlider.get()[1]);
                rangeSlider.noUiSlider.set([null, maxValue]);
            });
        }
        make_range_slider(0, max_count);

        var date_type = "";

        $("#apply_filter_update_count").click(function() {
            slider_data = true;
            ajax_filter(1);
        })


        var ajax_get_post_data = "";
        var excel_xhr;

        function RunExcelJSExport() {
            // Abort any ongoing Excel export AJAX request if present
            if (excel_xhr != null) {
                excel_xhr.abort();
            }

            // Check if ajax_get_post_data is not empty and add the excel_status parameter
            if (ajax_get_post_data != "") {
                if (Array.isArray(ajax_get_post_data)) {
                    ajax_get_post_data.push({
                        name: "excel_status",
                        value: 1
                    });
                } else if (typeof ajax_get_post_data === 'object') {
                    ajax_get_post_data["excel_status"] = 1;
                }
                show_loader();
                // Make the AJAX request
                excel_xhr = $.ajax({
                    type: "POST",
                    url: admin_url + "reports/lead_summary_filter",
                    data: ajax_get_post_data,
                    dataType: "JSON",
                    cache: false,
                    success: function(data) {
                        console.log("excel 1");
                        if (data.summary_daily_excel != undefined) {
                            // excel_data_array = data.excel_data;
                            summary_daily_excel = data.summary_daily_excel;
                        }
                        generate_excel_data(data)
                            .then(() => {
                                RunExcelJSExport__()
                                hide_loader();

                            })
                            .catch((error) => {
                                console.error("An error occurred while processing data:", error);
                            });
                    }
                });


                // Handle the successful response and run subsequent code
                excel_xhr.then(async function(data) {
                    try {
                        // Log the data received from the AJAX request
                        console.log("Excel export data:", data);

                        // Generate the Excel data using the response


                        // Continue with the next function in the process

                    } catch (error) {
                        // Handle any error that occurred in the generate_excel_data function
                        console.error("Error during Excel data generation:", error);
                    }
                }).catch(function(error) {
                    // Handle any error that occurred in the AJAX request
                    console.error("An error occurred:", error);
                });
            }
        }




        function RunExcelJSExport_() {
            var workbook = new ExcelJS.Workbook();
            Object.keys(excel_data_array).forEach(function(key) {
                let worksheet = workbook.addWorksheet(key);
                let excel_data = excel_data_array[key];
                // set up some data
                worksheet.getCell("A1").value = "Leads/Source";
                worksheet.getCell("A1").font = {
                    bold: true,
                };
                for (j = 1; j <= 1; j++) {
                    let index = 0;
                    for (let i = 1; i < (source_name.length); i++) {
                        // console.log(source_name);
                        if (source_name[index].name != undefined) {
                            // console.log(_getColumnLetter[i] + j);
                            worksheet.getCell(_getColumnLetter[i] + j).value = source_name[index].name;
                            worksheet.getCell(_getColumnLetter[i] + j).font = {
                                bold: true,
                                color: {
                                    argb: (source_name[index].color_name).replace("#", ""),
                                    size: 16
                                }
                            };

                        }
                        index++;
                    }
                }
                var index_type = 0;
                for (j = 2; j <= (status_name.length + 1); j++) {
                    if (source_name[index_type].name != undefined) {
                        worksheet.getCell("A" + j).value = status_name[index_type].name;
                        worksheet.getCell("A" + j).font = {
                            bold: true,
                            color: {
                                argb: (status_name[index_type].color).replace("#", ""),
                                size: 16
                            }
                        };

                    }
                    index_type++;
                }

                let index_upper = 0;
                for (j = 2; j <= (status_name.length + 1); j++) {
                    let index = 0;
                    for (let i = 1; i < (source_name.length); i++) {
                        if (source_name[index].name != undefined && status_name[index_upper].name != undefined) {
                            // worksheet.getCell(_getColumnLetter[i] + j).value = excel_data[0][status_name[index_upper].name + "_" + source_name[index].name].total;
                            let index_name = status_name[index_upper].name + "-" + source_name[index].name;
                            if (excel_data[index_name] != undefined) {
                                worksheet.getCell(_getColumnLetter[i] + j).value = Number(excel_data[index_name].total);
                                worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };
                            } else {
                                worksheet.getCell(_getColumnLetter[i] + j).value = 0;
                                worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };




                            }
                        }
                        index++;
                    }
                    index_upper++;
                }

                var con_index = (status_name.length + 5);
                worksheet.getCell("A" + con_index).value = "Conversion/Source";
                worksheet.getCell("A" + con_index).font = {
                    bold: true,
                };
                for (j = con_index; j <= con_index; j++) {
                    let index = 0;
                    for (let i = 1; i < (source_name.length); i++) {
                        if (source_name[index].name != undefined) {
                            worksheet.getCell(_getColumnLetter[i] + j).value = source_name[index].name;
                            worksheet.getCell(_getColumnLetter[i] + j).font = {
                                bold: true,
                                color: {
                                    argb: (source_name[index].color_name).replace("#", ""),
                                    size: 16
                                }
                            };

                        }
                        index++;
                    }
                }
                var index_type = 0;

                for (j = (con_index + 1); j < ((con_index + 1) + conversion_type.length); j++) {

                    if (conversion_type[index_type].name != undefined) {
                        worksheet.getCell("A" + j).value = conversion_type[index_type].name;
                        worksheet.getCell("A" + j).font = {
                            bold: true,
                            color: {
                                argb: (conversion_type[index_type].color).replace("#", ""),
                                size: 16
                            }
                        };

                    }
                    index_type++;
                }

                let con_index_upper = 0;
                for (j = (con_index + 1); j < ((con_index + 1) + (conversion_type.length)); j++) {
                    let index = 0;
                    for (let i = 1; i < (source_name.length); i++) {
                        if (source_name[index].name != undefined && conversion_type[con_index_upper].name != undefined) {
                            let index_name = source_name[index].name + "-" + conversion_type[con_index_upper].name;
                            if (excel_data["conversion_data"][index_name] != undefined) {
                                worksheet.getCell(_getColumnLetter[i] + j).value = Number(excel_data["conversion_data"][index_name]);
                                worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };
                            } else {
                                worksheet.getCell(_getColumnLetter[i] + j).value = 0;
                                worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };




                            }
                        }
                        index++;
                    }
                    con_index_upper++;
                }


                var con_index = ((con_index + 1) + conversion_type.length + 3);
                worksheet.getCell("A" + con_index).value = "Marketing/Source";
                worksheet.getCell("A" + con_index).font = {
                    bold: true,
                };
                for (j = con_index; j <= con_index; j++) {
                    let index = 0;
                    for (let i = 1; i < (conversion_type.length); i++) {
                        if (conversion_type[index].name != undefined) {
                            worksheet.getCell(_getColumnLetter[i] + j).value = conversion_type[index].name;
                            worksheet.getCell(_getColumnLetter[i] + j).font = {
                                bold: true,
                                color: {
                                    argb: (conversion_type[index].color).replace("#", ""),
                                    size: 16
                                }
                            };

                        }
                        index++;
                    }
                }
                var index_type = 0;

                for (j = (con_index + 1); j < ((con_index + 1) + marketing_type.length); j++) {

                    if (marketing_type[index_type].name != undefined) {
                        worksheet.getCell("A" + j).value = marketing_type[index_type].name;
                        worksheet.getCell("A" + j).font = {
                            bold: true,
                            color: {
                                argb: (marketing_type[index_type].color).replace("#", ""),
                                size: 16
                            }
                        };

                    }
                    index_type++;
                }

                let con_index_mar = 0;
                for (j = (con_index + 1); j < ((con_index + 1) + (marketing_type.length)); j++) {
                    let index = 0;
                    for (let i = 1; i < (conversion_type.length); i++) {
                        if (conversion_type[index].name != undefined && marketing_type[con_index_mar].name != undefined) {
                            let index_name = marketing_type[con_index_mar].name + "-" + conversion_type[index].name;
                            if (excel_data["performance_data"][index_name] != undefined) {
                                worksheet.getCell(_getColumnLetter[i] + j).value = Number(excel_data["performance_data"][index_name]);
                                worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };
                            } else {
                                worksheet.getCell(_getColumnLetter[i] + j).value = 0;
                                worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };




                            }
                        }
                        index++;
                    }
                    con_index_mar++;
                }


            });



            workbook.xlsx.writeBuffer().then(function(buffer) {

                // Save the workbook
                workbook.xlsx.writeBuffer().then(function(buffer) {
                    // Create a blob from the buffer
                    var blob = new Blob([buffer], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    });

                    // Create a URL for the blob
                    var url = window.URL.createObjectURL(blob);

                    // Create a link to download the file
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'Leads_Report.xlsx';
                    document.body.appendChild(a);

                    // Click the link to download the file
                    a.click();

                    // Remove the link
                    document.body.removeChild(a);
                });
            });

        }

        function RunExcelJSExport__() {
            console.log("generate excel");
            var workbook = new ExcelJS.Workbook();
            let excel_data_array_ = [{
                    "key": 1,
                    "name": "Lead Reports"
                },
                {
                    "key": 2,
                    "name": "Conversion Reports"
                },
                {
                    "key": 3,
                    "name": "Marketing Reports"
                }
            ];

            var length_add = 2;
            excel_data_array_.forEach(function(item) {
                if (item.key == 1) {
                    let worksheet = workbook.addWorksheet(item.name);
                    let con_index = 0;
                    Object.keys(excel_data_array).forEach(function(key) {

                        let excel_data = excel_data_array[key];
                        // console.log(excel_data);
                        // set up some data
                        // console.log(excel_data)
                        let daily_report = [];
                        if (summary_daily_excel[key] != undefined) {
                            daily_report = summary_daily_excel[key];
                        }
                        con_index = (con_index + 1);
                        worksheet.getCell("A" + con_index).value = key;
                        worksheet.getCell("A" + con_index).font = {
                            bold: true,
                        };
                        // con_index++;
                        worksheet.getCell("B" + con_index).value = "Status/Source";
                        worksheet.getCell("B" + con_index).font = {
                            bold: true,
                        };
                        for (j = con_index; j <= (con_index); j++) {
                            let index = 0;
                            for (let i = 2; i < (source_name.length + length_add); i++) {

                                if (source_name[index].name != undefined) {
                                    // console.log(_getColumnLetter[i] + j);
                                    worksheet.getCell(_getColumnLetter[i] + j).value = source_name[index].name;
                                    worksheet.getCell(_getColumnLetter[i] + j).font = {
                                        bold: true,
                                        color: {
                                            argb: (source_name[index].color_name).replace("#", ""),
                                            size: 16
                                        }
                                    };

                                }
                                index++;
                            }
                        }

                        var index_type = 0;
                        for (j = con_index + 1; j < ((con_index + 1) + (status_name.length)); j++) {

                            if (status_name[index_type].name !== undefined) {
                                worksheet.getCell("B" + j).value = status_name[index_type].name;
                                worksheet.getCell("B" + j).font = {
                                    bold: true,
                                    color: {
                                        argb: (status_name[index_type].color).replace("#", ""),
                                        size: 16
                                    }
                                };

                            }
                            index_type++;
                        }

                        let index_upper = 0;
                        for (j = con_index + 1; j < ((con_index + 1) + (status_name.length)); j++) {
                            let index = 0;
                            for (let i = 2; i < (source_name.length + length_add); i++) {
                                if (source_name[index].name !== undefined && status_name[index_upper].name !== undefined) {
                                    worksheet.getCell("A" + j).value = key;
                                    worksheet.getCell("A" + j).font = {
                                        bold: true,
                                    };
                                    // worksheet.getCell(_getColumnLetter[i] + j).value = excel_data[0][status_name[index_upper].name + "_" + source_name[index].name].total;
                                    let index_name = status_name[index_upper].name + "-" + source_name[index].name;
                                    if (excel_data[index_name] != undefined) {
                                        worksheet.getCell(_getColumnLetter[i] + j).value = Number(excel_data[index_name].total);
                                        worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                        worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                            horizontal: 'right',
                                            color: {
                                                argb: "FF0000"
                                            }
                                        };
                                    } else {
                                        worksheet.getCell(_getColumnLetter[i] + j).value = 0;
                                        worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                        worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                            horizontal: 'right',
                                            color: {
                                                argb: "FF0000"
                                            }
                                        };




                                    }
                                }
                                index++;
                            }
                            index_upper++;

                        }
                        if (daily_report.length > 0) {
                            // Set header values
                            worksheet.getCell(getColumnLetter(source_name.length + 5) + con_index).value = "User Name";
                            worksheet.getCell(getColumnLetter(source_name.length + 5) + con_index).font = {
                                bold: true,
                            };
                            worksheet.getCell(getColumnLetter(source_name.length + 6) + con_index).value = "Date";
                            worksheet.getCell(getColumnLetter(source_name.length + 6) + con_index).font = {
                                bold: true,
                            };
                            worksheet.getCell(getColumnLetter(source_name.length + 7) + con_index).value = "Count";
                            worksheet.getCell(getColumnLetter(source_name.length + 7) + con_index).font = {
                                bold: true,
                            };

                            // Populate data
                            let ii = 0;
                            for (let j = con_index + 1; j < (con_index + 1) + daily_report.length; j++) {
                                let date = daily_report[ii]["dateadded"];
                                if (date_type.toLowerCase() == "week") {
                                    date = getWeekRange(daily_report[ii]["dateadded"]);
                                }
                                worksheet.getCell(getColumnLetter(source_name.length + 5) + j).value = daily_report[ii]["full_name"];
                                worksheet.getCell(getColumnLetter(source_name.length + 6) + j).value = date;
                                worksheet.getCell(getColumnLetter(source_name.length + 7) + j).value = Number(daily_report[ii]["count"]);
                                worksheet.getCell(getColumnLetter(source_name.length + 7) + j).numFmt = numberFormat;
                                worksheet.getCell(getColumnLetter(source_name.length + 7) + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };

                                ii++;
                            }
                        }

                        if (daily_report.length > status_name.length) {
                            con_index = con_index + daily_report.length + 1;
                        } else {
                            con_index = con_index + status_name.length + 1;
                        }
                    });

                }
                if (item.key == 2) {
                    let worksheet = workbook.addWorksheet(item.name);
                    let con_index = 0;

                    Object.keys(excel_data_array).forEach(function(key) {
                        let excel_data = excel_data_array[key];
                        // set up some data
                        let daily_report = [];
                        if (summary_daily_excel[key] != undefined) {
                            daily_report = summary_daily_excel[key];
                        }

                        con_index = (con_index + 1);
                        worksheet.getCell("A" + con_index).value = key;
                        worksheet.getCell("A" + con_index).font = {
                            bold: true,
                        };
                        // con_index++;
                        worksheet.getCell("B" + con_index).value = "Conversion/Source";
                        worksheet.getCell("B" + con_index).font = {
                            bold: true,
                        };
                        for (j = con_index; j <= con_index; j++) {
                            let index = 0;
                            for (let i = 2; i < (source_name.length + length_add); i++) {
                                if (source_name[index].name != undefined) {
                                    worksheet.getCell(_getColumnLetter[i] + j).value = source_name[index].name;
                                    worksheet.getCell(_getColumnLetter[i] + j).font = {
                                        bold: true,
                                        color: {
                                            argb: (source_name[index].color_name).replace("#", ""),
                                            size: 16
                                        }
                                    };

                                }
                                index++;
                            }
                        }
                        var index_type = 0;
                        for (j = (con_index + 1); j < ((con_index + 1) + conversion_type.length); j++) {

                            if (conversion_type[index_type].name != undefined) {
                                worksheet.getCell("A" + j).value = key;
                                worksheet.getCell("A" + j).font = {
                                    bold: true,
                                };
                                worksheet.getCell("B" + j).value = conversion_type[index_type].name;
                                worksheet.getCell("B" + j).font = {
                                    bold: true,
                                    color: {
                                        argb: (conversion_type[index_type].color).replace("#", ""),
                                        size: 16
                                    }
                                };

                            }
                            index_type++;
                        }

                        let con_index_upper = 0;
                        for (j = (con_index + 1); j < ((con_index + 1) + (conversion_type.length)); j++) {
                            let index = 0;
                            for (let i = 2; i < (source_name.length + length_add); i++) {
                                if (source_name[index].name != undefined && conversion_type[con_index_upper].name != undefined) {
                                    let index_name = source_name[index].name + "-" + conversion_type[con_index_upper].name;
                                    if (conversion_type[con_index_upper] && conversion_type[con_index_upper].conversion_type.trim() !== "") {
                                        let parent_ids = conversion_type[con_index_upper].conversion_type.split(",").map(id => id.trim());


                                        parent_ids.forEach(id => {
                                            // Construct the index name for the parent
                                            console.log("asddadada" + conversion_type[con_index_upper].name, conversion_type__[id].name);
                                            let index_name_ = source_name[index].name + "-" + (conversion_type__[id] ? conversion_type__[id].name : "Unknown");

                                            // Initialize the target if it doesn't exist
                                            if (!excel_data["conversion_data"][index_name]) {
                                                excel_data["conversion_data"][index_name] = 0;
                                            }

                                            // Check if the source exists and add its value if it does
                                            if (excel_data["conversion_data"][index_name_]) {
                                                excel_data["conversion_data"][index_name] += excel_data["conversion_data"][index_name_];
                                            } else {
                                                console.warn(`Data for index name ${index_name_} not found`);
                                            }
                                        });


                                        if (parseInt(excel_data["conversion_data"][index_name]) > 0 && parseInt(excel_data_array_total[key]) > 0 && parent_ids.length > 0) {
                                            excel_data["conversion_data"][index_name] = parseInt(excel_data["conversion_data"][index_name]) / parseInt(excel_data_array_total[key]) * 100;
                                        }
                                    } else {
                                        console.warn(`Invalid or missing parent_id for con_index_upper ${con_index_upper}`);
                                    }

                                    if (excel_data["conversion_data"][index_name] != undefined) {
                                        worksheet.getCell(_getColumnLetter[i] + j).value = Number(excel_data["conversion_data"][index_name]);
                                        worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                        worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                            horizontal: 'right',
                                            color: {
                                                argb: "FF0000"
                                            }
                                        };
                                    } else {
                                        worksheet.getCell(_getColumnLetter[i] + j).value = 0;
                                        worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                        worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                            horizontal: 'right',
                                            color: {
                                                argb: "FF0000"
                                            }
                                        };




                                    }
                                }
                                index++;
                            }


                            con_index_upper++;
                        }

                        if (daily_report.length > 0) {
                            // Set header values
                            worksheet.getCell(getColumnLetter(source_name.length + 5) + con_index).value = "User Name";
                            worksheet.getCell(getColumnLetter(source_name.length + 5) + con_index).font = {
                                bold: true,
                            };
                            worksheet.getCell(getColumnLetter(source_name.length + 6) + con_index).value = "Date";
                            worksheet.getCell(getColumnLetter(source_name.length + 6) + con_index).font = {
                                bold: true,
                            };
                            worksheet.getCell(getColumnLetter(source_name.length + 7) + con_index).value = "Count";
                            worksheet.getCell(getColumnLetter(source_name.length + 7) + con_index).font = {
                                bold: true,
                            };

                            // Populate data
                            let ii = 0;
                            for (let j = con_index + 1; j < (con_index + 1) + daily_report.length; j++) {
                                let date = daily_report[ii]["dateadded"];
                                if (date_type.toLowerCase() == "week") {
                                    date = getWeekRange(daily_report[ii]["dateadded"]);
                                }
                                worksheet.getCell(getColumnLetter(source_name.length + 5) + j).value = daily_report[ii]["full_name"];
                                worksheet.getCell(getColumnLetter(source_name.length + 6) + j).value = date;
                                worksheet.getCell(getColumnLetter(source_name.length + 7) + j).value = Number(daily_report[ii]["count"]);
                                worksheet.getCell(getColumnLetter(source_name.length + 7) + j).numFmt = numberFormat;
                                worksheet.getCell(getColumnLetter(source_name.length + 7) + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };

                                ii++;
                            }
                        }

                        if (daily_report.length > conversion_type.length) {
                            con_index = con_index + daily_report.length + 1;
                        } else {
                            con_index = con_index + conversion_type.length + 1;
                        }


                    });
                }
                if (item.key == 3) {
                    let worksheet = workbook.addWorksheet(item.name);
                    let con_index = 0;
                    Object.keys(excel_data_array).forEach(function(key) {
                        let excel_data = excel_data_array[key];
                        // set up some data
                        let daily_report = [];
                        if (summary_daily_excel[key] != undefined) {
                            daily_report = summary_daily_excel[key];
                        }
                        con_index = (con_index + 1);
                        worksheet.getCell("A" + con_index).value = key;
                        worksheet.getCell("A" + con_index).font = {
                            bold: true,
                        };
                        // con_index++;
                        worksheet.getCell("B" + con_index).value = "Marketing/Conversion";
                        worksheet.getCell("B" + con_index).font = {
                            bold: true,
                        };
                        for (j = con_index; j <= con_index; j++) {
                            let index = 0;
                            // console.log(conversion_type.length);
                            for (let i = 2; i < (conversion_type.length + length_add); i++) {
                                // console.log(conversion_type[index].name);
                                if (conversion_type[index].name != undefined) {
                                    worksheet.getCell(_getColumnLetter[i] + j).value = conversion_type[index].name;
                                    worksheet.getCell(_getColumnLetter[i] + j).font = {
                                        bold: true,
                                        color: {
                                            argb: (conversion_type[index].color).replace("#", ""),
                                            size: 16
                                        }
                                    };

                                }
                                index++;
                            }
                        }
                        var index_type = 0;

                        for (j = (con_index + 1); j < ((con_index + 1) + marketing_type.length); j++) {

                            if (marketing_type[index_type].name != undefined) {
                                worksheet.getCell("B" + j).value = marketing_type[index_type].name;
                                worksheet.getCell("B" + j).font = {
                                    bold: true,
                                    color: {
                                        argb: (marketing_type[index_type].color).replace("#", ""),
                                        size: 16
                                    }
                                };

                            }
                            index_type++;
                        }

                        let con_index_mar = 0;
                        for (j = (con_index + 1); j < ((con_index + 1) + (marketing_type.length)); j++) {
                            let index = 0;
                            for (let i = 2; i < (conversion_type.length + length_add); i++) {
                                if (conversion_type[index].name != undefined && marketing_type[con_index_mar].name != undefined) {
                                    worksheet.getCell("A" + j).value = key;
                                    worksheet.getCell("A" + j).font = {
                                        bold: true,
                                    };
                                    let index_name = marketing_type[con_index_mar].name + "-" + conversion_type[index].name;
                                    if (conversion_type[con_index_mar] && conversion_type[index].conversion_type.trim() !== "") {
                                        let parent_ids = conversion_type[index].conversion_type.split(",").map(id => id.trim());


                                        parent_ids.forEach(id => {
                                            // Construct the index name for the parent

                                            let index_name_ = marketing_type[con_index_mar].name + "-" + (conversion_type__[id] ? conversion_type__[id].name : "Unknown");
                                            console.log(index_name_);
                                            console.log("asddadada" + marketing_type[con_index_mar].name, conversion_type__[id].name, excel_data["performance_data"][index_name_]);
                                            // Initialize the target if it doesn't exist
                                            if (!excel_data["performance_data"][index_name]) {
                                                excel_data["performance_data"][index_name] = 0;
                                            }

                                            // Check if the source exists and add its value if it does
                                            if (excel_data["performance_data"][index_name_]) {
                                                excel_data["performance_data"][index_name] += excel_data["performance_data"][index_name_];
                                            } else {
                                                console.warn(`Data for index name ${index_name_} not found`);
                                            }
                                        });


                                        if (parseInt(excel_data["performance_data"][index_name]) > 0 && parseInt(excel_data_array_total[key]) > 0 && parent_ids.length > 0) {
                                            excel_data["performance_data"][index_name] = parseInt(excel_data["performance_data"][index_name]) / parseInt(excel_data_array_total[key]) * 100;
                                        }
                                    } else {
                                        console.warn(`Invalid or missing parent_id for con_index_upper ${con_index_mar}`);
                                    }

                                    if (excel_data["performance_data"][index_name] != undefined) {
                                        worksheet.getCell(_getColumnLetter[i] + j).value = Number(excel_data["performance_data"][index_name]);
                                        worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                        worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                            horizontal: 'right',
                                            color: {
                                                argb: "FF0000"
                                            }
                                        };
                                    } else {
                                        worksheet.getCell(_getColumnLetter[i] + j).value = 0;
                                        worksheet.getCell(_getColumnLetter[i] + j).numFmt = numberFormat;
                                        worksheet.getCell(_getColumnLetter[i] + j).alignment = {
                                            horizontal: 'right',
                                            color: {
                                                argb: "FF0000"
                                            }
                                        };




                                    }
                                }
                                index++;
                            }
                            con_index_mar++;
                        }
                        if (daily_report.length > 0) {
                            // Set header values
                            worksheet.getCell(getColumnLetter(conversion_type.length + 5) + con_index).value = "User Name";
                            worksheet.getCell(getColumnLetter(conversion_type.length + 5) + con_index).font = {
                                bold: true,
                            };
                            worksheet.getCell(getColumnLetter(conversion_type.length + 6) + con_index).value = "Date";
                            worksheet.getCell(getColumnLetter(conversion_type.length + 6) + con_index).font = {
                                bold: true,
                            };
                            worksheet.getCell(getColumnLetter(conversion_type.length + 7) + con_index).value = "Count";
                            worksheet.getCell(getColumnLetter(conversion_type.length + 7) + con_index).font = {
                                bold: true,
                            };

                            // Populate data
                            let ii = 0;
                            for (let j = con_index + 1; j < (con_index + 1) + daily_report.length; j++) {
                                let date = daily_report[ii]["dateadded"];
                                if (date_type.toLowerCase() == "week") {
                                    date = getWeekRange(daily_report[ii]["dateadded"]);
                                }
                                worksheet.getCell(getColumnLetter(conversion_type.length + 5) + j).value = daily_report[ii]["full_name"];
                                worksheet.getCell(getColumnLetter(conversion_type.length + 6) + j).value = date;
                                worksheet.getCell(getColumnLetter(conversion_type.length + 7) + j).value = Number(daily_report[ii]["count"]);
                                worksheet.getCell(getColumnLetter(conversion_type.length + 7) + j).numFmt = numberFormat;
                                worksheet.getCell(getColumnLetter(conversion_type.length + 7) + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };

                                ii++;
                            }
                        }

                        if (daily_report.length > marketing_type.length) {
                            con_index = con_index + daily_report.length + 1;
                        } else {
                            con_index = con_index + marketing_type.length + 1;
                        }
                    });

                }
            });



            workbook.xlsx.writeBuffer().then(function(buffer) {

                // Save the workbook
                workbook.xlsx.writeBuffer().then(function(buffer) {
                    // Create a blob from the buffer
                    var blob = new Blob([buffer], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    });

                    // Create a URL for the blob
                    var url = window.URL.createObjectURL(blob);

                    // Create a link to download the file
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'Leads_Report_date_wise.xlsx';
                    document.body.appendChild(a);

                    // Click the link to download the file
                    a.click();

                    // Remove the link
                    document.body.removeChild(a);
                });
            });

        }



        $(document).on('click', '.btn-switch-toggle', function() {
            var parentDiv = $(this).closest(".parrent-div");
            parentDiv.find(".panel-body").toggle();
        });


        $(".filter_reset select").change(function() {
            var selectedValue = $(this).val();

            // Check if the changed select element has id="view_assigned"
            if ($(this).attr("id") == "view_assigned") {
                // Disable other selects and buttons except the one that triggered the change
                $(".filter_reset select").not(this).prop('disabled', true);
                $(".filter_reset .bs-actionsbox button").prop('disabled', true);
                $(this).closest(".filter_reset").find(".bs-actionsbox button").prop('disabled', false);

            } else {
                // Disable the select element with id="view_assigned"
                $("#view_assigned").prop('disabled', true);
            }

            // If no value is selected, enable all selects and buttons
            if (selectedValue.length == 0) {
                $(".filter_reset select").prop('disabled', false);
                $(".filter_reset .bs-actionsbox button").prop('disabled', false);
            }
        });


        var update_daily_staff_id = 0;

        function convertToHMS(seconds) {
            if (seconds === "") {
                seconds = 0;
            }
            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            seconds = seconds % 60;

            if (status === 1) {
                return hours + ":" + minutes + ":" + seconds;
            } else {
                return hours + " Hours : " + minutes + " Mins : " + seconds + " Sec";
            }

        }

        function daily_update_count(id, staffid) {
            update_daily_staff_id = staffid;
            $(id).toggle();
            $("#leads-overview-" + staffid).html('');
            if ($(id).is(":visible")) { // Corrected the if statement
                ajax_filter();
            }
        }

        $("#view_source").change(function() {
            $("[class*='hide_show_']").filter(function() {
                // Check if any of the classes start with "hide_show_"
                return $(this).attr("class").split(" ").some(function(className) {
                    return className.startsWith("hide_show_");
                });
            }).addClass("hide");
            $("[class*='hide_show_'] select").val('').selectpicker('refresh');

            var selectedValues = $(this).val();
            if (selectedValues.length > 1) {


            } else if (selectedValues.length === 1) {
                selectedValues.forEach(function(selectedValue) {
                    // console.log(selectedValue);
                    var elementID = ".hide_show_" + selectedValue;
                    // console.log($(elementID).length);
                    if ($(elementID).length > 0) {
                        $(elementID).removeClass("hide");
                    }
                });
            }
        });


        var assigned_summary;
        var totalLeads = 0;
        var sum;
        var staff_total_lead = 0;
        var staff_total_lead_array = [];
        var total_staff_report_array = [];

        // var conversion_type_set;

        var source_type = <?= (!empty($source_type)) ? json_encode($source_type) : [] ?>;
        var conversion_type = <?= (!empty($conversion_type)) ? json_encode($conversion_type) : [] ?>;
        var conversion_type__ = <?= (!empty($conversion_type_)) ? json_encode($conversion_type_) : [] ?>;
        var status_list = <?= (!empty($status_list)) ? json_encode($status_list, true) : [] ?>;
        var status_list_ = <?= (!empty($status_list_)) ? json_encode($status_list_, true) : [] ?>;
        var staff = <?= (!empty($staff_list)) ? json_encode($staff_list) : [] ?>;
        var staff_list = <?= (!empty($staff_list)) ? json_encode($staff_list) : [] ?>;
        var status_list_ = <?= (!empty($status_list_)) ? json_encode($status_list_) : [] ?>;
        var performance_array = <?= (!empty($marketing_type)) ? json_encode($marketing_type) : [] ?>;
        var performance_array_name = <?= (!empty($performance_array)) ? json_encode($performance_array) : [] ?>;

        if (status_list.length > 0) {
            status_list.push({
                name: 'Total Lead Status',
                color: "#28b8da"
            });
        }
        if (status_list.length > 0) {
            source_type.push({
                name: 'Total Lead Source',
                color: "#28b8da"
            });
        }

        function graph_represent_() {
            setTimeout(() => {
                if ($(".leads-overview-calls-daily").is(":visible")) {
                    ajax_filter(2);
                }
            }, 1000);

        }

        function set_call_filter(updateCount_filter, updateCount_filter_min = []) {
            if (Object.keys(updateCount_filter).length) {
                updateCount_filter = Object.values(updateCount_filter);
                updateCount_filter_min = Object.values(updateCount_filter_min);
                // $("#show_hide_staff_list").removeClass("hide");
                // $("#total_staff_list").html(data.total_staff_html);
                // $(".hide-graph-calls").removeClass("hide");
                // console.log(updateCount_filter);
                // console.log(updateCount_filter_min);
                let label_names = [];
                let min = [];
                let max = [];
                for (let i = 0; i < updateCount_filter.length; i++) {
                    let assigned = updateCount_filter[i].assigned;

                    if (staff[assigned] !== undefined) {
                        label_names.push(staff[assigned]);

                        let minValue = updateCount_filter_min[i] && updateCount_filter_min[i].total ? updateCount_filter_min[i].total : 0;
                        min.push(minValue);

                        let maxValue = updateCount_filter[i].total ? updateCount_filter[i].total : 0;
                        max.push(maxValue);
                    }
                }


                // console.log(label_names);
                // console.log(min);
                // console.log(max);

                var configCalls = {
                    type: "bar",
                    data: {
                        labels: label_names, // Date Objects
                        datasets: [{
                                label: "Filtered",
                                backgroundColor: "rgba(240, 140, 121, 0.8)",
                                borderColor: "rgba(140, 140, 140, 1.0)",
                                borderWidth: 0,
                                data: min,
                                fill: false,
                                radius: 0,
                            },
                            {
                                label: "Max",
                                backgroundColor: "rgba(121, 200, 121, 0.8)",
                                borderColor: "rgba(140, 140, 140, 0.0)",
                                borderWidth: 0,
                                data: max,
                                fill: "-1",
                                line: false,
                                radius: 0,
                            },
                            // {
                            //     label: "Total leads",
                            //     backgroundColor: "rgba(0, o, 238, 0.8)",
                            //     borderColor: "rgba(140, 140, 140, 1.0)",
                            //     borderWidth: 0,
                            //     data: data.total_leads,
                            //     fill: false,
                            //     radius: 0,
                            // }
                        ]
                    },
                    options: {
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            displayColors: false,
                        },
                        responsive: true,
                        title: {
                            display: true,
                            text: "Not Reachable Leads chat - Filtered/Max "
                        },
                        scales: {
                            x: {
                                stacked: true,
                                format: "HH mm",
                            },
                            y: {
                                stacked: true,
                                scaleLabel: {
                                    display: true,
                                    labelString: "value"
                                }
                            }
                        },
                        pan: {
                            enabled: true,
                            mode: "x",
                            speed: 10,
                            threshold: 10
                        },
                        zoom: {
                            enabled: true,
                            drag: false,
                            mode: "xy",
                            limits: {
                                max: 10,
                                min: 0.5
                            }
                        }
                    }
                };

                // Get the canvas context
                var ctxCalls = document.getElementById("canvas").getContext("2d");

                createOrUpdateChart("myCallsChart", ctxCalls, configCalls);

            }
        }

        $('#apply_filter').on('click', function() {
            ajax_filter()
        });

        function set_graph_(data) {
            if (data.summary_daily_ != undefined && data.summary_daily_.length > 0) {
                // console.log(data.summary_daily_);
                $(".hide-graph-daily").removeClass("hide");

                if (data.summary_daily_ != undefined && data.summary_daily_.length > 0) {
                    $(".hide-graph-daily").removeClass("hide");
                    var labels = []
                    if (date_type.toLowerCase() == "week") {
                        labels = data.summary_daily_.map(item => getWeekRange(item.dateadded));
                    } else {
                        labels = data.summary_daily_.map(item => item.dateadded);
                    }
                    var lable_value = data.summary_daily_.map(item => item.count);

                    var configDaily = {
                        type: "bar",
                        data: {
                            labels: labels, // Date Objects
                            datasets: [{
                                label: "Leads",
                                backgroundColor: "rgba(240, 140, 121, 0.8)",
                                borderColor: "rgba(140, 140, 140, 1.0)",
                                borderWidth: 0,
                                data: lable_value,
                                fill: false,
                                radius: 0,
                            }]
                        },
                        options: {
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                                displayColors: false,
                            },
                            responsive: true,
                            title: {
                                display: true,
                                text: "Date Wise Leads chat - Leads"
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    format: "HH mm",
                                },
                                y: {
                                    stacked: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: "value"
                                    }
                                }
                            },
                            pan: {
                                enabled: true,
                                mode: "x",
                                speed: 10,
                                threshold: 10
                            },
                            zoom: {
                                enabled: true,
                                drag: false,
                                mode: "xy",
                                limits: {
                                    max: 10,
                                    min: 0.5
                                }
                            }
                        }
                    };

                    // Get the canvas context
                    var ctxDaily = document.getElementById("canvas_daily").getContext("2d");

                    // Create a new chart with the updated configuration
                    createOrUpdateChart("canvas_daily", ctxDaily, configDaily);

                }

                if (data.update_count_daily_data != undefined) {
                    let html_update = "<div class='row scroll-div col-12'>";
                    for (i = 0; i < (data.update_count_daily_data).length; i++) {
                        html_update += "<div class='col-md-3 show-daily-update'><p>Date : " + data.update_count_daily_data[i].uni_dates + "</p><br><p>Update Count : " + data.update_count_daily_data[i].total + "</p><br><p>Call Duration : " + convertToHMS(data.update_count_daily_data[i].call_duration) + "</p></div>";
                    }
                    html_update += "</div>";
                    $(".leads-overview-" + update_daily_staff_id).html(html_update);
                    $(".leads-overview-" + update_daily_staff_id).removeClass("hide");
                    update_daily_staff_id = 0;
                }
                if (data.summary_daily_conversion != undefined && data.summary_daily_conversion.length > 0) {
                    $(".hide-graph-daily").removeClass("hide");

                    // Function to get all unique conversion types
                    function getAllConversionTypes(data) {
                        const types = new Set();
                        data.forEach(item => {
                            const parsedCounts = parseConversionCounts(item.conversion_counts);
                            Object.keys(parsedCounts).forEach(type => types.add(type));
                        });
                        return Array.from(types);
                    }

                    // Function to get all unique dates
                    function getAllDates(data) {
                        if (date_type.toLowerCase() == "week") {
                            return data.map(item => getWeekRange(item.dateadded));
                        } else {
                            return data.map(item => item.dateadded);
                        }
                    }

                    // Get all unique conversion types and dates
                    const conversionTypes = getAllConversionTypes(data.summary_daily_conversion);
                    const allDates = getAllDates(data.summary_daily_conversion);

                    // Initialize conversion data structure
                    const conversionData = {};
                    conversionTypes.forEach(type => {
                        conversionData[type] = allDates.map(date => ({
                            date,
                            count: 0
                        }));
                    });

                    // Fill in actual data
                    data.summary_daily_conversion.forEach(item => {
                        const date = date_type.toLowerCase() == "week" ? getWeekRange(item.dateadded) : item.dateadded;
                        const parsedCounts = parseConversionCounts(item.conversion_counts);
                        Object.keys(parsedCounts).forEach(type => {
                            const index = allDates.indexOf(date);
                            if (index !== -1) {
                                conversionData[type][index].count = parsedCounts[type];
                            }
                        });
                    });
                    // Prepare datasets
                    const datasets = Object.keys(conversionData).map(type => ({
                        label: type,
                        backgroundColor: conversion_type_color[type], // Function to generate random color
                        borderColor: conversion_type_color[type],
                        borderWidth: 1,
                        data: conversionData[type].map(item => item.count),
                        fill: false
                    }));

                    // Create the chart
                    const configDaily = {
                        type: "bar",
                        data: {
                            labels: allDates,
                            datasets: datasets
                        },
                        options: {
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                                displayColors: false,
                            },
                            responsive: true,
                            title: {
                                display: true,
                                text: "Date Wise Conversion Leads chat - Leads"
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                },
                                y: {
                                    stacked: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: "value"
                                    }
                                }
                            },
                            pan: {
                                enabled: true,
                                mode: "x",
                                speed: 10,
                                threshold: 10
                            },
                            zoom: {
                                enabled: true,
                                drag: false,
                                mode: "xy",
                                limits: {
                                    max: 10,
                                    min: 0.5
                                }
                            }
                        }
                    };

                    // Get the canvas context
                    var ctxDaily = document.getElementById("canvas_conversion").getContext("2d");
                    createOrUpdateChart("canvas_conversion", ctxDaily, configDaily);
                }
                if (data.summary_daily_marketing != undefined && data.summary_daily_marketing.length > 0) {
                    $(".hide-graph-daily").removeClass("hide");

                    // Function to get all unique marketing types
                    function getAllMarketingTypes(data) {
                        const types = new Set();
                        data.forEach(item => {
                            const parsedCounts = parseConversionCounts(item.marketing_count);
                            Object.keys(parsedCounts).forEach(type => types.add(type));
                        });
                        return Array.from(types);
                    }

                    // Function to get all unique dates
                    function getAllDates(data) {
                        if (date_type.toLowerCase() == "week") {
                            return data.map(item => getWeekRange(item.dateadded));
                        } else {
                            return data.map(item => item.dateadded);
                        }
                    }

                    // Get all unique marketing types and dates
                    const marketingTypes = getAllMarketingTypes(data.summary_daily_marketing);
                    const allDates = getAllDates(data.summary_daily_marketing);

                    // Initialize marketing data structure
                    const marketingData = {};
                    marketingTypes.forEach(type => {
                        marketingData[type] = allDates.map(date => ({
                            date,
                            count: 0
                        }));
                    });

                    // Fill in actual data
                    data.summary_daily_marketing.forEach(item => {
                        const date = date_type.toLowerCase() == "week" ? getWeekRange(item.dateadded) : item.dateadded;
                        const parsedCounts = parseConversionCounts(item.marketing_count);
                        Object.keys(parsedCounts).forEach(type => {
                            const index = allDates.indexOf(date);
                            if (index !== -1) {
                                marketingData[type][index].count = parsedCounts[type];
                            }
                        });
                    });

                    // Prepare datasets
                    const datasets = Object.keys(marketingData).map(type => ({
                        label: type,
                        backgroundColor: marketing_type_color[type], // Function to generate random color
                        borderColor: marketing_type_color[type],
                        borderWidth: 1,
                        data: marketingData[type].map(item => item.count),
                        fill: false
                    }));

                    // Create the chart
                    const configDaily = {
                        type: "bar",
                        data: {
                            labels: allDates,
                            datasets: datasets
                        },
                        options: {
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                                displayColors: false,
                            },
                            responsive: true,
                            title: {
                                display: true,
                                text: "Date Wise Marketing Leads chat - Leads"
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                },
                                y: {
                                    stacked: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: "value"
                                    }
                                }
                            },
                            pan: {
                                enabled: true,
                                mode: "x",
                                speed: 10,
                                threshold: 10
                            },
                            zoom: {
                                enabled: true,
                                drag: false,
                                mode: "xy",
                                limits: {
                                    max: 10,
                                    min: 0.5
                                }
                            }
                        }
                    };

                    // Get the canvas context
                    var ctxDaily = document.getElementById("canvas_marketing").getContext("2d");
                    createOrUpdateChart("canvas_marketing", ctxDaily, configDaily);
                }
            }
        }

        function ajax_filter(status_filter = 0) {

            var total_status = 1;
            var element_view_assign = document.getElementById("view_assigned");
            var element_view_source = document.getElementById("view_source");
            var element_view_status = document.getElementById("view_status");
            var element_view_fb_name = document.getElementById("view_facebook_names");
            var element_view_google_type = document.getElementById("view_source_marketing");
            var location = document.getElementById("location");
            date_type = document.getElementById("date_type").value;
            <?php if (is_admin()) { ?>
                var department = document.getElementById("department");
            <?php } else if ($role == 3 && $staff_department != "") { ?>
                var department = [];
                department.push("<?= $staff_department ?>");
            <?php } ?>

            var up_from_date = document.getElementById("up_from_date").value;
            var up_to_date = document.getElementById("up_to_date").value;
            var assign_from_date = document.getElementById("assign_from_date").value;
            var assign_to_date = document.getElementById("assign_to_date").value;
            var lead_type = $("#lead_type").val();
            var view_assigned_options = "";
            var view_source_options = "";
            var view_status_options = "";
            var view_fb_options = "";
            var view_google_options = "";
            var view_location = "";
            var view_department = "";
            var update_count_min = '';
            var update_count_max = '';
            var update_staff_id = "";
            if (slider_data && status_filter == 1) {
                update_count_min = document.getElementById("update_count_min").value;
                update_count_max = document.getElementById("update_count_max").value;
            }
            if (update_daily_staff_id != 0) {
                update_staff_id = update_daily_staff_id;
            }

            if (typeof(element_view_source) != 'undefined' && element_view_source != null) {
                view_source_options = document.getElementById('view_source').selectedOptions;
                view_source_options = Array.from(view_source_options).map(({
                    value
                }) => value);
            }
            if (typeof(element_view_status) != 'undefined' && element_view_status != null) {
                view_status_options = document.getElementById('view_status').selectedOptions;
                view_status_options = Array.from(view_status_options).map(({
                    value
                }) => value);
            }

            if (typeof(element_view_fb_name) != 'undefined' && element_view_fb_name != null) {
                view_fb_options = document.getElementById('view_facebook_names').selectedOptions;
                view_fb_options = Array.from(view_fb_options).map(({
                    value
                }) => value);
            }

            if (typeof(element_view_google_type) != 'undefined' && element_view_google_type != null) {
                view_google_options = document.getElementById('view_source_marketing').selectedOptions;
                view_google_options = Array.from(view_google_options).map(({
                    value
                }) => value);
            }
            if (typeof(element_view_assign) != 'undefined' && element_view_assign != null) {
                view_assigned_options = document.getElementById('view_assigned').selectedOptions;
                view_assigned_options = Array.from(view_assigned_options).map(({
                    value
                }) => value);
            }




            if (typeof(location) != 'undefined' && location != null) {
                view_location = document.getElementById('location').selectedOptions;
                view_location = Array.from(view_location).map(({
                    value
                }) => value);
            }

            <?php if (is_admin()) { ?>
                if (typeof(department) != 'undefined' && department != null) {
                    view_department = document.getElementById('department').selectedOptions;
                    view_department = Array.from(view_department).map(({
                        value
                    }) => value);
                }
            <?php } else if ($role == 3 && $staff_department != "") { ?>
                view_department = ["<?= $staff_department ?>"];
            <?php } ?>



            if (view_assigned_options.length > 0 || view_department.length > 0 || view_location.length > 0 || lead_type.length > 0) {
                total_status = 0;
            }

            var from_date = document.getElementById("from_date").value;
            var to_date = document.getElementById("to_date").value;
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
            let graph_status = 0;
            if (status_filter == 2) {
                graph_status = 1;
            }
            let call_status = 0;
            if (status_filter == 1) {
                call_status = 1;
            }


            if (xhr != null) {
                xhr.abort();
            }
            if (status_filter == "") {
                $("#generate_pdf").hide();
                $(".hide-btn-response").hide();
                $(".report-data .hide-graph").addClass("hide");
                $(".hide-graph-daily").addClass("hide");
                $("#show_hide_staff_list").addClass("hide");
                $('#apply_filter').attr("disabled", true);
            }
            show_loader("apply_filter");

            let ajax_post_data = {
                assigned: view_assigned_options,
                source: view_source_options,
                status: view_status_options,
                from_date: from_date,
                to_date: to_date,
                up_from_date: up_from_date,
                up_to_date: up_to_date,
                lead_type: lead_type,
                update_count_min: update_count_min,
                update_count_max: update_count_max,
                location: view_location,
                department: view_department,
                daily_update_count: update_staff_id,
                google_source: view_google_options,
                fb_source: view_fb_options,
                date_type: date_type,
                assign_from_date: assign_from_date,
                assign_to_date: assign_to_date,
                graph_status: graph_status,
                call_status: call_status,
                total_status: total_status
            };

            ajax_get_post_data = ajax_post_data
            xhr = $.ajax({
                type: "POST",
                url: admin_url + "reports/lead_summary_filter",
                data: ajax_post_data,
                dataType: "JSON",
                cache: false,
                success: function(data) {

                    $(".hide-graph-daily").removeClass("hide");
                    $('#apply_filter').attr("disabled", false);
                    // setMinMaxValues();
                    $('#apply_filter_update_count').attr("disabled", false);
                    hide_loader("apply_filter");
                    hide_loader("apply_filter_update_count");
                    $(".hide-graph-calls").removeClass("hide");

                    //alert(data);  //as a debugging message.

                    if (status_filter == 2) {
                        set_graph_(data);
                        return;
                    }

                    if (status_filter == 0) {
                        $(".leads-overview-calls").attr("style", "display:none");
                        $(".leads-overview-calls-daily").attr("style", "display:none");
                    }

                    if (data.update_count_daily_data != undefined) {
                        let html_update = "<div class='row scroll-div col-12'>";
                        for (i = 0; i < (data.update_count_daily_data).length; i++) {
                            html_update += "<div class='col-md-3 show-daily-update'><p>Date : " + data.update_count_daily_data[i].uni_dates + "</p><br><p>Update Count : " + data.update_count_daily_data[i].total + "</p><br><p>Call Duration : " + convertToHMS(data.update_count_daily_data[i].call_duration) + "</p></div>";
                        }
                        html_update += "</div>";
                        $(".leads-overview-" + update_daily_staff_id).html(html_update);
                        $(".leads-overview-" + update_daily_staff_id).removeClass("hide");
                        update_daily_staff_id = 0;
                    }

                    update_daily_staff_id = 0;

                    if (data.update_count_filter != undefined && Object.keys(data.update_count_filter).length > 0) {
                        update_count_filter_min = data.update_count_filter_min ? data.update_count_filter_min : [];
                        set_call_filter(data.update_count_filter, update_count_filter_min);
                        return;
                    }
                    if (data.report_list == 0) {
                        $(".leadSum").html("");
                    }

                    if (data.report_list == 1) {
                        $(".leadSum").html("");
                        var index = 1;
                        let {
                            source_summary = {}, assigned = [], summary = [], status_summary_performance = []
                        } = data;
                        let conversion_type_ = data.conversion_type_;




                        function capitalizeFirstLetter(string) {
                            return string.charAt(0).toUpperCase() + string.slice(1);
                        }

                        function calculateTotalLeads(assigned_summary) {
                            return Object.values(assigned_summary).reduce((sum, count) => sum + count, 0);
                        }

                        function generateLeadTypeHTML(assigned, conversion_type_set) {
                            // console.log("set-status", staff[assigned]);
                            return new Promise((resolve) => {

                                let ret = `<div class="col-12 panel-body"><h4><b>Leads Types</b></h4><hr>`;

                                staff_total_lead = 0
                                // if (summary && summary[assigned] && summary[assigned].status_counts) {
                                assigned_summary = [];
                                if (summary && summary[assigned] && summary[assigned].status_counts) {
                                    assigned_summary = JSON.parse(summary[assigned].status_counts);
                                    staff_total_lead = totalLeads = Object.values(assigned_summary).reduce((sum, count) => sum + count, 0);
                                    assigned_summary["Total Lead Status"] = totalLeads;

                                    total_staff_report_array["status"]["Total Lead Status"] = total_staff_report_array["status"]["Total Lead Status"] ? total_staff_report_array["status"]["Total Lead Status"] + totalLeads : totalLeads;
                                }


                                totalLeads = 0;
                                // console.log(staff_total_lead);
                                staff_total_lead_array[staff[assigned]] = staff_total_lead;
                                status_list.forEach(status => {
                                    let status_count = assigned_summary[status.name] || 0;
                                    // Ensure the 'status' property is initialized
                                    if (!total_staff_report_array["status"]) {
                                        total_staff_report_array["status"] = {};
                                    }
                                    // Initialize the specific status if it's not already defined
                                    if (!total_staff_report_array["status"][status.name]) {
                                        total_staff_report_array["status"][status.name] = 0;
                                    }
                                    // Add the status_count to the existing value

                                    if (status.conversion_type > 0) {
                                        totalLeads += status_count;

                                        if (conversion_type_set) {
                                            conversion_type_set[status.conversion_type] = conversion_type_set[status.conversion_type] ?
                                                conversion_type_set[status.conversion_type] + status_count :
                                                status_count;
                                        }
                                        total_staff_report_array["total"] = total_staff_report_array["total"] ? total_staff_report_array["total"] + status_count : status_count;
                                    }

                                    if (status.name != "Total Lead Status") {
                                        total_staff_report_array["status"][status.name] += status_count;
                                    }


                                    ret += `<div class="col-md-3 col-xs-6 border-right">
                                        <h3 class="bold">${status.percent ? `<span data-toggle="tooltip" data-title="${status_count}">${status.percent}%</span>` : status_count}</h3>
                                        <span style="color:${status.color}">${status.name}</span>
                                    </div>`;
                                });
                                // } else {
                                //     ret += `<div class="col-md-12">No data available</div>`;
                                // }
                                ret += `</div>`;
                                resolve(ret);
                            });
                        }

                        function generateSourceTypeHTML(assigned) {
                            // console.log("set-source", staff[assigned]);

                            source_type["Total Lead Source"] = 0;
                            return new Promise((resolve) => {
                                let ret = `<div class="col-12 panel-body"><h4><b>Sources Types</b></h4><hr>`;


                                source_type.forEach(source => {
                                    let source_key = assigned + "-" + source.id;
                                    let status_count = (source_summary[source_key] && source_summary[source_key].total) || 0;
                                    source_type["Total Lead Source"] += parseInt(status_count);


                                    if (source.name == "Total Lead Source") {
                                        status_count = source_type["Total Lead Source"];
                                        total_staff_report_array["source"]["Total Lead Source"] = total_staff_report_array["source"]["Total Lead Source"] ? total_staff_report_array["source"]["Total Lead Source"] + status_count : status_count;


                                    }

                                    // excel_data_array[staff[assigned]]["conversion_data"][source.name] = status_count;

                                    // Ensure the 'status' property is initialized
                                    if (!total_staff_report_array["source"]) {
                                        total_staff_report_array["source"] = {};
                                    }
                                    // Initialize the specific status if it's not already defined
                                    if (!total_staff_report_array["source"][source.name]) {
                                        total_staff_report_array["source"][source.name] = 0;
                                    }
                                    // Add the status_count to the existing value
                                    if (source.name != "Total Lead Source") {
                                        total_staff_report_array["source"][source.name] += parseInt(status_count);
                                    }



                                    ret += `<div class="col-md-3 col-xs-6 border-right">
                    <h3 class="bold">${source_summary[source_key] && source_summary[source_key].percent ? `<span data-toggle="tooltip" data-title="${status_count}">${source_summary[source_key].percent}%</span>` : status_count}</h3>
                    <span style="color:${source.color_name}">${source.name}</span>
                </div>`;

                                });

                                ret += `</div>`;
                                resolve(ret);
                            });
                        }


                        function generateConversionTypeHTML(totalLeads, conversion_type_set, staff_name) {
                            // console.log("set-conversion", staff[assigned]);

                            return new Promise((resolve) => {
                                let ret = '<div class="col-12 panel-body" class="con_tab"><h4><b>Conversion Type</b></h4><hr>';
                                conversion_type.forEach(conversion => {
                                    if (conversion.parent_id && conversion.parent_id !== "") {
                                        return;
                                    }
                                    let percentage = conversion_type_set[conversion.id] || 0;
                                    ret += '<div style="" class="col-md-3 col-xs-6 border-right"><h3 class="bold">';
                                    ret += percentage;

                                    total_staff_report_array["conversion"][conversion.name] = total_staff_report_array["conversion"][conversion.name] ? total_staff_report_array["conversion"][conversion.name] + percentage : percentage;

                                    // console.log(conversion);
                                    // excel_data_array[staff[assigned]]["conversion_data"][source.name + "-" + conversion_type__[source.conversion_id].name] = status_count;


                                    if (percentage > 0) {
                                        if (totalLeads !== 0) {
                                            percentage = (percentage / totalLeads) * 100;
                                            ret += `<span class='show-persentage'>${percentage.toFixed(2)}% </span>`;
                                        } else {
                                            ret += "<span class='show-persentage'>0.00% </span>";
                                        }
                                    } else {
                                        ret += "<span class='show-persentage'>0.00% </span>";
                                    }
                                    ret += `</h3><span style="color:${conversion.color || ''}">${conversion.name || ''}</span></div>`;
                                });
                                ret += '</div>';
                                resolve(ret);
                            });
                        }

                        function generatePerformanceTypeHTML(assigned) {
                            // console.log("set-performance", staff[assigned]);

                            return new Promise((resolve) => {
                                let html = '';
                                let sum = {};
                                // let status_summary_performance = status_summary_performance[assigned];

                                // Assuming conversion_type is an array of objects similar to the PHP $conversion_type array
                                // const values_sum = Object.values(conversion_type_set).filter(element => element.total_status === 1).map(element => {
                                //     const total = parseFloat(element.total); // Ensure 'total' is a number
                                //     return isNaN(total) ? 0 : total; // Handle cases where 'total' is not a number
                                // });
                                // console.log("value", conversion_type_set);
                                // console.log("value", values_sum);
                                // const total_sum = values_sum.reduce((sum, value) => sum + value, 0);
                                // console.log("total_sum", total_sum);

                                if (status_summary_performance[assigned]) {
                                    Object.values(status_summary_performance[assigned]).forEach(summary_performance => {
                                        let conversion_id = summary_performance.conversion_id;
                                        let marketing_id = summary_performance.marketing_id;

                                        let key = marketing_id + "_" + conversion_id;

                                        if (conversion_id > 0) {
                                            if (sum[key] === undefined) {
                                                sum[key] = 0;
                                            }
                                            sum[key] += parseInt(summary_performance.total);
                                        }
                                    });
                                }


                                Object.values(performance_array).forEach((per, key) => {
                                    html += '<div class="col-md-12 col-xs-12 "><h3 class="bold">';
                                    html += `<span style="color:${per.color}">${per.name}</span></h3></div>`;

                                    let per_percentage = 0;

                                    Object.values(conversion_type).forEach((conversion, kkey) => {
                                        if (conversion.parent_id) {
                                            return;
                                        }
                                        let percentage = 0;

                                        html += '<div class="col-md-3 col-xs-6 marketing-type border-right"><h3 class="bold">';


                                        html += sum[`${per.id}_${conversion.id}`] || 0;

                                        percentage = 0;
                                        if (conversion.parent_ids) {
                                            const ids = conversion.parent_ids.split(",");
                                            ids.forEach(c_id => {
                                                percentage += sum[`${per.id}_${c_id}`] || 0;
                                            });
                                        }
                                        // html += percentage || 0;
                                        if (!total_staff_report_array["performance"][per.name]) {
                                            total_staff_report_array["performance"][per.name] = {};
                                        }
                                        total_staff_report_array["performance"][per.name][conversion.name] = total_staff_report_array["performance"][per.name][conversion.name] ? total_staff_report_array["performance"][per.name][conversion.name] + percentage : percentage;
                                        if (percentage > 0) {
                                            if (totalLeads !== 0) {
                                                per_percentage += percentage;
                                                percentage = (percentage / totalLeads) * 100;
                                                html += `<span class='show-persentage'>${percentage.toFixed(2)}% </span>`;
                                            } else {
                                                html += "<span class='show-persentage'>0.00% </span>";
                                            }
                                        } else {
                                            html += "<span class='show-persentage'>0.00% </span>";
                                        }


                                        html += '</h3>';
                                        html += `<span style="color:${conversion.color}">${conversion.name}</span></div>`;
                                    });

                                    html += `<div class="col-md-3 col-xs-6 marketing-type border-right"><h3 class="bold">${per_percentage}`;
                                    if (per_percentage > 0) {
                                        html += `<span class="show-persentage">${((per_percentage / totalLeads) * 100).toFixed(2)}% </span>`;
                                    } else {
                                        html += "<span class='show-persentage'>0.00% </span>";
                                    }
                                    html += '</h3><span>Total</span></div>';

                                    html += '<br><hr class="hr-3" style="width: 100%; margin-top: 20px!important; display: inline-block;">';
                                });

                                resolve(html);
                            });
                        }

                        async function processAssignedData(data, assigned, index, status_list, source_summary, source_type) {
                            $("#show_hide_staff_list").removeClass("hide");
                            $("#generate_excel").show();
                            // excel_data_array = [];
                            total_staff_report_array = [];
                            total_staff_report_array["status"] = {};
                            total_staff_report_array["source"] = {};
                            total_staff_report_array["conversion"] = {};
                            total_staff_report_array["performance"] = {};
                            for (let i = 0; i < assigned.length; i++) {
                                const assignedId = assigned[i];
                                // console.log(staff[assignedId]);
                                let ret = '';
                                let staff_name = staff[assignedId] || 'Unknown';
                                let assigned_summary = [];
                                totalLeads = 0;
                                let conversion_type_set = []


                                // // Initialize the object for the staff_name if it doesn't exist
                                // if (!excel_data_array[staff_name]) {
                                //     excel_data_array[staff_name] = [];
                                //     excel_data_array[staff_name] = {};
                                //     excel_data_array[staff_name]["conversion_data"] = [];
                                //     excel_data_array[staff_name]["performance_data"] = [];
                                // }

                                // // Retrieve the data for the assignedId, defaulting to an empty array if not present
                                // var assignedData = data.excel_data[assignedId] || [];

                                // // Ensure that assignedData is an array before pushing to excel_data_array
                                // if (assignedData && typeof assignedData === 'object' && !Array.isArray(assignedData)) {
                                //     // Assign the object to excel_data_array[staff_name]
                                //     excel_data_array[staff_name] = assignedData;
                                // } else {
                                //     console.warn(`Data for assignedId ${assignedId} is not an object and will be skipped.`);
                                // }


                                // // Initialize conversion_data and performance_data for the staff_name if needed
                                // if (!excel_data_array[staff_name]["conversion_data"]) {
                                //     excel_data_array[staff_name]["conversion_data"] = [];
                                // }

                                // if (!excel_data_array[staff_name]["performance_data"]) {
                                //     excel_data_array[staff_name]["performance_data"] = [];
                                // }


                                if (!isEmpty(index) && index % 2 === 0) {
                                    ret += '<div class="break-page" style="page-break-before: always;"></div>';
                                }

                                ret += `<div class="col-md-12 report-data mt-3 panel_s row row-flex panel-body">
            <h4><b>${capitalizeFirstLetter(staff_name)}</b> 
            <a href="#" class="btn  filter-hide btn-default btn-with-tooltip daily-update-count" 
               data-staffid="${assignedId}" 
               data-toggle="tooltip" 
               data-title="Update Count" 
               data-placement="bottom" 
               onclick="daily_update_count('.leads-overview-${assignedId}', ${assignedId}); return false;">
               <i class="fa fa-bar-chart"></i>
            </a>
            </h4>
            <div class="row leads-overview-${assignedId}" style="display:none;">
            </div>
            <hr>
            <div class="col-md-6">
                ${await generateLeadTypeHTML(assignedId,conversion_type_set)}
            </div>
            <div class="col-md-6">
                ${await generateSourceTypeHTML(assignedId)}
            </div>
            <div class="col-md-12 parrent-div " style="margin-top:10px;">
                <div class="col-12 text-right" style="margin:5px;">
                    <button type="checked" class="btn btn-lg btn-toggle btn-switch-toggle" data-toggle="button" aria-pressed="false" autocomplete="off">
                        <div class="handle"></div>
                    </button>
                </div>
                ${await generateConversionTypeHTML(totalLeads,conversion_type_set,staff_name)}
                <div class="col-12 panel-body" class="con_tab" style="display:none;">
                    <h4><b>Marketing Type</b></h4><hr>
                    ${await generatePerformanceTypeHTML(assignedId)}
                </div>
            </div>
        </div>`;

                                $(".report_list").append(ret);

                                $("#total_staff_list").append(`
                                <div class="col-md-3 col-xs-6 border-right">
                                <h3 class="bold">${staff_total_lead}</h3>
                                <span>${capitalizeFirstLetter(staff_name)}</span>
                                </div>
                                `);



                            }


                            set_total_report();
                            // generate_excel_data(data, assigned);

                        }




                        function _l(key) {
                            return key;
                        }

                        function isEmpty(value) {
                            return value === undefined || value === null || value === '';
                        }

                        // console.log(data.excel_data);
                        processAssignedData(data, assigned, index, status_list, source_summary, source_type);
                        return;
                    } else if (data.report_list == 0) {
                        $("#generate_excel").show();
                        let all_status = data.summary;
                        console.lo
                        total_staff_report_array = [];
                        total_staff_report_array["status"] = {};
                        total_staff_report_array["source"] = {};
                        total_staff_report_array["conversion"] = {};
                        total_staff_report_array["performance"] = {};
                        total_staff_report_array["total"] = 0;

                        // Initialize status array
                        status_list.forEach(s => {
                            total_staff_report_array["status"][s.name] = 0;
                        });

                        // Initialize source array and related performance array
                        source_type.forEach(so => {
                            total_staff_report_array["source"][so.name] = 0;

                            // Ensure marketing_type and its corresponding name exist before accessing them
                            if (performance_array_name[so.marketing_type] && performance_array_name[so.marketing_type].name) {
                                total_staff_report_array["performance"][performance_array_name[so.marketing_type].name] = {};
                            }
                        });

                        // Initialize conversion array
                        conversion_type.forEach(co => {
                            if (co.parents !== "") {
                                total_staff_report_array["conversion"][co.name] = 0;
                            }
                        });

                        // Initialize performance array and nested conversion types
                        Object.keys(total_staff_report_array["performance"]).forEach(performanceKey => {
                            conversion_type.forEach(coo => {
                                if (coo.parents !== "") {
                                    total_staff_report_array["performance"][performanceKey][coo.name] = 0;
                                }
                            });
                        });

                        // Log the initialized structure
                        console.log(total_staff_report_array);


                        // Iterate over the summary to parse status counts
                        all_status.forEach(statusItem => {
                            // console.log(statusItem);
                            // Parse status_counts if it exists, otherwise set to an empty object
                            let statusCounts = statusItem.status_counts ? JSON.parse(statusItem.status_counts) : {};
                            Object.keys(statusCounts).forEach(status => {

                                // Add each key-value pair to the total_staff_report_array["status"]
                                if (!total_staff_report_array["status"][status]) {
                                    total_staff_report_array["status"][status] = 0;
                                }
                                total_staff_report_array["status"][status] += statusCounts[status];
                                total_staff_report_array["status"]["Total Lead Status"] = total_staff_report_array["status"]["Total Lead Status"] ? total_staff_report_array["status"]["Total Lead Status"] + parseInt(statusCounts[status]) : parseInt(statusCounts[status]);

                                if (status_name_color[status].conversion_type > 0) {
                                    total_staff_report_array["total"] = total_staff_report_array["total"] ? total_staff_report_array["total"] + parseInt(statusCounts[status]) : parseInt(statusCounts[status]);
                                }
                            });
                        });


                        // Iterate over the source summary to add source totals
                        data.source_summary.forEach(source_summary => {
                            total_staff_report_array["source"][source_summary.source_name] = source_summary.total ? source_summary.total : 0;
                            let total = source_summary.total ? source_summary.total : 0;
                            total_staff_report_array["source"]["Total Lead Source"] = total_staff_report_array["source"]["Total Lead Source"] ? total_staff_report_array["source"]["Total Lead Source"] + parseInt(total) : parseInt(total);
                        });

                        // Iterate over the status_summary_conversion to add conversion data
                        Object.keys(data.status_summary_conversion).forEach(summary_conversion => {
                            data.status_summary_conversion[summary_conversion].forEach(conversion_ => {
                                let conversionName = conversion_.conversion_name;
                                if (conversionName !== "") {
                                    if (!total_staff_report_array["conversion"][conversionName]) {
                                        total_staff_report_array["conversion"][conversionName] = 0;
                                    }
                                    total_staff_report_array["conversion"][conversionName] += parseInt(conversion_.total) || 0;

                                    // excel_array[staff[assigned]]["conversion_data"][source.name + "-" + source.marketing_name] = status_count;

                                }
                            });
                        });

                        // Iterate over the performance array to add performance data
                        performance_array.forEach(per => {
                            Object.keys(data.status_summary_performance).forEach(summary_performance => {
                                data.status_summary_performance[summary_performance].forEach(performance_ => {
                                    let conversionName = performance_.conversion_name;
                                    if (conversionName !== "" && performance_.marketing_name == per.name) {
                                        if (!total_staff_report_array["performance"][per.name]) {
                                            total_staff_report_array["performance"][per.name] = {};
                                        }
                                        if (!total_staff_report_array["performance"][per.name][conversionName]) {
                                            total_staff_report_array["performance"][per.name][conversionName] = 0;
                                        }
                                        total_staff_report_array["performance"][per.name][conversionName] += parseInt(performance_.total) || 0;
                                    }
                                });
                            });
                        });

                        console.log(total_staff_report_array);
                        set_total_report();
                        return;
                    }



                    function set_total_report() {
                        // Ensure total_staff_report and status_list are defined
                        if (!total_staff_report_array || !status_list) {
                            console.error("Missing required data: total_staff_report_array or status_list.");
                            return;
                        }

                        $(".total_staff_report").removeClass("hide");

                        let html = `<h4 class="bold">Total</h4>
                                        <hr>
                            <div class="col-md-6">
                    <div class="col-12 panel-body">
                        <h4><b>Leads Types</b></h4>
                        <hr>`;

                        status_list.forEach(status => {
                            // Ensure the status name exists in the total_staff_report_array
                            let statusCount = total_staff_report_array["status"][status.name] || 0;

                            html += `<div class="col-md-3 col-xs-6 border-right">
                            <h3 class="bold">${statusCount}</h3>
                            <span style="color:${status.color}">${status.name}</span>
                            </div> `;
                        });

                        html += `</div>
                                            </div> `;
                        // start source
                        html += `<div class="col-md-6">
                            <div class="col-12 panel-body" class="con_tab"><h4><b>Source Type</b></h4><hr>`;
                        source_type.forEach(source => {
                            let statusCount = total_staff_report_array["source"][source.name] || 0;
                            html += `<div class="col-md-3 col-xs-6 border-right">
                            <h3 class="bold">${statusCount}</h3>
                            <span style="color:${source.color_name}">${source.name}</span>
                            </div>`;
                        });
                        html += `</div>`;

                        // CONVERSION START
                        html += `<div class="col-md-12 parrent-div " style="margin-top:10px;">
                            <div class="col-12 text-right" style="margin:5px;">
                            <button type="checked" class="btn btn-lg btn-toggle btn-switch-toggle" data-toggle="button" aria-pressed="false" autocomplete="off">
                            <div class="handle"></div>
                            </button>
                            </div>
                            <div class="col-12 panel-body" class="con_tab"><h4><b>Conversion Type</b></h4><hr>`;
                        conversion_type.forEach(conversion => {
                            if (conversion.parent_id) {
                                return;
                            }
                            html += '<div style="" class="col-md-3 col-xs-6 border-right"><h3 class="bold">';
                            let percentage = (total_staff_report_array["conversion"][conversion.name] || 0);
                            html += percentage;

                            if (percentage > 0) {
                                if (total_staff_report_array["total"] !== 0) {
                                    percentage = (percentage / total_staff_report_array["total"]) * 100;
                                    html += `<span class='show-persentage'>${percentage.toFixed(2)}% </span>`;
                                } else {
                                    html += "<span class='show-persentage'>0.00% </span>";
                                }
                            } else {
                                html += "<span class='show-persentage'>0.00% </span>";
                            }
                            html += `</h3><span style="color:${conversion.color || ''}">${conversion.name || ''}</span></div>`;


                        });

                        html += `</div><div class="col-12 panel-body" class="con_tab" style="display:none;">
                                <h4><b>Marketing Type</b></h4><hr>`;
                        Object.values(performance_array).forEach((per, key) => {
                            html += '<div class="col-md-12 col-xs-12 "><h3 class="bold">';
                            html += `<span style="color:${per.color}">${per.name}</span></h3></div>`;
                            let per_percentage = 0;
                            conversion_type.forEach(conversion => {
                                if (conversion.parent_id) {
                                    return;
                                }
                                html += '<div class="col-md-3 col-xs-6 marketing-type border-right"><h3 class="bold">';
                                let percentage = total_staff_report_array["performance"][per.name][conversion.name];
                                total_staff_report_array["performance"][per.name][conversion.name]["total"] = total_staff_report_array["performance"][per.name][conversion.name]["total"] ? total_staff_report_array["performance"][per.name][conversion.name]["total"] + percentage : percentage;
                                html += percentage;
                                per_percentage += percentage;
                                if (percentage > 0) {
                                    if (total_staff_report_array["total"] !== 0) {
                                        percentage = (percentage / total_staff_report_array["total"]) * 100;
                                        html += `<span class='show-persentage'>${percentage.toFixed(2)}% </span>`;
                                    } else {
                                        html += "<span class='show-persentage'>0.00% </span>";
                                    }
                                } else {
                                    html += "<span class='show-persentage'>0.00% </span>";
                                }


                                html += '</h3>';
                                html += `<span style="color:${conversion.color}">${conversion.name}</span></div>`;
                            });

                            html += `<div class="col-md-3 col-xs-6 marketing-type border-right"><h3 class="bold">${per_percentage}`;
                            if (per_percentage > 0) {
                                html += `<span class="show-persentage">${((per_percentage / total_staff_report_array["total"]) * 100).toFixed(2)}% </span>`;
                            } else {
                                html += "<span class='show-persentage'>0.00% </span>";
                            }
                            html += '</h3><span>Total</span></div>';

                            html += '<br><hr class="hr-3" style="width: 100%; margin-top: 20px!important; display: inline-block;">';
                        });
                        html += ` </div>`;

                        html += `</div>`;

                        $(".total_staff_report").html(html);
                        return;
                    }


                }
            });

        }


        // function generate_excel_data(data) {

        //     for (let i = 0; i < data.assigned.length; i++) {
        //         const assignedId = assigned[i];
        //         let staff_name = staff[assignedId] || 'Unknown';
        //         // Initialize the object for the staff_name if it doesn't exist
        //         if (!excel_data_array[staff_name]) {
        //             excel_data_array[staff_name] = [];
        //             excel_data_array[staff_name] = {};
        //             excel_data_array[staff_name]["conversion_data"] = [];
        //             excel_data_array[staff_name]["performance_data"] = [];
        //         }

        //         Object.keys(data.status_summary_conversion).forEach(summary_conversion => {

        //             data.status_summary_conversion[summary_conversion].forEach(conversion_ => {
        //                 let conversionName = conversion_.conversion_name;
        //                 let sourceName = conversion_.source_name;
        //                 if (sourceName != "" && conversionName != "") {
        //                     // Construct the key for the conversion data
        //                     let key = sourceName + "-" + conversionName;

        //                     // Ensure the property is initialized before adding the total
        //                     if (!excel_data_array[staff[summary_conversion]]["conversion_data"][key]) {
        //                         excel_data_array[staff[summary_conversion]]["conversion_data"][key] = 0;
        //                     }

        //                     // Add the total to the existing value, parsing it as an integer (or float)
        //                     excel_data_array[staff[summary_conversion]]["conversion_data"][key] += parseInt(conversion_.total, 10);
        //                 }
        //             });

        //             // console.log(staff_name);

        //             data.status_summary_performance[summary_conversion].forEach(performance_ => {

        //                 let conversionName = performance_.conversion_name;
        //                 let marketingName = performance_.marketing_name;
        //                 if (marketingName != "" && conversionName != "") {
        //                     // Construct the key for the performance data
        //                     let key = marketingName + "-" + conversionName;

        //                     // Ensure the property is initialized before adding the total
        //                     if (!excel_data_array[staff[summary_conversion]]["performance_data"][key]) {
        //                         excel_data_array[staff[summary_conversion]]["performance_data"][key] = 0;
        //                     }

        //                     // Add the total to the existing value, parsing it as an integer (or float)
        //                     excel_data_array[staff[summary_conversion]]["performance_data"][key] += parseInt(performance_.total, 10);
        //                 }
        //             });
        //             // console.log(excel_data_array);
        //         });

        //     }
        // }

        function generate_excel_data(data) {
            console.log("Generating Excel data...");

            return new Promise((resolve, reject) => {
                try {
                    // Check if data and necessary properties exist and are in correct format
                    if (!data || typeof data !== 'object') {
                        throw new Error("Invalid input: 'data' should be an object.");
                    }

                    if (!Array.isArray(data.assigned)) {
                        throw new Error("Invalid data format: 'assigned' should be an array.");
                    }
                    if (data.assigned.length > 0) {
                        data.assigned.forEach((assignedId, index) => {
                            let staff_name = staff[assignedId] || 'Total Details';
                            excel_data_array_total[staff_name] = 0;

                            // Initialize the object for the staff_name if it doesn't exist
                            if (!excel_data_array[staff_name]) {
                                excel_data_array[staff_name] = {
                                    conversion_data: {},
                                    performance_data: {}
                                };
                            }

                            // Validate and copy summary data if available
                            if (data.summary && data.summary[assignedId]) {

                                // Assuming data.summary[assignedId] is an object or array of objects
                                Object.values(data.summary[assignedId]).forEach(summ => {
                                    if (summ.conversion_id > 0) {
                                        excel_data_array_total[staff_name] += parseInt(summ.total, 10) || 0;
                                    }
                                });

                                Object.assign(excel_data_array[staff_name], data.summary[assignedId]);
                            } else {
                                console.warn(`No summary found for assignedId ${assignedId}`);
                            }

                            // Process conversion data if available
                            if (data.status_summary_conversion && data.status_summary_conversion[assignedId]) {

                                data.status_summary_conversion[assignedId].forEach(conversion_ => {
                                    let conversionName = conversion_.conversion_name;
                                    let sourceName = conversion_.source_name;

                                    if (sourceName && conversionName) {
                                        // Construct the key for the conversion data
                                        let key = `${sourceName}-${conversionName}`;

                                        // Initialize if necessary and add the total
                                        if (!excel_data_array[staff_name]["conversion_data"][key]) {
                                            excel_data_array[staff_name]["conversion_data"][key] = 0;
                                        }
                                        excel_data_array[staff_name]["conversion_data"][key] += parseInt(conversion_.total, 10);
                                        // Ensure conversion_total[staff_name] is initialized
                                        if (!conversion_total[staff_name]) {
                                            conversion_total[staff_name] = {};
                                        }

                                        // Ensure conversion_total[staff_name][sourceName] is initialized
                                        if (!conversion_total[staff_name][sourceName]) {
                                            conversion_total[staff_name][sourceName] = 0;
                                        }

                                        // Add the total to the conversion data, ensuring it's parsed as an integer
                                        conversion_total[staff_name][sourceName] += parseInt(conversion_.total, 10) || 0;

                                    } else {
                                        console.warn(`Invalid conversion data for assignedId ${assignedId}:`, conversion_);
                                    }
                                });
                            } else {
                                console.warn(`No conversion data found for assignedId ${assignedId}`);
                            }

                            // Process performance data if available
                            if (data.status_summary_performance && data.status_summary_performance[assignedId]) {
                                data.status_summary_performance[assignedId].forEach(performance_ => {
                                    let conversionName = performance_.conversion_name;
                                    let marketingName = performance_.marketing_name;

                                    if (marketingName && conversionName) {
                                        // Construct the key for the performance data
                                        let key = `${marketingName}-${conversionName}`;

                                        // Initialize if necessary and add the total
                                        if (!excel_data_array[staff_name]["performance_data"][key]) {
                                            excel_data_array[staff_name]["performance_data"][key] = 0;
                                        }
                                        excel_data_array[staff_name]["performance_data"][key] += parseInt(performance_.total, 10);
                                    } else {
                                        console.warn(`Invalid performance data for assignedId ${assignedId}:`, performance_);
                                    }
                                });
                            } else {
                                console.warn(`No performance data found for assignedId ${assignedId}`);
                            }
                        });
                    } else {
                        data.assigned.push("Total Details");
                        data.assigned.forEach((assignedId, index) => {
                            let staff_name = staff[assignedId] || 'Unknown';
                            excel_data_array_total[staff_name] = 0;

                            // Initialize the object for the staff_name if it doesn't exist
                            if (!excel_data_array[staff_name]) {
                                excel_data_array[staff_name] = {
                                    conversion_data: {},
                                    performance_data: {}
                                };
                            }

                            // Validate and copy summary data if available
                            if (data.summary && data.summary[assignedId]) {

                                // Assuming data.summary[assignedId] is an object or array of objects
                                Object.values(data.summary[assignedId]).forEach(summ => {
                                    if (summ.conversion_id > 0) {
                                        excel_data_array_total[staff_name] += parseInt(summ.total, 10) || 0;
                                    }
                                });

                                Object.assign(excel_data_array[staff_name], data.summary[assignedId]);
                            } else {
                                console.warn(`No summary found for assignedId ${assignedId}`);
                            }

                            // Process conversion data if available
                            if (data.status_summary_conversion && data.status_summary_conversion[assignedId]) {

                                data.status_summary_conversion[assignedId].forEach(conversion_ => {
                                    let conversionName = conversion_.conversion_name;
                                    let sourceName = conversion_.source_name;

                                    if (sourceName && conversionName) {
                                        // Construct the key for the conversion data
                                        let key = `${sourceName}-${conversionName}`;

                                        // Initialize if necessary and add the total
                                        if (!excel_data_array[staff_name]["conversion_data"][key]) {
                                            excel_data_array[staff_name]["conversion_data"][key] = 0;
                                        }
                                        excel_data_array[staff_name]["conversion_data"][key] += parseInt(conversion_.total, 10);
                                        // Ensure conversion_total[staff_name] is initialized
                                        if (!conversion_total[staff_name]) {
                                            conversion_total[staff_name] = {};
                                        }

                                        // Ensure conversion_total[staff_name][sourceName] is initialized
                                        if (!conversion_total[staff_name][sourceName]) {
                                            conversion_total[staff_name][sourceName] = 0;
                                        }

                                        // Add the total to the conversion data, ensuring it's parsed as an integer
                                        conversion_total[staff_name][sourceName] += parseInt(conversion_.total, 10) || 0;

                                    } else {
                                        console.warn(`Invalid conversion data for assignedId ${assignedId}:`, conversion_);
                                    }
                                });
                            } else {
                                console.warn(`No conversion data found for assignedId ${assignedId}`);
                            }

                            // Process performance data if available
                            if (data.status_summary_performance && data.status_summary_performance[assignedId]) {
                                data.status_summary_performance[assignedId].forEach(performance_ => {
                                    let conversionName = performance_.conversion_name;
                                    let marketingName = performance_.marketing_name;

                                    if (marketingName && conversionName) {
                                        // Construct the key for the performance data
                                        let key = `${marketingName}-${conversionName}`;

                                        // Initialize if necessary and add the total
                                        if (!excel_data_array[staff_name]["performance_data"][key]) {
                                            excel_data_array[staff_name]["performance_data"][key] = 0;
                                        }
                                        excel_data_array[staff_name]["performance_data"][key] += parseInt(performance_.total, 10);
                                    } else {
                                        console.warn(`Invalid performance data for assignedId ${assignedId}:`, performance_);
                                    }
                                });
                            } else {
                                console.warn(`No performance data found for assignedId ${assignedId}`);
                            }
                        });
                    }

                    resolve(); // Indicate successful completion
                } catch (error) {
                    reject(`Error processing data: ${error.message}`); // Handle any errors
                }
            });
        }


        function getColumnLetter(index) {
            let columnLetter = '';
            while (index >= 0) {
                columnLetter = String.fromCharCode((index % 26) + 65) + columnLetter;
                index = Math.floor(index / 26) - 1;
            }
            return columnLetter;
        }

        function generateColumnSeries(rowNumber, count) {
            let series = [];
            for (let i = 0; i < count; i++) {
                let columnLetter = getColumnLetter(i);
                series.push(columnLetter);
            }
            return series;
        }

        var _getColumnLetter = generateColumnSeries(1, 100);

        async function generatePDF() {
            // Choose the element that your content will be rendered to.
            // const element = document.getElementById('pdf_generate');
            // // Choose the element and save the PDF for your user.
            // html2pdf().from(element).save();
            const element = document.getElementById('pdf_generate');
            const options = {
                filename: 'Report.pdf',
                margin: [10, 0, 10, 0],
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    dpi: 192,
                    scale: 2,
                    letterRendering: true,
                    useCORS: true,
                },
                pagebreak: {
                    mode: 'avoid-all',
                    after: '.break-page'
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a3',
                    orientation: 'landscape'
                }
            };
            await html2pdf().set(options).from(element).save();
            // await html2pdf().from(element).set(options).toPdf().output('datauristring').then(function(res) {
            //     console.log(res);
            // });
            // this.blobString = res;
            // await html2pdf().set(options).from(element).save();
            // console.log("sjkcbns");
            // var objWindow = window.open(location.href, "_self");
            // objWindow.close();
        }
    </script>