<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fees extends AdminController
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('fees_model');
    }

    function company($id = "")
    {

        if (!has_permission('customers', '', 'view')) {
            return ajax_access_denied(); // Use return to stop further execution
        }


        $data['id']     = $id ?? '';
        $data['segment']     = $this->fees_model->segment();
        $data['countries']     = $this->fees_model->countries();
        $data['universities']     = $this->fees_model->universities();

        // Always get all
        $allFees = $this->fees_model->getFeesStructure();
        $data["feesStructure"] = [];
        $data["feesStructure_data"] = $allFees;
        $allRegions = $this->fees_model->getRegions();
        $data["regions"] = array_column($allRegions, null, 'id');

        if (!empty($id)) {
            $data["feesStructure"] = $this->fees_model->getFeesStructure($id);
        }


        // Determine correct view page
        $view_page = 'admin/fees/company'; // You can switch based on type if needed

        // Load view
        $this->load->view($view_page, $data);
    }

    function saveData()
    {

        $id     = $this->input->post('id');
        $segment_id     = $this->input->post('segment_id');
        $country_id     = $this->input->post('country_id');
        $university_id  = $this->input->post('university_id');
        $region_id  = $this->input->post('region_id');
        $setDefault  = $this->input->post('setDefault');

        unset($_POST["setDefault"]);


        if (!$segment_id || !$country_id || !$university_id || !$region_id) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Required fields missing.',
                'status' => false,
                'message' => 'Required fields missing.'
            ]);
            return;
        }
        $_POST["university_info"]["banner_image"] = saveBase64Image($_POST["university_info"]["banner_image"], "uploads/pdf/feesStructure/");
        $_POST["university_info"]["logo"] = saveBase64Image($_POST["university_info"]["logo"], "uploads/pdf/feesStructure/");
        $_POST["university_info"]["university_logo"] = saveBase64Image($_POST["university_info"]["university_logo"], "uploads/pdf/feesStructure/");


        $data = [
            "segment_id"      => $segment_id,
            "country_id"      => $country_id,
            "university_id"   => $university_id,
            "region_id"   => $region_id,
            "segment_name"      => $_POST["university_info"]["segment_type"] ?? '',
            "country_name"      => $_POST["university_info"]["country_name"] ?? '',
            "university_name"   => $_POST["university_info"]["university_name"] ?? '',
            "region_name"   => $_POST["university_info"]["region_name"] ?? '',
            "university_data" => json_encode($this->input->post('university_info')),
            "section_data"    => json_encode($this->input->post('sections')),
            "contact_data"    => json_encode($this->input->post('contact')),

        ];



        // Check if record exists
        $exists = $this->fees_model->checkRecord($segment_id, $country_id, $university_id);

        if ($exists) {

            if (!empty($country_id) && $this->input->post('sections')) {
                // Update database
                $this->s_db->where('id', $country_id);
                $updated = $this->s_db->update('countries', [
                    'fees_structure' => json_encode($this->input->post('sections'), true)
                ]);
            }

            if (empty($_POST["id"])) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Record already exists for this selection.',
                    'status' => false,
                    'message' => 'Record already exists for this selection.'
                ]);
                return;
            } else {

                $data["updated_by"]      = get_staff_user_id();
                $data["updated_at"]      = date('Y-m-d H:i:s');
                // UPDATE
                $data["updated_at"] = date('Y-m-d H:i:s');

                $this->fees_model->updateRecord($id, $data);


                echo json_encode([
                    'resp_code' => 'RCS',
                    'resp_desc' => 'Data updated successfully.',
                    'status' => true,
                    'message' => 'Data updated successfully.',
                    "id" => $_POST["id"]
                ]);
            }
        } else {

            if (!empty($setDefault) && $setDefault == 1 && !empty($country_id) && $this->input->post('sections')) {
                // Update database
                $this->s_db->where('id', $country_id);
                $updated = $this->s_db->update('countries', [
                    'fees_structure' => json_encode($this->input->post('sections'), true)
                ]);
            }


            $data["created_by"]      = get_staff_user_id();
            $data["created_at"]      = date('Y-m-d H:i:s');
            // INSERT
            $this->fees_model->insertRecord($data);
            $insertId = $this->db->insert_id();
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Data inserted successfully.',
                'status' => true,
                'message' => 'Data inserted successfully.',
                "id" => $insertId
            ]);
        }
    }

    public function generate($id = "")
    {
        if (empty($id)) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid ID provided.'
            ]);
            return;
        }

        $data = $this->fees_model->getFeesStructure($id);
        $data["locations"] = $this->fees_model->officeLocations();

        $this->load->view('admin/fees/fees_pdf', $data);
    }

    public function savePdf()
    {
        // Load helper
        $this->load->helper('file');

        // Check if a file is uploaded
        if (!empty($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
            $file = $_FILES['pdf_file'];

            // Clean filename


            // Get POST data
            $countryName = $this->input->post('country_name');
            $segmentType = $this->input->post('segment_type');
            $university_name = $this->input->post('university_name');
            $region_name = $this->input->post('region_name');
            $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file['name']);
            // Define upload directory with subfolders
            $uploadDir = FCPATH . str_replace(" ", "_", "uploads/knowledge_base/fees_structures/" . $countryName . "/" . $region_name . "/" . $university_name . "/");

            // Create directories if they don't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Full path to move the file
            $targetPath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {

                // Relative path for DB (use forward slashes)
                $relativePath = "uploads/knowledge_base/fees_structures/" . $countryName . "/" . $region_name . "/" . $university_name . "/" . $filename;
                $relativePath = base_url() . str_replace(" ", "_", $relativePath);
                // Call knowledge_base function
                knowledge_base_from_path($relativePath, "Fees Structure/" . $segmentType);

                echo json_encode([
                    'status' => true,
                    'message' => 'File uploaded and added to knowledge base successfully!',
                    'path' => $relativePath
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to move uploaded file. Check folder permissions.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'No file uploaded or upload error.'
            ]);
        }
    }
}
