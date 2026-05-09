<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class LogFraud extends BaseController
{
    public function index()
    {
        $logFraud = $this->db->table('log_fraud')
            ->select('log_fraud.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->orderBy('log_fraud.created_at', 'DESC')
            ->get()->getResultArray();

        $data = [
            'title'    => 'Log Keamanan & Pelanggaran',
            'logFraud' => $logFraud
        ];

        return view('web/fraud/index', $data);
    }
}
