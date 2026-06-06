<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController; // Gunakan BaseController untuk akses helper
use App\Models\AbsensiModel;
use App\Models\SiswaModel;
use App\Services\AbsensiService;
use CodeIgniter\RESTful\ResourceController;

class AbsensiApi extends ResourceController
{
    protected AbsensiModel $absensiModel;
    protected SiswaModel $siswaModel;
    protected AbsensiService $absensiService;

    public function __construct()
    {
        $this->absensiModel   = new AbsensiModel();
        $this->siswaModel     = new SiswaModel();
        $this->absensiService = new AbsensiService();
    }

    public function submit()
    {
        $json = $this->request->getJSON(true);
        $siswaId  = $json['siswa_id'] ?? null;
        $lat      = (float) ($json['lat'] ?? 0);
        $lon      = (float) ($json['lon'] ?? 0);
        $isFake   = (int) ($json['is_fake_gps'] ?? 0);

        // 1. Validasi Service (Pusat logika Geofence)
        $validasi = $this->absensiService->validasiGeofencing($lat, $lon, $isFake);

        if (!$validasi['is_valid']) {
            return $this->respond([
                'status'  => 'error',
                'message' => $validasi['message']
            ], 400);
        }

        // 2. Simpan ke Database
        $this->absensiModel->insert([
            'siswa_id'   => $siswaId,
            'tanggal'    => date('Y-m-d'),
            'jam_masuk'  => date('H:i:s'),
            'status'     => 'Hadir',
            'is_fake_gps' => $isFake
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Absensi berhasil!']);
    }
}
