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

    /**
     * @param int $targetKelasId
     * @return bool
     */
    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === (int) session()->get('kelas_id');
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $sekarang      = Time::now('Asia/Jakarta');
        $tanggalFilter = (string) ($this->request->getGet('tanggal') ?? $sekarang->toDateString());
        $searchFilter  = (string) $this->request->getGet('search');

        $sortParam = (string) ($this->request->getGet('sort') ?? 'jam_masuk-desc');
        $sortParts = explode('-', $sortParam);
        $sortCol   = $sortParts[0] ?? 'jam_masuk';
        $sortDir   = $sortParts[1] ?? 'desc';

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        $isWaliKelas    = (bool) session()->get('is_wali_kelas');
        $kelasSessionId = (int) session()->get('kelas_id');

        $kelasFilterRaw = $isWaliKelas ? $kelasSessionId : $this->request->getGet('kelas_id');
        $kelasFilter    = !empty($kelasFilterRaw) ? (int)$kelasFilterRaw : null;

        $absensi = $this->absensiModel->getPaginatedAbsensiHarian($tanggalFilter, $kelasFilter, $searchFilter, $perPage, $sortCol, $sortDir);
        $pager   = $this->absensiModel->pager;

        // Ringkasan statistik kehadiran untuk tanggal & kelas yang difilter
        $summary = $this->absensiModel->getDailySummary($tanggalFilter, $kelasFilter);

        $cacheKeySiswa = 'list_siswa_dropdown_' . ($isWaliKelas ? $kelasSessionId : 'all');

        $siswaList = cache()->remember($cacheKeySiswa, 300, function () use ($isWaliKelas, $kelasSessionId) {
            $query = $this->siswaModel
                ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
                ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
                ->orderBy('siswa.nama_siswa', 'ASC');

            if ($isWaliKelas) {
                $query->where('siswa.kelas_id', $kelasSessionId);
            }
            return $query->findAll();
        });

        $listKelas = $isWaliKelas
            ? $this->kelasModel->where('id_kelas', $kelasSessionId)->findAll()
            : $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        $data = [
            'title'        => 'Data Absensi Harian',
            'tanggal'      => $tanggalFilter,
            'kelas_aktif'  => $kelasFilterRaw,
            'search_aktif' => $searchFilter,
            'sort_col'     => $sortCol,
            'sort_dir'     => $sortDir,
            'absensi'      => $absensi,
            'siswa'        => $siswaList,
            'list_kelas'   => $listKelas,
            'summary'      => $summary,
            'pager_links'  => $pager->links('default', 'tailwind_pagination'),
            'total_data'   => $pager->getTotal('default'),
            'page'         => $page,
            'perPage'      => $perPage
        ];

        return view('web/absensi', $data);
    }

    /**
     * @return mixed
     */
    public function inputManual()
    {
        $aturanValidasi = [
            'siswa_id'   => 'required|numeric',
            'tanggal'    => 'required|valid_date[Y-m-d]',
            'status'     => 'required|in_list[Hadir,Dispensasi,Sakit,Izin,Alpa]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Gagal memproses. Pastikan semua data diisi dengan format yang benar.');
        }

        $siswaId    = (int) $this->request->getPost('siswa_id');
        $tanggal    = (string) $this->request->getPost('tanggal');
        $status     = (string) $this->request->getPost('status');
        $keterangan = (string) $this->request->getPost('keterangan');

        $siswa = $this->siswaModel->find($siswaId);

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Siswa tidak ditemukan atau berada di luar otoritas Anda.');
        }

        $sekarangObject = Time::now('Asia/Jakarta');
        $tanggalHariIni = $sekarangObject->toDateString();

        // Hadir dan Dispensasi keduanya perlu jam_masuk
        $jamMasuk = null;
        if (in_array($status, ['Hadir', 'Dispensasi'])) {
            $jamMasuk = ($tanggal === $tanggalHariIni) ? $sekarangObject->toTimeString() : '07:00:00';
        }

        $absenLama = $this->absensiModel->where(['siswa_id' => $siswaId, 'tanggal' => $tanggal])->first();

        if ($absenLama) {
            if ($status === 'Hadir' && !empty($absenLama['jam_masuk'])) {
                $jamMasuk = $absenLama['jam_masuk'];
            }

            $this->absensiModel->update($absenLama['id_absensi'], [
                'kelas_id'   => $siswa['kelas_id'],
                'jam_masuk'  => $jamMasuk,
                'status'     => $status,
                'keterangan' => $keterangan
            ]);

            return redirect()->back()->with('success', 'Data absensi ' . $siswa['nama_siswa'] . ' berhasil diperbarui.');
        }

        $this->absensiModel->insert([
            'siswa_id'   => $siswaId,
            'kelas_id'   => $siswa['kelas_id'],
            'tanggal'    => $tanggal,
            'jam_masuk'  => $jamMasuk,
            'status'     => $status,
            'keterangan' => $keterangan
        ]);

        return redirect()->back()->with('success', 'Berhasil mencatat status ' . $status . ' untuk ' . $siswa['nama_siswa'] . '.');
    }
}
