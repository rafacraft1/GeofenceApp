<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Akun Superadmin
        $this->db->table('users')->insert([
            'username'      => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'nama_lengkap'  => 'Administrator Sistem',
            'role'          => 'Superadmin',
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        // 2. Data Pengaturan Default
        $this->db->table('pengaturan')->insert([
            'latitude_sekolah'  => '-6.20000000',
            'longitude_sekolah' => '106.81666600',
            'radius_meter'      => 50,
            'firebase_url'      => null,
            'jam_masuk'         => '06:30:00',
            'jam_pulang'        => '14:00:00',
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        // 3. Data Kelas Dummy (Penting untuk menguji fitur kelas yang baru kita buat)
        $this->db->table('kelas')->insertBatch([
            ['nama_kelas' => 'XII RPL 1', 'wali_kelas' => 'Budi Santoso, S.Kom', 'created_at' => date('Y-m-d H:i:s')],
            ['nama_kelas' => 'XII RPL 2', 'wali_kelas' => 'Siti Aminah, M.Pd', 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }
}
