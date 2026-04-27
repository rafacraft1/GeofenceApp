<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class TrackingApi extends ResourceController
{
    public function update_lokasi()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        $db = \Config\Database::connect();
        // INTEGRASI POIN 2: Cek berdasarkan api_token
        $siswa = $db->table('siswa')->where('api_token', $token)->get()->getRow();

        if (!$siswa || $siswa->is_blocked == 1) {
            return $this->failUnauthorized('Sesi tidak valid.');
        }

        $db->table('riwayat_lokasi')->insert([
            'siswa_id'    => $siswa->id,
            'latitude'    => $this->request->getPost('lat'),
            'longitude'   => $this->request->getPost('long'),
            'waktu_rekam' => Time::now('Asia/Jakarta')->toDateTimeString()
        ]);

        return $this->respond(['status' => 'ok']);
    }
}
