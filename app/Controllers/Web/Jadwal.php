<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Jadwal extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data = [
            'title'  => 'Pengaturan Hari Aktif Global',
            'jadwal' => $db->table('jadwal_absen')->orderBy('kode_hari', 'ASC')->get()->getResultArray()
        ];

        return view('web/jadwal', $data);
    }

    public function update()
    {
        $db = \Config\Database::connect();
        $liburKode = $this->request->getPost('is_libur') ?? [];

        $db->transStart();
        $db->table('jadwal_absen')->update(['is_libur' => 0]);
        if (!empty($liburKode)) {
            $db->table('jadwal_absen')->whereIn('kode_hari', $liburKode)->update(['is_libur' => 1]);
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/jadwal')->with('error', 'Terjadi kesalahan saat menyimpan jadwal.');
        }

        return redirect()->to('/admin/jadwal')->with('success', 'Status hari aktif & akhir pekan berhasil diperbarui.');
    }
}
