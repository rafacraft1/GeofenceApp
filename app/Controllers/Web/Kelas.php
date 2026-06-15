<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\UserModel;
use App\Models\SiswaModel;
use App\Models\ZonaModel;

class Kelas extends BaseController
{
    protected KelasModel $kelasModel;
    protected UserModel $userModel;
    protected SiswaModel $siswaModel;
    protected ZonaModel $zonaModel;

    public function __construct()
    {
        $this->kelasModel = new KelasModel();
        $this->userModel  = new UserModel();
        $this->siswaModel = new SiswaModel();
        $this->zonaModel  = new ZonaModel();
    }

    public function index()
    {
        // Ambil data guru untuk wali kelas (Role 2)
        $listGuru = $this->userModel->where('role_id', 2)->orderBy('nama_lengkap', 'ASC')->findAll();

        // MENCEGAH DUPLIKASI ZONA DEFAULT DI DROPDOWN UI
        // Hanya ambil data zona untuk penempatan kelas PKL/Magang massal (is_default = 0)
        $listZona = $this->zonaModel->where('is_default', 0)->orderBy('nama_zona', 'ASC')->findAll();

        $search = (string) $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $daftarKelas = $this->kelasModel->getPaginatedKelas($search, $perPage);

        $data = [
            'title'        => 'Manajemen Kelas & Zona PKL',
            'daftar_kelas' => $daftarKelas,
            'list_guru'    => $listGuru,
            'list_zona'    => $listZona,
            'search_aktif' => $search,
            'pager_links'  => $this->kelasModel->pager->links('default', 'tailwind_pagination'),
            'total_data'   => $this->kelasModel->pager->getTotal('default'),
            'page'         => $page,
            'perPage'      => $perPage
        ];

        return view('web/kelas', $data);
    }

    public function store()
    {
        $aturanValidasi = [
            'nama_kelas'    => 'required|is_unique[kelas.nama_kelas]',
            'wali_kelas_id' => 'permit_empty|numeric',
            'zona_id'       => 'permit_empty|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $waliId = $this->request->getPost('wali_kelas_id');
        $zonaId = $this->request->getPost('zona_id');

        $this->kelasModel->insert([
            'nama_kelas'    => (string) $this->request->getPost('nama_kelas'),
            'wali_kelas_id' => empty($waliId) ? null : (int) $waliId,
            'zona_id'       => empty($zonaId) ? null : (int) $zonaId
        ]);

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function update(string $id)
    {
        $kelasLama = $this->kelasModel->find($id);
        if (!$kelasLama) {
            return redirect()->to('/admin/kelas')->with('error', 'Data kelas tidak ditemukan.');
        }

        $aturanValidasi = [
            'nama_kelas'    => "required|is_unique[kelas.nama_kelas,id_kelas,{$id}]",
            'wali_kelas_id' => 'permit_empty|numeric',
            'zona_id'       => 'permit_empty|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $waliId = $this->request->getPost('wali_kelas_id');
        $zonaId = $this->request->getPost('zona_id');

        $this->kelasModel->update($id, [
            'nama_kelas'    => (string) $this->request->getPost('nama_kelas'),
            'wali_kelas_id' => empty($waliId) ? null : (int) $waliId,
            'zona_id'       => empty($zonaId) ? null : (int) $zonaId
        ]);

        // Bersihkan cache siswa karena perubahan zona kelas berdampak pada aturan geofence siswa di dalamnya
        cache()->deleteMatching('siswa_auth_*');

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $siswaCount = $this->siswaModel->where('kelas_id', $id)->countAllResults();

        if ($siswaCount > 0) {
            return redirect()->to('/admin/kelas')->with('error', "Gagal dihapus! Terdapat {$siswaCount} siswa yang masih terdaftar di kelas ini. Kosongkan atau mutasikan siswa terlebih dahulu.");
        }

        if ($this->kelasModel->delete($id)) {
            return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil dihapus.');
        }

        return redirect()->to('/admin/kelas')->with('error', 'Gagal menghapus data kelas.');
    }
}
