<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php

$required = "";
$batch_id = "";
$batch_name = "";
$batch_date = "";
$selected_university = '';
$selected_exam = '';
$selected_client_ids = [];
if (!empty($batch_data["id"])) {
    $batch_id = $batch_data["id"];
}
if (!empty($batch_data["name"])) {
    $batch_name = $batch_data["name"];
}
if (!empty($batch_data["exam_date"])) {
    $batch_date = $batch_data["exam_date"];
}
if (!empty($batch_data["university_name"])) {
    $selected_university = $batch_data["university_name"];
}
if (!empty($batch_data["exam_id"])) {
    $selected_exam = $batch_data["exam_id"];
}
if (!empty($batch_data["client_ids"])) {
    $selected_client_ids = explode(",", $batch_data["client_ids"]);
}
array_unshift($exams, array("id" => "", "name" => "Select Exam"))
?>
<style>
    #client_list {
        height: 300px;
        overflow-y: scroll;
        padding: 10px;
        box-shadow: 0px 0px 6px 2px lightgrey;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php if (isset($batch)) { ?>
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body no-padding-bottom">
                            <?php $this->load->view('admin/staff/stats'); ?>
                        </div>
                    </div>
                </div>
                <div class="batch">
                    <?php echo form_hidden('isedit'); ?>
                    <?php echo form_hidden('batchid', $batch->id); ?>
                </div>
            <?php } ?>

            <div class="panel_s">
                <div class="panel-body">
                    <h4 class="no-margin">Exam Batch</h4>
                    <hr>
                    <form method="post" id="exam_batch_form" action="<?= admin_url() ?>exam_batch/create" onsubmit="return false;">
                        <div class="form-group col-md-3">


                            <label>University Name </label>
                            <select name="university_name" required id="university_name" class="form-control selectpicker" data-actions-box="true" data-live-search="true" onchange="select_university_exam(this)">
                                <option value="">Select university</option>
                                <?php foreach ($university_list as $uni) {

                                ?>
                                    <option data-id="<?= $uni["university"] ?>" data-country="<?= $uni["country_name"] ?>" data-mandatory="<?= $uni["exam"] ?>" value="<?= $uni['university_name'] ?>"><?= $uni["university_name"] ?> - <?= $uni["country_name"] ?></option>
                                <?php
                                }
                                ?>

                            </select>
                        </div>

                        <div class="form-group col-md-3">

                            <?= render_select('exam_name', $exams, array('id', 'name'), 'Exam Name ', [], array('data-width' => '100%', 'data-none-selected-text' => 'Select Exam Name', 'data-actions-box' => true), array(), 'no-mbot', '', false, "exam_name"); ?>
                        </div>

                        <div class="form-group col-md-3">
                            <?php echo render_input('batch_name', 'Batch Name ', $batch_name, "text", array()); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <?php echo render_input('batch_date', 'Batch Date ', $batch_date, "date", array()); ?>
                        </div>

                        <div class="form-group col-md-12 ">
                            <label><small class='text-danger'>*</small> Applicants</label>
                            <div id="client_list">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="pull-end">
                                <button type="submit" onclick="create_batch()" class="btn btn-info">Create Batch</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
<script>
    var exam_array = <?= !empty($exams) ? json_encode($exams, true) : [] ?>;
    var selected_university = "<?= $selected_university ?>";
    var selected_exam = "<?= $selected_exam ?>";
    var selected_client_ids = <?= json_encode($selected_client_ids, true) ?>;
    if (selected_university !== "") {
        $("select[name='university_name']").val(selected_university).trigger("change");
    }
    setTimeout(() => {
        if (selected_exam !== "") {
            $("select[name='exam_name']").val(selected_exam).trigger("change");
        }
    }, 200);



    function validate_exam_batch_form() {
        // Predefined validation rules
        var e = {
            university_name: "required",
            exam_name: "required",
            batch_name: "required",
        }

        // Validate the form
        appValidateForm($("#exam_batch_form"), e);
    }

    function select_university_exam(obj) {

        // $("#exam_name").empty().selectpicker('refresh'); // Clear the select options properly
        // $("#exam_name").append(`<option value="">Select Exam Name</option>`);
        set_client_list([]);
        let selectedOption = $(obj).find(":selected");
        let selectedValue = selectedOption.val(); // Get selected value
        let countryName = selectedOption.data("country"); // Get selected option's data-country attribute
        let university_id = selectedOption.data("id"); // Get selected option's data-country attribute
        let examMandatory = selectedOption.data("mandatory"); // Get selected option's data-mandatory attribute
        if (selectedValue) {
            get_client_list(selectedValue);
        }

        //         if (examMandatory) {
        //     let mandatoryArray = Array.isArray(examMandatory) 
        //         ? examMandatory.map(item => String(item).trim())  // Ensure array elements are strings
        //         : String(examMandatory).split(',').map(item => item.trim()); // Convert string to array

        //     if (Array.isArray(exam_array)) { // Ensure `exam_array` is defined
        //         exam_array.forEach(function(exam) {
        //             if (mandatoryArray.includes(String(exam.id))) { // Compare as a string
        //                 $("#exam_name").append(`<option value="${exam.id}">${exam.name}</option>`); // Add options dynamically
        //             }
        //         });
        //     }
        // }


        //         $("#exam_name").selectpicker('refresh');
    }

    function set_client_list(client_list) {
        let html = "";

        if (client_list.length > 0) {
            html += "<ul class='list-unstyled'>"; // Ensure list is properly formatted

            client_list.forEach(client => {
                let checked = selected_client_ids.includes(client.userid) ? "checked" : "";

                html += `
        <li class='col-md-3'>
            <div class="form-check">
                <input type="checkbox" ${checked} name="selected_clients[]" value="${client.userid}" class="form-check-input selected_client">
                <label class="form-check-label">${client.full_name}</label>
            </div>
        </li>`;
            });


            html += "</ul>"; // Closing <ul> properly
        } else {
            html = "<p class='text-center text-muted'>No clients available.</p>";
        }

        $("#client_list").html(html);
    }



    function get_client_list(university_name) {
        if (!university_name || university_name.trim() === "") {
            alert_float("danger", "Please select a university name.");
            return;
        }

        let formData = new FormData();
        formData.append("csrf_token_name", csrfData.hash);
        formData.append("university_name", university_name);

        // AJAX request to fetch student list
        $.ajax({
            url: "<?php echo base_url('admin/exam_batch/client_list'); ?>",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from transforming FormData
            contentType: false, // Ensure correct Content-Type is set for FormData
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    // alert_float("success", res.resp_desc);
                    set_client_list(res.client_list);
                } else {
                    const message = res.resp_desc || "An unknown error occurred.";
                    alert_float("danger", message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }


    function create_batch() {
        let batch_id = <?= !empty($batch_id) ? $batch_id : 0 ?>;
        let university_name = $("#university_name").val();
        let exam_name = $("#exam_name").val();
        let batch_name = $("#batch_name").val();
        let batch_date = $("#batch_date").val();

        // Get all selected client IDs properly
        let client_selected_list = $("input[name='selected_clients[]']:checked")
            .map(function() {
                return $(this).val();
            })
            .get(); // Convert to array

        if (client_selected_list.length === 0) {
            alert_float("danger", "Please select at least one client.");
            return;
        }

        // Validate required fields


        let formData = new FormData();
        formData.append("csrf_token_name", csrfData.hash);
        formData.append("batch_id", batch_id);
        formData.append("university_name", university_name);
        formData.append("exam_name", exam_name);
        formData.append("batch_name", batch_name);
        formData.append("batch_date", batch_date);
        formData.append("client_list", JSON.stringify(client_selected_list)); // Send as JSON string

        // AJAX request to create batch
        $.ajax({
            url: "<?php echo base_url('admin/exam_batch/create_batch'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
                    window.location.href = "<?= base_url('admin/exam_batch') ?>";

                } else {
                    const message = res.resp_desc || "An unknown error occurred.";
                    alert_float("danger", message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }

    validate_exam_batch_form();
</script>
</body>

</html>