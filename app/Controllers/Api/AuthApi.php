<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;
use App\Models\SiswaModel;

class AuthApi extends ResourceController
{
    protected $format = 'json';
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * @return mixed
     */
    public function login()
    {
        $aturanValidasi = [
            'nis'       => 'required',
            'password'  => 'required',
            'device_id' => 'permit_empty|string',
            'fcm_token' => 'permit_empty|string'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $nis      = (string) $this->request->getPost('nis');
        $password = (string) $this->request->getPost('password');
        $deviceId = (string) $this->request->getPost('device_id');
        $fcmToken = (string) $this->request->getPost('fcm_token');

        $result = $this->authService->attemptLogin($nis, $password, $deviceId);

        if ($result['status'] === 404) {
            return $this->failNotFound($result['message']);
        }

        if ($result['status'] === 401 || $result['status'] === 403) {
            return $this->respond(['status' => $result['status'], 'message' => $result['message']], $result['status']);
        }

        if (!empty($fcmToken) && isset($result['data']['id_siswa'])) {
            $siswaModel = new SiswaModel();
            $siswaModel->update($result['data']['id_siswa'], ['fcm_token' => $fcmToken]);
        }

        return $this->respond([
            'status'        => 200,
            'message'       => $result['message'],
            'access_token'  => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'data'          => $result['data']
        ]);
    }

    /**
     * @return mixed
     */
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
            'status'        => 200,
            'message'       => 'Token berhasil diperbarui.',
            'access_token'  => $result['access_token'],
            'refresh_token' => $result['refresh_token']
        ]);
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        $siswa = $this->request->siswaAuth ?? null;

        if (!$siswa) {
            return $this->failUnauthorized('Akses ditolak.');
        }

        $result = $this->authService->logoutSiswa((int) $siswa['id_siswa']);

        return $this->respond([
            'status'  => 200,
            'message' => $result['message']
        ]);
    }
}
