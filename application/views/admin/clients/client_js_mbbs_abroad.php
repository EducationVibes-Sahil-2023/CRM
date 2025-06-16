<script>
    // comman functions

    var complete_application = <?= !empty($client->sc_100) && $client->sc_100 == 1 ? 1 : 0 ?>;
    var client_type = <?= !empty($client->client_type)  ? $client->client_type : 0 ?>;

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
    async function save_basic_details(status = 0) {

        show_loader();
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
            hide_loader();
            return false;
        }

        let phonenumber = $("input[name='mobile']").val();
        let p_phonenumber = $("input[name='fathers_mobile']").val();
        phonenumber = formatPhoneNumber(phonenumber);
        p_phonenumber = formatPhoneNumber(p_phonenumber);

        if (status == 0) {
            // Assuming phonenumber and p_phonenumber are already defined and cleaned
            if (phonenumber === p_phonenumber && phonenumber !== "") {
                alert_float("danger", "Student contact number and your parent's contact number cannot be the same.");
                hide_loader();
                return false;
            }

            if (phonenumber.length !== 10) {
                alert_float("danger", "Student contact number must be exactly 10 digits.");
                hide_loader();
                return false;
            }

            if (p_phonenumber.length !== 10) {
                alert_float("danger", "Parent's contact number must be exactly 10 digits.");
                hide_loader();
                return false;
            }

        } else {

        }

        let formData = new FormData($("#basic-information-form")[0]); // Create FormData from form

        // Wait for media files to be processed before proceeding
        await get_media_docs("basic-information-form .media-files", formData);

        formData.append("csrf_token_name", csrfData.hash);
        formData.append("clientid", $('input[name="clientid"]').val());

        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/student_update' ?>",
            type: "POST",
            data: formData,
            contentType: false, // Important for FormData
            processData: false, // Prevent jQuery from processing data
            dataType: "JSON",
            success: function(res) {
                hide_loader();
                console.log(res);
                if (res.resp_code === "RCS") {
                    let url = new URL(window.location.href);
                    let segments = url.pathname.split('/');
                    let baseUrl = `${url.origin}/${segments.slice(1, 5).join('/')}`;
                    window_reload(`${baseUrl}/${res.client_id}`);

                    // alert_float("success", res.resp_desc);
                } else if (res.resp_code && res.resp_desc) {
                    alert_float("danger", res.resp_desc);
                }
            },
            error: function(xhr, status, error) {
                hide_loader();
                // console.error("AJAX Error:", error);
            }
        });
    }

    // passport js
    async function save_passport_details() {

        var additional_fields = {};
        var form_status = true;
        show_loader();
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
            hide_loader();
            return false;
        }

        let formData = new FormData($("#passport-form")[0]); // Create FormData from form

        // Wait for media files to be processed before proceeding
        await get_media_docs("passport-form .media-files", formData);

        formData.append("csrf_token_name", csrfData.hash);
        formData.append("clientid", $('input[name="clientid"]').val());

        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/passport_info' ?>",
            type: "POST",
            data: formData,
            contentType: false, // Important for FormData
            processData: false, // Prevent jQuery from processing data
            dataType: "JSON",
            success: function(res) {
                hide_loader();
                if (res.resp_code === "RCS") {
                    window_reload();
                    alert_float("success", res.resp_desc);
                } else if (res.resp_code && res.resp_desc) {
                    alert_float("danger", res.resp_desc);
                }
            },
            error: function(xhr, status, error) {
                hide_loader();
                // console.error("AJAX Error:", error);
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
        $("#session_intake").on("change", function() {
            let selectedDate = $(this).val(); // Get selected value (YYYY-MM)

            if (selectedDate) {
                let [year, month] = selectedDate.split("-"); // Extract year and month

                if (month !== "02" && month !== "09") {
                    // If not February or September, auto-correct to the nearest allowed month
                    // let correctedMonth = (month < "06") ? "02" : "09"; // Before June → February, After → September
                    $(this).val('');
                    alert_float("danger", "Only February and September are allowed.");
                }
            }
        });


        if (client_type != 1) {
            $(".hide-client-type").parent("button.btn").hide();
        }


        // Pre-fill with the nearest allowed month on page load
        let today = new Date();
        let currentYear = today.getFullYear();
        let currentMonth = today.getMonth() + 1; // JavaScript months are 0-based
        let defaultMonth = (currentMonth < 6) ? "02" : "09"; // Before June → February, After → September
        // $("#session_intake").val(`${currentYear}-${defaultMonth}`);

        $('#study_country').trigger('change');

        if (typeof final_sumbit !== "undefined" && final_sumbit == 1) {
            setTimeout(() => {
                $(".tab-pane").each(function() {
                    if (!$(this).hasClass("disabled-form")) {
                        $(this).find(".form-disabled").each(function() {
                            $(this).find("input, select, .dropdown-toggle").attr("disabled", true);
                        });
                        $(this).find(".btn-save-fun").hide();
                    }
                });


                $("#save_admission_preferences").attr("disabled", true);

                // Directly applying instead of using another setTimeout
                $(".tags-input-wrapper").css("pointer-events", "none");
            }, 100);

            if (complete_application == 1) {
                $(".tab-pane form").find("input, select, textarea,button").prop("disabled", true).selectpicker("refresh");
            }
        }

        setTimeout(function() {
            $(window).off('beforeunload');
        }, 100);


    });

    function save_admission_preferences() {
        var additional_fields = {};
        var form_status = true;
        set_primary_enabled();
        show_loader();
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

        // console.log(form_status);
        if (!form_status) {
            set_primary_diabled();
            appValidateForm($("#admission-preferences-form"), additional_fields);
            hide_loader();
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

        // Conditionally add properties if they have values
        const primaryUniversity = $('#primary_university').val();
        if (primaryUniversity) {
            params.primary_university = primaryUniversity;
        }

        const primaryCountry = $('#primary_country').val();
        if (primaryCountry) {
            params.primary_country = primaryCountry;
        }

        try {
            // Validate selected universities for each country
            const countriesArr = $('#study_country').val();
            if (countriesArr) {
                $.each(countriesArr, function(index, value) {
                    const key = value.replace(" ", "_");
                    params.universities[key] = $(`#university${index}`).val();
                    if (!params.universities[key]) {
                        hide_loader();
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
                    hide_loader();
                    set_primary_diabled();
                    if (res.resp_id !== undefined) {
                        $('#admissionpreferencesid').val(res.resp_id);
                    }
                    window_reload();
                    alert_float('success', res.resp_desc);
                },
                error: function(err) {
                    set_primary_diabled();
                    hide_loader();
                    alert_float('danger', "An error occurred during submission.");
                    // console.error(err);
                }
            });
        } catch (error) {
            // console.error(error.message);
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
    if (typeof admissionpreferences_freeze !== 'undefined' && admissionpreferences_freeze === 1) {
        set_frezee();
    }





    // acadmic details

    async function save_admission_details() {

        var additional_fields = {};
        var form_status = true;
        var formData = new FormData(); // Initialize FormData

        show_loader();

        // Iterate through inputs, selects, and date fields
        $("#admission-details-form input:visible, #admission-details-form select:visible").each(function() {
            const value = $.trim($(this).val()); // Trim spaces
            const isRequired = $(this).attr("required-check") !== undefined;
            const name = $(this).attr("name");
            // console.log(name);

            if (isRequired) {
                additional_fields[name] = "required";
            }

            // Validate required fields
            if (isRequired && value === "") {
                form_status = false;
            } else {}
        });

        // console.log(additional_fields);

        if (!form_status) {
            appValidateForm($("#admission-details-form"), additional_fields);
            hide_loader();
            return false;
        }


        // Continue with form submission if valid
        // Example: formData.append("key", value);
        // Submit via AJAX or any other method


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
        formData.append("csrf_token_name", csrfData.hash);
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
                hide_loader();
                if (res.resp_code == 'RCS') {
                    alert_float('success', res.resp_desc);
                    window_reload();
                } else {
                    if (res.resp_code != '' && res.resp_desc != '') {
                        alert_float('danger', res.resp_desc);
                    }
                }
            }
        })
    }

    function check_registration_cash_status(element, className) {
        if ($(element).is(":checked")) {
            $("." + className).hide();
            $(element).val(1); // Show elements if checkbox is checked
        } else {
            $("." + className).show(); // Hide elements if checkbox is unchecked
            $(element).val(0);
        }
    }

    $(document).ready(function() {
        $("#twelth_result_status").on("change", function() {
            let isDeclared = $(this).val() === "Declared";
            let targetDiv = $(".twelth_result_status_div");

            targetDiv.toggle(isDeclared); // Show/Hide the div

            // Clear values of input, select, and file fields
            targetDiv.find("input:not([type='hidden']), select, input[type='file']").val("");

            // Refresh select fields using selectpicker (Bootstrap Select)
            targetDiv.find("select").selectpicker("refresh");

            // Manage required-check attribute
            if (isDeclared) {
                targetDiv.find("input, select, input[type='file']").attr("required-check", "required-check");
            } else {
                targetDiv.find("input, select, input[type='file']").removeAttr("required-check");
            }
        });


        $("#entrance_result_status").on("change", function() {
            let ers = $(this).val();
            let isAwaitedOrNotAppeared = ers === "Awaited" || ers === "Not Appeared" || ers === "" || ers === "Fail";

            // Toggle visibility of the elements with class "hide_"
            $(".hide_").toggle(!isAwaitedOrNotAppeared); // Hide when either "Awaited" or "Not Appeared" is selected

            $("input[name='entrance_roll'], input[name='entrance_percentage'], input.multiple_score, #entrance_exam_div input[type='file'],select[name='neet_status'],select[name='entrance_year']")
                .val(!isAwaitedOrNotAppeared ? "" : null)
                .attr("required-check", !isAwaitedOrNotAppeared ? "required-check" : "");
            $("#entrance_exam_div select[name='entrance_year']").val('').selectpicker("refresh");
            $("select[name='neet_status']").selectpicker('refresh');
            // Toggle multiple score inputs and labels
            $("input.multiple_score, .multiple_score_label").toggle(isAwaitedOrNotAppeared); // Show when either "Awaited" or "Not Appeared" is selected

        });

        setTimeout(() => {
            if (typeof set_primary_diabled === "function") {
                set_primary_diabled();
            } else {
                // console.warn("set_primary_diabled function does not exist.");
            }

            if (typeof set_university_diabled === "function") {
                set_university_diabled();
            } else {
                // console.warn("set_university_diabled function does not exist.");
            }
        }, 100);


    });


    // Save Documents
    function save_documents() {
        var additional_fields = {};
        var form_status = true;
        show_loader();
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
            hide_loader();
            return false; // Prevent form submission
        }

        // Initialize FormData object
        let formData = new FormData();
        let file_upload_status = false;
        // Append file inputs to FormData
        $("#documents-form input[type='file']").each(function() {
            let name = $(this).attr("name"); // Get the name of the file input
            let files = $(this)[0].files; // Get the file list
            let row = $(this).closest("tr"); // Closest row for associated fields
            let doc_type = row.find("input[name='doc_type[]']").val(); // Get doc_type from the row
            let doc_name = row.find("input[name='doc_name[]']").val(); // Get doc_name from the row
            let doc_url = row.find("input[name='doc_url[]']").val(); // Get doc_url from the row


            if (files.length > 0) {
                file_upload_status = true;
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
        formData.append("csrf_token_name", csrfData.hash);
        formData.append("clientid", $('input[name="clientid"]').val());

        if (!file_upload_status) {
            // alert_float("danger", "No media found to upload.");
            // hide_loader();
            // return false;
        }
        // AJAX request to upload documents
        $.ajax({
            url: "<?php echo base_url('admin/clients/upload_documents'); ?>",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from transforming FormData
            contentType: false, // Ensure correct Content-Type is set for FormData
            dataType: "JSON",
            success: function(res) {
                hide_loader();
                if (res.resp_code === "RCS") {
                    window_reload();
                    alert_float("success", res.resp_desc);
                } else {
                    const message = res.resp_desc || "An unknown error occurred.";
                    alert_float("danger", message);
                }
            },
            error: function(xhr, status, error) {
                // console.error("Error: ", error);
                hide_loader();
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }

    function fees_details() {
        var additional_fields = {};
        var form_status = true;
        show_loader();
        $("#fees-details-form input:visible, #fees-details-form select:visible, #fees-details-form input[type='date']:visible").each(function() {
            const value = $(this).val()?.trim(); // Get trimmed value
            const isRequired = $(this).attr("required-check") !== undefined; // Check if 'required-check' exists
            const name = $(this).attr("name"); // Get name attribute
            // console.log(isRequired);
            if (isRequired && name) {
                additional_fields[name] = "required";
                if (!value) {
                    form_status = false;
                }
            }
        });

        if (!form_status) {
            appValidateForm($("#fees-details-form"), additional_fields);
            hide_loader();
            return false;
        }

        let formData = new FormData(document.getElementById('fees-details-form')); // Correct way to initialize FormData

        // Append CSRF token and client ID
        formData.append("csrf_token_name", csrfData.hash);
        formData.append("clientid", $('input[name="clientid"]').val());
        formData.append("air_ticket_include", $('input[name="air_ticket_include"]').is(':checked') ? 1 : 0);
        $("#air_ticket_include").attr("disabled", false);


        // Function to process media files


        //

        // AJAX request to upload documents
        $.ajax({
            url: "<?php echo base_url('admin/clients/fees_details'); ?>",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from transforming FormData
            contentType: false, // Ensure correct Content-Type is set for FormData
            dataType: "JSON",
            success: function(res) {
                hide_loader();
                if (res.resp_code === "RCS") {
                    window_reload();
                    alert_float("success", res.resp_desc);
                } else {
                    const message = res.resp_desc || "An unknown error occurred.";
                    alert_float("danger", message);
                }
            },
            error: function(xhr, status, error) {
                // console.error("Error: ", error);
                hide_loader();
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }

    async function save_welcome_info() {
        var additional_fields = {};
        var form_status = true;
        show_loader();
        $(".disabled-form-welcome").removeAttr("disabled");
        $("#welcome-information-form input:visible, #welcome-information-form select:visible, #welcome-information-form input[type='date']:visible").each(function() {
            const value = $(this).val()?.trim(); // Get trimmed value
            const isRequired = $(this).attr("required-check") !== undefined; // Check if 'required-check' exists
            const name = $(this).attr("name"); // Get name attribute
            // console.log(isRequired);
            if (isRequired && name) {
                additional_fields[name] = "required";
                if (!value) {
                    form_status = false;
                }
            }
        });

        let formData = new FormData(document.getElementById('welcome-information-form')); // Correct way to initialize FormData
        if (!form_status) {
            if (typeof final_sumbit !== "undefined" && final_sumbit == 1) {
                $(".disabled-form-welcome").attr("disabled");
            }
            appValidateForm($("#welcome-information-form"), additional_fields);
            hide_loader();
            return false;
        }
        if (typeof final_sumbit !== "undefined" && final_sumbit == 1) {
            $(".disabled-form-welcome").attr("disabled");
        }




        // Append CSRF token and client ID
        formData.append("csrf_token_name", csrfData.hash);
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
                hide_loader();
                if (res.resp_code === "RCS") {
                    window_reload();
                    alert_float("success", res.resp_desc);
                } else {
                    const message = res.resp_desc || "An unknown error occurred.";
                    alert_float("danger", message);
                }
            },
            error: function(xhr, status, error) {
                // console.error("Error: ", error);
                hide_loader();
                alert_float("danger", "An error occurred while processing the request.");
            },
        });
    }

    $(".nav-tabs li").on("click", function() {
        if (typeof final_sumbit !== "undefined" && final_sumbit != 1) {
            $(".btn-save-fun").show(); // Show the save button
            $(".tab-pane").find("input,select").attr("readonly", false);
            $(".tab-pane").find("input[type='file']").attr("disabled", false);
            $(".tab-pane").find("input[type='checkbox']").attr("disabled", false);
            $(".tab-pane").find("select").attr("disabled", false);
            $(".tab-pane").find("textarea").attr("disabled", false);
            $("select").selectpicker('refresh');
            $(".tags-input-wrapper").css("pointer-events", "");
            $("#save_admission_preferences").attr("disabled", false);
            $(".btn-save-funn").show();
        }

        if (typeof final_sumbit !== "undefined" && final_sumbit == 1) {}

        if (complete_application == 1) {
            $(".tab-pane form").find("input, select, textarea,button").prop("disabled", true).selectpicker("refresh");
        }
    });


    function show_all_data() {
        setTimeout(() => {
            $(".tab-pane").addClass("active");
            $(".tab-pane").find("input,select").attr("readonly", true);
            $(".tab-pane").find("input[type='file']").attr("disabled", true);
            $(".tab-pane").find("select").attr("disabled", true);
            $(".tab-pane").find("textarea").attr("disabled", true);
            $(".tab-pane").find("input[type='checkbox']").attr("disabled", true);
            $("select").selectpicker('refresh');
            $(".btn-save-fun").hide();
            $(".btn-save-funn").hide();
            $(".tags-input-wrapper").css("pointer-events", "none");
            $("#save_admission_preferences").attr("disabled", true);
        }, 0);

    }


    // final subbition
    function final_submission() {

        var additional_fields = {};
        var form_status = true;
        $("form").each(function() {
            let form = $(this); // Cache the form element
            let formId = form.attr("id");
            $("#" + formId + " input:visible, #" + formId + " select:visible, #" + formId + " input[type='date']:visible").attr("disabled", false);
            $("#" + formId + " input:visible, #" + formId + " select:visible, #" + formId + " input[type='date']:visible").each(function() {
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


        if (confirm("Are you sure you want to proceed? This action will take you to the Review and Final Submission.")) {
            show_loader();
            let formData = new FormData(); // Correct way to initialize FormData

            // Append CSRF token and client ID
            formData.append("csrf_token_name", csrfData.hash);
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
                    hide_loader();
                    if (res.resp_code === "RCS") {
                        window_reload();
                        alert_float("success", res.resp_desc);
                    } else {
                        const message = res.resp_desc || "An unknown error occurred.";
                        alert_float("danger", message);
                    }
                },
                error: function(xhr, status, error) {
                    hide_loader();
                    // console.error("Error: ", error);
                    alert_float("danger", "An error occurred while processing the request.");
                },
            });
        } else {
            hide_loader();

        }





    }


    function window_reload(url = "") {
        // Get the current active tab ID (e.g., "passport", "documents")
        const activeTabTarget = $("ul.profile-tabs li.active a").attr("href")?.replace("#", "") || "";

        // Use current URL if none is provided
        url = url ? new URL(url, window.location.origin) : new URL(window.location.href);

        // Set 'tab' parameter to the active tab
        if (activeTabTarget) {
            url.searchParams.set("tab", activeTabTarget);
        }

        // Optional: Set the next tab as 'tab' if desired
        const nextTabHref = $("ul.profile-tabs li.active").next("li").find("a").attr("href");
        if (nextTabHref) {
            const nextTabId = nextTabHref.replace("#", "");
            // Uncomment below line if you want to overwrite 'tab' with next tab
            url.searchParams.set("tab", nextTabId);
        }

        // Redirect to the updated URL
        window.location.href = url.href;
    }



    function check_primary_university() {


        let universitiesArray = {};
        let countriesArrr = $('#study_country').val();
        let primary_university = "<?= $admissionpreferences->primary_university ?>"; // Get selected value
        $("#primary_country").val("");
        if (countriesArrr) {
            $.each(countriesArrr, function(index, value) {
                const key = value.replace(/\s+/g, "_"); // Replace spaces with underscores
                universitiesArray[key] = $(`#university${index}`).val();

            });
        }


        var $select = $("#primary_university");

        // Clear existing options except the first one
        $select.find("option:not(:first)").remove();

        // Loop through each country and add universities
        $.each(universitiesArray, function(country, universities) {
            var universityList = universities.split(",");

            // Append new university options
            $.each(universityList, function(index, university) {
                let trimmedUniversity = university.trim();
                let isSelected = primary_university === trimmedUniversity ? 'selected' : '';
                if (isSelected === 'selected') {
                    $("#primary_country").val(country);
                }
                $select.append(
                    $("<option></option>")
                    .val(trimmedUniversity)
                    .text(trimmedUniversity).attr("data-country", country)
                    .prop("selected", isSelected === 'selected') // Set selected option
                );
            });
        });

        // Refresh selectpicker UI if used
        if ($select.hasClass("selectpicker")) {
            $select.selectpicker("refresh");
        }
    }

    function select_primary_university(obj) {
        var country_name = $(obj).find("option:selected").data("country") || ""; // Default to empty if undefined
        $("#primary_country").val(country_name);
        // Debugging
        // console.log("Selected Country:", country_name);
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
                    // // console.log(selectedUniversityArr);
                    var count2 = 0;

                    for (const k in selectedUniversityArr) {
                        const v = selectedUniversityArr[k];
                        var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;
                        // // console.log(countryName);

                        let university_list = await show_university_dropdown(select_segment_default, k);
                        //// console.log(university_list);
                        //// console.log(university_list);
                        var tagInput1 = new TagsInput({
                            selector: `university${count2}`,
                            duplicate: false,
                            max: <?= MAX_UNIVERSITY_MBBS_ABROAD ?>,
                            suggestions: university_list
                        });

                        if (v !== '') {
                            //// console.log(v);
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
                        //// console.log(value);
                        let university_list = await show_university_dropdown(select_segment_default, v);
                        //// console.log(university_list);
                        var countryName = v.search("_") != -1 ? v.replace("_", " ") : v;

                        //// console.log(university_list);
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
        //// console.log(this)
        if (!(this.orignal_input = document.getElementById(this.options.selector))) {
            // console.error("tags-input couldn't find an element with the specified ID");
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
        check_primary_university();
        return this;
    }

    // Delete Tags
    TagsInput.prototype.deleteTag = function(tag, i) {
        tag.remove();
        this.arr.splice(i, 1);
        this.orignal_input.value = this.arr.join(',');
        check_primary_university();
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
                //// console.log(country_name);

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
                //// console.log(university_list);
                var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;
                if (universityArr.hasOwnProperty(k)) {
                    var tagInput1 = new TagsInput({
                        selector: `university${set_count}`,
                        duplicate: false,
                        max: <?= MAX_UNIVERSITY_MBBS_ABROAD ?>,
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
            // console.error("An error occurred during university setup:", error);
        }
    }

    function delete_documents(type, tracker_id, id) {
        // Basic field validation before confirmation
        if (!client_id || !tracker_id || !type || !id) {
            alert_float("danger", "Missing required information. Please refresh the page and try again.");
            return;
        }

        if (confirm("Are you sure you want to delete this document?")) {
            show_loader();

            let formData = new FormData();
            formData.append("csrf_token_name", csrfData.hash);
            formData.append("clientid", client_id);
            formData.append("type", type);
            formData.append("tracker_id", tracker_id);
            formData.append("id", id);

            $.ajax({
                url: "<?php echo base_url() . 'admin/clients/delete_documents' ?>",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "JSON",
                success: function(res) {
                    hide_loader();
                    if (res.resp_code === "RCS") {
                        window_reload(); // Refresh the page or section
                    } else if (res.resp_code && res.resp_desc) {
                        alert_float("danger", res.resp_desc);
                    } else {
                        alert_float("danger", "Unexpected server response. Please try again.");
                    }
                },
                error: function(xhr, status, error) {
                    hide_loader();
                    alert_float("danger", "AJAX request failed. Please check your network and try again.");
                }
            });
        }
    }
</script>