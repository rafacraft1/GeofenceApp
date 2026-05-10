<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;

class Tracking extends BaseController
{
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    public function index(string|null $targetId = null)
    {
        $kelasFilter = $this->request->getGet('kelas_id');

        // Ambil konfigurasi (Koordinat Sekolah & URL Firebase)
        $config = $this->db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        // Ambil Daftar Kelas (Untuk Dropdown Filter)
        $listKelas = $this->db->table('kelas')->orderBy('nama_kelas', 'ASC')->get()->getResultArray();

        // Refactor: Ambil seluruh data siswa untuk Sidebar menggunakan SiswaModel
        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->siswaModel->where('siswa.kelas_id', $kelasFilter);
        }

        $listSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

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
