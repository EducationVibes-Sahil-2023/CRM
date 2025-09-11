<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php

$required = "";
$batch_id = "";
$batch_name = "";
$selected_country = "";
$selected_university = [];
$selected_vendor = '';
$ticket_cost = '';
$payment_date = '';
$selected_payment_mode = '';
$fly_date = '';
$departure = '';
$selected_vendor = '';
$selected_client_ids = [];
if (!empty($batch_data["id"])) {
    $batch_id = $batch_data["id"];
}
if (!empty($batch_data["name"])) {
    $batch_name = $batch_data["name"];
}
if (!empty($batch_data["university_ids"])) {
    $selected_university = explode(",", $batch_data["university_ids"]);
}
if (!empty($batch_data["country_ids"])) {
    $selected_country = $batch_data["country_ids"];
}
if (!empty($batch_data["vendor_id"])) {
    $selected_vendor = $batch_data["vendor_id"];
}
if (!empty($batch_data["ticket_cost"])) {
    $ticket_cost = $batch_data["ticket_cost"];
}
if (!empty($batch_data["payment_date"])) {
    $payment_date = $batch_data["payment_date"];
}
if (!empty($batch_data["payment_mode"])) {
    $selected_payment_mode = $batch_data["payment_mode"];
}
if (!empty($batch_data["fly_date"])) {
    $fly_date = $batch_data["fly_date"];
}
if (!empty($batch_data["departure_location"])) {
    $departure = $batch_data["departure_location"];
}
if (!empty($batch_data["client_ids"])) {
    $selected_client_ids = explode(",", $batch_data["client_ids"]);
}


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
            <?php if (isset($batch)) {  ?>
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
                    <h4 class="no-margin">Fly Ticket Batch</h4>
                    <hr>
                    <form method="post" id="fly_batch_form" action="<?= admin_url() ?>exam_batch/create" onsubmit="return false;">

                        <div class="form-group col-md-3">


                            <label><small class="text-danger">*</small> Country Name </label>
                            <select name="country_name" required id="country_name" class="form-control selectpicker" data-actions-box="true" required data-live-search="true" onchange="select_country(this.value)">
                                <option value="">Select country</option>
                                <?php foreach ($country_list as $con) {
                                ?>
                                    <option data-country="<?= $con["country_name"] ?>" <?= $selected_country == $con['id'] ? 'selected' : '' ?> value="<?= $con['id'] ?>"><?= $con["country_name"] ?></option>
                                <?php
                                }
                                ?>

                            </select>
                        </div>
                        <div class="form-group col-md-3">


                            <label><small class="text-danger">*</small> University Name </label>
                            <select
                                name="university_name[]"
                                required
                                id="university_name"
                                class="form-control selectpicker"
                                data-actions-box="true"
                                data-live-search="true"
                                data-none-selected-text="Select University"
                                multiple
                                onchange="select_university(this)">
                            </select>

                        </div>

                        <div class="form-group col-md-3">
                            <?php 
                            $batch_names = [];
for ($i = 1; $i <= 10; $i++) {
    $batch_names[] = [
        'id'   => 'Batch ' . $i,
        'name' => 'Batch ' . $i
    ];
}
                            ?>
                            <?php echo render_select('batch_name', $batch_names, array('id', 'name'), "Batch Name", [$batch_name]); ?>
                        </div>


                        <div class="form-group col-md-12 ">
                            <label><small class='text-danger'>*</small> Applicants</label>
                            <div id="client_list">
                            </div>
                        </div>

                        <div class="form-group col-md-3">
                            <?php echo render_select('vendor_name', $vendor_list, array('id', 'name'), "Vendor Name", [$selected_vendor]); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <?php echo render_input('ticket_cost', 'Ticket Cost ', $ticket_cost, "number", array()); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <?php echo render_input('payment_date', 'Payment Date ', $payment_date, "date", array()); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <?php echo render_select('payment_mode', $payment_mode, array('id', 'name'), "Payment Mode", [$selected_payment_mode]); ?>
                        </div>


                        <div class="form-group col-md-3">
                            <?php echo render_input('fly_date', 'Fly Date ', $fly_date, "datetime-local", array()); ?>
                        </div>

                        <div class="form-group col-md-3">
                            <?php echo render_select('departure_location', $departure_location, array("id", "name"), "Departure Location", [$departure]); ?>
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
    var country_list = <?= json_encode($country_list, true) ?>;
    var university_list = <?= json_encode($university_list, true) ?>;
    const groupedByCountry = {};

    university_list.forEach(entry => {
        const country = entry.country_id;

        // Remove country_name from the entry (optional)
        const {
            country_name,
            ...universityData
        } = entry;

        if (!groupedByCountry[country]) {
            groupedByCountry[country] = [];
        }

        groupedByCountry[country].push(universityData);
    });

    var selected_university = <?= json_encode($selected_university, true) ?>;

    var selected_country = "<?= $selected_country ?>";
    var selected_exam = "<?= $selected_exam ?>";
    var selected_client_ids = <?= json_encode($selected_client_ids, true) ?>;

    if (selected_country !== "") {
        $("select[name='country_name']").trigger("change")
            .selectpicker("refresh"); // If using Bootstrap Select
    }


    setTimeout(() => {
        if (selected_exam !== "") {
            $("select[name='exam_name']").val(selected_exam).trigger("change");
        }
    }, 200);



    function validate_fly_batch_form() {
        // Predefined validation rules
        var e = {
            university_name: "required",
            exam_name: "required",
            batch_name: "required",
        }

        // Validate the form
        appValidateForm($("#fly_batch_form"), e);
    }

    function select_country(country_id) {
        const universities = groupedByCountry[country_id] || [];

        const $universitySelect = $('#university_name');
        $universitySelect.empty(); // Clear previous options



        // Add university options with data-university attribute
        universities.forEach(university => {
            $universitySelect.append(
                $('<option>', {
                    value: university.university_id,
                    text: university.university_name,
                    'data-university': university.university_name,
                    'data-country': university.country_name
                    // Add more attributes here if needed
                })
            );
        });

        // Refresh select picker
        $universitySelect.selectpicker('refresh');

        if (selected_university.length > 0) {
            $("#university_name")
                .val(selected_university) // Pass array directly
                .trigger("change")
                .selectpicker("refresh"); // If using Bootstrap Select
        }
    }



    function select_university(obj) {
        set_client_list([]); // Reset client list

        let selectedOptions = $(obj).find(":selected");
        let selectedValues = [];
        let selectedUniversities = [];

        selectedOptions.each(function() {
            const value = $(this).data('university');
            const country = $(this).data("country");
            const id = $(this).val();

            if (value) {
                selectedValues.push(value);
                selectedUniversities.push({
                    value,
                    country,
                    university_id: id
                });
            }
        });

        // Example: Pass array of selected university IDs to another function
        if (selectedValues.length > 0) {
            get_client_list(selectedValues); // Or handle each one as needed
        }

        // Optional: log the full info for each selected university
        console.log("Selected Universities:", selectedUniversities);
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



    function get_client_list(university_name = []) {
        if (
            !university_name ||
            (Array.isArray(university_name) && university_name.length === 0) ||
            (typeof university_name === 'string' && university_name.trim() === "")
        ) {
            alert_float("danger", "Please select a university name.");
            return;
        }

        let formData = new FormData();
        formData.append("csrf_token_name", csrfData.hash);

        // Append multiple universities (if array)
        if (Array.isArray(university_name)) {
            university_name.forEach(name => {
                formData.append("university_name[]", name); // Use [] for array values
            });
        } else {
            formData.append("university_name", university_name);
        }
       let batch_id = "<?= !empty($batch_id) ? $batch_id :'' ?>";
 formData.append("batch_id", batch_id);
        $.ajax({
            url: "<?php echo base_url('admin/fly_batch/client_list'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    set_client_list(res.client_list);
                } else {
                    const message = res.resp_desc || "An unknown error occurred.";
                    alert_float("danger", message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
                alert_float("danger", "An error occurred while processing the request.");
            }
        });
    }



    function create_batch() {
        let batch_id = <?= !empty($batch_id) ? $batch_id : 0 ?>;

        // Get field values
        let batch_name = $("#batch_name").val();
        let country_ids = $("#country_name").val();
        let country_name = $("#country_name option:selected").text();
        let ticket_cost = $("#ticket_cost").val();
        let payment_date = $("#payment_date").val();
        let payment_mode = $("#payment_mode").val();
        let fly_date = $("#fly_date").val();
        let vendor_name = $("#vendor_name").val();
        let departure_location = $("#departure_location").val();
        let university_ids = $("#university_name").val(); // multi-select
        let university_name = $("#university_name option:selected").map(function() {
            return $(this).text();
        }).get().join(",");

        let exam_name = $("#exam_name").val();
        let batch_date = $("#batch_date").val();

        // Get selected client IDs
        let client_selected_list = $("input[name='selected_clients[]']:checked")
            .map(function() {
                return $(this).val();
            })
            .get();

        // Basic validations
        if (!country_name || !batch_name || !university_name || !client_selected_list) {
            alert_float("danger", "Please fill all required fields.");
            return;
        }

        if (client_selected_list.length === 0) {
            alert_float("danger", "Please select at least one client.");
            return;
        }

        // Prepare form data
        let formData = new FormData();
        formData.append("csrf_token_name", csrfData.hash);
        formData.append("batch_id", batch_id);
        formData.append("batch_name", batch_name);
        formData.append("ticket_cost", ticket_cost);
        formData.append("payment_date", payment_date);
        formData.append("payment_mode", payment_mode);
        formData.append("fly_date", fly_date);
        formData.append("vendor_name", vendor_name);
        formData.append("departure_location", departure_location);
        formData.append("batch_date", batch_date);
        formData.append("country_ids", country_ids);
        formData.append("country_name", country_name);
        formData.append("university_ids", university_ids);
        formData.append("university_name", university_name);

        // Append selected client list
        formData.append("client_list", JSON.stringify(client_selected_list)); // send array as JSON string

        // Send AJAX request
        $.ajax({
            url: "<?php echo base_url('admin/fly_batch/create_batch'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
                    window.location.href = "<?= base_url('admin/fly_batch') ?>";
                } else {
                    alert_float("danger", res.resp_desc || "An unknown error occurred.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }


    validate_fly_batch_form();
</script>
</body>

</html>