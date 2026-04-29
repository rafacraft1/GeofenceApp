<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class PengumumanApi extends ResourceController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil 10 pengumuman terbaru
        $data = $db->table('pengumuman')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResult();

        return $this->respond([
            'status'  => 'success',
            'message' => 'Data pengumuman berhasil diambil',
            'data'    => $data
        ], 200);
    }
}
