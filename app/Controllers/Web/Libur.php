<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\HariLiburModel;

class Libur extends BaseController
{
    protected HariLiburModel $liburModel;

    public function __construct()
    {
        $this->liburModel = new HariLiburModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Hari Libur',
            'daftar_libur' => $this->liburModel->orderBy('tanggal', 'DESC')->findAll()
        ];

        return view('web/libur', $data);
    }

    public function store()
    {
        $tanggal = $this->request->getPost('tanggal');
        $keterangan = $this->request->getPost('keterangan');

        if (empty($tanggal) || empty($keterangan)) {
            return redirect()->back()->with('error', 'Tanggal dan Keterangan wajib diisi!');
        }

        $cekLibur = $this->liburModel->where('tanggal', $tanggal)->first();
        if ($cekLibur) {
            return redirect()->back()->with('error', 'Tanggal libur tersebut sudah terdaftar di sistem!');
        }

        $this->liburModel->insert([
            'tanggal'    => $tanggal,
            'keterangan' => $keterangan,
        ]);

        return redirect()->to('/admin/libur')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function delete(string $id)
    {
        $this->liburModel->delete($id);

        return redirect()->to('/admin/libur')->with('success', 'Hari libur berhasil dihapus.');
    }
}
