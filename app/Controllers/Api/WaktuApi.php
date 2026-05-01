<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class WaktuApi extends ResourceController
{
    public function index()
    {
        $sekarang = Time::now('Asia/Jakarta');

        $db = \Config\Database::connect();
        $pengaturan = $db->table('pengaturan')->where('id', 1)->get()->getRow();

        return $this->respond([
            'status'      => 'success',
            'waktu'       => $sekarang->toDateTimeString(),
            'jam_masuk'   => $pengaturan ? $pengaturan->jam_masuk : '07:00:00',
            'jam_pulang'  => $pengaturan ? $pengaturan->jam_pulang : '15:00:00',
            // --- TAMBAHAN BARU: Kirim koordinat dan radius sekolah ke Flutter ---
            'lat_sekolah' => $pengaturan ? $pengaturan->latitude_sekolah : 0,
            'lon_sekolah' => $pengaturan ? $pengaturan->longitude_sekolah : 0,
            'radius'      => $pengaturan ? $pengaturan->radius_meter : 50,
        ], 200);
    }
}
