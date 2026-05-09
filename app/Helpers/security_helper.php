<?php

if (!function_exists('deteksi_anomali_kecepatan')) {
    function deteksi_anomali_kecepatan(float $jarak_meter, float $selisih_waktu_detik): bool
    {
        if ($selisih_waktu_detik <= 0) return true; // Manipulasi waktu terdeteksi (Time jumping)

        $kecepatan_ms = $jarak_meter / $selisih_waktu_detik;
        $kecepatan_kmh = $kecepatan_ms * 3.6;

        // Asumsi kecepatan wajar maksimal di darat: 120 km/jam
        return $kecepatan_kmh > 120.0;
    }
}
