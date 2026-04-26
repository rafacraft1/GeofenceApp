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

        // 1. Validasi Duplikat
        $cek_absen = $this->db->table('absensi')
            ->where('siswa_id', $siswa_id)
            ->where('tanggal', $tanggal)
            ->countAllResults();

        if ($cek_absen > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Siswa tersebut sudah memiliki catatan absensi pada tanggal ini.');
        }

        $siswa = $this->db->table('siswa')->where('id', $siswa_id)->get()->getRow();

        // 2. Simpan Data (Waktu masuk hanya diisi jika status Hadir)
        $this->db->table('absensi')->insert([
            'siswa_id'    => $siswa_id,
            'tanggal'     => $tanggal,
            'waktu_masuk' => ($status == 'Hadir') ? Time::now('Asia/Jakarta')->toTimeString() : null,
            'status'      => $status,
            'keterangan'  => 'Manual Admin: ' . $keterangan,
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Berhasil mencatat status ' . $status . ' untuk ' . $siswa->nama_lengkap);
    }
}
