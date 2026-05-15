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
            'judul'  => 'required|min_length[5]|max_length[150]',
            'isi'    => 'required',
            'tipe'   => 'required|in_list[Info,Penting,Libur]',
            // Validasi format dilonggarkan untuk PDF, batas global 2MB (namun PDF akan dibatasi mutlak 1MB di bawah)
            'gambar' => 'max_size[gambar,2048]|ext_in[gambar,jpg,jpeg,png,pdf]|mime_in[gambar,image/jpg,image/jpeg,image/png,application/pdf]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $judul = (string) $this->request->getPost('judul');
        $isi   = (string) $this->request->getPost('isi');
        $tipe  = (string) $this->request->getPost('tipe');

        $fileGambar = $this->request->getFile('gambar');
        $namaGambar = null;

        if ($fileGambar && $fileGambar->isValid() && !$fileGambar->hasMoved()) {

            // PROTEKSI BACKEND MUTLAK: Jika file adalah PDF, cek ukurannya tidak boleh lebih dari 1 MB (1024 KB)
            if ($fileGambar->getMimeType() === 'application/pdf') {
                if ($fileGambar->getSizeByUnit('kb') > 1024) {
                    return redirect()->back()->withInput()->with('error', 'Gagal Upload: Ukuran file PDF maksimal adalah 1 MB.');
                }
            }

            $namaGambar = $fileGambar->getRandomName();
            $fileGambar->move(FCPATH . 'uploads/pengumuman', $namaGambar);
        }

        $this->pengumumanModel->insert([
            'judul'  => $judul,
            'isi'    => $isi,
            'tipe'   => $tipe,
            'gambar' => $namaGambar
        ]);

        // TRIGGER PUSH NOTIFICATION (FCM)
        $allTokens = $this->siswaModel->select('fcm_token')->where('fcm_token IS NOT NULL')->findAll();
        $tokenList = array_column($allTokens, 'fcm_token');

        if (!empty($tokenList)) {
            helper('fcm');
            send_fcm_notification($tokenList, "📢 " . $judul, substr(strip_tags($isi), 0, 100) . "...");
        }

        return redirect()->to(base_url('admin/pengumuman'))->with('success', 'Pengumuman berhasil disebarkan!');
    }

    public function delete(string $id)
    {
        $pengumuman = $this->pengumumanModel->find($id);

        if ($pengumuman) {
            if (!empty($pengumuman['gambar']) && file_exists(FCPATH . 'uploads/pengumuman/' . $pengumuman['gambar'])) {
                unlink(FCPATH . 'uploads/pengumuman/' . $pengumuman['gambar']);
            }

            $this->pengumumanModel->delete($id);
        }

        return redirect()->to(base_url('admin/pengumuman'))->with('success', 'Pengumuman berhasil ditarik dan dihapus.');
    }
}
