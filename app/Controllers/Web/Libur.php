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
        $search  = trim((string) $this->request->getGet('search'));
        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page_libur') ?? 1);
        $perPage = 10;

        $builder = $this->liburModel;

        if (!empty($search)) {
            $builder->groupStart()
                ->like('keterangan', $search)
                ->orLike('tanggal', $search)
                ->groupEnd();
        }

        $totalData    = $builder->countAllResults(false);
        $daftar_libur = $builder->orderBy('tanggal', 'DESC')->paginate($perPage, 'libur');
        $pagerLinks   = $this->liburModel->pager->makeLinks($page, $perPage, $totalData, 'default_full', 0, 'libur');

        $data = [
            'title'        => 'Manajemen Hari Libur',
            'daftar_libur' => $daftar_libur,
            'search'       => $search,
            'pager_links'  => $pagerLinks,
            'page'         => $page,
            'perPage'      => $perPage,
            'total_data'   => $totalData
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

        // Menggunakan back() agar state pagination tetap terjaga
        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function delete(string $id)
    {
        $this->liburModel->delete($id);
        return redirect()->back()->with('success', 'Hari libur berhasil dihapus secara permanen.');
    }
}
