<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;

class Pengaturan extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data = [
            'title'  => 'Pengaturan Geofence & Waktu',
            'config' => $db->table('pengaturan')->where('id', 1)->get()->getRow()
        ];
        return view('web/pengaturan', $data);
    }

    public function save()
    {
        $db = \Config\Database::connect();
        $updateData = [
            // Ubah menjadi latitude_sekolah dan longitude_sekolah
            'latitude_sekolah'  => $this->request->getPost('lat'),
            'longitude_sekolah' => $this->request->getPost('long'),
            'radius_meter'      => $this->request->getPost('radius'),
            'jam_masuk'         => $this->request->getPost('jam_masuk'),
            'jam_pulang'        => $this->request->getPost('jam_pulang'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        // Toleransi menit tidak lagi diupdate/disimpan

        $db->table('pengaturan')->where('id', 1)->update($updateData);
        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
