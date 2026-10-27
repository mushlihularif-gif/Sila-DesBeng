<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $projectId;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase-auth.json');
        
        if (file_exists($credentialsPath)) {
            $json = json_decode(file_get_contents($credentialsPath), true);
            $this->projectId = $json['project_id'] ?? null;
        }
    }

    public function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken || !$this->projectId) {
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return false;
            }

            $message = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

            if ($response->successful()) {
                Log::info("FCM Notification sent successfully to {$fcmToken}");
                return true;
            }

            Log::error("Failed to send FCM Notification", ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken()
    {
        $credentialsPath = storage_path('app/firebase-auth.json');
        
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentialsPath
        );
        $token = $credentials->fetchAuthToken();

        return $token['access_token'] ?? null;
    }
}
