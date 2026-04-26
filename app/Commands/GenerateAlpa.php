<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class GenerateAlpa extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:generate-alpa';
    protected $description = 'Otomatis memberikan status Alpa untuk siswa yang tidak absen hari ini.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Ambil waktu saat ini secara presisi
        $hari_ini = Time::now('Asia/Jakarta')->toDateString();
        $waktu_sekarang = Time::now('Asia/Jakarta')->toTimeString();
        $hari_minggu = Time::now('Asia/Jakarta')->getDayOfWeek(); // 1=Senin, 7=Minggu

        CLI::write("Memulai proses Generate Alpa untuk tanggal: {$hari_ini}...", 'yellow');

        // 1. Cek Akhir Pekan (Sabtu = 6, Minggu = 7)
        if ($hari_minggu >= 6) {
            CLI::write("Hari ini adalah akhir pekan. Proses dihentikan.", 'green');
            return;
        }

        // 2. Cek Hari Libur Nasional
        $libur = $db->table('hari_libur')->where('tanggal', $hari_ini)->get()->getRow();
        if ($libur) {
            CLI::write("Hari ini adalah hari libur: {$libur->keterangan}. Proses dihentikan.", 'green');
            return;
        }

        // 3. KUNCI PENGAMAN: Cek Jam Pulang (Sesuai SOP Baru)
        $pengaturan = $db->table('pengaturan')->where('id', 1)->get()->getRow();
        if ($waktu_sekarang <= $pengaturan->jam_pulang) {
            CLI::write("Batal: Belum melewati jam pulang ({$pengaturan->jam_pulang}). Siswa masih diizinkan absen masuk (Terlambat).", 'red');
            return;
        }

        // 4. Ambil ID Siswa Aktif yang belum terblokir
        $siswa_aktif = $db->table('siswa')->where('is_blocked', 0)->select('id')->get()->getResultArray();
        $siswa_ids = array_column($siswa_aktif, 'id');

        // 5. Ambil ID Siswa yang SUDAH absen hari ini
        $sudah_absen = $db->table('absensi')->where('tanggal', $hari_ini)->select('siswa_id')->get()->getResultArray();
        $absen_ids = array_column($sudah_absen, 'siswa_id');

        // 6. Cari selisihnya (Yang belum absen sama sekali hingga jam pulang)
        $belum_absen_ids = array_diff($siswa_ids, $absen_ids);

        if (empty($belum_absen_ids)) {
            CLI::write("Semua siswa sudah memiliki data presensi hari ini.", 'green');
            return;
        }

        // 7. Insert Batch status Alpa
        $insertData = [];
        foreach ($belum_absen_ids as $sid) {
            $insertData[] = [
                'siswa_id'    => $sid,
                'tanggal'     => $hari_ini,
                'status'      => 'Alpa',
                'keterangan'  => 'Dibuat otomatis oleh Sistem (Cron Job)'
            ];
        }

        $db->table('absensi')->insertBatch($insertData);
        CLI::write(count($insertData) . " siswa berhasil diset menjadi Alpa.", 'green');
    }
}
