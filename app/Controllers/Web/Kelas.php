<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\SiswaModel;

class Kelas extends BaseController
{
    protected KelasModel $kelasModel;
    protected RoleModel $roleModel;
    protected UserModel $userModel;
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->kelasModel = new KelasModel();
        $this->roleModel  = new RoleModel();
        $this->userModel  = new UserModel();
        $this->siswaModel = new SiswaModel();
    }

    public function index()
    {
        // Menggunakan Model untuk mengambil Role
        $roleGuru = $this->roleModel->where('nama_role', 'Guru')->first();

        $listGuru = [];
        if ($roleGuru) {
            $listGuru = $this->userModel
                ->where('role_id', $roleGuru['id_role'])
                ->orderBy('nama_lengkap', 'ASC')
                ->findAll();
        }

        // Menggunakan KelasModel untuk optimasi LEFT JOIN
        $kelas = $this->kelasModel
            ->select('kelas.*, COUNT(siswa.id_siswa) as jumlah_siswa')
            ->join('siswa', 'siswa.kelas_id = kelas.id_kelas', 'left')
            ->groupBy('kelas.id_kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->findAll();

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
        $namaKelas = trim((string) $this->request->getPost('nama_kelas'));
        $waliKelas = trim((string) $this->request->getPost('wali_kelas'));

        $aturanValidasi = [
            'nama_kelas' => 'required',
            'wali_kelas' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Nama kelas dan Wali kelas wajib diisi.');
        }

        if (!empty($idKelas)) {
            $cekDuplikat = $this->kelasModel
                ->where('nama_kelas', $namaKelas)
                ->where('id_kelas !=', $idKelas)
                ->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal update: Nama kelas "' . $namaKelas . '" sudah digunakan.');
            }

            // updated_at otomatis dihandle oleh CI4 Model
            $this->kelasModel->update($idKelas, [
                'nama_kelas' => $namaKelas,
                'wali_kelas' => $waliKelas
            ]);

            $pesan = "Data kelas $namaKelas berhasil diperbarui.";
        } else {
            $cekDuplikat = $this->kelasModel->where('nama_kelas', $namaKelas)->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal: Kelas "' . $namaKelas . '" sudah terdaftar.');
            }

            // created_at otomatis dihandle oleh CI4 Model
            $this->kelasModel->insert([
                'nama_kelas' => $namaKelas,
                'wali_kelas' => $waliKelas
            ]);

            $pesan = "Kelas baru $namaKelas berhasil ditambahkan.";
        }

        return redirect()->to('/admin/kelas')->with('success', $pesan);
    }

    public function delete(string $id)
    {
        $this->kelasModel->db->transStart();

        // Hapus siswa terkait menggunakan Model
        $this->siswaModel->where('kelas_id', $id)->delete();
        $this->kelasModel->delete($id);

        $this->kelasModel->db->transComplete();

        if ($this->kelasModel->db->transStatus() === false) {
            return redirect()->to('/admin/kelas')->with('error', 'Gagal menghapus kelas.');
        }

        return redirect()->to('/admin/kelas')->with('success', 'Kelas beserta seluruh siswa di dalamnya berhasil dihapus.');
    }
}
