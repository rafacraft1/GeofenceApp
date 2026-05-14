<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\PengaturanModel;
use App\Models\AbsensiModel;

class Tracking extends BaseController
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected PengaturanModel $pengaturanModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->siswaModel      = new SiswaModel();
        $this->kelasModel      = new KelasModel();
        $this->pengaturanModel = new PengaturanModel();
        $this->absensiModel    = new AbsensiModel();
    }

    /**
     * Menampilkan halaman utama Radar Tracking
     */
    public function index(string|null $targetId = null)
    {
        $kelasFilter = $this->request->getGet('kelas_id');

        $config    = $this->pengaturanModel->find(1);
        $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->siswaModel->where('siswa.kelas_id', $kelasFilter);
        }

        $listSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        $data = [
            'title'       => 'Radar Live Tracking',
            'config'      => $config,
            'list_siswa'  => $listSiswa,
            'list_kelas'  => $listKelas,
            'kelas_aktif' => $kelasFilter,
            'target_id'   => $targetId
        ];

        return view('web/tracking', $data);
    }

    /**
     * Endpoint API Internal untuk mendapatkan lokasi real-time siswa (AJAX)
     */
    public function getLocation(string $idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);

        // KUNCI REAL-TIME: Mengambil dari koordinat live di tabel siswa
        if ($siswa && !empty($siswa['lat_terakhir']) && !empty($siswa['long_terakhir'])) {
            return $this->response->setJSON([
                'status'      => 200,
                'lat'         => (float) $siswa['lat_terakhir'],
                'lng'         => (float) $siswa['long_terakhir'],
                'nama'        => $siswa['nama_siswa'],
                'last_update' => $siswa['updated_at'] // Waktu pembaruan radar terakhir
            ]);
        }

        return $this->response->setJSON([
            'status'  => 404,
            'message' => 'Belum ada sinyal radar dari perangkat siswa.'
        ]);
    }

    /**
     * Endpoint API Internal untuk melakukan PING paksa ke perangkat siswa (FCM HTTP v1)
     */
    public function pingSiswa(string $idSiswa)
    {
        helper('fcm');
        $siswa = $this->siswaModel->find($idSiswa);

        if (!$siswa) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Data siswa tidak ditemukan.']);
        }

        // 1. Validasi Keberadaan Token
        if (empty($siswa['fcm_token'])) {
            return $this->response->setJSON([
                'status'  => 400,
                'message' => 'Token FCM Kosong! Pastikan siswa sudah login di aplikasi terbaru.'
            ]);
        }

        // 2. Eksekusi Pengiriman via Helper (HTTP v1)
        $result = send_fcm_notification(
            (string) $siswa['fcm_token'],
            "PING_LOCATION",
            "Admin meminta pembaruan lokasi radar.",
            ['action' => 'fetch_location'] // Payload yang ditunggu oleh main.dart Flutter
        );

        if ($result === false) {
            return $this->response->setJSON([
                'status'  => 500,
                'message' => 'Gagal menghubungi server Google. Cek koneksi internet server.'
            ]);
        }

        $responseJson = json_decode($result, true);

        // 3. Analisa Respon Google Firebase
        if (isset($responseJson['error'])) {
            $errorMsg = $responseJson['error']['message'] ?? 'Unknown Firebase Error';
            return $this->response->setJSON([
                'status'  => 400,
                'message' => 'FIREBASE ERROR: ' . $errorMsg
            ]);
        }

        return $this->response->setJSON([
            'status'  => 200,
            'message' => 'Sinyal PING berhasil dikirim ke perangkat.'
        ]);
    }
}
