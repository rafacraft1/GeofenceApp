<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\KelasModel;

class Kelas extends BaseController
{
    // PERBAIKAN: Menambahkan tipe data 'KelasModel' pada properti
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

    public function store()
    {
        $this->kelasModel->save([
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'wali_kelas' => $this->request->getPost('wali_kelas'),
        ]);

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil ditambahkan.');
    }

    // PERBAIKAN: Menambahkan tipe data 'string' pada parameter $id
    public function update(string $id)
    {
        $this->kelasModel->update($id, [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'wali_kelas' => $this->request->getPost('wali_kelas'),
        ]);

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil diperbarui.');
    }

    // PERBAIKAN: Menambahkan tipe data 'string' pada parameter $id
    public function delete(string $id)
    {
        $this->kelasModel->delete($id);

        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil dihapus.');
    }
}
