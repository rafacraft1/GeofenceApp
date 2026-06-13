<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitDataSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        $this->db->table('role_menus')->truncate();
        $this->db->table('menus')->truncate();
        $this->db->table('roles')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('zona_jadwal')->truncate();
        $this->db->table('zona_absensi')->truncate();
        $this->db->table('pengaturan')->truncate();
        $this->db->table('kelas')->truncate();
        $this->db->table('hari_libur')->truncate();

        $this->db->table('roles')->insertBatch([
            ['id_role' => 1, 'nama_role' => 'Admin', 'warna_badge' => 'indigo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id_role' => 2, 'nama_role' => 'Guru',  'warna_badge' => 'emerald', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        ]);

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

        $this->db->table('zona_absensi')->insert([
            'id_zona'          => 1,
            'nama_zona'        => 'Sekolah Pusat (Default)',
            'latitude'         => -6.20000000,
            'longitude'        => 106.81666600,
            'radius'           => 50,
            'is_default'       => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $jadwalDefaultZona = [];
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        foreach ($hari as $kode => $nama) {
            $jadwalDefaultZona[] = [
                'zona_id'          => 1,
                'kode_hari'        => $kode,
                'nama_hari'        => $nama,
                'waktu_buka_absen' => '05:00:00',
                'jam_masuk'        => '06:30:00',
                'jam_pulang'       => ($kode === 5) ? '11:30:00' : '15:00:00',
                'is_libur'         => ($kode >= 6) ? 1 : 0
            ];
        }
        $this->db->table('zona_jadwal')->insertBatch($jadwalDefaultZona);

        $this->db->table('kelas')->insertBatch([
            ['id_kelas' => 1, 'nama_kelas' => '10-A', 'wali_kelas_id' => 2, 'zona_id' => null, 'created_at' => date('Y-m-d H:i:s')],
            ['id_kelas' => 2, 'nama_kelas' => '10-B', 'wali_kelas_id' => 3, 'zona_id' => null, 'created_at' => date('Y-m-d H:i:s')],
            ['id_kelas' => 3, 'nama_kelas' => '11-A', 'wali_kelas_id' => null, 'zona_id' => null, 'created_at' => date('Y-m-d H:i:s')],
        ]);

        $this->db->table('pengaturan')->insert([
            'id_pengaturan' => 1,
            'nama_aplikasi' => 'GeofenceApp',
            'nama_sekolah'  => 'SMKN 1 TGB',
            'updated_at'    => date('Y-m-d H:i:s')
        ]);

        $this->db->table('menus')->insertBatch([
            ['id_menu' => 1,  'nama_menu' => 'Dashboard',         'url' => 'admin/dashboard',  'icon' => 'fas fa-home',               'urutan' => 1,  'is_active' => 1],
            ['id_menu' => 2,  'nama_menu' => 'Live Radar',        'url' => 'admin/tracking',   'icon' => 'fas fa-map-marked-alt',     'urutan' => 2,  'is_active' => 1],
            ['id_menu' => 3,  'nama_menu' => 'Absensi Harian',    'url' => 'admin/absensi',    'icon' => 'fas fa-clipboard-check',    'urutan' => 3,  'is_active' => 1],
            ['id_menu' => 4,  'nama_menu' => 'Izin & Dispensasi', 'url' => 'admin/izin',       'icon' => 'fas fa-envelope-open-text', 'urutan' => 4,  'is_active' => 1],
            ['id_menu' => 5,  'nama_menu' => 'Log Keamanan',      'url' => 'admin/log-fraud',  'icon' => 'fas fa-shield-alt',         'urutan' => 5,  'is_active' => 1],
            ['id_menu' => 6,  'nama_menu' => 'Data Guru',         'url' => 'admin/user',       'icon' => 'fas fa-user-tie',           'urutan' => 6,  'is_active' => 1],
            ['id_menu' => 7,  'nama_menu' => 'Data Kelas',        'url' => 'admin/kelas',      'icon' => 'fas fa-chalkboard',         'urutan' => 7,  'is_active' => 1],
            ['id_menu' => 8,  'nama_menu' => 'Data Siswa',        'url' => 'admin/siswa',      'icon' => 'fas fa-users',              'urutan' => 8,  'is_active' => 1],
            ['id_menu' => 9,  'nama_menu' => 'Mutasi Kelas',      'url' => 'admin/mutasi',     'icon' => 'fas fa-exchange-alt',       'urutan' => 9,  'is_active' => 1],
            ['id_menu' => 10, 'nama_menu' => 'Zona Absensi',      'url' => 'admin/zona',       'icon' => 'fas fa-map-marker-alt',     'urutan' => 10, 'is_active' => 1],
            ['id_menu' => 12, 'nama_menu' => 'Hari Libur',        'url' => 'admin/libur',      'icon' => 'fas fa-calendar-times',     'urutan' => 12, 'is_active' => 1],
            ['id_menu' => 13, 'nama_menu' => 'Pengumuman',        'url' => 'admin/pengumuman', 'icon' => 'fas fa-bullhorn',           'urutan' => 13, 'is_active' => 1],
            ['id_menu' => 14, 'nama_menu' => 'Laporan Rekap',     'url' => 'admin/laporan',    'icon' => 'fas fa-file-excel',         'urutan' => 14, 'is_active' => 1],
            ['id_menu' => 15, 'nama_menu' => 'Pengaturan Sistem', 'url' => 'admin/pengaturan', 'icon' => 'fas fa-cogs',               'urutan' => 15, 'is_active' => 1],
            ['id_menu' => 16, 'nama_menu' => 'Audit Trail',       'url' => 'admin/audit-log',  'icon' => 'fas fa-shield-halved',      'urutan' => 16, 'is_active' => 1],
        ]);

        $roleMenus = [];

        // Admin mendapatkan seluruh akses menu yang tersedia
        $adminMenus = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16];
        foreach ($adminMenus as $menuId) {
            $roleMenus[] = ['id_role' => 1, 'id_menu' => $menuId];
        }

        // Guru hanya mendapatkan akses menu tertentu
        $aksesGuru = [1, 2, 3, 4, 5, 8, 13, 14];
        foreach ($aksesGuru as $menuId) {
            $roleMenus[] = ['id_role' => 2, 'id_menu' => $menuId];
        }

        $this->db->table('role_menus')->insertBatch($roleMenus);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
