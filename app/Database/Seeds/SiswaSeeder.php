<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        // Memanggil Faker dengan format lokalisasi Indonesia
        $faker = \Faker\Factory::create('id_ID');

        // Daftar kelas sesuai kebutuhan Anda
        $list_kelas = [
            'X TKJ A',
            'X TKJ B',
            'XI TKJ',
            'XII TKJ',
            'X APHPI A',
            'XI APHPI',
            'X TKR',
            'XI TKR',
            'XII TKR',
            'X APHP',
            'XI APHP',
            'XII APHP'
        ];

        $data_siswa = [];

        // Kita mulai NIS dari 20260001 agar rapi dan unik
        $nis_awal = 20260001;

        // Looping untuk setiap kelas
        foreach ($list_kelas as $kelas) {
            // Masing-masing kelas diisi 3 siswa
            for ($i = 1; $i <= 3; $i++) {
                $data_siswa[] = [
                    'nis'          => (string) $nis_awal++,
                    // Menghasilkan nama acak (tanpa gelar seperti S.Pd)
                    'nama_lengkap' => $faker->name,
                    'kelas'        => $kelas,
                    'device_id'    => null,
                    'fraud_count'  => 0,
                    'is_blocked'   => 0,
                    'api_token'    => null,
                    'last_login'   => null,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ];
            }
        }

        // Menyuntikkan seluruh data ke dalam tabel 'siswa' sekaligus (Insert Batch)
        $this->db->table('siswa')->insertBatch($data_siswa);

        echo "Berhasil menambahkan 36 data dummy siswa ke dalam database!\n";
    }
}
