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
        $data = [
            'title' => 'Manajemen Kelas',
            'kelas' => $this->kelasModel->findAll()
        ];

        return view('web/kelas', $data);
    }

    /**
     * Metode Dinamis: Insert jika baru, Update jika nama_kelas sudah ada.
     */
    public function store()
    {
        $namaKelas = (string) $this->request->getPost('nama_kelas');
        $waliKelas = (string) $this->request->getPost('wali_kelas');

        // Cari apakah nama kelas sudah ada (karena bersifat unik secara logika)
        $existing = $this->kelasModel->where('nama_kelas', $namaKelas)->first();

        if ($existing) {
            // Jika ada, lakukan Update
            $this->kelasModel->update($existing['id_kelas'], [
                'wali_kelas' => $waliKelas,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $pesan = "Data kelas $namaKelas berhasil diperbarui.";
        } else {
            // Jika tidak ada, lakukan Insert
            $this->kelasModel->save([
                'nama_kelas' => $namaKelas,
                'wali_kelas' => $waliKelas,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $pesan = "Data kelas $namaKelas berhasil ditambahkan.";
        }

        return redirect()->to('/admin/kelas')->with('success', $pesan);
    }

    // Metode update manual tetap dipertahankan jika dibutuhkan oleh rute spesifik, 
    // namun secara fungsional sudah tercover oleh store().
    public function update(string $id)
    {
        $this->kelasModel->update($id, [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'wali_kelas' => $this->request->getPost('wali_kelas'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $this->db->transStart();

        // Hapus siswa terkait agar tidak melanggar Foreign Key RESTRICT
        $this->db->table('siswa')->where('kelas_id', $id)->delete();
        $this->kelasModel->delete($id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->to('/admin/kelas')->with('error', 'Gagal menghapus kelas.');
        }

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas dan seluruh siswa di dalamnya berhasil dihapus.');
    }
}
