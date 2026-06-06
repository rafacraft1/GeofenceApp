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

    /* * PERHATIAN: Fungsi private checkAksesWaliKelas() TELAH DIHAPUS.
     * Logika tersebut kini otomatis diwariskan (inherited) dari BaseController.
     */

    public function index()
    {
        $tanggalFilter = $this->request->getGet('tanggal') ?? Time::now('Asia/Jakarta')->toDateString();
        $search        = trim((string) $this->request->getGet('search'));

        $isWaliKelas    = session()->get('is_wali_kelas');
        $kelasSessionId = session()->get('kelas_id');
        $kelasFilter    = $isWaliKelas ? $kelasSessionId : $this->request->getGet('kelas_id');

        $sort = strtolower(trim((string) $this->request->getGet('sort')));
        $dir  = strtoupper(trim((string) $this->request->getGet('dir')));
        $dir  = in_array($dir, ['ASC', 'DESC']) ? $dir : 'DESC';

        $allowedSorts = [
            'nama_siswa' => 'siswa.nama_siswa',
            'waktu'      => 'absensi.jam_masuk',
            'status'     => 'absensi.status'
        ];
        $sortColumn = $allowedSorts[$sort] ?? 'absensi.jam_masuk';

        // MENGGUNAKAN FUNGSI GLOBAL DARI BASE CONTROLLER
        $pg = $this->setupPagination('page_absensi', 15);

        $this->absensiModel->select('absensi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $tanggalFilter);

        if (!empty($kelasFilter)) {
            $this->absensiModel->where('siswa.kelas_id', $kelasFilter);
        }

        if (!empty($search)) {
            $this->absensiModel->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->groupEnd();
        }

        $totalData = $this->absensiModel->countAllResults(false);
        $absensi   = $this->absensiModel->orderBy($sortColumn, $dir)->paginate($pg['perPage'], 'absensi');

        $siswaQuery = $this->siswaModel
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy('siswa.nama_siswa', 'ASC');

        if ($isWaliKelas) {
            $siswaQuery->where('siswa.kelas_id', $kelasSessionId);
            $listKelas = $this->kelasModel->where('id_kelas', $kelasSessionId)->findAll();
        } else {
            $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        }

        $data = [
            'title'         => 'Data Absensi Harian',
            'tanggal'       => $tanggalFilter,
            'kelas_aktif'   => $kelasFilter,
            'search'        => $search,
            'absensi'       => $absensi,
            'siswa'         => $siswaQuery->findAll(),
            'list_kelas'    => $listKelas,
            'pager_links'   => $this->absensiModel->pager->makeLinks($pg['page'], $pg['perPage'], $totalData, 'default_full', 0, 'absensi'),
            'page'          => $pg['page'],
            'perPage'       => $pg['perPage'],
            'total_data'    => $totalData,
            'is_wali_kelas' => $isWaliKelas
        ];

        return view('web/absensi', $data);
    }

    public function inputManual()
    {
        $aturanValidasi = [
            'siswa_id' => 'required|numeric',
            'tanggal'  => 'required|valid_date[Y-m-d]',
            'status'   => 'required|in_list[Hadir,Sakit,Izin,Alpa]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->with('error', 'Gagal memproses. Pastikan semua data diisi dengan format yang benar.');
        }

        $siswaId    = (int) $this->request->getPost('siswa_id');
        $tanggal    = (string) $this->request->getPost('tanggal');
        $status     = (string) $this->request->getPost('status');
        $keterangan = (string) $this->request->getPost('keterangan');

        $siswa = $this->siswaModel->find($siswaId);

        // Menggunakan checkAksesWaliKelas dari BaseController
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Siswa tidak ditemukan atau berada di luar otoritas Anda.');
        }

        $waktuSekarang = Time::now('Asia/Jakarta')->toTimeString();
        $absenLama = $this->absensiModel->where(['siswa_id' => $siswaId, 'tanggal' => $tanggal])->first();

        $jamMasuk = ($status == 'Hadir') ? $waktuSekarang : null;

        if ($absenLama) {
            if ($status == 'Hadir' && !empty($absenLama['jam_masuk'])) {
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
