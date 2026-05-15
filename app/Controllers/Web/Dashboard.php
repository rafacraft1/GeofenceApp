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
        $hariIni      = Time::now('Asia/Jakarta')->toDateString();
        $isWaliKelas  = session()->get('is_wali_kelas');
        $kelasId      = session()->get('kelas_id');

        // ========================================================================
        // 1. MENGAMBIL STATISTIK UTAMA (Disesuaikan dengan Hak Akses)
        // ========================================================================

        // Total Siswa
        if ($isWaliKelas) {
            $this->siswaModel->where('kelas_id', $kelasId);
        }
        $totalSiswa = $this->siswaModel->countAllResults();

        // Helper function (Closure) untuk efisiensi scope query wali kelas pada tabel absensi
        $scopeWaliKelas = function ($builder) use ($isWaliKelas, $kelasId) {
            if ($isWaliKelas) {
                // Pastikan tabel siswa di-join agar bisa memfilter berdasarkan kelas_id
                // Menggunakan identifier yang unik jika join belum dilakukan
                $builder->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
                    ->where('siswa.kelas_id', $kelasId);
            }
            return $builder;
        };

        // Hadir Hari Ini
        $builderHadir = $this->absensiModel->where('absensi.tanggal', $hariIni)
            ->whereIn('absensi.status', ['Hadir', 'Terlambat', 'Dispensasi']);
        $hadirHariIni = $scopeWaliKelas($builderHadir)->countAllResults();

        // Alpa Hari Ini
        $builderAlpa = $this->absensiModel->where('absensi.tanggal', $hariIni)
            ->where('absensi.status', 'Alpa');
        $alpaHariIni = $scopeWaliKelas($builderAlpa)->countAllResults();

        // Fraud Hari Ini
        $builderFraud = $this->absensiModel->where('absensi.tanggal', $hariIni)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd();
        $fraudHariIni = $scopeWaliKelas($builderFraud)->countAllResults();

        $data = [
            'title'          => 'Dashboard Analytics',
            'total_siswa'    => $totalSiswa,
            'hadir_hari_ini' => $hadirHariIni,
            'alpa_hari_ini'  => $alpaHariIni,
            'fraud_hari_ini' => $fraudHariIni,
        ];

        // ========================================================================
        // 2. DATA DISTRIBUSI STATUS (Doughnut Chart)
        // ========================================================================
        $distQuery = $this->absensiModel->select('absensi.status, COUNT(absensi.id_absensi) as total')
            ->where('absensi.tanggal', $hariIni)
            ->groupBy('absensi.status');

        $distribusi = $scopeWaliKelas($distQuery)->findAll();

        $statusMap = ['Hadir' => 0, 'Dispensasi' => 0, 'Terlambat' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];
        foreach ($distribusi as $row) {
            if (isset($statusMap[$row['status']])) {
                $statusMap[$row['status']] = (int) $row['total'];
            }
        }
        $data['chart_distribution'] = json_encode(array_values($statusMap));

        // ========================================================================
        // 3. LEADERBOARD KELAS
        // ========================================================================
        $topQuery = $this->absensiModel
            ->select('kelas.nama_kelas, COUNT(absensi.id_absensi) as total_hadir')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id')
            ->where('absensi.tanggal', $hariIni)
            ->whereIn('absensi.status', ['Hadir', 'Terlambat', 'Dispensasi'])
            ->groupBy('kelas.id_kelas')
            ->orderBy('total_hadir', 'DESC')
            ->limit(5);

        if ($isWaliKelas) {
            $topQuery->where('siswa.kelas_id', $kelasId);
        }
        $data['top_classes'] = $topQuery->findAll();

        // ========================================================================
        // 4. DATA TREN KEHADIRAN 7 HARI TERAKHIR (Stacked Bar Chart)
        // ========================================================================
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

        $trendQuery = $this->absensiModel
            ->select('absensi.tanggal, absensi.status, COUNT(absensi.id_absensi) as total')
            ->where('absensi.tanggal >=', $dates[0])
            ->where('absensi.tanggal <=', $dates[6])
            ->whereIn('absensi.status', ['Hadir', 'Dispensasi', 'Terlambat', 'Alpa'])
            ->groupBy('absensi.tanggal, absensi.status');

        $rekapTrend = $scopeWaliKelas($trendQuery)->findAll();

        foreach ($rekapTrend as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if (in_array($row['status'], ['Hadir', 'Dispensasi'])) {
                    $grafikHadir[$idx] += (int) $row['total'];
                }
                if ($row['status'] == 'Terlambat') $grafikTerlambat[$idx] = (int) $row['total'];
                if ($row['status'] == 'Alpa') $grafikAlpa[$idx] = (int) $row['total'];
            }
        }

        $data['chart_labels']    = json_encode($grafikLabels);
        $data['chart_hadir']     = json_encode($grafikHadir);
        $data['chart_terlambat'] = json_encode($grafikTerlambat);
        $data['chart_alpa']      = json_encode($grafikAlpa);

        // ========================================================================
        // 5. DATA ANOMALI / FRAUD HARI INI
        // ========================================================================
        $listManipulasiQuery = $this->absensiModel
            ->select('absensi.jam_masuk, absensi.status, absensi.is_fake_gps, absensi.lat_masuk, absensi.long_masuk, siswa.nama_siswa, kelas.nama_kelas as kelas, siswa.nis, siswa.foto_profil')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $hariIni)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->orderBy('absensi.jam_masuk', 'DESC');

        if ($isWaliKelas) {
            $listManipulasiQuery->where('siswa.kelas_id', $kelasId);
        }

        $data['list_manipulasi'] = $listManipulasiQuery->findAll();

        return view('web/dashboard', $data);
    }
}
