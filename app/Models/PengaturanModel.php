<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanModel extends Model
{
    protected $table            = 'pengaturan';
    protected $primaryKey       = 'id_pengaturan';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'latitude_sekolah',
        'longitude_sekolah',
        'radius_meter'
    ];
}
