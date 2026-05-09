<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\KelasModel;

class Kelas extends BaseController
{
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->kelasModel = new KelasModel();
    }

    public function index()
    {
        // 1. Cari ID Role untuk 'Guru'
        $roleGuru = $this->db->table('roles')->where('nama_role', 'Guru')->get()->getRowArray();

        $listGuru = [];
        if ($roleGuru) {
            $listGuru = $this->db->table('users')
                ->where('role_id', $roleGuru['id_role'])
                ->orderBy('nama_lengkap', 'ASC')
                ->get()->getResultArray();
        }

        // 2. Ambil data kelas beserta hitung jumlah siswanya (Optimasi LEFT JOIN)
        $kelas = $this->db->table('kelas')
            ->select('kelas.*, COUNT(siswa.id_siswa) as jumlah_siswa')
            ->join('siswa', 'siswa.kelas_id = kelas.id_kelas', 'left')
            ->groupBy('kelas.id_kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title'    => 'Manajemen Kelas',
            'kelas'    => $kelas,
            'listGuru' => $listGuru
        ];

        return view('web/kelas', $data);
    }

    public function store()
    {
        $idKelas   = $this->request->getPost('id_kelas');
        $namaKelas = strtoupper(trim((string) $this->request->getPost('nama_kelas')));
        $waliKelas = (string) $this->request->getPost('wali_kelas');

        if (empty($namaKelas)) {
            return redirect()->back()->with('error', 'Nama kelas wajib diisi!');
        }

        if (!empty($idKelas)) {
            $cekDuplikat = $this->kelasModel->where('nama_kelas', $namaKelas)
                ->where('id_kelas !=', $idKelas)
                ->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal update: Nama kelas "' . $namaKelas . '" sudah digunakan.');
            }

            $this->kelasModel->update($idKelas, [
                'nama_kelas' => $namaKelas,
                'wali_kelas' => $waliKelas,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $pesan = "Data kelas $namaKelas berhasil diperbarui.";
        } else {
            $cekDuplikat = $this->kelasModel->where('nama_kelas', $namaKelas)->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal: Kelas "' . $namaKelas . '" sudah terdaftar.');
            }

            $this->kelasModel->save([
                'nama_kelas' => $namaKelas,
                'wali_kelas' => $waliKelas,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $pesan = "Kelas baru $namaKelas berhasil ditambahkan.";
        }

        return redirect()->to('/admin/kelas')->with('success', $pesan);
    }

    public function delete(string $id)
    {
        $this->db->transStart();
        $this->db->table('siswa')->where('kelas_id', $id)->delete();
        $this->kelasModel->delete($id);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->to('/admin/kelas')->with('error', 'Gagal menghapus kelas.');
        }

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas dan seluruh siswa di dalamnya berhasil dihapus.');
    }
}
