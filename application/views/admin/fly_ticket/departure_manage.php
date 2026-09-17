<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$table_data = array(
    _l('Location Name'),
    _l('Create By'),
    _l('Create Date'),
    "Action",
);
?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?= $title ?></h4>
                    </div>
                    <div class="panel-body _buttons">
                        <?php if (has_permission('fly_batch', '', 'departure_create')) { ?>


                            <button type="button" data-toggle="modal" data-target="#fly_location" class="btn btn-primary">
                                <i class="fa fa-plus"></i> <?php echo _l('create'); ?>
                            </button>

                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <?php
                            render_datatable($table_data, 'fly-departure');
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fly_location" tabindex="-1" role="dialog" aria-labelledby="fly_locationLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content fullscreen">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="fly_locationLabel">Departure Location</h4>
            </div>

            <?= form_open('', ['id' => 'departure_form']); ?>

            <div class="modal-body">
                <input type="hidden" name="departure_id" value="">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Departure Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>

            <?= form_close(); ?>

        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var exam_data_table = "";
    $(function() {



        $(function() {
            exam_data_table = initDataTable('.table-fly-departure', window.location.href);
        });


    });
    $('#fly_location').on('show.bs.modal', function(event) {
        // This runs every time before the modal opens
        $('#departure_form')[0].reset(); // reset form
        $('input[name=departure_id]').val(''); // clear hidden ID
    });

    function edit(id, data) {
        var decodedData = JSON.parse(atob(data));



        $('#fly_location').modal('show');
        $('input[name=departure_id]').val(id);
        $('input[name=name]').val(decodedData.name);
    }

    function deleteItem(id) {
        if (confirm("Are you sure you want to delete this departure location?")) {
            $.ajax({
                url: "<?= base_url() ?> ('Fly_batch/delete_departure/'); ?>" + id,
                type: "POST",
                dataType: "json",
                success: function(res) {
                    if (res.resp_code === "RCS") {
                        alert_float("success", res.resp_desc);
                        // Refresh DataTable
                        $('.table-fly-departure').DataTable().ajax.reload();
                    } else {
                        alert_float("danger", res.resp_desc);
                    }
                },
                error: function() {
                    alert_float("danger", "Server error. Please try again.");
                }
            });
        }
    }

    $(document).ready(function() {

        // Submit Form (Insert + Update)
        $('#departure_form').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "<?= admin_url('Fly_batch/save_departure'); ?>",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",

                success: function(res) {
                    if (res.resp_code === "RCS") {
                        alert_float("success", res.resp_desc);

                        // Close modal
                        $('#fly_location').modal('hide');

                        // Reset form
                        $('#departure_form')[0].reset();
                        $('input[name=departure_id]').val('');
                        exam_data_table.ajax.reload(null, false);

                    } else {
                        alert_float("danger", res.resp_desc);
                    }
                },

                error: function() {
                    alert_float("danger", "Server error. Please try again.");
                }
            });
        });

    });
</script>
</body>

</html>