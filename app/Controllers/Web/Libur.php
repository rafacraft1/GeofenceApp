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
            'title'        => 'Manajemen Hari Libur',
            'daftar_libur' => $this->liburModel->orderBy('tanggal', 'DESC')->findAll()
        ];

        return view('web/libur', $data);
    }

    public function store()
    {
        $aturanValidasi = [
            'tanggal'    => 'required|valid_date[Y-m-d]',
            'keterangan' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal (Format Y-m-d) dan Keterangan wajib diisi dengan benar.');
        }

        $tanggal    = (string) $this->request->getPost('tanggal');
        $keterangan = (string) $this->request->getPost('keterangan');

        $cekLibur = $this->liburModel->where('tanggal', $tanggal)->first();
        if ($cekLibur) {
            return redirect()->back()->withInput()->with('error', 'Tanggal libur tersebut sudah terdaftar di sistem!');
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
