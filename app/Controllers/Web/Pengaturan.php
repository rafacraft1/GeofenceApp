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
            'title'  => 'Pengaturan Sistem',
            'config' => $this->pengaturanModel->find(1)
        ];

        return view('web/pengaturan', $data);
    }

    public function save()
    {
        $rules = [
            'latitude_sekolah'  => 'required',
            'longitude_sekolah' => 'required',
            'radius_meter'      => 'required|numeric',
            'firebase_url'      => 'permit_empty|valid_url'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid: ' . $this->validator->listErrors());
        }

        $this->pengaturanModel->update(1, [
            'latitude_sekolah'  => (string) $this->request->getPost('latitude_sekolah'),
            'longitude_sekolah' => (string) $this->request->getPost('longitude_sekolah'),
            'radius_meter'      => (int) $this->request->getPost('radius_meter'),
            'firebase_url'      => (string) $this->request->getPost('firebase_url')
        ]); // updated_at diurus otomatis jika di set useTimestamps = true di pengaturan model

        return redirect()->back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }
}
