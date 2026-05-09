<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\BaseConnection;

class Absensi extends Controller
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $tanggal_filter = $this->request->getGet('tanggal') ?? Time::now('Asia/Jakarta')->toDateString();
        $kelas_filter   = $this->request->getGet('kelas_id');

        $builder = $this->db->table('absensi');
        $builder->select('absensi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas');
        $builder->join('siswa', 'siswa.id_siswa = absensi.siswa_id');
        $builder->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');
        $builder->where('absensi.tanggal', $tanggal_filter);

        if (!empty($kelas_filter)) {
            $builder->where('siswa.kelas_id', $kelas_filter);
        }

        $builder->orderBy('absensi.jam_masuk', 'DESC');
        $absensi = $builder->get()->getResultArray();

        $siswa = $this->db->table('siswa')
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->get()
            ->getResultArray();

        $list_kelas = $this->db->table('kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Data Absensi Harian',
            'tanggal'     => $tanggal_filter,
            'kelas_aktif' => $kelas_filter,
            'absensi'     => $absensi,
            'siswa'       => $siswa,
            'list_kelas'  => $list_kelas
        ];

        return view('web/absensi', $data);
    }

    public function input_manual()
    {
        $siswa_id   = $this->request->getPost('siswa_id');
        $tanggal    = $this->request->getPost('tanggal');
        $status     = $this->request->getPost('status');
        $keterangan = $this->request->getPost('keterangan'); // Telah ditangkap

        $siswa = $this->db->table('siswa')->where('id_siswa', $siswa_id)->get()->getRowArray();
        $waktu_sekarang = Time::now('Asia/Jakarta')->toTimeString();

        $absen_lama = $this->db->table('absensi')
            ->where('siswa_id', $siswa_id)
            ->where('tanggal', $tanggal)
            ->get()
            ->getRowArray();

        $jam_masuk = ($status == 'Hadir') ? $waktu_sekarang : null;

        if ($absen_lama) {
            if ($status == 'Hadir' && !empty($absen_lama['jam_masuk'])) {
                $jam_masuk = $absen_lama['jam_masuk'];
            }

            // PERBAIKAN: Menambahkan kolom keterangan pada fungsi update
            $this->db->table('absensi')->where('id_absensi', $absen_lama['id_absensi'])->update([
                'jam_masuk'  => $jam_masuk,
                'status'     => $status,
                'keterangan' => $keterangan,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Data absensi ' . $siswa['nama_siswa'] . ' berhasil diperbarui.');
        } else {
            // PERBAIKAN: Menambahkan kolom keterangan pada fungsi insert
            $this->db->table('absensi')->insert([
                'siswa_id'   => $siswa_id,
                'tanggal'    => $tanggal,
                'jam_masuk'  => $jam_masuk,
                'status'     => $status,
                'keterangan' => $keterangan,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Berhasil mencatat status ' . $status . ' untuk ' . $siswa['nama_siswa'] . '.');
        }
    }
}
