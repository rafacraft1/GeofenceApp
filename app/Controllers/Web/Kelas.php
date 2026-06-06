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
        $search = trim((string) $this->request->getGet('search'));

        // Mengambil daftar Guru untuk form dropdown
        $roleGuru = $this->roleModel->where('nama_role', 'Guru')->first();
        $listGuru = [];
        if ($roleGuru) {
            $listGuru = $this->userModel
                ->where('role_id', $roleGuru['id_role'])
                ->orderBy('nama_lengkap', 'ASC')
                ->findAll();
        }

        // Setup Pagination
        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page_kelas') ?? 1);
        $perPage = 10;

        // Query Aggregation & Search
        $builder = $this->kelasModel
            ->select('kelas.*, users.nama_lengkap as nama_wali, COUNT(siswa.id_siswa) as jumlah_siswa')
            ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
            ->join('siswa', 'siswa.kelas_id = kelas.id_kelas', 'left')
            ->groupBy('kelas.id_kelas');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kelas.nama_kelas', $search)
                ->orLike('users.nama_lengkap', $search)
                ->groupEnd();
        }

        $totalData = $builder->countAllResults(false);
        $kelas = $builder->orderBy('kelas.nama_kelas', 'ASC')->paginate($perPage, 'kelas');
        $pagerLinks = $this->kelasModel->pager->makeLinks($page, $perPage, $totalData, 'default_full', 0, 'kelas');

        $data = [
            'title'       => 'Manajemen Kelas',
            'kelas'       => $kelas,
            'listGuru'    => $listGuru,
            'search'      => $search,
            'pager_links' => $pagerLinks,
            'page'        => $page,
            'perPage'     => $perPage,
            'total_data'  => $totalData
        ];

        return view('web/kelas', $data);
    }

    public function store()
    {
        $idKelas     = $this->request->getPost('id_kelas');
        $namaKelas   = trim((string) $this->request->getPost('nama_kelas'));
        $waliKelasId = $this->request->getPost('wali_kelas_id');

        $aturanValidasi = [
            'nama_kelas' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Nama kelas wajib diisi.');
        }

        $waliKelasId = empty($waliKelasId) ? null : (int) $waliKelasId;

        if (!empty($idKelas)) {
            $cekDuplikat = $this->kelasModel
                ->where('nama_kelas', $namaKelas)
                ->where('id_kelas !=', $idKelas)
                ->first();

            if ($cekDuplikat) {
                return redirect()->back()->with('error', 'Gagal update: Nama kelas "' . $namaKelas . '" sudah digunakan.');
            }

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

            $this->kelasModel->insert([
                'nama_kelas'    => $namaKelas,
                'wali_kelas_id' => $waliKelasId
            ]);

            $pesan = "Kelas baru $namaKelas berhasil ditambahkan.";
        }

        // Menggunakan back() agar jika admin berada di page_kelas=2, halamannya tidak ter-reset
        return redirect()->back()->with('success', $pesan);
    }

    public function delete(string $id)
    {
        $this->kelasModel->db->transStart();

        $this->siswaModel->where('kelas_id', $id)->delete();
        $this->kelasModel->delete($id);

        $this->kelasModel->db->transComplete();

        if ($this->kelasModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menghapus kelas.');
        }

        return redirect()->back()->with('success', 'Kelas beserta seluruh siswa di dalamnya berhasil dihapus.');
    }
}
