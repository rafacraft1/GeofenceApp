<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Tracking extends BaseController
{
    // Menggunakan strict type-hinting dengan union type string|null untuk parameter
    public function index(string|null $targetId = null)
    {
        // $this->db sudah diinisialisasi di BaseController, tidak perlu \Config\Database::connect()

        $kelasFilter = $this->request->getGet('kelas_id');

        // Ambil konfigurasi (Koordinat Sekolah & URL Firebase)
        $config = $this->db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        // Ambil Daftar Kelas (Untuk Dropdown Filter)
        $listKelas = $this->db->table('kelas')->orderBy('nama_kelas', 'ASC')->get()->getResultArray();

        // Ambil seluruh data siswa untuk Sidebar (Join dengan kelas)
        $builder = $this->db->table('siswa')
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $builder->where('siswa.kelas_id', $kelasFilter);
        }

        $listSiswa = $builder->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Radar Live Tracking',
            'config'      => $config,
            'list_siswa'  => $listSiswa,
            'list_kelas'  => $listKelas,
            'kelas_aktif' => $kelasFilter,
            'target_id'   => $targetId
        ];

        return view('web/tracking', $data);
    }
}
