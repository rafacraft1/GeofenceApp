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
        // FITUR 8: Perluas scope guru — semua user kecuali Superadmin (role 1)
        $listGuru = $this->userModel
            ->select('id_user, nama_lengkap')
            ->where('role_id !=', 1)
            ->orderBy('nama_lengkap', 'ASC')
            ->findAll();

        // FITUR 6: Buat peta wali_kelas_id → nama_kelas untuk badge "Sudah Wali"
        $waliRows = $this->db->table('kelas')
            ->select('wali_kelas_id, nama_kelas')
            ->where('wali_kelas_id IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        $waliMap = []; // [user_id (int) => nama_kelas]
        foreach ($waliRows as $w) {
            $waliMap[(int) $w['wali_kelas_id']] = $w['nama_kelas'];
        }

        // Hanya zona non-default (untuk PKL/Kunjungan massal)
        $listZona = $this->zonaModel
            ->where('is_default', 0)
            ->orderBy('nama_zona', 'ASC')
            ->findAll();

        $search  = (string) $this->request->getGet('search');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $daftarKelas = $this->kelasModel->getPaginatedKelas($search, $perPage);

        // FITUR 1: Summary stats — satu query agregat
        $statRow = $this->db->query("
            SELECT
                COUNT(k.id_kelas) as total_kelas,
                SUM(k.wali_kelas_id IS NOT NULL) as ada_wali,
                SUM(k.zona_id IS NOT NULL) as kelas_pkl,
                COALESCE((SELECT COUNT(*) FROM siswa), 0) as total_siswa
            FROM kelas k
        ")->getRowArray();

        $summary = [
            'total_kelas' => (int) ($statRow['total_kelas'] ?? 0),
            'ada_wali'    => (int) ($statRow['ada_wali'] ?? 0),
            'kelas_pkl'   => (int) ($statRow['kelas_pkl'] ?? 0),
            'total_siswa' => (int) ($statRow['total_siswa'] ?? 0),
        ];

        $data = [
            'title'        => 'Manajemen Kelas & Zona PKL',
            'daftar_kelas' => $daftarKelas,
            'list_guru'    => $listGuru,
            'list_zona'    => $listZona,
            'wali_map'     => $waliMap,
            'summary'      => $summary,
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

        // Bersihkan cache siswa karena perubahan zona kelas berdampak pada geofence
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
