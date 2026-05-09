<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class PengumumanApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $pengumuman = $this->db->table('pengumuman')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->respond([
            'status'  => 200,
            'message' => 'Data pengumuman berhasil ditarik.',
            'data'    => $pengumuman
        ]);
    }
}
