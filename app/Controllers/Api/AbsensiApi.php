<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
// Baris 'use CodeIgniter\Database\BaseConnection;' sudah dihapus

class AbsensiApi extends ResourceController
{
    protected $format = 'json';

    /**
     * Properti untuk koneksi database
     * @var \CodeIgniter\Database\BaseConnection 
     */
    protected $db; // <-- Menggunakan penulisan alamat lengkap di komentar

    public function __construct()
    {
        // Inisialisasi koneksi database
        $this->db = \Config\Database::connect();

        // Memuat helper untuk fungsi geofence dan keamanan
        helper(['geo', 'security']);
    }

    /**
     * Helper internal untuk memvalidasi api_token siswa
     */
    private function getSiswaAuth()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = \str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        return $this->db->table('siswa')->where('api_token', $token)->get()->getRow();
    }

    // ==========================================
    // 1. API PRESENSI MASUK
    // ==========================================
    public function masuk()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi berakhir atau token tidak valid.');

        $lat     = $this->request->getPost('lat');
        $lon     = $this->request->getPost('long');
        $is_mock = $this->request->getPost('is_mock') === 'true';
        $foto    = $this->request->getPost('foto');

        if (!$lat || !$lon || !$foto) {
            return $this->failValidationErrors('Koordinat dan foto selfie wajib dikirim.');
        }

        // --- VALIDASI KEAMANAN (FAKE GPS) ---
        if ($is_mock) {
            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id = ?", [$siswa->id]);

            $siswa_cek = $this->db->table('siswa')->where('id', $siswa->id)->get()->getRow();
            $sisa = 3 - $siswa_cek->fraud_count;

            if ($siswa_cek->fraud_count >= 3) {
                $this->db->table('siswa')->where('id', $siswa->id)->update(['is_blocked' => 1]);
                return $this->failUnauthorized('AKUN DIBLOKIR! Anda terdeteksi menggunakan Fake GPS sebanyak 3 kali.');
            }

            return $this->failForbidden("Fake GPS Terdeteksi! Percobaan Anda tersisa $sisa kali lagi sebelum diblokir.");
        }

        $sekarang       = Time::now('Asia/Jakarta');
        $tanggal_ini    = $sekarang->toDateString();
        $pengaturan     = $this->db->table('pengaturan')->where('id', 1)->get()->getRow();

        // --- VALIDASI WAKTU PRESENSI MASUK ---
        $jam_masuk_pukul  = Time::parse($tanggal_ini . ' ' . $pengaturan->jam_masuk, 'Asia/Jakarta');
        $jam_pulang_pukul = Time::parse($tanggal_ini . ' ' . $pengaturan->jam_pulang, 'Asia/Jakarta');

        $buka_masuk  = $jam_masuk_pukul->subMinutes(45);
        $tutup_masuk = $jam_pulang_pukul->subMinutes(60);

        if ($sekarang->isBefore($buka_masuk)) {
            return $this->failForbidden('Presensi masuk belum dibuka. Dibuka pukul ' . $buka_masuk->format('H:i'));
        }

        if ($sekarang->isAfter($tutup_masuk)) {
            return $this->failForbidden('Batas waktu presensi masuk sudah lewat (Maksimal 1 jam sebelum jam pulang).');
        }

        // --- VALIDASI RADIUS GEOFENCE ---
        $jarak = \hitung_jarak_haversine($lat, $lon, $pengaturan->latitude_sekolah, $pengaturan->longitude_sekolah);
        if ($jarak > $pengaturan->radius_meter) {
            return $this->fail('Anda berada ' . \round($jarak) . 'm dari sekolah. Radius maksimal: ' . $pengaturan->radius_meter . 'm.');
        }

        // --- CEK DOUBLE INPUT ---
        $cek = $this->db->table('absensi')->where(['siswa_id' => $siswa->id, 'tanggal' => $tanggal_ini])->get()->getRow();
        if ($cek) return $this->failResourceExists('Anda sudah melakukan presensi masuk hari ini.');

        // --- VALIDASI & SIMPAN FOTO (Base64) ---
        $decodedFoto = \base64_decode($foto);
        $fileName = 'masuk_' . $siswa->id . '_' . \time() . '.jpg';
        \file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedFoto);

        // --- HITUNG STATUS & TELAT ---
        $status = 'Hadir';
        $menit_telat = 0;
        if ($sekarang->isAfter($jam_masuk_pukul)) {
            $status = 'Terlambat';
            $menit_telat = $sekarang->difference($jam_masuk_pukul)->getMinutes();
        }

        // --- SIMPAN DATA KE DATABASE ---
        $this->db->table('absensi')->insert([
            'siswa_id'    => $siswa->id,
            'tanggal'     => $tanggal_ini,
            'waktu_masuk' => $sekarang->toTimeString(),
            'status'      => $status,
            'menit_telat' => \abs($menit_telat),
            'foto_masuk'  => $fileName,
            'lat_masuk'   => $lat,
            'lon_masuk'   => $lon,
            'created_at'  => \date('Y-m-d H:i:s')
        ]);

        return $this->respondCreated(['status' => 200, 'message' => 'Berhasil melakukan presensi masuk. Selamat belajar!']);
    }

    // ==========================================
    // 2. API PRESENSI PULANG
    // ==========================================
    public function pulang()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi berakhir.');

        $lat     = $this->request->getPost('lat');
        $lon     = $this->request->getPost('long');
        $is_mock = $this->request->getPost('is_mock') === 'true';
        $foto    = $this->request->getPost('foto');

        // --- VALIDASI KEAMANAN PULANG (FAKE GPS) ---
        if ($is_mock) {
            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id = ?", [$siswa->id]);
            $siswa_cek = $this->db->table('siswa')->where('id', $siswa->id)->get()->getRow();

            if ($siswa_cek->fraud_count >= 3) {
                $this->db->table('siswa')->where('id', $siswa->id)->update(['is_blocked' => 1]);
                return $this->failUnauthorized('AKUN DIBLOKIR! Terdeteksi Fake GPS sebanyak 3 kali.');
            }
            return $this->failForbidden("Fake GPS Terdeteksi!");
        }

        $sekarang    = Time::now('Asia/Jakarta');
        $tanggal_ini = $sekarang->toDateString();
        $pengaturan  = $this->db->table('pengaturan')->where('id', 1)->get()->getRow();

        // --- CEK APAKAH SUDAH ABSEN MASUK ---
        $absen = $this->db->table('absensi')->where(['siswa_id' => $siswa->id, 'tanggal' => $tanggal_ini])->get()->getRow();
        if (!$absen) {
            return $this->failNotFound('Anda tidak bisa presensi pulang karena tidak tercatat presensi masuk hari ini.');
        }

        if ($absen->waktu_pulang != null) {
            return $this->failResourceExists('Anda sudah melakukan presensi pulang hari ini.');
        }

        // --- SIMPAN FOTO PULANG ---
        $decodedFoto = \base64_decode($foto);
        $fileName = 'pulang_' . $siswa->id . '_' . \time() . '.jpg';
        \file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedFoto);

        // --- UPDATE DATA PULANG ---
        $this->db->table('absensi')->where('id', $absen->id)->update([
            'waktu_pulang' => $sekarang->toTimeString(),
            'foto_pulang'  => $fileName,
            'lat_pulang'   => $lat,
            'lon_pulang'   => $lon,
            'updated_at'   => \date('Y-m-d H:i:s')
        ]);

        return $this->respondUpdated(['status' => 200, 'message' => 'Presensi pulang berhasil. Hati-hati di jalan!']);
    }

    // ==========================================
    // 3. API RIWAYAT (MAX 30 DATA)
    // ==========================================
    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi tidak valid.');

        $riwayat = $this->db->table('absensi')
            ->where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'DESC')
            ->limit(30)
            ->get()
            ->getResult();

        return $this->respond([
            'status'  => 200,
            'message' => 'Data riwayat berhasil ditarik.',
            'data'    => $riwayat
        ]);
    }
}
