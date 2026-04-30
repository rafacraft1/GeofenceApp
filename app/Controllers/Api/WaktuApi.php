<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class WaktuApi extends ResourceController
{
    public function index()
    {
        $sekarang = Time::now('Asia/Jakarta');
        return $this->respond([
            'status' => 'success',
            'waktu'  => $sekarang->toDateTimeString() // Format: YYYY-MM-DD HH:MM:SS
        ], 200);
    }
}
