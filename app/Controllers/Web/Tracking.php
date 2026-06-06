<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use CodeIgniter\I18n\Time;

class Tracking extends BaseController
{
    protected SiswaModel $siswaModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->absensiModel = new AbsensiModel();
    }

    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === session()->get('kelas_id');
        }
        return true;
    }

    public function index()
    {
        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = session()->get('kelas_id');

        $query = $this->siswaModel->select('siswa.id_siswa, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if ($isWaliKelas) {
            $query->where('siswa.kelas_id', $kelasId);
        }

        $data = [
            'title'      => 'Live Tracking Radar',
            'list_siswa' => $query->orderBy('siswa.nama_siswa', 'ASC')->findAll(),
            // NAMA VARIABEL DIUBAH MENJADI 'pengaturan' AGAR TIDAK BENTROK DENGAN CLASS PHP
            'pengaturan' => \Config\Database::connect()->table('pengaturan')->get()->getRowArray()
        ];

        return view('web/tracking', $data);
    }

    public function poll(string $idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $hariIni = Time::now('Asia/Jakarta')->toDateString();
        $absen   = $this->absensiModel->where(['siswa_id' => $idSiswa, 'tanggal' => $hariIni])->first();

        $routes = [];
        if (!empty($absen['lat_masuk'])) {
            $routes[] = ['lat' => $absen['lat_masuk'], 'lng' => $absen['long_masuk'], 'waktu' => $absen['jam_masuk']];
        }
        if (!empty($absen['lat_pulang'])) {
            $routes[] = ['lat' => $absen['lat_pulang'], 'lng' => $absen['long_pulang'], 'waktu' => $absen['jam_pulang']];
        }
        if (!empty($siswa['lat_terakhir'])) {
            $routes[] = ['lat' => $siswa['lat_terakhir'], 'lng' => $siswa['long_terakhir'], 'waktu' => 'Terkini'];
        }

        if (empty($routes)) {
            return $this->response->setJSON(['status' => 'pending']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $routes]);
    }

    public function ping(int $idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);
        if (!$siswa || empty($siswa['fcm_token']) || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Token tidak ditemukan.']);
        }

        helper('fcm');
        $payload = ['type' => 'trigger_tracking', 'action' => 'force_location_capture'];

        send_fcm_notification($siswa['fcm_token'], '', '', $payload);
        return $this->response->setJSON(['status' => 'success']);
    }
}
