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

    /**
     * @return mixed
     */
    public function index()
    {
        $sekarang    = Time::now('Asia/Jakarta');
        $hariIni     = $sekarang->toDateString();

        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = $isWaliKelas ? session()->get('kelas_id') : null;

        $cacheSuffix = $kelasId ?? 'all';

        if ($isWaliKelas) {
            $this->siswaModel->where('kelas_id', $kelasId);
        }
        $totalSiswa = $this->siswaModel->countAllResults();

        $stats      = $this->absensiModel->getDashboardStats($hariIni, $kelasId);
        $distribusi = $this->absensiModel->getDashboardDistribution($hariIni, $kelasId);
        $manipulasi = $this->absensiModel->getFraudList($hariIni, $kelasId);

        $absensiLokal = $this->absensiModel;

        $topClasses = cache()->remember('leaderboard_' . $cacheSuffix, 600, function () use ($absensiLokal, $hariIni, $kelasId) {
            return $absensiLokal->getLeaderboardKelas($hariIni, $kelasId);
        });

        $hadirHariIni = $stats['hadir'];
        $persenHadir  = ($totalSiswa > 0) ? round(($hadirHariIni / $totalSiswa) * 100) : 0;

        // Inisialisasi Array untuk 6 Status
        $grafikLabels     = [];
        $grafikHadir      = array_fill(0, 30, 0);
        $grafikTerlambat  = array_fill(0, 30, 0);
        $grafikAlpa       = array_fill(0, 30, 0);
        $grafikIzin       = array_fill(0, 30, 0);
        $grafikSakit      = array_fill(0, 30, 0);
        $grafikDispensasi = array_fill(0, 30, 0);
        $dates            = [];

        // Generate rentang tanggal 30 hari ke belakang
        for ($i = 29; $i >= 0; $i--) {
            $tgl = Time::parse($hariIni, 'Asia/Jakarta')->subDays($i)->toDateString();
            $dates[] = $tgl;
            $grafikLabels[] = date('d M', strtotime($tgl));
        }

        $rekapTrend = cache()->remember('trend_bulanan_' . $cacheSuffix, 3600, function () use ($absensiLokal, $dates, $kelasId) {
            return $absensiLokal->getTrendKehadiran($dates[0], $dates[29], $kelasId);
        });

        // Pemetaan data tren berdasarkan index tanggal (Menggunakan += secara konsisten)
        foreach ($rekapTrend as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if ($row['status'] === 'Hadir') {
                    $grafikHadir[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Terlambat') {
                    $grafikTerlambat[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Alpa') {
                    $grafikAlpa[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Izin') {
                    $grafikIzin[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Sakit') {
                    $grafikSakit[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Dispensasi') {
                    $grafikDispensasi[$idx] += (int) $row['total'];
                }
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
            'chart_izin'         => json_encode($grafikIzin),
            'chart_sakit'        => json_encode($grafikSakit),
            'chart_dispensasi'   => json_encode($grafikDispensasi),
            'list_manipulasi'    => $manipulasi,
        ];

        return view('web/dashboard', $data);
    }
}
