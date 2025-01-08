<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('before_leads_settings'); ?>
<?php echo form_hidden('settings[_leads_performance_settings]', 'true'); ?>
<h3>Performance Leads</h3>
<hr />
<?php
// Assuming $tbl_columns is an array of table column data
$tbl_columns = tbl_columns_leads_performance(); // Fetch the table columns data


// Initialize an empty array to group columns
$grouped_columns = [];

// Loop through the $tbl_columns array
foreach ($tbl_columns as $key => $row) {
    // Group columns by their "name" key
    if (isset($row["tbl"])) {
        $grouped_columns[$row["tbl"]][] = $row;
    }
}


// Result: $grouped_columns is now organized by table names
// echo "<pre>";
// print_r($grouped_columns);
// die;
?>
<form method="post">
    <?php
    foreach ($grouped_columns as $table => $column_name) {
    ?>
        <h4><?= ucfirst(str_replace("_", " ", str_replace(db_prefix(), "", $table))) ?></h4>

        <table class="<?= $table ?> table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Column Name</th>
                    <th>Label Name</th>
                    <th>Sequence</th>
                    <th>SQL Condition</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($column_name as $name): ?>
                    <?php
                    $id = $name["id"];
                    $name_tb = $name["tbl"] . "_" . $name["column"];
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox"
                                <?= !empty($name["show_column"]) && $name["show_column"] == 1 ? 'checked' : '' ?>
                                id="<?= $id ?>_checkbox"
                                onchange="set_update_column('<?= $id ?>')" name="show_column[<?= $id ?>]" value="1">
                            <input id="<?= $id ?>_id" name="id[<?= $id ?>]" type="hidden" value="<?= $id ?>">
                        </td>
                        <td><?= ucfirst(str_replace("_", " ", $name["column"])) ?></td>
                        <td>
                            <input id="<?= $id ?>_label"
                                name="label[<?= $id ?>]"
                                type="text"
                                class="form-control"
                                value="<?= !empty($name["label_name"]) ? htmlspecialchars($name["label_name"]) : '' ?>"
                                placeholder="Column Name">
                        </td>
                        <td>
                            <input id="<?= $id ?>_sequence"
                                name="sequence[<?= $id ?>]"
                                type="text"
                                class="form-control"
                                value="<?= !empty($name["sequence"]) ? htmlspecialchars($name["sequence"]) : '' ?>"
                                placeholder="Sequence">
                        </td>
                        <td>
                            <input id="<?= $id ?>_sql"
                                name="sql_condition[<?= $id ?>]"
                                type="text"
                                class="form-control"
                                value="<?= !empty($name["sql_condition"]) ? htmlspecialchars($name["sql_condition"]) : '' ?>"
                                placeholder="SQL Condition">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</form>
<?php
    }
?>

<script>
    function set_update_column(id_) {
        // Retrieve the label value and trim it
        var label = $.trim($("#" + id_ + "_label").val());
        var status = $("#" + id_ + "_checkbox").prop('checked') ? 1 : 0;

        // Validation: If the status is checked (1), ensure the label is not empty
        if (status === 1 && label === "") {
            alert("Please enter a label name before enabling this column.");
            $("#" + id_ + "_label").focus(); // Focus on the label input field
            $("#" + id_ + "_checkbox").prop('checked', false); // Uncheck the checkbox
            return false; // Stop further processing
        }

        // Debugging log for validation passed
        console.log(`ID: ${id_}, Label: ${label}, Status: ${status}`);
        // Proceed with additional processing if needed
    }
</script>