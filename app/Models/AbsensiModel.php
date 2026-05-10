<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table            = 'absensi';
    protected $primaryKey       = 'id_absensi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'siswa_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'foto_masuk',
        'foto_pulang',
        'lat_masuk',
        'long_masuk',
        'lat_pulang',
        'long_pulang',
        'is_fake_gps',
        'menit_telat',
        'keterangan'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
