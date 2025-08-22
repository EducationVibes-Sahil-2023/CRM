<?php
defined('BASEPATH') or exit('No direct script access allowed');

class googlesheet extends AdminController
{

    public function __construct()
    {
        die;
        parent::__construct();
        $this->load->library('GoogleSheetApi');
    }

    public function update()
    {
        $spreadsheetId = 'YOUR_SPREADSHEET_ID';
        $range = 'Sheet1!A2'; // Change as needed
        $values = [
            ["Hello from CodeIgniter 3!"]
        ];

        $response = $this->googlesheetapi->updateSheet($spreadsheetId, $range, $values);

        echo "<pre>";
        print_r($response);
    }
}
