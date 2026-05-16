<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitSistem extends Migration
{
    public function up()
    {
        // 0. Tabel Master Roles
        $this->forge->addField([
            'id_role'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_role'  => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_role', true);
        $this->forge->createTable('roles');

        // 1. Tabel Users
        $this->forge->addField([
            'id_user'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_lengkap'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'username'      => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'role_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id_role', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('users');

        // 2. Tabel Kelas
        $this->forge->addField([
            'id_kelas'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_kelas'    => ['type' => 'VARCHAR', 'constraint' => '50'],
            'wali_kelas_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kelas', true);
        $this->forge->addKey('wali_kelas_id');
        $this->forge->addForeignKey('wali_kelas_id', 'users', 'id_user', 'CASCADE', 'SET NULL');
        $this->forge->createTable('kelas');

        // 3. Tabel Siswa
        $this->forge->addField([
            'id_siswa'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kelas_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nis'           => ['type' => 'VARCHAR', 'constraint' => '20', 'unique' => true],
            'nama_siswa'    => ['type' => 'VARCHAR', 'constraint' => '100'],
            'password'      => ['type' => 'VARCHAR', 'constraint' => '255'],
            'foto_profil'   => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'device_id'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'api_token'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'fcm_token'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'lat_terakhir'  => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'long_terakhir' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'last_login'    => ['type' => 'DATETIME', 'null' => true],
            'is_blocked'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'fraud_count'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa', true);
        $this->forge->addKey('api_token');
        $this->forge->addKey('kelas_id');
        $this->forge->addForeignKey('kelas_id', 'kelas', 'id_kelas', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('siswa');

        // 4. Tabel Pengaturan 
        $this->forge->addField([
            'id_pengaturan'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'latitude_sekolah'  => ['type' => 'VARCHAR', 'constraint' => '100'],
            'longitude_sekolah' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'radius_meter'      => ['type' => 'INT', 'constraint' => 11],
            'firebase_url'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_pengaturan', true);
        $this->forge->createTable('pengaturan');

        // 5. Tabel Absensi (PERUBAHAN MAJOR: Penambahan kelas_id)
        $this->forge->addField([
            'id_absensi'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kelas_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // TAMBAHAN UNTUK HISTORICAL SNAPSHOT
            'tanggal'     => ['type' => 'DATE'],
            'jam_masuk'   => ['type' => 'TIME', 'null' => true],
            'jam_pulang'  => ['type' => 'TIME', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['Hadir', 'Sakit', 'Izin', 'Dispensasi', 'Alpa', 'Terlambat', 'Manipulasi', 'Libur'], 'default' => 'Hadir'],
            'foto_masuk'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'foto_pulang' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
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
        $this->forge->addKey('siswa_id');
        $this->forge->addKey('kelas_id');
        $this->forge->addKey('tanggal');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        // Jika kelas dihapus, biarkan data laporan absen historis tetap ada (Set Null)
        $this->forge->addForeignKey('kelas_id', 'kelas', 'id_kelas', 'CASCADE', 'SET NULL');
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

        // 7. Tabel Jadwal Absen
        $this->forge->addField([
            'id_jadwal'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_hari'  => ['type' => 'INT', 'constraint' => 1],
            'nama_hari'  => ['type' => 'VARCHAR', 'constraint' => '20'],
            'jam_masuk'  => ['type' => 'TIME', 'null' => true],
            'jam_pulang' => ['type' => 'TIME', 'null' => true],
            'is_libur'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id_jadwal', true);
        $this->forge->addKey('kode_hari');
        $this->forge->createTable('jadwal_absen');

        // 8. Tabel Hari Libur
        $this->forge->addField([
            'id_libur'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tanggal'    => ['type' => 'DATE'],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_libur', true);
        $this->forge->addKey('tanggal');
        $this->forge->createTable('hari_libur');

        // 9. Tabel Pengajuan Izin
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

        // 10. Tabel Log Fraud / Pelanggaran
        $this->forge->addField([
            'id_log'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'siswa_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipe_fraud'  => ['type' => 'VARCHAR', 'constraint' => '50'],
            'lat_fraud'   => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'long_fraud'  => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_log', true);
        $this->forge->addKey('siswa_id');
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_fraud');

        // 11. TABEL MENUS
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

        // 12. TABEL ROLE_MENUS
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
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('kelas', true);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
