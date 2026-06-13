<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\LogFraudModel;

class LogFraud extends BaseController
{
    protected LogFraudModel $logFraudModel;

    public function __construct()
    {
        $this->logFraudModel = new LogFraudModel();
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $search = (string) $this->request->getGet('search');
        $date   = (string) $this->request->getGet('date');

        $sortParam = (string) ($this->request->getGet('sort') ?? 'created_at-desc');
        $sortParts = explode('-', $sortParam);
        $sortCol   = $sortParts[0] ?? 'created_at';
        $sortDir   = strtolower($sortParts[1] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $builder = $this->logFraudModel
            ->select('log_fraud.id_log, log_fraud.tipe_fraud, log_fraud.lat_fraud, log_fraud.long_fraud, log_fraud.user_agent, log_fraud.created_at, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (session()->get('is_wali_kelas')) {
            $builder->where('siswa.kelas_id', session()->get('kelas_id'));
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->groupEnd();
        }

        if (!empty($date)) {
            $builder->where('DATE(log_fraud.created_at)', $date);
        }

        $allowedSortColumns = [
            'created_at' => 'log_fraud.created_at',
            'nama_siswa' => 'siswa.nama_siswa',
            'tipe_fraud' => 'log_fraud.tipe_fraud'
        ];

        $orderColumn = $allowedSortColumns[$sortCol] ?? 'log_fraud.created_at';

        $perPage  = 20;
        $logFraud = $builder
            ->orderBy($orderColumn, $sortDir)
            ->paginate($perPage, 'default');

        $data = [
            'title'       => 'Log Keamanan & Pelanggaran',
            'logFraud'    => $logFraud,
            'search'      => $search,
            'date'        => $date,
            'sort_col'    => $sortCol,
            'sort_dir'    => strtolower($sortDir),
            'pager_links' => $this->logFraudModel->pager->links('default', 'tailwind_pagination'),
        ];

        return view('web/fraud/index', $data);
    }
}
