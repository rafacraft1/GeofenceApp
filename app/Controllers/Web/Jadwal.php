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
            'title'         => 'Manajemen Jadwal Harian',
            'daftar_jadwal' => $this->jadwalModel->orderBy('kode_hari', 'ASC')->findAll()
        ];

        return view('web/jadwal', $data);
    }

    public function update()
    {
        $dataJadwal = $this->request->getPost('jadwal');

        if ($dataJadwal && is_array($dataJadwal)) {
            $this->jadwalModel->db->transStart();

            foreach ($dataJadwal as $id => $data) {
                // Keamanan ekstra: filter array
                $isLibur   = isset($data['is_libur']) ? 1 : 0;
                $jamMasuk  = empty($data['jam_masuk']) ? null : (string) $data['jam_masuk'];
                $jamPulang = empty($data['jam_pulang']) ? null : (string) $data['jam_pulang'];

                $this->jadwalModel->update((int) $id, [
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'is_libur'   => $isLibur
                ]);
            }

            $this->jadwalModel->db->transComplete();

            if ($this->jadwalModel->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memperbarui jadwal ke database.');
            }

            return redirect()->back()->with('success', 'Konfigurasi jadwal mingguan berhasil disimpan.');
        }

        return redirect()->back()->with('error', 'Payload jadwal tidak valid.');
    }
}
