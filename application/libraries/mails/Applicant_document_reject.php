<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Applicant_document_reject extends App_mail_template
{
    protected $for = 'client';

    protected $staff_email;

    protected $client_id;

    protected $staffid;
    protected $post_counselor_id;
    protected $document_id;

    public $slug = 'client-document-reject';

    public $rel_type = 'client';

    public function __construct($staff_email, $client_id, $staffid, $post_counselor_id, $document_id)
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->client_id   = $client_id;
        $this->staffid    = $staffid;
        $this->post_counselor_id    = $post_counselor_id;
        $this->document_id    = $document_id;
    }

    public function build()
    {
        $primary_contact_id = get_primary_contact_user_id($this->client_id);

        // $attachments = $this->ci->clients_model->registration_attachments($this->client_id);
        // if (!empty($attachments)) {
        //     foreach ($attachments as $attachment) {
        //         $this->add_attachment($attachment);
        //     }
        // }
        $this->to($this->staff_email)
            ->set_rel_id($this->staffid)
            ->set_merge_fields('client_merge_fields', $this->client_id, $primary_contact_id, "", $this->post_counselor_id, $this->document_id);
    }
}
