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

    protected $allowedFields    = [
        'kelas_id',
        'nis',
        'nama_siswa',
        'password',
        'foto_profil',
        'device_id',
        'api_token',
        'fcm_token',
        'lat_terakhir',
        'long_terakhir',
        'last_login',
        'is_blocked',
        'fraud_count'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil data siswa beserta nama kelasnya
     */
    public function getSiswaWithKelas(?string $id = null)
    {
        $this->select('siswa.*, kelas.nama_kelas');
        $this->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if ($id) {
            return $this->where('id_siswa', $id)->first();
        }

        return $this->findAll();
    }

    /**
     * Menerapkan filter, pencarian, dan pagination secara native
     */
    public function getPaginatedSiswa(?string $kelasFilter, ?string $searchFilter, int $perPage)
    {
        $this->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->where('siswa.kelas_id', $kelasFilter);
        }

        if (!empty($searchFilter)) {
            $this->groupStart()
                ->like('siswa.nama_siswa', $searchFilter)
                ->orLike('siswa.nis', $searchFilter)
                ->groupEnd();
        }

        return $this->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->paginate($perPage, 'default');
    }
}
