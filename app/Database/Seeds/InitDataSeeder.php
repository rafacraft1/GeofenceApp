<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitDataSeeder extends Seeder
{
    public function run()
    {
        // 0. PERBAIKAN BUG: Insert Master Role terlebih dahulu
        $this->db->table('roles')->insert([
            'nama_role'  => 'Superadmin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $role_id = $this->db->insertID();

        // 1. Akun Superadmin (Menggunakan role_id hasil insert)
        $this->db->table('users')->insert([
            'username'      => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'nama_lengkap'  => 'Administrator Sistem',
            'role_id'       => $role_id, // <- Diperbaiki dari 'role' => 'Superadmin'
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        ]);

        // 2. Data Pengaturan Default
        $this->db->table('pengaturan')->insert([
            'latitude_sekolah'  => '-6.20000000',
            'longitude_sekolah' => '106.81666600',
            'radius_meter'      => 50,
            'firebase_url'      => null,
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        // 3. Data Kelas Dummy
        $this->db->table('kelas')->insertBatch([
            ['nama_kelas' => 'XII RPL 1', 'wali_kelas' => 'Budi Santoso, S.Kom', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_kelas' => 'XII RPL 2', 'wali_kelas' => 'Siti Aminah, M.Pd', 'created_at' => date('Y-m-d H:i:s')],
        ]);

        // 4. Data Jadwal Absen (Senin - Minggu)
        $data_jadwal = [
            ['kode_hari' => 1, 'nama_hari' => 'Senin',  'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 2, 'nama_hari' => 'Selasa', 'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 3, 'nama_hari' => 'Rabu',   'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 4, 'nama_hari' => 'Kamis',  'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 5, 'nama_hari' => 'Jumat',  'jam_masuk' => '06:30:00', 'jam_pulang' => '11:30:00', 'is_libur' => 0],
            ['kode_hari' => 6, 'nama_hari' => 'Sabtu',  'jam_masuk' => null,       'jam_pulang' => null,       'is_libur' => 1],
            ['kode_hari' => 7, 'nama_hari' => 'Minggu', 'jam_masuk' => null,       'jam_pulang' => null,       'is_libur' => 1],
        ];

        $this->db->table('jadwal_absen')->truncate();
        $this->db->table('jadwal_absen')->insertBatch($data_jadwal);

        // 5. Data Dummy Hari Libur Nasional 
        $this->db->table('hari_libur')->truncate();
        $this->db->table('hari_libur')->insert([
            'tanggal'    => '2026-08-17',
            'keterangan' => 'HUT Kemerdekaan RI ke-81',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        echo "Berhasil menyinkronkan InitDataSeeder beserta Jadwal!\n";
    }
}
