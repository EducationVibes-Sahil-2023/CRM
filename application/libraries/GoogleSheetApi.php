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

            // Create service instances
            $this->sheetsService = new Sheets($this->client);
            $this->driveService = new Drive($this->client);
        } catch (Exception $e) {
            echo "❌ Initialization Error: " . $e->getMessage();
        }
    }

    /**
     * Share the spreadsheet with all emails in the given domain.
     */
    public function shareWithDomain($spreadsheetId, $domain = 'educationvibes.in', $role = 'reader')
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


    /**
     * Create a new spreadsheet and share it with a specific user.
     */
    public function createAndShareSheet($title = 'New Spreadsheet', $shareEmail = '', $role = 'writer')
    {
        try {
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
            $spreadsheetUrl = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId;
            return $spreadsheetUrl;
        } catch (Exception $e) {
            echo "❌ Sheet Creation Error: " . $e->getMessage();
            return false;
        }
    }
}
