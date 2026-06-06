<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UpdateMenuUrutanSeeder extends Seeder
{
    public function run()
    {
        // Data update hanya memodifikasi kolom urutan berdasarkan id_menu existing
        $dataUpdate = [
            // Tingkat 1: Overview & Aktivitas Harian
            ['id_menu' => 1,  'urutan' => 1],  // Dashboard
            ['id_menu' => 3,  'urutan' => 2],  // Absensi Harian
            ['id_menu' => 4,  'urutan' => 3],  // Izin & Sakit
            ['id_menu' => 5,  'urutan' => 4],  // Live Radar
            ['id_menu' => 6,  'urutan' => 5],  // Log Fraud

            // Tingkat 2: Analitik
            ['id_menu' => 7,  'urutan' => 6],  // Laporan Rekap

            // Tingkat 3: Data Master
            ['id_menu' => 2,  'urutan' => 7],  // Data Siswa
            ['id_menu' => 9,  'urutan' => 8],  // Data Kelas
            ['id_menu' => 14, 'urutan' => 9],  // Mutasi Kelas
            ['id_menu' => 8,  'urutan' => 10], // Data User/Guru

            // Tingkat 4: Konfigurasi
            ['id_menu' => 10, 'urutan' => 11], // Pengumuman
            ['id_menu' => 12, 'urutan' => 12], // Jadwal Harian
            ['id_menu' => 11, 'urutan' => 13], // Hari Libur

            // Tingkat 5: Sistem
            ['id_menu' => 13, 'urutan' => 14], // Pengaturan
        ];

        // Eksekusi update secara massal dan aman (hanya update kolom yang didefinisikan)
        $this->db->table('menus')->updateBatch($dataUpdate, 'id_menu');

        echo "Urutan menu berhasil diupdate untuk production!\n";
    }
}
