<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsaap extends AdminController
{
    // Bridge POSTs here. Public (no admin login) but secret-protected.
    public function incoming()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $secret = YOUR_SHARED_SECRET;                 // same as bridge .env
        if (!$data || ($data['secret'] ?? '') !== $secret) {
            show_404();
        }
        $this->db->insert('tbl_whatsapp_messages', [
            'staffid'   => (int) $data['user'],
            'direction' => $data['direction'],
            'number'    => $data['number'],
            'body'      => $data['body'],
            'type'      => $data['type'],
            'created'   => date('Y-m-d H:i:s', (int) $data['timestamp']),
        ]);
        echo json_encode(['ok' => true]);
    }

    // Optional admin page that hosts the connect panel.
    public function index()
    {
        $data['bridge'] = BRIDGE_HOST;
        $data['user']   = get_staff_user_id();
        $data['token']  = hash_hmac('sha256', (string) $data['user'], YOUR_SHARED_SECRET);
        $this->load->view('admin/whatsapp', $data);
    }
}