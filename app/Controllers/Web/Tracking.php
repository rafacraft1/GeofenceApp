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
     * PRIVATE HELPER: Memastikan keamanan akses Row-Level Security
     */
    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === session()->get('kelas_id');
        }
        return true;
    }

    /**
     * Menampilkan halaman utama Radar Tracking
     */
    public function index(string|null $targetId = null)
    {
        $keyword = $this->request->getGet('keyword');
        $config = $this->pengaturanModel->find(1);

        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        // PROTEKSI WALI KELAS: Batasi siswa yang dirender ke map
        if (session()->get('is_wali_kelas')) {
            $this->siswaModel->where('siswa.kelas_id', session()->get('kelas_id'));
        }

        // Filter Pencarian Backend
        if (!empty($keyword)) {
            $this->siswaModel->groupStart()
                ->like('siswa.nama_siswa', $keyword)
                ->orLike('siswa.nis', $keyword)
                ->groupEnd();
        }

        $listSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        $data = [
            'title'      => 'Radar Live Tracking',
            'config'     => $config,
            'list_siswa' => $listSiswa,
            'keyword'    => $keyword,
            'target_id'  => $targetId
        ];

        return view('web/tracking', $data);
    }

    /**
     * Endpoint API Internal untuk AJAX Frontend
     */
    public function getLocation(string $idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);

        // Keamanan API: Tolak jika ID siswa bukan dari kelasnya
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Siswa tidak ditemukan atau diluar jangkauan akses Anda.']);
        }

        if (!empty($siswa['lat_terakhir']) && !empty($siswa['long_terakhir'])) {
            return $this->response->setJSON([
                'status'      => 200,
                'lat'         => (float) $siswa['lat_terakhir'],
                'lng'         => (float) $siswa['long_terakhir'],
                'nama'        => $siswa['nama_siswa'],
                'last_update' => $siswa['updated_at']
            ]);
        }

        $hariIni = \CodeIgniter\I18n\Time::now('Asia/Jakarta')->toDateString();
        $absen = $this->absensiModel->where(['siswa_id' => $idSiswa, 'tanggal' => $hariIni])->first();

        if ($absen && ($absen['lat_masuk'] || $absen['lat_pulang'])) {
            $lat = $absen['lat_pulang'] ?? $absen['lat_masuk'];
            $lng = $absen['long_pulang'] ?? $absen['long_masuk'];
            $waktu = $absen['jam_pulang'] ?? $absen['jam_masuk'];

            return $this->response->setJSON([
                'status'      => 200,
                'lat'         => (float) $lat,
                'lng'         => (float) $lng,
                'nama'        => $siswa['nama_siswa'],
                'last_update' => $waktu
            ]);
        }

        return $this->response->setJSON(['status' => 404, 'message' => 'Belum ada data lokasi terbaru.']);
    }

    /**
     * Endpoint API Internal untuk Ping Paksa
     */
    public function pingSiswa(string $idSiswa)
    {
        helper('fcm');
        $siswa = $this->siswaModel->find($idSiswa);

        // Keamanan API: Cegah PING paksa ke device siswa kelas lain
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Data siswa tidak ditemukan atau diluar akses.']);
        }

        if (empty($siswa['fcm_token'])) {
            return $this->response->setJSON([
                'status'  => 400,
                'message' => 'Token FCM Kosong!'
            ]);
        }

        $result = send_fcm_notification(
            (string) $siswa['fcm_token'],
            "PING_LOCATION",
            "Admin meminta pembaruan lokasi radar.",
            ['action' => 'fetch_location']
        );

        if ($result === false) {
            return $this->response->setJSON(['status' => 500, 'message' => 'Gagal menghubungi server Google.']);
        }

        $responseJson = json_decode($result, true);

        if (isset($responseJson['error'])) {
            return $this->response->setJSON([
                'status'  => 400,
                'message' => 'FIREBASE ERROR: ' . $responseJson['error']['message']
            ]);
        }

        return $this->response->setJSON(['status' => 200, 'message' => 'Sinyal PING berhasil dikirim ke perangkat.']);
    }
}
