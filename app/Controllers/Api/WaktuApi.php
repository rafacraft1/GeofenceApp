<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class WaktuApi extends ResourceController
{
    public function index()
    {
        // Mengambil timezone dinamis dari file .env
        $timezone = env('app.appTimezone', 'Asia/Jakarta');

        $sekarang = Time::now($timezone);
        $tanggalSekarang = $sekarang->toDateString();
        $kodeHari = $sekarang->format('N');

        $db = \Config\Database::connect();

        $pengaturan = $db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        $isLibur = false;
        $namaLibur = '';
        $jamMasuk = '00:00:00';
        $jamPulang = '00:00:00';

        $cekLibur = $db->table('hari_libur')->where('tanggal', $tanggalSekarang)->get()->getRowArray();

        if ($cekLibur) {
            $isLibur = true;
            $namaLibur = $cekLibur['keterangan'];
        } else {
            $jadwal = $db->table('jadwal_absen')->where('kode_hari', $kodeHari)->get()->getRowArray();

            if ($jadwal) {
                if ($jadwal['is_libur'] == 1) {
                    $isLibur = true;
                    $namaLibur = 'Libur Akhir Pekan (' . $jadwal['nama_hari'] . ')';
                } else {
                    $jamMasuk = $jadwal['jam_masuk'];
                    $jamPulang = $jadwal['jam_pulang'];
                }
            }
        }

        return $this->respond([
            'status'      => 'success',
            'waktu'       => $sekarang->toDateTimeString(),
            'is_libur'    => $isLibur,
            'nama_libur'  => $namaLibur,
            'jam_masuk'   => $jamMasuk,
            'jam_pulang'  => $jamPulang,
            'lat_sekolah' => $pengaturan ? $pengaturan['latitude_sekolah'] : 0,
            'lon_sekolah' => $pengaturan ? $pengaturan['longitude_sekolah'] : 0,
            'radius'      => $pengaturan ? $pengaturan['radius_meter'] : 50,
        ], 200);
    }
}
