<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotations extends AdminController
{
    /* List all clients */
    public function index($type) {}
    public function mbbs_abroad()
    {

        if ($this->input->is_ajax_request()) {
            if (!has_permission('quotation', '', 'view')) {
                ajax_access_denied();
            }
            $this->app->get_table_data('university-quotation-mbbs-abroad');
        }

        $data = [];

        $view_page = 'admin/clients/quotation_university'; // You can switch based on type if needed

        // Load view
        $this->load->view($view_page, $data);
    }
}
