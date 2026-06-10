<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth
{
    private string $key;

    public function __construct()
    {
        $secretKey = getenv('JWT_SECRET_KEY');

        if (empty($secretKey)) {
            throw new \Exception('Kunci JWT (JWT_SECRET_KEY) belum dikonfigurasi di file .env.');
        }

        $this->key = $secretKey;
    }

    /**
     * @param array $data
     * @return string
     */
    public function generateAccessToken(array $data): string
    {
        return $this->buildToken($data, 900); // 15 Menit
    }

    /**
     * @param array $data
     * @return string
     */
    public function generateRefreshToken(array $data): string
    {
        return $this->buildToken($data, 604800); // 7 Hari
    }

    /**
     * @param array $data
     * @param int $expiration
     * @return string
     */
    private function buildToken(array $data, int $expiration): string
    {
        $issuedAt = time();
        $payload  = [
            'iat'  => $issuedAt,
            'iss'  => 'GeofenceApp',
            'exp'  => $issuedAt + $expiration,
            'data' => $data
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    /**
     * @param string $token
     * @return object|null
     */
    public function decodeToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
