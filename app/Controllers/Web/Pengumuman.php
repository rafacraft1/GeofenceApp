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
            'title'       => 'Broadcast Pengumuman',
            'pengumuman'  => $this->pengumumanModel->orderBy('created_at', 'DESC')->paginate(20, 'default'),
            'pager_links' => $this->pengumumanModel->pager->links('default', 'tailwind_pagination')
        ];

        return view('web/pengumuman', $data);
    }

    public function store()
    {
        // Validasi CI4: max_size 2048 berarti batas atas semua jenis file adalah 2MB
        $rules = [
            'judul'  => 'required|min_length[5]|max_length[150]',
            'isi'    => 'required',
            'tipe'   => 'required|in_list[Info,Penting,Libur]',
            'gambar' => 'max_size[gambar,2048]|ext_in[gambar,jpg,jpeg,png,pdf]|mime_in[gambar,image/jpg,image/jpeg,image/png,application/pdf]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $gambar     = $this->request->getFile('gambar');
        $namaGambar = null;

        if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
            // Pengecekan manual > 1MB dihapus agar tidak tumpang tindih. 
            // CI4 sudah memblokir otomatis file di atas 2MB berdasarkan rules di atas.
            $namaGambar = $gambar->getRandomName();
            $gambar->move(FCPATH . 'uploads/pengumuman', $namaGambar);
        }

        $judul = (string) $this->request->getPost('judul');
        $isi   = (string) $this->request->getPost('isi');

        $insertedId = $this->pengumumanModel->insert([
            'judul'  => $judul,
            'isi'    => $isi,
            'tipe'   => (string) $this->request->getPost('tipe'),
            'gambar' => $namaGambar
        ]);

        // Ambil semua token fcm siswa
        $allTokens = $this->siswaModel->select('fcm_token')->where('fcm_token IS NOT NULL')->findAll();
        $tokenList = array_column($allTokens, 'fcm_token');

        if (!empty($tokenList)) {
            helper('fcm');

            $dataPayload = [
                'type'   => 'pengumuman',
                'id_ref' => (string) $insertedId
            ];

            // --- ANTI CRASH FIREBASE: Pecah token per 500 data ---
            $chunkedTokens = array_chunk($tokenList, 500);

            foreach ($chunkedTokens as $batchTokens) {
                send_fcm_notification(
                    $batchTokens,
                    "📢 " . $judul,
                    substr(strip_tags($isi), 0, 100) . "...",
                    $dataPayload
                );
            }
            // -----------------------------------------------------
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
            return redirect()->to(base_url('admin/pengumuman'))->with('success', 'Pengumuman berhasil dihapus!');
        }

        return redirect()->to(base_url('admin/pengumuman'))->with('error', 'Data tidak ditemukan.');
    }
}
