<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PengumumanModel;

class PengumumanApi extends ResourceController
{
    protected $format = 'json';
    protected PengumumanModel $pengumumanModel;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
    }

    public function index()
    {
        $pengumuman = $this->pengumumanModel
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data pengumuman berhasil ditarik.',
            'data'    => $pengumuman
        ]);
    }
}
