<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Applicant_invitation_notification extends App_mail_template
{
    protected $for = 'client';

    protected $staff_email;

    protected $client_id;

    protected $staffid;
    protected $university_shortlisting_id;

    public $slug = 'client-invitation-notification';

    public $rel_type = 'client';

    public function __construct($staff_email, $client_id, $staffid, $university_shortlisting_id = "")
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->client_id   = $client_id;
        $this->staffid    = $staffid;
        $this->university_shortlisting_id    = $university_shortlisting_id;
    }

    public function build()
    {
        $primary_contact_id = get_primary_contact_user_id($this->client_id);
        $bcc_email =  $this->ci->clients_model->client_assign($this->client_id);

        $attachments = $this->ci->clients_model->invitation_attachments($this->university_shortlisting_id, $this->client_id);
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $this->add_attachment($attachment);
            }
        }

        if (!empty($attachments)) {
            $this->to($this->staff_email)->cc($bcc_email["email"])
                ->set_rel_id($this->staffid)
                ->set_merge_fields('client_merge_fields', $this->client_id, $primary_contact_id);
        }
    }
}
