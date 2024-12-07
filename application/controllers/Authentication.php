<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Authentication extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        hooks()->do_action('clients_authentication_constructor', $this);
    }

    public function index()
    {
        $this->login();
    }

    // Added for backward compatibilies
    public function admin()
    {
        redirect(admin_url('authentication'));
    }

    public function login()
    {
        if (is_client_logged_in()) {
            redirect(site_url());
        }

        $this->form_validation->set_rules('password', _l('clients_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('clients_login_email'), 'trim|required|valid_email');

        if (show_recaptcha_in_customers_area()) {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if ($this->form_validation->run() !== false) {
            $this->load->model('Authentication_model');

            $success = $this->Authentication_model->login(
                $this->input->post('email'),
                $this->input->post('password', false),
                $this->input->post('remember'),
                false
            );

            if (is_array($success) && isset($success['memberinactive'])) {
                set_alert('danger', _l('inactive_account'));
                redirect(site_url('authentication/login'));
            } elseif ($success == false) {
                set_alert('danger', _l('client_invalid_username_or_password'));
                redirect(site_url('authentication/login'));
            }

            $this->load->model('announcements_model');
            $this->announcements_model->set_announcements_as_read_except_last_one(get_contact_user_id());

            hooks()->do_action('after_contact_login');

            maybe_redirect_to_previous_url();
            redirect(site_url());
        }
        if (get_option('allow_registration') == 1) {
            $data['title'] = _l('clients_login_heading_register');
        } else {
            $data['title'] = _l('clients_login_heading_no_register');
        }
        $data['bodyclass'] = 'customers_login';

        $this->data($data);
        $this->view('login');
        $this->layout();
    }

    public function register()
    {
        if (get_option('allow_registration') != 1 || is_client_logged_in()) {
            redirect(site_url());
        }

        if (get_option('company_is_required') == 1) {
            $this->form_validation->set_rules('company', _l('client_company'), 'required');
        }

        if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions') == 1) {
            $this->form_validation->set_rules(
                'accept_terms_and_conditions',
                _l('terms_and_conditions'),
                'required',
                ['required' => _l('terms_and_conditions_validation')]
            );
        }

        $this->form_validation->set_rules('firstname', _l('client_firstname'), 'required|alpha|min_length[3]');
        $this->form_validation->set_rules('lastname', _l('client_lastname'), 'required|alpha|min_length[2]');
        $this->form_validation->set_rules('contact_phonenumber', _l('clients_phone'), 'required|numeric|min_length[10]|max_length[10]|is_unique[' . db_prefix() . 'contacts.phonenumber]', array('is_unique' => 'Your Mobile Number is already registered with us. Login with your email to proceed further with your application.'));
        $this->form_validation->set_rules('email', _l('client_email'), 'trim|required|is_unique[' . db_prefix() . 'contacts.email]|valid_email', array('is_unique' => 'Your Email id is already registered with us. Login with your email to proceed further with your application.'));
        $this->form_validation->set_rules('password', _l('clients_register_password'), 'required|min_length[7]|callback_valid_password');
        $this->form_validation->set_rules('passwordr', _l('clients_register_password_repeat'), 'required|matches[password]');

        if (show_recaptcha_in_customers_area()) {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }

        $custom_fields = get_custom_fields('customers', [
            'show_on_client_portal' => 1,
            'required'              => 1,
        ]);

        $custom_fields_contacts = get_custom_fields('contacts', [
            'show_on_client_portal' => 1,
            'required'              => 1,
        ]);

        foreach ($custom_fields as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }
        foreach ($custom_fields_contacts as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $data = $this->input->post();

                define('CONTACT_REGISTERING', true);

                $ip = $_SERVER['REMOTE_ADDR'];
                $ipdetails = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

                $ipcity      = ($data['city'] != '') ? $data['city'] : $ipdetails->city;
                $ipstate      = ($data['state'] != '') ? $data['state'] : $ipdetails->region;
                $ipcountry     = ($ipdetails->country == 'IN') ? '102' : 0;
                $ipzip       = ($data['zip'] != "") ? $data['zip'] : $ipdetails->postal;

                $clientid = $this->clients_model->add([
                    'billing_street'      => $data['address'],
                    'billing_city'        => $data['city'],
                    'billing_state'       => $data['state'],
                    'billing_zip'         => $data['zip'],
                    'billing_country'     => is_numeric($data['country']) ? $data['country'] : 0,
                    'firstname'           => $data['firstname'],
                    'lastname'            => $data['lastname'],
                    'email'               => $data['email'],
                    'contact_phonenumber' => $data['contact_phonenumber'],
                    'website'             => $data['website'],
                    'title'               => $data['title'],
                    'password'            => $data['passwordr'],
                    'company'             => $data['company'],
                    'vat'                 => isset($data['vat']) ? $data['vat'] : '',
                    'phonenumber'         => $data['phonenumber'],
                    'country'             => $data['country'],
                    'city'                => $ipcity, //$data['city'],
                    'address'             => $data['address'],
                    'zip'                 => $ipzip, //$data['zip'],
                    'state'               => $ipstate, //$data['state'],
                    'custom_fields'       => isset($data['custom_fields']) && is_array($data['custom_fields']) ? $data['custom_fields'] : [],
                ], true);

                if ($clientid) {
                    hooks()->do_action('after_client_register', $clientid);

                    if (get_option('customers_register_require_confirmation') == '1') {
                        send_customer_registered_email_to_administrators($clientid);

                        $this->clients_model->require_confirmation($clientid);
                        set_alert('success', _l('customer_register_account_confirmation_approval_notice'));
                        redirect(site_url('authentication/login'));
                    }

                    $this->load->model('authentication_model');

                    $logged_in = $this->authentication_model->login(
                        $this->input->post('email'),
                        $this->input->post('password', false),
                        false,
                        false
                    );

                    $redUrl = site_url();

                    if ($logged_in) {
                        hooks()->do_action('after_client_register_logged_in', $clientid);
                        set_alert('success', _l('clients_successfully_registered'));
                    } else {
                        set_alert('warning', _l('clients_account_created_but_not_logged_in'));
                        $redUrl = site_url('authentication/login');
                    }

                    send_customer_registered_email_to_administrators($clientid);
                    redirect($redUrl);
                }
            }
        }

        $data['title']     = _l('clients_register_heading');
        $data['bodyclass'] = 'register';
        $this->data($data);
        $this->view('register');
        $this->layout();
    }

    public function forgot_password()
    {
        if (is_client_logged_in()) {
            redirect(site_url());
        }

        $this->form_validation->set_rules(
            'email',
            _l('customer_forgot_password_email'),
            'trim|required|valid_email|callback_contact_email_exists'
        );

        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $this->load->model('Authentication_model');
                $success = $this->Authentication_model->forgot_password($this->input->post('email'));
                if (is_array($success) && isset($success['memberinactive'])) {
                    set_alert('danger', _l('inactive_account'));
                } elseif ($success == true) {
                    set_alert('success', _l('check_email_for_resetting_password'));
                } else {
                    set_alert('danger', _l('error_setting_new_password_key'));
                }
                redirect(site_url('authentication/forgot_password'));
            }
        }
        $data['title'] = _l('customer_forgot_password');
        $this->data($data);
        $this->view('forgot_password');

        $this->layout();
    }

    public function reset_password($staff, $userid, $new_pass_key)
    {
        $this->load->model('Authentication_model');
        if (!$this->Authentication_model->can_reset_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(site_url('authentication/login'));
        }

        $this->form_validation->set_rules('password', _l('customer_reset_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('customer_reset_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                hooks()->do_action('before_user_reset_password', [
                    'staff'  => $staff,
                    'userid' => $userid,
                ]);
                $success = $this->Authentication_model->reset_password(
                    0,
                    $userid,
                    $new_pass_key,
                    $this->input->post('passwordr', false)
                );
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    hooks()->do_action('after_user_reset_password', [
                        'staff'  => $staff,
                        'userid' => $userid,
                    ]);
                    set_alert('success', _l('password_reset_message'));
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(site_url('authentication/login'));
            }
        }
        $data['title'] = _l('admin_auth_reset_password_heading');
        $this->data($data);
        $this->view('reset_password');
        $this->layout();
    }

    public function logout()
    {
        $this->load->model('authentication_model');
        $this->authentication_model->logout(false);
        hooks()->do_action('after_client_logout');
        redirect(site_url('authentication/login'));
    }

    public function contact_email_exists($email = '')
    {
        $this->db->where('email', $email);
        $total_rows = $this->db->count_all_results(db_prefix() . 'contacts');

        if ($total_rows == 0) {
            $this->form_validation->set_message('contact_email_exists', _l('auth_reset_pass_email_not_found'));

            return false;
        }

        return true;
    }

    public function recaptcha($str = '')
    {
        return do_recaptcha_validation($str);
    }
    public function valid_password($password = '')
    {
        $password = trim($password);
        $regex_lowercase = '/[a-z]/';
        $regex_uppercase = '/[A-Z]/';
        $regex_number = '/[0-9]/';
        $regex_special = '/[!@#$%^&*()\-_=+{};:,<.>§~]/';
        if (empty($password)) {
            $this->form_validation->set_message('valid_password', 'The {field} field is required.');
            return FALSE;
        }
        if (preg_match_all($regex_lowercase, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must be at least one lowercase letter.');
            return FALSE;
        }
        if (preg_match_all($regex_uppercase, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must be at least one uppercase letter.');
            return FALSE;
        }
        if (preg_match_all($regex_number, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must have at least one number.');
            return FALSE;
        }
        if (preg_match_all($regex_special, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must have at least one special character.' . ' ' . htmlentities('!@#$%^&*()\-_=+{};:,<.>§~'));
            return FALSE;
        }
        if (strlen($password) < 7) {
            $this->form_validation->set_message('valid_password', 'The {field} field must be at least 7 characters in length.');
            return FALSE;
        }
        if (strlen($password) > 32) {
            $this->form_validation->set_message('valid_password', 'The {field} field cannot exceed 32 characters in length.');
            return FALSE;
        }
        return TRUE;
    }

   public function re_assign_cron()
{
    // Load required libraries and models
    $this->load->library('import/import_leads', [], 'import');
    $this->load->model('Leads_model');
    $limit = RE_ASSIGN_LEADS; // Define the number of leads to process per chunk
    $chunkSize = 500; // Number of leads to process per iteration

    $offset = 0; // Initialize offset for chunking

    do {
        // Fetch a chunk of lead data with status 1
        $data_leads = $this->db->query(
            "SELECT id, data, lead_id 
            FROM " . db_prefix() . "lead_temp 
            WHERE status = 1 and lead_id > 0 
            ORDER BY id DESC 
            LIMIT {$offset}, {$chunkSize}"
        )->result_array();

        if (!empty($data_leads)) {
            $reassign_data_array = [];
            $ids_to_delete = [];

            foreach ($data_leads as $lead) {
                if (!empty($lead['data'])) {
                    // Decode JSON data for the lead
                    $temp_lead_data = json_decode($lead['data'], true);

                    if (!empty($lead['lead_id'])) {
                        // Delete the existing lead in the main table if `lead_id` exists
                        $this->Leads_model->delete($lead['lead_id'], $temp_lead_data);

                        // Prepare for mass assignation and collect IDs for deletion
                        $reassign_data_array[] = $temp_lead_data;
                        $ids_to_delete[] = $lead['id'];
                    }
                }
            }

            // Check if there's any data to reassign
            if (!empty($reassign_data_array)) {
                try {
                    // Perform mass assignation
                    $this->import->mass_assignation($reassign_data_array);

                    // Delete successfully reassigned leads from the temporary table
                    $this->db->where_in('id', $ids_to_delete);
                    $this->db->delete(db_prefix() . 'lead_temp');

                    echo json_encode(['status' => 1, 'message' => 'Leads reassigned successfully.']);
                } catch (Exception $e) {
                    // Log and display error if assignation fails
                    log_message('error', 'Error in mass assignation: ' . $e->getMessage());
                    echo json_encode(['status' => 0, 'message' => 'Failed to reassign leads.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'No leads available for reassignment in this chunk.']);
            }
        }

        $offset += $chunkSize; // Move to the next chunk
    } while (!empty($data_leads)); // Continue until no leads are left

    if ($offset === 0) {
        echo json_encode(['status' => 0, 'message' => 'No leads found to reassign.']);
    }
}

}
