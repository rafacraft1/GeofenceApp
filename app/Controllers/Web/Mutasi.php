<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\SiswaModel;

class Mutasi extends BaseController
{
    protected KelasModel $kelasModel;
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->kelasModel = new KelasModel();
        $this->siswaModel = new SiswaModel();
    }

    public function index()
    {
        // 1. Ambil semua kelas untuk dropdown
        $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        // 2. Tangkap query string 'asal' (Sesuai dengan name="asal" di View formPilihAsal)
        $kelasAsalId = $this->request->getGet('asal');

        $siswaAsal = [];
        $kelasAsalData = null;

        if (!empty($kelasAsalId)) {
            // 3. Ambil detail kelas asal beserta nama Wali Kelasnya (jika ada)
            $kelasAsalData = $this->kelasModel->select('kelas.*, users.nama_lengkap as nama_wali')
                ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
                ->find($kelasAsalId);

            // 4. Ambil daftar siswa di kelas tersebut
            $siswaAsal = $this->siswaModel->where('kelas_id', $kelasAsalId)
                ->orderBy('nama_siswa', 'ASC')
                ->findAll();
        }

        // 5. Kirim data ke View dengan nama variabel yang SAMA PERSIS dengan deklarasi di View
        $data = [
            'title'         => 'Mutasi & Kenaikan Kelas',
            'listKelas'     => $listKelas,
            'kelasAsalId'   => $kelasAsalId,
            'kelasAsalData' => $kelasAsalData,
            'siswaAsal'     => $siswaAsal
        ];

        return view('web/mutasi', $data);
    }

    public function proses()
    {
        $aturanValidasi = [
            'kelas_asal'   => 'required|numeric',
            'kelas_tujuan' => 'required|numeric',
            'siswa_ids'    => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Pilih kelas asal, kelas tujuan, dan minimal 1 siswa untuk dimutasi.');
        }

        $kelasAsal   = (int) $this->request->getPost('kelas_asal');
        $kelasTujuan = (int) $this->request->getPost('kelas_tujuan');
        $siswaIds    = $this->request->getPost('siswa_ids');
        $pindahWali  = $this->request->getPost('pindah_wali') == '1';

        if ($kelasAsal === $kelasTujuan) {
            return redirect()->back()->with('error', 'Kelas asal dan kelas tujuan tidak boleh sama.');
        }

        if (!is_array($siswaIds) || empty($siswaIds)) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih.');
        }

        $this->siswaModel->db->transStart();

        // 1. Eksekusi pemindahan Siswa
        foreach ($siswaIds as $idSiswa) {
            $this->siswaModel->update($idSiswa, [
                'kelas_id' => $kelasTujuan,
                'zona_id'  => null // MENCEGAH GHOST PKL: Reset Zona PKL ke default saat pindah kelas
            ]);

            // Bersihkan cache absensi anak ini agar API-nya langsung minta zona yang baru
            cache()->delete("siswa_auth_{$idSiswa}");
        }

        // 2. Eksekusi pemindahan Wali Kelas (Jika dicentang oleh Admin)
        if ($pindahWali) {
            $kelasAsalData = $this->kelasModel->find($kelasAsal);
            if ($kelasAsalData && !empty($kelasAsalData['wali_kelas_id'])) {
                $waliId = $kelasAsalData['wali_kelas_id'];
                // Cabut guru dari kelas asal
                $this->kelasModel->update($kelasAsal, ['wali_kelas_id' => null]);
                // Pindahkan guru menjadi wali di kelas tujuan
                $this->kelasModel->update($kelasTujuan, ['wali_kelas_id' => $waliId]);
            }
        }

        $this->siswaModel->db->transComplete();

        if ($this->siswaModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses mutasi.');
        }

        // Hapus cache publik
        cache()->deleteMatching('list_siswa_dropdown_*');
        cache()->deleteMatching('leaderboard_*');

        // Redirect ke kelas tujuan untuk melihat hasilnya langsung
        return redirect()->to('/admin/mutasi?asal=' . $kelasTujuan)
            ->with('success', count($siswaIds) . ' siswa berhasil dipindahkan ke kelas baru.');
    }

    // Endpoint API Internal untuk AJAX "Smart Merge Warning" di Frontend
    public function checkTujuan(string $id)
    {
        $jumlahSiswa = $this->siswaModel->where('kelas_id', $id)->countAllResults();
        $kelasInfo   = $this->kelasModel->select('kelas.*, users.nama_lengkap as nama_wali')
            ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
            ->find($id);

        return $this->response->setJSON([
            'status'    => 200,
            'jumlah'    => $jumlahSiswa,
            'nama_wali' => $kelasInfo['nama_wali'] ?? 'Belum Ditentukan'
        ]);
    }
}
