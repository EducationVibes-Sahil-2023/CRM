<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Excel extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('excel_model');
    }

    /* List all clients */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('excel');
        }

        $data['title']          = "Excel Configuration";
        $view_page = 'admin/excel/manage';
        // Page doesn't exist, load a default page or show an error
        $this->load->view($view_page, $data);
    }

    public function create($id = "")
    {
        // Handle AJAX form submission
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();

            $sheetId = isset($data['sheetid']) ? intval($data['sheetid']) : 0;
            $fromDate = isset($data['fromDate']) && $data['fromDate'] != '' && $data['fromDate'] != '0000-00-00' ? $data['fromDate'] : null;
            $toDate   = isset($data['toDate']) && $data['toDate'] != '' && $data['toDate'] != '0000-00-00' ? $data['toDate'] : null;

            $sheetData = [
                'spreadsheetId' => $data['spreadsheetId'] ?? '',
                'fromDate'      => $fromDate,
                'toDate'        => $toDate,
                'acadmic_year'  => $data['acadmic_year'] ?? '',
                'sheet_name'    => $data['sheet_name'] ?? '',
                'sql_condition' => $data['sql_condition'] ?? '',
                'type' => $data['type'] ?? '',
                "orignal_documents_status" => !empty($data['orignal_documents_status']) ? 1 : 0,
                'status' => 1,
                'autoSync' => 1,
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sorted_data = [];

            foreach ($_POST["column_ids"] as $key => $column_id) {
                if (isset($_POST["sequence"][$key])) {
                    $sequence = (int) $_POST["sequence"][$key];
                    $sorted_data[] = [
                        'column_id' => $column_id,
                        'sequence'  => $sequence
                    ];
                }
            }

            // Sort by sequence ascending
            usort($sorted_data, function ($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });

            // Separate sorted arrays
            $sorted_column_ids = array_column($sorted_data, 'column_id');
            $sorted_sequences = array_column($sorted_data, 'sequence');



            // Implode for saving
            $sheetData["column_ids"] = implode(",", $sorted_column_ids);
            $sheetData["sequence"] = json_encode($sorted_data);




            if ($sheetId > 0) {
                // Update existing sheet
                $update = $this->excel_model->updateSheet($sheetId, $sheetData);
                echo json_encode([
                    'status' => $update ? 'RCS' : 'ERR',
                    'message' => $update ? 'Sheet updated successfully.' : 'Failed to update sheet.'
                ]);
                die;
            } else {
                // Insert new sheet
                $insertId = $this->excel_model->insertSheet($sheetData);
                echo json_encode([
                    'status' => $insertId ? 'RCS' : 'ERR',
                    'message' => $insertId ? 'Sheet created successfully.' : 'Failed to create sheet.',
                    'sheetid' => $insertId
                ]);
                die;
            }

            return;
        }

        // Render page (non-AJAX)
        $data['title'] = "Excel Create";
        $data["tbl_excel_columns"] = $this->db
            ->select('*')
            ->from(db_prefix() . 'excel_column_update')
            ->where('excel_id', 1)
            ->order_by('sequence', 'ASC')
            ->get()
            ->result_array();

        $data["excelInfo"] = $this->db
            ->select('*')
            ->from(db_prefix() . 'excel_data_update')
            ->where('id', $id)
            ->order_by('sequence', 'ASC')
            ->get()
            ->row();

        $this->load->view('admin/excel/create', $data);
    }
}
