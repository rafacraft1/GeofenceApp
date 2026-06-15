<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class BackupAbsensi extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:backup-harian';
    protected $description = 'Snapshot absensi harian ke folder public/download/ untuk rekap bulanan abadi (Merekam mutasi kelas & zona).';

    public function run(array $params)
    {
        $timezone   = env('app.appTimezone', 'Asia/Jakarta');
        // Mendukung argumen manual (Contoh: php spark absen:backup-harian 2026-07-15)
        $targetDate = $params[0] ?? Time::now($timezone)->toDateString();

        // Konversi format tanggal menjadi nama file bulanan (Contoh: juli2026)
        $timeObj    = Time::parse($targetDate, $timezone);
        $tahun      = $timeObj->format('Y');
        $bulanAngka = $timeObj->format('m');

        $daftarBulan = [
            '01' => 'januari',
            '02' => 'februari',
            '03' => 'maret',
            '04' => 'april',
            '05' => 'mei',
            '06' => 'juni',
            '07' => 'juli',
            '08' => 'agustus',
            '09' => 'september',
            '10' => 'oktober',
            '11' => 'november',
            '12' => 'desember'
        ];

        $namaFileBulanan = $daftarBulan[$bulanAngka] . $tahun;

        CLI::write("Memulai Snapshot Absensi: {$targetDate} untuk file [{$namaFileBulanan}]...", 'yellow');

        $db = \Config\Database::connect();

        // 1. Ambil Data Master Zona untuk pemetaan nama
        $zonaMentah = $db->table('zona_absensi')->get()->getResultArray();
        $zonaMap = [];
        $zonaDefault = 'Area Sekolah Pusat';
        foreach ($zonaMentah as $z) {
            $zonaMap[$z['id_zona']] = $z['nama_zona'];
            if ($z['is_default'] == 1) {
                $zonaDefault = $z['nama_zona'];
                $zonaMap['default_id'] = $z['id_zona'];
            }
        }

        // 2. Ambil seluruh data absensi hari tersebut
        $builder = $db->table('absensi');
        $builder->select('
            absensi.*, 
            siswa.nis, 
            siswa.nama_siswa, 
            siswa.zona_id as siswa_zona, 
            kelas.nama_kelas, 
            kelas.zona_id as kelas_zona
        ');
        $builder->join('siswa', 'siswa.id_siswa = absensi.siswa_id', 'left');
        $builder->join('kelas', 'kelas.id_kelas = absensi.kelas_id', 'left');
        $builder->where('absensi.tanggal', $targetDate);
        $builder->orderBy('absensi.jam_masuk', 'ASC');

        $dataAbsensi = $builder->get()->getResultArray();

        if (empty($dataAbsensi)) {
            CLI::write("Tidak ada data absensi pada tanggal {$targetDate}. Proses dibatalkan.", 'cyan');
            return;
        }

        // 3. Olah Data Menjadi Statis (Merekam Bukti Mutasi Kelas & Zona)
        $dataHariIni = [];
        foreach ($dataAbsensi as $row) {
            // Evaluasi zona persis pada HARI INI! 
            $zonaId = $row['siswa_zona'] ?? $row['kelas_zona'] ?? ($zonaMap['default_id'] ?? null);
            $namaZonaSaatIni = $zonaMap[$zonaId] ?? $zonaDefault;

            $dataHariIni[] = [
                'id_absensi'       => $row['id_absensi'],
                'tanggal'          => $row['tanggal'],
                'nis'              => $row['nis'] ?? 'TERHAPUS',
                'nama_siswa'       => $row['nama_siswa'] ?? 'SISWA TERHAPUS',
                'kelas_saat_absen' => $row['nama_kelas'] ?? 'Tanpa Kelas', // Status Kelas ABADI pada hari ini
                'zona_saat_absen'  => $namaZonaSaatIni,                    // Status Zona ABADI pada hari ini
                'jam_masuk'        => $row['jam_masuk'] ?? '-',
                'jam_pulang'       => $row['jam_pulang'] ?? '-',
                'status'           => $row['status'],
                'menit_telat'      => $row['menit_telat'],
                'is_fake_gps'      => $row['is_fake_gps'] ? 'Ya' : 'Tidak',
                'keterangan'       => $row['keterangan']
            ];
        }

        // 4. Persiapkan Direktori Penyimpanan di FOLDER PUBLIC
        // FCPATH menunjuk otomatis ke folder 'public/' di CodeIgniter 4
        $backupDir = FCPATH . 'download/';

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
            // Tambahkan index statis agar hacker tidak bisa melihat isi folder (Directory Listing)
            file_put_contents($backupDir . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        }

        $jsonFilePath = $backupDir . "{$namaFileBulanan}.json";
        $csvFilePath  = $backupDir . "{$namaFileBulanan}.csv";

        // 5. UPDATE DATA JSON BULANAN (Sistem Anti-Duplikat)
        $dataBulananGabungan = [];
        if (file_exists($jsonFilePath)) {
            $fileContent = file_get_contents($jsonFilePath);
            $dataBulananLama = json_decode($fileContent, true) ?: [];

            // Hapus data tanggal hari ini dari file lama (jika ada) untuk mencegah data ganda
            $dataBulananGabungan = array_filter($dataBulananLama, function ($item) use ($targetDate) {
                return $item['tanggal'] !== $targetDate;
            });
        }

        // Gabungkan sisa data bulan ini dengan data absensi terbaru
        $dataBulananGabungan = array_merge(array_values($dataBulananGabungan), $dataHariIni);

        // Simpan kembali ke JSON
        file_put_contents($jsonFilePath, json_encode($dataBulananGabungan, JSON_PRETTY_PRINT));

        // 6. UPDATE DATA CSV BULANAN (Tulis Ulang)
        $fileCsv = fopen($csvFilePath, 'w');

        // Menambahkan UTF-8 BOM agar jika Admin membukanya di Microsoft Excel, karakternya tidak rusak
        fputs($fileCsv, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // Tulis Header CSV
        fputcsv($fileCsv, ['ID Absen', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas (Saat Absen)', 'Zona/Lokasi (Saat Absen)', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran', 'Keterlambatan (Menit)', 'Terdeteksi Fake GPS', 'Keterangan Detail']);

        // Tulis Isi Data CSV
        foreach ($dataBulananGabungan as $row) {
            fputcsv($fileCsv, array_values($row));
        }
        fclose($fileCsv);

        CLI::write("✓ Backup Sukses! File tersedia di: public/download/{$namaFileBulanan}.csv", 'green');
        CLI::write("Total data di bulan ini: " . count($dataBulananGabungan) . " baris.", 'cyan');
        CLI::newLine();
    }
}
