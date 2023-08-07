<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Included in application/views/admin/clients/client.php
 */
?>
<script>
    Dropzone.options.clientAttachmentsUpload = false;
    var customer_id = $('input[name="userid"]').val();
    $(function() {

        if ($('#client-attachments-upload').length > 0) {
            new Dropzone('#client-attachments-upload', appCreateDropzoneOptions({
                paramName: "file",
                accept: function(file, done) {
                    done();
                },
                success: function(file, response) {
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        window.location.reload();
                    }
                }
            }));
        }

        // Save button not hidden if passed from url ?tab= we need to re-click again
        if (tab_active) {
            $('body').find('.nav-tabs [href="#' + tab_active + '"]').click();
        }

        $('a[href="#customer_admins"]').on('click', function() {
            $('.btn-bottom-toolbar').addClass('hide');
        });

        $('.profile-tabs a').not('a[href="#customer_admins"]').on('click', function() {
            $('.btn-bottom-toolbar').removeClass('hide');
        });

        $("input[name='tasks_related_to[]']").on('change', function() {
            var tasks_related_values = []
            $('#tasks_related_filter :checkbox:checked').each(function(i) {
                tasks_related_values[i] = $(this).val();
            });
            $('input[name="tasks_related_to"]').val(tasks_related_values.join());
            $('.table-rel-tasks').DataTable().ajax.reload();
        });

        var contact_id = get_url_param('contactid');
        if (contact_id) {
            contact(customer_id, contact_id);
        }

        // consents=CONTACT_ID
        var consents = get_url_param('consents');
        if (consents) {
            view_contact_consent(consents);
        }

        // If user clicked save and add new contact
        if (get_url_param('new_contact')) {
            contact(customer_id);
        }

        $('body').on('change', '.onoffswitch input.customer_file', function(event, state) {
            var invoker = $(this);
            var checked_visibility = invoker.prop('checked');
            var share_file_modal = $('#customer_file_share_file_with');
            setTimeout(function() {
                $('input[name="file_id"]').val(invoker.attr('data-id'));
                if (checked_visibility && share_file_modal.attr('data-total-contacts') > 1) {
                    share_file_modal.modal('show');
                } else {
                    do_share_file_contacts();
                }
            }, 200);
        });

        $('.customer-form-submiter').on('click', function() {
            var form = $('.client-form');
            //console.log(form.valid())
            if (form.valid()) {
                //console.log('working')
                if ($(this).hasClass('save-and-add-contact')) {
                    form.find('.additional').html(hidden_input('save_and_add_contact', 'true'));
                } else {
                    form.find('.additional').html('');
                }
                form.submit();
            }
        });

        if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
            document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    saveCustomerProfileExternalFile(files, 'dropbox');
                },
                linkType: "preview",
                extensions: app.options.allowed_files.split(','),
            }));
        }

        /* Customer profile tickets table */
        $('.table-tickets-single').find('#th-submitter').removeClass('toggleable');

        initDataTable('.table-tickets-single', admin_url + 'tickets/index/false/' + customer_id, undefined, undefined, 'undefined', [$('table thead .ticket_created_column').index(), 'desc']);

        /* Customer profile contracts table */
        initDataTable('.table-contracts-single-client', admin_url + 'contracts/table/' + customer_id, undefined, undefined, 'undefined', [6, 'desc']);

        /* Custome profile contacts table */
        var contactsNotSortable = [];
        <?php if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') { ?>
            contactsNotSortable.push($('#th-consent').index());
        <?php } ?>
        _table_api = initDataTable('.table-contacts', admin_url + 'clients/contacts/' + customer_id, contactsNotSortable, contactsNotSortable);
        if (_table_api) {
            <?php if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') { ?>
                _table_api.on('draw', function() {
                    var tableData = $('.table-contacts').find('tbody tr');
                    $.each(tableData, function() {
                        $(this).find('td:eq(1)').addClass('bg-light-gray');
                    });
                });
            <?php } ?>
        }
        /* Customer profile invoices table */
        initDataTable('.table-invoices-single-client',
            admin_url + 'invoices/table/' + customer_id,
            'undefined',
            'undefined',
            'undefined', [
                [3, 'desc'],
                [0, 'desc']
            ]);

        initDataTable('.table-credit-notes', admin_url + 'credit_notes/table/' + customer_id, ['undefined'], ['undefined'], undefined, [0, 'desc']);

        /* Customer profile Estimates table */
        initDataTable('.table-estimates-single-client',
            admin_url + 'estimates/table/' + customer_id,
            'undefined',
            'undefined',
            'undefined', [
                [3, 'desc'],
                [0, 'desc']
            ]);

        /* Customer profile payments table */
        initDataTable('.table-payments-single-client',
            admin_url + 'payments/table/' + customer_id, undefined, undefined,
            'undefined', [0, 'desc']);

        /* Customer profile reminders table */
        initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + customer_id + '/' + 'customer', undefined, undefined, undefined, [1, 'asc']);

        /* Customer profile expenses table */
        initDataTable('.table-expenses-single-client',
            admin_url + 'expenses/table/' + customer_id,
            'undefined',
            'undefined',
            'undefined', [5, 'desc']);

        /* Customer profile proposals table */
        initDataTable('.table-proposals-client-profile',
            admin_url + 'proposals/proposal_relations/' + customer_id + '/customer',
            'undefined',
            'undefined',
            'undefined', [6, 'desc']);

        /* Custome profile projects table */
        initDataTable('.table-projects-single-client', admin_url + 'projects/table/' + customer_id, undefined, undefined, 'undefined', <?php echo hooks()->apply_filters('projects_table_default_order', json_encode(array(5, 'asc'))); ?>);

        var vRules = {};
        if (app.options.company_is_required == 1) {
            vRules = {
                company: 'required',
            }
        }

        appValidateForm($('.client-form'), vRules);

        if (typeof(customer_id) == 'undefined') {
            $('#company').on('blur', function() {
                var company = $(this).val();
                var $companyExistsDiv = $('#company_exists_info');

                if (company == '') {
                    $companyExistsDiv.addClass('hide');
                    return;
                }

                $.post(admin_url + 'clients/check_duplicate_customer_name', {
                        company: company
                    })
                    .done(function(response) {
                        if (response) {
                            response = JSON.parse(response);
                            if (response.exists == true) {
                                $companyExistsDiv.removeClass('hide');
                                $companyExistsDiv.html('<div class="info-block mbot15">' + response.message + '</div>');
                            } else {
                                $companyExistsDiv.addClass('hide');
                            }
                        }
                    });
            });
        }

        $('.billing-same-as-customer').on('click', function(e) {
            e.preventDefault();
            $('textarea[name="billing_street"]').val($('textarea[name="address"]').val());
            $('input[name="billing_city"]').val($('input[name="city"]').val());
            $('input[name="billing_state"]').val($('input[name="state"]').val());
            $('input[name="billing_zip"]').val($('input[name="zip"]').val());
            $('select[name="billing_country"]').selectpicker('val', $('select[name="country"]').selectpicker('val'));
        });

        $('.customer-copy-billing-address').on('click', function(e) {
            e.preventDefault();
            $('textarea[name="shipping_street"]').val($('textarea[name="billing_street"]').val());
            $('input[name="shipping_city"]').val($('input[name="billing_city"]').val());
            $('input[name="shipping_state"]').val($('input[name="billing_state"]').val());
            $('input[name="shipping_zip"]').val($('input[name="billing_zip"]').val());
            $('select[name="shipping_country"]').selectpicker('val', $('select[name="billing_country"]').selectpicker('val'));
        });

        $('body').on('hidden.bs.modal', '#contact', function() {
            $('#contact_data').empty();
        });

        $('.client-form').on('submit', function() {
            $('select[name="default_currency"]').prop('disabled', false);
        });

    });

    function delete_contact_profile_image(contact_id) {
        requestGet('clients/delete_contact_profile_image/' + contact_id).done(function() {
            $('body').find('#contact-profile-image').removeClass('hide');
            $('body').find('#contact-remove-img').addClass('hide');
            $('body').find('#contact-img').attr('src', '<?php echo base_url('assets/images/user-placeholder.jpg'); ?>');
        });
    }

    function customerGoogleDriveSave(pickData) {
        saveCustomerProfileExternalFile(pickData, 'gdrive');
    }

    function saveCustomerProfileExternalFile(files, externalType) {
        $.post(admin_url + 'clients/add_external_attachment', {
            files: files,
            clientid: customer_id,
            external: externalType
        }).done(function() {
            window.location.reload();
        });
    }

    function validate_contact_form() {
        appValidateForm('#contact-form', {
            firstname: 'required',
            lastname: 'required',
            password: {
                required: {
                    depends: function(element) {

                        var $sentSetPassword = $('input[name="send_set_password_email"]');

                        if ($('#contact input[name="contactid"]').val() == '' && $sentSetPassword.prop('checked') == false) {
                            return true;
                        }
                    }
                }
            },
            email: {
                <?php if (hooks()->apply_filters('contact_email_required', "true") === "true") { ?>
                    required: true,
                <?php } ?>
                email: true,
                // Use this hook only if the contacts are not logging into the customers area and you are not using support tickets piping.
                <?php if (hooks()->apply_filters('contact_email_unique', "true") === "true") { ?>
                    remote: {
                        url: admin_url + "misc/contact_email_exists",
                        type: 'post',
                        data: {
                            email: function() {
                                return $('#contact input[name="email"]').val();
                            },
                            userid: function() {
                                return $('body').find('input[name="contactid"]').val();
                            }
                        }
                    }
                <?php } ?>
            }
        }, contactFormHandler);
    }

    function contactFormHandler(form) {
        $('#contact input[name="is_primary"]').prop('disabled', false);

        $("#contact input[type=file]").each(function() {
            if ($(this).val() === "") {
                $(this).prop('disabled', true);
            }
        });

        var formURL = $(form).attr("action");
        var formData = new FormData($(form)[0]);

        $.ajax({
            type: 'POST',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            url: formURL
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
                if (typeof(response.is_individual) != 'undefined' && response.is_individual) {
                    $('.new-contact').addClass('disabled');
                    if (!$('.new-contact-wrapper')[0].hasAttribute('data-toggle')) {
                        $('.new-contact-wrapper').attr('data-toggle', 'tooltip');
                    }
                }
            }

            if ($.fn.DataTable.isDataTable('.table-contacts')) {
                $('.table-contacts').DataTable().ajax.reload(null, false);
            } else if ($.fn.DataTable.isDataTable('.table-all-contacts')) {
                $('.table-all-contacts').DataTable().ajax.reload(null, false);
            }

            if (response.proposal_warning && response.proposal_warning != false) {
                $('body').find('#contact_proposal_warning').removeClass('hide');
                $('body').find('#contact_update_proposals_emails').attr('data-original-email', response.original_email);
                $('#contact').animate({
                    scrollTop: 0
                }, 800);
            } else {
                $('#contact').modal('hide');
            }
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.responseText));
        });
        return false;
    }

    function contact(client_id, contact_id) {
        if (typeof(contact_id) == 'undefined') {
            contact_id = '';
        }
        requestGet('clients/form_contact/' + client_id + '/' + contact_id).done(function(response) {
            $('#contact_data').html(response);
            $('#contact').modal({
                show: true,
                backdrop: 'static'
            });
            $('body').off('shown.bs.modal', '#contact');
            $('body').on('shown.bs.modal', '#contact', function() {
                if (contact_id == '') {
                    $('#contact').find('input[name="firstname"]').focus();
                }
            });
            init_selectpicker();
            init_datepicker();
            custom_fields_hyperlink();
            validate_contact_form();
        }).fail(function(error) {
            var response = JSON.parse(error.responseText);
            alert_float('danger', response.message);
        });
    }


    function update_all_proposal_emails_linked_to_contact(contact_id) {
        var data = {};
        data.update = true;
        data.original_email = $('body').find('#contact_update_proposals_emails').data('original-email');
        $.post(admin_url + 'clients/update_all_proposal_emails_linked_to_customer/' + contact_id, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
            }
            $('#contact').modal('hide');
        });
    }

    function do_share_file_contacts(edit_contacts, file_id) {
        var contacts_shared_ids = $('select[name="share_contacts_id[]"]');
        if (typeof(edit_contacts) == 'undefined' && typeof(file_id) == 'undefined') {
            var contacts_shared_ids_selected = $('select[name="share_contacts_id[]"]').val();
        } else {
            var _temp = edit_contacts.toString().split(',');
            for (var cshare_id in _temp) {
                contacts_shared_ids.find('option[value="' + _temp[cshare_id] + '"]').attr('selected', true);
            }
            contacts_shared_ids.selectpicker('refresh');
            $('input[name="file_id"]').val(file_id);
            $('#customer_file_share_file_with').modal('show');
            return;
        }
        var file_id = $('input[name="file_id"]').val();
        $.post(admin_url + 'clients/update_file_share_visibility', {
            file_id: file_id,
            share_contacts_id: contacts_shared_ids_selected,
            customer_id: $('input[name="userid"]').val()
        }).done(function() {
            window.location.reload();
        });
    }

    function save_longitude_and_latitude(clientid) {
        var data = {};
        data.latitude = $('#latitude').val();
        data.longitude = $('#longitude').val();
        $.post(admin_url + 'clients/save_longitude_and_latitude/' + clientid, data).done(function(response) {
            if (response == 'success') {
                alert_float('success', "<?php echo _l('updated_successfully', _l('client')); ?>");
            }
            setTimeout(function() {
                window.location.reload();
            }, 1200);
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }

    function fetch_lat_long_from_google_cprofile() {
        var data = {};
        data.address = $('#long_lat_wrapper').data('address');
        data.city = $('#long_lat_wrapper').data('city');
        data.country = $('#long_lat_wrapper').data('country');
        $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
        $.post(admin_url + 'misc/fetch_address_info_gmaps', data).done(function(data) {
            data = JSON.parse(data);
            $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
            if (data.response.status == 'OK') {
                $('input[name="latitude"]').val(data.lat);
                $('input[name="longitude"]').val(data.lng);
            } else {
                if (data.response.status == 'ZERO_RESULTS') {
                    alert_float('warning', "<?php echo _l('g_search_address_not_found'); ?>");
                } else {
                    alert_float('danger', data.response.status + ' - ' + data.response.error_message);
                }
            }
        });
    }

    // UNIVERSITY AND COUNTRY JS

    var suggetions_university = [];
    const countriesArr = <?php echo !empty($admissionpreferences) ? json_encode(explode(",", $admissionpreferences->study_country)) : '[]' ?>;
    const universityArr = <?php echo !empty($admissionpreferences->university) ? $admissionpreferences->university : '{}'
                            ?>;
    var selectedUniversityArr = [];
    // const universityArr = {};
    $('#study_country').on('change select2:opening', async function() {
        let value = $(this).val();
        if (value.length > 0) {
            if (value.length <= 3) {
                selectedUniversityArr = [];
                $('#countries').val(value.join(','));
                var str = '';
                for (let k = 0; k < value.length; k++) {
                    var v = value[k];
                    var countryName = v.search("_") != -1 ? v.replace("_", " ") : v;

                    str += `<div class="col-lg-4">
          <div class="form-group">
            <label for="university${k}">${countryName} University</label>
            <input type="hidden" class="form-control suggest_university" data-country-name="${countryName}" name="university${k}" id="university${k}" value="" required>
          </div>
        </div>`;

                    if (Object.keys(universityArr).length > 0) {
                        if (v in universityArr) {
                            selectedUniversityArr[v] = universityArr[v];
                        } else {
                            selectedUniversityArr[v] = "";
                        }
                    }
                }

                $('.universities').html(str);

                if (Object.keys(selectedUniversityArr).length > 0) {
                    //console.log(selectedUniversityArr);
                    var count2 = 0;

                    for (const k in selectedUniversityArr) {
                        const v = selectedUniversityArr[k];
                        var countryName = k.search("_") != -1 ? k.replace("_", " ") : k;

                        let university_list = await show_university_dropdown(select_segment_default, k);
                        //console.log(university_list);
                        //console.log(university_list);
                        var tagInput1 = new TagsInput({
                            selector: `university${count2}`,
                            duplicate: false,
                            max: 3,
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
                            max: 3,
                            suggestions: university_list
                        });

                        suggetions_university[countryName] = university_list;
                    }
                }
            }

            if (value.length >= 3) {
                $(`#study_country option`).prop('disabled', true);
                for (let k = 0; k < value.length; k++) {
                    var v = value[k];
                    $(`#study_country option[value="${v}"]`).prop('disabled', false);
                }
            } else {
                $(`#study_country option`).prop('disabled', false);
            }
            $('#study_country').selectpicker('refresh');
        } else {
            $('#countries').val("");
            $('.universities').html("");
        }
        // set_university();
    });

    // Plugin Constructor
    var TagsInput = function(opts) {
        this.options = Object.assign(TagsInput.defaults, opts);
        this.init();
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


        // tags.input.addEventListener('keydown', function(e) {
        //     var str = tags.input.value.trim();

        //     if (!!(~[9, 13, 188].indexOf(e.keyCode))) {
        //         e.preventDefault();
        //         tags.input.value = "";
        //         if (str != "")
        //             tags.addTag(str);
        //     }

        // });

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
                    return suggestion.toLowerCase().startsWith(str.toLowerCase());
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

    // window.addEventListener('click', function(event) {
    //     var div_elements = document.querySelectorAll('.suggestions-container');
    //     div_elements.forEach((div_elements) => {
    //         div_elements.classList.add('hide_sugg');
    //     });
    // });

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

                str += `<div class="col-lg-4">
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
                        max: 3,
                        suggestions: university_list
                    });

                    var defaultVal = universityArr[k].split(",");
                    //console.log(defaultVal);

                    if (defaultVal != "") {
                        defaultVal.forEach(function(k1, v1) {
                            tagInput1.addData([k1]);
                        });
                    }
                    set_count++;
                    suggetions_university[countryName] = university_list;
                }
            }

            //console.log("University setup completed.");
            // Place your subsequent code here
        } catch (error) {
            console.error("An error occurred during university setup:", error);
        }
    }

    set_university();


    $("#entrance_exam_given").on('change', function() {
        entrance_exam_given();
    });

    function entrance_exam_given() {
        var eeg = $("#entrance_exam_given").val();
        if (eeg == 'YES') {
            $("#entrance_exam_details_div").show();
        } else {
            $("#entrance_exam_details_div").hide();
        }
    }

    $('#study_country').trigger('change')


    // SUBMIT ADMISSION PREFERENCES

    $('#save_admission_preferences').on('click', function(e) {
        e.preventDefault()
        var params = {
            program: $('#program').val(),
            course: $('#course').val(),
            sessionIntake: $('#session_intake').val(),
            countries: $('#study_country').val().join(","),
            entranceExamGiven: $('#entrance_exam_given').val(),
            entranceExamDetails: $('#entrance_exam_details').val(),
            admissionPreferencesId: $('#admissionpreferencesid').val(),
            client_id: $('#client_id').val(),
            universities: {}
        }

        // let countriesArr = params.countries.split(',')
        let countriesArr = $('#study_country').val();

        $.each(countriesArr, function(k, v) {
            params.universities[v] = $(`#university${k}`).val()
            if (params.universities[v] == "") {
                alert_float('danger', "Select " + v + " university is requried.");
                return false;
            }
        })

        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/update_admission_preferences' ?>",
            type: "POST",
            data: params,
            dataType: "JSON",
            success: function(res) {
                // alert(res.resp_desc)
                if (res.resp_id != undefined) {
                    $('#admissionpreferencesid').val(res.resp_id);
                }
                alert_float('success', res.resp_desc);

            }
        })
    })

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

    $(document).ready(function() {

        $('input[type=radio][name=after_x_status]').change(function() {
            let selected_value = $(this).val(); // Use 'this' to get the value of the selected radio input.
            // Hide both academic details by default.
            $('#twelthAcademicDetails, #diplomaAcademicDetails').removeClass("show").addClass("hide");
            if (selected_value === 'Both') {
                $('#twelthAcademicDetails, #diplomaAcademicDetails').removeClass("hide").addClass("show");
            } else if (selected_value === '12th') {
                $('#diplomaAcademicDetails').removeClass("show").addClass("hide");
                $('#twelthAcademicDetails').removeClass("hide").addClass("show");
            } else if (selected_value === 'Diploma') {
                $('#twelthAcademicDetails').removeClass("show").addClass("hide");
                $('#diplomaAcademicDetails').removeClass("hide").addClass("show");
            }
        });

        $("#twelth_result_status").on('change', function() {
            var trs = $("#twelth_result_status").val();
            if (trs == 'Awaited') {
                $("#twelth_marking_scheme_div").hide();
                $("#twelth_marking_scheme_div").find("select").val('').selectpicker("refresh");
                $("#twelth_percentage").parents(".border2").hide();
                $("#twelth_percentage").val('');

            } else if (trs == 'Declared') {
                $("#twelth_marking_scheme_div").show();
                $("#twelth_percentage").parents(".border2").show();
            }
        })
        $("#diploma_result_status").on('change', function() {
            var drs = $("#diploma_result_status").val();
            if (drs == 'Awaited') {
                $("#diploma_marking_scheme_div").hide();
                $("#diploma_marking_scheme_div").find("select").val('').selectpicker("refresh");
                $("#diploma_percentage").parents(".border2").hide();
                $("#diploma_percentage").val('');

            } else if (drs == 'Declared') {
                $("#diploma_marking_scheme_div").show();
                $("#diploma_percentage").parents(".border2").show();
            }
        })

        $("#entrance_result_status").on('change', function() {
            var ers = $("#entrance_result_status").val();
            if (ers == 'Awaited') {
                $("#entrance_percentage").parents(".border2").hide();
                $("#entrance_percentage").val('');

            } else if (ers == 'Declared') {
                $("#entrance_percentage").parents(".border2").show();
            }
        })
        $("#graduation_result_status").on('change', function() {
            var grs = $("#graduation_result_status").val();
            if (grs == 'Awaited') {
                $("#graduation_marking_scheme_div").hide();
                $("#graduation_percentage").parents(".border2").hide();
            } else if (grs == 'Declared') {
                $("#graduation_percentage").parents(".border2").show();
                $("#graduation_marking_scheme_div").show();
            }
        })


        var selectedRadioButton = $("input[type='radio']:checked");

        selectedRadioButton.prop("checked", true).trigger("change");


        // var selectedRadioButton = $("input[type='radio']");

        // // Trigger a click event on the selected radio button
        // // selectedRadioButton.click();
        $("input[name='after_x_status']").change(function() {
            $("#twelthAcademicDetails").find("input,select").val('').selectpicker("refresh");
            $("#diplomaAcademicDetails").find("input,select").val('').selectpicker("refresh");
        })
        setTimeout(() => {
            $('#twelth_result_status,#diploma_result_status,#entrance_result_status').trigger('change');

        }, 1000);
        $('#study_country').trigger('change');
        $("#declaration").find("input,select").attr("disabled", true).selectpicker("refresh");
        // $("#academic_details,#declaration").find("input,select").attr("disabled", true).selectpicker("refresh");
    })


    $('#save_profile_data').on('click', function(e) {
        e.preventDefault()
        let data = []; // Define data as an array, not an object
        $(".profile-data-div  div > input").each(function() {
            let name = $(this).attr("name");
            let value = $(this).val();;
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });

        $(".profile-data-div  div > textarea").each(function() {
            let name = $(this).attr("name");
            let value = $(this).val();
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });

        $(".profile-data-div  div > select").each(function() {
            let name = $(this).attr("name");
            let value = $(this).find("option:selected").val();
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });


        data.push({
            name: "csrf_token_name",
            value: $('input[name="csrf_token_name"]').val()
        });

        data.push({
            name: "clientid",
            value: $('input[name="clientid"]').val()
        });


        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/profile_update' ?>",
            type: "POST",
            data: data,
            dataType: "JSON",
            success: function(res) {
                // alert(res.resp_desc)
                if (res.resp_code == "RCS") {
                    alert_float('success', res.resp_desc);
                } else {
                    if (res.resp_code != '' && res.resp_desc != '') {
                        alert_float('danger', res.resp_desc);
                    }
                }
            }
        })
    })


    $('#save_student_data').on('click', function(e) {
        e.preventDefault()
        let data = []; // Define data as an array, not an object
        $(".student-data-div  div > input").each(function() {
            let name = $(this).attr("name");
            let value = $(this).val();;
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });

        $(".student-data-div  div > textarea").each(function() {
            let name = $(this).attr("name");
            let value = $(this).val();
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });

        $(".student-data-div  div > select").each(function() {
            let name = $(this).attr("name");
            let value = $(this).find("option:selected").val();
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });



        data.push({
            name: "csrf_token_name",
            value: $('input[name="csrf_token_name"]').val()
        });

        data.push({
            name: "clientid",
            value: $('input[name="clientid"]').val()
        });


        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/student_update' ?>",
            type: "POST",
            data: data,
            dataType: "JSON",
            success: function(res) {
                // alert(res.resp_desc)
                if (res.resp_code == "RCS") {
                    alert_float('success', res.resp_desc);
                } else {
                    if (res.resp_code != '' && res.resp_desc != '') {
                        alert_float('danger', res.resp_desc);
                    }
                }
            }
        })
    })

    $('#save_admission_details').on('click', function(e) {
        e.preventDefault()
        let data = []; // Define data as an array, not an object

        $("#academic_details div > input").each(function() {
            let name = $(this).attr("name");
            let value = $(this).val();
            let type = $(this).attr("type");

            if (type === "radio") {
                if ($(this).prop("checked")) {
                    data.push({
                        name: name,
                        value: value
                    });
                }
            } else {
                if (name !== undefined && value !== undefined) {
                    data.push({
                        name: name,
                        value: value
                    });
                }
            }
        });


        $("#academic_details  div > textarea").each(function() {
            let name = $(this).attr("name");
            let value = $(this).val();
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });

        $("#academic_details  div > select").each(function() {
            let name = $(this).attr("name");
            let value = $(this).find("option:selected").val();
            if (name != undefined && value != undefined) {
                data.push({
                    name: name,
                    value: value
                });
            }
        });


        data.push({
            name: "csrf_token_name",
            value: $('input[name="csrf_token_name"]').val()
        });

        data.push({
            name: "clientid",
            value: $('input[name="clientid"]').val()
        });


        $.ajax({
            url: "<?php echo base_url() . 'admin/clients/student_acadmic' ?>",
            type: "POST",
            data: data,
            dataType: "JSON",
            success: function(res) {
                // alert(res.resp_desc)
                if (res.resp_code == "RCS") {
                    alert_float('success', res.resp_desc);
                } else {
                    if (res.resp_code != '' && res.resp_desc != '') {
                        alert_float('danger', res.resp_desc);
                    }
                }
            }
        })
    })
</script>