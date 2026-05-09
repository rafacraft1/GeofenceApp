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

    public function updateToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        $fcmToken = $this->request->getPost('fcm_token');

        if (!$fcmToken) return $this->fail('FCM Token wajib dikirim.');

        $siswa = $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
        if (!$siswa) return $this->failUnauthorized('Sesi tidak valid.');

        $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update([
            'fcm_token' => $fcmToken,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(['status' => 200, 'message' => 'Token FCM berhasil diperbarui.']);
    }
}
