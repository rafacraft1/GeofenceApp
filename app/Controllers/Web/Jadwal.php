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
        // PROTEKSI SPA: Pastikan request berasal dari AJAX Frontend
        if (!$this->request->isAJAX()) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        $dataJadwal = $this->request->getPost('jadwal');

        if ($dataJadwal && is_array($dataJadwal)) {
            $this->jadwalModel->db->transStart();

            foreach ($dataJadwal as $id => $data) {
                $isLibur   = isset($data['is_libur']) ? 1 : 0;
                $jamMasuk  = empty($data['jam_masuk']) ? null : (string) $data['jam_masuk'];
                $jamPulang = empty($data['jam_pulang']) ? null : (string) $data['jam_pulang'];

                // VALIDASI LOGIKA WAKTU: Jika tidak libur, jam wajib diisi dan logis
                if (!$isLibur) {
                    if (empty($jamMasuk) || empty($jamPulang)) {
                        $this->jadwalModel->db->transRollback();
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Hari kerja wajib diisi Jam Masuk dan Jam Pulang secara lengkap.']);
                    }

                    if (strtotime($jamMasuk) >= strtotime($jamPulang)) {
                        $this->jadwalModel->db->transRollback();
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Logika Waktu Salah: Jam Pulang tidak boleh lebih awal dari Jam Masuk.']);
                    }
                }

                $this->jadwalModel->update((int) $id, [
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'is_libur'   => $isLibur
                ]);
            }

            $this->jadwalModel->db->transComplete();

            if ($this->jadwalModel->db->transStatus() === false) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui jadwal ke database. Silakan coba lagi.']);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Konfigurasi jadwal mingguan berhasil disimpan secara permanen.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Payload jadwal tidak terdeteksi atau tidak valid.']);
    }
}
