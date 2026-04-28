<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Tracking extends BaseController
{
    public function index($target_id = null)
    {
        $db = \Config\Database::connect();

        // Ambil semua siswa untuk ditampilkan di Sidebar Checklist
        $siswa = $db->table('siswa')
            ->select('id, nis, nama_lengkap, kelas')
            ->orderBy('kelas', 'ASC')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title'      => 'Radar Live Tracking',
            'list_siswa' => $siswa,
            'target_id'  => $target_id, // ID siswa jika di-klik dari tabel daftar siswa
            'config'     => $db->table('pengaturan')->where('id', 1)->get()->getRow()
        ];

        return view('web/tracking', $data);
    }
}
