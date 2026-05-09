<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DatabaseReset extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:reset-all';
    protected $description = 'Refresh migrasi dan jalankan semua seeder secara otomatis.';

    public function run(array $params)
    {
        CLI::newLine();
        // PERBAIKAN: Menggunakan 'red' bukan 'bg_red'
        CLI::write('--- PERINGATAN SISTEM ---', 'black', 'red');
        CLI::write('Perintah ini akan menghapus seluruh tabel dan data yang ada!', 'red');

        $confirmation = CLI::prompt('Apakah Anda yakin ingin melanjutkan?', ['y', 'n']);

        if ($confirmation !== 'y') {
            CLI::write('Proses dibatalkan oleh pengguna.', 'yellow');
            CLI::newLine();
            return;
        }

        CLI::newLine();
        // PERBAIKAN: Menggunakan 'yellow' bukan 'bg_yellow'
        CLI::write('--- MEMULAI RESET DATABASE TOTAL ---', 'black', 'yellow');
        CLI::newLine();

        // 1. Refresh Migration
        CLI::write('Step 1: Refreshing migrations (InitSistem)...', 'cyan');
        $this->call('migrate:refresh');
        CLI::write('✓ Migration refresh selesai.', 'green');
        CLI::newLine();

        // 2. InitDataSeeder
        CLI::write('Step 2: Menjalankan InitDataSeeder...', 'cyan');
        $this->call('db:seed', ['InitDataSeeder']); //
        CLI::write('✓ InitDataSeeder selesai.', 'green');
        CLI::newLine();

        // 3. SiswaSeeder
        CLI::write('Step 3: Menjalankan SiswaSeeder...', 'cyan');
        $this->call('db:seed', ['SiswaSeeder']); //
        CLI::newLine();
        CLI::write('✓ SiswaSeeder selesai.', 'green');

        CLI::newLine();
        // PERBAIKAN: Menggunakan 'green' bukan 'bg_green'
        CLI::write('--- SEMUA PROSES BERHASIL DISELESAIKAN ---', 'black', 'green');
        CLI::write('Anda sekarang dapat login menggunakan akun admin atau guru yang baru dibuat.', 'cyan');
        CLI::newLine();
    }
}
