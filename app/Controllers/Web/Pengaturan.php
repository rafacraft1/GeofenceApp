<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;

class Pengaturan extends Controller
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $config = $this->db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        $data = [
            'title'  => 'Pengaturan Sistem',
            'config' => $config
        ];

        return view('web/pengaturan', $data);
    }

    public function save()
    {
        $rules = [
            'latitude_sekolah'  => 'required',
            'longitude_sekolah' => 'required',
            'radius_meter'      => 'required|numeric',
            'firebase_url'      => 'permit_empty|valid_url', // Diubah menjadi opsional agar fleksibel
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid: ' . $this->validator->listErrors());
        }

        $this->db->table('pengaturan')->where('id_pengaturan', 1)->update([
            'latitude_sekolah'  => $this->request->getPost('latitude_sekolah'),
            'longitude_sekolah' => $this->request->getPost('longitude_sekolah'),
            'radius_meter'      => $this->request->getPost('radius_meter'),
            'firebase_url'      => $this->request->getPost('firebase_url'),
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
