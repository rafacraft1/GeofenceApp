<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;

class Pengumuman extends Controller
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

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
        // PERBAIKAN: Penambahan validasi input
        $rules = [
            'judul' => 'required|min_length[5]|max_length[150]',
            'isi'   => 'required',
            'tipe'  => 'required|in_list[Info,Peringatan,Penting]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Pastikan judul dan isi pengumuman terisi dengan benar.');
        }

        $this->db->table('pengumuman')->insert([
            'judul'      => $this->request->getPost('judul'),
            'isi'        => $this->request->getPost('isi'),
            'tipe'       => $this->request->getPost('tipe'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil disiarkan!');
    }

    public function delete(string $id)
    {
        $this->db->table('pengumuman')->where('id_pengumuman', $id)->delete();
        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditarik/dihapus.');
    }
}
