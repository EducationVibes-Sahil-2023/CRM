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
                // $condition_sql .= " AND (p.acadmic_year = '{$acadmic_year}')";
                 list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

            // build semester codes
            $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . "))";
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
                // $CI->db->where('id', $currentId);
                // $CI->db->update(db_prefix() . "excel_data_update", [
                //     'lastSync' => date('Y-m-d H:i:s')
                // ]);
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
            // $condition_sql .= " AND (p.acadmic_year = '{$acadmic_year}')";
            
             list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

            // build semester codes
            $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . "))";
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
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);

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
            // $condition_sql .= " AND (p.acadmic_year = '{$acadmic_year}')";
            
             list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

            // build semester codes
            $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . "))";
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
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);

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


function splitSqlColumns($string)
{
    $columns = [];
    $current = '';
    $depth = 0;
    $length = strlen($string);

    for ($i = 0; $i < $length; $i++) {
        $char = $string[$i];

        if ($char === '(') {
            $depth++;
        } elseif ($char === ')') {
            $depth--;
        }

        if ($char === ',' && $depth === 0) {
            $columns[] = trim($current);
            $current = '';
        } else {
            $current .= $char;
        }
    }

    if (trim($current) !== '') {
        $columns[] = trim($current);
    }

    return $columns;
}

function syncExcel_neww($id = "",$currentId="",$sheetStatus="")
{


//           ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// 
    $CI = &get_instance();

//   $CI->db->query("SET SESSION group_concat_max_len = 100000");
    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status, excel_type,apostile_documents_status,group_by,application_doc,ignore_join,org,ap,upd,vap")
        ->from(db_prefix() . "excel_data_update")
        ->where("autoSync", 1);

    if (!empty($id)) {
        $CI->db->where("spreadsheetId", $id);
    }

 if (!empty($currentId)) {
        $CI->db->where("id", $currentId);
        
    }
    
    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();
    
    if(!empty($sheetStatus) && $sheetStatus == 1)
    {
   
        header('Content-Type: application/json');
        echo json_encode($sheetData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
        
    }
  
  if (!empty($currentId) && $currentId == 40) {
        syncExcel_neww_($id,$currentId,$sheetStatus);
        
    }
  

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
        if ((int) $sheet['excel_type'] === 4) {

            $dataArray[] = fly_excel_sync($id);
            continue;
            // die;
        }

        if ((int) $sheet['excel_type'] === 5) {

            $dataArray[] = visa_excel_sync($id);
            continue;
            // die;
        }
        if ((int) $sheet['excel_type'] === 6) {

            $dataArray[] = payment_quotations($id);
            continue;
            // die;
        }

        if ((int) $sheet['excel_type'] === 7) {

            $dataArray[] = ex_visa_data($id);
            continue;
            // die;
        }

        if ((int) $sheet['excel_type'] === 8) {

            $dataArray[] = ex_ticket_data($id);
            continue;
            // die;
        }

   if ((int) $sheet['excel_type'] === 9) {

            $dataArray[] = visitor_logs_data($id);
            continue;
            // die;
        }
        
          if ((int) $sheet['excel_type'] === 10) {

            $dataArray[] = external_apostile_data($id);
            continue;
            // die;
        }
        if ((int) $sheet['excel_type'] !== 1) {
            continue;
        }
$selectSequence =[];

        $currentId                = $sheet['id'] ?? null;
        $fromDate                 = $sheet['fromDate'] ?? null;
        $toDate                   = $sheet['toDate'] ?? null;
        $acadmic_year             = $sheet['acadmic_year'] ?? null;
        $spreadsheetId            = $sheet['spreadsheetId'] ?? null;
        $sheet_name               = $sheet['sheet_name'] ?? null;
        $orignal_documents_status = $sheet['orignal_documents_status'] ?? null;
        $apostile_documents_status = $sheet['apostile_documents_status'] ?? null;
        $sql_conditions           = $sheet['sql_condition'] ?? null;
        $application_doc = $sheet['application_doc'] ?? null;
        $group_by_sql = $sheet['group_by'] ?? null;
        $ignore_join = $sheet["ignore_join"]??0;
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
           


     $orignal_documents = get_orignal_document_list(0, 0, 0, 0, 0, 0, 0, ["excel_show " => 1]);
            $upload_document   = get_documents(2, [], 0, "", [db_prefix() . 'document_upload_type.orignal_status' => '1']);

            $queryPart = [];

            if (!empty($orignal_documents)) {
                foreach ($orignal_documents as $document) {
                    $short_name        = trim($document['short_name']);
                    $safe_column_name  = 'org_'.preg_replace('/[^A-Za-z0-9]+/', '_', $short_name);
                    $extra_columns[]   = $safe_column_name;
                    $queryPart[] = "MAX(
    CASE
        WHEN od.short_name = " . $CI->db->escape($short_name) . "
             AND (dr.location_id != 12 OR dr.location_id IS NULL)
        THEN 'YES'
        ELSE 'NO'
    END
) AS `" . $safe_column_name . "`";
                }
            }

            if (!empty($upload_document)) {
                foreach ($upload_document as $docu) {
                    $doc_id           = (int) $docu['id'];
                    $safe_column_name = 'upl_'.preg_replace('/[^A-Za-z0-9]+/', '_', $docu["name"]);
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
        $apostileSub = "";
        if (!empty($apostile_documents_status) && (int) $apostile_documents_status === 1) {

            $apostileSub .= "LEFT JOIN (
        SELECT 
        ca.userid,
        GROUP_CONCAT(DISTINCT od.short_name) AS all_docs,
        GROUP_CONCAT(DISTINCT CASE WHEN ca.received_status = 1 THEN od.short_name END) AS received_docs,
        GROUP_CONCAT(DISTINCT CASE WHEN ca.by_vendor = 1 THEN od.short_name END) AS by_vendor_docs
        FROM tblclient_apostille_data ca
        JOIN tblorignal_documents od ON od.id = ca.doc_id
        GROUP BY ca.userid
        ) doc_list ON doc_list.userid = c.userid";



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

                $apostileSub .= " LEFT JOIN (
    SELECT userid";
                foreach ($apostille_documents as $apostille) {

                    $short_name        = trim($apostille['short_name']);
                    if ($apostille["apostile_status"] == 1) {
                        $safe_column_name  = "Ap_" . str_replace(" ", "_", $short_name);
                    } else if ($apostille["visa_apostile"] == 1) {
                        $safe_column_name  = "Ap_" . str_replace(" ", "_", $short_name);
                    } else {
                        $safe_column_name  =  str_replace(" ", "_", $short_name);
                    }

$safe_column_name = preg_replace('/[^A-Za-z0-9]+/', '_', $safe_column_name);
                    $safe_column_name = str_replace(".", "", $safe_column_name);
                    $extra_columns[]   = $safe_column_name;
                    $queryPart[] = "IFNULL(doc_status." . $safe_column_name . ",'Pending') as {$safe_column_name} ";

                    $apostileSub .= " ,COALESCE(MAX(CASE WHEN doc_id = " . $apostille['id'] . " THEN (CASE WHEN received_status = 1 THEN 'Received' ELSE 'Sent' END) END), 'Pending') AS {$safe_column_name} ";
                }
                if (!empty($queryPart)) {
                    $selectColumnName .= ', ' . implode(",\n", $queryPart);
                }

                $apostileSub .= " FROM tblclient_apostille_data
    GROUP BY userid
) doc_status ON doc_status.userid = c.userid ";
            }
        }
        
       if (!empty($application_doc) && $application_doc == 1)
{
    $upload_document_application = get_documents(
        2,
        [],
        0,
        "",
        [db_prefix() . 'document_upload_type.application_doc' => '1']
    );
 

    if (!empty($upload_document_application)) {
        $queryPart=[];
        foreach ($upload_document_application as $app_doc) {

            $docId = (int) $app_doc['id'];

            // safer column alias
            $safe_column_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $app_doc["name"]);

            $extra_columns[] = $safe_column_name;

            // $queryPart[] = "
            //     CASE 
            //         WHEN JSON_UNQUOTE(JSON_EXTRACT(CAST(cd.data AS CHAR), '$.\"$docId\".approval_status')) = '1' THEN 'Approved'
            //         WHEN JSON_UNQUOTE(JSON_EXTRACT(CAST(cd.data AS CHAR), '$.\"$docId\".approval_status')) = '2' THEN 'Rejected'
            //         ELSE 'Pending'
            //     END AS `$safe_column_name`
            // ";
            
//          $queryPart[] = "
// CASE 
//     WHEN cd.data REGEXP CONCAT('\"id\":', $docId, '[^}]*\"approval_status\":\"1\"')
//     THEN 'Approved'
//     WHEN cd.data REGEXP CONCAT('\"id\":', $docId, '[^}]*\"approval_status\":\"2\"')
//     THEN 'Rejected'
//     ELSE 'Pending'
// END AS `$safe_column_name`
// ";

// id present in an object at all (either the doc exists or it doesn't)
$idExists = '"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?[[:space:]]*[,}]';

// id + a specific approval_status in the SAME object (either field order)
$idStatus = function ($value) use ($docId) {
    $value = (int) $value;

    $id = '"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?[[:space:]]*[,}]';
    $st = '"approval_status"[[:space:]]*:[[:space:]]*"?' . $value . '"?[[:space:]]*[,}]';

    return '('
        . $id . '[^}]*' . $st
        . '|'
        . $st . '[^}]*' . $id
        . ')';
};

$queryPart[] = "
    CASE
        WHEN cd.data REGEXP '" . $idStatus(1) . "' THEN 'Approved'
        WHEN cd.data REGEXP '" . $idStatus(2) . "' THEN 'Rejected'
        WHEN cd.data REGEXP '" . $idExists . "' THEN 'Submitted'
        ELSE 'Pending'
    END AS `$safe_column_name`
";

// $docId = preg_quote((string)$docId, '/');

// $idExists = '"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?';

// $idApproved =
//     '(?:"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?[^}]*"approval_status"[[:space:]]*:[[:space:]]*"?1"?'
//     . '|'
//     . '"approval_status"[[:space:]]*:[[:space:]]*"?1"?[^}]*"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?)';

// $idRejected =
//     '(?:"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?[^}]*"approval_status"[[:space:]]*:[[:space:]]*"?2"?'
//     . '|'
//     . '"approval_status"[[:space:]]*:[[:space:]]*"?2"?[^}]*"id"[[:space:]]*:[[:space:]]*"?' . $docId . '"?)';

// $queryPart[] = "
// CASE
//     WHEN cd.data IS NULL THEN 'Pending'
//     WHEN cd.data REGEXP '$idApproved' THEN 'Approved'
//     WHEN cd.data REGEXP '$idRejected' THEN 'Rejected'
//     WHEN cd.data REGEXP '$idExists' THEN 'Submitted'
//     ELSE 'Pending'
// END AS `$safe_column_name`
// ";
        }
        
//       $column_name = preg_replace('/[^a-zA-Z0-9_]/', '_', 'Admission letter');

// $extra_columns[] = $column_name;

// $queryPart[] = "
// IF(
//     u.application_file = '',
//     'Pending',
//     'Submitted'
// ) AS `$column_name`
// ";
        
        
       
        
    }

    if (!empty($queryPart)) {
                 $selectColumnName .= ', ' . implode(",\n", $queryPart);
                 
           
         
            }
}

        // else{
        //         if (!empty($apostile_documents_status) && (int) $apostile_documents_status === 1) {
        //             $apostille_documents = get_orignal_document_list(0, 0, 1);
        //             $apostille_visa_apostile_documents = get_orignal_document_list(0, 0, 0, 0, 0, 0, 1, ["status" => 0]);

        //             // Ensure both are arrays before merging
        //             if (!is_array($apostille_documents)) {
        //                 $apostille_documents = [];
        //             }
        //             if (!is_array($apostille_visa_apostile_documents)) {
        //                 $apostille_visa_apostile_documents = [];
        //             }

        //             $apostille_documents = array_merge($apostille_documents, $apostille_visa_apostile_documents);
        //             $queryPart = [];

        //             if (!empty($apostille_documents)) {
        //                 foreach ($apostille_documents as $apostille) {
        //                     $short_name        = trim($apostille['short_name']);
        //                     if ($apostille["apostile_status"] == 1) {
        //                         $safe_column_name  = "Ap_" . str_replace(" ", "_", $short_name);
        //                     } else if ($apostille["visa_apostile"] == 1) {
        //                         $safe_column_name  = "Ap_" . str_replace(" ", "_", $short_name);
        //                     } else {
        //                         $safe_column_name  =  str_replace(" ", "_", $short_name);
        //                     }
        //                     $extra_columns[]   = $safe_column_name;

        //                     $queryPart[] = "
        // COALESCE(
        //   (
        //     SELECT
        //       CASE
        //         WHEN received_status = 1 THEN 'Received'
        //         ELSE 'Sent'
        //       END
        //     FROM " . db_prefix() . "client_apostille_data
        //     WHERE userid = c.userid
        //       AND doc_id = " . (int)$apostille['id'] . "
        //     ORDER BY id DESC
        //     LIMIT 1
        //   ),
        //   'Pending'
        // ) AS `" . $safe_column_name . "`";
        //                 }
        //                 if (!empty($queryPart)) {
        //                     $selectColumnName .= ', ' . implode(",\n", $queryPart);
        //                 }
        //             }
        //         }
        // }



        // Build conditions
        $condition_sql = "";
        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND (c.datecreated BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        if (!empty($acadmic_year)) {

            // $condition_sql .= " AND (p.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";


            // extract start & end years



            // safer split (handles spaces correctly)
            list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

            // build semester codes
            $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . "))";
            
    //               $condition_sql .=" AND (
    //     TRIM(SUBSTRING_INDEX(p.acadmic_year, '-', 1)) = '" . $start . "'
    //     OR p.acadmic_year IS NULL
    //     OR p.acadmic_year = ''
    // ) ";
    
        }
        if (!empty($sql_conditions)) {
            $condition_sql .= " {$sql_conditions}";
        }

        $group_by = "";
        $apostile_query = "";
        // if (!empty($group_by_sql)) {
        //     $group_by = $group_by_sql;

        //     $apostile_query = " JOIN (
        //             SELECT 
        //             aps.id,
        //                 aps.userid,
        //                 apostille_cost AS Total_cost,
        //                 courier_date AS courier_date,
        //                 payment_date AS payment_date,
        //                 apostille_received AS apostille_received,
        //                 vendor_id AS vendor_id,
        //                 doc_id AS doc_id,
        //                 tod.short_name doc_name,
        //                 if(by_vendor=1,'Yes','No') by_vendor,
        //                 CASE 
        //                     WHEN aps.id is NULL  THEN 'Pending'
        //                     WHEN received_status = 0 THEN 'Sent'
        //                     WHEN received_status = 1 THEN 'Received'
        //                     ELSE 'Pending'
        //                 END AS apostille_status,
        //                 aps.currency_text as currency_text
        //             FROM " . db_prefix() . "client_apostille_data aps
        //             join " . db_prefix() . "orignal_documents  tod ON aps.doc_id = tod.id
        //         ) AS apostille_summary ON apostille_summary.userid = c.userid  ";
        // } else {
        //     $apostile_query = " LEFT JOIN (
        //             SELECT 
        //                 userid,
        //                 SUM(apostille_cost) AS Total_cost,
        //                 MAX(courier_date) AS courier_date,
        //                 MAX(payment_date) AS payment_date,
        //                 MAX(apostille_received) AS apostille_received,
        //                 GROUP_CONCAT(vendor_id) AS vendor_id,
        //                 GROUP_CONCAT(doc_id) AS doc_id,
        //                 if(by_vendor=1,'Yes','No') by_vendor,
        //                 CASE 
        //                     WHEN COUNT(*) = 0 THEN 'Pending'
        //                     WHEN SUM(received_status = 0) > 0 THEN 'Sent'
        //                     WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
        //                     ELSE 'Pending'
        //                 END AS apostille_status
        //             FROM " . db_prefix() . "client_apostille_data
        //             GROUP BY userid
        //         ) AS apostille_summary ON apostille_summary.userid = c.userid ";
        // }
        
        if($ignore_join==1){
        if ($group_by_sql) {
$group_by = $group_by_sql;
    $apostile_query = " JOIN (
        SELECT 
            aps.id,
            aps.userid,
            (
                aps.apostille_cost * CASE
                    WHEN aps.exchange_rate = 0 OR aps.exchange_rate IS NULL THEN 1
                    ELSE aps.exchange_rate
                END
            ) AS Total_cost,
            aps.exchange_rate,
            aps.apostille_cost,
            aps.courier_date AS courier_date,
            aps.payment_date AS payment_date,
            aps.apostille_received AS apostille_received,
            aps.vendor_id AS vendor_id,
            aps.doc_id AS doc_id,
            tod.short_name AS doc_name,
            IF(aps.by_vendor = 1, 'Yes', 'No') AS by_vendor,
            CASE
                WHEN aps.id IS NULL THEN 'Pending'
                WHEN aps.received_status = 0 THEN 'Sent'
                WHEN aps.received_status = 1 THEN 'Received'
                ELSE 'Pending'
            END AS apostille_status,
            aps.currency_text AS currency_text
        FROM " . db_prefix() . "client_apostille_data aps
        JOIN " . db_prefix() . "orignal_documents tod
            ON aps.doc_id = tod.id
    ) AS apostille_summary
    ON apostille_summary.userid = c.userid";
    
    
     if ($group_by_sql == "translation_summary.id") {
     $apostile_query = " JOIN (
        SELECT 
            aps.id,
            aps.userid,
            (
                aps.translation_cost * CASE
                    WHEN aps.exchange_rate = 0 OR aps.exchange_rate IS NULL THEN 1
                    ELSE aps.exchange_rate
                END
            ) AS Total_cost,
               aps.exchange_rate,
            aps.translation_cost,
            aps.courier_date AS courier_date,
            aps.payment_date AS payment_date,
            aps.translation_received AS translation_received,
            aps.vendor_id AS vendor_id,
            aps.doc_id AS doc_id,
            tod.short_name AS doc_name,
            IF(aps.by_vendor = 1, 'Yes', 'No') AS by_vendor,
            CASE
                WHEN aps.id IS NULL THEN 'Pending'
                WHEN aps.received_status = 0 THEN 'Sent'
                WHEN aps.received_status = 1 THEN 'Received'
                ELSE 'Pending'
            END AS translation_status,
            aps.currency_text AS currency_text
        FROM " . db_prefix() . "client_translation_data aps
        JOIN " . db_prefix() . "orignal_documents tod
            ON aps.doc_id = tod.id
    ) AS translation_summary
    ON translation_summary.userid = c.userid";
     }

} else {

    $apostile_query = " LEFT JOIN (
        SELECT
            userid,
            SUM(
                apostille_cost * CASE
                    WHEN exchange_rate = 0 OR exchange_rate IS NULL THEN 1
                    ELSE exchange_rate
                END
            ) AS total_cost,
            aps.exchange_rate,
            aps.apostille_cost,
            MAX(courier_date) AS courier_date,
            MAX(payment_date) AS payment_date,
            MAX(apostille_received) AS apostille_received,
            GROUP_CONCAT(vendor_id) AS vendor_id,
            GROUP_CONCAT(doc_id) AS doc_id,
            IF(MAX(by_vendor) = 1, 'Yes', 'No') AS by_vendor,
            CASE
                WHEN COUNT(*) = 0 THEN 'Pending'
                WHEN SUM(received_status = 0) > 0 THEN 'Sent'
                WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
                ELSE 'Pending'
            END AS apostille_status
        FROM " . db_prefix() . "client_apostille_data
        GROUP BY userid
    ) AS apostille_summary
    ON apostille_summary.userid = c.userid";
    
    
     if ($group_by_sql == "translation_summary.id") {
     $apostile_query = " LEFT JOIN (
        SELECT 
            aps.id,
            aps.userid,
            (
                aps.translation_cost * CASE
                    WHEN aps.exchange_rate = 0 OR aps.exchange_rate IS NULL THEN 1
                    ELSE aps.exchange_rate
                END
            ) AS Total_cost,
            aps.exchange_rate,
            aps.translation_cost,
            aps.courier_date AS courier_date,
            aps.payment_date AS payment_date,
            aps.translation_received AS translation_received,
            aps.vendor_id AS vendor_id,
            aps.doc_id AS doc_id,
            tod.short_name AS doc_name,
            IF(aps.by_vendor = 1, 'Yes', 'No') AS by_vendor,
            CASE
                WHEN aps.id IS NULL THEN 'Pending'
                WHEN aps.received_status = 0 THEN 'Sent'
                WHEN aps.received_status = 1 THEN 'Received'
                ELSE 'Pending'
            END AS translation_status,
            aps.currency_text AS currency_text
        FROM " . db_prefix() . "client_translation_data aps
        JOIN " . db_prefix() . "orignal_documents tod
            ON aps.doc_id = tod.id
    ) AS translation_summary
    ON translation_summary.userid = c.userid";
     }

}
}

        // Main SQL

        // LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid and p.primary_university = u.university_name
//         $sql = "SELECT {$selectColumnName}
//                 FROM " . db_prefix() . "clients c
//                 LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
//                 LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
//                 LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
//                 LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
//                 LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
//                 LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
//                 LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
               
                
//                 LEFT JOIN tbladmission_preferences p 
//                 ON p.userid = c.userid
//                  LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid AND u.status = 1 
//               AND (
//         (u.university_name IS NOT NULL AND p.primary_university = u.university_name)
//         OR (u.university_name IS NULL)
//   )

//                 LEFT JOIN " . db_prefix() . "university_partner u_p ON u_p.id = u.partner
//                 LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
//                 LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
//                 LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
//                 LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
//                 LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
//                 LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
//                 LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
// LEFT JOIN (
//     SELECT 
//         vd_latest.*,
//         vd_sum.total_visa_cost,
//         vv.name AS vendor_name,
//         pm.name AS payment_mode_name
//     FROM " . db_prefix() . "visa_details vd_latest

//     /* Total visa cost per user */
//     INNER JOIN (
//         SELECT 
//             userid,
//             SUM(cost) AS total_visa_cost
//         FROM " . db_prefix() . "visa_details
//         GROUP BY userid
//     ) vd_sum 
//         ON vd_latest.userid = vd_sum.userid

//     /* Latest visa record per user */
//     INNER JOIN (
//         SELECT 
//             userid,
//             MAX(id) AS latest_id
//         FROM " . db_prefix() . "visa_details
//         GROUP BY userid
//     ) vd_max 
//         ON vd_latest.userid = vd_max.userid
//       AND vd_latest.id = vd_max.latest_id

//     /* Extra joins */
//     LEFT JOIN " . db_prefix() . "vendor_list vv 
//         ON vv.id = vd_latest.vendor_id

//     LEFT JOIN " . db_prefix() . "payment_mode pm 
//         ON pm.id = vd_latest.payment_mode
// ) vd 
// ON vd.userid = c.userid




//                 LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "document_upload_type dt ON dt.lead_type = 2 AND dt.orignal_status = 1
//                 LEFT JOIN " . db_prefix() . "currencies cu ON cu.id = c.scholarship_currency
//                 LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = u.fees_payment_currency_id
          
//           LEFT JOIN (
//     SELECT td_latest.*,
//           td_sum.total_ticket_cost
//     FROM " . db_prefix() . "ticket_data td_latest
//     INNER JOIN (
//         SELECT client_id, SUM(IF(ticket_status != 6, ticket_cost, -ticket_cost)) AS total_ticket_cost
//         FROM " . db_prefix() . "ticket_data
//         GROUP BY client_id
//     ) td_sum ON td_latest.client_id = td_sum.client_id
//     INNER JOIN (
//         SELECT client_id, MAX(id) AS latest_id
//         FROM " . db_prefix() . "ticket_data where ticket_status !=6
//         GROUP BY client_id
//     ) td_max ON td_latest.client_id = td_max.client_id 
//             AND td_latest.id = td_max.latest_id
// ) td ON td.client_id = c.userid



//                 LEFT JOIN " . db_prefix() . "vendor_list vl ON vl.id = td.vendor_id
//                 LEFT JOIN " . db_prefix() . "departure_location fl ON fl.id = td.departure_location
//                 LEFT JOIN " . db_prefix() . "ticket_batch tb ON tb.id = td.old_batch_id
//                 LEFT JOIN " . db_prefix() . "pcc_status pcc ON pcc.id = c.pcc_status
                
//               {$apostile_query}  {$apostileSub}
//                 WHERE 1=1 {$condition_sql}
//                 GROUP BY c.userid {$group_by}   Order by c.userid";



//     $sql = "SELECT {$selectColumnName}
//                 FROM " . db_prefix() . "clients c
//                 LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
//                 LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
//                 LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
//                 LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
//                 LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
//                 LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
//                 LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
               
                
//                 LEFT JOIN tbladmission_preferences p 
//                 ON p.userid = c.userid
//                  LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid AND u.status = 1 
//               AND (
//         (u.university_name IS NOT NULL AND p.primary_university = u.university_name)
//         OR (u.university_name IS NULL)
//   )

//                 LEFT JOIN " . db_prefix() . "university_partner u_p ON u_p.id = u.partner
//                 LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
//                 LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
//                 LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
//                 LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
//                 LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
//                 LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
//                 LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
//                 LEFT JOIN " . db_prefix() . "neet_status ns ON ns.id = ad.neet_status 


//  LEFT JOIN (
//     SELECT 
//       userid, 
//       SUM(cost) AS total_visa_cost, 
//       MAX(id) AS latest_id 
//     FROM 
//       tblvisa_details 
//     GROUP BY 
//       userid
//   ) vd_sum ON vd_sum.userid = c.userid 
  
//   LEFT JOIN tblvisa_details vd_latest ON vd_latest.id = vd_sum.latest_id 
//   LEFT JOIN (
//     SELECT 
//       client_id, 
//       SUM(
//         IF(
//           ticket_status != 6, ticket_cost, - ticket_cost
//         )
//       ) AS total_ticket_cost, 
//       MAX(
//         CASE WHEN ticket_status != 6 THEN id END
//       ) AS latest_id 
//     FROM 
//       tblticket_data 
//     GROUP BY 
//       client_id
//   ) td_sum ON td_sum.client_id = c.userid 
//   LEFT JOIN tblticket_data td ON td.id = td_sum.latest_id

//                 LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
//                 LEFT JOIN " . db_prefix() . "document_upload_type dt ON dt.lead_type = 2 AND dt.orignal_status = 1
//                 LEFT JOIN " . db_prefix() . "currencies cu ON cu.id = c.scholarship_currency
//                 LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = u.fees_payment_currency_id
//                 LEFT JOIN " . db_prefix() . "vendor_list vl ON vl.id = td.vendor_id
//                 LEFT JOIN " . db_prefix() . "departure_location fl ON fl.id = td.departure_location
//                 LEFT JOIN " . db_prefix() . "ticket_batch tb ON tb.id = td.old_batch_id
//                 LEFT JOIN " . db_prefix() . "pcc_status pcc ON pcc.id = c.pcc_status
                
//               {$apostile_query}  {$apostileSub}
//                 WHERE 1=1 {$condition_sql}
//               GROUP BY " . ($group_by ?: "c.userid") ;

 

$sql = "SELECT {$selectColumnName}
                FROM " . db_prefix() . "clients c
                LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
                LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
                LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
                LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
                LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
                LEFT JOIN " . db_prefix() . "staff rc ON c.referralCounsollor = rc.staffid
                LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
                LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status

                LEFT JOIN " . db_prefix() . "admission_preferences p
                    ON p.userid = c.userid
                LEFT JOIN " . db_prefix() . "client_university_shortlisting u
                    ON u.client_id = c.userid
                   AND u.status = 1
                   AND (
                        (u.university_name IS NOT NULL AND p.primary_university = u.university_name)
                     OR (u.university_name IS NULL)
                   )
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
                LEFT JOIN " . db_prefix() . "neet_status ns ON ns.id = ad.neet_status

                /* ---------- Visa: latest record + total + vendor + payment_mode ---------- */
                LEFT JOIN (
                    SELECT
                        v.*,
                        v_sum.total_visa_cost,
                        vv.name AS vendor_name,
                        pm.name AS payment_mode_name
                    FROM " . db_prefix() . "visa_details v
                    INNER JOIN (
                        SELECT
                            userid,
                            
                            SUM(
        cost * CASE
            WHEN exchange_rate = 0 THEN 1
            ELSE exchange_rate
        END
    ) AS total_visa_cost,
                            MAX(id)   AS latest_id
                        FROM " . db_prefix() . "visa_details
                        GROUP BY userid
                    ) v_sum
                        ON v_sum.userid = v.userid
                       AND v.id        = v_sum.latest_id
                    LEFT JOIN " . db_prefix() . "vendor_list vv
                        ON vv.id = v.vendor_id
                    LEFT JOIN " . db_prefix() . "payment_mode pm
                        ON pm.id = v.payment_mode
                ) vd ON vd.userid = c.userid

                /* ---------- Tickets: latest non-cancelled + total ---------- */
                LEFT JOIN (
                    SELECT
                        t.*,
                        t_sum.total_ticket_cost
                    FROM " . db_prefix() . "ticket_data t
                    INNER JOIN (
                        SELECT
                            client_id,
                            SUM(IF(ticket_status <> 6, ticket_cost, -ticket_cost)) AS total_ticket_cost,
                            MAX(CASE WHEN ticket_status <> 6 THEN id END)          AS latest_id
                        FROM " . db_prefix() . "ticket_data
                        GROUP BY client_id
                    ) t_sum
                        ON t_sum.client_id = t.client_id
                       AND t.id            = t_sum.latest_id
                ) td ON td.client_id = c.userid

                LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "document_upload_type dt
                    ON dt.lead_type = 2 AND dt.orignal_status = 1
                LEFT JOIN " . db_prefix() . "currencies cu  ON cu.id  = c.scholarship_currency
                LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = u.fees_payment_currency_id
                LEFT JOIN " . db_prefix() . "vendor_list vl       ON vl.id = td.vendor_id
                LEFT JOIN " . db_prefix() . "departure_location fl ON fl.id = td.departure_location
                LEFT JOIN " . db_prefix() . "ticket_batch tb       ON tb.id = td.old_batch_id
                LEFT JOIN " . db_prefix() . "pcc_status pcc        ON pcc.id = c.pcc_status

                {$apostile_query} {$apostileSub}
                WHERE 1=1 {$condition_sql}
                GROUP BY " . ($group_by ?: "c.userid");
                
               $offset = isset($_REQUEST['offset']) ? (int) $_REQUEST['offset'] : 0;
$limit  = isset($_REQUEST['limit'])  ? (int) $_REQUEST['limit']  : 0;

if ($limit > 0) {
    if ($offset < 0) $offset = 0;
    $sql .= " LIMIT " . $offset . ", " . $limit . " ";
}
                
                // Order by c.userid

// SUM(cost) AS total_visa_cost,

        //  if (!empty($apostile_documents_status) && (int) $apostile_documents_status === 1) {
        //      echo $sql; die;
        //  }
        // if (!empty($orignal_documents_status) && (int) $orignal_documents_status === 1) {
        //  echo $sql; die;
        //         }
        // if(!empty($_REQUEST['debug']) && $_REQUEST['debug'] == 1 && $currentId ==11)
        // {
        //  echo $sql; die;
        // }
        
        // if($currentId ==27)
        // {
        //  echo $sql; die;
        // }
        
        
       
        $arrayData = $CI->db->query($sql)->result_array();
//  if($currentId ==36){
//             // echo $currentId;
//             // die;
//       echo "<pre>";
//         print_r($arrayData);
//         }
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
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);

        // Add to final array
        $dataArray[] = [
            "currentId" => $currentId,
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues,

        ];
    }
    
 

    // Output JSON safely
    header('Content-Type: application/json');
    echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}



function syncExcel_neww_($id = "", $currentId = "", $sheetStatus = "")
{
    
    
//           ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

    $CI = &get_instance();
    $CI->db->query("SET SESSION group_concat_max_len = 1000000");

    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids,sequence, orignal_documents_status, excel_type, apostile_documents_status, group_by, application_doc, ignore_join, org, ap, upd, vap")
        ->from(db_prefix() . "excel_data_update")
        ->where("autoSync", 1);

    if (!empty($id))        { $CI->db->where("spreadsheetId", $id); }
    if (!empty($currentId)) { $CI->db->where("id", $currentId); }

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();

    if (!empty($sheetStatus) && $sheetStatus == 1) {
        header('Content-Type: application/json');
        echo json_encode($sheetData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    if (empty($sheetData)) {
        return [];
    }

    // JSON -> [column_id => sequence], sorted
    $seqMap = static function ($json) {
        $out = [];
        if (empty($json)) { return $out; }
        $rows = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($rows)) { return $out; }
        foreach ($rows as $r) {
            if (!isset($r['column_id'], $r['sequence'])) { continue; }
            if (!is_numeric($r['column_id']) || !is_numeric($r['sequence'])) { continue; }
            $out[(int) $r['column_id']] = (float) $r['sequence'];
        }
        asort($out, SORT_NUMERIC);
        return $out;
    };

    // unique, SQL-safe alias
    $alias = static function ($prefix, $name, array &$used) {
        $a = rtrim($prefix . preg_replace('/[^A-Za-z0-9]+/', '_', (string) $name), '_');
        if ($a === '' || $a === rtrim($prefix, '_')) { $a = rtrim($prefix, '_') . '_col'; }
        $base = $a; $i = 2;
        while (isset($used[$a])) { $a = $base . '_' . $i++; }
        $used[$a] = true;
        return $a;
    };

    $delegates = [
        2 => 'leads_excel_sync', 3 => 'sa_excel_sync', 4 => 'fly_excel_sync',
        5 => 'visa_excel_sync',  6 => 'payment_quotations', 7 => 'ex_visa_data',
        8 => 'ex_ticket_data',   9 => 'visitor_logs_data', 10 => 'external_apostile_data',
    ];

    $dataArray = [];

    foreach ($sheetData as $sheet) {

        $type = (int) $sheet['excel_type'];

        if (isset($delegates[$type])) {
            if ($type === 2 || $type === 3) { $delegates[$type]($id); die; }
            $dataArray[] = $delegates[$type]($id);
            continue;
        }
        if ($type !== 1) { continue; }

        $sheetId     = $sheet['id'] ?? null;
        $sheet_name  = $sheet['sheet_name'] ?? null;
        $groupBySql  = $sheet['group_by'] ?? null;
        $ignoreJoin  = (int) ($sheet['ignore_join'] ?? 0);

        $column_ids = array_values(array_unique(array_filter(
            array_map('intval', explode(",", (string) ($sheet['column_ids'] ?? '')))
        )));
        if (empty($column_ids)) { continue; }

        $items = [];                       // ['seq','ord','sql','label']
        $used  = [];
        $baseSeq = array_flip($column_ids);

        /* ---------- base columns ---------- */
        $baseRows = $CI->db->select('id, name, fetch_column_name')
            ->from(db_prefix() . 'excel_column_update')
              ->where_in("id", $column_ids)
            ->order_by("FIELD(id, " . implode(',', $column_ids) . ")", "", false)
            ->get()->result_array();
// echo "<pre>";
// print_r($sheetData);
// die;
     $seqModecolumns = $seqMap($sheet['sequence'] ?? null);

foreach ($baseRows as $r) {
    $expr = trim((string) ($r['fetch_column_name'] ?? ''));
    if ($expr === '') {
        continue;
    }

    $parts = array_values(
        array_filter(
            array_map('trim', splitSqlColumns($expr)),
            'strlen'
        )
    );

    foreach ($parts as $k => $part) {
        $items[] = [
            'seq'   => $seqModecolumns[(int) $r['id']] ?? PHP_INT_MAX,
            'ord'   => $k,
            'sql'   => $part,
            'label' => count($parts) > 1
                ? $r['name'] . ' ' . ($k + 1)
                : $r['name'],
        ];
    }
}
        if (empty($items)) { continue; }

        /* ---------- original + upload documents (org / upd) ---------- */
        if ((int) ($sheet['orignal_documents_status'] ?? 0) === 1) {

            $orgSeq = $seqMap($sheet['org'] ?? null);
            $updSeq = $seqMap($sheet['upd'] ?? null);
            $seqMode = !empty($orgSeq) || !empty($updSeq);

            $orgDocs = $seqMode
                ? (!empty($orgSeq) ? get_orignal_document_list(0,0,0,0,0,0,0, [], [], ['id' => array_keys($orgSeq)]) : [])
                : get_orignal_document_list(0,0,0,0,0,0,0, ['excel_show' => 1]);

            $updDocs = $seqMode
                ? (!empty($updSeq) ? get_documents(2, [], 0, "", [], [db_prefix() . "document_upload_type.id", array_keys($updSeq)]) : [])
                : get_documents(2, [], 0, "", [db_prefix() . 'document_upload_type.orignal_status' => '1']);

            foreach ((array) $orgDocs as $d) {
                $name = trim($d['short_name'] ?? '');
                $did  = (int) ($d['id'] ?? 0);
                if ($did <= 0 || $name === '') { continue; }
                $a = $alias('org_', $name, $used);
                $items[] = [
                    'seq' => $orgSeq[$did] ?? PHP_INT_MAX, 'ord' => 0, 'label' => $a,
                    'sql' => "MAX(CASE WHEN od.short_name = " . $CI->db->escape($name)
                           . " AND (dr.location_id != 12 OR dr.location_id IS NULL) THEN 'YES' ELSE 'NO' END) AS `{$a}`",
                ];
            }

            foreach ((array) $updDocs as $d) {
                $name = trim($d['name'] ?? '');
                $did  = (int) ($d['id'] ?? 0);
                if ($did <= 0 || $name === '') { continue; }
                $a = $alias('upl_', $name, $used);
                $items[] = [
                    'seq' => $updSeq[$did] ?? PHP_INT_MAX, 'ord' => 0, 'label' => $a,
                    'sql' => "CASE
                        WHEN JSON_EXTRACT(CAST(cd.data AS CHAR CHARACTER SET utf8), '$[*].id') IS NOT NULL
                         AND JSON_CONTAINS(JSON_EXTRACT(CAST(cd.data AS CHAR CHARACTER SET utf8), '$[*].id'), JSON_QUOTE('{$did}')) THEN 'YES'
                        WHEN JSON_EXTRACT(CAST(cd.data AS CHAR CHARACTER SET utf8), '$.\"{$did}\".id') IS NOT NULL THEN 'YES'
                        ELSE 'NO' END AS `{$a}`",
                ];
            }

            // $items[] = ['seq' => PHP_INT_MAX, 'ord' => 1, 'label' => 'Invitation_letter',
            //     'sql' => "IF(u.invitation_letter IS NOT NULL AND u.invitation_letter != '', 'Yes', 'No') AS Invitation_letter"];
            // $items[] = ['seq' => PHP_INT_MAX, 'ord' => 2, 'label' => 'Admission_letter',
            //     'sql' => "IF(u.application_file IS NOT NULL AND u.application_file != '', 'Yes', 'No') AS Admission_letter"];
        }

        /* ---------- apostille + visa apostille (ap / vap) ---------- */
        $apostileSub = "";
        if ((int) ($sheet['apostile_documents_status'] ?? 0) === 1) {

            $apostileSub = "LEFT JOIN (
                SELECT ca.userid,
                       GROUP_CONCAT(DISTINCT od.short_name) AS all_docs,
                       GROUP_CONCAT(DISTINCT CASE WHEN ca.received_status = 1 THEN od.short_name END) AS received_docs,
                       GROUP_CONCAT(DISTINCT CASE WHEN ca.by_vendor = 1 THEN od.short_name END) AS by_vendor_docs
                FROM " . db_prefix() . "client_apostille_data ca
                JOIN " . db_prefix() . "orignal_documents od ON od.id = ca.doc_id
                GROUP BY ca.userid
            ) doc_list ON doc_list.userid = c.userid ";

            $apSeq  = $seqMap($sheet['ap']  ?? null);
            $vapSeq = $seqMap($sheet['vap'] ?? null);
            $seqMode = !empty($apSeq) || !empty($vapSeq);

            $apDocs = $seqMode
                ? (!empty($apSeq) ? get_orignal_document_list(0,0,1,0,0,0,0, [], [], ['id' => array_keys($apSeq)]) : [])
                : get_orignal_document_list(0, 0, 1);

            $vapDocs = $seqMode
                ? (!empty($vapSeq) ? get_orignal_document_list(0,0,0,0,0,0,1, ["status" => 0], [], ['id' => array_keys($vapSeq)]) : [])
                : get_orignal_document_list(0, 0, 0, 0, 0, 0, 1, ["status" => 0]);

            // keep each doc paired with the sequence map it came from
            $apostilleDocs = [];
            foreach ((array) $apDocs  as $d) { $apostilleDocs[] = [$d, $apSeq];  }
            foreach ((array) $vapDocs as $d) { $apostilleDocs[] = [$d, $vapSeq]; }

            $subSelect = [];
            foreach ($apostilleDocs as [$d, $map]) {
                $name = trim($d['short_name'] ?? '');
                $did  = (int) ($d['id'] ?? 0);
                if ($did <= 0 || $name === '') { continue; }

                $prefix = (!empty($d['apostile_status']) || !empty($d['visa_apostile'])) ? 'Ap_' : '';
                $a = $alias($prefix, $name, $used);

                $items[] = [
                    'seq' => $map[$did] ?? PHP_INT_MAX, 'ord' => 3, 'label' => $a,
                    'sql' => "IFNULL(doc_status.`{$a}`, 'Pending') AS `{$a}`",
                ];
                $subSelect[] = ", COALESCE(MAX(CASE WHEN doc_id = {$did} THEN "
                             . "(CASE WHEN received_status = 1 THEN 'Received' ELSE 'Sent' END) END), 'Pending') AS `{$a}`";
            }

            if (!empty($subSelect)) {
                $apostileSub .= " LEFT JOIN ( SELECT userid" . implode('', $subSelect)
                              . " FROM " . db_prefix() . "client_apostille_data GROUP BY userid"
                              . " ) doc_status ON doc_status.userid = c.userid ";
            }
        }

        /* ---------- application documents ---------- */
        if ((int) ($sheet['application_doc'] ?? 0) === 1) {
            $appDocs = get_documents(2, [], 0, "", [db_prefix() . 'document_upload_type.application_doc' => '1']);

            foreach ((array) $appDocs as $d) {
                $did  = (int) ($d['id'] ?? 0);
                $name = trim($d['name'] ?? '');
                if ($did <= 0 || $name === '') { continue; }

                $a = $alias('', $name, $used);

                $idRe = '"id"[[:space:]]*:[[:space:]]*"?' . $did . '"?[[:space:]]*[,}]';
                $pair = static function ($v) use ($did, $idRe) {
                    $st = '"approval_status"[[:space:]]*:[[:space:]]*"?' . (int) $v . '"?[[:space:]]*[,}]';
                    return '(' . $idRe . '[^}]*' . $st . '|' . $st . '[^}]*' . $idRe . ')';
                };

                $items[] = [
                    'seq' => PHP_INT_MAX, 'ord' => 4, 'label' => $a,
                    'sql' => "CASE
                        WHEN cd.data REGEXP '" . $pair(1) . "' THEN 'Approved'
                        WHEN cd.data REGEXP '" . $pair(2) . "' THEN 'Rejected'
                        WHEN cd.data REGEXP '" . $idRe . "'   THEN 'Submitted'
                        ELSE 'Pending' END AS `{$a}`",
                ];
            }
        }

        /* ---------- one sort: SELECT list and headers stay aligned ---------- */
        usort($items, static function ($a, $b) {
            return [$a['seq'], $a['ord']] <=> [$b['seq'], $b['ord']];
        });

//         echo "<pre>";
// print_r($items);
// die;

        $selectColumnName = implode(",\n", array_column($items, 'sql'));
        $columns          = array_column($items, 'label');

        /* ---------- conditions ---------- */
        $cond = "";
        if (!empty($sheet['fromDate']) && !empty($sheet['toDate'])) {
            $cond .= " AND (c.datecreated BETWEEN " . $CI->db->escape($sheet['fromDate'])
                   . " AND " . $CI->db->escape($sheet['toDate']) . ")";
        }
        if (!empty($sheet['acadmic_year']) && strpos($sheet['acadmic_year'], '-') !== false) {
            $yr = array_map('trim', explode("-", $sheet['acadmic_year']));
            if (count($yr) >= 2) {
                $cond .= " AND (p.session_intake IN (" . $CI->db->escape($yr[0] . "-09")
                       . "," . $CI->db->escape($yr[1] . "-02") . "))";
            }
        }
        if (!empty($sheet['sql_condition'])) {
            $cond .= " " . $sheet['sql_condition'];
        }

        /* ---------- apostille / translation join ---------- */
        $group_by = "";
        $apostileJoin = "";

        if ($ignoreJoin === 1) {
            $isTrans = ($groupBySql === "translation_summary.id");
            $tbl     = $isTrans ? "client_translation_data" : "client_apostille_data";
            $costCol = $isTrans ? "translation_cost"        : "apostille_cost";
            $recvCol = $isTrans ? "translation_received"    : "apostille_received";
            $sumName = $isTrans ? "translation_summary"     : "apostille_summary";
            $statCol = $isTrans ? "translation_status"      : "apostille_status";

            if (!empty($groupBySql)) {
                $group_by = $groupBySql;
                $apostileJoin = " JOIN (
                    SELECT aps.id, aps.userid,
                           (aps.{$costCol} * CASE WHEN aps.exchange_rate = 0 OR aps.exchange_rate IS NULL THEN 1 ELSE aps.exchange_rate END) AS Total_cost,
                           aps.exchange_rate, aps.{$costCol},
                           aps.courier_date, aps.payment_date, aps.{$recvCol},
                           aps.vendor_id, aps.doc_id, tod.short_name AS doc_name,
                           IF(aps.by_vendor = 1, 'Yes', 'No') AS by_vendor,
                           CASE WHEN aps.id IS NULL THEN 'Pending'
                                WHEN aps.received_status = 0 THEN 'Sent'
                                WHEN aps.received_status = 1 THEN 'Received'
                                ELSE 'Pending' END AS {$statCol},
                           aps.currency_text
                    FROM " . db_prefix() . "{$tbl} aps
                    JOIN " . db_prefix() . "orignal_documents tod ON aps.doc_id = tod.id
                ) AS {$sumName} ON {$sumName}.userid = c.userid";
            } else {
                $apostileJoin = " LEFT JOIN (
                    SELECT userid,
                           SUM(apostille_cost * CASE WHEN exchange_rate = 0 OR exchange_rate IS NULL THEN 1 ELSE exchange_rate END) AS total_cost,
                           MAX(exchange_rate)      AS exchange_rate,
                           MAX(apostille_cost)     AS apostille_cost,
                           MAX(courier_date)       AS courier_date,
                           MAX(payment_date)       AS payment_date,
                           MAX(apostille_received) AS apostille_received,
                           GROUP_CONCAT(vendor_id) AS vendor_id,
                           GROUP_CONCAT(doc_id)    AS doc_id,
                           IF(MAX(by_vendor) = 1, 'Yes', 'No') AS by_vendor,
                           CASE WHEN COUNT(*) = 0 THEN 'Pending'
                                WHEN SUM(received_status = 0) > 0 THEN 'Sent'
                                WHEN SUM(received_status = 1) = COUNT(*) THEN 'Received'
                                ELSE 'Pending' END AS apostille_status
                    FROM " . db_prefix() . "client_apostille_data
                    GROUP BY userid
                ) AS apostille_summary ON apostille_summary.userid = c.userid";
            }
        }

        /* ---------- main query ---------- */
        $sql = "SELECT {$selectColumnName}
            FROM " . db_prefix() . "clients c
            LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
            LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
            LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
            LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
            LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
            LEFT JOIN " . db_prefix() . "staff rc ON c.referralCounsollor = rc.staffid
            LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
            LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
            LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
            LEFT JOIN " . db_prefix() . "client_university_shortlisting u
                ON u.client_id = c.userid AND u.status = 1
               AND ((u.university_name IS NOT NULL AND p.primary_university = u.university_name)
                    OR (u.university_name IS NULL))
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
            LEFT JOIN " . db_prefix() . "neet_status ns ON ns.id = ad.neet_status
            LEFT JOIN (
                SELECT v.*, v_sum.total_visa_cost, vv.name AS vendor_name, pm.name AS payment_mode_name
                FROM " . db_prefix() . "visa_details v
                INNER JOIN (
                    SELECT userid,
                           SUM(cost * CASE WHEN exchange_rate = 0 THEN 1 ELSE exchange_rate END) AS total_visa_cost,
                           MAX(id) AS latest_id
                    FROM " . db_prefix() . "visa_details GROUP BY userid
                ) v_sum ON v_sum.userid = v.userid AND v.id = v_sum.latest_id
                LEFT JOIN " . db_prefix() . "vendor_list vv ON vv.id = v.vendor_id
                LEFT JOIN " . db_prefix() . "payment_mode pm ON pm.id = v.payment_mode
            ) vd ON vd.userid = c.userid
            LEFT JOIN (
                SELECT t.*, t_sum.total_ticket_cost
                FROM " . db_prefix() . "ticket_data t
                INNER JOIN (
                    SELECT client_id,
                           SUM(IF(ticket_status <> 6, ticket_cost, -ticket_cost)) AS total_ticket_cost,
                           MAX(CASE WHEN ticket_status <> 6 THEN id END) AS latest_id
                    FROM " . db_prefix() . "ticket_data GROUP BY client_id
                ) t_sum ON t_sum.client_id = t.client_id AND t.id = t_sum.latest_id
            ) td ON td.client_id = c.userid
            LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
            LEFT JOIN " . db_prefix() . "document_upload_type dt ON dt.lead_type = 2 AND dt.orignal_status = 1
            LEFT JOIN " . db_prefix() . "currencies cu  ON cu.id  = c.scholarship_currency
            LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = u.fees_payment_currency_id
            LEFT JOIN " . db_prefix() . "vendor_list vl        ON vl.id  = td.vendor_id
            LEFT JOIN " . db_prefix() . "departure_location fl ON fl.id  = td.departure_location
            LEFT JOIN " . db_prefix() . "ticket_batch tb       ON tb.id  = td.old_batch_id
            LEFT JOIN " . db_prefix() . "pcc_status pcc        ON pcc.id = c.pcc_status
            {$apostileJoin} {$apostileSub}
            WHERE 1=1 {$cond}
            GROUP BY " . ($group_by ?: "c.userid");

        $offset = isset($_REQUEST['offset']) ? max(0, (int) $_REQUEST['offset']) : 0;
        $limit  = isset($_REQUEST['limit'])  ? (int) $_REQUEST['limit'] : 0;
        if ($limit > 0) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
// print_r($_REQUEST);
// die;

// print_r($offset);
        $dataArray[] = [
            "currentId"     => $sheetId,
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => array_map('array_values', $CI->db->query($sql)->result_array()),
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fly_excel_sync($id = "")
{

    $CI = &get_instance();

    // $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    $CI->db->query("SET SESSION sql_mode = ''");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 4)
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
            $condition_sql .= " AND (c.datecreated BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        if (!empty($acadmic_year)) {
            // $condition_sql .= " AND (p.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";


            // safer split (handles spaces correctly)
            list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

             $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ") )";
                
                //   $condition_sql .= " AND p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ")";
        }
        $condition_sql = "";
        $condition_sql .= " AND ((l.type = 2 OR l.type IS NULL) OR c.client_type = 2)  and c.userid IS NOT NULL ";


        // INNER JOIN (
        //     SELECT td_latest.*,
        //           td_sum.total_ticket_cost
        //     FROM " . db_prefix() . "ticket_data td_latest
        //     INNER JOIN (
        //         SELECT client_id, SUM(IF(ticket_status != 6, ticket_cost, -ticket_cost)) AS total_ticket_cost
        //         FROM " . db_prefix() . "ticket_data
        //         GROUP BY client_id
        //     ) td_sum ON td_latest.client_id = td_sum.client_id
        //     INNER JOIN (
        //         SELECT client_id, MAX(id) AS latest_id
        //         FROM " . db_prefix() . "ticket_data
        //         GROUP BY client_id
        //     ) td_max ON td_latest.client_id = td_max.client_id 
        //             AND td_latest.id = td_max.latest_id
        // ) td ON td.client_id = c.userid


        $sql = "SELECT {$selectColumnName}
FROM " . db_prefix() . "clients c

 JOIN " . db_prefix() . "ticket_data td ON td.client_id = c.userid
LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
 JOIN " . db_prefix() . "basic_details b ON b.userid = c.userid
LEFT JOIN " . db_prefix() . "applicant_status aps ON aps.id = c.active
LEFT JOIN " . db_prefix() . "leads l ON (l.id = c.leadid AND l.type = 2)
LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
LEFT JOIN " . db_prefix() . "client_university_shortlisting us ON (us.client_id = c.userid AND us.status = 1)
LEFT JOIN " . db_prefix() . "application_status appst ON appst.id = us.application_status
LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
LEFT JOIN " . db_prefix() . "visa_details vd ON vd.userid = c.userid
LEFT JOIN " . db_prefix() . "visa_status vs ON vs.id = vd.status
LEFT JOIN " . db_prefix() . "ticket_status ts ON ts.id = td.ticket_status
LEFT JOIN " . db_prefix() . "departure_location dl ON dl.id = td.departure_location
LEFT JOIN " . db_prefix() . "ticket_batch tb ON tb.id = td.old_batch_id
LEFT JOIN " . db_prefix() . "vendor_list vl ON vl.id = td.vendor_id
LEFT JOIN " . db_prefix() . "departure_location tdl ON tdl.id = td.departure_location
LEFT JOIN " . db_prefix() . "payment_mode pm ON pm.id = td.payment_mode

WHERE 1=1 AND c.client_type in (1,2) {$condition_sql}
GROUP BY c.userid,td.id ";


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

        $arrayDataValues = array_map('array_values', $arrayData);
        // Update last sync
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);
        // Add to final array
        return  $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
        //      header('Content-Type: application/json');
        // echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // exit;
    }
}



function visa_excel_sync($id = "")
{

    $CI = &get_instance();

    // $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    $CI->db->query("SET SESSION sql_mode = ''");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 5)
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
            $condition_sql .= " AND (c.datecreated BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        if (!empty($acadmic_year)) {
            // $condition_sql .= " AND (p.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";


            // safer split (handles spaces correctly)
            list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

           $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ") )";


            // $condition_sql .= " AND (p.session_intake = " . $CI->db->escape($first_semester) .
            //     " OR p.session_intake = " . $CI->db->escape($second_semester) . ")";
            
            //   $condition_sql .= " AND p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ") ";
        }
        $condition_sql = "";
        $condition_sql .= " AND ((l.type = 2 OR l.type IS NULL) OR c.client_type = 2)  and c.userid IS NOT NULL ";

        $sql = "SELECT {$selectColumnName}
FROM " . db_prefix() . "visa_details vd
LEFT JOIN " . db_prefix() . "clients c ON c.userid = vd.userid
 LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
  LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
LEFT JOIN " . db_prefix() . "basic_details b ON b.userid = c.userid
LEFT JOIN " . db_prefix() . "applicant_status aps ON aps.id = c.active
LEFT JOIN " . db_prefix() . "leads l ON (l.id = c.leadid AND l.type =2)
LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
LEFT JOIN " . db_prefix() . "client_university_shortlisting us ON (us.client_id = c.userid AND us.status = 1)
LEFT JOIN " . db_prefix() . "application_status appst ON appst.id = us.application_status
LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
 LEFT JOIN " . db_prefix() . "payment_mode pm ON pm.id = vd.payment_mode
LEFT JOIN " . db_prefix() . "visa_status vs ON vs.id = vd.status
LEFT JOIN " . db_prefix() . "vendor_list vl ON vl.id = vd.vendor_id
LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = vd.currency_type

WHERE 1=1 {$condition_sql}
GROUP BY c.userid,vd.id";




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

        $arrayDataValues = array_map('array_values', $arrayData);
        // Update last sync
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);
        // Add to final array
        return  $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
        //      header('Content-Type: application/json');
        // echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // exit;
    }
}

function sa_excel_sync($id = "")
{

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
            // $condition_sql .= " AND (" . db_prefix() . "admission_preferences.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";


            // safer split (handles spaces correctly)
            list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

            // build semester codes
             $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( " . db_prefix() . "admission_preferences.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ") )";


            // $condition_sql .= " AND (" . db_prefix() . "admission_preferences.session_intake = " . $CI->db->escape($first_semester) .
            //     " OR " . db_prefix() . "admission_preferences.session_intake = " . $CI->db->escape($second_semester) . ")";
                
                //   $condition_sql .= " AND admission_preferences.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ")";
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
LEFT JOIN (SELECT td1.*, td2.total_cost FROM " . db_prefix() . "ticket_data td1 INNER JOIN (SELECT MAX(id) AS max_id, SUM(IF(ticket_status != 6, ticket_cost, -ticket_cost)) total_cost FROM " . db_prefix() . "ticket_data GROUP BY client_id) td2 ON td1.id = td2.max_id) td ON td.client_id = " . db_prefix() . "clients.userid
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

  if(!empty($_REQUEST['debug']) && $_REQUEST['debug'] == 1)
        {
         echo $sql; die;
        }

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
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);

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
            // $CI->db->where('id', $currentId)
            //     ->update(db_prefix() . "excel_data_update", [
            //         'lastSync' => date('Y-m-d H:i:s')
            //     ]);

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

function ma_quotations()
{
    $condition_sql = "";

    $CI = &get_instance();
    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");
    $acadmic_year = "2025 - 2026";
    
    if(!empty($_GET['year']))
    {
       $acadmic_year = $_GET['year'];
    }

    if (!empty($acadmic_year)) {

        // $condition_sql .= " AND (p.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";


        // extract start & end years



        // safer split (handles spaces correctly)
        list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

        // build semester codes
        $first_semester  = $start . "-09";
        $second_semester = $end . "-02";


       $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ") )";
       
        // $condition_sql .= " AND (p.session_intake = " . $CI->db->escape($first_semester) .
        //     " OR p.session_intake = " . $CI->db->escape($second_semester) . ")";
            
            //  $condition_sql .= " AND p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . ") ";
    }
    $columns = [
        "Applicant Name",
        "University Name",
        "Acadmic Year",
        "Year",
        "Status",
        "App stage",
        "App Sub Stage",
        "Owner",
        "Counsellor Name",
        "Release to Counsollor",
        "Dues",
        "Fees",
        "Currency",
        "Amount",
        "Payment Mode",
        "Exchange Value",
        "INR Values",
        "Pay Mode",
        "Pay Vendor",
        "Quotation Label",
        "USD Rate",
        "Fly Batch",
        "Fly Date",
        "Departure",
        "Country",
        "Transaction Type"
    ];
    $sheet_name = "Quotation";

    $condition_sql .= " AND ((l.type = 2 OR l.type IS NULL) OR c.client_type = 2)  ";
    try {
        // ✅ Correct SQL (removed trailing comma before FROM)
        $sql = "
            SELECT 
                CONCAT(bd.first_name,' ', bd.last_name) AS applicant_name,
                p.primary_university as university_name,
                aq.acadmic_year,
                CONCAT(aq.year, ' Year') AS year,
                aq.release_to_counsellor,
                aq.exchange_value,
                aq.university_due,
                aq.company_due,
                CONCAT('Q', ROW_NUMBER() OVER (
                    PARTITION BY aq.university_name, aq.client_id 
                    ORDER BY aq.id ASC
                )) AS quotation_label,
                 s.name student_status,
                 ts.name sub_stage,
                 CONCAT(tt.id,' ',tt.name) application_stage,
                 if(c.client_type=1,'EV','EVP') as client_type,
                 if(c.client_type=1,CONCAT(st.firstname,' ',st.lastname),evp.name) as counsellor_name,
                 tb.name as batch_name,
                DATE_FORMAT(
                IF(td.fly_date IS NOT NULL AND td.fly_date != '0000-00-00',
                td.fly_date,
                NULL
                ), '%d-%m-%Y'
                ) AS fly_date,
                fl.name as departure,
                p.primary_country
               

                 ev_partner
           FROM " . db_prefix() . "applicant_quotation_payment aq
LEFT JOIN " . db_prefix() . "basic_details bd ON aq.client_id = bd.userid
LEFT JOIN " . db_prefix() . "clients c ON c.userid = aq.client_id
LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
LEFT JOIN (
    SELECT t1.*
    FROM " . db_prefix() . "ticket_data t1
    INNER JOIN (
        SELECT client_id, MAX(id) AS latest_id
        FROM " . db_prefix() . "ticket_data
        GROUP BY client_id
    ) t2 ON t1.client_id = t2.client_id AND t1.id = t2.latest_id
) td ON td.client_id = aq.client_id
LEFT JOIN " . db_prefix() . "departure_location fl ON fl.id = td.departure_location
LEFT JOIN " . db_prefix() . "ticket_batch tb ON tb.id = td.batch_id
LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
LEFT JOIN " . db_prefix() . "ev_partner evp 
    ON evp.id = c.agent_id
WHERE 1=1 
  AND aq.status = 1
  {$condition_sql}
GROUP BY aq.id

               
        ";


        $arrayData = $CI->db->query($sql)->result_array();


        $dataArray = [[
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayData,
            "company_dues_name" => array_column($CI->db->select("id,name")
                ->from(db_prefix() . "company_dues_fees")
                ->get()->result_array(), null, "id"),
            "quotation_mode" => array_column($CI->db->select("*")
                ->from(db_prefix() . "quotation_mode")
                ->get()->result_array(), null, "id"),
            "quotation_vendors" => array_column($CI->db->select("id,name")
                ->from(db_prefix() . "quotation_vendor")
                ->where("status", 1)
                ->get()->result_array(), null, "id"),
            "currency" => array_column($CI->db->select("id,name,symbol")
                ->from(db_prefix() . "currencies")
                ->order_by("isdefault", "DESC")
                ->order_by("id", "ASC")
                ->get()->result_array(), null, "id"),
            "quotation_payment_mode" => array_column($CI->db->select("*")
                ->from(db_prefix() . "quotation_paymente_mode")
                ->get()->result_array(), null, "id"),
            "company_dues_name" => array_column($CI->db->select("*")
                ->from(db_prefix() . "company_dues_fees")
                ->get()->result_array(), null, "id"),
            "transaction_type" => array_column($CI->db->select("*")
                ->from(db_prefix() . "transaction_type")
                ->get()->result_array(), null, "id"),

        ]];

        header('Content-Type: application/json');
        echo json_encode($dataArray);
        die;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => 0,
            "message" => "Error: " . $e->getMessage(),
            "data" => []
        ]);
    }

    exit;
}


function paymentDues()
{

    $CI = &get_instance();
    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");
    // fetch fees with lead_type as well
    $feesList = $CI->db->select("id, name")
        ->from(db_prefix() . "applicant_fees")->where_in("id", [1, 3, 5, 6, 7])
        ->order_by("sequence", "ASC")
        ->get()
        ->result_array();

    $columns = [
        "Owner",
        "Country",
        "Primary University",
        "App Process Stage",
        "Student Name",
        "Counsellor Name"
    ];

    $normal = $pay = $dues = [];

    foreach ($feesList as $fee) {
        $normal[] = $fee['name'];
        $pay[]    = "Pay " . $fee['name'];
        $dues[]   = "Dues " . $fee['name'];
    }

    $columns = array_merge($columns, $normal, $pay, $dues);





    $sheet_name = "Payment Dues";

    $sql = "
SELECT 
    IF(c.client_type = 2, 'EVP', 'EV') AS owner,
    ap.primary_country,
    ap.primary_university,
    CONCAT(c.applicant_stage, ' ', tt.name) AS app_process_stage,
    CONCAT(bd.first_name, ' ', bd.last_name) AS student_name,

    CONCAT('[', GROUP_CONCAT(
        DISTINCT JSON_OBJECT(
            'fees_id', fd.fees_id,
            'amount', fd.amount,
            'currency_id', fd.currency_id
        )
    ), ']') AS fees_details_json,


    CONCAT('[', GROUP_CONCAT(
        DISTINCT JSON_OBJECT(
            'pay_id', pq.id,
            'fees_id', pq.payment_type,
            'amount', pq.amount,
            'currency_id', pq.ex_currency,
            'fess_infomation',pq.fess_infomation
        )
    ), ']') AS payment_details_json,


    CONCAT('[', GROUP_CONCAT(
    DISTINCT JSON_OBJECT(
        'fees_id', fd.fees_id,
        'amount', fd.amount - IF(fd.fees_id != " . RETURN_FEES_ID . ", IFNULL(pq.amount, 0), 0),
        'currency_id', fd.currency_id
    )
), ']') AS due_details_json,


    IF(c.client_type = 2, evp.name, CONCAT(st.firstname, ' ', st.lastname)) AS counsellor_name

FROM " . db_prefix() . "applicant_fees_details fd
 JOIN " . db_prefix() . "clients c 
    ON fd.client_id = c.userid
LEFT JOIN " . db_prefix() . "basic_details bd 
    ON bd.userid = fd.client_id  
LEFT JOIN " . db_prefix() . "admission_preferences ap 
    ON ap.userid = fd.client_id
LEFT JOIN " . db_prefix() . "applicant_stages tt 
    ON tt.id = c.applicant_stage
LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts 
    ON ts.id = c.applicant_sub_status  
LEFT JOIN " . db_prefix() . "leads l 
    ON c.leadid = l.id
LEFT JOIN " . db_prefix() . "payment_quotations pq 
    ON pq.client_id = fd.client_id and pq.status > 0
LEFT JOIN " . db_prefix() . "staff st 
    ON st.staffid = l.assigned
LEFT JOIN " . db_prefix() . "ev_partner evp 
    ON evp.id = c.agent_id

WHERE  (l.type = 2  OR l.type IS NULL OR c.client_type = 2)  and  pq.status > 0 
GROUP BY fd.client_id
";


    // $sql = "
    // WITH payment_flat AS (
    //     SELECT 
    //         pq.client_id,
    //         CAST(JSON_EXTRACT(fee_item, '$.fee_id') AS UNSIGNED) AS fee_id,
    //         CAST(JSON_EXTRACT(fee_item, '$.fee_inr_value') AS DECIMAL(18,2)) AS fee_inr_value,
    //         TRIM(BOTH '\"' FROM JSON_UNQUOTE(JSON_EXTRACT(fee_item, '$.fee_currency'))) AS fee_currency
    //     FROM tblpayment_quotations pq
    //     CROSS JOIN JSON_TABLE(
    //         pq.fess_infomation, 
    //         '$[*]' 
    //         COLUMNS (
    //             fee_item JSON PATH '$'
    //         )
    //     ) AS jt
    //     WHERE pq.status > 0 
    //       AND pq.payment_type != 16
    // ),
    // agg_fees AS (
    //     -- Sum by client_id, fee_id, currency to make them unique
    //     SELECT 
    //         client_id, 
    //         fee_id, 
    //         fee_currency, 
    //         SUM(fee_inr_value) AS total_inr
    //     FROM payment_flat
    //     GROUP BY client_id, fee_id, fee_currency
    // ),
    // merged_json AS (
    //     -- Convert to JSON array
    //     SELECT 
    //         client_id,
    //         JSON_ARRAYAGG(
    //             JSON_OBJECT(
    //                 'fee_id', fee_id,
    //                 'fee_currency', fee_currency,
    //                 'fee_inr_value', total_inr
    //             )
    //         ) AS merged_fees_json
    //     FROM agg_fees
    //     GROUP BY client_id
    // )
    // SELECT
    //     c.userid AS client_id,
    //     IF(c.client_type = 2, 'EVP', 'EV') AS owner,
    //     CONCAT(c.applicant_stage, ' ', tt.name) AS app_process_stage,
    //     CONCAT(bd.first_name, ' ', bd.last_name) AS student_name,
    //     ap.primary_country,
    //     ap.primary_university,
    //     IF(c.client_type = 2, evp.name, CONCAT(st.firstname, ' ', st.lastname)) AS counsellor_name,
    //     pq.payment_type,
    //     CONCAT(
    //         '[', 
    //         GROUP_CONCAT(
    //             DISTINCT JSON_OBJECT(
    //                 'fees_id', fd.fees_id,
    //                 'amount', fd.amount,
    //                 'currency_id', fd.currency_id
    //             )
    //         ), 
    //         ']'
    //     ) AS fees_details_json,
    //     mj.merged_fees_json AS payment_details_json
    // FROM tblclients c
    // LEFT JOIN tblbasic_details bd ON bd.userid = c.userid
    // LEFT JOIN tbladmission_preferences ap ON ap.userid = c.userid
    // LEFT JOIN tblapplicant_stages tt ON tt.id = c.applicant_stage
    // LEFT JOIN tblleads l ON c.leadid = l.id
    // LEFT JOIN tblstaff st ON st.staffid = l.assigned
    // LEFT JOIN tblev_partner evp ON evp.id = c.agent_id
    // LEFT JOIN tblpayment_quotations pq ON pq.client_id = c.userid AND pq.status > 0
    // LEFT JOIN tblapplicant_fees_details fd ON fd.client_id = c.userid
    // JOIN merged_json mj ON mj.client_id = c.userid
    // WHERE (l.type = 2 OR l.type IS NULL OR c.client_type = 2)
    //   AND c.userid = 863
    // GROUP BY 
    //     c.userid;
    // ";



    $arrayData = $CI->db->query($sql)->result_array();


    $dataArray = [[
        "columnName"    => $columns,
        "workSheetName" => $sheet_name,
        "rowData"       => $arrayData,
        "currency" => array_column($CI->db->select("id,name,symbol")
            ->from(db_prefix() . "currencies")
            ->order_by("isdefault", "DESC")
            ->order_by("id", "ASC")
            ->get()->result_array(), null, "id"),
        "fess_type" => $feesList
    ]];

    header('Content-Type: application/json');
    echo json_encode($dataArray);
    die;
}
function payment_quotations($id = '')
{

    $CI = &get_instance();

    // $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    $CI->db->query("SET SESSION sql_mode = ''");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition, column_ids, orignal_documents_status")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 6)
        ->where("autoSync", 1);

    if (!empty($id)) {
        $CI->db->where("spreadsheetId", $id);
    }

    $sheetData = $CI->db->order_by("id", "asc")->get()->result_array();


    $get_currencies = get_currencies();
    $get_currencies = array_column($get_currencies, null, 'id');

    $university_applicant_fees_payments = university_applicant_fees_payments();
    $university_applicant_fees_payments = array_column($university_applicant_fees_payments, null, 'id');
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
            $condition_sql .= " AND (c.datecreated BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        if (!empty($acadmic_year)) {
            // $condition_sql .= " AND (p.acadmic_year = " . $CI->db->escape($acadmic_year) . ")";
            
             list($start, $end) = array_map('trim', explode(" - ", $acadmic_year));

            // build semester codes
            $first_semester  = $start . "-09";
            $second_semester = $end . "-02";

            $condition_sql .= " AND ( p.session_intake IN (" . $CI->db->escape($first_semester) .",". $CI->db->escape($second_semester) . "))";
        }
        // $condition_sql = "";
        $condition_sql .= " AND ((l.type = 2 OR l.type IS NULL) OR c.client_type = 2)  and c.userid IS NOT NULL ";

        $sql = "
        SELECT 
        {$selectColumnName},pq.exchange_value,fess_infomation,IF(c.air_ticket_include = 1, 'Yes', 'No') as air_ticket_include
        FROM `" . db_prefix() . "payment_quotations` pq 
        LEFT JOIN " . db_prefix() . "clients c ON pq.client_id = c.userid 
        LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
         LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
        LEFT JOIN " . db_prefix() . "applicant_quotation_payment aqp ON ( aqp.id = pq.quotation_id and aqp.status = 1 )
        LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
        LEFT JOIN " . db_prefix() . "quotation_mode m ON m.id = pq.mode 
        LEFT JOIN " . db_prefix() . "basic_details b ON b.userid = pq.client_id 
        JOIN " . db_prefix() . "applicant_fees f ON f.id = pq.payment_type  
        LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
        LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status 
        LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid 
        LEFT JOIN " . db_prefix() . "office_location lo ON lo.id = pq.location_id  
        LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid 
        LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status 
        LEFT JOIN " . db_prefix() . "staff rc ON c.referralCounsollor = rc.staffid
        
                LEFT JOIN  " . db_prefix() . "admission_preferences p 
                ON p.userid = pq.client_id 
             
   LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = pq.client_id 
        AND u.status = 1 
         AND (
        (u.university_name IS NOT NULL AND p.primary_university = u.university_name)
        OR (u.university_name IS NULL)
   )
       
        LEFT JOIN " . db_prefix() . "university_partner u_p ON u_p.id = u.partner 
        LEFT JOIN " . db_prefix() . "quotation_vendor vl ON vl.id = pq.vendor_id 
        LEFT JOIN " . db_prefix() . "currencies ctf ON ctf.id = pq.ex_currency
        LEFT JOIN " . db_prefix() . "transaction_type ptt ON ptt.id = pq.transaction_type
        
        LEFT JOIN (
        SELECT td_latest.*,
        td_sum.total_ticket_cost
        FROM " . db_prefix() . "ticket_data td_latest
        INNER JOIN (
        SELECT client_id, SUM(IF(ticket_status != 6, ticket_cost, -ticket_cost)) AS total_ticket_cost
        FROM " . db_prefix() . "ticket_data
        GROUP BY client_id
        ) td_sum ON td_latest.client_id = td_sum.client_id
        INNER JOIN (
        SELECT client_id, MAX(id) AS latest_id
        FROM " . db_prefix() . "ticket_data
        GROUP BY client_id
        ) td_max ON td_latest.client_id = td_max.client_id 
        AND td_latest.id = td_max.latest_id
        ) td ON td.client_id = c.userid
        LEFT JOIN " . db_prefix() . "ticket_batch tb ON tb.id = td.batch_id
        WHERE 1=1 and pq.status > 0  {$condition_sql}
        GROUP BY pq.id ORDER BY pq.client_id
        
        ";



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

        $arrayDataValues = array_map('array_values', $arrayData);
        // Update last sync
        // $CI->db->where('id', $currentId);
        // $CI->db->update(db_prefix() . "excel_data_update", [
        //     'lastSync' => date('Y-m-d H:i:s')
        // ]);
        // Add to final array
        return  $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues,
            "get_currencies" => $get_currencies,
            "university_applicant_fees_payments" => $university_applicant_fees_payments
        ];
        //      header('Content-Type: application/json');
        // echo json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // exit;
    }
}


function paymentDuesHostel()
{


    $country = (!empty($_GET['country']) && is_string($_GET['country'])) ? $_GET['country'] : '';
    $CI = &get_instance();
    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");
    // fetch fees with lead_type as well
    $feesList = [];
    $feesList["g"] = $CI->db->select("id, name")
        ->from(db_prefix() . "applicant_fees")->where("georgia_hostel", 1)
        ->order_by("sequence", "ASC")
        ->get()
        ->result_array();



    $feesList["r"] = $CI->db->select("id, name")
        ->from(db_prefix() . "applicant_fees")->where("russia_hostel", 1)
        ->order_by("sequence", "ASC")
        ->get()
        ->result_array();

    $today = date('Y-m-d');

    $columns['g'] = [
        "Student Name",
        "Passport",
        "University Name",
        "Company",
        "Hostel Type",
        "Hostel Name",
        "Floor No",
        "Room No",
        "Room Capacity",
        // "Rent",
        // "Currency",
        "Start Date",
        "End Date",
        "Months",
        // "Payment Mode",
        // "Payment Type",
        // "Transaction Type",
        // "Vendor Name",
        "Remark",
        "Active Status",
    ];

    $columns['r'] = [
        "Student Name",
        "Passport",
        "University Name",
        "Company",
        "Hostel Type",
        "Hostel Name",
        // "Payment Mode",
        // "Payment Type",
        "Trans. Type",
        "Vendor Name",
        "Month",
        "Remark",
        "Active Status",
    ];

    // $columns = [
    //     "Student Name",
    //     "Passport",
    //     "University Name",
    //     "Hostel Type",
    //     "Hostel Name",
    //     "Floor No",
    //     "Room No",
    //     "Room Capacity",
    //     // "Rent",
    //     // "Currency",
    //     "Start Date",
    //     "End Date",
    //     "Months",
    //     // "Payment Mode",
    //     // "Payment Type",
    //     // "Transaction Type",
    //     // "Vendor Name",
    //     "Remark"
    // ];



    $normal = $pay = $dues = [];
    foreach ($feesList as $fees) {
        foreach ($fees as $fee) {
            $normal[] = $fee['name'];
            $pay[]    = "Pay " . $fee['name'];
            $dues[]   = "Dues " . $fee['name'];
        }
    }

    $columns = array_merge($columns, $normal, $pay, $dues);


    $today = date('Y-m-d');

    $sheet_name = "Hostel Payment Dues";

    $active_status_sql = " ,  CASE 
        WHEN LOWER(ho.hostel_type) = 'russia' THEN
            CASE 
                WHEN hp1.id > 0 THEN 'YES'
                ELSE 'No'
            END

        WHEN LOWER(ho.hostel_type) = 'georgia' THEN
            CASE 
                WHEN hp1.start_date IS NOT NULL 
                 AND hp1.end_date IS NOT NULL
                 AND '$today' BETWEEN hp1.start_date AND hp1.end_date
                THEN 'YES'
                ELSE 'NO'
            END

        ELSE 'N/A'
    END AS active_status ";



    $sql = "SELECT 
    ho.id AS student_id,
    ho.acadmic_year AS acadmic_year,
    ho.passport AS passport,
    ho.name AS student_name,
    hq.id AS quotation_id,
    hq.university_name,
    hq.room_no,
    hq.floor_no,
    h.name AS hostel_type,
    h.hostel_name AS hostel_name,
    hq.room_capacity,
    ho.rent_amount,
    c.name AS currency_name,
    hq.start_date,
    hq.end_date,
    hq.hostel_due,
    hq.pdf,
    m.name AS mode,
    hp1.id as payment_id,
    hp1.vendor_id,
IF(
    hp1.vendor_id > 0,
    IF(hp1.mode = 3, hv.name, vl.name),
    hp1.vendor_name
) AS vendor_name,
    hp1.type,
    hp1.pay_date,
    hp1.pay_date,
    hp1.remark,
    tpt.name AS transaction_type,
    hp1.acadmic_year AS acadmic_year,
    hp1.year AS year,
    MONTHNAME(hp1.pay_date) AS month,
    dl.name as location,
    hp1.ex_currency,
    hp1.amount,
    ho.hostel_type as country_hostel_type,
    hc.name as company_name,
    GREATEST(
    1,
    TIMESTAMPDIFF(MONTH, hq.start_date, hq.end_date)
    + (DAY(hq.end_date) >= DAY(hq.start_date))
) AS month_difference,

    JSON_ARRAYAGG(
        JSON_MERGE_PATCH(
            CAST(fees_table.fee AS JSON),
            JSON_OBJECT('payment_type', hp1.payment_type)
        )
    ) AS fess_infomation
    
    $active_status_sql

FROM tblhostel_infomation ho
LEFT JOIN tblhostel h 
    ON ho.hostel = h.id
    
LEFT JOIN tblhostel_quotation hq 
    ON ho.id = hq.hostel_info_id AND hq.status > 0
    LEFT JOIN tblhostel_company hc 
    ON hc.id = hq.company 
LEFT JOIN tblhostel_payments hp1 
    ON hp1.quotation_id = hq.id AND hp1.status > 0
LEFT JOIN tbl_hostel_vendors hv 
    ON hv.id = hp1.vendor_id
LEFT JOIN tblquotation_mode m 
    ON m.id = hp1.mode
LEFT JOIN tblcurrencies c 
    ON c.id = ho.currency
LEFT JOIN tblapplicant_fees f 
    ON f.id = hp1.payment_type  
LEFT JOIN tbltransaction_type tpt 
    ON tpt.id = hp1.transaction_type
    LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = hp1.location_id
LEFT JOIN " . db_prefix() . "quotation_vendor vl ON vl.id = hp1.vendor_id 

LEFT JOIN LATERAL (
    SELECT fee
    FROM JSON_TABLE(
        hp1.fess_infomation,
        '$[*]' COLUMNS (fee JSON PATH '$')
    ) AS jt
) AS fees_table ON TRUE

WHERE ho.status = 1";

  if (!empty($country)) {
        $sql .= " AND ho.hostel_type = " . $CI->db->escape($country) . " ";
    }


    if (!empty($_GET["group_by"]) && is_string($_GET["group_by"])) {
        $sql .= " " . $_GET["group_by"] . " ";
    } else {
        $sql .= " GROUP BY ho.id ";
    }
    $sql .= " ORDER BY ho.id  ASC";

    $arrayData = $CI->db->query($sql)->result_array();

    $dataArray = [[
        "columnName"    => $columns,
        "workSheetName" => $sheet_name,
        "rowData"       => $arrayData,
        "currency" => array_column($CI->db->select("id,name,symbol")
            ->from(db_prefix() . "currencies")
            ->order_by("isdefault", "DESC")
            ->order_by("id", "ASC")
            ->get()->result_array(), null, "id"),
        "fess_type" => $feesList
    ]];

    header('Content-Type: application/json');
    echo json_encode($dataArray);
    die;
}


function ex_visa_data($id)
{
    

    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $sheetData = $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, sheet_name, sql_condition, column_ids")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 7)
        ->where("autoSync", 1)
         ->where("spreadsheetId", $id)
        
        ->order_by("id", "asc")
        ->get()
        ->result_array();

    $dataArray = [];


    foreach ($sheetData as $sheet) {
        $currentId     = $sheet['id'] ?? null;
        $fromDate      = $sheet['fromDate'] ?? null;
        $toDate        = $sheet['toDate'] ?? null;
        $spreadsheetId = $sheet['spreadsheetId'] ?? null;
        $sheet_name    = $sheet['sheet_name'] ?? null;

        // Parse column IDs
        $column_ids_raw = $sheet['column_ids'] ?? '';
        $column_ids = (is_string($column_ids_raw) && trim($column_ids_raw) !== '')
            ? array_map('intval', explode(",", $column_ids_raw))
            : [];

        if (empty($column_ids)) {
            continue; // skip if no columns configured
        }
        $orderColumns = implode(',', $column_ids);
        // Fetch column names in correct order

        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, $orderColumns)) AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->get()
            ->row()
            ->fetch_column_name ?? '';
        if (empty($selectColumnName)) {
            continue;
        }
        // Build conditions
        $condition_sql = "";
        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND (vd.payment_date BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }

   
        $sql = "
    SELECT 
        {$selectColumnName}
    FROM `" . db_prefix() . "external_visa_data` vd
    LEFT JOIN `" . db_prefix() . "external_visa_type` vt 
        ON vd.visa_type = vt.id
    LEFT JOIN `" . db_prefix() . "external_visa_status` vs 
        ON vd.visa_status = vs.id
    LEFT JOIN `" . db_prefix() . "external_visa_vendor` v 
        ON vd.visa_vendor = v.id
    LEFT JOIN `" . db_prefix() . "external_payment_mode` m 
    on m.id = vd.payment_mode
    WHERE 1=1 {$condition_sql}
    GROUP BY vd.id
    ORDER BY vd.id DESC
";

        $arrayData = $CI->db->query($sql)->result_array();

        // Get column names
        $sheetColumnName = $CI->db->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id, {$orderColumns})", "", false)
            ->get()
            ->result_array();
        $columns = array_column($sheetColumnName, "name");


        // Update last sync
        // $CI->db->where('id', $currentId)
        //     ->update(db_prefix() . "excel_data_update", [
        //         'lastSync' => date('Y-m-d H:i:s')
        //     ]);
        // Add to final array
        
                $arrayDataValues = [];
        foreach ($arrayData as $row) {
            $valuesOnly = [];
            foreach ($row as $v) {
                $valuesOnly[] = $v === null ? '' : $v; // Replace null with blank
            }
            $arrayDataValues[] = $valuesOnly;
        }
       return  $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
    }
    // header('Content-Type: application/json');
    // echo json_encode($dataArray);
    // exit;
}
function external_apostile_data($id)
{
    $CI = &get_instance();
 
    $CI->db->query("SET SESSION group_concat_max_len = 1000000000");
 
    // Fetch sheet config(s)
    $sheetData = $CI->db
        ->select("id, spreadsheetId, fromDate, toDate, autoSync, sheet_name, sql_condition, column_ids")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 10)
        ->where("autoSync", 1)
        ->where("spreadsheetId", $id)
        ->order_by("id", "asc")
        ->get()
        ->result_array();
 
    $dataArray = [];
 
    foreach ($sheetData as $sheet) {
 
        $currentId     = $sheet['id'] ?? null;
        $fromDate      = $sheet['fromDate'] ?? null;
        $toDate        = $sheet['toDate'] ?? null;
        $spreadsheetId = $sheet['spreadsheetId'] ?? null;
        $sheet_name    = $sheet['sheet_name'] ?? null;
        $sqlCondition  = $sheet['sql_condition'] ?? '';
 
        // Parse column IDs. intval + filter keeps this safe to interpolate below.
        $column_ids_raw = $sheet['column_ids'] ?? '';
        $column_ids = (is_string($column_ids_raw) && trim($column_ids_raw) !== '')
            ? array_values(array_filter(array_map('intval', explode(",", $column_ids_raw))))
            : [];
 
        if (empty($column_ids)) {
            continue; // skip if no columns configured
        }
 
        $orderColumns = implode(',', $column_ids);
 
        // Fetch SELECT expressions in configured order.
        // NOTE: ->row() is null when nothing matches, so guard it. Chaining
        // ->row()->fetch_column_name is a fatal error on PHP 8 when the row is
        // missing, and ?? does not save you - it only catches undefined, not null->prop.
        $columnRow = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, {$orderColumns}) SEPARATOR ', ') AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where("fetch_column_name IS NOT NULL", null, false)
            ->where("TRIM(fetch_column_name) <> ''", null, false)
            ->where_in("id", $column_ids)
            ->get()
            ->row();
 
        $selectColumnName = $columnRow ? ($columnRow->fetch_column_name ?? '') : '';
 
        if (empty($selectColumnName)) {
            continue;
        }
 
        // Header names, same order.
        // Filtered identically to the SELECT list, so a row with a NULL or blank
        // fetch_column_name cannot leave you with more headers than columns -
        // which is what shifts every value one cell to the left in the sheet.
        $sheetColumnName = $CI->db
            ->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where("fetch_column_name IS NOT NULL", null, false)
            ->where("TRIM(fetch_column_name) <> ''", null, false)
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id, {$orderColumns})", "", false)
            ->get()
            ->result_array();
 
        $columns = array_column($sheetColumnName, "name");
 
        // Build conditions
        $condition_sql = "";
 
        if (!empty($fromDate) && !empty($toDate)) {
            // Half-open range on the bare column instead of DATE(a.created_at).
            // Wrapping the column in a function makes the index unusable and
            // forces a full table scan on every sync.
            $condition_sql .= " AND a.created_at >= " . $CI->db->escape($fromDate . " 00:00:00")
                            . " AND a.created_at <  " . $CI->db->escape(date('Y-m-d', strtotime($toDate . ' +1 day')) . " 00:00:00");
        }
 
        if (!empty($sqlCondition) && trim($sqlCondition) !== '') {
            $condition_sql .= " AND (" . $sqlCondition . ")";
        }
 
        $sql = "
    SELECT
        {$selectColumnName}
    FROM `" . db_prefix() . "external_client_apostille_data` a
    INNER JOIN `" . db_prefix() . "orignal_documents` d
        ON a.doc_id = d.id
    LEFT JOIN `" . db_prefix() . "vendor_list` v
        ON a.vendor_id = v.id
    LEFT JOIN `" . db_prefix() . "payment_mode` p
        ON a.payment_mode = p.id
    LEFT JOIN `" . db_prefix() . "staff` s
        ON a.created_by = s.staffid
    WHERE 1=1 {$condition_sql}
    GROUP BY a.id
    ORDER BY a.id DESC
";
 
        $query = $CI->db->query($sql);
 
        if (!$query) {
            log_message('error', 'external_apostile_data: query failed for sheet id ' . $currentId);
            continue;
        }
 
        $arrayData = $query->result_array();
 
        // Rows -> plain indexed arrays, nulls -> ""
        $arrayDataValues = [];
        foreach ($arrayData as $row) {
            $valuesOnly = [];
            foreach ($row as $v) {
                $valuesOnly[] = ($v === null) ? '' : $v;
            }
            $arrayDataValues[] = $valuesOnly;
        }
 
        // Append - do NOT return here. `return $dataArray[] = [...]` inside the
        // loop hands back only the FIRST sheet and silently drops the rest.
        $dataArray = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
    }
 
    // Always an array, never null - Apps Script accepts both a single object
    // and an array of them, but null breaks the JSON parse.
    return $dataArray??[];
}

function visitor_logs_data($id)
{
    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Pagination
    $limit  = (int)$CI->input->get('limit');
    $offset = (int)$CI->input->get('offset');

    if ($limit <= 0) {
        $limit = 500;
    }

    if ($offset < 0) {
        $offset = 0;
    }

    // Sheet Config
    $sheetData = $CI->db
        ->select("id, spreadsheetId, fromDate, toDate, autoSync, sheet_name, sql_condition, column_ids")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 9)
        ->where("autoSync", 1)
        ->where("spreadsheetId", $id)
        ->order_by("id", "ASC")
        ->get()
        ->result_array();

    $response = [];

    foreach ($sheetData as $sheet) {

        $fromDate   = $sheet['fromDate'];
        $toDate     = $sheet['toDate'];
        $sheet_name = $sheet['sheet_name'];

        $column_ids = !empty($sheet['column_ids'])
            ? array_map('intval', explode(',', $sheet['column_ids']))
            : [];

        if (empty($column_ids)) {
            continue;
        }

        $orderColumns = implode(",", $column_ids);

        // Dynamic Select
        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id,$orderColumns)) AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->get()
            ->row()
            ->fetch_column_name ?? '';

        if (empty($selectColumnName)) {
            continue;
        }

        // Date Filter
        $condition_sql = "";

        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND DATE(v.date_of_visit) BETWEEN "
                . $CI->db->escape($fromDate)
                . " AND "
                . $CI->db->escape($toDate);
        }

        // Total Records
        $countSql = "
            SELECT COUNT(DISTINCT v.id) total
            FROM " . db_prefix() . "visitor_request v
            LEFT JOIN " . db_prefix() . "leads l ON v.lead_id=l.id
            WHERE 1=1 {$condition_sql}
        ";

        $totalRows = (int)$CI->db->query($countSql)->row()->total;

        // Main Query
        $sql = "
            SELECT
                {$selectColumnName}
            FROM " . db_prefix() . "visitor_request v

            LEFT JOIN " . db_prefix() . "leads l
                ON v.lead_id=l.id

            LEFT JOIN " . db_prefix() . "leads_type t
                ON l.type=t.id

            LEFT JOIN " . db_prefix() . "leads_status s
                ON l.status=s.id

            LEFT JOIN " . db_prefix() . "leads_sources so
                ON l.source=so.id

            LEFT JOIN " . db_prefix() . "visitor_type vt
                ON v.visitor_type=vt.id

            LEFT JOIN " . db_prefix() . "visitor_status vs
                ON v.status=vs.id

            LEFT JOIN " . db_prefix() . "staff ass
                ON v.created_by=ass.staffid

            LEFT JOIN " . db_prefix() . "staff att
                ON v.assigned=att.staffid

            LEFT JOIN " . db_prefix() . "cities_ ct
                ON ct.id=v.location

            WHERE 1=1
            {$condition_sql}

            GROUP BY v.id

            ORDER BY v.id DESC

            LIMIT {$limit}
            OFFSET {$offset}
        ";

        $rows = $CI->db->query($sql)->result_array();

        // Header Names
        $sheetColumnName = $CI->db
            ->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id,$orderColumns)", "", false)
            ->get()
            ->result_array();

        $headers = array_column($sheetColumnName, "name");

        // Clean NULL
        $rowData = [];

        foreach ($rows as $row) {

            $tmp = [];

            foreach ($row as $value) {

                $tmp[] = $value === null ? "" : $value;

            }

            $rowData[] = $tmp;
        }

        $response[] = [

            "columnName"    => $headers,

            "workSheetName" => $sheet_name,

            "rowData"       => $rowData,

            "limit"         => $limit,

            "offset"        => $offset,

            "totalRows"     => $totalRows,

            "hasMore"       => ($offset + count($rowData)) < $totalRows

        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalize_sheet_payload($data)
{
    $out = [];
 
    $walk = function ($node) use (&$walk, &$out) {
        if (!is_array($node)) {
            return;
        }
        // A sheet object - take it, do not descend further
        if (array_key_exists('columnName', $node)
            || array_key_exists('workSheetName', $node)
            || array_key_exists('rowData', $node)) {
            $out[] = [
                'columnName'    => isset($node['columnName'])    ? array_values((array) $node['columnName']) : [],
                'workSheetName' => isset($node['workSheetName']) ? (string) $node['workSheetName']           : '',
                'rowData'       => isset($node['rowData'])       ? array_values((array) $node['rowData'])    : [],
            ];
            return;
        }
        // A container - descend
        foreach ($node as $child) {
            $walk($child);
        }
    };
 
    $walk($data);
    return $out;
}

function ex_ticket_data($id)
{
    

    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $sheetData = $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, sheet_name, sql_condition, column_ids")
        ->from(db_prefix() . "excel_data_update")
        ->where("excel_type", 8)
        ->where("autoSync", 1)
        ->where("spreadsheetId",$id)
        ->order_by("id", "asc")
        ->get()
        ->result_array();

    $dataArray = [];

    foreach ($sheetData as $sheet) {
        $currentId     = $sheet['id'] ?? null;
        $fromDate      = $sheet['fromDate'] ?? null;
        $toDate        = $sheet['toDate'] ?? null;
        $spreadsheetId = $sheet['spreadsheetId'] ?? null;
        $sheet_name    = $sheet['sheet_name'] ?? null;

        // Parse column IDs
        $column_ids_raw = $sheet['column_ids'] ?? '';
        $column_ids = (is_string($column_ids_raw) && trim($column_ids_raw) !== '')
            ? array_map('intval', explode(",", $column_ids_raw))
            : [];

        if (empty($column_ids)) {
            continue; // skip if no columns configured
        }
        $orderColumns = implode(',', $column_ids);
        // Fetch column names in correct order

        $selectColumnName = $CI->db
            ->select("GROUP_CONCAT(fetch_column_name ORDER BY FIELD(id, $orderColumns)) AS fetch_column_name", false)
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->get()
            ->row()
            ->fetch_column_name ?? '';
        if (empty($selectColumnName)) {
            continue;
        }
        // Build conditions
        $condition_sql = "";
        if (!empty($fromDate) && !empty($toDate)) {
            $condition_sql .= " AND (vd.payment_date BETWEEN " . $CI->db->escape($fromDate) . " AND " . $CI->db->escape($toDate) . ")";
        }
        $sql = "
    SELECT 
        {$selectColumnName}
    FROM `" . db_prefix() . "external_ticket_data` vd
    LEFT JOIN `" . db_prefix() . "external_ticket_vendor` v 
        ON vd.ticket_vendor = v.id
    LEFT JOIN `" . db_prefix() . "external_payment_mode` m 
    on m.id = vd.payment_mode
    LEFT JOIN `" . db_prefix() . "airline` a 
    on a.id = vd.airline
     LEFT JOIN `" . db_prefix() . "flight_type` ft
    on ft.id = vd.flight_type
    LEFT JOIN `" . db_prefix() . "departure_location` dl
    on dl.id = vd.departure_id
     LEFT JOIN `" . db_prefix() . "departure_location` ddl
    on ddl.id = vd.destination_id
    LEFT JOIN `" . db_prefix() . "external_visa_type` tt
    on tt.id = vd.ticket_type
    LEFT JOIN " . db_prefix() . "ticket_status ts
     ON ts.id = vd.ticket_status
    
    
    WHERE 1=1 and vd.status=1 {$condition_sql}
    GROUP BY vd.id
    ORDER BY vd.id DESC
";


        $arrayData = $CI->db->query($sql)->result_array();

        // Get column names
        $sheetColumnName = $CI->db->select("name")
            ->from(db_prefix() . "excel_column_update")
            ->where_in("id", $column_ids)
            ->order_by("FIELD(id, {$orderColumns})", "", false)
            ->get()
            ->result_array();
        $columns = array_column($sheetColumnName, "name");


        // Update last sync
        // $CI->db->where('id', $currentId)
        //     ->update(db_prefix() . "excel_data_update", [
        //         'lastSync' => date('Y-m-d H:i:s')
        //     ]);
        // Add to final array
        
                $arrayDataValues = [];
        foreach ($arrayData as $row) {
            $valuesOnly = [];
            foreach ($row as $v) {
                $valuesOnly[] = $v === null ? '' : $v; // Replace null with blank
            }
            $arrayDataValues[] = $valuesOnly;
        }
        return $dataArray[] = [
            "columnName"    => $columns,
            "workSheetName" => $sheet_name,
            "rowData"       => $arrayDataValues
        ];
    }
    // header('Content-Type: application/json');
    // echo json_encode($dataArray);
    // exit;
}


// Read data from sheet
// if (!function_exists('read_sheet_data')) {
//     function read_sheet_data($id)
//     {
//         $googleSheet = get_google_sheet_instance();

//         return $googleSheet->readSheet($spreadsheetId, $range);
//     }
// }
