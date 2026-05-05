<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalAbsenModel extends Model
{
    protected $table            = 'jadwal_absen';
    protected $primaryKey       = 'id_jadwal';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Hanya kolom ini yang boleh diubah Admin (kode_hari dan nama_hari tetap statis)
    protected $allowedFields    = ['jam_masuk', 'jam_pulang', 'is_libur'];
}
