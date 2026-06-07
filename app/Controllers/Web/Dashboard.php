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
        $hariIni     = Time::now('Asia/Jakarta')->toDateString();
        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = $isWaliKelas ? session()->get('kelas_id') : null;

        // 1. Total Siswa (Dihitung di model)
        if ($isWaliKelas) {
            $this->siswaModel->where('kelas_id', $kelasId);
        }
        $totalSiswa = $this->siswaModel->countAllResults();

        // 2. Mengambil Seluruh Metrik dari Model Terpusat (Skinny Controller)
        $stats      = $this->absensiModel->getDashboardStats($hariIni, $kelasId);
        $distribusi = $this->absensiModel->getDashboardDistribution($hariIni, $kelasId);
        $topClasses = $this->absensiModel->getLeaderboardKelas($hariIni, $kelasId);
        $manipulasi = $this->absensiModel->getFraudList($hariIni, $kelasId);

        // Menghitung Persentase murni di Controller (Pindah dari View)
        $hadirHariIni = $stats['hadir'];
        $persenHadir  = ($totalSiswa > 0) ? round(($hadirHariIni / $totalSiswa) * 100) : 0;

        // 3. Merakit Data Tren 7 Hari Terakhir
        $grafikLabels    = [];
        $grafikHadir     = array_fill(0, 7, 0);
        $grafikTerlambat = array_fill(0, 7, 0);
        $grafikAlpa      = array_fill(0, 7, 0);
        $dates           = [];

        for ($i = 6; $i >= 0; $i--) {
            $tgl = Time::now('Asia/Jakarta')->subDays($i)->toDateString();
            $dates[] = $tgl;
            $grafikLabels[] = date('d M', strtotime($tgl));
        }

        $rekapTrend = $this->absensiModel->getTrendKehadiran($dates[0], $dates[6], $kelasId);

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
