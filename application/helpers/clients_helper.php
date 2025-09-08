<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check whether the contact email is verified
 * @since  2.2.0
 * @param  mixed  $id contact id
 * @return boolean
 */
function is_contact_email_verified($id = null)
{
    $id = !$id ? get_contact_user_id() : $id;

    if (isset($GLOBALS['contact']) && $GLOBALS['contact']->id == $id) {
        return !is_null($GLOBALS['contact']->email_verified_at);
    }

    $CI = &get_instance();

    $CI->db->select('email_verified_at');
    $CI->db->where('id', $id);
    $contact = $CI->db->get(db_prefix() . 'contacts')->row();

    if (!$contact) {
        return false;
    }

    return !is_null($contact->email_verified_at);
}

/**
 * Check whether the user disabled verification emails for contacts
 * @return boolean
 */
function is_email_verification_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'contact-verification-email', 'active' => 0]) == 0;
}
/**
 * Check if client id is used in the system
 * @param  mixed  $id client id
 * @return boolean
 */
function is_client_id_used($id)
{
    $total = 0;

    $checkCommonTables = [db_prefix() . 'subscriptions', db_prefix() . 'creditnotes', db_prefix() . 'projects', db_prefix() . 'invoices', db_prefix() . 'expenses', db_prefix() . 'estimates'];

    foreach ($checkCommonTables as $table) {
        $total += total_rows($table, [
            'client' => $id,
        ]);
    }

    $total += total_rows(db_prefix() . 'contracts', [
        'client' => $id,
    ]);

    $total += total_rows(db_prefix() . 'proposals', [
        'rel_id'   => $id,
        'rel_type' => 'customer',
    ]);

    $total += total_rows(db_prefix() . 'tickets', [
        'userid' => $id,
    ]);

    $total += total_rows(db_prefix() . 'tasks', [
        'rel_id'   => $id,
        'rel_type' => 'customer',
    ]);

    return hooks()->apply_filters('is_client_id_used', $total > 0 ? true : false, $id);
}
/**
 * Check if customer has subscriptions
 * @param  mixed $id customer id
 * @return boolean
 */
function customer_has_subscriptions($id)
{
    return hooks()->apply_filters('customer_has_subscriptions', total_rows(db_prefix() . 'subscriptions', ['clientid' => $id]) > 0);
}
/**
 * Get client by ID or current queried client
 * @param  mixed $id client id
 * @return mixed
 */
function get_client($id = null)
{
    if (empty($id) && isset($GLOBALS['client'])) {
        return $GLOBALS['client'];
    }

    // Client global object not set
    if (empty($id)) {
        return null;
    }

    $client = get_instance()->clients_model->get($id);

    return $client;
}
/**
 * Get predefined tabs array, used in customer profile
 * @return array
 */
function get_customer_profile_tabs()
{
    return get_instance()->app_tabs->get_customer_profile_tabs();
}

/**
 * Filter only visible tabs selected from the profile
 * @param  array $tabs available tabs
 * @return array
 */
function filter_client_visible_tabs($tabs)
{
    $newTabs = [];

    $visible = get_option('visible_customer_profile_tabs');
    if ($visible != 'all') {
        $visible = unserialize($visible);
    }

    $appliedSettings = is_array($visible);
    foreach ($tabs as $key => $tab) {

        // Check visibility from settings too
        // if ($key != 'profile' && $key != 'contacts' && $appliedSettings) {
        //     if (array_key_exists($key, $visible) && $visible[$key] == false) {
        //         continue;
        //     }
        // }

        if ($key != 'profile' && $appliedSettings) {
            if (array_key_exists($key, $visible) && $visible[$key] == false) {
                continue;
            }
        }
        $newTabs[$key] = $tab;
    }

    return hooks()->apply_filters('client_filtered_visible_tabs', $newTabs);
}
/**
 * @todo
 * Find a way to get the customer_id inside this function or refactor the hook
 * @param  string $group the tabs groups
 * @return null
 */
function app_init_customer_profile_tabs()
{
    $client_id = null;

    $remindersText = _l('client_reminders_tab');

    if ($client = get_client()) {
        $client_id = $client->userid;

        $total_reminders = total_rows(
            db_prefix() . 'reminders',
            [
                'isnotified' => 0,
                'staff'      => get_staff_user_id(),
                'rel_type'   => 'customer',
                'rel_id'     => $client_id,
            ]
        );

        if ($total_reminders > 0) {
            $remindersText .= ' <span class="badge">' . $total_reminders . '</span>';
        }
    }

    $CI = &get_instance();

    $CI->app_tabs->add_customer_profile_tab('profile', [
        'name'     => _l('client_add_edit_profile'),
        'icon'     => 'fa fa-user-circle',
        'view'     => 'admin/clients/groups/profile',
        'position' => 5,
        'leadType' => ''
    ]);

    $CI->app_tabs->add_customer_profile_tab('contacts', [
        'name'     => !is_empty_customer_company($client_id) || empty($client_id) ? _l('customer_contacts') : _l('contact'),
        'icon'     => 'fa fa-users',
        'view'     => 'admin/clients/groups/contacts',
        'position' => 10,
    ]);

    $CI->app_tabs->add_customer_profile_tab('notes', [
        'name'     => _l('contracts_notes_tab'),
        'icon'     => 'fa fa-sticky-note-o',
        'view'     => 'admin/clients/groups/notes',
        'position' => 15,
    ]);

    $CI->app_tabs->add_customer_profile_tab('statement', [
        'name'     => _l('customer_statement'),
        'icon'     => 'fa fa-area-chart',
        'view'     => 'admin/clients/groups/statement',
        'visible'  => has_permission('invoices', '', 'view'),
        'position' => 20,
    ]);

    $CI->app_tabs->add_customer_profile_tab('invoices', [
        'name'     => _l('client_invoices_tab'),
        'icon'     => 'fa fa-file-text',
        'view'     => 'admin/clients/groups/invoices',
        'visible'  => (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())),
        'position' => 25,
    ]);

    $CI->app_tabs->add_customer_profile_tab('payments', [
        'name'     => _l('client_payments_tab'),
        'icon'     => 'fa fa-line-chart',
        'view'     => 'admin/clients/groups/payments',
        'visible'  => (has_permission('payments', '', 'view') || has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())),
        'position' => 30,
    ]);

    $CI->app_tabs->add_customer_profile_tab('proposals', [
        'name'     => _l('proposals'),
        'icon'     => 'fa fa-file-powerpoint-o',
        'view'     => 'admin/clients/groups/proposals',
        'visible'  => (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || (get_option('allow_staff_view_proposals_assigned') == 1 && staff_has_assigned_proposals())),
        'position' => 35,
    ]);

    $CI->app_tabs->add_customer_profile_tab('credit_notes', [
        'name'     => _l('credit_notes'),
        'icon'     => 'fa fa-sticky-note-o',
        'view'     => 'admin/clients/groups/credit_notes',
        'visible'  => (has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own')),
        'position' => 40,
    ]);

    $CI->app_tabs->add_customer_profile_tab('estimates', [
        'name'     => _l('estimates'),
        'icon'     => 'fa fa-clipboard',
        'view'     => 'admin/clients/groups/estimates',
        'visible'  => (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own') || (get_option('allow_staff_view_estimates_assigned') == 1 && staff_has_assigned_estimates())),
        'position' => 45,
    ]);

    $CI->app_tabs->add_customer_profile_tab('subscriptions', [
        'name'     => _l('subscriptions'),
        'icon'     => 'fa fa-repeat',
        'view'     => 'admin/clients/groups/subscriptions',
        'visible'  => (has_permission('subscriptions', '', 'view') || has_permission('subscriptions', '', 'view_own')),
        'position' => 50,
    ]);

    $CI->app_tabs->add_customer_profile_tab('expenses', [
        'name'     => _l('expenses'),
        'icon'     => 'fa fa-file-text-o',
        'view'     => 'admin/clients/groups/expenses',
        'visible'  => (has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')),
        'position' => 55,
    ]);

    $CI->app_tabs->add_customer_profile_tab('contracts', [
        'name'     => _l('contracts'),
        'icon'     => 'fa fa-file',
        'view'     => 'admin/clients/groups/contracts',
        'visible'  => (has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own')),
        'position' => 60,
    ]);

    $CI->app_tabs->add_customer_profile_tab('projects', [
        'name'     => _l('projects'),
        'icon'     => 'fa fa-bars',
        'view'     => 'admin/clients/groups/projects',
        'position' => 65,
    ]);

    $CI->app_tabs->add_customer_profile_tab('tasks', [
        'name'     => _l('tasks'),
        'icon'     => 'fa fa-tasks',
        'view'     => 'admin/clients/groups/tasks',
        'position' => 70,
    ]);

    $CI->app_tabs->add_customer_profile_tab('tickets', [
        'name'     => _l('tickets'),
        'icon'     => 'fa fa-ticket',
        'view'     => 'admin/clients/groups/tickets',
        'visible'  => ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()),
        'position' => 75,
    ]);

    $CI->app_tabs->add_customer_profile_tab('attachments', [
        'name'     => _l('customer_attachments'),
        'icon'     => 'fa fa-paperclip',
        'view'     => 'admin/clients/groups/attachments',
        'position' => 80,
    ]);

    $CI->app_tabs->add_customer_profile_tab('vault', [
        'name'     => _l('vault'),
        'icon'     => 'fa fa-lock',
        'view'     => 'admin/clients/groups/vault',
        'position' => 85,
    ]);

    $CI->app_tabs->add_customer_profile_tab('reminders', [
        'name'     => $remindersText,
        'icon'     => 'fa fa-clock-o',
        'view'     => 'admin/clients/groups/reminders',
        'position' => 90,
    ]);

    $CI->app_tabs->add_customer_profile_tab('map', [
        'name'     => _l('customer_map'),
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/map',
        'position' => 95,
    ]);
    $CI->app_tabs->add_customer_profile_tab('orignal_document', [
        'name'     => _l('Orignal Documents'),
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/orignal_documents',
        'position' => 95,
        'leadType' => '2'
    ]);

    $CI->app_tabs->add_customer_profile_tab('apostille', [
        'name'     => _l('Apostille Documents'),
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/apostille',
        'position' => 95,
        'leadType' => '2'
    ]);
    $CI->app_tabs->add_customer_profile_tab('tracker', [
        'name'     => _l('customer_tracker'),
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/applicant_tracker',
        'position' => 95,
        'leadType' => '2'
    ]);
    $CI->app_tabs->add_customer_profile_tab('quotation', [
        'name'     => "Quotation",
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/quotation',
        'position' => 95,
        'leadType' => '2'
    ]);

    $CI->app_tabs->add_customer_profile_tab('study_tracker', [
        'name'     => _l('customer_tracker'),
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/study_abroad_tracker',
        'position' => 95,
        'leadType' => '1'
    ]);

    $CI->app_tabs->add_customer_profile_tab('fly_ticket', [
        'name'     => "Fly Ticket",
        'icon'     => 'fa fa-map-marker',
        'view'     => 'admin/clients/groups/fly_ticket',
        'position' => 95,

    ]);


    $post_staff = array_column($CI->staff_model->post_sale_get(), "staffid");


    if (is_admin() || in_array(get_staff_user_id(), $post_staff)) {
        $CI->app_tabs->add_customer_profile_tab('visa', [
            'name'     => _l('visa_tracker'),
            'icon'     => 'fa fa-cc-visa',
            'view'     => 'admin/clients/groups/visa',
            'position' => 95,
        ]);
        $CI->app_tabs->add_customer_profile_tab('accommodation', [
            'name'     => _l('accomodation_tracker'),
            'icon'     => 'fa fa-plane',
            'view'     => 'admin/clients/groups/accommodation',
            'position' => 95,
        ]);

        $CI->app_tabs->add_customer_profile_tab('activity_logs', [
            'name'     => "Activity Logs",
            'icon'     => 'fa fa-clock-o menu-icon',
            'view'     => 'admin/clients/groups/activity_logs',
            'visible'  => has_permission('customers', '', 'activity_logs'),
            'position' => 95,
        ]);
    }
}

/**
 * Get client id by lead id
 * @since  Version 1.0.1
 * @param  mixed $id lead id
 * @return mixed     client id
 */
function get_client_id_by_lead_id($id)
{
    $CI = &get_instance();
    $CI->db->select('userid')->from(db_prefix() . 'clients')->where('leadid', $id);

    return $CI->db->get()->row()->userid;
}

/**
 * Check if contact id passed is primary contact
 * If you dont pass $contact_id the current logged in contact will be checked
 * @param  string  $contact_id
 * @return boolean
 */
function is_primary_contact($contact_id = '')
{
    if (!is_numeric($contact_id)) {
        $contact_id = get_contact_user_id();
    }

    if (total_rows(db_prefix() . 'contacts', ['id' => $contact_id, 'is_primary' => 1]) > 0) {
        return true;
    }

    return false;
}

/**
 * Check if client have invoices with multiple currencies
 * @return booelan
 */
function is_client_using_multiple_currencies($clientid = '', $table = null)
{
    if (!$table) {
        $table = db_prefix() . 'invoices';
    }

    $CI = &get_instance();

    $clientid = $clientid == '' ? get_client_user_id() : $clientid;
    $CI->load->model('currencies_model');
    $currencies            = $CI->currencies_model->get();
    $total_currencies_used = 0;
    foreach ($currencies as $currency) {
        $CI->db->where('currency', $currency['id']);
        $CI->db->where('clientid', $clientid);
        $total = $CI->db->count_all_results($table);
        if ($total > 0) {
            $total_currencies_used++;
        }
    }

    $retVal = true;
    if ($total_currencies_used > 1) {
        $retVal = true;
    } elseif ($total_currencies_used == 0 || $total_currencies_used == 1) {
        $retVal = false;
    }

    return hooks()->apply_filters('is_client_using_multiple_currencies', $retVal, [
        'client_id' => $clientid,
        'table'     => $table,
    ]);
}


/**
 * Function used to check if is really empty customer company
 * Can happen user to have selected that the company field is not required and the primary contact name is auto added in the company field
 * @param  mixed  $id
 * @return boolean
 */
function is_empty_customer_company($id)
{
    $CI = &get_instance();
    $CI->db->select('company');
    $CI->db->from(db_prefix() . 'clients');
    $CI->db->where('userid', $id);
    $row = $CI->db->get()->row();
    if ($row) {
        if ($row->company == '') {
            return true;
        }

        return false;
    }

    return true;
}

/**
 * Get ids to check what files with contacts are shared
 * @param  array  $where
 * @return array
 */
function get_customer_profile_file_sharing($where = [])
{
    $CI = &get_instance();
    $CI->db->where($where);

    return $CI->db->get(db_prefix() . 'shared_customer_files')->result_array();
}

/**
 * Get customer id by passed contact id
 * @param  mixed $id
 * @return mixed
 */
function get_user_id_by_contact_id($id)
{
    $CI = &get_instance();

    $userid = $CI->app_object_cache->get('user-id-by-contact-id-' . $id);
    if (!$userid) {
        $CI->db->select('userid')
            ->where('id', $id);
        $client = $CI->db->get(db_prefix() . 'contacts')->row();

        if ($client) {
            $userid = $client->userid;
            $CI->app_object_cache->add('user-id-by-contact-id-' . $id, $userid);
        }
    }

    return $userid;
}

/**
 * Get primary contact user id for specific customer
 * @param  mixed $userid
 * @return mixed
 */
function get_primary_contact_user_id($userid)
{
    $CI = &get_instance();
    $CI->db->where('userid', $userid);
    $CI->db->where('is_primary', 1);
    $row = $CI->db->get(db_prefix() . 'contacts')->row();

    if ($row) {
        return $row->id;
    }

    return false;
}

/**
 * Get client full name
 * @param  string $contact_id Optional
 * @return string Firstname and Lastname
 */
function get_contact_full_name($contact_id = '')
{
    $contact_id == '' ? get_contact_user_id() : $contact_id;

    $CI = &get_instance();

    $contact = $CI->app_object_cache->get('contact-full-name-data-' . $contact_id);

    if (!$contact) {
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->select('firstname,lastname')->from(db_prefix() . 'contacts')->get()->row();
        $CI->app_object_cache->add('contact-full-name-data-' . $contact_id, $contact);
    }

    if ($contact) {
        return $contact->firstname . ' ' . $contact->lastname;
    }

    return '';
}
/**
 * Return contact profile image url
 * @param  mixed $contact_id
 * @param  string $type
 * @return string
 */
function contact_profile_image_url($contact_id, $type = 'small')
{
    $url  = base_url('assets/images/user-placeholder.jpg');
    $CI   = &get_instance();
    $path = $CI->app_object_cache->get('contact-profile-image-path-' . $contact_id);

    if (!$path) {
        $CI->app_object_cache->add('contact-profile-image-path-' . $contact_id, $url);

        $CI->db->select('profile_image');
        $CI->db->from(db_prefix() . 'contacts');
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->get()->row();

        if ($contact && !empty($contact->profile_image)) {
            $path = 'uploads/client_profile_images/' . $contact_id . '/' . $type . '_' . $contact->profile_image;
            $CI->app_object_cache->set('contact-profile-image-path-' . $contact_id, $path);
        }
    }

    if ($path && file_exists($path)) {
        $url = base_url($path);
    }

    return $url;
}
/**
 * Used in:
 * Search contact tickets
 * Project dropdown quick switch
 * Calendar tooltips
 * @param  [type] $userid [description]
 * @return [type]         [description]
 */
function get_company_name($userid, $prevent_empty_company = false)
{
    $_userid = get_client_user_id();
    if ($userid !== '') {
        $_userid = $userid;
    }
    $CI = &get_instance();

    $select = ($prevent_empty_company == false ? get_sql_select_client_company() : 'company');

    $client = $CI->db->select($select)
        ->where('userid', $_userid)
        ->from(db_prefix() . 'clients')
        ->get()
        ->row();
    if ($client) {
        return $client->company;
    }

    return '';
}

function get_client_name($userid, $prevent_empty_company = false)
{
    $_userid = get_client_user_id();
    if ($userid !== '') {
        $_userid = $userid;
    }
    $CI = &get_instance();

    $client = $CI->db->select("CONCAT(first_name,' ',last_name) as clientName")
        ->where('userid', $_userid)
        ->from(db_prefix() . 'basic_details')
        ->get()
        ->row();
    if ($client) {
        return $client->clientName;
    }

    return '';
}


/**
 * Get client default language
 * @param  mixed $clientid
 * @return mixed
 */
function get_client_default_language($clientid = '')
{
    if (!is_numeric($clientid)) {
        $clientid = get_client_user_id();
    }

    $CI = &get_instance();
    $CI->db->select('default_language');
    $CI->db->from(db_prefix() . 'clients');
    $CI->db->where('userid', $clientid);
    $client = $CI->db->get()->row();
    if ($client) {
        return $client->default_language;
    }

    return '';
}

/**
 * Function is customer admin
 * @param  mixed  $id       customer id
 * @param  staff_id  $staff_id staff id to check
 * @return boolean
 */
function is_customer_admin($id, $staff_id = '')
{
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $CI       = &get_instance();
    $cache    = $CI->app_object_cache->get($id . '-is-customer-admin-' . $staff_id);

    if ($cache) {
        return $cache['retval'];
    }

    $total = total_rows(db_prefix() . 'customer_admins', [
        'customer_id' => $id,
        'staff_id'    => $staff_id,
    ]);

    $retval = $total > 0 ? true : false;
    $CI->app_object_cache->add($id . '-is-customer-admin-' . $staff_id, ['retval' => $retval]);

    return $retval;
}
/**
 * Check if staff member have assigned customers
 * @param  mixed $staff_id staff id
 * @return boolean
 */
function have_assigned_customers($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-customers-' . $staff_id);

    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'customer_admins', [
            'staff_id' => $staff_id,
        ]);
        $CI->app_object_cache->add('staff-total-assigned-customers-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}
/**
 * Check if contact has permission
 * @param  string  $permission permission name
 * @param  string  $contact_id     contact id
 * @return boolean
 */
function has_contact_permission($permission, $contact_id = '')
{
    $CI = &get_instance();

    if (!class_exists('app')) {
        $CI->load->library('app');
    }

    $permissions = get_contact_permissions();

    if (empty($contact_id)) {
        $contact_id = get_contact_user_id();
    }

    foreach ($permissions as $_permission) {
        if ($_permission['short_name'] == $permission) {
            return total_rows(db_prefix() . 'contact_permissions', [
                'permission_id' => $_permission['id'],
                'userid'        => $contact_id,
            ]) > 0;
        }
    }

    return false;
}
/**
 * Load customers area language
 * @param  string $customer_id
 * @return string return loaded language
 */
function load_client_language($customer_id = '')
{
    $CI       = &get_instance();
    $language = get_option('active_language');

    if (is_client_logged_in() || $customer_id != '') {
        $client_language = get_client_default_language($customer_id);

        if (
            !empty($client_language)
            && file_exists(APPPATH . 'language/' . $client_language)
        ) {
            $language = $client_language;
        }
    }

    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];

    $CI->lang->load($language . '_lang', $language);
    if (file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')) {
        $CI->lang->load('custom_lang', $language);
    }

    $GLOBALS['language'] = $language;
    $GLOBALS['locale']   = get_locale_key($language);

    hooks()->do_action('after_load_client_language', $language);

    return $language;
}
/**
 * Check if client have transactions recorded
 * @param  mixed $id clientid
 * @return boolean
 */
function client_have_transactions($id)
{
    $total = 0;

    foreach ([db_prefix() . 'invoices', db_prefix() . 'creditnotes', db_prefix() . 'estimates'] as $table) {
        $total += total_rows($table, [
            'clientid' => $id,
        ]);
    }

    $total += total_rows(db_prefix() . 'expenses', [
        'clientid' => $id,
        'billable' => 1,
    ]);

    $total += total_rows(db_prefix() . 'proposals', [
        'rel_id'   => $id,
        'rel_type' => 'customer',
    ]);

    return hooks()->apply_filters('customer_have_transactions', $total > 0, $id);
}


/**
 * Predefined contact permission
 * @return array
 */
function get_contact_permissions()
{
    $permissions = [
        [
            'id'         => 1,
            'name'       => _l('customer_permission_invoice'),
            'short_name' => 'invoices',
        ],
        [
            'id'         => 2,
            'name'       => _l('customer_permission_estimate'),
            'short_name' => 'estimates',
        ],
        [
            'id'         => 3,
            'name'       => _l('customer_permission_contract'),
            'short_name' => 'contracts',
        ],
        [
            'id'         => 4,
            'name'       => _l('customer_permission_proposal'),
            'short_name' => 'proposals',
        ],
        [
            'id'         => 5,
            'name'       => _l('customer_permission_support'),
            'short_name' => 'support',
        ],
        [
            'id'         => 6,
            'name'       => _l('customer_permission_projects'),
            'short_name' => 'projects',
        ],
    ];

    return hooks()->apply_filters('get_contact_permissions', $permissions);
}

function get_contact_permission($name)
{
    $permissions = get_contact_permissions();

    foreach ($permissions as $permission) {
        if ($permission['short_name'] == $name) {
            return $permission;
        }
    }

    return false;
}

/**
 * Additional checking for customers area, when contact edit his profile
 * This function will check if the checkboxes for email notifications should be shown
 * @return boolean
 */
function can_contact_view_email_notifications_options()
{
    if (
        has_contact_permission('invoices')
        || has_contact_permission('estimates')
        || has_contact_permission('projects')
        || has_contact_permission('contracts')
    ) {
        return true;
    }

    return false;
}

/**
 * With this function staff can login as client in the clients area
 * @param  mixed $id client id
 */
function login_as_client($id)
{
    $CI = &get_instance();

    $CI->db->select(db_prefix() . 'contacts.id, active')
        ->where('userid', $id)
        ->where('is_primary', 1);

    $primary = $CI->db->get(db_prefix() . 'contacts')->row();

    if (!$primary) {
        set_alert('danger', _l('no_primary_contact'));
        redirect($_SERVER['HTTP_REFERER']);
    } elseif ($primary->active == '0') {
        set_alert('danger', 'Customer primary contact is not active, please set the primary contact as active in order to login as client');
        redirect($_SERVER['HTTP_REFERER']);
    }

    $CI->load->model('announcements_model');
    $CI->announcements_model->set_announcements_as_read_except_last_one($primary->id);

    $user_data = [
        'client_user_id'      => $id,
        'contact_user_id'     => $primary->id,
        'client_logged_in'    => true,
        'logged_in_as_client' => true,
    ];

    $CI->session->set_userdata($user_data);
}

function send_customer_registered_email_to_administrators($client_id)
{
    $CI = &get_instance();
    $CI->load->model('staff_model');
    $admins = $CI->staff_model->get('', ['active' => 1, 'admin' => 1]);

    foreach ($admins as $admin) {
        send_mail_template('customer_new_registration_to_admins', $admin['email'], $client_id, $admin['staffid']);
    }
}

/**
 * Return and perform additional checkings for contact consent url
 * @param  mixed $contact_id contact id
 * @return string
 */
function contact_consent_url($contact_id)
{
    $CI = &get_instance();

    $consent_key = get_contact_meta($contact_id, 'consent_key');

    if (empty($consent_key)) {
        $consent_key = app_generate_hash() . '-' . app_generate_hash();
        $meta_id     = false;
        if (total_rows(db_prefix() . 'contacts', ['id' => $contact_id]) > 0) {
            $meta_id = add_contact_meta($contact_id, 'consent_key', $consent_key);
        }
        if (!$meta_id) {
            return '';
        }
    }

    return site_url('consent/contact/' . $consent_key);
}

/**
 *  Get customer attachment
 * @param   mixed $id   customer id
 * @return  array
 */
function get_all_customer_attachments($id)
{
    $CI = &get_instance();

    $attachments                = [];
    $attachments['invoice']     = [];
    $attachments['estimate']    = [];
    $attachments['credit_note'] = [];
    $attachments['proposal']    = [];
    $attachments['contract']    = [];
    $attachments['lead']        = [];
    $attachments['task']        = [];
    $attachments['customer']    = [];
    $attachments['ticket']      = [];
    $attachments['expense']     = [];

    $has_permission_expenses_view = has_permission('expenses', '', 'view');
    $has_permission_expenses_own  = has_permission('expenses', '', 'view_own');
    if ($has_permission_expenses_view || $has_permission_expenses_own) {
        // Expenses
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);
        if (!$has_permission_expenses_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }

        $CI->db->from(db_prefix() . 'expenses');
        $expenses = $CI->db->get()->result_array();
        $ids      = array_column($expenses, 'id');
        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'expense');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();
            foreach ($_attachments as $_att) {
                array_push($attachments['expense'], $_att);
            }
        }
    }


    $has_permission_invoices_view = has_permission('invoices', '', 'view');
    $has_permission_invoices_own  = has_permission('invoices', '', 'view_own');
    if ($has_permission_invoices_view || $has_permission_invoices_own || get_option('allow_staff_view_invoices_assigned') == 1) {
        $noPermissionQuery = get_invoices_where_sql_for_staff(get_staff_user_id());
        // Invoices
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);

        if (!$has_permission_invoices_view) {
            $CI->db->where($noPermissionQuery);
        }

        $CI->db->from(db_prefix() . 'invoices');
        $invoices = $CI->db->get()->result_array();

        $ids = array_column($invoices, 'id');
        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'invoice');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();
            foreach ($_attachments as $_att) {
                array_push($attachments['invoice'], $_att);
            }
        }
    }

    $has_permission_credit_notes_view = has_permission('credit_notes', '', 'view');
    $has_permission_credit_notes_own  = has_permission('credit_notes', '', 'view_own');

    if ($has_permission_credit_notes_view || $has_permission_credit_notes_own) {
        // credit_notes
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);

        if (!$has_permission_credit_notes_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }

        $CI->db->from(db_prefix() . 'creditnotes');
        $credit_notes = $CI->db->get()->result_array();

        $ids = array_column($credit_notes, 'id');
        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'credit_note');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();
            foreach ($_attachments as $_att) {
                array_push($attachments['credit_note'], $_att);
            }
        }
    }

    $permission_estimates_view = has_permission('estimates', '', 'view');
    $permission_estimates_own  = has_permission('estimates', '', 'view_own');

    if ($permission_estimates_view || $permission_estimates_own || get_option('allow_staff_view_proposals_assigned') == 1) {
        $noPermissionQuery = get_estimates_where_sql_for_staff(get_staff_user_id());
        // Estimates
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);
        if (!$permission_estimates_view) {
            $CI->db->where($noPermissionQuery);
        }
        $CI->db->from(db_prefix() . 'estimates');
        $estimates = $CI->db->get()->result_array();

        $ids = array_column($estimates, 'id');
        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'estimate');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

            foreach ($_attachments as $_att) {
                array_push($attachments['estimate'], $_att);
            }
        }
    }

    $has_permission_proposals_view = has_permission('proposals', '', 'view');
    $has_permission_proposals_own  = has_permission('proposals', '', 'view_own');

    if ($has_permission_proposals_view || $has_permission_proposals_own || get_option('allow_staff_view_proposals_assigned') == 1) {
        $noPermissionQuery = get_proposals_sql_where_staff(get_staff_user_id());
        // Proposals
        $CI->db->select('rel_id,id');
        $CI->db->where('rel_id', $id);
        $CI->db->where('rel_type', 'customer');
        if (!$has_permission_proposals_view) {
            $CI->db->where($noPermissionQuery);
        }
        $CI->db->from(db_prefix() . 'proposals');
        $proposals = $CI->db->get()->result_array();

        $ids = array_column($proposals, 'id');

        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'proposal');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

            foreach ($_attachments as $_att) {
                array_push($attachments['proposal'], $_att);
            }
        }
    }

    $permission_contracts_view = has_permission('contracts', '', 'view');
    $permission_contracts_own  = has_permission('contracts', '', 'view_own');
    if ($permission_contracts_view || $permission_contracts_own) {
        // Contracts
        $CI->db->select('client,id');
        $CI->db->where('client', $id);
        if (!$permission_contracts_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }
        $CI->db->from(db_prefix() . 'contracts');
        $contracts = $CI->db->get()->result_array();

        $ids = array_column($contracts, 'id');

        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'contract');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

            foreach ($_attachments as $_att) {
                array_push($attachments['contract'], $_att);
            }
        }
    }

    $CI->db->select('leadid')
        ->where('userid', $id);
    $customer = $CI->db->get(db_prefix() . 'clients')->row();

    if ($customer->leadid != null) {
        $CI->db->where('rel_id', $customer->leadid);
        $CI->db->where('rel_type', 'lead');
        $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();
        foreach ($_attachments as $_att) {
            array_push($attachments['lead'], $_att);
        }
    }

    $CI->db->select('ticketid,userid');
    $CI->db->where('userid', $id);
    $CI->db->from(db_prefix() . 'tickets');
    $tickets = $CI->db->get()->result_array();

    $ids = array_column($tickets, 'ticketid');

    if (count($ids) > 0) {
        $CI->db->where_in('ticketid', $ids);
        $_attachments = $CI->db->get(db_prefix() . 'ticket_attachments')->result_array();

        foreach ($_attachments as $_att) {
            array_push($attachments['ticket'], $_att);
        }
    }

    $has_permission_tasks_view = has_permission('tasks', '', 'view');
    $noPermissionQuery         = get_tasks_where_string(false);
    $CI->db->select('rel_id, id');
    $CI->db->where('rel_id', $id);
    $CI->db->where('rel_type', 'customer');

    if (!$has_permission_tasks_view) {
        $CI->db->where($noPermissionQuery);
    }

    $CI->db->from(db_prefix() . 'tasks');
    $tasks = $CI->db->get()->result_array();

    $ids = array_column($tasks, 'ticketid');
    if (count($ids) > 0) {
        $CI->db->where_in('rel_id', $ids);
        $CI->db->where('rel_type', 'task');

        $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

        foreach ($_attachments as $_att) {
            array_push($attachments['task'], $_att);
        }
    }

    $CI->db->where('rel_id', $id);
    $CI->db->where('rel_type', 'customer');
    $client_main_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

    $attachments['customer'] = $client_main_attachments;

    return hooks()->apply_filters('all_client_attachments', $attachments, $id);
}

/**
 * Used in customer profile vaults feature to determine if the vault should be shown for staff
 * @param  array $entries vault entries from database
 * @return array
 */
function _check_vault_entries_visibility($entries)
{
    $new = [];
    foreach ($entries as $entry) {
        if ($entry['visibility'] != 1) {
            if ($entry['visibility'] == 2 && !is_admin() && $entry['creator'] != get_staff_user_id()) {
                continue;
            } elseif ($entry['visibility'] == 3 && $entry['creator'] != get_staff_user_id() && !is_admin()) {
                continue;
            }
        }
        $new[] = $entry;
    }

    if (count($new) == 0) {
        $new = -1;
    }

    return $new;
}
/**
 * Default SQL select for selecting the company
 * @return string
 */
function get_sql_select_client_company()
{
    return 'CASE 
    WHEN ' . db_prefix() . 'clients.company IS NULL OR ' . db_prefix() . 'clients.company = \'\' THEN 
      (SELECT CONCAT(firstname, \' \', lastname) 
       FROM ' . db_prefix() . 'contacts 
       WHERE userid = ' . db_prefix() . 'clients.userid AND is_primary = 1) 
    ELSE 
    ' . db_prefix() . 'clients.company 
  END AS  company';
}

function can_logged_in_contact_change_language()
{
    if (!isset($GLOBALS['contact'])) {
        return false;
    }

    return $GLOBALS['contact']->is_primary == '1' && get_option('disable_language') == 0;
}


function applicant_tracker($lead_type)
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'applicant_tracker')
        ->where('status', 1);

    if (!empty($lead_type)) {
        $CI->db->where("FIND_IN_SET('$lead_type',lead_type) >", 0);
    }

    $CI->db->order_by("orderby", "asc");

    return  $CI->db->get()->result_array(); // Execute and return result

}

function applicant_tracker_mbbs($lead_type)
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'applicant_tracker_mbbs')
        ->where('status', 1);

    if (!empty($lead_type)) {
        $CI->db->where("FIND_IN_SET('$lead_type',lead_type) >", 0);
    }

    $CI->db->order_by("orderby", "asc");

    return  $CI->db->get()->result_array(); // Execute and return result

}


function applicant_tracker_study($lead_type)
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'applicant_tracker_study')
        ->where('status', 1);

    if (!empty($lead_type)) {
        $CI->db->where("FIND_IN_SET('$lead_type',lead_type) >", 0);
    }

    $CI->db->order_by("orderby", "asc");

    return  $CI->db->get()->result_array(); // Execute and return result

}





function get_condition_offer($client_id, $university_id)
{
    $CI = &get_instance();
    return $client_tracker = $CI->db->select("*")
        ->where('status', 1)
        ->where('client_id', $client_id)
        ->where('university_id', $university_id)
        ->from(db_prefix() . 'offer_condition')
        ->order_by("id", "asc")
        ->get()
        ->result_array();
}
function university_applicant_fees($university_quotation = "", $applicant_quotaion = "", $university_array = [], $client_id = "")
{
    $CI = &get_instance();

    // Select base columns from applicant_fees
    $CI->db->select("f.*")
        ->from(db_prefix() . 'applicant_fees f')
        ->where('f.status', 1);


    if (!empty($university_quotation)) {
        $CI->db->where('f.university_quotation', 1);
        $CI->db->order_by("f.university_quotation_sequence", "asc");
    }

    if (!empty($applicant_quotaion)) {
        $CI->db->where('f.applicant_quotaion', 1);
        $CI->db->order_by("f.applicant_quotaion_sequence", "asc");
    }


    // Conditionally join applicant_fees_details if client_id is provided
    if (!empty($client_id)) {
        $CI->db->select("d.amount, d.currency_id, d.fees_id, d.id as detail_id,d.amount as amount")
            ->join(db_prefix() . 'applicant_quotation_fees_details d', "f.id = d.fees_id AND d.client_id = {$client_id}", "LEFT")->or_where($university_array);
    }

    if (!empty($university_array["university_name"]) && !empty($university_quotation)) {
        $CI->db->select("d.amount, d.currency_id, d.fees_id, d.id as detail_id")
            ->join(db_prefix() . 'applicant_quotation_fees_details d', "f.id = d.fees_id ", "LEFT");

        if (!empty($university_array["university_name"])) {
            $CI->db->where($university_array);
        }
    }






    $client_fees = $CI->db->get()->result_array();

    return $client_fees;
}

function university_applicant_fees_details($where = [])
{
    $CI = &get_instance();

    $results = $CI->db->select("fd.*,f.backend")
        ->from(db_prefix() . 'applicant_quotation_fees_details fd')
        ->join(db_prefix() . 'applicant_fees f', 'f.id = fd.fees_id', 'left')
        ->where($where)
        // ->where('f.backend', 1)
        ->order_by("fd.id", "asc")
        ->get()
        ->result_array();

    $grouped = [];

    foreach ($results as $row) {
        // use correct column (acadmic_year instead of just year if that’s the DB field)
        $year = $row['year'] ?? $row['year'] ?? 'unknown';

        if (!isset($grouped[$year])) {
            $grouped[$year] = [];
        }

        $grouped[$year][] = $row;
    }

    return $grouped;
}



function get_clients_fees($lead_type = "", $client_id = "", $university_quotation = "", $applicant_quotaion = "")
{
    $CI = &get_instance();

    // Select base columns from applicant_fees
    $CI->db->select("f.*")
        ->from(db_prefix() . 'applicant_fees f')
        ->where('f.status', 1);
    if (!empty($lead_type)) {
        $CI->db->where('f.lead_type', $lead_type);
    }

    if (!empty($university_quotation)) {
        $CI->db->where('f.university_quotation', $university_quotation);
        $CI->db->order_by("f.university_quotation_sequence", "asc");
    }

    if (!empty($applicant_quotaion)) {
        $CI->db->where('f.applicant_quotaion', $applicant_quotaion);
        $CI->db->order_by("f.applicant_quotaion_sequence", "asc");
    }


    // Conditionally join applicant_fees_details if client_id is provided
    if (!empty($client_id)) {
        $CI->db->select("d.amount, d.currency_id, d.fees_id, d.id as detail_id")
            ->join(db_prefix() . 'applicant_fees_details d', "f.id = d.fees_id AND d.client_id = {$client_id}", "LEFT");
    }

    $CI->db->order_by("f.sequence", "asc");

    $client_fees = $CI->db->get()->result_array();

    return $client_fees;
}



function get_passport_stages()
{
    $CI = &get_instance();
    return $passport_stages = $CI->db->select("*")
        ->where('status', 1)
        ->from(db_prefix() . 'passport_stages')
        ->order_by("sequence", "asc")
        ->get()
        ->result_array();
}

function get_caste_category()
{
    $CI = &get_instance();
    return $caste_category = $CI->db->select("*")
        ->where('status', 1)
        ->from(db_prefix() . 'caste_category')
        ->get()
        ->result_array();
}

function get_neet_status()
{
    $CI = &get_instance();
    return $caste_category = $CI->db->select("*")
        ->where('status', 1)
        ->from(db_prefix() . 'neet_status')
        ->get()
        ->result_array();
}

function get_currencies()
{
    $CI = &get_instance();

    try {
        // Fetch data from the currencies table with specified ordering
        $currencies = $CI->db->select("*")
            ->from(db_prefix() . 'currencies')
            ->order_by("isdefault", "DESC")
            ->order_by("id", "ASC")
            ->get()
            ->result_array();

        return $currencies; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching currencies: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function check_country_rest($studyCountries)
{
    $studyCountries = array_map('trim', $studyCountries); // Clean whitespace
    $hasOtherCountry = false;

    foreach ($studyCountries as $country) {
        if (strtolower($country) !== 'georgia') {
            $hasOtherCountry = true;
            break;
        }
    }

    // If other countries (not Georgia) exist, remove Georgia
    if ($hasOtherCountry) {
        $studyCountries = array_filter($studyCountries, function ($country) {
            return strtolower($country) !== 'georgia';
        });

        // Add 'Rest' if not already included
        if (!in_array('Rest', $studyCountries)) {
            $studyCountries[] = 'Rest';
        }
    }

    if (empty($studyCountries)) {
        $studyCountries[] = 'Rest';
    }

    return $studyCountries;
}

function get_documents($lead_type = "", $selected_country = [], $show_all = 0, $stage = "", $where = [])
{
    $CI = &get_instance();

    if (!empty($selected_country)) {
        $selected_country = check_country_rest($selected_country);
    }


    try {
        // Fetch documents where country is empty
        $CI->db->select(db_prefix() . "document_upload_type.*, " . db_prefix() . "file_type.type AS file_type, " . db_prefix() . "applicant_stages.name AS stage")
            ->from(db_prefix() . 'document_upload_type')
            ->join(db_prefix() . 'file_type', db_prefix() . 'file_type.id = ' . db_prefix() . 'document_upload_type.file_type', 'left')
            ->join(db_prefix() . 'applicant_stages', db_prefix() . 'applicant_stages.id = ' . db_prefix() . 'document_upload_type.stages', 'left')
            ->where(db_prefix() . "document_upload_type.country", ""); // Country is empty


        if (!empty($lead_type)) {
            $CI->db->where(db_prefix() . "document_upload_type.lead_type", $lead_type);
        }

        if (!empty($stage)) {
            $CI->db->where(db_prefix() . "document_upload_type.stages", $stage);
        }

        if (!empty($where)) {
            $CI->db->where($where);
        }

        $CI->db->order_by("sequence", "ASC");
        $document = $CI->db->group_by("document_upload_type.id")
            ->get()
            ->result_array();

        // Fetch documents based on selected country
        $CI->db->select(db_prefix() . "document_upload_type.*, " . db_prefix() . "file_type.type AS file_type, " . db_prefix() . "applicant_stages.name AS stage")
            ->from(db_prefix() . 'document_upload_type')
            ->join(db_prefix() . 'file_type', db_prefix() . 'file_type.id = ' . db_prefix() . 'document_upload_type.file_type', 'left')
            ->join(db_prefix() . 'applicant_stages', db_prefix() . 'applicant_stages.id = ' . db_prefix() . 'document_upload_type.stages', 'left');

        if (!empty($lead_type)) {
            $CI->db->where(db_prefix() . "document_upload_type.lead_type", $lead_type);
        }

        if (!empty($stage)) {
            $CI->db->where(db_prefix() . "document_upload_type.stages", $stage);
        }
        if (!empty($where)) {
            $CI->db->where($where);
        }

        if (!empty($selected_country)) {
            $CI->db->group_start(); // Start AND group for country filtering
            foreach ($selected_country as $country) {
                $CI->db->or_where("FIND_IN_SET('$country', " . db_prefix() . "document_upload_type.country) >", 0);
            }
            $CI->db->group_end(); // End group
        }

        $document_country = $CI->db->order_by("sequence", "ASC")
            ->get()
            ->result_array();

        // Merge both document arrays
        $document = array_merge($document, $document_country);


        usort($document, function ($a, $b) {
            return $a['sequence'] <=> $b['sequence'];
        });
        $unique = [];
        $seen_ids = [];

        foreach ($document as $item) {
            if (!in_array($item['id'], $seen_ids)) {
                $seen_ids[] = $item['id'];
                $unique[] = $item;
            }
        }

        $document = $unique;

        return $document;
    } catch (Exception $e) {
        log_message('error', 'Error fetching document: ' . $e->getMessage());
        return []; // Return an empty array in case of an error
    }
}


function get_clients_documents($client_id)
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $clients_document = $CI->db
            ->select("data")
            ->where(array("client_id" => $client_id))
            ->from(db_prefix() . 'client_documents')
            ->get()
            ->result_array();

        return $clients_document; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_applicant_stage()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $applicant_stages = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'sa_applicant_stages')
            ->get()
            ->result_array();

        return $applicant_stages; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_applicant_stage_mbbs()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $applicant_stages = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'applicant_stages')
            ->get()
            ->result_array();

        return $applicant_stages; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_applicant_sub_stage()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $applicant_sub_stages = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'application_sub_category')
            ->get()
            ->result_array();

        return $applicant_sub_stages; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}


function get_applicant_sub_stage_mbbs()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $applicant_sub_stages = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'application_sub_category_mbbs')
            ->get()
            ->result_array();

        return $applicant_sub_stages; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_university_partner_names()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $university_partner = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'university_partner')
            ->get()
            ->result_array();

        return $university_partner; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_board_dropdown()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $board_dropdown = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'board')
            ->get()
            ->result_array();

        return $board_dropdown; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}



function get_clients_fees_details($lead_type, $client_id, $fees_id = "")
{
    $CI = &get_instance();
    $CI->db->select("TRIM(c.symbol) AS symbol, TRIM(d.amount) AS amount, CONCAT(TRIM(c.symbol), TRIM(d.amount)) AS total_amount,f.id,d.currency_id,f.fees")
        ->from(db_prefix() . 'applicant_fees f')
        ->join(db_prefix() . 'applicant_fees_details d', "f.id = d.fees_id")
        ->join(db_prefix() . 'currencies c', "c.id = d.currency_id")
        ->where('lead_type', $lead_type)
        ->where('client_id', $client_id);

    if (!empty($fees_id)) {
        $CI->db->where('f.id', $fees_id);
    }

    $client_fees = $CI->db->order_by("sequence", "asc")
        ->get()
        ->result_array();

    return $client_fees;
}

function get_clients_fees_details_ids($lead_type, $client_id = [], $fees_id = "")
{
    $CI = &get_instance();
    $CI->db->select("CONCAT(client_id,'-',f.id) fees_id,TRIM(c.symbol) AS symbol, TRIM(d.amount) AS amount, CONCAT(TRIM(c.symbol), TRIM(d.amount)) AS total_amount,f.id")
        ->from(db_prefix() . 'applicant_fees f')
        ->join(db_prefix() . 'applicant_fees_details d', "f.id = d.fees_id")
        ->join(db_prefix() . 'currencies c', "c.id = d.currency_id")
        ->where('lead_type', $lead_type);

    if (!empty($client_id)) {
        $CI->db->where_in('client_id', $client_id);
    }

    if (!empty($fees_id)) {
        $CI->db->where('f.id', $fees_id);
    }

    $client_fees = $CI->db->order_by("sequence", "asc")
        ->get()
        ->result_array();
    if (!empty($client_fees)) {
        $client_fees = array_column($client_fees, "total_amount", "fees_id");
    }

    return $client_fees;
}

function get_orignal_document_data($client_id)
{
    $CI = &get_instance();
    $CI->db->select("o.*,r.received_date,CONCAT(firstname,' ',lastname) as received_by,r.id as received_id,l.name received_location,r.in_transit,l.status l_status")
        ->from(db_prefix() . 'orignal_documents o')
        ->join(db_prefix() . 'orignal_documents_received r', "o.id = r.doc_id AND r.userid = {$client_id}", "LEFT")
        ->join(db_prefix() . 'staff s', "s.staffid = r.received_by ", "LEFT")
        ->join(db_prefix() . 'office_location l', "l.id = r.location_id ", "LEFT")
        ->where('o.status', 1);
    return $CI->db->order_by("id", "asc")->get()->result_array();
}

function orignal_document_status()
{
    $CI = &get_instance();
    $CI->db->select("o.*")
        ->from(db_prefix() . 'orignal_document_status o');
    return $CI->db->order_by("o.id", "asc")->get()->result_array();
}


function activity_orignal_document($id)
{
    $CI = &get_instance();
    $sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'DESC');
    $CI->db->where('client_id', $id);
    $CI->db->order_by('date', $sorting);
    return $CI->db->get(db_prefix() . 'orignal_document_activity')->result_array();
}

function get_orignal_document_list(
    $rest = 0,
    $georgia = 0,
    $apostile = 0,
    $id = "",
    $visa_rest = 0,
    $visa_georgia = 0,
    $visa_apostile = 0,
    $where = [],
    $where_or = []
) {
    $CI = &get_instance();
    $CI->db->select("*")
        ->from(db_prefix() . 'orignal_documents o');

    // Dynamic filters
    if (!empty($rest)) {
        $CI->db->where("rest", 1);
    }
    if (!empty($georgia)) {
        $CI->db->where("georgia", 1);
    }
    if (!empty($visa_rest)) {
        $CI->db->where("visa_rest", 1);
    }
    if (!empty($visa_georgia)) {
        $CI->db->where("visa_georgia", 1);
    }
    if (!empty($apostile)) {
        $CI->db->where("apostile_status", 1);
    }
    if (!empty($visa_apostile)) {
        $CI->db->where("visa_apostile", 1);
    }
    if (!empty($id)) {
        $CI->db->where("id", $id);
    }

    // Always active records
    if (!empty($where) || !empty($where_or)) {
        $CI->db->where($where);
    } else {
        // Default filter only if user has NOT passed status
        // $CI->db->where("status", 1);
    }

    // AND conditions
    if (!empty($where)) {
        $CI->db->where($where);
    }

    // OR conditions (grouped properly)
    if (!empty($where_or)) {
        $CI->db->group_start();
        $CI->db->or_where($where_or);
        $CI->db->group_end();
    }

    return $CI->db->order_by("id", "asc")->get()->result_array();
}


function get_applicant_statuses($id = "")
{
    $CI = &get_instance();
    $CI->db->select("*")
        ->from(db_prefix() . 'applicant_status o');
    if (!empty($id)) {
        $CI->db->where('id', $id);
    }
    if (!empty($id)) {
        return $CI->db->order_by("id", "asc")->get()->row();
    } else {
        return $CI->db->order_by("id", "asc")->get()->result_array();
    }
}

function get_application_statuses($id = "")
{
    $CI = &get_instance();
    $CI->db->select("*")
        ->from(db_prefix() . 'application_status o');
    if (!empty($id)) {
        $CI->db->where('id', $id);
    }
    if (!empty($id)) {
        return $CI->db->order_by("id", "asc")->get()->row();
    } else {
        return $CI->db->order_by("id", "asc")->get()->result_array();
    }
}

function get_orignal_document_data_list($client_ids_array = [], $return = 0, $where = [])
{
    $client_ids = implode(",", $client_ids_array);
    $CI = &get_instance();
    $CI->db->select("r.userid,group_concat(o.id) document_ids,group_concat(o.name) document_names,group_concat(r.id) received_id,l.status l_status")
        ->from(db_prefix() . 'orignal_documents_received r')
        ->join(db_prefix() . 'orignal_documents o', "o.id = r.doc_id AND r.userid IN ({$client_ids})")
        ->join(db_prefix() . 'office_location l', "l.id = r.location_id ", "LEFT");
    if (!empty($return) && $return == 1) {
        $CI->db->where("l.status", 2);
    } else {
        $CI->db->where("l.status", 1);
    }

    if (!empty($where)) {
        $CI->db->where($where);
    }

    $CI->db->group_by("r.userid");
    $result = $CI->db->order_by("r.userid", "asc")->get()->result_array();

    // Find users without documents
    $missing_users = [];
    $client_id_array = explode(",", $client_ids); // Convert back to an array for checking

    if (!empty($result)) {
        $found_users = array_column($result, "userid");

        foreach ($client_id_array as $client_id) {
            if (!in_array($client_id, $found_users)) {
                $missing_users[] = $client_id; // Collect users with missing documents
                return [
                    "error" => true,
                    "message" => "No documents found for user: " . get_client_name($client_id)
                ];
            }
        }
    } else {
        return [
            "error" => true,
            "message" => "No documents found for user: " . get_client_name($client_ids_array[0])
        ];
    }

    return array_column($result, null, "userid");
}

function applicant_last_update($client_id)
{
    $CI = &get_instance();
    $data = [];
    $data["last_update"] = date('Y-m-d H:i:s');
    $CI->db->where('userid', $client_id);
    $CI->db->update(db_prefix() . 'clients', $data);
}


function get_view_columns()
{
    $CI = &get_instance();
    return $passport_stages = $CI->db->select("*")
        ->where('status', 1)
        ->from(db_prefix() . 'ma_applicant_view')
        ->get()
        ->result_array();
}

function get_view_columns_sa()
{
    $CI = &get_instance();
    return $passport_stages = $CI->db->select("*")
        ->where('status', 1)
        ->from(db_prefix() . 'sa_applicant_view')
        ->get()
        ->result_array();
}
function checkName_aff($client_ids, $document_ids)
{
    $CI = &get_instance();

    $CI->db->select("o.id AS doc_id, o.name AS doc_name,check_name_status")
        ->from(db_prefix() . 'orignal_documents o');
    if (!empty($document_ids)) {
        $CI->db->where_in('o.id', $document_ids);
    }
    $CI->db->where('o.check_name_status', 1);

    $all_documents = $CI->db->get()->result_array();

    if (!empty($all_documents)) {
        $CI->db->select("c.userid")
            ->from(db_prefix() . 'clients c')
            ->where_in('c.userid', $client_ids);
        $CI->db->where('c.name_aff_status', 0);


        $clientsData = $CI->db->get()->result_array();
        if (!empty($clientsData[0]["userid"])) {
            $client_name = get_client_name($clientsData[0]["userid"]);

            $doc_name = $all_documents[0]["doc_name"];

            $data = [
                'resp_code' => 'ERR',
                'resp_desc' => "User '{$client_name}' '{$doc_name}' not checked in document section."
            ];
            echo json_encode($data);
            exit;
        }
    }
}
function get_orignal_document_data_list_apostille($client_ids_array = [], $document_ids = [], $check_status = 0, $vendor_id = "", $apostille_document_vendor = [])
{
    $CI = &get_instance();
    $client_ids = array_map('intval', $client_ids_array);
    checkName_aff($client_ids, $document_ids);
    if (!empty($document_ids) && !empty($apostille_document_vendor)) {
        $document_ids = array_diff($document_ids, $apostille_document_vendor);

        if (empty($document_ids)) {

            if ($check_status == 1) {



                $CI->db->select("o.id AS doc_id, o.name AS doc_name")
                    ->from(db_prefix() . 'orignal_documents o');
                // ->where(['o.status' => 1, 'o.apostile_status' => 1]);
                if (!empty($apostille_document_vendor)) {
                    $CI->db->where_in('o.id', $apostille_document_vendor);
                }

                $all_documents = $CI->db->get()->result_array();
                $valid_doc_ids = array_column($all_documents, 'doc_id');

                $CI->db->select("r.userid, r.doc_id")
                    ->from(db_prefix() . 'client_apostille_data r')
                    ->where_in('r.userid', $client_ids);

                if (!empty($valid_doc_ids)) {
                    $CI->db->where_in('r.doc_id', $valid_doc_ids);
                }

                $check_Apostille_data = $CI->db->get()->result_array();


                if (!empty($check_Apostille_data) && $_POST["manual_status"] != 1) {

                    $user_id = $check_Apostille_data[0]["userid"];
                    $doc_id = $check_Apostille_data[0]["doc_id"];
                    $client_name = get_client_name($user_id);
                    $doc_details = get_orignal_document_list('', '', '', $doc_id)[0];
                    $doc_name = !empty($doc_details["name"]) ? $doc_details["name"] : "Unknown";

                    return [
                        "error" => true,
                        "message" => "User '{$client_name}' has already apostilled original document '{$doc_name}'."
                    ];
                }
            } else if ($check_status == 2) {
                // Step 1: Fetch existing combinations from DB
                $CI->db->select("r.id,r.userid, r.doc_id")
                    ->from(db_prefix() . 'client_apostille_data r')
                    ->where_in('r.userid', $client_ids);

                if (!empty($_POST["apostile_id"])) {
                    $CI->db->where('r.id', $_POST["apostile_id"]);
                }

                if (!empty($document_ids)) {
                    $CI->db->where_in('r.doc_id', $document_ids);
                }

                if (!empty($vendor_id)) {
                    $CI->db->where_in('r.vendor_id', $vendor_id);
                }

                $existing_combinations = $CI->db->get()->result_array();

                // Step 2: Build a set of existing keys for fast lookup
                $existing_map = [];
                foreach ($existing_combinations as $row) {
                    $existing_map[$row['userid'] . '_' . $row['doc_id']] = true;
                }

                // Step 3: Loop through input combinations to validate
                foreach ($client_ids as $userid) {
                    foreach ($document_ids as $doc_id) {
                        $key = $userid . '_' . $doc_id;
                        if (!isset($existing_map[$key])) {
                            $client_name = get_client_name($userid);
                            $doc_name = get_orignal_document_list('', '', '', $doc_id)[0]["name"] ?? "Unknown Document";

                            return [
                                "error" => true,
                                "message" => "No apostille sent record found for client '{$client_name}' and document '{$doc_name}'."
                            ];

                            die;
                        }
                    }
                }

                return $existing_combinations;
            }
            return true;
            die;
        }
    }



    // Validate input
    if ($check_status == 0 || empty($client_ids_array)) {
        return [
            "error" => true,
            "message" => "Something went wrong. Required data missing or status check not enabled."
        ];
    }

    // Step 1: Fetch valid documents (active + apostille enabled)
    $CI->db->select("o.id AS doc_id, o.name AS doc_name")
        ->from(db_prefix() . 'orignal_documents o');
    // ->where(['o.status' => 1, 'o.apostile_status' => 1]);
    if (!empty($document_ids)) {
        $CI->db->where_in('o.id', $document_ids);
    }

    $all_documents = $CI->db->get()->result_array();

    if (empty($all_documents)) {
        return [
            "error" => true,
            "message" => "No valid documents found with Apostille status."
        ];
    }

    $valid_doc_ids = array_column($all_documents, 'doc_id');
    $CI->db->select(db_prefix() . 'admission_preferences.userid, tblclient_university_shortlisting.invitation_letter');
    $CI->db->from(db_prefix() . 'admission_preferences');
    $CI->db->join(
        db_prefix() . 'client_university_shortlisting',
        db_prefix() . 'admission_preferences.userid = tblclient_university_shortlisting.client_id AND tbladmission_preferences.primary_university = tblclient_university_shortlisting.university_name',
        "LEFT"
    );
    $CI->db->where_in(db_prefix() . 'admission_preferences.userid', $client_ids_array);
    $CI->db->group_start();
    $CI->db->where(db_prefix() . 'client_university_shortlisting.invitation_letter', '');
    $CI->db->or_where(db_prefix() . 'client_university_shortlisting.invitation_letter IS NULL', NULL, FALSE);
    $CI->db->group_end();

    $query = $CI->db->get();
    $invitation_result = $query->result_array();

    // Step 2: Check if document already apostilled
    if ($check_status == 1) {
        $CI->db->select("r.userid, r.doc_id")
            ->from(db_prefix() . 'client_apostille_data r')
            ->where_in('r.userid', $client_ids);

        if (!empty($document_ids)) {
            $CI->db->where_in('r.doc_id', $valid_doc_ids);
        }

        $check_Apostille_data = $CI->db->get()->result_array();


        if (!empty($check_Apostille_data) && $_POST["manual_status"] != 1) {
            $user_id = $check_Apostille_data[0]["userid"];
            $doc_id = $check_Apostille_data[0]["doc_id"];
            $client_name = get_client_name($user_id);
            $doc_details = get_orignal_document_list('', '', '', $doc_id)[0];
            $doc_name = !empty($doc_details["name"]) ? $doc_details["name"] : "Unknown";

            return [
                "error" => true,
                "message" => "User '{$client_name}' has already apostilled original document '{$doc_name}'."
            ];
        }
    } else if ($check_status == 2) {
        // Step 1: Fetch existing combinations from DB
        $CI->db->select("r.id,r.userid, r.doc_id")
            ->from(db_prefix() . 'client_apostille_data r')
            ->where_in('r.userid', $client_ids);

        if (!empty($document_ids)) {
            $CI->db->where_in('r.doc_id', $document_ids);
        }

        if (!empty($_POST["apostile_id"])) {
            $CI->db->where('r.id', $_POST["apostile_id"]);
        }

        if (!empty($vendor_id)) {
            $CI->db->where_in('r.vendor_id', $vendor_id);
        }

        $existing_combinations = $CI->db->get()->result_array();

        // Step 2: Build a set of existing keys for fast lookup
        $existing_map = [];
        foreach ($existing_combinations as $row) {
            $existing_map[$row['userid'] . '_' . $row['doc_id']] = true;
        }

        // Step 3: Loop through input combinations to validate
        foreach ($client_ids as $userid) {
            foreach ($document_ids as $doc_id) {
                $key = $userid . '_' . $doc_id;
                if (!isset($existing_map[$key])) {
                    $client_name = get_client_name($userid);
                    $doc_name = get_orignal_document_list('', '', '', $doc_id)[0]["name"] ?? "Unknown Document";

                    return [
                        "error" => true,
                        "message" => "No apostille sent record found for client '{$client_name}' and document '{$doc_name}'."
                    ];

                    die;
                }
            }
        }

        return $existing_combinations;
    }

    // Step 3: Get received original documents
    $CI->db->select("r.userid, r.doc_id")
        ->from(db_prefix() . 'orignal_documents_received r')
        ->where_in('r.userid', $client_ids)
        ->where_in('r.doc_id', $valid_doc_ids);
    $received_docs = $CI->db->get()->result_array();

    // Step 4: Build received docs map
    $received_map = [];
    foreach ($received_docs as $row) {
        $received_map[$row['userid']][] = $row['doc_id'];
    }

    // Step 5: Get basic client info
    $CI->db->select("b.userid, CONCAT(b.first_name, ' ', b.last_name) AS clientName")
        ->from(db_prefix() . 'basic_details b')
        ->where_in('b.userid', $client_ids);
    $clients = $CI->db->get()->result_array();

    // Step 6: Check for missing received documents
    $errors = [];
    foreach ($clients as $client) {
        $user_id = $client['userid'];
        $client_name = $client['clientName'];
        $received = isset($received_map[$user_id]) ? $received_map[$user_id] : [];

        foreach ($all_documents as $doc) {
            if (!in_array($doc['doc_id'], $received)) {
                $errors[] = "User '{$client_name}' has not received original document '{$doc['doc_name']}'.";

                return [
                    "error" => true,
                    "message" => $errors, // Return first error message (optional: return all as list)
                ];
            }
        }
    }

    // If any errors found, return the first one
    if (!empty($errors)) {
        return [
            "error" => true,
            "message" => $errors[0], // Return first error message (optional: return all as list)
        ];
    }

    return [
        "error" => false,
        "message" => "Validation passed."
    ];
}


function get_vendor_list($vendor_type = "")
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'vendor_list')
        ->where('status', 1);

    if (!empty($vendor_type)) {
        $CI->db->where("FIND_IN_SET('$vendor_type',vendor_type) >", 0);
    }

    return  $CI->db->get()->result_array(); // Execute and return result
}

function get_courier_list()
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'courier_type')
        ->where('status', 1);



    return  $CI->db->get()->result_array(); // Execute and return result
}

function get_payment_mode()
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'payment_mode')
        ->where('status', 1);



    return  $CI->db->get()->result_array(); // Execute and return result
}

function fly_batch($id = "")
{
    $CI = &get_instance();

    $CI->db->select("id,name,created_at,created_by")
        ->from(db_prefix() . 'ticket_batch')
        ->where('status', 1);
    if (!empty($id)) {
        $CI->db->where('id', $id);
    }



    return  $CI->db->get()->result_array(); // Execute and return result
}

function fly_departure($id = "")
{
    $CI = &get_instance();

    $CI->db->select("id,name")
        ->from(db_prefix() . 'departure_location')
        ->where('status', 1);
    if (!empty($id)) {
        $CI->db->where('id', $id);
    }

    return  $CI->db->get()->result_array(); // Execute and return result
}


function fly_status($id = "")
{
    $CI = &get_instance();

    $CI->db->select("*")
        ->from(db_prefix() . 'ticket_status')
        ->where('status', 1);
    if (!empty($id)) {
        $CI->db->where('id', $id);
    }

    return  $CI->db->get()->result_array(); // Execute and return result
}

function get_apostille_document_data($client_id, $visa_apostile = 0)
{
    $CI = &get_instance();
    $CI->db->select("r.*, 
        o.name, 
        v.name AS vendor_name, 
        CONCAT(s.firstname, ' ', s.lastname) AS created_by, 
        CASE 
             WHEN r.id IS NULL THEN 'Pending'  
    WHEN r.received_status = 1 THEN 'Received'  
    WHEN r.received_status = 0 THEN 'Sent'     
    ELSE 'Pending'          
        END AS apostille_status,
        IF(ord.id IS NULL, 'No', 'Yes') AS original_received")
        ->from(db_prefix() . 'orignal_documents o')
        ->join(db_prefix() . 'client_apostille_data r', "o.id = r.doc_id AND r.userid = {$client_id}", "LEFT")
        ->join(db_prefix() . 'orignal_documents_received ord', "ord.doc_id = o.id AND ord.userid = {$client_id}", "LEFT")
        ->join(db_prefix() . 'vendor_list v', "v.id = r.vendor_id", "LEFT")
        ->join(db_prefix() . 'staff s', "s.staffid = r.created_by", "LEFT");

    $CI->db->where("o.apostile_status", 1);
    if (!empty($visa_apostile)) {
        $CI->db->or_where("o.visa_apostile", 1);
    }

    return $CI->db->order_by("o.id", "asc")->get()->result_array();
}

function activity_apostille_document($id)
{
    $CI = &get_instance();
    $sorting = hooks()->apply_filters('lead_activity_log_default_sort', 'DESC');
    $CI->db->where('client_id', $id);
    $CI->db->order_by('date', $sorting);
    return $CI->db->get(db_prefix() . 'apostille_document_activity')->result_array();
}

function get_approval_documents($userid)
{
    $CI = &get_instance();
    $approval_documents = $CI->db->select("data as url")->where(array("document_status" => 1, "client_id" => $userid))->get(db_prefix() . "client_documents")->row();
    print_r($approval_documents);
}

function visa_details($client_id, $limit = 0, $show_all = 0)
{
    $CI = &get_instance();
    $CI->db->where('userid', $client_id);
    if (empty($show_all)) {
        $CI->db->where('status!=', 4);
    } else {
        // $CI->db->where('status!=', 4);

    }
    $CI->db->order_by('id', "asc");
    if (!empty($limt)) {
        $CI->db->where('status!=', 4);
        $CI->db->limit(1);
    }
    return $CI->db->get(db_prefix() . 'visa_details')->result_array();
}

// function validate_orignal_documents($client_ids, $country_names = [])
// {
//     if (!empty($country_names)) {
//         $country_names = check_country_rest($country_names);
//     }


//     $CI = &get_instance();

//     // Step 1: Fetch valid documents
//     $CI->db->select("o.id AS doc_id, o.name AS doc_name,if(minor_status = 2,o.id ,0) check_minor")
//         ->from(db_prefix() . 'orignal_documents o')
//         ->where('o.status', 1);


//     if (in_array("Rest", $country_names)) {
//         $CI->db->where('o.visa_rest', 1);
//     } else {
//         $CI->db->where('o.visa_georgia', 1);
//     }

//     // Optional: filter by specific document IDs (ensure $document_ids is set if used)
//     // if (!empty($document_ids ?? [])) {
//     //     $CI->db->where_in('o.id', $document_ids);
//     // }

//     $all_documents = $CI->db->get()->result_array();

//     $minor_id = "";

//     $filtered_documents = array_filter($all_documents, function ($doc) use (&$minor_id) {
//         if (!empty($doc['check_minor']) && $doc['check_minor'] > 0) {
//             $minor_id = $doc['doc_id'];
//             return true; // Exclude this document
//         }
//         return true; // Keep documents where check_minor is 0
//     });


//     $valid_doc_ids = array_column($filtered_documents, 'doc_id');

//     if (empty($valid_doc_ids)) {
//         return [
//             "error" => true,
//             "message" => "No valid documents found for the selected country.",
//         ];
//     }

//     // Step 2: Get received documents
//     $CI->db->select("r.userid, r.doc_id")
//         ->from(db_prefix() . 'orignal_documents_received r')
//         ->where_in('r.userid', $client_ids)
//         ->where_in('r.doc_id', $valid_doc_ids);
//     $received_docs = $CI->db->get()->result_array();

//     // Step 3: Map received documents by user ID
//     $received_map = [];
//     foreach ($received_docs as $row) {
//         $received_map[$row['userid']][] = $row['doc_id'];
//     }

//     // Step 4: Fetch client names
//     $CI->db->select("b.userid, CONCAT(b.first_name, ' ', b.last_name) AS clientName,CASE 
//     WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 1
//     ELSE 0
// END AS is_minor,country")
//         ->from(db_prefix() . 'basic_details b')
//          ->join(db_prefix() . 'admission_preferences a','a.userid = b.userid','left')
//         ->where_in('b.userid', $client_ids);
//     $clients = $CI->db->get()->result_array();


//     $errors = [];
//     foreach ($clients as $client) {
//         $user_id = $client['userid'];
//         $client_name = $client['clientName'];
//         $clientMinor = $client['is_minor'];
//         $received = isset($received_map[$user_id]) ? $received_map[$user_id] : [];

//         foreach ($all_documents as $doc) {

//             // Skip document check if it is minor-only and client is not a minor
//             if ($doc["check_minor"] > 0 && $clientMinor == 0) {
//                 continue;
//             }

//             // Check if document was received
//             if (!in_array($doc['doc_id'], $received)) {
//                 $errors[] = "User '{$client_name}' has not received original document '{$doc['doc_name']}'.";

//                 return [
//                     "error" => true,
//                     "message" => $errors, // Return first error message (optional: return all as list)
//                 ];
//             }
//         }
//     }

//     // If any errors found, return the first one
//     if (!empty($errors)) {
//         return [
//             "error" => true,
//             "message" => $errors[0], // Return first error message (optional: return all as list)
//         ];
//     }

//     return [
//         "error" => false,
//         "message" => "Validation passed."
//     ];
// }

function validate_orignal_documents($client_ids, $country_names = [], $visaApostile = 0)
{
    $CI = &get_instance();

    // Step 1: If no countries provided, fetch them based on client IDs
    if (empty($country_names)) {
        $CI->db->select("DISTINCT TRIM(LOWER(a.study_country)) AS country")
            ->from(db_prefix() . 'admission_preferences a')
            ->where_in('a.userid', $client_ids);
        $country_rows = $CI->db->get()->result_array();

        $country_names = array_column($country_rows, 'country');
        $country_names = array_map('ucfirst', $country_names); // Normalize, e.g., 'georgia'
    }

    // Step 2: Normalize countries (handle 'Rest' if needed)
    $country_names = check_country_rest($country_names);

    // Step 3: Fetch valid documents based on visa country
    $CI->db->select("o.id AS doc_id, o.name AS doc_name, IF(minor_status = 2, o.id, 0) AS check_minor")
        ->from(db_prefix() . 'orignal_documents o')
        ->where('o.status', 1);
    if ($visaApostile == 1) {
        $CI->db->where('o.visa_apostile', 1);
    }

    if (in_array("Rest", $country_names)) {
        $CI->db->where('o.visa_rest', 1);
    } else {
        $CI->db->where('o.visa_georgia', 1);
    }

    $all_documents = $CI->db->get()->result_array();


    // Step 4: Filter minor documents
    $filtered_documents = $all_documents;
    $valid_doc_ids = array_column($filtered_documents, 'doc_id');

    if (empty($valid_doc_ids)) {
        $data =  [
            "error" => true,
            "message" => "No valid documents found for the selected country.",
            'resp_code' => 'ERR',
            'resp_desc' => "No valid documents found for the selected country.",
        ];
        echo json_encode($data);
        die;
    }

    // Step 5: Get received documents
    $CI->db->select("r.userid, r.doc_id")
        ->from(db_prefix() . 'orignal_documents_received r')
        ->where_in('r.userid', $client_ids)
        ->where_in('r.doc_id', $valid_doc_ids);
    $received_docs = $CI->db->get()->result_array();

    // Step 6: Map received documents by user ID
    $received_map = [];
    foreach ($received_docs as $row) {
        $received_map[$row['userid']][] = $row['doc_id'];
    }

    // Step 7: Fetch client details (including minor status and country)
    $CI->db->select("b.userid, CONCAT(b.first_name, ' ', b.last_name) AS clientName, 
                     CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 1 ELSE 0 END AS is_minor,
                     TRIM(LOWER(a.study_country)) AS country")
        ->from(db_prefix() . 'basic_details b')
        ->join(db_prefix() . 'admission_preferences a', 'a.userid = b.userid', 'left')
        ->where_in('b.userid', $client_ids);
    $clients = $CI->db->get()->result_array();

    // Step 8: Validate for each client
    $errors = [];
    foreach ($clients as $client) {
        $user_id = $client['userid'];
        $client_name = $client['clientName'];
        $clientMinor = $client['is_minor'];
        $received = $received_map[$user_id] ?? [];

        foreach ($filtered_documents as $doc) {
            // Skip minor-only documents for non-minors
            if ($doc['check_minor'] > 0 && $clientMinor == 0) {
                continue;
            }

            // Check if the document is received
            if (!in_array($doc['doc_id'], $received)) {
                // $errors[] = "User '{$client_name}' has not received original document '{$doc['doc_name']}'.";
                $data =  [
                    "error" => true,
                    "message" => "No valid documents found for the selected country.",
                    'resp_code' => 'ERR',
                    'resp_desc' =>  "User '{$client_name}' has not received original document '{$doc['doc_name']}'."
                ];
                echo json_encode($data);
                die;
            }
        }
    }

    return [
        "error" => false,
        "message" => "Validation passed."
    ];
}


function check_invitation_letter($client_ids_array = [])
{
    $CI = &get_instance();
    $errors = [];

    if (empty($client_ids_array)) {
        $data = [
            "error" => true,
            "message" => "No client IDs provided.",
            'resp_code' => 'ERR',
            'resp_desc' => "No client IDs provided."

        ];

        echo json_encode($data);
        die;
    }

    try {
        // Get records where invitation_letter is either NULL or empty string
        $CI->db->select(db_prefix() . 'admission_preferences.userid, ' . db_prefix() . 'client_university_shortlisting.invitation_letter');
        $CI->db->from(db_prefix() . 'admission_preferences');
        $CI->db->join(
            db_prefix() . 'client_university_shortlisting',
            db_prefix() . 'admission_preferences.userid = ' . db_prefix() . 'client_university_shortlisting.client_id 
             AND tbladmission_preferences.primary_university = ' . db_prefix() . 'client_university_shortlisting.university_name',
            "LEFT"
        );
        $CI->db->where_in(db_prefix() . 'admission_preferences.userid', $client_ids_array);
        $CI->db->group_start();
        $CI->db->where(db_prefix() . 'client_university_shortlisting.invitation_letter', '');
        $CI->db->or_where(db_prefix() . 'client_university_shortlisting.invitation_letter IS NULL', NULL, FALSE);
        $CI->db->group_end();

        $query = $CI->db->get();
        $result = $query->result_array();

        if (!empty($result)) {
            foreach ($result as $row) {
                $client_name = get_client_name($row["userid"]);
                $errors[] = "User '{$client_name}' has not received an Invitation Letter.";
                $data = [
                    'resp_code' => 'ERR',
                    'resp_desc' => "User '{$client_name}' has not received an Invitation Letter."
                ];
                echo json_encode($data);
                die;
            }
        }

        // Check if any IDs are completely missing from the shortlist table (no join record)
        $CI->db->select('DISTINCT(' . db_prefix() . 'admission_preferences.userid)');
        $CI->db->from(db_prefix() . 'admission_preferences');
        $CI->db->join(
            db_prefix() . 'client_university_shortlisting',
            db_prefix() . 'admission_preferences.userid = ' . db_prefix() . 'client_university_shortlisting.client_id 
             AND tbladmission_preferences.primary_university = ' . db_prefix() . 'client_university_shortlisting.university_name',
            "LEFT"
        );
        $CI->db->where_in(db_prefix() . 'admission_preferences.userid', $client_ids_array);
        $CI->db->where(db_prefix() . 'client_university_shortlisting.client_id IS NULL', null, false);

        $missing_query = $CI->db->get();
        $missing_rows = $missing_query->result_array();

        foreach ($missing_rows as $row) {
            $client_name = get_client_name($row["userid"]);
            $errors[] = "User '{$client_name}' does not have any university shortlisting record.";

            $data = [
                'resp_code' => 'ERR',
                'resp_desc' => "User '{$client_name}' has not received an Invitation Letter."
            ];
            echo json_encode($data);
            die;
        }

        if (!empty($errors)) {
            return [
                "error" => true,
                "message" => $errors,
            ];
            $data = [
                'resp_code' => 'ERR',
                'resp_desc' => $errors
            ];
            echo json_encode($data);
            die;
        }

        return [
            "error" => false,
            "message" => "All users have valid Invitation Letters.",
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => ["Server error: " . $e->getMessage()],
        ];
    }
}

function check_neet_Aff($client_ids_array)
{
    $CI = &get_instance();

    if (empty($client_ids_array)) {
        $data = [
            "error" => true,
            "message" => "No client IDs provided.",
            'resp_code' => 'ERR',
            'resp_desc' => "No client IDs provided."
        ];

        echo json_encode($data);
        die;
    }

    try {
        // Select client_id and neet_aff_status
        $CI->db->select([
            db_prefix() . 'clients.userid',
            'IFNULL(' . db_prefix() . 'neet_status.neet_aff_status, 1) as neet_aff_status'
        ]);

        $CI->db->from(db_prefix() . 'clients');
        $CI->db->join(
            db_prefix() . 'academic_entrance_score',
            db_prefix() . 'academic_entrance_score.client_id = ' . db_prefix() . 'clients.userid',
            "LEFT"
        );
        $CI->db->join(
            db_prefix() . 'neet_status',
            db_prefix() . 'neet_status.id = ' . db_prefix() . 'academic_entrance_score.type',
            "LEFT"
        );

        // Filter by provided client IDs
        $CI->db->where_in(db_prefix() . 'clients.userid', $client_ids_array);

        // Only where neet_aff_status = 0
        $CI->db->where('IFNULL(' . db_prefix() . 'neet_status.neet_aff_status, 1) =', 1, FALSE);
        $query = $CI->db->get();
        $results = $query->result_array();
        $doc_id = ORIGNAL_DOCUMENT_NEET_AFF_ID;

        if (!empty($results)) {
            // Extract all client IDs from results
            $new_clientIds = array_column($results, "userid");

            // Convert array to comma-separated string for SQL
            $clientIdsStr = implode(",", $new_clientIds);

            // SQL to check if doc_id exists in client_documents JSON
            $sql = "
        SELECT client_id,
               CASE 
                   WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id') IS NOT NULL 
                        AND JSON_CONTAINS(
                              JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id'),
                              JSON_QUOTE('{$doc_id}')
                        )
                   THEN 'YES'
                   
                   WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$.\"{$doc_id}\".id') IS NOT NULL
                   THEN 'YES'
                   
                   ELSE 'NO'
               END AS status
        FROM " . db_prefix() . "client_documents
        WHERE client_id IN ($clientIdsStr)
    ";

            $query = $CI->db->query($sql);
            $docResults = $query->result_array();

            // ✅ Filter out only client_ids where status = 'NO'
            $missingClients = array_column(
                array_filter($docResults, function ($row) {
                    return $row['status'] === 'NO';
                }),
                'client_id'
            );


            foreach ($missingClients as $row) {
                $client_name = get_client_name($row);
                $errors[] = "User '{$client_name}' does not have any university shortlisting record.";

                $data = [
                    'resp_code' => 'ERR',
                    'resp_desc' => "User '{$client_name}' has mandatory to upload Neet Affidavite."
                ];
                echo json_encode($data);
                die;
            }
        }



        return [
            "error" => false,
            "data" => $results, // all matching client_ids + neet_aff_status = 0
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => ["Server error: " . $e->getMessage()],
        ];
    }
}


function check_name_Aff($client_ids_array)
{
    $CI = &get_instance();

    if (empty($client_ids_array)) {
        $data = [
            "error" => true,
            "message" => "No client IDs provided.",
            'resp_code' => 'ERR',
            'resp_desc' => "No client IDs provided."
        ];

        echo json_encode($data);
        die;
    }

    try {
        // Select client_id and neet_aff_status
        $CI->db->select([
            db_prefix() . 'clients.userid',
        ]);
        $CI->db->from(db_prefix() . 'clients');
        // Filter by provided client IDs
        $CI->db->where_in(db_prefix() . 'clients.userid', $client_ids_array);

        // Only where neet_aff_status = 0
        $CI->db->where(db_prefix() . 'clients.name_aff_status', 1);
        $query = $CI->db->get();
        $results = $query->result_array();

        $doc_id = ORIGNAL_DOCUMENT_NAME_AFF_ID;

        if (!empty($results)) {
            // Extract all client IDs from results
            $new_clientIds = array_column($results, "userid");

            // Convert array to comma-separated string for SQL
            $clientIdsStr = implode(",", $new_clientIds);

            // SQL to check if doc_id exists in client_documents JSON
            $sql = "
        SELECT client_id,
               CASE 
                   WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id') IS NOT NULL 
                        AND JSON_CONTAINS(
                              JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id'),
                              JSON_QUOTE('{$doc_id}')
                        )
                   THEN 'YES'
                   
                   WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$.\"{$doc_id}\".id') IS NOT NULL
                   THEN 'YES'
                   
                   ELSE 'NO'
               END AS status
        FROM " . db_prefix() . "client_documents
        WHERE client_id IN ($clientIdsStr)
    ";

            $query = $CI->db->query($sql);
            $docResults = $query->result_array();

            // ✅ Filter out only client_ids where status = 'NO'
            $missingClients = array_column(
                array_filter($docResults, function ($row) {
                    return $row['status'] === 'NO';
                }),
                'client_id'
            );


            foreach ($missingClients as $row) {
                $client_name = get_client_name($row);
                $errors[] = "User '{$client_name}' does not have any university shortlisting record.";

                $data = [
                    'resp_code' => 'ERR',
                    'resp_desc' => "User '{$client_name}' has mandatory to upload Name Affidavite."
                ];
                echo json_encode($data);
                die;
            }
        }

        return [
            "error" => false,
            "data" => $results, // all matching client_ids + neet_aff_status = 0
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => ["Server error: " . $e->getMessage()],
        ];
    }
}


function check_minor_Aff($client_ids_array)
{
    $CI = &get_instance();

    if (empty($client_ids_array)) {
        $data = [
            "error" => true,
            "message" => "No client IDs provided.",
            'resp_code' => 'ERR',
            'resp_desc' => "No client IDs provided."
        ];
        echo json_encode($data);
        die;
    }

    try {
        // ✅ Select only minors (age < 18)
        $CI->db->select([
            db_prefix() . 'basic_details.userid',
            'CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 1 ELSE 0 END AS is_minor'
        ]);
        $CI->db->from(db_prefix() . 'basic_details');
        $CI->db->where_in(db_prefix() . 'basic_details.userid', $client_ids_array);

        $query = $CI->db->get();
        $results = $query->result_array();

        $doc_id = ORIGNAL_DOCUMENT_MINOR_ID; // Name Affidavit document

        if (!empty($results)) {
            // ✅ Extract only minors (is_minor = 1)
            $minor_clientIds = array_column(
                array_filter($results, function ($row) {
                    return $row['is_minor'] == 1;
                }),
                'userid'
            );

            if (!empty($minor_clientIds)) {
                $clientIdsStr = implode(",", $minor_clientIds);

                // ✅ SQL to check if doc_id exists in client_documents JSON
                $sql = "
                    SELECT client_id,
                           CASE 
                               WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id') IS NOT NULL 
                                    AND JSON_CONTAINS(
                                          JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id'),
                                          JSON_QUOTE('{$doc_id}')
                                    )
                               THEN 'YES'
                               ELSE 'NO'
                           END AS status
                    FROM " . db_prefix() . "client_documents
                    WHERE client_id IN ($clientIdsStr)
                ";

                $query = $CI->db->query($sql);
                $docResults = $query->result_array();

                // ✅ Filter minors missing the affidavit
                $missingClients = array_column(
                    array_filter($docResults, function ($row) {
                        return $row['status'] === 'NO';
                    }),
                    'client_id'
                );

                foreach ($missingClients as $client_id) {
                    $client_name = get_client_name($client_id);
                    $data = [
                        'resp_code' => 'ERR',
                        'resp_desc' => "User '{$client_name}' must upload the Minor Affidavit (required for minors)."
                    ];
                    echo json_encode($data);
                    die;
                }
            }
        }

        // ✅ If everything is fine
        return [
            "error" => false,
            "data" => $results,
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => ["Server error: " . $e->getMessage()],
        ];
    }
}



function get_orignal_document_data_list_visa($client_ids_array = [], $check_status = 0, $vendor_id = "")
{
    $CI = &get_instance();

    // Validate input
    if ($check_status == 0 || empty($client_ids_array)) {
        return [
            "error" => true,
            "message" => "Something went wrong. Required data missing or status check not enabled."
        ];
    }

    $client_ids = array_map('intval', $client_ids_array); // Safe casting to integer

    $resultOrignal = validate_orignal_documents($client_ids, []);
    if (!empty($resultOrignal["error"]) && $resultOrignal["error"] == 1) {
        $data = [
            'error'               => true,
            'message'               =>  $resultOrignal["message"],
        ];

        // set_alert('danger',  $message);
        return $data;
    }


    check_invitation_letter($client_ids_array);

    $stage = 9;
    $lead_type = 2;
    foreach ($client_ids as $userid) {
        $university_shortlisting = $CI->clients_model->university_shortlisting($userid, 1);

        if (!is_array($university_shortlisting)) {
            $university_shortlisting = [];
        }
        // Extract country names
        $country_names = array_column($university_shortlisting, "country_name");

        // Fetch document types based on lead type and country names
        $documents_type = get_documents($lead_type, $country_names, "", $stage);

        // Map document types by ID
        $documents_type_ids = !empty($documents_type) ? array_column($documents_type, null, "id") : [];

        // Fetch client documents
        $applicant_documents = get_clients_documents($userid);
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
            if (!empty($doc_required)) {
                $client_name = get_client_name($userid);
                $doc_names = implode(", ", $doc_required);
                return [
                    "error" => true,
                    "message" => "{$client_name} - {$doc_names} are mandatory to proceed to the next step."

                ];
                die;
            }
        }

        // return $doc_required; // Return the missing document names
    }



    if ($check_status == 1) {
        $CI->db->select("v.userid")
            ->from(db_prefix() . 'visa_details v')
            ->where_in('v.userid', $client_ids);

        $check_Visa_data = $CI->db->get()->result_array();

        if (!empty($check_Visa_data)) {
            $user_id = $check_Visa_data[0]["userid"];

            $client_name = get_client_name($user_id);

            return [
                "error" => true,
                "message" => "User '{$client_name}' has already visa Apply."
            ];
        }
    } else if ($check_status == 2) {
        // Step 1: Fetch existing combinations from DB
        $CI->db->select("r.id,r.userid, r.vendor_id")
            ->from(db_prefix() . 'visa_details r')
            ->where_in('r.userid', $client_ids)
            ->where('r.bulk', 1);

        if (!empty($vendor_id)) {
            $CI->db->where_in('r.vendor_id', $vendor_id);
        }

        $existing_combinations = $CI->db->get()->result_array();

        // Step 2: Build a set of existing keys for fast lookup
        $existing_map = [];
        foreach ($existing_combinations as $row) {
            $existing_map[$row['userid'] . '_' . $row['vendor_id']] = true;
        }



        // Step 3: Loop through input combinations to validate
        foreach ($client_ids as $userid) {
            $key = $userid . '_' . $vendor_id;
            if (!isset($existing_map[$key])) {
                $client_name = get_client_name($userid);

                return [
                    "error" => true,
                    "message" => "No visa apply record found for client '{$client_name}'."
                ];

                die;
            }
        }

        return $existing_combinations;
    }
    // If any errors found, return the first one
    if (!empty($errors)) {
        return [
            "error" => true,
            "message" => $errors[0], // Return first error message (optional: return all as list)
        ];
    }

    return [
        "error" => false,
        "message" => "Validation passed."
    ];
}

function doc_urls_additional($user_id)
{
    $CI = &get_instance();
    $user_id = (int) $user_id;

    $final_files = [];

    // Centralized config: table name => fields with labels
    $queries = [
        db_prefix() . 'clients' => [
            'where' => "userid = {$user_id}",
            'order' => 'registration_slip DESC',
            'fields' => [
                'quotation' => 'Quotation',
                'registration_slip' => 'Registration Slip',
                'registration_slip_invoice' => 'Registration Slip Invoice',
                'fees_structure' => 'Fees Structure',
                'refund_payment_proof' => 'Refund Payment Proof'
            ]
        ],
        db_prefix() . 'client_university_shortlisting' => [
            'where' => "client_id = {$user_id}",
            'order' => 'university_fees_payment_slip DESC',
            'fields' => [
                'application_file' => 'Application Letter',
                'ministry_payment' => 'MD Payment Proof',
                'fees_deposite_slip' => 'Payment Proof',
                'university_fees_payment_slip' => 'University Payment Receipt',
                'invitation_letter' => 'Invitation Letter'
            ]
        ],
        db_prefix() . 'visa_details' => [
            'where' => "userid = {$user_id}",
            'fields' => [
                'file' => 'Visa'
            ]
        ],
        // db_prefix() . 'ticket_data' => [
        //     'where' => "client_id = {$user_id}",
        //     'fields' => [
        //         'file' => 'Ticket'
        //     ]
        // ],
    ];

    // Build and run queries dynamically
    foreach ($queries as $table => $config) {
        $fieldSelections = [];
        foreach ($config['fields'] as $field => $label) {
            $fieldSelections[] = "{$field} AS `{$label}`";
        }

        $sql = "SELECT " . implode(', ', $fieldSelections) . " FROM {$table} WHERE {$config['where']}";
        if (!empty($config['order'])) {
            $sql .= " ORDER BY {$config['order']}";
        }

        $results = $CI->db->query($sql)->result_array();
        foreach ($results as $row) {
            foreach ($row as $label => $path) {
                if (!empty($path) && $path !== '0') {
                    $final_files[] = [
                        'name' => $label,
                        'url' => base_url($path)
                    ];
                }
            }
        }
    }

    return $final_files;
}

function get_ev_partner()
{
    $CI = &get_instance();

    try {
        // Fetch data from the `document_upload_type` table with a join to the `file_type` table
        $board_dropdown = $CI->db
            ->select("*")
            ->where(array("status" => 1))
            ->from(db_prefix() . 'ev_partner')
            ->get()
            ->result_array();

        return $board_dropdown; // Return the fetched data
    } catch (Exception $e) {
        // Log the error message if an exception occurs
        log_message('error', 'Error fetching document: ' . $e->getMessage());

        return []; // Return an empty array to ensure function fails gracefully
    }
}

function get_universities_list($search = '')
{
    $CI = &get_instance();

    try {
        $CI->db->select('*')
            ->from(db_prefix() . 'universities_name')
            ->where('status', 1);

        $search = trim($search);

        if (!empty($search)) {
            $CI->db->group_start(); // Begin search grouping

            if (ctype_digit($search)) {
                $CI->db->where('id', (int)$search);

                if (strlen($search) >= 3) {
                    $CI->db->or_like('name', $search);
                }
            } else {
                $CI->db->like('name', $search);
            }

            $CI->db->group_end(); // End search grouping
        }

        // Order: prioritize exact ID match if numeric
        if (ctype_digit($search)) {
            $CI->db->order_by("CASE WHEN id = " . (int)$search . " THEN 0 ELSE 1 END", "ASC", false);
        }

        $CI->db->order_by('name', 'ASC');
        $CI->db->limit(50);

        return $CI->db->get()->result_array();
    } catch (Exception $e) {
        log_message('error', 'Error fetching universities list: ' . $e->getMessage());
        return [];
    }
}



function study_abroad_vendors()
{
    $CI = &get_instance();
    return $study_abroad_vendors = $CI->db->select("*")
        ->where('status', 1)
        ->from(db_prefix() . 'vendor_study_abroad')
        ->get()
        ->result_array();
}

function applicant_pendency()
{
    $CI = &get_instance();
    $applicationPendency = $CI->db->select("*")
        ->from(db_prefix() . 'client_university_pendency')
        ->get()
        ->result_array();

    $pendencyArray = [];
    foreach ($applicationPendency as $pendency) {
        if (isset($pendency["tracker_id"]) && $pendency["tracker_id"] !== null) {
            $pendencyArray[$pendency["shortlisting_id"]][$pendency["tracker_id"]][] = $pendency;
        }
    }

    return $pendencyArray;
}

function applicant_pendency_status()
{
    $CI = &get_instance();
    return $applicationPendencyStatue = $CI->db->select("*")
        ->from(db_prefix() . 'applicant_pendency_status')
        ->get()
        ->result_array();
}


function pendency_status()
{
    $CI = &get_instance();
    return $PendencyStatue = $CI->db->select("*")
        ->from(db_prefix() . 'pendency_status')
        ->get()
        ->result_array();
}

function offerletterStatus()
{
    $CI = &get_instance();
    return $offerletterStatus = $CI->db->select("*")
        ->from(db_prefix() . 'offer_letter_status')
        ->get()
        ->result_array();
}

function filter_country_university_array($leadType)
{
    $CI = &get_instance();

    $CI->db->select('s.country_name, s.university_name, s.country_id, s.university_id,st.staffid,st.firstname,st.lastname,t.id source_id,t.name source_name,group_concat(c.userid) as client_ids,group_concat(ap.acadmic_year) as acadmic_year');
    $CI->db->from(db_prefix() . 'clients c');
    $CI->db->join(db_prefix() . 'leads l', 'c.leadid = l.id', "LEFT");
    $CI->db->join(db_prefix() . 'admission_preferences ap', 'ap.userid = c.userid', "LEFT");
    $CI->db->join(db_prefix() . 'client_university_shortlisting s', 'c.userid = s.client_id and s.status=1', "LEFT");
    $CI->db->join(db_prefix() . 'staff st', 'c.addedfrom = st.staffid', "LEFT");
    $CI->db->join(db_prefix() . 'leads_sources t', 'l.source = t.id', "LEFT");
    $CI->db->where('l.type', $leadType);
    if ($leadType == 2) {
        $CI->db->or_where('c.client_type ', 2);
    }

    $CI->db->where('s.university_name!= ', null);

    $CI->db->group_by('s.country_name, s.university_name,c.addedfrom,t.id,ap.acadmic_year');

    $query = $CI->db->get();
    $result = $query->result_array();
    if (is_admin()) {
        //   echo  $CI->db->last_query();
    }

    $countries = [];
    $universities = [];
    $counselor = [];
    $sources = [];
    $acadmic_year = [];

    $seenCountries = [];
    $seenUniversities = [];
    $seenCounselor = [];
    $seenSources = [];
    $seenAcadmic_year = [];


    foreach ($result as $row) {
        if (!empty($row['country_name']) && !isset($seenCountries[$row['country_name']])) {
            $countries[] = [
                "id" => $row['country_id'],
                "country_name" => $row['country_name']
            ];
            $seenCountries[$row['country_name']] = true;
        }

        if (!empty($row['university_name']) && !isset($seenUniversities[$row['university_name']])) {
            $universities[] = [
                "id" => $row['university_id'],
                "university_name" => $row['university_name']
            ];
            $seenUniversities[$row['university_name']] = true;
            $seenAcadmic_year[$row['university_name']] = array_unique(array_map(fn($year) => ['id' => $year, 'name' => $year], explode(",", $row['acadmic_year'])), SORT_REGULAR);
        }
        if (!empty($row['staffid']) && !isset($seenCounselor[$row['staffid']])) {
            $counselor[] = [
                "staffid" => $row['staffid'],
                "firstname" => $row['firstname'],
                "lastname" => $row['lastname']
            ];
            $seenCounselor[$row['staffid']] = true;
        }
        if (!empty($row['source_id']) && !isset($seenSources[$row['source_id']])) {
            $sources[] = [
                "id" => $row['source_id'],
                "name" => $row['source_name']
            ];
            $seenSources[$row['source_id']] = true;
        }
    }

    // Optional: Sort alphabetically by name
    usort($countries, fn($a, $b) => strcmp($a['country_name'], $b['country_name']));
    usort($universities, fn($a, $b) => strcmp($a['university_name'], $b['university_name']));
    usort($counselor, fn($a, $b) => strcmp($a['staffid'], $b['staffid']));
    usort($sources, fn($a, $b) => strcmp($a['id'], $b['id']));
    usort($acadmic_year, fn($a, $b) => strcmp($a['id'], $b['id']));


    return [
        'countries' => $countries,
        'universities' => $universities,
        'counselor' => $counselor,
        'source' => $sources,
        'acadmic_year' => $seenAcadmic_year

    ];
}


function get_diploma_board_list()
{
    $CI = &get_instance();

    try {
        // Build query
        $CI->db->select('*')
            ->from(db_prefix() . 'diploma_board')
            ->where('status', 1);

        // Order and limit
        $CI->db->order_by('name', 'ASC');
        $CI->db->limit(100);

        // Execute query
        $university_dropdown = $CI->db->get()->result_array();

        return $university_dropdown;
    } catch (Exception $e) {
        log_message('error', 'Error fetching diploma list: ' . $e->getMessage());
        return [];
    }
}

function get_offer_letters($client_id, $shortlisting_id)
{

    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->from(db_prefix() . 'university_offer_letter');
    $CI->db->where('client_id', $client_id);
    $CI->db->where('shortlisting_id', $shortlisting_id);
    $CI->db->order_by("id", "asc");
    $query = $CI->db->get();
    return $result = $query->result_array();
}


function get_pre_deposite($client_id, $shortlisting_id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->from(db_prefix() . 'applicntion_pre_deposite');
    $CI->db->where('client_id', $client_id);
    $CI->db->where('shortlisting_id', $shortlisting_id);
    $CI->db->order_by("id", "asc");
    $query = $CI->db->get();
    return $result = $query->result_array();
}

function get_status_table($table_name)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->from(db_prefix() . $table_name);
    $CI->db->where('status', 1);
    $CI->db->order_by("id", "asc");
    $query = $CI->db->get();
    return $result = $query->result_array();
}

function get_interview($client_id, $shortlisting_id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->from(db_prefix() . 'application_interview');
    $CI->db->where('client_id', $client_id);
    $CI->db->where('shortlisting_id', $shortlisting_id);
    $CI->db->order_by("id", "asc");
    $query = $CI->db->get();
    return $result = $query->result_array();
}

function get_offerLetters($client_id)
{
    $CI = &get_instance();
    $CI->db->select('o.offer_letter file,s.university_name,s.country_name,s.course_name');
    $CI->db->from(db_prefix() . 'university_offer_letter o');
    $CI->db->join(db_prefix() . 'client_university_shortlisting s', 's.client_id = o.client_id AND s.id = o.shortlisting_id', 'LEFT');
    $CI->db->where('o.client_id', $client_id);
    $CI->db->order_by("o.id", "asc");
    $query = $CI->db->get();
    return $result = $query->result_array();
}

function get_preDeposite($client_id)
{
    $CI = &get_instance();
    $CI->db->select('pd.proof_of_deposite file,s.university_name,s.country_name,s.course_name');
    $CI->db->from(db_prefix() . 'applicntion_pre_deposite pd');
    $CI->db->join(db_prefix() . 'client_university_shortlisting s', 's.client_id = pd.client_id AND s.id = pd.shortlisting_id', 'LEFT');
    $CI->db->where('pd.client_id', $client_id);
    $CI->db->order_by("pd.id", "asc");
    $query = $CI->db->get();
    return $result = $query->result_array();
}
