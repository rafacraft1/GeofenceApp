<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class Izin extends BaseController
{
    public function index()
    {
        // PERBAIKAN: Menggunakan kueri kustom pada orderBy agar fungsi FIELD() tidak dirusak oleh CI4
        $daftarIzin = $this->db->table('pengajuan_izin')
            ->select('pengajuan_izin.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy("FIELD(pengajuan_izin.status, 'Pending', 'Approved', 'Rejected')", '', false) // Parameter false mematikan escaping
            ->orderBy('pengajuan_izin.created_at', 'DESC')
            ->get()->getResultArray();

        $data = [
            'title'      => 'Manajemen Pengajuan Izin',
            'daftarIzin' => $daftarIzin
        ];

        return view('web/izin/index', $data);
    }

    // PERBAIKAN: Penambahan strict type-hinting 'string' pada parameter $id_izin
    public function approve(string $id_izin)
    {
        $izin = $this->db->table('pengajuan_izin')->where('id_izin', $id_izin)->get()->getRowArray();

        if (!$izin || $izin['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Data tidak valid atau sudah diproses.');
        }

        $this->db->transStart();

        // 1. Ubah status pengajuan menjadi Approved
        $this->db->table('pengajuan_izin')->where('id_izin', $id_izin)->update([
            'status'     => 'Approved',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Insert ke tabel absensi untuk rentang tanggal tersebut
        $tglMulai   = Time::parse($izin['tanggal_mulai']);
        $tglSelesai = Time::parse($izin['tanggal_selesai']);
        $insertData = [];

        while ($tglMulai->toDateString() <= $tglSelesai->toDateString()) {
            $tanggalString = $tglMulai->toDateString();

            // Cek apakah sudah ada absen di tanggal ini, jika ada, hapus (override)
            $this->db->table('absensi')->where([
                'siswa_id' => $izin['siswa_id'],
                'tanggal'  => $tanggalString
            ])->delete();

            $insertData[] = [
                'siswa_id'    => $izin['siswa_id'],
                'tanggal'     => $tanggalString,
                'status'      => $izin['jenis'], // Sakit atau Izin
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

    // PERBAIKAN: Penambahan strict type-hinting 'string' pada parameter $id_izin
    public function reject(string $id_izin)
    {
        $this->db->table('pengajuan_izin')->where('id_izin', $id_izin)->update([
            'status'     => 'Rejected',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}
