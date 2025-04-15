<?php
require_once APPPATH . '../vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetApi
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(FCPATH . 'google-credentials/service-account.json');
        $this->client->setScopes([Sheets::SPREADSHEETS]);

        $this->service = new Sheets($this->client);
    }

    public function updateSheet($spreadsheetId, $range, $values = [])
    {
        $body = new Sheets\ValueRange([
            'values' => $values
        ]);

        $params = ['valueInputOption' => 'RAW'];
        return $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }
}
