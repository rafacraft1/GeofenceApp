<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use Kreait\Firebase\Factory;

class TrackingApi extends ResourceController
{
    public function update_lokasi()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('api_token', $token)->get()->getRow();

        // Cek validitas token dan status blokir
        if (!$siswa || $siswa->is_blocked == 1) {
            return $this->failUnauthorized('Sesi tidak valid atau akun diblokir.');
        }

        // Parsing data latitude dan longitude (dikunci ke format float agar kebal error/typo)
        $lat   = (float) $this->request->getPost('lat');
        $lon   = (float) $this->request->getPost('long');
        $waktu = Time::now('Asia/Jakarta')->toDateTimeString();

        // 1. Simpan ke Database MySQL (Riwayat Lokasi)
        $db->table('riwayat_lokasi')->insert([
            'siswa_id'    => $siswa->id,
            'latitude'    => $lat,
            'longitude'   => $lon,
            'waktu_rekam' => $waktu
        ]);

        // 2. Tembak ke Firebase Realtime Database
        $config = $db->table('pengaturan')->where('id', 1)->get()->getRow();

        if (!$config || empty($config->firebase_url)) {
            // Jika firebase_url kosong, tetap catat ke DB, namun beri tahu via API
            return $this->respond(['status' => 'ok', 'message' => 'Tersimpan lokal. Firebase belum disetting.']);
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount(APPPATH . 'Config/firebase_credentials.json')
                ->withDatabaseUri($config->firebase_url); // URL dinamis dari database

            $database = $factory->createDatabase();

            // Perbarui kordinat siswa di node /live_tracking/{id_siswa}
            $database->getReference('live_tracking/' . $siswa->id)->set([
                'lat'   => $lat,
                'long'  => $lon,
                'waktu' => $waktu,
                'nama'  => $siswa->nama_lengkap,
                'kelas' => $siswa->kelas
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Firebase Error: ' . $e->getMessage());
        }

        return $this->respond(['status' => 'ok']);
    }
}
