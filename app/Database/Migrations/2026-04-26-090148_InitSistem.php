<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitSistem extends Migration
{
    public function up()
    {
        // 1. Tabel Kelas
        $this->forge->addField([
            'id_kelas'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_kelas' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'wali_kelas' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kelas', true);
        $this->forge->createTable('kelas');

        // 2. Tabel Siswa
        $this->forge->addField([
            'id_siswa'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kelas_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nis'         => ['type' => 'VARCHAR', 'constraint' => '20', 'unique' => true],
            'nama_siswa'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'password'    => ['type' => 'VARCHAR', 'constraint' => '255'],
            'foto_profil' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'device_id'   => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'api_token'   => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'fcm_token'   => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'last_login'  => ['type' => 'DATETIME', 'null' => true],
            'is_blocked'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'fraud_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa', true);
        $this->forge->addForeignKey('kelas_id', 'kelas', 'id_kelas', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('siswa');

        // 3. Tabel Users
        $this->forge->addField([
            'id_user'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_lengkap'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'username'      => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'role'          => ['type' => 'VARCHAR', 'constraint' => '50'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('users');

        // 4. Tabel Pengaturan
        $this->forge->addField([
            'id_pengaturan'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'latitude_sekolah'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'longitude_sekolah' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'radius_meter'      => ['type' => 'INT', 'constraint' => 11],
            'firebase_url'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'jam_masuk'         => ['type' => 'TIME'],
            'jam_pulang'        => ['type' => 'TIME'],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_pengaturan', true);
        $this->forge->createTable('pengaturan');

        // 5. Tabel Absensi (DITAMBAHKAN KOLOM FOTO)
        $this->forge->addField([
            'id_absensi'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal'     => ['type' => 'DATE'],
            'jam_masuk'   => ['type' => 'TIME', 'null' => true],
            'jam_pulang'  => ['type' => 'TIME', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Terlambat', 'Manipulasi'], 'default' => 'Hadir'],
            'foto_masuk'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true], // TAMBAHAN BARU
            'foto_pulang' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true], // TAMBAHAN BARU
            'lat_masuk'   => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'long_masuk'  => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'lat_pulang'  => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'long_pulang' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'is_fake_gps' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'menit_telat' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'keterangan'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_absensi', true);
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absensi');

        // 6. Tabel Pengumuman
        $this->forge->addField([
            'id_pengumuman' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'judul'         => ['type' => 'VARCHAR', 'constraint' => '150'],
            'isi'           => ['type' => 'TEXT'],
            'tipe'          => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'Info'],
            'gambar'        => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_pengumuman', true);
        $this->forge->createTable('pengumuman');
    }

    public function down()
    {
        $this->forge->dropTable('pengumuman', true);
        $this->forge->dropTable('absensi', true);
        $this->forge->dropTable('pengaturan', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('kelas', true);
    }
}
