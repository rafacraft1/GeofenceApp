<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitDataSeeder extends Seeder
{
    public function run()
    {
        // 0. Nonaktifkan pengecekan Foreign Key agar bisa melakukan TRUNCATE
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Bersihkan data lama agar tidak duplikat saat seeding ulang
        $this->db->table('roles')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('pengaturan')->truncate();
        $this->db->table('kelas')->truncate();
        $this->db->table('jadwal_absen')->truncate();
        $this->db->table('hari_libur')->truncate();

        // 2. Data Master Role (Admin & Guru)
        $this->db->table('roles')->insertBatch([
            ['id_role' => 1, 'nama_role' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id_role' => 2, 'nama_role' => 'Guru',  'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        ]);

        // 3. Akun Sistem (1 Admin Utama, 2 Guru)
        $this->db->table('users')->insertBatch([
            [
                'nama_lengkap'  => 'Administrator Sistem',
                'username'      => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                'role_id'       => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'nama_lengkap'  => 'Budi Santoso, S.Kom',
                'username'      => 'gurubudi',
                'password_hash' => password_hash('guru123', PASSWORD_BCRYPT),
                'role_id'       => 2,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'nama_lengkap'  => 'Siti Aminah, M.Pd',
                'username'      => 'gurusiti',
                'password_hash' => password_hash('guru123', PASSWORD_BCRYPT),
                'role_id'       => 2,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]
        ]);

        // 4. Data Pengaturan Default
        $this->db->table('pengaturan')->insert([
            'latitude_sekolah'  => '-7.42238748',
            'longitude_sekolah' => '106.72535956',
            'radius_meter'      => 50,
            'firebase_url'      => null,
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        // 5. Data Kelas Dummy Terhubung dengan Wali Kelas
        $this->db->table('kelas')->insertBatch([
            ['nama_kelas' => 'XII RPL 1', 'wali_kelas' => 'Budi Santoso, S.Kom', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_kelas' => 'XII RPL 2', 'wali_kelas' => 'Siti Aminah, M.Pd', 'created_at' => date('Y-m-d H:i:s')],
        ]);

        // 6. Data Jadwal Absen (Senin - Minggu)
        $this->db->table('jadwal_absen')->insertBatch([
            ['kode_hari' => 1, 'nama_hari' => 'Senin',  'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 2, 'nama_hari' => 'Selasa', 'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 3, 'nama_hari' => 'Rabu',   'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 4, 'nama_hari' => 'Kamis',  'jam_masuk' => '06:30:00', 'jam_pulang' => '14:00:00', 'is_libur' => 0],
            ['kode_hari' => 5, 'nama_hari' => 'Jumat',  'jam_masuk' => '06:30:00', 'jam_pulang' => '11:30:00', 'is_libur' => 0],
            ['kode_hari' => 6, 'nama_hari' => 'Sabtu',  'jam_masuk' => null,       'jam_pulang' => null,       'is_libur' => 1],
            ['kode_hari' => 7, 'nama_hari' => 'Minggu', 'jam_masuk' => null,       'jam_pulang' => null,       'is_libur' => 1],
        ]);

        // 7. Data Dummy Hari Libur Nasional 
        $this->db->table('hari_libur')->insert([
            'tanggal'    => '2026-08-17',
            'keterangan' => 'HUT Kemerdekaan RI ke-81',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 8. Aktifkan kembali pengecekan Foreign Key
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        echo "Berhasil menyinkronkan Data Utama, Role, Akun Admin & Guru, beserta Jadwal!\n";
    }
}
