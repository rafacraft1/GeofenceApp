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
        $hariIni = Time::now('Asia/Jakarta')->toDateString();

        // 1. Mengambil Statistik Utama Hari Ini (Dispensasi dihitung Hadir)
        $data = [
            'title'          => 'Dashboard Analytics',
            'total_siswa'    => $this->siswaModel->countAllResults(),
            'hadir_hari_ini' => $this->absensiModel->where('tanggal', $hariIni)->whereIn('status', ['Hadir', 'Terlambat', 'Dispensasi'])->countAllResults(),
            'alpa_hari_ini'  => $this->absensiModel->where('tanggal', $hariIni)->where('status', 'Alpa')->countAllResults(),
            'fraud_hari_ini' => $this->absensiModel->where('tanggal', $hariIni)
                ->groupStart()
                ->where('status', 'Manipulasi')
                ->orWhere('is_fake_gps', 1)
                ->groupEnd()
                ->countAllResults(),
        ];

        // 2. Data Distribusi Status untuk Doughnut Chart
        $distribusi = $this->absensiModel
            ->select('status, COUNT(*) as total')
            ->where('tanggal', $hariIni)
            ->groupBy('status')
            ->findAll();

        // Tambahkan Dispensasi di Map
        $statusMap = ['Hadir' => 0, 'Dispensasi' => 0, 'Terlambat' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];
        foreach ($distribusi as $row) {
            if (isset($statusMap[$row['status']])) {
                $statusMap[$row['status']] = (int) $row['total'];
            }
        }
        $data['chart_distribution'] = json_encode(array_values($statusMap));

        // 3. Leaderboard: Top 5 Kelas dengan Kehadiran Tertinggi (Dispensasi Dihitung)
        $data['top_classes'] = $this->absensiModel
            ->select('kelas.nama_kelas, COUNT(absensi.id_absensi) as total_hadir')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id')
            ->where('absensi.tanggal', $hariIni)
            ->whereIn('absensi.status', ['Hadir', 'Terlambat', 'Dispensasi'])
            ->groupBy('kelas.id_kelas')
            ->orderBy('total_hadir', 'DESC')
            ->limit(5)
            ->findAll();

        // 4. Data Tren Kehadiran 7 Hari Terakhir (Stacked Bar Chart)
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

        $rekapTrend = $this->absensiModel
            ->select('tanggal, status, COUNT(*) as total')
            ->where('tanggal >=', $dates[0])
            ->where('tanggal <=', $dates[6])
            ->whereIn('status', ['Hadir', 'Dispensasi', 'Terlambat', 'Alpa']) // Tarik Dispensasi juga
            ->groupBy('tanggal, status')
            ->findAll();

        foreach ($rekapTrend as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                // Leburkan Dispensasi ke dalam balok grafik Hadir agar UI tetap bersih
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

        // 5. Data Anomali/Fraud untuk Tabel & Map
        $data['list_manipulasi'] = $this->absensiModel
            ->select('absensi.jam_masuk, absensi.status, absensi.is_fake_gps, absensi.lat_masuk, absensi.long_masuk, siswa.nama_siswa, kelas.nama_kelas as kelas, siswa.nis, siswa.foto_profil')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $hariIni)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->orderBy('absensi.jam_masuk', 'DESC')
            ->findAll();

        return view('web/dashboard', $data);
    }
}
