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

    /**
     * PRIVATE HELPER: Memastikan keamanan akses Row-Level Security
     */
    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === session()->get('kelas_id');
        }
        return true;
    }

    public function index()
    {
        // Tangkap Parameter Filter & Search
        $search       = trim((string) $this->request->getGet('search'));
        $statusFilter = $this->request->getGet('status');

        $this->izinModel
            ->select('pengajuan_izin.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (session()->get('is_wali_kelas')) {
            $this->izinModel->where('siswa.kelas_id', session()->get('kelas_id'));
        }

        if (!empty($statusFilter)) {
            $this->izinModel->where('pengajuan_izin.status', $statusFilter);
        }

        if (!empty($search)) {
            $this->izinModel->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->groupEnd();
        }

        // UX: Jika tidak ada filter status, prioritaskan yang Pending agar segera diproses
        if (empty($statusFilter)) {
            $this->izinModel->orderBy("FIELD(pengajuan_izin.status, 'Pending', 'Approved', 'Rejected')", '', false);
        }

        $this->izinModel->orderBy('pengajuan_izin.created_at', 'DESC');

        // Server-Side Pagination
        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page_izin') ?? 1);
        $perPage = 15;

        $totalData  = $this->izinModel->countAllResults(false);
        $daftarIzin = $this->izinModel->paginate($perPage, 'izin');
        $pagerLinks = $this->izinModel->pager->makeLinks($page, $perPage, $totalData, 'default_full', 0, 'izin');

        $data = [
            'title'      => 'Persetujuan Izin & Sakit',
            'daftarIzin' => $daftarIzin,
            'search'     => $search,
            'status'     => $statusFilter,
            'pager_links' => $pagerLinks,
            'page'       => $page,
            'perPage'    => $perPage,
            'total_data' => $totalData,
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

        // Loop untuk memasukkan data absensi di setiap hari yang diajukan
        while ($tglMulai->toDateString() <= $tglSelesai->toDateString()) {
            $tanggalString = $tglMulai->toDateString();

            // Hapus absensi existing di tanggal tersebut jika ada (timpa data lama)
            $this->absensiModel->where([
                'siswa_id' => $izin['siswa_id'],
                'tanggal'  => $tanggalString
            ])->delete();

            $insertData[] = [
                'siswa_id'   => $izin['siswa_id'],
                'kelas_id'   => $izin['kelas_id'], // Injeksi snapshot historis
                'tanggal'    => $tanggalString,
                'status'     => $izin['jenis'], // Izin / Sakit
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
