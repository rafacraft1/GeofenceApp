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

        $perPage  = 20;
        $logFraud = $builder
            ->orderBy('log_fraud.created_at', 'DESC')
            ->paginate($perPage, 'default');

        $data = [
            'title'       => 'Log Keamanan & Pelanggaran',
            'logFraud'    => $logFraud,
            'search'      => $search,
            'date'        => $date,
            'pager_links' => $this->logFraudModel->pager->links('default', 'tailwind_pagination'),
        ];

        return view('web/fraud/index', $data);
    }
}
