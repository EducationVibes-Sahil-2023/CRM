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

function getLastEmailWhatsappDate($type, $id, $clientid)
{
    $CI = &get_instance();
    $data = $CI->db->select("*")
        ->from(db_prefix() . "whatsapp_email_logs")
        ->where(array("type" => $type, "template_id" => $id, "clientid" => $clientid))
        ->order_by("id", "DESC")
        ->limit(1)
        ->get()
        ->row();

    if (!empty($data->datetime)) {
        // Format the date using strtotime to convert datetime string to timestamp.
        $formattedDate = date("F j, Y, g:i:s A", strtotime($data->datetime));
        // Capitalize the first letter of the type
        $capitalizedType = ucfirst($type);
        $message = $capitalizedType . " Last send - " . $formattedDate;
        $documentList = "";
        if (!empty($data->documents)) {
            $documentList = "Document List : " . $data->documents;
            $message = $documentList . " " . $message;
        }
        return '<button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $message . '" class="btn btn-success btn-xs"><i class="fa fa-check"></i></button> &nbsp;';
    }
}
