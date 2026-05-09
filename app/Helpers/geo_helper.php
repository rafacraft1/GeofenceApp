<?php

if (!function_exists('hitung_jarak_haversine')) {
    function hitung_jarak_haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth_radius = 6371000.0; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));

        return round($earth_radius * $c); // Hasil dalam meter
    }
}
