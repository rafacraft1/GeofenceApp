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
     * Menghitung jarak haversine antara dua titik koordinat (meter)
     */
    public function hitungJarakMetres(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Memproses seluruh validasi bisnis untuk presensi masuk
     */
    public function validasiMasuk(array $aturanZona, float $lat, float $lon, Time $sekarang, bool $isDispensasi): array
    {
        $currentTime = $sekarang->format('H:i:s');
        $tanggalSekarang = $sekarang->toDateString();

        // ---------------------------------------------------------
        // 1. Cek Libur Rutin / Mingguan (Dari tabel zona_jadwal)
        // ---------------------------------------------------------
        if ($aturanZona['is_libur'] == 1) {
            return [
                'status'  => false,
                'message' => "Hari libur rutin (akhir pekan) untuk zona " . $aturanZona['nama_zona']
            ];
        }

        // ---------------------------------------------------------
        // 2. Cek Libur Khusus / PKL Targetting (Dari tabel hari_libur)
        // ---------------------------------------------------------
        $liburNasional = cache()->remember('hari_libur_' . $tanggalSekarang, 86400, function () use ($tanggalSekarang) {
            return $this->db->table('hari_libur')
                ->where('tanggal', $tanggalSekarang)
                ->get()
                ->getRowArray();
        });

        if ($liburNasional) {
            // Jika tipe libur Nasional, semua siswa libur tanpa terkecuali
            if (($liburNasional['tipe_libur'] ?? 'Nasional') === 'Nasional') {
                return [
                    'status'  => false,
                    'message' => "Absensi ditutup. Hari ini libur nasional: " . $liburNasional['keterangan']
                ];
            }

            // Jika tipe libur Internal, blokir hanya untuk zona default sekolah
            if (($liburNasional['tipe_libur'] ?? 'Nasional') === 'Internal' && $aturanZona['is_default'] == 1) {
                return [
                    'status'  => false,
                    'message' => "Absensi ditutup karena agenda internal: " . $liburNasional['keterangan'] . ". Siswa magang/PKL tetap mengikuti aturan perusahaan."
                ];
            }
        }

        // ---------------------------------------------------------
        // 3. Validasi Batas Waktu Masuk
        // ---------------------------------------------------------
        if (!$isDispensasi) {
            if ($currentTime < $aturanZona['waktu_buka_absen']) {
                return [
                    'status'  => false,
                    'message' => "Absensi di zona {$aturanZona['nama_zona']} dibuka pukul " . date('H:i', strtotime((string)$aturanZona['waktu_buka_absen'])) . " WIB."
                ];
            }
            if ($currentTime > $aturanZona['jam_pulang']) {
                return [
                    'status'  => false,
                    'message' => "Sesi absensi masuk sudah ditutup."
                ];
            }
        }

        // ---------------------------------------------------------
        // 4. Kalkulasi Keterlambatan
        // ---------------------------------------------------------
        $isTelat = $currentTime > $aturanZona['jam_masuk'];
        $menitTelat = ($isTelat && !$isDispensasi) ? abs($sekarang->difference(Time::parse($tanggalSekarang . ' ' . $aturanZona['jam_masuk'], 'Asia/Jakarta'))->getMinutes()) : 0;

        $status = $isDispensasi ? 'Dispensasi' : 'Hadir';
        $keterangan = $isDispensasi ? 'Hadir di Lokasi Kegiatan' : 'Tepat Waktu';

        // ---------------------------------------------------------
        // 5. Validasi Radius Geofence (Jika bukan dispensasi)
        // ---------------------------------------------------------
        if (!$isDispensasi) {
            $jarakMeter = $this->hitungJarakMetres($lat, $lon, (float)$aturanZona['latitude'], (float)$aturanZona['longitude']);

            if ($jarakMeter > (float)$aturanZona['radius']) {
                return [
                    'status'  => false,
                    'message' => 'Anda berada di luar zona absensi (' . round($jarakMeter) . ' meter dari titik pusat).'
                ];
            }

            if ($isTelat) {
                $status = 'Terlambat';
                $keterangan = "Terlambat {$menitTelat} Menit di " . $aturanZona['nama_zona'];
            } else {
                $keterangan = "Tepat Waktu di " . $aturanZona['nama_zona'];
            }
        }

        return [
            'status'       => true,
            'absen_status' => $status,
            'keterangan'   => $keterangan,
            'menit_telat'  => $menitTelat
        ];
    }

    /**
     * Memproses seluruh validasi bisnis untuk presensi pulang
     */
    public function validasiPulang(array $aturanZona, float $lat, float $lon, Time $sekarang, bool $isDispensasi): array
    {
        if (!$isDispensasi) {
            if ($sekarang->format('H:i:s') < $aturanZona['jam_pulang']) {
                return [
                    'status'  => false,
                    'message' => "Belum waktunya pulang. Jam pulang untuk zona {$aturanZona['nama_zona']} adalah " . date('H:i', strtotime((string)$aturanZona['jam_pulang'])) . " WIB."
                ];
            }

            $jarakMeter = $this->hitungJarakMetres($lat, $lon, (float)$aturanZona['latitude'], (float)$aturanZona['longitude']);
            if ($jarakMeter > (float)$aturanZona['radius']) {
                return [
                    'status'  => false,
                    'message' => 'Anda berada di luar zona absensi (' . round($jarakMeter) . ' meter dari titik pusat).'
                ];
            }
        }

        return ['status' => true];
    }
}
