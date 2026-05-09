<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalAbsenModel extends Model
{
    protected $table            = 'jadwal_absen';
    protected $primaryKey       = 'id_jadwal';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // PERBAIKAN: Menambahkan kode_hari dan nama_hari sebagai standar jaring pengaman model
    protected $allowedFields    = ['kode_hari', 'nama_hari', 'jam_masuk', 'jam_pulang', 'is_libur'];
}
