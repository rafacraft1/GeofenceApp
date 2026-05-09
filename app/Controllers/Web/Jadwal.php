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
            'daftar_jadwal' => $this->jadwalModel->orderBy('kode_hari', 'ASC')->findAll()
        ];

        return view('web/jadwal', $data);
    }

    public function update()
    {
        $dataJadwal = $this->request->getPost('jadwal');

        if ($dataJadwal && is_array($dataJadwal)) {
            $this->jadwalModel->db->transStart(); // Mulai transaksi

            foreach ($dataJadwal as $id => $data) {
                $isLibur = isset($data['is_libur']) ? 1 : 0;
                $jamMasuk = empty($data['jam_masuk']) ? null : $data['jam_masuk'];
                $jamPulang = empty($data['jam_pulang']) ? null : $data['jam_pulang'];

                $this->jadwalModel->update($id, [
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'is_libur'   => $isLibur
                ]);
            }

            $this->jadwalModel->db->transComplete(); // Selesaikan transaksi

            if ($this->jadwalModel->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memperbarui jadwal ke database.');
            }

            return redirect()->back()->with('success', 'Jadwal Mingguan berhasil diperbarui!');
        }

        return redirect()->back()->with('error', 'Data jadwal tidak ditemukan.');
    }
}
