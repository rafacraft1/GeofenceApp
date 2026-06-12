<?php

if (!function_exists('send_fcm_notification')) {
    function send_fcm_notification(string|array $tokens, string $title, string $body, array $data = []): string|bool
    {
        $keyFilePath = APPPATH . 'Config/firebase_credentials.json';

        if (!file_exists($keyFilePath)) {
            return json_encode(['error' => ['message' => 'File firebase_credentials.json tidak ditemukan.']]);
        }

        $keyData = json_decode(file_get_contents($keyFilePath), true);
        $projectId = $keyData['project_id'] ?? '';

        if (empty($projectId)) {
            return json_encode(['error' => ['message' => 'Project ID hilang.']]);
        }

        try {
            $client = new \Google\Client();
            $client->setAuthConfig($keyFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();

            $tokenArray = $client->getAccessToken();
            if (!isset($tokenArray['access_token'])) {
                return json_encode(['error' => ['message' => 'Gagal membuat Access Token dari Google.']]);
            }
            $accessToken = $tokenArray['access_token'];

            $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];

            $tokenList = is_array($tokens) ? $tokens : [$tokens];
            $responses = [];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            foreach ($tokenList as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'data' => $data
                    ]
                ];

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                $responses[] = curl_exec($ch);
            }

            curl_close($ch);

            return end($responses);
        } catch (\Exception $e) {
            return json_encode(['error' => ['message' => 'Exception Server: ' . $e->getMessage()]]);
        }
    }
}
