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
        $this->db->table('role_menus')->truncate();
        $this->db->table('menus')->truncate();
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

        // 3. Akun Sistem (1 Admin Utama, 2 Guru) - PERSIS SEPERTI EXISTING KODING ANDA
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
            'radius_meter'      => 100,
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

        // ========================================================
        // 8. DATA MENUS (Menggunakan Class FontAwesome)
        // ========================================================
        $this->db->table('menus')->insertBatch([
            ['id_menu' => 1,  'nama_menu' => 'Dashboard',        'url' => 'admin/dashboard',  'icon' => 'fas fa-th-large', 'urutan' => 1, 'is_active' => 1],
            ['id_menu' => 2,  'nama_menu' => 'Data Siswa',       'url' => 'admin/siswa',      'icon' => 'fas fa-user-graduate', 'urutan' => 2, 'is_active' => 1],
            ['id_menu' => 3,  'nama_menu' => 'Data Absensi',     'url' => 'admin/absensi',    'icon' => 'fas fa-calendar-check', 'urutan' => 3, 'is_active' => 1],
            ['id_menu' => 4,  'nama_menu' => 'Persetujuan Izin', 'url' => 'admin/izin',       'icon' => 'fas fa-envelope-open-text', 'urutan' => 4, 'is_active' => 1],
            ['id_menu' => 5,  'nama_menu' => 'Live Tracking',    'url' => 'admin/tracking',   'icon' => 'fas fa-map-marked-alt', 'urutan' => 5, 'is_active' => 1],
            ['id_menu' => 6,  'nama_menu' => 'Rekap Laporan',    'url' => 'admin/laporan',    'icon' => 'fas fa-file-invoice', 'urutan' => 6, 'is_active' => 1],
            ['id_menu' => 7,  'nama_menu' => 'Log Keamanan',     'url' => 'admin/log-fraud',  'icon' => 'fas fa-shield-virus', 'urutan' => 7, 'is_active' => 1],
            ['id_menu' => 8,  'nama_menu' => 'Manajemen Kelas',  'url' => 'admin/kelas',      'icon' => 'fas fa-school', 'urutan' => 8, 'is_active' => 1],
            ['id_menu' => 9,  'nama_menu' => 'Manajemen User',   'url' => 'admin/user',       'icon' => 'fas fa-user-shield', 'urutan' => 9, 'is_active' => 1],
            ['id_menu' => 10, 'nama_menu' => 'Pengumuman',       'url' => 'admin/pengumuman', 'icon' => 'fas fa-bullhorn', 'urutan' => 10, 'is_active' => 1],
            ['id_menu' => 11, 'nama_menu' => 'Hari Libur',       'url' => 'admin/libur',      'icon' => 'fas fa-calendar-times', 'urutan' => 11, 'is_active' => 1],
            ['id_menu' => 12, 'nama_menu' => 'Jadwal Harian',    'url' => 'admin/jadwal',     'icon' => 'fas fa-clock', 'urutan' => 12, 'is_active' => 1],
            ['id_menu' => 13, 'nama_menu' => 'Pengaturan',       'url' => 'admin/pengaturan', 'icon' => 'fas fa-cogs', 'urutan' => 13, 'is_active' => 1],
        ]);
        // ========================================================
        // 9. DATA BARU: Pemetaan Role ke Menu (Role_Menus)
        // ========================================================
        $roleMenus = [];

        // ADMIN (Role 1) mendapatkan HAK AKSES PENUH ke semua menu (1 s/d 13)
        for ($i = 1; $i <= 13; $i++) {
            $roleMenus[] = ['id_role' => 1, 'id_menu' => $i];
        }

        // GURU (Role 2) hanya mendapatkan HAK AKSES OPERASIONAL (Menu 1 s/d 7)
        for ($i = 1; $i <= 7; $i++) {
            $roleMenus[] = ['id_role' => 2, 'id_menu' => $i];
        }

        $this->db->table('role_menus')->insertBatch($roleMenus);

        // 10. Aktifkan kembali pengecekan Foreign Key
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        echo "Berhasil menyinkronkan Data Utama, Role, Akun Admin & Guru, beserta Jadwal!\n";
        echo "RBAC Database-Driven Berhasil Di-seed!\n";
    }
}
