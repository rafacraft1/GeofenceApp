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
        return view('web/pengumuman', [
            'title'      => 'Broadcast Pengumuman',
            'pengumuman' => $this->pengumumanModel->orderBy('created_at', 'DESC')->findAll()
        ]);
    }

    public function store()
    {
        // 1. Validasi Ketat
        $rules = [
            'judul'  => 'required|min_length[5]|max_length[150]',
            'isi'    => 'required',
            'tipe'   => 'required|in_list[Info,Penting,Libur]',
            'gambar' => 'permit_empty|max_size[gambar,2048]|ext_in[gambar,jpg,jpeg,png,pdf]|mime_in[gambar,image/jpg,image/jpeg,image/png,application/pdf]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $fileGambar = $this->request->getFile('gambar');
        $namaGambar = null;

        if ($fileGambar && $fileGambar->isValid() && !$fileGambar->hasMoved()) {
            // Proteksi ukuran PDF 1MB
            if ($fileGambar->getMimeType() === 'application/pdf' && $fileGambar->getSizeByUnit('kb') > 1024) {
                return redirect()->back()->withInput()->with('error', 'Gagal Upload: Ukuran file PDF maksimal 1 MB.');
            }

            $namaGambar = $fileGambar->getRandomName();
            $fileGambar->move(FCPATH . 'uploads/pengumuman', $namaGambar);
        }

        $this->pengumumanModel->insert([
            'judul'  => (string)$this->request->getPost('judul'),
            'isi'    => (string)$this->request->getPost('isi'),
            'tipe'   => (string)$this->request->getPost('tipe'),
            'gambar' => $namaGambar
        ]);

        // 2. Optimasi Pengiriman FCM (Batch/Chunking)
        $tokens = $this->siswaModel->select('fcm_token')->where('fcm_token IS NOT NULL')->findAll();
        $tokenList = array_column($tokens, 'fcm_token');

        if (!empty($tokenList)) {
            helper('fcm');
            // Jika token sangat banyak, gunakan chunking agar tidak timeout
            $chunks = array_chunk($tokenList, 500);
            foreach ($chunks as $chunk) {
                send_fcm_notification($chunk, "📢 " . $this->request->getPost('judul'), substr(strip_tags((string)$this->request->getPost('isi')), 0, 100) . "...");
            }
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
        return redirect()->to(base_url('admin/pengumuman'))->with('success', 'Pengumuman berhasil ditarik.');
    }
}
