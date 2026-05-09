<?php

namespace App\Models;

use CodeIgniter\Model;

class HariLiburModel extends Model
{
    protected $table            = 'hari_libur';
    protected $primaryKey       = 'id_libur';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // PERBAIKAN: created_at dihapus dari allowedFields karena sudah ditangani otomatis oleh CI4
    protected $allowedFields    = ['tanggal', 'keterangan'];

    // Mengaktifkan fitur auto timestamp untuk created_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';

    // Dikosongkan karena kita tidak membuat kolom updated_at untuk tabel ini
    protected $updatedField     = '';
}
