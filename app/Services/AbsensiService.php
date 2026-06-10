<?php

namespace App\Services;

use CodeIgniter\I18n\Time;

class AbsensiService
{
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        helper('geo');
    }

    /**
     * @param float $latSiswa
     * @param float $lonSiswa
     * @param int $isFakeGps
     * @return array
     */
    public function validasiGeofencing(float $latSiswa, float $lonSiswa, int $isFakeGps): array
    {
        if ($isFakeGps === 1) {
            return [
                'is_valid' => false,
                'status'   => 'Manipulasi',
                'message'  => 'Terdeteksi penggunaan aplikasi Fake GPS / Mock Location.'
            ];
        }

        $pengaturan = cache()->remember('koordinat_sekolah', 86400, function () {
            return $this->db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();
        });

        if (!$pengaturan) {
            return [
                'is_valid' => false,
                'status'   => 'Error',
                'message'  => 'Koordinat pusat sekolah belum dikonfigurasi.'
            ];
        }

        $latSekolah     = (float) $pengaturan['latitude_sekolah'];
        $lonSekolah     = (float) $pengaturan['longitude_sekolah'];
        $radiusMaksimal = (int) $pengaturan['radius_meter'];

        $jarakMeter = hitung_jarak_haversine($latSiswa, $lonSiswa, $latSekolah, $lonSekolah);

        if ($jarakMeter > $radiusMaksimal) {
            return [
                'is_valid' => false,
                'status'   => 'Manipulasi',
                'message'  => "Anda berada di luar area sekolah. Jarak Anda: {$jarakMeter} meter (Maks: {$radiusMaksimal}m)."
            ];
        }

        return [
            'is_valid'    => true,
            'status'      => 'Aman',
            'jarak_meter' => $jarakMeter
        ];
    }

    /**
     * @param Time $sekarang
     * @param string $jamMasuk
     * @param string $jamPulang
     * @return array
     */
    public function evaluasiWaktuMasuk(Time $sekarang, string $jamMasuk, string $jamPulang): array
    {
        $tanggalSekarang = $sekarang->toDateString();
        $waktuMasuk      = Time::parse($tanggalSekarang . ' ' . $jamMasuk, 'Asia/Jakarta');
        $waktuPulang     = Time::parse($tanggalSekarang . ' ' . $jamPulang, 'Asia/Jakarta');

        $batasAwalMasuk  = $waktuMasuk->subMinutes(30);
        $batasAkhirMasuk = $waktuPulang->subMinutes(30);

        if ($sekarang->isBefore($batasAwalMasuk)) {
            return ['status' => false, 'message' => 'Belum waktunya presensi masuk. Absen dibuka pukul ' . $batasAwalMasuk->toTimeString()];
        }

        if ($sekarang->isAfter($batasAkhirMasuk)) {
            return ['status' => false, 'message' => 'Batas waktu presensi masuk hari ini telah habis.'];
        }

        $menitTelat = 0;
        $isTelat    = false;

        if ($sekarang->isAfter($waktuMasuk)) {
            $menitTelat = abs($sekarang->difference($waktuMasuk)->getMinutes());
            $isTelat    = true;
        }

        return [
            'status'      => true,
            'is_telat'    => $isTelat,
            'menit_telat' => $menitTelat
        ];
    }

    /**
     * @param Time $sekarang
     * @param string $jamPulang
     * @return array
     */
    public function evaluasiWaktuPulang(Time $sekarang, string $jamPulang): array
    {
        $tanggalSekarang  = $sekarang->toDateString();
        $waktuPulang      = Time::parse($tanggalSekarang . ' ' . $jamPulang, 'Asia/Jakarta');
        $batasAkhirPulang = Time::parse($tanggalSekarang . ' 23:00:00', 'Asia/Jakarta');

        if ($sekarang->isBefore($waktuPulang)) {
            return ['status' => false, 'message' => 'Belum waktunya presensi pulang. Jadwal pulang pukul ' . $waktuPulang->toTimeString()];
        }

        if ($sekarang->isAfter($batasAkhirPulang)) {
            return ['status' => false, 'message' => 'Batas waktu presensi pulang (23:00) telah habis.'];
        }

        return ['status' => true];
    }
}
