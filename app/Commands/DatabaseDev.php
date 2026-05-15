<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class DatabaseDev extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:dev';
    protected $description = 'Reset database untuk lingkungan DEVELOPMENT (Migration + Master Data + Dummy Siswa).';

    public function run(array $params)
    {
        CLI::newLine();
        CLI::write('--- [MODE DEVELOPMENT] RESET DATABASE ---', 'black', 'cyan');
        CLI::write('Peringatan: Seluruh data akan dihapus dan diganti dengan data dummy.', 'yellow');

        $confirmation = CLI::prompt('Lanjutkan proses reset dev?', ['y', 'n']);

        if (strtolower($confirmation) !== 'y') {
            CLI::write('Proses dibatalkan.', 'light_gray');
            return;
        }

        try {
            $seeder = \Config\Database::seeder();

            CLI::write('Step 1: Refreshing migrations...', 'cyan');
            $this->call('migrate:refresh');

            CLI::write('Step 2: Seeding InitDataSeeder (Master Data)...', 'cyan');
            $seeder->call('InitDataSeeder');

            CLI::write('Step 3: Seeding SiswaSeeder (Dummy Data)...', 'cyan');
            $seeder->call('SiswaSeeder');

            CLI::newLine();
            CLI::write('✓ DATABASE DEV BERHASIL DIRESET', 'black', 'green');
            CLI::newLine();
        } catch (Throwable $e) {
            CLI::error('GAGAL: ' . $e->getMessage());
        }
    }
}
