<?php

if (!function_exists('send_fcm_notification')) {
    function send_fcm_notification(string|array $tokens, string $title, string $body, array $data = []): string|bool
    {
        // PERBAIKAN: Menyesuaikan dengan nama file JSON yang Anda miliki
        $keyFilePath = APPPATH . 'Config/firebase_credentials.json';

        if (!file_exists($keyFilePath)) {
            return json_encode(['error' => ['message' => 'File firebase_credentials.json tidak ditemukan di folder app/Config/']]);
        }

        $keyData = json_decode(file_get_contents($keyFilePath), true);
        $projectId = $keyData['project_id'] ?? '';

        if (empty($projectId)) {
            return json_encode(['error' => ['message' => 'Format JSON tidak valid atau Project ID hilang.']]);
        }

        try {
            // Generate OAuth 2.0 Token (Berlaku 1 Jam)
            $client = new \Google\Client();
            $client->setAuthConfig($keyFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();

            $tokenArray = $client->getAccessToken();
            if (!isset($tokenArray['access_token'])) {
                return json_encode(['error' => ['message' => 'Gagal membuat Access Token dari Google.']]);
            }
            $accessToken = $tokenArray['access_token'];

            // Susun Endpoint HTTP v1
            $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

            // HTTP v1 hanya memproses 1 token per request
            $targetToken = is_array($tokens) ? $tokens[0] : $tokens;

            // Susun Payload versi HTTP v1
            $payload = [
                'message' => [
                    'token' => $targetToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $data
                ]
            ];

            // Eksekusi cURL
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        } catch (\Exception $e) {
            return json_encode(['error' => ['message' => 'Exception Server: ' . $e->getMessage()]]);
        }
    }
}
