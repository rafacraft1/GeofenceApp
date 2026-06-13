<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

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
        return $this->buildToken($data, 900);
    }

    /**
     * @param array $data
     * @return string
     */
    public function generateRefreshToken(array $data): string
    {
        return $this->buildToken($data, 604800);
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
     * @return array
     */
    public function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return ['status' => 'valid', 'data' => $decoded->data];
        } catch (ExpiredException $e) {
            return ['status' => 'expired', 'message' => 'Token telah kadaluarsa.'];
        } catch (SignatureInvalidException $e) {
            return ['status' => 'invalid', 'message' => 'Tanda tangan token tidak valid.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Token rusak atau tidak valid.'];
        }
    }
}
