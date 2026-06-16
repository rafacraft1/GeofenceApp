<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class DatabaseProd extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:prod';
    protected $description = 'FACTORY RESET: Reset DB, Hapus Uploads, & Bersihkan Sampah Sistem CI4.';

    public function run(array $params)
    {
        CLI::newLine();
        CLI::write('=================================================', 'yellow');
        CLI::write('      !!! [FACTORY RESET] SISTEM GEOFENCE !!!    ', 'black', 'red');
        CLI::write('=================================================', 'yellow');
        CLI::write('BAHAYA: Seluruh data tabel, riwayat, foto, dan cache akan musnah!', 'red');

        $confirmation = CLI::prompt('Ketik "PROD" untuk mengeksekusi Factory Reset ini', 'n');

        if ($confirmation !== 'PROD') {
            CLI::write('Konfirmasi salah. Factory reset dibatalkan.', 'yellow');
            return;
        }

        try {
            $seeder = \Config\Database::seeder();

            CLI::newLine();

            // STEP 1: Hapus File Upload (Foto Absen, Izin, Profil)
            CLI::write('Step 1: Membersihkan file uploads (Data Pengguna)...', 'cyan');
            $this->cleanUploadFolders();

            CLI::newLine();

            // STEP 2: Hapus Sampah Framework CI4 (Cache, Logs, Sessions)
            CLI::write('Step 2: Membersihkan sampah sistem CI4 (Cache, Logs, Sessions)...', 'cyan');
            $this->cleanWritableFolders();

            CLI::newLine();

            // STEP 3: Drop & Recreate Database
            CLI::write('Step 3: Refreshing migrations (Drop & Recreate Tables)...', 'cyan');
            $this->call('migrate:refresh');

            CLI::newLine();

            // STEP 4: Seed Master Data
            CLI::write('Step 4: Seeding InitDataSeeder (Master Data)...', 'cyan');
            $seeder->call('InitDataSeeder');

            CLI::newLine();
            CLI::write('=================================================', 'green');
            CLI::write(' ✓ FACTORY RESET SELESAI. SISTEM KEMBALI FRESH.  ', 'black', 'green');
            CLI::write('=================================================', 'green');
            CLI::newLine();
        } catch (Throwable $e) {
            CLI::error('GAGAL: ' . $e->getMessage());
        }
    }

    /**
     * Membersihkan folder upload public tanpa menghapus file keamanan
     */
    private function cleanUploadFolders()
    {
        $folders = [
            FCPATH . 'uploads/absensi/',
            FCPATH . 'uploads/izin/',
            FCPATH . 'uploads/pengumuman/',
            FCPATH . 'uploads/profiles/',
            FCPATH . 'uploads/siswa/'
        ];

        $totalDeleted = 0;

        foreach ($folders as $folder) {
            if (!is_dir($folder)) continue;

            $files = new \DirectoryIterator($folder);
            $folderDeletedCount = 0;

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();

                    if (!in_array($filename, ['.htaccess', 'index.html', 'index.php', '.gitkeep'])) {
                        unlink($file->getPathname());
                        $folderDeletedCount++;
                        $totalDeleted++;
                    }
                }
            }

            $folderName = basename($folder);
            CLI::write("  - Folder '$folderName' bersih ($folderDeletedCount file dihapus).", 'white');
        }

        CLI::write("=> Total $totalDeleted file gambar/dokumen dihapus.", 'green');
    }

    /**
     * Membersihkan sampah internal framework di folder writable/
     */
    private function cleanWritableFolders()
    {
        // Menggunakan WRITEPATH bawaan CI4 untuk menunjuk ke folder writable/
        $folders = [
            WRITEPATH . 'cache/',
            WRITEPATH . 'debugbar/',
            WRITEPATH . 'logs/',
            WRITEPATH . 'session/'
        ];

        $totalDeleted = 0;

        foreach ($folders as $folder) {
            if (!is_dir($folder)) continue;

            $files = new \DirectoryIterator($folder);
            $folderDeletedCount = 0;

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();

                    if (!in_array($filename, ['.htaccess', 'index.html', 'index.php', '.gitkeep'])) {
                        unlink($file->getPathname());
                        $folderDeletedCount++;
                        $totalDeleted++;
                    }
                }
            }

            $folderName = basename($folder);
            CLI::write("  - Writable '$folderName' bersih ($folderDeletedCount file dihapus).", 'white');
        }

        CLI::write("=> Total $totalDeleted file sampah sistem dihapus.", 'green');
    }
}
