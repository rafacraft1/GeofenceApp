<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class AppUpdate extends BaseCommand
{
    protected $group       = 'System';
    protected $name        = 'app:update';
    protected $description = 'Menarik pembaruan aplikasi terbaru dari GitHub dan menjalankan migrasi.';

    public function run(array $params)
    {
        CLI::newLine();
        CLI::write('=================================================', 'yellow');
        CLI::write('      !!! MEMULAI PROSES UPDATE APLIKASI !!!     ', 'black', 'green');
        CLI::write('=================================================', 'yellow');

        // Tentukan branch yang dipakai (main / master)
        $branch = 'main';

        try {
            // STEP 1: Tarik Pembaruan dari Git
            CLI::write("Step 1: Menarik kode terbaru dari branch '$branch'...", 'cyan');
            $gitOutput = [];
            $gitCode = 0;
            // Menjalankan perintah git pull di terminal server
            exec("git pull origin $branch 2>&1", $gitOutput, $gitCode);

            foreach ($gitOutput as $line) {
                CLI::write('  > ' . $line, 'white');
            }

            if ($gitCode !== 0) {
                CLI::error('GAGAL: Proses Git Pull dihentikan. Pastikan server memiliki akses ke GitHub.');
                return;
            }

            CLI::newLine();

            // STEP 2: Update Composer (Jika ada library baru)
            CLI::write('Step 2: Memeriksa dan memperbarui library (Composer)...', 'cyan');
            $composerOutput = [];
            exec('composer install --no-dev --optimize-autoloader 2>&1', $composerOutput);

            // Tampilkan 3 baris terakhir saja agar terminal tidak terlalu penuh
            $shortOutput = array_slice($composerOutput, -3);
            foreach ($shortOutput as $line) {
                CLI::write('  > ' . $line, 'white');
            }

            CLI::newLine();

            // STEP 3: Jalankan Database Migrations
            CLI::write('Step 3: Mengecek struktur Database (Migrations)...', 'cyan');
            $this->call('migrate'); // Memanggil perintah php spark migrate otomatis

            CLI::newLine();

            // STEP 4: Bersihkan Cache Aplikasi (Bukan file upload)
            CLI::write('Step 4: Membersihkan Cache Sistem...', 'cyan');
            $this->cleanSystemCache();

            CLI::newLine();
            CLI::write('=================================================', 'green');
            CLI::write(' ✓ UPDATE SELESAI! SISTEM BERJALAN DI VERSI BARU.', 'black', 'green');
            CLI::write('=================================================', 'green');
            CLI::newLine();
        } catch (Throwable $e) {
            CLI::error('TERJADI KESALAHAN FATAL: ' . $e->getMessage());
        }
    }

    /**
     * Membersihkan Cache dan Logs agar aplikasi membaca kode yang baru
     */
    private function cleanSystemCache()
    {
        $folders = [
            WRITEPATH . 'cache/',
            WRITEPATH . 'debugbar/'
        ];

        $deleted = 0;
        foreach ($folders as $folder) {
            if (!is_dir($folder)) continue;
            $files = new \DirectoryIterator($folder);
            foreach ($files as $file) {
                if ($file->isFile() && !in_array($file->getFilename(), ['.htaccess', 'index.html'])) {
                    unlink($file->getPathname());
                    $deleted++;
                }
            }
        }
        CLI::write("  - Cache sistem berhasil dibersihkan ($deleted file sampah dihapus).", 'green');
    }
}
