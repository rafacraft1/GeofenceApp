<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;

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

        $jumlahSiswaPerKelas = 3;
        $totalSiswa = count($list_kelas) * $jumlahSiswaPerKelas;
        $currentStep = 0;

        CLI::newLine();
        CLI::write('Memulai sinkronisasi data Kelas dan pembuatan dummy Siswa...', 'cyan');

        // 0. Nonaktifkan pengecekan Foreign Key agar bisa melakukan TRUNCATE
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Kosongkan tabel siswa agar siap di-seed ulang tanpa bentrok NIS
        $this->db->table('siswa')->truncate();

        foreach ($list_kelas as $nama_kelas) {

            // Cek apakah kelas sudah ada, jika tidak, buatkan (Upsert Logic)
            $kelasRow = $this->db->table('kelas')->where('nama_kelas', $nama_kelas)->get()->getRowArray();
            if (!$kelasRow) {
                $this->db->table('kelas')->insert([
                    'nama_kelas' => $nama_kelas,
                    'wali_kelas' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $kelas_id = $this->db->insertID();
            } else {
                $kelas_id = (int) $kelasRow['id_kelas'];
            }

            for ($i = 1; $i <= $jumlahSiswaPerKelas; $i++) {
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
                    'fcm_token'    => null,
                    'last_login'   => null,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ];

                $currentStep++;
                CLI::showProgress($currentStep, $totalSiswa);
            }
        }

        CLI::newLine();
        CLI::write('Menyimpan data masal ke database...', 'yellow');

        $this->db->table('siswa')->insertBatch($data_siswa);

        // 2. Aktifkan kembali pengecekan Foreign Key
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        CLI::newLine();
        CLI::write("Berhasil! $totalSiswa data dummy Siswa telah ditambahkan secara menyeluruh.", 'green');
        CLI::newLine();
    }
}
