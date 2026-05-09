<?php

/**
 * Fungsi Global untuk mengirim Push Notification via FCM
 */
if (!function_exists('send_fcm_notification')) {
    function send_fcm_notification(string|array $tokens, string $title, string $body, array $data = []): string|bool
    {
        // Ganti dengan Server Key dari Firebase Console Anda (Project Settings > Cloud Messaging)
        $serverKey = 'YOUR_SERVER_KEY_HERE';
        $url = 'https://fcm.googleapis.com/fcm/send';

        $msg = [
            'title' => $title,
            'body'  => $body,
            'sound' => 'default',
        ];

        $payload = [
            'registration_ids' => is_array($tokens) ? $tokens : [$tokens],
            'notification'     => $msg,
            'data'             => $data,
            'priority'         => 'high'
        ];

        $headers = [
            'Authorization: key=' . $serverKey,
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
    }
}
