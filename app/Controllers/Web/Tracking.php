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

    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === session()->get('kelas_id');
        }
        return true;
    }

    public function index(string|null $targetId = null)
    {
        $keyword = $this->request->getGet('keyword');
        $config = $this->pengaturanModel->find(1);

        // ✅ PERBAIKAN: Menambahkan siswa.device_id ke dalam select data
        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, siswa.device_id, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (session()->get('is_wali_kelas')) {
            $this->siswaModel->where('siswa.kelas_id', session()->get('kelas_id'));
        }

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

    public function getLocation(string $idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $points = [];
        $hariIni = \CodeIgniter\I18n\Time::now('Asia/Jakarta')->toDateString();
        $absen = $this->absensiModel->where(['siswa_id' => $idSiswa, 'tanggal' => $hariIni])->first();

        if ($absen && !empty($absen['lat_masuk']) && !empty($absen['long_masuk'])) {
            $points[] = [
                'lat'   => (float) $absen['lat_masuk'],
                'lng'   => (float) $absen['long_masuk'],
                'waktu' => $absen['jam_masuk'] . ' (Absen Masuk)',
                'tipe'  => 'riwayat'
            ];
        }

        if ($absen && !empty($absen['lat_pulang']) && !empty($absen['long_pulang'])) {
            $points[] = [
                'lat'   => (float) $absen['lat_pulang'],
                'lng'   => (float) $absen['long_pulang'],
                'waktu' => $absen['jam_pulang'] . ' (Absen Pulang)',
                'tipe'  => 'riwayat'
            ];
        }

        if (!empty($siswa['lat_terakhir']) && !empty($siswa['long_terakhir'])) {
            $points[] = [
                'lat'   => (float) $siswa['lat_terakhir'],
                'lng'   => (float) $siswa['long_terakhir'],
                'waktu' => date('H:i:s', strtotime($siswa['updated_at'])) . ' (Live Update)',
                'tipe'  => 'live'
            ];
        }

        if (empty($points)) {
            return $this->response->setJSON(['status' => 'pending', 'message' => 'Belum ada data lokasi terbaru.']);
        }

        $uniquePoints = [];
        $lastCoord = '';
        foreach ($points as $pt) {
            $coord = $pt['lat'] . ',' . $pt['lng'];
            if ($coord !== $lastCoord) {
                $uniquePoints[] = $pt;
                $lastCoord = $coord;
            } else {
                $lastIndex = count($uniquePoints) - 1;
                $uniquePoints[$lastIndex]['waktu'] .= ' & ' . $pt['waktu'];
                $uniquePoints[$lastIndex]['tipe'] = $pt['tipe'];
            }
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $uniquePoints]);
    }

    public function pingSiswa(string $idSiswa)
    {
        helper('fcm');
        $siswa = $this->siswaModel->find($idSiswa);

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Data siswa tidak ditemukan atau diluar akses.']);
        }

        if (empty($siswa['fcm_token'])) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Perangkat siswa belum tersambung (Token FCM Kosong).']);
        }

        $result = send_fcm_notification(
            (string) $siswa['fcm_token'],
            "PING_LOCATION",
            "Admin meminta pembaruan lokasi radar.",
            ['action' => 'fetch_location']
        );

        if ($result === false) {
            return $this->response->setJSON(['status' => 500, 'message' => 'Gagal menghubungi Firebase Cloud.']);
        }

        $responseJson = json_decode($result, true);

        if (isset($responseJson['error'])) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Error HP Siswa: ' . $responseJson['error']['message']]);
        }

        return $this->response->setJSON(['status' => 200, 'message' => 'Sinyal PING berhasil dikirim ke perangkat.']);
    }
}
