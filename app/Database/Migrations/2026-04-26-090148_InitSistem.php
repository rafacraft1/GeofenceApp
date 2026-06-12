<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitSistem extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_role'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_role'   => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => true],
            'warna_badge' => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'gray'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_role', true);
        $this->forge->createTable('roles');

        $this->forge->addField([
            'id_user'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_lengkap'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'username'      => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'foto'          => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'role_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id_role', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('users');

        // STRUKTUR BARU: ZONA ABSENSI (Tanpa Waktu)
        $this->forge->addField([
            'id_zona'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_zona'        => ['type' => 'VARCHAR', 'constraint' => '100'],
            'latitude'         => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude'        => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'radius'           => ['type' => 'INT', 'constraint' => 11, 'default' => 50],
            'is_default'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_zona', true);
        $this->forge->createTable('zona_absensi');

        // STRUKTUR BARU: JADWAL KHUSUS PER ZONA (Senin-Minggu)
        $this->forge->addField([
            'id_zona_jadwal'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'zona_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kode_hari'        => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true],
            'nama_hari'        => ['type' => 'VARCHAR', 'constraint' => '20'],
            'waktu_buka_absen' => ['type' => 'TIME', 'default' => '05:00:00'],
            'jam_masuk'        => ['type' => 'TIME', 'default' => '06:30:00'],
            'jam_pulang'       => ['type' => 'TIME', 'default' => '15:00:00'],
            'is_libur'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id_zona_jadwal', true);
        $this->forge->addKey('zona_id');
        $this->forge->addForeignKey('zona_id', 'zona_absensi', 'id_zona', 'CASCADE', 'CASCADE');
        $this->forge->createTable('zona_jadwal');

        $this->forge->addField([
            'id_kelas'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_kelas'    => ['type' => 'VARCHAR', 'constraint' => '50'],
            'wali_kelas_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'zona_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kelas', true);
        $this->forge->addKey('wali_kelas_id');
        $this->forge->addKey('zona_id');
        $this->forge->addForeignKey('wali_kelas_id', 'users', 'id_user', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('zona_id', 'zona_absensi', 'id_zona', 'CASCADE', 'SET NULL');
        $this->forge->createTable('kelas');

        $this->forge->addField([
            'id_siswa'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kelas_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'zona_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nis'           => ['type' => 'VARCHAR', 'constraint' => '20', 'unique' => true],
            'nama_siswa'    => ['type' => 'VARCHAR', 'constraint' => '100'],
            'password'      => ['type' => 'VARCHAR', 'constraint' => '255'],
            'foto_profil'   => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'device_id'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'api_token'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'fcm_token'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'lat_terakhir'  => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'long_terakhir' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'last_login'    => ['type' => 'DATETIME', 'null' => true],
            'is_blocked'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'fraud_count'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa', true);
        $this->forge->addKey('api_token');
        $this->forge->addKey('fcm_token');
        $this->forge->addKey('kelas_id');
        $this->forge->addKey('zona_id');
        $this->forge->addForeignKey('kelas_id', 'kelas', 'id_kelas', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('zona_id', 'zona_absensi', 'id_zona', 'CASCADE', 'SET NULL');
        $this->forge->createTable('siswa');

        $this->forge->addField([
            'id_pengaturan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_aplikasi' => ['type' => 'VARCHAR', 'constraint' => '100', 'default' => 'GeofenceApp'],
            'nama_sekolah'  => ['type' => 'VARCHAR', 'constraint' => '150', 'default' => 'Nama Sekolah'],
            'firebase_url'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_pengaturan', true);
        $this->forge->createTable('pengaturan');

        $this->forge->addField([
            'id_absensi'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kelas_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tanggal'     => ['type' => 'DATE'],
            'jam_masuk'   => ['type' => 'TIME', 'null' => true],
            'jam_pulang'  => ['type' => 'TIME', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['Hadir', 'Sakit', 'Izin', 'Dispensasi', 'Alpa', 'Terlambat', 'Manipulasi', 'Libur'], 'default' => 'Hadir'],
            'foto_masuk'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'foto_pulang' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'lat_masuk'   => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'long_masuk'  => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'lat_pulang'  => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'long_pulang' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'is_fake_gps' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'menit_telat' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'keterangan'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_absensi', true);
        $this->forge->addKey('siswa_id');
        $this->forge->addKey('kelas_id');
        $this->forge->addKey('tanggal');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('kelas_id', 'kelas', 'id_kelas', 'CASCADE', 'SET NULL');
        $this->forge->createTable('absensi');

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

        // JADWAL GLOBAL (Hanya menyimpan status aktif/libur akhir pekan)
        $this->forge->addField([
            'id_jadwal'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_hari'  => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true],
            'nama_hari'  => ['type' => 'VARCHAR', 'constraint' => '20'],
            'is_libur'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id_jadwal', true);
        $this->forge->addKey('kode_hari');
        $this->forge->createTable('jadwal_absen');

        $this->forge->addField([
            'id_libur'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tanggal'    => ['type' => 'DATE'],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_libur', true);
        $this->forge->addKey('tanggal');
        $this->forge->createTable('hari_libur');

        $this->forge->addField([
            'id_izin'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal_mulai'   => ['type' => 'DATE'],
            'tanggal_selesai' => ['type' => 'DATE'],
            'jenis'           => ['type' => 'ENUM', 'constraint' => ['Sakit', 'Izin', 'Dispensasi'], 'default' => 'Sakit'],
            'alasan'          => ['type' => 'TEXT'],
            'bukti_foto'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Pending', 'Approved', 'Rejected'], 'default' => 'Pending'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_izin', true);
        $this->forge->addKey('siswa_id');
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengajuan_izin');

        $this->forge->addField([
            'id_log'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipe_fraud'  => ['type' => 'VARCHAR', 'constraint' => '50'],
            'lat_fraud'   => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'long_fraud'  => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_log', true);
        $this->forge->addKey('siswa_id');
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_fraud');

        $this->forge->addField([
            'id_menu'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_menu'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'url'        => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'icon'       => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'urutan'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addKey('id_menu', true);
        $this->forge->createTable('menus');

        $this->forge->addField([
            'id_role' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_menu' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addKey(['id_role', 'id_menu'], true);
        $this->forge->addForeignKey('id_role', 'roles', 'id_role', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_menu', 'menus', 'id_menu', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_menus');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        $this->forge->dropTable('role_menus', true);
        $this->forge->dropTable('menus', true);
        $this->forge->dropTable('log_fraud', true);
        $this->forge->dropTable('pengajuan_izin', true);
        $this->forge->dropTable('hari_libur', true);
        $this->forge->dropTable('jadwal_absen', true);
        $this->forge->dropTable('pengumuman', true);
        $this->forge->dropTable('absensi', true);
        $this->forge->dropTable('pengaturan', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('kelas', true);
        $this->forge->dropTable('zona_jadwal', true);
        $this->forge->dropTable('zona_absensi', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
