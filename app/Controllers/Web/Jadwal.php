<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\JadwalAbsenModel;

class Jadwal extends BaseController
{
    protected JadwalAbsenModel $jadwalModel;

    public function __construct()
    {
        $this->jadwalModel = new JadwalAbsenModel();
    }

    public function index()
    {
        $data = [
            'title'  => 'Manajemen Jadwal Harian',
            // Ambil semua hari, urutkan dari Senin (1) sampai Minggu (7)
            'daftar_jadwal' => $this->jadwalModel->orderBy('kode_hari', 'ASC')->findAll()
        ];

        return view('web/jadwal', $data);
    }

    public function update()
    {
        // Menangkap array data dari form
        $dataJadwal = $this->request->getPost('jadwal');

        if ($dataJadwal && is_array($dataJadwal)) {
            foreach ($dataJadwal as $id => $data) {
                // Jika checkbox libur dicentang, is_libur = 1, sisanya 0
                $isLibur = isset($data['is_libur']) ? 1 : 0;

                // Jika libur, jam bisa dikosongkan (null)
                $jamMasuk = empty($data['jam_masuk']) ? null : $data['jam_masuk'];
                $jamPulang = empty($data['jam_pulang']) ? null : $data['jam_pulang'];

                $this->jadwalModel->update($id, [
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'is_libur'   => $isLibur
                ]);
            }
            return redirect()->back()->with('success', 'Jadwal Mingguan berhasil diperbarui!');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui jadwal.');
    }
}
