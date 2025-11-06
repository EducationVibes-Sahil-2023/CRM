<?php

defined('BASEPATH') or exit('No direct script access allowed');

class hostel_management extends AdminController
{
    // Define the property

    function __construct()
    {


        //         ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        parent::__construct();
        $this->load->model('Hostel_model'); // Load the model
        $this->load->model('quotation_model');
    }

    function manage($page_type = '')
    {


        // ✅ Permission check
        if (!has_permission('hostel_management', '', 'view_own') && !has_permission('hostel_management', '', 'view')) {
            return access_denied('hostel_management'); // Use return to stop further execution
        }

        $data = [];
        $data["hostel"] = $this->db->query("SELECT id,name from tblhostel where status =1  ")->result_array();
        $data["hostel_company"] = $this->db->query("SELECT id,name from tblhostel_company where status =1  ")->result_array();
        $view_page = 'manage'; // Default view page
        if (!empty($page_type)) {
            $view_page = strtolower($page_type);
            // $data["dropdown_country_university_selection"] = $this->s_db->query("SELECT co.name,c.country_name,u.university_name,c.id country_id,u.id university_id FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') where co.id = 7  ")->result_array();
        }

        $data["universities"] = array_column($this->s_db->query("SELECT co.name,c.country_name,u.university_name,c.id country_id,u.id university_id FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') where co.id = 7  ")->result_array(), null, 'university_id');
        // $data["dropdown_country_university_selection"] = $this->s_db->query("SELECT co.name,c.country_name,u.university_name,c.id country_id,u.id university_id FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') where co.id = 7  ")->result_array();
        $this->load->view('admin/hostel_management/' . $view_page . '_manage', $data);
    }

    function table($table_type = '')
    {

        // ✅ Permission check
        if (!has_permission('hostel_management', '', 'view_own') && !has_permission('hostel_management', '', 'view')) {
            return access_denied('hostel_management'); // Use return to stop further execution
        }

        // ✅ Correct table view (filename from views/admin/tables/)
        $view = 'hostel'; // corresponds to application/views/admin/tables/visa_clients.php

        if (!empty($table_type)) {
            $view = $table_type . '_table'; // corresponds to application/views/admin/tables/visa_clients.php
        }
        // ✅ Call DataTable loader
        return $this->app->get_table_data($view);
    }

    function hostel_information()
    {

        // ✅ Permission check
        if (!has_permission('hostel', '', 'view_own') && !has_permission('hostel', '', 'view')) {
            return access_denied('hostel'); // Use return to stop further execution
        }

        // ✅ Correct table view (filename from views/admin/tables/)
        $view = 'hostel_information'; // corresponds to application/views/admin/tables/visa_clients.php

        // ✅ Call DataTable loader
        return $this->app->get_table_data($view);
    }

    function create($id)
    {

        // ✅ Permission check
        if (!has_permission('hostel_management', '', 'create')) {
            return access_denied('hostel_management'); // Stop execution immediately
        }

        if (!empty($id) && !has_permission('hostel_management', '', 'edit')) {
            return access_denied('hostel_management'); // Stop execution immediately
        }

        // ✅ Prepare any required data (if needed in view)
        $data = [];
        $data["id"] = $id;
        if (!empty($id)) {
            $data["visaData"] = $this->db->where('id', $id)->get(db_prefix() . 'hostel')->row();
        }
        // ✅ Set correct view page
        $view_page = 'admin/hostel_management/create'; // Example path for view file

        // ✅ Load view safely
        $this->load->view($view_page, $data);
    }



    function hostel($segment = "", $id = "")
    {
        // ✅ Permission check
      if (
    !has_permission('hostel_management', '', 'view')
    && !has_permission('hostel_management', '', 'view_own')
    && !has_permission('hostel_management', '', 'edit')
) {
    return access_denied('hostel_management');
}


        if (!$id) {
            redirect(admin_url('hostel_management'));
        }

        $data = [];

        if (!empty($_GET['tab'])) {
            $data['active_tab'] = $_GET['tab'];
        } else {
            $data['active_tab'] = 'profile';
        }
        $data["view_page"] = 'profile'; // Example path for view file



        $data["getId"] = $id;
        $data['hostelData'] = $this->db->select('*')->where('id', $id)->get(db_prefix() . 'hostel_infomation')->row();

        $data["universities"] = array_column($this->s_db->query("SELECT co.name,c.country_name,u.university_name,c.id country_id,u.id university_id FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') where co.id = 7  ")->result_array(), null, 'university_id');

        $data["hostelRentelData"] = array_column($this->Hostel_model->get_hostel_rentInfo(), null, "hostel_id");

        if ($_GET['tab'] == 'profile') {

            if (!has_permission('hostel_management', '', 'view_own') && !has_permission('hostel_management', '', 'view')) {
                return access_denied('hostel_management'); // Stop execution immediately
            }
            if (empty($data['hostelData'])) {
                redirect(admin_url('hostel_management'));
            }
            $data["view_page"] = 'profile';
        } else if ($_GET['tab'] == 'quotation') {

            if (!has_permission('hostel_management', '', 'quotation')) {
                return access_denied('hostel_management'); // Stop execution immediately
            }

            $data["view_page"] = 'quotation';
            $data["quotation_id"] = $_GET["quotation_id"] ?? '';
        } else if ($_GET['tab'] == 'payment') {

            if (!has_permission('hostel_management', '', 'payment')) {
                return access_denied('hostel_management'); // Stop execution immediately
            }

            $data["view_page"] = 'payment';
            $data["quotation_id"] = $_GET["quotation_id"] ?? '';
        }
        $data["active_segment"] = "";
        if (!empty($segment)) {
            $data["view_page"] = $segment . "_" . $data['active_tab'];
            $data["active_segment"] = $segment;
        }
        $this->load->view('admin/hostel_management/group', $data);
    }

    public function quotation()
    {

        $quotationSave = [
            "hostel_info_id" => $_POST["hostel_info_id"] ?? "",
            "university_name" => $_POST["university_name"] ?? "",
            "start_date" => $_POST["start_date"] ?? "",
            "end_date" => $_POST["end_date"] ?? "",
            "room_capacity" => $_POST["room_capacity"] ?? 0,
            "floor_No" => $_POST["floor_No"] ?? 0,
            "room_No" => $_POST["room_No"] ?? 0,
            "company" => $_POST["company"] ?? "",
            "rent" => $_POST["rent"] ?? "",
            "currency" => $_POST["rent_currency_type"] ?? "",
            "hostel" => $_POST["hostel"] ?? "",
            "exchange_value" => ($_POST["currency_exchange"]) ?? "",
            'hostel_due'  => ($_POST["university_dues"]) ?? "",
            'company_due'     => ($_POST["company_due"]) ?? "",
            'release_to_counsellor'     => ($_POST["release_to_counsellor"]) ?? "",
            'year'     => ($_POST["year"]) ?? 0,
            'acadmic_year'     => ($_POST["acadmic_year"]) ?? '',
            'services'     => ($_POST["services"]) ?? ''

        ];



        try {

            $quotation_id = $_POST["quotation_id"] ?? null;

            // 🔹 Optional: Check if record already exists
              $checkData = [
            'hostel_info_id' => $_POST["hostel_info_id"] ?? null,
            'hostel'         => $_POST["hostel"] ?? null,
            'company'        => $_POST["company"] ?? null,
            'start_date'     => $_POST["start_date"] ?? null,
            'end_date'       => $_POST["end_date"] ?? null,
            'acadmic_year'   => $_POST["acadmic_year"] ?? null,
            'year'           => $_POST["year"] ?? null,
        ];

        // ✅ Remove empty null or ""
        $checkData = array_filter($checkData, function($v) {
            return $v !== null && $v !== "" && $v !== "0";
        });

        // ✅ Check duplicate only with NON-EMPTY values
        if (!empty($checkData)) {
            $this->db->where($checkData);

            if (!empty($quotation_id)) {
                $this->db->where('id !=', $quotation_id); // ignore same record on update
            }

            $duplicate = $this->db->get(db_prefix() . 'hostel_quotation')->row();

            if ($duplicate) {
                echo json_encode([
                    'resp_code' => 'DUP',
                    'resp_desc' => 'Duplicate entry exists for selected Hostel, Company, Date or Academic Year.'
                ]);
                return;
            }
        }

            if (!empty($quotation_id)) {
                // 🔸 Update existing record
                $quotationSave['updated_date'] = date('Y-m-d H:i:s');
                $quotationSave['updated_by'] = get_staff_user_id();

                $this->db->where('id', $quotation_id);
                $updated = $this->db->update(db_prefix() . 'hostel_quotation', $quotationSave);

                if ($updated) {
                    // ✅ Update success
                    $response = [
                        'resp_code' => 'RCS',
                        'resp_desc' => 'Quotation updated successfully.'
                    ];
                } else {
                    // ❌ Update failed
                    $db_error = $this->db->error();
                    $response = [
                        'resp_code' => 'ERR',
                        'resp_desc' => 'Failed to update quotation: ' . $db_error['message']
                    ];
                }
            } else {
                // 🔸 Insert new record
                $quotationSave['created_date'] = date('Y-m-d H:i:s');
                $quotationSave['created_by'] = get_staff_user_id();

                $inserted = $this->db->insert(db_prefix() . 'hostel_quotation', $quotationSave);

                if ($inserted) {
                    // ✅ Insert success
                    $response = [
                        'resp_code' => 'RCS',
                        'resp_desc' => 'Quotation added successfully.',
                        'insert_id' => $this->db->insert_id()
                    ];
                } else {
                    // ❌ Insert failed
                    $db_error = $this->db->error();
                    $response = [
                        'resp_code' => 'ERR',
                        'resp_desc' => 'Failed to add quotation: ' . $db_error['message']
                    ];
                }
            }

            echo json_encode($response);
        } catch (Exception $e) {
            // ❌ Error Response
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage()
            ]);
        }
    }

    public function quotationDelete()
    {
        $quotation_id = $_POST["quotation_id"] ?? null;

        if (!$quotation_id) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Invalid Quotation ID.'
            ]);
            return;
        }

        $this->db->where('id', $quotation_id);
        $this->db->update(db_prefix() . 'hostel_quotation', ["status" => "0"]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Quotation deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Error deleting quotation or quotation not found.'
            ]);
        }
    }

    public function quotation_table($page_type = "", $hostelInfo_Id)
    {
        $view = "hostel_quotation_released";

        if (!empty($page_type)) {
            $view = "hostel_quotation_" . $page_type . '_table'; // corresponds to application/views/admin/tables/visa_clients.php
        }
        // Load the corresponding table data
        $this->app->get_table_data($view, ["hostel_info_id" => $hostelInfo_Id]);
    }


    function rental($page_type = '')
    {
        $data = [];
        // $data["dropdown_country_university_selection"] = $this->s_db->query("SELECT co.name,c.country_name,u.university_name,c.id country_id,u.id university_id FROM course co left join countries c ON (co.id = c.segment_id) left join universities u on (u.country_id = c.id and u.status ='0') where co.id = 7  ")->result_array();
        $data["hostelData"] = $this->db->query("SELECT id,name from tblhostel where status =1  ")->result_array();

        if (!empty($page_type)) {
            $data['active_tab'] = $page_type;
        } else {
            $data['active_tab'] = 'rental';
        }
        $this->load->view('admin/hostel_management/rental_' . $page_type, $data);
    }

    function rental_table($table_type = '')
    {
        // ✅ Permission check
        if (!has_permission('hostel_management', '', 'backend')) {
            return access_denied('hostel_management'); // Use return to stop further execution
        }

        // ✅ Correct table view (filename from views/admin/tables/)
        $view = 'hostel_rental'; // corresponds to application/views/admin/tables/visa_clients.php

        if (!empty($table_type)) {
            $view = "hostel_" . $table_type . '_table'; // corresponds to application/views/admin/tables/visa_clients.php
        }
        // ✅ Call DataTable loader
        return $this->app->get_table_data($view);
    }

    function save_rental_details()
    {
        try {
            if (!has_permission('hostel_management', '', 'backend')) {
                return access_denied('hostel_management'); // Stop execution immediately
            }

            if (!empty($data['id']) && !has_permission('hostel_management', '', 'backend_edit')) {
                return access_denied('hostel_management'); // Stop execution immediately
            }
            $data = $this->input->post();



            // Prepare data array
            $save_data = [
                'hostel_id' => $data['hostel_id'],
                // 'university_name' => $data['university_name'],
                'room_capacity' => $data['room_capacity'] ?? 0,
                'rent' => $data['rent'] ?? 0,
                'currency' => $data['currency'] ?? 0,
                'rental_details' => $data['rental_details'] ?? '{}',
                'acadmic_year' => $data['acadmic_year'] ?? "",
                'year' => $data['year'] ?? 0,
            ];

            $check_duplicate = [];
            $check_duplicate['hostel_id'] = $data['hostel_id'];
            $check_duplicate['room_capacity'] = $data['room_capacity'];

            // check duplicate entry
            $this->db->where($check_duplicate)->where('status', 1)->where('id!=', $data['rental_id'] ?? 0);
            $this->db->from(db_prefix() . 'hostel_rental');
            $count = $this->db->count_all_results();

            if ($count > 0) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Duplicate entry found for the same university and room capacity.'
                ]);
                die;
            }


            if (!empty($data['rental_id'])) {
                $save_data['updated_date'] = date('Y-m-d H:i:s');
                $save_data['updated_by'] = get_staff_user_id();
                // Update existing record
                $this->db->where('id', $data['rental_id']);
                $this->db->update(db_prefix() . 'hostel_rental', $save_data);
                $record_id = $data['id'];
            } else {
                $save_data['created_date'] = date('Y-m-d H:i:s');
                $save_data['created_by'] = get_staff_user_id();
                // Insert new record
                $this->db->insert(db_prefix() . 'hostel_rental', $save_data);
                $record_id = $this->db->insert_id();
            }

            // Return structured response
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Hostel rental details saved successfully.',

            ]);
        } catch (Exception $e) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage()
            ]);
        }
    }

    function delete_rental($id)
    {
        // ✅ Permission check
        if (!has_permission('hostel_management', '', 'backend_delete')) {
            return access_denied('hostel_management'); // Stop execution immediately
        }

        if (!$id) {
            redirect(admin_url('hostel_management'));
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'hostel_rental', ["status" => "0"]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Hostel rental record deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Error deleting hostel rental record or record not found.'
            ]);
        }
    }

    function save_hostel_details()
    {

        try {
            if (!has_permission('hostel_management', '', 'create')) {
                return access_denied('hostel_management'); // Stop execution
            }

            $data = $this->input->post();

            // If updating, check edit permission
            if (!empty($data['hostel_management_id']) && !has_permission('hostel_management', '', 'edit')) {
                return access_denied('hostel_management');
            }

            // --- Check for duplicate passport ---
            $this->db->where('passport', $data['passport'] ?? '');
            $this->db->where('status', 1);
            if (!empty($data['hostel_management_id'])) {
                // Exclude current record when updating
                $this->db->where('id !=', $data['hostel_management_id']);
            }
            $existing = $this->db->get(db_prefix() . 'hostel_infomation')->row();
            if ($existing) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Passport number already exists.'
                ]);
                return; // Stop execution
            }

            // --- Prepare data array ---
            $save_data = [
                'name' => $data['student_name'] ?? '',
                'university_id' => $data['university_id'] ?? '',
                'passport' => $data['passport'] ?? '',
                'university_name' => $data['university_name'] ?? '',
                'floor_no' => $data['floor_No'] ?? '',
                'room_no' => $data['room_No'] ?? '',
                'company' => $data['company'] ?? '',
                'hostel' => $data['hostel'] ?? '',
                'room_capacity' => $data['room_capacity'] ?? '',
                'rent_amount' => $data['rent'] ?? '',
                'currency' => $data['rent_currency_type'] ?? '',
                'start_date' => $data['startdate'] ?? '',
                'end_date' => $data['enddate'] ?? '',
                'acadmic_year' => $data['acadmic_year'] ?? '',
            ];

            if (!empty($data['hostel_management_id'])) {
                // Update existing record
                $save_data['updated_date'] = date('Y-m-d H:i:s');
                $save_data['updated_by'] = get_staff_user_id();
                $this->db->where('id', $data['hostel_management_id']);
                $this->db->update(db_prefix() . 'hostel_infomation', $save_data);
                $record_id = $data['hostel_management_id'];
            } else {
                // Insert new record
                $save_data['created_date'] = date('Y-m-d H:i:s');
                $save_data['created_by'] = get_staff_user_id();
                $this->db->insert(db_prefix() . 'hostel_infomation', $save_data);
                $record_id = $this->db->insert_id();
            }


            // Return success response
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Hostel details saved successfully.',
                'record_id' => $record_id
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage()
            ]);
        }
    }



    function delete($id)
    {
        // ✅ Permission check
        if (!has_permission('hostel_management', '', 'delete')) {
            return access_denied('hostel_management'); // Stop execution immediately
        }

        if (!$id) {
            redirect(admin_url('hostel_management'));
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'hostel_infomation', ["status" => "0"]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Hostel record deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Error deleting hostel record or record not found.'
            ]);
        }
    }


    function quotationGenerate()
    {
        if (!has_permission('hostel_management', '', 'hostel_invoice_generate')) {
            return access_denied('hostel_management'); // Stop execution if no permission
        }

        $hostelInfo_Id = $_POST["hostel_info_id"] ?? 1;
        $quotation_id = $_POST["quotation_id"] ?? 1;

        if (!$quotation_id) {
            redirect(admin_url('hostel_management'));
        }

        $this->db->select([
            'h.*',
            'hq.id',
            'hq.university_name',
            'hq.room_no',
            'hq.floor_no',
            'hq.company',
            'hq.hostel',
            'hq.room_capacity',
            'hq.start_date',
            'hq.end_date',
            'hq.exchange_value',
            'hq.hostel_due',
            'hq.created_date',
            'hi.name',
            'hi.passport',
            'hq.company',
            'hq.hostel',

            // ✅ Simplified service_name using GROUP_CONCAT
            "(CASE 
        WHEN COUNT(DISTINCT hs.name) = 1 THEN MAX(hs.name)
        WHEN COUNT(DISTINCT hs.name) = 2 THEN REPLACE(GROUP_CONCAT(DISTINCT hs.name ORDER BY hs.name SEPARATOR ' & '), ',', ' & ')
        ELSE CONCAT(
            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT hs.name ORDER BY hs.name SEPARATOR ', '), ', ', COUNT(DISTINCT hs.name) - 1),
            ' & ',
            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT hs.name ORDER BY hs.name SEPARATOR ', '), ', ', -1)
        )
    END) AS service_name",

            // ✅ Month difference
            'TIMESTAMPDIFF(MONTH, hq.start_date, hq.end_date) 
        + (DAY(hq.end_date) >= DAY(hq.start_date)) AS month_difference'
        ])
            ->from(db_prefix() . 'hostel_quotation AS hq')
            ->join(db_prefix() . 'hostel_infomation AS hi', 'hi.id = hq.hostel_info_id', 'left')
            ->join(db_prefix() . 'hostel AS h', 'h.id = hq.hostel', 'left')
            ->join(db_prefix() . 'hostel_quotation_services AS hs', 'FIND_IN_SET(hs.id, hq.services) > 0', 'left')
            ->where('hq.hostel_info_id', $hostelInfo_Id)
            ->where('hq.id', $quotation_id)
            ->group_by('hq.id'); // ✅ Add group_by to make GROUP_CONCAT work properly





        $data['hostelData'] = $this->db->get()->row();


        // echo "<pre>";
        // print_r($data['hostelData']);
        // echo "</pre>";
        // die;


        // Disable SSL verification (for images/fonts)
        stream_context_set_default(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

        // Initialize TCPDF
        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Education Vibes');
        $pdf->SetTitle('Hostel Invoice');
        $pdf->SetSubject('Hostel Invoice');


        // Disable default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // This won't work anymore if you decide to add a watermark
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 006', PDF_HEADER_STRING);
        // ✅ Force small margins to fit more on one page
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(false, 0); // ✅ Disable automatic page breaks completely

        // Add single page
        $pdf->AddPage();

        $stampPath = FCPATH . $data['hostelData']->hostel_stamp;

        // Check if image exists
        if (file_exists($stampPath)) {

            // X and Y coordinates in mm
            $x = 130; // distance from left
            $y = 230;  // distance from top

            // Width of image in mm (height auto-scaled)
            $width = 40;

            // Place the stamp at absolute position
            $pdf->Image($stampPath, $x, $y, $width, 0, '', '', '', false, 300);
        }

        // Optional: Custom fonts
        $path_gill_sans_mt = APPPATH . 'libraries/tcpdf/fonts/GILB____.ttf';
        $path_book_antiqua = APPPATH . 'libraries/tcpdf/fonts/book-antiqua-bold.ttf';
        $path_Cambria_Math = APPPATH . 'libraries/tcpdf/fonts/Cambria Math.ttf';
        $path_Cambria = APPPATH . 'libraries/tcpdf/fonts/Cambria/Cambria Bold 700.ttf';

        $data["gillsansmt"]   = TCPDF_FONTS::addTTFfont($path_gill_sans_mt, 'TrueTypeUnicode', '', 15);
        $data["book_antiqua"] = TCPDF_FONTS::addTTFfont($path_book_antiqua, 'TrueTypeUnicode', '', 15);
        $data["Cambria_Math"] = TCPDF_FONTS::addTTFfont($path_Cambria_Math, 'TrueTypeUnicode', '', 15);
        $data["Cambria"]      = TCPDF_FONTS::addTTFfont($path_Cambria, 'TrueTypeUnicode', '', 15);

        $pdf->setImageScale(1.7);


        // Load the HTML view
        $html = $this->load->view('admin/pdf/hostel_invoice', $data, true);

        // ✅ Make sure the HTML fits in one page
        // Shrink content slightly if it’s long (use CSS or scale below)
        // $pdf->writeHTMLCell(
        //     0,        // width
        //     0,        // height
        //     '',       // x
        //     '',       // y
        //     $html,    // html
        //     0,        // border
        //     1,        // line break
        //     0,        // fill
        //     true,     // reset height
        //     // 'C',      // align
        //     true      // autopadding
        // );
        $pdf->writeHTML($html, true, false, true, false, '');

        // ✅ Output PDF to browser (single page)
        // $pdf->Output('hostel_invoice_' . $data['hostelData']->id . '.pdf', 'I');

        // die;
        $upload_dir = FCPATH . APPLICANT_UPLOAD_DOCUMENT_PATH . $hostelInfo_Id . "/Hostel-Quotation/";

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true) && !is_dir($upload_dir)) {
                echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
                return;
            }
        }

        // $file_name = 'Quotation_' . time() . '.pdf';

        $file_name = $data["hostelData"]->name . " " .
            $data["hostelData"]->university_name . " " .
            $data["hostelData"]->start_date . " " .
            $data["hostelData"]->end_date . " " .
            $data["hostelData"]->room_capacity . " " .
            time() . '.pdf';

        $file_name = strtolower(str_replace(" ", "_", $file_name));


        $file_path = $upload_dir . $file_name;

        // Remove if exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Save file
        $pdf->Output($file_path, 'F');

        if (!file_exists($file_path)) {
            echo json_encode(["status" => "error", "message" => "Failed to generate PDF file."]);
            return;
        }

        // -----------------------------
        // Update quotation record (not clients!)

        // -----------------------------
        $update_data = ["pdf" =>  base_url() . APPLICANT_UPLOAD_DOCUMENT_PATH . $hostelInfo_Id . "/Hostel-Quotation/" . $file_name];
        $this->db->where(["hostel_info_id" => $hostelInfo_Id, "id" => $quotation_id]);
        $this->db->update(db_prefix() . 'hostel_quotation', $update_data);


        // -----------------------------
        // Return response
        // -----------------------------
        echo json_encode([
            "status"   => "success",
            "pdf_url"  => base_url(APPLICANT_UPLOAD_DOCUMENT_PATH . $hostelInfo_Id . "/Hostel-Quotation/" . $file_name)
        ]);
    }

    public function payment_table($table_type = "", $hostel_info_id)
    {
        if (!has_permission('hostel_management', '', 'payment')) {
            throw new Exception("Access denied: Quotation Payment View");
        }
        $view = "hostel_payments";

        if (!empty($table_type)) {
            $view = $table_type . '_hostel_payment_table'; // corresponds to application/views/admin/tables/visa_clients.php
        }

        // Load the corresponding table data
        $this->app->get_table_data($view, ["hostel_info_id" => $hostel_info_id]);
    }
    public function payment_quotation()
    {


        try {
            $payment_id         = $this->input->post("payment_id") ?? '';
            $acadmic_year         = $this->input->post("acadmic_year") ?? '';
            $year         = $this->input->post("year") ?? '';
            $hostel_info_id          = $this->input->post("hostel_info_id") ?? '';
            $university_name    = $this->input->post("university_name") ?? '';
            $start_date       = $this->input->post("start_date") ?? '';
            $end_date       = $this->input->post("end_date") ?? '';
            $room_capacity         = $this->input->post("room_capacity") ?? '';
            $currency_exchange  = $this->input->post("currency_exchange") ?? '';
            $ex_currency  = $this->input->post("ex_currency") ?? '';
            $location_id  = $this->input->post("location_id") ?? '';
            $tt_copy  = $this->input->post("tt_copy") ?? 0;
            $inr_value  = $this->input->post("inr_value") ?? 0;
            $currency_disabled  = $this->input->post("currency_disabled") ?? 0;
            $quotation_id  = $this->input->post("quotation_id") ?? 0;
            $total_inr_amount  = $this->input->post("total_inr_amount") ?? 0;
            $payment_quotations = $this->input->post("payment_quotations")
                ? json_decode($this->input->post("payment_quotations"), true)
                : [];

            if (empty($payment_quotations)) {
                throw new Exception("No payment quotations provided.");
            }

            // 🔒 Permission checks
            if (!empty($payment_id) && !has_permission('hostel_management', '', 'payment')) {
                throw new Exception("Access denied: Quotation Payment Edit");
            }
            if (empty($payment_id) && !has_permission('hostel_management', '', 'payment')) {
                throw new Exception("Access denied: Quotation Payment Create");
            }

            $seenEntries = [];
            $insertRows  = [];
            $updateRows  = [];
            $activity_data = [];
            foreach ($payment_quotations as $key => $payment) {
                // $row = [
                //     "hostel_info_id"       => $hostel_info_id,
                //     "university_name" => $university_name,
                //     "academic_year"   => $acadmic_year,
                //     "year"            => $study_year,
                //     "ex_currency"            => $ex_currency,
                //     "exchange_value"  => $currency_exchange,
                //     "mode"            => $payment['mode'] ?? '',
                //     "transaction_type"            => $payment['transaction_type'] ?? '',
                //     "amount"          => isset($payment['amount']) ? str_replace(',', '', $payment['amount']) : 0,
                //     "pay_date"        => $payment['pay_date'] ?? null,
                //     "payment_type"        => $payment['payment_type'] ?? "",
                //     "inr_value"        => $inr_value ?? 0,
                //     "total_inr_amount"        => $total_inr_amount ?? 0,
                //     "tt_copy" => $tt_copy ?? 0,
                //     "currency_disabled" => $currency_disabled ?? 0,
                //     "quotation_id" => $quotation_id ?? 0,
                //     "location_id" => $location_id ?? 0

                // ];

                $row = [
                    "hostel_info_id"        => $hostel_info_id,
                    "acadmic_year"        => $acadmic_year,
                    "year"        => $year,
                    "university_name"  => $university_name,
                    "start_date"    => $start_date,
                    "end_date"    => $end_date,
                    "room_capacity"             => $room_capacity,
                    "ex_currency"      => $ex_currency,
                    "exchange_value"   => $currency_exchange,

                    // if mode key exists, take its value, otherwise 0
                    "mode"             => isset($payment['mode']) ? $payment['mode'] : 0,
                    "transaction_type"             => isset($payment['transaction_type']) ? $payment['transaction_type'] : 0,

                    // clean numeric string (e.g., "1,000" → 1000)
                    "amount"           => isset($payment['amount']) ? str_replace(',', '', $payment['amount']) : 0,

                    "pay_date"         => isset($payment['pay_date']) ? $payment['pay_date'] : null,
                    "payment_type"     => isset($payment['payment_type']) ? $payment['payment_type'] : 0,

                    // safe fallbacks
                    "inr_value"        => isset($inr_value) ? $inr_value : 0,
                    "total_inr_amount" => isset($total_inr_amount) ? $total_inr_amount : 0,
                    "tt_copy"          => isset($tt_copy) ? $tt_copy : 0,
                    "currency_disabled" => isset($currency_disabled) ? $currency_disabled : 0,
                    "quotation_id"     => isset($quotation_id) ? $quotation_id : 0,
                    "location_id"      => isset($location_id) ? $location_id : 0
                ];



                // Metadata
                if (!empty($payment_id)) {
                    $row["id"]         = $payment_id;
                    $row["updated_by"] = get_staff_user_id();
                    $row["updated_date"] = date('Y-m-d H:i:s');
                } else {
                    $row["created_by"]   = get_staff_user_id();
                    $row["created_date"] = date('Y-m-d H:i:s');
                }

                // Handle type (array → string)
                $row["type"] = !empty($payment["type"]) && is_array($payment["type"])
                    ? implode(",", $payment["type"])
                    : "";

                // Vendor handling
                if (is_numeric($payment["vendor_id"])) {
                    $row["vendor_id"]   = $payment["vendor_id"];
                    $row["vendor_name"] = '';
                } else {
                    $row["vendor_id"]   = 0;
                    $row["vendor_name"] = !empty($payment["vendor_id"]) ? $payment["vendor_id"] : $payment["vendor_name"];
                }


                // Split data (JSON encode)
                if (!empty($payment["split_data"])) {
                    $row["fess_infomation"] = json_encode($payment["split_data"], JSON_UNESCAPED_UNICODE);
                }

                // 🚫 Prevent duplicate in same request
                $entryKey = implode("|", [
                    $hostel_info_id,
                    $university_name,
                    $start_date,
                    $end_date,
                    $room_capacity,
                    $row['mode'],
                    $row['amount'],
                    $row['pay_date'],
                    "vendor_id:" . ($row['vendor_id'] ?? 0),
                    "vendor_name:" . ($row['vendor_name'] ?? '')
                ]);
                if (isset($seenEntries[$entryKey])) {
                    throw new Exception("Duplicate detected in current submission (Mode {$row['mode']}, Amount {$row['amount']}).");
                }
                $seenEntries[$entryKey] = true;

                // 🚫 Prevent duplicate in DB
                // $this->db->where([
                //     'hostel_info_id'      => $hostel_info_id,
                //     'university_name' => $university_name,
                //     'start_date'  => $start_date,
                //     'end_date'  => $end_date,
                //     'room_capacity'           => $room_capacity,
                //     'mode'           => $row['mode'],
                //     'amount'         => $row['amount'],
                //     'pay_date'       => $row['pay_date']
                // ]);

                $where = [
                    'hostel_info_id' => $hostel_info_id,
                    'university_name' => $university_name,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'room_capacity' => $room_capacity,
                    'mode' => $row['mode'],
                    'amount' => $row['amount'],
                    'pay_date' => $row['pay_date']
                ];

                // Filter out empty values (null, '', or 0 if you wish)
                $filtered_where = array_filter($where, function ($v) {
                    return ($v !== null && $v !== ''); // you can adjust this rule
                });

                if (!empty($filtered_where)) {
                    $this->db->where($filtered_where);
                }


                if (!empty($row["vendor_id"])) {
                    $this->db->where('vendor_id', $row["vendor_id"]);
                } else {
                    $this->db->where('vendor_name', $row["vendor_name"]);
                }
                if (!empty($row["id"])) {
                    $this->db->where('id !=', $row["id"]);
                }

                $this->db->where('status > ', 0);
                $duplicate = $this->db->get(db_prefix() . 'hostel_payments')->row();


                // if ($duplicate) {
                //     throw new Exception("Duplicate entry already exists (Mode {$row['mode']}, Amount {$row['amount']}).");
                // }

                // 📎 File upload
                if (!empty($_FILES["proof_" . $key]['name'])) {
                    $documents = $_FILES["proof_" . $key];
                    $file_name_ = ($hostel_info_id ? get_client_name($hostel_info_id) : 'proof') . "_" . time();
                    $upload_data = [
                        "name"     => $file_name_ . "." . pathinfo($documents['name'], PATHINFO_EXTENSION),
                        "type"     => $documents['type'],
                        "tmp_name" => $documents['tmp_name'],
                        "error"    => $documents['error'],
                        "size"     => $documents['size'],
                    ];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $file_name = upload_applicant_documents($hostel_info_id, $upload_data);
                        $row['pdf'] = $file_name["file_path"];
                        $row['status'] = 3;
                        $this->db->insert(db_prefix() . 'hostel_payment_activity_log', [
                            "date"        => date('Y-m-d H:i:s'),
                            "staffid"     => get_staff_user_id(),
                            "hostel_info_id"   => $hostel_info_id,
                            "description" => $payment_id ? "Payment Proof update successfully " : "Payment Proof add successfully ",
                            "payment_id" => $payment_id ?? 1
                        ]);
                    }
                }

                if (!empty($_FILES["tt_proof_" . $key]['name'])) {
                    $documents = $_FILES["tt_proof_" . $key];
                    $file_name_ = ($hostel_info_id ? get_client_name($hostel_info_id) : 'tt_proof') . "_" . time();
                    $upload_data = [
                        "name"     => $file_name_ . "." . pathinfo($documents['name'], PATHINFO_EXTENSION),
                        "type"     => $documents['type'],
                        "tmp_name" => $documents['tmp_name'],
                        "error"    => $documents['error'],
                        "size"     => $documents['size'],
                    ];
                    if ($upload_data["error"] === UPLOAD_ERR_OK) {
                        $file_name = upload_applicant_documents($hostel_info_id, $upload_data);
                        $row['tt_pdf'] = $file_name["file_path"];
                        $this->db->insert(db_prefix() . 'hostel_payment_activity_log', [
                            "date"        => date('Y-m-d H:i:s'),
                            "staffid"     => get_staff_user_id(),
                            "hostel_info_id"   => $hostel_info_id,
                            "description" => $payment_id ? "Payment TT Proof update successfully " : "Payment TT Proof add successfully ",
                            "payment_id" => $payment_id ?? 1
                        ]);
                    }
                }
                // Decide insert/update bucket
                if (!empty($row["id"])) {
                    $updateRows[] = $row;
                } else {
                    $insertRows[] = $row;
                }

                $activity_data[] = [
                    "date"        => date('Y-m-d H:i:s'),
                    "staffid"     => get_staff_user_id(),
                    "hostel_info_id"   => $hostel_info_id,
                    "description" => json_encode([
                        'ExchangeData' => $currency_exchange,
                        'PaymentData' => [
                            $hostel_info_id,
                            $university_name,
                            $start_date,
                            $end_date,
                            $room_capacity,
                            $row['mode'],
                            $row['amount'],
                            $row['pay_date'],
                            "vendor_id:" . ($row['vendor_id'] ?? 0),
                            "vendor_name:" . ($row['vendor_name'] ?? '')
                        ],
                        'SplitData' => $payment["split_data"]
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    "payment_id" => $payment_id ?? 1
                ];
            }



            // 🔹 Insert or Update
            if (!empty($updateRows)) {
                $this->db->update_batch(db_prefix() . 'hostel_payments', $updateRows, 'id');
            }
            if (!empty($insertRows)) {
                $this->db->insert_batch(db_prefix() . 'hostel_payments', $insertRows);
            }

            if (!empty($activity_data)) {
                $this->db->insert_batch(db_prefix() . 'hostel_payment_activity_log', $activity_data);
            }


            if ($this->db->affected_rows()) {
                echo json_encode([
                    'resp_code' => 'RCS',
                    'resp_desc' => 'Payment quotation data saved successfully.'
                ]);
            } else {
                throw new Exception("No changes were made or failed to save payment quotation data.");
            }
        } catch (Exception $e) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage()
            ]);
        }
    }

    public function payment_information($page_type = '')
    {
        try {
            // Load model
            $this->load->model('Payments_model');

            // Get input safely
            $hostel_info_id = $this->input->post('hostel_info_id', true);

            if (empty($hostel_info_id)) {
                throw new Exception("hostel_info_id ID is required.");
            }

            $view = "payment_information";
            if (!empty($page_type)) {
                $view = $page_type . "_payment_information";
            }

            $data = [];
            $data["hostel_info_id"] = $hostel_info_id;
            // Fetch data
            $pageData = $this->load->view(
                "admin/hostel_management/" . $view,
                $data,
                true
            );

            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Payment information retrieved successfully.',
                'data'      => $pageData
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage()
            ]);
        }
    }


    public function hostel_management()
    {
        $data = [];
        //   $data["universities"] = $this->Hostel_model->get_hostel_rentInfo();
        $this->load->view('admin/hostel_management/create', $data);
    }

    public function save_hostel()
    {
        try {
            // Permission check
            if (!has_permission('hostel', '', 'create')) {
                return access_denied('hostel');
            }

            $data = $this->input->post();

            // --- Check for duplicate hostel name ---
            $this->db->where('hostel_name', $data['hostel_name'] ?? '');
            if (!empty($data['hostel_management_id'])) {
                $this->db->where('id !=', $data['hostel_management_id']);
            }
            $existing = $this->db->get(db_prefix() . 'hostel')->row();
            if ($existing) {
                echo json_encode([
                    'resp_code' => 'ERR',
                    'resp_desc' => 'Hostel Name already exists.'
                ]);
                return;
            }

            $id = $data['hostel_management_id'] ?? 0;

            // Remove unnecessary fields
            unset($data['hostel_management_id'], $data['university_name']);

            $upload_path = FCPATH . 'uploads/hostel/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            // --- File upload handling ---
            foreach ($_FILES as $field => $file) {
                if (!empty($file['name'])) {
                    $new_filename = time() . '_' . preg_replace('/\s+/', '_', $file['name']);
                    $target_path = $upload_path . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $target_path)) {
                        // Save the relative path in $data with the same key as input
                        $data[$field] = 'uploads/hostel/' . $new_filename;
                    } else {
                        echo json_encode([
                            'resp_code' => 'ERR',
                            'resp_desc' => "Failed to upload file: {$file['name']}"
                        ]);
                        return;
                    }
                }
            }
            // --- Insert or Update ---
            if (!empty($id)) {
                // Update
                $data['updated_date'] = date('Y-m-d H:i:s');
                $data['updated_by'] = get_staff_user_id();
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'hostel', $data);
                $record_id = $id;
            } else {
                // Insert
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['created_by'] = get_staff_user_id();
                $this->db->insert(db_prefix() . 'hostel', $data);
                $record_id = $this->db->insert_id();
            }

            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Hostel details saved successfully.',
                'record_id' => $record_id
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => $e->getMessage()
            ]);
        }
    }



    function delete_hostel($id)
    {
        // ✅ Permission check
        if (!has_permission('hostel', '', 'delete')) {
            return access_denied('hostel'); // Stop execution immediately
        }

        if (!$id) {
            redirect(admin_url('hostel'));
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'hostel', ["status" => "0"]);

        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'resp_code' => 'RCS',
                'resp_desc' => 'Hostel record deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'resp_code' => 'ERR',
                'resp_desc' => 'Error deleting hostel record or record not found.'
            ]);
        }
    }

    public function quotation_payment_approved()
    {
        $data = [];
        // if ((!has_permission('payment_quotation', '', 'payment_approval'))) {
        //     access_denied('Quatation Payment Approval');
        //     die;
        // }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $quotation_payment_id = $this->input->post("quotation_payment_id");
            $hostel_info_id            = $this->input->post("hostel_info_id");
            $status               = (int) $this->input->post("status");
            $activity_data = [];
            $this->db->select("id,status");
            $this->db->where('hostel_info_id', $hostel_info_id);
            $this->db->where('id', $quotation_payment_id);
            $check_ = $this->db->get(db_prefix() . 'hostel_payments')->row();

            if (!$check_) {
                $data['resp_code'] = 'ERR';
                $data['resp_desc'] = 'Payment quotation not found';
            } else {
                $current_status = (int) $check_->status;

                if (in_array($current_status, [1, 2]) &&  $status != 0) {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'This quotation has already been ' . ($current_status == 1 ? 'approved' : 'rejected') . '.';
                } elseif ($status == 0) {
                    // Delete record
                    $this->db->where('id', $quotation_payment_id)->update(db_prefix() . 'hostel_payments', ['pdf' => '', 'status' => $status]);
                    if ($this->db->affected_rows() > 0) {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = 'Quotation payment deleted successfully.';
                    } else {
                        $data['resp_code'] = 'ERR';
                        $data['resp_desc'] = 'Failed to delete quotation payment.';
                    }

                    $activity_data[] = [
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "hostel_info_id"   => $hostel_info_id,
                        "description" => 'Quotation payment Delete successfully.',
                        "payment_id" => $quotation_payment_id ?? 1
                    ];

                    $this->db->insert_batch(db_prefix() . 'hostel_payment_activity_log', $activity_data);
                } elseif (in_array($status, [1, 2])) {
                    // Update to approve/reject
                    $this->db->where('id', $quotation_payment_id)
                        ->update(db_prefix() . 'hostel_payments', ['status' => $status]);

                    if ($this->db->affected_rows()) {
                        $data['resp_code'] = 'RCS';
                        $data['resp_desc'] = $status == 1 ? 'Quotation payment approved successfully.' : 'Quotation payment rejected successfully.';
                    } else {
                        $data['resp_code'] = 'ERR';
                        $data['resp_desc'] = 'Failed to update quotation payment.';
                    }

                    $activity_data[] = [
                        "date"        => date('Y-m-d H:i:s'),
                        "staffid"     => get_staff_user_id(),
                        "hostel_info_id"   => $hostel_info_id,
                        "description" => 'Quotation payment ' . ($status == 1 ? 'Approved' : 'Rejected') . ' successfully.',
                        "payment_id" => $quotation_payment_id ?? 1
                    ];

                    $this->db->insert_batch(db_prefix() . 'hostel_payment_activity_log', $activity_data);
                } else {
                    $data['resp_code'] = 'ERR';
                    $data['resp_desc'] = 'Invalid status action.';
                }
            }
        } else {
            $data['resp_code'] = 'ERR';
            $data['resp_desc'] = 'Invalid request method';
        }

        echo json_encode($data);
    }
    
    
    function hostelPaymentGenerate()
    {
        
        //  if (!has_permission('hostel_management', '', 'hostel_invoice_generate')) {
        //     return access_denied('hostel_management'); // Stop execution if no permission
        // }
        
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$hostels = $this->db->select("university")
    ->from(db_prefix() . "hostel")
    ->get()
    ->result_array();

// Extract only `university` values
// Extract only the names
$university_names = array_column($hostels, 'university');

// Remove empty values (optional)
$university_names = array_filter($university_names);



$university_details = $this->s_db->select("universities.id, universities.university_name, countries.country_name,CONCAT('https://educationvibes.in/',logo_image) as logo_image")
    ->from("universities")
    ->join("countries", "countries.id = universities.country_id", "left")
    ->join("university_banner", "university_banner.university_id = universities.id", "left")
    ->where_in("universities.university_name", $university_names)
    ->get()
    ->result_array();



$data["university_details"] = array_column($university_details,null,"university_name");

$payment_id = 17;

$columns = [
    "hp.university_name",
    "hp.acadmic_year",
    "hp.hostel_info_id",
    "hp.id",
    "hp.pay_date",
    "hi.name",
    "hi.passport",
    "h.name as hostel_name",
    "h.email",
    "h.contact_number",
    "h.hostel_logo",
    ];
$this->db->select($columns)
         ->from(db_prefix() . 'hostel_payments AS hp')
         ->join(db_prefix() . 'hostel_infomation AS hi', 'hi.id = hp.hostel_info_id', 'left')
         ->join(db_prefix() . 'hostel_quotation AS hq', 'hq.id = hp.quotation_id', 'left')
         ->join(db_prefix() . 'hostel AS h', 'h.id = hi.hostel', 'left')
         ->where('hp.id', $payment_id);

$data['hostelData'] = $this->db->get()->row();
$data["invoice_number"] = str_pad($payment_id, 6, '0', STR_PAD_LEFT);

// echo "<pre>";
// print_r($data['hostelData']);
// echo "</pre>";
// die;
   


        // Disable SSL verification (for images/fonts)
        stream_context_set_default(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

        // Initialize TCPDF
        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Education Vibes');
        $pdf->SetTitle('Hostel Invoice');
        $pdf->SetSubject('Hostel Invoice');


        // Disable default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // This won't work anymore if you decide to add a watermark
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 006', PDF_HEADER_STRING);
        // ✅ Force small margins to fit more on one page
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(false, 0); // ✅ Disable automatic page breaks completely

        // Add single page
        $pdf->AddPage();

        // $stampPath = FCPATH . $data['hostelData']->hostel_stamp;

        // // Check if image exists
        // if (file_exists($stampPath)) {

        //     // X and Y coordinates in mm
        //     $x = 130; // distance from left
        //     $y = 230;  // distance from top

        //     // Width of image in mm (height auto-scaled)
        //     $width = 40;

        //     // Place the stamp at absolute position
        //     $pdf->Image($stampPath, $x, $y, $width, 0, '', '', '', false, 300);
        // }

        // Optional: Custom fonts
        $path_gill_sans_mt = APPPATH . 'libraries/tcpdf/fonts/GILB____.ttf';
        $path_book_antiqua = APPPATH . 'libraries/tcpdf/fonts/book-antiqua-bold.ttf';
        $path_Cambria_Math = APPPATH . 'libraries/tcpdf/fonts/Cambria Math.ttf';
        $path_Cambria = APPPATH . 'libraries/tcpdf/fonts/Cambria/Cambria Bold 700.ttf';

        $data["gillsansmt"]   = TCPDF_FONTS::addTTFfont($path_gill_sans_mt, 'TrueTypeUnicode', '', 15);
        $data["book_antiqua"] = TCPDF_FONTS::addTTFfont($path_book_antiqua, 'TrueTypeUnicode', '', 15);
        $data["Cambria_Math"] = TCPDF_FONTS::addTTFfont($path_Cambria_Math, 'TrueTypeUnicode', '', 15);
        $data["Cambria"]      = TCPDF_FONTS::addTTFfont($path_Cambria, 'TrueTypeUnicode', '', 15);

        $pdf->setImageScale(1.7);


        // Load the HTML view
        $html = $this->load->view('admin/pdf/hostel_payment_receipt', $data, true);

         $pdf->writeHTML($html, true, false, true, false, '');

        // ✅ Output PDF to browser (single page)
        $pdf->Output('hostel_invoice_' . $data['hostelData']->id . '.pdf', 'I');

        die;
        $upload_dir = FCPATH . APPLICANT_UPLOAD_DOCUMENT_PATH . $hostelInfo_Id . "/Hostel-Quotation/";

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true) && !is_dir($upload_dir)) {
                echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
                return;
            }
        }

        // $file_name = 'Quotation_' . time() . '.pdf';

        $file_name = $data["hostelData"]->name . " " .
            $data["hostelData"]->university_name . " " .
            $data["hostelData"]->start_date . " " .
            $data["hostelData"]->end_date . " " .
            $data["hostelData"]->room_capacity . " " .
            time() . '.pdf';

        $file_name = strtolower(str_replace(" ", "_", $file_name));


        $file_path = $upload_dir . $file_name;

        // Remove if exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Save file
        $pdf->Output($file_path, 'F');

        if (!file_exists($file_path)) {
            echo json_encode(["status" => "error", "message" => "Failed to generate PDF file."]);
            return;
        }

        // -----------------------------
        // Update quotation record (not clients!)

        // -----------------------------
        $update_data = ["pdf" =>  base_url() . APPLICANT_UPLOAD_DOCUMENT_PATH . $hostelInfo_Id . "/Hostel-Quotation/" . $file_name];
        $this->db->where(["hostel_info_id" => $hostelInfo_Id, "id" => $quotation_id]);
        $this->db->update(db_prefix() . 'hostel_quotation', $update_data);


        // -----------------------------
        // Return response
        // -----------------------------
        echo json_encode([
            "status"   => "success",
            "pdf_url"  => base_url(APPLICANT_UPLOAD_DOCUMENT_PATH . $hostelInfo_Id . "/Hostel-Quotation/" . $file_name)
        ]);
        
    }
}
