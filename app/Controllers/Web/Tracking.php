<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;

class Tracking extends Controller
{
    public function index(string $target_id = null)
    {
        $db = \Config\Database::connect();

        $kelas_filter = $this->request->getGet('kelas_id');

        // Ambil konfigurasi (Koordinat Sekolah & URL Firebase)
        // Disini ID-nya menggunakan primary key id_pengaturan sesuai migrasi terbaru
        $config = $db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        // Ambil Daftar Kelas (Untuk Dropdown Filter)
        $list_kelas = $db->table('kelas')->orderBy('nama_kelas', 'ASC')->get()->getResultArray();

        // Ambil seluruh data siswa untuk Sidebar (Join dengan kelas)
        $builder = $db->table('siswa')
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        // Jika admin memfilter kelas di halaman tracking
        if (!empty($kelas_filter)) {
            $builder->where('siswa.kelas_id', $kelas_filter);
        }

        $list_siswa = $builder->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Radar Live Tracking',
            'config'      => $config,
            'list_siswa'  => $list_siswa,
            'list_kelas'  => $list_kelas,
            'kelas_aktif' => $kelas_filter,
            'target_id'   => $target_id
        ];

        return view('web/tracking', $data);
    }
}
