<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;

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
        $nis      = $this->request->getPost('nis');
        $password = (string) $this->request->getPost('password');
        $deviceId = $this->request->getPost('device_id');

        if (!$nis || !$password) {
            return $this->failValidationErrors('NIS dan Password wajib diisi.');
        }

        // 1. Eksekusi seluruh logika kompleks melalui Service
        $result = $this->authService->attemptLogin($nis, $password, $deviceId);

        // 2. Mapping format status ke standar RESTful Response CI4
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
}
