<?php

defined('BASEPATH') or exit('No direct script access allowed');

class partner extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('partner_model');
    }
    public function university()
    {

        if (!has_permission('partners', '', 'view')) {
            access_denied('Partners view');
            die;
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('university_partner');
        }
        $data['title']                = "University partner";
        $data['type']                = "university";
        $this->load->view('admin/partner/university', $data);
    }

    public function ev_partner()
    {
        if (!has_permission('partners', '', 'view')) {
            access_denied('Partners view');
            die;
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('ev_partner');
        }
        $data['title']                = "EV partner";
        $data['type']                = "ev_partner";
        $this->load->view('admin/partner/ev_partner', $data);
    }

    public function partner_name()
    {

        if (!has_permission('partners', '', 'create')) {
            access_denied('Partners create');
            die;
        }
        if ($this->input->post()) {
            $message          = '';
            $data             = $this->input->post();
            $type = $data["type"];
            if (empty($type)) {
                echo json_encode([
                    'success'              => 0,
                    'message'              => "Invalid Parameers"
                ]);
                die;
            }
            $table = '';
            $partner_type = '';
            if (strtolower($type) == 'university') {
                $table = db_prefix() . 'university_partner';
                $partner_type = 'name';
            } else if (strtolower($type) == 'ev_partner') {
                $table = db_prefix() . 'ev_partner';
                $partner_type = 'name';
            } else {
                echo json_encode([
                    'success'              => 0,
                    'message'              => "Invalid Parameers"
                ]);
                die;
            }
            $data["name"] = $data["name"];
            unset($data['type']);

            if (!$this->input->post('partner_id')) {
                unset($data['partner_id']);
                $check_partner = $this->db->select("id")->where([$partner_type => $data["name"]])->get($table)->row_array();

                if (!empty($check_partner)) {
                    echo json_encode([
                        'success'              => false,
                        'message'              => "partner name already exist"
                    ]);
                    die;
                } else {

                    $data["name"] = $data["name"];
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["created_by"] = get_staff_user_id();

                    $id = $this->partner_model->add($table, $data);
                    if ($id) {
                        $success = true;
                        $message = _l('added_successfully', _l('vender'));
                    }
                    echo json_encode([
                        'success'              => $success,
                        'message'              => $message
                    ]);
                    die;
                }
            } else {

                if (!has_permission('partners', '', 'edit')) {
                    access_denied('Partners edit');
                    die;
                }

                $id = $data['partner_id'];
                unset($data['partner_id']);

                $check_partner = $this->db->select("id")->where([$partner_type => $data["name"], "id!=" => $id])->get($table)->row_array();

                if (!empty($check_partner)) {
                    echo json_encode([
                        'success'              => false,
                        'message'              => "partner name already exist"
                    ]);
                    die;
                } else {


                    if (strtolower($type) == 'applicant') {
                        $data["name"] = $data["partner"];
                        unset($data["partner"]);
                    }

                    $data["updated_at"] = date('Y-m-d H:i:s');
                    $data["updated_by"] = get_staff_user_id();
                    $success = $this->partner_model->update($table, $data, $id);

                    if ($success) {
                        $message = _l('updated_successfully', _l('vender'));
                    }
                    echo json_encode([
                        'success'              => $success,
                        'message'              => $message,
                    ]);
                }
            }
            die;
        }
    }
}
