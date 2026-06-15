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
        $perPage = 15;
        $page    = (int) ($this->request->getGet('page') ?? 1);

        $data = [
            'title'        => 'Manajemen Hari Libur',
            'daftar_libur' => $this->liburModel->orderBy('tanggal', 'DESC')->paginate($perPage, 'default'),
            'pager_links'  => $this->liburModel->pager->links('default', 'tailwind_pagination')
        ];

        return view('web/libur', $data);
    }

    public function store()
    {
        $aturanValidasi = [
            'tanggal'    => 'required|valid_date[Y-m-d]',
            'keterangan' => 'required',
            'tipe_libur' => 'required|in_list[Nasional,Internal]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal, Keterangan, atau Tipe Libur tidak valid.');
        }

        $tanggal    = (string) $this->request->getPost('tanggal');
        $keterangan = (string) $this->request->getPost('keterangan');
        $tipeLibur  = (string) $this->request->getPost('tipe_libur');

        $cekLibur = $this->liburModel->where('tanggal', $tanggal)->first();
        if ($cekLibur) {
            return redirect()->back()->withInput()->with('error', 'Tanggal libur tersebut sudah terdaftar di sistem!');
        }

        $this->liburModel->insert([
            'tanggal'    => $tanggal,
            'keterangan' => $keterangan,
            'tipe_libur' => $tipeLibur
        ]);

        cache()->delete('hari_libur_' . $tanggal);

        return redirect()->to('/admin/libur')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function delete(string $id)
    {
        $libur = $this->liburModel->find($id);
        if ($libur) {
            cache()->delete('hari_libur_' . $libur['tanggal']);
            $this->liburModel->delete($id);
        }

        return redirect()->to('/admin/libur')->with('success', 'Hari libur berhasil dihapus.');
    }
}
