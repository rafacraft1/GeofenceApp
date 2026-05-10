<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use App\Models\KelasModel;
use CodeIgniter\I18n\Time;

class Absensi extends BaseController
{
    protected SiswaModel $siswaModel;
    protected AbsensiModel $absensiModel;
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->absensiModel = new AbsensiModel();
        $this->kelasModel   = new KelasModel();
    }

    public function index()
    {
        $tanggalFilter = $this->request->getGet('tanggal') ?? Time::now('Asia/Jakarta')->toDateString();
        $kelasFilter   = $this->request->getGet('kelas_id');

        $this->absensiModel->select('absensi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $tanggalFilter);

        if (!empty($kelasFilter)) {
            $this->absensiModel->where('siswa.kelas_id', $kelasFilter);
        }

        $absensi = $this->absensiModel->orderBy('absensi.jam_masuk', 'DESC')->findAll();

        $siswa = $this->siswaModel
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

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
        // Peningkatan Keamanan: Validasi input wajib dilakukan
        $aturanValidasi = [
            'siswa_id'   => 'required|numeric',
            'tanggal'    => 'required|valid_date[Y-m-d]',
            'status'     => 'required|in_list[Hadir,Sakit,Izin,Alpa]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Gagal memproses. Pastikan semua data diisi dengan format yang benar.');
        }

        $siswaId    = (int) $this->request->getPost('siswa_id');
        $tanggal    = (string) $this->request->getPost('tanggal');
        $status     = (string) $this->request->getPost('status');
        $keterangan = (string) $this->request->getPost('keterangan');

        $siswa = $this->siswaModel->find($siswaId);
        if (!$siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan.');
        }

        $waktuSekarang = Time::now('Asia/Jakarta')->toTimeString();
        $absenLama = $this->absensiModel->where(['siswa_id' => $siswaId, 'tanggal' => $tanggal])->first();

        $jamMasuk = ($status == 'Hadir') ? $waktuSekarang : null;

        if ($absenLama) {
            if ($status == 'Hadir' && !empty($absenLama['jam_masuk'])) {
                $jamMasuk = $absenLama['jam_masuk'];
            }

            $this->absensiModel->update($absenLama['id_absensi'], [
                'jam_masuk'  => $jamMasuk,
                'status'     => $status,
                'keterangan' => $keterangan
            ]);

            return redirect()->back()->with('success', 'Data absensi ' . $siswa['nama_siswa'] . ' berhasil diperbarui.');
        }

        $this->absensiModel->insert([
            'siswa_id'   => $siswaId,
            'tanggal'    => $tanggal,
            'jam_masuk'  => $jamMasuk,
            'status'     => $status,
            'keterangan' => $keterangan
        ]);

        return redirect()->back()->with('success', 'Berhasil mencatat status ' . $status . ' untuk ' . $siswa['nama_siswa'] . '.');
    }
}
