<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    protected SiswaModel $siswaModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->absensiModel = new AbsensiModel();
    }

    public function index()
    {
        // ✅ Optimasi: Inisialisasi waktu hanya 1x di awal
        $sekarang    = Time::now('Asia/Jakarta');
        $hariIni     = $sekarang->toDateString();

        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = $isWaliKelas ? session()->get('kelas_id') : null;

        // Identifier unik untuk Cache agar data Admin dan Wali Kelas tidak tertukar
        $cacheSuffix = $kelasId ?? 'all';

        // ✅ Optimasi 1: Cache Total Siswa (Valid 12 Jam)
        $totalSiswa = cache()->remember('total_siswa_' . $cacheSuffix, 43200, function () use ($isWaliKelas, $kelasId) {
            if ($isWaliKelas) {
                $this->siswaModel->where('kelas_id', $kelasId);
            }
            return $this->siswaModel->countAllResults();
        });

        // ⚡ Data Real-Time (Tetap akses langsung ke DB karena sangat sensitif waktu)
        $stats      = $this->absensiModel->getDashboardStats($hariIni, $kelasId);
        $distribusi = $this->absensiModel->getDashboardDistribution($hariIni, $kelasId);
        $manipulasi = $this->absensiModel->getFraudList($hariIni, $kelasId);

        // ✅ Optimasi 2: Cache Leaderboard (Valid 10 Menit)
        $topClasses = cache()->remember('leaderboard_' . $cacheSuffix, 600, function () use ($hariIni, $kelasId) {
            return $this->absensiModel->getLeaderboardKelas($hariIni, $kelasId);
        });

        $hadirHariIni = $stats['hadir'];
        $persenHadir  = ($totalSiswa > 0) ? round(($hadirHariIni / $totalSiswa) * 100) : 0;

        $grafikLabels    = [];
        $grafikHadir     = array_fill(0, 7, 0);
        $grafikTerlambat = array_fill(0, 7, 0);
        $grafikAlpa      = array_fill(0, 7, 0);
        $dates           = [];

        // ✅ Optimasi 3: Memanipulasi subDays tanpa memanggil ulang construct Time::now()
        for ($i = 6; $i >= 0; $i--) {
            // Gunakan subDays pada clone atau instance baru dari timestamp yang sama jika diperlukan
            // Namun di CI4, subDays memodifikasi objek, jadi lebih aman parse dari string
            $tgl = Time::parse($hariIni, 'Asia/Jakarta')->subDays($i)->toDateString();
            $dates[] = $tgl;
            $grafikLabels[] = date('d M', strtotime($tgl));
        }

        // ✅ Optimasi 4: Cache Data Tren Mingguan (Valid 1 Jam)
        $rekapTrend = cache()->remember('trend_mingguan_' . $cacheSuffix, 3600, function () use ($dates, $kelasId) {
            return $this->absensiModel->getTrendKehadiran($dates[0], $dates[6], $kelasId);
        });

        foreach ($rekapTrend as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if (in_array($row['status'], ['Hadir', 'Dispensasi'])) {
                    $grafikHadir[$idx] += (int) $row['total'];
                }
                if ($row['status'] === 'Terlambat') $grafikTerlambat[$idx] = (int) $row['total'];
                if ($row['status'] === 'Alpa')      $grafikAlpa[$idx]      = (int) $row['total'];
            }
        }

        $data = [
            'title'              => 'Dashboard Analytics',
            'is_wali_kelas'      => $isWaliKelas,
            'total_siswa'        => $totalSiswa,
            'hadir_hari_ini'     => $hadirHariIni,
            'alpa_hari_ini'      => $stats['alpa'],
            'fraud_hari_ini'     => $stats['fraud'],
            'persen_hadir'       => $persenHadir,
            'chart_distribution' => json_encode($distribusi),
            'top_classes'        => $topClasses,
            'chart_labels'       => json_encode($grafikLabels),
            'chart_hadir'        => json_encode($grafikHadir),
            'chart_terlambat'    => json_encode($grafikTerlambat),
            'chart_alpa'         => json_encode($grafikAlpa),
            'list_manipulasi'    => $manipulasi,
        ];

        return view('web/dashboard', $data);
    }
}
