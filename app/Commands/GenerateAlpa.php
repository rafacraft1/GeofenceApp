<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use App\Models\HariLiburModel;
use App\Models\JadwalAbsenModel;

class GenerateAlpa extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:generate-alpa';
    protected $description = 'Otomatis memberikan status Alpa untuk siswa yang tidak absen hari ini.';

    public function run(array $params)
    {
        $siswaModel   = new SiswaModel();
        $absensiModel = new AbsensiModel();
        $liburModel   = new HariLiburModel();
        $jadwalModel  = new JadwalAbsenModel();

        $timezone       = env('app.appTimezone', 'Asia/Jakarta');
        $waktu_sekarang = Time::now($timezone);
        $hari_ini       = $waktu_sekarang->toDateString();
        $jam_sekarang   = $waktu_sekarang->toTimeString();
        $kode_hari      = $waktu_sekarang->format('N');

        CLI::write("Memulai proses Generate Alpa untuk tanggal: {$hari_ini}...", 'yellow');

        // 1. Cek Hari Libur Nasional
        $libur = $liburModel->where('tanggal', $hari_ini)->first();
        if ($libur) {
            CLI::write("Hari ini adalah hari libur: {$libur['keterangan']}. Cronjob dibatalkan.", 'green');
            return;
        }

        // 2. Cek Jadwal Masuk
        $jadwal = $jadwalModel->where('kode_hari', $kode_hari)->first();
        if (!$jadwal || $jadwal['is_libur'] == 1) {
            CLI::write("Hari ini disetting libur/akhir pekan pada jadwal. Cronjob dibatalkan.", 'green');
            return;
        }

        // 3. Pengecekan Batas Jam (Mencegah Alpa prematur)
        if ($jam_sekarang < $jadwal['jam_masuk']) {
            CLI::write("Belum melampaui batas jam masuk ({$jadwal['jam_masuk']}). Proses dihentikan.", 'red');
            return;
        }

        $subqueryAbsensi = $absensiModel->builder()->select('siswa_id')->where('tanggal', $hari_ini);

        // PERBAIKAN: Menarik kelas_id untuk disuntikkan ke tabel absensi
        $siswaBelumAbsen = $siswaModel->select('id_siswa, kelas_id')
            ->where('is_blocked', 0)
            ->whereNotIn('id_siswa', $subqueryAbsensi)
            ->findAll();

        if (empty($siswaBelumAbsen)) {
            CLI::write("Semua siswa aktif sudah memiliki data presensi hari ini.", 'green');
            return;
        }

        $insertData  = [];
        $waktuInsert = $waktu_sekarang->toDateTimeString();

        foreach ($siswaBelumAbsen as $siswa) {
            $insertData[] = [
                'siswa_id'    => $siswa['id_siswa'],
                'kelas_id'    => $siswa['kelas_id'], // INJEKSI HISTORICAL SNAPSHOT
                'tanggal'     => $hari_ini,
                'status'      => 'Alpa',
                'keterangan'  => 'Dibuat otomatis oleh Sistem (Cronjob)',
                'created_at'  => $waktuInsert,
                'updated_at'  => $waktuInsert
            ];
        }

        $absensiModel->insertBatch($insertData);
        CLI::write("Berhasil men-generate status Alpa untuk " . count($insertData) . " siswa.", 'green');
    }
}
