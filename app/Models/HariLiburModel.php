<?php

namespace App\Models;

use CodeIgniter\Model;

class HariLiburModel extends Model
{
    protected $table            = 'hari_libur';
    protected $primaryKey       = 'id_libur';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Kolom yang diizinkan untuk diisi secara manual
    protected $allowedFields    = ['tanggal', 'keterangan', 'created_at'];

    // Mengaktifkan fitur auto timestamp untuk created_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';

    // Dikosongkan karena kita tidak membuat kolom updated_at untuk tabel ini
    protected $updatedField     = '';
}
