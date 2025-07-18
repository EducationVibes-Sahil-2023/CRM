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

    function change_passport_status(type = "") {
        // Clear passport input initially
        $("#passport_number").val("");

        // Get selected passport option's custom attributes
        let selectedOption = $("#passport option:selected");
        let status = selectedOption.attr("data-passport_number_status");
        let arn_status = selectedOption.attr("data-passport_arn_status");

        // Handle passport number input display
        if (status == 1) {
            $(".passport-div-status").removeClass("hide").show();
        } else {
            $(".passport-div-status").hide().addClass("hide");
            $(".passport-div-status input").val('');
        }
        console.log(type);
        console.log(arn_status);
        // Handle ARN input display only if type == 1
        if (type == 1) {
            if (arn_status == 1) {
                $(".passport-div-ARN").removeClass("hide").show();
            } else {
                $(".passport-div-ARN").hide().addClass("hide");
                $(".passport-div-ARN input").val('');
            }
        }
    }



    function isValidARN($arn_number) {
        return preg_match('/^[0-9]{15}$/', $arn_number);
    }


    // admission prefrencess 

    $(document).ready(function() {
        applicationIndex = $(".university-combinations").length

        // $("#session_intake").on("change", function() {
        //     let selectedDate = $(this).val(); // Get selected value (YYYY-MM)

        //     if (selectedDate) {
        //         let [year, month] = selectedDate.split("-"); // Extract year and month

        //         // if (month !== "02" && month !== "09") {
        //         //     // If not February or September, auto-correct to the nearest allowed month
        //         //     // let correctedMonth = (month < "06") ? "02" : "09"; // Before June → February, After → September
        //         //     $(this).val('');
        //         //     alert_float("danger", "Only February and September are allowed.");
        //         // }
        //     }
        // });


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

    $(document).on('shown.bs.select', 'select.study_courses', function() {
        const $select = $(this);
        setTimeout(() => {
            $('.bs-searchbox input').off('input').on('input', function() {
                let searchVal = $(this).val();
                loadCourses(searchVal, $select); // Pass correct select element
            });
        }, 300);
    });

    function save_admission_preferences() {
        var additional_fields = {};
        var form_status = true;
        // set_primary_enabled();
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
            // set_primary_diabled();
            appValidateForm($("#admission-preferences-form"), additional_fields);
            hide_loader();
            return false; // Prevent form submission if validation fails
        }

        // Collect form data
        const params = {
            program: $('#program').val(),
            course: "STUDY",
            sessionIntake: $('#session_intake').val(),
            acadmic_year: $('#acadmic_year').val(),
            admissionPreferencesId: $('#admissionpreferencesid').val(),
            client_id: $('#client_id').val(),
            application_universities: []
        };

        // Conditionally add properties if they have values

        try {
            // Validate selected universities for each country
            // const countriesArr = $('#study_country').val();
            // if (countriesArr) {
            //     $.each(countriesArr, function(index, value) {
            //         const key = value.replace(" ", "_");
            //         params.universities[key] = $(`#university${index}`).val();
            //         if (!params.universities[key]) {
            //             hide_loader();
            //             alert_float('danger', `Select a university for ${value}.`);
            //             throw new Error(`University selection for ${value} is required.`);
            //         }
            //     });
            // }

            let isValid = true;
            const seenCombinations = new Set();
            params.application_universities = []; // Ensure it's initialized

            $(".university-combinations").each(function() {
                const $combo = $(this);

                const countryId = $combo.find("select.study_country").val();
                const countryName = $combo.find("select.study_country option:selected").text().trim();

                const universityId = $combo.find("select.study_universities").val();
                const universityName = $combo.find("select.study_universities option:selected").text().trim();

                const courseId = $combo.find("select.study_courses").val();
                const courseName = $combo.find("select.study_courses option:selected").text().trim();

                const sessionIntake = $combo.find(".session_intake_combination").val().trim();
                const id = $combo.find(".shortlisting_id").val();

                if (!countryId) {
                    hide_loader();
                    alert_float('danger', "Please select a country in all university combinations.");
                    isValid = false;
                    return false; // Break `.each` loop
                }

                const comboKey = `${countryId}_${universityId}_${courseId}_${sessionIntake}`;
                if (seenCombinations.has(comboKey)) {
                    hide_loader();
                    alert_float('danger', "Duplicate university combination found. Please ensure each combination is unique.");
                    isValid = false;
                    return false;
                }

                seenCombinations.add(comboKey);

                params.application_universities.push({
                    id: id,
                    country_id: countryId,
                    countryName: countryName,
                    university_id: universityId,
                    university_name: universityName,
                    course_id: courseId,
                    course_name: courseName,
                    session_intake: sessionIntake,
                    is_primary: $combo.find(".is_primary").is(":checked") ? 1 : 0
                });
            });

            if (!isValid) {
                hide_loader();
                // Prevent form submission or further processing if needed
                return;
            }



            // Submit the form data via AJAX
            $.ajax({
                url: "<?php echo base_url() . 'admin/clients/update_admission_preferences' ?>",
                type: "POST",
                data: params,
                dataType: "JSON",
                success: function(res) {
                    hide_loader();
                    // set_primary_diabled();
                    if (res.resp_code != "ERR") {
                        if (res.resp_id !== undefined) {
                            $('#admissionpreferencesid').val(res.resp_id);
                        }
                        window_reload();
                        alert_float('success', res.resp_desc);
                    } else {
                        alert_float('danger', res.resp_desc);

                    }
                },
                error: function(err) {
                    // set_primary_diabled();
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
        const additional_fields = {};
        let form_status = true;
        const formData = new FormData();

        show_loader();

        try {
            // Step 1: Validate Required Fields
            $("#admission-details-form input:visible, #admission-details-form select:visible").each(function() {
                const $el = $(this);
                const name = $el.attr("name");
                const value = $.trim($el.val());
                const isRequired = $el.is("[required-check]");

                if (!name) return;

                if (isRequired && value === "") {
                    additional_fields[name] = "required";
                    form_status = false;
                }
            });

            if (!form_status) {
                appValidateForm($("#admission-details-form"), additional_fields);
                alert_float('danger', 'Please fill all required fields.');
                hide_loader();
                return;
            }

            // Step 2: Collect Form Data
            $("#admission-details-form div > input, #admission-details-form div > textarea, #admission-details-form div > select").each(function() {
                const $el = $(this);
                const name = $el.attr("name");
                const type = $el.attr("type");

                if (!name) return;

                if (type === "radio" && !$el.prop("checked")) return;

                if (type === "file") {
                    const file = $el[0].files[0];
                    if (file) formData.append(name, file);
                } else {
                    formData.append(name, $el.val());
                }
            });

            // Step 3: Static Hidden Values
            formData.append("csrf_token_name", csrfData.hash);
            formData.append("clientid", $('input[name="clientid"]').val());
            formData.append("academicDetailsId", $('input[name="academicDetailsId"]').val());
            formData.append("elt_status", $('input[name="elt_status"]').is(':checked') ? 1 : 0);

            // Step 4: Extra Async File Data
            await get_media_docs("admission-details-form .media-files", formData);
            await get_entranceExams(formData);

            // Step 5: AJAX Submission
            $.ajax({
                url: "<?php echo base_url('admin/clients/student_acadmic'); ?>",
                type: "POST",
                data: formData,
                dataType: "JSON",
                processData: false,
                contentType: false,
                success: function(res) {
                    hide_loader();
                    if (res.resp_code === 'RCS') {
                        alert_float('success', res.resp_desc);
                        window_reload();
                    } else {
                        alert_float('danger', res.resp_desc || 'Something went wrong.');
                    }
                },
                error: function() {
                    hide_loader();
                    alert_float('danger', 'A network error occurred while saving admission details.');
                }
            });
        } catch (e) {
            console.error("Error in save_admission_details:", e);
            alert_float('danger', 'Unexpected error occurred. Please try again.');
            hide_loader();
        }
    }



    function get_entranceExams(formData) {
        const entranceExamDetails = [];

        return new Promise((resolve) => {
            $("#entrance-exam-div .entrance-exams").each(function() {
                const fileInput = $(this).find("input[type='file']")[0];
                const fileUrl = $(fileInput).data("fileurl") || null;
                const files = fileInput?.files || [];

                const id = $(this).find("input.entrance_id").val()?.trim();
                const examSelect = $(this).find("select");
                const exam_id = examSelect.val()?.trim();
                const entranceExam = examSelect.find("option:selected").text()?.trim();
                const marks = $(this).find("input.entrance_marks").val()?.trim();

                entranceExamDetails.push({
                    id: id || null,
                    exam_id: exam_id || null,
                    entranceExam: entranceExam || null,
                    marks: marks || null,
                    hasFile: files.length > 0,
                    fileUrl: fileUrl
                });

                if (files.length > 0) {
                    Array.from(files).forEach((file, index) => {
                        formData.append(`files_entrance_${exam_id}`, file);
                    });
                }
            });

            formData.append("entrance_exam_details", JSON.stringify(entranceExamDetails));
            resolve();
        });
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

        $('input[type=radio][name=after_x_status]').change(function() {
            let selected_value = $(this).val();
            $('#twelthAcademicDetails, #diplomaAcademicDetails').removeClass("show").addClass("hide");
            if (selected_value == 'Both') {
                $('#twelthAcademicDetails, #diplomaAcademicDetails').removeClass("hide").addClass("show");
            } else if (selected_value == '12th') {
                // console.log("12 select");
                $('#twelthAcademicDetails').removeClass("hide").addClass("show");
            } else if (selected_value == 'Diploma') {
                $('#diplomaAcademicDetails').removeClass("hide").addClass("show");
            }
        });

        $('input[type=radio][name=after_xx_status]').change(function() {
            let selected_value = $(this).val(); // Use 'this' to get the value of the selected radio input.
            // console.log(selected_value);
            // Hide both academic details by default.
            $('#graduationAcademicDetails, #post_graduationAcademicDetails').removeClass("show").addClass("hide");

            if (selected_value == 'Both') {
                $('#graduationAcademicDetails, #post_graduationAcademicDetails').removeClass("hide").addClass("show");
            } else if (selected_value == 'Graduation') {
                // console.log("12 select");
                $('#graduationAcademicDetails').removeClass("hide").addClass("show");
            } else if (selected_value == 'Post Graduation') {
                $('#post_graduationAcademicDetails').removeClass("hide").addClass("show");
            }
        });

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

        $("#diploma_result_status").on('change', function() {
            const drs = $(this).val();
            const $detailsSection = $("#diplomaAcademicDetails .result-change-hide");

            // Clear input, select, and file values within the section
            $detailsSection.find("input, select").val('');
            $detailsSection.find("input[type='file']").val(null); // Proper way to clear file input

            // Toggle visibility based on status
            if (drs === 'Declared') {
                $detailsSection.show();
            } else {
                $detailsSection.hide();
            }
        });

        $("#graduation_result_status").on('change', function() {
            const drs = $(this).val();
            const $detailsSection = $("#graduationAcademicDetails .result-change-hide");

            // Clear input, select, and file values within the section
            $detailsSection.find("input, select").val('');
            $detailsSection.find("input[type='file']").val(null); // Proper way to clear file input

            // Toggle visibility based on status
            if (drs === 'Declared') {
                $detailsSection.show();
            } else {
                $detailsSection.hide();
            }
        });

        $("#post_graduation_result_status").on('change', function() {
            const drs = $(this).val();
            const $detailsSection = $("#post_graduationAcademicDetails .result-change-hide");

            // Clear input, select, and file values within the section
            $detailsSection.find("input, select").val('');
            $detailsSection.find("input[type='file']").val(null); // Proper way to clear file input

            // Toggle visibility based on status
            if (drs === 'Declared') {
                $detailsSection.show();
            } else {
                $detailsSection.hide();
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
            // ✅ Call optional functions if they exist
            if (typeof set_primary_diabled === "function") {
                set_primary_diabled();
            }

            if (typeof set_university_diabled === "function") {
                set_university_diabled();
            }

            // ✅ When University dropdown is opened
            $(document).on('shown.bs.select', 'select.study_universities', async function() {
                const $container = $(this).closest(".university-combinations");
                const $countrySelect = $container.find("select.study_country");
                const $universitySelect = $container.find("select.study_universities");
                const $courseSelect = $container.find("select.study_courses");

                const selectedCountryId = $countrySelect.val();
                const selectedUniversity = $universitySelect.val();
                const selectedCourse = $courseSelect.val();

                // 🔁 Load universities based on selected country
                await handleCountryChange($countrySelect.get(0), $universitySelect);

                // 🔁 Restore selected university
                if (selectedUniversity) {
                    $universitySelect.val(selectedUniversity).selectpicker('refresh').trigger("change");
                }
            });

            // ✅ When Course dropdown value is changed
            $(document).on('shown.bs.select', 'select.study_courses', async function() {
                const $container = $(this).closest(".university-combinations");
                const $courseSelect = $(this);
                const selectedCourse = $courseSelect.val();
                const selectedText = $courseSelect.find("option:selected").text().trim();
                const searchTerm = selectedText.split(" ")[0] || '';

                console.log("Loading courses for:", searchTerm);
                await loadCourses(searchTerm, $courseSelect, selectedCourse);
            });

        }, 200);





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
            console.log(additional_fields);
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
        let registrationAmount = $('#registrationAmount').val();
        let currency_id = $('#registrationAmount').data("currency_id");
        let fees_id = $('#registrationAmount').data("id");
        let client_id = $('input[name="clientid"]').val();

        let feesDetails = {
            amount: registrationAmount,
            currency_id: currency_id,
            fees_id: fees_id,
            client_id: client_id
        };

        // Convert the object to JSON string before appending
        formData.append("feesDetails", JSON.stringify(feesDetails));




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
            $(".fa-fa-icons").show();

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
            $(".fa-fa-icons").hide();

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
                $("#" + formId + " input:visible, #" + formId + " select:visible, #" + formId + " input[type='date']:visible").attr("disabled", true);

                appValidateForm("#" + formId, additional_fields);

                return false;
            }
            $("#" + formId + " input:visible, #" + formId + " select:visible, #" + formId + " input[type='date']:visible").attr("disabled", true);

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


    var suggetions_university = [];
    const countriesArr = <?php echo !empty($admissionpreferences) ? json_encode(explode(",", $admissionpreferences->study_country)) : '[]' ?>;
    const universityArr = <?php echo !empty($admissionpreferences->university) ? $admissionpreferences->university : '{}' ?>;

    var selectedUniversityArr = [];

    // $(document).on('change', '.study_country', async function() {
    //     let $this = $(this);
    //     let selectedValue = $this.val();
    //     let selectedTextValue = $this.text();
    //     console.log(selectedValue);
    //     console.log(selectedTextValue);
    //     // Find the corresponding university select inside the same wrapper
    //     let $universitySelect = $this.closest('.study_universities');

    //     $universitySelect.empty();

    //     let countryName = selectedValue.replace(/_/g, ' ');
    //     let universityList = await show_university_dropdown(selectedValue);
    //     console.log(universityList);
    //     // Create options
    //     let universityOptions = '<option value="">Select university</option>';
    //     universityList.forEach(function(uni) {
    //         $universitySelect.append(new Option(uni.university_name, uni.university_id));

    //     });

    //     // Inject and refresh the university select
    //     $universitySelect.selectpicker('refresh');
    // });



    function delete_documents_study(type, tracker_id, id) {
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
                url: "<?php echo base_url() . 'admin/clients/delete_documents_study' ?>",
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

    $(document).ready(function() {
        // Name: only letters and spaces
        $(".name-validation-check").on("keyup", function() {
            const name = $(this).val().trim();
            if (!/^[A-Za-z\s]*$/.test(name)) {
                // Remove any non-letter characters
                $(this).val(name.replace(/[^A-Za-z\s]/g, ""));
            }
        });

        // Email: prevent invalid characters (basic restriction)
        $(".email-validation-check").on("keyup", function() {
            let email = $(this).val();
            // Allow only characters typically used in emails
            email = email.replace(/[^\w@.\-_+]/g, "");
            $(this).val(email);
        });

        // Phone: allow only digits, max 10 digits
        $(".phone-validation-check").on("keyup", function() {
            let phone = $(this).val().replace(/\D/g, "");
            if (phone.length > 10) {
                phone = phone.slice(0, 10);
            }
            $(this).val(phone);
        });
    });
</script>