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

    // Memastikan seluruh kolom yang bisa diupdate terdaftar di sini
    protected $allowedFields    = [
        'kelas_id',
        'nis',
        'nama_siswa',
        'password',
        'foto_profil',
        'device_id',
        'api_token',
        'fcm_token',
        'lat_terakhir',   // <-- TAMBAHAN UNTUK LIVE TRACKING
        'long_terakhir',  // <-- TAMBAHAN UNTUK LIVE TRACKING
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
}
