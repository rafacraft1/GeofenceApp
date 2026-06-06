<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\LogFraudModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;

class LogFraud extends BaseController
{
    protected LogFraudModel $logFraudModel;
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->logFraudModel = new LogFraudModel();
        $this->siswaModel    = new SiswaModel();
        $this->kelasModel    = new KelasModel();
    }

    public function index()
    {
        // 1. Tangkap Parameter Filter & Search
        $search      = trim((string) $this->request->getGet('search'));
        $tipeFilter  = $this->request->getGet('tipe_fraud');

        $isWaliKelas    = session()->get('is_wali_kelas');
        $kelasSessionId = session()->get('kelas_id');
        $kelasFilter    = $isWaliKelas ? $kelasSessionId : $this->request->getGet('kelas_id');

        // 2. Tangkap Parameter Sorting
        $sort = strtolower(trim((string) $this->request->getGet('sort')));
        $dir  = strtoupper(trim((string) $this->request->getGet('dir')));
        $dir  = in_array($dir, ['ASC', 'DESC']) ? $dir : 'DESC';

        $allowedSorts = [
            'waktu'      => 'log_fraud.created_at',
            'nama_siswa' => 'siswa.nama_siswa',
            'tipe'       => 'log_fraud.tipe_fraud'
        ];
        $sortColumn = $allowedSorts[$sort] ?? 'log_fraud.created_at';

        // 3. Setup Pagination
        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page_fraud') ?? 1);
        $perPage = 15;

        // 4. Bangun Query Utama
        $this->logFraudModel->select('log_fraud.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->logFraudModel->where('siswa.kelas_id', $kelasFilter);
        }

        if (!empty($tipeFilter)) {
            $this->logFraudModel->where('log_fraud.tipe_fraud', $tipeFilter);
        }

        if (!empty($search)) {
            $this->logFraudModel->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->groupEnd();
        }

        // 5. Eksekusi Query dengan Server-Side Pagination
        $totalData = $this->logFraudModel->countAllResults(false);
        $logData   = $this->logFraudModel->orderBy($sortColumn, $dir)
            ->paginate($perPage, 'fraud');

        $pagerLinks = $this->logFraudModel->pager->makeLinks($page, $perPage, $totalData, 'default_full', 0, 'fraud');

        // Ambil Data Kelas untuk Dropdown Filter (Jika bukan Wali Kelas)
        $listKelas = [];
        if (!$isWaliKelas) {
            $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        }

        // Ambil Unique Tipe Fraud untuk Dropdown Dinamis
        $listTipe = $this->logFraudModel->select('tipe_fraud')->distinct()->findAll();

        $data = [
            'title'       => 'Log Fraud & Keamanan',
            'log_data'    => $logData,
            'search'      => $search,
            'kelas_aktif' => $kelasFilter,
            'tipe_aktif'  => $tipeFilter,
            'list_kelas'  => $listKelas,
            'list_tipe'   => $listTipe,
            'pager_links' => $pagerLinks,
            'page'        => $page,
            'perPage'     => $perPage,
            'total_data'  => $totalData,
            'is_wali_kelas' => $isWaliKelas
        ];

        return view('web/log_fraud', $data);
    }
}
