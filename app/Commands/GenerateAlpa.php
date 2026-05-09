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

        $timezone       = env('app.appTimezone', 'Asia/Jakarta');
        $waktu_sekarang = Time::now($timezone);
        $hari_ini       = $waktu_sekarang->toDateString();
        $jam_sekarang   = $waktu_sekarang->toTimeString();
        $kode_hari      = $waktu_sekarang->format('N'); // 1 = Senin, 7 = Minggu

        CLI::write("Memulai proses Generate Alpa untuk tanggal: {$hari_ini}...", 'yellow');

        // 1. Cek Hari Libur Nasional
        $libur = $db->table('hari_libur')->where('tanggal', $hari_ini)->get()->getRow();
        if ($libur) {
            CLI::write("Hari ini adalah hari libur: {$libur->keterangan}. Proses dihentikan.", 'green');
            return;
        }

        // 2. Cek Jadwal Mingguan (Apakah hari ini libur atau belum waktunya)
        $jadwal = $db->table('jadwal_absen')->where('kode_hari', $kode_hari)->get()->getRow();

        if (!$jadwal || $jadwal->is_libur == 1) {
            CLI::write("Hari ini disetel sebagai hari libur pada jadwal mingguan. Proses dihentikan.", 'green');
            return;
        }

        // 3. Validasi Jam Pulang
        if ($jam_sekarang <= $jadwal->jam_pulang) {
            CLI::write("Batal: Belum melewati jam pulang ({$jadwal->jam_pulang}). Siswa masih diizinkan absen masuk (Terlambat).", 'red');
            return;
        }

        // 4. Ambil ID Siswa Aktif yang belum terblokir (PERBAIKAN: Menggunakan id_siswa)
        $siswa_aktif = $db->table('siswa')->select('id_siswa')->where('is_blocked', 0)->get()->getResultArray();
        $siswa_ids   = array_column($siswa_aktif, 'id_siswa');

        // 5. Ambil ID Siswa yang SUDAH absen hari ini
        $sudah_absen = $db->table('absensi')->select('siswa_id')->where('tanggal', $hari_ini)->get()->getResultArray();
        $absen_ids   = array_column($sudah_absen, 'siswa_id');

        // 6. Cari selisihnya (Yang belum absen sama sekali)
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
                'keterangan'  => 'Dibuat otomatis oleh Sistem',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];
        }

        $db->table('absensi')->insertBatch($insertData);
        CLI::write(count($insertData) . " siswa berhasil diset menjadi Alpa.", 'green');
    }
}
