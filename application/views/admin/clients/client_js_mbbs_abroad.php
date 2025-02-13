<script>
    // comman functions


    function get_media_docs(id, formData) {
        formData.doc_url = [];
        return new Promise((resolve) => {
            $("#" + id).each(function() {
                let fileInput = $(this).find("input[type='file']")[0]; // Get file input
                let files = fileInput?.files || []; // Get files or empty array if no file input

                let docType = $(this).find("input[name='doc_type[]']").val()?.trim(); // Get doc_type
                let docName = $(this).find("input[name='doc_name[]']").val()?.trim(); // Get doc_name
                let docUrl = $(this).find("input[name='doc_url[]']").val()?.trim(); // Get doc_url

                if (files.length > 0) {
                    // Append files to FormData
                    Array.from(files).forEach((file) => {
                        formData.append(`files_${docType || "unknown"}`, file);
                    });

                    if (docType) formData.append("doc_type_id[]", docType);
                    if (docName) formData.append("doc_type_name[]", docName);
                    formData.append("doc_url[]", ""); // Empty URL since file is uploaded
                } else if (docUrl) {
                    // Handle URL-based documents if no file is uploaded
                    formData.append("doc_url[]", docUrl);
                    if (docType) formData.append("doc_type_id[]", docType);
                    if (docName) formData.append("doc_type_name[]", docName);
                }
            });

            resolve(); // Resolve Promise after processing is done
        });
    }

    // student js 
    async function save_basic_details() {
        var additional_fields = {};
        var form_status = true;

        $("#basic-information-form input, #basic-information-form select, #basic-information-form input[type='date']").each(function() {
            const value = $(this).val()?.trim(); // Get trimmed value
            const isRequired = $(this).attr("required-check") !== undefined; // Check if 'required-check' exists
            const name = $(this).attr("name"); // Get name attribute

            if (isRequired && name) {
                additional_fields[name] = "required";
                if (!value) {
                    form_status = false;
                }
            }
        });

        if (!form_status) {
            appValidateForm($("#basic-information-form"), additional_fields);
            return false;
        }

        let formData = new FormData($("#basic-information-form")[0]); // Create FormData from form

        // Wait for media files to be processed before proceeding
        await get_media_docs("basic-information-form .media-files", formData);

        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("clientid", $('input[name="clientid"]').val());

        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/student_update' ?>",
            type: "POST",
            data: formData,
            contentType: false, // Important for FormData
            processData: false, // Prevent jQuery from processing data
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
                } else if (res.resp_code && res.resp_desc) {
                    alert_float("danger", res.resp_desc);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }

    // passport js
    async function save_passport_details() {

        var additional_fields = {};
        var form_status = true;

        $("#passport-form input:visible, #passport-form select:visible, #passport-form input[type='date']:visible").each(function() {
            const value = $(this).val()?.trim(); // Get trimmed value
            const isRequired = $(this).attr("required-check") !== undefined; // Check if 'required-check' exists
            const name = $(this).attr("name"); // Get name attribute

            if (isRequired && name) {
                additional_fields[name] = "required";
                if (!value) {
                    form_status = false;
                }
            }
        });

        if (!form_status) {
            appValidateForm($("#passport-form"), additional_fields);
            return false;
        }

        let formData = new FormData($("#passport-form")[0]); // Create FormData from form

        // Wait for media files to be processed before proceeding
        await get_media_docs("passport-form .media-files", formData);

        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("clientid", $('input[name="clientid"]').val());

        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/passport_info' ?>",
            type: "POST",
            data: formData,
            contentType: false, // Important for FormData
            processData: false, // Prevent jQuery from processing data
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
                } else if (res.resp_code && res.resp_desc) {
                    alert_float("danger", res.resp_desc);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });

    }

    function change_passport_status() {
        $("#passport_number").val("");
        let status = $("#passport option:selected").attr("data-passport_number_status");
        if (status == 1) {
            $(".passport-div-status").show();
            $(".passport-div-status").removeClass("hide");
        } else {
            $(".passport-div-status").hide();
            $(".passport-div-status input").val('');
            $(".passport-div-status").addClass("hide");
        }
    }

    // admission prefrencess 

    $(document).ready(function() {
        $(document).ready(function() {
            $("#session_intake").on("change", function() {
                let selectedDate = $(this).val(); // Get selected value (YYYY-MM)

                if (selectedDate) {
                    let [year, month] = selectedDate.split("-"); // Extract year and month

                    if (month !== "02" && month !== "09") {
                        // If not February or September, auto-correct to the nearest allowed month
                        let correctedMonth = (month < "06") ? "02" : "09"; // Before June → February, After → September
                        $(this).val(`${year}-${correctedMonth}`);
                        alert("Only February and September are allowed.");
                    }
                }
            });

            // Pre-fill with the nearest allowed month on page load
            let today = new Date();
            let currentYear = today.getFullYear();
            let currentMonth = today.getMonth() + 1; // JavaScript months are 0-based
            let defaultMonth = (currentMonth < 6) ? "02" : "09"; // Before June → February, After → September
            $("#session_intake").val(`${currentYear}-${defaultMonth}`);

            $('#study_country').trigger('change');

            if (final_sumbit == 1) {
                setTimeout(() => {
                    $(".form-disabled").each(function() {
                        $(this).find("input, select,.dropdown-toggle ").attr("disabled", true); // Disable inputs & selects inside .form-disabled
                        $(".btn-save-fun").hide();
                    });
                }, 100);



            }




        });
    });

    function save_admission_preferences() {
        var additional_fields = {};
        var form_status = true;

        // Iterate through inputs, selects, and date fields
        $("#admission-preferences-form input, #admission-preferences-form select, #admission-preferences-form date").each(function() {
            const value = $(this).val(); // Get the value of the field
            const isrequired = $(this).attr("required-check") !== undefined; // Check for 'required-check' attribute
            const name = $(this).attr("name"); // Get the name attribute

            if (isrequired && name) {
                // Add to additional_fields with validation rule
                additional_fields[name] = "required";
                if ($.trim(value) === "") {
                    form_status = false;

                }
            }
        });

        console.log(form_status);
        if (!form_status) {
            appValidateForm($("#admission-preferences-form"), additional_fields);
            return false; // Prevent form submission if validation fails
        }

        // Collect form data
        const params = {
            program: $('#program').val(),
            course: "MBBS",
            sessionIntake: $('#session_intake').val(),
            acadmic_year: $('#acadmic_year').val(),
            countries: $('#study_country').val() ? $('#study_country').val().join(",") : "",
            entranceExamDetails: $('#entrance_exam_details').val(),
            admissionPreferencesId: $('#admissionpreferencesid').val(),
            client_id: $('#client_id').val(),
            course_name: $('#course_name').val(),
            universities: {}
        };

        try {
            // Validate selected universities for each country
            const countriesArr = $('#study_country').val();
            if (countriesArr) {
                $.each(countriesArr, function(index, value) {
                    const key = value.replace(" ", "_");
                    params.universities[key] = $(`#university${index}`).val();
                    if (!params.universities[key]) {
                        alert_float('danger', `Select a university for ${value}.`);
                        throw new Error(`University selection for ${value} is required.`);
                    }
                });
            }


            // Submit the form data via AJAX
            $.ajax({
                url: "<?php echo base_url() . 'admin/clients/update_admission_preferences' ?>",
                type: "POST",
                data: params,
                dataType: "JSON",
                success: function(res) {
                    if (res.resp_id !== undefined) {
                        $('#admissionpreferencesid').val(res.resp_id);
                    }
                    alert_float('success', res.resp_desc);
                },
                error: function(err) {
                    alert_float('danger', "An error occurred during submission.");
                    console.error(err);
                }
            });
        } catch (error) {
            console.error(error.message);
        }
    }

    // FREEZE ADMISSION PREFERENCES

    $('#freeze_admission_preferences').on('click', function(e) {
        e.preventDefault()

        let admissionPreferencesId = $('#admissionpreferencesid').val()

        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/freeze_admission_preferences' ?>",
            type: "POST",
            data: {
                admissionPreferencesId: admissionPreferencesId
            },
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code == 'RCS') {
                    var freeze = res.data.is_freezed == 0 ? 'Freeze' : 'Unfreeze'
                    if (res.data.is_freezed != 0) {
                        set_frezee();
                    } else {
                        un_set_frezee();
                    }
                    $('#freeze_admission_preferences').html(freeze)
                }
                alert(res.resp_desc)
            }
        })
    })

    function set_frezee() {
        $("#admission_preferences").find('input,select').attr("disabled", true).selectpicker("refresh");
        setTimeout(() => {
            $(".tags-input-wrapper").css("pointer-events", "none");
        }, 2000);
        $("#save_admission_preferences").attr("disabled", true);
    }

    function un_set_frezee() {
        $("#admission_preferences").find('input,select').attr("disabled", false).selectpicker("refresh");
        $(".tags-input-wrapper").css("pointer-events", "");
        $("#save_admission_preferences").attr("disabled", false);

    }
    if (admissionpreferences_freeze == 1) {
        set_frezee();
    }




    // acadmic details

    async function save_admission_details() {

        var additional_fields = {};
        var form_status = true;
        var formData = new FormData(); // Initialize FormData

        // Iterate through inputs, selects, and date fields
        $("#admission-details-form input:visible, #admission-details-form select:visible, #admission-details-form date:visible").each(function() {
            const value = $(this).val();
            const isRequired = $(this).attr("required-check") !== undefined;
            const name = $(this).attr("name");

            if (isRequired && name) {
                additional_fields[name] = "required";
                if ($.trim(value) === "") {
                    form_status = false;
                    $(this).addClass("error");
                }
            }
        });

        if (!form_status) {
            appValidateForm($("#admission-details-form"), additional_fields);
            return false;
        }

        // Collect form data and append to FormData
        $("#admission-details-form div > input, #admission-details-form div > textarea, #admission-details-form div > select").each(function() {
            let name = $(this).attr("name");
            let type = $(this).attr("type");

            if (!name) return; // Skip if no name attribute

            if (type === "radio" && !$(this).prop("checked")) return; // Only add checked radio buttons

            let value = type === "file" ? $(this)[0].files[0] : $(this).val(); // Handle file input separately

            if (value !== undefined && value !== null) {
                formData.append(name, value);
            }
        });

        // Get CSRF token and client ID
        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("clientid", $('input[name="clientid"]').val());

        // Handle media files
        await get_media_docs("admission-details-form .media-files", formData);


        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/student_acadmic' ?>",
            type: "POST",
            data: formData,
            dataType: "JSON",
            processData: false, // Prevent jQuery from processing data
            contentType: false, // Prevent jQuery from setting content type
            success: function(res) {
                if (res.resp_code == 'RCS') {
                    alert_float('success', res.resp_desc);
                } else {
                    if (res.resp_code != '' && res.resp_desc != '') {
                        alert_float('danger', res.resp_desc);
                    }
                }
            }
        })
    }


    $(document).ready(function() {
        $("#twelth_result_status").on("change", function() {
            let show = $(this).val() === "Declared";
            $(".twelth_result_status_div").toggle(show).find("input,select,file").val("").selectpicker("refresh");
            $(".twelth_result_status_div").toggle(show);
        });

        $("#entrance_result_status").on("change", function() {
            let ers = $(this).val();
            let isAwaited = ers === "Awaited",
                isNotAppeared = ers === "Not Appeared";

            // Toggle visibility
            $(".hide_").toggle(!isAwaited);

            // Disable and clear fields when "Not Appeared" is selected
            $("input[name='entrance_roll'], input[name='entrance_year'], input[name='entrance_percentage'], input.multiple_score, #entrance_exam_div input[type='file']")
                .prop("disabled", isNotAppeared)
                .val(isNotAppeared ? "" : null)
                .attr("required-check", isNotAppeared ? null : "required");

            // Toggle multiple score inputs and labels
            $("input.multiple_score, .multiple_score_label").toggle(!isAwaited);
        });

        setTimeout(() => {
            if (typeof set_primary_diabled === "function") {
                set_primary_diabled();
            } else {
                console.warn("set_primary_diabled function does not exist.");
            }

            if (typeof set_university_diabled === "function") {
                set_university_diabled();
            } else {
                console.warn("set_university_diabled function does not exist.");
            }
        }, 100);


    });


    // Save Documents
    function save_documents() {
        var additional_fields = {};
        var form_status = true;

        // Validate visible input, select, and date fields
        $("#documents-form input:visible, #documents-form select:visible, #documents-form date:visible").each(function() {
            const value = $(this).val(); // Get the value of the field
            const isRequired = $(this).attr("required-check") !== undefined; // Check for 'required-check' attribute
            const name = $(this).attr("name"); // Get the name attribute

            if (isRequired && name) {
                // Add field to validation rules
                additional_fields[name] = "required";
                if ($.trim(value) === "") {
                    form_status = false;
                    // Highlight invalid fields
                    $(this).addClass("error");
                } else {
                    $(this).removeClass("error");
                }
            }
        });

        if (!form_status) {
            // Show validation errors
            appValidateForm($("#documents-form"), additional_fields);
            return false; // Prevent form submission
        }

        // Initialize FormData object
        let formData = new FormData();

        // Append file inputs to FormData
        $("#documents-form input[type='file']").each(function() {
            let name = $(this).attr("name"); // Get the name of the file input
            let files = $(this)[0].files; // Get the file list
            let row = $(this).closest("tr"); // Closest row for associated fields
            let doc_type = row.find("input[name='doc_type[]']").val(); // Get doc_type from the row
            let doc_name = row.find("input[name='doc_name[]']").val(); // Get doc_name from the row
            let doc_url = row.find("input[name='doc_url[]']").val(); // Get doc_url from the row


            if (files.length > 0) {
                // Handle files
                for (let i = 0; i < files.length; i++) {
                    formData.append("files_" + doc_type, files[i]); // Append file to FormData

                }
                if (doc_type) {
                    formData.append("doc_type_id[]", doc_type); // Append doc_type ID
                }
                if (doc_name) {
                    formData.append("doc_type_name[]", doc_name); // Append doc_type name
                }
                formData.append("doc_url[]", ''); // Append URL
            } else if (doc_url && doc_url.trim() !== '') {
                // Handle URLs if no files are uploaded
                formData.append("doc_url[]", doc_url); // Append URL
                if (doc_type) {
                    formData.append("doc_type_id[]", doc_type); // Append doc_type ID
                }
                if (doc_name) {
                    formData.append("doc_type_name[]", doc_name); // Append doc_type name
                }
            }
        });


        // Serialize and append other form fields to FormData
        // $("#documents-form")
        //     .serializeArray()
        //     .forEach(function(item) {
        //         formData.append(item.name, item.value);
        //     });

        // Append CSRF token and client ID
        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("clientid", $('input[name="clientid"]').val());

        // AJAX request to upload documents
        $.ajax({
            url: "<?php echo base_url('admin/clients/upload_documents'); ?>",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from transforming FormData
            contentType: false, // Ensure correct Content-Type is set for FormData
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
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

    async function save_welcome_info() {
        var additional_fields = {};
        var form_status = true;

        $("#welcome-information-form input, #welcome-information-form select, #welcome-information-form input[type='date']").each(function() {
            const value = $(this).val()?.trim(); // Get trimmed value
            const isRequired = $(this).attr("required-check") !== undefined; // Check if 'required-check' exists
            const name = $(this).attr("name"); // Get name attribute
            console.log(isRequired);
            if (isRequired && name) {
                additional_fields[name] = "required";
                if (!value) {
                    form_status = false;
                }
            }
        });

        if (!form_status) {
            appValidateForm($("#welcome-information-form"), additional_fields);
            return false;
        }


        let formData = new FormData(document.getElementById('welcome-information-form')); // Correct way to initialize FormData

        // Append CSRF token and client ID
        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("clientid", $('input[name="clientid"]').val());


        // Function to process media files


        //

        // AJAX request to upload documents
        $.ajax({
            url: "<?php echo base_url('admin/clients/welcome_configuration'); ?>",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from transforming FormData
            contentType: false, // Ensure correct Content-Type is set for FormData
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
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


    $(".nav-tabs li").on("click", function() {
        if (final_sumbit != 1) {
            $(".btn-save-fun").show(); // Show the save button
            $(".tab-pane").find("input,select").attr("readonly", false);
        }

    });


    function show_all_data() {
        setTimeout(() => {
            $(".tab-pane").addClass("active");
            $(".tab-pane").find("input,select").attr("readonly", true);
            $(".btn-save-fun").hide();
        }, 0);

    }

    // final subbition
    function final_submission() {

        var additional_fields = {};
        var form_status = true;

        $("form").each(function() {
            let form = $(this); // Cache the form element
            let formId = form.attr("id");
            $("#" + formId + " input, #" + formId + " select, #" + formId + " input[type='date']").attr("disabled", false);
            $("#" + formId + " input, #" + formId + " select, #" + formId + " input[type='date']").each(function() {
                let $input = $(this);
                let value = $input.val();
                let isRequired = $input.attr("required-check") !== undefined; // Check if 'required-check' exists
                let name = $input.attr("name"); // Get name attribute

                if (isRequired && name) {
                    additional_fields[name] = "required";
                    if (!value) {
                        console.log(name);
                        form_status = false;
                    }
                }
            });

            // Validate the form if any required field is missing
            if (!form_status) {
                alert("First fill all requried fields");
                appValidateForm("#" + formId, additional_fields);
                return false;
            }
        });

        if (!form_status) {

            return false;
        }







        let formData = new FormData(); // Correct way to initialize FormData

        // Append CSRF token and client ID
        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("clientid", $('input[name="clientid"]').val());
        formData.append("submition_status", 1);


        // Function to process media files


        //

        // AJAX request to upload documents
        $.ajax({
            url: "<?php echo base_url('admin/clients/final_submitted'); ?>",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from transforming FormData
            contentType: false, // Ensure correct Content-Type is set for FormData
            dataType: "JSON",
            success: function(res) {
                if (res.resp_code === "RCS") {
                    alert_float("success", res.resp_desc);
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

    // set university
    var suggetions_university = [];
    const countriesArr = <?php echo !empty($admissionpreferences) ? json_encode(explode(",", $admissionpreferences->study_country)) : '[]' ?>;
    const universityArr = <?php echo !empty($admissionpreferences->university) ? $admissionpreferences->university : '{}' ?>;

    var selectedUniversityArr = [];
    // const universityArr = {};
    $('#study_country').on('change select2:opening', async function() {
        set_primary_enabled();
        let value = $(this).val();
        if (value.length > 0) {
            if (value.length <= 2) {
                selectedUniversityArr = [];
                $('#countries').val(value.join(','));
                var str = '';
                for (let k = 0; k < value.length; k++) {
                    var v = value[k];
                    var countryName = v.search("_") != -1 ? v.replace("_", " ") : v;
                    let c = v.replace(" ", "_");
                    str += `<div class="col-lg-6">
          <div class="form-group">
            <label for="university${k}">${countryName} University</label>
            <input type="hidden" class="form-control suggest_university" data-country-name="${countryName}" name="university${k}" id="university${k}" value="" required>
          </div>
        </div>`;

                    if (Object.keys(universityArr).length > 0) {
                        if (c in universityArr) {
                            selectedUniversityArr[countryName] = universityArr[c];
                        } else {
                            selectedUniversityArr[countryName] = "";
                        }
                    }
                }

                $('.universities').html(str);

                if (Object.keys(selectedUniversityArr).length > 0) {
                    // console.log(selectedUniversityArr);
                    var count2 = 0;

                    for (const k in selectedUniversityArr) {
                        const v = selectedUniversityArr[k];
                        var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;
                        // console.log(countryName);

                        let university_list = await show_university_dropdown(select_segment_default, k);
                        //console.log(university_list);
                        //console.log(university_list);
                        var tagInput1 = new TagsInput({
                            selector: `university${count2}`,
                            duplicate: false,
                            max: 5,
                            suggestions: university_list
                        });

                        if (v !== '') {
                            //console.log(v);
                            if (typeof v !== 'undefined') {
                                var defaultVal = v.split(",");
                                defaultVal.forEach(function(k1, v1) {
                                    tagInput1.addData([k1]);
                                });
                            }
                        }
                        count2++;
                        suggetions_university[countryName] = university_list;
                    }
                } else {
                    for (let k = 0; k < value.length; k++) {
                        var v = value[k];
                        //console.log(value);
                        let university_list = await show_university_dropdown(select_segment_default, v);
                        //console.log(university_list);
                        var countryName = v.search("_") != -1 ? v.replace("_", " ") : v;

                        //console.log(university_list);
                        var tagInput1 = new TagsInput({
                            selector: `university${k}`,
                            duplicate: false,
                            max: 5,
                            suggestions: university_list
                        });

                        suggetions_university[countryName] = university_list;
                    }
                }
            }

            if (value.length >= 2) {
                $(`#study_country option`).prop('disabled', true);
                for (let k = 0; k < value.length; k++) {
                    var v = value[k];
                    $(`#study_country option[value="${v}"]`).prop('disabled', false);
                }
            } else {
                // $(`#study_country option`).prop('disabled', false);

                // set staff configuration
                $('#study_country option:not([not-change])').prop('disabled', false);

            }
            $('#study_country').selectpicker('refresh');
        } else {
            $('#countries').val("");
            $('.universities').html("");
        }
        // setTimeout(() => {
        set_primary_diabled();
        // }, 300);
        // set_university();
    });



    // Plugin Constructor
    var TagsInput = function(opts) {
        this.options = Object.assign(TagsInput.defaults, opts);
        this.init();
        set_university_diabled();
    }

    // Initialize the plugin
    TagsInput.prototype.init = function(opts) {
        this.options = opts ? Object.assign(this.options, opts) : this.options;

        if (this.initialized)
            this.destroy();
        //console.log(this)
        if (!(this.orignal_input = document.getElementById(this.options.selector))) {
            console.error("tags-input couldn't find an element with the specified ID");
            return this;
        }

        this.arr = [];
        this.wrapper = document.createElement('div');
        this.input = document.createElement('input');
        init(this);
        initEvents(this);

        this.initialized = true;
        return this;
    }

    // Add Tags
    TagsInput.prototype.addTag = function(string) {

        if (this.anyErrors(string))
            return;

        this.arr.push(string);
        var tagInput = this;

        var tag = document.createElement('span');
        tag.className = this.options.tagClass;
        tag.innerText = string;

        var closeIcon = document.createElement('a');
        closeIcon.innerHTML = '&times;';

        // delete the tag when icon is clicked
        closeIcon.addEventListener('click', function(e) {
            e.preventDefault();
            var tag = this.parentNode;

            for (var i = 0; i < tagInput.wrapper.childNodes.length; i++) {
                if (tagInput.wrapper.childNodes[i] == tag)
                    tagInput.deleteTag(tag, i);
            }
        })


        tag.appendChild(closeIcon);
        this.wrapper.insertBefore(tag, this.input);
        this.orignal_input.value = this.arr.join(',');

        return this;
    }

    // Delete Tags
    TagsInput.prototype.deleteTag = function(tag, i) {
        tag.remove();
        this.arr.splice(i, 1);
        this.orignal_input.value = this.arr.join(',');
        return this;
    }

    // Make sure input string have no error with the plugin
    TagsInput.prototype.anyErrors = function(string) {
        if (this.options.max != null && this.arr.length >= this.options.max) {
            alert('max university limit reached for this country');
            return true;
        }

        if (!this.options.duplicate && this.arr.indexOf(string) != -1) {
            alert('duplicate found " ' + string + ' " ')
            return true;
        }

        return false;
    }

    // Add tags programmatically 
    TagsInput.prototype.addData = function(array) {
        var plugin = this;

        array.forEach(function(string) {
            plugin.addTag(string);
        })
        return this;
    }

    // Get the Input String
    TagsInput.prototype.getInputString = function() {
        return this.arr.join(',');
    }


    // destroy the plugin
    TagsInput.prototype.destroy = function() {
        this.orignal_input.removeAttribute('hidden');

        delete this.orignal_input;
        var self = this;

        Object.keys(this).forEach(function(key) {
            if (self[key] instanceof HTMLElement)
                self[key].remove();

            if (key != 'options')
                delete self[key];
        });

        this.initialized = false;
    }

    // Private function to initialize the tag input plugin
    function init(tags) {
        tags.wrapper.append(tags.input);
        tags.wrapper.classList.add(tags.options.wrapperClass);
        tags.orignal_input.setAttribute('hidden', 'true');
        // tags.orignal_input.hide();
        tags.orignal_input.parentNode.insertBefore(tags.wrapper, tags.orignal_input);
    }

    // initialize the Events
    function initEvents(tags) {
        tags.wrapper.addEventListener('click', function() {
            tags.input.focus();
        });

        tags.input.addEventListener('click', function() {
            var div_elements = document.querySelectorAll('.suggestions-container');
            div_elements.forEach((div_elements) => {
                div_elements.classList.add('hide_sugg');
            });
        });

        tags.input.addEventListener('keyup', function(event) {
            var closestFormGroup = event.target.closest('.form-group');
            var suggestUniversityElement = "";
            var country_name = "";

            if (closestFormGroup) {
                suggestUniversityElement = closestFormGroup.querySelector('.suggest_university').id;
                country_name = closestFormGroup.querySelector('.suggest_university').getAttribute("data-country-name");
            }

            var str = event.target.value.trim();
            var suggestions = suggetions_university[country_name];

            if (suggestions && suggestions.length > 0) {
                var matchedSuggestions = suggetions_university[country_name].filter(function(suggestion) {
                    // return suggestion.toLowerCase().startsWith(str.toLowerCase());

                    return suggestion.toLowerCase().includes(str.toLowerCase());
                });

                var div_elements = document.querySelectorAll('.suggestions-container');
                div_elements.forEach((div_elements) => {
                    div_elements.classList.add('hide_sugg');
                });
                if (str.trim() != "") {
                    displaySuggestions(matchedSuggestions, suggestUniversityElement, event, tags);
                }
            }
        });
    }


    // Display the auto-suggestions
    function displaySuggestions(suggestions, id, event, tags) {

        var suggestionsContainer = document.getElementById('suggestions-container-' + id);

        if (!suggestionsContainer) {
            suggestionsContainer = document.createElement('ul');
            suggestionsContainer.id = 'suggestions-container-' + id;
            suggestionsContainer.classList.add('suggestions-container');
            suggestionsContainer.setAttribute('data-university', id);
            document.getElementById(id).after(suggestionsContainer);
        }
        suggestionsContainer.closest(".suggestions-container").classList.remove('hide_sugg');
        suggestionsContainer.innerHTML = '';

        suggestions.forEach(function(suggestion) {
            var suggestionItem = document.createElement('li');
            suggestionItem.innerText = suggestion;

            suggestionItem.addEventListener('click', function() {
                var selectedSuggestion = this.innerText;
                let u_id = $(this).closest(".suggestions-container").attr("data-university");
                //console.log(country_name);

                tags.addTag(selectedSuggestion);
                let country_name = $("#" + u_id).attr("data-country-name");
                country_name = country_name.search("_") != -1 ? country_name.replace("_", " ") : country_name;
                let selected_university = $("#" + u_id).val();
                event.target.value = '';
                selectedUniversityArr[country_name] = selected_university;
                universityArr[country_name] = selected_university;
                suggestionsContainer.closest(".suggestions-container").classList.add('hide_sugg');

            });

            suggestionsContainer.appendChild(suggestionItem);
        });

    }
    // Set All the Default Values
    TagsInput.defaults = {
        selector: '',
        wrapperClass: 'tags-input-wrapper',
        tagClass: 'tag',
        max: null,
        duplicate: false,
        suggestions: []
    }

    window.TagsInput = TagsInput;

    // var count = 0;
    function set_university_div() {
        return new Promise((resolve, reject) => {
            let set_count = 0;
            let str = "";

            for (const k in universityArr) {
                var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;

                str += `<div class="col-lg-6">
        <div class="form-group">
          <label for="university${set_count}">${countryName} University</label>
          <input type="hidden" class="form-control suggest_university" data-country-name="${countryName}" name="university${set_count}" id="university${set_count}" value="" required>
        </div>
      </div>`;

                set_count++;
            }

            if (str !== "") {
                resolve(str);
            } else {
                reject(new Error("Failed to generate university HTML."));
            }
        });
    }

    async function set_university() {
        try {
            const str = await set_university_div();
            $('.universities').html(str);

            let set_count = 0;
            let leadTypeSelect = document.getElementById("lead_type");
            let selectedValue = leadTypeSelect.options[leadTypeSelect.selectedIndex].text.trim().toLowerCase();

            for (const k in universityArr) {
                let university_list = await show_university_dropdown(selectedValue, k);
                //console.log(university_list);
                var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;
                if (universityArr.hasOwnProperty(k)) {
                    var tagInput1 = new TagsInput({
                        selector: `university${set_count}`,
                        duplicate: false,
                        max: 5,
                        suggestions: university_list
                    });

                    var defaultVal = universityArr[k].split(",");

                    if (defaultVal != "") {
                        defaultVal.forEach(function(k1, v1) {
                            tagInput1.addData([k1]);
                        });
                    }
                    set_count++;
                    suggetions_university[countryName] = university_list;
                }
            }

        } catch (error) {
            console.error("An error occurred during university setup:", error);
        }
    }
</script>