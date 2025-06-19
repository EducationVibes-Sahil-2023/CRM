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
                                        <label for="spreadsheetId">Spreadsheet ID</label>
                                        <input type="text" class="form-control" value="<?= $excelInfo->spreadsheetId ?>" required name="spreadsheetId" id="spreadsheetId" placeholder="Enter Spreadsheet ID">
                                    </div>

                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="fromDate">From Date</label>
                                        <input type="text" class="form-control" value="<?= $excelInfo->fromDate != "0000-00-00" ? $excelInfo->fromDate : '' ?>" name="fromDate" id="fromDate" placeholder="YYYY-MM-DD">
                                    </div>

                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="toDate">To Date</label>
                                        <input type="text" class="form-control" value="<?= $excelInfo->toDate != "0000-00-00" ? $excelInfo->toDate : '' ?>" name="toDate" id="toDate" placeholder="YYYY-MM-DD">
                                    </div>

                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="acadmic_year">Academic Year</label>
                                        <select class="form-control" name="acadmic_year" id="acadmic_year" required>
                                            <option value="">-- Select Academic Year --</option>
                                            <?php foreach ($academicYears as $year): ?>
                                                <option value="<?= $year ?>" <?= trim($excelInfo->acadmic_year) == $year ? 'selected' : '' ?>>
                                                    <?= $year ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="sheet_name">Sheet Name</label>
                                        <input type="text" class="form-control" required name="sheet_name" value="<?= $excelInfo->sheet_name ?>" id="sheet_name" placeholder="Enter Sheet Name">
                                    </div>

                                    <div class="col-md-3 mb-3 form-group">
                                        <label for="sql_condition">Select Type</label>
                                        <select name="sql_condition" required id="sql_condition" class="form-control">
                                            <option value=" AND l.type = 2 " data-id="1" <?= $excelInfo->type == "1" ? 'selected' : '' ?>>EV</option>
                                            <option value=" AND c.client_type = 2 " data-id="2" <?= $excelInfo->type == "2" ? 'selected' : '' ?>>EVP</option>
                                        </select>

                                    </div>
                                    <div class="col-md-3 mb-3 form-group">
                                        <label for=""> </label><br>
                                        <button type="submit" class="btn btn-primary margin-top" onclick="createSheet()">Create</button>
                                    </div>
                                </div>


                                <input type="hidden" name="sheetid" value="<?= !empty($excelInfo->id) ? $excelInfo->id : '' ?>">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                                <div id="orignal_documents" class="table-responsive col-12">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col">S.No</th>
                                                <th scope="col">Column Name</th>
                                                <th scope="col">
                                                    <div class="checkbox">

                                                        <input type="checkbox" onclick="select_all_checkbox(this, 'show_column')"> <label>Show Column</label>

                                                    </div>
                                                </th>
                                                <th scope="col">Sequence</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($tbl_excel_columns)) : ?>
                                                <?php
                                                $index = 1;
                                                foreach ($tbl_excel_columns as $key => $col) :
                                                ?>
                                                    <tr>
                                                        <td><?= $index ?></td>
                                                        <td><?= $col["name"] ?></td>
                                                        <td>
                                                            <div class="checkbox">
                                                                <input type="checkbox" name="column_ids[<?= $col["id"] ?>]" id="show_column_<?= $col["id"] ?>" class="show_column column_ids" <?= !empty($selected_column[$col["id"]]) ? 'checked' : '' ?> value="<?= $col["id"] ?>"><label></label>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <input class="form-control" id="sequence_column_<?= $col["id"] ?>" value="<?= !empty($selected_sequence[$col["id"]]) ? $selected_sequence[$col["id"]] : '' ?>" type="number" name="sequence[<?= $col["id"] ?>]">
                                                        </td>
                                                    </tr>
                                                <?php $index++;
                                                endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">
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
        // Create FormData from the form
        const form = document.getElementById("table-view-form");
        const formData = new FormData(form);
        const select = document.getElementById('sql_condition');
        const selectedOption = select.options[select.selectedIndex];
        const dataId = selectedOption.getAttribute('data-id');
        formData.append('type', dataId);

        // Check if at least one column_ids checkbox is selected
        const selectedColumns = document.querySelectorAll('input.column_ids:checked');

        if (selectedColumns.length === 0) {
            alert("Please select at least one column.");
            return false;
        }


        // You can append the checked values if needed
        // selectedColumns.forEach(input => {
        //     // formData.append("column_ids[]", input.value);
        // });

        // Continue with form submission or AJAX logic here
        // Example: send the formData via fetch or AJAX

        // AJAX request
        $.ajax({
            url: "<?= base_url() ?>admin/excel/create", // 🔁 Replace with actual URL
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                response = JSON.parse(response);
                if (response.status === "RCS") {
                    alert_float('success', response.message);

                    // Redirect after short delay (e.g., 2 seconds)
                    setTimeout(function() {
                        window.location.href = "<?= base_url('/admin/excel/') ?>";
                    }, 500);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                // ❌ Handle error
                console.error("Error:", error);
                alert("Something went wrong. Please try again.");
            }
        });
    }

    function select_all_checkbox(obj, className) {
        if ($(obj).prop("checked")) {
            $("." + className).prop("checked", true);
        } else {
            $("." + className).prop("checked", false);
        }
    }
</script>
</body>

</html>