<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    public function index()
    {
        $hariIni = Time::now('Asia/Jakarta')->toDateString();

        $data = [
            'title'          => 'Dashboard',
            'total_siswa'    => $this->db->table('siswa')->countAllResults(),
            'hadir_hari_ini' => $this->db->table('absensi')->where('tanggal', $hariIni)->whereIn('status', ['Hadir', 'Terlambat'])->countAllResults(),
            'alpa_hari_ini'  => $this->db->table('absensi')->where('tanggal', $hariIni)->where('status', 'Alpa')->countAllResults(),
            'fraud_hari_ini' => $this->db->table('absensi')->where('tanggal', $hariIni)->groupStart()->where('status', 'Manipulasi')->orWhere('is_fake_gps', 1)->groupEnd()->countAllResults(),
        ];

        // Key array view tetap dipertahankan snake_case agar tidak merusak View dashboard.php
        $data['list_manipulasi'] = $this->db->table('absensi')
            ->select('absensi.jam_masuk, absensi.status, absensi.is_fake_gps, siswa.nama_siswa, kelas.nama_kelas as kelas, siswa.nis, siswa.foto_profil')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $hariIni)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->orderBy('absensi.jam_masuk', 'DESC')
            ->get()
            ->getResultArray();

        // PERBAIKAN: Optimasi Query Grafik & Variabel camelCase
        $grafikLabels = [];
        $grafikHadir = array_fill(0, 7, 0);
        $grafikTerlambat = array_fill(0, 7, 0);
        $grafikAlpa = array_fill(0, 7, 0);
        $dates = [];

        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Time::now('Asia/Jakarta')->subDays($i)->toDateString();
            $dates[] = $tanggal;
            $grafikLabels[] = date('d M', strtotime($tanggal));
        }

        $startDate = $dates[0];
        $endDate   = $dates[6];

        $rekapGrafik = $this->db->table('absensi')
            ->select('tanggal, status, COUNT(*) as total')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->whereIn('status', ['Hadir', 'Terlambat', 'Alpa'])
            ->groupBy('tanggal, status')
            ->get()
            ->getResultArray();

        foreach ($rekapGrafik as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if ($row['status'] == 'Hadir') $grafikHadir[$idx] = (int) $row['total'];
                if ($row['status'] == 'Terlambat') $grafikTerlambat[$idx] = (int) $row['total'];
                if ($row['status'] == 'Alpa') $grafikAlpa[$idx] = (int) $row['total'];
            }
        }

        $data['chart_labels']    = json_encode($grafikLabels);
        $data['chart_hadir']     = json_encode($grafikHadir);
        $data['chart_terlambat'] = json_encode($grafikTerlambat);
        $data['chart_alpa']      = json_encode($grafikAlpa);

        return view('web/dashboard', $data);
    }
}
