<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;

class Pengaturan extends Controller
{
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Ambil data pengaturan ID 1
        $config = $this->db->table('pengaturan')->where('id', 1)->get()->getRow();

        $data = [
            'title'  => 'Pengaturan Sistem',
            'config' => $config
        ];

        return view('web/pengaturan', $data);
    }

    public function save()
    {
        // Validasi input sederhana
        $rules = [
            'latitude_sekolah'  => 'required',
            'longitude_sekolah' => 'required',
            'radius_meter'      => 'required|numeric',
            'firebase_url'      => 'required|valid_url',
            'jam_masuk'         => 'required',
            'jam_pulang'        => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Pastikan semua data terisi dengan benar.');
        }

        // Update data ke database
        $this->db->table('pengaturan')->where('id', 1)->update([
            'latitude_sekolah'  => $this->request->getPost('latitude_sekolah'),
            'longitude_sekolah' => $this->request->getPost('longitude_sekolah'),
            'radius_meter'      => $this->request->getPost('radius_meter'),
            'firebase_url'      => $this->request->getPost('firebase_url'), // Kolom baru
            'jam_masuk'         => $this->request->getPost('jam_masuk'),
            'jam_pulang'        => $this->request->getPost('jam_pulang'),
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
