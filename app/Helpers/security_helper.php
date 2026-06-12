<?php

if (!function_exists('deteksi_anomali_kecepatan')) {
    function deteksi_anomali_kecepatan(float $jarak_meter, float $selisih_waktu_detik): bool
    {
        if ($selisih_waktu_detik <= 0) return true;

        $kecepatan_ms = $jarak_meter / $selisih_waktu_detik;
        $kecepatan_kmh = $kecepatan_ms * 3.6;

        return $kecepatan_kmh > 120.0;
    }
}
