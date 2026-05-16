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
        // PROTEKSI ABSOLUT: Hanya Admin (Role 1) yang bisa mengakses fitur Mutasi SCD
        if (session()->get('role_id') != 1) {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses Ditolak: Fitur Mutasi hanya diperuntukkan bagi Administrator.');
        }

        $kelasAsalId = $this->request->getGet('asal');

        $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        $siswaAsal = [];
        $kelasAsalData = null;

        if (!empty($kelasAsalId)) {
            $siswaAsal = $this->siswaModel->where('kelas_id', $kelasAsalId)->orderBy('nama_siswa', 'ASC')->findAll();

            // Ambil detail kelas asal beserta nama wali kelasnya
            $kelasAsalData = $this->kelasModel->select('kelas.*, users.nama_lengkap as nama_wali')
                ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
                ->where('id_kelas', $kelasAsalId)
                ->first();
        }

        $data = [
            'title'         => 'Mutasi & Kenaikan Kelas',
            'listKelas'     => $listKelas,
            'siswaAsal'     => $siswaAsal,
            'kelasAsalId'   => $kelasAsalId,
            'kelasAsalData' => $kelasAsalData
        ];

        return view('web/mutasi', $data);
    }

    /**
     * Endpoint AJAX untuk Smart Merge Warning
     */
    public function checkTujuan(string $idKelas)
    {
        if (session()->get('role_id') != 1) return $this->response->setJSON(['status' => 403]);

        $count = $this->siswaModel->where('kelas_id', $idKelas)->countAllResults();
        $kelas = $this->kelasModel->select('kelas.*, users.nama_lengkap as nama_wali')
            ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
            ->where('id_kelas', $idKelas)
            ->first();

        return $this->response->setJSON([
            'status'    => 200,
            'jumlah'    => $count,
            'nama_wali' => $kelas['nama_wali'] ?? 'Tidak ada / Kosong'
        ]);
    }

    public function proses()
    {
        if (session()->get('role_id') != 1) {
            return redirect()->to('/admin/dashboard');
        }

        $kelasAsalId   = $this->request->getPost('kelas_asal');
        $kelasTujuanId = $this->request->getPost('kelas_tujuan');
        $siswaIds      = $this->request->getPost('siswa_ids'); // Array ID Siswa
        $pindahWali    = $this->request->getPost('pindah_wali') ? true : false;

        if (empty($kelasAsalId) || empty($kelasTujuanId)) {
            return redirect()->back()->with('error', 'Validasi gagal: Kelas Asal dan Tujuan harus dipilih.');
        }

        if ($kelasAsalId === $kelasTujuanId) {
            return redirect()->back()->with('error', 'Validasi gagal: Kelas Tujuan tidak boleh sama dengan Kelas Asal.');
        }

        if (empty($siswaIds) || !is_array($siswaIds)) {
            return redirect()->back()->with('error', 'Validasi gagal: Pilih minimal satu siswa untuk dimutasi.');
        }

        // =========================================================================
        // MULAI TRANSAKSI DATABASE (Atomicity dijamin)
        // =========================================================================
        $this->kelasModel->db->transStart();

        // 1. Eksekusi Pindah Massal Siswa yang dicentang
        $this->siswaModel->whereIn('id_siswa', $siswaIds)->set(['kelas_id' => $kelasTujuanId])->update();

        // 2. Eksekusi Pertukaran Jabatan Wali Kelas (SCD Logic)
        if ($pindahWali) {
            $kelasAsal = $this->kelasModel->find($kelasAsalId);
            if (!empty($kelasAsal['wali_kelas_id'])) {
                // Tanamkan ID Wali Kelas asal ke Kelas Tujuan
                $this->kelasModel->update($kelasTujuanId, ['wali_kelas_id' => $kelasAsal['wali_kelas_id']]);
                // Cabut jabatan Wali Kelas dari Kelas Asal (dikosongkan)
                $this->kelasModel->update($kelasAsalId, ['wali_kelas_id' => null]);
            }
        }

        $this->kelasModel->db->transComplete();

        if ($this->kelasModel->db->transStatus() === false) {
            return redirect()->to('/admin/mutasi')->with('error', 'Terjadi fatal error pada database saat memproses mutasi.');
        }

        return redirect()->to('/admin/mutasi')->with('success', count($siswaIds) . ' Siswa berhasil dimutasi secara aman.');
    }
}
