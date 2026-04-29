<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;

class Dashboard extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $hari_ini = Time::now('Asia/Jakarta')->toDateString();

        // 1. Data untuk Kartu Metrik (Statistik Hari Ini)
        $data = [
            'title'          => 'Dashboard',
            'total_siswa'    => $db->table('siswa')->countAllResults(),
            'hadir_hari_ini' => $db->table('absensi')->where('tanggal', $hari_ini)->whereIn('status', ['Hadir', 'Terlambat'])->countAllResults(),
            'alpa_hari_ini'  => $db->table('absensi')->where('tanggal', $hari_ini)->where('status', 'Alpa')->countAllResults(),
            'fraud_hari_ini' => $db->table('absensi')->where('tanggal', $hari_ini)->groupStart()->where('status', 'Manipulasi')->orWhere('is_fake_gps', 1)->groupEnd()->countAllResults(),
        ];

        // 2. Data Deteksi Manipulasi Hari Ini (Real-time DB)
        $data['list_manipulasi'] = $db->table('absensi')
            ->select('absensi.waktu_masuk, absensi.status, absensi.is_fake_gps, siswa.nama_lengkap, siswa.kelas, siswa.nis, siswa.foto')
            ->join('siswa', 'siswa.id = absensi.siswa_id')
            ->where('absensi.tanggal', $hari_ini)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->orderBy('absensi.waktu_masuk', 'DESC')
            ->get()
            ->getResult();

        // 3. Data untuk Grafik (Tren 7 Hari Terakhir)
        $grafik_labels = [];
        $grafik_hadir = [];
        $grafik_terlambat = [];
        $grafik_alpa = [];

        // Looping mundur dari 6 hari yang lalu sampai hari ini (0)
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Time::now('Asia/Jakarta')->subDays($i)->toDateString();

            // Format label jadi "Tanggal Bulan" (contoh: 24 Apr)
            $grafik_labels[] = date('d M', strtotime($tanggal));

            // Hitung total tiap status pada tanggal tersebut
            $grafik_hadir[]     = $db->table('absensi')->where('tanggal', $tanggal)->where('status', 'Hadir')->countAllResults();
            $grafik_terlambat[] = $db->table('absensi')->where('tanggal', $tanggal)->where('status', 'Terlambat')->countAllResults();
            $grafik_alpa[]      = $db->table('absensi')->where('tanggal', $tanggal)->where('status', 'Alpa')->countAllResults();
        }

        // Lempar data ke view dalam format JSON agar bisa dibaca Javascript (Chart.js)
        $data['chart_labels']    = json_encode($grafik_labels);
        $data['chart_hadir']     = json_encode($grafik_hadir);
        $data['chart_terlambat'] = json_encode($grafik_terlambat);
        $data['chart_alpa']      = json_encode($grafik_alpa);

        return view('web/dashboard', $data);
    }
}
