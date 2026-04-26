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
            'sekolah_lat'     => -6.20000000,
            'sekolah_long'    => 106.81666600,
            'radius_meter'    => 50,
            'jam_masuk'       => '07:00:00',
            'jam_pulang'      => '15:00:00',
            'updated_at'      => date('Y-m-d H:i:s')
        ]);
    }
}
