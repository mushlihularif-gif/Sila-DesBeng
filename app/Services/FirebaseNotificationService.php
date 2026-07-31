<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $projectId;
    protected $credentialsPath;

    public function __construct()
    {
        // Path to the Firebase service account JSON
        $this->credentialsPath = storage_path('app/firebase-auth.json');
        
        // Extract project ID from the JSON file if it exists
        if (file_exists($this->credentialsPath)) {
            $credentials = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $credentials['project_id'] ?? null;
        }
    }

    /**
     * Get OAuth2 Token from Google using Service Account
     */
    protected function getAccessToken()
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error('Firebase credentials file not found at ' . $this->credentialsPath);
            return null;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            
            // fetch access token
            $token = $client->fetchAccessTokenWithAssertion();
            
            return $token['access_token'] ?? null;
            
        } catch (\Exception $e) {
            Log::error('Firebase access token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send Push Notification using FCM HTTP v1 API
     */
    public function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        if (empty($fcmToken)) {
            Log::warning('Cannot send FCM notification: fcm_token is empty.');
            return false;
        }

        $accessToken = $this->getAccessToken();

        if (!$accessToken || !$this->projectId) {
            Log::error('Failed to initialize Firebase credentials for Push Notifications.');
            return false;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                // Pastikan data adalah object (assoc array dikonversi ke object) agar JSON valid
                'data' => empty($data) ? new \stdClass() : $data,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, $message);

        if ($response->successful()) {
            Log::info("Push Notification sent successfully to {$fcmToken}");
            return true;
        } else {
            Log::error("Failed to send Push Notification: " . $response->body());
            return false;
        }
    }
}
