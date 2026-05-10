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
        $logFraud = $this->logFraudModel
            ->select('log_fraud.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy('log_fraud.created_at', 'DESC')
            ->findAll();

        $data = [
            'title'    => 'Log Keamanan & Pelanggaran',
            'logFraud' => $logFraud
        ];

        return view('web/fraud/index', $data);
    }
}
