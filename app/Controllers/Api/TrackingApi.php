<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Models\SiswaModel;

class TrackingApi extends ResourceController
{
    protected SiswaModel $siswaModel;
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->db         = \Config\Database::connect();
        helper(['fcm', 'date']);
    }

    /**
     * @param int $idSiswa
     * @param string $jenisPelanggaran
     * @return void
     */
    private function catatFraud(int $idSiswa, string $jenisPelanggaran): void
    {
        $siswa = $this->siswaModel->find($idSiswa);
        $newFraudCount = (int) ($siswa['fraud_count'] ?? 0) + 1;
        $isBlocked = ($newFraudCount >= 3) ? 1 : 0;

        $this->siswaModel->update($idSiswa, [
            'fraud_count' => $newFraudCount,
            'is_blocked'  => $isBlocked
        ]);

        $this->db->table('log_fraud')->insert([
            'siswa_id'          => $idSiswa,
            'jenis_pelanggaran' => $jenisPelanggaran,
            'waktu_kejadian'    => Time::now('Asia/Jakarta')->toDateTimeString()
        ]);
    }

    /**
     * @param int|string $siswaId
     * @return mixed
     */
    public function triggerTracking(int|string $siswaId)
    {
        $siswa = $this->siswaModel->find($siswaId);
        if (!$siswa || empty($siswa['fcm_token'])) {
            return $this->failNotFound('Siswa atau Token FCM tidak ditemukan.');
        }

        $dataPayload = [
            'type'      => 'trigger_tracking',
            'timestamp' => (string) time(),
            'action'    => 'force_location_capture'
        ];

        $fcmResult = send_fcm_notification((string)$siswa['fcm_token'], '', '', $dataPayload);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Sinyal pelacakan telah dikirim ke perangkat siswa.',
            'fcm_log' => json_decode((string)$fcmResult, true) ?? $fcmResult
        ]);
    }

    /**
     * @return mixed
     */
    public function storeLocation()
    {
        $siswaAuth = $this->request->siswaAuth ?? [];
        $siswaId   = $siswaAuth['id_siswa'] ?? null;

        if (!$siswaId) {
            return $this->failUnauthorized('Sesi token tidak valid.');
        }

        $json      = $this->request->getJSON(true);
        $locations = $json['locations'] ?? [];

        if (empty($locations)) {
            return $this->failValidationErrors(['error' => 'Array locations wajib dikirim.']);
        }

        $validLocations = [];
        $hasMock        = false;

        foreach ($locations as $loc) {
            $isMock   = (int) ($loc['is_mock'] ?? 0);
            $accuracy = (float) ($loc['accuracy'] ?? 999);

            if ($isMock === 1) {
                $hasMock = true;
                continue;
            }

            if ($accuracy > 100) {
                continue;
            }

            $validLocations[] = $loc;
        }

        if ($hasMock) {
            $this->catatFraud((int) $siswaId, 'Mock Location (Fake GPS) terdeteksi saat Pelacakan Real-time (Background)');
        }

        if (!empty($validLocations)) {
            $cacheKey = 'tracking_siswa_' . $siswaId;
            cache()->save($cacheKey, $validLocations, 60);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Data lokasi berhasil disinkronisasi dengan server.'
        ]);
    }

    /**
     * @param int|string $siswaId
     * @return mixed
     */
    public function pollLocation(int|string $siswaId)
    {
        $cacheKey   = 'tracking_siswa_' . $siswaId;
        $dataLokasi = cache($cacheKey);

        if ($dataLokasi) {
            return $this->respond([
                'status' => 'success',
                'data'   => $dataLokasi
            ]);
        }

        return $this->respond([
            'status'  => 'pending',
            'message' => 'Menunggu balasan lokasi dari perangkat siswa...'
        ]);
    }
}
