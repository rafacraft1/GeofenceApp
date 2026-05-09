<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Pengumuman extends BaseController
{
    // $this->db sudah diinisialisasi otomatis oleh BaseController

    public function index()
    {
        $pengumuman = $this->db->table('pengumuman')->orderBy('created_at', 'DESC')->get()->getResultArray();

        $data = [
            'title'      => 'Broadcast Pengumuman',
            'pengumuman' => $pengumuman
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

        $judul = $this->request->getPost('judul');
        $isi   = $this->request->getPost('isi');

        $this->db->table('pengumuman')->insert([
            'judul'      => $judul,
            'isi'        => $isi,
            'tipe'       => $this->request->getPost('tipe'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // TRIGGER PUSH NOTIFICATION (Fase 5)
        $allTokens = $this->db->table('siswa')->select('fcm_token')->where('fcm_token IS NOT NULL')->get()->getResultArray();
        $tokenList = array_column($allTokens, 'fcm_token');

        if (!empty($tokenList)) {
            send_fcm_notification($tokenList, "📢 " . $judul, substr(strip_tags((string) $isi), 0, 100) . "...");
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil disiarkan!');
    }

    public function delete(string $id)
    {
        $this->db->table('pengumuman')->where('id_pengumuman', $id)->delete();
        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditarik/dihapus.');
    }
}
