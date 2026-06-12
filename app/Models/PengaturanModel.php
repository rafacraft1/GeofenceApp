<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanModel extends Model
{
    protected $table            = 'pengaturan';
    protected $primaryKey       = 'id_pengaturan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'nama_aplikasi',
        'nama_sekolah',
        'firebase_url',
        'updated_at'
    ];
}
