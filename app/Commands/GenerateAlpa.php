<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use App\Models\AbsensiModel;
use App\Models\HariLiburModel;

class GenerateAlpa extends BaseCommand
{
    protected $group       = 'Absensi';
    protected $name        = 'absen:generate-alpa';
    protected $description = 'Otomatis memberikan status Alpa untuk siswa yang tidak absen hari ini (Mendukung Multi-Zona & PKL).';

    public function run(array $params)
    {
        // Gunakan Query Builder native untuk relasi yang kompleks
        $db = \Config\Database::connect();

        $timezone       = env('app.appTimezone', 'Asia/Jakarta');
        $waktu_sekarang = Time::now($timezone);
        $hari_ini       = $waktu_sekarang->toDateString();
        $jam_sekarang   = $waktu_sekarang->toTimeString();
        $kode_hari      = $waktu_sekarang->format('N');

        CLI::write("Memulai proses Generate Alpa (Multi-Zona) untuk tanggal: {$hari_ini}...", 'yellow');

        // ---------------------------------------------------------
        // 1. CEK LIBUR NASIONAL (GLOBAL)
        // ---------------------------------------------------------
        $liburModel = new HariLiburModel();
        $liburHariIni = $liburModel->where('tanggal', $hari_ini)->first();

        // Jika Libur Nasional, SELURUH siswa (termasuk PKL) libur. Eksekusi berhenti mutlak.
        if ($liburHariIni && ($liburHariIni['tipe_libur'] ?? 'Nasional') === 'Nasional') {
            CLI::write("Hari ini adalah Libur Nasional: {$liburHariIni['keterangan']}. Semua zona diliburkan. Cronjob dibatalkan.", 'green');
            return;
        }

        // ---------------------------------------------------------
        // 2. PETAKAN JADWAL SELURUH ZONA HARI INI
        // ---------------------------------------------------------
        $defaultZona = $db->table('zona_absensi')->where('is_default', 1)->get()->getRowArray();
        if (!$defaultZona) {
            CLI::error("FATAL ERROR: Zona default sekolah tidak ditemukan di database!");
            return;
        }

        $jadwalMentah = $db->table('zona_jadwal')
            ->select('zona_jadwal.*, zona_absensi.is_default, zona_absensi.nama_zona')
            ->join('zona_absensi', 'zona_absensi.id_zona = zona_jadwal.zona_id')
            ->where('kode_hari', $kode_hari)
            ->get()
            ->getResultArray();

        $jadwalByZona = [];
        foreach ($jadwalMentah as $j) {
            $jadwalByZona[$j['zona_id']] = $j;
        }

        // ---------------------------------------------------------
        // 3. AMBIL DATA SISWA YANG BOLOS HARI INI
        // ---------------------------------------------------------
        $absensiModel = new AbsensiModel();
        $subqueryAbsensi = $absensiModel->builder()->select('siswa_id')->where('tanggal', $hari_ini);

        // Ambil siswa yang belum ada log absensinya hari ini, beserta hierarki zonanya
        $siswaBelumAbsen = $db->table('siswa')
            ->select('siswa.id_siswa, siswa.kelas_id, siswa.zona_id as siswa_zona, kelas.zona_id as kelas_zona')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('siswa.is_blocked', 0)
            ->whereNotIn('siswa.id_siswa', $subqueryAbsensi)
            ->get()
            ->getResultArray();

        if (empty($siswaBelumAbsen)) {
            CLI::write("Luar Biasa! Semua siswa aktif sudah memiliki log presensi hari ini.", 'green');
            return;
        }

        $insertData = [];
        $waktuInsert = $waktu_sekarang->toDateTimeString();

        // ---------------------------------------------------------
        // 4. EVALUASI ALPA BERDASARKAN ATURAN ZONA MASING-MASING
        // ---------------------------------------------------------
        foreach ($siswaBelumAbsen as $siswa) {
            // Logika Penentuan Zona: Zona Pribadi (PKL) -> Jika Kosong pakai Zona Kelas -> Jika kosong pakai Zona Sekolah
            $effectiveZonaId = $siswa['siswa_zona'] ?? $siswa['kelas_zona'] ?? $defaultZona['id_zona'];

            $jadwal = $jadwalByZona[$effectiveZonaId] ?? null;

            if (!$jadwal) {
                continue; // Lewati jika admin belum membuat jadwal untuk zona perusahaan ini
            }

            // A. Cek Libur Rutin (Misal: Perusahaan ini libur di hari Sabtu, tapi sekolah masuk)
            if ($jadwal['is_libur'] == 1) {
                continue;
            }

            // B. Cek Libur Internal Sekolah (Misal: Rapat Guru)
            if ($liburHariIni && ($liburHariIni['tipe_libur'] ?? 'Nasional') === 'Internal') {
                if ($jadwal['is_default'] == 1) {
                    continue; // Bebaskan siswa reguler dari Alpa karena sekolah sedang libur internal
                }
                // Anak PKL (is_default = 0) akan tetap lanjut dievaluasi ke bawah!
            }

            // C. Cek Ambang Batas Waktu Alpa
            if ($jam_sekarang < $jadwal['jam_masuk']) {
                // Belum waktunya memvonis Alpa. (Bisa jadi cronjob berjalan jam 07:00, tapi anak PKL masuk jam 09:00).
                continue;
            }

            // JIKA GAGAL MELEWATI SEMUA FILTER DI ATAS = FIX BOLOS / ALPA
            $insertData[] = [
                'siswa_id'    => $siswa['id_siswa'],
                'kelas_id'    => $siswa['kelas_id'],
                'tanggal'     => $hari_ini,
                'status'      => 'Alpa',
                'keterangan'  => 'Dibuat otomatis oleh Sistem (Cronjob)',
                'created_at'  => $waktuInsert,
                'updated_at'  => $waktuInsert
            ];
        }

        // ---------------------------------------------------------
        // 5. SIMPAN KE DATABASE
        // ---------------------------------------------------------
        if (!empty($insertData)) {
            $absensiModel->insertBatch($insertData);
            CLI::write("Berhasil men-generate status Alpa untuk " . count($insertData) . " siswa secara proporsional sesuai aturan zona masing-masing.", 'green');
        } else {
            CLI::write("Tidak ada siswa yang memenuhi syarat untuk di-Alpa-kan pada waktu saat ini.", 'yellow');
        }
    }
}
