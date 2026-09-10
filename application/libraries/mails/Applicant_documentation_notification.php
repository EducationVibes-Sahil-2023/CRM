<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Applicant_documentation_notification extends App_mail_template
{
    protected $for = 'client';

    protected $staff_email;

    protected $client_id;

    protected $staffid;

    public $slug = 'client-documentation-notification';

    public $rel_type = 'client';

    public function __construct($staff_email, $client_id, $staffid)
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->client_id   = $client_id;
        $this->staffid    = $staffid;
    }

    public function build()
    {
        $primary_contact_id = get_primary_contact_user_id($this->client_id);
        $bcc_email =  $this->ci->clients_model->client_assign($this->client_id);

$basicDetails = $this->ci->clients_model->getBasicDetails($this->client_id);
 
 $secondaryEmail = '';

if (!empty($basicDetails->fathers_email)) {
    $secondaryEmail = trim($basicDetails->fathers_email);
}

// Prepare To emails
$CCEmails = [$bcc_email["email"]];

if (!empty($secondaryEmail)) {
    $CCEmails[] = $secondaryEmail;
}
        // $attachments = $this->ci->clients_model->registration_attachments($this->client_id);
        // if (!empty($attachments)) {
        //     foreach ($attachments as $attachment) {
        //         $this->add_attachment($attachment);
        //     }
        // }
        $this->to($this->staff_email)->cc($CCEmails)
            ->set_rel_id($this->staffid)
            ->set_merge_fields('client_merge_fields', $this->client_id, $primary_contact_id);
    }
}
