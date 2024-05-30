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
                                        <div id="total_staff_list" class="col-12 panel-body leadSum mt-3">
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
                                <div class="leadSum">
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



        $("#apply_filter_update_count").click(function() {
            slider_data = true;
            $('#apply_filter').trigger("click");
        })
        $('#apply_filter').on('click', function() {
            var element_view_assign = document.getElementById("view_assigned");
            var element_view_source = document.getElementById("view_source");
            var element_view_status = document.getElementById("view_status");
            var element_view_fb_name = document.getElementById("view_facebook_names");
            var element_view_google_type = document.getElementById("view_source_marketing");
            var location = document.getElementById("location");
            var date_type = document.getElementById("date_type").value;
            <?php if (is_admin()) { ?>
                var department = document.getElementById("department");
            <?php } else if ($role == 3 && $staff_department != "") { ?>
                var department = "<?= $staff_department ?>";
            <?php } ?>

            var up_from_date = document.getElementById("up_from_date").value;
            var up_to_date = document.getElementById("up_to_date").value;
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
                view_department = "<?= $staff_department ?>";
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
                    date_type: date_type

                },
                dataType: "JSON",
                cache: false,
                success: function(data) {
                    $('#apply_filter').attr("disabled", false);
                    $('#apply_filter_update_count').attr("disabled", false);
                    hide_loader("apply_filter");
                    hide_loader("apply_filter_update_count");
                    //alert(data);  //as a debugging message.
                    if (data.status != undefined) {
                        // $(".leadSum").html('');
                        // $(".leadSum").innerHTML = data.status;
                        $(".leadSum").html(data.status);
                        $(".filter-hide").removeClass("hide");
                    }
                    // $("#updationCounter").html(data.update_count);
                    slider_data = false;
                    if (data.status != "") {
                        $("#generate_pdf").show();
                        $(".hide-btn-response").show();
                    }
                    if (data.excel_data != undefined) {
                        excel_data_array = data.excel_data;
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


                    if (data.update_count_label != undefined && data.update_count_label.length > 0) {
                        $("#show_hide_staff_list").removeClass("hide");
                        $("#total_staff_list").html(data.total_staff_html);
                        $(".hide-graph-calls").removeClass("hide");

                        var configCalls = {
                            type: "bar",
                            data: {
                                labels: data.update_count_label, // Date Objects
                                datasets: [{
                                        label: "Filtered",
                                        backgroundColor: "rgba(240, 140, 121, 0.8)",
                                        borderColor: "rgba(140, 140, 140, 1.0)",
                                        borderWidth: 0,
                                        data: data.update_count_min,
                                        fill: false,
                                        radius: 0,
                                    },
                                    {
                                        label: "Max",
                                        backgroundColor: "rgba(121, 200, 121, 0.8)",
                                        borderColor: "rgba(140, 140, 140, 0.0)",
                                        borderWidth: 0,
                                        data: data.update_count_max,
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


                    if (data.summary_daily_ != undefined && data.summary_daily_.length > 0) {
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

                        // Assuming data.summary_daily_conversion is an array of objects
                        // if (data.summary_daily_conversion != undefined && data.summary_daily_conversion.length > 0) {
                        //     $(".hide-graph-daily").removeClass("hide");
                        //     var labels = []
                        //     if (date_type.toLowerCase() == "week") {
                        //         labels = data.summary_daily_conversion.map(item => getWeekRange(item.dateadded));
                        //     } else {
                        //         labels = data.summary_daily_conversion.map(item => item.dateadded);
                        //     }

                        //     // Collect unique conversion types and their data
                        //     const conversionData = {};
                        //     data.summary_daily_conversion.forEach(item => {
                        //         const parsedCounts = parseConversionCounts(item.conversion_counts);
                        //         Object.keys(parsedCounts).forEach(type => {
                        //             if (!conversionData[type]) {
                        //                 conversionData[type] = [];
                        //             }
                        //             conversionData[type].push(parsedCounts[type]);
                        //         });
                        //     });

                        //     // Prepare datasets
                        //     const datasets = Object.keys(conversionData).map(type => {
                        //         return {
                        //             label: type,
                        //             backgroundColor: randomColor(), // Function to generate random color
                        //             borderColor: randomColor(),
                        //             borderWidth: 1,
                        //             data: conversionData[type],
                        //             fill: false
                        //         };
                        //     });

                        //     // Create the chart
                        //     const configDaily = {
                        //         type: "bar",
                        //         data: {
                        //             labels: labels,
                        //             datasets: datasets
                        //         },
                        //         options: {
                        //             tooltips: {
                        //                 mode: 'index',
                        //                 intersect: false,
                        //                 displayColors: false,
                        //             },
                        //             responsive: true,
                        //             title: {
                        //                 display: true,
                        //                 text: "Date Wise Conversion Leads chat - Leads"
                        //             },
                        //             scales: {
                        //                 x: {
                        //                     stacked: true,
                        //                 },
                        //                 y: {
                        //                     stacked: true,
                        //                     scaleLabel: {
                        //                         display: true,
                        //                         labelString: "value"
                        //                     }
                        //                 }
                        //             },
                        //             pan: {
                        //                 enabled: true,
                        //                 mode: "x",
                        //                 speed: 10,
                        //                 threshold: 10
                        //             },
                        //             zoom: {
                        //                 enabled: true,
                        //                 drag: false,
                        //                 mode: "xy",
                        //                 limits: {
                        //                     max: 10,
                        //                     min: 0.5
                        //                 }
                        //             }
                        //         }
                        //     };

                        //     // Get the canvas context
                        //     var ctxDaily = document.getElementById("canvas_conversion").getContext("2d");
                        //     createOrUpdateChart("canvas_conversion", ctxDaily, configDaily);


                        // }


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

                        // if (data.summary_daily_marketing != undefined && data.summary_daily_marketing.length > 0) {
                        //     $(".hide-graph-daily").removeClass("hide");
                        //     var labels = []
                        //     if (date_type.toLowerCase() == "week") {
                        //         labels = data.summary_daily_marketing.map(item => getWeekRange(item.dateadded));
                        //     } else {
                        //         labels = data.summary_daily_marketing.map(item => item.dateadded);
                        //     }

                        //     // Collect unique conversion types and their data
                        //     const marketingData = {};
                        //     data.summary_daily_marketing.forEach(item => {
                        //         const parsedCounts = parseConversionCounts(item.marketing_count);
                        //         console.log(parsedCounts);
                        //         Object.keys(parsedCounts).forEach(type => {
                        //             if (!marketingData[type]) {
                        //                 marketingData[type] = [];
                        //             }
                        //             marketingData[type].push(parsedCounts[type]);
                        //         });
                        //     });
                        //     console.log(marketingData);
                        //     // Prepare datasets
                        //     const datasets = Object.keys(marketingData).map(type => {
                        //         return {
                        //             label: labels,
                        //             backgroundColor: randomColor(), // Function to generate random color
                        //             borderColor: randomColor(),
                        //             borderWidth: 1,
                        //             data: marketingData[type],
                        //             fill: false
                        //         };
                        //     });

                        //     // Create the chart
                        //     const configDaily = {
                        //         type: "bar",
                        //         data: {
                        //             labels: labels,
                        //             datasets: datasets
                        //         },
                        //         options: {
                        //             tooltips: {
                        //                 mode: 'index',
                        //                 intersect: false,
                        //                 displayColors: false,
                        //             },
                        //             responsive: true,
                        //             title: {
                        //                 display: true,
                        //                 text: "Date Wise Marketing Leads chat - Leads"
                        //             },
                        //             scales: {
                        //                 x: {
                        //                     stacked: true,
                        //                 },
                        //                 y: {
                        //                     stacked: true,
                        //                     scaleLabel: {
                        //                         display: true,
                        //                         labelString: "value"
                        //                     }
                        //                 }
                        //             },
                        //             pan: {
                        //                 enabled: true,
                        //                 mode: "x",
                        //                 speed: 10,
                        //                 threshold: 10
                        //             },
                        //             zoom: {
                        //                 enabled: true,
                        //                 drag: false,
                        //                 mode: "xy",
                        //                 limits: {
                        //                     max: 10,
                        //                     min: 0.5
                        //                 }
                        //             }
                        //         }
                        //     };

                        //     // Get the canvas context
                        //     var ctxDaily = document.getElementById("canvas_marketing").getContext("2d");
                        //     createOrUpdateChart("canvas_marketing", ctxDaily, configDaily);


                        // }

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


                        // if (data.total_leads_staff != undefined) {


                        //     $(".hide-graph-leads").removeClass("hide");

                        //     var config = {
                        //         type: "bar",
                        //         data: {
                        //             labels: data.total_leads_staff, // Date Objects
                        //             datasets: [{
                        //                 label: "Total leads",
                        //                 backgroundColor: "rgba(240, 140, 121, 0.8)",
                        //                 borderColor: "rgba(140, 140, 140, 1.0)",
                        //                 borderWidth: 0,
                        //                 data: data.total_leads,
                        //                 fill: false,
                        //                 radius: 0,
                        //             }]
                        //         },
                        //         options: {
                        //             tooltips: {
                        //                 mode: 'index',
                        //                 intersect: false,
                        //                 displayColors: false,
                        //             },
                        //             responsive: true,
                        //             title: {
                        //                 display: true,
                        //                 text: "Total Leads chat"
                        //             },
                        //             scales: {
                        //                 x: {
                        //                     stacked: true,
                        //                     format: "HH mm",
                        //                 },
                        //                 y: {
                        //                     stacked: true,
                        //                     scaleLabel: {
                        //                         display: true,
                        //                         labelString: "value"
                        //                     }
                        //                 }
                        //             },
                        //             pan: {
                        //                 enabled: true,
                        //                 mode: "x",
                        //                 speed: 10,
                        //                 threshold: 10
                        //             },
                        //             zoom: {
                        //                 enabled: true,
                        //                 drag: false,
                        //                 mode: "xy",
                        //                 limits: {
                        //                     max: 10,
                        //                     min: 0.5
                        //                 }
                        //             }
                        //         }
                        //     };

                        //     // Get the canvas context
                        //     var ctx = document.getElementById("canvas_").getContext("2d");

                        //     // Destroy the existing chart (if it exists)
                        //     if (window.myLine) {
                        //         window.myLine.destroy();
                        //     }

                        //     // Create a new chart with the updated configuration
                        //     window.myLine = new Chart(ctx, config);

                        // }
                    }
                }
            }); // you have missed this bracket
            return false;
        });

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

        // Create a new workbook and worksheet
        // / create a new workbook and worksheet

        const numberFormat = '#,##0.00'; // Number format pattern
        function RunExcelJSExport() {
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
                    for (let i = 66; i < (66 + source_name.length); i++) {
                        if (source_name[index].name != undefined) {
                            worksheet.getCell(String.fromCharCode(i) + j).value = source_name[index].name;
                            worksheet.getCell(String.fromCharCode(i) + j).font = {
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
                    for (let i = 66; i < (66 + source_name.length); i++) {
                        if (source_name[index].name != undefined && status_name[index_upper].name != undefined) {
                            // worksheet.getCell(String.fromCharCode(i) + j).value = excel_data[0][status_name[index_upper].name + "_" + source_name[index].name].total;
                            let index_name = status_name[index_upper].name + "-" + source_name[index].name;
                            if (excel_data[index_name] != undefined) {
                                worksheet.getCell(String.fromCharCode(i) + j).value = Number(excel_data[index_name].total);
                                worksheet.getCell(String.fromCharCode(i) + j).numFmt = numberFormat;
                                worksheet.getCell(String.fromCharCode(i) + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };
                            } else {
                                worksheet.getCell(String.fromCharCode(i) + j).value = 0;
                                worksheet.getCell(String.fromCharCode(i) + j).numFmt = numberFormat;
                                worksheet.getCell(String.fromCharCode(i) + j).alignment = {
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
                    for (let i = 66; i < (66 + source_name.length); i++) {
                        if (source_name[index].name != undefined) {
                            worksheet.getCell(String.fromCharCode(i) + j).value = source_name[index].name;
                            worksheet.getCell(String.fromCharCode(i) + j).font = {
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
                    for (let i = 66; i < (66 + source_name.length); i++) {
                        if (source_name[index].name != undefined && conversion_type[con_index_upper].name != undefined) {
                            let index_name = source_name[index].name + "-" + conversion_type[con_index_upper].name;
                            if (excel_data["conversion_data"][index_name] != undefined) {
                                worksheet.getCell(String.fromCharCode(i) + j).value = Number(excel_data["conversion_data"][index_name]);
                                worksheet.getCell(String.fromCharCode(i) + j).numFmt = numberFormat;
                                worksheet.getCell(String.fromCharCode(i) + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };
                            } else {
                                worksheet.getCell(String.fromCharCode(i) + j).value = 0;
                                worksheet.getCell(String.fromCharCode(i) + j).numFmt = numberFormat;
                                worksheet.getCell(String.fromCharCode(i) + j).alignment = {
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
                    for (let i = 66; i < (66 + conversion_type.length); i++) {
                        if (conversion_type[index].name != undefined) {
                            worksheet.getCell(String.fromCharCode(i) + j).value = conversion_type[index].name;
                            worksheet.getCell(String.fromCharCode(i) + j).font = {
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
                    for (let i = 66; i < (66 + conversion_type.length); i++) {
                        if (conversion_type[index].name != undefined && marketing_type[con_index_mar].name != undefined) {
                            let index_name = marketing_type[con_index_mar].name + "-" + conversion_type[index].name;
                            if (excel_data["performance_data"][index_name] != undefined) {
                                worksheet.getCell(String.fromCharCode(i) + j).value = Number(excel_data["performance_data"][index_name]);
                                worksheet.getCell(String.fromCharCode(i) + j).numFmt = numberFormat;
                                worksheet.getCell(String.fromCharCode(i) + j).alignment = {
                                    horizontal: 'right',
                                    color: {
                                        argb: "FF0000"
                                    }
                                };
                            } else {
                                worksheet.getCell(String.fromCharCode(i) + j).value = 0;
                                worksheet.getCell(String.fromCharCode(i) + j).numFmt = numberFormat;
                                worksheet.getCell(String.fromCharCode(i) + j).alignment = {
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


            // console.log(letters);
            // console.log(letters);
            // // print the values in cells A1 through D1
            // worksheet.getCell('A1').value = 'Value in A1';
            // worksheet.getCell('B1').value = 'Value in B1';
            // worksheet.getCell('C1').value = 'Value in C1';
            // worksheet.getCell('D1').value = 'Value in D1';


            // Save the workbook as an xlsx file
            workbook.xlsx.writeBuffer().then(function(buffer) {
                // return;
                //   saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'example.xlsx');

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
                    console.log(selectedValue);
                    var elementID = ".hide_show_" + selectedValue;
                    console.log($(elementID).length);
                    if ($(elementID).length > 0) {
                        $(elementID).removeClass("hide");
                    }
                });
            }
        });
    </script>