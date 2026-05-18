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

    // ✅ Pendaftaran kolom identitas sistem
    protected $allowedFields    = [
        'nama_aplikasi',
        'nama_sekolah',
        'latitude_sekolah',
        'longitude_sekolah',
        'radius_meter',
        'firebase_url',
        'updated_at'
    ];

    // Karena di database hanya ada updated_at tanpa created_at
    protected $useTimestamps    = true;
    protected $createdField     = '';
    protected $updatedField     = 'updated_at';
}
