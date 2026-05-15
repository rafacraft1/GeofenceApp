<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        // 1. Nonaktifkan pengecekan Foreign Key untuk proses Truncate yang aman
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Bersihkan tabel siswa beserta seluruh riwayat transaksinya
        $this->db->table('log_fraud')->truncate();
        $this->db->table('pengajuan_izin')->truncate();
        $this->db->table('absensi')->truncate();
        $this->db->table('siswa')->truncate();

        $siswaData = [];
        $nisAwal   = 2026001; // Format NIS angkatan 2026

        // 3. Persiapan Data Dummy Nama Siswa (15 Siswa)
        $namaKelas10A = ['Agus Prayitno', 'Bunga Citra', 'Candra Wijaya', 'Dewi Lestari', 'Eko Saputro'];
        $namaKelas10B = ['Fajar Siddiq', 'Gita Wirjawan', 'Hadi Sucipto', 'Intan Nuraini', 'Joko Santoso'];
        $namaKelas11A = ['Kartika Putri', 'Lukman Hakim', 'Maulana Yusuf', 'Nina Zatulini', 'Oka Antara'];

        // 4. Closure (Fungsi Helper) untuk generate array data siswa
        $generateSiswa = function (array $namaList, int $kelasId) use (&$nisAwal, &$siswaData) {
            foreach ($namaList as $nama) {
                $nis = (string) $nisAwal++;
                $siswaData[] = [
                    'kelas_id'      => $kelasId,
                    'nis'           => $nis,
                    'nama_siswa'    => $nama,
                    // Password default sama dengan NIS
                    'password'      => password_hash($nis, PASSWORD_BCRYPT),
                    'foto_profil'   => null,
                    'device_id'     => null,
                    'api_token'     => null,
                    'fcm_token'     => null,
                    'lat_terakhir'  => null,
                    'long_terakhir' => null,
                    'last_login'    => null,
                    'is_blocked'    => 0,
                    'fraud_count'   => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ];
            }
        };

        // 5. Eksekusi Distribusi Siswa ke Masing-masing Kelas
        $generateSiswa($namaKelas10A, 1); // Kelas 10-A (Wali: Budi Santoso)
        $generateSiswa($namaKelas10B, 2); // Kelas 10-B (Wali: Siti Aminah)
        $generateSiswa($namaKelas11A, 3); // Kelas 11-A (Tanpa Wali)

        // 6. Insert Batch Data Siswa
        $this->db->table('siswa')->insertBatch($siswaData);

        // 7. Aktifkan kembali pengecekan Foreign Key
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
