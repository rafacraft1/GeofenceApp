<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use App\Models\SiswaModel;

class GenerateAlpa extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:generate-alpa';
    protected $description = 'Otomatis memberikan status Alpa untuk siswa yang tidak absen hari ini.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $siswaModel = new SiswaModel();

        $timezone       = env('app.appTimezone', 'Asia/Jakarta');
        $waktu_sekarang = Time::now($timezone);
        $hari_ini       = $waktu_sekarang->toDateString();
        $jam_sekarang   = $waktu_sekarang->toTimeString();
        $kode_hari      = $waktu_sekarang->format('N');

        CLI::write("Memulai proses Generate Alpa untuk tanggal: {$hari_ini}...", 'yellow');

        // 1. Cek Hari Libur Nasional
        $libur = $db->table('hari_libur')->where('tanggal', $hari_ini)->get()->getRow();
        if ($libur) {
            CLI::write("Hari ini adalah hari libur: {$libur->keterangan}. Proses dihentikan.", 'green');
            return;
        }

        // 2. Cek Jadwal Mingguan
        $jadwal = $db->table('jadwal_absen')->where('kode_hari', $kode_hari)->get()->getRow();
        if (!$jadwal || $jadwal->is_libur == 1) {
            CLI::write("Hari ini adalah akhir pekan / diliburkan. Proses dihentikan.", 'green');
            return;
        }

        // 3. Validasi Waktu Eksekusi (Harus setelah jam masuk selesai)
        if ($jam_sekarang < $jadwal->jam_masuk) {
            CLI::write("Belum melampaui batas jam masuk ({$jadwal->jam_masuk}). Proses dihentikan.", 'red');
            return;
        }

        // 4. Ambil Siswa yang belum absen (Memanfaatkan Subquery & Indexing)
        // Jauh lebih cepat dan hemat memori dibandingkan array_diff
        $subqueryAbsensi = $db->table('absensi')->select('siswa_id')->where('tanggal', $hari_ini);

        $siswaBelumAbsen = $siswaModel->select('id_siswa')
            ->where('is_blocked', 0)
            ->whereNotIn('id_siswa', $subqueryAbsensi)
            ->findAll();

        if (empty($siswaBelumAbsen)) {
            CLI::write("Semua siswa aktif sudah memiliki data presensi hari ini.", 'green');
            return;
        }

        // 5. Insert Batch Status Alpa
        $insertData = [];
        $waktuInsert = $waktu_sekarang->toDateTimeString();

        foreach ($siswaBelumAbsen as $siswa) {
            $insertData[] = [
                'siswa_id'    => $siswa['id_siswa'],
                'tanggal'     => $hari_ini,
                'status'      => 'Alpa',
                'keterangan'  => 'Dibuat otomatis oleh Sistem (Cronjob)',
                'created_at'  => $waktuInsert,
                'updated_at'  => $waktuInsert
            ];
        }

        // Insert batch untuk performa database maksimal
        $db->table('absensi')->insertBatch($insertData);

        $total = count($insertData);
        CLI::write("Berhasil men-generate status Alpa untuk {$total} siswa.", 'green');
    }
}
