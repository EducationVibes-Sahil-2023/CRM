<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="customer_group_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('customer_group_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('customer_group_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/clients/group', array('id' => 'customer-group-modal')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'customer_group_name'); ?>
                        <?php echo form_hidden('id'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button group="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('load', function() {
        appValidateForm($('#customer-group-modal'), {
            name: 'required'
        }, manage_customer_groups);

        $('#customer_group_modal').on('show.bs.modal', function(e) {
            var invoker = $(e.relatedTarget);
            var group_id = $(invoker).data('id');
            $('#customer_group_modal .add-title').removeClass('hide');
            $('#customer_group_modal .edit-title').addClass('hide');
            $('#customer_group_modal input[name="id"]').val('');
            $('#customer_group_modal input[name="name"]').val('');
            // is from the edit button
            if (typeof(group_id) !== 'undefined') {
                $('#customer_group_modal input[name="id"]').val(group_id);
                $('#customer_group_modal .add-title').addClass('hide');
                $('#customer_group_modal .edit-title').removeClass('hide');
                $('#customer_group_modal input[name="name"]').val($(invoker).parents('tr').find('td').eq(0).text());
            }

        });

        prog();
        setTimeout(() => {
            course();

        }, 200);
        setTimeout(() => {
            // entrance_exam_given();
            $('#entrance_exam_details').change();
        }, 400);
        // study_country();
        // entrance_exam_given();
        $("#program").on('change', function() {
            prog();
        });
        $("#course").on('change', function() {
            course();
        });


    });

    function manage_customer_groups(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                if ($.fn.DataTable.isDataTable('.table-customer-groups')) {
                    $('.table-customer-groups').DataTable().ajax.reload();
                }
                if ($('body').hasClass('dynamic-create-groups') && typeof(response.id) != 'undefined') {
                    var groups = $('select[name="groups_in[]"]');
                    groups.prepend('<option value="' + response.id + '">' + response.name + '</option>');
                    groups.selectpicker('refresh');
                }
                alert_float('success', response.message);
            }
            $('#customer_group_modal').modal('hide');
        });
        return false;
    }


    var getProgram = <?= !empty($program_data) ? (json_encode($program_data, true)) : "[]"; ?>;
    var getCourse = <?= !empty($course_data) ? (json_encode($course_data, true)) : "[]"; ?>;
    var getEntrance = <?= !empty($entrance_data) ? (json_encode($entrance_data, true)) : "[]"; ?>;

    function get_course(program_id) {
        return new Promise((resolve, reject) => {
            let course_array = [];
            course_array.push({
                id: '',
                name: 'Select Course'
            })
            for (let j = 0; j < getCourse.length; j++) {
                if (program_id === getCourse[j].program_id) {
                    course_array.push({
                        id: getCourse[j].id,
                        name: getCourse[j].name,
                        spe_status: getCourse[j].spe_status
                    });
                }
            }

            resolve(course_array);
        });
    }

    function get_entrance() {
        return new Promise((resolve, reject) => {
            let entrance_array = [];
            entrance_array.push({
                id: '',
                name: 'Select Entrance'
            })
            let lead_type = $("#lead_type").val();
            for (let j = 0; j < getEntrance.length; j++) {
                if (lead_type === getEntrance[j].segment_id) {
                    entrance_array.push({
                        id: getEntrance[j].id,
                        name: getEntrance[j].name
                    });
                }
            }

            resolve(entrance_array);
        });
    }


    async function prog() {
        // console.log("start");
        var program = $("#program").val();
        var getProgram_array = [];
        // console.log(getProgram);
        for (let i = 0; i < getProgram.length; i++) {
            getProgram_array[getProgram[i].id] = await get_course(getProgram[i].id);
        }

        var $select = $('#course');
        $select.val('').selectpicker("refresh")
        var selectedCourse = "<?php echo $admissionpreferences->course ?>";
        // console.log(selectedCourse);
        $select.find('option').remove();
        if (getProgram_array[program] != undefined) {
            console.log(getProgram_array[program]);
            $.each(getProgram_array[program], function(key, value) {
                var sel = "";

                if (selectedCourse != '') {
                    sel = (value.id == selectedCourse) ? 'selected' : '';
                }
                $select.append('<option data-spe="' + value.spe_status + '" value="' + value.id + '"' + sel + ' >' + value.name + '</option>');
            });
        }
        $select.selectpicker("refresh")
        $("#course_name_field input").val('');
        $(".course_name_field").hide();
    }



    async function course() {

        var course = $("#course").val();
        var selectedCourseText = $("#course option:selected").text();
        var selectedSpec = $("#course option:selected").attr("data-spe");
        $("#course_name").removeAttr('requried');
        $("#course_name").removeAttr('requried-check');
        $(".course_name_field").hide();
        $("#course_name_field input").val('');
        if (course != "") {
            if (selectedSpec == 1) {
                $(".course_name_field").show();
                $("#course_name").attr('requried', true);
                $("#course_name").attr('requried-check', true);

            }

        } else {}



        var getEntrance_array = [];
        for (let i = 0; i < getCourse.length; i++) {
            getEntrance_array = await get_entrance();
        }
        var selectedExam = "<?php echo $admissionpreferences->entrance_exam_details ?>";
        selectedExam_array = selectedExam.split(",");
        var $select = $("#entrance_exam_details");
        $select.find('option').remove();


        $.each(getEntrance_array, function(key, value) {
            var sel = '';
            if (selectedExam_array.length > 0) {
                if (selectedExam_array.includes(value.id.toString()) && parseInt(value.id) > 0) {
                    sel = "selected";
                }
            }
            $select.append('<option value="' + value.id + '" ' + sel + '>' + value.name + '</option>');
        });

        $select.selectpicker("refresh");

        let value = $("#entrance_exam_details").val();
        if (value != undefined) {
            value = value.filter(function(element) {
                return element !== "" && element !== " " && element !== null && element !== undefined;
            });
            if (value.length >= 2) {
                $(`#entrance_exam_details option`).prop('disabled', true);
                for (let k = 0; k < value.length; k++) {
                    var v = value[k];
                    $(`#entrance_exam_details option[value="${v}"]`).prop('disabled', false);
                }
            } else {
                $(this).find('option').prop('disabled', false);
            }
            $(this).selectpicker("refresh")
        }

    }
</script>