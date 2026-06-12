<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;

class Mutasi extends BaseController
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->kelasModel = new KelasModel();
    }

    public function index()
    {
        if (session()->get('role_id') != 1) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses Ditolak: Fitur Mutasi hanya diperuntukkan bagi Administrator.');
        }

        $kelasAsalId = $this->request->getGet('asal');

        $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        $siswaAsal = [];
        $kelasAsalData = null;

        if (!empty($kelasAsalId)) {
            $siswaAsal = $this->siswaModel->where('kelas_id', $kelasAsalId)->orderBy('nama_siswa', 'ASC')->findAll();

            $kelasAsalData = $this->kelasModel->select('kelas.*, users.nama_lengkap as nama_wali')
                ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
                ->where('id_kelas', $kelasAsalId)
                ->first();
        }

        $data = [
            'title'         => 'Mutasi Siswa & Wali Kelas (SCD)',
            'listKelas'     => $listKelas,
            'kelasAsalId'   => $kelasAsalId,
            'siswaAsal'     => $siswaAsal,
            'kelasAsalData' => $kelasAsalData
        ];

        return view('web/mutasi', $data);
    }

    public function proses()
    {
        if (session()->get('role_id') != 1) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses Ditolak.');
        }

        $kelasAsalId   = (int) $this->request->getPost('kelas_asal_id');
        $kelasTujuanId = (int) $this->request->getPost('kelas_tujuan_id');
        $siswaIds      = $this->request->getPost('siswa_id');
        $pindahWali    = $this->request->getPost('pindah_wali') ? true : false;

        if ($kelasAsalId === $kelasTujuanId) {
            return redirect()->back()->with('error', 'Validasi gagal: Kelas asal dan tujuan tidak boleh sama.');
        }

        if (empty($siswaIds) || !is_array($siswaIds)) {
            return redirect()->back()->with('error', 'Validasi gagal: Pilih minimal satu siswa untuk dimutasi.');
        }

        $this->kelasModel->db->transStart();

        $this->siswaModel->whereIn('id_siswa', $siswaIds)->set(['kelas_id' => $kelasTujuanId])->update();

        if ($pindahWali) {
            $kelasAsal = $this->kelasModel->find($kelasAsalId);
            if (!empty($kelasAsal['wali_kelas_id'])) {
                $this->kelasModel->update($kelasTujuanId, ['wali_kelas_id' => $kelasAsal['wali_kelas_id']]);
                $this->kelasModel->update($kelasAsalId, ['wali_kelas_id' => null]);
            }
        }

        $this->kelasModel->db->transComplete();

        if ($this->kelasModel->db->transStatus() === false) {
            return redirect()->to('/admin/mutasi')->with('error', 'Terjadi fatal error pada database saat memproses mutasi.');
        }

        return redirect()->to('/admin/mutasi')->with('success', count($siswaIds) . ' Siswa berhasil dimutasi ke kelas baru.');
    }
}
