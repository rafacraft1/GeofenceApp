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
        // ✅ OPTIMASI: Gunakan Cache (1 Jam) untuk menghindari join dan load tabel users terus menerus
        $listGuru = cache()->remember('list_dropdown_guru', 3600, function () {
            $roleGuru = $this->roleModel->where('nama_role', 'Guru')->first();
            if ($roleGuru) {
                return $this->userModel
                    ->where('role_id', $roleGuru['id_role'])
                    ->orderBy('nama_lengkap', 'ASC')
                    ->findAll();
            }
            return [];
        });

        // PERBAIKAN: Join ke tabel users untuk mengambil nama wali kelas
        $kelas = $this->kelasModel
            ->select('kelas.*, users.nama_lengkap as nama_wali, COUNT(siswa.id_siswa) as jumlah_siswa')
            ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
            ->join('siswa', 'siswa.kelas_id = kelas.id_kelas', 'left')
            ->groupBy('kelas.id_kelas')
            ->orderBy('kelas.nama_kelas', 'ASC')
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
        $idKelas     = $this->request->getPost('id_kelas');
        $namaKelas   = trim((string) $this->request->getPost('nama_kelas'));
        // PERBAIKAN: Mengambil ID, bukan Nama
        $waliKelasId = $this->request->getPost('wali_kelas_id');

        $aturanValidasi = [
            'nama_kelas' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Nama kelas wajib diisi.');
        }

        // Tangani null jika dikosongkan ("-- Tanpa Wali Kelas --")
        $waliKelasId = empty($waliKelasId) ? null : (int) $waliKelasId;

        if (!empty($idKelas)) {
            $cekDuplikat = $this->kelasModel
                ->where('nama_kelas', $namaKelas)
                ->where('id_kelas !=', $idKelas)
                ->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal update: Nama kelas "' . $namaKelas . '" sudah digunakan.');
            }

            // PERBAIKAN: Field yang disimpan adalah wali_kelas_id
            $this->kelasModel->update($idKelas, [
                'nama_kelas'    => $namaKelas,
                'wali_kelas_id' => $waliKelasId
            ]);

            $pesan = "Data kelas $namaKelas berhasil diperbarui.";
        } else {
            $cekDuplikat = $this->kelasModel->where('nama_kelas', $namaKelas)->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal: Kelas "' . $namaKelas . '" sudah terdaftar.');
            }

            // PERBAIKAN: Field yang disimpan adalah wali_kelas_id
            $this->kelasModel->insert([
                'nama_kelas'    => $namaKelas,
                'wali_kelas_id' => $waliKelasId
            ]);

            $pesan = "Kelas baru $namaKelas berhasil ditambahkan.";
        }

        return redirect()->to('/admin/kelas')->with('success', $pesan);
    }

    public function delete(string $id)
    {
        $this->kelasModel->db->transStart();

        $this->siswaModel->where('kelas_id', $id)->delete();
        $this->kelasModel->delete($id);

        $this->kelasModel->db->transComplete();

        if ($this->kelasModel->db->transStatus() === false) {
            return redirect()->to('/admin/kelas')->with('error', 'Gagal menghapus kelas.');
        }

        return redirect()->to('/admin/kelas')->with('success', 'Kelas beserta seluruh siswa di dalamnya berhasil dihapus.');
    }
}
