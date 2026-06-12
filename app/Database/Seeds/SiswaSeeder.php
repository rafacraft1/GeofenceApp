<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        $this->db->table('log_fraud')->truncate();
        $this->db->table('pengajuan_izin')->truncate();
        $this->db->table('absensi')->truncate();
        $this->db->table('siswa')->truncate();

        $siswaData = [];
        $nisAwal   = 2026001;

        $namaKelas10A = ['Agus Prayitno', 'Bunga Citra', 'Candra Wijaya', 'Dewi Lestari', 'Eko Saputro'];
        $namaKelas10B = ['Fajar Siddiq', 'Gita Wirjawan', 'Hadi Sucipto', 'Intan Nuraini', 'Joko Santoso'];
        $namaKelas11A = ['Kartika Putri', 'Lukman Hakim', 'Maulana Yusuf', 'Nina Zatulini', 'Oka Antara'];

        $generateSiswa = function (array $namaList, int $kelasId) use (&$nisAwal, &$siswaData) {
            foreach ($namaList as $nama) {
                $nis = (string) $nisAwal++;
                $siswaData[] = [
                    'kelas_id'      => $kelasId,
                    'zona_id'       => null,
                    'nis'           => $nis,
                    'nama_siswa'    => $nama,
                    'password'      => password_hash($nis, PASSWORD_BCRYPT),
                    'foto_profil'   => null,
                    'device_id'     => null,
                    'api_token'     => null,
                    'fcm_token'     => null,
                    'lat_terakhir'  => null,
                    'long_terakhir' => null,
                    'last_login'    => null,
                    'is_blocked'    => 0,
                    'fraud_count'   => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ];
            }
        };

        $generateSiswa($namaKelas10A, 1);
        $generateSiswa($namaKelas10B, 2);
        $generateSiswa($namaKelas11A, 3);

        $this->db->table('siswa')->insertBatch($siswaData);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
