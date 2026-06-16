<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\ZonaModel;
use App\Models\AbsensiModel;

class Tracking extends BaseController
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected ZonaModel $zonaModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->kelasModel   = new KelasModel();
        $this->zonaModel    = new ZonaModel();
        $this->absensiModel = new AbsensiModel();
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

        $zonaDefault = $this->zonaModel->getDefaultZona() ?? [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius' => 50
        ];

        // Join ke tabel zona_absensi untuk menampilkan spesifik zona PKL (jika ada)
        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, siswa.device_id, kelas.nama_kelas, zona_absensi.latitude as zona_lat, zona_absensi.longitude as zona_lng, zona_absensi.radius as zona_radius')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->join('zona_absensi', 'zona_absensi.id_zona = siswa.zona_id', 'left');

        if (session()->get('is_wali_kelas')) {
            $this->siswaModel->where('siswa.kelas_id', session()->get('kelas_id'));
        }

        if (!empty($keyword)) {
            $this->siswaModel->groupStart()
                ->like('siswa.nama_siswa', $keyword)
                ->orLike('siswa.nis', $keyword)
                ->groupEnd();
        }

        $listSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')->orderBy('siswa.nama_siswa', 'ASC')->findAll();

        $data = [
            'title'        => 'Radar Live Tracking',
            'zona_default' => $zonaDefault,
            'list_siswa'   => $listSiswa,
            'keyword'      => $keyword,
            'target_id'    => $targetId
        ];

        return view('web/tracking', $data);
    }

    public function getLocation(string $idSiswa)
    {
        $siswa = cache()->remember('siswa_track_' . $idSiswa, 300, function () use ($idSiswa) {
            return $this->siswaModel->find($idSiswa);
        });

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $points = [];
        $hariIni = \CodeIgniter\I18n\Time::now('Asia/Jakarta')->toDateString();

        $absen = cache()->remember('absen_track_' . $idSiswa . '_' . $hariIni, 60, function () use ($idSiswa, $hariIni) {
            return $this->absensiModel->where(['siswa_id' => $idSiswa, 'tanggal' => $hariIni])->first();
        });

        if ($absen && !empty($absen['lat_masuk']) && !empty($absen['long_masuk'])) {
            $points[] = [
                'lat' => (float) $absen['lat_masuk'],
                'lng' => (float) $absen['long_masuk'],
                'waktu' => $absen['jam_masuk'] . ' (Absen Masuk)',
                'tipe' => 'riwayat'
            ];
        }

        $liveLocations = cache('tracking_siswa_' . $idSiswa);

        if (is_array($liveLocations) && !empty($liveLocations)) {
            foreach ($liveLocations as $loc) {
                $lat = $loc['lat'] ?? $loc['latitude'] ?? 0;
                $lng = $loc['lng'] ?? $loc['longitude'] ?? 0;
                $waktuStr = $loc['waktu'] ?? $loc['timestamp'] ?? date('H:i:s');

                if ($lat != 0 && $lng != 0) {
                    $points[] = ['lat' => (float) $lat, 'lng' => (float) $lng, 'waktu' => $waktuStr . ' (Live Track)', 'tipe' => 'live'];
                }
            }
        } elseif (!empty($siswa['lat_terakhir']) && !empty($siswa['long_terakhir'])) {
            $points[] = ['lat' => (float) $siswa['lat_terakhir'], 'lng' => (float) $siswa['long_terakhir'], 'waktu' => date('H:i:s', strtotime((string)$siswa['updated_at'])) . ' (Live Terakhir)', 'tipe' => 'live'];
        }

        if ($absen && !empty($absen['lat_pulang']) && !empty($absen['long_pulang'])) {
            $points[] = ['lat' => (float) $absen['lat_pulang'], 'lng' => (float) $absen['long_pulang'], 'waktu' => $absen['jam_pulang'] . ' (Absen Pulang)', 'tipe' => 'riwayat'];
        }

        if (empty($points)) return $this->response->setJSON(['status' => 'pending', 'message' => 'Belum ada data lokasi terbaru.']);

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

        // Selalu return 'csrf_token' untuk memperbarui kunci pada Javascript browser
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Data siswa tidak ditemukan atau diluar akses.',
                'csrf_token' => csrf_hash()
            ]);
        }
        if (empty($siswa['fcm_token'])) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Perangkat siswa belum tersambung (Token FCM Kosong).',
                'csrf_token' => csrf_hash()
            ]);
        }

        $result = send_fcm_notification((string)$siswa['fcm_token'], "Pengingat Absensi", "Jangan Lupa Absen Masuk/Pulang Hari ini", ['action' => 'fetch_location']);

        if ($result === false) return $this->response->setJSON([
            'status' => 500,
            'message' => 'Gagal menghubungi Firebase Cloud.',
            'csrf_token' => csrf_hash()
        ]);

        $responseJson = json_decode($result, true);
        if (isset($responseJson['error'])) return $this->response->setJSON([
            'status' => 400,
            'message' => 'Error HP Siswa: ' . $responseJson['error']['message'],
            'csrf_token' => csrf_hash()
        ]);

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Sinyal PING berhasil dikirim ke perangkat.',
            'csrf_token' => csrf_hash()
        ]);
    }
}
