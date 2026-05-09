<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\BaseConnection;

class Dashboard extends Controller
{
    public function index()
    {
        /** @var BaseConnection $db */
        $db = \Config\Database::connect();
        $hari_ini = Time::now('Asia/Jakarta')->toDateString();

        $data = [
            'title'          => 'Dashboard',
            'total_siswa'    => $db->table('siswa')->countAllResults(),
            'hadir_hari_ini' => $db->table('absensi')->where('tanggal', $hari_ini)->whereIn('status', ['Hadir', 'Terlambat'])->countAllResults(),
            'alpa_hari_ini'  => $db->table('absensi')->where('tanggal', $hari_ini)->where('status', 'Alpa')->countAllResults(),
            'fraud_hari_ini' => $db->table('absensi')->where('tanggal', $hari_ini)->groupStart()->where('status', 'Manipulasi')->orWhere('is_fake_gps', 1)->groupEnd()->countAllResults(),
        ];

        $data['list_manipulasi'] = $db->table('absensi')
            ->select('absensi.jam_masuk, absensi.status, absensi.is_fake_gps, siswa.nama_siswa, kelas.nama_kelas as kelas, siswa.nis, siswa.foto_profil')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $hari_ini)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->orderBy('absensi.jam_masuk', 'DESC')
            ->get()
            ->getResultArray();

        // PERBAIKAN: Optimasi Query Grafik - 1 kali hit ke database dengan Group By
        $grafik_labels = [];
        $grafik_hadir = array_fill(0, 7, 0);
        $grafik_terlambat = array_fill(0, 7, 0);
        $grafik_alpa = array_fill(0, 7, 0);
        $dates = [];

        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Time::now('Asia/Jakarta')->subDays($i)->toDateString();
            $dates[] = $tanggal;
            $grafik_labels[] = date('d M', strtotime($tanggal));
        }

        $startDate = $dates[0];
        $endDate   = $dates[6];

        $rekap_grafik = $db->table('absensi')
            ->select('tanggal, status, COUNT(*) as total')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->whereIn('status', ['Hadir', 'Terlambat', 'Alpa'])
            ->groupBy('tanggal, status')
            ->get()
            ->getResultArray();

        foreach ($rekap_grafik as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if ($row['status'] == 'Hadir') $grafik_hadir[$idx] = $row['total'];
                if ($row['status'] == 'Terlambat') $grafik_terlambat[$idx] = $row['total'];
                if ($row['status'] == 'Alpa') $grafik_alpa[$idx] = $row['total'];
            }
        }

        $data['chart_labels']    = json_encode($grafik_labels);
        $data['chart_hadir']     = json_encode($grafik_hadir);
        $data['chart_terlambat'] = json_encode($grafik_terlambat);
        $data['chart_alpa']      = json_encode($grafik_alpa);

        return view('web/dashboard', $data);
    }
}
