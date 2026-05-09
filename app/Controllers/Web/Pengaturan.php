<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Pengaturan extends BaseController
{
    // $this->db sudah diinisialisasi otomatis oleh BaseController

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
            'firebase_url'      => 'permit_empty|valid_url', // Fleksibel jika kosong
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
