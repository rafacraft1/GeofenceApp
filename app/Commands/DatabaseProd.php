<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class DatabaseProd extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:prod';
    protected $description = 'Reset database untuk lingkungan PRODUCTION (Migration + Master Data SAJA).';

    public function run(array $params)
    {
        CLI::newLine();
        CLI::write('!!! [MODE PRODUCTION] RESET DATABASE !!!', 'black', 'red');
        CLI::write('BAHAYA: Seluruh data transaksi akan hilang permanen!', 'red');

        $confirmation = CLI::prompt('Ketik "PROD" untuk mengonfirmasi pembersihan database ini:', 'n');

        if ($confirmation !== 'PROD') {
            CLI::write('Konfirmasi salah. Proses dibatalkan demi keamanan.', 'yellow');
            return;
        }

        try {
            $seeder = \Config\Database::seeder();

            CLI::write('Step 1: Refreshing migrations...', 'cyan');
            $this->call('migrate:refresh');

            CLI::write('Step 2: Seeding InitDataSeeder (Master Data)...', 'cyan');
            $seeder->call('InitDataSeeder');

            CLI::newLine();
            CLI::write('✓ DATABASE PROD BERHASIL DI-INITIALIZE', 'black', 'green');
            CLI::newLine();
        } catch (Throwable $e) {
            CLI::error('GAGAL: ' . $e->getMessage());
        }
    }
}
