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

            $post = $this->input->post(NULL, true); // XSS Filtering enabled

            $sheetId     = (int) ($post['sheetid'] ?? 0);
            $excel_type  = (int) ($post['excel_type'] ?? 0);

            $fromDate    = (!empty($post['fromDate']) && $post['fromDate'] !== '0000-00-00') ? $post['fromDate'] : null;
            $toDate      = (!empty($post['toDate']) && $post['toDate'] !== '0000-00-00') ? $post['toDate'] : null;

            $sheetData = [
                'spreadsheetId'             => $post['spreadsheetId'] ?? '',
                'fromDate'                  => $fromDate,
                'toDate'                    => $toDate,
                'acadmic_year'              => $post['acadmic_year'] ?? '',
                'sheet_name'                => $post['sheet_name'] ?? '',
                'sql_condition'             => $post['sql_condition'] ?? '',
                'type'                      => $post['type'] ?? '',
                'orignal_documents_status'  => !empty($post['orignal_documents_status']) ? 1 : 0,
                'apostile_documents_status' => !empty($post['apostile_documents_status']) ? 1 : 0,
                'status'                    => 1,
                'autoSync'                  => 1,
                'created_by'                => get_staff_user_id(),
                'created_at'                => date('Y-m-d H:i:s'),
                'excel_type'                => $excel_type,
            ];

            // Sort column IDs by sequence
            $sorted_data = [];
            if (!empty($post["column_ids"]) && !empty($post["sequence"])) {
                foreach ($post["column_ids"] as $key => $column_id) {
                    if (isset($post["sequence"][$key])) {
                        $sorted_data[] = [
                            'column_id' => $column_id,
                            'sequence'  => (int) $post["sequence"][$key]
                        ];
                    }
                }

                usort($sorted_data, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
            }

            $sheetData["column_ids"] = implode(",", array_column($sorted_data, 'column_id'));
            $sheetData["sequence"]   = json_encode($sorted_data);

            // Insert or Update
            if ($sheetId > 0) {
                $success = $this->excel_model->updateSheet($sheetId, $sheetData);
                $response = [
                    'status'  => $success ? 'RCS' : 'ERR',
                    'message' => $success ? 'Sheet updated successfully.' : 'Failed to update sheet.'
                ];
            } else {
                $insertId = $this->excel_model->insertSheet($sheetData);
                $response = [
                    'status'  => $insertId ? 'RCS' : 'ERR',
                    'message' => $insertId ? 'Sheet created successfully.' : 'Failed to create sheet.',
                    'sheetid' => $insertId
                ];
            }

            echo json_encode($response);
            exit;
        }

        // Render page (non-AJAX)
        $data['title'] = "Excel Create";
        $data["tbl_excel_columns"] = $this->db
            ->where('excel_id', 1)
            ->order_by('sequence', 'ASC')
            ->get(db_prefix() . 'excel_column_update')
            ->result_array();

        $data["excelInfo"] = $this->db
            ->where('id', $id)
            ->get(db_prefix() . 'excel_data_update')
            ->row();

        $this->load->view('admin/excel/create', $data);
    }
    
     public function create_new($id = "")
    {
        // Handle AJAX form submission
        if ($this->input->is_ajax_request()) {
            
            echo "<pre>";
            // print_r($_POST);
            // die;

            $post = $this->input->post(NULL, true); // XSS Filtering enabled

            $sheetId     = (int) ($post['sheetid'] ?? 0);
            $excel_type  = (int) ($post['excel_type'] ?? 0);

            $fromDate    = (!empty($post['fromDate']) && $post['fromDate'] !== '0000-00-00') ? $post['fromDate'] : null;
            $toDate      = (!empty($post['toDate']) && $post['toDate'] !== '0000-00-00') ? $post['toDate'] : null;

            $sheetData = [
                'spreadsheetId'             => $post['spreadsheetId'] ?? '',
                'fromDate'                  => $fromDate,
                'toDate'                    => $toDate,
                'acadmic_year'              => $post['acadmic_year'] ?? '',
                'sheet_name'                => $post['sheet_name'] ?? '',
                'sql_condition'             => $post['sql_condition'] ?? '',
                'type'                      => $post['type'] ?? '',
                'orignal_documents_status'  => !empty($post['orignal_documents_status']) ? 1 : 0,
                'apostile_documents_status' => !empty($post['apostile_documents_status']) ? 1 : 0,
                'status'                    => 1,
                'autoSync'                  => 1,
                'created_by'                => get_staff_user_id(),
                'created_at'                => date('Y-m-d H:i:s'),
                'excel_type'                => $excel_type,
            ];

            // Sort column IDs by sequence
            $sorted_data = [];
            if (!empty($post["column_ids"]) && !empty($post["sequence"])) {
                foreach ($post["column_ids"] as $key => $column_id) {
                    if (isset($post["sequence"][$key])) {
                        $sorted_data[] = [
                            'column_id' => $column_id,
                            'sequence'  => (int) $post["sequence"][$key]
                        ];
                    }
                }

                usort($sorted_data, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
            }
            
            $org_sorted_data=[];
             if (!empty($post["org_sequence"])) {
                foreach ($post["org_sequence"] as $key => $column_id) {

                    if (!empty($column_id)) {
                        $org_sorted_data[] = [
                            'column_id' => $key,
                            'sequence'  => (int) $column_id
                        ];
                    }
                }

                usort($org_sorted_data, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
            }
              $upd_sorted_data=[];
             if (!empty($post["upd_sequence"])) {
                foreach ($post["upd_sequence"] as $key => $column_id) {

                    if (!empty($column_id)) {
                        $upd_sorted_data[] = [
                            'column_id' => $key,
                            'sequence'  => (int) $column_id
                        ];
                    }
                }

                usort($upd_sorted_data, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
            }
              $ap_sorted_data=[];
             if (!empty($post["ap_sequence"])) {
                foreach ($post["ap_sequence"] as $key => $column_id) {

                    if (!empty($column_id)) {
                        $ap_sorted_data[] = [
                            'column_id' => $key,
                            'sequence'  => (int) $column_id
                        ];
                    }
                }

                usort($ap_sorted_data, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
            }
              $visa_sorted_data=[];
             if (!empty($post["visa_sequence"])) {
                foreach ($post["visa_sequence"] as $key => $column_id) {

                    if (!empty($column_id)) {
                        $visa_sorted_data[] = [
                            'column_id' => $key,
                            'sequence'  => (int) $column_id
                        ];
                    }
                }

                usort($visa_sorted_data, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
            }
            
         

            $sheetData["column_ids"] = implode(",", array_column($sorted_data, 'column_id'));
            $sheetData["sequence"]   = json_encode($sorted_data);

            
            $sheetData["org"]   = json_encode($org_sorted_data);
            $sheetData["upd"]   = json_encode($upd_sorted_data);
            $sheetData["ap"]   = json_encode($ap_sorted_data);
            $sheetData["vap"]   = json_encode($visa_sorted_data);

            // Insert or Update
            if ($sheetId > 0) {
                $success = $this->excel_model->updateSheet($sheetId, $sheetData);
                $response = [
                    'status'  => $success ? 'RCS' : 'ERR',
                    'message' => $success ? 'Sheet updated successfully.' : 'Failed to update sheet.'
                ];
            } else {
                $insertId = $this->excel_model->insertSheet($sheetData);
                $response = [
                    'status'  => $insertId ? 'RCS' : 'ERR',
                    'message' => $insertId ? 'Sheet created successfully.' : 'Failed to create sheet.',
                    'sheetid' => $insertId
                ];
            }

            echo json_encode($response);
            exit;
        }

        // Render page (non-AJAX)
        $data['title'] = "Excel Create";
        $data["tbl_excel_columns"] = $this->db
            ->where('excel_id', 1)
            ->order_by('sequence', 'ASC')
            ->get(db_prefix() . 'excel_column_update')
            ->result_array();

        $data["excelInfo"] = $this->db
            ->where('id', $id)
            ->get(db_prefix() . 'excel_data_update')
            ->row();

        $this->load->view('admin/excel/create_new', $data);
    }
    
}
