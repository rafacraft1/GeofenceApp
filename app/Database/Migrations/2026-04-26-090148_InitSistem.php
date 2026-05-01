<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitSistem extends Migration
{
    public function up()
    {
        // ========================================================================
        // 1. TABEL USERS (Admin & Guru)
        // ========================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username'      => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'nama_lengkap'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'role'          => ['type' => 'ENUM', 'constraint' => ['Superadmin', 'Guru'], 'default' => 'Guru'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');

        // ========================================================================
        // 2. TABEL PENGATURAN (Geofence & Waktu)
        // ========================================================================
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'latitude_sekolah'  => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude_sekolah' => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'radius_meter'      => ['type' => 'INT', 'constraint' => 11, 'default' => 50],
            'firebase_url'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'jam_masuk'         => ['type' => 'TIME'],
            'jam_pulang'        => ['type' => 'TIME'],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pengaturan');

        // ========================================================================
        // 3. TABEL SISWA (Ditambah fcm_token)
        // ========================================================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nis'          => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'nama_lengkap' => ['type' => 'VARCHAR', 'constraint' => 100],
            'kelas'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'foto'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device_id'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'fraud_count'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_blocked'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'api_token'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'fcm_token'    => ['type' => 'TEXT', 'null' => true], // <-- TAMBAHAN BARU
            'last_login'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('siswa');

        // ========================================================================
        // 4. TABEL HARI LIBUR
        // ========================================================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tanggal'    => ['type' => 'DATE', 'unique' => true],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('hari_libur');

        // ========================================================================
        // 5. TABEL ABSENSI
        // ========================================================================
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal'      => ['type' => 'DATE'],
            'waktu_masuk'  => ['type' => 'TIME', 'null' => true],
            'waktu_pulang' => ['type' => 'TIME', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Manipulasi'], 'default' => 'Hadir'],
            'menit_telat'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'foto_masuk'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lat_masuk'    => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'lon_masuk'    => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'foto_pulang'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lat_pulang'   => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'lon_pulang'   => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'keterangan'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_fake_gps'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['siswa_id', 'tanggal']);
        $this->forge->createTable('absensi');

        // ========================================================================
        // 6. TABEL RIWAYAT LOKASI
        // ========================================================================
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'latitude'    => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude'   => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'waktu_rekam' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('riwayat_lokasi');

        // ========================================================================
        // 7. TABEL PENGUMUMAN
        // ========================================================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'judul'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'isi'        => ['type' => 'TEXT'],
            'tipe'       => ['type' => 'ENUM', 'constraint' => ['Info', 'Penting', 'Libur'], 'default' => 'Info'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pengumuman');

        // ========================================================================
        // 8. TABEL PENGAJUAN RESET DEVICE
        // ========================================================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'alasan'     => ['type' => 'TEXT'],
            'status'     => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengajuan_reset');
    }

    public function down()
    {
        $this->forge->dropTable('pengajuan_reset', true);
        $this->forge->dropTable('riwayat_lokasi', true);
        $this->forge->dropTable('absensi', true);
        $this->forge->dropTable('hari_libur', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('pengaturan', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('pengumuman', true);
    }
}
