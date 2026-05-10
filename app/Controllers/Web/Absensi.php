<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use CodeIgniter\I18n\Time;

class Absensi extends BaseController
{
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    public function index()
    {
        $tanggalFilter = $this->request->getGet('tanggal') ?? Time::now('Asia/Jakarta')->toDateString();
        $kelasFilter   = $this->request->getGet('kelas_id');

        // Builder untuk tabel absensi tetap dipertahankan
        $builder = $this->db->table('absensi');
        $builder->select('absensi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas');
        $builder->join('siswa', 'siswa.id_siswa = absensi.siswa_id');
        $builder->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');
        $builder->where('absensi.tanggal', $tanggalFilter);

        if (!empty($kelasFilter)) {
            $builder->where('siswa.kelas_id', $kelasFilter);
        }

        $builder->orderBy('absensi.jam_masuk', 'DESC');
        $absensi = $builder->get()->getResultArray();

        // Refactor: Menggunakan SiswaModel
        $siswa = $this->siswaModel
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        $listKelas = $this->db->table('kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Data Absensi Harian',
            'tanggal'     => $tanggalFilter,
            'kelas_aktif' => $kelasFilter,
            'absensi'     => $absensi,
            'siswa'       => $siswa,
            'list_kelas'  => $listKelas
        ];

        return view('web/absensi', $data);
    }

    public function inputManual()
    {
        $siswaId    = $this->request->getPost('siswa_id');
        $tanggal    = $this->request->getPost('tanggal');
        $status     = $this->request->getPost('status');
        $keterangan = $this->request->getPost('keterangan');

        // Refactor: Menggunakan find() pada SiswaModel
        $siswa = $this->siswaModel->find($siswaId);
        $waktuSekarang = Time::now('Asia/Jakarta')->toTimeString();

        $absenLama = $this->db->table('absensi')
            ->where('siswa_id', $siswaId)
            ->where('tanggal', $tanggal)
            ->get()
            ->getRowArray();

        $jamMasuk = ($status == 'Hadir') ? $waktuSekarang : null;

        if ($absenLama) {
            if ($status == 'Hadir' && !empty($absenLama['jam_masuk'])) {
                $jamMasuk = $absenLama['jam_masuk'];
            }

            $this->db->table('absensi')->where('id_absensi', $absenLama['id_absensi'])->update([
                'jam_masuk'  => $jamMasuk,
                'status'     => $status,
                'keterangan' => $keterangan,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Data absensi ' . $siswa['nama_siswa'] . ' berhasil diperbarui.');
        } else {
            $this->db->table('absensi')->insert([
                'siswa_id'   => $siswaId,
                'tanggal'    => $tanggal,
                'jam_masuk'  => $jamMasuk,
                'status'     => $status,
                'keterangan' => $keterangan,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Berhasil mencatat status ' . $status . ' untuk ' . $siswa['nama_siswa'] . '.');
        }
    }
}
