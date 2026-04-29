<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;

class Tracking extends Controller
{
    public function index(string $target_id = null)
    {
        $db = \Config\Database::connect();

        // Ambil konfigurasi (Koordinat Sekolah & URL Firebase)
        $config = $db->table('pengaturan')->where('id', 1)->get()->getRow();

        // Ambil seluruh data siswa untuk Sidebar
        $list_siswa = $db->table('siswa')
            ->orderBy('kelas', 'ASC')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title'      => 'Radar Live Tracking',
            'config'     => $config,
            'list_siswa' => $list_siswa,
            'target_id'  => $target_id
        ];

        return view('web/tracking', $data);
    }
}
