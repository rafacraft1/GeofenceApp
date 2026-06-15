<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SiswaModel;

class FcmApi extends ResourceController
{
    protected $format = 'json';
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    private function getSiswaAuth(): array
    {
        /** @var mixed $request */
        $request = $this->request;
        return (array) $request->siswaAuth;
    }

    public function updateToken()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'fcm_token' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors('FCM Token wajib dikirim.');
        }

        // PERBAIKAN: Gunakan getVar() agar bisa membaca raw JSON payload dari aplikasi Flutter
        $fcmToken = (string) $this->request->getVar('fcm_token');

        $this->siswaModel->update($siswa['id_siswa'], [
            'fcm_token' => $fcmToken
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Token FCM berhasil diperbarui.'
        ]);
    }
}
