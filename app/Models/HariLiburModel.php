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

    protected $useTimestamps    = false;
}
