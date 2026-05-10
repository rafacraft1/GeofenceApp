<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\PengajuanIzinModel;
use App\Models\AbsensiModel;
use CodeIgniter\I18n\Time;

class Izin extends BaseController
{
    protected PengajuanIzinModel $izinModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->izinModel    = new PengajuanIzinModel();
        $this->absensiModel = new AbsensiModel();
    }

    public function index()
    {
        $daftarIzin = $this->izinModel
            ->select('pengajuan_izin.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy("FIELD(pengajuan_izin.status, 'Pending', 'Approved', 'Rejected')", '', false)
            ->orderBy('pengajuan_izin.created_at', 'DESC')
            ->findAll();

        $data = [
            'title'      => 'Manajemen Pengajuan Izin',
            'daftarIzin' => $daftarIzin
        ];

        return view('web/izin/index', $data);
    }

    public function approve(string $idIzin)
    {
        $izin = $this->izinModel->find($idIzin);

        if (!$izin || $izin['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Data tidak valid atau sudah diproses.');
        }

        // Mulai Transaksi Database Aman
        $this->izinModel->db->transStart();

        $this->izinModel->update($idIzin, ['status' => 'Approved']);

        $tglMulai   = Time::parse($izin['tanggal_mulai']);
        $tglSelesai = Time::parse($izin['tanggal_selesai']);
        $insertData = [];

        while ($tglMulai->toDateString() <= $tglSelesai->toDateString()) {
            $tanggalString = $tglMulai->toDateString();

            // Hapus data absen yang tumpang tindih menggunakan Model
            $this->absensiModel->where([
                'siswa_id' => $izin['siswa_id'],
                'tanggal'  => $tanggalString
            ])->delete();

            $insertData[] = [
                'siswa_id'   => $izin['siswa_id'],
                'tanggal'    => $tanggalString,
                'status'     => $izin['jenis'],
                'keterangan' => 'Disetujui via sistem: ' . $izin['alasan']
            ];

            $tglMulai = $tglMulai->addDays(1);
        }

        if (!empty($insertData)) {
            $this->absensiModel->insertBatch($insertData);
        }

        $this->izinModel->db->transComplete();

        if ($this->izinModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyetujui izin.');
        }

        return redirect()->back()->with('success', 'Pengajuan berhasil disetujui. Data absensi otomatis diperbarui.');
    }

    public function reject(string $idIzin)
    {
        $this->izinModel->update($idIzin, ['status' => 'Rejected']);
        return redirect()->back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}
