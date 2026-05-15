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

        // Map data untuk melengkapi URL Lampiran dan Flag PDF
        foreach ($pengumuman as &$p) {
            if (!empty($p['gambar'])) {
                // Berikan URL lengkap agar Android/Flutter bisa langsung merender file
                $p['file_url'] = base_url('uploads/pengumuman/' . $p['gambar']);
                // Beri flag penanda agar frontend tahu ini PDF atau Gambar
                $p['is_pdf']   = (strtolower(pathinfo((string)$p['gambar'], PATHINFO_EXTENSION)) === 'pdf');
            } else {
                $p['file_url'] = null;
                $p['is_pdf']   = false;
            }
        }

        return $this->respond([
            'status'  => 200,
            'message' => 'Data pengumuman berhasil ditarik.',
            'data'    => $pengumuman
        ]);
    }
}
