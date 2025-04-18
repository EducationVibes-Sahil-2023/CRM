<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Drive;

class GoogleSheetApi
{
    protected $client;
    protected $sheetsService;
    protected $driveService;
    protected $spreadsheetId;
    protected $spreadsheetUrl;
    protected $service;



    public function __construct()
    {
        try {
            // Initialize Google Client
            $this->client = new Client();
            $this->client->setAuthConfig(FCPATH . 'google-credentials/service-account.json');
            $this->client->setScopes([
                Sheets::SPREADSHEETS,
                Drive::DRIVE, // Needed for sharing permissions
            ]);
            $this->service = new Sheets($this->client);
            $this->client->setAccessType('offline');

            // Create service instances
            $this->sheetsService = new Sheets($this->client);
            $this->driveService = new Drive($this->client);
            // $this->spreadsheetId = $this->createAndShareSheet("MA Tracker 2024-2025", "sahil.chaudhary@educationvibes.in");
        } catch (Exception $e) {
            echo "❌ Initialization Error: " . $e->getMessage();
        }
    }

    public function createAndShareSheet($title = 'New Spreadsheet', $shareEmail = 'sahil.chaudhary@educationvibes.in', $role = 'writer')
    {
        try {

            $driveService = new Drive($this->client);

            // 1. Check if a sheet with the same name already exists
            $query = sprintf(
                "mimeType='application/vnd.google-apps.spreadsheet' and name='%s' and trashed=false",
                addslashes($title)
            );

            $files = $driveService->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);

            if (count($files->getFiles()) > 0) {


                // Found existing sheet
                $existingSheet = $files->getFiles()[0];
                return $existingSheet->getId();
            }

            // Create the spreadsheet
            $spreadsheet = new Google_Service_Sheets_Spreadsheet([
                'properties' => ['title' => $title]
            ]);

            $createdSheet = $this->sheetsService->spreadsheets->create($spreadsheet);
            $spreadsheetId = $createdSheet->spreadsheetId;

            // Share with specific user
            if (!empty($shareEmail)) {
                $permission = new Google_Service_Drive_Permission([
                    'type' => 'user',
                    'role' => $role, // 'reader' or 'writer'
                    'emailAddress' => $shareEmail
                ]);

                $this->driveService->permissions->create(
                    $spreadsheetId,
                    $permission,
                    ['sendNotificationEmail' => true] // optional
                );
            }

            // Return the spreadsheet URL
            $this->spreadsheetUrl = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId;
            return $spreadsheetId;
        } catch (Exception $e) {
            echo "❌ Sheet Creation Error: " . $e->getMessage();
            return false;
        }
    }

    public function shareWithDomain($spreadsheetId, $domain = 'sahil.chaudhary@educationvibes.in', $role = 'reader')
    {
        try {
            $permission = new Google\Service\Drive\Permission([
                'type' => 'domain',
                'role' => $role, // 'reader' or 'writer'
                'domain' => $domain,
            ]);

            $this->driveService->permissions->create(
                $spreadsheetId,
                $permission,
                ['sendNotificationEmail' => false]
            );

            return true;
        } catch (Exception $e) {
            echo "❌ Sharing Error: " . $e->getMessage();
            return false;
        }
    }


    public function updateSheetColumnNames($spreadsheetId, $columnNames = [])
    {

        $sheetName = $this->listSheetNames($spreadsheetId);
        if (empty($spreadsheetId) || empty($sheetName) || empty($columnNames)) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Spreadsheet ID, Sheet Name, and Column Names are required.'
            ];
        }

        $range = $sheetName . '!A1:' . chr(64 + count($columnNames)) . '1';

        $body = new Google_Service_Sheets_ValueRange([
            'values' => [$columnNames]
        ]);

        $params = ['valueInputOption' => 'RAW'];

        try {
            $response = $this->service->spreadsheets_values->update(
                $spreadsheetId,
                $range,
                $body,
                $params
            );

            return [
                'resp_code' => 'RCS',
                'resp_desc' => 'Sheet column headers updated successfully.',
                'updated_cells' => $response->getUpdatedCells()
            ];
        } catch (Exception $e) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Google Sheets API Error: ' . $e->getMessage()
            ];
        }
    }

    public function updateSheetData($spreadsheetId, $arrayData = [])
    {
        $sheetName = $this->listSheetNames($spreadsheetId);
        if (empty($spreadsheetId) || empty($sheetName) || empty($arrayData)) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Spreadsheet ID, Sheet Name, and Data are required.'
            ];
        }

        // Format array with headers + values
        $sheetData = $this->formatForGoogleSheet($arrayData);

        $range = 'Sheet1!A2'; // Include headers
        $body = new Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => [
                [
                    'range' => $range,
                    'values' => $sheetData
                ]
            ]
        ]);

        try {
            $response = $this->service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

            return [
                'resp_code' => 'RCS',
                'resp_desc' => 'Sheet data updated successfully via batchUpdate.',
                'total_updated_cells' => $response->getTotalUpdatedCells()
            ];
        } catch (Exception $e) {
            return [
                'resp_code' => 'ERR',
                'resp_desc' => 'Google Sheets API Error: ' . $e->getMessage()
            ];
        }
    }

    public function formatForGoogleSheet($arrayData)
    {
        if (empty($arrayData)) {
            return [];
        }

        $headers = array_keys($arrayData[0]);
        $sheetData = [];
        // $sheetData[] = $headers;

        foreach ($arrayData as $row) {
            // Match values to the header order
            $sheetData[] = array_map(function ($key) use ($row) {
                return isset($row[$key]) ? $row[$key] : ''; // handle missing keys
            }, $headers);
        }

        return $sheetData;
    }






    public function listSheetNames($spreadsheetId)
    {
        try {
            // Fetch spreadsheet metadata to check sheet names
            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            $sheetNames = array_map(function ($sheet) {
                return $sheet['properties']['title'];
            }, $spreadsheet->getSheets());

            return $sheetNames[0];
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }

    public function listAllSheets()
    {
        try {
            $driveService = new Drive($this->client);

            $optParams = [
                'q' => "mimeType='application/vnd.google-apps.spreadsheet' and trashed = false",
                'fields' => 'files(id, name, webViewLink)',
                'orderBy' => 'createdTime desc'
            ];

            $results = $driveService->files->listFiles($optParams);
            $files = $results->getFiles();

            if (empty($files)) {
                echo "⚠️ No sheets found.";
                return [];
            }

            $sheetList = [];
            foreach ($files as $file) {
                $sheetList[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'link' => $file->getWebViewLink()
                ];
            }

            return $sheetList;
        } catch (Exception $e) {
            echo '❌ Error: ' . $e->getMessage();
            return [];
        }
    }

    /** ✅ FETCH DATA **/
    public function fetchData($range)
    {
        try {
            // Define the range properly with the sheet name and the range of cells you want to read
            $range = 'MA Tracker!A1:C2'; // Adjust as needed

            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );

            // Check if response has values
            $values = $response->getValues();

            if (empty($values)) {
                echo "No data found.<br>";
            } else {
                echo "<pre>";
                print_r($values); // Print the fetched data for debugging
                echo "</pre>";
            }
        } catch (Exception $e) {
            echo "❌ Fetch Error: " . $e->getMessage(); // Log the exact error message
        }
    }

    public function getSpreadsheetInfo()
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);

            $info = [
                'spreadsheetId' => $spreadsheet->getSpreadsheetId(),
                'title' => $spreadsheet->getProperties()->getTitle(),
                'sheets' => []
            ];

            foreach ($spreadsheet->getSheets() as $sheet) {
                $info['sheets'][] = [
                    'sheetId' => $sheet->getProperties()->getSheetId(),
                    'title' => $sheet->getProperties()->getTitle(),
                    'rowCount' => $sheet->getProperties()->getGridProperties()->getRowCount(),
                    'columnCount' => $sheet->getProperties()->getGridProperties()->getColumnCount()
                ];
            }

            return $info;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
