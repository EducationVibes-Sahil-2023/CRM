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

        $this->load->view('admin/vendor/visa_manage', $data);
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

        $this->load->view('admin/vendor/accommodation_manage', $data);
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

        $this->load->view('admin/vendor/applicant_manage', $data);
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

    public function ma_vendor()
    {
        if (!is_admin()) {
            access_denied('vendor');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('ma_vendors');
        }
        $data['title']                = _l('MA Vendors');
        $data['type']                = _l('applicant');

        $this->load->view('admin/vendor/ma_vendor_manage', $data);
    }


    public function ma_vendor_name()
    {

        if ($this->input->post()) {
            $data    = $this->input->post();
            $table   = db_prefix() . "vendor_list";
            $success = false;
            $message = '';

            // Normalize vendor_type (always string)
            $vendorType = '';
            if (!empty($data['vendor_type'])) {
                if (is_array($data['vendor_type'])) {
                    $vendorType = implode(",", $data['vendor_type']);
                } else {
                    $vendorType = $data['vendor_type']; // already string
                }
            }

      
            // ADD CASE
            if (empty($data['vendor_id'])) {
                // Check if vendor already exists
                $check_vendor = $this->db->select("id")
                    ->where("name", $data["vendor_name"])
                    ->get($table)
                    ->row_array();

                if (!empty($check_vendor)) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Vendor name already exists"
                    ]);
                    exit;
                }

                // Insert new vendor_name
                $insertData = [
                    'name'        => $data['vendor_name'],
                    'status'      => isset($data['status']) ? $data['status'] : 1,
                    'vendor_type' => $vendorType,
                ];

                $this->db->insert($table, $insertData);
                $id = $this->db->insert_id();

                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('vendor'));
                }

                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
                exit;
            }

            // UPDATE CASE
            else {
                $id = $data['vendor_id'];

                // Check duplicate name (excluding current record)
                $check_vendor = $this->db->select("id")
                    ->where("name", $data["vendor_name"])
                    ->where("id!=", $id)
                    ->get($table)
                    ->row_array();

                if (!empty($check_vendor)) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Vendor name already exists"
                    ]);
                    exit;
                }

                // Update vendor
                $updateData = [
                    'name'        => $data['vendor_name'],
                    'status'      => isset($data['status']) ? $data['status'] : 1,
                    'vendor_type' => $vendorType,
                ];

                $this->db->where('id', $id);
                $success = $this->db->update($table, $updateData);

                if ($success) {
                    $message = _l('updated_successfully', _l('vendor'));
                }

                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
                exit;
            }
        }
    }
}
