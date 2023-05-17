<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@3.4.0/dist/exceljs.min.js"></script>
<style>
    .report-data {
        /* padding: 10px !important; */
        /* box-shadow: 0px 0px 10px lightgray; */
        /* margin-top: 20px; */
    }

    /* .leadSum .panel_s .panel-body {
        min-height: 400px;
    } */

    .leadSum .panel_s .panel-body {
        min-height: 350px;
        padding: 10px !important;
    }

    .border-right h3 {
        margin: 10px 0px;
    }

    .leadSum {
        font-size: 14px;
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

    .switch .btn-toggle {
        top: 50%;
        transform: translateY(-50%);
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
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- <form action="<?= base_url("admin/reports/leads_reports_generate") ?>" method="POST"> -->
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <?php if (has_permission('leads', '', 'view')) { ?>
                            <div class="col-md-2 leads-filter-column">
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

                        <div class="col-md-2 leads-filter-column">
                            <select name="lead_type" id="lead_type" class="selectpicker" data-width="100%">
                                <option value="">Select Lead Type</option>
                                <?php foreach ($type as $tp => $vl) {
                                ?>
                                    <option value="<?php echo $vl['id']; ?>"><?php echo $vl['name']; ?></option>
                                <?php } ?>
                            </select>
                            <?php
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
                        <div class="col-md-6 leads-filter-column">
                            <div class="form-group">

                                <button class="btn btn-primary" id="apply_filter">Apply Filter</button>
                                <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                <!-- <button class="btn btn-xs btn-danger hide-btn-response" onclick="generatePDF()" id="generate_pdf" style="display:none;"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Generate Pdf</button> -->
                                <button class="btn btn-xs btn-success hide-btn-response" onclick="RunExcelJSExport()" id="generate_excel" style="display:none;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export to Excel</button>
                            </div>
                        </div>
                        <!-- </form> -->
                    </div>
                </div>
                <!-- <div class="col-md-12">

                </div> -->
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body" id="pdf_generate">
                        <h3>Report Generate</h3>
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

<script>
    var source_name = <?= !empty($sources) ? json_encode($sources, true) : '' ?>;
    var status_name = <?= !empty($status) ? json_encode($status, true) : '' ?>;
    var conversion_type = <?= !empty($conversion_type) ? json_encode($conversion_type, true) : '' ?>;
    var marketing_type = <?= !empty($marketing_type) ? json_encode($marketing_type, true) : '' ?>;
    var excel_data_array = [];
    // const workbook = new ExcelJS.Workbook();

    var xhr = null;
    $('#apply_filter').on('click', function() {
        var element_view_assign = document.getElementById("view_assigned");
        var element_view_source = document.getElementById("view_source");
        var element_view_status = document.getElementById("view_status");
        var up_from_date = document.getElementById("up_from_date").value;
        var up_to_date = document.getElementById("up_to_date").value;
        var lead_type = $("#lead_type").val();
        var view_assigned_options = "";
        var view_source_options = "";
        var view_status_options = "";
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
        if (typeof(element_view_assign) != 'undefined' && element_view_assign != null) {
            view_assigned_options = document.getElementById('view_assigned').selectedOptions;
            view_assigned_options = Array.from(view_assigned_options).map(({
                value
            }) => value);
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

        if (xhr != null) {
            xhr.abort();
        }
        $("#generate_pdf").hide();
        $(".hide-btn-response").hide();

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
                lead_type: lead_type
                // followup_from_date: followup_from_date,
                // followup_to_date: followup_to_date,
                // assign_from_date: assign_from_date,
                // assign_to_date: assign_to_date,

            },
            dataType: "JSON",
            cache: false,
            success: function(data) {
                //alert(data);  //as a debugging message.
                $(".leadSum").html('');
                $(".leadSum").html(data.status);
                $(".leadSum").innerHTML = data.status;
                // $("#updationCounter").html(data.update_count);

                if (data.status != "") {
                    $("#generate_pdf").show();
                    $(".hide-btn-response").show();
                }
                if (data.excel_data != undefined) {
                    excel_data_array = data.excel_data;
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
</script>