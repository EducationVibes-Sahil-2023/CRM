<?php

defined('BASEPATH') or exit('No direct script access allowed');

function send_gdpr_email_template($template, $user_id)
{
    $CI = &get_instance();
    $CI->load->model('staff_model');

    $staff = $CI->staff_model->get('', ['active' => 1, 'admin' => 1]);

    foreach ($staff as $member) {
        send_mail_template($template, $member, $user_id);
    }
}

function is_gdpr()
{
    return get_option('enable_gdpr') === '1';
}

function getLastEmailWhatsappDate($type, $templateId, $clientid = '', $visitorid = '')
{
    $CI = &get_instance();

    if (empty($type) || empty($templateId)) {
        return '';
    }

    $CI->db->select('datetime, documents')
        ->from(db_prefix() . 'whatsapp_email_logs')
        ->where('type', $type)
        ->where('template_id', $templateId);

    if (!empty($clientid)) {
        $CI->db->where('clientid', $clientid);
    }

    if (!empty($visitorid)) {
        $CI->db->where('visitor_id', $visitorid);
    }
    
    
    if (empty($clientid) && empty($visitorid)) {
        return '';
    }


    $data = $CI->db
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get()
        ->row();

    if (empty($data) || empty($data->datetime)) {
        return '';
    }

    $formattedDate = date(
        'F j, Y, g:i:s A',
        strtotime($data->datetime)
    );

    $message = ucfirst($type) . ' Last sent - ' . $formattedDate;

    if (!empty($data->documents)) {
        $message = 'Document List: ' . $data->documents . ' | ' . $message;
    }

    // Prevent tooltip XSS issues
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    return '
        <button
            type="button"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="' . $message . '"
            class="btn btn-success btn-xs">
            <i class="fa fa-check"></i>
        </button>&nbsp;
    ';
}
