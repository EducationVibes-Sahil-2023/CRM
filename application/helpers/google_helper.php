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

        if (!empty($orignal_documents)) {
            //               echo $sql;
            // die;
        }
        // echo $sql;
        // die;
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


function syncExcel_neww($id = "")
{
    $CI = &get_instance();

    $CI->db->query("SET SESSION group_concat_max_len = 10000000000");

    // Fetch sheet config(s)
    $CI->db->select("id, spreadsheetId, fromDate, toDate, autoSync, acadmic_year, sheet_name, sql_condition,column_ids,orignal_documents_status")
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

        $currentId               = $sheet['id'] ?? null;
        $fromDate                = $sheet['fromDate'] ?? null;
        $toDate                  = $sheet['toDate'] ?? null;
        $acadmic_year            = $sheet['acadmic_year'] ?? null;
        $spreadsheetId           = $sheet['spreadsheetId'] ?? null;
        $sheet_name              = $sheet['sheet_name'] ?? null;
        $orignal_documents_status = $sheet['orignal_documents_status'] ?? null;
        $sql_conditions          = $sheet['sql_condition'] ?? null;

        $column_ids_raw = $sheet['column_ids'] ?? '';
        $column_ids = is_string($column_ids_raw) && !empty($column_ids_raw)
            ? explode(",", $column_ids_raw)
            : [];

        $order = implode(',', $column_ids);

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
        if (!empty($orignal_documents_status) && $orignal_documents_status == 1) {

            $orignal_documents = get_orignal_document_list();
            $upload_document = get_documents(2, [], 0, "", [db_prefix() . 'document_upload_type.orignal_status' => '1']);

            $queryPart = [];



            if (!empty($orignal_documents)) {
                foreach ($orignal_documents as $document) {
                    $short_name = trim($document['short_name']);
                    $safe_column_name = str_replace(" ", "_", $short_name);
                    $extra_columns[] = $safe_column_name;

                    $queryPart[] = "MAX(CASE WHEN od.short_name = '" . $CI->db->escape_str($short_name) . "' 
                          THEN 'YES' ELSE 'NO' END) AS `" . $safe_column_name . "`";
                }
            }

            if (!empty($upload_document)) {
                foreach ($upload_document as $docu) {
                    $doc_id = (int) $docu['id'];
                    $safe_column_name = str_replace(" ", "_", $docu["name"]);
                    $extra_columns[] = $safe_column_name;

                    $queryPart[] = "
            CASE
                WHEN JSON_SEARCH(
                    CAST(CAST(cd.data AS CHAR CHARACTER SET utf8) AS JSON),
                    'one',
                    '$doc_id',
                    NULL,
                    '$.*.id'
                ) IS NOT NULL
                THEN 'YES'
                ELSE 'NO'
            END AS `$safe_column_name`
        ";
                }
            }

            $extra_columns[] = "Invitation_letter";
            $queryPart[] = "IF(u.invitation_letter IS NOT NULL AND u.invitation_letter != '', 'Yes', 'No') AS Invitation_letter";

            $extra_columns[] = "Admission_letter";
            $queryPart[] = "IF(u.application_file IS NOT NULL AND u.application_file != '', 'Yes', 'No') AS Admission_letter";


            if (!empty($queryPart)) {
                $selectColumnName .= ', ' . implode(",\n", $queryPart);
            }
        }

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

        $sql = "SELECT {$selectColumnName}
                FROM " . db_prefix() . "clients c
                LEFT JOIN " . db_prefix() . "basic_details b ON c.userid = b.userid
                LEFT JOIN " . db_prefix() . "ev_partner evp ON evp.id = c.agent_id
                LEFT JOIN " . db_prefix() . "applicant_status s ON c.active = s.id
                LEFT JOIN " . db_prefix() . "leads l ON l.id = c.leadid
                LEFT JOIN " . db_prefix() . "staff st ON l.assigned = st.staffid
                LEFT JOIN " . db_prefix() . "applicant_stages tt ON tt.id = c.applicant_stage
                LEFT JOIN " . db_prefix() . "application_sub_category_mbbs ts ON ts.id = c.applicant_sub_status
                LEFT JOIN " . db_prefix() . "admission_preferences p ON p.userid = c.userid
                LEFT JOIN " . db_prefix() . "client_university_shortlisting u ON u.client_id = c.userid AND u.status = 1
                LEFT JOIN " . db_prefix() . "applicant_fees_details fd ON fd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "applicant_fees f ON f.id = fd.fees_id
                LEFT JOIN " . db_prefix() . "orignal_document_status o ON o.id = c.orignal_document_status
                LEFT JOIN " . db_prefix() . "orignal_documents_received dr ON dr.userid = c.userid
                LEFT JOIN " . db_prefix() . "office_location dl ON dl.id = dr.location_id
                LEFT JOIN " . db_prefix() . "orignal_documents od ON od.id = dr.doc_id
                LEFT JOIN " . db_prefix() . "client_passport_details pd ON pd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "passport_stages ps ON ps.id = pd.passport_status
                LEFT JOIN " . db_prefix() . "academic_details ad ON ad.userid = c.userid
                LEFT JOIN " . db_prefix() . "client_documents cd ON cd.client_id = c.userid
                LEFT JOIN " . db_prefix() . "document_upload_type dt ON dt.lead_type = 2 AND dt.orignal_status = 1
                LEFT JOIN " . db_prefix() . "currencies cu ON cu.id = c.scholarship_currency
                LEFT JOIN (
                    SELECT 
                        userid,
                        SUM(apostille_cost) AS Total_cost,
                        MAX(courier_date) AS courier_date,
                        MAX(payment_date) AS payment_date,
                        GROUP_CONCAT(vendor_id) AS vendor_id,
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

        //  if (!empty($orignal_documents_status) && $orignal_documents_status == 1) {

        //      echo $sql;
        //      die;
        //  }
        $arrayData = $CI->db->query($sql)->result_array();

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

        $arrayDataValues = [];
        foreach ($arrayData as $row) {
            $valuesOnly = [];
            foreach ($row as $v) {
                $valuesOnly[] = $v === null ? '' : $v;
            }
            $arrayDataValues[] = $valuesOnly;
        }

        $CI->db->where('id', $currentId);
        $CI->db->update(db_prefix() . "excel_data_update", [
            'lastSync' => date('Y-m-d H:i:s')
        ]);

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


// Read data from sheet
// if (!function_exists('read_sheet_data')) {
//     function read_sheet_data($id)
//     {
//         $googleSheet = get_google_sheet_instance();

//         return $googleSheet->readSheet($spreadsheetId, $range);
//     }
// }
