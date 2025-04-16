<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Google_sheet extends AdminController
{
    private $spreadsheetId = 'YOUR_SPREADSHEET_ID';
    private $client;

    public function __construct()
    {

        die;
        parent::__construct();

        $this->load->library('GoogleSheetApi');

        // // Load Google API Client
        // $this->client = new \Google_Client();
        // $this->client->setApplicationName('CI3 Google Sheets');
        // $this->client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        // $this->client->setAuthConfig(APPPATH . 'credentials/your-service-account.json');
        // $this->client->setAccessType('offline');
    }

    // Fetch data from sheet
    public function fetch()
    {
        $service = new \Google_Service_Sheets($this->client);

        $range = 'Sheet1!A1:E'; // Adjust range
        $response = $service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            echo "No data found.";
        } else {
            echo "<pre>";
            print_r($values);
            echo "</pre>";
        }
    }

    // Update or append data to sheet
    public function update()
    {
        $service = new \Google_Service_Sheets($this->client);

        $range = 'Sheet1!A1'; // Starting cell
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => [
                ['John', 'Doe', 'john@example.com', 'Active']
            ]
        ]);

        $params = ['valueInputOption' => 'RAW'];

        $result = $service->spreadsheets_values->append(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );

        echo $result->getUpdates()->getUpdatedCells() . " cells updated.";
    }
}
