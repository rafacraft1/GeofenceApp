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

        if (!$siswa || $siswa->is_blocked == 1) {
            return $this->failUnauthorized('Sesi tidak valid atau akun diblokir.');
        }

        $rawLat = $this->request->getPost('lat');
        $rawLon = $this->request->getPost('long');

        if ($rawLat === null || $rawLon === null || $rawLat === '' || $rawLon === '') {
            return $this->failValidationErrors('Koordinat latitude dan longitude wajib dikirim.');
        }

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
            return $this->respondCreated([
                'status'  => 200,
                'message' => 'Tersimpan lokal. Firebase belum disetting.'
            ]);
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount(APPPATH . 'Config/firebase_credentials.json')
                ->withDatabaseUri($config->firebase_url);

            $database = $factory->createDatabase();

            $database->getReference('live_tracking/' . $siswa->id)->set([
                'lat'   => $lat,
                'long'  => $lon,
                'waktu' => $waktu,
                'nama'  => $siswa->nama_lengkap,
                'kelas' => $siswa->kelas
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Firebase Error: ' . $e->getMessage());

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

    // ==========================================
    // API TRIGGER TRACKING (DIPANGGIL OLEH WEB ADMIN)
    // ==========================================
    public function ping_siswa($siswa_id = null)
    {
        if (!$siswa_id) {
            return $this->failValidationErrors('ID Siswa wajib diisi.');
        }

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('id', $siswa_id)->get()->getRow();

        if (!$siswa) {
            return $this->failNotFound('Data siswa tidak ditemukan.');
        }

        if (empty($siswa->fcm_token)) {
            return $this->fail('Siswa ini belum memiliki FCM Token (Belum login di aplikasi terbaru).');
        }

        try {
            // Inisialisasi Firebase Messaging
            $factory = (new Factory)->withServiceAccount(APPPATH . 'Config/firebase_credentials.json');
            $messaging = $factory->createMessaging();

            // Buat SILENT PUSH NOTIFICATION (Data Message Only)
            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $siswa->fcm_token)
                ->withData([
                    'action'    => 'TRACKING_REQUEST',
                    'timestamp' => time()
                ]);

            // Tembakkan ke HP Siswa
            $messaging->send($message);

            return $this->respond([
                'status'  => 200,
                'message' => 'Sinyal pelacakan berhasil ditembakkan ke HP Siswa. Menunggu respon dari aplikasi...'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Gagal mengirim FCM: ' . $e->getMessage());
            return $this->fail('Gagal mengirim sinyal pelacakan. Error: ' . $e->getMessage());
        }
    }
}
