<?php

defined('BASEPATH') or exit('No direct script access allowed');



$CI = &get_instance();
$CI->load->library('GoogleSheetApi');


// Create a new sheet using ID from database
if (!function_exists('create_sheet')) {
    function create_sheet($id)
    {
        $CI = &get_instance();
        $response = [
            'resp_code' => 'ERR',
            'resp_desc' => "Excel Sheet Not create successfully"
        ];
        try {
            // Fetch sheet name from DB
            $sheetData = $CI->db->select("name")
                ->from(db_prefix() . "excel_data_update")
                ->where("id", $id)
                ->get()
                ->row();

            if (!$sheetData) {
                log_message('error', "Sheet data not found for ID: $id");
                return false;
            }

            $sheetName = $sheetData->name;

            // Create and share sheet
            $spreadsheetId = $CI->googlesheetapi->createAndShareSheet($sheetName);

            if (!empty($spreadsheetId)) {
                $CI->db->where('id', $id);
                $CI->db->update(db_prefix() . "excel_data_update", [
                    'spreadsheetId' => $spreadsheetId,
                    'spreadsheetUrl' => 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId
                ]);

                $sheetColumnName = $CI->db->select("name")
                    ->from(db_prefix() . "excel_column_update")
                    ->where("excel_id", $id)
                    ->order_by("sequence", "ASC")
                    ->get()
                    ->result_array();

                // Convert to a flat array of just column names
                $columnHeaders = [];
                foreach ($sheetColumnName as $col) {
                    $columnHeaders[] = $col['name'];
                }

                $response_data = $CI->googlesheetapi->updateSheetColumnNames($spreadsheetId, $columnHeaders);

                print_r($response_data);
                if ($response_data["resp_code"] == "RCS") {
                    $response = [
                        'resp_code' => 'RCS',
                        'resp_desc' => "Google Excel Sheet update successfully"
                    ];
                } else {
                    $response = $response_data;
                }

                return $response;
            } else {

                $response = [
                    'resp_code' => 'RCS',
                    'resp_desc' => "Failed to create spreadsheet for: $sheetName"
                ];

                log_message('error', "Failed to create spreadsheet for: $sheetName");
            }
        } catch (Exception $e) {

            $response = [
                'resp_code' => 'RCS',
                'resp_desc' => 'Error in create_sheet: ' . $e->getMessage()
            ];
            log_message('error', 'Error in create_sheet: ' . $e->getMessage());
        }



        return $response;
    }
}

// Insert or update data to existing sheet
if (!function_exists('get_spreadsheetId')) {
    function get_spreadsheetId($id)
    {
        $CI = &get_instance();
        return $sheetData = $CI->db->select("spreadsheetId")
            ->from(db_prefix() . "excel_data_update")
            ->where("id", $id)
            ->get()
            ->row()->spreadsheetId;
    }
}

// Insert or update data to existing sheet
if (!function_exists('insert_sheet_data')) {
    function insert_sheet_data($id)
    {

        $CI = &get_instance();
        $arrayData = get_data_excel($id);
        $spreadsheetId = get_spreadsheetId($id);
        return $CI->googlesheetapi->updateSheetData($spreadsheetId, $arrayData);
    }
}


if (!function_exists('get_data_excel')) {
    function get_data_excel($id)
    {

        $CI = &get_instance();

        $selectColumnName = $CI->db->select("group_concat(fetch_column_name) as fetch_column_name")
            ->from(db_prefix() . "excel_column_update")
            ->where("excel_id", $id)
            ->order_by("sequence", "ASC")
            ->get()
            ->row()->fetch_column_name;

        $sql = "Select " . $selectColumnName . "
    from " . db_prefix() . "clients c LEFT join " . db_prefix() . "basic_details b on c.userid = b.userid LEFT join " . db_prefix() . "applicant_status s on c.active = s.id   JOIN " . db_prefix() . "leads l ON l.id = c.leadid  left join " . db_prefix() . "staff st on l.assigned = st.staffid LEFT JOIN  " . db_prefix() . "applicant_tracker tt ON tt.id = (c.applicant_status+1) left join " . db_prefix() . "admission_preferences p on p.userid = c.userid left join " . db_prefix() . "client_university_shortlisting u on u.client_id  = c.userid LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id  LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id=c.userid
LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
where l.type =2 group by c.userid";
        return  $CI->db->query($sql)->result_array();
        // return $CI->googlesheetapi->updateSheet($spreadsheetId, $range, $values);
    }
}

// Read data from sheet
if (!function_exists('read_sheet_data')) {
    function read_sheet_data($id)
    {
        $googleSheet = get_google_sheet_instance();

        return $googleSheet->readSheet($spreadsheetId, $range);
    }
}
