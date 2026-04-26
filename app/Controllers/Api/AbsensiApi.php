<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        helper(['geo', 'security']);
    }

    private function getSiswaAuth()
    {
        $token = str_replace('Bearer ', '', $this->request->getHeaderLine('Authorization'));
        return $this->db->table('siswa')->where('device_id', $token)->get()->getRow();
    }

    public function masuk()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Token tidak valid atau perangkat tidak dikenali.');

        $lat = $this->request->getPost('lat');
        $lon = $this->request->getPost('long');
        $is_mock = $this->request->getPost('is_mock') === 'true';
        $tanggal_hari_ini = Time::now('Asia/Jakarta')->toDateString();
        $waktu_sekarang = Time::now('Asia/Jakarta')->toTimeString();

        $pengaturan = $this->db->table('pengaturan')->where('id', 1)->get()->getRow();

        // 1. Cek Batas Waktu Absen (Tidak bisa absen masuk jika sudah lewat jam pulang)
        if ($waktu_sekarang > $pengaturan->jam_pulang) {
            return $this->failForbidden('Batas waktu absensi masuk untuk hari ini sudah berakhir.');
        }

        // 2. Cek Duplikasi
        $cek_absen = $this->db->table('absensi')->where(['siswa_id' => $siswa->id, 'tanggal' => $tanggal_hari_ini])->countAllResults();
        if ($cek_absen > 0) return $this->failResourceExists('Anda sudah melakukan absensi hari ini.');

        $jarak = hitung_jarak_haversine($pengaturan->sekolah_lat, $pengaturan->sekolah_long, $lat, $lon);

        // 3. Keamanan Anti-Fake GPS
        if ($is_mock) {
            $this->db->table('siswa')->where('id', $siswa->id)->set('fraud_count', 'fraud_count+1', false)->update();
            $siswa_updated = $this->db->table('siswa')->where('id', $siswa->id)->get()->getRow();

            if ($siswa_updated->fraud_count >= 3) {
                $this->db->table('siswa')->where('id', $siswa->id)->update(['is_blocked' => 1]);
            }

            $this->db->table('absensi')->insert([
                'siswa_id' => $siswa->id,
                'tanggal' => $tanggal_hari_ini,
                'lat_masuk' => $lat,
                'long_masuk' => $lon,
                'status' => 'Manipulasi',
                'is_fake_gps' => 1,
                'keterangan' => 'Terdeteksi Fake GPS (Mock Location)'
            ]);
            return $this->failForbidden('Sistem mendeteksi Fake GPS. Aktivitas dicatat.');
        }

        // 4. Validasi Radius
        if ($jarak > $pengaturan->radius_meter) return $this->failForbidden("Anda berada di luar jangkauan ({$jarak} meter).");

        // 5. Kalkulasi Keterlambatan (SOP Baru Tanpa Toleransi)
        $status = 'Hadir';
        $menit_telat = 0;
        $waktu_masuk_strtotime = strtotime($pengaturan->jam_masuk);

        if (strtotime($waktu_sekarang) > $waktu_masuk_strtotime) {
            $status = 'Terlambat';
            $menit_telat = round((strtotime($waktu_sekarang) - $waktu_masuk_strtotime) / 60);
        }

        // 6. Simpan Data
        $this->db->table('absensi')->insert([
            'siswa_id' => $siswa->id,
            'tanggal' => $tanggal_hari_ini,
            'waktu_masuk' => $waktu_sekarang,
            'lat_masuk' => $lat,
            'long_masuk' => $lon,
            'jarak_masuk' => $jarak,
            'menit_telat' => $menit_telat,
            'status' => $status
        ]);

        return $this->respondCreated(['status' => 'success', 'message' => "Absen masuk berhasil. Status: {$status}"]);
    }

    public function pulang()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Token tidak valid atau perangkat tidak dikenali.');

        $tanggal_hari_ini = Time::now('Asia/Jakarta')->toDateString();
        $waktu_sekarang = Time::now('Asia/Jakarta')->toTimeString();

        // 1. Cek apakah sudah waktunya jam pulang
        $pengaturan = $this->db->table('pengaturan')->where('id', 1)->get()->getRow();
        if ($waktu_sekarang < $pengaturan->jam_pulang) {
            return $this->failForbidden('Belum waktunya jam pulang.');
        }

        // 2. Cek ketersediaan data absen
        $absen = $this->db->table('absensi')->where(['siswa_id' => $siswa->id, 'tanggal' => $tanggal_hari_ini])->get()->getRow();
        if (!$absen) return $this->failNotFound('Data absen masuk hari ini tidak ditemukan.');
        if ($absen->waktu_pulang != null) return $this->failResourceExists('Anda sudah absen pulang hari ini.');

        // 3. Simpan Data Pulang
        $this->db->table('absensi')->where('id', $absen->id)->update(['waktu_pulang' => $waktu_sekarang]);
        return $this->respondUpdated(['status' => 'success', 'message' => 'Absen pulang berhasil.']);
    }

    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Token tidak valid atau perangkat tidak dikenali.');

        $riwayat = $this->db->table('absensi')->where('siswa_id', $siswa->id)->orderBy('tanggal', 'DESC')->limit(30)->get()->getResult();
        return $this->respond(['status' => 'success', 'data' => $riwayat]);
    }
}
