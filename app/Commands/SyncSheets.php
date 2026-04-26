<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class SyncSheets extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:sync-sheets';
    protected $description = 'Sinkronisasi data absensi harian ke Google Sheets.';

    public function run(array $params)
    {
        $hari_ini = Time::now('Asia/Jakarta')->toDateString();
        CLI::write("Memulai sinkronisasi Google Sheets untuk tanggal: {$hari_ini}...", 'yellow');

        // Catatan Implementasi: 
        // 1. Letakkan file kredensial JSON dari Google Cloud di folder writable/google-credentials.json
        // 2. Pastikan ID Spreadsheet sudah diset (di .env atau tabel pengaturan).

        $kredensial_path = WRITEPATH . 'google-credentials.json';

        if (!file_exists($kredensial_path)) {
            CLI::write("ERROR: File kredensial Google API tidak ditemukan di {$kredensial_path}.", 'red');
            return;
        }

        try {
            $client = new \Google\Client();
            $client->setAuthConfig($kredensial_path);
            $client->addScope(\Google\Service\Sheets::SPREADSHEETS);
            $service = new \Google\Service\Sheets($client);

            // Ambil Data dari DB
            $db = \Config\Database::connect();
            $data_absen = $db->table('absensi')
                ->select('siswa.nis, siswa.nama_lengkap, absensi.status, absensi.waktu_masuk')
                ->join('siswa', 'siswa.id = absensi.siswa_id')
                ->where('absensi.tanggal', $hari_ini)
                ->get()->getResultArray();

            $values = [];
            foreach ($data_absen as $row) {
                $values[] = [$hari_ini, $row['nis'], $row['nama_lengkap'], $row['status'], $row['waktu_masuk']];
            }

            // Eksekusi Push ke Sheets (Ganti 'YOUR_SHEET_ID' nanti)
            /*
            $spreadsheetId = 'YOUR_SHEET_ID';
            $range = 'Sheet1!A:E';
            $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
            $params = ['valueInputOption' => 'USER_ENTERED'];
            $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            */

            CLI::write("Berhasil menyiapkan " . count($values) . " baris data (Simulasi API Push selesai).", 'green');
        } catch (\Exception $e) {
            CLI::write("Gagal sinkronisasi: " . $e->getMessage(), 'red');
        }
    }
}
