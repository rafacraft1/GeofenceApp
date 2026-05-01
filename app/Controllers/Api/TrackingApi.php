<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use Kreait\Firebase\Factory;

class TrackingApi extends ResourceController
{
    protected $format = 'json';

    public function update_lokasi()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token      = \str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            return $this->failUnauthorized('Token tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('api_token', $token)->get()->getRow();

        // Cek validitas token dan status blokir
        if (!$siswa || $siswa->is_blocked == 1) {
            return $this->failUnauthorized('Sesi tidak valid atau akun diblokir.');
        }

        $rawLat = $this->request->getPost('lat');
        $rawLon = $this->request->getPost('long');

        // === PERBAIKAN: Validasi keberadaan data sebelum di-cast ke float ===
        // Mencegah PHP merekam koordinat 0.0 jika data dari Android kosong
        if ($rawLat === null || $rawLon === null || $rawLat === '' || $rawLon === '') {
            return $this->failValidationErrors('Koordinat latitude dan longitude wajib dikirim.');
        }

        // Parsing data latitude dan longitude (dikunci ke format float agar kebal error/typo)
        $lat   = (float) $rawLat;
        $lon   = (float) $rawLon;
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
            return $this->respondCreated([
                'status'  => 200, 
                'message' => 'Tersimpan lokal. Firebase belum disetting.'
            ]);
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
            
            // === PERBAIKAN: Tetap return 200 karena MySQL sukses, tapi beri tahu Firebase error ===
            return $this->respondCreated([
                'status'  => 200, 
                'message' => 'Tersimpan lokal. Gagal sinkronisasi Firebase.'
            ]);
        }

        return $this->respondCreated([
            'status'  => 200, 
            'message' => 'Lokasi berhasil diperbarui dan disinkronkan ke Firebase.'
        ]);
    }
}