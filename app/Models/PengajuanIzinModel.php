<?php

namespace App\Models;

use CodeIgniter\Model;

class PengajuanIzinModel extends Model
{
    protected $table            = 'pengajuan_izin';
    protected $primaryKey       = 'id_izin';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    // Membatasi kolom yang bisa diisi dari luar untuk keamanan
    protected $allowedFields    = [
        'siswa_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'bukti_foto',
        'status'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
