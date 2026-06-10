<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SiswaModel;

class TrackingApi extends ResourceController
{
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        helper(['fcm', 'date']);
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

        $fcmResult = send_fcm_notification($siswa['fcm_token'], '', '', $dataPayload);

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

        $cacheKey = 'tracking_siswa_' . $siswaId;
        cache()->save($cacheKey, $locations, 60);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Data lokasi berhasil diunggah ke memori sementara server.'
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
