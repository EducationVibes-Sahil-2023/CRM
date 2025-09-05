<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotations extends AdminController
{
    /* List all clients */
    function __construct()
    {
        parent::__construct();
        $this->load->model('quotation_model');
    }
    public function index($type) {}
    public function mbbs_abroad()
    {

        if ($this->input->is_ajax_request()) {
            if (!has_permission('quotation', '', 'view_own')) {
                ajax_access_denied();
            }
            $this->app->get_table_data('university-quotation-mbbs-abroad');
        }

        $data = [];

        $view_page = 'admin/clients/quotation_university'; // You can switch based on type if needed

        // Load view
        $this->load->view($view_page, $data);
    }
    public function create_mbbs_abroad($id = "")
    {
        if ($this->input->is_ajax_request()) {
            if (!has_permission('quotation', '', 'create')) {
                ajax_access_denied();
            }

            $quotation_id = $this->input->post('quotation_id');

            // ✅ Duplicate check
            $this->db->where([
                'university_name' => $this->input->post('university'),
                'acadmic_year'    => $this->input->post('acadmic_year'),
                'year'            => $this->input->post('study_year')
            ]);
            
            if (!empty($quotation_id)) {
                $this->db->where('id !=', $quotation_id);
            }
            $check_existing = $this->db->get(db_prefix() . 'university_quotation')->row();

            if ($check_existing) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Quotation already exists for the selected university, academic year, and study year.'
                ]);
                exit;
            }

            // ✅ Add/Update quotation via model
            $quotation_id = $this->quotation_model->quotation_add($this->input->post());

            if ($quotation_id) {
                echo json_encode([
                    'resp_code'    => 'RCS',
                    'resp_desc'    => !empty($this->input->post('quotation_id'))
                        ? 'Quotation updated successfully.'
                        : 'Quotation created successfully.',
                    'quotation_id' => $quotation_id
                ]);
            } else {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Failed to save quotation. Please try again.'
                ]);
            }
            exit;
        }

        // ✅ If not AJAX, load quotation view
        $data = [];
        $data["quotation_id"]   = $id;
        $data["quotation_data"] = !empty($id) ? $this->quotation_model->quotation_data($id) : [];
      
        $view_page = 'admin/clients/quotation_university_mbbs_abroad';
        $this->load->view($view_page, $data);
    }
}
