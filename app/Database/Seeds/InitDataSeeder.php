<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitDataSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        $this->db->table('role_menus')->truncate();
        $this->db->table('menus')->truncate();
        $this->db->table('roles')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('pengaturan')->truncate();
        $this->db->table('kelas')->truncate();
        $this->db->table('jadwal_absen')->truncate();
        $this->db->table('hari_libur')->truncate();

        // 2. Data Master Role (Menyisipkan default warna badge menggunakan class text Tailwind CSS)
        $this->db->table('roles')->insertBatch([
            ['id_role' => 1, 'nama_role' => 'Admin', 'warna_badge' => 'indigo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id_role' => 2, 'nama_role' => 'Guru',  'warna_badge' => 'emerald', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        ]);

        // 3. Data Users 
        // Menggunakan Konstanta Default Password (Meskipun di seeder, kita siapkan struktur rapi)
        $passwordDefault = password_hash('123456', PASSWORD_BCRYPT);
        $this->db->table('users')->insertBatch([
            [
                'id_user'       => 1,
                'nama_lengkap'  => 'Administrator Sistem',
                'username'      => 'admin',
                'password_hash' => $passwordDefault,
                'foto'          => null,
                'role_id'       => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'id_user'       => 2,
                'nama_lengkap'  => 'Budi Santoso, S.Pd',
                'username'      => 'guru1',
                'password_hash' => $passwordDefault,
                'foto'          => null,
                'role_id'       => 2,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'id_user'       => 3,
                'nama_lengkap'  => 'Siti Aminah, M.Pd',
                'username'      => 'guru2',
                'password_hash' => $passwordDefault,
                'foto'          => null,
                'role_id'       => 2,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'id_user'       => 4,
                'nama_lengkap'  => 'Drs. Ahmad Dahlan',
                'username'      => 'guru3',
                'password_hash' => $passwordDefault,
                'foto'          => null,
                'role_id'       => 2,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]
        ]);

        // 4. Data Kelas beserta Relasi Wali Kelas
        $this->db->table('kelas')->insertBatch([
            ['id_kelas' => 1, 'nama_kelas' => '10-A', 'wali_kelas_id' => 2, 'created_at' => date('Y-m-d H:i:s')],
            ['id_kelas' => 2, 'nama_kelas' => '10-B', 'wali_kelas_id' => 3, 'created_at' => date('Y-m-d H:i:s')],
            ['id_kelas' => 3, 'nama_kelas' => '11-A', 'wali_kelas_id' => null, 'created_at' => date('Y-m-d H:i:s')],
        ]);

        // 5. Data Pengaturan Sekolah Default (Menyisipkan nama_aplikasi dan nama_sekolah)
        $this->db->table('pengaturan')->insert([
            'id_pengaturan'     => 1,
            'nama_aplikasi'     => 'GeofenceApp', // Nama default aplikasi web
            'nama_sekolah'      => 'SMKN 1 TGB',  // Nama default identitas institusi
            'latitude_sekolah'  => '-6.200000',
            'longitude_sekolah' => '106.816666',
            'radius_meter'      => 50,
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        // 6. Data Jadwal Absen Default
        $jadwal = [];
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
        foreach ($hari as $kode => $nama) {
            $jadwal[] = [
                'kode_hari'  => $kode,
                'nama_hari'  => $nama,
                'jam_masuk'  => '07:00:00',
                'jam_pulang' => '15:00:00',
                'is_libur'   => 0
            ];
        }
        $jadwal[] = ['kode_hari' => 6, 'nama_hari' => 'Sabtu', 'jam_masuk' => null, 'jam_pulang' => null, 'is_libur' => 1];
        $jadwal[] = ['kode_hari' => 7, 'nama_hari' => 'Minggu', 'jam_masuk' => null, 'jam_pulang' => null, 'is_libur' => 1];

        $this->db->table('jadwal_absen')->insertBatch($jadwal);

        // 7. TABEL MENUS
        $this->db->table('menus')->insertBatch([
            ['id_menu' => 1,  'nama_menu' => 'Dashboard',        'url' => 'admin/dashboard',  'icon' => 'fas fa-home', 'urutan' => 1, 'is_active' => 1],
            ['id_menu' => 2,  'nama_menu' => 'Data Siswa',       'url' => 'admin/siswa',      'icon' => 'fas fa-users', 'urutan' => 2, 'is_active' => 1],
            ['id_menu' => 3,  'nama_menu' => 'Absensi Harian',   'url' => 'admin/absensi',    'icon' => 'fas fa-clipboard-check', 'urutan' => 3, 'is_active' => 1],
            ['id_menu' => 4,  'nama_menu' => 'Izin & Sakit',     'url' => 'admin/izin',       'icon' => 'fas fa-envelope-open-text', 'urutan' => 4, 'is_active' => 1],
            ['id_menu' => 5,  'nama_menu' => 'Live Radar',       'url' => 'admin/tracking',   'icon' => 'fas fa-map-marked-alt', 'urutan' => 5, 'is_active' => 1],
            ['id_menu' => 6,  'nama_menu' => 'Log Fraud',        'url' => 'admin/log-fraud',  'icon' => 'fas fa-shield-alt', 'urutan' => 6, 'is_active' => 1],
            ['id_menu' => 7,  'nama_menu' => 'Laporan Rekap',    'url' => 'admin/laporan',    'icon' => 'fas fa-file-excel', 'urutan' => 7, 'is_active' => 1],
            ['id_menu' => 8,  'nama_menu' => 'Data User/Guru',   'url' => 'admin/user',       'icon' => 'fas fa-user-tie', 'urutan' => 8, 'is_active' => 1],
            ['id_menu' => 9,  'nama_menu' => 'Data Kelas',       'url' => 'admin/kelas',      'icon' => 'fas fa-chalkboard', 'urutan' => 9, 'is_active' => 1],
            ['id_menu' => 10, 'nama_menu' => 'Pengumuman',       'url' => 'admin/pengumuman', 'icon' => 'fas fa-bullhorn', 'urutan' => 10, 'is_active' => 1],
            ['id_menu' => 11, 'nama_menu' => 'Hari Libur',       'url' => 'admin/libur',      'icon' => 'fas fa-calendar-times', 'urutan' => 11, 'is_active' => 1],
            ['id_menu' => 12, 'nama_menu' => 'Jadwal Harian',    'url' => 'admin/jadwal',     'icon' => 'fas fa-clock', 'urutan' => 12, 'is_active' => 1],
            ['id_menu' => 13, 'nama_menu' => 'Pengaturan',       'url' => 'admin/pengaturan', 'icon' => 'fas fa-cogs', 'urutan' => 13, 'is_active' => 1],
            ['id_menu' => 14, 'nama_menu' => 'Mutasi Kelas',     'url' => 'admin/mutasi',     'icon' => 'fas fa-exchange-alt', 'urutan' => 14, 'is_active' => 1],
        ]);

        // 8. Pemetaan Role ke Menu (Role_Menus)
        $roleMenus = [];
        for ($i = 1; $i <= 14; $i++) {
            $roleMenus[] = ['id_role' => 1, 'id_menu' => $i];
        }
        for ($i = 1; $i <= 7; $i++) {
            $roleMenus[] = ['id_role' => 2, 'id_menu' => $i];
        }

        $this->db->table('role_menus')->insertBatch($roleMenus);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
