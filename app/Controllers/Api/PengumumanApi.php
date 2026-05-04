<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class PengumumanApi extends ResourceController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('pengumuman')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->respond([
            'status'  => 'success',
            'message' => 'Data pengumuman berhasil diambil',
            'data'    => $data
        ], 200);
    }
}
