<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load custom vendor (your separate folder)
require_once FCPATH . 'fcm_vendor/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class Fcm_lib {

    private $project_id;
    private $service_account_path;
    private $scope = 'https://www.googleapis.com/auth/firebase.messaging';

    public function __construct()
    {
        // Set your project ID
        $this->project_id = FIREBASE_PROJECT_ID;

        // FIXED path (correct + safe)
        $this->service_account_path = APPPATH . 'config/firebase-service-account.json';
    }

    /**
     * 🔐 Generate Access Token
     */
    private function generateAccessToken()
    {
        $credentials = new ServiceAccountCredentials(
            $this->scope,
            json_decode(file_get_contents($this->service_account_path), true)
        );

        $handler = HttpHandlerFactory::build();
        $token = $credentials->fetchAuthToken($handler);

        if (!isset($token['access_token'])) {
            throw new Exception("Failed to get access token");
        }

        return $token['access_token'];
    }

    /**
     * 📲 Send FCM Push Notification
     */
    // public function send($deviceToken, $title, $body, $data = [])
    // {
    //     try {
    //         $accessToken = $this->generateAccessToken();

    //         $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";

    //         $payload = [
    //             "message" => [
    //                 "token" => $deviceToken,
    //                 "notification" => [
    //                     "title" => $title,
    //                     "body"  => $body
    //                 ],
    //                 "data" => $data
    //             ]
    //         ];

    //         $headers = [
    //             "Authorization: Bearer " . $accessToken,
    //             "Content-Type: application/json"
    //         ];

    //         $ch = curl_init();

    //         curl_setopt_array($ch, [
    //             CURLOPT_URL => $url,
    //             CURLOPT_POST => true,
    //             CURLOPT_HTTPHEADER => $headers,
    //             CURLOPT_RETURNTRANSFER => true,
    //             CURLOPT_POSTFIELDS => json_encode($payload),
    //         ]);

    //         $response = curl_exec($ch);

    //         if ($response === false) {
    //             throw new Exception("Curl Error: " . curl_error($ch));
    //         }

    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         curl_close($ch);

    //         return [
    //             "success"  => ($httpCode === 200),
    //             "status"   => $httpCode,
    //             "response" => json_decode($response, true)
    //         ];

    //     } catch (Exception $e) {

    //         log_message('error', 'FCM Error: ' . $e->getMessage());

    //         return [
    //             "success" => false,
    //             "error"   => $e->getMessage()
    //         ];
    //     }
    // }
    
        public function sendBulk_message($bulkMessage = [], $data = [], $sound = "default")
{
    if (empty($deviceTokens)) return [];

    $responses = [];

    // ✅ Clean tokens (remove empty + duplicates)
    $bulkMessage = array_unique(array_filter($bulkMessage));

    foreach ($bulkMessage as $bulk) {

$token = $bulk['token'];
$title = $bulk['token'];
$body = $bulk['token'];
        $responses[] = $this->send($token, $title, $body, $data, $sound);
    }

    return $responses;
}

    
    public function sendBulk($deviceTokens = [], $title, $body, $data = [], $sound = "default")
{
    if (empty($deviceTokens)) return [];

    $responses = [];

    // ✅ Clean tokens (remove empty + duplicates)
    $deviceTokens = array_unique(array_filter($deviceTokens));

    foreach ($deviceTokens as $token) {

        $responses[] = $this->send($token, $title, $body, $data, $sound);
    }

    return $responses;
}

    public function send($deviceToken, $title, $body, $data = [], $sound = "default")
{
    try {
        $accessToken = $this->generateAccessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";

        // ✅ Ensure all data values are strings
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = (string) $value;
            }
        }

        $payload = [
            "message" => [
                "token" => $deviceToken,

                "notification" => [
                    "title" => $title,
                    "body"  => $body
                ],

                // ✅ Android config
                "android" => [
                    "priority" => "high",
                    "notification" => [
                        "sound" => $sound // 🔥 dynamic sound
                    ]
                ],

                // ✅ iOS config
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => $sound
                        ]
                    ]
                ],

                "data" => $data
            ]
        ];

        $headers = [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json"
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception("Curl Error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            "success"  => ($httpCode === 200),
            "status"   => $httpCode,
            "response" => json_decode($response, true)
        ];

    } catch (Exception $e) {

        log_message('error', 'FCM Error: ' . $e->getMessage());

        return [
            "success" => false,
            "error"   => $e->getMessage()
        ];
    }
}
}