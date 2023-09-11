<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('vendor_model');
    }
    public function visa()
    {

        close_setup_menu();
        if (!is_admin()) {
            access_denied('vendor');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('visa_vendor');
        }
        $data['title']                = _l('Visa Vendors');
        $data['type']                = _l('visa');

        $this->load->view('admin//vendor/visa_manage', $data);
    }
    public function accommodation()
    {

        close_setup_menu();
        if (!is_admin()) {
            access_denied('vendor');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('accommodation_vendor');
        }
        $data['title']                = _l('Accommodation Vendors');
        $data['type']                = _l('accommodation');

        $this->load->view('admin//vendor/accommodation_manage', $data);
    }
    public function applicant()
    {

        close_setup_menu();
        if (!is_admin()) {
            access_denied('vendor');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('applicant_vendor');
        }
        $data['title']                = _l('Applicant Vendors');
        $data['type']                = _l('applicant');

        $this->load->view('admin//vendor/applicant_manage', $data);
    }

    public function vendor_name()
    {
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
            $vendor_type = '';
            if (strtolower($type) == 'visa') {
                $table = db_prefix() . 'vendor_visa';
                $vendor_type = 'vendor';
            } else if (strtolower($type) == 'applicant') {
                $table = db_prefix() . 'profile_creater_vendor';
                $vendor_type = 'name';
            } else if (strtolower($type) == 'accommodation') {
                $table = db_prefix() . 'vendor_accommodation';
                $vendor_type = 'vendor';
            } else {
                echo json_encode([
                    'success'              => 0,
                    'message'              => "Invalid Parameers"
                ]);
                die;
            }
            $data["vendor"] = $data["vendor_name"];
            unset($data['type']);
            unset($data['vendor_name']);

            if (!$this->input->post('vendor_id')) {
                unset($data['vendor_id']);
                $check_vendor = $this->db->select("id")->where([$vendor_type => $data["vendor"]])->get($table)->row_array();

                if (!empty($check_vendor)) {
                    echo json_encode([
                        'success'              => false,
                        'message'              => "Vendor name already exist"
                    ]);
                    die;
                } else {

                    if (strtolower($type) == 'applicant') {
                        $data["name"] = $data["vendor"];
                        unset($data["vendor"]);
                    }
                    
                    $id = $this->vendor_model->add($table, $data);
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
                $id = $data['vendor_id'];
                unset($data['vendor_id']);

                $check_vendor = $this->db->select("id")->where([$vendor_type => $data["vendor"], "id!=" => $id])->get($table)->row_array();

                if (!empty($check_vendor)) {
                    echo json_encode([
                        'success'              => false,
                        'message'              => "Vendor name already exist"
                    ]);
                    die;
                } else {


                    if (strtolower($type) == 'applicant') {
                        $data["name"] = $data["vendor"];
                        unset($data["vendor"]);
                    }

                    $success = $this->vendor_model->update($table, $data, $id);

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
