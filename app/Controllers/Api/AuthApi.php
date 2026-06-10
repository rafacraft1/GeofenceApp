<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;

/**
 * @return mixed
 */
class AuthApi extends ResourceController
{
    protected $format = 'json';
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        $aturanValidasi = [
            'nis'       => 'required|numeric',
            'password'  => 'required',
            'device_id' => 'permit_empty|string'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $nis      = (string) $this->request->getPost('nis');
        $password = (string) $this->request->getPost('password');
        $deviceId = (string) $this->request->getPost('device_id');

        // Eksekusi logika kompleks melalui Service
        $result = $this->authService->attemptLogin($nis, $password, $deviceId);

        if ($result['status'] === 404) {
            return $this->failNotFound($result['message']);
        }

        if ($result['status'] === 401) {
            return $this->failUnauthorized($result['message']);
        }

        if ($result['status'] === 200) {
            return $this->respond([
                'status'  => 200,
                'message' => $result['message'],
                'token'   => $result['token'],
                'data'    => $result['data']
            ]);
        }

        return $this->failServerError('Terjadi kesalahan pada server saat memproses login.');
    }

    public function refresh()
    {
        $refreshToken = (string) $this->request->getPost('refresh_token');

        if (empty($refreshToken)) {
            return $this->failValidationErrors('Refresh token wajib disertakan.');
        }

        $result = $this->authService->refreshAccessToken($refreshToken);

        if ($result['status'] === 401) {
            return $this->failUnauthorized($result['message']);
        }

        return $this->respond([
            'status'       => 200,
            'message'      => 'Token berhasil diperbarui.',
            'access_token' => $result['access_token']
        ]);
    }
}
