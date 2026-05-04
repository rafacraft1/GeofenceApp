<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table            = 'kelas';
    protected $primaryKey       = 'id_kelas';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    // Field yang diizinkan untuk diisi secara manual
    protected $allowedFields    = ['nama_kelas', 'wali_kelas'];

    // Mengaktifkan pengisian otomatis untuk created_at dan updated_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
