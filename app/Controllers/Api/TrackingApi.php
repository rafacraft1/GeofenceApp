<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class TrackingApi extends ResourceController
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
        $token = str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        return $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
    }

    // Refactored: update_lokasi -> updateLokasi
    public function updateLokasi()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi tidak valid.');

        $lat = $this->request->getPost('lat');
        $lon = $this->request->getPost('long');

        if (!$lat || !$lon) return $this->fail('Koordinat tidak lengkap.');

        // Update database untuk koordinat terakhir (Internal Monitoring)
        $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update([
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(['status' => 200, 'message' => 'Lokasi berhasil diperbarui.']);
    }

    // Refactored: ping_siswa -> pingSiswa
    public function pingSiswa(string $targetId)
    {
        helper('fcm');

        $siswa = $this->db->table('siswa')->where('id_siswa', $targetId)->get()->getRowArray();
        if (!$siswa || empty($siswa['fcm_token'])) {
            return $this->failNotFound('Siswa tidak ditemukan atau HP sedang offline.');
        }

        // Kirim sinyal "SILENT PING" ke HP Siswa agar app Android mengirim lokasi terbaru
        $result = send_fcm_notification(
            (string) $siswa['fcm_token'],
            "PING_LOCATION",
            "Permintaan lokasi real-time dari Admin.",
            ['action' => 'fetch_location'] // Data payload untuk trigger background service di Android
        );

        if ($result) {
            return $this->respond(['status' => 200, 'message' => 'Sinyal ping terkirim ke perangkat siswa.']);
        }

        return $this->failServerError('Gagal mengirim sinyal ping.');
    }
}
