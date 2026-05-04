<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;

class Pengumuman extends Controller
{
    /** @var BaseConnection */
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // PERBAIKAN: Gunakan getResultArray()
        $pengumuman = $this->db->table('pengumuman')->orderBy('created_at', 'DESC')->get()->getResultArray();

        $data = [
            'title'      => 'Broadcast Pengumuman',
            'pengumuman' => $pengumuman
        ];

        return view('web/pengumuman', $data);
    }

    public function store()
    {
        $this->db->table('pengumuman')->insert([
            'judul'      => $this->request->getPost('judul'),
            'isi'        => $this->request->getPost('isi'),
            'tipe'       => $this->request->getPost('tipe'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil disiarkan!');
    }

    public function delete(string $id)
    {
        // PERBAIKAN: Gunakan id_pengumuman
        $this->db->table('pengumuman')->where('id_pengumuman', $id)->delete();
        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditarik/dihapus.');
    }
}
