<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
function column_array($array)
{
    return array_combine($array, $array);
}

$selected_column = [];
$selected_sequence = [];

if (!empty($excelInfo->column_ids)) {
    $column_ids_array = explode(",", $excelInfo->column_ids);
    $selected_column = column_array($column_ids_array); // assuming column_array is a helper function
}

if (!empty($excelInfo->sequence)) {
    $sequence_data = json_decode($excelInfo->sequence, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($sequence_data)) {
        $selected_sequence = array_column($sequence_data, 'sequence', 'column_id');
    } else {
        // Optional: log or handle the JSON error
        $selected_sequence = [];
    }
}

$currentYear = date('Y');
$academicYears = [
    ($currentYear - 1) . ' - ' . $currentYear,
    $currentYear . ' - ' . ($currentYear + 1),
    ($currentYear + 1) . ' - ' . ($currentYear + 2)
];

?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body _buttons">

                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />


                        <div class="col-md-12">
                            <form method="post" id="table-view-form" onsubmit="return false;">
                                <div class="row">
                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="excel_type">Excel Type</label>
                                        <select name="excel_type" id="excel_type" class="form-control selectpicker" onchange="ChangeType(this.value)" required>
                                            <option value="1" <?php echo ($excelInfo->excel_type == "1") ? 'selected' : ''; ?>>MA Applicant</option>
                                            <option value="2" <?php echo ($excelInfo->excel_type == "2") ? 'selected' : ''; ?>>Leads</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="spreadsheetId">Spreadsheet ID</label>
                                        <input type="text" class="form-control" name="spreadsheetId" id="spreadsheetId" placeholder="Enter Spreadsheet ID"
                                            value="<?php echo htmlspecialchars($excelInfo->spreadsheetId); ?>" required>
                                    </div>

                                    <div class="col-md-2 mb-3 form-group">
                                        <label for="fromDate">From Date</label>
                                        <input type="text" class="form-control" name="fromDate" id="fromDate" placeholder="YYYY-MM-DD"
                                            value="<?php echo ($excelInfo->fromDate != "0000-00-00") ? $excelInfo->fromDate : ''; ?>">
                                    </div>

                                    <div class="col-md-2 mb-3 form-group">
                                        <label for="toDate">To Date</label>
                                        <input type="text" class="form-control" name="toDate" id="toDate" placeholder="YYYY-MM-DD"
                                            value="<?php echo ($excelInfo->toDate != "0000-00-00") ? $excelInfo->toDate : ''; ?>">
                                    </div>

                                    <div class="col-md-2 mb-3 form-group hide-options applicant-ma">
                                        <label for="acadmic_year">Academic Year</label>
                                        <select class="form-control" name="acadmic_year" id="acadmic_year" required>
                                            <option value="">-- Select Academic Year --</option>
                                            <?php foreach ($academicYears as $year): ?>
                                                <option value="<?php echo $year; ?>" <?php echo (trim($excelInfo->acadmic_year) == $year) ? 'selected' : ''; ?>>
                                                    <?php echo $year; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="sheet_name">Sheet Name</label>
                                        <input type="text" class="form-control" name="sheet_name" id="sheet_name" placeholder="Enter Sheet Name"
                                            value="<?php echo htmlspecialchars($excelInfo->sheet_name); ?>" required>
                                    </div>

                                    <div class="col-md-3 mb-3 form-group hide-options applicant-ma">
                                        <label for="sql_condition">Select Type</label>
                                        <select name="sql_condition" id="sql_condition" class="form-control" required>
                                            <option value=" AND l.type = 2 " data-id="1" <?php echo ($excelInfo->type == "1") ? 'selected' : ''; ?>>EV</option>
                                            <option value=" AND c.client_type = 2 " data-id="2" <?php echo ($excelInfo->type == "2") ? 'selected' : ''; ?>>EVP</option>
                                            <option value=" AND (l.type = 2 OR c.client_type = 2) " data-id="3" <?php echo ($excelInfo->type == "3") ? 'selected' : ''; ?>>Both</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3 form-group hide-options applicant-ma">
                                        <label>&nbsp;</label>
                                        <div class="checkbox">
                                            <input type="checkbox" name="orignal_documents_status" id="orignal_documents_status"
                                                <?php echo (!empty($excelInfo->orignal_documents_status) && $excelInfo->orignal_documents_status == 1) ? 'checked' : ''; ?>>
                                            <label for="orignal_documents_status">Original Document Status</label>
                                        </div>
                                    </div>

                                    <div class="col-md-2 mb-3 form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary margin-top" onclick="createSheet()">Create</button>
                                    </div>
                                </div>

                                <input type="hidden" name="sheetid" value="<?php echo !empty($excelInfo->id) ? $excelInfo->id : ''; ?>">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                    value="<?php echo $this->security->get_csrf_hash(); ?>">

                                <div id="orignal_documents" class="table-responsive col-12">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Column Name</th>
                                                <th>
                                                    <div class="checkbox">
                                                        <input type="checkbox" onclick="select_all_checkbox(this, 'show_column')">
                                                        <label>Show Column</label>
                                                    </div>
                                                </th>
                                                <th>Sequence</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($tbl_excel_columns)): ?>
                                                <?php foreach ($tbl_excel_columns as $index => $col): ?>
                                                    <tr class='excel-type-tr excel-type-<?= $col['excel_type'] ?>'>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><?php echo htmlspecialchars($col["name"]); ?></td>
                                                        <td>
                                                            <div class="checkbox">
                                                                <input type="checkbox"
                                                                    name="column_ids[<?php echo $col['id']; ?>]"
                                                                    id="show_column_<?php echo $col['id']; ?>"
                                                                    class="show_column column_ids"
                                                                    value="<?php echo $col['id']; ?>"
                                                                    <?php echo !empty($selected_column[$col['id']]) ? 'checked' : ''; ?>>
                                                                <label></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="any" class="form-control"
                                                                id="sequence_column_<?php echo $col['id']; ?>"
                                                                name="sequence[<?php echo $col['id']; ?>]"
                                                                value="<?php echo !empty($selected_sequence[$col['id']]) ? $selected_sequence[$col['id']] : ''; ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">
                                                        <h5>No Columns Available</h5>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    function ChangeType(type) {
        // Clear all inputs and selects inside hide-options
        $("div.hide-options input").val('');
        $("div.hide-options select").val('').selectpicker('refresh');

        // Hide and reset Excel type rows
        $(".excel-type-tr").hide();
        $(".excel-type-tr").find("input,input[type='checkbox']").val('').prop("checked", false);

        // Hide all hide-options sections
        $("div.hide-options").hide();

        // Show based on type
        if (type === "1" || type === 1) {
            $(".applicant-ma").show();
            $(".excel-type-1").show();
        } else if (type === "2" || type === 2) {
            $(".excel-type-2").show();
        }
    }



    function validation_set(form_id) {
        return new Promise((resolve, reject) => {
            let form_status = true;
            let additional_fields = {};

            $("#" + form_id + " input:visible, #" + form_id + " select:visible, #" + form_id + " textarea:visible").each(function() {
                const value = $(this).val()?.trim(); // Get trimmed value
                const name = $(this).attr("name"); // Get name attribute
                const isRequired = $(this).prop("required"); // Corrected typo: "requried" -> "required"

                if (isRequired && name) {
                    additional_fields[name] = "required";
                    if (!value) {
                        form_status = false;
                    }
                }
            });

            if (!form_status) {
                console.log(additional_fields);
                appValidateForm($("#" + form_id), additional_fields);
                reject("Form validation failed."); // Reject the promise if validation fails
            } else {
                resolve("Form validation passed."); // Resolve if all fields are valid
            }
        });
    }


    validation_set("table-view-form");

    function createSheet() {
        // Get form safely
        const form = document.getElementById("table-view-form");
        if (!form) {
            console.warn("Form element #table-view-form not found. Aborting createSheet.");
            return false;
        }

        const formData = new FormData(form);

        // Get select safely
        const select = document.getElementById('sql_condition');
        if (select && select.selectedIndex >= 0) {
            const selectedOption = select.options[select.selectedIndex];
            const dataId = selectedOption ? selectedOption.getAttribute('data-id') : '';
            if (dataId) {
                formData.append('type', dataId);
            }
        } else {
            console.warn("'#sql_condition' not found or no option selected.");
        }

        // Check if at least one column_ids checkbox is selected
        const selectedColumns = document.querySelectorAll('input.column_ids:checked');
        if (!selectedColumns || selectedColumns.length === 0) {
            alert("Please select at least one column.");
            return false;
        }

        // Send AJAX request
        $.ajax({
            url: "<?= base_url() ?>admin/excel/create",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error("Invalid JSON response", e);
                    alert("Invalid server response.");
                    return;
                }

                if (response.status === "RCS") {
                    alert_float('success', response.message);
                    setTimeout(function() {
                        window.location.href = "<?= base_url('/admin/excel/') ?>";
                    }, 500);
                } else {
                    alert_float('danger', response.message || "Unknown error.");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                alert("Something went wrong. Please try again.");
            }
        });
    }


    function select_all_checkbox(obj, className) {
   if ($(obj).prop("checked")) {
    $("." + className + ":visible").prop("checked", true);
} else {
    $("." + className + ":visible").prop("checked", false);
}

    }

    $(document).ready(function() {
        let excel_type = $("#excel_type").val();
        let excel_type_tr = $(".excel-type-tr").hide();

        if (excel_type !== '') {
            $(".excel-type-" + excel_type).show(); // use .show() instead of .hide()
        }
        $("div.hide-options").hide();
        if (excel_type === "1" || excel_type === 1) {
            $(".applicant-ma").show();
            $(".excel-type-1").show();
        } else if (excel_type === "2" || excel_type === 2) {
            $(".excel-type-2").show();
        }
    });
</script>
</body>

</html>