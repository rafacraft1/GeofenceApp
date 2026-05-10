<?php

namespace App\Models;

use CodeIgniter\Model;

class LogFraudModel extends Model
{
    protected $table            = 'log_fraud';
    protected $primaryKey       = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['siswa_id', 'tipe_fraud', 'lat_fraud', 'long_fraud', 'user_agent'];

    // Tabel log_fraud di migrasi hanya memiliki created_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
}
