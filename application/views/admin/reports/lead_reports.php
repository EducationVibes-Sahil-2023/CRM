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
                                    <a href="#" class="btn btn-default btn-with-tooltip hide-graph hide-graph-calls hide" data-toggle="tooltip" data-title="<?php echo _l('Calls Leads Chart'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview-calls'); return false;">Show Calls Chart <i class="fa fa-bar-chart"></i></a>
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
                                    <a href="#" class="btn btn-default btn-with-tooltip hide-graph hide-graph-daily hide" data-toggle="tooltip" data-title="<?php echo _l('Calls Leads Chart'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview-calls-daily'); return false;">Show Date Wise Chart <i class="fa fa-bar-chart"></i></a>

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

        // Convert all colors in conversion_type_color to rgba
        // for (let name in conversion_type_color) {
        //     if (conversion_type_color.hasOwnProperty(name)) {
        //         conversion_type_color[name] = hexToRgba(conversion_type_color[name]);
        //     }
        // }

        // console.log(conversion_type_color); // Output the converted colors


        var source_name = <?= !empty($sources) ? json_encode($sources, true) : '' ?>;
        var status_name = <?= !empty($status) ? json_encode($status, true) : '' ?>;
        var conversion_type = <?= !empty($conversion_type) ? json_encode($conversion_type, true) : '' ?>;
        var marketing_type = <?= !empty($marketing_type) ? json_encode($marketing_type, true) : '' ?>;
        var excel_data_array = [];
        var summary_daily_excel = [];
        const max_count = 30;
        const max = 30;

        // Initialize an object to store chart instances
        if (!window.myCharts) {
            window.myCharts = {};
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
            // Parse the year and week number from the input
            const year = parseInt(yearWeek.slice(0, 4), 10);
            const week = parseInt(yearWeek.slice(4), 10);

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
            $('#apply_filter').trigger("click");
        })



        function RunExcelJSExport() {
            // RunExcelJSExport_()
            RunExcelJSExport__();
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

            excel_data_array_.forEach(function(item) {
                if (item.key == 1) {
                    let worksheet = workbook.addWorksheet(item.name);
                    let con_index = 0;
                    Object.keys(excel_data_array).forEach(function(key) {

                        let excel_data = excel_data_array[key];
                        // set up some data
                        console.log(excel_data)
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
                            for (let i = 2; i < (source_name.length); i++) {

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

                        for (j = con_index + 1; j < ((con_index) + (status_name.length)); j++) {
                            console.log(j);
                            console.log((con_index) + (status_name.length));
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
                        for (j = con_index + 1; j < ((con_index) + (status_name.length)); j++) {
                            let index = 0;
                            for (let i = 2; i < (source_name.length); i++) {
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
                            for (let i = 2; i < (source_name.length); i++) {
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
                            for (let i = 2; i < (source_name.length); i++) {
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
                            for (let i = 2; i < (conversion_type.length); i++) {
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
                            for (let i = 2; i < (conversion_type.length); i++) {
                                if (conversion_type[index].name != undefined && marketing_type[con_index_mar].name != undefined) {
                                    worksheet.getCell("A" + j).value = key;
                                    worksheet.getCell("A" + j).font = {
                                        bold: true,
                                    };
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


        // // Create a new workbook
        // var workbook = new ExcelJS.Workbook();

        // // Add a worksheet to the workbook
        // var worksheet = workbook.addWorksheet('My Sheet');

        // // Add some data to the worksheet
        // worksheet.columns = [{
        //         header: 'Name',
        //         key: 'name'
        //     },
        //     {
        //         header: 'Age',
        //         key: 'age'
        //     },
        //     {
        //         header: 'Gender',
        //         key: 'gender'
        //     }
        // ];

        // worksheet.addRow({
        //     name: 'John',
        //     age: 30,
        //     gender: 'Male'
        // });
        // worksheet.addRow({
        //     name: 'Jane',
        //     age: 25,
        //     gender: 'Female'
        // });
        // worksheet.addRow({
        //     name: 'Bob',
        //     age: 40,
        //     gender: 'Male'
        // });

        // // Save the workbook
        // workbook.xlsx.writeBuffer().then(function(buffer) {
        //     // Create a blob from the buffer
        //     var blob = new Blob([buffer], {
        //         type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        //     });

        //     // Create a URL for the blob
        //     var url = window.URL.createObjectURL(blob);

        //     // Create a link to download the file
        //     var a = document.createElement('a');
        //     a.href = url;
        //     a.download = 'my-workbook.xlsx';
        //     document.body.appendChild(a);

        //     // Click the link to download the file
        //     a.click();

        //     // Remove the link
        //     document.body.removeChild(a);
        // });

        $(document).on('click', '.btn-switch-toggle', function() {
            var parentDiv = $(this).closest(".parrent-div");
            parentDiv.find(".panel-body").toggle();
        });

        //based on:
        //https://github.com/chartjs/chartjs-plugin-zoom/blob/master/samples/zoom-time.html


        // window.onload = function() {
        //     var ctx = document.getElementById("canvas").getContext("2d");
        //     window.myLine = new Chart(ctx, config);
        // };
        $(".filter_reset select").change(function() {
            var selectedValue = $(this).val();
            if ($(this).attr("id") == "view_assigned") {
                $(".filter_reset select").not(this).prop('disabled', true);
            } else {
                $("#view_assigned").prop('disabled', true);
            }
            if (selectedValue.length == 0) {
                $(".filter_reset select").prop('disabled', false);
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
                $('#apply_filter').trigger("click");
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
        var total_staff_report_array = [];
        total_staff_report_array["status"] = {};
        total_staff_report_array["source"] = {};
        // var conversion_type_set;

        $('#apply_filter').on('click', function() {
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
            if (slider_data) {
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

            if (xhr != null) {
                xhr.abort();
            }
            $("#generate_pdf").hide();
            $(".hide-btn-response").hide();
            $(".report-data .hide-graph").addClass("hide");
            $("#show_hide_staff_list").addClass("hide");

            // $(".leadSum").html('');
            $('#apply_filter').attr("disabled", true);
            show_loader("apply_filter");
            xhr = $.ajax({
                type: "POST",
                url: admin_url + "reports/lead_summary_filter",
                data: {
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
                    assign_to_date: assign_to_date

                },
                dataType: "JSON",
                cache: false,
                success: function(data) {
                    $('#apply_filter').attr("disabled", false);
                    $('#apply_filter_update_count').attr("disabled", false);
                    hide_loader("apply_filter");
                    hide_loader("apply_filter_update_count");
                    //alert(data);  //as a debugging message.


                    if (data.update_count_daily_data != undefined) {
                        let html_update = "<div class='row scroll-div col-12'>";
                        for (i = 1; i < (data.update_count_daily_data).length; i++) {
                            html_update += "<div class='col-md-3 show-daily-update'><p>Date : " + data.update_count_daily_data[i].uni_dates + "</p><br><p>Update Count : " + data.update_count_daily_data[i].total + "</p><br><p>Call Duration : " + convertToHMS(data.update_count_daily_data[i].call_duration) + "</p></div>";
                        }
                        html_update += "</div>";
                        $(".leads-overview-" + update_daily_staff_id).html(html_update);
                        $(".leads-overview-" + update_daily_staff_id).removeClass("hide");
                        update_daily_staff_id = 0;
                    }


                    if (data.report_list == 1) {
                        $(".leadSum").html("");
                        var index = 1;
                        let {
                            source_summary = {}, source_type = [], conversion_type, status_list = [], assigned = [], staff = [], summary = [], status_summary_performance = [], performance_array = []
                        } = data;
                        let conversion_type_ = data.conversion_type_;
                        status_list.push({
                            name: 'Total Lead Status',
                            color: "#28b8da"
                        });

                        source_type.push({
                            name: 'Total Lead Source',
                            color: "#28b8da"
                        });



                        function capitalizeFirstLetter(string) {
                            return string.charAt(0).toUpperCase() + string.slice(1);
                        }

                        function calculateTotalLeads(assigned_summary) {
                            return Object.values(assigned_summary).reduce((sum, count) => sum + count, 0);
                        }

                        function generateLeadTypeHTML(assigned, conversion_type_set) {
                            console.log("set-status", staff[assigned]);
                            return new Promise((resolve) => {

                                let ret = `<div class="col-12 panel-body"><h4><b>Leads Types</b></h4><hr>`;

                                staff_total_lead = 0
                                // if (summary && summary[assigned] && summary[assigned].status_counts) {
                                assigned_summary = [];
                                if (summary && summary[assigned] && summary[assigned].status_counts) {
                                    assigned_summary = JSON.parse(summary[assigned].status_counts);
                                    staff_total_lead = totalLeads = Object.values(assigned_summary).reduce((sum, count) => sum + count, 0);
                                    assigned_summary["Total Lead Status"] = totalLeads;

                                    total_staff_report_array["status"]["Total Lead Status"] = total_staff_report_array["status"]["Total Lead Status"] ? total_staff_report_array["status"]["Total Lead Status"] : totalLeads;

                                }


                                totalLeads = 0;
                                // console.log(staff_total_lead);
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
                                    total_staff_report_array["status"][status.name] += status_count;

                                    if (status.conversion_type > 0) {
                                        totalLeads += status_count;

                                        if (conversion_type_set) {
                                            conversion_type_set[status.conversion_type] = conversion_type_set[status.conversion_type] ?
                                                conversion_type_set[status.conversion_type] + status_count :
                                                status_count;
                                        }
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
                            console.log("set-source", staff[assigned]);

                            source_type["Total Lead Source"] = 0;
                            return new Promise((resolve) => {
                                let ret = `<div class="col-12 panel-body"><h4><b>Sources Types</b></h4><hr>`;


                                source_type.forEach(source => {
                                    let source_key = assigned + "-" + source.id;
                                    let status_count = (source_summary[source_key] && source_summary[source_key].total) || 0;
                                    source_type["Total Lead Source"] += parseInt(status_count);
                                    if (source.name == "Total Lead Source") {
                                        status_count = source_type["Total Lead Source"];
                                        total_staff_report_array["source"]["Total Lead Source"] = total_staff_report_array["source"]["Total Lead Source"] ? total_staff_report_array["source"]["Total Lead Source"] : status_count;

                                    }

                                    // Ensure the 'status' property is initialized
                                    if (!total_staff_report_array["source"]) {
                                        total_staff_report_array["source"] = {};
                                    }
                                    // Initialize the specific status if it's not already defined
                                    if (!total_staff_report_array["source"][source.name]) {
                                        total_staff_report_array["source"][source.name] = 0;
                                    }
                                    // Add the status_count to the existing value
                                    total_staff_report_array["source"][source.name] += parseInt(status_count);



                                    ret += `<div class="col-md-3 col-xs-6 border-right">
                    <h3 class="bold">${source_summary[source_key] && source_summary[source_key].percent ? `<span data-toggle="tooltip" data-title="${status_count}">${source_summary[source_key].percent}%</span>` : status_count}</h3>
                    <span style="color:${source.color_name}">${source.name}</span>
                </div>`;

                                });

                                ret += `</div>`;
                                resolve(ret);
                            });
                        }


                        function generateConversionTypeHTML(totalLeads, conversion_type_set) {
                            console.log("set-conversion", staff[assigned]);

                            return new Promise((resolve) => {
                                let ret = '<div class="col-12 panel-body" class="con_tab"><h4><b>Conversion Type</b></h4><hr>';
                                conversion_type.forEach(conversion => {
                                    if (conversion.parent_id && conversion.parent_id !== "") {
                                        return;
                                    }
                                    let percentage = conversion_type_set[conversion.id] || 0;
                                    ret += '<div style="" class="col-md-3 col-xs-6 border-right"><h3 class="bold">';
                                    ret += percentage.toFixed(2);
                                   

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
                            console.log("set-performance", staff[assigned]);

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
                            for (let i = 0; i < assigned.length; i++) {
                                const assignedId = assigned[i];
                                // console.log(staff[assignedId]);
                                let ret = '';
                                let staff_name = staff[assignedId] || 'Unknown';
                                let assigned_summary = [];
                                totalLeads = 0;
                                let conversion_type_set = []

                                // console.log(conversion_type_set);
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
                ${await generateConversionTypeHTML(totalLeads,conversion_type_set)}
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
                        }


                        function _l(key) {
                            return key;
                        }

                        function isEmpty(value) {
                            return value === undefined || value === null || value === '';
                        }

                        processAssignedData(data, assigned, index, status_list, source_summary, source_type);
                    }
                }
            });

        });



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