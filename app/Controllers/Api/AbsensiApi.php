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
        $foto    = $this->request->getPost('foto'); // Base64 string

        if (!$lat || !$lon || !$foto) {
            return $this->failValidationErrors('Koordinat dan foto selfie wajib dikirim.');
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
            return $this->failForbidden('Presensi masuk belum dibuka. Dibuka pukul ' . $buka_masuk->toTimeString('H:i'));
        }

        if ($sekarang->isAfter($tutup_masuk)) {
            return $this->failForbidden('Batas waktu presensi masuk sudah lewat (Maksimal 1 jam sebelum jam pulang).');
        }

        // --- VALIDASI KEAMANAN (FAKE GPS) ---
        if ($is_mock) {
            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id = ?", [$siswa->id]);
            $siswa_cek = $this->db->table('siswa')->where('id', $siswa->id)->get()->getRow();
            if ($siswa_cek->fraud_count >= 3) {
                $this->db->table('siswa')->where('id', $siswa->id)->update(['is_blocked' => 1]);
                return $this->failUnauthorized('Akun diblokir secara otomatis karena 3x terdeteksi Fake GPS.');
            }
            return $this->fail('Aplikasi manipulasi lokasi (Fake GPS) terdeteksi!');
        }

        // --- VALIDASI RADIUS GEOFENCE ---
        $jarak = \hitung_jarak_haversine($lat, $lon, $pengaturan->latitude_sekolah, $pengaturan->longitude_sekolah);
        if ($jarak > $pengaturan->radius_meter) {
            return $this->fail('Anda berada ' . \round($jarak) . 'm dari sekolah. Radius maksimal: ' . $pengaturan->radius_meter . 'm.');
        }

        // --- CEK DOUBLE INPUT ---
        $cek = $this->db->table('absensi')->where(['siswa_id' => $siswa->id, 'tanggal' => $tanggal_ini])->get()->getRow();
        if ($cek) return $this->failResourceExists('Anda sudah melakukan presensi masuk hari ini.');

        // --- VALIDASI & SIMPAN FOTO (Gaya Modern PHP 8) ---
        $decodedFoto = \base64_decode($foto);
        $finfo       = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType    = $finfo->buffer($decodedFoto);

        if (!\in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
            return $this->fail('Format file foto tidak diizinkan.');
        }

        $fileName = 'masuk_' . $siswa->id . '_' . \time() . '.jpg';
        \file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedFoto);

        // --- HITUNG STATUS & TELAT ---
        $status = 'Hadir';
        $menit_telat = 0;
        if ($sekarang->isAfter($jam_masuk_pukul)) {
            $status = 'Terlambat';
            $menit_telat = $sekarang->difference($jam_masuk_pukul)->getMinutes();
        }

        $this->db->table('absensi')->insert([
            'siswa_id'    => $siswa->id,
            'tanggal'     => $tanggal_ini,
            'waktu_masuk' => $sekarang->toTimeString(),
            'status'      => $status,
            'menit_telat' => \abs($menit_telat),
            'foto_masuk'  => $fileName,
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

        $sekarang    = Time::now('Asia/Jakarta');
        $tanggal_ini = $sekarang->toDateString();
        $pengaturan  = $this->db->table('pengaturan')->where('id', 1)->get()->getRow();

        // --- VALIDASI WAKTU PULANG ---
        $jam_pulang_pukul = Time::parse($tanggal_ini . ' ' . $pengaturan->jam_pulang, 'Asia/Jakarta');
        $batas_akhir      = Time::parse($tanggal_ini . ' 23:45:00', 'Asia/Jakarta');

        if ($sekarang->isBefore($jam_pulang_pukul)) {
            return $this->failForbidden('Belum waktunya presensi pulang. Silakan tunggu hingga ' . \substr($pengaturan->jam_pulang, 0, 5));
        }

        if ($sekarang->isAfter($batas_akhir)) {
            return $this->failForbidden('Batas presensi pulang hari ini (23:45) sudah lewat.');
        }

        // --- CEK APAKAH SUDAH ABSEN MASUK ---
        $absen = $this->db->table('absensi')->where(['siswa_id' => $siswa->id, 'tanggal' => $tanggal_ini])->get()->getRow();
        if (!$absen) {
            return $this->failNotFound('Anda tidak bisa presensi pulang karena tidak tercatat presensi masuk hari ini.');
        }

        if ($absen->waktu_pulang != null) {
            return $this->failResourceExists('Anda sudah melakukan presensi pulang hari ini.');
        }

        // --- VALIDASI KEAMANAN & RADIUS ---
        if ($is_mock) {
            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id = ?", [$siswa->id]);
            return $this->fail('Fake GPS terdeteksi!');
        }

        $jarak = \hitung_jarak_haversine($lat, $lon, $pengaturan->latitude_sekolah, $pengaturan->longitude_sekolah);
        if ($jarak > $pengaturan->radius_meter) {
            return $this->fail('Presensi pulang wajib dilakukan di area sekolah.');
        }

        // --- SIMPAN FOTO PULANG ---
        $decodedFoto = \base64_decode($foto);
        $finfo       = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType    = $finfo->buffer($decodedFoto);

        if (!\in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
            return $this->fail('Format foto tidak valid.');
        }

        $fileName = 'pulang_' . $siswa->id . '_' . \time() . '.jpg';
        \file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedFoto);

        $this->db->table('absensi')->where('id', $absen->id)->update([
            'waktu_pulang' => $sekarang->toTimeString(),
            'foto_pulang'  => $fileName,
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
