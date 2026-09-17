   <div class="clearfix"></div>
   <?php
    $roomCapacity = json_decode($universities[$hostelData->university_id ?? '']["rooms"], true) ?? [];
    $get_currencies = get_currencies();

    $hostel = getDataInformation('hostel', 'id, name', 'status = 1');
    $hostel_company = getDataInformation('hostel_company', 'id, name', 'status = 1');
    ?>
   <?= form_open('', ['id' => 'hostel_management_form']); ?>

   <input type="hidden" name="hostel_management_id" value="<?= $getId ?? '' ?>">
   <input type="hidden" name="hostel_type" value="<?= $hostelData->hostel_type ?? 'russia' ?>">

   <div class="row">


       <div class="col-md-3">
           <?= render_input('student_name', 'Name', $hostelData->name ?? '', 'text', ["placeholder" => "Enter Name"]); ?>
       </div>
       <div class="col-md-3">
           <?= render_input(
                'passport',
                'Passport Number',
                $hostelData->passport ?? '',
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
                [$hostelData->university_id ?? ''],
                ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, "onchange" => "get_hostel_rentInfo(this.value)"]
            );
            ?>
       </div>

       <!-- <div class="col-md-3">
           <?= render_input('floor_No', 'Floor No', $hostelData->floor_no ?? '', 'number', ["placeholder" => "Enter Floor No"]); ?>
       </div> -->

       <!-- <div class="col-md-3">
           <?= render_input('room_No', 'Room No', $hostelData->room_no ?? '', 'number', ["placeholder" => "Enter Room No"]); ?>
       </div> -->


       <div class="col-md-3">
           <?= render_select(
                'company',
                $hostel_company,
                ['id', 'name'],
                'Company',
                $hostelData->company ?? '',
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
                $hostelData->hostel ?? '',
                ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true]
            );
            ?>
       </div>

       <div class="col-lg-3">
           <div class="form-group">
               <label for="acadmic_year">Academic Year <small class="text-danger">*</small></label>
               <?php
                $currentYear = date("Y");
                $startYear = 2023;               // Start from 2023
                $endYear = $currentYear + 2;     // End at current year + 2

                // Generate academic years from 2023 up to currentYear + 2
                $years = [];
                for ($year = $startYear; $year < $endYear; $year++) {
                    $years[] = $year . " - " . ($year + 1);
                }

                // Use saved preference or default to current year range
                $selectedYear = !empty($hostelData->acadmic_year)
                    ? $hostelData->acadmic_year
                    : ($currentYear . " - " . ($currentYear + 1));
                ?>

               <select class="form-control" id="acadmic_year" name="acadmic_year" required>
                   <?php foreach ($years as $year): ?>
                       <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                           <?= $year ?>
                       </option>
                   <?php endforeach; ?>
               </select>
           </div>
       </div>


       <!-- <div class="col-md-3">
           <?= render_select(
                'room_capacity',
                $roomCapacity,
                ['room_capacity', 'room_capacity'],
                'Room Capacity',
                $hostelData->room_capacity ?? '',
                ['data-width' => '100%', 'data-none-selected-text' => 'No Selected', 'data-actions-box' => true, 'onchange' => 'selectRoomCapacity(this.value)']
            );
            ?>
       </div> -->

       <!-- <div class="col-md-3">
           <label>Room Rent <span class="text-danger">*</span></label><br>
           <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-3 form-group">
               <input type="text" name="rent" <?= $required ?> class="form-control currency-amount fees_rent" placeholder="0.00" id="rent" value="<?= $hostelData->rent_amount ?? '' ?>" size="8" onkeypress="return acceptText(this,'number')">
               <div class="input-group-addon currency-addon">
                   <select name="rent_currency_type" id="rent" class="currency-selector currency-selector-rent" onchange="updateSymbol('rent')">
                       <?php foreach ($get_currencies as $c) {
                        ?>
                           <option
                               data-symbol="<?= $c['symbol'] ?>"
                               value="<?= $c['id'] ?>"
                               data-placeholder="0.00" <?= $hostelData->currency ?? '' == $c['id'] ? 'selected' : '' ?>>
                               <?= $c['name'] ?>
                           </option>
                       <?php
                        }
                        ?>
                   </select>

               </div>
           </div>
       </div> -->

       <!-- <div class="col-md-3">
           <?= render_input('startdate', 'Start Date',  $hostelData->start_date ?? '', 'date'); ?>
       </div>

       <div class="col-md-3">
           <?= render_input('enddate', 'End Date',  $hostelData->end_date ?? '', 'date'); ?>
       </div> -->

   </div>
   <div class="pull-right mtop15">
       <button type="submit" class="btn btn-primary">Save</button>
       <!-- <button type="button" class="btn btn-secondary" onclick="modalClose('hostel_management')" data-bs-dismiss="modal">Close</button> -->
   </div>
   <?= form_close(); ?>


   <?php init_tail(); ?>

   <script>
       var get_university_rentData = <?= json_encode(array_column($universities, null, "university_id"), true) ?>;

       var selectedUniversityRoomData = [];

       function get_hostel_rentInfo(id) {
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

           // Initialize form validation
           appValidateForm($('#hostel_management_form'), {
               student_name: 'required',
               university_id: 'required',
               passport: 'required',
               room_No: 'required',
               company: 'required',
               hostel: 'required',
               room_capacity: 'required',
               rent: 'required',
               rent_currency_type: 'required',
               startdate: 'required',
               enddate: 'required'
           });


       });

       $('#hostel_management_form').on('submit', function(e) {
           e.preventDefault(); // Prevent default submit

           var form = $(this);

           // Check if form is valid
           if (!form.valid()) {
               // If validation fails, stop submission
               return false;
           }

           var url = '<?= admin_url("hostel_management/save_hostel_details"); ?>';
           var formData = new FormData(this);

           // Append country_name from select
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