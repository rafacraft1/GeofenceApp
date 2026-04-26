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
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'sekolah_lat'     => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'sekolah_long'    => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'radius_meter'    => ['type' => 'INT', 'constraint' => 11],
            'jam_masuk'       => ['type' => 'TIME'],
            'jam_pulang'      => ['type' => 'TIME'],
            // Catatan: toleransi_menit telah dihapus sesuai SOP baru
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pengaturan');

        // ========================================================================
        // 3. TABEL SISWA
        // ========================================================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nis'          => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'nama_lengkap' => ['type' => 'VARCHAR', 'constraint' => 100],
            'kelas'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'device_id'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'fraud_count'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_blocked'   => ['type' => 'BOOLEAN', 'default' => 0],
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
        // 5. TABEL ABSENSI (Tergantung pada Siswa)
        // ========================================================================
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal'      => ['type' => 'DATE'],
            'waktu_masuk'  => ['type' => 'TIME', 'null' => true],
            'waktu_pulang' => ['type' => 'TIME', 'null' => true],
            'lat_masuk'    => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'long_masuk'   => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'jarak_masuk'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'menit_telat'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status'       => ['type' => 'ENUM', 'constraint' => ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa', 'Manipulasi']],
            'is_fake_gps'  => ['type' => 'BOOLEAN', 'default' => 0],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['siswa_id', 'tanggal']);
        $this->forge->createTable('absensi');

        // ========================================================================
        // 6. TABEL RIWAYAT LOKASI (Tergantung pada Siswa)
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
    }

    public function down()
    {
        // Eksekusi Drop Table secara terbalik agar Foreign Key tidak bentrok
        $this->forge->dropTable('riwayat_lokasi', true);
        $this->forge->dropTable('absensi', true);
        $this->forge->dropTable('hari_libur', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('pengaturan', true);
        $this->forge->dropTable('users', true);
    }
}
