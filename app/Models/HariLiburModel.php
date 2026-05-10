<?php

namespace App\Models;

use CodeIgniter\Model;

class HariLiburModel extends Model
{
    protected $table            = 'hari_libur';
    protected $primaryKey       = 'id_libur';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['tanggal', 'keterangan'];

    // PERBAIKAN BUG: Matikan auto-timestamps agar CI4 tidak memaksa mencari kolom 'updated_at'
    protected $useTimestamps    = false;
}
