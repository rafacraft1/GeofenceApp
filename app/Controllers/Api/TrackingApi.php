<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SiswaModel;

class TrackingApi extends ResourceController
{
    // Perbaikan Type Hinting untuk menghilangkan warning Intelephense
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        // Memanggil helper FCM bawaan project Anda dan helper Cache
        helper(['fcm', 'date']);
    }

    /**
     * 1. DIPANGGIL OLEH WEB ADMIN
     * Membangunkan HP siswa secara diam-diam via FCM Data Message
     */
    public function triggerTracking(int|string $siswaId) // Tambahan type hint int|string
    {
        $siswa = $this->siswaModel->find($siswaId);
        if (!$siswa || empty($siswa['fcm_token'])) {
            return $this->failNotFound('Siswa atau Token FCM tidak ditemukan.');
        }

        // Payload data khusus (tanpa notification agar silent)
        // Nilai dikirim sebagai string karena FCM HTTP v1 mensyaratkan value dari data array berupa string
        $dataPayload = [
            'type'      => 'trigger_tracking',
            'timestamp' => (string) time(),
            'action'    => 'force_location_capture'
        ];

        // PERBAIKAN ERROR 1: Panggil fungsi yang benar dari fcm_helper.php
        $fcmResult = send_fcm_notification(
            $siswa['fcm_token'],
            '', // Title kosong
            '', // Body kosong
            $dataPayload
        );

        return $this->respond([
            'status'  => 'success',
            'message' => 'Sinyal pelacakan telah dikirim ke perangkat siswa.',
            'fcm_log' => json_decode((string)$fcmResult, true) ?? $fcmResult
        ]);
    }

    /**
     * 2. DIPANGGIL OLEH APLIKASI FLUTTER
     * Menerima array 4 lokasi (3 riwayat lokal + 1 saat ini) dan menyimpannya di Cache CI4
     */
    public function storeLocation()
    {
        $json = $this->request->getJSON(true);
        $siswaId = $json['siswa_id'] ?? null;
        $locations = $json['locations'] ?? [];

        if (empty($siswaId) || empty($locations)) {
            // PERBAIKAN ERROR 2: Gunakan fungsi bawaan CI4 failValidationErrors
            return $this->failValidationErrors(['error' => 'Data siswa_id dan array locations wajib dikirim.']);
        }

        // MENGGUNAKAN CACHE SEBAGAI JEMBATAN SEMENTARA (TTL: 60 Detik)
        // Memori akan otomatis bersih setelah 60 detik (tidak membebani database)
        $cacheKey = 'tracking_siswa_' . $siswaId;
        cache()->save($cacheKey, $locations, 60);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Data 4 titik lokasi berhasil diunggah ke memori sementara server.'
        ]);
    }

    /**
     * 3. DIPANGGIL OLEH WEB ADMIN (POLLING)
     * Mengambil data dari Cache untuk digambar di peta web
     */
    public function pollLocation(int|string $siswaId) // Tambahan type hint int|string
    {
        $cacheKey = 'tracking_siswa_' . $siswaId;
        $dataLokasi = cache($cacheKey);

        if ($dataLokasi) {
            return $this->respond([
                'status' => 'success',
                'data'   => $dataLokasi
            ]);
        }

        // Jika cache kosong, berarti HP Flutter belum merespon
        return $this->respond([
            'status'  => 'pending',
            'message' => 'Menunggu balasan lokasi dari perangkat siswa...'
        ]);
    }
}
