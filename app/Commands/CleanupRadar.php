<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\RawSql;

class CleanupRadar extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:cleanup-radar';
    protected $description = 'Menghapus log Live Tracking yang berusia lebih dari 30 hari secara aman.';

    public function run(array $params)
    {
        CLI::write("Memulai pembersihan tabel riwayat_lokasi...", 'yellow');

        $db = \Config\Database::connect();
        $builder = $db->table('riwayat_lokasi');

        $totalDeleted = 0;
        $chunkSize    = 1000;

        while (true) {
            $builder->where('waktu_rekam <', new RawSql('NOW() - INTERVAL 30 DAY'))
                ->limit($chunkSize)
                ->delete();

            $affected = $db->affectedRows();
            $totalDeleted += $affected;

            if ($affected < $chunkSize) {
                break;
            }

            usleep(100000);
        }

        if ($totalDeleted > 0) {
            CLI::write("Pembersihan selesai. Total {$totalDeleted} baris data lama telah dihapus.", 'green');
        } else {
            CLI::write("Pembersihan selesai. Tidak ada data log yang melebihi batas 30 hari.", 'green');
        }
    }
}
