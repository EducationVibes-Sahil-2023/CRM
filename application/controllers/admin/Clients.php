<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Clients extends AdminController
{
    /* List all clients */
    public function index()
    {
        $lastSegment = $this->uri->segment($this->uri->total_segments());
        if (!has_permission('customers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
                access_denied('customers');
            }
        }
        $user_lead_type = get_user_lead_type(get_staff_user_id());
        $data["user_lead_type"] = 0;
        if (!empty($user_lead_type->lead_type)) {
            $data["user_lead_type"] = $user_lead_type->lead_type;
        }
        if ($lastSegment == "clients") {
            redirect(admin_url());
        }

        $this->load->model('contracts_model');
        $this->load->model('leads_model');
        $data['contract_types'] = $this->contracts_model->get_contract_types();
        $data['groups']         = $this->clients_model->get_groups();
        $data['title']          = _l('clients');

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $this->load->model('invoices_model');
        $data['invoice_statuses'] = $this->invoices_model->get_statuses();

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('projects_model');
        $data['project_statuses'] = $this->projects_model->get_project_statuses();

        $data['customer_admins'] = $this->clients_model->get_customers_admin_unique_ids();
        // $data['application_stage'] = $this->clients_model->get_application_stage();
        $data['application_stage'] = get_applicant_stage();
        $data['application_sub_stage'] = $this->clients_model->get_application_sub_stage();
        $data['application_sub_stage_mbbs'] = $this->clients_model->get_application_sub_stage_mbbs();

        $whereContactsLoggedIn = '';
        if (!has_permission('customers', '', 'view')) {
            $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        }

        $data['contacts_logged_in_today'] = $this->clients_model->get_contacts('', 'last_login LIKE "' . date('Y-m-d') . '%"' . $whereContactsLoggedIn);

        $data['countries'] = $this->clients_model->get_clients_distinct_countries();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        // $data['staff'] = $this->staff_model->post_sale_get('', ['active' => 1]);
        $data['sources']  = $this->leads_model->get_source();
        $data['leadType'] = $this->leads_model->get_type();
        $data['vendorType'] = $this->leads_model->get_vendor();
        // $view_page = 'admin/clients/' . $lastSegment . "_manage";

        $view_page = 'admin/clients/' . $lastSegment . "_manage";
        $view_path = FCPATH . "application/views/" . $view_page . ".php"; // Adjust the path if needed

        if (file_exists($view_path)) {
            $this->load->view($view_page, $data);
        } else {
            $view_page = 'admin/clients/manage';
            // Page doesn't exist, load a default page or show an error
            $this->load->view($view_page, $data);
        }
    }


    public function customers()
    {

        // Permission check
        if (!has_permission('customers', '', 'view')) {
            return ajax_access_denied(); // Use return to stop further execution
        }

        // Load necessary models
        $this->load->model('leads_model');

        // Prepare data for view
        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['sources']   = $this->leads_model->get_source();
        $data['leadType']  = $this->leads_model->get_type();

        // Determine correct view page
        $view_page = 'admin/clients/customer'; // You can switch based on type if needed

        // Load view
        $this->load->view($view_page, $data);
    }


    public function customers_table()
    {
        // Permission check
        if (!has_permission('customers', '', 'view')) {
            return ajax_access_denied(); // Use return to stop execution
        }

        $view = "customers";

        // Load the corresponding table data
        $this->app->get_table_data($view);
    }


    public function table($type = "")
    {

        if (!has_permission('customers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
                ajax_access_denied();
            }
        }
        $view = "clients";
        if ($type == 2) {
            $view = "mbbs_abroad_clients";
        }
        $this->app->get_table_data($view);
    }

    public function all_contacts()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('all_contacts');
        }

        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }

        $data['title'] = _l('customer_contacts');
        $this->load->view('admin/clients/all_contacts', $data);
    }


    public function client($id = '')
    {
        // $database_secondary = $this->load->database('database_secondary', TRUE);

        $this->load->model('leads_model');
        $data['lead_type'] = $this->leads_model->get_type();
        $client = "";
        if (!empty($id)) {
            $client = $this->clients_model->get($id);
        }
        $data["dropdown_country_university_selection"] = $this->s_db->query("SELECT co.name,c.country_name,u.university_name FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') ")->result_array();
        if (!has_permission('customers', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                if ($client->addedfrom == get_staff_user_id()) {
                } else {
                    access_denied('customers');
                }
            }
        }

        $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);


        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                if (!has_permission('customers', '', 'create')) {
                    access_denied('customers');
                }

                $data = $this->input->post();
                $save_and_add_contact = false;
                if (isset($data['save_and_add_contact'])) {
                    unset($data['save_and_add_contact']);
                    $save_and_add_contact = true;
                }
                $id = $this->clients_model->add($data);
                if (!has_permission('customers', '', 'view')) {
                    $assign['customer_admins']   = [];
                    $assign['customer_admins'][] = get_staff_user_id();
                    $this->clients_model->assign_admins($assign, $id);
                }
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('client')));
                    if ($save_and_add_contact == false) {
                        redirect(admin_url('clients/client/' . $id));
                    } else {
                        redirect(admin_url('clients/client/' . $id . '?group=contacts&new_contact=true'));
                    }
                }
            } else {
                if (!has_permission('customers', '', 'edit')) {
                    if (!is_customer_admin($id)) {
                        if ($client->addedfrom == get_staff_user_id()) {
                        } else {
                            access_denied('customers');
                        }
                        // access_denied('customers');
                    }
                }
                $success = $this->clients_model->update($this->input->post(), $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('client')));
                }
                redirect(admin_url('clients/client/' . $id));
            }
        }

        $group         = !$this->input->get('group') ? 'profile' : $this->input->get('group');
        $data['group'] = $group;

        if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
            redirect(admin_url('clients/client/' . $id . '?group=contacts&contactid=' . $contact_id));
        }

        // Customer groups
        $data['groups'] = $this->clients_model->get_groups();


        if ($id == '') {
            $title = _l('add_new', _l('client_lowercase'));
        } else {
            $this->load->model('leads_model');

            $client                = $this->clients_model->get($id);

            $data["lead_data"]                = $this->leads_model->get($client->leadid);
            $data['customer_tabs'] = get_customer_profile_tabs();

            $prefix_page = !empty($data["lead_data"]->type_name)
                ? strtolower(str_replace(" ", "_", $data["lead_data"]->type_name))
                : '';

            foreach ($data['customer_tabs'] as $key => $tabs) {
                $urls = explode("/", $tabs["view"]); // Split URL into parts
                $last_index = count($urls) - 1; // Get last index

                // Modify the last segment by adding the prefix
                $urls[$last_index] = $prefix_page . '_' . $urls[$last_index];

                // Rebuild the URL
                $modified_url = implode("/", $urls);

                // Construct full file path using CodeIgniter's VIEWPATH constant
                $file_path = VIEWPATH . $modified_url . ".php";

                // Check if the file exists
                if (file_exists($file_path)) {
                    $data['customer_tabs'][$key]["view"] = $modified_url;
                } else {
                }
            }




            if (!$client) {
                show_404();
            }

            $data['contacts'] = $this->clients_model->get_contacts($id);
            $data['basicDetails'] = $this->clients_model->get_contact_by_userid($data['contacts'][0]['userid']);

            $data['tab']      = isset($data['customer_tabs'][$group]) ? $data['customer_tabs'][$group] : null;

            if (!$data['tab']) {
                show_404();
            }
            $data['basicdetails'] = $this->clients_model->getBasicDetails($id);
            $title          = $data["basicdetails"]->first_name . " " . $data["basicdetails"]->last_name;

            // Fetch data based on groups
            if ($group == 'profile') {
                $data['customer_groups'] = $this->clients_model->get_customer_groups($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);
                $data['university_shortlisting'] = $this->clients_model->university_shortlisting($id, 1);
                $data['passport_info'] = $this->clients_model->getPassportDetails($id);
                $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);
                $data['parentdetails'] = $this->clients_model->getParentDetails($id);
                $data['academicdetails'] = $this->clients_model->getAcademicDetails($id);
                $data['declarationdetails'] = $this->clients_model->getDeclarationDetails($id);
                $data['program_data'] = $this->clients_model->getProgram();
                $data['course_data'] = $this->clients_model->getCourse();
                $data['entrance_data'] = $this->clients_model->getEntrance();
                $data['documents'] =  $this->clients_model->get_documents($id);
                $data['score_columns'] =  $this->clients_model->get_scroe_column();
                $data['score_value'] =  $this->clients_model->get_scroe_value($id);
            } elseif ($group == 'attachments') {
                $data['attachments'] = get_all_customer_attachments($id);
            } elseif ($group == 'vault') {
                $data['vault_entries'] = hooks()->apply_filters('check_vault_entries_visibility', $this->clients_model->get_vault_entries($id));

                if ($data['vault_entries'] === -1) {
                    $data['vault_entries'] = [];
                }
            } elseif ($group == 'estimates') {
                $this->load->model('estimates_model');
                $data['estimate_statuses'] = $this->estimates_model->get_statuses();
            } elseif ($group == 'invoices') {
                $this->load->model('invoices_model');
                $data['invoice_statuses'] = $this->invoices_model->get_statuses();
            } elseif ($group == 'credit_notes') {
                $this->load->model('credit_notes_model');
                $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
                $data['credits_available']     = $this->credit_notes_model->total_remaining_credits_by_customer($id);
            } elseif ($group == 'payments') {
                $this->load->model('payment_modes_model');
                $data['payment_modes'] = $this->payment_modes_model->get();
            } elseif ($group == 'notes') {
                $data['user_notes'] = $this->misc_model->get_notes($id, 'customer');
            } elseif ($group == 'projects') {
                $this->load->model('projects_model');
                $data['project_statuses'] = $this->projects_model->get_project_statuses();
            } elseif ($group == 'statement') {
                if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
                    set_alert('danger', _l('access_denied'));
                    redirect(admin_url('clients/client/' . $id));
                }

                $data = array_merge($data, prepare_mail_preview_data('customer_statement', $id));
            } elseif ($group == 'map') {
                if (get_option('google_api_key') != '' && !empty($client->latitude) && !empty($client->longitude)) {
                    $this->app_scripts->add('map-js', base_url($this->app_scripts->core_file('assets/js', 'map.js')) . '?v=' . $this->app_css->core_version());

                    $this->app_scripts->add('google-maps-api-js', [
                        'path'       => 'https://maps.googleapis.com/maps/api/js?key=' . get_option('google_api_key') . '&callback=initMap',
                        'attributes' => [
                            'async',
                            'defer',
                            'latitude'       => "$client->latitude",
                            'longitude'      => "$client->longitude",
                            'mapMarkerTitle' => "$client->company",
                        ],
                    ]);
                }
            } elseif ($group == 'tracker') {
                $this->load->model('exam_model');
                $data['upload_documents'] = $this->clients_model->get_update_documents($id);
                $data['upload_documents_button'] = $this->clients_model->upload_documents_button();
                $data['profile_verification_button'] = $this->clients_model->profile_verification_button();
                $data['profile_creator_vendor'] = $this->clients_model->get_profile_creator_vendor();
                $data['profile_creation_data'] = $this->clients_model->get_profile_creator_data($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);
                $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);
                $data['university_shortlisting'] = $this->clients_model->university_shortlisting($id);
                $data['university_application_status'] = $this->clients_model->university_status_update();
                $data['university_status_submit'] = $this->clients_model->university_status_submit();
                $data['documents'] =  $this->clients_model->get_documents($id);
                $university_names = array_column($data['university_shortlisting'], "university_name");
                $data['university_exams'] = [];
                if (!empty($university_names)) {
                    $data['university_exams'] = $this->clients_model->university_exams($university_names);
                }
                $data['exams_array'] = array_column(get_university_exam(), null, "id");
                $data['entrance_exams'] =  $this->clients_model->entrance_exams($id);
                $data['entrance_exams'] = array_reduce($data['entrance_exams'], function ($acc, $row) {
                    $acc[$row['university_name']] = ($acc[$row['university_name']] ?? []);
                    $acc[$row['university_name']][] = $row;
                    return $acc;
                }, []);


                $data['legalization'] =  $this->clients_model->legalization_data($id);

                $data['customer_vendors'] = [];
                if (!empty($data['profile_creation_data'][0]["vendor"])) {
                    $data['customer_vendors'] = $this->clients_model->get_profile_creator_vendor($data['profile_creation_data'][0]["vendor"]);
                }
            } else if ($group == 'fly_ticket') {
                $data['country_list'] = get_country_list(7);
                $data['vendor_list'] = get_vendor_list(3);
                $data['payment_mode'] = get_payment_mode();
                $data['departure_location'] = get_departure_list();
            } else if ($group == 'visa') {
                $data['selected_university_country'] = $this->clients_model->selected_university_country($id);
                $data['vendor'] = $this->clients_model->visa_vendor();
                $data["visa_data"] = $this->db->select('*')->where(['client_id' => $id])->get(db_prefix() . 'visa_documents')->result_array();
            } else if ($group == 'accommodation') {
                $data['selected_university_country'] = $this->clients_model->selected_university_country($id);
                $data['vendor'] = $this->clients_model->accommodation_vendor();
                $data["accommodation_data"] = $this->db->select('*')->where(['client_id' => $id])->get(db_prefix() . 'accommodation')->result_array();
                $data["flight_data"] = $this->db->select('*')->where(['client_id' => $id])->get(db_prefix() . 'flight')->result_array();
            }
            $data["client_infomation"] = $this->clients_model->get($id);


            // $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['members'] = $this->staff_model->post_sale_get();

            $data['staff'] = [];
            if (!empty($data["lead_data"]->type)) {
                $lead_status_data = $data["lead_data"]->type;
                foreach ($data['members'] as $members) {
                    // if ($members["lead_type"] == $lead_status_data) {
                    $data['staff'][] = $members;
                    // }
                }
            }
                // echo $data["lead_data"]->form_data->lead_status;
            ;

            $data['client'] = $client;
            // $title          = $client->company;


            // Get all active staff members (used to add reminder)
            $data['members'] = $data['staff'];

            if (!empty($data['client']->company)) {
                // Check if is realy empty client company so we can set this field to empty
                // The query where fetch the client auto populate firstname and lastname if company is empty
                if (is_empty_customer_company($data['client']->userid)) {
                    $data['client']->company = '';
                }
            }
        }
        $data['lead_type_status'] = $this->db->select('type')->where('id', $client->leadid)->get(db_prefix() . 'leads')->row()->type;
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        if ($id != '') {
            $customer_currency = $data['client']->default_currency;

            foreach ($data['currencies'] as $currency) {
                if ($customer_currency != 0) {
                    if ($currency['id'] == $customer_currency) {
                        $customer_currency = $currency;

                        break;
                    }
                } else {
                    if ($currency['isdefault'] == 1) {
                        $customer_currency = $currency;

                        break;
                    }
                }
            }

            if (is_array($customer_currency)) {
                $customer_currency = (object) $customer_currency;
            }

            $data['customer_currency'] = $customer_currency;

            $slug_zip_folder = ($client->company != ''
                ? $client->companyclient
                : get_contact_full_name(get_primary_contact_user_id($client->userid))
            );

            $data['zip_in_folder'] = slug_it($slug_zip_folder);
        }

        $data['bodyclass'] = 'customer-profile dynamic-create-groups';
        $data['title']     = $title;
        $data['client_id']     = $id;




        // $data["customer_tabs"]["profile"]["view"] = 'admin/clients/groups/' . !empty($data["lead_data"]->type_name) ? 'admin/clients/groups/' . 'profile_' . str_replace(" ", "_", strtolower($data["lead_data"]->type_name)) : 'admin/clients/groups/' . 'profile';


        $data["tab"]["js"] =  !empty($data["lead_data"]->type_name) ? 'admin/clients/client_js_' . str_replace(" ", "_", strtolower($data["lead_data"]->type_name)) : 'admin/clients/client_js';

        $this->load->view('admin/clients/client', $data);
    }

    public function ev_partner($id = '')
    {
        // $database_secondary = $this->load->database('database_secondary', TRUE);

        $this->load->model('leads_model');
        $data['lead_type'] = $this->leads_model->get_type();
        $client = "";
        if (!empty($id)) {
            $client = $this->clients_model->get($id);
        }
        $data["dropdown_country_university_selection"] = $this->s_db->query("SELECT co.name,c.country_name,u.university_name FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') ")->result_array();
        if (!has_permission('customers', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                if ($client->addedfrom == get_staff_user_id()) {
                } else {
                    access_denied('customers');
                }
            }
        }

        $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);


        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                if (!has_permission('customers', '', 'create')) {
                    access_denied('customers');
                }

                $data = $this->input->post();
                $save_and_add_contact = false;
                if (isset($data['save_and_add_contact'])) {
                    unset($data['save_and_add_contact']);
                    $save_and_add_contact = true;
                }
                $id = $this->clients_model->add($data);
                if (!has_permission('customers', '', 'view')) {
                    $assign['customer_admins']   = [];
                    $assign['customer_admins'][] = get_staff_user_id();
                    $this->clients_model->assign_admins($assign, $id);
                }
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('client')));
                    if ($save_and_add_contact == false) {
                        redirect(admin_url('clients/client/' . $id));
                    } else {
                        redirect(admin_url('clients/client/' . $id . '?group=contacts&new_contact=true'));
                    }
                }
            } else {
                if (!has_permission('customers', '', 'edit')) {
                    if (!is_customer_admin($id)) {
                        if ($client->addedfrom == get_staff_user_id()) {
                        } else {
                            access_denied('customers');
                        }
                        // access_denied('customers');
                    }
                }
                $success = $this->clients_model->update($this->input->post(), $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('client')));
                }
                redirect(admin_url('clients/client/' . $id));
            }
        }

        $group         = !$this->input->get('group') ? 'profile' : $this->input->get('group');
        $data['group'] = $group;

        if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
            redirect(admin_url('clients/client/' . $id . '?group=contacts&contactid=' . $contact_id));
        }

        // Customer groups
        $data['groups'] = $this->clients_model->get_groups();


        if ($id == '') {
            $title = _l('add_new', _l('client_lowercase'));
        } else {
            $this->load->model('leads_model');

            $client                = $this->clients_model->get($id);

            if (!empty($client->leadid)) {
                $data["lead_data"]                = $this->leads_model->get($client->leadid);
            }
            $data['customer_tabs'] = get_customer_profile_tabs();

            $prefix_page = !empty($data["lead_data"]->type_name)
                ? strtolower(str_replace(" ", "_", $data["lead_data"]->type_name))
                : '';

            foreach ($data['customer_tabs'] as $key => $tabs) {
                $urls = explode("/", $tabs["view"]); // Split URL into parts
                $last_index = count($urls) - 1; // Get last index

                // Modify the last segment by adding the prefix
                $urls[$last_index] = $prefix_page . '_' . $urls[$last_index];

                // Rebuild the URL
                $modified_url = implode("/", $urls);

                // Construct full file path using CodeIgniter's VIEWPATH constant
                $file_path = VIEWPATH . $modified_url . ".php";

                // Check if the file exists
                if (file_exists($file_path)) {
                    $data['customer_tabs'][$key]["view"] = $modified_url;
                } else {
                }
            }




            if (!$client) {
                show_404();
            }

            $data['contacts'] = $this->clients_model->get_contacts($id);
            $data['basicDetails'] = $this->clients_model->get_contact_by_userid($data['contacts'][0]['userid']);

            $data['tab']      = isset($data['customer_tabs'][$group]) ? $data['customer_tabs'][$group] : null;


            if (!$data['tab']) {
                show_404();
            }
            $data['basicdetails'] = $this->clients_model->getBasicDetails($id);
            $title          = $data["basicdetails"]->first_name . " " . $data["basicdetails"]->last_name;

            // Fetch data based on groups
            if ($group == 'profile') {
                $data["tab"]["view"] =  'admin/clients/groups/ev_partner_profile';

                $data['customer_groups'] = $this->clients_model->get_customer_groups($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);

                $data['passport_info'] = $this->clients_model->getPassportDetails($id);
                $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);
                $data['parentdetails'] = $this->clients_model->getParentDetails($id);
                $data['academicdetails'] = $this->clients_model->getAcademicDetails($id);
                $data['declarationdetails'] = $this->clients_model->getDeclarationDetails($id);
                $data['program_data'] = $this->clients_model->getProgram();
                $data['course_data'] = $this->clients_model->getCourse();
                $data['entrance_data'] = $this->clients_model->getEntrance();
                $data['documents'] =  $this->clients_model->get_documents($id);
                $data['score_columns'] =  $this->clients_model->get_scroe_column();
                $data['score_value'] =  $this->clients_model->get_scroe_value($id);
            } elseif ($group == 'attachments') {
                $data['attachments'] = get_all_customer_attachments($id);
            } elseif ($group == 'vault') {
                $data['vault_entries'] = hooks()->apply_filters('check_vault_entries_visibility', $this->clients_model->get_vault_entries($id));

                if ($data['vault_entries'] === -1) {
                    $data['vault_entries'] = [];
                }
            } elseif ($group == 'estimates') {
                $this->load->model('estimates_model');
                $data['estimate_statuses'] = $this->estimates_model->get_statuses();
            } elseif ($group == 'invoices') {
                $this->load->model('invoices_model');
                $data['invoice_statuses'] = $this->invoices_model->get_statuses();
            } elseif ($group == 'credit_notes') {
                $this->load->model('credit_notes_model');
                $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
                $data['credits_available']     = $this->credit_notes_model->total_remaining_credits_by_customer($id);
            } elseif ($group == 'payments') {
                $this->load->model('payment_modes_model');
                $data['payment_modes'] = $this->payment_modes_model->get();
            } elseif ($group == 'notes') {
                $data['user_notes'] = $this->misc_model->get_notes($id, 'customer');
            } elseif ($group == 'projects') {
                $this->load->model('projects_model');
                $data['project_statuses'] = $this->projects_model->get_project_statuses();
            } elseif ($group == 'statement') {
                if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
                    set_alert('danger', _l('access_denied'));
                    redirect(admin_url('clients/client/' . $id));
                }

                $data = array_merge($data, prepare_mail_preview_data('customer_statement', $id));
            } elseif ($group == 'map') {
                if (get_option('google_api_key') != '' && !empty($client->latitude) && !empty($client->longitude)) {
                    $this->app_scripts->add('map-js', base_url($this->app_scripts->core_file('assets/js', 'map.js')) . '?v=' . $this->app_css->core_version());

                    $this->app_scripts->add('google-maps-api-js', [
                        'path'       => 'https://maps.googleapis.com/maps/api/js?key=' . get_option('google_api_key') . '&callback=initMap',
                        'attributes' => [
                            'async',
                            'defer',
                            'latitude'       => "$client->latitude",
                            'longitude'      => "$client->longitude",
                            'mapMarkerTitle' => "$client->company",
                        ],
                    ]);
                }
            } elseif ($group == 'tracker') {
                $data["tab"]["view"] =  'admin/clients/groups/mbbs_abroad_applicant_tracker';
                $this->load->model('exam_model');
                $data['upload_documents'] = $this->clients_model->get_update_documents($id);
                $data['upload_documents_button'] = $this->clients_model->upload_documents_button();
                $data['profile_verification_button'] = $this->clients_model->profile_verification_button();
                $data['profile_creator_vendor'] = $this->clients_model->get_profile_creator_vendor();
                $data['profile_creation_data'] = $this->clients_model->get_profile_creator_data($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);
                $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);
                $data['university_shortlisting'] = $this->clients_model->university_shortlisting($id);
                $data['university_application_status'] = $this->clients_model->university_status_update();
                $data['university_status_submit'] = $this->clients_model->university_status_submit();
                $data['documents'] =  $this->clients_model->get_documents($id);
                $university_names = array_column($data['university_shortlisting'], "university_name");
                $data['university_exams'] = [];
                if (!empty($university_names)) {
                    $data['university_exams'] = $this->clients_model->university_exams($university_names);
                }
                $data['exams_array'] = array_column(get_university_exam(), null, "id");
                $data['entrance_exams'] =  $this->clients_model->entrance_exams($id);
                $data['entrance_exams'] = array_reduce($data['entrance_exams'], function ($acc, $row) {
                    $acc[$row['university_name']] = ($acc[$row['university_name']] ?? []);
                    $acc[$row['university_name']][] = $row;
                    return $acc;
                }, []);


                $data['legalization'] =  $this->clients_model->legalization_data($id);

                $data['customer_vendors'] = [];
                if (!empty($data['profile_creation_data'][0]["vendor"])) {
                    $data['customer_vendors'] = $this->clients_model->get_profile_creator_vendor($data['profile_creation_data'][0]["vendor"]);
                }
            } else if ($group == 'fly_ticket') {
                $data['country_list'] = get_country_list(7);
                $data['vendor_list'] = get_vendor_list(3);
                $data['payment_mode'] = get_payment_mode();
                $data['departure_location'] = get_departure_list();
            } else if ($group == 'visa') {
                $data['selected_university_country'] = $this->clients_model->selected_university_country($id);
                $data['vendor'] = $this->clients_model->visa_vendor();
                $data["visa_data"] = $this->db->select('*')->where(['client_id' => $id])->get(db_prefix() . 'visa_documents')->result_array();
            } else if ($group == 'accommodation') {
                $data['selected_university_country'] = $this->clients_model->selected_university_country($id);
                $data['vendor'] = $this->clients_model->accommodation_vendor();
                $data["accommodation_data"] = $this->db->select('*')->where(['client_id' => $id])->get(db_prefix() . 'accommodation')->result_array();
                $data["flight_data"] = $this->db->select('*')->where(['client_id' => $id])->get(db_prefix() . 'flight')->result_array();
            }
            $data["client_infomation"] = $this->clients_model->get($id);


            // $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['members'] = $this->staff_model->post_sale_get();

            $data['staff'] = [];
            if (!empty($data["lead_data"]->type)) {
                $lead_status_data = $data["lead_data"]->type;
                foreach ($data['members'] as $members) {
                    // if ($members["lead_type"] == $lead_status_data) {
                    $data['staff'][] = $members;
                    // }
                }
            }
                // echo $data["lead_data"]->form_data->lead_status;
            ;

            $data['client'] = $client;
            // $title          = $client->company;


            // Get all active staff members (used to add reminder)
            $data['members'] = $data['staff'];

            if (!empty($data['client']->company)) {
                // Check if is realy empty client company so we can set this field to empty
                // The query where fetch the client auto populate firstname and lastname if company is empty
                if (is_empty_customer_company($data['client']->userid)) {
                    $data['client']->company = '';
                }
            }
        }
        $data['lead_type_status'] = 2;
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        if ($id != '') {
            $customer_currency = $data['client']->default_currency;

            foreach ($data['currencies'] as $currency) {
                if ($customer_currency != 0) {
                    if ($currency['id'] == $customer_currency) {
                        $customer_currency = $currency;

                        break;
                    }
                } else {
                    if ($currency['isdefault'] == 1) {
                        $customer_currency = $currency;

                        break;
                    }
                }
            }

            if (is_array($customer_currency)) {
                $customer_currency = (object) $customer_currency;
            }

            $data['customer_currency'] = $customer_currency;

            $slug_zip_folder = ($client->company != ''
                ? $client->companyclient
                : get_contact_full_name(get_primary_contact_user_id($client->userid))
            );

            $data['zip_in_folder'] = slug_it($slug_zip_folder);
        }

        $data['bodyclass'] = 'customer-profile dynamic-create-groups';
        $data['title']     = $title;
        $data['client_id']     = $id;




        // $data["customer_tabs"]["profile"]["view"] = 'admin/clients/groups/' . !empty($data["lead_data"]->type_name) ? 'admin/clients/groups/' . 'profile_' . str_replace(" ", "_", strtolower($data["lead_data"]->type_name)) : 'admin/clients/groups/' . 'profile';


        $data["tab"]["js"] =  'admin/clients/client_js_mbbs_abroad';
        $data["tab"]["left_tabs"] =  'admin/clients/ev_tabs';

        $this->load->view('admin/clients/client', $data);
    }


    public function export($contact_id)
    {
        if (is_admin()) {
            $this->load->library('gdpr/gdpr_contact');
            $this->gdpr_contact->export($contact_id);
        }
    }

    // Used to give a tip to the user if the company exists when new company is created
    public function check_duplicate_customer_name()
    {
        if (has_permission('customers', '', 'create')) {
            $companyName = trim($this->input->post('company'));
            $response    = [
                'exists'  => (bool) total_rows(db_prefix() . 'clients', ['company' => $companyName]) > 0,
                'message' => _l('company_exists_info', '<b>' . $companyName . '</b>'),
            ];
            echo json_encode($response);
        }
    }

    public function save_longitude_and_latitude($client_id)
    {
        if (!has_permission('customers', '', 'edit')) {
            if (!is_customer_admin($client_id)) {
                ajax_access_denied();
            }
        }

        $this->db->where('userid', $client_id);
        $this->db->update(db_prefix() . 'clients', [
            'longitude' => $this->input->post('longitude'),
            'latitude'  => $this->input->post('latitude'),
        ]);
        if ($this->db->affected_rows() > 0) {
            echo 'success';
        } else {
            echo 'false';
        }
    }

    public function form_contact($customer_id, $contact_id = '')
    {
        if (!has_permission('customers', '', 'view')) {
            if (!is_customer_admin($customer_id)) {
                echo _l('access_denied');
                die;
            }
        }
        $data['customer_id'] = $customer_id;
        $data['contactid']   = $contact_id;
        if ($this->input->post()) {
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            unset($data['contactid']);
            if ($contact_id == '') {
                if (!has_permission('customers', '', 'create')) {
                    if (!is_customer_admin($customer_id)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                        echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                        die;
                    }
                }
                $id      = $this->clients_model->add_contact($data, $customer_id);
                $message = '';
                $success = false;
                if ($id) {
                    handle_contact_profile_image_upload($id);
                    $success = true;
                    $message = _l('added_successfully', _l('contact'));
                }
                echo json_encode([
                    'success'             => $success,
                    'message'             => $message,
                    'has_primary_contact' => (total_rows(db_prefix() . 'contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                    'is_individual'       => is_empty_customer_company($customer_id) && total_rows(db_prefix() . 'contacts', ['userid' => $customer_id]) == 1,
                ]);
                die;
            }
            if (!has_permission('customers', '', 'edit')) {
                if (!is_customer_admin($customer_id)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
            }
            $original_contact = $this->clients_model->get_contact($contact_id);
            $success          = $this->clients_model->update_contact($data, $contact_id);
            $message          = '';
            $proposal_warning = false;
            $original_email   = '';
            $updated          = false;
            if (is_array($success)) {
                if (isset($success['set_password_email_sent'])) {
                    $message = _l('set_password_email_sent_to_client');
                } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                    $updated = true;
                    $message = _l('set_password_email_sent_to_client_and_profile_updated');
                }
            } else {
                if ($success == true) {
                    $updated = true;
                    $message = _l('updated_successfully', _l('contact'));
                }
            }
            if (handle_contact_profile_image_upload($contact_id) && !$updated) {
                $message = _l('updated_successfully', _l('contact'));
                $success = true;
            }
            if ($updated == true) {
                $contact = $this->clients_model->get_contact($contact_id);
                if (total_rows(db_prefix() . 'proposals', [
                    'rel_type' => 'customer',
                    'rel_id' => $contact->userid,
                    'email' => $original_contact->email,
                ]) > 0 && ($original_contact->email != $contact->email)) {
                    $proposal_warning = true;
                    $original_email   = $original_contact->email;
                }
            }
            echo json_encode([
                'success'             => $success,
                'proposal_warning'    => $proposal_warning,
                'message'             => $message,
                'original_email'      => $original_email,
                'has_primary_contact' => (total_rows(db_prefix() . 'contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
            ]);
            die;
        }
        if ($contact_id == '') {
            $title = _l('add_new', _l('contact_lowercase'));
        } else {
            $data['contact'] = $this->clients_model->get_contact($contact_id);

            if (!$data['contact']) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                echo json_encode([
                    'success' => false,
                    'message' => 'Contact Not Found',
                ]);
                die;
            }
            $title = $data['contact']->firstname . ' ' . $data['contact']->lastname;
        }

        $data['customer_permissions'] = get_contact_permissions();
        $data['title']                = $title;
        $this->load->view('admin/clients/modals/contact', $data);
    }

    public function confirm_registration($client_id)
    {
        if (!is_admin()) {
            access_denied('Customer Confirm Registration, ID: ' . $client_id);
        }
        $this->clients_model->confirm_registration($client_id);
        set_alert('success', _l('customer_registration_successfully_confirmed'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function update_file_share_visibility()
    {
        if ($this->input->post()) {
            $file_id           = $this->input->post('file_id');
            $share_contacts_id = [];

            if ($this->input->post('share_contacts_id')) {
                $share_contacts_id = $this->input->post('share_contacts_id');
            }

            $this->db->where('file_id', $file_id);
            $this->db->delete(db_prefix() . 'shared_customer_files');

            foreach ($share_contacts_id as $share_contact_id) {
                $this->db->insert(db_prefix() . 'shared_customer_files', [
                    'file_id'    => $file_id,
                    'contact_id' => $share_contact_id,
                ]);
            }
        }
    }

    public function delete_contact_profile_image($contact_id)
    {
        hooks()->do_action('before_remove_contact_profile_image');
        if (file_exists(get_upload_path_by_type('contact_profile_images') . $contact_id)) {
            delete_dir(get_upload_path_by_type('contact_profile_images') . $contact_id);
        }
        $this->db->where('id', $contact_id);
        $this->db->update(db_prefix() . 'contacts', [
            'profile_image' => null,
        ]);
    }

    public function mark_as_active($id)
    {
        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'clients', [
            'active' => 1,
        ]);
        redirect(admin_url('clients/client/' . $id));
    }

    public function consents($id)
    {
        if (!has_permission('customers', '', 'view')) {
            if (!is_customer_admin(get_user_id_by_contact_id($id))) {
                echo _l('access_denied');
                die;
            }
        }

        $this->load->model('gdpr_model');
        $data['purposes']   = $this->gdpr_model->get_consent_purposes($id, 'contact');
        $data['consents']   = $this->gdpr_model->get_consents(['contact_id' => $id]);
        $data['contact_id'] = $id;
        $this->load->view('admin/gdpr/contact_consent', $data);
    }

    public function update_all_proposal_emails_linked_to_customer($contact_id)
    {
        $success = false;
        $email   = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email,userid');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'customer',
                'rel_id'   => $contact->userid,
                'email'    => $this->input->post('original_email'),
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update(db_prefix() . 'proposals', [
                    'email' => $contact->email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('contact_lowercase'),
                $contact->email,
            ]),
        ]);
    }

    public function assign_admins($id)
    {
        if (!has_permission('customers', '', 'create') && !has_permission('customers', '', 'edit')) {
            access_denied('customers');
        }
        $success = $this->clients_model->assign_admins($this->input->post(), $id);
        if ($success == true) {
            set_alert('success', _l('updated_successfully', _l('client')));
        }

        redirect(admin_url('clients/client/' . $id . '?tab=customer_admins'));
    }

    public function delete_customer_admin($customer_id, $staff_id)
    {
        if (!has_permission('customers', '', 'create') && !has_permission('customers', '', 'edit')) {
            access_denied('customers');
        }

        $this->db->where('customer_id', $customer_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete(db_prefix() . 'customer_admins');
        redirect(admin_url('clients/client/' . $customer_id) . '?tab=customer_admins');
    }

    public function delete_contact($customer_id, $id)
    {
        if (!has_permission('customers', '', 'delete')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('customers');
            }
        }
        $contact      = $this->clients_model->get_contact($id);
        $hasProposals = false;
        if ($contact && is_gdpr()) {
            if (total_rows(db_prefix() . 'proposals', ['email' => $contact->email]) > 0) {
                $hasProposals = true;
            }
        }

        $this->clients_model->delete_contact($id);
        if ($hasProposals) {
            $this->session->set_flashdata('gdpr_delete_warning', true);
        }
        redirect(admin_url('clients/client/' . $customer_id . '?group=contacts'));
    }

    public function contacts($client_id)
    {
        $this->app->get_table_data('contacts', [
            'client_id' => $client_id,
        ]);
    }

    public function upload_attachment($id)
    {
        handle_client_attachments_upload($id);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->misc_model->add_attachment_to_database($this->input->post('clientid'), 'customer', $this->input->post('files'), $this->input->post('external'));
        }
    }

    public function delete_attachment($customer_id, $id)
    {
        if (has_permission('customers', '', 'delete') || is_customer_admin($customer_id)) {
            $this->clients_model->delete_attachment($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* Delete client */
    public function delete($id)
    {


        if (!has_permission('customers', '', 'delete')) {
            access_denied('customers');
        }
        if (!$id) {
            redirect($_SERVER['HTTP_REFERER']);
        }
        $response = $this->clients_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('customer_delete_transactions_warning', _l('invoices') . ', ' . _l('estimates') . ', ' . _l('credit_notes')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('client')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('client_lowercase')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* Staff can login as client */
    public function login_as_client($id)
    {
        if (is_admin()) {
            login_as_client($id);
        }
        hooks()->do_action('after_contact_login');
        redirect(site_url());
    }

    public function get_customer_billing_and_shipping_details($id)
    {
        echo json_encode($this->clients_model->get_customer_billing_and_shipping_details($id));
    }

    /* Change client status / active / inactive */
    public function change_contact_status($id, $status)
    {
        if (has_permission('customers', '', 'edit') || is_customer_admin(get_user_id_by_contact_id($id))) {
            if ($this->input->is_ajax_request()) {
                $this->clients_model->change_contact_status($id, $status);
            }
        }
    }

    /* Change client status / active / inactive */
    public function change_client_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->clients_model->change_client_status($id, $status);
        }
    }

    /* Zip function for credit notes */
    public function zip_credit_notes($id)
    {
        $has_permission_view = has_permission('credit_notes', '', 'view');

        if (!$has_permission_view && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('Zip Customer Credit Notes');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'credit_notes',
                'status'            => $this->input->post('credit_note_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=credit_notes'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    public function zip_invoices($id)
    {
        $has_permission_view = has_permission('invoices', '', 'view');
        if (
            !$has_permission_view && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0'
        ) {
            access_denied('Zip Customer Invoices');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'invoices',
                'status'            => $this->input->post('invoice_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=invoices'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    /* Since version 1.0.2 zip client estimates */
    public function zip_estimates($id)
    {
        $has_permission_view = has_permission('estimates', '', 'view');
        if (
            !$has_permission_view && !has_permission('estimates', '', 'view_own')
            && get_option('allow_staff_view_estimates_assigned') == '0'
        ) {
            access_denied('Zip Customer Estimates');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'estimates',
                'status'            => $this->input->post('estimate_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=estimates'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    public function zip_payments($id)
    {
        $has_permission_view = has_permission('payments', '', 'view');

        if (
            !$has_permission_view && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0'
        ) {
            access_denied('Zip Customer Payments');
        }

        $this->load->library('app_bulk_pdf_export', [
            'export_type'       => 'payments',
            'payment_mode'      => $this->input->post('paymentmode'),
            'date_from'         => $this->input->post('zip-from'),
            'date_to'           => $this->input->post('zip-to'),
            'redirect_on_error' => admin_url('clients/client/' . $id . '?group=payments'),
        ]);

        $this->app_bulk_pdf_export->set_client_id($id);
        $this->app_bulk_pdf_export->set_client_id_column(db_prefix() . 'clients.userid');
        $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
        $this->app_bulk_pdf_export->export();
    }

    public function import()
    {
        if (!has_permission('customers', '', 'create')) {
            access_denied('customers');
        }

        $dbFields = $this->db->list_fields(db_prefix() . 'contacts');
        foreach ($dbFields as $key => $contactField) {
            if ($contactField == 'phonenumber') {
                $dbFields[$key] = 'contact_phonenumber';
            }
        }

        $dbFields = array_merge($dbFields, $this->db->list_fields(db_prefix() . 'clients'));

        $this->load->library('import/import_customers', [], 'import');

        $this->import->setDatabaseFields($dbFields)
            ->setCustomFields(get_custom_fields('customers'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();


            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['groups']    = $this->clients_model->get_groups();
        $data['title']     = _l('import');
        $data['bodyclass'] = 'dynamic-create-groups';
        $this->load->view('admin/clients/import', $data);
    }

    public function groups()
    {
        if (!is_admin()) {
            access_denied('Customer Groups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customers_groups');
        }
        $data['title'] = _l('customer_groups');
        $this->load->view('admin/clients/groups_manage', $data);
    }

    public function group()
    {
        if (!is_admin() && get_option('staff_members_create_inline_customer_groups') == '0') {
            access_denied('Customer Groups');
        }

        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id      = $this->clients_model->add_group($data);
                $message = $id ? _l('added_successfully', _l('customer_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['name'],
                ]);
            } else {
                $success = $this->clients_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('customer_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('clients/groups'));
        }
        $response = $this->clients_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('customer_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('customer_group_lowercase')));
        }
        redirect(admin_url('clients/groups'));
    }

    public function bulk_action()
    {
        $apostille_documents = array_column(get_orignal_document_list(0, 0, 1), null, "id");
        $apostille_vendors = array_column(get_vendor_list(), null, "id");
        hooks()->do_action('before_do_bulk_action_for_customers');

        $total_deleted = 0;
        $data = [
            'resp_code' => 'ERR',
            'resp_desc' => 'No valid action performed',
        ];

        if ($this->input->post()) {
            $ids = $this->input->post('ids');

            // Validate if IDs are present
            if (empty($ids) || !is_array($ids)) {

                $data = [
                    'resp_code' => 'ERR',
                    'resp_desc' => "No valid IDs selected.",
                ];
                echo json_encode($data);
                die;
            }

            // Handle Mass Delete
            if ($this->input->post('mass_delete') == "true") {

                foreach ($ids as $id) {
                    if ($this->clients_model->delete($id)) {
                        $total_deleted++;
                    }
                }
                $data = [
                    'resp_code' => 'RCS',
                    'resp_desc' => _l('total_clients_deleted', $total_deleted),
                ];
                set_alert('success', _l('total_clients_deleted', $total_deleted));
                echo json_encode($data);
                die;
            } else if ($this->input->post('apostille_status') == "true") {

                $documents_id = $this->input->post('apostille_document') ?? [];
                $document_cost = $this->input->post('document_cost') ?? [];

                // Required fields
                $vendor_id = $this->input->post('apostille_vendor');
                $courier_date = $this->input->post('apostille_date');
                $receiving_date = $this->input->post('apostille_receiving_date');
                $payment_date = $this->input->post('apostille_payment_date');

                // Validate essential inputs
                // if (empty($ids) || empty($documents_id) || empty($vendor_id) || empty($courier_date)) {
                //     $data = [
                //         'resp_code' => 'ERR',
                //         'resp_desc' => 'Missing required fields: applicants, documents, vendor, or courier date.',
                //     ];
                //     echo json_encode($data);
                //     exit;
                // }

                // Get original document data and validate
                $check_status = 1;
                if (!empty($courier_date) && !empty($documents_id)) {
                    $check_status = 1; // insert new apostile data 
                } else {
                    $check_status = 2; // update apostile data 
                }

                // else if (empty($courier_date) && empty($documents_id) && (!empty($receiving_date) || !empty($payment_date))) {
                //     $check_status = 2; // update apostile data 
                // }
                $get_data_from_document = get_orignal_document_data_list_apostille($ids, $documents_id, $check_status, $vendor_id);

                if (isset($get_data_from_document["error"]) && $get_data_from_document["error"] == 1) {
                    $data = [
                        'resp_code' => 'ERR',
                        'resp_desc' => $get_data_from_document["message"],
                    ];
                    echo json_encode($data);
                    exit;
                }
                if ($check_status == 1) {
                    $insert_apostille_data = [];
                    $activity_data = [];
                    foreach ($ids as $applicant_id) {
                        foreach ($documents_id as $doc_id) {
                            // Validate document cost
                            if (!isset($document_cost[$doc_id]) || !is_numeric($document_cost[$doc_id])) {
                                // $data = [
                                //     'resp_code' => 'ERR',
                                //     'resp_desc' => "Invalid or missing cost for document ID: $doc_id",
                                // ];
                                // echo json_encode($data);
                                // exit;
                            }

                            $insert_apostille_data[] = [
                                "userid" => $applicant_id,
                                "vendor_id" => $vendor_id,
                                "doc_id" => $doc_id,
                                "courier_date" => $courier_date,
                                "apostille_received" => $receiving_date,
                                "apostille_cost" => $document_cost[$doc_id],
                                "payment_date" => $payment_date,
                                "created_at" => date('Y-m-d H:i:s'),
                                "created_by" => get_staff_user_id(),
                                "received_status" => !empty($receiving_date) ? 1 : 0,
                            ];
                            $doc_name = !empty($apostille_documents[$doc_id]['name']) ? $apostille_documents[$doc_id]['name'] : 'Unknown Document';
                            $cost = !empty($document_cost[$doc_id]) ? " with cost ₹{$document_cost[$doc_id]}" : '';
                            $vendor = !empty($apostille_vendors[$vendor_id]['name']) ? ", vendor: {$apostille_vendors[$vendor_id]['name']}" : '';
                            $courier = !empty($courier_date) ? ", courier date: {$courier_date}" : '';
                            $received = !empty($receiving_date) ? ", receiving date: {$receiving_date}" : '';
                            $payment = !empty($payment_date) ? ", payment date: {$payment_date}" : '';

                            $message = "Sent apostille for document \"{$doc_name}\"{$cost}{$vendor}{$courier}{$received}{$payment}.";

                            $activity_data[] = [
                                "date" => date('Y-m-d H:i:s'),
                                "staffid" => get_staff_user_id(),
                                "client_id" => $applicant_id,
                                "description" => $message
                            ];
                        }
                    }

                    // Insert into DB
                    if (!empty($insert_apostille_data)) {
                        $inserted = $this->db->insert_batch(db_prefix() . "client_apostille_data", $insert_apostille_data);
                        if ($inserted) {

                            $this->db->insert_batch(db_prefix() . 'apostille_document_activity', $activity_data);
                            $data = [
                                'resp_code' => 'RCS',
                                'resp_desc' => 'Apostille document bulk updated successfully.',
                            ];
                        } else {
                            $data = [
                                'resp_code' => 'ERR',
                                'resp_desc' => 'Database insert failed.',
                            ];
                        }
                    }

                    echo json_encode($data);
                    exit;
                } else if ($check_status == 2) {

                    $update_apostille_data = [];
                    $activity_data = [];
                    foreach ($get_data_from_document as $rec_apostille) {

                        $row = [
                            "id" => $rec_apostille["id"],
                            "updated_at" => date('Y-m-d H:i:s'),
                            "updated_by" => get_staff_user_id(),
                        ];

                        if (!empty($receiving_date)) {
                            $row["apostille_received"] = $receiving_date;
                            $row["received_status"] = 1;
                        }

                        if (!empty($document_cost[$rec_apostille['doc_id']])) {
                            $row["apostille_cost"] = $document_cost[$rec_apostille['doc_id']];
                        }

                        if (!empty($payment_date)) {
                            $row["payment_date"] = $payment_date;
                        }

                        if (!empty($courier_date)) {
                            $row["courier_date"] = $courier_date;
                        }

                        $update_apostille_data[] = $row;


                        $doc_name = !empty($apostille_documents[$rec_apostille['doc_id']]['name']) ? $apostille_documents[$rec_apostille['doc_id']]['name'] : 'Unknown Document';
                        $cost = !empty($document_cost[$rec_apostille['doc_id']]) ? " with cost ₹{$document_cost[$rec_apostille['doc_id']]}" : '';
                        $vendor = !empty($apostille_vendors[$vendor_id]['name']) ? ", vendor: {$apostille_vendors[$vendor_id]['name']}" : '';
                        $courier = !empty($courier_date) ? ", courier date: {$courier_date}" : '';
                        $received = !empty($receiving_date) ? ", receiving date: {$receiving_date}" : '';
                        $payment = !empty($payment_date) ? ", payment date: {$payment_date}" : '';

                        $message = "Update apostille for document \"{$doc_name}\"{$cost}{$vendor}{$courier}{$received}{$payment}.";

                        $activity_data[] = [
                            "date" => date('Y-m-d H:i:s'),
                            "staffid" => get_staff_user_id(),
                            "client_id" => $rec_apostille["userid"],
                            "description" => $message
                        ];
                    }

                    // Perform batch update
                    if (!empty($update_apostille_data)) {
                        $updated = $this->db->update_batch(db_prefix() . "client_apostille_data", $update_apostille_data, "id");
                        $this->db->insert_batch(db_prefix() . 'apostille_document_activity', $activity_data);
                        if ($updated) {
                            $data = [
                                'resp_code' => 'RCS',
                                'resp_desc' => 'Apostille document bulk updated successfully.',
                            ];
                        } else {
                            $data = [
                                'resp_code' => 'ERR',
                                'resp_desc' => 'Apostille document update failed.',
                            ];
                        }

                        echo json_encode($data);
                        die;
                    }
                } else {
                    $data = [
                        'resp_code' => 'ERR',
                        'resp_desc' => 'Appostile data not updated.',
                    ];
                }
            } else if ($this->input->post('visa_status') == "true") {

                // Required fields
                $vendor_id = $this->input->post('visa_vendor');
                $courier_date = $this->input->post('visa_date');
                $visa_courier_type = $this->input->post('visa_courier_type');
                $visa_payment_date = $this->input->post('visa_payment_date');
                $visa_cost = $this->input->post('visa_cost');
                $visa_payment_mode = $this->input->post('visa_payment_mode');


                $check_status = 1;
                if (!empty($courier_date)) {
                    $check_status = 1; // insert new apostile data 
                } else {
                    $check_status = 2; // update apostile data 
                }



                $get_data_from_document = get_orignal_document_data_list_visa($ids, $check_status, $vendor_id);

                if ((isset($get_data_from_document["error"]) && $get_data_from_document["error"] == 1) || (isset($get_data_from_document["resp_code"]) && $get_data_from_document["resp_code"] == "ERR")) {
                    $data = [
                        'resp_code' => 'ERR',
                        'resp_desc' => !empty($get_data_from_document["resp_desc"])
                            ? $get_data_from_document["resp_desc"]
                            : (!empty($get_data_from_document["message"])
                                ? $get_data_from_document["message"]
                                : 'Error message'),

                    ];
                    echo json_encode($data);
                    exit;
                }

                if ($check_status == 1) {
                    $insert_visa_data = [];
                    $activity_data = [];
                    foreach ($ids as $applicant_id) {



                        $insert_visa_data[] = [
                            "userid" => $applicant_id,
                            "vendor_id" => $vendor_id,
                            "courier_date" => $courier_date,
                            "payment_mode" => $visa_payment_mode,
                            "cost" => !empty($visa_cost) ? $visa_cost : "",
                            "courier_type" => !empty($visa_courier_type) ? $visa_courier_type : '',
                            "payment_date" => $visa_payment_date,
                            "status" => 1,
                            " bulk" => 1,
                            "created_at" => date('Y-m-d H:i:s'),
                            "created_by" => get_staff_user_id(),
                            "received_status" => !empty($receiving_date) ? 1 : 0,
                        ];

                        $cost = !empty($visa_cost) ? " with cost ₹{$visa_cost}" : '';
                        $vendor = !empty($apostille_vendors[$vendor_id]['name']) ? ", vendor: {$apostille_vendors[$vendor_id]['name']}" : '';
                        $courier = !empty($courier_date) ? ", courier date: {$courier_date}" : '';
                        $payment = !empty($payment_date) ? ", payment date: {$payment_date}" : '';

                        $message = "Apply Visa details \"{$cost}{$vendor}{$courier}{$payment}.";

                        $activity_data[] = [
                            "date" => date('Y-m-d H:i:s'),
                            "staffid" => get_staff_user_id(),
                            "client_id" => $applicant_id,
                            "description" => $message
                        ];
                    }

                    // Insert into DB
                    if (!empty($insert_visa_data)) {
                        $inserted = $this->db->insert_batch(db_prefix() . "visa_details", $insert_visa_data);
                        if ($inserted) {

                            $this->db->insert_batch(db_prefix() . 'visa_document_activity', $activity_data);
                            $data = [
                                'resp_code' => 'RCS',
                                'resp_desc' => 'Visa bulk updated successfully.',
                            ];
                        } else {
                            $data = [
                                'resp_code' => 'ERR',
                                'resp_desc' => 'Database insert failed.',
                            ];
                        }
                    }

                    echo json_encode($data);
                    exit;
                } else if ($check_status == 2) {


                    $update_visa_data = [];
                    $activity_data = [];
                    foreach ($get_data_from_document as $rec_visa) {

                        $row = [
                            "id" => $rec_visa["id"],
                            "updated_at" => date('Y-m-d H:i:s'),
                            "updated_by" => get_staff_user_id(),
                        ];

                        if (!empty($visa_cost)) {
                            $row["cost"] = $visa_cost;
                        }

                        if (!empty($visa_payment_date)) {
                            $row["payment_date"] = $visa_payment_date;
                        }

                        if (!empty($courier_date)) {
                            $row["courier_date"] = $courier_date;
                        }

                        if (!empty($visa_payment_mode)) {
                            $row["payment_mode"] = $visa_payment_mode;
                        }

                        if (!empty($visa_courier_type)) {
                            $row["courier_type"] = $visa_courier_type;
                        }
                        $update_visa_data[] = $row;


                        // $doc_name = !empty($apostille_documents[$rec_visa['doc_id']]['name']) ? $apostille_documents[$rec_visa['doc_id']]['name'] : 'Unknown Document';
                        $cost = !empty($visa_cost) ? " with cost ₹{$visa_cost}" : '';
                        $vendor = !empty($apostille_vendors[$vendor_id]['name']) ? ", vendor: {$apostille_vendors[$vendor_id]['name']}" : '';
                        $courier = !empty($courier_date) ? ", courier date: {$courier_date}" : '';
                        $payment = !empty($visa_payment_date) ? ", payment date: {$visa_payment_date}" : '';

                        $message = "Update Visa details \"{$cost}{$vendor}{$courier}{$payment}.";

                        $activity_data[] = [
                            "date" => date('Y-m-d H:i:s'),
                            "staffid" => get_staff_user_id(),
                            "client_id" => $rec_visa["userid"],
                            "description" => $message
                        ];
                    }

                    // Perform batch update
                    if (!empty($update_visa_data)) {
                        $updated = $this->db->update_batch(db_prefix() . "visa_details", $update_visa_data, "id");
                        $this->db->insert_batch(db_prefix() . 'visa_document_activity', $activity_data);
                        if ($updated) {
                            $data = [
                                'resp_code' => 'RCS',
                                'resp_desc' => 'Visa Details bulk updated successfully.',
                            ];
                        } else {
                            $data = [
                                'resp_code' => 'ERR',
                                'resp_desc' => 'Visa details update failed.',
                            ];
                        }

                        echo json_encode($data);
                        die;
                    }
                } else {
                    $data = [
                        'resp_code' => 'ERR',
                        'resp_desc' => 'Visa data not updated.',
                    ];
                }
            } else if (
                ($this->input->post('in_transit') === true ||
                    (empty($this->input->post('office_location')) && empty($this->input->post('document_status')))) ||
                (!empty($this->input->post('office_location')))
            ) {

                $get_data_from_document = get_orignal_document_data_list($ids);



                if (!empty($get_data_from_document["error"]) && $get_data_from_document["error"] === true) {
                    $data['resp_desc'] = $get_data_from_document["message"];
                    $data['resp_code'] = "ERR";
                    set_alert('danger', $get_data_from_document["message"]);
                } else {

                    foreach ($get_data_from_document as $document) {
                        $update_data = [];
                        $count = count(explode(",", $document["document_ids"]));

                        $update_data["userid"] = $document["userid"];
                        $update_data["document_ids"] = explode(",", $document["document_ids"]);
                        $update_data["document_name"] = explode(",", $document["document_names"]);
                        $update_data["received_id"] = explode(",", $document["received_id"]);
                        $update_data["status_text"] = $this->input->post('status_text');
                        $update_data["in_transit"] = $this->input->post('in_transit');
                        $update_data["transit_location"] = $this->input->post('from_location') . " - " . $this->input->post('to_location');

                        // Get locations and locations_name from input
                        $location = $this->input->post('office_location');
                        $location_name = $this->input->post('locations_name');

                        // Repeat locations and locations_name to match document count
                        $update_data["locations"] = array_fill(0, $count, $location);
                        $update_data["locations_name"] = array_fill(0, $count, $location_name);


                        $response =  $this->clients_model->update_documents($update_data, $document["userid"]);
                    }

                    $data = [
                        'resp_code' => 'RCS',
                        'resp_desc' => 'Original document bulk update successfully',
                    ];
                    set_alert('success', "Original document bulk update successfully");
                }
                echo json_encode($data);
                die;
            }
        }

        echo json_encode($data);
    }


    public function vault_entry_create($customer_id)
    {
        $data = $this->input->post();

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        unset($data['id']);
        $data['creator']      = get_staff_user_id();
        $data['creator_name'] = get_staff_full_name($data['creator']);
        $data['description']  = nl2br($data['description']);
        $data['password']     = $this->encryption->encrypt($this->input->post('password', false));

        if (empty($data['port'])) {
            unset($data['port']);
        }

        $this->clients_model->vault_entry_create($data, $customer_id);
        set_alert('success', _l('added_successfully', _l('vault_entry')));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_update($entry_id)
    {
        $entry = $this->clients_model->get_vault_entry($entry_id);

        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $data = $this->input->post();

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $data['last_updated_from'] = get_staff_full_name(get_staff_user_id());
            $data['description']       = nl2br($data['description']);

            if (!empty($data['password'])) {
                $data['password'] = $this->encryption->encrypt($this->input->post('password', false));
            } else {
                unset($data['password']);
            }

            if (empty($data['port'])) {
                unset($data['port']);
            }

            $this->clients_model->vault_entry_update($entry_id, $data);
            set_alert('success', _l('updated_successfully', _l('vault_entry')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_delete($id)
    {
        $entry = $this->clients_model->get_vault_entry($id);
        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $this->clients_model->vault_entry_delete($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_encrypt_password()
    {
        $id            = $this->input->post('id');
        $user_password = $this->input->post('user_password', false);
        $user          = $this->staff_model->get(get_staff_user_id());

        if (!app_hasher()->CheckPassword($user_password, $user->password)) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error_msg' => _l('vault_password_user_not_correct')]);
            die;
        }

        $vault    = $this->clients_model->get_vault_entry($id);
        $password = $this->encryption->decrypt($vault->password);

        $password = html_escape($password);

        // Failed to decrypt
        if (!$password) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
            echo json_encode(['error_msg' => _l('failed_to_decrypt_password')]);
            die;
        }

        echo json_encode(['password' => $password]);
    }

    public function get_vault_entry($id)
    {
        $entry = $this->clients_model->get_vault_entry($id);
        unset($entry->password);
        $entry->description = clear_textarea_breaks($entry->description);
        echo json_encode($entry);
    }

    public function statement_pdf()
    {
        $customer_id = $this->input->get('customer_id');

        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        try {
            $pdf = statement_pdf($data['statement']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(slug_it(_l('customer_statement') . '-' . $data['statement']['client']->company) . '.pdf', $type);
    }

    public function send_statement()
    {
        $customer_id = $this->input->get('customer_id');

        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        $send_to = $this->input->post('send_to');
        $cc      = $this->input->post('cc');

        $success = $this->clients_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('statement_sent_to_client_success'));
        } else {
            set_alert('danger', _l('statement_sent_to_client_fail'));
        }

        redirect(admin_url('clients/client/' . $customer_id . '?group=statement'));
    }

    public function statement()
    {
        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
            echo _l('access_denied');
            die;
        }

        $customer_id = $this->input->get('customer_id');
        $from        = $this->input->get('from');
        $to          = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        $data['from'] = $from;
        $data['to']   = $to;

        $viewData['html'] = $this->load->view('admin/clients/groups/_statement', $data, true);

        echo json_encode($viewData);
    }

    // UPDATE ADMISSION PREFERENCES
    public function update_admission_preferences()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $params = $this->input->post();
            $client_id = $params['client_id'];
            $primary_university = !empty($params['primary_university']) ? $params['primary_university'] : '';
            $primary_country = !empty($params['primary_country']) ? $params['primary_country'] : '';
            $dataArr = [
                'program' => !empty($params['program']) ? $params['program'] : '',
                'course' => $params['course'],
                'entrance_exam_details' => (!empty($params['entranceExamDetails'])) ? implode(",", $params['entranceExamDetails']) : '',
                'session_intake' => !empty($params['sessionIntake']) ? $params['sessionIntake'] : '',
                'acadmic_year' => !empty($params['acadmic_year']) ? $params['acadmic_year'] : '',
                'userid' => $params['client_id'],
                'course_name' => !empty($params['course_name']) ? $params['course_name'] : '',
            ];

            if ($params['countries'] != "") {
                $dataArr['study_country'] = $params['countries'];
                $dataArr['university'] = json_encode($params['universities'], true);
            }

            $admissionPreferencesId = $this->clients_model->addAdmissionPreferences($dataArr, $params['admissionPreferencesId']);

            if (!empty($primary_university) && !empty($primary_country)) {
                $update = $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'admission_preferences', array("primary_university" => $primary_university, "primary_country" => $primary_country));
            }

            $check_client = $this->db->select('tracker_id')
                ->where('userid', $client_id)
                ->get(db_prefix() . 'clients')
                ->row();




            if (empty($check_client->tracker_id) && $check_client->tracker_id == 0) {
            } else {
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => UNIVERSITY_SHORTLISTING, "applicant_sub_status" => UNIVERSITY_SHORTLISTING_PENDING, "tracker_id" => 1));
            }


            if ($admissionPreferencesId) {
                if (!empty($params['admissionPreferencesId'])) {
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Admission Preferences Information Updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                } else {
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Admission Preferences Information Created by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                }
                applicant_last_update($client_id);
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = 'Admission Preferences successfully updated';
                $data['resp_id'] = $admissionPreferencesId;
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Request failed!!';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    // FREEZE ADMISSION PREFERENCES

    public function freeze_admission_preferences()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $params = $this->input->post();
            $admission_preferences = $this->clients_model->getAdmissionPreferencesDetails($params['admissionPreferencesId']);
            $freeze = $admission_preferences->freeze == 0 ? 1 : 0;
            $freeze_text = $admission_preferences->freeze == 0 ? 'freezed' : 'unfreezed';
            $dataArr = [
                'freeze' => $freeze,
            ];

            $admissionPreferencesId = $this->clients_model->addAdmissionPreferences($dataArr, $params['admissionPreferencesId']);
            if ($admissionPreferencesId) {
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = 'Admission Preferences ' . $freeze_text . ' successfully';
                $data['data'] = array(
                    'is_freezed' => $freeze
                );
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Request failed!!';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function documents_approval()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $doc_id = $this->input->post("doc_id");
            $client_id = $this->input->post("client_id");
            $status = $this->input->post("status");
            $document_data =  $this->db->select("id,data");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_documents')->row();
            if (!empty($check_->id)) {
                // Ensure $check_->data is valid JSON
                $already_data = json_decode($check_->data, true);

                $documents_type =  get_documents("", [], 1);
                $documents_type =  array_column($documents_type, null, 'id');
                $already_data = array_column($already_data, null, "id"); // Make it associative with 'id' as key




                if (empty($status)) {
                    if (isset($already_data[$doc_id])) {
                        unset($already_data[$doc_id]); // Remove the entry by doc_id
                    }
                    $update = $this->db->where("id", $check_->id);
                    $this->db->update(db_prefix() . 'client_documents', array("data" => json_encode($already_data, true)));
                    $rows_affected = $this->db->affected_rows();
                    if ($rows_affected > 0) {
                        $update_client_data = [
                            "applicant_status" => 0,
                            "applicant_stage" => DOCUMENT,
                            "applicant_sub_status" => DOCUMENT_APPROVAL_PENDING,
                        ];
                        if (!empty($update_client_data)) {
                            $this->db->where("userid", $client_id);
                            $this->db->update(db_prefix() . 'clients', $update_client_data);
                        }
                        applicant_last_update($client_id);
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = "Document delete successfully";
                        set_alert('success', "Document delete successfully");
                    } else {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = "Some this went wrong delete data";
                        set_alert('danger', "Some this went wrong delete data");
                    }
                    echo json_encode($data);
                    die;
                }

                if (!empty($already_data[$doc_id])) {
                    $already_data[$doc_id]["approval_date"] = date('Y-m-d H:i:s');
                    $already_data[$doc_id]["approval_status"] = $status;
                    $already_data[$doc_id]["approval_by"] = get_staff_user_id();
                    $doc_name = $documents_type[$doc_id]["name"];
                    $status_name =  !empty($status) && $status == 1 ? 'Approved' : 'Reject';
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "{$doc_name} document {$status_name} by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));

                    // $client = $this->clients_model->getBasicDetails($client_id);

                    // if (!empty($client->email)) {
                    //     send_mail_template('Applicant_document_reject', $client->email, $client_id, get_staff_user_id(), get_staff_user_id(), $doc_id);
                    // }

                    // Fetch client information by client ID
                    $clientInformation = $this->clients_model->get($client_id);
                    if ($status == 2) {                    // Check if client information is found
                        if ($clientInformation && isset($clientInformation->addedfrom)) {

                            // Fetch staff details using client user ID
                            $staffDetails = $this->staff_model->get($clientInformation->addedfrom);
                            // Check if staff details and staff email are valid
                            if ($staffDetails && !empty($staffDetails->email)) {

                                // Send rejection email using the template
                                send_mail_template(
                                    'Applicant_document_reject',
                                    $staffDetails->email,
                                    $client_id,
                                    get_staff_user_id(),
                                    get_staff_user_id(),
                                    $doc_id
                                );
                            } else {
                                log_message('error', 'Staff details not found or email missing for client ID: ' . $client_id);
                            }
                        } else {
                            log_message('error', 'Client information not found for client ID: ' . $client_id);
                        }
                    }
                }

                $update = $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_documents', array("data" => json_encode($already_data, true)));
                $rows_affected = $this->db->affected_rows();
                if ($rows_affected > 0) {
                    applicant_last_update($client_id);
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Document update successfully";
                    set_alert('success', "Document update successfully");
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Some this went wrong update data";
                    set_alert('danger', "Some this went wrong update data");
                }
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function upload_documents()
    {

        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $doc_ids = $this->input->post("doc_type_id");
            $doc_names = $this->input->post("doc_type_name");
            $document_url = $this->input->post("doc_url");
            $documents_type =  get_documents("", [], 1);
            $documents_type =  array_column($documents_type, null, 'id');

            $update_array = [];
            $client_id = $this->input->post("clientid");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;

            $this->db->select("data");
            $this->db->where('client_id', $client_id);
            $already_data = $this->db->get(db_prefix() . 'client_documents')->row();


            if (!empty($already_data->data)) {
                // Ensure $check_->data is valid JSON
                $already_data = json_decode($already_data->data, true);
                $already_data = array_column($already_data, null, "id");
            }


            for ($i = 0; $i < count($doc_ids); $i++) {
                $documents = $_FILES["files_" . $doc_ids[$i]];

                $upload_data = [];
                $file_name_ = str_replace(" ", "-", $doc_names[$i]);
                if (!empty($documents['name'])) {
                    $upload_data["name"] =  $file_name_ . "." . pathinfo($documents['name'], PATHINFO_EXTENSION);
                    $upload_data["type"] = $documents['type'];
                    $upload_data["tmp_name"] = $documents['tmp_name'];
                    $upload_data["error"] = $documents['error'];
                    $upload_data["size"] = $documents['size'];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {;
                        $file_name = upload_applicant_documents($client_id, $upload_data);
                        array_push($update_array, array("id" => $doc_ids[$i], "document_file" => $file_name["file_path"], "updated_by" => get_staff_user_id(), "updated_date" => date('Y-m-d H:i:s')));
                        $doc_name = $documents_type[$doc_ids[$i]]["name"];

                        $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "{$doc_name} document uploaded by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                    }
                } else if (!empty($document_url[$i])) {
                    if (!empty($already_data[$doc_ids[$i]])) {
                        array_push($update_array, $already_data[$doc_ids[$i]]);
                    }
                }
            }


            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_documents')->row();

            if (!empty($check_->id)) {

                $_update_data = array(
                    "data" => json_encode($update_array, true),
                    "updated_date" => date('Y-m-d H:i:s'),
                    "document_status" => 0,
                    "document_update_datetime" => date('Y-m-d H:i:s'),
                    "updated_by" => get_staff_user_id()
                );
                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_documents', $_update_data);

                $rows_affected = $this->db->affected_rows();
                if (isset($applicant_status)) {
                    // $this->db->where("userid", $client_id);
                    // $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status, "applicant_stage" => 2, "applicant_sub_status" => 6));
                    // get_applicant_status($applicant_status, $client_id);
                }
                if ($rows_affected > 0) {
                    $this->db->where("userid", $client_id);
                    $this->db->where("userid", $client_id);




                    // $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => DOCUMENT, "applicant_sub_status" => DOCUMENT_APPROVAL_PENDING, "tracker_id" => 0));



                    applicant_last_update($client_id);
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_document_successfully', _l('client'));
                    set_alert('success', _l('update_client_document_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_document_failed', _l('client'));
                    set_alert('danger', _l('update_client_document_failed', _l('client')));
                }
            } else {

                if (!empty($update_array)) {
                    $insert_update_data = array(
                        "client_id" => $client_id,
                        "data" => json_encode($update_array, true),
                        "status" => 1,
                        "created_date" => date('Y-m-d H:i:s'),
                        "created_by" => get_staff_user_id()
                    );
                    $insert_id =   $this->db->insert(db_prefix() . 'client_documents', $insert_update_data);
                    $insert_id =   $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Document upload by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));

                    if ($insert_id) {
                        if (isset($applicant_status)) {
                            $this->db->where("userid", $client_id);
                            $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                        }
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = _l('update_client_document_successfully', _l('client'));
                        set_alert('success', _l('update_client_document_successfully', _l('client')));
                    } else {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = _l('update_client_document_failed', _l('client'));
                        set_alert('danger', _l('update_client_document_failed', _l('client')));
                    }
                }
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function welcome_configuration()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                $client_id = $this->input->post("clientid");
                $update_student_data = [];
                $_update = [];

                foreach ($_FILES as $key => $files) {
                    $documents = $files;
                    $file_name_ = $key;
                    if (!empty($documents['name'])) {
                        $upload_data["name"] =  $file_name_ . "." . pathinfo($documents['name'], PATHINFO_EXTENSION);
                        $upload_data["type"] = $documents['type'];
                        $upload_data["tmp_name"] = $documents['tmp_name'];
                        $upload_data["error"] = $documents['error'];
                        $upload_data["size"] = $documents['size'];
                        if ($upload_data["error"] === UPLOAD_ERR_OK) {;
                            $file_name = upload_applicant_documents($client_id, $upload_data);
                            $_update[$key] = $file_name["file_path"];
                        }
                    }
                }


                $_update["date_of_payment"] = !empty($_POST["date_of_payment"]) ? $_POST["date_of_payment"] : '';
                $_update["registration_slip_cash_status"] = !empty($_POST["registration_slip_cash_status"]) ? $_POST["registration_slip_cash_status"] : '';
                $_update["payment_recevied_from"] = !empty($_POST["payment_recevied_from"]) ? $_POST["payment_recevied_from"] : '';
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $_update);

                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Welcome message data updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                applicant_last_update($client_id);
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Welcome Information update successfully";
                set_alert('success', "Welcome Information update successfully");
            } catch (Exception $e) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function final_submitted()
    {

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                $client_id = $this->input->post("clientid");
                $update_student_data = [];
                $_update = [];

                $_update["submission_status"] = !empty($_POST["submition_status"]) ? $_POST["submition_status"] : '';
                $_update["submission_date"] = date('Y-m-d H:i:s');

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $_update);

                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Registration complete successfully";
                set_alert('success', "Registration complete successfully");
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => DOCUMENT, "applicant_sub_status" => DOCUMENT_APPROVAL_PENDING));

                $this->db->insert(db_prefix() . 'application_activity_log', array(
                    "description" => "Registration process completed and submitted by - ",
                    "date" => date('Y-m-d H:i:s'),
                    "staffid" => get_staff_user_id(),
                    "client_id" => $client_id
                ));

                // $client = $this->clients_model->getBasicDetails($client_id);
                applicant_last_update($client_id);
                $generate_registration_slip =  $this->registration_slip_preview($client_id);
            } catch (Exception $e) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }


    public function update_email_creation()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email_creation = !empty($this->input->post("email_creation")) ? $this->input->post("email_creation") : '';
            $vendor = !empty($this->input->post("vendor")) ? $this->input->post("vendor") : '';
            $sop = !empty($_FILES["sop"]) ? $_FILES["sop"] : '';
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;

            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_profile_creation')->row();

            if ($check_) {
                $_update = array(
                    "email" => $email_creation,
                    "email_updated_date" => date('Y-m-d H:i:s'),
                    "email_updated_by" => get_staff_user_id()
                );
                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_profile_creation', $_update);
                $rows_affected = $this->db->affected_rows();

                if ($rows_affected > 0) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_email_successfully', _l('client'));
                    set_alert('success', _l('update_client_email_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_email_failed', _l('client'));
                    set_alert('danger', _l('update_client_email_failed', _l('client')));
                }
            } else {
                $insert_update_data = array(
                    "client_id" => $client_id,
                    "email" => $email_creation,
                    "status" => 1,
                    "created_date" => date('Y-m-d H:i:s'),
                    "created_by" => get_staff_user_id()
                );

                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Profile email updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));

                if (isset($applicant_status)) {
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                    get_applicant_status($applicant_status, $client_id);
                }
                $this->db->insert(db_prefix() . 'client_profile_creation', $insert_update_data);
                $insert_id = $this->db->insert_id();

                if ($insert_id) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_email_successfully', _l('client'));
                    set_alert('success', _l('update_client_email_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_email_failed', _l('client'));
                    set_alert('danger', _l('update_client_email_failed', _l('client')));
                }
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_vendor()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $email_creation = !empty($this->input->post("email_creation")) ? $this->input->post("email_creation") : '';
            $vendor = !empty($this->input->post("vendor")) ? $this->input->post("vendor") : '';
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;
            // $sop = !empty($_FILES["sop"]) ? $_FILES["sop"] : '';
            $client_id = $this->input->post("client_id");
            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_profile_creation')->row();

            if ($check_) {
                $_update = array(
                    "vendor" => $vendor,
                    "vendor_updated_date" => date('Y-m-d H:i:s'),
                    "vendor_updated_by" => get_staff_user_id()
                );
                $_update["profile_status"] = 0;
                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_profile_creation', $_update);
                $rows_affected = $this->db->affected_rows();

                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Profile vendor selected updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));


                if (isset($applicant_status)) {
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                    get_applicant_status($applicant_status, $client_id);
                }
                if ($rows_affected > 0) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_vendor_successfully', _l('client'));
                    set_alert('success', _l('update_client_vendor_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_vendor_failed', _l('client'));
                    set_alert('danger', _l('update_client_vendor_failed', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'First create email then update vendor.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_sop()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sop_document = !empty($_FILES["sop_document"]) ? $_FILES["sop_document"] : '';
            $document_url = !empty($_FILES["document_url"]) ? $_FILES["document_url"] : '';
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;

            $client_id = $this->input->post("client_id");
            $this->db->select("id,email,vendor");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_profile_creation')->row();

            if ($check_) {
                if (empty($sop_document) && empty($document_url)) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Sop file is requried.';
                    echo json_encode($data);
                }
                if (empty($check_->vendor)) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'First select vendor type.';
                    echo json_encode($data);
                }

                $_update = array(
                    // "sop" => $sop_document,
                    "sop_updated_date" => date('Y-m-d H:i:s'),
                    "sop_updated_by" => get_staff_user_id()
                );
                if (!empty($sop_document)) {
                    $file = upload_applicant_documents($client_id, $sop_document, APPLICANT_UPLOAD_SOP_DOCUMENT_PATH, APPLICANT_UPLOAD_SOP_DOCUMENT);
                    if (!empty($file["file_path"])) {
                        $_update["sop"] = $file["file_path"];
                    }
                } elseif (!empty($document_url)) {
                    $_update["sop"] = $document_url;
                }

                $_update["profile_status"] = 0;

                if (isset($applicant_status)) {

                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                }
                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_profile_creation', $_update);
                $rows_affected = $this->db->affected_rows();


                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Profile sop updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));

                get_applicant_status($applicant_status, $client_id);

                if ($rows_affected > 0) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_sop_successfully', _l('client'));
                    set_alert('success', _l('update_client_sop_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_sop_failed', _l('client'));
                    set_alert('danger', _l('update_client_sop_failed', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'First create email then update vendor.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_profile_data()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = !empty($this->input->post("client_id")) ? $this->input->post("client_id") : '';
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;
            $this->db->select("id,email,vendor,sop");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_documents')->row();

            if (!empty($check_->id)) {
                if (empty($check_->email)) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Please add email first';
                } elseif (empty($check_->vendor)) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Please select vendor first.';
                } elseif (empty($check_->sop)) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Please upload sop document.';
                } else {
                    if (isset($applicant_status)) {
                        $applicant_status_text = "";
                        if ($applicant_status == 1) {
                            $applicant_status_text = "Approved";
                        } else {
                            $applicant_status_text = "Reject";
                        }
                        $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Profile " . $applicant_status_text . " by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                        get_applicant_status($applicant_status, $client_id);
                    }
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'First create email then update vendor.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }
        echo json_encode($data);
    }

    public function update_document_verification_status()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $document_status = $this->input->post("document_status");


            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_documents')->row();

            if ($check_) {
                if (empty($document_status) && empty($document_status)) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Sop file is requried.';
                    echo json_encode($data);
                }

                $_update = array(
                    "document_status" => $document_status,
                    "document_update_datetime" => date('Y-m-d H:i:s'),
                    "document_updated_by" => get_staff_user_id()
                );



                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_documents', $_update);
                $rows_affected = $this->db->affected_rows();


                $document_status_text = "";
                if ($document_status == 1) {
                    $document_status_text = "Approved";
                    // $this->db->where("userid", $client_id);
                    // $this->db->update(db_prefix() . 'clients', array("applicant_status" => 1, "applicant_stage" => 2, "applicant_sub_status" => 7));
                    get_applicant_status(1, $client_id);
                    // $this->mbbs_update_university($client_id);

                } else if ($document_status == 2) {
                    $document_status_text = "Reject";
                    // $this->db->where("userid", $client_id);
                    // $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => 2, "applicant_sub_status" => 8));

                    get_applicant_status(0, $client_id);
                }

                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Document " . $document_status_text . " by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));


                if ($rows_affected > 0) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_doc_status_successfully', _l('client'));
                    set_alert('success', _l('update_client_doc_status_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_doc_status_failed', _l('client'));
                    set_alert('danger', _l('update_client_doc_status_failed', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something bad happen.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_profile()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;

            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_profile_creation')->row();
            if ($check_) {
                if (isset($applicant_status)) {

                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                    $rows_affected = $this->db->affected_rows();
                    get_applicant_status($applicant_status, $client_id);

                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_profile_status_successfully', _l('client'));
                    set_alert('success', _l('update_client_profile_status_successfully', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something bad happen.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function mbbs_update_university($client_id)
    {
        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //         $admissionpreferences = $this->clients_model->getAdmissionPreferences($client_id);
        //         $university_shortlisting = !empty($admissionpreferences->university) ? json_decode($admissionpreferences->university, true) : [];
        //         $university_shortlisting_insert_arr = [];
        //         $university_shortlisting_update_arr = [];
        //         if (!empty($university_shortlisting)) {
        //             $this->db->where('client_id', $client_id);
        //             $this->db->update(db_prefix() . 'client_university_shortlisting', array("status" => 0));
        //             foreach ($university_shortlisting as $university_s) {
        //                 print_r($university_s);
        //                 $this->db->select("id");
        //                 $this->db->where(array('client_id' => $client_id, "university_name" => $university_s));
        //                 $check_ = $this->db->get(db_prefix() . 'client_university_shortlisting')->row();

        //                 if (!empty($check_->id)) {
        //                     array_push($university_shortlisting_update_arr, array("university_name" => $university_s, "status" => 1, "id" => $check_->id, 'updated_by' => get_staff_user_id(), 'updated_date' => date('Y-m-d H:i:s')));
        //                 } else {
        //                     array_push($university_shortlisting_insert_arr, array("client_id" => $client_id, "university_name" => $university_s, "status" => 1, "created_by" => get_staff_user_id(), "created_date" => date('Y-m-d H:i:s')));
        //                 }
        //             }
        //         }


        //         $update_university = "";
        //         if (!empty($university_shortlisting_insert_arr) || !empty($university_shortlisting_update_arr)) {
        //             if (!empty($university_shortlisting_insert_arr)) {
        //                 $update_university =  $this->db->insert_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_insert_arr);
        //             }

        //             if (!empty($university_shortlisting_update_arr)) {
        //                 $update_university = $this->db->update_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_update_arr, "id");
        //             }

        //             $this->db->where("userid", $client_id);
        //             $this->db->update(db_prefix() . 'clients', array("applicant_status" => 4));
        //             $rows_affected = $this->db->affected_rows();

        //             $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "University shortlisted list send to applicant by  - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
        //         }

        //         $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
        //         $ids = array_column($university_shortlisting_data, "id");
        //         if ($update_university) {
        //             $data['resp_code'] = 'RCS';
        //             $data['resp_desc'] = _l('update_custumer_update_successfully', _l('client'));
        //             $data['ids'] = $ids;
        //             $data['university_shortlisting'] = $university_shortlisting_data;
        //             set_alert('success', _l('update_custumer_update_successfully', _l('client')));
        //         } else {
        //             $data['resp_code'] = 'RCS';
        //             $data['resp_desc'] = _l('update_custumer_failed_successfully', _l('client'));
        //             set_alert('danger', _l('update_custumer_failed_successfully', _l('client')));
        //         }

        //     } else {
        //         $data['resp_code'] = 'ERR';
        //         $data['resp_desc'] = 'Something bad happen.';
        //     }
        //     echo json_encode($data);

    }


    public function update_university()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;
            $university_shortlisting = !empty($this->input->post("university_shortlisting")) ? json_decode($this->input->post("university_shortlisting"), true) : [];
            $university_shortlisting_insert_arr = [];
            $university_shortlisting_update_arr = [];

            $admissionpreferences = $this->clients_model->getAdmissionPreferences($client_id);

            if (empty($admissionpreferences->primary_university) ||  empty($admissionpreferences->primary_country)) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something wrong check primary country & University.';
                return $data;
                die;
            }

            $check_primary_university_exist = $this->checkUniversityExists($university_shortlisting, $admissionpreferences->primary_university, $admissionpreferences->primary_country);
            // if($check_primary_university_exist)

            if ($check_primary_university_exist === false) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Primary University Selection is Mandatory.';
                return $data;
                die;
            }
            if (!empty($university_shortlisting)) {
                $this->db->where('client_id', $client_id);
                $this->db->update(db_prefix() . 'client_university_shortlisting', array("status" => 0));
                foreach ($university_shortlisting as $university_s) {
                    $this->db->select("id");
                    $this->db->where(array('client_id' => $client_id, "id" => $university_s["university_id"]));
                    $check_ = $this->db->get(db_prefix() . 'client_university_shortlisting')->row();

                    if (!empty($check_->id)) {
                        array_push($university_shortlisting_update_arr, array("university_name" => $university_s["university"], "vendor_id" => $university_s["vendor"], "status" => 1, "id" => $check_->id, 'updated_by' => get_staff_user_id(), 'updated_date' => date('Y-m-d H:i:s')));
                    } else {
                        array_push($university_shortlisting_insert_arr, array("client_id" => $client_id, "university_name" => $university_s["university"], "vendor_id" => $university_s["vendor"], "status" => 1, "created_by" => get_staff_user_id(), "created_date" => date('Y-m-d H:i:s')));
                    }
                }
            }
            $update_university = "";
            if (!empty($university_shortlisting_insert_arr) || !empty($university_shortlisting_update_arr)) {
                if (!empty($university_shortlisting_insert_arr)) {
                    $update_university =  $this->db->insert_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_insert_arr);
                }

                if (!empty($university_shortlisting_update_arr)) {
                    $update_university = $this->db->update_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_update_arr, "id");
                }

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                $rows_affected = $this->db->affected_rows();

                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "University shortlisted list send to applicant by  - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                get_applicant_status($applicant_status, $client_id);


                $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
                $ids = array_column($university_shortlisting_data, "id");
                if ($update_university) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_custumer_update_successfully', _l('client'));
                    $data['ids'] = $ids;
                    $data['university_shortlisting'] = $university_shortlisting_data;
                    set_alert('success', _l('update_custumer_update_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_custumer_failed_successfully', _l('client'));
                    set_alert('danger', _l('update_custumer_failed_successfully', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something bad happen.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_profile_verification_status()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $document_status = $this->input->post("profile_status");

            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_profile_creation')->row();

            if ($check_) {

                $_update = array(
                    "profile_status" => $document_status,
                    "approved_date" => date('Y-m-d H:i:s'),
                    "approved_by" => get_staff_user_id()
                );
                if ($document_status == 1) {
                    $_update["email_verified"] = 1;
                }
                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_profile_creation', $_update);
                $rows_affected = $this->db->affected_rows();
                if ($document_status == 1) {

                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Profile is Approved by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));

                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => 2));
                    get_applicant_status(2, $client_id);
                    $rows_affected = $this->db->affected_rows();
                } else {
                    get_applicant_status(1, $client_id);
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Profile is Reject by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                }


                if ($rows_affected > 0) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_profile_status_successfully', _l('client'));
                    set_alert('success', _l('update_client_profile_status_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_client_profile_status_failed', _l('client'));
                    set_alert('danger', _l('update_client_profile_status_failed', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something bad happen.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_university_status()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;
            $university_shortlisting_status = !empty($this->input->post("university_shortlisting_status")) ? json_decode($this->input->post("university_shortlisting_status"), true) : [];
            $university_shortlisting_update_arr = [];
            if (!empty($university_shortlisting_status)) {
                foreach ($university_shortlisting_status as $university_s) {
                    array_push($university_shortlisting_update_arr, array("university_submit_status" => $university_s["university_status_submit"], "id" => $university_s["university_id"], 'submited_by' => get_staff_user_id(), 'updated_date' => date('Y-m-d H:i:s'), 'submit_date' => date('Y-m-d H:i:s')));
                }
            }

            $update_university = "";
            if (!empty($university_shortlisting_update_arr)) {
                if (!empty($university_shortlisting_update_arr)) {
                    $update_university = $this->db->update_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_update_arr, "id");
                }

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                $rows_affected = $this->db->affected_rows();
                get_applicant_status($applicant_status, $client_id);
                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Application shortlisting by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));

                $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);




                $ids = array_column($university_shortlisting_data, "id");
                if ($update_university) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_custumer_update_successfully', _l('client'));
                    $data['ids'] = $ids;
                    $data['university_shortlisting'] = $university_shortlisting_data;
                    set_alert('success', _l('update_custumer_update_successfully', _l('client')));
                } else {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = _l('update_custumer_failed_successfully', _l('client'));
                    set_alert('danger', _l('update_custumer_failed_successfully', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something bad happen.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function update_university_offer_status()
    {

        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;
            $university_ids = !empty($this->input->post("university_id")) ? $this->input->post("university_id") : [];
            $offer_letter_status = !empty($this->input->post("offer_letter_status")) ? $this->input->post("offer_letter_status") : [];
            $media_file_status = !empty($this->input->post("media_file_status")) ? $this->input->post("media_file_status") : [];
            $media_file_url = !empty($this->input->post("media_file_url")) ? $this->input->post("media_file_url") : [];
            $conditional_notes = !empty($this->input->post("conditional_notes")) ? $this->input->post("conditional_notes") : [];
            $conditional_array = !empty($this->input->post("conditional_array")) ? $this->input->post("conditional_array") : [];
            $university_status = !empty($this->input->post("university_status")) ? $this->input->post("university_status") : [];

            $media_file = !empty($_FILES["media_file"]) ? $_FILES["media_file"] : [];
            $media_file_condition = !empty($_FILES["conditional_media_file"]) ? $_FILES["conditional_media_file"] : [];


            $university_shortlisting_update_arr = [];
            if (!empty($university_ids)) {
                $i = 0;
                foreach ($university_ids as $k => $university_id) {

                    $media_path = $media_file_url[$university_id];
                    if (!empty($media_file_url[$university_id])) {
                        $media_path = $media_file_url[$university_id];
                    }
                    if ($media_file_status[$k] != 1) {
                        $media_path = "";
                    }
                    $upload_data = [];
                    if (!empty($media_file['name'][$university_id]) && $media_file_status[$k] == 1) {
                        $upload_data["name"] = $media_file['name'][$university_id];
                        $upload_data["type"] = $media_file['type'][$university_id];
                        $upload_data["tmp_name"] = $media_file['tmp_name'][$university_id];
                        $upload_data["error"] = $media_file['error'][$university_id];
                        $upload_data["size"] = $media_file['size'][$university_id];
                        if ($upload_data["error"] === UPLOAD_ERR_OK) {

                            $file_name = upload_applicant_documents($client_id, $upload_data);
                            $media_path = $file_name["file_path"];
                        }
                        $i++;
                    }
                    array_push($university_shortlisting_update_arr, array("university_offer_status" => $offer_letter_status[$k], "id" => $university_id, "media_file" => $media_path, 'updated_by' => get_staff_user_id(), 'updated_date' => date('Y-m-d H:i:s'), 'offer_date' => date('Y-m-d H:i:s')));
                }


                $condition_array_data = [];
                $condition_array_update_data = [];
                $condition_a = [];
                $index_ = 0;
                $index_u = 0;
                foreach ($conditional_array as $keyy => $con) {
                    $con_array = json_decode($con);
                    if (empty($con_array->university_status) || $con_array->university_status == '' || $con_array->university_status <= 0) {

                        $this->db->where('client_id', $client_id);
                        $this->db->where('university_id', $university_id);
                        $this->db->update(db_prefix() . 'offer_condition', [
                            'status' => 0
                        ]);

                        if (empty($con_array->condition_id)) {
                            $condition_a[$con_array->university_id] = empty($condition_a[$con_array->university_id]) ? 0 : $condition_a[$con_array->university_id] + 1;
                            $condition_array_data[$index_] = array("client_id" => $client_id, "university_id" => $con_array->university_id, "status" => 1, "condition_text" => $con_array->condition, "created_at" => get_staff_user_id(), "created_date" => date('Y-m-d H:i:s'), "file" =>  $con_array->media_url);
                            $index_++;
                        } else {
                            $condition_a[$con_array->university_id] = empty($condition_a[$con_array->university_id]) ? 0 : $condition_a[$con_array->university_id] + 1;
                            $condition_array_update_data[$index_u] = array("id" => $con_array->condition_id, "client_id" => $client_id, "university_id" => $con_array->university_id, "status" => 1, "condition_text" => $con_array->condition, "updated_by" => get_staff_user_id(), "updated_date" => date('Y-m-d H:i:s'), "file" => $con_array->media_url);
                            $index_u++;
                        }

                        $upload_data = [];
                        if (!empty($media_file_condition['name'][$con_array->media_name])) {
                            $upload_data["name"] = $media_file_condition["name"][$con_array->media_name];
                            $upload_data["type"] = $media_file_condition["type"][$con_array->media_name];
                            $upload_data["tmp_name"] = $media_file_condition["tmp_name"][$con_array->media_name];
                            $upload_data["error"] = $media_file_condition["error"][$con_array->media_name];
                            $upload_data["size"] = $media_file_condition["size"][$con_array->media_name];
                            if ($upload_data["error"] === UPLOAD_ERR_OK) {

                                $file_name = upload_applicant_documents($client_id, $upload_data);
                                $media_path = $file_name["file_path"];
                            }
                            if (empty($con_array->condition_id)) {
                                $condition_array_data[(count($condition_array_data) - 1)]["file"] = $media_path;
                            } else {
                                $condition_array_update_data[(count($condition_array_update_data) - 1)]["file"] = $media_path;
                            }
                        }
                    }
                }


                $update_university = "";
                if (!empty($university_shortlisting_update_arr)) {
                    if (!empty($university_shortlisting_update_arr)) {
                        $update_university = $this->db->update_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_update_arr, "id");
                        if (!empty($condition_array_data)) {
                            $this->db->insert_batch(db_prefix() . "offer_condition", $condition_array_data);
                        }

                        if (!empty($condition_array_update_data)) {
                            $this->db->update_batch(db_prefix() . "offer_condition", $condition_array_update_data, "id");
                        }
                    }

                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "University Offer updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                    get_applicant_status($applicant_status, $client_id);
                    $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
                    $ids = array_column($university_shortlisting_data, "id");
                    if ($update_university) {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = _l('update_custumer_update_offer_successfully', _l('client'));
                        $data['ids'] = $ids;
                        $data['university_shortlisting'] = $university_shortlisting_data;
                        set_alert('success', _l('update_custumer_update_offer_successfully', _l('client')));
                    } else {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = _l('update_custumer_failed_offer_successfully', _l('client'));
                        set_alert('danger', _l('update_custumer_failed_offer_successfully', _l('client')));
                    }
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Something bad happen.';
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Invalid request method';
            }

            echo json_encode($data);
        }
    }

    public function update_notes()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $stage_id = !empty($this->input->post("stage_id")) ? $this->input->post("stage_id") : "";
            $applicant_notes = !empty($this->input->post("applicant_notes")) ? $this->input->post("applicant_notes") : "";
            $notes_id = !empty($this->input->post("notes_id")) ? $this->input->post("notes_id") : "";
            if (!empty($notes_id)) {
                $this->db->where("id", $notes_id);
                $this->db->update(db_prefix() . 'application_notes', array("application_stage" => $stage_id, "note" => $applicant_notes, "updated_by" => get_staff_user_id(), "updated_date" => date('Y-m-d H:i:s'), "status" => 1, "client_id" => $client_id, "editable_status" => 1));
            } else {
                $this->db->insert(db_prefix() . 'application_notes', array("application_stage" => $stage_id, "note" => $applicant_notes, "created_by" => get_staff_user_id(), "created_date" => date('Y-m-d H:i:s'), "status" => 1, "client_id" => $client_id));
            }


            $rows_affected = $this->db->affected_rows();
            if ($rows_affected) {
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Notes update successfully.";
                $data['notes'] = [];

                set_alert('success', "Notes update successfully.");
            } else {
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Notes update failed";
                set_alert('danger', "Notes update failed");
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function get_application_notes($client_id = "")
    {

        $application_note_list = $this->clients_model->application_note_list($client_id);

        $html = '';
        foreach ($application_note_list as $i => $note) {
            $html .= "<div class='note-box'>";
            $html .= '<a href="' . admin_url('profile/' . $note["created_by"]) . '" target="_blank">';
            $html .= staff_profile_image($note['created_by'], array('staff-profile-image-small', 'pull-left mright10'));
            $html .= '</a>';
            $html .= '<div class="media-body">';
            if ($note['created_by'] == get_staff_user_id() || is_admin()) {
                // $html .= '<a href="#" class="pull-right text-danger" onclick="delete_lead_note(this, ' . $note['id'] . ', ' . $lead->id . '); return false;"><i class="fa fa fa-times"></i></a>';
                $html .= '<a href="#" class="pull-right mright5"  data-id="' . $note['id'] . '" data-notes="' . check_for_links(app_happy_text($note['note'])) . '" onclick="edit_notes(' . $note['id'] . ',this); return false;"><i class="fa fa-pencil-square-o"></i></a>';
            }
            $html .= ' <span data-toggle="tooltip" data-title="' . _dt($note['datetime']) . '" data-original-title="" title="">
            <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
         </span>';
            $html .= '<small>' . _l('lead_note_date_added', _dt($note['datetime'])) . '</small>';
            if ($note['editable_status'] == 1) {
                $html .= '<small class="note-edit">Edited</small>';
            }
            $html .= '<a href="' . admin_url('profile/' . $note["created_by"]) . '" target="_blank">';
            $html .= '<h5 class="media-heading bold">' . get_staff_full_name($note['created_by']) . '</h5>';
            $html .= '<h6 class="media-heading bold text-warning">' . $note['application_stage_name'] . '</h6>';
            $html .= '</a>';
            $html .= '<div data-note-description="' . $note['id'] . '" class="text-muted">';
            $html .= check_for_links(app_happy_text($note['note']));
            $html .= '</div>';
            $html .= '<div data-note-edit-textarea="' . $note['id'] . '" class="hide mtop15">';
            $html .= '</div>';
            $html .= '</div>';
            $html .= "</div>";
        }

        echo $html;
    }

    public function get_application_activity($client_id = "")
    {

        $activity_log = $this->clients_model->application_activity($client_id);

        $html = '';
        foreach ($activity_log as $log) {

            $html .= '<div class="feed-item">';
            $html .= '<div class="date">';
            $html .= '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($log['date']) . '">';
            $html .= time_ago($log['datetime']);
            $html .= '</span>';
            $html .= '</div>';
            $html .= '<div class="text">';
            if ($log['staffid'] != 0) {
                $html .= '<a href="' . admin_url('profile/' . $log["staffid"]) . '">';
                $html .= staff_profile_image($log['staffid'], array('staff-profile-xs-image', 'pull-left', 'mright5'));
                $html .= '</a>';
            }
            $datetime = '';
            if (!empty($log['datetime'])) {
                $datetime = unserialize($log['datetime']);
                $html .= ($log['staffid'] == 0) ? _l($log['description'], $datetime) : $log['full_name'] . ' - ' . _l($log['description'], $datetime);
                if ($log['full_name'] != "") {
                    $html .= $log['full_name'];
                }
            } else {
                $html .= $log['full_name'] . ' - ';
                if ($log['custom_activity'] == 0) {
                    $html .= _l($log['description']);
                } else {
                    $html .= _l($log['description'], '', false);
                }
                if ($log['full_name'] != "") {
                    $html .= $log['full_name'];
                }
            }
            $html .= '</div>';
            $html .= '</div>';
        }


        echo $html;
    }

    public function update_approval()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $university_id = $this->input->post("university_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : null;
            $status = !empty($this->input->post("status")) ? $this->input->post("status") : "0";

            $client_id = intval($client_id);
            $university_id = intval($university_id);
            $applicant_status = ($applicant_status !== null) ? intval($applicant_status) : null;
            $status = intval($status);

            try {
                if (!empty($university_id) && !empty($client_id)) {
                    $this->db->where(array("client_id" => $client_id, "id" => $university_id));
                    $this->db->update(db_prefix() . 'client_university_shortlisting', array(
                        "updated_by" => get_staff_user_id(),
                        "updated_date" => date('Y-m-d H:i:s'),
                        "acceptance_status" => $status,
                    ));

                    if (isset($applicant_status)) {
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                        get_applicant_status($applicant_status, $client_id);
                    }
                    $rows_affected = $this->db->affected_rows();
                    if ($rows_affected) {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = "University update successfully.";
                        set_alert('success', "University update successfully.");
                    } else {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = "University update failed";
                        set_alert('danger', "University update failed");
                    }
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Invalid parameters";
                }
            } catch (Exception $e) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function profile_update()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $client_id = $this->input->post("clientid");
                $update_applicant_data = [];
                $update_applicant_custom_data["customers"] = [];
                unset($_POST["clientid"]);
                foreach ($_POST as $key => $value) {
                    if (!empty($value) && strpos($key, 'custom_fields') !== false) {
                        // If the key contains 'custom_fields' and the value is not empty, add to custom data array
                        foreach ($value as $k => $custom_value) {
                            $update_applicant_custom_data["customers"] = $custom_value;
                        }
                    } else {
                        // Otherwise, add to general data array
                        if ($key != 'clientid') {
                            $update_applicant_data[$key] = $value;
                        }
                    }
                }

                $this->db->where('userid', $client_id);
                $rows_affected = $this->db->update(db_prefix() . 'clients', $update_applicant_data);
                if ($rows_affected) {
                    handle_custom_fields_post($client_id, $update_applicant_custom_data);
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Profile update successfully.";
                    set_alert('success', "Profile update successfully.");
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Profile update failed";
                    set_alert('danger', "Profile update failed");
                }
            } catch (Exception $e) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function media_upload($data, $media)
    {

        if (empty($media["files"]["name"])) {
            return false;
            die;
        }
        $doc_ids = isset($data["doc_type_id"]) ? $data["doc_type_id"] : [];
        $doc_names = isset($data["doc_type_name"]) ? $data["doc_type_name"] : [];
        $document_url = isset($data["doc_url"]) ? $data["doc_url"] : [];
        $update_array = [];
        $client_id = $data["clientid"];

        $documents_type =  get_documents("", [], 1);
        $documents_type =  array_column($documents_type, null, 'id');
        $this->db->select("data");
        $this->db->where('client_id', $client_id);
        $already_data = $this->db->get(db_prefix() . 'client_documents')->row();


        if (!empty($already_data->data)) {
            // Ensure $check_->data is valid JSON
            $already_data = json_decode($already_data->data, true);
            $already_data = array_column($already_data, null, "id");
        }

        for ($i = 0; $i < count($doc_ids); $i++) {
            $documents = $media["files_" . $doc_ids[$i]];

            $upload_data = [];
            $file_name_ = str_replace(" ", "-", $doc_names[$i]);
            if (!empty($documents['name'])) {
                $upload_data["name"] =  $file_name_ . "." . pathinfo($documents['name'], PATHINFO_EXTENSION);
                $upload_data["type"] = $documents['type'];
                $upload_data["tmp_name"] = $documents['tmp_name'];
                $upload_data["error"] = $documents['error'];
                $upload_data["size"] = $documents['size'];
                if ($upload_data["error"] === UPLOAD_ERR_OK) {;
                    $file_name = upload_applicant_documents($client_id, $upload_data);
                    array_push($update_array, array("id" => $doc_ids[$i], "document_file" => $file_name["file_path"], "updated_by" => get_staff_user_id(), "updated_date" => date('Y-m-d H:i:s')));
                    $doc_name = $documents_type[$doc_ids[$i]]["name"];

                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "{$doc_name} document uploaded by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                }
            } else if (!empty($document_url[$i])) {
                if (!empty($already_data[$doc_ids[$i]])) {
                    array_push($update_array, $already_data[$doc_ids[$i]]);
                }
                // array_push($update_array, array("id" => $doc_ids[$i], "document_file" => !empty($document_url[$i]) ? $document_url[$i] : ''));
            }
        }

        $this->db->select("*");
        $this->db->where('client_id', $client_id);
        $check_ = $this->db->get(db_prefix() . 'client_documents')->row();

        if (!empty($check_->id)) {
            // Ensure $check_->data is valid JSON
            $already_data = json_decode($check_->data, true);
            if (!is_array($already_data)) {
                $already_data = []; // Fallback to an empty array if JSON is invalid
            }

            // Ensure $doc_ids is an array
            $doc_ids = is_array($doc_ids) ? $doc_ids : [];

            $already_data = array_column($already_data, null, "id"); // Convert to associative array

            $new_array_update = [];

            // Remove IDs that exist in $doc_ids
            $filtered_data = array_diff_key($already_data, array_flip($doc_ids));

            $new_array_update = array_values($filtered_data); // Re-index the array

            // Ensure $update_array is an array before merging
            $update_array = isset($update_array) && is_array($update_array) ? $update_array : [];

            $update_array = array_merge($update_array, $new_array_update);


            $_update_data = array(
                "data" => json_encode($update_array, true),
                "updated_date" => date('Y-m-d H:i:s'),
                "document_status" => 0,
                "document_update_datetime" => date('Y-m-d H:i:s'),
                "updated_by" => get_staff_user_id()
            );
            $this->db->where("id", $check_->id);
            $this->db->update(db_prefix() . 'client_documents', $_update_data);
        } else {

            if (!empty($update_array)) {
                $insert_update_data = array(
                    "client_id" => $client_id,
                    "data" => json_encode($update_array, true),
                    "status" => 1,
                    "created_date" => date('Y-m-d H:i:s'),
                    "created_by" => get_staff_user_id()
                );
                $this->db->insert(db_prefix() . 'client_documents', $insert_update_data);
            }
        }
    }

    public function student_update()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // try {

            $client_id = $this->input->post("clientid");
            $media_upload_data = $_POST;
            $update_student_data = [];
            $update_applicant_custom_data["customers"] = [];
            $reference_name = $_POST["reference_name"];
            $address = $_POST["address"];
            $agent_id_raw = trim($_POST["agent_id"] ?? '');
            $agent_id = trim($agent_id_raw);
            $state = trim($_POST["state"] ?? '');
            unset($_POST["clientid"]);
            unset($_POST["doc_type_id"]);
            unset($_POST["doc_type_name"]);
            unset($_POST["doc_type"]);
            unset($_POST["doc_name"]);
            unset($_POST["doc_url"]);
            unset($_POST["reference_name"]);
            unset($_POST["files"]);
            unset($_POST["agent_id"]);
            unset($_POST["state"]);
            unset($_POST["address"]);


            if (empty($client_id) || !empty($agent_id)) {
                $first_name = trim($_POST["first_name"] ?? '');
                $last_name = trim($_POST["last_name"] ?? '');
                unset($_POST["agent_id"]);
                $unique_agent_id = base64_encode($first_name . $last_name . $agent_id);

                // Check if unique_agent_id is empty
                if (empty($unique_agent_id)) {
                    echo json_encode(["resp_code" => "ERR", "resp_desc" => "Unique Agent ID is empty."]);
                    return;
                }


                // Check if unique_agent_id already exists
                $this->db->where('unique_agent_id', $unique_agent_id);

                if (!empty($client_id)) {
                    $this->db->where('userid!=', $client_id);
                }
                $exists = $this->db->get(db_prefix() . 'clients')->row();



                if ($exists) {
                    echo json_encode(["resp_code" => "ERR", "resp_desc" => "Student already exist already exists."]);
                    return;
                }

                if (empty($client_id)) {

                    $client_data = ["active" => 1, "datecreated" => date('Y-m-d H:i:s'), "addedfrom" => get_staff_user_id(), "applicant_status" => 0, "applicant_stage" => 1, "applicant_sub_status" => 1, "tracker_id" => 0, "client_type" => 2, "agent_id" => $agent_id, "unique_agent_id" => $unique_agent_id];
                    $this->db->insert(db_prefix() . 'clients', $client_data);
                    $client_id = $this->db->insert_id();
                } else {
                    $client_data = ["agent_id" => $agent_id];
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', $client_data);
                }
            }



            foreach ($_POST as $key => $value) {
                if (!empty($value) && strpos($key, 'custom_fields') !== false) {
                    // If the key contains 'custom_fields' and the value is not empty, add to custom data array
                    foreach ($value as $k => $custom_value) {
                        $update_applicant_custom_data["customers"] = $custom_value;
                    }
                } else {
                    // Otherwise, add to general data array
                    if ($key != 'clientid') {
                        $update_student_data[$key] = $value;
                    }
                }
            }
            // Assuming this is part of a function or method in a CodeIgniter controller or model
            $check_client = $this->db->select('id')
                ->where('userid', $client_id)
                ->get(db_prefix() . 'basic_details')->row();



            if (!empty($check_client->id)) {
                $update_student_data["updated_at"] = date('Y-m-d H:i:s');


                $this->db->where('userid', $client_id);
                $rows_affected = $this->db->update(db_prefix() . 'basic_details', $update_student_data);
                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Basic Information Updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
            } else {
                $update_student_data["created_at"] = date('Y-m-d H:i:s');
                $update_student_data["userid"] = $client_id;

                $rows_affected = $this->db->insert(db_prefix() . 'basic_details', $update_student_data);

                $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Basic Information Created by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
            }

            if (!empty($reference_name) || !empty($state) || !empty($address)) {

                $updateClientInfo = [];
                if (!empty($reference_name)) {
                    $updateClientInfo['reference_name'] = $reference_name;
                }
                if (!empty($state)) {
                    $updateClientInfo['state'] = $state;
                }
                if (!empty($address)) {
                    $updateClientInfo['address'] = $address;
                }
                $this->db->where('userid', $client_id);
                $rows_affected = $this->db->update(db_prefix() . 'clients', $updateClientInfo);
                if (!empty($reference_name)) {
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Refrence Information Updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                }
            }


            if ($rows_affected) {
                if (!empty($media_upload_data["doc_type"])) {
                    $this->media_upload($media_upload_data, $_FILES);
                }
                // handle_custom_fields_post($client_id, $update_applicant_custom_data);
                applicant_last_update($client_id);
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Basic information update successfully.";
                $data['client_id'] = $client_id;
                set_alert('success', "Basic information update successfully.");
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = "Basic information update failed";
                set_alert('danger', "Basic information update failed");
            }
            // } catch (Exception $e) {
            //     $data['resp_code'] = 'ERR';
            //     $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            // }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function passport_info()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $client_id = $this->input->post("clientid");
                $media_upload_data = $_POST;
                $passpot_data = [];
                unset($_POST["clientid"]);
                unset($_POST["doc_type_id"]);
                unset($_POST["doc_type_name"]);
                unset($_POST["doc_type"]);
                unset($_POST["doc_name"]);
                unset($_POST["doc_url"]);


                foreach ($_POST as $key => $value) {
                    if (!empty($value) && strpos($key, 'custom_fields') !== false) {
                        // If the key contains 'custom_fields' and the value is not empty, add to custom data array
                        foreach ($value as $k => $custom_value) {
                            $update_applicant_custom_data["customers"] = $custom_value;
                        }
                    } else {
                        // Otherwise, add to general data array
                        if ($key != 'clientid') {
                            $passpot_data[$key] = $value;
                        }
                    }
                }

                $check_client = $this->db->select('id')
                    ->where('client_id', $client_id)
                    ->get(db_prefix() . 'client_passport_details')->row();
                if (!empty($check_client->id)) {
                    $passpot_data["updated_date"] = date('Y-m-d H:i:s');
                    $passpot_data["updated_by"] = get_staff_user_id();
                    $this->db->where('client_id', $client_id);
                    $rows_affected = $this->db->update(db_prefix() . 'client_passport_details', $passpot_data);
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Passport Information updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                } else {
                    $passpot_data["client_id"] = $client_id;
                    $passpot_data["created_date"] = date('Y-m-d H:i:s');
                    $passpot_data["created_by"] = get_staff_user_id();
                    $rows_affected = $this->db->insert(db_prefix() . 'client_passport_details', $passpot_data);
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Passport Information updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                }

                if ($rows_affected) {

                    if (!empty($media_upload_data["doc_type"][0])) {
                        $this->media_upload($media_upload_data, $_FILES);
                    }
                    // handle_custom_fields_post($client_id, $update_applicant_custom_data);
                    applicant_last_update($client_id);
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Passport information update successfully.";
                    set_alert('success', "Passport information update successfully.");
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Passport information update failed";
                    set_alert('danger', "Passport information update failed");
                }
            } catch (Exception $e) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function student_acadmic()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $client_id = $this->input->post("clientid");
                $media_upload_data = $_POST;
                $passpot_data = [];
                unset($_POST["clientid"]);
                unset($_POST["doc_type_id"]);
                unset($_POST["doc_type_name"]);
                unset($_POST["doc_type"]);
                unset($_POST["doc_name"]);
                unset($_POST["doc_url"]);

                $academicDetailsId = $this->input->post("academicDetailsId");
                $update_academic_data = [];
                $update_applicant_custom_data["customers"] = [];
                unset($_POST["clientid"]);
                unset($_POST["academicDetailsId"]);
                $scrore_update = [];
                foreach ($_POST as $key => $value) {
                    // print_r($_POST[$key]);
                    // print_r($key);
                    // print_r($value);
                    if (strpos($key, 'score_column') !== false) {
                        $score_data = explode("-", $key);
                        if (!empty($score_data[1])) {
                            array_push($scrore_update, array("client_id" => $client_id, "type" => $score_data[1], "value" => $value));
                        }

                        unset($_POST[$key]);
                        $key = '';
                    }
                    if (!empty($key)) {
                        if (!empty($value) && strpos($key, 'custom_fields') !== false) {
                            // If the key contains 'custom_fields' and the value is not empty, add to custom data array
                            foreach ($value as $k => $custom_value) {
                                $update_applicant_custom_data["customers"] = $custom_value;
                            }
                        } else {
                            // Otherwise, add to general data array
                            if ($key != 'clientid') {
                                $update_academic_data[$key] = $value;
                            }
                        }
                    }
                }
                $this->db->delete(db_prefix() . "academic_entrance_score", array("client_id" => $client_id));

                if (empty($academicDetailsId)) {
                    $update_academic_data["created_at"] = date('Y-m-d H:i:s');
                    $update_academic_data["userid"] = $client_id;
                    $rows_affected = $this->db->insert(db_prefix() . 'academic_details', $update_academic_data);

                    // Assuming you're using CodeIgniter Active Record, adjust if you're using a different database framework or raw SQL
                    // Delete existing academic entrance scores for the given client_id


                    if (!empty($scrore_update)) {
                        // Insert the new batch of academic entrance scores

                        $this->db->insert_batch(db_prefix() . "academic_entrance_score", $scrore_update);
                        // $this->db->where("userid", $client_id);
                        // $this->db->update(db_prefix() . 'clients', array("applicant_stage" => 2, "applicant_sub_status" => 5));
                    }
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Acadmic Details Information Updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                } else {
                    $update_academic_data["updated_at"] = date('Y-m-d H:i:s');
                    $this->db->where('userid', $client_id);
                    $this->db->where('id', $academicDetailsId);
                    $rows_affected = $this->db->update(db_prefix() . 'academic_details', $update_academic_data);
                    // $this->db->where("userid", $client_id);
                    // $this->db->update(db_prefix() . 'clients', array("applicant_stage" => 2, "applicant_sub_status" => 5));
                    if (!empty($scrore_update)) {
                        // Insert the new batch of academic entrance scores

                        $this->db->insert_batch(db_prefix() . "academic_entrance_score", $scrore_update);
                    }
                    $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Acadmic Details Information Updated by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                }
                if ($rows_affected) {
                    if (!empty($media_upload_data["doc_type"][0])) {
                        $this->media_upload($media_upload_data, $_FILES);
                    }
                    // handle_custom_fields_post($client_id, $update_applicant_custom_data);
                    // $this->db->where("userid", $client_id);
                    // $this->db->update(db_prefix() . 'clients', array("applicant_stage" => 2, "applicant_sub_status" => 5));
                    applicant_last_update($client_id);
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Academic information update successfully.";
                    set_alert('success', "Academic information update successfully.");
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Academic information update failed";
                    set_alert('danger', "Academic information update failed");
                }
            } catch (Exception $e) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }

    public function upload_visa_documents()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
            echo json_encode($data);
            return;
        }

        $type = $this->input->post("type");
        $client_id = $this->input->post("client_id");

        if (empty($type) || empty($client_id)) {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid input data';
            echo json_encode($data);
            return;
        }


        $check_visa_documents = $this->db->select('*')->where(['client_id' => $client_id])->get(db_prefix() . 'visa_documents')->result_array();

        if (empty($check_visa_documents)) {
            $this->db->insert(db_prefix() . 'visa_documents', [
                "created_date"  => date('Y-m-d H:i:s'),
                "created_by"    => get_staff_user_id(),
                "client_id"     => $client_id,
            ]);
        }

        if ($type == 'document') {
            $label_data = $this->input->post("document_label");
            $document_url = $this->input->post("document_url");
            $update_array = [];

            for ($i = 0; $i < count($label_data); $i++) {
                $upload_data = [];
                $files = $_FILES['document_file_' . $i];

                if (!empty($files['name'])) {
                    $upload_data["name"] = $files['name'];
                    $upload_data["type"] = $files['type'];
                    $upload_data["tmp_name"] = $files['tmp_name'];
                    $upload_data["error"] = $files['error'];
                    $upload_data["size"] = $files['size'];

                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $file_name = upload_applicant_documents($client_id, $upload_data);
                        array_push($update_array, array("label_name" => $label_data[$i], "document_file" => $file_name["file_path"]));
                    }
                } elseif (!empty($document_url[$i])) {
                    array_push($update_array, array("label_name" => $label_data[$i], "document_file" => !empty($document_url[$i]) ? $document_url[$i] : ''));
                }
            }

            if (!empty($update_array)) {
                $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'visa_documents', ["visa_documents" => json_encode($update_array, true)]);
                // $rows_affected = $this->db->affected_rows();

                if ($rows_affected) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Visa documents update successfully.";
                    set_alert('success', "Visa documents update successfully.");
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Visa documents update failed";
                    set_alert('danger', "Visa documents update failed");
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'No files to upload';
            }
        } else if ($type == 'vendor') {
            $vendor = $this->input->post("vendor");
            if (!empty($vendor)) {
                $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'visa_documents', ["vendor" => $vendor]);
                // $rows_affected = $this->db->affected_rows();

                if ($rows_affected) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Visa vendor update successfully.";
                    set_alert('success', "Visa vendor update successfully.");
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Visa vendor update failed";
                    set_alert('danger', "Visa vendor update failed");
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'No vendor update';
            }
        } else if ($type == 'visa') {
            $visa_type = !empty($this->input->post("visa_type")) ? explode(",", $this->input->post("visa_type")) : '';

            if (!empty($visa_type)) {
                $update_array = [
                    "ihs_fee" => 0,
                    "visa_fee" => 0,
                    "vfs_fee" => 0,
                ];

                if (in_array("ihs_fee", $visa_type)) {
                    $update_array["ihs_fee"] = 1;
                }

                if (in_array("visa_fee", $visa_type)) {
                    $update_array["visa_fee"] = 1;
                }

                if (in_array("vfs_fee", $visa_type)) {
                    $update_array["vfs_fee"] = 1;
                }

                $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'visa_documents', $update_array);
                // $rows_affected = $this->db->affected_rows();

                if ($rows_affected) {
                    $data['resp_code'] = 'RCS';
                    $data['resp_desc'] = "Visa update successfully.";
                    set_alert('success', "Visa update successfully.");
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "Visa update failed";
                    set_alert('danger', "Visa update failed");
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'No visa update';
            }
        } else if ($type == "date") {
            $biometric_status = $this->input->post("biometric_status");
            $biometric_date = $this->input->post("biometric_date");
            $interview_status = $this->input->post("interview_status");
            $interview_date = $this->input->post("interview_date");

            $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'visa_documents', ["biometric_status" => $biometric_status, "biometric_date" => $biometric_date, "interview_status" => $interview_status, "interview_date" => $interview_date]);
            // $rows_affected = $this->db->affected_rows();

            if ($rows_affected) {
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Visa details update successfully.";
                set_alert('success', "Visa details update successfully.");
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = "Visa details update failed";
                set_alert('danger', "Visa details update failed");
            }
        } else if ($type == 'status') {
            $visa_status = !empty($this->input->post("visa_status")) ? $this->input->post("visa_status") : '';
            $note = !empty($this->input->post("note")) ? trim($this->input->post("note")) : '';
            $files = $_FILES["visa_file"];
            $visa_file_url = !empty($this->input->post("visa_file_url")) ? $this->input->post("visa_file_url") : '';
            $visa_file_path = "";

            if (!empty($files['name'])) {
                $upload_data["name"] = $files['name'];
                $upload_data["type"] = $files['type'];
                $upload_data["tmp_name"] = $files['tmp_name'];
                $upload_data["error"] = $files['error'];
                $upload_data["size"] = $files['size'];

                if ($upload_data["error"] === UPLOAD_ERR_OK) {
                    $file_name = upload_applicant_documents($client_id, $upload_data);
                    $visa_file_path = $file_name["file_path"];
                }
            } else {
                $visa_file_path = $visa_file_url;
            }

            $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'visa_documents', ["visa_status" => $visa_status, "note" => $note, "visa_file" => $visa_file_path]);
            // $rows_affected = $this->db->affected_rows();

            if ($rows_affected) {
                $data['resp_code'] = 'RCS';
                $data['resp_desc'] = "Visa status update successfully.";
                set_alert('success', "Visa status update successfully.");
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = "Visa status update failed";
                set_alert('danger', "Visa status update failed");
            }
        }

        echo json_encode($data);
    }

    function update_accommodation()
    {

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
            echo json_encode($data);
            return;
        }
        $client_id = $this->input->post("client_id");

        if (empty($client_id)) {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid input data';
            echo json_encode($data);
            return;
        }

        $check_accommodation = $this->db->select('*')->where(['client_id' => $client_id])->get(db_prefix() . 'accommodation')->result_array();

        if (empty($check_accommodation)) {
            $this->db->insert(db_prefix() . 'accommodation', [
                "created_date"  => date('Y-m-d H:i:s'),
                "created_by"    => get_staff_user_id(),
                "client_id"     => $client_id,
            ]);
        }
        $update_data = $_POST;


        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $files) {
                if (!empty($files['name'])) {
                    $upload_data["name"] = $files['name'];
                    $upload_data["type"] = $files['type'];
                    $upload_data["tmp_name"] = $files['tmp_name'];
                    $upload_data["error"] = $files['error'];
                    $upload_data["size"] = $files['size'];

                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $file_name = upload_applicant_documents($client_id, $upload_data);
                        $update_data[$key] = $file_name["file_path"];
                    }
                }
            }
        }

        $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'accommodation', $update_data);
        // $rows_affected = $this->db->affected_rows();

        if ($rows_affected) {
            $data['resp_code'] = 'RCS';
            $data['resp_desc'] = "Accommodation details update successfully.";
            set_alert('success', "Accommodation details update successfully.");
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = "Accommodation details update failed";
            set_alert('danger', "Accommodation details update failed");
        }

        echo json_encode($data);
    }

    function update_flight()
    {

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
            echo json_encode($data);
            return;
        }
        $client_id = $this->input->post("client_id");

        if (empty($client_id)) {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid input data';
            echo json_encode($data);
            return;
        }

        $check_flight = $this->db->select('*')->where(['client_id' => $client_id])->get(db_prefix() . 'flight')->result_array();

        if (empty($check_flight)) {
            $this->db->insert(db_prefix() . 'flight', [
                "created_date"  => date('Y-m-d H:i:s'),
                "created_by"    => get_staff_user_id(),
                "client_id"     => $client_id,
            ]);
        }
        $update_data = $_POST;


        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $files) {
                if (!empty($files['name'])) {
                    $upload_data["name"] = $files['name'];
                    $upload_data["type"] = $files['type'];
                    $upload_data["tmp_name"] = $files['tmp_name'];
                    $upload_data["error"] = $files['error'];
                    $upload_data["size"] = $files['size'];

                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $file_name = upload_applicant_documents($client_id, $upload_data);
                        $update_data[$key] = $file_name["file_path"];
                    }
                }
            }
        }

        $rows_affected = $this->db->where(["client_id" => $client_id])->update(db_prefix() . 'flight', $update_data);
        $rows_affected = $this->db->affected_rows();

        if ($rows_affected) {
            $data['resp_code'] = 'RCS';
            $data['resp_desc'] = "Flight details update successfully.";
            set_alert('success', "Flight details update successfully.");
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = "Flight details update failed";
            set_alert('danger', "Flight details update failed");
        }

        echo json_encode($data);
    }

    public function registration_slip_preview($client_id)
    {
        try {
            $data = [];
            $client = $this->clients_model->get($client_id);
            $client_basic = $this->clients_model->getBasicDetails($client_id);
            $get_clients_fees = get_clients_fees_details(2, $client_id);

            $admission_prefrences = $this->clients_model->getAdmissionPreferences($client_id);
            $fees_amount = !empty($get_clients_fees) ? array_column($get_clients_fees, null, 'id') : [];
            $registration_amount = $fees_amount[REGISTRATION_AMOUNT_ID]["total_amount"];
            $total_amount = $fees_amount[TOTAL_AMOUNT_ID]["total_amount"];
            $pending_amount = $fees_amount[REGISTRATION_AMOUNT_ID]["amount"] - $fees_amount[TOTAL_AMOUNT_ID]["amount"];

            // Prepare Data for PDF
            $data["student_name"] = $client_basic->first_name . " " . $client_basic->last_name;
            $data["university_name"] = $admission_prefrences->primary_university;
            $data["country"] = $admission_prefrences->primary_country;
            $data["total_amount"] = $total_amount;
            $data["registration_amount"] = $registration_amount;
            $data["pending_amount"] = $pending_amount;
            $data["address"] = nl2br(htmlspecialchars($client->address));
            $data["date_of_payment"] = $client->date_of_payment;
            $data["acadmic_year"] = $admission_prefrences->acadmic_year;
            $data["invoice_number"] = "BRCM-00" . $client_id;
            $data["invoice_no"] = str_pad($client_id, 6, '0', STR_PAD_LEFT);
            $data["payment_recevied_from"] = $client->payment_recevied_from;

            // Disable SSL verification
            stream_context_set_default(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

            // Initialize TCPDF
            $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10, 10);
            $pdf->AddPage();

            // Load Fonts
            $path_gill_sans_mt = APPPATH . 'libraries/tcpdf/fonts/GILB____.ttf';
            $path_book_antiqua = APPPATH . 'libraries/tcpdf/fonts/book-antiqua-bold.ttf';
            $path_Cambria_Math = APPPATH . 'libraries/tcpdf/fonts/Cambria Math.ttf';
            $path_Cambria = APPPATH . 'libraries/tcpdf/fonts/Cambria/Cambria Bold 700.ttf';

            $data["gillsansmt"] = TCPDF_FONTS::addTTFfont($path_gill_sans_mt, 'TrueTypeUnicode', '', 15);
            $data["book_antiqua"] = TCPDF_FONTS::addTTFfont($path_book_antiqua, 'TrueTypeUnicode', '', 15);
            $data["Cambria_Math"] = TCPDF_FONTS::addTTFfont($path_Cambria_Math, 'TrueTypeUnicode', '', 15);
            $data["Cambria"] = TCPDF_FONTS::addTTFfont($path_Cambria, 'TrueTypeUnicode', '', 15);

            $pdf->setImageScale(1.7);


            // Load HTML Template
            $html = $this->load->view('admin/pdf/registration', $data, true);

            $pdf->writeHTML($html, true, false, true, false, '');


            // Define File Path
            $upload_dir = FCPATH . APPLICANT_UPLOAD_DOCUMENT_PATH . $client_id . "/";

            // Ensure directory exists and is writable
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true) && !is_dir($upload_dir)) {
                    return ["status" => "error", "message" => "Failed to create upload directory."];
                }
            }

            $file_name = 'Registration_Slip_' . time() . '.pdf'; // Unique file name
            $file_path = $upload_dir . $file_name;

            // Remove previous file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Save the new PDF file on the server
            $pdf->Output($file_path, 'F');

            // Verify if the file was created successfully
            if (!file_exists($file_path)) {
                return ["status" => "error", "message" => "Failed to generate PDF file."];
            }

            // File URL
            $file_url = $upload_dir . $file_name;

            $update_client_data = [];

            $update_client_data["registration_slip_invoice"] =  APPLICANT_UPLOAD_DOCUMENT_PATH . $client_id . "/" . $file_name;
            $this->db->where("userid", $client_id);
            $this->db->update(db_prefix() . 'clients', $update_client_data);
            return ["status" => "success", "pdf_url" => APPLICANT_UPLOAD_DOCUMENT_PATH . $client_id . "/" . $file_name];
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }


    public function check_documents_validation($id)
    {
        $check_documents = $this->check_documents($id);
        if (!empty($check_documents)) {
            $doc_names = implode(", ", $check_documents);
            $message = "{$doc_names} are mandatory to proceed to the next step.";
            $data = [
                'resp_code'               => 'ERR',
                'resp_desc'               => "Document requried " . $message,
            ];
            echo json_encode($data);
            exit;
        }
    }

    public function mbbs_tracker()
    {
        $data = array();


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
            echo json_encode($data);
            return;
        }

        $client_id = $this->input->post("client_id");
        $lead_type = $this->input->post("lead_type");
        $tracker_id = $this->input->post("tracker_id");
        $skip_status = !empty($this->input->post("skip")) ? $this->input->post("skip") : 0;
        $completed = !empty($this->input->post("completed")) ? $this->input->post("completed") : 0;
        $secondary_university_remark = !empty($this->input->post("secondary_university_remark")) ? $this->input->post("secondary_university_remark") : 0;
        $complete_application = !empty($this->input->post("complete_application")) && $this->input->post("complete_application") == 1 ? 1 : 0;

        if (!empty($complete_application)) {
            $data['resp_code'] = 'RCS';
            $data['resp_desc'] = '';
            echo json_encode($data);
            return;
        }

        $post_data = $_POST;
        if (empty($client_id)) {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid input data';
            echo json_encode($data);
            return;
        }



        if (!empty($secondary_university_remark)) {
            $this->db->where("userid", $client_id);
            $this->db->update(db_prefix() . 'clients', array("secondary_university_remark" => $secondary_university_remark));
        }
        if ($completed == 1) {
            $sc_100 = !empty($this->input->post("sc_100")) ? $this->input->post("sc_100") : 0;

            $update_client_data = [
                "sc_100" => $sc_100,
                "applicant_status" => 0,
                "applicant_stage" => !empty($sc_100) ? SC : VISA,
                "applicant_sub_status" => !empty($sc_100) ? SC_PENDING : VISA_STAMP,
            ];

            $this->db->where("userid", $client_id);
            $this->db->update(db_prefix() . 'clients', $update_client_data);

            $data =  [
                'resp_code' => 'RCS',
                'resp_desc' => "Service charge 100% update successfully."
            ];


            echo json_encode($data);
            return;
        }

        $check_client = $this->db->select('tracker_id')
            ->where('userid', $client_id)
            ->get(db_prefix() . 'clients')
            ->row();

        if (!empty($skip_status) && $skip_status == 1) {
            if ($tracker_id == 6) {
                $check_documents = $this->check_documents(8);
                if (!empty($check_documents)) {
                    // If required documents are missing
                    $doc_names = implode(", ", $check_documents);
                    $message = "{$doc_names} are mandatory to proceed to the next step.";

                    $data = [
                        'resp_code'               => 'ERR',
                        'resp_desc'               => "Document requried " . $message,
                    ];

                    set_alert('danger', "Document requried " . $message);

                    echo json_encode($data);
                    return;
                }

                $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id, 1);

                if (!empty($university_shortlisting_data) && !empty($university_shortlisting_data[0]["invitation_letter"])) {
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => INVITATION,
                        "applicant_sub_status" => INVITATION_RECEIVED,
                    ];
                } else {
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => INVITATION,
                        "applicant_sub_status" => INVITATION_PENDING,
                    ];
                }
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);
            }
            if ($tracker_id == 4) {

                $update_client_data = [
                    "applicant_status" => 0,
                    "applicant_stage" => LEGALIZATION,
                    "applicant_sub_status" => LEGALIZATION_PENDING,
                ];

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);
            }
            if ($tracker_id == 3) {

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => ENTRANCE_EXAM, "applicant_sub_status" => ENTRANCE_EXAM_PENDING));
            }
            $this->update_applicant_tracker_stages($client_id, $tracker_id);
            $legalization =  $this->clients_model->legalization_data($client_id);
            $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
            $data =  [
                'resp_code' => 'RCS',
                'resp_desc' => "Skip this stage.",
                'legalization' => $legalization,
                'invitation' => $university_shortlisting_data
            ];

            echo json_encode($data);

            return;
        }

        if ($tracker_id == 1) {
            $data = $this->document_verification($post_data);

            if (empty($check_client->tracker_id) && $check_client->tracker_id == 0) {
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => UNIVERSITY_SHORTLISTING, "applicant_sub_status" => UNIVERSITY_SHORTLISTING_PENDING));
            } else {
                $data["pass_stage"] = $check_client->tracker_id;
            }
        } else if ($tracker_id == 2) {
            $data = $this->university_shortlisting($post_data);
        } else if ($tracker_id == 3) {
            $data = $this->admission_letter();
        } else if ($tracker_id == 4) {
            $data = $this->entrance_exam();
        } else if ($tracker_id == 5) {
            $this->db->select("count(1) check_count");
            $this->db->where(array('client_id' => $client_id, "status" => 1));
            $check_count = $this->db->get(db_prefix() . 'client_university_shortlisting')->row();
            if (!empty($check_count->check_count) && $check_count->check_count > 1) {
                $data =  [
                    'resp_code' => 'ERR',
                    'resp_desc' => "Please select only one primary university in the Shortlisting section to proceed.",
                ];

                echo json_encode($data);
                return;
                die;
            }
            $this->check_documents_validation(($tracker_id + 1));
            $data = $this->Legalization();
        } else if ($tracker_id == 6) {
            $data = $this->feesDeposite();
        } else if ($tracker_id == 7) {
            $data = $this->invitationLetter();
        } else if ($tracker_id == 8) {

            $visa_information_check = visa_details($client_id, 1);
            $visa_information = visa_details($client_id, 0, 1);
            $this->db->where('userid', $client_id);
            $this->db->update(db_prefix() . 'clients', array("payment_3_received" => 1, "payment_3_received_date" => date('Y-m-d H:i:s')));

            if (empty($visa_information_check)) {
                $update_client_data = [
                    "applicant_status" => 0,
                    "applicant_stage" => VISA,
                    "applicant_sub_status" => VISA_PENDING,
                ];
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);
            } else {

                if ($visa_information_check[0]["payment_date"] != "0000-00-00") {
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => VISA,
                        "applicant_sub_status" => VISA_APPLY
                    ];
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', $update_client_data);
                }
            }

            $this->update_applicant_tracker_stages($client_id, $tracker_id);

            $data = [
                'resp_code' => 'RCS',
                'resp_desc' => "3rd payment received successfully.",
                'visa_details' => $visa_information
            ];

            echo json_encode($data);
            return;
        } else if ($tracker_id == 9) {
            $data = $this->visaLetter();
            $data["stage_next_permission"] = has_permission('application_tracker_mbbbs_sc') ? 1 : 0;
        } else {
            $data =  [
                'resp_code' => 'ERR',
                'resp_desc' => "Invalid Stage",
            ];

            echo json_encode($data);
            return;
        }


        applicant_last_update($client_id);
        echo json_encode($data);
    }

    private function update_applicant_tracker_stages($client_id, $tracker_id)
    {
        $this->db->where("userid", $client_id);
        $this->db->update(db_prefix() . 'clients', array("tracker_id" => $tracker_id));
    }

    private function document_verification($data)
    {

        $client_id = !empty($data["client_id"]) ? $data["client_id"] : '';
        $tracker_id = !empty($data["tracker_id"]) ? $data["tracker_id"] : '';
        $this->db->insert(db_prefix() . 'application_activity_log', array(
            "description" => "All documents verified by - " . get_staff_full_name(get_staff_user_id()),
            "date" => date('Y-m-d H:i:s'),
            "staffid" => get_staff_user_id(),
            "client_id" => $client_id
        ));
        $rows_affected = $this->db->affected_rows();
        if ($rows_affected) {

            $check_client = $this->db->select('tracker_id')
                ->where('userid', $client_id)
                ->get(db_prefix() . 'clients')
                ->row();
            if (empty($check_client->tracker_id) && $check_client->tracker_id == 0) {
                $this->db->where("userid", $client_id);
                $this->update_applicant_tracker_stages($client_id, $tracker_id);
            }
        }
        $data = [];
        if ($rows_affected) {
            $data['resp_code'] = 'RCS';
            $data['resp_desc'] = "Document Verification update successfully.";
            set_alert('success', "Document Verification update successfully.");
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = "Document Verification update failed";
            set_alert('danger', "Document Verification update failed");
        }
        return $data;
    }

    private function university_shortlisting($post_date)
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
            $university_shortlisting = !empty($this->input->post("university_shortlisting")) ? json_decode($this->input->post("university_shortlisting"), true) : [];
            $university_shortlisting_insert_arr = [];
            $university_shortlisting_update_arr = [];
            if (!empty($university_shortlisting)) {
                $this->db->where('client_id', $client_id);
                $this->db->update(db_prefix() . 'client_university_shortlisting', array("status" => 0));
                foreach ($university_shortlisting as $university_s) {
                    $this->db->select("id");
                    $this->db->where(array('client_id' => $client_id, "university_name" => $university_s["university_name"], "country_name" => $university_s["country_name"]));
                    $check_ = $this->db->get(db_prefix() . 'client_university_shortlisting')->row();

                    if (!empty($check_->id)) {
                        array_push($university_shortlisting_update_arr, array("university_name" => $university_s["university_name"], "country_name" => $university_s["country_name"], "vendor_id" => $university_s["vendor"], "status" => 1, "university_status" => 1, "id" => $check_->id, 'updated_by' => get_staff_user_id(), 'updated_date' => date('Y-m-d H:i:s')));
                    } else {
                        array_push($university_shortlisting_insert_arr, array("client_id" => $client_id, "university_name" => $university_s["university_name"], "country_name" => $university_s["country_name"], "vendor_id" => $university_s["vendor"], "university_status" => 1, "status" => 1, "created_by" => get_staff_user_id(), "created_date" => date('Y-m-d H:i:s')));
                    }
                }
            }

            $admissionpreferences = $this->clients_model->getAdmissionPreferences($client_id);

            $check_primary_university_exist = $this->checkUniversityExists($university_shortlisting, $admissionpreferences->primary_university, $admissionpreferences->primary_country);
            if ($check_primary_university_exist === false) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Primary University Selection is Mandatory.';
                return $data;
                die;
            }
            if (empty($admissionpreferences->primary_university) ||  empty($admissionpreferences->primary_country)) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something wrong check primary country & university.';
                return $data;
                die;
            }
            $update_university = "";
            if (!empty($university_shortlisting_insert_arr) || !empty($university_shortlisting_update_arr)) {
                if (!empty($university_shortlisting_insert_arr)) {
                    $update_university =  $this->db->insert_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_insert_arr);
                }

                if (!empty($university_shortlisting_update_arr)) {
                    $update_university = $this->db->update_batch(db_prefix() . "client_university_shortlisting", $university_shortlisting_update_arr, "id");
                }


                $rows_affected = $this->db->affected_rows();

                if (!empty($post_date) && $post_date['save'] == 1) {
                    $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
                    $ids = array_column($university_shortlisting_data, "id");
                    $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));

                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => UNIVERSITY_SHORTLISTING, "applicant_sub_status" => UNIVERSITY_APPLIED));
                    return  $data = [
                        'resp_code'               => 'RCS',
                        'resp_desc'               => "University shortlisting updated successfully.",
                        'ids'                     => $ids,
                        'university_shortlisting' => $university_shortlisting_data
                    ];
                    die;
                }
                if ($rows_affected) {
                    // Check required documents for stage 3
                    $check_documents = $this->check_documents(4);

                    if (empty($check_documents)) {
                        // If no missing documents, update applicant tracker stage
                        $this->update_applicant_tracker_stages($client_id, $tracker_id);
                        // $this->db->where("userid", $client_id);
                        // $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => ADMISSION, "applicant_sub_status" => ADMISSION_LETTER_PENDING));
                        $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
                        $ids = array_column($university_shortlisting_data, "id");

                        $check_primary_university_exist = $this->checkUniversityExists($university_shortlisting_data, $admissionpreferences->primary_university, $admissionpreferences->primary_country);

                        // Default status update
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', [
                            "applicant_status" => 0,
                            "applicant_stage"  => ADMISSION,
                            "applicant_sub_status" => ADMISSION_LETTER_PENDING
                        ]);

                        // If partner or application date exists AND application_file is empty → APPLY
                        if (
                            ((!empty($check_primary_university_exist['partner']) && !empty($check_primary_university_exist['partner']) != 0) || (!empty($check_primary_university_exist['application_date'])) &&  $check_primary_university_exist['application_date'] != "0000-00-00")
                            && empty($check_primary_university_exist['application_file'])
                        ) {
                            $this->db->where("userid", $client_id);
                            $this->db->update(db_prefix() . 'clients', [
                                "applicant_status" => 0,
                                "applicant_stage"  => ADMISSION,
                                "applicant_sub_status" => ADMISSION_LETTER_APPLY
                            ]);
                        }

                        // If partner or application date exists AND application_file exists → RECEIVED
                        if (
                            (!empty($check_primary_university_exist['partner']) || (!empty($check_primary_university_exist['application_date']) && $check_primary_university_exist['application_date'] != "0000-00-00"))
                            && !empty($check_primary_university_exist['application_file'])
                        ) {
                            $this->db->where("userid", $client_id);
                            $this->db->update(db_prefix() . 'clients', [
                                "applicant_status" => 0,
                                "applicant_stage"  => ADMISSION,
                                "applicant_sub_status" => ADMISSION_LETTER_RECEIVED
                            ]);
                        }

                        // Insert activity log for university shortlisting update
                        $this->db->insert(db_prefix() . 'application_activity_log', array(
                            "description" => "University shortlisting completed and updated by " . get_staff_full_name(get_staff_user_id()),
                            "date"        => date('Y-m-d H:i:s'),
                            "staffid"     => get_staff_user_id(),
                            "client_id"   => $client_id
                        ));
                    } else {
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => UNIVERSITY_SHORTLISTING, "applicant_sub_status" => UNIVERSITY_DOC_PENDING));
                    }
                }

                // Fetch updated university shortlisting data
                $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
                $ids = array_column($university_shortlisting_data, "id");

                if ($update_university) {
                    if (!empty($check_documents)) {
                        // If required documents are missing
                        $doc_names = implode(", ", $check_documents);
                        $message = "{$doc_names} are mandatory to proceed to the next step.";

                        $data = [
                            'resp_code'               => 'ERR',
                            'resp_desc'               => "University shortlisting updated successfully. " . $message,
                            'ids'                     => $ids,
                            'university_shortlisting' => $university_shortlisting_data
                        ];

                        set_alert('danger', "University shortlisting updated successfully. " . $message);
                    } else {
                        // If all required documents are available
                        $data = [
                            'resp_code'               => 'RCS',
                            'resp_desc'               => "University shortlisting updated successfully.",
                            'ids'                     => $ids,
                            'university_shortlisting' => $university_shortlisting_data
                        ];

                        set_alert('success', "University shortlisting updated successfully.");
                    }
                } else {
                    // Handle university update failure
                    $data = [
                        'resp_code' => 'ERR',
                        'resp_desc' => _l('update_customer_failed_successfully', _l('client'))
                    ];

                    set_alert('danger', _l('update_customer_failed_successfully', _l('client')));
                }
            } else {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something bad happen.';
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        return $data;
    }



    function select_primary_university()
    {
        $primary_university = $this->input->post('primaryUniversity', true);
        $primary_country = $this->input->post('primaryCountry', true);
        $client_id = $this->input->post('client_id', true);

        if (!empty($primary_university) && !empty($primary_country) && !empty($client_id)) {
            $this->db->where("userid", $client_id);
            $update = $this->db->update(db_prefix() . 'admission_preferences', [
                "primary_university" => $primary_university,
                "primary_country"    => $primary_country
            ]);

            if ($update) {
                echo json_encode([
                    'resp_code' => 'RCS',
                    'resp_desc' => 'Primary university and country updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Update failed. Please try again.'
                ]);
            }
        } else {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Required fields are missing.'
            ]);
        }
    }
    function reset_university_shortlisting()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
            echo json_encode($data);
            return;
        }

        $client_id = $this->input->post("client_id", true);

        if (empty($client_id)) {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Client ID is required';
            echo json_encode($data);
            return;
        }

        // Delete old shortlisting records
        $this->db->delete(db_prefix() . "client_university_shortlisting", ["client_id" => $client_id]);

        // Get new shortlisting data
        $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
        $ids = array_column($university_shortlisting_data, "id");

        $this->db->where("userid", $client_id);
        $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => UNIVERSITY_SHORTLISTING, "applicant_sub_status" => UNIVERSITY_SHORTLISTING_PENDING));

        $this->update_applicant_tracker_stages($client_id, 1);

        $data = [
            'resp_code'               => 'RCS',
            'resp_desc'               => 'University shortlisting reset successfully.',
            'ids'                     => $ids,
            'university_shortlisting' => $university_shortlisting_data
        ];

        echo json_encode($data);
    }


    private function admission_letter()
    {
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method',
            ];
        }

        $client_id = $this->input->post("client_id");
        $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
        $admission = !empty($this->input->post("admission")) ? json_decode($this->input->post("admission"), true) : [];
        $save = !empty($this->input->post("save")) ? $this->input->post("save") : 0;
        $admission_letter = [];
        $files = $_FILES;

        if (empty($client_id) || empty($admission)) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid client ID or admission data received',
            ];
        }

        $batch_update_data = [];

        foreach ($admission as $row) {
            if (empty($row['id'])) {
                continue; // Skip invalid entries
            }

            $university_name = $row['university'] ?? '';
            $country_name = $row['country'] ?? '';

            $update_entry = [
                'id'                => $row['id'],
                'partner'           => $row['partner'] ?? null,
                'application_date'  => $row['application_date'] ?? null,
                'documents'         => !empty($row['documents']) ? $row['documents'] : '',
            ];
            $admission_letter[] = $row['addmission_letter_url'];

            $file_input_name = "admission_letter_" . $row['id'];

            if (isset($files[$file_input_name]) && !empty($files[$file_input_name]['name'])) {
                $document = $files[$file_input_name];

                if ($document['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($document['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid("admission_letter_") . "." . $file_extension;

                    $uploaded_file = upload_applicant_documents($client_id, [
                        "name"      => $file_name,
                        "type"      => $document['type'],
                        "tmp_name"  => $document['tmp_name'],
                        "error"     => $document['error'],
                        "size"      => $document['size']
                    ]);

                    if (!empty($uploaded_file["file_path"])) {
                        $update_entry['application_file'] = $uploaded_file["file_path"];
                        $update_entry['application_updated_date'] = date('Y-m-d H:i:s');
                    }

                    // Log file upload
                    $this->db->insert(db_prefix() . 'application_activity_log', [
                        "description" => "Admission letter uploaded for {$university_name}, {$country_name} by staff ID: " . get_staff_user_id(),
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "client_id"   => $client_id
                    ]);
                }
            }

            $batch_update_data[] = $update_entry;
        }

        if (!empty($batch_update_data)) {
            $this->db->update_batch(db_prefix() . 'client_university_shortlisting', $batch_update_data, 'id');


            $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
            $admissionpreferences = $this->clients_model->getAdmissionPreferences($client_id);

            if ($save == 1) {
                $data = [
                    'resp_code'               => 'RCS',
                    'resp_desc'               => "Admission Letter updated successfully.",
                    'university_shortlisting' => $university_shortlisting_data
                ];


                $check_primary_university_exist = $this->checkUniversityExists($university_shortlisting_data, $admissionpreferences->primary_university, $admissionpreferences->primary_country);

                // Default status update
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', [
                    "applicant_status" => 0,
                    "applicant_stage"  => ADMISSION,
                    "applicant_sub_status" => ADMISSION_LETTER_PENDING
                ]);




                // If partner or application date exists AND application_file is empty → APPLY
                if (
                    (!empty($check_primary_university_exist['partner']) || (!empty($check_primary_university_exist['application_date']) && $check_primary_university_exist['application_date'] != "0000-00-00"))
                    && empty($check_primary_university_exist['application_file'])
                ) {
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', [
                        "applicant_status" => 0,
                        "applicant_stage"  => ADMISSION,
                        "applicant_sub_status" => ADMISSION_LETTER_APPLY
                    ]);
                }

                // If partner or application date exists AND application_file exists → RECEIVED
                if (
                    (!empty($check_primary_university_exist['partner']) || (!empty($check_primary_university_exist['application_date']) && $check_primary_university_exist['application_date'] != "0000-00-00"))
                    && !empty($check_primary_university_exist['application_file'])
                ) {
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', [
                        "applicant_status" => 0,
                        "applicant_stage"  => ADMISSION,
                        "applicant_sub_status" => ADMISSION_LETTER_RECEIVED
                    ]);
                }

                $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));

                return $data;
                die;
            }

            if (empty($admissionpreferences->primary_university) ||  empty($admissionpreferences->primary_country)) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Something wrong check primary country & university.';
                return $data;
                die;
            }

            $check_primary_university_exist = $this->checkUniversityExists($university_shortlisting_data, $admissionpreferences->primary_university, $admissionpreferences->primary_country);

            if ($check_primary_university_exist === false) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Primary University Selection is Mandatory.';
                return $data;
                die;
            } else {
                if (empty($check_primary_university_exist["application_file"])) {
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => ADMISSION, "applicant_sub_status" => ADMISSION_LETTER_APPLY));
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = "The primary university application letter for '" . $check_primary_university_exist["university_name"] . "' is mandatory to proceed to the next step.";
                    return $data;
                    die;
                }
            }
            if (!empty($_FILES) || !empty($admission_letter)) {
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0, "applicant_stage" => ENTRANCE_EXAM, "applicant_sub_status" => ENTRANCE_EXAM_PENDING));
                $entrance_exams =  $this->clients_model->entrance_exams($client_id);
                $this->update_applicant_tracker_stages($client_id, $tracker_id);
                $entrance_exams = array_reduce($entrance_exams, function ($acc, $row) {
                    $acc[$row['university_name']] = ($acc[$row['university_name']] ?? []);
                    $acc[$row['university_name']][] = $row;
                    return $acc;
                }, []);
                return [
                    'resp_code' => 'RCS',
                    'resp_desc' => "Admission data updated successfully.",
                    'entrance_exams' => $entrance_exams
                ];
            } else {
                return [
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Admission data updated successfully.Waiting for admission letter.',
                ];
            }
        } else {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid records to update',
            ];
        }
    }

    private function entrance_exam()
    {
        // Ensure the request is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method',
            ];
        }

        // Retrieve input values
        $client_id = $this->input->post("client_id");
        $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
        $entrance_exam_data = $this->input->post("entrance_exam");
        $legalization =  $this->clients_model->legalization_data($client_id);

        // Validate client_id
        if (empty($client_id)) {
            return [
                "resp_code" => "ERR",
                "resp_desc" => "Client ID is required",
            ];
        }

        // Validate entrance exam data
        if (empty($entrance_exam_data)) {
            return [
                "resp_code" => "ERR",
                "resp_desc" => "No entrance exam data received",
            ];
        }

        // Decode entrance exam data properly
        $entrance_exam_data = json_decode($entrance_exam_data, true);

        if (!is_array($entrance_exam_data)) {
            return [
                "resp_code" => "ERR",
                "resp_desc" => "Invalid entrance exam data format",
            ];
        }

        $update_data = [];
        $insert_data = [];

        foreach ($entrance_exam_data as $exam) {
            // Validate required fields
            if ($exam["manually"] == 1) {
                if (empty($exam["client_id"]) || empty($exam["exam_id"]) || empty($exam["status"])) {
                    return [
                        "resp_code" => "ERR",
                        "resp_desc" => "Missing required entrance exam fields",
                    ];
                }
            } else {
                if (empty($exam["batch_id"]) || empty($exam["client_id"]) || empty($exam["exam_id"]) || empty($exam["status"])) {
                    return [
                        "resp_code" => "ERR",
                        "resp_desc" => "Missing required entrance exam fields",
                    ];
                }
            }

            $batch_id = $exam["batch_id"];
            $exam_id = $exam["exam_id"];
            $status = $exam["status"];
            $exam_date = $exam["exam_date"];

            // Check if the record exists
            $existing_exam = $this->db->get_where(db_prefix() . "clients_exam_status", [
                "exam_id" => $exam_id,
                "client_id" => $client_id,
                "batch_id" => $batch_id,
            ])->row_array();

            if ($existing_exam) {
                // Prepare data for batch update
                $update_data[] = [
                    "id" => $existing_exam["id"],
                    "exam_id" => $exam_id,
                    "client_id" => $client_id,
                    "batch_id" => $batch_id,
                    "status" => $status,
                ];
            } else {
                // Prepare data for batch insert
                $insert_data[] = [
                    "exam_id" => $exam_id,
                    "client_id" => $client_id,
                    "batch_id" => $batch_id,
                    "status" => $status,
                ];
            }

            $client_exam_data[] = [
                "client_id"  => $client_id,
                "exam_date"  => $exam_date,
                "exam_id"    => $exam_id,
                "batch_id"   => $batch_id,
                "m_university_name" => $exam["m_university_name"],
                "m_university_id" => $exam["m_university_id"]
            ];

            if ($exam["manually"] == 1) {

                $this->db->where(array("batch_id" => $batch_id, "client_id" => $client_id))
                    ->delete(db_prefix() . 'clients_exam');

                // Insert data into the database
                if (!empty($client_exam_data)) {
                    $this->db->insert_batch(db_prefix() . 'clients_exam', $client_exam_data);
                }
            }
        }



        if (!empty($update_data)) {
            $this->db->update_batch(db_prefix() . "clients_exam_status", $update_data, "id");
        }

        // Execute batch insert
        if (!empty($insert_data)) {
            $this->db->insert_batch(db_prefix() . "clients_exam_status", $insert_data);
        }

        // Update client applicant status
        $update_client_data = [
            "applicant_status" => 0,
            "applicant_stage" => LEGALIZATION,
            "applicant_sub_status" => LEGALIZATION_PENDING,
        ];



        $this->db->where("userid", $client_id);
        $this->db->update(db_prefix() . 'clients', $update_client_data);

        // Update applicant tracker stages
        $this->update_applicant_tracker_stages($client_id, $tracker_id);
        $this->db->insert(db_prefix() . 'application_activity_log', array(
            "description" => "Entrance Exam updated by " . get_staff_full_name(get_staff_user_id()),
            "date"        => date('Y-m-d H:i:s'),
            "staffid"     => get_staff_user_id(),
            "client_id"   => $client_id
        ));

        return [
            "resp_code" => "RCS",
            "resp_desc" => "Entrance exam data processed successfully",
            "legalization" => $legalization
        ];
    }

    private function Legalization()
    {

        $data = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method',
            ];
        }

        $client_id = $this->input->post("client_id");
        $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
        $legalization = !empty($this->input->post("legalization")) ? json_decode($this->input->post("legalization"), true) : [];
        $legalization_doc = [];
        $files = $_FILES;

        if (empty($client_id) || empty($legalization)) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid client ID or legalization data received',
            ];
        }

        $batch_update_data = [];

        foreach ($legalization as $row) {
            if (empty($row['id'])) {
                continue; // Skip invalid entries
            }

            $update_entry = [
                'id'                => $row['id'],
                'ministry_document_recived'           => $row['ministry_doc_received'] ?? 0,
                'contract_signed'  => $row['contract_signed'] ?? 0
            ];


            $file_input_name = "ministry_doc_payment_" . $row['id'];

            if (isset($files[$file_input_name]) && !empty($files[$file_input_name]['name'])) {
                $document = $files[$file_input_name];

                if ($document['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($document['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid("ministry_payment_") . "." . $file_extension;

                    $uploaded_file = upload_applicant_documents($client_id, [
                        "name"      => $file_name,
                        "type"      => $document['type'],
                        "tmp_name"  => $document['tmp_name'],
                        "error"     => $document['error'],
                        "size"      => $document['size']
                    ]);

                    if (!empty($uploaded_file["file_path"])) {
                        $update_entry['ministry_payment'] = $uploaded_file["file_path"];
                        $update_entry['ministry_payment_date'] = date('Y-m-d H:i:s');
                    }

                    // Log file upload
                    $this->db->insert(db_prefix() . 'application_activity_log', [
                        "description" => "Ministry Payment Slip  uploaded by staff ID: " . get_staff_user_id(),
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "client_id"   => $client_id
                    ]);
                }
            }

            $batch_update_data[] = $update_entry;
        }



        if (!empty($batch_update_data)) {
            $update = $this->db->update_batch(db_prefix() . 'client_university_shortlisting', $batch_update_data, 'id');

            $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);

            $update_client_data = [
                "applicant_status" => 0,
                "applicant_stage" => FEES_DEPOSITE,
                "applicant_sub_status" => FEES_DEPOSITE_PENDING,
            ];

            $this->db->where("userid", $client_id);
            $this->db->update(db_prefix() . 'clients', $update_client_data);

            // Update applicant tracker stages
            $this->update_applicant_tracker_stages($client_id, $tracker_id);

            $this->db->insert(db_prefix() . 'application_activity_log', array(
                "description" => "Legalization data updated by " . get_staff_full_name(get_staff_user_id()),
                "date"        => date('Y-m-d H:i:s'),
                "staffid"     => get_staff_user_id(),
                "client_id"   => $client_id
            ));

            return [
                'resp_code' => 'RCS',
                'resp_desc' => "Legalization data updated successfully.",
                'fees_deposite' => $university_shortlisting_data
            ];
        } else {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid records to update',
            ];
        }
    }

    private function feesDeposite()
    {

        $data = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method',
            ];
        }

        $client_id = $this->input->post("client_id");
        $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
        $fees_deposite = !empty($this->input->post("fees_deposite")) ? json_decode($this->input->post("fees_deposite"), true) : [];
        $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);

        $legalization_doc = [];
        $files = $_FILES;

        if (empty($client_id) || empty($fees_deposite)) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid client ID or Fees data received',
            ];
        }

        $batch_update_data = [];

        foreach ($fees_deposite as $row) {
            if (empty($row['id'])) {
                continue; // Skip invalid entries
            }

            $update_entry = [
                'id'                => $row['id'],
                'fees_deposite_date'           => $row['date_of_payment'] ?? "",
                'payment_amount'           => $row['payment_amount'] ?? ""
            ];


            $file_input_name = "payment_slip_" . $row['id'];

            if (isset($files[$file_input_name]) && !empty($files[$file_input_name]['name'])) {
                $document = $files[$file_input_name];

                if ($document['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($document['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid("ministry_payment_") . "." . $file_extension;

                    $uploaded_file = upload_applicant_documents($client_id, [
                        "name"      => $file_name,
                        "type"      => $document['type'],
                        "tmp_name"  => $document['tmp_name'],
                        "error"     => $document['error'],
                        "size"      => $document['size']
                    ]);

                    if (!empty($uploaded_file["file_path"])) {
                        $update_entry['fees_deposite_slip'] = $uploaded_file["file_path"];
                    }

                    // Log file upload
                    $this->db->insert(db_prefix() . 'application_activity_log', [
                        "description" => "Fees Payment Slip  uploaded by staff ID: " . get_staff_user_id(),
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "client_id"   => $client_id
                    ]);
                }
            }


            $file_input_name = "university_payment_slip_" . $row['id'];

            if (isset($files[$file_input_name]) && !empty($files[$file_input_name]['name'])) {
                $document = $files[$file_input_name];

                if ($document['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($document['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid("ministry_payment_") . "." . $file_extension;

                    $uploaded_file = upload_applicant_documents($client_id, [
                        "name"      => $file_name,
                        "type"      => $document['type'],
                        "tmp_name"  => $document['tmp_name'],
                        "error"     => $document['error'],
                        "size"      => $document['size']
                    ]);

                    if (!empty($uploaded_file["file_path"])) {
                        $update_entry['university_fees_payment_slip'] = $uploaded_file["file_path"];
                    }

                    // Log file upload
                    $this->db->insert(db_prefix() . 'application_activity_log', [
                        "description" => "University Payment Slip  uploaded by staff ID: " . get_staff_user_id(),
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "client_id"   => $client_id
                    ]);
                }
            }

            $batch_update_data[] = $update_entry;
        }



        if (!empty($batch_update_data)) {
            $update = $this->db->update_batch(db_prefix() . 'client_university_shortlisting', $batch_update_data, 'id');


            $update_client_data = [
                "applicant_status" => 0,
                "applicant_stage" => INVITATION,
                "applicant_sub_status" => INVITATION_PENDING,
            ];

            $this->db->where("userid", $client_id);
            $this->db->update(db_prefix() . 'clients', $update_client_data);

            // Update applicant tracker stages
            $this->update_applicant_tracker_stages($client_id, $tracker_id);
            $this->db->insert(db_prefix() . 'application_activity_log', array(
                "description" => "Fees data updated by " . get_staff_full_name(get_staff_user_id()),
                "date"        => date('Y-m-d H:i:s'),
                "staffid"     => get_staff_user_id(),
                "client_id"   => $client_id
            ));

            return [
                'resp_code' => 'RCS',
                'resp_desc' => "Fees Deposite data updated successfully.",
                'invitation' => $university_shortlisting_data
            ];
        } else {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid records to update',
            ];
        }
    }

    private function invitationLetter()
    {

        $data = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method',
            ];
        }

        $client_id = $this->input->post("client_id");
        $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
        $invitation = !empty($this->input->post("invitation")) ? json_decode($this->input->post("invitation"), true) : [];
        $university_shortlisting_data = $this->clients_model->university_shortlisting($client_id);
        $save = !empty($this->input->post("save")) ? $this->input->post("save") : 0;

        $files = $_FILES;

        if (empty($client_id) || empty($invitation)) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid client ID or Fees data received',
            ];
        }

        $batch_update_data = [];

        foreach ($invitation as $row) {
            if (empty($row['id'])) {
                continue; // Skip invalid entries
            }

            $update_entry = [
                'id'                => $row['id'],
                'invitation_receiving_date'           => $row['receiving_date'] ?? "",
                'entry_date'           => $row['entry_date'] ?? "",
                'telex_no'           => $row['telex_no'] ?? ""
            ];


            $file_input_name = "invitation_letter_" . $row['id'];

            if (isset($files[$file_input_name]) && !empty($files[$file_input_name]['name'])) {
                $document = $files[$file_input_name];

                if ($document['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($document['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid("invitation_letter") . "." . $file_extension;

                    $uploaded_file = upload_applicant_documents($client_id, [
                        "name"      => $file_name,
                        "type"      => $document['type'],
                        "tmp_name"  => $document['tmp_name'],
                        "error"     => $document['error'],
                        "size"      => $document['size']
                    ]);

                    if (!empty($uploaded_file["file_path"])) {
                        $update_entry['invitation_letter'] = $uploaded_file["file_path"];
                    }

                    // Log file upload
                    $this->db->insert(db_prefix() . 'application_activity_log', [
                        "description" => "Invitation letter uploaded by staff ID: " . get_staff_user_id(),
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "client_id"   => $client_id
                    ]);
                }
            }

            $batch_update_data[] = $update_entry;
        }



        if (!empty($batch_update_data)) {
            $update = $this->db->update_batch(db_prefix() . 'client_university_shortlisting', $batch_update_data, 'id');


            if ($save == 1) {
                $data = [
                    'resp_code'               => 'RCS',
                    'resp_desc'               => "Invitation Letter updated successfully.",
                    'university_shortlisting' => $university_shortlisting_data
                ];

                $update_client_data = [
                    "applicant_status" => 0,
                    "applicant_stage" => INVITATION,
                    "applicant_sub_status" => INVITATION_RECEIVED,
                ];

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);

                $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));
                return $data;
                die;
            }


            $update_client_data = [
                "applicant_status" => 0,
                "applicant_stage" => THIRD_PAYMENT,
                "applicant_sub_status" => THIRD_PAYMENT_PENDING,
            ];

            $this->db->where("userid", $client_id);
            $this->db->update(db_prefix() . 'clients', $update_client_data);

            // Update applicant tracker stages
            $this->update_applicant_tracker_stages($client_id, $tracker_id);
            $this->db->insert(db_prefix() . 'application_activity_log', array(
                "description" => "Invitation data updated by " . get_staff_full_name(get_staff_user_id()),
                "date"        => date('Y-m-d H:i:s'),
                "staffid"     => get_staff_user_id(),
                "client_id"   => $client_id
            ));
            return [
                'resp_code' => 'RCS',
                'resp_desc' => "Invitation Letter data updated successfully.",
                'invitation' => $university_shortlisting_data
            ];
        } else {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid records to update',
            ];
        }
    }

    public function remove_visa_details()
    {
        // Check if POST request has data
        if ($this->input->post()) {
            // Retrieve the visa ID and user ID from the POST request
            $visa_id = $this->input->post('visa_id');
            $userid  = $this->input->post('client_id');

            // Ensure visa_id and userid are provided
            if (empty($visa_id) || empty($userid)) {
                $response = [
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Missing required parameters (visa_id or client_id).',
                ];
            } else {
                // Perform the delete operation
                $this->db->where(['userid' => $userid, 'id' => $visa_id]);
                $this->db->delete(db_prefix() . 'visa_details');

                // Check if the delete was successful
                if ($this->db->affected_rows() > 0) {
                    // Success response
                    $response = [
                        'resp_code' => 'RCS',
                        'resp_desc' => 'Visa details deleted successfully.',
                    ];
                } else {
                    // If no rows were deleted, it may mean the record doesn't exist
                    $response = [
                        'resp_code' => 'ERR',
                        'resp_desc' => 'Visa details not found or already deleted.',
                    ];
                }
            }
        } else {
            // Invalid request method
            $response = [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method.',
            ];
        }

        // Output response as JSON
        echo json_encode($response);
    }

    private function visaLetter()
    {
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid request method',
            ];
        }

        $client_id = $this->input->post("client_id");
        $tracker_id = !empty($this->input->post("tracker_id")) ? $this->input->post("tracker_id") : 1;
        $visa = !empty($this->input->post("visa")) ? json_decode($this->input->post("visa"), true) : [];
        $files = $_FILES;


        if (empty($this->input->post("save"))) {
            $check_documents = $this->check_documents(10);
            if (!empty($check_documents)) {
                // If required documents are missing
                $doc_names = implode(", ", $check_documents);
                $message = "{$doc_names} are mandatory to proceed to the next step.";

                $data = [
                    'resp_code'               => 'ERR',
                    'resp_desc'               =>  $message,
                ];

                $visa_information_check = visa_details($client_id, 1);

                if (empty($visa_information_check)) {
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => VISA,
                        "applicant_sub_status" => VISA_PENDING,
                    ];
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', $update_client_data);
                } else {

                    if ($visa_information_check[0]["payment_date"] != "0000-00-00") {
                        $update_client_data = [
                            "applicant_status" => 0,
                            "applicant_stage" => VISA,
                            "applicant_sub_status" => VISA_APPLY
                        ];
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', $update_client_data);
                    }
                }
                $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));
                return $data;
                die;
            }
        }

        if (empty($this->input->post("save"))) {
            $university_shortlisting = $this->clients_model->university_shortlisting($client_id, 1);
            $country_names = array_column($university_shortlisting, "country_name");
            $resultOrignal = validate_orignal_documents([$client_id], $country_names);
            if (!empty($resultOrignal["error"]) && $resultOrignal["error"] == 1) {
                $data = [
                    'resp_code'               => 'ERR',
                    'resp_desc'               =>  $resultOrignal["message"][0],
                ];

                $visa_information_check = visa_details($client_id, 1);

                if (empty($visa_information_check)) {
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => VISA,
                        "applicant_sub_status" => VISA_PENDING,
                    ];
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', $update_client_data);
                } else {

                    if ($visa_information_check[0]["payment_date"] != "0000-00-00") {
                        $update_client_data = [
                            "applicant_status" => 0,
                            "applicant_stage" => VISA,
                            "applicant_sub_status" => VISA_APPLY
                        ];
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', $update_client_data);
                    }

                    if ($visa_information_check[0]["visa_rejected"] == 1) {
                        $update_client_data = [
                            "applicant_status" => 0,
                            "applicant_stage" => VISA,
                            "applicant_sub_status" => VISA_APPLY
                        ];
                        $this->db->where("userid", $client_id);
                        $this->db->update(db_prefix() . 'clients', $update_client_data);
                    }
                }
                $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));
                return $data;
            }
        }

        $batch_update_data = [];
        $batch_insert_data = [];
        $received_status_pass = false;
        $visa_sub_stage = VISA_PENDING;
        foreach ($visa as $key => $row) {
            $received_status_pass = false;
            $visa_sub_stage = VISA_PENDING;
            $data_ = [];

            $visa_status = 1;
            $received_status = 0;
            if (!empty($row['visa_payment_date'])) {
                $visa_status = 2;
                $visa_sub_stage = VISA_APPLY;
            }
            if (!empty($row['visa_receiving_date'])) {
                $visa_status = 3;
                $received_status = 1;

                $visa_sub_stage = VISA_APPLY;
            }

            if (!empty($row['visa_receiving_date']) && !empty($row['visa_payment_date'])) {
                $received_status_pass = true;
            }


            if (!empty($row['visa_rejected']) && $row['visa_rejected'] == 1) {
                $visa_status = 4;
                $visa_sub_stage = VISA_REJECTED;
            }
            $data_ = [
                'id'                => $row['id'],
                'userid'           => $client_id ?? "",
                'vendor_id'           => $row['visa_vendor'] ?? "",
                'courier_date'           => $row['visa_date'] ?? "",
                'cost'           =>     $row['visa_cost'] ?? "",
                'payment_date'           => $row['visa_payment_date'] ?? "",
                'payment_mode'           => $row['visa_payment_mode'] ?? "",
                'courier_type'           => $row['visa_courier_type'] ?? "",
                'entry_date'           => $row['visa_entry_date'] ?? "",
                'updated_by'           =>  get_staff_user_id(),
                'updated_at'           => date('Y-m-d H:i:s'),
                'receiving_date'           => $row['visa_receiving_date'] ?? "",
                'status'           =>      $visa_status,
                'received_status'           => $received_status
            ];


            $file_input_name = "visa_file_" . $key;

            if (isset($files[$file_input_name]) && !empty($files[$file_input_name]['name'])) {
                $document = $files[$file_input_name];

                if ($document['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($document['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid("visa_letter") . "." . $file_extension;

                    $uploaded_file = upload_applicant_documents($client_id, [
                        "name"      => $file_name,
                        "type"      => $document['type'],
                        "tmp_name"  => $document['tmp_name'],
                        "error"     => $document['error'],
                        "size"      => $document['size']
                    ]);

                    if (!empty($uploaded_file["file_path"])) {
                        $data_['file'] = $uploaded_file["file_path"];
                    }

                    // Log file upload
                    $this->db->insert(db_prefix() . 'application_activity_log', [
                        "description" => "Visa details uploaded by staff ID: " . get_staff_user_id(),
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "client_id"   => $client_id
                    ]);
                }
            }

            if (!empty($data_["id"])) {
                $batch_update_data[] = $data_;
            } else {
                $data_["created_at"] = date('Y-m-d H:i:s');
                $data_["created_by"] = get_staff_user_id();
                $batch_insert_data[] = $data_;
            }
        }






        if (!empty($batch_update_data) || !empty($batch_insert_data)) {
            if (!empty($batch_update_data)) {
                $update = $this->db->update_batch(db_prefix() . 'visa_details', $batch_update_data, 'id');
            }

            if (!empty($batch_insert_data)) {
                $update = $this->db->insert_batch(db_prefix() . 'visa_details', $batch_insert_data);
            }


            $visa_information_check = visa_details($client_id, 1);
            if (empty($visa_information_check)) {
                if (!empty($this->input->post("save"))) {
                    $responseData = [
                        'resp_code' => 'RCS',
                        'resp_desc' => "Visa letter data updated successfully.",
                        'visa_details' => visa_details($client_id, 0, 1)
                    ];
                } else {
                    $responseData = [
                        'resp_code' => 'ERR',
                        'resp_desc' => "Visa letter data updated successfully. However, the information is incomplete to proceed to the next step.",
                        'visa_details' => visa_details($client_id, 0, 1)
                    ];
                }

                $update_client_data = [
                    "applicant_status" => 0,
                    "applicant_stage" => VISA,
                    "applicant_sub_status" => $visa_sub_stage,
                ];

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);

                $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));

                return $responseData;
                die;
            }



            if ($received_status_pass == true && empty($this->input->post("save"))) {
                $update_client_data = [
                    "applicant_status" => 0,
                    "applicant_stage" => VISA,
                    "applicant_sub_status" => VISA_STAMP,
                ];

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);
                $this->update_applicant_tracker_stages($client_id, $tracker_id);
                $responseData = [
                    'resp_code' => 'RCS',
                    'resp_desc' => "Visa Letter data updated successfully.",
                    'visa_details' => visa_details($client_id, 0, 1)

                ];
            } else {
                $update_client_data = [
                    "applicant_status" => 0,
                    "applicant_stage" => VISA,
                    "applicant_sub_status" => $visa_sub_stage,
                ];

                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);
                if (!empty($this->input->post("save"))) {
                    $responseData = [
                        'resp_code' => 'RCS',
                        'resp_desc' => "Visa letter data updated successfully.",
                        'visa_details' => visa_details($client_id, 0, 1)
                    ];
                } else {
                    $responseData = [
                        'resp_code' => 'ERR',
                        'resp_desc' => "Visa letter data updated successfully. However, the information is incomplete to proceed to the next step.",
                        'visa_details' => visa_details($client_id, 0, 1)
                    ];
                }
                $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));
            }

            // Update applicant tracker stages
            $this->db->insert(db_prefix() . 'application_activity_log', array(
                "description" => "Visa data updated by " . get_staff_full_name(get_staff_user_id()),
                "date"        => date('Y-m-d H:i:s'),
                "staffid"     => get_staff_user_id(),
                "client_id"   => $client_id
            ));

            return $responseData;
        } else {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'No valid records to update',
                'visa_details' => visa_details($client_id, 0, 1)
            ];
        }
    }
    private function check_documents($stage)
    {
        // Retrieve POST data safely
        $lead_type = filter_input(INPUT_POST, "lead_type", FILTER_SANITIZE_STRING);
        $client_id = filter_input(INPUT_POST, "client_id", FILTER_SANITIZE_STRING);

        // Decode university_shortlisting JSON safely
        // $university_shortlisting = isset($_POST["university_shortlisting"]) ? json_decode($_POST["university_shortlisting"], true) : [];
        $university_shortlisting = $this->clients_model->university_shortlisting($client_id, 1);

        if (!is_array($university_shortlisting)) {
            $university_shortlisting = [];
        }

        // Extract country names
        // $country_names = array_column($university_shortlisting, "country_name");

        $country_names = array_column($university_shortlisting, "country_name");

        if (in_array("georgia", array_map('strtolower', $country_names))) {
            // If 'georgia' is present in any case (e.g., 'Georgia', 'GEORGIA')
            $documents_type = get_documents($lead_type, $country_names, "", $stage);
        } else {
            $country_names[] = "Rest"; // Add 'Rest' to the list
            $documents_type = get_documents($lead_type, $country_names, "", $stage);
        }

        // Fetch document types based on lead type and country names
        $documents_type = get_documents($lead_type, $country_names, "", $stage);

        // Map document types by ID
        $documents_type_ids = !empty($documents_type) ? array_column($documents_type, null, "id") : [];

        // Fetch client documents
        $applicant_documents = get_clients_documents($client_id);
        $client_documents = (!empty($applicant_documents[0]["data"])) ? json_decode($applicant_documents[0]["data"], true) : [];

        // Map client documents by ID
        $client_documents_ids = !empty($client_documents) ? array_column($client_documents, null, "id") : [];

        // Initialize required documents array
        $doc_required = [];

        // Compare required documents with client documents
        if (!empty($documents_type_ids)) {
            foreach ($documents_type_ids as $key => $doc) {
                if (!isset($client_documents_ids[$key]) || empty($client_documents_ids[$key]['approval_status']) || $client_documents_ids[$key]['approval_status'] != 1) {
                    $doc_required[] = $doc["name"];
                }
            }
        }

        return $doc_required; // Return the missing document names
    }

    function checkUniversityExists($array, $university_name, $country_name)
    {
        foreach ($array as $item) {
            if (
                isset($item['university_name'], $item['country_name']) && // Check if keys exist
                trim($item['university_name']) === trim($university_name) &&
                trim($item['country_name']) === trim($country_name)
            ) {

                return $item; // Found the university in the given country
            }
        }
        return false; // Not found
    }
    function email_send_trigger()
    {
        // Validate if client_id and type are set
        if (!isset($_POST["client_id"]) || !isset($_POST["type"])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'Missing required parameters.'
            ]);
            return;
        }

        $client_id = $_POST["client_id"];
        $type = $_POST["type"];
        $university_id = !empty($_POST["s_university_id"]) ? $_POST["s_university_id"] : 0;
        $university_name = !empty($_POST["s_university_name"]) ? $_POST["s_university_name"] : "";

        // Fetch client details
        $client = $this->clients_model->getBasicDetails($client_id);

        if (!$client || empty($client->email)) {
            http_response_code(404); // Not Found
            echo json_encode([
                'success' => false,
                'message' => 'Client email not found.'
            ]);
            return;
        }

        // Define email templates based on type
        $email_templates = [
            1 => 'Applicant_documentation_notification',
            2 => 'Applicant_entrance_exam',
            3 => 'Applicant_invitation_notification',
            4 => 'Applicant_visa_notification'
        ];

        // Validate email type
        if (!array_key_exists($type, $email_templates)) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email type provided.'
            ]);
            return;
        }

        // Attempt to send the email
        try {
            $email_sent = send_mail_template($email_templates[$type], $client->email, $client_id, get_staff_user_id(), "", $university_id, $university_name);

            if ($email_sent) {
                http_response_code(200); // OK
                echo json_encode([
                    'success' => true,
                    'message' => 'Email sent successfully.'
                ]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again later.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function whatsapp_message_send()
    {
        try {
            // Validate required POST parameters
            if (!isset($_POST["client_id"], $_POST["type"])) {
                http_response_code(400); // Bad Request
                echo json_encode([
                    'success' => false,
                    'message' => "Missing required parameters."
                ]);
                return;
            }

            // Retrieve and sanitize inputs
            $client_id = trim($_POST["client_id"]);
            $template_id = trim($_POST["type"]);
            $university_id = !empty($_POST["s_university_id"]) ? trim($_POST["s_university_id"]) : 0;
            $university_name = !empty($_POST["s_university_name"]) ? trim($_POST["s_university_name"]) : "";

            $attachments = [];

            // Check for required type and its related attachments
            if (!empty($template_id) && $template_id == 1) {
                $attachments = $this->clients_model->registration_attachments($client_id, 1);

                if (empty($attachments["registration_slip_invoice"])) {
                    http_response_code(400); // Bad Request
                    echo json_encode([
                        'success' => false,
                        'message' => "Registration slip is not generated."
                    ]);
                    return;
                }
            }

            // Attempt to send WhatsApp message
            $whatsapp_sent = whatsapp_message_send($client_id, $template_id, $attachments);

            if ($whatsapp_sent) {
                http_response_code(200); // OK
                echo json_encode([
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully.'
                ]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send WhatsApp message. Please try again later.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error

            // Log the error if a logging system is in place
            error_log("WhatsApp Message Send Error: " . $e->getMessage());

            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    public function generate_registration_slip()
    {
        $client_id = $_POST["client_id"] ?? '';
        $slip_generate = $_POST["slip_generate"] ?? '0';
        $email_send = $_POST["email_send"] ?? '0';
        $whatsapp_send = $_POST["whatsapp_send"] ?? '0';

        if (empty($client_id)) {
            http_response_code(400);
            echo json_encode([
                "resp_code" => "RCS",
                "resp_desc" => "Client ID is required."
            ]);
            return;
        }

        $client = $this->clients_model->getBasicDetails($client_id);
        if (!$client) {
            http_response_code(404);
            echo json_encode([
                "resp_code" => "ERR",
                "resp_desc" => "Client not found"
            ]);
            return;
        }

        if ($slip_generate == 1) {
            $generate_registration_slip = $this->registration_slip_preview($client_id);

            if ($generate_registration_slip["status"] !== "success") {
                http_response_code(500);
                echo json_encode([
                    "resp_code" => "ERR",
                    "resp_desc" => "Failed to generate registration slip. " . $generate_registration_slip["message"] ?? ''
                ]);
                return;
            }

            $response = [
                "resp_code" => "RCS",
                "resp_desc" => "Registration Slip generate successfully.",
            ];
            $response["slip_generate"] = 1;
            $response["slip_data"]["url"] = $generate_registration_slip["pdf_url"];

            echo json_encode($response);
            return;
        }


        $response = [
            "resp_code" => "RCS",
            "resp_desc" => "Registration slip generated successfully.",
        ];

        // Send email if required
        if (!empty($client->email) && $email_send == 1) {
            $email_status = send_mail_template('Applicant_new_registration', $client->email, $client_id, get_staff_user_id());
            if (!$email_status) {
                $response = [
                    "resp_code" => "ERR",
                    "resp_desc" => "Failed to send email.",
                ];
            } else {
                $response = [
                    "resp_code" => "RCS",
                    "resp_desc" => "Registration Email sent successfully.",
                ];
            }
            echo json_encode($response);
            return;
        }

        // Send WhatsApp message if required
        if ($whatsapp_send == 1) {
            $template_id = 1;
            if (!empty($template_id) && $template_id == 1) {
                $attachments = $this->clients_model->registration_attachments($client_id, 1);
                if (empty($attachments["url"])) {
                    http_response_code(400);

                    $response = [
                        "resp_code" => "ERR",
                        "resp_desc"
                        => "Registration slip is not generated.",
                    ];
                    echo json_encode($response);
                    return;
                }
                $whatsapp_sent = whatsapp_message_send($client_id, $template_id, $attachments);

                $response = [
                    "resp_code" => "RCS",
                    "resp_desc" => "WhatsApp message sent successfully.",
                ];
            }
        }


        echo json_encode($response);
    }

    public function orignal_document()
    {
        $data = $_POST;
        $client_id = $_POST["client_id"];
        $response = $this->clients_model->update_documents($data, $client_id);
        applicant_last_update($client_id);
        echo json_encode($response);
    }

    public function update_client_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            return $this->clients_model->update_client_status($this->input->post());
        }
    }

    public function fees_details()
    {
        try {
            $data = $this->input->post();
            $client_id = $data["clientid"];
            $air_ticket_include = !empty($data["air_ticket_include"]) ? $data["air_ticket_include"] : 0;
            // Validate required fields
            if (empty($data["clientid"]) || empty($data["applicant_fees"])) {

                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => "Missing required data"
                ]);
                die;
            }

            $fees_array = [];
            $fees_array_update = [];

            foreach ($data["applicant_fees"] as $applicant_fee) {
                // Ensure required values exist
                if (!isset($data[$applicant_fee]) || !isset($data[$applicant_fee . "_id"]) || !isset($data[$applicant_fee . "_currency_type"])) {
                    continue;
                }

                $fee_data = [
                    "client_id"   => $data["clientid"],
                    "amount"      => $data[$applicant_fee],
                    "fees_id"     => $data[$applicant_fee . "_id"],
                    "currency_id" => $data[$applicant_fee . "_currency_type"],
                    "created_by"  => get_staff_user_id(),
                    "created_at"  => date('Y-m-d H:i:s')
                ];

                // Corrected detail_id validation
                $detail_id_key = $applicant_fee . "_detail_id_" . $data[$applicant_fee . "_id"];
                if (!empty($data[$detail_id_key])) {
                    $fee_data["id"] = $data[$detail_id_key];
                    $fees_array_update[] = $fee_data;
                } else {
                    if (!empty($data[$applicant_fee])) {
                        $fees_array[] = $fee_data;
                    }
                }
            }

            // Database transaction start
            $this->db->trans_start();

            // Insert new records
            if (!empty($fees_array)) {
                $this->db->insert_batch(db_prefix() . 'applicant_fees_details', $fees_array);
                $this->db->insert(db_prefix() . 'application_fees_activity_log', array("fees_details" => json_encode($fees_array_update), "description" => " Fess Information Insert by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
            }


            // Update existing records
            if (!empty($fees_array_update)) {
                $updated_rows = $this->db->update_batch(db_prefix() . 'applicant_fees_details', $fees_array_update, 'id');

                if ($updated_rows > 0) {
                    $this->db->insert(db_prefix() . 'application_fees_activity_log', [
                        "fees_details" => json_encode($fees_array_update),
                        "description"  => "Fees information updated by staff ID: " . get_staff_user_id(),
                        "date"         => date('Y-m-d H:i:s'),
                        "staffid"      => get_staff_user_id(),
                        "client_id"    => $data["clientid"]
                    ]);
                }
            }

            // Commit transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Database transaction failed.", 500);
            }

            $this->db->where('userid', $data['clientid']);
            $this->db->update(db_prefix() . 'clients', ['air_ticket_include' => $air_ticket_include]);

            if ($this->db->affected_rows() > 0) {
                $this->db->insert(db_prefix() . 'application_fees_activity_log', [
                    'fees_details' => json_encode($fees_array_update),
                    'description'  => 'Fees and Air Ticket info updated by Staff ID: ' . get_staff_user_id(),
                    'date'         => date('Y-m-d H:i:s'),
                    'staffid'      => get_staff_user_id(),
                    'client_id'    => $data['clientid']
                ]);
            }

            // Return success response
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => "Fees details updated successfully."
            ]);
            exit;
        } catch (Exception $e) {
            // Rollback if any error occurs
            $this->db->trans_rollback();

            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
            exit;
        }
    }

    public function download_approved_documents()
    {
        try {
            $data = $this->input->post();
            $userid = !empty($data["userid"]) ? $data["userid"] : '';
            // Validate required fields
            if (empty($data["userid"])) {

                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => "Missing required data userid"
                ]);
                die;
            }
            // $documents_name = get_documents()
            $documents = get_clients_documents($userid);
            $documents_type =  array_column(get_documents(), "name", "id");

            $doc_urls = [];
            if (!empty($documents[0]['data'])) {
                $documents = json_decode($documents[0]['data'], true);

                foreach ($documents as $doc) {
                    if ($doc['approval_status'] == 1) {
                        $name = !empty($documents_type[$doc["id"]]) ? $documents_type[$doc["id"]] : '';
                        $doc_urls[] = array("url" => base_url($doc['document_file']), "name" => sanitizeFileName($name));
                    }
                }
            }
            $doc_urls_additional = doc_urls_additional($userid);

            $doc_urls = array_merge($doc_urls, $doc_urls_additional);
            if (!empty($doc_urls)) {
                echo json_encode([
                    'resp_code' => 'RCS',
                    'resp_desc' => 'Please wait to download documents.',
                    'data' => $doc_urls
                ]);
            } else {
                // Return success response
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => "User No documents found."
                ]);
            }

            exit;
        } catch (Exception $e) {
            // Rollback if any error occurs
            $this->db->trans_rollback();

            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
            exit;
        }
    }

    public function delete_documents()
    {
        try {
            $data = $this->input->post();
            if (empty($data["clientid"]) || empty($data["tracker_id"]) ||  empty($data["id"]) ||  empty($data["type"])) {
                return $this->json_response('ERR', 'Missing required data');
            }

            $staff_id = get_staff_user_id();
            $timestamp = date('Y-m-d H:i:s');
            $shortlisting_tbl = db_prefix() . "client_university_shortlisting";
            $client_id = $data["clientid"];
            $tracker_id = $data["tracker_id"];
            $update_client_data = [];
            $document_type = "";
            switch ((int)$data["type"]) {
                case 1:
                    if (empty($data["id"])) {
                        return $this->json_response('ERR', 'Missing shortlisting ID');
                    }
                    $document_type = "Application";
                    $this->db->update($shortlisting_tbl, [
                        "application_file" => "",
                        "updated_by" => $staff_id,
                        "updated_date" => $timestamp
                    ], ['id' => $data["id"]]);
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage"  => ADMISSION,
                        "applicant_sub_status" => ADMISSION_LETTER_APPLY
                    ];
                    break;

                case 2:
                    if (empty($data["id"])) {
                        return $this->json_response('ERR', 'Missing shortlisting ID');
                    }
                    $document_type = "Ministry Payment";

                    $this->db->update($shortlisting_tbl, [
                        "ministry_payment" => "",
                        "ministry_payment_date" => "",
                        "updated_by" => $staff_id,
                        "updated_date" => $timestamp
                    ], ['id' => $data["id"]]);
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => LEGALIZATION,
                        "applicant_sub_status" => LEGALIZATION_PENDING,
                    ];
                    break;

                case 3:
                    if (empty($data["id"])) {
                        return $this->json_response('ERR', 'Missing shortlisting ID');
                    }
                    $document_type = "Fees Deposite Slip";

                    $this->db->update($shortlisting_tbl, [
                        "fees_deposite_slip" => "",
                        "updated_by" => $staff_id,
                        "updated_date" => $timestamp
                    ], ['id' => $data["id"]]);
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => FEES_DEPOSITE,
                        "applicant_sub_status" => FEES_DEPOSITE_PENDING,
                    ];
                    break;

                case 4:
                    if (empty($data["id"])) {
                        return $this->json_response('ERR', 'Missing shortlisting ID');
                    }
                    $document_type = "University Fees Deposite";

                    $this->db->update($shortlisting_tbl, [
                        "university_fees_payment_slip" => "",
                        "updated_by" => $staff_id,
                        "updated_date" => $timestamp
                    ], ['id' => $data["id"]]);
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => FEES_DEPOSITE,
                        "applicant_sub_status" => FEES_DEPOSITE_PENDING,
                    ];
                    break;

                case 5:
                    if (empty($data["id"])) {
                        return $this->json_response('ERR', 'Missing shortlisting ID');
                    }
                    $document_type = "invitation Letter";

                    $this->db->update($shortlisting_tbl, [
                        "invitation_letter" => "",
                        "invitation_receiving_date" => "",
                        "updated_by" => $staff_id,
                        "updated_date" => $timestamp
                    ], ['id' => $data["id"]]);
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => INVITATION,
                        "applicant_sub_status" => INVITATION_PENDING,
                    ];
                    break;

                case 6:
                    if (empty($data["id"])) {
                        return $this->json_response('ERR', 'Missing visa ID');
                    }
                    $document_type = "Visa Letter";

                    $visa_tbl = db_prefix() . "visa_details";
                    $this->db->update($visa_tbl, [
                        "file" => "",
                        "status" => 2,
                        "receiving_date" => "",
                        "entry_date" => "",
                        "received_status" => 0,
                        "updated_by" => $staff_id,
                        "updated_at" => $timestamp
                    ], ['id' => $data["id"]]);
                    $update_client_data = [
                        "applicant_status" => 0,
                        "applicant_stage" => VISA,
                        "applicant_sub_status" => VISA_APPLY
                    ];
                    break;

                default:
                    return $this->json_response('ERR', 'Invalid document type');
            }


            if (!empty($update_client_data)) {

                $this->db->insert(db_prefix() . 'application_document_activity_log', array("description" => $document_type . " Document Deleted by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $id));
                $this->db->where("userid", $client_id);
                $this->db->update(db_prefix() . 'clients', $update_client_data);
            }

            $this->update_applicant_tracker_stages($client_id, ($tracker_id - 1));
            applicant_last_update($client_id);
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Document deleted successfully'
            ]);
        } catch (Exception $e) {
            log_message('error', 'Delete document error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    private function json_response($code, $message)
    {
        echo json_encode([
            'resp_code' => $code,
            'resp_desc' => $message
        ]);
        exit;
    }
}
