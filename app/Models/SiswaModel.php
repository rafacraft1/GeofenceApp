<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    // Pastikan kelas_id masuk ke dalam allowedFields
    protected $allowedFields    = ['kelas_id', 'nis', 'nama_siswa', 'password', 'foto_profil', 'device_id'];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil data siswa beserta nama kelasnya
     */
    public function getSiswaWithKelas(string $id = null)
    {
        $this->select('siswa.*, kelas.nama_kelas');
        $this->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if ($id) {
            return $this->where('id_siswa', $id)->first();
        }

        return $this->findAll();
    }
}
