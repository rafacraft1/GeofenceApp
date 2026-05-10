<?php

namespace App\Services;

class AbsensiService
{
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        helper('geo'); // Memanggil geo_helper.php yang sudah kita verifikasi
    }

    /**
     * Memvalidasi keaslian lokasi dan mendeteksi Fraud
     */
    public function validasiGeofencing(float $latSiswa, float $lonSiswa, int $isFakeGps): array
    {
        // 1. Deteksi Mock Location dari Android
        if ($isFakeGps === 1) {
            return [
                'is_valid' => false,
                'status'   => 'Manipulasi',
                'message'  => 'Terdeteksi penggunaan aplikasi Fake GPS / Mock Location.'
            ];
        }

        // 2. Ambil titik pusat sekolah & radius dari tabel pengaturan
        $pengaturan = $this->db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        if (!$pengaturan) {
            return [
                'is_valid' => false,
                'status'   => 'Error',
                'message'  => 'Koordinat pusat sekolah belum dikonfigurasi oleh Admin.'
            ];
        }

        $latSekolah = (float) $pengaturan['latitude_sekolah'];
        $lonSekolah = (float) $pengaturan['longitude_sekolah'];
        $radiusMaksimal = (int) $pengaturan['radius_meter'];

        // 3. Eksekusi Rumus Haversine murni dari Helper
        $jarakMeter = hitung_jarak_haversine($latSiswa, $lonSiswa, $latSekolah, $lonSekolah);

        // 4. Deteksi jarak (Apakah di luar jangkauan pagar gaib/geofence?)
        if ($jarakMeter > $radiusMaksimal) {
            return [
                'is_valid' => false,
                'status'   => 'Manipulasi',
                'message'  => "Anda berada di luar area sekolah. Jarak Anda: {$jarakMeter} meter (Maks: {$radiusMaksimal}m)."
            ];
        }

        // Jika lolos semua validasi
        return [
            'is_valid'    => true,
            'status'      => 'Aman',
            'jarak_meter' => $jarakMeter
        ];
    }
}
