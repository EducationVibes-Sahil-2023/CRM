<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$roomCapacity = [];

for ($i = 1; $i <= 6; $i++) {
    $roomCapacity[] = [
        'id' => $i,
        'name' => $i
    ];
}

$statusJson = [
    ['id' => 1, 'name' => 'active'],
    ['id' => 2, 'name' => 'not active']
];

$paymentStatus = [
    ['id' => 1, 'name' => 'Paid'],
    ['id' => 2, 'name' => 'Dues'],
    // ['id' => 3, 'name' => 'new']
];


$get_currencies = get_currencies();

$table_data = array(
    array('name' => 'Name'),
    array('name' => 'Passport No'),
    array('name' => 'University Name'),
    array('name' => 'Acadmic Year'),
    array('name' => 'Year'),
    array('name' => 'Company'),
    array('name' => 'Hostel'),
    // array('name' => 'Start Date'),
    // array('name' => 'End Date'),
    // array('name' => 'No of Months'),
);


?>
<div id="wrapper">
    <style>
        .margin-top {
            margin-top: 20px;
        }
    </style>
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <?php


                        if (is_admin()) {
                        ?>
                            <div id="filterArea" class=" hidden-xs">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="bold"><?php echo _l('filter_by'); ?></p>
                                    </div>
                                    <?php if (has_permission('hostel_management', '', 'view') || has_permission('hostel_management', '', 'own_view')) { ?>
                                        <!--<div class="col-md-2  margin-top leads-filter-column">-->
                                        <!--   <?php echo render_select('view_assigned[]', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'view_assigned'); ?>-->
                                        <!--</div>-->
                                    <?php } ?>

                                    <div class="col-md-2  margin-top leads-filter-column">
                                        <?php echo render_select('room_capacity[]', $roomCapacity, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Room Capacity", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'room_capacity'); ?>
                                    </div>

                                    <div class="col-md-2  margin-top leads-filter-column">
                                        <?php echo render_select('hostel_company[]', $hostel_company, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Hostel Company", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'hostel_company'); ?>
                                    </div>

                                    <div class="col-md-2  margin-top leads-filter-column">
                                        <?php echo render_select('hostel_name[]', $hostel, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Hostel Name", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, 'hostel_name'); ?>
                                    </div>

                                    <div class="col-lg-2 margin-top leads-filter-column">
                                        <div class="form-group">
                                            <!--<label for="session_intake">Start Date <small class="text-danger">*</small></label>-->
                                            <input type="month" class="form-control" id="start_date" name="start_date"

                                                placeholder="Select Month and Year">
                                        </div>
                                    </div>

                                    <div class="col-md-2 margin-top leads-filter-column">
                                        <?php
                                        echo render_select(
                                            'status[]',                 // name
                                            $statusJson,                // options array
                                            array('id', 'name'),        // key & value fields
                                            '',                         // label (none)
                                            '',                         // selected value
                                            array(
                                                'data-width' => '100%',
                                                'data-none-selected-text' => "Status",
                                                'multiple' => true,
                                                'data-actions-box' => true
                                            ),
                                            array(),
                                            'no-mbot',
                                            '',
                                            false,
                                            'status'
                                        );
                                        ?>
                                    </div>

                                    <div class="col-md-2 margin-top leads-filter-column">
                                        <?php
                                        echo render_select(
                                            'payment_status[]',
                                            $paymentStatus,
                                            array('id', 'name'),
                                            '',
                                            '',
                                            array(
                                                'data-width' => '100%',
                                                'data-none-selected-text' => "Payment Status",
                                                'multiple' => true,
                                                'data-actions-box' => true
                                            ),
                                            array(),
                                            'no-mbot',
                                            '',
                                            false,
                                            'payment_status'
                                        );
                                        ?>
                                    </div>

                                    <div class="col-md-4 margin-top leads-filter-column">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" id="apply_filter">Apply Filter</button>

                                            <!-- <button class="btn btn-primary" id="apply_filter">Apply Filter</button> -->
                                            <button class="btn btn-primary" onclick="window. location. reload();">Reset</button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        <?php

                        }

                        ?>
                    </div>
                </div>
                <br>
                <div class="panel_s">
                    <div class="panel-body">



                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo _l('Hostel Management'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('hostel_management', '', 'create')) {
                                ?>
                                    <button type="button" onclick="modalOpen('hostel_management')" data-bs-toggle="modal" data-bs-target="#hostel_management" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('create'); ?>
                                    </button>

                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />

                        <div class="clearfix mtop20"></div>
                        <?php
                        render_datatable($table_data, 'hostel', [], [
                            'data-last-order-identifier' => 'hostel',
                            'data-default-order'         => get_table_last_order('hostel'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hostel_management" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="hostelManagementLabel">Hostel Rental Information</h4>
                <button type="button" class="btn-close" onclick="modalClose('hostel_management')" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <?= form_open('', ['id' => 'hostel_management_form']); ?>
            <div class="modal-body">
                <input type="hidden" name="hostel_management_id" value="">
                <div class="row">


                    <div class="col-md-3">
                        <?= render_input('student_name', 'Name', '', 'text', ["placeholder" => "Enter Name"]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= render_input(
                            'passport',
                            'Passport Number',
                            "",
                            'text',
                            [
                                "placeholder" => "Passport Number",
                                "pattern"   => "^[A-Z0-9]{6,9}$",
                                "title"     => "Passport number must be 6 to 9 characters, only uppercase letters (A-Z) and numbers (0-9).",
                                "maxlength" => "9",
                                "minlength" => "6"
                            ]
                        ); ?>
                    </div>

                    <div class="col-md-3">
                        <?= render_select(
                            'university_id',
                            $universities,
                            ['university_id', 'university_name'],
                            'University Name',
                            '',
                            ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, "onchange" => "get_university_rentInfo(this.value)"]
                        );
                        ?>
                    </div>

                    <!--<div class="col-md-3">-->
                    <!--    <?= render_input('floor_No', 'Floor No', '', 'number', ["placeholder" => "Enter Floor No"]); ?>-->
                    <!--</div>-->

                    <!--<div class="col-md-3">-->
                    <!--    <?= render_input('room_No', 'Room No', '', 'number', ["placeholder" => "Enter Room No"]); ?>-->
                    <!--</div>-->


                    <div class="col-md-3">
                        <?= render_select(
                            'company',
                            $hostel_company,
                            ['id', 'name'],
                            'Company',
                            '',
                            ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true]
                        );
                        ?>
                    </div>

                    <div class="col-md-3">
                        <?= render_select(
                            'hostel',
                            $hostel,
                            ['id', 'name'],
                            'Hostel',
                            '',
                            ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true]
                        );
                        ?>
                    </div>

                    <div class="col-md-3 hide">
                        <?= render_select(
                            'room_capacity',
                            [],
                            ['id', 'name'],
                            'Room Capacity',
                            '',
                            ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, 'onchange' => 'selectRoomCapacity(this.value)']
                        );
                        ?>
                    </div>

                    <div class="col-md-3 hide">
                        <label>Room Rent <span class="text-danger">*</span></label><br>
                        <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
                            <input type="text" name="rent" <?= $required ?> class="form-control currency-amount fees_rent" placeholder="0.00" id="rent" value="" size="8" onkeypress="return acceptText(this,'number')">
                            <div class="input-group-addon currency-addon">
                                <select name="rent_currency_type" id="rent" class="currency-selector currency-selector-rent" onchange="updateSymbol('rent')">
                                    <?php foreach ($get_currencies as $c) {
                                    ?>
                                        <option
                                            data-symbol="<?= $c['symbol'] ?>"
                                            value="<?= $c['id'] ?>"
                                            data-placeholder="0.00">
                                            <?= $c['name'] ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>

                            </div>
                        </div>
                    </div>


                    <div class="col-lg-3">
                        <label for="acadmic_year">Academic Year <span class="text-danger">*</span></label>
                        <div class="form-group">
                            <?php
                            $startYear = 2023; // Always start from 2023
                            $endYear = date("Y") + 2; // End at current year + 2

                            $years = [];
                            $years[] = ['id' => '', 'name' => 'Select Academic Year']; // default option

                            for ($year = $startYear; $year < $endYear; $year++) {
                                $label = $year . ' - ' . ($year + 1);
                                $years[] = ['id' => $label, 'name' => $label];
                            }

                            ?>

                            <select class="form-control" id="acadmic_year" name="acadmic_year" required>
                                <?php foreach ($years as $year): ?>
                                    <option value="<?= $year["id"] ?>"><?= $year["name"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                    <!-- <div class="col-md-3"> -->
                    <!--    <?= render_input('startdate', 'Start Date', '', 'date'); ?>-->
                    <!--</div>-->

                    <!--<div class="col-md-3">-->
                    <!--    <?= render_input('enddate', 'End Date', '', 'date'); ?>-->
                    <!--</div>-->

                    <!-- </div> -->
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="modalClose('hostel_management')" data-bs-dismiss="modal">Close</button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>


    <?php init_tail(); ?>
    <script>
        var tAPI = "";
        var get_university_rentData = <?= json_encode(array_column($universities, null, "university_id"), true) ?>;

        var selectedUniversityRoomData = [];

        function get_university_rentInfo(id) {
            $("select[name='room_capacity']").html('');
            $("select[name='room_capacity']").append('<option value="">No Selected</option>');
            if (get_university_rentData[id]) {
                console.log(get_university_rentData[id]);

                let rooms = get_university_rentData[id].rooms ? JSON.parse(get_university_rentData[id].rooms) : [];
                selectedUniversityRoomData = rooms;
                rooms.forEach(room => {
                    $("select[name='room_capacity']").append('<option value="' + room.room_capacity + '">' + room.room_capacity + '</option>');
                });

            } else {
                alert_float('danger', 'No rental information found for the selected university.');
            }
            $("select[name='room_capacity']").selectpicker('refresh');
        }


        function selectRoomCapacity(id) {
            let roomData = selectedUniversityRoomData.find(r => r.room_capacity == id);
            if (roomData) {
                console.log("roomData", roomData);
                $("input[name='rent']").val(roomData.rent);
                $("select[name='rent_currency_type']").val(roomData.currency);
            } else {
                $("input[name='rent']").val('');
                $("select[name='rent_currency_type']").val('');
            }
        }
        $(function() {
            var CustomersServerParams = {};
            $.each($('._hidden_inputs._filters input'), function() {
                CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
            });
            tAPI = initDataTable('.table-hostel', admin_url + 'hostel_management/table/russia_hostel', [0], [0], CustomersServerParams);

            // Initialize form validation
            appValidateForm($('#hostel_management_form'), {
                student_name: 'required',
                university_id: 'required',
                // floor_No: 'required',
                // room_No: 'required',
                company: 'required',
                hostel: 'required',
                room_capacity: 'required',
                acadmic_year: 'required',
                // rent: 'required',
                // rent_currency_type: 'required',
                // startdate: 'required',
                // enddate: 'required'
            });


        });

        function Delete(id) {
            if (!confirm('Are you sure you want to delete this hostel record?')) {
                return;
            }

            $.ajax({
                url: '<?= admin_url("hostel_management/delete/"); ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.resp_code === 'RCS') {
                        alert_float('success', response.resp_desc);
                        // Remove row from table (if using DataTables)
                        tAPI.ajax.reload();
                    } else {
                        alert_float('danger', response.resp_desc);
                    }
                },
                error: function(xhr, status, error) {
                    alert_float('danger', 'Something went wrong: ' + error);
                }
            });
        }

        function modalOpen(id) {
            // Reset form
            var form = $('#' + id + '_form');
            form[0].reset();
            form.find('input[name="hostel_management_id"]').val('');
            form.find('select.selectpicker').selectpicker('refresh');

            // Open modal
            $('#' + id).modal('show');
        }


        function modalClose(id) {
            // Reset form
            var form = $('#' + id + '_form');
            form[0].reset();
            form.find('input[name="hostel_management_id"]').val('');
            form.find('select.selectpicker').selectpicker('refresh');

            // Open modal
            $('#' + id).modal('hide');
        }


        $('#hostel_management_form').on('hidden.bs.modal', function() {
            // Clear hidden ID
            $(this).find('input[name="hostel_management_id"]').val('');
            // Optional: reset the entire form
            $(this).find('form')[0].reset();
            $(this).find('form select.selectpicker').selectpicker('refresh');
        });

        $('#hostel_management_form').on('submit', function(e) {
            e.preventDefault(); // stop normal submission

            var form = $(this);
            var isValid = true; // validation flag

            // Clear previous errors
            form.find('.is-invalid').removeClass('is-invalid');

            // Loop through required visible fields
            form.find("input[required]:not([type='hidden']):visible, select[required]:visible, textarea[required]:visible")
                .each(function() {
                    let value = $(this).val();
                    if (!value || String(value).trim() === "") {
                        $(this).addClass("is-invalid");
                        isValid = false; // found an invalid field

                        // Optional: show tooltip or inline message
                        let label = $(this).closest(".form-group").find("label").text().trim();
                        let fieldName = label || $(this).attr("name");
                        console.warn("Missing required:", fieldName);
                    }
                });

            // Stop submission if invalid
            if (!isValid) {
                alert_float('warning', 'Please fill all required fields before submitting.');
                return false; // exit early
            }

            // Proceed with AJAX submit if all fields valid
            var url = '<?= admin_url("hostel_management/save_hostel_details"); ?>';
            var formData = new FormData(this);

            // Append additional field(s)
            var university_text = $('#university_id option:selected').text() || '';
            formData.append('university_name', university_text);

            show_loader();

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    hide_loader();

                    if (response.resp_code === 'RCS') {
                        alert_float('success', response.resp_desc);
                        $("#hostel_management").modal('hide');
                        form[0].reset();
                        form.find('select.selectpicker').selectpicker('refresh');
                        tAPI.ajax.reload(null, false); // refresh DataTable
                    } else {
                        alert_float('danger', 'Error: ' + response.resp_desc);
                    }
                },
                error: function(xhr, status, error) {
                    hide_loader();
                    alert_float('danger', 'Something went wrong: ' + error);
                }
            });
        });


        document.getElementById("passport").addEventListener("input", function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Convert to uppercase & remove invalid characters
        });
    </script>