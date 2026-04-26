<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupRadar extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:cleanup-radar';
    protected $description = 'Menghapus log Live Tracking yang berusia lebih dari 30 hari.';

    public function run(array $params)
    {
        CLI::write("Memulai pembersihan tabel riwayat_lokasi...", 'yellow');

        $db = \Config\Database::connect();

        // Eksekusi Delete
        $db->query("DELETE FROM riwayat_lokasi WHERE waktu_rekam < NOW() - INTERVAL 30 DAY");

        $affected = $db->affectedRows();
        CLI::write("Pembersihan selesai. {$affected} baris data lama telah dihapus.", 'green');
    }
}
