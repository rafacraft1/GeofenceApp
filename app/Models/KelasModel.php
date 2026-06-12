<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table            = 'kelas';
    protected $primaryKey       = 'id_kelas';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = ['nama_kelas', 'wali_kelas_id'];
    protected $useTimestamps    = true;

    /**
     * Mengambil data kelas lengkap dengan nama wali kelasnya
     */
    public function getKelasWithWali()
    {
        return $this->select('kelas.*, users.nama_lengkap as nama_wali')
            ->join('users', 'users.id_user = kelas.wali_kelas_id', 'left')
            ->findAll();
    }
}
