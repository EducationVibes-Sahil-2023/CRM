<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_department(); return false;" class="btn btn-info pull-left display-block">
                                <?php echo "New Board"; ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            "Board Name",
                            _l('status'),
                            _l('action'),
                        ), 'board'); ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="school_board" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('school_board/board_name')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo "Edit Board"; ?></span>
                    <span class="add-title"><?php echo "Create Board"; ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>

                        <div class="form-group">
                            <input type="hidden" class="form-control" name="type" value="<?= $type ?>">
                            <input type="hidden" class="form-control" name="board_id" id="board_id" placeholder="Enter Board id">
                            <div class="form-group mb-3">
                                <label for="name">Board Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="board_name" placeholder="Enter board name" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">In-Active</option>
                                </select>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-board', window.location.href, [2], [2], undefined, [1, 'asc']);
        appValidateForm($('form'), {
            name: 'required',
            email: {
                email: true,
                remote: {
                    url: admin_url + "board/name_exists",
                    type: 'post',
                    data: {
                        email: function() {
                            return $('input[name="name"]').val();
                        }
                    }
                }
            }
        }, manage_board);
        $('#school_board').on('hidden.bs.modal', function(event) {
            $('#school_board input[name="name"]').val('');
            $('#school_board input[name="board_id"]').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });

        $('#school_board').on('show.bs.modal', function(event) {

            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#school_board input[name="text"]').val('');
            $('#school_board select[name="status"]').val(1);
            $('#currency_modal .add-title').removeClass('hide');
            $('#currency_modal .edit-title').addClass('hide');

            if (typeof(id) !== 'undefined') {
                $('input[name="board_id"]').val(id);
                var name = $(button).data("name")
                console.log(name);
                var status = $(button).data("status")
                $('input[name="name"]').val(name);
                $('select[name="status"]').val(status);
                $('#currency_modal .add-title').addClass('hide');
                $('#currency_modal .edit-title').removeClass('hide');
                $('#currency_modal input[name="name"]').val(name);
            }
        });
    });

    function manage_board(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            } else {
                alert_float('danger', response.message);
                return false;
            }

            $('.table-board').DataTable().ajax.reload();
            $('#school_board').modal('hide');
        }).fail(function(data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message);
        });
        return false;
    }

    function new_department() {
        $('#school_board').modal('show');
        $('.edit-title').addClass('hide');
    }
</script>
</body>

</html>