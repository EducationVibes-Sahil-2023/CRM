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

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();

    if (empty($sheetData)) {
        return [];
    }

    $dataArray = [];
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
                LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
                LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
                LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
                LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
                LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
                LEFT JOIN (
        SELECT 
            userid,sum(apostille_cost) as Total_cost,max(courier_date) as courier_date,max(payment_date) as payment_date,GROUP_CONCAT(vendor_id) as vendor_id,
            CASE 
                WHEN COUNT(*) = 0 THEN 'Pending'
                WHEN SUM(received_status = 0) > 0 THEN 'Sent'
                WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
                ELSE 'Pending'
            END AS apostille_status
        FROM " . db_prefix() . "client_apostille_data
        GROUP BY userid
    ) AS apostille_summary ON apostille_summary.userid = c.userid
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
            "workSheetName" => $sheet_name,
            "rowData" => $arrayDataValues
        );
    }



    header('Content-Type: application/json');
    echo json_encode($dataArray);
    die;
}


function syncExcel_new($id = "")
{
    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition,column_ids,orignal_documents_status")
        ->from(db_prefix() . "excel_data_update")
        ->where("autoSync", 1);

    if (!empty($id)) {
        // $id = array_filter(explode(",", $id));
        $CI->db->where("spreadsheetId", $id);
    }

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();

    if (empty($sheetData)) {
        return [];
    }

    $dataArray = [];
    foreach ($sheetData as $sheet) {

        $currentId               = isset($sheet['id']) ? $sheet['id'] : null;
        $fromDate                = isset($sheet['fromDate']) ? $sheet['fromDate'] : null;
        $toDate                  = isset($sheet['toDate']) ? $sheet['toDate'] : null;
        $acadmic_year            = isset($sheet['acadmic_year']) ? $sheet['acadmic_year'] : null;
        $spreadsheetId           = isset($sheet['spreadsheetId']) ? $sheet['spreadsheetId'] : null;
        $sheet_name              = isset($sheet['sheet_name']) ? $sheet['sheet_name'] : null;
        $orignal_documents_status = isset($sheet['orignal_documents_status']) ? $sheet['orignal_documents_status'] : null;
        $sql_conditions          = isset($sheet['sql_condition']) ? $sheet['sql_condition'] : null;

        $column_ids_raw          = isset($sheet['column_ids']) ? $sheet['column_ids'] : '';
        $column_ids              = is_string($column_ids_raw) && !empty($column_ids_raw)
            ? explode(",", $column_ids_raw)
            : [];


        // Ensure $column_ids is a non-empty array

        // Fetch selected column names ordered by sequence
        $order = implode(',', $column_ids); // convert array to comma-separated string



        // Step 1: Fetch concatenated column names
        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, $order)) AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->get()
            ->row()
            ->fetch_column_name ?? '';

        if (empty($selectColumnName)) {
            // Skip this loop iteration if no columns found
            continue;
        }

        $extra_columns = [];
        if (!empty($orignal_documents_status) && $orignal_documents_status == 1) {
            // Step 2: Fetch original documents
            $orignal_documents = get_orignal_document_list(); // Returns an array

            // $upload_document = get_documents(1, [], 0, "", [db_prefix() . 'document_upload_type.orignal_status' => '1']);
            // $applicant_documents = get_clients_documents(617);

            // // Decode applicant documents if available
            // if (!empty($applicant_documents[0]["data"])) {
            //     $decoded_data = json_decode($applicant_documents[0]["data"], true);
            //     if (!empty($decoded_data)) {
            //         $applicant_documents = array_column($decoded_data, null, "id");
            //     }
            // }


            $queryPart = [];

            // Process applicant documents
            // if (!empty($upload_document)) {
            //     foreach ($upload_document as $document) {
            //         $doc_id = $document['id'];
            //         $short_name = trim($document['name']);
            //         $safe_column_name = str_replace(" ", "_", $short_name);
            //         $extra_columns[] = $safe_column_name;

            //         $file_url = !empty($applicant_documents[$doc_id]["document_file"]) ? $applicant_documents[$doc_id]["document_file"] : '';

            //         // Determine approval status
            //         if (isset($applicant_documents[$doc_id]["approval_status"])) {
            //             $status = ($applicant_documents[$doc_id]["approval_status"] == 1) ? "'Yes'" : "'No'";
            //         } else {
            //             $status = !empty($file_url) ? "'No'" : "''";  // Empty string in SQL
            //         }

            //         // Add to query part as: 'Yes' AS Document_Name
            //         $queryPart[] = "$status AS `$safe_column_name`";
            //     }
            // }

            // Process original documents for CASE WHEN logic
            if (!empty($orignal_documents)) {
                foreach ($orignal_documents as $documents) {
                    $short_name = trim($documents['short_name']); // Clean the short name
                    $safe_column_name = str_replace(" ", "_", $short_name); // Sanitize column alias
                    $extra_columns[] = $safe_column_name;

                    // Add a CASE WHEN expression for each document
                    $queryPart[] = "MAX(CASE WHEN od.short_name = '" . $CI->db->escape_str($short_name) . "' 
                          THEN 'YES' ELSE 'NO' END) AS `" . $safe_column_name . "`";
                }
            }

            // Step 3: Append dynamic CASE columns to existing SELECT list
            if (!empty($queryPart)) {
                $selectColumnName .= ',' . implode(',', $queryPart);
            }
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
                LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid and u.status=1
                LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
                LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
                LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
                LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
                LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
                LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
                LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
                 LEFT JOIN (
        SELECT 
            userid,sum(apostille_cost) as Total_cost,max(courier_date) as courier_date,max(payment_date) as payment_date,GROUP_CONCAT(vendor_id) as vendor_id,
            CASE 
                WHEN COUNT(*) = 0 THEN 'Pending'
                WHEN SUM(received_status = 0) > 0 THEN 'Sent'
                WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
                ELSE 'Pending'
            END AS apostille_status
        FROM " . db_prefix() . "client_apostille_data
        GROUP BY userid
    ) AS apostille_summary ON apostille_summary.userid = c.userid
                WHERE 1=1 {$condition_sql}
                GROUP BY c.userid";


        $arrayData = $CI->db->query($sql)->result_array();

        // Fetch column headers (in order)
        $sheetColumnName = $CI->db->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id, " . implode(',', array_map('intval', $column_ids)) . ")", "", false)
            ->get()
            ->result_array();


        $columns = array_column($sheetColumnName, "name");
        if (!empty($extra_columns)) {
            $columns = array_merge($columns, $extra_columns);
        }

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
            "workSheetName" => $sheet_name,
            "rowData" => $arrayDataValues
        );
    }



    header('Content-Type: application/json');
    echo json_encode($dataArray);
    die;
}


// function syncExcel_neww($id = "")
// {
//     $CI = &get_instance();

//     $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

//     // Fetch sheet config(s)
//     $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition,column_ids,orignal_documents_status,excel_type")
//         ->from(db_prefix() . "excel_data_update")
//         // ->where("excel_type", 1)
//         ->where("autoSync", 1);

//     if (!empty($id)) {
//         $CI->db->where("spreadsheetId", $id);
//     }

//     $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();

//     if (empty($sheetData)) {
//         return [];
//     }

//     $dataArray = [];
//     foreach ($sheetData as $sheet) {

//         if ($sheet['excel_type'] == 2) {
//             leads_excel_sync();
//             die;
//         }

//         if ($sheet['excel_type'] != 1) {
//             continue;
//         }


//         $currentId               = $sheet['id'] ?? null;
//         $fromDate                = $sheet['fromDate'] ?? null;
//         $toDate                  = $sheet['toDate'] ?? null;
//         $acadmic_year            = $sheet['acadmic_year'] ?? null;
//         $spreadsheetId           = $sheet['spreadsheetId'] ?? null;
//         $sheet_name              = $sheet['sheet_name'] ?? null;
//         $orignal_documents_status = $sheet['orignal_documents_status'] ?? null;
//         $sql_conditions          = $sheet['sql_condition'] ?? null;

//         $column_ids_raw = $sheet['column_ids'] ?? '';
//         $column_ids = is_string($column_ids_raw) && !empty($column_ids_raw)
//             ? explode(",", $column_ids_raw)
//             : [];

//         $order = implode(',', $column_ids);

//         $selectColumnName = $CI->db
//             ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, $order)) AS fetch_column_name", false)
//             ->from(db_prefix() . "excel_column_update")
//             ->where_in("id", $column_ids)
//             ->get()
//             ->row()
//             ->fetch_column_name ?? '';

//         if (empty($selectColumnName)) {
//             continue;
//         }

//         $extra_columns = [];
//         if (!empty($orignal_documents_status) && $orignal_documents_status == 1) {

//             $orignal_documents = get_orignal_document_list();
//             $upload_document = get_documents(2, [], 0, "", [db_prefix() . 'document_upload_type.orignal_status' => '1']);

//             $queryPart = [];



//             if (!empty($orignal_documents)) {
//                 foreach ($orignal_documents as $document) {
//                     $short_name = trim($document['short_name']);
//                     $safe_column_name = str_replace(" ", "_", $short_name);
//                     $extra_columns[] = $safe_column_name;

//                     $queryPart[] = "MAX(CASE WHEN od.short_name = '" . $CI->db->escape_str($short_name) . "' 
//                           THEN 'YES' ELSE 'NO' END) AS `" . $safe_column_name . "`";
//                 }
//             }

//             if (!empty($upload_document)) {
//                 foreach ($upload_document as $docu) {
//                     $doc_id = (int) $docu['id'];
//                     $safe_column_name = str_replace(" ", "_", $docu["name"]);
//                     $extra_columns[] = $safe_column_name;

//                     $queryPart[] = "
//             CASE
//                 WHEN JSON_SEARCH(
//                     CAST(CAST(cd.data AS CHAR CHARACTER SET utf8) AS JSON),
//                     'one',
//                     '$doc_id',
//                     NULL,
//                     '$.*.id'
//                 ) IS NOT NULL
//                 THEN 'YES'
//                 ELSE 'NO'
//             END AS `$safe_column_name`
//         ";
//                 }
//             }

//             $extra_columns[] = "Invitation_letter";
//             $queryPart[] = "IF(u.invitation_letter IS NOT NULL AND u.invitation_letter != '', 'Yes', 'No') AS Invitation_letter";

//             $extra_columns[] = "Admission_letter";
//             $queryPart[] = "IF(u.application_file IS NOT NULL AND u.application_file != '', 'Yes', 'No') AS Admission_letter";


//             if (!empty($queryPart)) {
//                 $selectColumnName .= ', ' . implode(",\n", $queryPart);
//             }
//         }

//         $condition_sql = "";

//         if (!empty($fromDate) && !empty($toDate)) {
//             $condition_sql .= " AND (c.datecreated BETWEEN '{$fromDate}' AND '{$toDate}')";
//         }

//         if (!empty($acadmic_year)) {
//             $condition_sql .= " AND (p.acadmic_year = '{$acadmic_year}')";
//         }

//         if (!empty($sql_conditions)) {
//             $condition_sql .= " {$sql_conditions}";
//         }

//         $sql = "SELECT {$selectColumnName}
//                 FROM " . db_prefix() . "clients c
//                 LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
//                 LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
//                 LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
//                 LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
//                 LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
//                 LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
//                 LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
//                 LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
//                 LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid AND u.status = 1
//                 LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
//                 LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
//                 LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
//                 LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
//                 LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
//                 LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
//                 LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
//                 LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "document_upload_type dt ON dt.lead_type = 2 AND dt.orignal_status = 1
//                 LEFT JOIN " . db_prefix() . "currencies cu ON cu.id = c.scholarship_currency
//                 LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = u.fees_payment_currency_id
//                 LEFT JOIN (
//                     SELECT 
//                         userid,
//                         SUM(apostille_cost) AS Total_cost,
//                         MAX(courier_date) AS courier_date,
//                         MAX(payment_date) AS payment_date,
//                         GROUP_CONCAT(vendor_id) AS vendor_id,
//                         CASE 
//                             WHEN COUNT(*) = 0 THEN 'Pending'
//                             WHEN SUM(received_status = 0) > 0 THEN 'Sent'
//                             WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
//                             ELSE 'Pending'
//                         END AS apostille_status
//                     FROM " . db_prefix() . "client_apostille_data
//                     GROUP BY userid
//                 ) AS apostille_summary ON apostille_summary.userid = c.userid
//                 WHERE 1=1 {$condition_sql}
//                 GROUP BY c.userid";

//         //  if (!empty($orignal_documents_status) && $orignal_documents_status == 1) {

//         //      echo $sql;
//         //      die;
//         //  }
//         $arrayData = $CI->db->query($sql)->result_array();

//         $sheetColumnName = $CI->db->select("name")
//             ->from(db_prefix() . "excel_column_update")
//             ->where_in("id", $column_ids)
//             ->order_by("FIELD(id, " . implode(',', array_map('intval', $column_ids)) . ")", "", false)
//             ->get()
//             ->result_array();

//         $columns = array_column($sheetColumnName, "name");
//         if (!empty($extra_columns)) {
//             $columns = array_merge($columns, $extra_columns);
//         }

//         $arrayDataValues = [];
//         foreach ($arrayData as $row) {
//             $valuesOnly = [];
//             foreach ($row as $v) {
//                 $valuesOnly[] = $v === null ? '' : $v;
//             }
//             $arrayDataValues[] = $valuesOnly;
//         }

//         $CI->db->where('id', $currentId);
//         $CI->db->update(db_prefix() . "excel_data_update", [
//             'lastSync' => date('Y-m-d H:i:s')
//         ]);

//         $dataArray[] = array(
//             "columnName" => $columns,
//             "workSheetName" => $sheet_name,
//             "rowData" => $arrayDataValues
//         );
//     }

//     header('Content-Type: application/json');
//     echo json_encode($dataArray);
//     die;
// }

function syncExcel_neww($id = "")
{

    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status, excel_type,apostile_documents_status,group_by")
        ->from(db_prefix() . "excel_data_update")
        ->where("autoSync", 1);

    if (!empty($id)) {
        $CI->db->where("spreadsheetId", $id);
    }

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();

    if (empty($sheetData)) {
        return [];
    }

    $dataArray = [];

    foreach ($sheetData as $sheet) {
        // Excel type handling
        if ((int) $sheet['excel_type'] === 2) {
            leads_excel_sync($id);
            die;
        }
        if ((int) $sheet['excel_type'] === 3) {
            sa_excel_sync($id);
            die;
        }
        if ((int) $sheet['excel_type'] !== 1) {
            continue;
        }

        $currentId                = $sheet['id'] ?? null;
        $fromDate                 = $sheet['fromDate'] ?? null;
        $toDate                   = $sheet['toDate'] ?? null;
        $acadmic_year             = $sheet['acadmic_year'] ?? null;
        $spreadsheetId            = $sheet['spreadsheetId'] ?? null;
        $sheet_name               = $sheet['sheet_name'] ?? null;
        $orignal_documents_status = $sheet['orignal_documents_status'] ?? null;
        $apostile_documents_status = $sheet['apostile_documents_status'] ?? null;
        $sql_conditions           = $sheet['sql_condition'] ?? null;
        $group_by_sql = $sheet['group_by'] ?? null;
        // Parse column IDs
        $column_ids_raw = $sheet['column_ids'] ?? '';
        $column_ids = (is_string($column_ids_raw) && trim($column_ids_raw) !== '')
            ? array_filter(array_map('intval', explode(",", $column_ids_raw)))
            : [];

        if (empty($column_ids)) {
            continue; // skip if no columns configured
        }

        $order = implode(',', $column_ids);

        // Fetch column names in correct order
        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, $order)) AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->get()
            ->row()
            ->fetch_column_name ?? '';

        if (empty($selectColumnName)) {
            continue;
        }

        $extra_columns = [];

        // Handle original documents extra columns
        if (!empty($orignal_documents_status) && (int) $orignal_documents_status === 1) {
            $orignal_documents = get_orignal_document_list(0, 0, 0, 0, 0, 0, 0, ["status" => 1]);
            $upload_document   = get_documents(2, [], 0, "", [db_prefix() . 'document_upload_type.orignal_status' => '1']);

            $queryPart = [];

            if (!empty($orignal_documents)) {
                foreach ($orignal_documents as $document) {
                    $short_name        = trim($document['short_name']);
                    $safe_column_name  = str_replace(" ", "_", $short_name);
                    $extra_columns[]   = $safe_column_name;
                    $queryPart[]       = "MAX(CASE WHEN od.short_name = " . $CI->db->escape($short_name) . " THEN 'YES' ELSE 'NO' END) AS `" . $safe_column_name . "`";
                }
            }

            if (!empty($upload_document)) {
                foreach ($upload_document as $docu) {
                    $doc_id           = (int) $docu['id'];
                    $safe_column_name = str_replace(" ", "_", $docu["name"]);
                    $extra_columns[]  = $safe_column_name;
                    $queryPart[] = "
    CASE 
        WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id') IS NOT NULL 
             AND JSON_CONTAINS(
                   JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$[*].id'),
                   JSON_QUOTE('{$doc_id}')
             )
        THEN 'YES'
        
        WHEN JSON_EXTRACT(CAST(data AS CHAR CHARACTER SET utf8), '$.\"{$doc_id}\".id') IS NOT NULL
        THEN 'YES'
        
        ELSE 'NO'
    END AS `{$safe_column_name}`";
                }
            }

            // Invitation letter
            $extra_columns[] = "Invitation_letter";
            $queryPart[] = "IF(u.invitation_letter IS NOT NULL AND u.invitation_letter != '', 'Yes', 'No') AS Invitation_letter";

            // Admission letter
            $extra_columns[] = "Admission_letter";
            $queryPart[] = "IF(u.application_file IS NOT NULL AND u.application_file != '', 'Yes', 'No') AS Admission_letter";

            if (!empty($queryPart)) {
                $selectColumnName .= ', ' . implode(",\n", $queryPart);
            }
        }

        if (!empty($apostile_documents_status) && (int) $apostile_documents_status === 1) {
            $apostille_documents = get_orignal_document_list(0, 0, 1);
            $apostille_visa_apostile_documents = get_orignal_document_list(0, 0, 0, 0, 0, 0, 1, ["status" => 0]);

            // Ensure both are arrays before merging
            if (!is_array($apostille_documents)) {
                $apostille_documents = [];
            }
            if (!is_array($apostille_visa_apostile_documents)) {
                $apostille_visa_apostile_documents = [];
            }

            $apostille_documents = array_merge($apostille_documents, $apostille_visa_apostile_documents);
            $queryPart = [];

            if (!empty($apostille_documents)) {
                foreach ($apostille_documents as $apostille) {
                    $short_name        = trim($apostille['short_name']);
                    if ($apostille["apostile_status"] == 1) {
                        $safe_column_name  = "Ap_" . str_replace(" ", "_", $short_name);
                    } else if ($apostille["visa_apostile"] == 1) {
                        $safe_column_name  = "Ap_" . str_replace(" ", "_", $short_name);
                    } else {
                        $safe_column_name  =  str_replace(" ", "_", $short_name);
                    }
                    $extra_columns[]   = $safe_column_name;

                    $queryPart[] = "
COALESCE(
  (
    SELECT
      CASE
        WHEN received_status = 1 THEN 'Received'
        ELSE 'Sent'
      END
    FROM " . db_prefix() . "client_apostille_data
    WHERE userid = c.userid
      AND doc_id = " . (int)$apostille['id'] . "
    ORDER BY id DESC
    LIMIT 1
  ),
  'Pending'
) AS `" . $safe_column_name . "`";
                }
                if (!empty($queryPart)) {
                    $selectColumnName .= ', ' . implode(",\n", $queryPart);
                }
            }
        }
        // Build conditions
        $condition_sql = "";
        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND (c.datecreated BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        if (!empty($acadmic_year)) {
            $condition_sql .= " AND (p.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";
        }
        if (!empty($sql_conditions)) {
            $condition_sql .= " {$sql_conditions}";
        }

        $group_by = "";
        $apostile_query = "";
        if (!empty($group_by_sql)) {
            $group_by = "," . $group_by_sql;

            $apostile_query = " JOIN (
                    SELECT 
                    aps.id,
                        aps.userid,
                        apostille_cost AS Total_cost,
                        courier_date AS courier_date,
                        payment_date AS payment_date,
                        apostille_received AS apostille_received,
                        vendor_id AS vendor_id,
                        doc_id AS doc_id,
                        tod.short_name doc_name,
                        if(by_vendor=1,'Yes','No') by_vendor,
                        CASE 
                            WHEN aps.id is NULL  THEN 'Pending'
                            WHEN received_status = 0 THEN 'Sent'
                            WHEN received_status = 1 THEN 'Received'
                            ELSE 'Pending'
                        END AS apostille_status,
                        aps.currency_text as currency_text
                    FROM " . db_prefix() . "client_apostille_data aps
                    join " . db_prefix() . "orignal_documents  tod ON aps.doc_id = tod.id
                ) AS apostille_summary ON apostille_summary.userid = c.userid  ";
        } else {
            $apostile_query = " LEFT JOIN (
                    SELECT 
                        userid,
                        SUM(apostille_cost) AS Total_cost,
                        MAX(courier_date) AS courier_date,
                        MAX(payment_date) AS payment_date,
                        MAX(apostille_received) AS apostille_received,
                        GROUP_CONCAT(vendor_id) AS vendor_id,
                        GROUP_CONCAT(doc_id) AS doc_id,
                        if(by_vendor=1,'Yes','No') by_vendor,
                        CASE 
                            WHEN COUNT(*) = 0 THEN 'Pending'
                            WHEN SUM(received_status = 0) > 0 THEN 'Sent'
                            WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
                            ELSE 'Pending'
                        END AS apostille_status
                    FROM " . db_prefix() . "client_apostille_data
                    GROUP BY userid
                ) AS apostille_summary ON apostille_summary.userid = c.userid ";
        }
        // Main SQL
        $sql = "SELECT {$selectColumnName}
                FROM " . db_prefix() . "clients c
                LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
                LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
                LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
                LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
                LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
                LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
                LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
                LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid AND u.status = 1 
                LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid and p.primary_university = u.university_name
                LEFT JOIN " . db_prefix() . "university_partner u_p ON u_p.id = u.partner
                LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
                LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
                LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
                LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
                LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
                LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
                LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
                LEFT JOIN " . db_prefix() . "visa_details vd ON vd.userid = c.userid
                LEFT JOIN " . db_prefix() . "vendor_visa vv ON vv.id = vd.vendor_id
                LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "document_upload_type dt ON dt.lead_type = 2 AND dt.orignal_status = 1
                LEFT JOIN " . db_prefix() . "currencies cu ON cu.id = c.scholarship_currency
                LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = u.fees_payment_currency_id
               {$apostile_query} 
                WHERE 1=1 {$condition_sql}
                GROUP BY c.userid {$group_by}   Order by c.userid";


        //  if (!empty($apostile_documents_status) && (int) $apostile_documents_status === 1) {
        //      echo $sql; die;
        //  }
        // if (!empty($orignal_documents_status) && (int) $orignal_documents_status === 1) {
        //  echo $sql; die;
        //         }
        // if($currentId == 11)
        // {
        //  echo $sql; die;
        // }
        $arrayData = $CI->db->query($sql)->result_array();

        // Get column names
        $sheetColumnName = $CI->db->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id, " . implode(',', $column_ids) . ")", "", false)
            ->get()
            ->result_array();

        $columns = array_column($sheetColumnName, "name");
        if (!empty($extra_columns)) {
            $columns = array_merge($columns, $extra_columns);
        }

        // Prepare data rows
        // $arrayDataValues = [];
        // foreach ($arrayData as $row) {
        //     $valuesOnly = [];
        //     foreach ($row as $v) {
        //         $valuesOnly[] = $v === null ? '' : $v;
        //     }
        //     $arrayDataValues[] = $valuesOnly;
        // }

        $arrayDataValues = array_map('array_values', $arrayData);

        // Update last sync
        $CI->db->where('id', $currentId);
        $CI->db->update(db_prefix() . "excel_data_update", [
            'lastSync' => date('Y-m-d H:i:s')
        ]);

        // Add to final array
        $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
    }

    // Output JSON safely
    header('Content-Type: application/json');
    echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sa_excel_sync($id = "")
{
    error_reporting(0);

    ini_set('display_errors', 1);

    $CI = &get_instance();

    // $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    $CI->db->query("SET SESSION sql_mode = ''");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 3)
        ->where("autoSync", 1);

    if (!empty($id)) {
        $CI->db->where("spreadsheetId", $id);
    }

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();

    if (empty($sheetData)) {
        return [];
    }

    $dataArray = [];

    foreach ($sheetData as $sheet) {


        $currentId                = $sheet['id'] ?? null;
        $fromDate                 = $sheet['fromDate'] ?? null;
        $toDate                   = $sheet['toDate'] ?? null;
        $acadmic_year             = $sheet['acadmic_year'] ?? null;
        $spreadsheetId            = $sheet['spreadsheetId'] ?? null;
        $sheet_name               = $sheet['sheet_name'] ?? null;
        $orignal_documents_status = $sheet['orignal_documents_status'] ?? null;
        $apostile_documents_status = $sheet['apostile_documents_status'] ?? null;
        $sql_conditions           = $sheet['sql_condition'] ?? null;

        // Parse column IDs
        $column_ids_raw = $sheet['column_ids'] ?? '';
        $column_ids = (is_string($column_ids_raw) && trim($column_ids_raw) !== '')
            ? array_filter(array_map('intval', explode(",", $column_ids_raw)))
            : [];

        if (empty($column_ids)) {
            continue; // skip if no columns configured
        }
        $order = implode(',', $column_ids);

        // Fetch column names in correct order
        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, $order)) AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->get()
            ->row()
            ->fetch_column_name ?? '';

        if (empty($selectColumnName)) {
            continue;
        }

        $extra_columns = [];

        // Build conditions
        $condition_sql = "";
        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND (" . db_prefix() . "clients.datecreated BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        if (!empty($acadmic_year)) {
            $condition_sql .= " AND (" . db_prefix() . "admission_preferences.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";
        }
        // if (!empty($sql_conditions)) {
        //     $condition_sql .= " {$sql_conditions}";
        // }

        $condition_sql .= " AND " . db_prefix() . "leads.type = 1 ";

        $sql = " SELECT {$selectColumnName}
FROM " . db_prefix() . "clients
LEFT JOIN " . db_prefix() . "basic_details ON " . db_prefix() . "basic_details.userid = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "applicant_status ON " . db_prefix() . "applicant_status.id = " . db_prefix() . "clients.active
LEFT JOIN " . db_prefix() . "leads ON (" . db_prefix() . "leads.id = " . db_prefix() . "clients.leadid AND " . db_prefix() . "leads.type = 1)
LEFT JOIN " . db_prefix() . "staff ON " . db_prefix() . "leads.assigned = " . db_prefix() . "staff.staffid
LEFT JOIN " . db_prefix() . "leads_status ON " . db_prefix() . "leads_status.id = " . db_prefix() . "leads.status
LEFT JOIN " . db_prefix() . "leads_type ON " . db_prefix() . "leads_type.id = " . db_prefix() . "leads.type
LEFT JOIN " . db_prefix() . "leads_sources ON " . db_prefix() . "leads_sources.id = " . db_prefix() . "leads.source
LEFT JOIN " . db_prefix() . "applicant_tracker ON " . db_prefix() . "applicant_tracker.id = (" . db_prefix() . "clients.applicant_status + 1)
LEFT JOIN " . db_prefix() . "admission_preferences ON " . db_prefix() . "admission_preferences.userid = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "client_university_shortlisting ON (" . db_prefix() . "client_university_shortlisting.client_id = " . db_prefix() . "clients.userid AND " . db_prefix() . "client_university_shortlisting.status = 1)
LEFT JOIN " . db_prefix() . "application_status ON " . db_prefix() . "application_status.id = " . db_prefix() . "client_university_shortlisting.application_status
LEFT JOIN " . db_prefix() . "applicant_fees_details ON " . db_prefix() . "applicant_fees_details.client_id = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "applicant_fees ON " . db_prefix() . "applicant_fees.id = " . db_prefix() . "applicant_fees_details.fees_id
LEFT JOIN " . db_prefix() . "currencies ON " . db_prefix() . "currencies.id = " . db_prefix() . "applicant_fees_details.currency_id
LEFT JOIN " . db_prefix() . "sa_applicant_stages stage_category ON stage_category.id = " . db_prefix() . "clients.applicant_stage
LEFT JOIN " . db_prefix() . "application_sub_category_study stage_sub_category ON stage_sub_category.id = " . db_prefix() . "clients.applicant_sub_status
LEFT JOIN " . db_prefix() . "client_passport_details ON " . db_prefix() . "client_passport_details.client_id = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "passport_stages ON " . db_prefix() . "passport_stages.id = " . db_prefix() . "client_passport_details.passport_status
LEFT JOIN " . db_prefix() . "academic_details ON " . db_prefix() . "academic_details.userid = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "visa_details ON " . db_prefix() . "visa_details.userid = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "sa_applicant_stages u_stage_category ON u_stage_category.id = " . db_prefix() . "client_university_shortlisting.applicant_stage
LEFT JOIN " . db_prefix() . "application_sub_category_study u_stage_sub_category ON u_stage_sub_category.id = " . db_prefix() . "client_university_shortlisting.applicant_sub_status
LEFT JOIN " . db_prefix() . "visa_status ON " . db_prefix() . "visa_status.id = " . db_prefix() . "visa_details.status
LEFT JOIN " . db_prefix() . "vendor_study_abroad ON " . db_prefix() . "vendor_study_abroad.id = " . db_prefix() . "client_university_shortlisting.vendor_id
LEFT JOIN " . db_prefix() . "admission_program ON " . db_prefix() . "admission_program.id = " . db_prefix() . "admission_preferences.degree
LEFT JOIN (SELECT client_id, shortlisting_id, SUM(payment_amount) AS total_payment_amount, MAX(id) AS latest_deposite_id, MAX(date_of_deposite) AS latest_deposite_date, MAX(currency_type) AS currency_type FROM " . db_prefix() . "applicntion_pre_deposite GROUP BY client_id, shortlisting_id) AS deposit_summary ON deposit_summary.client_id = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "offer_condition ON " . db_prefix() . "offer_condition.client_id = " . db_prefix() . "clients.userid AND " . db_prefix() . "offer_condition.university_id = " . db_prefix() . "client_university_shortlisting.university_id
LEFT JOIN " . db_prefix() . "university_offer_letter ON " . db_prefix() . "university_offer_letter.client_id = " . db_prefix() . "clients.userid
LEFT JOIN (SELECT td1.*, td2.total_cost FROM " . db_prefix() . "ticket_data td1 INNER JOIN (SELECT MAX(id) AS max_id, SUM(ticket_cost) total_cost FROM " . db_prefix() . "ticket_data GROUP BY client_id) td2 ON td1.id = td2.max_id) td ON td.client_id = " . db_prefix() . "clients.userid
LEFT JOIN " . db_prefix() . "ticket_status ts ON ts.id = td.ticket_status
LEFT JOIN " . db_prefix() . "departure_location dl ON dl.id = td.departure_location
LEFT JOIN " . db_prefix() . "ticket_batch tb ON tb.id = td.batch_id
LEFT JOIN " . db_prefix() . "university_partner u_p ON u_p.id = " . db_prefix() . "client_university_shortlisting.partner
LEFT JOIN (SELECT client_id, shortlisting_id, tracker_id, IFNULL(COUNT(DISTINCT id), 0) AS totalPendency FROM tblclient_university_pendency WHERE status = 1 GROUP BY client_id, shortlisting_id, tracker_id) AS total_pendency ON total_pendency.client_id = tblclients.userid AND total_pendency.shortlisting_id = tblclient_university_shortlisting.id AND tblclient_university_shortlisting.tracker_id + 1 = total_pendency.tracker_id
WHERE 1=1 {$condition_sql}
GROUP BY " . db_prefix() . "clients.userid";


        //  if (!empty($apostile_documents_status) && (int) $apostile_documents_status === 1) {
        //  echo $sql; die;
        //  }


        $sql = preg_replace('/\s+/', ' ', trim($sql));
        $query = $CI->db->query($sql);






        $arrayData = $query->result_array();



        // Get column names
        $sheetColumnName = $CI->db->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id, " . implode(',', $column_ids) . ")", "", false)
            ->get()
            ->result_array();

        $columns = array_column($sheetColumnName, "name");
        if (!empty($extra_columns)) {
            $columns = array_merge($columns, $extra_columns);
        }

        // Prepare data rows
        // $arrayDataValues = [];
        // foreach ($arrayData as $row) {
        //     $valuesOnly = [];
        //     foreach ($row as $v) {
        //         $valuesOnly[] = $v === null ? '' : $v;
        //     }
        //     $arrayDataValues[] = $valuesOnly;
        // }

        $arrayDataValues = array_map('array_values', $arrayData);

        // $arrayDataValues = array_map('array_values', $arrayData);

        // Update last sync
        $CI->db->where('id', $currentId);
        $CI->db->update(db_prefix() . "excel_data_update", [
            'lastSync' => date('Y-m-d H:i:s')
        ]);

        // Add to final array
        $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
    }

    // Output JSON safely
    header('Content-Type: application/json');
    echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}



function leads_excel_sync($id = "")
{
    $CI = &get_instance();
    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet configs
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 2)
        ->where("autoSync", 1);

    if (!empty($id)) {
        $CI->db->where("spreadsheetId", $id);
    }

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();
    if (empty($sheetData)) {
        return [];
    }

    $dataArray = [];

    // Fetch lead types once
    $leadTypes = $CI->db->select("name, id")
        ->from("tblleads_type")
        ->where("autoSync", 1)
        ->order_by("id", "asc")
        ->get()
        ->result_array();

    foreach ($sheetData as $sheet) {
        foreach ($leadTypes as $lType) {

            $currentId      = $sheet['id'];
            $fromDate       = $sheet['fromDate'] ?? null;
            $toDate         = $sheet['toDate'] ?? null;
            $spreadsheetId  = $sheet['spreadsheetId'] ?? null;
            $sheet_name     = $lType['name'] ?? null;
            $column_ids_raw = $sheet['column_ids'] ?? '';
            $column_ids     = is_string($column_ids_raw) && !empty($column_ids_raw)
                ? array_map('intval', explode(",", $column_ids_raw))
                : [];

            if (empty($column_ids)) {
                continue; // Skip if no columns
            }

            // Build ordered column IDs for SQL FIELD()
            $orderColumns = implode(',', $column_ids);

            // Get actual column names in the specified order
            $selectColumnName = $CI->db
                ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, {$orderColumns})) AS fetch_column_name", false)
                ->from(db_prefix() . "excel_column_update")
                ->where_in("id", $column_ids)
                ->get()
                ->row()
                ->fetch_column_name ?? '';

            if (empty($selectColumnName)) {
                continue;
            }

            // SQL condition
            $condition_sql = "";
            if (!empty($fromDate) && !empty($toDate)) {
                $condition_sql .= " AND DATE(l.dateadded) BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate);
            }
            if (!empty($lType['id'])) {
                $condition_sql .= " AND l.type = " . (int)$lType['id'];
            }
            // if (!empty($sheet['sql_condition'])) {
            //     $condition_sql .= " " . $sheet['sql_condition']; // Optional — ensure it's safe before enabling
            // }
            $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 500; // default batch size
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            // Main SQL query
            $sql = "
                SELECT {$selectColumnName}
                FROM tblleads l
                LEFT JOIN tblleads_status ls ON l.status = ls.id
                LEFT JOIN tblleads_type lt ON lt.id = l.type
                LEFT JOIN tblleads_sources lso ON lso.id = l.source
                LEFT JOIN tblstaff tsf ON tsf.staffid = l.assigned
                LEFT JOIN tblreminders tr ON l.id = tr.rel_id AND tr.rel_type = 'lead'
                LEFT JOIN tbllead_marketing lm ON lm.id = lso.marketing_type
                LEFT JOIN tblcustomfields cf ON cf.fieldto = 'leads' AND cf.active = '1'
                LEFT JOIN tblcustomfieldsvalues cfv ON cfv.fieldto = 'leads' AND cfv.fieldid = cf.id AND cfv.relid = l.id
                WHERE 1=1 {$condition_sql}
                GROUP BY l.id
                ORDER BY l.id DESC
                LIMIT {$offset}, {$limit}
            ";

            $arrayData = $CI->db->query($sql)->result_array();


            // Column names for final output
            $sheetColumnName = $CI->db->select("name")
                ->from(db_prefix() . "excel_column_update")
                ->where_in("id", $column_ids)
                ->where("excel_type", 2)
                ->order_by("FIELD(id, {$orderColumns})", "", false)
                ->get()
                ->result_array();

            $columns = array_column($sheetColumnName, "name");

            // Format row values

            $arrayData = array_map('array_values', $arrayData);


            // Update last sync
            $CI->db->where('id', $currentId)
                ->update(db_prefix() . "excel_data_update", [
                    'lastSync' => date('Y-m-d H:i:s')
                ]);

            // Append to output
            $dataArray[] = [
                "columnName"    => $columns,
                "workSheetName" => $sheet_name,
                "rowData"       => $arrayData
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($dataArray);
    exit;
}



// Read data from sheet
// if (!function_exists('read_sheet_data')) {
//     function read_sheet_data($id)
//     {
//         $googleSheet = get_google_sheet_instance();

//         return $googleSheet->readSheet($spreadsheetId, $range);
//     }
// }
