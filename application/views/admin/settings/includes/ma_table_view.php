<?php
$tbllead_performance_column = $this->leads_model->tblma_applicant_tracker();
$table_view = array_column(get_view_columns(), null, "id");
echo form_hidden('settings[ma_table_view]', 'true');

?>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <h4 class="fs-title">Table View</h4>
            <hr>

            <form method="post" id="table-view-form" onsubmit="return false;">
                <div class="row">
                    <div class="col-md-3 margin-top leads-filter-column filter_reset">
                        <?php echo render_select('table_view', $table_view, array('id', 'name'), '', [], array('data-width' => '100%', 'data-none-selected-text' => 'Table View'), array(), 'no-mbot', '', false, 'table_view'); ?>
                    </div>
                </div>

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
                                <th scope="col">
                                    <div class="checkbox">

                                        <input type="checkbox" onclick="select_all_checkbox(this, 'selected_column')"> <label>Selected Column</label>

                                    </div>
                                </th>
                                <th scope="col">Sequence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tbllead_performance_column)) : ?>
                                <?php
                                $index = 1;
                                foreach ($tbllead_performance_column as $key => $col) :
                                ?>
                                    <tr>
                                        <td><?= $index ?></td>
                                        <td><?= $col["label_name"] ?></td>
                                        <td>
                                            <div class="checkbox">
                                                <input type="checkbox" name="column_ids[<?= $col["id"] ?>]" id="show_column_<?= $col["id"] ?>" class="show_column" value="<?= $col["id"] ?>"><label></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="checkbox">

                                                <input type="checkbox" name="selected_ids[<?= $col["id"] ?>]" id="selected_column_<?= $col["id"] ?>" id="" class="selected_column" value="<?= $col["id"] ?>"><label></label>

                                            </div>
                                        </td>
                                        <td>
                                            <input class="form-control" id="sequence_column_<?= $col["id"] ?>" type="number" name="sequence[<?= $col["id"] ?>]">
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
<?php init_tail(); ?>
<script>
    var tbllead_performance_column_array = <?= !empty($table_view) ? json_encode($table_view, JSON_UNESCAPED_UNICODE) : '[]' ?>;
    var show_column_array = [];
    var selected_column_array = [];
    var sequence = [];
    var selected_view = $("#table_view option:selected").val() || 0;
    $(document).ready(function() {
        
        if (tbllead_performance_column_array[selected_view]) {
            show_column_array = (tbllead_performance_column_array[selected_view].column_ids || "").split(",");
            selected_column_array = (tbllead_performance_column_array[selected_view].selected_ids || "").split(",");
            sequence = (tbllead_performance_column_array[selected_view].sequence || "").split(",");
            set_checkbox();
        }
    })


    $("#table_view").change(function() {
        let select_view = $("#table_view option:selected").val() || 0;
        $("input[type='checkbox']").prop("checked", false);
        $("input[type='number']").val("");

        if (tbllead_performance_column_array[select_view]) {
            show_column_array = (tbllead_performance_column_array[select_view].column_ids || "").split(",");
            selected_column_array = (tbllead_performance_column_array[select_view].selected_ids || "").split(",");
            sequence = (tbllead_performance_column_array[select_view].sequence || "").split(",");
            set_checkbox();
        }
    });

    function set_checkbox() {
        // Ensure arrays exist
        if (typeof show_column_array !== "undefined") {
            show_column_array.forEach(function(show_c) {
                console.log(show_c);
                $("#show_column_" + show_c).prop("checked", true);
            });
        }

        if (typeof selected_column_array !== "undefined") {
            selected_column_array.forEach(function(value, key) {
                $("#selected_column_" + value).prop("checked", true);
                $("#sequence_column_" + value).val(sequence[key]);
            });

        }
    }


    function select_all_checkbox(obj, className) {
        if ($(obj).prop("checked")) {
            $("." + className).prop("checked", true);
        } else {
            $("." + className).prop("checked", false);
        }
    }
</script>