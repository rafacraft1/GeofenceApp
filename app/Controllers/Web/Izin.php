<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class Izin extends BaseController
{
    public function index()
    {
        $daftarIzin = $this->db->table('pengajuan_izin')
            ->select('pengajuan_izin.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy("FIELD(pengajuan_izin.status, 'Pending', 'Approved', 'Rejected')", '', false)
            ->orderBy('pengajuan_izin.created_at', 'DESC')
            ->get()->getResultArray();

        $data = [
            'title'      => 'Manajemen Pengajuan Izin',
            'daftarIzin' => $daftarIzin
        ];

        return view('web/izin/index', $data);
    }

    public function approve(string $idIzin)
    {
        $izin = $this->db->table('pengajuan_izin')->where('id_izin', $idIzin)->get()->getRowArray();

        if (!$izin || $izin['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Data tidak valid atau sudah diproses.');
        }

        $this->db->transStart();

        $this->db->table('pengajuan_izin')->where('id_izin', $idIzin)->update([
            'status'     => 'Approved',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $tglMulai   = Time::parse($izin['tanggal_mulai']);
        $tglSelesai = Time::parse($izin['tanggal_selesai']);
        $insertData = [];

        while ($tglMulai->toDateString() <= $tglSelesai->toDateString()) {
            $tanggalString = $tglMulai->toDateString();

            $this->db->table('absensi')->where([
                'siswa_id' => $izin['siswa_id'],
                'tanggal'  => $tanggalString
            ])->delete();

            $insertData[] = [
                'siswa_id'    => $izin['siswa_id'],
                'tanggal'     => $tanggalString,
                'status'      => $izin['jenis'],
                'keterangan'  => 'Disetujui via sistem: ' . $izin['alasan'],
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];

            $tglMulai = $tglMulai->addDays(1);
        }

        if (!empty($insertData)) {
            $this->db->table('absensi')->insertBatch($insertData);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyetujui izin.');
        }

        return redirect()->back()->with('success', 'Pengajuan berhasil disetujui. Data absensi otomatis diperbarui.');
    }

    public function reject(string $idIzin)
    {
        $this->db->table('pengajuan_izin')->where('id_izin', $idIzin)->update([
            'status'     => 'Rejected',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}
