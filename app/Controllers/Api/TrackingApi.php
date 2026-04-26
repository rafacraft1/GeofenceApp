<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class TrackingApi extends ResourceController
{
    public function update_lokasi()
    {
        $token = str_replace('Bearer ', '', $this->request->getHeaderLine('Authorization'));
        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('device_id', $token)->get()->getRow();

        if (!$siswa || $siswa->is_blocked == 1) return $this->failUnauthorized();

        $db->table('riwayat_lokasi')->insert([
            'siswa_id'    => $siswa->id,
            'latitude'    => $this->request->getPost('lat'),
            'longitude'   => $this->request->getPost('long'),
            'waktu_rekam' => Time::now('Asia/Jakarta')->toDateTimeString()
        ]);

        return $this->respond(['status' => 'ok']);
    }
}
