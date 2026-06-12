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

    public function index()
    {
        $this->logFraudModel
            ->select('log_fraud.id_log, log_fraud.tipe_fraud, log_fraud.lat_fraud, log_fraud.long_fraud, log_fraud.user_agent, log_fraud.created_at, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (session()->get('is_wali_kelas')) {
            $this->logFraudModel->where('siswa.kelas_id', session()->get('kelas_id'));
        }

        $perPage  = 20;
        $logFraud = $this->logFraudModel
            ->orderBy('log_fraud.created_at', 'DESC')
            ->paginate($perPage, 'default');

        $data = [
            'title'       => 'Log Keamanan & Pelanggaran',
            'logFraud'    => $logFraud,
            'pager_links' => $this->logFraudModel->pager->links('default', 'tailwind_pagination'),
        ];

        return view('web/fraud/index', $data);
    }
}
