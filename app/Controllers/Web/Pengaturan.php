<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\PengaturanModel;

class Pengaturan extends BaseController
{
    protected PengaturanModel $pengaturanModel;

    public function __construct()
    {
        $this->pengaturanModel = new PengaturanModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Pengaturan Sistem',
            'pengaturan' => $this->pengaturanModel->find(1)
        ];

        return view('web/pengaturan', $data);
    }

    public function save()
    {
        $rules = [
            'nama_aplikasi' => 'required|min_length[3]',
            'nama_sekolah'  => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'nama_aplikasi' => (string) $this->request->getPost('nama_aplikasi'),
            'nama_sekolah'  => (string) $this->request->getPost('nama_sekolah'),
            'firebase_url'  => (string) $this->request->getPost('firebase_url'),
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        $this->pengaturanModel->update(1, $data);

        session()->set([
            'nama_aplikasi' => $data['nama_aplikasi'],
            'nama_sekolah'  => $data['nama_sekolah']
        ]);

        cache()->delete('pengaturan_global');

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan identitas aplikasi berhasil diperbarui!');
    }
}
