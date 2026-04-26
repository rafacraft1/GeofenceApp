<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;

class Absensi extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $tanggal_filter = $this->request->getGet('tanggal') ?? Time::now('Asia/Jakarta')->toDateString();

        $builder = $this->db->table('absensi');
        $builder->select('absensi.*, siswa.nama_lengkap, siswa.nis, siswa.kelas');
        $builder->join('siswa', 'siswa.id = absensi.siswa_id');
        $builder->where('absensi.tanggal', $tanggal_filter);
        $builder->orderBy('absensi.waktu_masuk', 'DESC');
        $absensi = $builder->get()->getResult();

        $siswa = $this->db->table('siswa')
            ->select('id, nis, nama_lengkap, kelas')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title'   => 'Data Absensi Harian',
            'tanggal' => $tanggal_filter,
            'absensi' => $absensi,
            'siswa'   => $siswa
        ];

        return view('web/absensi', $data);
    }

    public function input_manual()
    {
        $siswa_id   = $this->request->getPost('siswa_id');
        $tanggal    = $this->request->getPost('tanggal');
        $status     = $this->request->getPost('status');
        $keterangan = $this->request->getPost('keterangan');

        $siswa = $this->db->table('siswa')->where('id', $siswa_id)->get()->getRow();
        $waktu_sekarang = Time::now('Asia/Jakarta')->toTimeString();

        // 1. Cek apakah catatan absensi untuk siswa ini di tanggal tersebut sudah ada
        $absen_lama = $this->db->table('absensi')
            ->where('siswa_id', $siswa_id)
            ->where('tanggal', $tanggal)
            ->get()
            ->getRow();

        // Tentukan waktu masuk bawaan (Hanya diisi jika status Hadir)
        $waktu_masuk = ($status == 'Hadir') ? $waktu_sekarang : null;

        if ($absen_lama) {
            // ==========================================
            // LOGIKA UPDATE (MERALAT DATA YANG ADA)
            // ==========================================

            // Pengaman: Jika diralat menjadi "Hadir", tapi siswa sebelumnya sudah punya jam masuk asli, 
            // maka pertahankan jam masuk aslinya agar tidak tertimpa jam saat admin menekan tombol edit.
            if ($status == 'Hadir' && $absen_lama->waktu_masuk != null) {
                $waktu_masuk = $absen_lama->waktu_masuk;
            }

            $this->db->table('absensi')->where('id', $absen_lama->id)->update([
                'waktu_masuk' => $waktu_masuk,
                'status'      => $status,
                'keterangan'  => 'Diralat Admin: ' . $keterangan,
                'updated_at'  => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Data absensi ' . $siswa->nama_lengkap . ' berhasil DIRALAT menjadi ' . $status . '.');
        } else {
            // ==========================================
            // LOGIKA INSERT (INPUT DATA BARU)
            // ==========================================
            $this->db->table('absensi')->insert([
                'siswa_id'    => $siswa_id,
                'tanggal'     => $tanggal,
                'waktu_masuk' => $waktu_masuk,
                'status'      => $status,
                'keterangan'  => 'Manual Admin: ' . $keterangan,
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Berhasil mencatat status ' . $status . ' untuk ' . $siswa->nama_lengkap . '.');
        }
    }
}
