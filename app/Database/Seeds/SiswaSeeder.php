<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');

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
        $nis_awal = 20260001;

        foreach ($list_kelas as $nama_kelas) {

            $kelasRow = $this->db->table('kelas')->where('nama_kelas', $nama_kelas)->get()->getRowArray();

            if (!$kelasRow) {
                $this->db->table('kelas')->insert([
                    'nama_kelas' => $nama_kelas,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $kelas_id = $this->db->insertID();
            } else {
                $kelas_id = $kelasRow['id_kelas'];
            }

            for ($i = 1; $i <= 3; $i++) {
                $nis = (string) $nis_awal++;

                $data_siswa[] = [
                    'nis'          => $nis,
                    'nama_siswa'   => $faker->name,
                    'kelas_id'     => $kelas_id,
                    'password'     => password_hash($nis, PASSWORD_BCRYPT),
                    'foto_profil'  => null,
                    'device_id'    => null,
                    'fraud_count'  => 0,
                    'is_blocked'   => 0,
                    'api_token'    => null,
                    'fcm_token'    => null, // SUDAH DIAKTIFKAN KEMBALI
                    'last_login'   => null, // SUDAH DIAKTIFKAN KEMBALI
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->table('siswa')->insertBatch($data_siswa);

        echo "Berhasil menyinkronkan data Kelas dan menambahkan 36 data dummy Siswa!\n";
    }
}
