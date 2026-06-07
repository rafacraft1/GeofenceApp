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

    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === session()->get('kelas_id');
        }
        return true;
    }

    public function index()
    {
        // 1. Tangkap Parameter Request
        $searchFilter = $this->request->getGet('search');

        // Logika Sort (Default: Terbaru & Pending di atas)
        $sortParam = $this->request->getGet('sort') ?? 'created_at-desc';
        $sortParts = explode('-', $sortParam);
        $sortCol   = $sortParts[0] ?? 'created_at';
        $sortDir   = $sortParts[1] ?? 'desc';

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        // Proteksi Wali Kelas
        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasFilter = $isWaliKelas ? (int) session()->get('kelas_id') : null;

        // 2. Tarik Data dengan Pagination & Sort dari Model
        $daftarIzin = $this->izinModel->getPaginatedIzin($kelasFilter, $searchFilter, $perPage, $sortCol, $sortDir);
        $pager      = $this->izinModel->pager;

        $data = [
            'title'        => 'Manajemen Pengajuan Izin',
            'search_aktif' => $searchFilter,
            'sort_col'     => $sortCol,
            'sort_dir'     => $sortDir,
            'daftarIzin'   => $daftarIzin,

            // Variabel Pagination
            'pager_links'  => $pager->links('default', 'tailwind_pagination'),
            'total_data'   => $pager->getTotal('default'),
            'page'         => $page,
            'perPage'      => $perPage
        ];

        return view('web/izin/index', $data);
    }

    public function approve(string $idIzin)
    {
        $izin = $this->izinModel
            ->select('pengajuan_izin.*, siswa.kelas_id')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->where('id_izin', $idIzin)
            ->first();

        if (!$izin || $izin['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Data tidak valid atau sudah diproses.');
        }

        if (!$this->checkAksesWaliKelas((int)$izin['kelas_id'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak berhak memproses izin siswa dari kelas lain.');
        }

        $this->izinModel->db->transStart();

        $this->izinModel->update($idIzin, ['status' => 'Approved']);

        $tglMulai   = Time::parse($izin['tanggal_mulai']);
        $tglSelesai = Time::parse($izin['tanggal_selesai']);
        $insertData = [];

        while ($tglMulai->toDateString() <= $tglSelesai->toDateString()) {
            $tanggalString = $tglMulai->toDateString();

            $this->absensiModel->where([
                'siswa_id' => $izin['siswa_id'],
                'tanggal'  => $tanggalString
            ])->delete();

            $insertData[] = [
                'siswa_id'   => $izin['siswa_id'],
                'kelas_id'   => $izin['kelas_id'],
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
        $izin = $this->izinModel
            ->select('pengajuan_izin.*, siswa.kelas_id')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->where('id_izin', $idIzin)
            ->first();

        if (!$izin || $izin['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Data tidak valid atau sudah diproses.');
        }

        if (!$this->checkAksesWaliKelas((int)$izin['kelas_id'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak berhak menolak izin siswa dari kelas lain.');
        }

        $this->izinModel->update($idIzin, ['status' => 'Rejected']);
        return redirect()->back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}
