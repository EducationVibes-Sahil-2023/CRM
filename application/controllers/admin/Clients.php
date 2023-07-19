<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Clients extends AdminController
{
    /* List all clients */
    public function index()
    {
        if (!has_permission('customers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
                access_denied('customers');
            }
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
        $data['application_stage'] = $this->clients_model->get_application_stage();
        $data['application_sub_stage'] = $this->clients_model->get_application_sub_stage();

        $whereContactsLoggedIn = '';
        if (!has_permission('customers', '', 'view')) {
            $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        }

        $data['contacts_logged_in_today'] = $this->clients_model->get_contacts('', 'last_login LIKE "' . date('Y-m-d') . '%"' . $whereContactsLoggedIn);

        $data['countries'] = $this->clients_model->get_clients_distinct_countries();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['sources']  = $this->leads_model->get_source();
        $data['leadType'] = $this->leads_model->get_type();
        $data['vendorType'] = $this->leads_model->get_vendor();
        $this->load->view('admin/clients/manage', $data);
    }

    public function table()
    {

        if (!has_permission('customers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
                ajax_access_denied();
            }
        }

        $this->app->get_table_data('clients');
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

    /* Edit client or add new client*/
    public function client($id = '')
    {
        // $database_secondary = $this->load->database('database_secondary', TRUE);
        $this->load->model('leads_model');
        $data['lead_type'] = $this->leads_model->get_type();
        $data["dropdown_country_university_selection"] = $this->s_db->query("SELECT co.name,c.country_name,u.university_name FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') ")->result_array();
        if (!has_permission('customers', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('customers');
            }
        }

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
                        access_denied('customers');
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

            if (!$client) {
                show_404();
            }

            $data['contacts'] = $this->clients_model->get_contacts($id);
            $data['basicDetails'] = $this->clients_model->get_contact_by_userid($data['contacts'][0]['userid']);

            $data['tab']      = isset($data['customer_tabs'][$group]) ? $data['customer_tabs'][$group] : null;

            if (!$data['tab']) {
                show_404();
            }

            // Fetch data based on groups
            if ($group == 'profile') {
                $data['customer_groups'] = $this->clients_model->get_customer_groups($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);
                $data['basicdetails'] = $this->clients_model->getBasicDetails($id);
                $data['admissionpreferences'] = $this->clients_model->getAdmissionPreferences($id);

                $data['parentdetails'] = $this->clients_model->getParentDetails($id);

                $data['academicdetails'] = $this->clients_model->getAcademicDetails($id);
                $data['declarationdetails'] = $this->clients_model->getDeclarationDetails($id);
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
                $data['customer_vendors'] = [];
                if (!empty($data['profile_creation_data'][0]["vendor"])) {
                    $data['customer_vendors'] = $this->clients_model->get_profile_creator_vendor($data['profile_creation_data'][0]["vendor"]);
                }
            }


            // $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['members'] = $this->staff_model->get('', ['active' => 1]);

            $data['staff'] = [];
            if (!empty($data["lead_data"]->form_data->lead_status)) {
                $lead_status_data = $data["lead_data"]->form_data->lead_status;
                foreach ($data['members'] as $members) {
                    if ($members["lead_type"] == $lead_status_data) {
                        $data['staff'][] = $members;
                    }
                }
            }

            $data['client'] = $client;
            $title          = $client->company;

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
            redirect(admin_url('clients'));
        }
        $response = $this->clients_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('customer_delete_transactions_warning', _l('invoices') . ', ' . _l('estimates') . ', ' . _l('credit_notes')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('client')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('client_lowercase')));
        }
        redirect(admin_url('clients'));
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
        hooks()->do_action('before_do_bulk_action_for_customers');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');
            $groups = $this->input->post('groups');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->clients_model->delete($id)) {
                            $total_deleted++;
                        }
                    } else {
                        if (!is_array($groups)) {
                            $groups = false;
                        }
                        $this->client_groups_model->sync_customer_groups($id, $groups);
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_clients_deleted', $total_deleted));
        }
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

            $dataArr = [
                'program' => $params['program'],
                'course' => $params['course'],
                'entrance_exam_given' => $params['entranceExamGiven'],
                'entrance_exam_details' => ($params['entranceExamGiven'] == 'YES') ? $params['entranceExamDetails'] : '',
                'session_intake' => $params['sessionIntake'],
                'userid' => $params['client_id'],
            ];

            if ($params['countries'] != "") {
                $dataArr['study_country'] = $params['countries'];
                $dataArr['university'] = json_encode($params['universities'], true);
            }

            $admissionPreferencesId = $this->clients_model->addAdmissionPreferences($dataArr, $params['admissionPreferencesId']);
            if ($admissionPreferencesId) {
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

    public function upload_documents()
    {
        $data = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $label_data = $this->input->post("document_label");
            $document_url = $this->input->post("document_url");
            $documents = $_FILES["document_file"];
            $update_array = [];
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;

            $files = $_FILES['document_file'];

            for ($k = $i = 0; $i < count($label_data); $i++) {
                $upload_data = [];
                if (!empty($files['name'][$k])) {
                    $upload_data["name"] = $files['name'][$k];
                    $upload_data["type"] = $files['type'][$k];
                    $upload_data["tmp_name"] = $files['tmp_name'][$k];
                    $upload_data["error"] = $files['error'][$k];
                    $upload_data["size"] = $files['size'][$k];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {;
                        $file_name = upload_applicant_documents($client_id, $upload_data);
                        array_push($update_array, array("label_name" => $label_data[$k], "document_file" => $file_name["file_path"]));
                    }
                    $k++;
                } else if (!empty($document_url[$i])) {
                    array_push($update_array, array("label_name" => $label_data[$i], "document_file" => !empty($document_url[$i]) ? $document_url[$i] : ''));
                }
            }

            $this->db->select("id");
            $this->db->where('client_id', $client_id);
            $check_ = $this->db->get(db_prefix() . 'client_documents')->row();

            if (!empty($check_->id)) {
                $_update_data = array(
                    "data" => json_encode($update_array, true),
                    "updated_date" => date('Y-m-d H:i:s'),
                    "updated_by" => get_staff_user_id()
                );
                $this->db->where("id", $check_->id);
                $this->db->update(db_prefix() . 'client_documents', $_update_data);

                $insert_id =   $this->db->insert(db_prefix() . 'application_activity_log', array("description" => "Document upload by - ", "date" => date('Y-m-d H:i:s'), "staffid" => get_staff_user_id(), "client_id" => $client_id));
                $rows_affected = $this->db->affected_rows();
                if (isset($applicant_status)) {
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => $applicant_status));
                    get_applicant_status($applicant_status, $client_id);
                }
                if ($rows_affected > 0) {
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
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => 1));
                    get_applicant_status(1, $client_id);
                } else if ($document_status == 2) {
                    $document_status_text = "Reject";
                    $this->db->where("userid", $client_id);
                    $this->db->update(db_prefix() . 'clients', array("applicant_status" => 0));
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

    public function update_university()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $this->input->post("client_id");
            $applicant_status = !empty($this->input->post("applicant_status")) ? $this->input->post("applicant_status") : 0;
            $university_shortlisting = !empty($this->input->post("university_shortlisting")) ? json_decode($this->input->post("university_shortlisting"), true) : [];
            $university_shortlisting_insert_arr = [];
            $university_shortlisting_update_arr = [];
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
}
