<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use App\Models\PengaturanModel;
use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    protected SiswaModel $siswaModel;
    protected AbsensiModel $absensiModel;
    protected PengaturanModel $pengaturanModel;

    public function __construct()
    {
        $this->siswaModel      = new SiswaModel();
        $this->absensiModel    = new AbsensiModel();
        $this->pengaturanModel = new PengaturanModel();
    }

    public function index()
    {
        $hariIni      = Time::now('Asia/Jakarta')->toDateString();
        $isWaliKelas  = session()->get('is_wali_kelas');
        $kelasId      = session()->get('kelas_id');

        // 1. AMBIL KOORDINAT SEKOLAH UNTUK CENTER MAP
        $pengaturan  = $this->pengaturanModel->first();
        $latSekolah  = $pengaturan['latitude_sekolah'] ?? '-6.200000';
        $longSekolah = $pengaturan['longitude_sekolah'] ?? '106.816666';

        // 2. TOTAL SISWA
        if ($isWaliKelas) {
            $this->siswaModel->where('kelas_id', $kelasId);
        }
        $totalSiswa = $this->siswaModel->countAllResults();

        // ========================================================================
        // 3. OPTIMASI SUPER: 1 QUERY UNTUK 4 STATISTIK (Hadir, Alpa, Fraud, Distribusi)
        // Mencegah N+1 query issue, sangat menghemat resource server.
        // ========================================================================
        $builderStats = $this->absensiModel->select('absensi.status, absensi.is_fake_gps, COUNT(absensi.id_absensi) as total')
            ->where('absensi.tanggal', $hariIni)
            ->groupBy('absensi.status, absensi.is_fake_gps');

        if ($isWaliKelas) {
            $builderStats->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
                ->where('siswa.kelas_id', $kelasId);
        }
        $rawStats = $builderStats->findAll();

        $hadirHariIni = 0;
        $alpaHariIni  = 0;
        $fraudHariIni = 0;
        $statusMap    = ['Hadir' => 0, 'Dispensasi' => 0, 'Terlambat' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];

        foreach ($rawStats as $row) {
            $total  = (int) $row['total'];
            $status = $row['status'];
            $isFake = $row['is_fake_gps'];

            // A. Populate Data Distribusi (Doughnut Chart)
            if (isset($statusMap[$status])) {
                $statusMap[$status] += $total;
            }

            // B. Populate Summary Cards
            if (in_array($status, ['Hadir', 'Terlambat', 'Dispensasi'])) {
                $hadirHariIni += $total;
            }
            if ($status === 'Alpa') {
                $alpaHariIni += $total;
            }
            if ($status === 'Manipulasi' || $isFake == 1) {
                $fraudHariIni += $total;
            }
        }

        // ========================================================================
        // 4. LEADERBOARD KELAS
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
        $topClasses = $topQuery->findAll();

        // ========================================================================
        // 5. DATA TREN KEHADIRAN 7 HARI TERAKHIR
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

        if ($isWaliKelas) {
            $trendQuery->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
                ->where('siswa.kelas_id', $kelasId);
        }
        $rekapTrend = $trendQuery->findAll();

        foreach ($rekapTrend as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if (in_array($row['status'], ['Hadir', 'Dispensasi'])) {
                    $grafikHadir[$idx] += (int) $row['total'];
                }
                if ($row['status'] == 'Terlambat') $grafikTerlambat[$idx] += (int) $row['total'];
                if ($row['status'] == 'Alpa')      $grafikAlpa[$idx]      += (int) $row['total'];
            }
        }

        // ========================================================================
        // 6. DATA ANOMALI / FRAUD HARI INI
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

        $listManipulasi = $listManipulasiQuery->findAll();

        $data = [
            'title'              => 'Dashboard Analytics',
            'lat_sekolah'        => $latSekolah,
            'long_sekolah'       => $longSekolah,
            'total_siswa'        => $totalSiswa,
            'hadir_hari_ini'     => $hadirHariIni,
            'alpa_hari_ini'      => $alpaHariIni,
            'fraud_hari_ini'     => $fraudHariIni,
            'chart_distribution' => json_encode(array_values($statusMap)),
            'top_classes'        => $topClasses,
            'chart_labels'       => json_encode($grafikLabels),
            'chart_hadir'        => json_encode($grafikHadir),
            'chart_terlambat'    => json_encode($grafikTerlambat),
            'chart_alpa'         => json_encode($grafikAlpa),
            'list_manipulasi'    => $listManipulasi,
        ];

        return view('web/dashboard', $data);
    }
}
