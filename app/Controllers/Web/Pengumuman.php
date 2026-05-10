<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\PengumumanModel;
use App\Models\SiswaModel;

class Pengumuman extends BaseController
{
    protected PengumumanModel $pengumumanModel;
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
        $this->siswaModel      = new SiswaModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Broadcast Pengumuman',
            'pengumuman' => $this->pengumumanModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('web/pengumuman', $data);
    }

    public function store()
    {
        $rules = [
            'judul' => 'required|min_length[5]|max_length[150]',
            'isi'   => 'required',
            'tipe'  => 'required|in_list[Info,Peringatan,Penting]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Pastikan judul dan isi pengumuman terisi dengan benar.');
        }

        $judul = (string) $this->request->getPost('judul');
        $isi   = (string) $this->request->getPost('isi');

        $this->pengumumanModel->insert([
            'judul' => $judul,
            'isi'   => $isi,
            'tipe'  => (string) $this->request->getPost('tipe')
        ]);

        // TRIGGER PUSH NOTIFICATION
        $allTokens = $this->siswaModel->select('fcm_token')->where('fcm_token IS NOT NULL')->findAll();
        $tokenList = array_column($allTokens, 'fcm_token');

        if (!empty($tokenList)) {
            helper('fcm');
            send_fcm_notification($tokenList, "📢 " . $judul, substr(strip_tags($isi), 0, 100) . "...");
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil disiarkan!');
    }

    public function delete(string $id)
    {
        $this->pengumumanModel->delete($id);
        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditarik/dihapus.');
    }
}
