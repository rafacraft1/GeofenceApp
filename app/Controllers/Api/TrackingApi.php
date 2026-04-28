<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use Kreait\Firebase\Factory; // Library Firebase PHP

class TrackingApi extends ResourceController
{
    public function update_lokasi()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('api_token', $token)->get()->getRow();

        if (!$siswa || $siswa->is_blocked == 1) return $this->failUnauthorized('Sesi tidak valid.');

        $lat = $this->request->getPost('lat');
        $lon = $this->request->getPost('long');
        $waktu = Time::now('Asia/Jakarta')->toDateTimeString();

        // 1. Simpan ke MySQL (Sebagai Riwayat/Log)
        $db->table('riwayat_lokasi')->insert([
            'siswa_id'    => $siswa->id,
            'latitude'    => $lat,
            'longitude'   => $lon,
            'waktu_rekam' => $waktu
        ]);

        // 2. Tembak ke Firebase Realtime Database (Untuk Peta Live Admin)
        try {
            $factory = (new Factory)
                ->withServiceAccount(APPPATH . 'Config/firebase_credentials.json')
                ->withDatabaseUri('https://geofenceapp-96e7c-default-rtdb.asia-southeast1.firebasedatabase.app/'); // GANTI DENGAN URL FIREBASE ANDA
            $database = $factory->createDatabase();
            $database->getReference('live_tracking/' . $siswa->id)->set([
                'lat'   => $lat,
                'long'  => $lon,
                'waktu' => $waktu,
                'nama'  => $siswa->nama_lengkap,
                'kelas' => $siswa->kelas
            ]);
        } catch (\Exception $e) {
            // Bypass error jika firebase belum disetup agar API Android tidak crash
            log_message('error', 'Firebase Error: ' . $e->getMessage());
        }

        return $this->respond(['status' => 'ok']);
    }
}
