<?php

defined('BASEPATH') or exit('No direct script access allowed');



$CI = &get_instance();
$CI->load->library('GoogleSheetApi');


// Create a new sheet using ID from database
if (!function_exists('create_sheet')) {
    function create_sheet($id, $sheet_name = "")
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

                $response_data = $CI->googlesheetapi->updateSheetColumnNames($spreadsheetId, $columnHeaders, $sheet_name);

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
    function insert_sheet_data($id = "")
    {

        $CI = &get_instance();
        $arrayData = get_data_excel($id);
    }
}

if (!function_exists('get_data_excel')) {
    function get_data_excel($id = "")
    {
        $CI = &get_instance();

        // Build the query for excel data update
        $CI->db->select("id,spreadsheetId, fromDate, toDate, autoSync,acadmic_year,sheet_name,sql_condition")
            ->from(db_prefix() . "excel_data_update");

        $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

        if (!empty($id)) {
            $CI->db->where("id", $id);
        }

        $CI->db->where("autoSync", 1);
        $sheetData = $CI->db->get()->result_array();

        // If no data found, return empty
        if (empty($sheetData)) {
            return [];
        }

        foreach ($sheetData as $sheet) {

            $currentId = $sheet['id']; // Important for multiple autoSync rows
            $fromDate = $sheet['fromDate']; // Important for multiple autoSync rows
            $toDate = $sheet['toDate']; // Important for multiple autoSync rows
            $acadmic_year = $sheet['acadmic_year']; // Important for multiple autoSync rows
            $spreadsheetId = $sheet['spreadsheetId']; // Important for multiple autoSync rows
            $sheet_name = $sheet['sheet_name']; // Important for multiple autoSync rows
            $sql_conditions = $sheet['sql_condition']; // Important for multiple autoSync rows

            $response_ = create_sheet($currentId, $sheet_name);

            // Get selected columns
            $selectColumnName = $CI->db
                ->select("GROUP_CONCAT(fetch_column_name ORDER BY sequence ASC) as fetch_column_name", false)
                ->from(db_prefix() . "excel_column_update")
                ->where("excel_id", $currentId)
                ->get()
                ->row()
                ->fetch_column_name;


            if (empty($selectColumnName)) {
                continue; // Skip if no columns
            }

            $condition_sql = "";

            if (!empty($fromDate) && !empty($toDate)) {
                // Apply to CodeIgniter query builder (this works the same as BETWEEN)
                // $CI->db->where("c.datecreated BETWEEN '{$fromDate}' AND '{$toDate}'", null, false);
                // Prepare raw SQL condition for manual query usage
                $condition_sql .= " AND (c.datecreated BETWEEN '{$fromDate}' AND '{$toDate}')";
            }

            if (!empty($acadmic_year)) {
                // Apply to CodeIgniter query builder (this works the same as BETWEEN)
                // $CI->db->where("c.datecreated BETWEEN '{$fromDate}' AND '{$toDate}'", null, false);
                // Prepare raw SQL condition for manual query usage
                $condition_sql .= " AND (p.acadmic_year = '{$acadmic_year}')";
            }

            if (!empty($sql_conditions)) {
                $condition_sql .= $sql_conditions;
            }

            // Build the main data query
            $sql = "SELECT {$selectColumnName}
                    FROM " . db_prefix() . "clients c
                    LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
                    LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
                    LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
                    LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
                    LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
                    LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = (c.applicant_stage)
                    LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = (c.applicant_sub_status)
                    LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
                    LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid
                    LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
                    LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
                    LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
                    LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
                    LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
                    LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
                    WHERE 1 = 1  {$condition_sql}
                    GROUP BY c.userid";



            $arrayData = $CI->db->query($sql)->result_array(); // Return first successful result


            if (!empty($spreadsheetId) && !empty($arrayData)) {
                $CI->db->where('id', $currentId);
                $CI->db->update(db_prefix() . "excel_data_update", [
                    'lastSync' => date('Y-m-d H:i:s')
                ]);
                $CI->googlesheetapi->updateSheetData($spreadsheetId, $arrayData, $sheet_name);
            }
        }

        return true; // No valid autoSync sheet found
    }
}

function syncExcel($id = "")
{
    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition")
        ->from(db_prefix() . "excel_data_update")
        ->where("autoSync", 1);

    if (!empty($id)) {
        // $id = array_filter(explode(",", $id));
        $CI->db->where("spreadsheetId", $id);
    }

    $sheetData = $CI->db->order_by("id","asc")->get()->result_array();

    if (empty($sheetData)) {
        return [];
    }

$dataArray=[];
    foreach ($sheetData as $sheet) {

        $currentId = $sheet['id'];
        $fromDate = $sheet['fromDate'];
        $toDate = $sheet['toDate'];
        $acadmic_year = $sheet['acadmic_year'];
        $spreadsheetId = $sheet['spreadsheetId'];
        $sheet_name = $sheet['sheet_name'];
        $sql_conditions = $sheet['sql_condition'];

        // Fetch selected columns
        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY sequence ASC) as fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where("excel_id", $currentId)
            ->get()
            ->row()
            ->fetch_column_name;

        if (empty($selectColumnName)) {
            continue; // Skip if no column selected
        }

        // Build WHERE conditions
        $condition_sql = "";

        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND (c.datecreated BETWEEN '{$fromDate}' AND '{$toDate}')";
        }

        if (!empty($acadmic_year)) {
            $condition_sql .= " AND (p.acadmic_year = '{$acadmic_year}')";
        }

        if (!empty($sql_conditions)) {
            $condition_sql .= " {$sql_conditions}";
        }

        // Main query
        $sql = "SELECT {$selectColumnName}
                FROM " . db_prefix() . "clients c
                LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
                LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
                LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
                LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
                LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
                LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = (c.applicant_stage)
                LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = (c.applicant_sub_status)
                LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
                LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid
                LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
                LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
                LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
                LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
                WHERE 1=1 {$condition_sql}
                GROUP BY c.userid";

        $arrayData = $CI->db->query($sql)->result_array();

        // Fetch column headers (in order)
        $sheetColumnName = $CI->db->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where("excel_id", $currentId)
            ->order_by("sequence", "ASC")
            ->get()
            ->result_array();

        $columns = array_column($sheetColumnName, "name");

        // Convert row data to values only, respecting column order

$arrayDataValues = [];
foreach ($arrayData as $row) {
    $valuesOnly = [];
    foreach ($row as $v) {
        $valuesOnly[] = $v === null ? '' : $v; // Replace null with blank
    }
    $arrayDataValues[] = $valuesOnly;
}


        // Update last sync timestamp
        $CI->db->where('id', $currentId);
        $CI->db->update(db_prefix() . "excel_data_update", [
            'lastSync' => date('Y-m-d H:i:s')
        ]);

        // Output JSON
         $dataArray[] = array(
            "columnName" => $columns,
            "workSheetName"=>$sheet_name,
            "rowData" => $arrayDataValues
        );
       
    }
    
    

        header('Content-Type: application/json');
        echo json_encode($dataArray);
        die;

}


// Read data from sheet
// if (!function_exists('read_sheet_data')) {
//     function read_sheet_data($id)
//     {
//         $googleSheet = get_google_sheet_instance();

//         return $googleSheet->readSheet($spreadsheetId, $range);
//     }
// }
