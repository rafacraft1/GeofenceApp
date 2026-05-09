<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class FcmApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function getSiswaAuth()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = \str_replace('Bearer ', '', $authHeader);

        if (empty($token)) return null;

        return $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
    }

    public function updateToken()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) {
            return $this->failUnauthorized('Sesi tidak valid atau telah kedaluwarsa.');
        }

        $fcmToken = (string) $this->request->getPost('fcm_token');

        if (empty($fcmToken)) {
            return $this->failValidationErrors('FCM Token wajib dikirim.');
        }

        $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update([
            'fcm_token'  => $fcmToken,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Token FCM berhasil diperbarui.'
        ]);
    }
}
