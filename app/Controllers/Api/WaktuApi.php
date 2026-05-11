<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class WaktuApi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        // 1. Set Zona Waktu Server
        $timezone = env('app.appTimezone', 'Asia/Jakarta');
        $waktuNow = Time::now($timezone);

        $db = \Config\Database::connect();

        // 2. Inisialisasi Nilai Default (Fallback jika database kosong)
        $jamMasuk   = '07:00:00';
        $jamPulang  = '15:00:00';
        $latSekolah = -6.914744;
        $lonSekolah = 107.609810;
        $radius     = 50.0;
        $isLibur    = false;
        $namaLibur  = '';

        try {
            // A. Ambil Data Lokasi dari tabel `pengaturan`
            if ($db->tableExists('pengaturan')) {
                $pengaturan = $db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRow();
                if ($pengaturan) {
                    $latSekolah = (float) $pengaturan->latitude_sekolah;
                    $lonSekolah = (float) $pengaturan->longitude_sekolah;
                    $radius     = (float) $pengaturan->radius_meter;
                }
            }

            // B. Ambil Jadwal Jam Masuk & Pulang dari tabel `jadwal_absen`
            // $kodeHari = 1 (Senin) s/d 7 (Minggu)
            $kodeHari = $waktuNow->format('N');
            if ($db->tableExists('jadwal_absen')) {
                $jadwal = $db->table('jadwal_absen')->where('kode_hari', $kodeHari)->get()->getRow();
                if ($jadwal) {
                    $jamMasuk  = $jadwal->jam_masuk;
                    $jamPulang = $jadwal->jam_pulang;
                    // Cek jika hari ini di-setting sebagai libur (misal hari Minggu)
                    if ($jadwal->is_libur == 1) {
                        $isLibur   = true;
                        $namaLibur = 'Libur Akhir Pekan';
                    }
                }
            }

            // C. Override Libur jika tanggal hari ini ada di tabel `hari_libur`
            $tanggalHariIni = $waktuNow->format('Y-m-d');
            if ($db->tableExists('hari_libur')) {
                $liburNasional = $db->table('hari_libur')->where('tanggal', $tanggalHariIni)->get()->getRow();
                if ($liburNasional) {
                    $isLibur   = true;
                    $namaLibur = $liburNasional->keterangan;
                }
            }
        } catch (\Exception $e) {
            // Jika ada error database, API tidak akan hancur (500), melainkan pakai nilai default
            log_message('error', 'API Waktu Error: ' . $e->getMessage());
        }

        // 3. Kirim ke Flutter dengan struktur yang sesuai dengan api_client.dart
        return $this->respond([
            'status'  => 200,
            'message' => 'Berhasil mengambil data konfigurasi server',
            'data'    => [
                'waktu'       => $waktuNow->toDateTimeString(),
                'jam_masuk'   => $jamMasuk,
                'jam_pulang'  => $jamPulang,
                'lat_sekolah' => $latSekolah,
                'lon_sekolah' => $lonSekolah,
                'radius'      => $radius,
                'is_libur'    => $isLibur,
                'nama_libur'  => $namaLibur
            ]
        ]);
    }
}
