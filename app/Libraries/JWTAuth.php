<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth
{
    private string $key;

    public function __construct()
    {
        // ✅ Wajib mengambil secret key dari file .env
        $secretKey = getenv('JWT_SECRET_KEY');

        if (empty($secretKey)) {
            throw new \Exception('Kunci JWT (JWT_SECRET_KEY) belum dikonfigurasi di file .env. Hal ini berbahaya untuk keamanan.');
        }

        $this->key = $secretKey;
    }

    /**
     * Membuat Token JWT baru
     */
    public function generateToken(array $data): string
    {
        $issuedAt = time();
        $expire   = $issuedAt + 43200; // Token valid selama 12 Jam (mencakup 1 shift sekolah penuh)

        $payload = [
            'iat'  => $issuedAt,
            'iss'  => 'GeofenceApp',
            'exp'  => $expire,
            'data' => $data
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    /**
     * Membaca dan memvalidasi Token JWT
     */
    public function decodeToken(string $token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            // Token tidak valid, format salah, atau sudah expired
            return null;
        }
    }
}
