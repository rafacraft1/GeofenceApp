<?php

namespace App\Models;

use CodeIgniter\Model;

class ZonaModel extends Model
{
    protected $table            = 'zona_absensi';
    protected $primaryKey       = 'id_zona';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'nama_zona',
        'latitude',
        'longitude',
        'radius',
        'is_default'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getDefaultZona(): ?array
    {
        return $this->where('is_default', 1)->first();
    }
}
